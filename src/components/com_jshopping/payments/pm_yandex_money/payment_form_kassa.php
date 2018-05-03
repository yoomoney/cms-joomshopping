<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

const INSTALLMENTS_MIN_AMOUNT = 3000;

$cart_data = JSFactory::getModel('cart', 'jshop');
$cart_data->load();

if ($pmConfigs['paymode'] != '1') : ?>
<table class="radio" style="margin-left: 50px;">
    <tbody>
    <?php
        $listMethods = array(
            \YandexCheckout\Model\PaymentMethodType::YANDEX_MONEY   => 'PC',
            \YandexCheckout\Model\PaymentMethodType::BANK_CARD      => 'AC',
            \YandexCheckout\Model\PaymentMethodType::CASH           => 'GP',
            \YandexCheckout\Model\PaymentMethodType::MOBILE_BALANCE => 'MC',
            \YandexCheckout\Model\PaymentMethodType::WEBMONEY       => 'WM',
            \YandexCheckout\Model\PaymentMethodType::SBERBANK       => 'SB',
            \YandexCheckout\Model\PaymentMethodType::ALFABANK       => 'AB',
            \YandexCheckout\Model\PaymentMethodType::QIWI           => 'QW',
            \YandexCheckout\Model\PaymentMethodType::INSTALLMENTS   => 'installments',
        );
        $num += 0;
        foreach ($listMethods as $long => $short) :
            if (isset($pmConfigs['method_' . $long]) && $pmConfigs['method_' . $long] == '1') :
                if ($long === \YandexCheckout\Model\PaymentMethodType::INSTALLMENTS) {
                    if (isset($cart_data->price_product)
                        && ($cart_data->price_product < INSTALLMENTS_MIN_AMOUNT)) {
                        continue;
                    }
                }
                $num += 1; ?>
                <tr class="highlight">
                    <td>
                        <input type="radio" name="params[pm_yandex_money][payment_type]" value="<?php echo $long; ?>"
                            <?php echo ($num == 1 ? ' checked' : ''); ?> id="yandex_money_<?php echo $long; ?>" />
                    </td>
                    <td><img src="<?php echo JURI::root(); ?>components/com_jshopping/images/yandex_money/<?php echo strtolower($short); ?>.png">
                    </td>
                    <td>
                        <label for="yandex_money_<?php echo $long; ?>"><?php echo constant('_JSHOP_YM_METHOD_' . strtoupper($long) . '_DESCRIPTION'); ?></label>
                    </td>
                </tr>
                <?php if ($long === \YandexCheckout\Model\PaymentMethodType::ALFABANK) : ?>
                <tr class="highlight additional-field" id="ym-alfa-login-block" style="display:none;">
                    <td colspan="3">
                        <label for="ym-alfa-login">Укажите логин, и мы выставим счет в Альфа-Клике. После этого останется подтвердить платеж на сайте интернет-банка.</label><br />
                        <input type="text" name="params[pm_yandex_money][alfaLogin]" value="" id="ym-alfa-login" />
                        <div id="ym-alfa-login-error"></div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if ($long === \YandexCheckout\Model\PaymentMethodType::QIWI) : ?>
                <tr class="highlight additional-field" id="ym-qiwi-phone-block" style="display:none;">
                    <td colspan="3">
                        <label for="ym-qiwi-phone">Телефон, который привязан к Qiwi Wallet</label><br />
                        <input type="text" name="params[pm_yandex_money][qiwiPhone]" value="" id="ym-qiwi-phone" />
                        <div id="ym-qiwi-phone-error"></div>
                    </td>
                </tr>
            <?php endif; ?>
            <?php endif;
        endforeach;
    ?>
    </tbody>
</table>
<?php else : ?>
    <input type="hidden" name="params[pm_yandex_money][payment_type]" value="" id="pm_yandex_money_payment_type"/>
<?php endif; ?>
<script type="text/javascript">
function check_pm_yandex_money() {
    var form = document.getElementById('payment_form');
    var checkList = form['params[pm_yandex_money][payment_type]'];
    if (checkList.value === 'qiwi') {
        var phone = form['params[pm_yandex_money][qiwiPhone]'].value.replace(/[^\d]+/, '');
        if (phone.length == 0) {
            jQuery('#ym-qiwi-phone-error').text('Укажите телефон');
            return;
        }
    }
    if (checkList.value === 'alfabank') {
        var login = form['params[pm_yandex_money][alfaLogin]'].value.trim();
        if (login.length == 0) {
            jQuery('#ym-alfa-login-error').text('Укажите логин в Альфа-клике');
            return;
        }
    }
    document.getElementById('payment_form').submit();
}
jQuery(document).ready(function () {
    var form = document.getElementById('payment_form');
    var checkList = form['params[pm_yandex_money][payment_type]'];
    var qiwi = jQuery('#ym-qiwi-phone-block');
    var alfa = jQuery('#ym-alfa-login-block');

    jQuery(checkList).change(function () {
        qiwi.css('display', 'none');
        alfa.css('display', 'none');
        jQuery('#ym-alfa-login-error').text('');
        jQuery('#ym-qiwi-phone-error').text('');
        if (checkList.value === 'qiwi') {
            qiwi.css('display', 'table-row');
        }
        if (checkList.value === 'alfabank') {
            alfa.css('display', 'table-row');
        }
    });
});
</script>
<script type="text/javascript">
    const ym_installments_shop_id = <?= $pmConfigs['shop_id'] ?>;
    const ym_installments_total_amount = <?= $cart_data->price_product; ?>;
    const ym_installments_language = "ru";

    jQuery.get("https://money.yandex.ru/credit/order/ajax/credit-pre-schedule?shopId="
        + ym_installments_shop_id + "&sum=" + ym_installments_total_amount, function (data) {
        const ym_installments_amount_text = "<?= _JSHOP_YM_METHOD_INSTALLMENTS_AMOUNT; ?>";
        if (ym_installments_amount_text && data && data.amount) {
            jQuery('label[for=yandex_money_installments]').append(ym_installments_amount_text.replace('%s', data.amount));
        }
    });
</script>
