<?php

namespace YooMoney\Helpers;

use YooMoney\Model\KassaSecondReceiptModel;
use YooMoney\Model\OrderModel;

/**
 * Класс методов для работы с чеками
 */
class ReceiptHelper
{
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var OrderHelper
     */
    private $orderHelper;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->orderHelper = new OrderHelper();
    }

    /**
     * Выполняет отправку второго чека
     *
     * @param $orderId
     * @param $kassa
     * @param $status
     * @return void|KassaSecondReceiptModel
     */
    public function sendSecondReceipt($orderId, $kassa, $status)
    {

        if (!$this->isNeedSecondReceipt(
            $status,
            $kassa->isSendReceipt(),
            $kassa->isSendSecondReceipt(),
            $kassa->getSecondReceiptStatus())
        ) {
            return;
        }

        $order = \JSFactory::getTable('order', 'jshop');
        $order->load($orderId);

        $apiClient = $kassa->getClient();

        $orderInfo = array(
            'orderId'    => $order->order_id,
            'user_email' => $order->email,
            'user_phone' => $order->phone,
        );

        $orderModel = new OrderModel();

        try {
            $paymentInfo = $apiClient->getPaymentInfo($orderModel->getPaymentIdByOrderId($order->order_id));
        } catch (\Exception $e) {
            $this->logger->log('info', 'fail get payment info');
            return;
        }

        $secondReceipt = new KassaSecondReceiptModel($paymentInfo, $orderInfo, $apiClient);
        if ($secondReceipt->sendSecondReceipt()) {
            $this->orderHelper->saveOrderHistory(
                $order,
                sprintf(
                    _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_HISTORY,
                    number_format($secondReceipt->getSettlementsSum(), 2, '.', ' ')
                )
            );
        }
    }

    /**
     * Проверка условий необходимости отправки второго чека
     *
     * @param $status
     * @param $isSendReceipt
     * @param $isSendSecondReceipt
     * @param $secondReceiptStatus
     *
     * @return bool
     */
    private function isNeedSecondReceipt($status, $isSendReceipt, $isSendSecondReceipt, $secondReceiptStatus)
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
}