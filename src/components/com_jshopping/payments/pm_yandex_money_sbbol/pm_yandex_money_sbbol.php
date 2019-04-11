<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

use YandexCheckout\Model\Notification\NotificationSucceeded;
use YandexCheckout\Model\Notification\NotificationWaitingForCapture;
use YandexCheckout\Model\NotificationEventType;
use YandexCheckout\Model\PaymentMethodType;
use YandexCheckout\Model\PaymentStatus;
use YandexMoney\Model\KassaPaymentMethod;

defined('_JEXEC') or die('Restricted access');

define('JSH_DIR', realpath(dirname(__FILE__).'/../..'));
define('DIR_DOWNLOAD', JSH_DIR.'/log');

require_once dirname(__FILE__).'/../pm_yandex_money/lib/autoload.php';
require_once dirname(__FILE__).'/SbbolException.php';

class pm_yandex_money_sbbol extends PaymentRoot
{
    private $orderModel;
    private $kassa;

    public function __construct()
    {
        $this->joomlaVersion = (version_compare(JVERSION, '3.0', '<') == 1) ? 2 : 3;
    }

    function showPaymentForm($params, $pmconfigs)
    {
        $this->loadLanguageFile();
        include(dirname(__FILE__)."/paymentform.php");
    }

    public function getArticlesList()
    {
        try {
            if (!defined('JPATH_SITE')) {
                throw new Exception('JPATH_SITE not defined');
            }
            if (!class_exists('JModelLegacy')) {
                throw new Exception('JModelLegacy not exists');
            }
            if (!file_exists(JPATH_SITE.'/components/com_content/models')) {
                throw new Exception(JPATH_SITE.'/components/com_content/models not exists');
            }
            if (!is_dir(JPATH_SITE.'/components/com_content/models')) {
                throw new Exception(JPATH_SITE.'/components/com_content/models not dir');
            }

            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', 1);

            JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
            if (!class_exists('ContentHelperAssociation')) {
                $path = JPATH_SITE.'/components/com_content/helpers/association.php';
                if (file_exists($path)) {
                    require_once $path;
                }
            }
            /** @var ContentModelArticles $model */
            $model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
            if ($model === null) {
                throw new Exception('model is null');
            }
            if (!class_exists('JComponentHelper')) {
                throw new Exception('JComponentHelper not exists');
            }
            /** @var Joomla\Registry\Registry $params */
            $params = JComponentHelper::getParams('com_content');
            $model->setState('params', $params);
            $model->setState('list.limit', 0);

            $list = array();
            foreach ($model->getItems() as $page) {
                $list[$page->id] = $page->title;
            }
        } catch (Exception $e) {
            $list = array();
        }

        return $list;
    }

    public function getDisplayNameParams()
    {
        $names      = array();
        $this->mode = $this->getMode($this->getParams());
        if ($this->mode == self::MODE_PAYMENTS) {
            $names = array(
                'ya_payments_fio' => _JSHOP_YM_PAYMENTS_FIO_LABEL,
            );
        }

        return $names;
    }

    /**
     * function call in admin
     */
    public function showAdminFormParams($params)
    {
        $array_params = array(
            'testmode',
            'password',
            'shoppassword',
            'shopid',
            'scid',
            'account',
            'transaction_end_status',
            'ym_pay_id',
            'ym_pay_desc',
            'ya_payments_fio',
            'ya_sbbol_default_tax',
            'sbbol_purpose',
        );
        if (empty($params['sbbol_purpose'])) {
            $params['sbbol_purpose'] = 'Оплата заказа %order_id%';
        }

        $taxes = $taxes = JSFactory::getAllTaxes();

        foreach ($taxes as $k => $tax) {
            $array_params[] = 'ya_sbbol_tax_'.$k;
        }

        foreach ($array_params as $key) {
            if (!isset($params[$key])) {
                $params[$key] = '';
            }
        }
        foreach (array('kassa', 'money', 'payments') as $type) {
            $key = $type.'_transaction_end_status';
            if (!isset($params[$key])) {
                $params[$key] = $params['transaction_end_status'];
            }
        }
        if (!isset($params['use_ssl'])) {
            $params['use_ssl'] = 0;
        }
        $params['articles'] = $this->getArticlesList();
        $this->loadLanguageFile();
        $orders     = JModelLegacy::getInstance('orders', 'JshoppingModel'); //admin model
        $dispatcher = JDispatcher::getInstance();
        if ($this->joomlaVersion === 2) {
            $filename = '2x';
        } else {
            $filename = '';

            $dispatcher->register('onBeforeEditPayments', array($this, 'onBeforeEditPayments'));
        }

        include(dirname(__FILE__)."/sbboladminparamsform".$filename.".php");
    }

    public function onBeforeEditPayments($view)
    {
        $view->tmp_html_start = '';
        $view->tmp_html_end   = '';
    }

    private function loadLanguageFile()
    {
        $lang    = JFactory::getLanguage();
        $langtag = $lang->getTag();
        if (file_exists(JPATH_ROOT.'/components/com_jshopping/payments/pm_yandex_money_sbbol/lang/'.$langtag.'.php')) {
            require_once(JPATH_ROOT.'/components/com_jshopping/payments/pm_yandex_money_sbbol/lang/'.$langtag.'.php');
        } else {
            require_once(JPATH_ROOT.'/components/com_jshopping/payments/pm_yandex_money_sbbol/lang/ru-RU.php');
        }
    }

    /**
     * @param $pmconfigs
     * @param $order
     */
    function showEndForm($pmconfigs, $order)
    {
        $this->ym_test_mode = $pmconfigs['testmode'];
        $this->ym_pay_mode  = ($pmconfigs['paymode'] == '1');

        $uri = JURI::getInstance();

        $this->loadLanguageFile();
        $cart = JSFactory::getModel('cart', 'jshop');
        if (method_exists($cart, 'init')) {
            $cart->init('cart', 1);
        } else {
            $cart->load('cart');
        }

        /** @var jshopCart $cart */
        $cart = JSFactory::getModel('cart', 'jshop');
        if (method_exists($cart, 'init')) {
            $cart->init('cart', 1);
        } else {
            $cart->load('cart');
        }


        $redirectUrl = $uri->toString(array('scheme', 'host', 'port'))
                       .SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_yandex_money&no_lang=1&order_id=".$order->order_id);
        $redirectUrl = htmlspecialchars_decode($redirectUrl);
        try {
            $payment = $this->getKassaPaymentMethod($pmconfigs)->createSbbolPayment($order, $cart, $redirectUrl);
        } catch (\SbbolException $e) {

            $redirectUrl = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
            $app         = JFactory::getApplication();
            $app->enqueueMessage('У вас в корзине товары, для которых действуют разные ставки НДС — их нельзя оплатить одновременно. Можно разбить покупку на несколько этапов: сначала оплатить товары с одной ставкой НДС, потом — с другой.',
                'error');
            $app->redirect($redirectUrl);
        }

        $redirect = $redirectUrl;
        if ($payment !== null) {
            $confirmation = $payment->getConfirmation();
            if ($confirmation instanceof \YandexCheckout\Model\Confirmation\ConfirmationRedirect) {
                $redirect = $confirmation->getConfirmationUrl();
            }
            $this->getOrderModel()->savePayment($order->order_id, $payment);
        } else {
            $redirect = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
            $this->setErrorMessage(_JSHOP_YM_ERROR_MESSAGE_CREATE_PAYMENT);
        }

        $app = JFactory::getApplication();
        $app->redirect($redirect);
    }

    public function getUrlParams($pmconfigs)
    {
        $this->mode = $this->getMode($pmconfigs);
        if ($this->mode == self::MODE_KASSA && $_POST['paymentType'] == 'MP' && $this->checkSign($_POST)) {
            $this->ym_shopid = $pmconfigs['shopid'];
            $this->sendCode($_POST, '0');
            die();
        }
        $params = array();
        if ($_POST['orderNumber']) {
            $params['order_id'] = (int)$_POST['module_order_id'];
        } else {
            $params['order_id'] = (int)$_POST['label'];
        }
        $params['hash']      = "";
        $params['checkHash'] = 0;

        return $params;
    }

    /**
     * Метод проверки валидности платежа, вызывается при создании платежа после возврата пользователя на страницу
     * подтверждения заказа, так же вызывается при приходе нотификации от платёжной системы
     *
     * @param array $pmConfigs Массив настроек платёжной системы
     * @param object $order Инстанс заказа
     * @param string $act Значение параметра "act" в ссылке
     *
     * @return array Массив с информацией о транзакции
     */
    function checkTransaction($pmConfigs, $order, $act)
    {
        if ($act === 'notify') {
            $this->log('debug', 'Notification callback called');
            $source = file_get_contents('php://input');
            $this->log('debug', 'Notification body: '.$source);
            if (empty($source)) {
                $this->log('debug', 'Notification error: body is empty!');
                header('HTTP/1.1 400 Body is empty');
                die();
            }
            $json = json_decode($source, true);
            if (empty($json)) {
                $this->log('debug', 'Notification error: invalid body!');
                header('HTTP/1.1 400 Invalid body');
                die();
            }
            $kassa        = $this->getKassaPaymentMethod($pmConfigs);
            $notification = ($json['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
                ? new NotificationSucceeded($json)
                : new NotificationWaitingForCapture($json);
            $payment      = $kassa->fetchPayment($notification->getObject()->getId());
            if (!$payment) {
                $this->log('debug', 'Notification error: payment not exist');
                header('HTTP/1.1 404 Payment not exists');
                die();
            }

            if ($notification->getEvent() === NotificationEventType::PAYMENT_SUCCEEDED
                && $payment->getStatus() === PaymentStatus::SUCCEEDED
            ) {
                try {
                    $jshopConfig = JSFactory::getConfig();

                    /** @var jshopCheckout $checkout */
                    $checkout             = JSFactory::getModel('checkout', 'jshop');
                    $endStatus            = $pmConfigs['transaction_end_status'];
                    $order->order_created = 1;
                    $order->order_status  = $endStatus;
                    $order->store();
                    if ($jshopConfig->send_order_email) {
                        $checkout->sendOrderEmail($order->order_id);
                    }
                    if ($jshopConfig->order_stock_removed_only_paid_status) {
                        $product_stock_removed = in_array($endStatus,
                            $jshopConfig->payment_status_enable_download_sale_file);
                    } else {
                        $product_stock_removed = 1;
                    }
                    if ($product_stock_removed) {
                        $order->changeProductQTYinStock("-");
                    }
                    $checkout->changeStatusOrder($order->order_id, $endStatus, 0);
                    $message = '';
                    $paymentMethod = $payment->getPaymentMethod();
                    if($paymentMethod->getType() == PaymentMethodType::B2B_SBERBANK) {
                        $payerBankDetails = $payment->getPaymentMethod()->getPayerBankDetails();

                        $fields  = array(
                            'fullName'   => 'Полное наименование организации',
                            'shortName'  => 'Сокращенное наименование организации',
                            'adress'     => 'Адрес организации',
                            'inn'        => 'ИНН организации',
                            'kpp'        => 'КПП организации',
                            'bankName'   => 'Наименование банка организации',
                            'bankBranch' => 'Отделение банка организации',
                            'bankBik'    => 'БИК банка организации',
                            'account'    => 'Номер счета организации',
                        );

                        foreach ($fields as $field => $caption) {
                            if (isset($requestData[$field])) {
                                $message .= $caption.': '.$payerBankDetails->offsetGet($field).'\n';
                            }
                        }
                    }

                    if (!empty($message)) {
                        $this->saveOrderHistory($order, $message);
                    }
                } catch (Exception $e) {
                    $this->log('debug', $e->getMessage());
                    header('HTTP/1.1 500 Internal Server Error');
                }
                exit();
            }

            $this->log('debug', 'Notification error: wrong payment status');
            header('HTTP/1.1 401 Payment not exists');
            exit();
        } else {
            $this->log('debug', 'Check transaction for order#'.$order->order_id);
            $transactionId = $this->getOrderModel()->getPaymentIdByOrderId($order->order_id);
            if (empty($transactionId)) {
                $this->log('debug', 'Payment id for order#'.$order->order_id.' not exists');

                return array(3, 'Transaction not exists', '', 'Transaction not exists');
            }
            $payment = $this->getKassaPaymentMethod($pmConfigs)->fetchPayment($transactionId);
            if ($payment === null) {
                $this->log('debug', 'Payment for order#'.$order->order_id.' not exists');

                return array(3, 'Transaction not exists', '', 'Transaction not exists');
            }
            if (!$payment->getPaid()) {
                $this->log('debug', 'Payment '.$payment->getId().' for order#'.$order->order_id.' not paid');
                $redirectUrl = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
                $app         = JFactory::getApplication();
                $app->redirect($redirectUrl);
            } else {
                $this->log('debug', 'Payment '.$payment->getId().' for order#'.$order->order_id.' paid');

                return array(
                    -1,
                    sprintf(_JSHOP_YM_PAYMENT_CAPTURED_TEXT, $transactionId),
                    $transactionId,
                    _JSHOP_YM_PAYMENT_CAPTURED,
                );
            }
        }
    }

    /**
     * @return \YandexMoney\Model\OrderModel
     */
    public function getOrderModel()
    {
        if ($this->orderModel === null) {
            $this->orderModel = new \YandexMoney\Model\OrderModel();
        }

        return $this->orderModel;
    }

    public function log($level, $message, $context = array())
    {
        if (!$this->debugLog) {
            return;
        }
        $replace = array();
        foreach ($context as $key => $value) {
            if (is_scalar($value)) {
                $replace['{'.$key.'}'] = $value;
            } else {
                $replace['{'.$key.'}'] = json_encode($value);
            }
        }
        if (!empty($replace)) {
            $message = strtr($message, $replace);
        }
        $fileName = $this->getLogFileName();
        $fd       = @fopen($fileName, 'a');
        if ($fd) {
            flock($fd, LOCK_EX);
            fwrite($fd, date(DATE_ATOM).' ['.$level.'] '.$message."\r\n");
            flock($fd, LOCK_UN);
            fclose($fd);
        }
    }

    private function getLogFileName()
    {
        return realpath(JSH_DIR).'/log/pm_yandex_money.log';
    }

    public function getKassaPaymentMethod($pmConfigs)
    {
        $this->loadLanguageFile();
        if ($this->kassa === null) {
            $this->kassa = new KassaPaymentMethod($this, $pmConfigs);
        }

        return $this->kassa;
    }

    /**
     * @param $order
     *
     * @return string
     */
    private function generateReturnUrl($order)
    {
        $uri         = JURI::getInstance();
        $redirectUrl = $uri->toString(array('scheme', 'host', 'port'))
                       .SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_yandex_money_sbbol&no_lang=1&order_id=".$order->order_id);
        $redirectUrl = htmlspecialchars_decode($redirectUrl);

        return $redirectUrl;
    }

    public function saveOrderHistory($order, $comments)
    {
        $history                    = JSFactory::getTable('orderHistory', 'jshop');
        $history->order_id          = $order->order_id;
        $history->order_status_id   = $order->order_status;
        $history->status_date_added = getJsDate();
        $history->customer_notify   = 0;
        $history->comments          = $comments;

        return $history->store();
    }
}
