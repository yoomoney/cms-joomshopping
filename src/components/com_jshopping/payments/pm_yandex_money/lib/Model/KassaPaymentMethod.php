<?php


namespace YandexMoney\Model;

use YandexCheckout\Client;
use YandexCheckout\Common\Exceptions\NotFoundException;
use YandexCheckout\Model\ConfirmationType;
use YandexCheckout\Model\CurrencyCode;
use YandexCheckout\Model\Payment;
use YandexCheckout\Model\PaymentData\B2b\Sberbank\VatData;
use YandexCheckout\Model\PaymentData\B2b\Sberbank\VatDataType;
use YandexCheckout\Model\PaymentData\PaymentDataB2bSberbank;
use YandexCheckout\Model\PaymentInterface;
use YandexCheckout\Model\PaymentMethodType;
use YandexCheckout\Request\Payments\CreatePaymentRequest;
use YandexCheckout\Request\Payments\Payment\CreateCaptureRequest;
use YandexCheckout\Request\Payments\Payment\CreateCaptureRequestBuilder;

require_once JPATH_ROOT.'/components/com_jshopping/payments/pm_yandex_money_sbbol/SbbolException.php';

if (!defined(_JSHOP_YM_VERSION)) {
    define('_JSHOP_YM_VERSION', '1.1.0');
}


class KassaPaymentMethod
{
    private $module;
    private $client;
    private $shopId;
    private $password;
    private $defaultTaxRateId;
    private $taxRates;
    private $sendReceipt;
    private $descriptionTemplate;
    private $isEnableHoldMode;
    private $pmconfigs;

    /**
     * KassaPaymentMethod constructor.
     *
     * @param \pm_yandex_money $module
     * @param array $pmConfig
     */
    public function __construct($module, $pmConfig)
    {
        $this->pmconfigs           = $pmConfig;
        $this->module              = $module;
        $this->shopId              = $pmConfig['shop_id'];
        $this->password            = $pmConfig['shop_password'];
        $this->descriptionTemplate = !empty($pmConfig['ya_kassa_description_template'])
            ? $pmConfig['ya_kassa_description_template']
            : _JSHOP_YM_DESCRIPTION_DEFAULT_PLACEHOLDER;

        $this->defaultTaxRateId = 1;
        if (!empty($pmConfig['ya_kassa_default_tax'])) {
            $this->defaultTaxRateId = $pmConfig['ya_kassa_default_tax'];
        }

        $this->taxRates = array();
        foreach ($pmConfig as $key => $value) {
            if (strncmp('ya_kassa_tax_', $key, 13) === 0) {
                $taxRateId                  = substr($key, 13);
                $this->taxRates[$taxRateId] = $value;
            }
        }

        $this->sendReceipt      = isset($pmConfig['ya_kassa_send_check']) && $pmConfig['ya_kassa_send_check'] == '1';
        $this->isEnableHoldMode = isset($pmConfig['ya_kassa_enable_hold_mode']) && $pmConfig['ya_kassa_enable_hold_mode'] == '1';
    }

    public function getShopId()
    {
        return $this->shopId;
    }

    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param \jshopOrder $order
     * @param \jshopCart $cart
     * @param $returnUrl
     *
     * @return null|\YandexCheckout\Request\Payments\CreatePaymentResponse
     *
     * @since version
     */
    public function createPayment($order, $cart, $returnUrl)
    {
        try {
            $params  = unserialize($order->payment_params_data);
            $builder = CreatePaymentRequest::builder();
            $builder->setAmount($order->order_total)
                    ->setCapture($this->getCaptureValue($params['payment_type']))
                    ->setClientIp($_SERVER['REMOTE_ADDR'])
                    ->setDescription($this->createDescription($order))
                    ->setMetadata(array(
                        'order_id'       => $order->order_id,
                        'cms_name'       => 'ya_api_joomshopping',
                        'module_version' => _JSHOP_YM_VERSION,
                    ));

            $confirmation = array(
                'type'      => ConfirmationType::REDIRECT,
                'returnUrl' => $returnUrl,
            );
            if (!empty($params['payment_type'])) {
                $paymentType = $params['payment_type'];
                if ($paymentType === PaymentMethodType::ALFABANK) {
                    $paymentType  = array(
                        'type'  => $paymentType,
                        'login' => trim($params['alfaLogin']),
                    );
                    $confirmation = ConfirmationType::EXTERNAL;
                } elseif ($paymentType === PaymentMethodType::QIWI) {
                    $paymentType = array(
                        'type'  => $paymentType,
                        'phone' => preg_replace('/[^\d]+/', '', $params['qiwiPhone']),
                    );
                }
                $builder->setPaymentMethodData($paymentType);
            }
            $builder->setConfirmation($confirmation);

            $receipt = null;
            if (count($cart->products) && $this->sendReceipt) {
                $this->factoryReceipt($builder, $cart->products, $order);
            }

            $request = $builder->build();
            if ($request->hasReceipt()) {
                $request->getReceipt()->normalize($request->getAmount());
            }
        } catch (\Exception $e) {
            $this->module->log('error', 'Failed to build request: '.$e->getMessage());

            return null;
        }

        try {
            $payment = $this->getClient()->createPayment($request);
        } catch (\Exception $e) {
            $this->module->log('error', 'Failed to create payment: '.$e->getMessage());

            return null;
        }

        return $payment;
    }


    public function createSbbolPayment($order, $cart, $returnUrl)
    {
        try {
            $pmconfigs = $this->pmconfigs;
            $builder   = CreatePaymentRequest::builder();
            $builder->setAmount($order->order_total)
                    ->setCapture(true)
                    ->setClientIp($_SERVER['REMOTE_ADDR'])
                    ->setMetadata(array(
                        'order_id'       => $order->order_id,
                        'cms_name'       => 'ya_api_joomshopping',
                        'module_version' => _JSHOP_YM_VERSION,
                    ));

            $confirmation = array(
                'type'      => ConfirmationType::REDIRECT,
                'returnUrl' => $returnUrl,
            );

            $usedTaxes = array();
            if (count($cart->products)) {
                foreach ($cart->products as $product) {
                    if (isset($pmconfigs['ya_sbbol_tax_'.$product['tax_id']])) {
                        $usedTaxes[] = $pmconfigs['ya_sbbol_tax_'.$product['tax_id']];
                    } else {
                        $usedTaxes[] = $pmconfigs['ya_sbbol_default_tax'];
                    }
                }
            } else {

            }

            $usedTaxes = array_unique($usedTaxes);
            if (count($usedTaxes) !== 1) {
                throw new \SbbolException();
            }

            $paymentMethodData = new PaymentDataB2bSberbank();
            $vatData           = new VatData();
            $vatType           = reset($usedTaxes);
            if ($vatType !== VatDataType::UNTAXED) {
                $vatData->setType(VatDataType::CALCULATED);
                $vatData->setRate($vatType);
                $vatSum = $order->order_total * $vatType / 100;
                $vatData->setAmount(array('value' => $vatSum, 'currency' => CurrencyCode::RUB));
            } else {
                $vatData->setType(VatDataType::UNTAXED);
            }
            $orderData = json_decode(json_encode($order), true);
            foreach ($orderData as $key => $value) {
                $orderData["%{$key}%"] = $value;
                unset($orderData[$key]);
            }

            $paymentPurpose = strtr($pmconfigs['sbbol_purpose'], $orderData);

            $paymentMethodData->setVatData($vatData);
            $paymentMethodData->setPaymentPurpose($paymentPurpose);
            $builder->setConfirmation($confirmation);
            $builder->setPaymentMethodData($paymentMethodData);
            $request = $builder->build();
        } catch (\SbbolException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->module->log('error', 'Failed to build request: '.$e->getMessage());

            return null;
        }

        try {
            $payment = $this->getClient()->createPayment($request);
        } catch (\Exception $e) {
            $this->module->log('error', 'Failed to create payment: '.$e->getMessage());

            return null;
        }

        return $payment;
    }

    /**
     * @param PaymentInterface $payment
     *
     * @return PaymentInterface|null
     */
    public function capturePayment($payment)
    {
        try {
            $builder = CreateCaptureRequest::builder();
            $builder->setAmount($payment->getAmount());
            $request  = $builder->build();
            $response = $this->getClient()->capturePayment($request, $payment->getId());
        } catch (\Exception $e) {
            $this->module->log('error', 'Failed to create capture payment: '.$e->getMessage());

            return null;
        }

        return $response;
    }

    /**
     * @param string $paymentId
     *
     * @return PaymentInterface|null
     */
    public function fetchPayment($paymentId)
    {
        $payment = null;
        try {
            $payment = $this->getClient()->getPaymentInfo($paymentId);
        } catch (\Exception $e) {
            $this->module->log('error', 'Failed to fetch payment information from API: '.$e->getMessage());
        }

        return $payment;
    }

    /**
     * @param \YandexCheckout\Request\Payments\CreatePaymentRequestBuilder|CreateCaptureRequestBuilder $builder
     * @param array $products
     * @param $order
     */
    public function factoryReceipt($builder, $products, $order)
    {
        $shippingModel   = \JSFactory::getTable('shippingMethod', 'jshop');
        $shippingMethods = $shippingModel->getAllShippingMethodsCountry($order->d_country, $order->payment_method_id);
        $defaultTaxRate  = $this->defaultTaxRateId;
        if (!empty($order->email)) {
            $builder->setReceiptEmail($order->email);
        } else {
            $builder->setReceiptPhone($order->phone);
        }
        $shipping = false;
        foreach ($shippingMethods as $tmp) {
            if ($tmp->shipping_id == $order->shipping_method_id) {
                $shipping = $tmp;
                break;
            }
        }

        $moduleTaxes = \JSFactory::getModel("taxes");
        $allTaxes    = $moduleTaxes ? $moduleTaxes->getAllTaxes() : array();
        foreach ($products as $product) {
            if (is_array($product)) {
                if (isset($product['tax_id']) && !empty($this->taxRates[$product['tax_id']])) {
                    $taxId = $this->taxRates[$product['tax_id']];
                } else {
                    $taxId = $defaultTaxRate;
                }
                $builder->addReceiptItem($product['product_name'], $product['price'], $product['quantity'], $taxId);
            } elseif (is_object($product)) {
                $taxId         = $defaultTaxRate;
                $suitableTaxes = array_filter($allTaxes, function ($tax) use ($product) {
                    return $product->product_tax == $tax->tax_value;
                });
                if (!empty($suitableTaxes)) {
                    $suitableTax = reset($suitableTaxes);
                    if (!empty($this->taxRates[$suitableTax->tax_id])) {
                        $taxId = $this->taxRates[$suitableTax->tax_id];
                    }
                }
                $builder->addReceiptItem($product->product_name, $product->product_item_price,
                    $product->product_quantity, $taxId);
            }
        }

        if ($order->shipping_method_id && $shipping) {
            if (!empty($this->taxRates[$shipping->shipping_tax_id])) {
                $taxId = $this->taxRates[$shipping->shipping_tax_id];
                $builder->addReceiptShipping($shipping->name, $shipping->shipping_stand_price, $taxId);
            } else {
                $builder->addReceiptShipping($shipping->name, $shipping->shipping_stand_price, $defaultTaxRate);
            }
        }
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if ($this->client === null) {
            $this->client = new Client();
            $this->client->setAuth($this->shopId, $this->password);
            $this->client->setLogger($this->module);
        }

        return $this->client;
    }

    /**
     * @return bool
     */
    public function checkConnection()
    {
        try {
            $payment = $this->getClient()->getPaymentInfo('00000000-0000-0000-0000-000000000001');
        } catch (NotFoundException $e) {
            return true;
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param \jshopOrder $order
     *
     * @return string
     */
    private function createDescription($order)
    {
        $descriptionTemplate = $this->descriptionTemplate;

        $replace = array();
        foreach ($order as $property => $value) {
            $replace['%'.$property.'%'] = $value;
        }

        $description = strtr($descriptionTemplate, $replace);

        return (string)mb_substr($description, 0, Payment::MAX_LENGTH_DESCRIPTION);
    }

    /**
     * @return bool
     */
    public function isEnableHoldMode()
    {
        return $this->isEnableHoldMode;
    }

    /**
     * @param string $paymentMethod
     *
     * @return bool
     */
    private function getCaptureValue($paymentMethod)
    {
        if (!$this->isEnableHoldMode()) {
            return true;
        }

        return !in_array($paymentMethod, array('', PaymentMethodType::BANK_CARD));
    }

    /**
     * @return bool
     */
    public function isSendReceipt()
    {
        return $this->sendReceipt;
    }

}