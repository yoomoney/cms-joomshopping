<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

use YandexCheckout\Model\Confirmation\ConfirmationEmbedded;
use YandexCheckout\Model\Confirmation\ConfirmationRedirect;
use YandexCheckout\Model\Notification\NotificationSucceeded;
use YandexCheckout\Model\Notification\NotificationWaitingForCapture;
use YandexCheckout\Model\NotificationEventType;
use YandexCheckout\Model\PaymentMethodType;
use YandexCheckout\Model\PaymentStatus;
use YandexCheckout\Model\Receipt\PaymentMode;
use YandexCheckout\Model\Receipt\PaymentSubject;
use YandexCheckout\Request\Payments\CreatePaymentResponse;
use YandexMoney\Model\KassaPaymentMethod;
use YandexMoney\Model\KassaSecondReceiptModel;

defined('_JEXEC') or die('Restricted access');

define('JSH_DIR', realpath(dirname(__FILE__).'/../..'));
define('DIR_DOWNLOAD', JSH_DIR.'/log');

require_once dirname(__FILE__).'/lib/autoload.php';

define('_JSHOP_YM_VERSION', '1.4.0');

class pm_yandex_money extends PaymentRoot
{
    const MODE_OFF = 0;
    const MODE_KASSA = 1;
    const MODE_MONEY = 2;
    const MODE_PAYMENTS = 3;

    private $mode = -1;
    private $joomlaVersion;
    private $debugLog = true;

    private $element = 'pm_yandex_money';
    private $repository = 'yandex-money/yandex-money-cms-v2-joomshopping';
    private $downloadDirectory = 'pm_yandex_money';
    private $backupDirectory = 'pm_yandex_money/backups';
    private $versionDirectory = 'pm_yandex_money/download';

    private static $disabledPaymentMethods = array(
        PaymentMethodType::B2B_SBERBANK,
        PaymentMethodType::WECHAT,
    );

    private static $customPaymentMethods = array(
        'widget',
    );

    public $existentcheckform = true;
    public $ym_pay_mode, $ym_test_mode, $ym_password, $ym_shopid, $ym_scid;

    /**
     * @var YandexMoney\Model\OrderModel
     */
    private $orderModel;

    /**
     * @var KassaPaymentMethod
     */
    private $kassa;

    public function __construct()
    {
        $this->joomlaVersion = (version_compare(JVERSION, '3.0', '<') == 1) ? 2 : 3;
    }

    function showPaymentForm($params, $pmConfigs)
    {
        $this->loadLanguageFile();
        $this->mode = $this->getMode($pmConfigs);

        if ($this->mode === self::MODE_KASSA) {
            include(dirname(__FILE__)."/payment_form_kassa.php");
        } else {
            include(dirname(__FILE__)."/paymentform.php");
        }
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
     * Проверяет параметры указанные пользователем на странице выбора способа оплаты
     *
     * @param array $params Массив параметров, указанных в params[pm_yandex_money] на странице выбора способа оплаты
     * @param array $pmConfigs Настройки модуля оплаты
     *
     * @return bool True если все параметры вылидны, false если нет
     */
    public function checkPaymentInfo($params, $pmConfigs)
    {
        $this->mode = $this->getMode($pmConfigs);


        if ($this->mode === self::MODE_OFF) {
            $this->log('error', 'Please activate payment method');
            $this->setErrorMessage(_JSHOP_ERROR_PAYMENT);

            return false;
        } elseif ($this->mode === self::MODE_PAYMENTS) {
            // если платёжка, то проверяем ФИО указанные пользователем
            if (empty($params) || empty($params['ya_payments_fio'])) {
                return false;
            }
            $name = trim($params['ya_payments_fio']);
            if (empty($name)) {
                return false;
            }
        } elseif ($this->mode === self::MODE_KASSA) {
            // если оплата через кассу, то должен быть указан способ оплаты
            $paymentType = isset($params['payment_type']) ? $params['payment_type'] : '';
            if (empty($paymentType) && $pmConfigs['paymode'] == '1') {
                return true;
            } else {
                if (in_array($paymentType, self::getPaymentMethods())) {
                    if ($paymentType === PaymentMethodType::QIWI) {
                        if (empty($params['qiwiPhone'])) {
                            return false;
                        }
                        $phone = preg_replace('/[^\d]+/', '', $params['qiwiPhone']);
                        if (empty($phone) || strlen($phone) < 4 || strlen($phone) > 16) {
                            $this->setErrorMessage('Указанное значение не является телефонным номером');

                            return false;
                        }
                        $params['qiwiPhone'] = $phone;
                    } elseif ($paymentType === PaymentMethodType::ALFABANK) {
                        if (empty($params['alfaLogin'])) {
                            $this->setErrorMessage('Укажите логин в Альфа-клике');

                            return false;
                        }
                        $login = trim($params['alfaLogin']);
                        if (empty($login)) {
                            return false;
                        }
                    }

                    return true;
                }

                return false;
            }
        }

        return true;
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
        $this->loadLanguageFile();
        $this->installExtension();

        if (isset($_GET['subaction'])) {

            if ($_GET['subaction'] === 'get_log_messages') {
                $this->viewModuleLogs();
            } elseif ($_GET['subaction'] === 'clear_log_messages') {
                $this->clearModuleLogs();
            } elseif ($_GET['subaction'] === 'restore_backup') {
                ob_start();
                $result = $this->restoreBackup();
                $data   = ob_get_clean();
                if (!empty($data)) {
                    $result['output'] = $data;
                }
                echo json_encode($result);
                exit();
            } elseif ($_GET['subaction'] === 'remove_backup') {
                ob_start();
                $result = $this->removeBackup();
                $data   = ob_get_clean();
                if (!empty($data)) {
                    $result['output'] = $data;
                }
                echo json_encode($result);
                exit();
            } elseif ($_GET['subaction'] === 'update') {
                ob_start();
                $result = $this->updateVersion();
                $data   = ob_get_clean();
                if (!empty($data)) {
                    $result['output'] = $data;
                }
                echo json_encode($result);
                exit();
            }
        }
        $array_params = array(
            'kassa_send_check',
            'testmode',
            'paymode',
            'moneymode',
            'kassamode',
            'paymentsmode',
            'method_ym',
            'method_cards',
            'method_ym2',
            'method_cards2',
            'method_cash',
            'method_phone',
            'method_wm',
            'method_ab',
            'method_sb',
            'method_ma',
            'method_pb',
            'method_qw',
            'method_qp',
            'method_widget',
            'password',
            'shoppassword',
            'shopid',
            'scid',
            'account',
            'transaction_end_status',
            'ym_pay_id',
            'ym_pay_desc',
            'ya_payments_fio',
            'page_mpos',
            'ya_kassa_description_template',
            'ya_kassa_send_check',
            'ya_kassa_default_tax',
            'method_mp',
            'debug_log',
            'ya_kassa_default_payment_mode',
            'ya_kassa_default_payment_subject',
            'ya_kassa_default_delivery_payment_mode',
            'ya_kassa_default_delivery_payment_subject',
        );

        $paymentModeEnum = array(
            PaymentMode::FULL_PREPAYMENT    => 'Полная предоплата ('.PaymentMode::FULL_PREPAYMENT.')',
            PaymentMode::PARTIAL_PREPAYMENT => 'Частичная предоплата ('.PaymentMode::PARTIAL_PREPAYMENT.')',
            PaymentMode::ADVANCE            => 'Аванс ('.PaymentMode::ADVANCE.')',
            PaymentMode::FULL_PAYMENT       => 'Полный расчет ('.PaymentMode::FULL_PAYMENT.')',
            PaymentMode::PARTIAL_PAYMENT    => 'Частичный расчет и кредит ('.PaymentMode::PARTIAL_PAYMENT.')',
            PaymentMode::CREDIT             => 'Кредит ('.PaymentMode::CREDIT.')',
            PaymentMode::CREDIT_PAYMENT     => 'Выплата по кредиту ('.PaymentMode::CREDIT_PAYMENT.')',
        );

        $paymentSubjectEnum = array(
            PaymentSubject::COMMODITY             => 'Товар ('.PaymentSubject::COMMODITY.')',
            PaymentSubject::EXCISE                => 'Подакцизный товар ('.PaymentSubject::EXCISE.')',
            PaymentSubject::JOB                   => 'Работа ('.PaymentSubject::JOB.')',
            PaymentSubject::SERVICE               => 'Услуга ('.PaymentSubject::SERVICE.')',
            PaymentSubject::GAMBLING_BET          => 'Ставка в азартной игре ('.PaymentSubject::GAMBLING_BET.')',
            PaymentSubject::GAMBLING_PRIZE        => 'Выигрыш в азартной игре ('.PaymentSubject::GAMBLING_PRIZE.')',
            PaymentSubject::LOTTERY               => 'Лотерейный билет ('.PaymentSubject::LOTTERY.')',
            PaymentSubject::LOTTERY_PRIZE         => 'Выигрыш в лотерею ('.PaymentSubject::LOTTERY_PRIZE.')',
            PaymentSubject::INTELLECTUAL_ACTIVITY => 'Результаты интеллектуальной деятельности ('.PaymentSubject::INTELLECTUAL_ACTIVITY.')',
            PaymentSubject::PAYMENT               => 'Платеж ('.PaymentSubject::PAYMENT.')',
            PaymentSubject::AGENT_COMMISSION      => 'Агентское вознаграждение ('.PaymentSubject::AGENT_COMMISSION.')',
            PaymentSubject::COMPOSITE             => 'Несколько вариантов ('.PaymentSubject::COMPOSITE.')',
            PaymentSubject::ANOTHER               => 'Другое ('.PaymentSubject::ANOTHER.')',
        );

        if (!is_array($params)) {
            $params = array();
        }

        $params['paymentModeEnum']    = $paymentModeEnum;
        $params['paymentSubjectEnum'] = $paymentSubjectEnum;
        $params['paymentMethods']     = self::getPaymentMethods();

        $taxes = JSFactory::getAllTaxes();

        foreach ($taxes as $k => $tax) {
            $array_params[] = 'ya_kassa_tax_'.$k;
        }

        foreach ($array_params as $key) {
            if (!isset($params[$key])) {
                $params[$key] = '';
            }
        }
        if (!isset($params['use_ssl'])) {
            $params['use_ssl'] = 0;
        }

        $orders = JModelLegacy::getInstance('orders', 'JshoppingModel'); //admin model
        if ($this->joomlaVersion === 2) {
            $filename = '2x';
        } else {
            $filename   = '';
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->register('onBeforeEditPayments', array($this, 'onBeforeEditPayments'));
        }

        $zip_enabled  = function_exists('zip_open');
        $curl_enabled = function_exists('curl_init');
        if ($zip_enabled && $curl_enabled) {
            $force       = false;
            $versionInfo = $this->checkModuleVersion($force);
            if (version_compare($versionInfo['version'], _JSHOP_YM_VERSION) > 0) {
                $new_version_available = true;
                $changelog             = $this->getChangeLog(_JSHOP_YM_VERSION, $versionInfo['version']);
                $newVersion            = $versionInfo['version'];
            } else {
                $new_version_available = false;
                $changelog             = '';
                $newVersion            = _JSHOP_YM_VERSION;
            }
            $newVersionInfo = $versionInfo;

            $backups = $this->getBackupList();
        }

        if ($params['kassamode']) {
            if (!empty($params['shop_id']) && !empty($params['shop_password'])) {
                $kassa = $this->getKassaPaymentMethod($params);
                if (!$kassa->checkConnection()) {
                    $errorCredentials = _JSHOP_YM_KASSA_CREDENTIALS_ERROR;
                }
                if (strncmp('test_', $params['shop_password'], 5) === 0) {
                    $testWarning = _JSHOP_YM_KASSA_TEST_WARNING;
                }
            }
        }

        include(dirname(__FILE__)."/adminparamsform".$filename.".php");
    }

    /**
     *
     */
    public function viewModuleLogs()
    {
        $fileName = $this->getLogFileName();
        $fd       = @fopen($fileName, 'r');
        $logs     = array();
        if ($fd) {
            flock($fd, LOCK_SH);
            $size = filesize($fileName);
            if ($size > 0) {
                $logs = array_map('trim', explode("\n", fread($fd, $size)));
            }
            flock($fd, LOCK_UN);
            fclose($fd);
            $logs = array_reverse($logs);
        }
        echo json_encode(array_slice($logs, 0, 100));
        exit();
    }

    /**
     *
     */
    public function clearModuleLogs()
    {
        $fileName = $this->getLogFileName();
        $fd       = @fopen($fileName, 'w');
        $success  = false;
        if ($fd) {
            flock($fd, LOCK_SH);
            flock($fd, LOCK_UN);
            fclose($fd);
            $success = true;
        }
        echo json_encode(array('success' => $success));
        exit();
    }

    private function restoreBackup()
    {
        if (!empty($_POST['file_name'])) {
            $fileName = DIR_DOWNLOAD.'/'.$this->backupDirectory.'/'.$_POST['file_name'];
            if (!file_exists($fileName)) {
                $this->log('error', 'File "'.$fileName.'" not exists');

                return array(
                    'message' => 'Файл резервной копии '.$fileName.' не найден',
                    'success' => false,
                );
            }
            try {
                $sourceDirectory = dirname(dirname(realpath(JSH_DIR)));
                $archive         = new \YandexMoney\Updater\Archive\RestoreZip($fileName);
                $archive->restore('file_map.map', $sourceDirectory);
            } catch (Exception $e) {
                $this->log('error', $e->getMessage());
                if ($e->getPrevious() !== null) {
                    $this->log('error', $e->getPrevious()->getMessage());
                }

                return array(
                    'message' => _JSHOP_YM_UPDATER_ERROR_RESTORE.$e->getMessage(),
                    'success' => false,
                );
            }

            return array(
                'message' => _JSHOP_YM_UPDATER_SUCCESS_MESSAGE.$_POST['file_name'],
                'success' => true,
            );
        }

        return array(
            'message' => _JSHOP_YM_UPDATER_ERROR_REMOVE,
            'success' => false,
        );
    }

    public function removeBackup()
    {
        if (!empty($_POST['file_name'])) {
            $fileName = DIR_DOWNLOAD.'/'.$this->backupDirectory.'/'.str_replace(array('/', '\\'), array('', ''),
                    $_POST['file_name']);
            if (!file_exists($fileName)) {
                $this->log('error', 'File "'.$fileName.'" not exists');

                return array(
                    'message' => sprintf(_JSHOP_YM_ERROR_BACKUP_NOT_FOUND, $fileName),
                    'success' => false,
                );
            }

            if (!unlink($fileName) || file_exists($fileName)) {
                $this->log('error', 'Failed to unlink file "'.$fileName.'"');

                return array(
                    'message' => _JSHOP_YM_ERROR_REMOVE_BACKUP.$fileName,
                    'success' => false,
                );
            }

            return array(
                'message' => sprintf(_JSHOP_YM_SUCCESS_REMOVE_BECKUP, $fileName),
                'success' => true,
            );
        }

        return array(
            'message' => _JSHOP_YM_UPDATER_ERROR_REMOVE,
            'success' => false,
        );
    }

    public function updateVersion()
    {
        $versionInfo = $this->checkModuleVersion(false);
        $fileName    = $this->downloadLastVersion($versionInfo['tag']);
        if (!empty($fileName)) {
            if ($this->createBackup(_JSHOP_YM_VERSION)) {
                if ($this->unpackLastVersion($fileName)) {
                    $result = array(
                        'message' => sprintf(_JSHOP_YM_SUCCESS_UPDATE_VERSION, $_POST['version'], $fileName),
                        'success' => true,
                    );
                } else {
                    $result = array(
                        'message' => sprintf(_JSHOP_YM_ERROR_UNPACK_NEW_VERSION, $fileName),
                        'success' => false,
                    );
                }
            } else {
                $result = array(
                    'message' => _JSHOP_YM_ERROR_CREATE_BACKUP,
                    'success' => false,
                );
            }
        } else {
            $result = array(
                'message' => _JSHOP_YM_ERROR_DOWNLOAD_NEW_VERSION,
                'success' => false,
            );
        }

        return $result;
    }

    public function onBeforeEditPayments($view)
    {
        $view->tmp_html_start = '';
        $view->tmp_html_end   = '';
    }

    /**
     *
     */
    private function loadLanguageFile()
    {
        $lang    = JFactory::getLanguage();
        $langTag = $lang->getTag();
        if (file_exists(JPATH_ROOT.'/components/com_jshopping/payments/pm_yandex_money/lang/'.$langTag.'.php')) {
            require_once(JPATH_ROOT.'/components/com_jshopping/payments/pm_yandex_money/lang/'.$langTag.'.php');
        } else {
            require_once(JPATH_ROOT.'/components/com_jshopping/payments/pm_yandex_money/lang/ru-RU.php');
        }
    }

    /**
     * @param array $callbackParams
     *
     * @return bool
     */
    public function checkSign($callbackParams)
    {
        if ($this->mode == self::MODE_MONEY) {
            $string = $callbackParams['notification_type'].'&'.$callbackParams['operation_id'].'&'
                .$callbackParams['amount'].'&'.$callbackParams['currency'].'&'.$callbackParams['datetime'].'&'
                .$callbackParams['sender'].'&'.$callbackParams['codepro'].'&'.$this->ym_password.'&'
                .$callbackParams['label'];
            $check  = (sha1($string) == $callbackParams['sha1_hash']);
            if (!$check) {
                header('HTTP/1.0 401 Unauthorized');

                return false;
            }
        }

        return true;
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
        $this->mode = $this->getMode($pmConfigs);
        if ($this->mode === self::MODE_MONEY) {
            $this->ym_pay_mode = ($pmConfigs['paymode'] == '1');
            $this->ym_shopid   = $pmConfigs['shopid'];
            $this->ym_scid     = $pmConfigs['scid'];

            $order->order_total = floatval($order->order_total);

            $callbackParams = JRequest::get('post');
            $this->loadLanguageFile();
            $check = $this->checkSign($callbackParams);
        } elseif ($this->mode === self::MODE_KASSA) {

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

                if ($notification->getEvent() === NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE
                    && $payment->getStatus() === PaymentStatus::WAITING_FOR_CAPTURE
                ) {
                    if ($kassa->isEnableHoldMode()
                        && $payment->getPaymentMethod()->getType() === PaymentMethodType::BANK_CARD
                    ) {
                        $this->log('info', 'Hold payment '.$payment->getId());
                        try {
                            /** @var jshopCheckout $checkout */
                            $checkout             = JSFactory::getModel('checkout', 'jshop');
                            $onHoldStatus         = $pmConfigs['ya_kassa_hold_mode_on_hold_status'];
                            $order->order_created = 1;
                            $order->order_status  = $onHoldStatus;
                            $order->store();
                            $checkout->changeStatusOrder($order->order_id, $onHoldStatus, 0);
                            $this->saveOrderHistory($order, sprintf(_JSHOP_YM_HOLD_MODE_COMMENT_ON_HOLD,
                                $payment->getExpiresAt()->format('d.m.Y H:i')));
                        } catch (Exception $e) {
                            $this->log('debug', $e->getMessage());
                            header('HTTP/1.1 500 Internal Server Error');
                        }
                    } else {
                        $payment = $kassa->capturePayment($notification->getObject());
                        if (!$payment || $payment->getStatus() !== PaymentStatus::SUCCEEDED) {
                            $this->log('debug', 'Capture payment error');
                            header('HTTP/1.1 400 Bad Request');
                        }
                    }
                    exit();
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

                        $this->sendSecondReceipt($order->order_id, $pmConfigs, $endStatus);

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

        } else {
            $check = true;
        }
        //
        if ($check) {
            if ($this->mode == self::MODE_KASSA) {
                return array(1, '');
            } else {
                return array(1, '');
            }
        } elseif ($this->mode == self::MODE_KASSA) {
            return array(1, '');
        } else {
            return array(0, 'hash error');
        }
    }

    public function getFormUrl()
    {
        if ($this->mode == self::MODE_MONEY) {
            return $this->individualGetFormUrl();
        } else {
            return 'https://money.yandex.ru/fastpay/confirm';
        }
    }

    public function individualGetFormUrl()
    {
        if ($this->ym_test_mode) {
            return 'https://demomoney.yandex.ru/quickpay/confirm.xml';
        } else {
            return 'https://money.yandex.ru/quickpay/confirm.xml';
        }
    }

    /**
     * @param $pmConfigs
     * @param jshopOrder $order
     */
    function showEndForm($pmConfigs, $order)
    {
        $this->ym_test_mode = isset($pmConfigs['testmode']) ? $pmConfigs['testmode'] : false;
        $this->mode = $this->getMode($pmConfigs);

        if ($this->mode === self::MODE_KASSA) {
            if ($this->processKassaPayment($pmConfigs, $order)) {
                return;
            }
            // если произошла ошибка, редиректим на шаг выбора метода оплаты
            $redirectUrl = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step5');
            $app         = JFactory::getApplication();
            $app->redirect($redirectUrl);
        }
        $this->ym_pay_mode = (isset($pmConfigs['paymode']) && $pmConfigs['paymode'] == '1');

        $uri         = JURI::getInstance();
        $liveUrlHost = $uri->toString(array("scheme", 'host', 'port'));

        $ym_params = unserialize($order->payment_params_data);

        $item_name = $liveUrlHost." ".sprintf(_JSHOP_PAYMENT_NUMBER, $order->order_number);
        $this->loadLanguageFile();

        $return = $liveUrlHost.SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_yandex_money&order_id=".$order->id);

        $order->order_total = $this->fixOrderTotal($order);
        if ($ym_params['ym-payment-type'] == 'MP') {
            $app = JFactory::getApplication();
            $app->redirect(JRoute::_(JURI::root().'index.php?option=com_content&view=article&id='.$pmConfigs['page_mpos']));
        }
        ?>
        <html>
        <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
            <script src="/media/jui/js/jquery.min.js"></script>
        </head>
        <body>
        <?php if ($this->mode == self::MODE_MONEY) { ?>
            <form method="POST" action="<?php echo $this->getFormUrl(); ?>" id="paymentform" name="paymentform">
                <input type="hidden" name="receiver" value="<?php echo $pmConfigs['account']; ?>">
                <input type="hidden" name="formcomment" value="<?php echo $item_name; ?>">
                <input type="hidden" name="short-dest" value="<?php echo $item_name; ?>">
                <input type="hidden" name="writable-targets" value="false">
                <input type="hidden" name="comment-needed" value="true">
                <input type="hidden" name="label" value="<?php echo $order->order_id; ?>">
                <input type="hidden" name="quickpay-form" value="shop">
                <input type="hidden" name="paymentType" value="<?php echo $ym_params['ym-payment-type'] ?>"/>
                <input type="hidden" name="targets" value="<?php echo $item_name; ?>">
                <input type="hidden" name="sum" value="<?php echo $order->order_total; ?>" data-type="number">
                <input type="hidden" name="comment" value="<?php echo $order->order_add_info; ?>">
                <input type="hidden" name="need-fio" value="true">
                <input type="hidden" name="need-email" value="true">
                <input type="hidden" name="need-phone" value="false">
                <input type="hidden" name="need-address" value="false">
                <input type="hidden" name="successURL" value="<?php echo $return; ?>">
                <?php echo _JSHOP_REDIRECT_TO_PAYMENT_PAGE; ?>
            </form>
        <?php } elseif ($this->mode == self::MODE_PAYMENTS) {
            $this->finishOrder($order, $pmConfigs['ym_pay_status']);
            $narrative = $this->parseTemplate($pmConfigs['ym_pay_desc'], $order);
            ?>
            <form method="POST" action="<?php echo $this->getFormUrl(); ?>" id="paymentform" name="paymentform">
                <input type="hidden" name="formId" value="<?php echo htmlspecialchars($pmConfigs['ym_pay_id']); ?>"/>
                <input type="hidden" name="narrative" value="<?php echo htmlspecialchars($narrative); ?>"/>
                <input type="hidden" name="fio" value="<?php echo htmlspecialchars($ym_params['ya_payments_fio']); ?>"/>
                <input type="hidden" name="sum" value="<?php echo $order->order_total; ?>"/>
                <input type="hidden" name="quickPayVersion" value="2"/>
                <input type="hidden" name="cms_name" value="joomla"/>
                <?php echo _JSHOP_REDIRECT_TO_PAYMENT_PAGE; ?>
            </form>
        <?php } ?>

        </body>
        <script type="text/javascript">document.getElementById('paymentform').submit();</script>
        </html>
        <?php
        die();
    }

    public function processKassaPayment($pmConfigs, $order)
    {
        $app         = JFactory::getApplication();
        $uri         = JURI::getInstance();
        $redirectUrl = $uri->toString(array('scheme', 'host', 'port'))
            .SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_yandex_money&no_lang=1&order_id=".$order->order_id);
        $redirectUrl = htmlspecialchars_decode($redirectUrl);

        /** @var jshopCart $cart */
        $cart = JSFactory::getModel('cart', 'jshop');
        if (method_exists($cart, 'init')) {
            $cart->init('cart', 1);
        } else {
            $cart->load('cart');
        }

        try {
            $payment = $this->getKassaPaymentMethod($pmConfigs)->createPayment($order, $cart, $redirectUrl);
        } catch (\Exception $e) {
            $redirect = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
            $app->enqueueMessage(_JSHOP_YM_ERROR_MESSAGE_CREATE_PAYMENT, 'error');
            $app->redirect($redirectUrl);
        }

        $redirect = $redirectUrl;
        if ($payment !== null) {
            $confirmation = $payment->getConfirmation();
            $this->getOrderModel()->savePayment($order->order_id, $payment);

            if ($confirmation instanceof ConfirmationRedirect) {
                $redirect = $confirmation->getConfirmationUrl();
            }  elseif ($confirmation instanceof ConfirmationEmbedded) {
                $this->renderWidget($payment, $redirect);
                return true;
            }
        } else {
            $redirect = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
            $app->enqueueMessage(_JSHOP_YM_ERROR_MESSAGE_CREATE_PAYMENT, 'error');
        }

        $app->redirect($redirect);
    }

    /**
     * Инициализирует параметры для обработки процессором заказа из URL запроса при возврате на сайт
     *
     * @param array $pmConfigs Настройки модуля оплаты
     *
     * @return array Массив параметров, который будет использоваться в процессоре заказа
     */
    public function getUrlParams($pmConfigs)
    {
        $this->mode = $this->getMode($pmConfigs);
        $params     = array();
        if ($this->mode == self::MODE_KASSA) {
            $this->log('debug', 'Get URL parameters for payment');
            if (isset($_GET['order_id'])) {
                $this->log('debug', 'Order id exists in return url: '.$_GET['order_id']);
                $params['order_id'] = (int)$_GET['order_id'];
                $paymentId          = $this->getOrderModel()->getPaymentIdByOrderId($params['order_id']);
                if (empty($paymentId)) {
                    $this->log('debug', 'Redirect user to payment method page: payment id not exists');
                    $redirectUrl = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
                    $app         = JFactory::getApplication();
                    $app->redirect($redirectUrl);
                }
                $payment = $this->getKassaPaymentMethod($pmConfigs)->fetchPayment($paymentId);
                if ($payment === null) {
                    $this->log('debug', 'Redirect user to payment method page: payment not exists');
                    $redirectUrl = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
                    $app         = JFactory::getApplication();
                    $app->redirect($redirectUrl);
                }
                if (!$payment->getPaid()) {
                    $this->log('debug', 'Redirect user to payment method page: payment not paid');
                    $redirectUrl = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
                    $app         = JFactory::getApplication();
                    $app->redirect($redirectUrl);
                }
                $params['hash']              = "";
                $params['checkHash']         = 0;
                $params['checkReturnParams'] = 1;
                $this->log('debug', 'Return url params is: '.json_encode($params));
            } elseif (isset($_GET['act']) && $_GET['act'] === 'notify') {
                $this->log('debug', 'Notification callback check URL parameters');
                $source = file_get_contents('php://input');
                $this->log('debug', 'Notification body source: '.$source);
                if (empty($source)) {
                    $this->log('debug', 'Notification error: body is empty');
                    header('HTTP/1.1 400 Body is empty');
                    die();
                }
                $json = json_decode($source, true);
                if (empty($json)) {
                    $this->log('debug', 'Notification error: invalid body');
                    header('HTTP/1.1 400 Invalid body');
                    die();
                }
                try {
                    $notification = ($json['event'] === YandexCheckout\Model\NotificationEventType::PAYMENT_SUCCEEDED)
                        ? new NotificationSucceeded($json)
                        : new NotificationWaitingForCapture($json);
                    $meta         = $notification->getObject()->getMetadata();
                    if (empty($meta['order_id'])) {
                        $this->log('debug', 'Notification error: metadata order_id not exists');
                        header('HTTP/1.1 400 Invalid body');
                        die();
                    }
                } catch (Exception $e) {
                    $this->log('debug', 'Notification error: '.$e->getMessage());
                    header('HTTP/1.1 400 Invalid body');
                    die();
                }
                $params['order_id']          = $meta['order_id'];
                $params['hash']              = "";
                $params['checkHash']         = 0;
                $params['checkReturnParams'] = 1;
                $this->log('debug', 'Notify url params is: '.json_encode($params));
            } else {
                $this->log('debug', 'Order id not exists in return url: '.json_encode($_GET));
            }
        } else {
            $params['order_id']  = (int)$_POST['label'];
            $params['hash']      = "";
            $params['checkHash'] = 0;
        }

        return $params;
    }

    /**
     * @param CreatePaymentResponse $payment
     * @param string $returnUrl
     */
    private function renderWidget($payment, $returnUrl)
    {
        $token = $payment->getConfirmation()->getConfirmationToken();
        require_once __DIR__ . "/widget.php";
    }

    private function fixOrderTotal($order)
    {
        $total = $order->order_total;
        if ($order->currency_code_iso == 'HUF') {
            $total = round($total);
        } else {
            $total = number_format($total, 2, '.', '');
        }

        return $total;
    }

    private function getMode($paymentConfig)
    {
        if ($this->mode == -1) {
            $this->mode = self::MODE_OFF;
            if (isset($paymentConfig['kassamode']) && $paymentConfig['kassamode'] == '1') {
                $this->mode        = self::MODE_KASSA;
                $this->ym_password = $paymentConfig['shoppassword'];
            } elseif (isset($paymentConfig['moneymode']) && $paymentConfig['moneymode'] == '1') {
                $this->mode        = self::MODE_MONEY;
                $this->ym_password = $paymentConfig['password'];
            } elseif (isset($paymentConfig['paymentsmode']) && $paymentConfig['paymentsmode'] == '1') {
                $this->mode = self::MODE_PAYMENTS;
            }
            $this->debugLog = isset($paymentConfig['debug_log']) && $paymentConfig['debug_log'] == '1';
        }

        return $this->mode;
    }

    /**
     * @param string $tpl
     * @param jshopOrder $order
     *
     * @return string
     */
    private function parseTemplate($tpl, $order)
    {
        $replace = array();
        foreach ($order as $property => $value) {
            $replace['%'.$property.'%'] = $value;
        }

        return strtr($tpl, $replace);
    }

    /**
     * @param jshopOrder $order
     * @param int $endStatus
     *
     * @return int
     */
    private function finishOrder($order, $endStatus)
    {
        $act            = 'finish';
        $payment_method = 'pm_yandex_money';
        $no_lang        = '1';

        if ($this->joomlaVersion === 2) {
            // joomla 2.x order finish
            $jshopConfig = JSFactory::getConfig();

            /** @var jshopCheckout $checkout */
            $checkout = JSFactory::getModel('checkout', 'jshop');

            $order->order_created = 1;
            $order->order_status  = $endStatus;
            $order->store();
            if ($jshopConfig->send_order_email) {
                $checkout->sendOrderEmail($order->order_id);
            }
            if ($jshopConfig->order_stock_removed_only_paid_status) {
                $product_stock_removed = (in_array($endStatus, $jshopConfig->payment_status_enable_download_sale_file));
            } else {
                $product_stock_removed = 1;
            }
            if ($product_stock_removed) {
                $order->changeProductQTYinStock("-");
            }
            $checkout->changeStatusOrder($order->order_id, $endStatus, 0);

            $checkout->deleteSession();
        } else {
            // joomla 3.x order finish
            /** @var jshopCheckoutBuy $checkout */
            $checkout = JSFactory::getModel('checkoutBuy', 'jshop');
            $checkout->saveToLogPaymentData();
            $checkout->setSendEndForm(0);

            $checkout->setAct($act);
            $checkout->setPaymentMethodClass($payment_method);
            $checkout->setNoLang($no_lang);
            $checkout->loadUrlParams();
            $checkout->setOrderId($order->order_id);

            $codebuy = $checkout->buy();
            if ($codebuy == 0) {
                JError::raiseWarning('', $checkout->getError());

                return 0;
            }

            /** @var jshopCheckoutFinish $checkout */
            $checkout = JSFactory::getModel('checkoutFinish', 'jshop');
            $order_id = $checkout->getEndOrderId();
            $text     = $checkout->getFinishStaticText();
            if ($order_id) {
                $checkout->paymentComplete($order_id, $text);
            }
            $checkout->clearAllDataCheckout();
        }
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

    public function getKassaPaymentMethod($pmConfigs)
    {
        $this->loadLanguageFile();
        if ($this->kassa === null) {
            $this->kassa = new KassaPaymentMethod($this, $pmConfigs);
        }

        return $this->kassa;
    }

    private function getLogFileName()
    {
        return realpath(JSH_DIR).'/log/pm_yandex_money.log';
    }

    public function checkModuleVersion($useCache = true)
    {
        $this->preventDirectoryCreation();

        $file = DIR_DOWNLOAD.'/'.$this->downloadDirectory.'/version_log.txt';

        if ($useCache) {
            if (file_exists($file)) {
                $content = preg_replace('/\s+/', '', file_get_contents($file));
                if (!empty($content)) {
                    $parts = explode(':', $content);
                    if (count($parts) === 2) {
                        if (time() - $parts[1] < 3600 * 8) {
                            return array(
                                'tag'     => $parts[0],
                                'version' => preg_replace('/[^\d\.]+/', '', $parts[0]),
                                'time'    => $parts[1],
                                'date'    => $this->dateDiffToString($parts[1]),
                            );
                        }
                    }
                }
            }
        }

        $connector = new \YandexMoney\Updater\GitHubConnector();
        $version   = $connector->getLatestRelease($this->repository);
        if (empty($version)) {
            return array();
        }

        $cache = $version.':'.time();
        file_put_contents($file, $cache);

        return array(
            'tag'     => $version,
            'version' => preg_replace('/[^\d\.]+/', '', $version),
            'time'    => time(),
            'date'    => $this->dateDiffToString(time()),
        );
    }

    public function getChangeLog($currentVersion, $newVersion)
    {
        $this->preventDirectoryCreation();

        $connector = new \YandexMoney\Updater\GitHubConnector();

        $dir          = DIR_DOWNLOAD.'/'.$this->downloadDirectory;
        $newChangeLog = $dir.'/CHANGELOG-'.$newVersion.'.md';
        if (!file_exists($newChangeLog)) {
            $fileName = $connector->downloadLatestChangeLog($this->repository, $dir);
            if (!empty($fileName)) {
                rename($dir.'/'.$fileName, $newChangeLog);
            }
        }

        $oldChangeLog = $dir.'/CHANGELOG-'.$currentVersion.'.md';
        if (!file_exists($oldChangeLog)) {
            $fileName = $connector->downloadLatestChangeLog($this->repository, $dir);
            if (!empty($fileName)) {
                rename($dir.'/'.$fileName, $oldChangeLog);
            }
        }

        $result = '';
        if (file_exists($newChangeLog)) {
            $result = $connector->diffChangeLog($oldChangeLog, $newChangeLog);
        }

        return $result;
    }

    private function dateDiffToString($timestamp)
    {
        return date('d.m.Y H:i', $timestamp);
    }

    public function getBackupList()
    {
        $result = array();

        $this->preventDirectoryCreation();
        $dir = DIR_DOWNLOAD.'/'.$this->backupDirectory;

        $handle = opendir($dir);
        while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $ext = pathinfo($entry, PATHINFO_EXTENSION);
            if ($ext === 'zip') {
                $backup            = array(
                    'name' => pathinfo($entry, PATHINFO_FILENAME).'.zip',
                    'size' => $this->formatSize(filesize($dir.'/'.$entry)),
                );
                $parts             = explode('-', $backup['name'], 3);
                $backup['version'] = $parts[0];
                $backup['time']    = $parts[1];
                $backup['date']    = date('d.m.Y H:i:s', $parts[1]);
                $backup['hash']    = $parts[2];
                $result[]          = $backup;
            }
        }

        return $result;
    }

    private function preventDirectoryCreation()
    {
        if (!file_exists(DIR_DOWNLOAD)) {
            mkdir(DIR_DOWNLOAD);
        }
        $dir = DIR_DOWNLOAD.'/'.$this->downloadDirectory;
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        $dir = DIR_DOWNLOAD.'/'.$this->backupDirectory;
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        $dir = DIR_DOWNLOAD.'/'.$this->versionDirectory;
        if (!file_exists($dir)) {
            mkdir($dir);
        }
    }

    private function formatSize($size)
    {
        static $sizes = array(
            'B',
            'kB',
            'MB',
            'GB',
            'TB',
        );

        $i = 0;
        while ($size > 1024) {
            $size /= 1024.0;
            $i++;
        }

        return number_format($size, 2, '.', ',').'&nbsp;'.$sizes[$i];
    }

    private function downloadLastVersion($tag, $useCache = true)
    {
        $this->preventDirectoryCreation();

        $dir = DIR_DOWNLOAD.'/'.$this->versionDirectory;
        if (!file_exists($dir)) {
            if (!mkdir($dir)) {
                $this->log('error', _JSHOP_YM_FAILED_CREATE_DIRECTORY.$dir);

                return false;
            }
        } elseif ($useCache) {
            $fileName = $dir.'/'.$tag.'.zip';
            if (file_exists($fileName)) {
                return $fileName;
            }
        }

        $connector = new \YandexMoney\Updater\GitHubConnector();
        $fileName  = $connector->downloadRelease($this->repository, $tag, $dir);
        if (empty($fileName)) {
            $this->log('error', _JSHOP_YM_FAILED_DOWNLOAD_UPDATE);

            return false;
        }

        return $fileName;
    }

    public function createBackup($version)
    {
        $this->preventDirectoryCreation();

        $sourceDirectory = dirname(dirname(JSH_DIR));
        $reader          = new \YandexMoney\Updater\ProjectStructure\ProjectStructureReader();
        $root            = $reader->readFile(JSH_DIR.'/payments/pm_yandex_money/lib/joomshopping.map',
            $sourceDirectory);

        $rootDir  = $version.'-'.time();
        $fileName = $rootDir.'-'.uniqid('', true).'.zip';
        $dir      = DIR_DOWNLOAD.'/'.$this->backupDirectory;
        try {
            $fileName = $dir.'/'.$fileName;
            $archive  = new \YandexMoney\Updater\Archive\BackupZip($fileName, $rootDir);
            $archive->backup($root);
        } catch (Exception $e) {
            $this->log('error', 'Failed to create backup: '.$e->getMessage());

            return false;
        }

        return true;
    }

    public function unpackLastVersion($fileName)
    {
        if (!file_exists($fileName)) {
            $this->log('error', 'File "'.$fileName.'" not exists');

            return false;
        }

        try {
            $sourceDirectory = dirname(dirname(JSH_DIR));
            $archive         = new \YandexMoney\Updater\Archive\RestoreZip($fileName, $this);
            $archive->restore('joomshopping.map', $sourceDirectory);
        } catch (Exception $e) {
            $this->log('error', $e->getMessage());
            if ($e->getPrevious() !== null) {
                $this->log('error', $e->getPrevious()->getMessage());
            }

            return false;
        }

        return true;
    }

    private function installExtension()
    {
        $addon    = JTable::getInstance('addon', 'jshop');
        $manifest = '{"creationDate":"20.07.2018","author":"YandexMoney","authorEmail":"cms@yamoney.ru","authorUrl":"https://kassa.yandex.ru","version":"'._JSHOP_YM_VERSION.'"}';
        $addon->installJoomlaExtension(
            array(
                'name'           => 'YandexMoney',
                'type'           => 'plugin',
                'element'        => $this->element,
                'folder'         => 'jshoppingadmin',
                'client_id'      => 0,
                'enabled'        => 1,
                'access'         => 1,
                'protected'      => 0,
                'manifest_cache' => $manifest,
            ));
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

    public function sendSecondReceipt($orderId, $pmconfig, $status)
    {
        $kassa = $this->getKassaPaymentMethod($pmconfig);
        $apiClient = $kassa->getClient();
        $order     = JSFactory::getTable('order', 'jshop');
        $order->load($orderId);


        $orderInfo = array(
            'orderId'    => $order->order_id,
            'user_email' => $order->email,
            'user_phone' => $order->phone,
        );

        if (!$this->isNeedSecondReceipt($status, $kassa->isSendReceipt(), $kassa->isSendSecondReceipt(), $kassa->getSecondReceiptStatus())) {
            return;
        }

        try {
            $paymentInfo = $apiClient->getPaymentInfo($this->getOrderModel()->getPaymentIdByOrderId($order->order_id));
        } catch (Exception $e) {
            $this->log('info', 'fail get payment info');
            return;
        }

        $secondReceipt = new KassaSecondReceiptModel($paymentInfo, $orderInfo, $apiClient);
        if ($secondReceipt->sendSecondReceipt()) {
            $this->saveOrderHistory(
                    $order,
                    sprintf(
                _JSHOP_YM_KASSA_SEND_SECOND_RECEIPT_HISTORY,
                        number_format($secondReceipt->getSettlementsSum(), 2, '.', ' ')
                    )
            );
        }
    }

    /**
     * @param $status
     * @param $isSendReceipt
     * @param $isSendSecondReceipt
     * @param $secondReceiptStatus
     *
     * @return bool
     */
    public function isNeedSecondReceipt($status, $isSendReceipt, $isSendSecondReceipt, $secondReceiptStatus)
    {
        if (!$isSendReceipt) {
            return false;
        } elseif (!$isSendSecondReceipt) {
            return false;
        } elseif ((int)$status !== (int)$secondReceiptStatus) {
            return false;
        }

        return true;
    }

    /**
     * Возвращает доступные способы оплаты.
     * @return array
     */
    private static function getPaymentMethods()
    {
        $enabledPaymentMethods = array();
        $paymentMethods = array_merge(self::$customPaymentMethods, PaymentMethodType::getEnabledValues());
        foreach ($paymentMethods as $value) {
            if (!in_array($value, self::$disabledPaymentMethods)) {
                $enabledPaymentMethods[] = $value;
            }
        }

        return $enabledPaymentMethods;
    }
}