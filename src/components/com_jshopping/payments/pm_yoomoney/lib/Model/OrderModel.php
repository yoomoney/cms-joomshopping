<?php

namespace YooMoney\Model;

use YooKassa\Model\PaymentInterface;
use YooKassa\Model\RefundInterface;

class OrderModel
{
    /**
     * @var \JDatabaseDriver
     */
    private $_db;

    public function __construct()
    {
        $this->_db = \JFactory::getDbo();
    }

    public function savePayment($orderId, $payment)
    {
        $query = $this->_db->getQuery(true);
        $query->select('payment_id')
            ->from('#__yoomoney_payments')
            ->where($this->_db->quoteName('order_id') . ' = ' . (int)$orderId);
        $this->_db->setQuery($query);
        $record = $this->_db->loadRow();
        if (empty($record)) {
            $this->insertPayment($orderId, $payment);
        } else {
            $this->updatePayment($orderId, $payment);
        }
    }

    /**
     * Возвращает payment id по id заказа
     *
     * @param $orderId
     * @return null|string
     */
    public function getPaymentIdByOrderId($orderId)
    {
        $query = $this->_db->getQuery(true);
        $query->select('payment_id')
            ->from('#__yoomoney_payments')
            ->where($this->_db->quoteName('order_id') . ' = ' . (int)$orderId);
        $this->_db->setQuery($query);
        $record = $this->_db->loadRow();
        if (empty($record)) {
            return null;
        }
        return $record[0];
    }

    /**
     * Возвращает id заказа по payment id
     *
     * @param $paymentId
     * @return null|string
     */
    public function getOrderIdByPaymentId($paymentId)
    {
        $query = $this->_db->getQuery(true);
        $query->select('order_id')
            ->from('#__yoomoney_payments')
            ->where(
                $this->_db->quoteName('payment_id') . ' = \'' . $paymentId . '\''
            );
        $this->_db->setQuery($query);
        $record = $this->_db->loadRow();
        if (empty($record)) {
            return null;
        }
        return $record[0];
    }

    /**
     * @param int $orderId
     * @param PaymentInterface $payment
     */
    private function insertPayment($orderId, $payment)
    {
        $paymentMethodId = '';
        if ($payment->getPaymentMethod() !== null) {
            $paymentMethodId = $payment->getPaymentMethod()->getId();
        }

        $query = $this->_db->getQuery(true);
        $query->clear()->insert('#__yoomoney_payments')
            ->columns(
                array(
                    $this->_db->quoteName('order_id'), $this->_db->quoteName('payment_id'),
                    $this->_db->quoteName('status'), $this->_db->quoteName('amount'),
                    $this->_db->quoteName('currency'), $this->_db->quoteName('payment_method_id'),
                    $this->_db->quoteName('paid'), $this->_db->quoteName('created_at')
                )
            )
            ->values(
                (int)$orderId . ','
                . $this->_db->quote($payment->getId()) . ","
                . $this->_db->quote($payment->getStatus()) . ","
                . $this->_db->quote($payment->getAmount()->getValue()) . ","
                . $this->_db->quote($payment->getAmount()->getCurrency()) . ","
                . $this->_db->quote($paymentMethodId) . ","
                . "'" . ($payment->getPaid() ? 'Y' : 'N') . "',"
                . $this->_db->quote($payment->getCreatedAt()->format('Y-m-d H:i:s'))
            );
        $this->_db->setQuery($query);
        try {
            $this->_db->execute();
        }
        catch (\JDatabaseExceptionExecuting $e) {
            \JError::raiseError(500, $e->getMessage());
        }
    }

    /**
     * @param int $orderId
     * @param PaymentInterface $payment
     */
    private function updatePayment($orderId, $payment)
    {
        $paymentMethodId = '';
        if ($payment->getPaymentMethod() !== null) {
            $paymentMethodId = $payment->getPaymentMethod()->getId();
        }

        $query = $this->_db->getQuery(true);
        $query->update('#__yoomoney_payments')
            ->set(
                $this->_db->quoteName('payment_id') . ' = ' . $this->_db->quote($payment->getId()) . ',' .
                $this->_db->quoteName('status') . ' = ' . $this->_db->quote($payment->getStatus()) . ',' .
                $this->_db->quoteName('amount') . ' = ' . $this->_db->quote($payment->getAmount()->getValue()) . ',' .
                $this->_db->quoteName('currency') . ' = ' . $this->_db->quote($payment->getAmount()->getCurrency()) . ',' .
                $this->_db->quoteName('payment_method_id') . ' = ' . $this->_db->quote($paymentMethodId) . ',' .
                $this->_db->quoteName('paid') . ' = \'' . ($payment->getPaid() ? 'Y' : 'N') . '\',' .
                $this->_db->quoteName('created_at') . ' = ' . $this->_db->quote($payment->getCreatedAt()->format('Y-m-d H:i:s'))
            )
            ->where($this->_db->quoteName('order_id') . ' = ' . (int)$orderId);
        $this->_db->setQuery($query);
        try {
            $this->_db->execute();
        }
        catch (\JDatabaseExceptionExecuting $e) {
            \JError::raiseError(500, $e->getMessage());
        }
    }

    /**
     * Возвращает запись из таблицы возвратов
     *
     * @param $refundId
     * @return null|array
     */
    public function getRefundById($refundId)
    {
        $query = $this->_db->getQuery(true);
        $query->select('*')
            ->from('#__yoomoney_refunds')
            ->where(
                $this->_db->quoteName('refund_id') . ' = \'' . $refundId . '\''
            );
        $this->_db->setQuery($query);
        $record = $this->_db->loadRow();
        if (empty($record)) {
            return null;
        }
        return $record;
    }

    /**
     * Добавляет запись в таблицу возвратов
     *
     * @param int $orderId
     * @param RefundInterface $refund
     */
    public function insertRefund($orderId, $refund)
    {
        $query = $this->_db->getQuery(true);
        $query->clear()->insert('#__yoomoney_refunds')
            ->columns(
                array(
                    $this->_db->quoteName('refund_id'),
                    $this->_db->quoteName('order_id'),
                    $this->_db->quoteName('created_at')
                )
            )
            ->values(
                $this->_db->quote($refund->getId()) . ","
                . (int)$orderId . ','
                . $this->_db->quote($refund->getCreatedAt()->format('Y-m-d H:i:s'))
            );
        $this->_db->setQuery($query);
        $this->_db->execute();
    }
}