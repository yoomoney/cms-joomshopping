<?php

namespace YooMoney\Helpers;

/**
 * Класс методов для работы с заказами
 */
class OrderHelper
{
    /**
     * @var JVersionDependenciesHelper
     */
    private $dependenciesHelper;

    public function __construct()
    {
        $this->dependenciesHelper = new JVersionDependenciesHelper();
    }

    /**
     * Сохраняет запись в истории к заказу
     *
     * @param $order
     * @param $comments
     * @return mixed
     */
    public function saveOrderHistory($order, $comments)
    {
        $history                    = \JSFactory::getTable('orderHistory', 'jshop');
        $history->order_id          = $order->order_id;
        $history->order_status_id   = $order->order_status;
        $history->status_date_added = $this->dependenciesHelper->getJsDate();
        $history->customer_notify   = 0;
        $history->comments          = $comments;

        return $history->store();
    }
}