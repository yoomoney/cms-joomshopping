<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

$uri         = JURI::getInstance();
$liveurlhost = $uri->toString(array("scheme", 'host', 'port'));
$sslurlhost  = $uri->toString(array('host', 'port'));

$notify_url = 'https://'.$sslurlhost.SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_yandex_money_sbbol&no_lang=1");
$notify_url = htmlspecialchars_decode($notify_url);


function escapeValue($value)
{
    return htmlspecialchars($value);
}

?>
<div class="col100">
    <fieldset class="adminform">
        <p><?php echo _JSHOP_YM_LICENSE_TEXT2; ?></p>
        <p><?php echo _JSHOP_YM_VERSION_DESCRIPTION; ?><?php echo _JSHOP_YM_VERSION; ?></p>

        <div class="row">
            <div class="span11 offset1">
                <h4><?php echo _JSHOP_YM_SBBOL_HEAD; ?></h4>
            </div>
        </div>

        <div class="row">
            <div class="span4 offset1">
                <p><?php echo _JSHOP_YM_LICENSE_TEXT; ?></p>
            </div>
        </div>
        <div class="row">
            <div class="span11 offset1">
                <h4><?php echo _JSHOP_YM_KASSA_HEAD_LK; ?></h4>
            </div>
        </div>
        <div class="row">
            <div class="span11 offset1">
                <div class="form-group">
                    <div class="span2"><label for="pm_params[shop_id]">shopId</label></div>
                    <div class="span8">
                        <input name="pm_params[shop_id]" type="text" class="form-control" id="pm_params[shop_id]"
                               value="<?php echo escapeValue($params['shop_id']); ?>">
                        <p class="help-block"><?php echo _JSHOP_YM_KASSA_SHOP_ID_DESCRIPTION; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="span11 offset1">
                <div class="form-group">
                    <div class="span2"><label for="pm_params[shop_password]" class="">Секретное слово</label></div>
                    <div class="span8">
                        <input name="pm_params[shop_password]" type="text" class="form-control"
                               id="pm_params[shop_password]"
                               value="<?php echo escapeValue($params['shop_password']); ?>">
                        <p class="help-block"><?php echo _JSHOP_YM_KASSA_PASSWORD_DESCRIPTION; ?></p>
                    </div>
                </div>
            </div>
        </div>


        <div>
            <div class="row">
                <div class="span11 offset1">
                    <div class="form-group">
                        <div class="span2"><label for="pm_params[sbbol_purpose]" class="">Шаблон для назначения
                                платежа</label></div>
                        <div class="span8">
                            <input name="pm_params[sbbol_purpose]" type="text" class="form-control"
                                   id="pm_params[sbbol_purpose]"
                                   value="<?php echo escapeValue($params['sbbol_purpose']); ?>">
                            <p class="help-block">Это назначение платежа будет в платёжном поручении.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sbbolTaxesArea" style="padding-left: 360px;">
                <div class="row with-select">
                    <div class="span11 offset1">
                        <div class="form-group">
                            <div class="span2"><label for="pm_params[ya_sbbol_default_tax]" class="">Ставка НДС по
                                    умолчанию</label></div>
                            <div class="span8">
                                <select name="pm_params[ya_sbbol_default_tax]" class="fixed-width-xl"
                                        id="pm_params[ya_sbbol_default_tax]">
                                    <option <?php if ($params['ya_sbbol_default_tax'] == 'untaxed') { ?> selected="selected" <?php } ?>
                                            value="untaxed">Без НДС
                                    </option>
                                    <option <?php if ($params['ya_sbbol_default_tax'] == '7') { ?> selected="selected" <?php } ?>
                                            value="7">7%
                                    </option>
                                    <option <?php if ($params['ya_sbbol_default_tax'] == '10') { ?> selected="selected" <?php } ?>
                                            value="10">10%
                                    </option>
                                    <option <?php if ($params['ya_sbbol_default_tax'] == '18') { ?> selected="selected" <?php } ?>
                                            value="18">18%
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sbbolTaxesArea" style="padding-left: 360px;">
                <div class="row">
                    <div class="span11 offset1"><label><?php echo _JSHOP_YM_SBBOL_TAX_RATES_HEAD; ?></label></div>
                </div>
                <div class="row">
                    <div class="span11 offset1">
                        <div class="span2"><label>Ставка в вашем магазине.</label></div>
                        <p class="help-block">Слева — ставка НДС в вашем магазине, справа — в Яндекс.Кассе. Пожалуйста,
                            сопоставьте их.</p>
                    </div>
                </div>
                <?php foreach ($taxes as $k => $tax) { ?>
                    <div class="row with-select">
                        <div class="span11 offset1">
                            <div class="form-group">
                                <div class="span2"><label for="pm_params[ya_sbbol_tax_<?php echo $k; ?>]"
                                                          class=""><?php echo $tax; ?>% передавать в Яндекс.Кассу
                                        как</label></div>
                                <div class="span8">
                                    <select name="pm_params[ya_sbbol_tax_<?php echo $k; ?>]" class=" fixed-width-xl"
                                            id="pm_params[ya_sbbol_tax_<?php echo $k; ?>]">
                                        <option <?php if ($params['ya_sbbol_tax_'.$k] == 'untaxed') { ?> selected="selected" <?php } ?>
                                                value="untaxed">Без НДС
                                        </option>
                                        <option <?php if ($params['ya_sbbol_tax_'.$k] == '7') { ?> selected="selected" <?php } ?>
                                                value="7">7%
                                        </option>
                                        <option <?php if ($params['ya_sbbol_tax_'.$k] == '10') { ?> selected="selected" <?php } ?>
                                                value="10">10%
                                        </option>
                                        <option <?php if ($params['ya_sbbol_tax_'.$k] == '18') { ?> selected="selected" <?php } ?>
                                                value="18">18%
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="row">
                    <div class="span11 offset1">
                        <p class="help-block">
                            <?php echo _JSHOP_YM_SBBOL_HELP_TEXT; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="span11 offset1" style="padding-top: 10px;">
                <div class="form-group">
                    <div class="span2"><label class=""><?= _JSHOP_YM_NOTIFICATION_URL_LABEL ?></label></div>
                    <div class="span8">
                        <input class="form-control span8 disabled" value="<?php echo escapeValue($notify_url); ?>"
                               disabled><br>
                        <p class="help-block"><?php echo _JSHOP_YM_NOTIFICATION_URL_HELP_TEXT; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="span11 offset1">
                <h4><?php echo _JSHOP_YM_COMMON_HEAD; ?></h4>
            </div>
        </div>
        <div class="row">
            <div class="span11 offset1">
                <div class="form-group">
                    <div class="span2"><label
                                for="pm_params[kassa_transaction_end_status]"><?php echo _JSHOP_YM_COMMON_STATUS; ?></label>
                    </div>
                    <div class="span8">
                        <?php
                        print JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                            'pm_params[kassa_transaction_end_status]',
                            'class="inputbox transaction-end-status" size="1" data-type="kassa"', 'status_id', 'name',
                            $params['kassa_transaction_end_status']);
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="pm_params[transaction_end_status]" id="transaction-end-status"
               value="<?php echo $params['transaction_end_status']; ?>"/>

    </fieldset>
</div>
<div class="clr"></div>
<script type="text/javascript">
    function yandex_validate_mode(paymode) {
        jQuery(function ($) {
            if (paymode == 1) {
                $(".with-select").hide();
            } else {
                $(".with-select").show();
            }
        });
    }
    function taxes_validate_mode(paymode) {
        jQuery(function ($) {
            if (paymode == 1) {
                $(".taxesArea").show();
            } else {
                $(".taxesArea").hide();
            }
        });
    }

    window.addEvent('domready', function () {
        yandex_validate_mode(<?php if ($params['paymode'] == '1') {
            echo "1";
        } ?>);
        taxes_validate_mode(<?php if ($params['ya_kassa_send_check'] == '1') {
            echo "1";
        } ?>);
        var endStatusInput = document.getElementById('transaction-end-status');
        jQuery('.pay-mode').change(function () {
            if (this.checked) {
                var self = this;
                jQuery('.pay-mode').each(function () {
                    if (this != self) {
                        this.checked = false;
                    } else {
                        var id = 'pm_params' + this.getAttribute('id') + '_transaction_end_status';
                        endStatusInput.value = document.getElementById(id).value;
                    }
                });
            }
        });
        jQuery('.transaction-end-status').change(function () {
            console.log('Related checkbox id: ' + this.dataset.type);
            var relatedCheckbox = document.getElementById(this.dataset.type);
            console.log(relatedCheckbox);
            if (relatedCheckbox.checked) {
                endStatusInput.value = this.value;
            }
        });
    });
</script>