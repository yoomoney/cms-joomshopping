<?php

use YandexCheckout\Model\PaymentStatus;
use YandexCheckout\Request\Payments\Payment\CreateCaptureRequest;

defined('_JEXEC') or die('Restricted access');

include dirname(__FILE__).'/../../../components/com_jshopping/payments/pm_yandex_money/pm_yandex_money.php';

class plgJshoppingAdminPm_yandex_money extends JPlugin
{
    public function onBeforeChangeOrderStatusAdmin($order_id, &$status)
    {
        $paymentmethod = JSFactory::getTable('paymentmethod', 'jshop');

        $all_payment_methods = $paymentmethod->getAllPaymentMethods();
        $pm_kassa            = null;
        foreach ($all_payment_methods as $pm) {
            $scriptname = ($pm->scriptname != '') ? $pm->scriptname : $pm->payment_class;
            if ($scriptname !== 'pm_yandex_money') {
                continue;
            }
            $pm_kassa = $pm;
            break;
        }
        if (!$pm_kassa) {
            return;
        }

        $paymentmethod->load($pm_kassa->payment_id);
        $parseString = new parseString($pm_kassa->payment_params);
        $pmconfig    = $parseString->parseStringToParams();

        $pm_yandex_money = new pm_yandex_money();
        $kassa           = $pm_yandex_money->getKassaPaymentMethod($pmconfig);
        if (!$kassa->isEnableHoldMode()) {
            return;
        }

        $onHoldStatus   = $pmconfig['ya_kassa_hold_mode_on_hold_status'];
        $cancelStatus   = $pmconfig['ya_kassa_hold_mode_cancel_status'];
        $completeStatus = $pmconfig['kassa_transaction_end_status'];
        if (!in_array($status, array($completeStatus, $cancelStatus))) {
            return;
        }

        /** @var jshopOrder $order */
        $order = JSFactory::getTable('order', 'jshop');
        $order->load($order_id);

        if ($order->payment_method_id !== $pm_kassa->payment_id) {
            return;
        }

        if ($order->order_status !== $onHoldStatus) {
            return;
        }

        if ($status === $completeStatus) {
            $apiClient = $kassa->getClient();
            $payment   = $apiClient->getPaymentInfo($pm_yandex_money->getOrderModel()->getPaymentIdByOrderId($order->order_id));
            try {
                $builder = CreateCaptureRequest::builder();
                $builder->setAmount($order->order_total);

                if ($kassa->isSendReceipt()) {
                    $kassa->factoryReceipt($builder, $order->getAllItems(), $order);
                }

                $request = $builder->build();
                $payment = $apiClient->capturePayment($request, $payment->getId());
            } catch (\Exception $e) {
                $pm_yandex_money->log('error', 'Capture error: '.$e->getMessage());
            }

            if (!$payment || $payment->getStatus() !== PaymentStatus::SUCCEEDED) {
                $status = $onHoldStatus;
                $pm_yandex_money->saveOrderHistory($order, _JSHOP_YM_HOLD_MODE_CAPTURE_PAYMENT_FAIL);
                $pm_yandex_money->log('error', 'Capture payment error: capture failed');
                return;
            }
            $order->order_status = $completeStatus;
            $pm_yandex_money->saveOrderHistory($order, _JSHOP_YM_HOLD_MODE_CAPTURE_PAYMENT_SUCCESS);
        }

        if ($status === $cancelStatus) {
            $apiClient = $kassa->getClient();
            $payment   = null;
            try {
                $payment = $apiClient->cancelPayment($order->transaction);
            } catch (\Exception $e) {
                $pm_yandex_money->log('error', 'Capture error: '.$e->getMessage());
            }
            if (!$payment || $payment->getStatus() !== PaymentStatus::CANCELED) {
                $status = $onHoldStatus;
                $pm_yandex_money->saveOrderHistory($order, _JSHOP_YM_HOLD_MODE_CANCEL_PAYMENT_FAIL);
                $pm_yandex_money->log('error', 'Cancel payment error: cancel failed');
                return;
            }
            $order->order_status = $cancelStatus;
            $pm_yandex_money->saveOrderHistory($order, _JSHOP_YM_HOLD_MODE_CANCEL_PAYMENT_SUCCESS);
        }
    }
}
