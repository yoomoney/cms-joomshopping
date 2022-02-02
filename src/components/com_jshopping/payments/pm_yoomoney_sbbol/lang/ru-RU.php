<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YooMoney
 * @copyright Copyright (C) 2020 YooMoney. All rights reserved.
 */

defined('_JEXEC') or die();

//определяем константы для русского языка
define('_JSHOP_YOO_LICENSE_TEXT',"Для работы с модулем нужно подключить магазин к <a target=\"_blank\" href=\"https://yookassa.ru/\">ЮKassa</a>.");
define('_JSHOP_YOO_VERSION_DESCRIPTION','Версия модуля ');

define('_JSHOP_YOO_TAB_MONEY', 'ЮMoney');
define('_JSHOP_YOO_TAB_KASSA', 'ЮKassa');

define('_JSHOP_YOO_KASSA_ON', 'Включить приём платежей через ЮKassa');
define('_JSHOP_YOO_KASSA_TEST', 'Тестовый режим');
define('_JSHOP_YOO_KASSA_WORK', 'Реальный режим');
define('_JSHOP_YOO_KASSA_HELP_CHECKURL', 'Этот адрес понадобится, только если его попросят специалисты ЮKassa');
define('_JSHOP_YOO_KASSA_HELP_SUCCESSURL', 'Включите «Использовать страницы успеха и ошибки с динамическими адресами» в настройках личного кабинета ЮKassa');
define('_JSHOP_YOO_KASSA_HELP_DYNAMICURL', 'Страницы с динамическими адресами');

define('_JSHOP_YOO_KASSA_HEAD_LK', 'Параметры из личного кабинета ЮKassa');
define('_JSHOP_YOO_KASSA_SHOP_ID_LABEL', 'shopId');
define('_JSHOP_YOO_KASSA_SHOP_ID_DESCRIPTION', 'Скопируйте shopId из личного кабинета ЮKassa');
define('_JSHOP_YOO_KASSA_PASSWORD_LABEL', 'Секретный ключ');
define('_JSHOP_YOO_KASSA_PASSWORD_DESCRIPTION', 'Выпустите и активируйте секретный ключ в личном кабинете ЮKassa. Потом скопируйте его сюда.');
define('_JSHOP_YOO_KASSA_PARAMS', 'Shop ID, scid, ShopPassword можно посмотреть в <a href=\'https://yookassa.ru/joinups\' target=\'_blank\'>личном кабинете</a> после подключения ЮKassa.');

define('_JSHOP_YOO_KASSA_PAYMODE_HEAD', 'Настройка сценария оплаты');
define('_JSHOP_YOO_KASSA_PAYMODE_LABEL', 'Выбор способа оплаты');
define('_JSHOP_YOO_KASSA_SEND_RECEIPT_LABEL', 'Отправлять в ЮKassa данные для чеков (54-ФЗ)');
define('_JSHOP_YOO_DESCRIPTION_TITLE', 'Описание платежа');
define('_JSHOP_YOO_DESCRIPTION_DEFAULT_PLACEHOLDER', 'Оплата заказа №%order_id%');
define('_JSHOP_YOO_DESCRIPTION_HELP', 'Это описание транзакции, которое пользователь увидит при оплате, а вы — в личном кабинете ЮKassa. Например, «Оплата заказа №72».<br>
Чтобы в описание подставлялся номер заказа (как в примере), поставьте на его месте %order_id% (Оплата заказа №%order_id%).<br>
Ограничение для описания — 128 символов.');
define('_JSHOP_YOO_ENABLE_HOLD_MODE', 'Включить отложенную оплату');
define('_JSHOP_YOO_ENABLE_HOLD_MODE_HELP', 'Если опция включена, платежи с карт проходят в 2 этапа: у клиента сумма замораживается, и вам вручную нужно подтвердить её списание – через панель администратора. <a href="https://yookassa.ru/features/#9" target="_blank">Подробное описание Холдирования.</a>');
define('_JSHOP_YOO_HOLD_MODE_STATUSES', 'Какой статус присваивать заказу, если он:');
define('_JSHOP_YOO_HOLD_MODE_ON_HOLD_STATUS', 'ожидает подтверждения');
define('_JSHOP_YOO_HOLD_MODE_ON_HOLD_STATUS_HELP', 'заказ переходит в этот статус при поступлении и остается в нем пока оператор магазина не подтвердит или не отменит платеж');
define('_JSHOP_YOO_HOLD_MODE_CANCEL_STATUS', 'отменён');
define('_JSHOP_YOO_HOLD_MODE_CANCEL_STATUS_HELP', 'заказ переходит в этот статус после отмены платежа');
define('_JSHOP_YOO_HOLD_MODE_COMMENT_ON_HOLD', 'Поступил новый платёж. Он ожидает подтверждения до %1$s, после чего автоматически отменится');
define('_JSHOP_YOO_HOLD_MODE_CAPTURE_PAYMENT_SUCCESS', 'Вы подтвердили платёж в ЮKassa.');
define('_JSHOP_YOO_HOLD_MODE_CAPTURE_PAYMENT_FAIL', 'Платёж не подтвердился. Попробуйте ещё раз.');
define('_JSHOP_YOO_HOLD_MODE_CANCEL_PAYMENT_SUCCESS', 'Вы отменили платёж в ЮKassa. Деньги вернутся клиенту.');
define('_JSHOP_YOO_HOLD_MODE_CANCEL_PAYMENT_FAIL', 'Платёж не отменился. Попробуйте ещё раз.');
define('_JSHOP_YOO_KASSA_PAYMODE_KASSA', 'На стороне ЮKassa');
define('_JSHOP_YOO_KASSA_PAYMODE_SHOP', 'На стороне магазина');
define('_JSHOP_YOO_KASSA_PAYMODE_LINK', '<a href=\'https://yookassa.ru/docs/payment-solution/payment-form/basics\' target=\'_blank\'>Подробнее о сценариях оплаты</a>');
define('_JSHOP_YOO_KASSA_SELECT_TEXT', 'Отметьте способы оплаты, которые указаны в вашем договоре с ЮMoney');
define('_JSHOP_YOO_KASSA_CREDENTIALS_ERROR', 'Проверьте shopId и Секретный ключ — где-то есть ошибка. А лучше скопируйте их прямо из <a href="https://yookassa.ru/my/" target="_blank">личного кабинета ЮKassa</a>');
define('_JSHOP_YOO_KASSA_TEST_WARNING', 'Вы включили тестовый режим приёма платежей. Проверьте, как проходит оплата, и напишите менеджеру ЮKassa. Он выдаст рабочие shopId и Секретный ключ. <a href="https://yookassa.ru/docs/support/payments/onboarding/integration#api__04" target="_blank">Инструкция</a>');
define('_JSHOP_YOO_METHOD_YOO_MONEY_DESCRIPTION', 'ЮMoney');
define('_JSHOP_YOO_METHOD_CARDS_DESCRIPTION', 'Банковские карты');
define('_JSHOP_YOO_METHOD_BANK_CARD_DESCRIPTION', 'Банковские карты');
define('_JSHOP_YOO_METHOD_CASH_DESCRIPTION', 'Наличные через терминалы');
define('_JSHOP_YOO_METHOD_MOBILE_BALANCE_DESCRIPTION', 'Баланс мобильного');
define('_JSHOP_YOO_METHOD_WEBMONEY_DESCRIPTION', 'Кошелёк WebMoney');
define('_JSHOP_YOO_METHOD_ALFABANK_DESCRIPTION', 'Альфа-Клик');
define('_JSHOP_YOO_METHOD_SBERBANK_DESCRIPTION', 'SberPay');
define('_JSHOP_YOO_METHOD_MA_DESCRIPTION', 'MasterPass');
define('_JSHOP_YOO_METHOD_PB_DESCRIPTION', 'Интернет-банк Промсвязьбанка');
define('_JSHOP_YOO_METHOD_QIWI_DESCRIPTION', 'QIWI Wallet');
define('_JSHOP_YOO_METHOD_QP_DESCRIPTION', 'Доверительный платеж (Куппи.ру)');
define('_JSHOP_YOO_METHOD_MP_DESCRIPTION', 'Мобильный терминал');
define('_JSHOP_YOO_METHOD_INSTALLMENTS_DESCRIPTION', 'Заплатить по частям');
define('_JSHOP_YOO_METHOD_INSTALLMENTS_AMOUNT', ' (%s ₽ в месяц)');
define('_JSHOP_YOO_KASSA_MPOS_LABEL', 'Страница успеха для способа «Оплата картой при доставке»');
define('_JSHOP_YOO_KASSA_MPOS_HELP', 'Это страница с информацией о доставке. Укажите на ней, когда привезут товар и как его можно будет оплатить');

define('_JSHOP_YOO_MONEY_HEAD', 'Для работы с модулем нужно <a href="https://yoomoney.ru/" target="_blank">открыть кошелёк</a> на ЮMoney и
					<a href="https://yoomoney.ru/transfer/myservices/http-notification" target="_blank">зарегистрировать приложение</a> на сайте ЮMoney');
define('_JSHOP_YOO_MONEY_ON', 'Включить приём платежей в кошелёк на ЮMoney');
define('_JSHOP_YOO_MONEY_REDIRECT_HELP', 'Скопируйте эту ссылку в поле Redirect URL на <a href=\'https://yoomoney.ru/transfer/myservices/http-notification\' target=\'_blank\'>странице регистрации приложения</a>.');

define('_JSHOP_YOO_MONEY_SET_HEAD', 'Настройки приёма платежей');
define('_JSHOP_YOO_MONEY_WALLET', 'Номер кошелька');
define('_JSHOP_YOO_MONEY_PSW', 'Секретное слово');

define('_JSHOP_YOO_MONEY_SELECT_HEAD', 'Настройка сценария оплаты');
define('_JSHOP_YOO_MONEY_SELECT_LABEL', 'Способы оплаты');
define('_JSHOP_YOO_METHOD_YM2_DESCRIPTION', 'Кошелёк ЮMoney');
define('_JSHOP_YOO_METHOD_CARDS2_DESCRIPTION', 'Банковская карта');

define('_JSHOP_YOO_COMMON_HEAD', 'Дополнительные настройки для администратора');
define('_JSHOP_YOO_COMMON_STATUS', 'Статус заказа после оплаты');

define('_JSHOP_YOO_PAYMENTS_STATUS_LABEL', 'Статус заказа');
define('_JSHOP_YOO_PAYMENTS_STATUS_INFO', 'Статус должен показать, что результат платежа неизвестен: о том, что клиент
заплатил, можно узнать только из письма от Платежки или в своем банке.');
define('_JSHOP_YOO_PAYMENTS_FIO_LABEL', 'ФИО плательщика');
define('_JSHOP_YOO_PAYMENTS_CONFIRM_LABEL', 'Далее');
define('_JSHOP_YOO_PAYMENTS_EMPTY_NAME_ERROR', 'Укажите ФИО плательщика');
define('_JSHOP_YOO_PAYMENTS_INVALID_NAME_ERROR', 'ФИО плательщика должно состоять из фамилии, имени и отчества, разделённых пробелами');

// версия 2.х
define('_JSHOP_YOO_LICENSE','Лицензионный договор:');
define('_JSHOP_YOO_LICENSE_TEXT2',"<p>Любое использование Вами программы означает полное и безоговорочное принятие Вами условий лицензионного договора, размещенного по адресу <a href='https://yoomoney.ru/doc.xml?id=527132' target='_blank'>https://yoomoney.ru/doc.xml?id=527132</a> (далее – «Лицензионный договор»). Если Вы не принимаете условия Лицензионного договора в полном объёме, Вы не имеете права использовать программу в каких-либо целях.</p>");
define('_JSHOP_YOO_TESTMODE_DESCRIPTION', 'Использовать в тестовом режиме?');
define('_JSHOP_YOO_MODE_DESCRIPTION', 'Способ приёма платежей:');
define('_JSHOP_YOO_MODE1_DESCRIPTION', 'ЮMoney');
define('_JSHOP_YOO_MODE2_DESCRIPTION', 'ЮKassa (выбор оплаты на стороне сайта)');
define('_JSHOP_YOO_MODE3_DESCRIPTION', 'ЮKassa (выбор оплаты на стороне ЮKassa)');
define('_JSHOP_YOO_REG_IND', 'Если у вас нет аккаунта в ЮMoney, то следует зарегистрироваться тут - <a href="https://yoomoney.ru/" target="_blank">https://yoomoney.ru/</a><br/><b>ВАЖНО!</b> Вам нужно будет указать ссылку для приёма HTTP уведомлений здесь - <a href="https://yoomoney.ru/transfer/myservices/http-notification">https://yoomoney.ru/transfer/myservices/http-notification</a>');

define('_JSHOP_YOO_REG_ORG', 'Для работы с модулем необходимо <a href="https://yookassa.ru/joinups">подключить магазин к ЮKassa</a>. После подключения вы получите параметры для приёма платежей (идентификатор магазина — shopId и номер витрины — scid).');
define('_JSHOP_YOO_METHODS_DESCRIPTION', 'Укажите необходимые способы оплаты');
define('_JSHOP_YOO_PASSWORD', 'Секретное слово (shopPassword) для обмена сообщениями:');
define('_JSHOP_YOO_SHOPID', 'Идентификатор вашего магазина в ЮMoney (ShopID):');
define('_JSHOP_YOO_SCID', 'Идентификатор витрины вашего магазина в ЮMoney (scid):');
define('_JSHOP_YOO_PARAM', 'Название параметра');
define('_JSHOP_YOO_VALUE', 'Значение');
define('_JSHOP_YOO_RETURNURL', 'Динамический');
define('_JSHOP_YOO_ACCOUNT_DESCRIPTION', 'Номер кошелька ЮMoney:');

define('_JSHOP_YOO_PAY', 'Оплатить!');
define('_JSHOP_YOO_TRANSACTION_END', 'Статус заказа для успешных транзакций');
define('_JSHOP_YOO_TEXT_MPOS', 'Страница с инструкцией для платеждей через мобильный терминал!');

//updater
define('_JSHOP_YOO_UPDATER_ERROR_RESTORE', 'Не удалось восстановить модуль из резервной копии: ');
define('_JSHOP_YOO_UPDATER_SUCCESS_MESSAGE', 'Модуль был успешно восстановлен из резервной копии: ');
define('_JSHOP_YOO_UPDATER_ERROR_REMOVE', 'Не был передан удаляемый файл резервной копии');
define('_JSHOP_YOO_ERROR_BACKUP_NOT_FOUND', 'Файл резервной копии %s не найден');
define('_JSHOP_YOO_ERROR_REMOVE_BACKUP', 'Не удалось удалить файл резервной копии ');
define('_JSHOP_YOO_SUCCESS_REMOVE_BECKUP', 'Файл резервной копии %s был успешно удалён');
define('_JSHOP_YOO_SUCCESS_UPDATE_VERSION', 'Версия модуля %s (%s) была успешно загружена и установлена');
define('_JSHOP_YOO_ERROR_UNPACK_NEW_VERSION', 'Не удалось распаковать загруженный архив %s, подробную информацию о произошедшей ошибке можно найти в логах модуля');
define('_JSHOP_YOO_ERROR_CREATE_BACKUP', 'Не удалось создать резервную копию установленной версии модуля, подробную информацию о произошедшей ошибке можно найти в логах модуля');
define('_JSHOP_YOO_ERROR_DOWNLOAD_NEW_VERSION', 'Не удалось загрузить архив с новой версией, подробную информацию о произошедшей ошибке можно найти в логах модуля');
define('_JSHOP_YOO_FAILED_CREATE_DIRECTORY', 'Не удалось создать директорию ');
define('_JSHOP_YOO_FAILED_DOWNLOAD_UPDATE', 'Не удалось загрузить архив с обновлением');
define('_JSHOP_YOO_UPDATER_HEADER_TEXT', ' Здесь будут появляться новые версии модуля — с новыми возможностями или с исправленными ошибками. Чтобы установить новую версию модуля, нажмите кнопку «Обновить».');
define('_JSHOP_YOO_UPDATER_ABOUT', 'О модуле:');
define('_JSHOP_YOO_UPDATER_CURRENT_VERSION', 'Установленная версия модуля —');
define('_JSHOP_YOO_UPDATER_LAST_VERSION', 'Последняя версия модуля —');
define('_JSHOP_YOO_UPDATER_LAST_CHECK', 'Последняя проверка наличия новых версий —');
define('_JSHOP_YOO_UPDATER_CHECK', 'Проверить наличие обновлений');
define('_JSHOP_YOO_HISTORY_LABEL', 'История изменений:');
define('_JSHOP_YOO_UPDATE_LABEL', 'Обновить');
define('_JSHOP_YOO_INSTALL_MESSAGE', 'Установлена последняя версия модуля.');
define('_JSHOP_YOO_BACKUPS_LABEL', 'Резервные копии');
define('_JSHOP_YOO_MODULE_VERSION_LABEL', 'Версия модуля');
define('_JSHOP_YOO_BACKUP_DATE_CREATE', 'Дата создания');
define('_JSHOP_YOO_BACKUP_FILE_NAME', 'Имя файла');
define('_JSHOP_YOO_BACKUP_FILE_SIZE', 'Размер файла');
define('_JSHOP_YOO_UPDATER_RESTORE', 'Восстановить');
define('_JSHOP_YOO_UPDATER_DELETE', 'Удалить');
define('_JSHOP_YOO_UPDATER_APPROVE_ACTION_MESSAGE', 'Вы действительно хотите обновить модуль до последней версии?');
define('_JSHOP_YOO_UPDATER_APPROVE_DELETE_MESSAGE', 'Вы действительно хотите удалить резервную копию');
define('_JSHOP_YOO_UPDATER_APPROVE_RESTORE_MESSAGE', 'Вы действительно хотите восстановить резервную копию');
define('_JSHOP_YOO_UPDATER_TEXT_HEADER', 'Обновление модуля');
define('_JSHOP_YOO_UPDATER_ABOUT_TEXT', 'Здесь будут появляться новые версии модуля — с новыми возможностями или с исправленными ошибками.');
define('_JSHOP_YOO_UPDATER_DISABLED_TEXT', ' К сожалению функция обновления модуля недоступна');
define('_JSHOP_YOO_UPDATER_CAUSE_ZIP_CURL', 'так как для не установлены расширения <strong>"zip"</strong> и <strong>"curl"</strong>.');
define('_JSHOP_YOO_UPDATER_CAUSE_ZIP', 'так как для не установлено расширение <strong>"zip"</strong>.');
define('_JSHOP_YOO_UPDATER_CAUSE_CURL', 'так как для не установлено расширение <strong>"curl"</strong>.');



define('_JSHOP_YOO_WAITING_FOR_CAPTURE', 'Ожидается проведение оплаты');
define('_JSHOP_YOO_CAPTURE_FAILED', 'Платёж не был проведён');
define('_JSHOP_YOO_PAYMENT_CAPTURED', 'Оплата была проведена');
define('_JSHOP_YOO_PAYMENT_CAPTURED_TEXT', 'Платёж %s проведён');
define('_JSHOP_YOO_ERROR_MESSAGE_CREATE_PAYMENT', 'Не удалось создать платёж, попробуйте выбрать другой способ оплаты.');
define('_JSHOP_YOO_ALFA_CLICK_TEXT', 'Укажите логин, и мы выставим счет в Альфа-Клике. После этого останется подтвердить платеж на сайте интернет-банка.');
define('_JSHOP_YOO_QIWI_PHONE_TEXT', 'Телефон, который привязан к Qiwi Wallet');
define('_JSHOP_YOO_FILL_PHONE_MESSAGE', 'Укажите телефон');
define('_JSHOP_YOO_FILL_ALFA_CLICK_LOGIN', 'Укажите логин в Альфа-клике');
define('_JSHOP_YOO_ENABLE', 'Включить');
define('_JSHOP_YOO_DISABLE', 'Выключить');
define('_JSHOP_YOO_DEFAULT_TAX_LABEL', 'Ставка по умолчанию');
define('_JSHOP_YOO_DEFAULT_TAX_DESCRIPTION', 'Ставка по умолчанию будет в чеке, если в карточке товара не указана другая ставка.');
define('_JSHOP_YOO_TAX_RATES_LABEL', 'Сопоставьте ставки');
define('_JSHOP_YOO_TAX_IN_MODULE', 'Ставка в вашем магазине');
define('_JSHOP_YOO_TAX_FOR_CHECKOUT', 'Ставка для чека в налоговую');
define('_JSHOP_YOO_WITHOUT_VAT', 'Без НДС');
define('_JSHOP_YOO_VAT_10_100', 'Расчётная ставка 10/110');
define('_JSHOP_YOO_VAT_18_118', 'Расчётная ставка 18/118');
define('_JSHOP_YOO_NOTIFICATION_URL_LABEL', 'Адрес для уведомлений');
define('_JSHOP_YOO_NOTIFICATION_URL_HELP_TEXT', 'Этот адрес понадобится, только если его попросят специалисты ЮKassa');
define('_JSHOP_YOO_LOG_VIEW_LABEL', 'Просмотр логов модуля');
define('_JSHOP_YOO_CLEAR_LOGS', 'Очистить журнал');
define('_JSHOP_YOO_CLOSE', 'Закрыть');
define('_JSHOP_YOO_LOGS_LABEL', 'Журнал сообщений модуля');

define('_JSHOP_YOO_TAB_UPDATE', 'Обновления');

define('_JSHOP_YOO_KASSA_ENABLE_SBBOL', 'Включить платежи через Сбербанк Бизнес Онлайн');
define('_JSHOP_YOO_SBBOL_HELP_TEXT', 'При оплате через Сбербанк Бизнес Онлайн есть ограничение: в одном заказе могут быть только товары с одинаковой ставкой НДС. Если клиент захочет оплатить за один раз товары с разными ставками — мы покажем ему сообщение, что так сделать не получится.');
define('_JSHOP_YOO_SBBOL_HEAD', 'Чтобы платежи через Сбербанк Бизнес Онлайн работали, магазин должен быть подключен к <a href="https://yookassa.ru/">ЮKassa.</a>');
define('_JSHOP_YOO_SBBOL_TAX_RATES_HEAD', 'Сопоставьте ставки НДС в вашем магазине со ставками для Сбербанка Бизнес Онлайн');