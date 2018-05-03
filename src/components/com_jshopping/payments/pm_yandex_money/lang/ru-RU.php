<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

defined('_JEXEC') or die();

//определяем константы для русского языка
define('_JSHOP_YM_LICENSE_TEXT',"Для работы с модулем нужно подключить магазин к <a target=\"_blank\" href=\"https://kassa.yandex.ru/\">Яндекс.Кассе</a>.");
define('_JSHOP_YM_VERSION_DESCRIPTION','Версия модуля ');

define('_JSHOP_YM_TAB_MONEY', 'Яндекс.Деньги');
define('_JSHOP_YM_TAB_KASSA', 'Яндекс.Касса');
define('_JSHOP_YM_TAB_PAYMENTS', 'Яндекс.Платежка');

define('_JSHOP_YM_KASSA_ON', 'Включить приём платежей через Яндекс.Кассу');
define('_JSHOP_YM_KASSA_TEST', 'Тестовый режим');
define('_JSHOP_YM_KASSA_WORK', 'Реальный режим');
define('_JSHOP_YM_KASSA_HELP_CHECKURL', 'Этот адрес понадобится, только если его попросят специалисты Яндекс.Кассы');
define('_JSHOP_YM_KASSA_HELP_SUCCESSURL', 'Включите «Использовать страницы успеха и ошибки с динамическими адресами» в настройках личного кабинета Яндекс.Кассы');
define('_JSHOP_YM_KASSA_HELP_DYNAMICURL', 'Страницы с динамическими адресами');

define('_JSHOP_YM_KASSA_HEAD_LK', 'Параметры из личного кабинета Яндекс.Кассы');
define('_JSHOP_YM_KASSA_SHOP_ID_LABEL', 'shopId');
define('_JSHOP_YM_KASSA_SHOP_ID_DESCRIPTION', 'Скопируйте shopId из личного кабинета Яндекс.Кассы');
define('_JSHOP_YM_KASSA_PASSWORD_LABEL', 'Секретный ключ');
define('_JSHOP_YM_KASSA_PASSWORD_DESCRIPTION', 'Выпустите и активируйте секретный ключ в личном кабинете Яндекс.Кассы. Потом скопируйте его сюда.');
define('_JSHOP_YM_KASSA_PARAMS', 'Shop ID, scid, ShopPassword можно посмотреть в <a href=\'https://money.yandex.ru/joinups\' target=\'_blank\'>личном кабинете</a> после подключения Яндекс.Кассы.');

define('_JSHOP_YM_KASSA_PAYMODE_HEAD', 'Настройка сценария оплаты');
define('_JSHOP_YM_KASSA_PAYMODE_LABEL', 'Выбор способа оплаты');
define('_JSHOP_YM_KASSA_SEND_RECEIPT_LABEL', 'Отправлять в Яндекс.Кассу данные для чеков (54-ФЗ)');
define('_JSHOP_YM_KASSA_PAYMODE_KASSA', 'На стороне Кассы');
define('_JSHOP_YM_KASSA_PAYMODE_SHOP', 'На стороне магазина');
define('_JSHOP_YM_KASSA_PAYMODE_LINK', '<a href=\'https://tech.yandex.ru/money/doc/payment-solution/payment-form/payment-form-docpage/\' target=\'_blank\'>Подробнее о сценариях оплаты</a>');
define('_JSHOP_YM_KASSA_SELECT_TEXT', 'Отметьте способы оплаты, которые указаны в вашем договоре с Яндекс.Деньгами');
define('_JSHOP_YM_KASSA_CREDENTIALS_ERROR', 'Проверьте shopId и Секретный ключ — где-то есть ошибка. А лучше скопируйте их прямо из <a href="https://kassa.yandex.ru/my" target="_blank">личного кабинета Яндекс.Кассы</a>');
define('_JSHOP_YM_KASSA_TEST_WARNING', 'Вы включили тестовый режим приема платежей. Проверьте, как проходит оплата, и напишите менеджеру Кассы. Он выдаст рабочие shopId и Секретный ключ. <a href="https://yandex.ru/support/checkout/payments/api.html#api__04" target="_blank">Инструкция</a>');
define('_JSHOP_YM_METHOD_YANDEX_MONEY_DESCRIPTION', 'Яндекс.Деньги');
define('_JSHOP_YM_METHOD_CARDS_DESCRIPTION', 'Банковские карты');
define('_JSHOP_YM_METHOD_BANK_CARD_DESCRIPTION', 'Банковские карты');
define('_JSHOP_YM_METHOD_CASH_DESCRIPTION', 'Наличные через терминалы');
define('_JSHOP_YM_METHOD_MOBILE_BALANCE_DESCRIPTION', 'Баланс мобильного');
define('_JSHOP_YM_METHOD_WEBMONEY_DESCRIPTION', 'Кошелек WebMoney');
define('_JSHOP_YM_METHOD_ALFABANK_DESCRIPTION', 'Альфа-Клик');
define('_JSHOP_YM_METHOD_SBERBANK_DESCRIPTION', 'Сбербанк Онлайн');
define('_JSHOP_YM_METHOD_MA_DESCRIPTION', 'MasterPass');
define('_JSHOP_YM_METHOD_PB_DESCRIPTION', 'Интернет-банк Промсвязьбанка');
define('_JSHOP_YM_METHOD_QIWI_DESCRIPTION', 'QIWI Wallet');
define('_JSHOP_YM_METHOD_QP_DESCRIPTION', 'Доверительный платеж (Куппи.ру)');
define('_JSHOP_YM_METHOD_MP_DESCRIPTION', 'Мобильный терминал');
define('_JSHOP_YM_METHOD_INSTALLMENTS_DESCRIPTION', 'Заплатить по частям');
define('_JSHOP_YM_METHOD_INSTALLMENTS_AMOUNT', ' (%s ₽ в месяц)');
define('_JSHOP_YM_KASSA_MPOS_LABEL', 'Страница успеха для способа «Оплата картой при доставке»');
define('_JSHOP_YM_KASSA_MPOS_HELP', 'Это страница с информацией о доставке. Укажите на ней, когда привезут товар и как его можно будет оплатить');

define('_JSHOP_YM_MONEY_HEAD', 'Для работы с модулем нужно <a href=\'https://money.yandex.ru/new\' target=\'_blank\'>открыть кошелек</a> на Яндексе и
					<a href=\'https://sp-money.yandex.ru/myservices/online.xml\' target=\'_blank\'>зарегистрировать приложение</a> на сайте Яндекс.Денег');
define('_JSHOP_YM_MONEY_ON', 'Включить прием платежей в кошелек на Яндексе');
define('_JSHOP_YM_MONEY_REDIRECT_HELP', 'Скопируйте эту ссылку в поле Redirect URL на <a href=\'https://sp-money.yandex.ru/myservices/online.xml\' target=\'_blank\'>странице регистрации приложения</a>.');

define('_JSHOP_YM_MONEY_SET_HEAD', 'Настройки приема платежей');
define('_JSHOP_YM_MONEY_WALLET', 'Номер кошелька');
define('_JSHOP_YM_MONEY_PSW', 'Секретное слово');

define('_JSHOP_YM_MONEY_SELECT_HEAD', 'Настройка сценария оплаты');
define('_JSHOP_YM_MONEY_SELECT_LABEL', 'Способы оплаты');
define('_JSHOP_YM_METHOD_YM2_DESCRIPTION', 'Кошелек Яндекс.Деньги');
define('_JSHOP_YM_METHOD_CARDS2_DESCRIPTION', 'Банковская карта');

define('_JSHOP_YM_COMMON_HEAD', 'Дополнительные настройки для администратора');
define('_JSHOP_YM_COMMON_STATUS', 'Статус заказа после оплаты');

define('_JSHOP_YM_PAYMENTS_HEAD', 'Это платежная форма на ваш сайт. Позволяет принимать платежи на счет компании — с
карт и из кошельков Яндекс.Денег, без договора.<br />
Для настройки нужен ID формы: он придет в письме, когда вы
<a href="https://money.yandex.ru/fastpay/" target="_blank">соберете форму в конструкторе</a>.');

define('_JSHOP_YM_PAYMENTS_ON', 'Включить прием платежей через Платежку');
define('_JSHOP_YM_PAYMENTS_ID_LABEL', 'ID формы');
define('_JSHOP_YM_PAYMENTS_DESCRIPTION_LABEL', 'Назначение платежа');
define('_JSHOP_YM_PAYMENTS_DESCRIPTION_PLACEHOLDER', 'Номер заказа %order_id%. Оплата через Яндекс.Платежку');
define('_JSHOP_YM_PAYMENTS_DESCRIPTION_INFO', 'Назначение будет в платежном поручении от банка. Напишите в нем всё,
что поможет отличить заказ, который оплатили через Платежку.');
define('_JSHOP_YM_PAYMENTS_STATUS_LABEL', 'Статус заказа');
define('_JSHOP_YM_PAYMENTS_STATUS_INFO', 'Статус должен показать, что результат платежа неизвестен: о том, что клиент
заплатил, можно узнать только из письма от Платежки или в своем банке.');
define('_JSHOP_YM_PAYMENTS_FIO_LABEL', 'ФИО плательщика');
define('_JSHOP_YM_PAYMENTS_CONFIRM_LABEL', 'Далее');
define('_JSHOP_YM_PAYMENTS_EMPTY_NAME_ERROR', 'Укажите ФИО плательщика');
define('_JSHOP_YM_PAYMENTS_INVALID_NAME_ERROR', 'ФИО плательщика должно состоять из фамилии, имени и отчества, разделённых пробелами');

// версия 2.х
define('_JSHOP_YM_LICENSE','Лицензионный договор:');
define('_JSHOP_YM_LICENSE_TEXT2',"<p>Любое использование Вами программы означает полное и безоговорочное принятие Вами условий лицензионного договора, размещенного по адресу <a href='https://money.yandex.ru/doc.xml?id=527132' target='_blank'>https://money.yandex.ru/doc.xml?id=527132</a> (далее – «Лицензионный договор»). Если Вы не принимаете условия Лицензионного договора в полном объёме, Вы не имеете права использовать программу в каких-либо целях.</p>");
define('_JSHOP_YM_TESTMODE_DESCRIPTION', 'Использовать в тестовом режиме?');
define('_JSHOP_YM_MODE_DESCRIPTION', 'Способ приема платежей:');
define('_JSHOP_YM_MODE1_DESCRIPTION', 'Яндекс.Денеги');
define('_JSHOP_YM_MODE2_DESCRIPTION', 'Яндекс.Касса (выбор оплаты на стороне сайта)');
define('_JSHOP_YM_MODE3_DESCRIPTION', 'Яндекс.Касса (выбор оплаты на стороне Яндекс.Кассы)');
define('_JSHOP_YM_MODE4_DESCRIPTION', 'Яндекс.Платежка (банковские карты, кошелек)');
define('_JSHOP_YM_REG_IND', 'Если у вас нет аккаунта в Яндекс-Деньги, то следует зарегистрироваться тут - <a href="https://money.yandex.ru/" target="_blank">https://money.yandex.ru/</a><br/><b>ВАЖНО!</b> Вам нужно будет указать ссылку для приема HTTP уведомлений здесь - <a href="https://sp-money.yandex.ru/myservices/online.xml">https://sp-money.yandex.ru/myservices/online.xml</a>');

define('_JSHOP_YM_REG_ORG', 'Для работы с модулем необходимо <a href="https://money.yandex.ru/joinups/">подключить магазин к Яндек.Кассе</a>. После подключения вы получите параметры для приема платежей (идентификатор магазина — shopId и номер витрины — scid).');
define('_JSHOP_YM_METHODS_DESCRIPTION', 'Укажите необходимые способы оплаты');
define('_JSHOP_YM_PASSWORD', 'Секретное слово (shopPassword) для обмена сообщениями:');
define('_JSHOP_YM_SHOPID', 'Идентификатор вашего магазина в Яндекс.Деньгах (ShopID):');
define('_JSHOP_YM_SCID', 'Идентификатор витрины вашего магазина в Яндекс.Деньгах (scid):');
define('_JSHOP_YM_PARAM', 'Название параметра');
define('_JSHOP_YM_VALUE', 'Значение');
define('_JSHOP_YM_RETURNURL', 'Динамический');
define('_JSHOP_YM_ACCOUNT_DESCRIPTION', 'Номер кошелька Яндекс:');

define('_JSHOP_YM_PAY', 'Оплатить!');
define('_JSHOP_YM_TRANSACTION_END', 'Статус заказа для успешных транзакций');
define('_JSHOP_YM_TEXT_MPOS', 'Страница с инструкцией для платеждей через мобильный терминал!');