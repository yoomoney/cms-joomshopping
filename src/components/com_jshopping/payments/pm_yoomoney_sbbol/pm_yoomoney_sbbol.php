<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YooMoney
 * @copyright Copyright (C) 2020 YooMoney. All rights reserved.
 */

use YooKassa\Model\Notification\AbstractNotification;
use YooMoney\Helpers\YoomoneyNotificationFactory;
use YooMoney\Model\KassaPaymentMethod;
use YooMoney\Model\SbbolException;
use YooMoney\Helpers\Logger;
use YooMoney\Helpers\TransactionHelper;
use YooMoney\Helpers\JVersionDependenciesHelper;

defined('_JEXEC') or die('Restricted access');

define('JSH_DIR', realpath(dirname(__FILE__).'/../..'));
define('DIR_DOWNLOAD', JSH_DIR.'/log');
define('_JSHOP_YOO_VERSION', '2.3.0');

require_once dirname(__FILE__) . '/../pm_yoomoney/lib/autoload.php';
require_once dirname(__FILE__).'/SbbolException.php';

class pm_yoomoney_sbbol extends PaymentRoot
{
    private $orderModel;
    private $kassa;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TransactionHelper
     */
    private $transactionHelper;

    /**
     * @var JVersionDependenciesHelper
     */
    private $versionHelper;

    /**
     * @var YoomoneyNotificationFactory
     */
    private $yooNotificationHelper;

    public function __construct()
    {
        $this->versionHelper = new JVersionDependenciesHelper();
        $this->logger = new Logger();
        $this->transactionHelper = new TransactionHelper();
        $this->yooNotificationHelper = new YoomoneyNotificationFactory();
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
        return array();
    }

    /**
     * function call in admin
     *
     * @param string|array $params
     * @return void
     *
     */
    public function showAdminFormParams($params)
    {
        $array_params = array(
            'testmode',
            'password',
            'shop_password',
            'shop_id',
            'scid',
            'account',
            'transaction_end_status',
            'yoopay_id',
            'yoopay_desc',
            'yoo_payments_fio',
            'yoo_sbbol_default_tax',
            'sbbol_purpose',
        );

        if (!is_array($params)) {
            $params = array();
        }

        if (!isset($params['sbbol_purpose']) || empty($params['sbbol_purpose'])) {
            $params['sbbol_purpose'] = 'Оплата заказа %order_id%';
        }

        $taxes = $taxes = JSFactory::getAllTaxes();

        foreach ($taxes as $k => $tax) {
            $array_params[] = 'yoo_sbbol_tax_'.$k;
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

        $filename = $this->versionHelper->getFilesVersionPostfix();
        $this->versionHelper->registerEventListener('onBeforeEditPayments', array($this, 'onBeforeEditPayments'));

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
        if (file_exists(JPATH_ROOT.'/components/com_jshopping/payments/pm_yoomoney_sbbol/lang/'.$langtag.'.php')) {
            require_once(JPATH_ROOT.'/components/com_jshopping/payments/pm_yoomoney_sbbol/lang/'.$langtag.'.php');
        } else {
            require_once(JPATH_ROOT.'/components/com_jshopping/payments/pm_yoomoney_sbbol/lang/ru-RU.php');
        }
    }

    /**
     * @param $pmconfigs
     * @param $order
     */
    function showEndForm($pmconfigs, $order)
    {
        $app = JFactory::getApplication();
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
            .$this->versionHelper->getSefLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_yoomoney&no_lang=1&order_id=".$order->order_id);
        $redirectUrl = htmlspecialchars_decode($redirectUrl);

        try {
            $payment = $this->getKassaPaymentMethod($pmconfigs)->createSbbolPayment($order, $cart, $redirectUrl);
        } catch (SbbolException $e) {
            $redirectUrl = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
            $app->enqueueMessage('У вас в корзине товары, для которых действуют разные ставки НДС — их нельзя оплатить одновременно. Можно разбить покупку на несколько этапов: сначала оплатить товары с одной ставкой НДС, потом — с другой.',
                'error');
            $app->redirect($redirectUrl);
        } catch (\Exception $e) {
            $redirectUrl = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
            $app->enqueueMessage(_JSHOP_YOO_ERROR_MESSAGE_CREATE_PAYMENT, 'error');
            $app->redirect($redirectUrl);
        }

        $redirect = $redirectUrl;

        if ($payment !== null) {
            $confirmation = $payment->getConfirmation();

            if ($confirmation instanceof \YooKassa\Model\Confirmation\ConfirmationRedirect) {
                $redirect = $confirmation->getConfirmationUrl();
            }

            $this->getOrderModel()->savePayment($order->order_id, $payment);
        } else {
            $redirect = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
            $this->setErrorMessage(_JSHOP_YOO_ERROR_MESSAGE_CREATE_PAYMENT);
        }

        $app->redirect($redirect);
    }

    public function getUrlParams($pmconfigs)
    {
        $params = array(
            "hash"      => "",
            "checkHash" => 0,
        );

        if (isset($_GET['act']) && $_GET['act'] === 'notify') {
            $this->log('debug', 'Notification callback check URL parameters');

            try {
                $notification = $this->yooNotificationHelper->getNotificationObject();
                $orderId = $this->getOrderIdByNotification($notification);
            } catch (Exception $e) {
                $this->log('debug', 'Notification error: '.$e->getMessage());
                header('HTTP/1.1 400 Invalid body');
                die();
            }
            $params['order_id']          = $orderId;
            $params['hash']              = "";
            $params['checkHash']         = 0;
            $params['checkReturnParams'] = 1;
            $this->log('debug', 'Notify url params is: '.json_encode($params));

            return $params;
        }

        if ($_POST['orderNumber']) {
            $params['order_id'] = (int)$_POST['module_order_id'];
        } else {
            $params['order_id'] = (int)$_POST['label'];
        }

        return $params;
    }

    /**
     * Возвращает id заказа по значению metadata.order_id в уведомлении, или, если уведомление о статусе
     * refund.succeeded, то вызывает метод поиска refund.succeeded в БД по id платежа
     *
     * @param AbstractNotification $notification
     * @return string
     * @throws Exception
     */
    private function getOrderIdByNotification($notification)
    {
        $object = $notification->getObject();
        if (method_exists($object, 'getMetadata')) {
            $meta = $object->getMetadata();
            $orderId = $meta['order_id'];
        } else {
            $orderId = $this->getOrderModel()->getOrderIdByPaymentId($object->getPaymentId());
        }

        if (empty($orderId)) {
            throw new \Exception('Notification error: order_id was not found');
        }

        return $orderId;
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
        $kassa = $this->getKassaPaymentMethod($pmConfigs);

        if ($act === 'notify') {
            $this->log('debug', 'Notification callback called');

            try {
                $result = $this->transactionHelper->processNotification($kassa, $pmConfigs, $order);
            } catch (Exception $e) {
                $this->log('debug', $e->getMessage());
                header('HTTP/1.1 500 Internal Server Error');
                die;
            }
            if (!$result) {
                $this->log('debug', 'Notification error: wrong payment status');
                header('HTTP/1.1 401 Payment does not exists');
            }

            exit();
        }

        $this->log('debug', 'Check transaction for order#'.$order->order_id);

        if (!$this->checkPaymentByOrderId($order->order_id, $pmConfigs)) {
            return array(3, 'Transaction not exists', '', 'Transaction not exists');
        }
        $transactionId = $this->getOrderModel()->getPaymentIdByOrderId($order->order_id);
        if (empty($transactionId)) {
            $this->log('debug', 'Payment id for order#'.$order->order_id.' not exists');

            return array(3, 'Transaction not exists', '', 'Transaction not exists');
        }

        return array(
            -1,
            sprintf(_JSHOP_YOO_PAYMENT_CAPTURED_TEXT, $transactionId),
            $transactionId,
            _JSHOP_YOO_PAYMENT_CAPTURED,
        );
    }

    /**
     * Проверяет, что платеж существует для переданного id заказа и оплачен
     *
     * @param $orderId
     * @param $pmConfigs
     * @return bool
     */
    private function checkPaymentByOrderId($orderId, $pmConfigs)
    {
        $paymentId = $this->getOrderModel()->getPaymentIdByOrderId($orderId);
        if (empty($paymentId)) {
            $this->log('debug', 'Redirect user to payment method page: payment id not exists');

            return false;
        }
        $payment = $this->getKassaPaymentMethod($pmConfigs)->fetchPayment($paymentId);
        if ($payment === null) {
            $this->log('debug', 'Redirect user to payment method page: payment not exists');

            return false;
        }
        if (!$payment->getPaid()) {
            $this->log('debug', 'Redirect user to payment method page: payment not paid');
            $redirectUrl = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
            $app         = JFactory::getApplication();
            $app->redirect($redirectUrl);

            return false;
        }

        $this->log('debug', 'Payment '.$payment->getId().' for order#' . $orderId . ' paid');

        return true;
    }

    /**
     * @return \YooMoney\Model\OrderModel
     */
    public function getOrderModel()
    {
        if ($this->orderModel === null) {
            $this->orderModel = new \YooMoney\Model\OrderModel();
        }

        return $this->orderModel;
    }

    public function log($level, $message, $context = array())
    {
        $this->logger->log($level, $message, $context);
    }

    /**
     * Возвращает путь к файлу лога
     *
     * @return string
     */
    private function getLogFileName()
    {
        return $this->logger->getLogFileName();
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
            .$this->versionHelper->getSefLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_yoomoney_sbbol&no_lang=1&order_id=".$order->order_id);
        $redirectUrl = htmlspecialchars_decode($redirectUrl);

        return $redirectUrl;
    }
}
