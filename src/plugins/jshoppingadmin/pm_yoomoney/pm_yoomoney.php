<?php

use YooKassa\Model\PaymentStatus;
use YooKassa\Request\Payments\Payment\CreateCaptureRequest;

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).'/../../../components/com_jshopping/payments/payment.php';
require_once dirname(__FILE__) . '/../../../components/com_jshopping/payments/pm_yoomoney/pm_yoomoney.php';
require_once dirname(__FILE__) . '/../../../components/com_jshopping/payments/pm_yoomoney/lib/Model/KassaSecondReceiptModel.php';

class plgJshoppingAdminPm_yoomoney extends JPlugin
{

    public function onBeforeChangeOrderStatusAdmin($order_id, &$status)
    {
        $paymentMethod = JSFactory::getTable('paymentmethod', 'jshop');

        $all_payment_methods = $paymentMethod->getAllPaymentMethods();
        $pm_kassa            = null;

        foreach ($all_payment_methods as $pm) {
            $scriptName = ($pm->scriptname != '') ? $pm->scriptname : $pm->payment_class;
            if ($scriptName !== 'pm_yoomoney') {
                continue;
            }
            $pm_kassa = $pm;
            break;
        }

        if (!$pm_kassa) {
            return;
        }

        $paymentMethod->load($pm_kassa->payment_id);
        $parseString = new parseString($pm_kassa->payment_params);
        $pmconfig    = $parseString->parseStringToParams();

        $pm_yoomoney = new pm_yoomoney();
        $kassa           = $pm_yoomoney->getKassaPaymentMethod($pmconfig);

        $pm_yoomoney->sendSecondReceipt($order_id, $pmconfig, $status);

        if (!$kassa->isEnableHoldMode()) {
            return;
        }

        $onHoldStatus   = $pmconfig['yookassa_hold_mode_on_hold_status'];
        $cancelStatus   = $pmconfig['yookassa_hold_mode_cancel_status'];
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
            $payment   = $apiClient->getPaymentInfo($pm_yoomoney->getOrderModel()->getPaymentIdByOrderId($order->order_id));
            try {
                $builder = CreateCaptureRequest::builder();
                $builder->setAmount($order->order_total);

                if ($kassa->isSendReceipt()) {
                    $kassa->factoryReceipt($builder, $order->getAllItems(), $order);
                }

                $request = $builder->build();
                $payment = $apiClient->capturePayment($request, $payment->getId());
            } catch (\Exception $e) {
                $pm_yoomoney->log('error', 'Capture error: '.$e->getMessage());
            }

            if (!$payment || $payment->getStatus() !== PaymentStatus::SUCCEEDED) {
                $status = $onHoldStatus;
                $pm_yoomoney->saveOrderHistory($order, _JSHOP_YOO_HOLD_MODE_CAPTURE_PAYMENT_FAIL);
                $pm_yoomoney->log('error', 'Capture payment error: capture failed');
                return;
            }
            $order->order_status = $completeStatus;
            $pm_yoomoney->saveOrderHistory($order, _JSHOP_YOO_HOLD_MODE_CAPTURE_PAYMENT_SUCCESS);


            $pm_yoomoney->sendSecondReceipt($order_id, $pmconfig, $completeStatus);

        }

        if ($status === $cancelStatus) {
            $apiClient = $kassa->getClient();
            $payment   = null;

            try {
                $payment = $apiClient->cancelPayment($order->transaction);
            } catch (\Exception $e) {
                $pm_yoomoney->log('error', 'Capture error: '.$e->getMessage());
            }

            if (!$payment || $payment->getStatus() !== PaymentStatus::CANCELED) {
                $status = $onHoldStatus;
                $pm_yoomoney->saveOrderHistory($order, _JSHOP_YOO_HOLD_MODE_CANCEL_PAYMENT_FAIL);
                $pm_yoomoney->log('error', 'Cancel payment error: cancel failed');
                return;
            }
            $order->order_status = $cancelStatus;
            $pm_yoomoney->saveOrderHistory($order, _JSHOP_YOO_HOLD_MODE_CANCEL_PAYMENT_SUCCESS);
        }
    }

}
