<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YooMoney
 * @copyright Copyright (C) 2020 YooMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

$uri         = JURI::getInstance();
$liveurlhost = $uri->toString(array("scheme", 'host', 'port'));
$sslurlhost  = $uri->toString(array('host', 'port'));

$notify_url = 'https://'.$sslurlhost.\JSHelper::SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_yoomoney_sbbol&no_lang=1");
$notify_url = htmlspecialchars_decode($notify_url);


function escapeValue($value)
{
    return htmlspecialchars($value);
}

?>
<style>
    table.adminFormKassaTable td {
        vertical-align: top !important;
    }
    table.adminFormKassaTable td.key {
        width: 250px;
    }
</style>
<div class="col100">
    <fieldset class="adminform">
        <p><?php echo _JSHOP_YOO_LICENSE_TEXT2; ?></p>
        <p><?php echo _JSHOP_YOO_VERSION_DESCRIPTION; ?><?php echo _JSHOP_YOO_VERSION; ?></p>

        <div class="row">
            <div class="span11 offset1">
                <h4><?php echo _JSHOP_YOO_SBBOL_HEAD; ?></h4>
            </div>
        </div>

        <div class="row">
            <div class="span4 offset1">
                <p><?php echo _JSHOP_YOO_LICENSE_TEXT; ?></p>
            </div>
        </div>
        <table class="adminFormKassaTable">
            <tr>
                <td colspan="2">
                    <h4><?= _JSHOP_YOO_KASSA_HEAD_LK; ?></h4>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <label for="pm_params[shop_id]">shopId</label>
                </td>
                <td>
                    <input name="pm_params[shop_id]" type="text" class="form-control" id="pm_params[shop_id]"
                           value="<?php echo escapeValue($params['shop_id']); ?>">
                    <p class="help-block"><?php echo _JSHOP_YOO_KASSA_SHOP_ID_DESCRIPTION; ?></p>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <label for="pm_params[shop_password]" class="">Секретное слово</label>
                </td>
                <td>
                    <input name="pm_params[shop_password]" type="text" class="form-control"
                           id="pm_params[shop_password]"
                           value="<?php echo escapeValue($params['shop_password']); ?>">
                    <p class="help-block"><?php echo _JSHOP_YOO_KASSA_PASSWORD_DESCRIPTION; ?></p>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <label for="pm_params[sbbol_purpose]">Шаблон для назначения платежа</label>
                </td>
                <td>
                    <input name="pm_params[sbbol_purpose]" type="text" class="form-control"
                           id="pm_params[sbbol_purpose]"
                           value="<?php echo escapeValue($params['sbbol_purpose']); ?>">
                    <p class="help-block">Это назначение платежа будет в платёжном поручении.</p>
                    <table>
                        <tr>
                            <td>
                                <label for="pm_params[yoo_sbbol_default_tax]" class="">Ставка НДС по
                                    умолчанию</label>
                            </td>
                            <td>
                                <select name="pm_params[yoo_sbbol_default_tax]"
                                        class="form-select form-control form-select-sm"
                                        id="pm_params[yoo_sbbol_default_tax]">
                                    <option <?php if ($params['yoo_sbbol_default_tax'] == 'untaxed') { ?> selected="selected" <?php } ?>
                                            value="untaxed">Без НДС
                                    </option>
                                    <option <?php if ($params['yoo_sbbol_default_tax'] == '7') { ?> selected="selected" <?php } ?>
                                            value="7">7%
                                    </option>
                                    <option <?php if ($params['yoo_sbbol_default_tax'] == '10') { ?> selected="selected" <?php } ?>
                                            value="10">10%
                                    </option>
                                    <option <?php if ($params['yoo_sbbol_default_tax'] == '18') { ?> selected="selected" <?php } ?>
                                            value="18">18%
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <p><?php echo _JSHOP_YOO_SBBOL_TAX_RATES_HEAD; ?></p>
                                <p class="help-block">Слева — ставка НДС в вашем магазине, справа — в ЮKassa. Пожалуйста,
                                    сопоставьте их.</p>
                            </td>
                        </tr>
                        <?php foreach ($taxes as $k => $tax) { ?>
                            <tr>
                                <td colspan="2">
                                    <label for="pm_params[yoo_sbbol_tax_<?php echo $k; ?>]" class=""><?php echo $tax; ?>
                                        % передавать в ЮKassa как</label>
                                    <select name="pm_params[yoo_sbbol_tax_<?php echo $k; ?>]" class="form-select form-control form-select-sm"
                                            id="pm_params[yoo_sbbol_tax_<?php echo $k; ?>]">
                                        <option <?php if ($params['yoo_sbbol_tax_'.$k] == 'untaxed') { ?> selected="selected" <?php } ?>
                                                value="untaxed">Без НДС
                                        </option>
                                        <option <?php if ($params['yoo_sbbol_tax_'.$k] == '7') { ?> selected="selected" <?php } ?>
                                                value="7">7%
                                        </option>
                                        <option <?php if ($params['yoo_sbbol_tax_'.$k] == '10') { ?> selected="selected" <?php } ?>
                                                value="10">10%
                                        </option>
                                        <option <?php if ($params['yoo_sbbol_tax_'.$k] == '18') { ?> selected="selected" <?php } ?>
                                                value="18">18%
                                        </option>
                                    </select>

                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                    <p class="help-block">
                        <?php echo _JSHOP_YOO_SBBOL_HELP_TEXT; ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <label class=""><?= _JSHOP_YOO_NOTIFICATION_URL_LABEL ?></label>
                </td>
                <td>
                    <div class="input-group has-success">
                        <input type="text" class="form-control valid form-control-success" readonly=""
                               value="<?= escapeValue($notify_url) ?>" aria-invalid="false">
                    </div>
                    <p class="help-block"><?php echo _JSHOP_YOO_NOTIFICATION_URL_HELP_TEXT; ?></p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h4><?php echo _JSHOP_YOO_COMMON_HEAD; ?></h4>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <label for="pm_params[kassa_transaction_end_status]"><?php echo _JSHOP_YOO_COMMON_STATUS; ?></label>
                </td>
                <td>
                    <?php
                    print JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                        'pm_params[kassa_transaction_end_status]',
                        'class="form-select form-control form-select-sm transaction-end-status" size="1" data-type="kassa"', 'status_id', 'name',
                        $params['kassa_transaction_end_status']);
                    ?>
                </td>
            </tr>
        </table>
        <input type="hidden" name="pm_params[transaction_end_status]" id="transaction-end-status"
               value="<?php echo $params['transaction_end_status']; ?>"/>
    </fieldset>
</div>
<div class="clr"></div>
<script type="text/javascript">
    function yoomoney_validate_mode(paymode) {
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

    window.addEventListener('DOMContentLoaded', function () {
        yoomoney_validate_mode(<?php if ($params['paymode'] == '1') {
            echo "1";
        } ?>);
        taxes_validate_mode(<?php if ($params['yookassa_send_check'] == '1') {
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