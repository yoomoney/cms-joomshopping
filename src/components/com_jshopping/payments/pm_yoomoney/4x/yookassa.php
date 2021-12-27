<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YooMoney
 * @copyright Copyright (C) 2020 YooMoney. All rights reserved.
 */

use YooKassa\Model\PaymentMethodType;

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('uitab.addTab', 'yamTab', 'kassa-tab', _JSHOP_YOO_TAB_KASSA);

?>
    <div class="row">
        <div class="col">
            <p><?php echo _JSHOP_YOO_LICENSE_TEXT; ?></p>
        </div>
    </div>
<?php if (isset($errorCredentials)) : ?>
    <div class="row">
        <div class="col">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle"></i> <?php echo $errorCredentials; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($testWarning)) : ?>
    <div class="row">
        <div class="col">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle"></i> <?php echo $testWarning; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
<?php endif; ?>
    <style>
        table.adminFormKassaTable td {
            vertical-align: top !important;
        }
        table.adminFormKassaTable td.key {
            width: 250px;
        }
        .taxesArea {
            margin-bottom: 10px;
        }
    </style>
    <table class="admintable adminFormKassaTable" width = "100%" >
        <!--On/Off-->
        <tr>
            <td  class="key">
                <?php echo _JSHOP_YOO_KASSA_ON; ?>
            </td>
            <td>
                <input type="checkbox" id="kassa" class="pay-mode" name="pm_params[kassamode]" value="1"
                    <?php if (isSelected($params, 'kassamode')) {
                        echo "checked";
                    } ?> />
            </td>
        </tr>
        <!--On/Off end-->
        <!--Profile params-->
        <tr>
            <td colspan="2">
                <h4><?php echo _JSHOP_YOO_KASSA_HEAD_LK; ?></h4>
            </td>
        </tr>
        <tr>
            <td  class="key">
                <label for="pm_params-shop-id"><?php echo _JSHOP_YOO_KASSA_SHOP_ID_LABEL; ?></label>
            </td>
            <td>
                <input name="pm_params[shop_id]" type="text" class="form-control" id="pm_params-shop-id"
                       value="<?php echo escapeValue($params['shop_id']); ?>">
                <p class="help-block"><?php echo _JSHOP_YOO_KASSA_SHOP_ID_DESCRIPTION; ?></p>
            </td>
        </tr>
        <tr>
            <td>
                <label for="pm-params-shop-password">
                    <?php echo _JSHOP_YOO_KASSA_PASSWORD_LABEL; ?>
                </label>
            </td>
            <td>
                <input name="pm_params[shop_password]" type="text" class="form-control" id="pm-params-shop-password"
                       value="<?php echo escapeValue($params['shop_password']); ?>">
                <p class="help-block"><?php echo _JSHOP_YOO_KASSA_PASSWORD_DESCRIPTION; ?></p>
            </td>
        </tr>
        <!--Profile params end-->
        <!--Payment scenario-->
        <tr>
            <td colspan="2">
                <h4><?php echo _JSHOP_YOO_KASSA_PAYMODE_HEAD; ?></h4>
            </td>
        </tr>
        <tr>
            <td  class="key">
                <label><?php echo _JSHOP_YOO_KASSA_PAYMODE_LABEL; ?></label>
            </td>
            <td>
                <input type="radio" class="paymode" name="pm_params[paymode]" value="1"
                       onclick="yoomoney_validate_mode(1);"
                    <?php if ($params['paymode'] == '1') {
                        echo "checked";
                    } ?> /> <?php echo _JSHOP_YOO_KASSA_PAYMODE_KASSA; ?><br>
                <div class="with-kassa">
                </div>
                <input type="radio" class="paymode" name="pm_params[paymode]" value="0"
                       onclick="yoomoney_validate_mode(0);"
                    <?php if ($params['paymode'] == '0') {
                        echo "checked";
                    } ?> /> <?php echo _JSHOP_YOO_KASSA_PAYMODE_SHOP; ?>
                <p><?php echo _JSHOP_YOO_KASSA_PAYMODE_LINK; ?></p>
                <div class="row with-select">
                    <p><?php echo _JSHOP_YOO_KASSA_SELECT_TEXT; ?></p>
                    <div style="display: none" id="warning_for_verify_file_install" class="span9 offset3 alert alert-warning"></div>
                </div>
                <?php foreach ($params['paymentMethods'] as $value) : ?>
                    <div class="row with-select">
                        <div class="col">
                            <input type="checkbox" class="input-kassa"
                                   name="pm_params[method_<?php echo $value; ?>]" value="1"
                                <?php if ($params['method_'.$value] == '1') {
                                    echo "checked";
                                } ?> />
                            <?php echo constant('_JSHOP_YOO_METHOD_'.strtoupper($value).'_DESCRIPTION'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </td>
        </tr>
        <!--Payment scenario end-->
        <!--Transaction data-->
        <tr>
            <td  class="key">
                <label for="yookassa_description_template">
                    <?= _JSHOP_YOO_DESCRIPTION_TITLE; ?>
                </label>
            </td>
            <td>
                <input name="pm_params[yookassa_description_template]" type="text" class="form-control"
                       id="yookassa_description_template"
                       value="<?= !empty($params['yookassa_description_template'])
                           ? $params['yookassa_description_template']
                           : _JSHOP_YOO_DESCRIPTION_DEFAULT_PLACEHOLDER; ?>">
                <p class="help-block"><?= _JSHOP_YOO_DESCRIPTION_HELP; ?></p>
            </td>
        </tr>
        <!--Transaction data end-->
        <!--Payment holding-->
        <tr>
            <td  class="key">
                <label for="yookassa_description_template">
                    <?= _JSHOP_YOO_ENABLE_HOLD_MODE; ?>
                </label>
            </td>
            <td>
                <input type="checkbox" class="input-kassa"
                       name="pm_params[yookassa_enable_hold_mode]"
                       value="1" id="yookassa_enable_hold_mode"
                    <?= $params['yookassa_enable_hold_mode'] == '1' ? "checked" : ''; ?> />
                <span><?= _JSHOP_YOO_ENABLE_HOLD_MODE_HELP; ?></span>
                <table id="yookassa_enable_hold_mode_extended_settings">
                    <tr>
                        <td colspan="2">
                            <p><?= _JSHOP_YOO_HOLD_MODE_STATUSES; ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="pm_params[yookassa_hold_mode_on_hold_status]"><?= _JSHOP_YOO_HOLD_MODE_ON_HOLD_STATUS; ?></label>
                        </td>
                        <td>
                            <?= JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                                'pm_params[yookassa_hold_mode_on_hold_status]',
                                'class="form-select form-control form-select-sm" size="1" data-type="kassa"', 'status_id',
                                'name', $params['yookassa_hold_mode_on_hold_status']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="pm_params[yookassa_hold_mode_cancel_status]"><?= _JSHOP_YOO_HOLD_MODE_CANCEL_STATUS; ?></label>
                        </td>
                        <td>
                            <?= JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                                'pm_params[yookassa_hold_mode_cancel_status]',
                                'class="form-select form-control form-select-sm" size="1" data-type="kassa"', 'status_id',
                                'name', $params['yookassa_hold_mode_cancel_status']); ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!--Payment holding end-->
        <!--54-FZ-->
        <tr>
            <td  class="key">
                <label>
                    <?= _JSHOP_YOO_KASSA_SEND_RECEIPT_LABEL; ?>
                </label>
            </td>
            <td>
                <input onclick="taxes_validate_mode(1)" type="radio" class="yookassa_send_check"
                       name="pm_params[yookassa_send_check]" value="1"
                    <?php if ($params['yookassa_send_check'] == '1') {
                        echo "checked";
                    } ?> /> <?= _JSHOP_YOO_ENABLE ?> <br>
                <input onclick="taxes_validate_mode(0)" type="radio" class="yookassa_send_check"
                       name="pm_params[yookassa_send_check]" value="0"
                    <?php if ($params['yookassa_send_check'] == '0') {
                        echo "checked";
                    } ?> /> <?= _JSHOP_YOO_DISABLE ?>
                <div class="taxesArea row">
                    <div class="col">
                        <label for="pm_params[yookassa_default_tax]"><?= _JSHOP_YOO_DEFAULT_TAX_LABEL; ?></label>
                        <select name="pm_params[yookassa_default_tax]" class="form-select form-control form-select-sm"
                                id="pm_params[yookassa_default_tax]">
                            <option <?php if ($params['yookassa_default_tax'] == 1) { ?> selected="selected" <?php } ?>
                                    value="1"><?= _JSHOP_YOO_WITHOUT_VAT ?></option>
                            <option <?php if ($params['yookassa_default_tax'] == 2) { ?> selected="selected" <?php } ?>
                                    value="2"><?= _JSHOP_YOO_VAT_0 ?></option>
                            <option <?php if ($params['yookassa_default_tax'] == 3) { ?> selected="selected" <?php } ?>
                                    value="3"><?= _JSHOP_YOO_VAT_10 ?></option>
                            <option <?php if ($params['yookassa_default_tax'] == 4) { ?> selected="selected" <?php } ?>
                                    value="4"><?= _JSHOP_YOO_VAT_20 ?></option>
                            <option <?php if ($params['yookassa_default_tax'] == 5) { ?> selected="selected" <?php } ?>
                                    value="5"><?= _JSHOP_YOO_VAT_10_100 ?></option>
                            <option <?php if ($params['yookassa_default_tax'] == 6) { ?> selected="selected" <?php } ?>
                                    value="6"><?= _JSHOP_YOO_VAT_20_120 ?></option>
                        </select>
                        <p class="help-block"><?= _JSHOP_YOO_DEFAULT_TAX_DESCRIPTION; ?></p>
                    </div>
                    <p><?= _JSHOP_YOO_TAX_RATES_LABEL ?></p>
                    <table class="row">
                        <tr>
                            <td width="200px;">
                                <?= _JSHOP_YOO_TAX_IN_MODULE ?>
                            </td>
                            <td>
                                <?= _JSHOP_YOO_TAX_FOR_CHECKOUT ?>
                            </td>
                        </tr>
                        <?php foreach ($taxes as $k => $tax) { ?>
                            <tr>
                                <td>
                                    <label for="pm_params[yookassa_tax_<?php echo $k; ?>]"
                                           class=""><?php echo $tax; ?>%</label>
                                </td>
                                <td>
                                    <div class="col">
                                        <select name="pm_params[yookassa_tax_<?php echo $k; ?>]" class="form-select form-control form-select-sm"
                                                id="pm_params[yookassa_tax_<?php echo $k; ?>]">
                                            <option <?php if ($params['yookassa_tax_'.$k] == 1) { ?> selected="selected" <?php } ?>
                                                    value="1"><?= _JSHOP_YOO_WITHOUT_VAT ?></option>
                                            <option <?php if ($params['yookassa_tax_'.$k] == 2) { ?> selected="selected" <?php } ?>
                                                    value="2"><?= _JSHOP_YOO_VAT_0 ?></option>
                                            <option <?php if ($params['yookassa_tax_'.$k] == 3) { ?> selected="selected" <?php } ?>
                                                    value="3"><?= _JSHOP_YOO_VAT_10 ?></option>
                                            <option <?php if ($params['yookassa_tax_'.$k] == 4) { ?> selected="selected" <?php } ?>
                                                    value="4"><?= _JSHOP_YOO_VAT_20 ?></option>
                                            <option <?php if ($params['yookassa_tax_'.$k] == 5) { ?> selected="selected" <?php } ?>
                                                    value="5"><?= _JSHOP_YOO_VAT_10_100 ?></option>
                                            <option <?php if ($params['yookassa_tax_'.$k] == 6) { ?> selected="selected" <?php } ?>
                                                    value="6"><?= _JSHOP_YOO_VAT_20_120 ?></option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>

                <div class="taxesArea row">
                    <div class="col">
                        <label for="pm_params[yookassa_default_tax_system]"><?= _JSHOP_YOO_DEFAULT_TAX_SYSTEM_LABEL; ?></label>
                        <select name="pm_params[yookassa_default_tax_system]" class="form-select form-control form-select-sm" id="pm_params[yookassa_default_tax_system]">
                            <option value="">-</option>
                            <?php foreach(range(1, 6) as $tax_id) : ?>
                                <option <?php if ($params['yookassa_default_tax_system'] == $tax_id) { ?> selected="selected" <?php } ?>
                                        value="<?= $tax_id ?>"><?= constant("_JSHOP_YOO_DEFAULT_TAX_SYSTEM_{$tax_id}_LABEL") ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="help-block"><?= _JSHOP_YOO_DEFAULT_TAX_DESCRIPTION; ?></p>
                    </div>
                </div>

                <div class="taxesArea row">
                    <div class="col">
                        <label for="pm_params[yookassa_default_payment_mode]"> Признак способа расчета</label>
                        <select name="pm_params[yookassa_default_payment_mode]" class="form-select form-control form-select-sm"
                                id="pm_params[yookassa_default_payment_mode]">
                            <?php foreach ($params['paymentModeEnum'] as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $params['yookassa_default_payment_mode'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="taxesArea row">
                    <div class="col">
                        <label for="pm_params[yookassa_default_payment_subject]"> Признак предмета расчета</label>
                        <select name="pm_params[yookassa_default_payment_subject]" class="form-select form-control form-select-sm"
                                id="pm_params[yookassa_default_payment_subject]">
                            <?php foreach ($params['paymentSubjectEnum'] as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $params['yookassa_default_payment_subject'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="taxesArea row">
                    <div class="col">
                        <label for="pm_params[yookassa_default_delivery_payment_mode]"> Признак способа расчета для
                            доставки</label>
                        <select name="pm_params[yookassa_default_delivery_payment_mode]" class="form-select form-control form-select-sm"
                                id="pm_params[yookassa_default_delivery_payment_mode]">
                            <?php foreach ($params['paymentModeEnum'] as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $params['yookassa_default_delivery_payment_mode'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="taxesArea row">
                    <div class="col">
                        <label for="pm_params[yookassa_default_delivery_payment_subject]"> Признак предмета расчета для доставки</label>
                        <select name="pm_params[yookassa_default_delivery_payment_subject]" class="form-select form-control form-select-sm"
                                id="pm_params[yookassa_default_delivery_payment_subject]">
                            <?php foreach ($params['paymentSubjectEnum'] as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $params['yookassa_default_delivery_payment_subject'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="taxesArea row">
                    <div class="col">
                        <?= _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_LABEL ?>
                        <label>
                            <input style="vertical-align:middle;margin:-2px 3px 0 0;cursor: pointer" name="pm_params[send_second_receipt]" <?= $params['send_second_receipt'] ? "checked" : "" ?> type="radio" value="1">
                            <?= _JSHOP_YOO_ENABLE ?>
                        </label>

                        <label>
                            <input style="vertical-align:middle;margin:-2px 3px 0 0;cursor: pointer" name="pm_params[send_second_receipt]" <?= $params['send_second_receipt'] ? "" : "checked" ?> type="radio" value="0">
                            <?= _JSHOP_YOO_DISABLE ?>
                        </label>
                        <div class="secondReceiptArea row">
                            <div class="col">
                                <?= _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_INFO ?>
                            </div>
                        </div>
                        <div class="secondReceiptArea row">
                            <div>
                                <div class="col">
                                    <table style="max-width: 700px" class="table table-hover">
                                        <tbody>
                                        <tr>
                                            <td style="border: none">
                                                <?=_JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_STATUS_LABEL?>
                                            </td>
                                            <td style="border: none">
                                                <?php
                                                print JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                                                    'pm_params[kassa_second_receipt_status]',
                                                    'class="form-select form-control form-select-sm" size="1" data-type="kassa"', 'status_id', 'name',
                                                    $params['kassa_second_receipt_status']);
                                                ?>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <p class="help-block">
                                <?= _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_HELP_BLOCK ?>
                            </p>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <!--54-FZ end-->
        <!--Address for notifications-->
        <tr>
            <td>
                <label for="jform[joomlatoken][token]"><?= _JSHOP_YOO_NOTIFICATION_URL_LABEL ?></label>
            </td>
            <td>
                <div class="input-group has-success">
                    <input type="text" class="form-control valid form-control-success" name="jform[joomlatoken][token]" id="jform_joomlatoken_token" readonly=""
                           value="<?= escapeValue($notify_url) ?>" aria-invalid="false">
                </div>
                <p class="help-block"><?= _JSHOP_YOO_NOTIFICATION_URL_HELP_TEXT ?></p>
            </td>
        </tr>
        <!--Address for notifications end-->
        <!--Additional settings-->
        <tr>
            <td colspan="2">
                <h4><?= _JSHOP_YOO_COMMON_HEAD ?></h4>
            </td>
        </tr>
        <tr>
            <td>
                <label for="pm_params[kassa_transaction_end_status]"><?php echo _JSHOP_YOO_COMMON_STATUS; ?></label>
            </td>
            <td>
                <?php
                print JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                    'pm_params[kassa_transaction_end_status]',
                    'class="transaction-end-status form-select form-control form-select-sm" size="1" data-type="kassa"', 'status_id', 'name',
                    $params['kassa_transaction_end_status']);
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="pm_params-debug_log">Debug log</label>
            </td>
            <td>
                <select class="form-select form-control form-select-sm" name="pm_params[debug_log]" id="pm_params-debug_log">
                    <option value="1"<?= $params['debug_log'] == '1' ? ' selected' : '' ?>><?= _JSHOP_YOO_ENABLE ?></option>
                    <option value="0"<?= $params['debug_log'] == '1' ? '' : ' selected' ?>> <?= _JSHOP_YOO_DISABLE ?></option>
                </select>
                <a href="javascript://" id="show_module_log"><?= _JSHOP_YOO_LOG_VIEW_LABEL ?></a>
            </td>
        </tr>
        <!--Additional settings end-->
    </table>


    <div class="modal fade" id="log-modal-window" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4"><?= _JSHOP_YOO_LOGS_LABEL ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow:auto;max-height:60vh;">
                    <div style="padding:10px;">
                        <pre id="logs-list"></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-left" id="clear-logs"><?= _JSHOP_YOO_CLEAR_LOGS ?></button>
                </div>
            </div>
        </div>
    </div>


<?php echo JHtml::_('uitab.endTab'); ?>