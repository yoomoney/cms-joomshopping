<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YooMoney
 * @copyright Copyright (C) 2020 YooMoney. All rights reserved.
 */

use YooKassa\Model\Confirmation\ConfirmationEmbedded;
use YooKassa\Model\Confirmation\ConfirmationRedirect;
use YooKassa\Model\Notification\AbstractNotification;
use YooKassa\Model\PaymentMethodType;
use YooKassa\Model\Receipt\PaymentMode;
use YooKassa\Model\Receipt\PaymentSubject;
use YooKassa\Request\Payments\CreatePaymentResponse;
use YooMoney\Helpers\OrderHelper;
use YooMoney\Model\KassaPaymentMethod;
use YooMoney\Helpers\ReceiptHelper;
use YooMoney\Helpers\Logger;
use YooMoney\Helpers\TransactionHelper;
use YooMoney\Helpers\JVersionDependenciesHelper;
use YooMoney\Helpers\YoomoneyNotificationFactory;

defined('_JEXEC') or die('Restricted access');

define('JSH_DIR', realpath(dirname(__FILE__).'/../..'));
define('DIR_DOWNLOAD', JSH_DIR.'/log');

require_once dirname(__FILE__).'/lib/autoload.php';

define('_JSHOP_YOO_VERSION', '2.3.1');

class pm_yoomoney extends PaymentRoot
{
    const MODE_OFF = 0;
    const MODE_KASSA = 1;
    const MODE_MONEY = 2;
    const MODE_PAYMENTS = 3;

    private $mode = -1;
    private $debugLog = true;

    private $element = 'pm_yoomoney';
    private $repository = 'yoomoney/cms-joomshopping';
    private $downloadDirectory = 'pm_yoomoney';
    private $backupDirectory = 'pm_yoomoney/backups';
    private $versionDirectory = 'pm_yoomoney/download';

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

    private static $disabledPaymentMethods = array(
        PaymentMethodType::B2B_SBERBANK,
        PaymentMethodType::WECHAT,
    );

    private static $customPaymentMethods = array(
        'widget',
    );

    public $existentcheckform = true;
    public $yoopay_mode, $yootest_mode, $yoopassword, $yooshopid, $yooscid;

    /**
     * @var YooMoney\Model\OrderModel
     */
    private $orderModel;

    /**
     * @var KassaPaymentMethod
     */
    private $kassa;

    /**
     * @var ReceiptHelper
     */
    private $receiptHelper;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    public function __construct()
    {
        $this->versionHelper = new JVersionDependenciesHelper();
        $this->logger = new Logger();
        $this->transactionHelper = new TransactionHelper();
        $this->yooNotificationHelper = new YoomoneyNotificationFactory();
        $this->receiptHelper = new ReceiptHelper();
        $this->orderHelper = new OrderHelper();
    }

    /**
     * get save payment params
     * @return boolean
     */
    function getSavePaymentParams(){
        return true;
    }

    function showPaymentForm($params, $pmConfigs)
    {
        $this->loadLanguageFile();
        $this->mode = $this->getMode($pmConfigs);

        if ($this->mode === self::MODE_KASSA) {
            include(dirname(__FILE__) . "/payment_form_yookassa.php");
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
                'yoo_payments_fio' => _JSHOP_YOO_PAYMENTS_FIO_LABEL,
            );
        }

        return $names;
    }

    /**
     * Проверяет параметры указанные пользователем на странице выбора способа оплаты
     *
     * @param array $params Массив параметров, указанных в params[pm_yoomoney] на странице выбора способа оплаты
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
            if (empty($params) || empty($params['yoo_payments_fio'])) {
                return false;
            }
            $name = trim($params['yoo_payments_fio']);
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
            } elseif ($_GET['subaction'] === 'install_widget_well_known') {
                $result = array(
                    "is_success" => $this->installApplePayFileForWidget(),
                    'message' => _JSHOP_YOO_INSTALL_VERIFY_APPLE_PAY_FILE_WARNING,
                );
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
            'yoopay_id',
            'yoopay_desc',
            'yoo_payments_fio',
            'page_mpos',
            'yookassa_description_template',
            'yookassa_send_check',
            'yookassa_default_tax',
            'yookassa_default_tax_system',
            'method_mp',
            'debug_log',
            'yookassa_default_payment_mode',
            'yookassa_default_payment_subject',
            'yookassa_default_delivery_payment_mode',
            'yookassa_default_delivery_payment_subject',
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
            $array_params[] = 'yookassa_tax_'.$k;
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
        $filename = $this->versionHelper->getFilesVersionPostfix();

        $this->versionHelper->registerEventListener('onBeforeEditPayments', array($this, 'onBeforeEditPayments'));

        $zip_enabled  = function_exists('zip_open');
        $curl_enabled = function_exists('curl_init');
        if ($zip_enabled && $curl_enabled) {
            $force       = false;
            $versionInfo = $this->checkModuleVersion($force);
            if (version_compare($versionInfo['version'], _JSHOP_YOO_VERSION) > 0) {
                $new_version_available = true;
                $changelog             = $this->getChangeLog(_JSHOP_YOO_VERSION, $versionInfo['version']);
                $newVersion            = $versionInfo['version'];
            } else {
                $new_version_available = false;
                $changelog             = '';
                $newVersion            = _JSHOP_YOO_VERSION;
            }
            $newVersionInfo = $versionInfo;

            $backups = $this->getBackupList();
        }

        if ($params['kassamode']) {
            if (!empty($params['shop_id']) && !empty($params['shop_password'])) {
                $kassa = $this->getKassaPaymentMethod($params);
                if (!$kassa->checkConnection()) {
                    $errorCredentials = _JSHOP_YOO_KASSA_CREDENTIALS_ERROR;
                }
                if (strncmp('test_', $params['shop_password'], 5) === 0) {
                    $testWarning = _JSHOP_YOO_KASSA_TEST_WARNING;
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

    /**
     * Возвращает путь к файлу лога
     *
     * @return string
     */
    private function getLogFileName()
    {
        return $this->logger->getLogFileName();
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
                $archive         = new \YooMoney\Updater\Archive\RestoreZip($fileName);
                $archive->restore('file_map.map', $sourceDirectory);
            } catch (Exception $e) {
                $this->log('error', $e->getMessage());
                if ($e->getPrevious() !== null) {
                    $this->log('error', $e->getPrevious()->getMessage());
                }

                return array(
                    'message' => _JSHOP_YOO_UPDATER_ERROR_RESTORE.$e->getMessage(),
                    'success' => false,
                );
            }

            return array(
                'message' => _JSHOP_YOO_UPDATER_SUCCESS_MESSAGE.$_POST['file_name'],
                'success' => true,
            );
        }

        return array(
            'message' => _JSHOP_YOO_UPDATER_ERROR_REMOVE,
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
                    'message' => sprintf(_JSHOP_YOO_ERROR_BACKUP_NOT_FOUND, $fileName),
                    'success' => false,
                );
            }

            if (!unlink($fileName) || file_exists($fileName)) {
                $this->log('error', 'Failed to unlink file "'.$fileName.'"');

                return array(
                    'message' => _JSHOP_YOO_ERROR_REMOVE_BACKUP.$fileName,
                    'success' => false,
                );
            }

            return array(
                'message' => sprintf(_JSHOP_YOO_SUCCESS_REMOVE_BECKUP, $fileName),
                'success' => true,
            );
        }

        return array(
            'message' => _JSHOP_YOO_UPDATER_ERROR_REMOVE,
            'success' => false,
        );
    }

    public function updateVersion()
    {
        $versionInfo = $this->checkModuleVersion(false);
        $fileName    = $this->downloadLastVersion($versionInfo['tag']);
        if (!empty($fileName)) {
            if ($this->createBackup(_JSHOP_YOO_VERSION)) {
                if ($this->unpackLastVersion($fileName)) {
                    $result = array(
                        'message' => sprintf(_JSHOP_YOO_SUCCESS_UPDATE_VERSION, $_POST['version'], $fileName),
                        'success' => true,
                    );
                } else {
                    $result = array(
                        'message' => sprintf(_JSHOP_YOO_ERROR_UNPACK_NEW_VERSION, $fileName),
                        'success' => false,
                    );
                }
            } else {
                $result = array(
                    'message' => _JSHOP_YOO_ERROR_CREATE_BACKUP,
                    'success' => false,
                );
            }
        } else {
            $result = array(
                'message' => _JSHOP_YOO_ERROR_DOWNLOAD_NEW_VERSION,
                'success' => false,
            );
        }

        return $result;
    }

    private function installApplePayFileForWidget()
    {
        clearstatcache();
        $directoryWellKnownPath = JPATH_ROOT . DIRECTORY_SEPARATOR . ".well-known";
        $directoryWellKnownFile = $directoryWellKnownPath . DIRECTORY_SEPARATOR . "apple-developer-merchantid-domain-association";;

        if (file_exists($directoryWellKnownFile)) {
            return true;
        }

        if (!file_exists($directoryWellKnownPath)) {
            if (!@mkdir($directoryWellKnownPath, 0755)) {
                return false;
            }
        }

        $result = @file_put_contents(
            $directoryWellKnownFile,
            '7B227073704964223A2236354545363242363931303142343742414637434132324336344232453843314531353341373238363339453042333731454543434341324237463345354535222C2276657273696F6E223A312C22637265617465644F6E223A313536353731323134383430382C227369676E6174757265223A223330383030363039326138363438383666373064303130373032613038303330383030323031303133313066333030643036303936303836343830313635303330343032303130353030333038303036303932613836343838366637306430313037303130303030613038303330383230336536333038323033386261303033303230313032303230383638363066363939643963636137306633303061303630383261383634386365336430343033303233303761333132653330326330363033353530343033306332353431373037303663363532303431373037303663363936333631373436393666366532303439366537343635363737323631373436393666366532303433343132303264323034373333333132363330323430363033353530343062306331643431373037303663363532303433363537323734363936363639363336313734363936663665323034313735373436383666373236393734373933313133333031313036303335353034306130633061343137303730366336353230343936653633326533313062333030393036303335353034303631333032353535333330316531373064333133363330333633303333333133383331333633343330356131373064333233313330333633303332333133383331333633343330356133303632333132383330323630363033353530343033306331663635363336333264373336643730326436323732366636623635373232643733363936373665356635353433333432643533343134653434343234663538333131343330313230363033353530343062306330623639346635333230353337393733373436353664373333313133333031313036303335353034306130633061343137303730366336353230343936653633326533313062333030393036303335353034303631333032353535333330353933303133303630373261383634386365336430323031303630383261383634386365336430333031303730333432303030343832333066646162633339636637356532303263353064393962343531326536333765326139303164643663623365306231636434623532363739386638636634656264653831613235613863323165346333336464636538653261393663326636616661313933303334356334653837613434323663653935316231323935613338323032313133303832303230643330343530363038326230363031303530353037303130313034333933303337333033353036303832623036303130353035303733303031383632393638373437343730336132663266366636333733373032653631373037303663363532653633366636643266366636333733373033303334326436313730373036633635363136393633363133333330333233303164303630333535316430653034313630343134303232343330306239616565656434363331393761346136356132393965343237313832316334353330306330363033353531643133303130316666303430323330303033303166303630333535316432333034313833303136383031343233663234396334346639336534656632376536633466363238366333666132626266643265346233303832303131643036303335353164323030343832303131343330383230313130333038323031306330363039326138363438383666373633363430353031333038316665333038316333303630383262303630313035303530373032303233303831623630633831623335323635366336393631366536333635323036663665323037343638363937333230363336353732373436393636363936333631373436353230363237393230363136653739323037303631373237343739323036313733373337353664363537333230363136333633363537303734363136653633363532303666363632303734363836353230373436383635366532303631373037303663363936333631363236633635323037333734363136653634363137323634323037343635373236643733323036313665363432303633366636653634363937343639366636653733323036663636323037353733363532633230363336353732373436393636363936333631373436353230373036663663363936333739323036313665363432303633363537323734363936363639363336313734363936663665323037303732363136333734363936333635323037333734363137343635366436353665373437333265333033363036303832623036303130353035303730323031313632613638373437343730336132663266373737373737326536313730373036633635326536333666366432663633363537323734363936363639363336313734363536313735373436383666373236393734373932663330333430363033353531643166303432643330326233303239613032376130323538363233363837343734373033613266326636333732366332653631373037303663363532653633366636643266363137303730366336353631363936333631333332653633373236633330306530363033353531643066303130316666303430343033303230373830333030663036303932613836343838366637363336343036316430343032303530303330306130363038326138363438636533643034303330323033343930303330343630323231303064613163363361653862653566363466386531316538363536393337623962363963343732626539336561633332333361313637393336653461386435653833303232313030626435616662663836396633633063613237346232666464653466373137313539636233626437313939623263613066663430396465363539613832623234643330383230326565333038323032373561303033303230313032303230383439366432666266336139386461393733303061303630383261383634386365336430343033303233303637333131623330313930363033353530343033306331323431373037303663363532303532366636663734323034333431323032643230343733333331323633303234303630333535303430623063316434313730373036633635323034333635373237343639363636393633363137343639366636653230343137353734363836663732363937343739333131333330313130363033353530343061306330613431373037303663363532303439366536333265333130623330303930363033353530343036313330323535353333303165313730643331333433303335333033363332333333343336333333303561313730643332333933303335333033363332333333343336333333303561333037613331326533303263303630333535303430333063323534313730373036633635323034313730373036633639363336313734363936663665323034393665373436353637373236313734363936663665323034333431323032643230343733333331323633303234303630333535303430623063316434313730373036633635323034333635373237343639363636393633363137343639366636653230343137353734363836663732363937343739333131333330313130363033353530343061306330613431373037303663363532303439366536333265333130623330303930363033353530343036313330323535353333303539333031333036303732613836343863653364303230313036303832613836343863653364303330313037303334323030303466303137313138343139643736343835643531613565323538313037373665383830613265666465376261653464653038646663346239336531333335366435363635623335616532326430393737363064323234653762626130386664373631376365383863623736626236363730626563386538323938346666353434356133383166373330383166343330343630363038326230363031303530353037303130313034336133303338333033363036303832623036303130353035303733303031383632613638373437343730336132663266366636333733373032653631373037303663363532653633366636643266366636333733373033303334326436313730373036633635373236663666373436333631363733333330316430363033353531643065303431363034313432336632343963343466393365346566323765366334663632383663336661326262666432653462333030663036303335353164313330313031666630343035333030333031303166663330316630363033353531643233303431383330313638303134626262306465613135383333383839616134386139396465626562646562616664616362323461623330333730363033353531643166303433303330326533303263613032616130323838363236363837343734373033613266326636333732366332653631373037303663363532653633366636643266363137303730366336353732366636663734363336313637333332653633373236633330306530363033353531643066303130316666303430343033303230313036333031303036306132613836343838366637363336343036303230653034303230353030333030613036303832613836343863653364303430333032303336373030333036343032333033616366373238333531313639396231383666623335633335366361363262666634313765646439306637353464613238656265663139633831356534326237383966383938663739623539396639386435343130643866396465396332666530323330333232646435343432316230613330353737366335646633333833623930363766643137376332633231366439363466633637323639383231323666353466383761376431623939636239623039383932313631303639393066303939323164303030303331383230313863333038323031383830323031303133303831383633303761333132653330326330363033353530343033306332353431373037303663363532303431373037303663363936333631373436393666366532303439366537343635363737323631373436393666366532303433343132303264323034373333333132363330323430363033353530343062306331643431373037303663363532303433363537323734363936363639363336313734363936663665323034313735373436383666373236393734373933313133333031313036303335353034306130633061343137303730366336353230343936653633326533313062333030393036303335353034303631333032353535333032303836383630663639396439636361373066333030643036303936303836343830313635303330343032303130353030613038313935333031383036303932613836343838366637306430313039303333313062303630393261383634383836663730643031303730313330316330363039326138363438383666373064303130393035333130663137306433313339333033383331333333313336333033323332333835613330326130363039326138363438383666373064303130393334333131643330316233303064303630393630383634383031363530333034303230313035303061313061303630383261383634386365336430343033303233303266303630393261383634383836663730643031303930343331323230343230306463316331626362653237356662363066663361663437363239636464353866396263323138333034653866323738613463313830316237353466653839363330306130363038326138363438636533643034303330323034343733303435303232313030396563323139666431396663326661326536373232393730393538333831343338366265343264353864323634303262643665383265633833323636336539333032323033363863323238616362313731393261653434626538366535386235313461636235386337396438663839373936323735653837363730373435363735333432303030303030303030303030227D'
        );

        $this->log("info", "Result apple-pay file write $result");

        return $result !== false;
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
        if (file_exists(JPATH_ROOT.'/components/com_jshopping/payments/pm_yoomoney/lang/'.$langTag.'.php')) {
            require_once(JPATH_ROOT.'/components/com_jshopping/payments/pm_yoomoney/lang/'.$langTag.'.php');
        } else {
            require_once(JPATH_ROOT.'/components/com_jshopping/payments/pm_yoomoney/lang/ru-RU.php');
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
                .$callbackParams['sender'].'&'.$callbackParams['codepro'].'&'.$this->yoopassword.'&'
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
        $kassa = $this->getKassaPaymentMethod($pmConfigs);

        $this->mode = $this->getMode($pmConfigs);

        $check = true;

        if ($this->mode === self::MODE_MONEY) {
            $this->yoopay_mode = ($pmConfigs['paymode'] == '1');
            $this->yooshopid   = $pmConfigs['shopid'];
            $this->yooscid     = $pmConfigs['scid'];

            $order->order_total = floatval($order->order_total);

            $callbackParams = JRequest::get('post');
            $this->loadLanguageFile();
            $check = $this->checkSign($callbackParams);
        }

        if ($this->mode === self::MODE_KASSA) {

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
        return 'https://yoomoney.ru/quickpay/confirm.xml';
    }

    /**
     * @param $pmConfigs
     * @param jshopOrder $order
     */
    function showEndForm($pmConfigs, $order)
    {
        $this->yootest_mode = isset($pmConfigs['testmode']) ? $pmConfigs['testmode'] : false;
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
        $this->yoopay_mode = (isset($pmConfigs['paymode']) && $pmConfigs['paymode'] == '1');

        $uri         = JURI::getInstance();
        $liveUrlHost = $uri->toString(array("scheme", 'host', 'port'));

        $yooparams = unserialize($order->payment_params_data);

        $this->loadLanguageFile();
        $item_name = $liveUrlHost." ".sprintf(_JSHOP_PAYMENT_NUMBER, $order->order_number);

        $return = $liveUrlHost.$this->versionHelper->getSefLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_yoomoney&order_id=".$order->id);

        $order->order_total = $this->fixOrderTotal($order);
        if ($yooparams['yoo-payment-type'] == 'MP') {
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
                <input type="hidden" name="paymentType" value="<?php echo $yooparams['yoo-payment-type'] ?>"/>
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
            $this->finishOrder($order, $pmConfigs['yoopay_status']);
            $narrative = $this->parseTemplate($pmConfigs['yoopay_desc'], $order);
            ?>
            <form method="POST" action="<?php echo $this->getFormUrl(); ?>" id="paymentform" name="paymentform">
                <input type="hidden" name="formId" value="<?php echo htmlspecialchars($pmConfigs['yoopay_id']); ?>"/>
                <input type="hidden" name="narrative" value="<?php echo htmlspecialchars($narrative); ?>"/>
                <input type="hidden" name="fio" value="<?php echo htmlspecialchars($yooparams['yoo_payments_fio']); ?>"/>
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
            .$this->versionHelper->getSefLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_yoomoney&no_lang=1&order_id=".$order->order_id);
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
            $app->enqueueMessage(_JSHOP_YOO_ERROR_MESSAGE_CREATE_PAYMENT, 'error');
            $app->redirect($redirect);
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
            $app->enqueueMessage(_JSHOP_YOO_ERROR_MESSAGE_CREATE_PAYMENT, 'error');
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
        $params = array();
        if ($this->mode != self::MODE_KASSA) {

            $params['order_id']  = (int)$_POST['label'];
            $params['hash']      = "";
            $params['checkHash'] = 0;

            return $params;
        }

        $this->log('debug', 'Get URL parameters for payment');
        if (isset($_GET['order_id'])) {

            $this->log('debug', 'Order id exists in return url: '.$_GET['order_id']);

            $params['order_id'] = (int)$_GET['order_id'];

            if (!$this->checkPaymentByOrderId($params['order_id'], $pmConfigs)) {
                $redirectUrl = JRoute::_(JURI::root().'index.php?option=com_jshopping&controller=checkout&task=step3');
                $app         = JFactory::getApplication();
                $app->redirect($redirectUrl);
            }

            $params['hash']              = "";
            $params['checkHash']         = 0;
            $params['checkReturnParams'] = 1;
            $this->log('debug', 'Return url params is: '.json_encode($params));

            return $params;
        }

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

        $this->log('debug', 'Order id not exists in return url: '.json_encode($_GET));

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
                $this->yoopassword = $paymentConfig['shoppassword'];
            } elseif (isset($paymentConfig['moneymode']) && $paymentConfig['moneymode'] == '1') {
                $this->mode        = self::MODE_MONEY;
                $this->yoopassword = $paymentConfig['password'];
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
        $payment_method = 'pm_yoomoney';
        $no_lang        = '1';

        if ($this->versionHelper->getJoomlaVersion() === 2) {
            // joomla 2.x order finish
            $jshopConfig = JSFactory::getConfig();

            /** @var jshopCheckout $checkout */
            $checkout = JSFactory::getModel('checkout', 'jshop');

            $order->order_created = 1;
            $order->order_status  = $endStatus;
            $order->store();
            try {
                if ($jshopConfig->send_order_email) {
                    $checkout->sendOrderEmail($order->order_id);
                }
            } catch (\Exception $exception) {
                $this->log('debug', $exception->getMessage());
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

    /**
     * @param $level
     * @param $message
     * @param array $context
     */
    public function log($level, $message, $context = array())
    {
        if (!$this->debugLog) {
            return;
        }

        $this->logger->log($level, $message, $context);
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

    /**
     * @param $pmConfigs
     * @return KassaPaymentMethod
     */
    public function getKassaPaymentMethod($pmConfigs)
    {
        $this->loadLanguageFile();
        if ($this->kassa === null) {
            $this->kassa = new KassaPaymentMethod($this, $pmConfigs);
        }

        return $this->kassa;
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

        $connector = new \YooMoney\Updater\GitHubConnector();
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

        $connector = new \YooMoney\Updater\GitHubConnector();

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
                $this->log('error', _JSHOP_YOO_FAILED_CREATE_DIRECTORY.$dir);

                return false;
            }
        } elseif ($useCache) {
            $fileName = $dir.'/'.$tag.'.zip';
            if (file_exists($fileName)) {
                return $fileName;
            }
        }

        $connector = new \YooMoney\Updater\GitHubConnector();
        $fileName  = $connector->downloadRelease($this->repository, $tag, $dir);
        if (empty($fileName)) {
            $this->log('error', _JSHOP_YOO_FAILED_DOWNLOAD_UPDATE);

            return false;
        }

        return $fileName;
    }

    public function createBackup($version)
    {
        $this->preventDirectoryCreation();

        $sourceDirectory = dirname(dirname(JSH_DIR));
        $reader          = new \YooMoney\Updater\ProjectStructure\ProjectStructureReader();
        $root            = $reader->readFile(JSH_DIR.'/payments/pm_yoomoney/lib/joomshopping.map',
            $sourceDirectory);

        $rootDir  = $version.'-'.time();
        $fileName = $rootDir.'-'.uniqid('', true).'.zip';
        $dir      = DIR_DOWNLOAD.'/'.$this->backupDirectory;
        try {
            $fileName = $dir.'/'.$fileName;
            $archive  = new \YooMoney\Updater\Archive\BackupZip($fileName, $rootDir);
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
            $archive         = new \YooMoney\Updater\Archive\RestoreZip($fileName, $this);
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
        $addon = $this->versionHelper->getAddonTableObj();

        $manifest = '{"creationDate":"20.07.2018","author":"YooMoney","authorEmail":"cms@yoomoney.ru","authorUrl":"https://yookassa.ru","version":"'._JSHOP_YOO_VERSION.'"}';
        $addon->installJoomlaExtension(
            array(
                'name'           => 'YooMoney',
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

    /**
     * @param $order
     * @param $comments
     * @return mixed
     */
    public function saveOrderHistory($order, $comments)
    {
        return $this->orderHelper->saveOrderHistory($order, $comments);
    }

    /**
     * @param $orderId
     * @param KassaPaymentMethod $kassa
     * @param $status
     */
    public function sendSecondReceipt($orderId, $kassa, $status)
    {
        $this->receiptHelper->sendSecondReceipt($orderId, $kassa, $status);
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