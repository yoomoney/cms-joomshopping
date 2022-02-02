<?php

namespace YooMoney\Helpers;

use YooKassa\Model\NotificationEventType;
use YooKassa\Model\PaymentStatus;
use YooKassa\Model\Refund;
use YooKassa\Model\RefundStatus;
use YooKassa\Model\PaymentMethodType;
use YooMoney\Model\KassaPaymentMethod;
use YooMoney\Model\OrderModel;
use Joomla\Component\Jshopping\Site\Table\OrderTable;

/**
 * В классе объединены методы для обработки входящих уведомлений от Юkassa
 */
class TransactionHelper
{
    const CANCELED_STATUS_ID = 3;
    const REFUNDED_STATUS_ID = 4;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @var ReceiptHelper
     */
    private $receiptHelper;

    /**
     * @var YoomoneyNotificationFactory
     */
    private $yooNotificationHelper;

    /**
     * @var OrderModel
     */
    private $orderModel;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->yooNotificationHelper = new YoomoneyNotificationFactory();
        $this->orderHelper = new OrderHelper();
        $this->receiptHelper = new ReceiptHelper();
        $this->orderModel = new OrderModel();
    }

    /**
     * Обрабатывает уведомление от Юkassa в зависимости от статуса платежа в уведомлении
     *
     * @param KassaPaymentMethod $kassa
     * @param array $pmConfigs
     * @param OrderTable $order
     * @return bool|void
     * @throws \Exception
     */
    public function processNotification($kassa, $pmConfigs, $order)
    {
        $notificationObj = $this->yooNotificationHelper->getNotificationObject();
        $paymentId = $notificationObj->getObject()->getId();

        $refund = null;
        if ($notificationObj->getEvent() === NotificationEventType::REFUND_SUCCEEDED) {
            $refundId = $notificationObj->getObject()->getId();
            $refund = $kassa->fetchRefund($refundId);

            if (!$refund) {
                $this->logger->log('debug', 'Notification error: refund is not exist');
                header('HTTP/1.1 404 Refund is not exist');
                die();
            }
            $paymentId = $refund->getPaymentId();
        }

        $payment = $kassa->fetchPayment($paymentId);
        if (!$payment) {
            $this->logger->log('debug', 'Notification error: payment is not exist');
            header('HTTP/1.1 404 Payment not exists');
            die();
        }

        if (
            $notificationObj->getEvent() === NotificationEventType::PAYMENT_SUCCEEDED
            && $payment->getStatus() === PaymentStatus::SUCCEEDED
        ) {
            $this->processSucceedNotification($pmConfigs, $order, $payment, $kassa);
            return true;
        }

        if (
            $notificationObj->getEvent() === NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE
            && $payment->getStatus() === PaymentStatus::WAITING_FOR_CAPTURE
        ) {
            $this->processWaitingForCaptureNtfctn($pmConfigs, $order, $payment, $kassa, $notificationObj);
            return true;
        }

        if (
            $notificationObj->getEvent() === NotificationEventType::PAYMENT_CANCELED
            && $payment->getStatus() === PaymentStatus::CANCELED
            && $kassa->isEnableHoldMode()
        ) {
            $this->processCanceledHoldPaymentNtfctn($pmConfigs, $order, $payment);
            return true;
        }

        if (
            $notificationObj->getEvent() === NotificationEventType::PAYMENT_CANCELED
            && $payment->getStatus() === PaymentStatus::CANCELED
            && !$kassa->isEnableHoldMode()
        ) {
            $this->logger->log('info', 'Canceled payment ' . $payment->getId());
            $this->processCanceledPaymentNtfctn($order);
            return true;
        }

        if (
            $notificationObj->getEvent() === NotificationEventType::REFUND_SUCCEEDED
            && $payment->getStatus() === PaymentStatus::SUCCEEDED
            && $refund->getStatus() == RefundStatus::SUCCEEDED
            && !$this->isRefundAlreadyGot($refund->getId())
        ) {
            $this->logger->log(
                'info', 'Refund payment ' . $payment->getId() . '. Refund ID ' . $refund->getId()
            );
            $this->processRefundNtfctn($order, $refund);
            return true;
        }

        if (
            $notificationObj->getEvent() === NotificationEventType::DEAL_CLOSED
            || $notificationObj->getEvent() === NotificationEventType::PAYOUT_CANCELED
            || $notificationObj->getEvent() === NotificationEventType::PAYOUT_SUCCEEDED
        ) {
            return true;
        }

        return false;
    }

    /**
     * Проверяет в БД было ли уже получено уведомление о возврате с таким id
     *
     * @param $refundId
     * @return bool
     */
    private function isRefundAlreadyGot($refundId)
    {
        try {
            $refund = $this->orderModel->getRefundById($refundId);
        } catch (\Exception $e) {
            $this->logger->log('debug', 'Failed to read a refund from DB: ' . $e->getMessage());
        }

        return !empty($refund);
    }

    /**
     * Выполняет действия, если получено уведомление о статусе payment.succeeded
     *
     * @param $pmConfigs
     * @param $order
     * @param $payment
     * @param KassaPaymentMethod $kassa
     */
    private function processSucceedNotification($pmConfigs, $order, $payment, $kassa)
    {
        $jshopConfig = \JSFactory::getConfig();

        /** @var jshopCheckout $checkout */
        $checkout             = \JSFactory::getModel('checkout', 'jshop');
        $endStatus            = $pmConfigs['transaction_end_status'];
        $order->order_created = 1;
        $order->order_status  = $endStatus;
        $order->store();

        try {
            if ($jshopConfig->send_order_email) {
                $checkout->sendOrderEmail($order->order_id);
            }
        } catch (\Exception $exception) {
            $this->logger->log('debug', $exception->getMessage());
        }

        $product_stock_removed = true;
        if ($jshopConfig->order_stock_removed_only_paid_status) {
            $product_stock_removed = in_array($endStatus, $jshopConfig->payment_status_enable_download_sale_file);
        }

        if ($product_stock_removed) {
            $order->changeProductQTYinStock("-");
        }

        $this->receiptHelper->sendSecondReceipt($order->order_id, $kassa, $endStatus);

        $checkout->changeStatusOrder($order->order_id, $endStatus, 0);

        $paymentMethod = $payment->getPaymentMethod();
        if($paymentMethod->getType() == PaymentMethodType::B2B_SBERBANK) {
            $message = $this->getSuccessOrderHistoryMessageForB2B($paymentMethod);
        }

        if (!empty($message)) {
            $this->orderHelper->saveOrderHistory($order, $message);
        }
    }

    /**
     * Возвращает сообщение для истории статусов заказа, если тип платежа b2b_sberbank
     *
     * @param $paymentMethod
     * @return string
     */
    private function getSuccessOrderHistoryMessageForB2B($paymentMethod)
    {
        $payerBankDetails = $paymentMethod->getPayerBankDetails();

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
        $message = '';
        foreach ($fields as $field => $caption) {
            if (isset($requestData[$field])) {
                $message .= $caption.': '.$payerBankDetails->offsetGet($field).'\n';
            }
        }
        return $message;
    }

    /**
     * Выполняет действия, если получено уведомление о статусе payment.waiting_for_capture
     *
     * @param $pmConfigs
     * @param $order
     * @param $payment
     * @param $kassa
     * @param $notificationObj
     */
    private function processWaitingForCaptureNtfctn($pmConfigs, $order, $payment, $kassa, $notificationObj)
    {
        if ($kassa->isEnableHoldMode()) {
            $this->logger->log('info', 'Hold payment '.$payment->getId());

            /** @var jshopCheckout $checkout */
            $checkout             = \JSFactory::getModel('checkout', 'jshop');
            $onHoldStatus         = $pmConfigs['yookassa_hold_mode_on_hold_status'];
            $order->order_created = 1;
            $order->order_status  = $onHoldStatus;
            $order->store();
            $checkout->changeStatusOrder($order->order_id, $onHoldStatus, 0);
            $this->orderHelper->saveOrderHistory(
                $order,
                sprintf(_JSHOP_YOO_HOLD_MODE_COMMENT_ON_HOLD,
                $payment->getExpiresAt()->format('d.m.Y H:i'))
            );

        } else {
            $payment = $kassa->capturePayment($notificationObj->getObject());
            if (!$payment || $payment->getStatus() !== PaymentStatus::SUCCEEDED) {
                $this->logger->log('debug', 'Capture payment error');
                header('HTTP/1.1 400 Bad Request');
            }
        }
    }

    /**
     * Выполняет действия, если получено уведомление о статусе payment.canceled и включен режим холдирования
     *
     * @param $pmConfigs
     * @param $order
     * @param $payment
     */
    private function processCanceledHoldPaymentNtfctn($pmConfigs, $order, $payment)
    {
        $this->logger->log('info', 'Canceled hold payment ' . $payment->getId());

        /** @var jshopCheckout $checkout */
        $checkout             = \JSFactory::getModel('checkout', 'jshop');
        $cancelHoldStatus         = $pmConfigs['yookassa_hold_mode_cancel_status'];
        $order->order_created = 1;
        $order->order_status  = $cancelHoldStatus;
        $order->store();
        $checkout->changeStatusOrder($order->order_id, $cancelHoldStatus, 0);
    }

    /**
     * Выполняет действия, если получено уведомление о статусе payment.canceled
     *
     * @param $order
     */
    private function processCanceledPaymentNtfctn($order)
    {
        /** @var jshopCheckout $checkout */
        $checkout             = \JSFactory::getModel('checkout', 'jshop');

        $order->order_created = 1;
        $order->order_status  = self::CANCELED_STATUS_ID;
        $order->store();
        $checkout->changeStatusOrder($order->order_id, self::CANCELED_STATUS_ID, 0);
    }

    /**
     * Выполняет действия, если получено уведомление о статусе refund.succeeded
     *
     * @param $order
     * @param Refund $refund
     */
    private function processRefundNtfctn($order, $refund)
    {
        try {
            $this->orderModel->insertRefund($order->getId(), $refund);
        } catch (\Exception $e) {
            $this->logger->log('debug', 'Failed to save a refund to DB: ' . $e->getMessage());

            $this->logger->log('debug', 'Will create the refund table.');
            try {
                $tableChecker = new \YooMoney\Updater\Tables\TableChecker();
                $tableChecker->checkYoomoneyRefunds();
            } catch (\Exception $e) {
                $this->logger->log('debug', 'Failure to create the refund table: ' . $e->getMessage());

                return;
            }

            $this->orderModel->insertRefund($order->getId(), $refund);
        }

        /** @var jshopCheckout $checkout */
        $checkout = \JSFactory::getModel('checkout', 'jshop');

        $order->order_created = 1;
        $order->order_status  = self::REFUNDED_STATUS_ID;
        $order->store();
        $checkout->changeStatusOrder($order->order_id, self::REFUNDED_STATUS_ID, 0);
        $this->orderHelper->saveOrderHistory(
            $order,
            sprintf(_JSHOP_YOO_KASSA_REFUND_SUCCEDED_ORDER_HISTORY,
                $refund->getAmount()->getValue())
        );
    }
}