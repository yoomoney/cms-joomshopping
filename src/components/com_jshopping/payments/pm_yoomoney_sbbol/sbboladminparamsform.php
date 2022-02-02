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

        <div class="row  mb-3">
            <div class="col">
                <h4><?php echo _JSHOP_YOO_KASSA_HEAD_LK; ?></h4>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-3">
                <label for="pm_params-shop-id">shopId</label>
            </div>
            <div class="col-9">
                <input type="text" name="pm_params[shop_id]" class="form-control" id="pm_params-shop-id"
                       value="<?php echo escapeValue($params['shop_id']); ?>">
                <div  class="form-text"><?php echo _JSHOP_YOO_KASSA_SHOP_ID_DESCRIPTION; ?></div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-3">
                <label for="pm_params-shop-password">
                    <?php echo _JSHOP_YOO_KASSA_PASSWORD_LABEL; ?>
                </label>
            </div>

            <div class="col-9">
                <input name="pm_params[shop_password]" type="text" class="form-control" id="pm_params-shop-password"
                       value="<?php echo escapeValue($params['shop_password']); ?>">
                <div  class="form-text"><?php echo _JSHOP_YOO_KASSA_PASSWORD_DESCRIPTION; ?></div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-3">
                <label for="yookassa_description_template">
                    Шаблон для назначения платежа
                </label>
            </div>

            <div class="col-9">
                <input name="pm_params[sbbol_purpose]" type="text" class="form-control"
                       id="yookassa_description_template"
                       value="<?php echo escapeValue($params['sbbol_purpose']); ?>"
                >
                <div  class="form-text">Это назначение платежа будет в платёжном поручении.</div>
                <div class="row mt-3">
                    <div class="col-auto">
                        <label class="col-form-label">Ставка НДС по умолчанию</label>
                    </div>
                    <div class="col-auto">
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
                    </div>
                </div>
                <p class="mt-3 mb-1"><?= _JSHOP_YOO_SBBOL_TAX_RATES_HEAD ?>:</p>
                <p class="mb-1">Слева — ставка НДС в вашем магазине, справа — в ЮKassa. Пожалуйста, сопоставьте их.</p>
                <?php foreach ($taxes as $k => $tax) { ?>
                <div class="row mb-2">
                    <div class="col-4">
                        <label><?php echo $tax; ?>% передавать в ЮKassa как</label>
                    </div>
                    <div class="col-4">
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
                    </div>
                </div>
                <?php } ?>
                <div  class="form-text"><?php echo _JSHOP_YOO_SBBOL_HELP_TEXT; ?></div>
            </div>

        </div>

        <div class="row mb-3">
            <div class="col-3">
                <label><?php echo _JSHOP_YOO_NOTIFICATION_URL_LABEL; ?></label>
            </div>

            <div class="col-9 ">
                <div class="input-group has-success">
                    <input type="text" class="form-control valid form-control-success"
                           name="jform[joomlatoken][token]" id="jform_joomlatoken_token" readonly=""
                           value="<?= escapeValue($notify_url) ?>" aria-invalid="false"
                    >
                </div>
                <div  class="form-text"><?php echo _JSHOP_YOO_NOTIFICATION_URL_HELP_TEXT; ?></div>
            </div>
        </div>
        <div class="row  mb-3">
            <div class="col">
                <h4><?php echo _JSHOP_YOO_COMMON_HEAD; ?></h4>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-3">
                <label for="kassa_transaction_end_status">
                    <?php echo _JSHOP_YOO_COMMON_STATUS; ?>
                </label>
            </div>

            <div class="col-9">
                <?php
                print JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                    'pm_params[kassa_transaction_end_status]',
                    'class="transaction-end-status form-select form-control form-select-sm" size="1" data-type="kassa"', 'status_id', 'name',
                    $params['kassa_transaction_end_status']);
                ?>
            </div>
        </div>

        <input type="hidden" name="pm_params[transaction_end_status]" id="transaction-end-status"
               value="<?php echo $params['kassa_transaction_end_status']; ?>"/>
    </fieldset>
</div>
<div class="clr"></div>
<script type="text/javascript">
    window.addEventListener('DOMContentLoaded', function () {
        jQuery('.transaction-end-status').change(function () {
            const endStatusInput = document.getElementById('transaction-end-status');
            endStatusInput.value = this.value;
        });
    });
</script>