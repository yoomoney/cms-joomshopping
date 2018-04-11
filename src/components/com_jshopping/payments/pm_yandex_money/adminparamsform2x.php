<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

function escapeValue($value)
{
    return htmlspecialchars($value);
}

?>
<style>
    .help-block {
        font-size: 11px;
    }
    p.help-block {
        margin: 0;
        padding: 3px 0;
    }
</style>
<div class="col100">
<fieldset class="adminform">
<table class="admintable" width = "100%" >
<tr>
    <td><b> <?php echo _JSHOP_YM_VERSION_DESCRIPTION; ?></b></td>
    <td><?php echo _JSHOP_YM_VERSION; ?></td>
</tr>
<tr>
    <td valign="top"><b> <?php echo _JSHOP_YM_LICENSE; ?></b></td>
    <td><?php echo _JSHOP_YM_LICENSE_TEXT2; ?></td>
</tr>
<tr>
    <td class="key">
        <b><?php echo _JSHOP_YM_MODE_DESCRIPTION;?></b>
    </td>
    <td>
    <?php
        $state = array();
        $state[] = JHTML::_('select.option', '1', _JSHOP_YM_MODE1_DESCRIPTION,  'value', 'text');
        $state[] = JHTML::_('select.option', '2', _JSHOP_YM_MODE2_DESCRIPTION);
        $state[] = JHTML::_('select.option', '3', _JSHOP_YM_MODE3_DESCRIPTION);
        $state[] = JHTML::_('select.option', '4', _JSHOP_YM_MODE4_DESCRIPTION);
        echo JHTML::_('select.genericlist', $state, 'pm_params[mode]', 'style="width: 300px;" onchange="yandex_validate_mode()"', 'value', 'text', $params['mode'], 'ym_mode');
    ?>
    </td>
</tr>
<?php
    $uri = JURI::getInstance();
    $liveUrlHost = $uri->toString(array("scheme",'host', 'port'));
    $sslUrlHost = 'https://'.$uri->toString(array('host', 'port'));

    $notify_url = $sslUrlHost.SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_yandex_money&no_lang=1");
    $notify_url = htmlspecialchars_decode($notify_url);
?>
<tr class="individ">
    <td style="width:250px;" class="key">
        <b> <?php echo _JSHOP_YM_TESTMODE_DESCRIPTION;?></b>
    </td>
    <td>
        <?php echo JHTML::_('select.booleanlist', 'pm_params[testmode]', 'class = "inputbox" size = "1"', $params['testmode']); ?>
    </td>
    <td></td>
    <td>
        <?php echo _JSHOP_YM_REG_IND;?>:<br/><br>
        <table style="border: 1px black solid;">
            <tr>
                <td style="border: 1px black solid; padding: 5px;"><?php echo _JSHOP_YM_PARAM?></td>
                <td style="border: 1px black solid; padding: 5px;"><?php echo _JSHOP_YM_VALUE?></td>
            </tr>
            <tr>
                <td style="border: 1px black solid; padding: 5px;">Redirect URI</td>
                <td style="border: 1px black solid; padding: 5px;"><?php echo $notify_url?></td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td></td>
    <td></td>
</tr>
<tr class="org">
    <td  class="key">
        <b><?php echo _JSHOP_YM_KASSA_SHOP_ID_LABEL;?></b>
    </td>
    <td>
        <input type = "text" class = "inputbox" name = "pm_params[shop_id]" size="45" value = "<?php echo $params['shop_id']?>" />
        <p class="help-block"><?php echo _JSHOP_YM_KASSA_SHOP_ID_DESCRIPTION; ?></p>
    </td>
</tr>
<tr class="org">
    <td  class="key">
        <b><?php echo _JSHOP_YM_KASSA_PASSWORD_LABEL; ?></b>
    </td>
    <td>
        <input type = "text" class = "inputbox" name = "pm_params[shop_password]" size="45" value = "<?php echo $params['shop_password']?>" />
        <p class="help-block"><?php echo _JSHOP_YM_KASSA_PASSWORD_DESCRIPTION; ?></p>
    </td>
</tr>

<tr class="payments">
    <td></td>
    <td>
        <?php echo _JSHOP_YM_PAYMENTS_HEAD; ?><br /><br />
    </td>
</tr>

<tr class="with-select">
    <td class="key" colspan="2"><br/><br/><b><?php echo _JSHOP_YM_METHODS_DESCRIPTION; ?></b></td>
</tr>
<?php
$list_methods = array('ym' => 'PC','cards'=>'AC');
foreach ($list_methods as $m_long => $m_short) : ?>
    <tr class="individ">
        <td><?php echo constant('_JSHOP_YM_METHOD_'.strtoupper($m_long).'_DESCRIPTION');?></td>
        <td><?php print JHTML::_('select.booleanlist', 'pm_params[method_'.$m_long.']', 'class = "inputbox"', $params['method_'.$m_long]); ?></td>
    </tr>
<?php endforeach; ?>
<?php foreach (\YandexCheckout\Model\PaymentMethodType::getEnabledValues() as $value) : ?>
    <tr class="with-select org">
        <td><?php echo constant('_JSHOP_YM_METHOD_'.strtoupper($value).'_DESCRIPTION');?></td>
        <td><?php print JHTML::_('select.booleanlist', 'pm_params[method_'.$value.']', 'class = "inputbox"', $params['method_'.$value]); ?></td>
    </tr>
<?php endforeach; ?>

<tr class="org">
    <td><b><?php echo _JSHOP_YM_KASSA_SEND_RECEIPT_LABEL; ?></b></td>
    <td>
        <input onclick="taxes_validate_mode(1)" type = "radio" class = "ya_kassa_send_check" name = "pm_params[ya_kassa_send_check]" value = "1"
            <?php if($params['ya_kassa_send_check']=='1') echo "checked"; ?> /> Включить <br>
        <input onclick="taxes_validate_mode(0)" type = "radio" class = "ya_kassa_send_check" name = "pm_params[ya_kassa_send_check]" value = "0"
            <?php if($params['ya_kassa_send_check']=='0') echo "checked"; ?> /> Выключить
    </td>
</tr>

<tr class="org"<?php ($params['ya_kassa_send_check']=='1' ? ' style="display:none;"' : '') ?> id="select_send_check">
    <td>
        <b>Сопоставьте ставки</b><br />
    </td>
    <td>
        <table>
            <tr>
                <th>Ставка в вашем магазине</th>
                <th>Ставка для чека в налоговую</th>
            </tr>
            <?php foreach ($taxes as $k => $tax) : ?>
                <tr>
                    <td><?php echo $tax; ?></td>
                    <td>
                        <select name="pm_params[ya_kassa_tax_<?php echo $k; ?>]" class=" fixed-width-xl" id="pm_params[ya_kassa_tax_<?php echo $k; ?>]">
                            <option <?php if ($params['ya_kassa_tax_'.$k] == 1) { ?> selected="selected" <?php } ?> value="1">Без НДС</option>
                            <option <?php if ($params['ya_kassa_tax_'.$k] == 2) { ?> selected="selected" <?php } ?> value="2">0%</option>
                            <option <?php if ($params['ya_kassa_tax_'.$k] == 3) { ?> selected="selected" <?php } ?> value="3">10%</option>
                            <option <?php if ($params['ya_kassa_tax_'.$k] == 4) { ?> selected="selected" <?php } ?> value="4">18%</option>
                            <option <?php if ($params['ya_kassa_tax_'.$k] == 5) { ?> selected="selected" <?php } ?> value="5">Расчётная ставка 10/110</option>
                            <option <?php if ($params['ya_kassa_tax_'.$k] == 6) { ?> selected="selected" <?php } ?> value="6">Расчётная ставка 18/118</option>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </td>
</tr>

<tr class="individ">
    <td  class="key">
        <b><?php echo _JSHOP_YM_PASSWORD;?></b>
    </td>
    <td>
        <input type = "text" class = "inputbox" name = "pm_params[password]" size="45" value = "<?php echo $params['password']?>" />
    </td>
</tr>
<tr class="individ">
    <td  class="key">
         <b><?php echo _JSHOP_YM_ACCOUNT_DESCRIPTION;?></b>
    </td>
    <td>
        <input type = "text" class = "inputbox" name = "pm_params[account]" size="45" value = "<?php echo $params['account']?>" />
    </td>
</tr>
<tr class="payments">
    <td  class="key">
        <b><?php echo _JSHOP_YM_PAYMENTS_ID_LABEL;?></b>
    </td>
    <td>
        <input type = "text" class = "inputbox" name = "pm_params[ym_pay_id]" size="45" value = "<?php echo $params['ym_pay_id']?>" />
    </td>
</tr>
<tr class="payments">
    <td></td>
    <td><br /></td>
</tr>
<tr class="payments">
    <td  class="key">
        <b><?php echo _JSHOP_YM_PAYMENTS_DESCRIPTION_LABEL;?></b>
    </td>
    <td>
        <input type = "text" class = "inputbox" name = "pm_params[ym_pay_desc]" size="45" value = "<?php echo empty($params['ym_pay_desc']) ? _JSHOP_YM_PAYMENTS_DESCRIPTION_PLACEHOLDER : escapeValue($params['ym_pay_desc']); ?>" />
    </td>
</tr>
<tr class="payments">
    <td></td>
    <td><?php echo _JSHOP_YM_PAYMENTS_DESCRIPTION_INFO; ?><br /><br /></td>
</tr>


<tr>
    <td class="key individ org">
        <b><?php echo _JSHOP_YM_TRANSACTION_END;?></b>
    </td>
    <td class="key payments">
        <b><?php echo _JSHOP_YM_PAYMENTS_STATUS_LABEL;?></b>
    </td>
    <td>
        <?php echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_end_status'] ); ?>

        <input name="pm_params[kassamode]" type="hidden" value="1" id="kassamode">
        <input name="pm_params[moneymode]" type="hidden" value="0" id="moneymode">
        <input name="pm_params[paymentsmode]" type="hidden" value="0" id="paymentsmode">
        <input name="pm_params[paymode]" type="hidden" value="1" id="paymode">
    </td>
</tr>
<tr class="payments">
    <td></td>
    <td><?php echo _JSHOP_YM_PAYMENTS_STATUS_INFO; ?><br /><br /></td>
</tr>

<tr class="org">
    <td></td>
    <td>
        <?php echo _JSHOP_YM_REG_ORG;?>:<br/><br/>
        <table style="border: 1px black solid;">
            <tr>
                <td style="border: 1px black solid; padding: 5px;"><?php echo _JSHOP_YM_PARAM?></td>
                <td style="border: 1px black solid; padding: 5px;"><?php echo _JSHOP_YM_VALUE?></td>
            </tr>
            <tr>
                <td style="border: 1px black solid; padding: 5px;">Адрес для уведомлений</td>
                <td style="border: 1px black solid; padding: 5px;">
                    <?php echo $notify_url?><br />
                    <p class="help-block">Этот адрес понадобится, только если его попросят специалисты Яндекс.Кассы</p>
                </td>
            </tr>
        </table>
    </td>
</tr>

</table>
</fieldset>
</div>
<div class="clr"></div>
<script type="text/javascript">
    function yandex_validate_mode(){
        jQuery(function($){
            var yandex_mode = $("#ym_mode").val();
            if (yandex_mode == 1) {
                $(".without-select").hide();
                $(".org").hide();
                $(".payments").hide();
                $(".individ").show();

                $("#kassamode").val('0');
                $("#moneymode").val('1');
                $("#paymentsmode").val('0');
                $("#paymode").val('0');
            } else if (yandex_mode == 2) {
                $(".without-select").hide();
                $(".individ").hide();
                $(".payments").hide();
                $(".org").show();
                $(".with-select").show();

                $("#kassamode").val('1');
                $("#moneymode").val('0');
                $("#paymentsmode").val('0');
                $("#paymode").val('0');
            } else if (yandex_mode == 3) {
                $(".org").show();
                $(".without-select").show();
                $(".with-select").hide();
                $(".individ").hide();
                $(".payments").hide();


                $("#kassamode").val('1');
                $("#moneymode").val('0');
                $("#paymentsmode").val('0');
                $("#paymode").val('1');
            } else if (yandex_mode == 4) {
                $(".with-select").hide();
                $(".individ").hide();
                $(".org").hide();
                $(".payments").show();
                $(".without-select").show();

                $("#kassamode").val('0');
                $("#moneymode").val('0');
                $("#paymentsmode").val('1');
                $("#paymode").val('0');
            }
        });
    }
    function taxes_validate_mode(show) {
        var el = document.getElementById('select_send_check');
        el.style.display = show ? 'table-row' : 'none';
    }
    window.addEvent('domready', function() {
        yandex_validate_mode();
    });

</script>
