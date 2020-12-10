<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YooMoney
 * @copyright Copyright (C) 2020 YooMoney. All rights reserved.
 */

use YooKassa\Model\PaymentMethodType;

defined('_JEXEC') or die('Restricted access');

const INSTALLMENTS_MIN_AMOUNT = 3000;

const PAYMENT_METHOD_WIDGET = 'widget';

$cart_data = JSFactory::getModel('cart', 'jshop');
$cart_data->load();

if ($pmConfigs['paymode'] != '1') : ?>
<table class="radio" style="margin-left: 50px;">
    <tbody>
    <?php
        $listMethods = array(
        PaymentMethodType::YOO_MONEY          => 'PC',
            PaymentMethodType::BANK_CARD      => 'AC',
            PaymentMethodType::CASH           => 'GP',
            PaymentMethodType::MOBILE_BALANCE => 'MC',
            PaymentMethodType::WEBMONEY       => 'WM',
            PaymentMethodType::SBERBANK       => 'SB',
            PaymentMethodType::ALFABANK       => 'AB',
            PaymentMethodType::QIWI           => 'QW',
            PaymentMethodType::INSTALLMENTS   => 'installments',
            PaymentMethodType::TINKOFF_BANK   => PaymentMethodType::TINKOFF_BANK,
            PAYMENT_METHOD_WIDGET             => PAYMENT_METHOD_WIDGET,
        );
        $num += 0;
        foreach ($listMethods as $long => $short) :
            if (isset($pmConfigs['method_' . $long]) && $pmConfigs['method_' . $long] == '1') :
                if ($long === PaymentMethodType::INSTALLMENTS) {
                    if (isset($cart_data->price_product)
                        && ($cart_data->price_product < INSTALLMENTS_MIN_AMOUNT)) {
                        continue;
                    }
                }
                $num += 1; ?>
                <tr class="highlight">
                    <td>
                        <input type="radio" name="params[pm_yoomoney][payment_type]" value="<?php echo $long; ?>"
                            <?php echo ($num == 1 ? ' checked' : ''); ?> id="yoomoney_<?php echo $long; ?>" />
                    </td>
                    <td><img src="<?php echo JURI::root(); ?>components/com_jshopping/images/yoomoney/<?php echo strtolower($short); ?>.png">
                    </td>
                    <td>
                        <label for="yoomoney_<?php echo $long; ?>"><?php echo constant('_JSHOP_YOO_METHOD_' . strtoupper($long) . '_DESCRIPTION_PUBLIC'); ?></label>
                    </td>
                </tr>
                <?php if ($long === PaymentMethodType::ALFABANK) : ?>
                <tr class="highlight additional-field" id="yoo-alfa-login-block" style="display:none;">
                    <td></td>
                    <td></td>
                    <td>
                        <label for="yoo-alfa-login"><?=constant("_JSHOP_YOO_ALFA_CLICK_TEXT")?></label><br />
                        <input type="text" name="params[pm_yoomoney][alfaLogin]" value="" id="yoo-alfa-login" />
                        <div id="yoo-alfa-login-error"></div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if ($long === PaymentMethodType::QIWI) : ?>
                <tr class="highlight additional-field" id="yoo-qiwi-phone-block" style="display:none;">
                    <td></td>
                    <td></td>
                    <td>
                        <label for="yoo-qiwi-phone"><?=constant("_JSHOP_YOO_QIWI_PHONE_TEXT")?></label><br />
                        <input type="text" name="params[pm_yoomoney][qiwiPhone]" value="" id="yoo-qiwi-phone" />
                        <div id="yoo-qiwi-phone-error"></div>
                    </td>
                </tr>
            <?php endif; ?>
            <?php endif;
        endforeach;
    ?>
    </tbody>
</table>
<?php else : ?>
    <input type="hidden" name="params[pm_yoomoney][payment_type]" value="" id="pm_yoomoney_payment_type"/>
<?php endif; ?>
<script type="text/javascript">
function check_pm_yoomoney() {
    var form = document.getElementById('payment_form');
    var checkList = form['params[pm_yoomoney][payment_type]'];
    if (checkList.value === 'qiwi') {
        var phone = form['params[pm_yoomoney][qiwiPhone]'].value.replace(/[^\d]+/, '');
        if (phone.length == 0) {
            jQuery('#yoo-qiwi-phone-error').text('<?= constant('_JSHOP_YOO_FILL_PHONE_MESSAGE')?>');
            return;
        }
    }
    if (checkList.value === 'alfabank') {
        var login = form['params[pm_yoomoney][alfaLogin]'].value.trim();
        if (login.length == 0) {
            jQuery('#yoo-alfa-login-error').text('<?= constant('_JSHOP_YOO_FILL_ALFA_CLICK_LOGIN')?>');
            return;
        }
    }
    document.getElementById('payment_form').submit();
}
jQuery(document).ready(function () {
    var form = document.getElementById('payment_form');
    var checkList = form['params[pm_yoomoney][payment_type]'];
    var qiwi = jQuery('#yoo-qiwi-phone-block');
    var alfa = jQuery('#yoo-alfa-login-block');

    jQuery(checkList).change(function () {
        qiwi.css('display', 'none');
        alfa.css('display', 'none');
        jQuery('#yoo-alfa-login-error').text('');
        jQuery('#yoo-qiwi-phone-error').text('');
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
    const yooinstallments_shop_id = <?= $pmConfigs['shop_id'] ?>;
    const yooinstallments_total_amount = <?= $cart_data->price_product; ?>;
    const yooinstallments_language = "ru";

    jQuery.get("https://yoomoney.ru/credit/order/ajax/credit-pre-schedule?shopId="
        + yooinstallments_shop_id + "&sum=" + yooinstallments_total_amount, function (data) {
        const yooinstallments_amount_text = "<?= _JSHOP_YOO_METHOD_INSTALLMENTS_AMOUNT; ?>";
        if (yooinstallments_amount_text && data && data.amount) {
            jQuery('label[for=yoomoney_installments]').append(yooinstallments_amount_text.replace('%s', data.amount));
        }
    });
</script>
