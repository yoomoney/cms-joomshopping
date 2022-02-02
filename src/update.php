<?php
$db = JFactory:: getDbo();

$query = "CREATE TABLE IF NOT EXISTS `#__yoomoney_payments` (
    `order_id`          INTEGER  NOT NULL,
    `payment_id`        CHAR(36) NOT NULL,
    `status`            ENUM('pending', 'waiting_for_capture', 'succeeded', 'canceled') NOT NULL,
    `amount`            DECIMAL(11, 2) NOT NULL,
    `currency`          CHAR(3) NOT NULL,
    `payment_method_id` CHAR(36) NOT NULL,
    `paid`              ENUM('Y', 'N') NOT NULL,
    `created_at`        DATETIME NOT NULL,
    `captured_at`       DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    CONSTRAINT `' . DB_PREFIX . 'yoomoney_payment_pk` PRIMARY KEY (`order_id`),
    CONSTRAINT `' . DB_PREFIX . 'yoomoney_payment_unq_payment_id` UNIQUE (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8 COLLATE=utf8_general_ci";

$db->setQuery($query)->execute();

$query = $db->getQuery(true);

$queryParams = array(
    "payment_code",
    "payment_class",
    "payment_publish",
    "payment_ordering",
    "payment_type",
    "price",
    "price_type",
    "tax_id",
    "show_descr_in_email",
    "name_en-GB",
    "name_de-DE",
);

$valuesYm = array(
    $db->quote('YooMoney'),
    $db->quote('pm_yoomoney'),
    1,
    0,
    2,
    $db->quote('0.00'),
    0,
    1,
    0,
    $db->quote('YooMoney'),
    $db->quote('YooMoney'),
);

$valuesYmSbbol = array(
    $db->quote('YooMoneySbbol'),
    $db->quote('pm_yoomoney_sbbol'),
    1,
    0,
    2,
    $db->quote('0.00'),
    0,
    1,
    0,
    $db->quote('YooKassa: Sbbol'),
    $db->quote('YooKassa: Sbbol'),
);


$query->delete($db->quoteName("#__jshopping_payment_method"))->where(array($db->quoteName('payment_code') . ' = ' . $db->quote('YooMoney')));
$db->setQuery($query)->execute();

$query = $db->getQuery(true);
$query->delete($db->quoteName("#__jshopping_payment_method"))->where(array($db->quoteName('payment_code') . ' = ' . $db->quote('YooMoneySbbol')));
$db->setQuery($query)->execute();

$query = $db->getQuery(true);
$query->insert($db->quoteName("#__jshopping_payment_method"))
    ->columns($db->quoteName($queryParams))
    ->values(implode(",", $valuesYm));
$db->setQuery($query)->execute();

$query = $db->getQuery(true);
$query->insert($db->quoteName("#__jshopping_payment_method"))
    ->columns($db->quoteName($queryParams))
    ->values(implode(",", $valuesYmSbbol));
$db->setQuery($query)->execute();

$columns = $db->getTableColumns('#__jshopping_payment_method');

if (isset($columns['name_ru-RU'])) {
    $query = $db->getQuery(true);
    $query->update('#__jshopping_payment_method')
        ->set($db->quoteName('name_ru-RU') . ' = ' . $db->quote('ЮMoney'))
        ->where(array($db->quoteName('payment_code') . ' = ' . $db->quote('YooMoney')));
    $db->setQuery($query)->execute();

    $query = $db->getQuery(true);
    $query->update('#__jshopping_payment_method')
        ->set($db->quoteName('name_ru-RU') . ' = ' . $db->quote('ЮKassa: Сббол'))
        ->where(array($db->quoteName('payment_code') . ' = ' . $db->quote('YooMoneySbbol')));
    $db->setQuery($query)->execute();
}

/**
 * Создание таблицы возвратов
 */
$query = "CREATE TABLE IF NOT EXISTS `#__yoomoney_refunds` (
    `refund_id`        CHAR(36) NOT NULL,
    `order_id`          INTEGER  NOT NULL,
    `created_at`        DATETIME NOT NULL,
    CONSTRAINT `' . DB_PREFIX . 'yoomoney_refund_pk` PRIMARY KEY (`refund_id`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8 COLLATE=utf8_general_ci";

$db->setQuery($query)->execute();