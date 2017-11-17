<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

if ($pmconfigs['paymentsmode'] != '3') {
    if (empty($pmconfigs['ya_payments_fio'])) {
        $parts = array();
        $user = JSFactory::getUserShop();
        if (!empty($user->l_name)) {
            $parts[] = $user->l_name;
        }
        if (!empty($user->f_name)) {
            $parts[] = $user->f_name;
        }
        if (!empty($user->m_name)) {
            $parts[] = $user->m_name;
        }
        $pmconfigs['ya_payments_fio'] = implode(' ', $parts);
    }
}

if (isset($pmconfigs['kassamode']) && $pmconfigs['paymode']=='1' && $pmconfigs['kassamode']=='1') return; ?>
<table class="radio" style="margin-left: 50px;">
    <tbody>
    <?php
    if ($pmconfigs['paymentsmode'] != '1') {
        $list_methods=(isset($pmconfigs['kassamode']) && $pmconfigs['kassamode']!='1')?array('ym2'=>'PC','cards2'=>'AC'):array('ym'=>'PC','cards'=>'AC','cash'=>'GP','phone'=>'MC','wm'=>'WM','sb'=>'SB','ab'=>'AB','pb'=>'PB','ma'=>'MA', 'qw'=>'QW', 'qp'=>'QP', 'mp'=>'MP');
        $num+=0;
        foreach ($list_methods as $m_long => $m_short) {
            if (isset($pmconfigs['method_' . $m_long]) && $pmconfigs['method_' . $m_long] == '1' || ($pmconfigs['moneymode'] == '1' && ($m_short == "PC" || $m_short == "AC"))) {
                $num += 1; ?>
                <tr class="highlight">
                    <td><input type="radio" name="params[pm_yandex_money][ym-payment-type]"
                               value="<?php echo $m_short; ?>"
                            <?php if ($num == 1) {
                                echo "checked";
                            } ?> id="ym<?php echo $num; ?>"></td>
                    <td><?php if ($m_short != 'MP') { ?><img
                            src="<?php echo JURI::root(); ?>components/com_jshopping/images/yandex_money/<?php echo strtolower($m_short); ?>.png"><?php } ?>
                    </td>
                    <td>
                        <label for="ym<?php echo $num; ?>"><?php echo constant('_JSHOP_YM_METHOD_' . strtoupper($m_long) . '_DESCRIPTION'); ?></label>
                    </td>
                </tr>
            <?php }
        }
    } else { ?>
        <tr>
            <td width="150" rowspan="2" valign="top" style="padding-top: 5px;">
                <label for="ya_payments_fio"><?php echo _JSHOP_YM_PAYMENTS_FIO_LABEL; ?></label>
            </td>
            <td>
                <input type="text" class="inputbox" name="params[pm_yandex_money][ya_payments_fio]" id="ya_payments_fio" value="<?php print $pmconfigs['ya_payments_fio']?>"/>
            </td>
        </tr>
        <tr>
            <td id="ya_payments_fio_error">

            </td>
        </tr>
    <?php } ?>
    </tbody>
 </table>
<script type="text/javascript">
function check_pm_yandex_money(){
    <?php if ($pmconfigs['paymentsmode'] == '1') : ?>
    var value = jQuery("#ya_payments_fio").val().trim();
    if (value.length == 0) {
        jQuery("#ya_payments_fio_error").text('<?php echo _JSHOP_YM_PAYMENTS_EMPTY_NAME_ERROR; ?>');
        return;
    }
    var names = value.split(' '), tmp = [];
    for (var i = 0; i < names.length; i++) {
        if (names[i].length > 0) {
            tmp.push(names[i]);
        }
    }
    if (tmp.length != 3) {
        jQuery("#ya_payments_fio_error").text('<?php echo _JSHOP_YM_PAYMENTS_INVALID_NAME_ERROR; ?>');
        return;
    }
    jQuery("#ya_payments_fio").val(tmp.join(' '));
    jQuery("#ya_payments_fio_error").text("");
    <?php endif; ?>
    jQuery('#payment_form').submit();
}
</script>