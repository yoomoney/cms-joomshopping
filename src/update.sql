
INSERT INTO `#__jshopping_payment_method` (`payment_code`, `payment_class`, `payment_publish`, `payment_ordering`,
                                           `payment_type`, `price`, `price_type`, `tax_id`, `show_descr_in_email`,
                                           `name_en-GB`, `name_de-DE`)
VALUES ('YandexMoney20', 'pm_yandex_money', 1, 0, 2, 0.00, 0, 1, 0, 'Yandex.Money 2.0', 'Yandex.Money 2.0');

CREATE TABLE IF NOT EXISTS `#__ya_money_payments` (
    `order_id`          INTEGER  NOT NULL,
    `payment_id`        CHAR(36) NOT NULL,
    `status`            ENUM('pending', 'waiting_for_capture', 'succeeded', 'canceled') NOT NULL,
    `amount`            DECIMAL(11, 2) NOT NULL,
    `currency`          CHAR(3) NOT NULL,
    `payment_method_id` CHAR(36) NOT NULL,
    `paid`              ENUM('Y', 'N') NOT NULL,
    `created_at`        DATETIME NOT NULL,
    `captured_at`       DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    CONSTRAINT `' . DB_PREFIX . 'ya_money_payment_pk` PRIMARY KEY (`order_id`),
    CONSTRAINT `' . DB_PREFIX . 'ya_money_payment_unq_payment_id` UNIQUE (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8 COLLATE=utf8_general_ci;

UPDATE `#__jshopping_payment_method` SET `name_ru-RU` = 'Яндекс.Деньги 2.0' WHERE `payment_class` = 'pm_yandex_money';