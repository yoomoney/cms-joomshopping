<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YooMoney
 * @copyright Copyright (C) 2020 YooMoney. All rights reserved.
 */

use YooKassa\Model\PaymentMethodType;

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('bootstrap.addTab', 'yamTab', 'kassa-tab', _JSHOP_YOO_TAB_KASSA);

?>
    <div class="row">
        <div class="span4 offset1">
            <p><?php echo _JSHOP_YOO_LICENSE_TEXT; ?></p>
        </div>
    </div>
<?php if (isset($errorCredentials)) : ?>
    <div class="row">
        <div class="span10 offset1">
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $errorCredentials; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($testWarning)) : ?>
    <div class="row">
        <div class="span10 offset1">
            <div class="alert alert-info"><i class="fa fa-exclamation-circle"></i> <?php echo $testWarning; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    </div>
<?php endif; ?>
    <div class="row">
        <div class="span4 offset2">
            <div class='form-horizontal'>
                <div class="form-group">
                    <input type="checkbox" id="kassa" class="form-control pay-mode" name="pm_params[kassamode]"
                           value="1"
                        <?php if (isSelected($params, 'kassamode')) {
                            echo "checked";
                        } ?> /> <?php echo _JSHOP_YOO_KASSA_ON; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="span11 offset1">
            <h4><?php echo _JSHOP_YOO_KASSA_HEAD_LK; ?></h4>
        </div>
    </div>
    <div class="row">
        <div class="span11 offset1">
            <div class="form-group">
                <div class="span2"><label for="pm_params-shop-id"><?php echo _JSHOP_YOO_KASSA_SHOP_ID_LABEL; ?></label>
                </div>
                <div class="span8">
                    <input name="pm_params[shop_id]" type="text" class="form-control" id="pm_params-shop-id"
                           value="<?php echo escapeValue($params['shop_id']); ?>">
                    <p class="help-block"><?php echo _JSHOP_YOO_KASSA_SHOP_ID_DESCRIPTION; ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="span11 offset1">
            <div class="form-group">
                <div class="span2"><label for="pm-params-shop-password"
                                          class=""><?php echo _JSHOP_YOO_KASSA_PASSWORD_LABEL; ?></label></div>
                <div class="span8">
                    <input name="pm_params[shop_password]" type="text" class="form-control" id="pm-params-shop-password"
                           value="<?php echo escapeValue($params['shop_password']); ?>">
                    <p class="help-block"><?php echo _JSHOP_YOO_KASSA_PASSWORD_DESCRIPTION; ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="span11 offset1">
            <h4><?php echo _JSHOP_YOO_KASSA_PAYMODE_HEAD; ?></h4>
        </div>
    </div>
    <div class="row">
        <div class="span11 offset1">
            <div class="span2"><label><?php echo _JSHOP_YOO_KASSA_PAYMODE_LABEL; ?></label></div>
            <div class="span8">
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
                <p class="help-block"><?php echo _JSHOP_YOO_KASSA_PAYMODE_LINK; ?></p>
            </div>
        </div>
    </div>
    <div class="row with-select">
        <div class="span10 offset3">
            <p><?php echo _JSHOP_YOO_KASSA_SELECT_TEXT; ?></p>
        </div>
        <div style="display: none" id="warning_for_verify_file_install" class="span9 offset3 alert alert-warning"></div>
    </div>
<?php foreach ($params['paymentMethods'] as $value) : ?>
    <div class="row with-select">
        <div class="span11 offset1">
            <div class="span8 offset2">
                <input type="checkbox" class="form-control input-kassa"
                       name="pm_params[method_<?php echo $value; ?>]" value="1"
                    <?php if ($params['method_'.$value] == '1') {
                        echo "checked";
                    } ?> />
                <?php echo constant('_JSHOP_YOO_METHOD_'.strtoupper($value).'_DESCRIPTION'); ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
    <div class="row">
        <div class="span11 offset1">
            <div class="form-group">
                <div class="span2"><label for="yookassa_description_template"
                                          class=""><?= _JSHOP_YOO_DESCRIPTION_TITLE; ?></label></div>
                <div class="span8">
                    <input name="pm_params[yookassa_description_template]" type="text" class="form-control"
                           id="yookassa_description_template"
                           value="<?= !empty($params['yookassa_description_template'])
                               ? $params['yookassa_description_template']
                               : _JSHOP_YOO_DESCRIPTION_DEFAULT_PLACEHOLDER; ?>">
                    <p class="help-block"><?= _JSHOP_YOO_DESCRIPTION_HELP; ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="span11 offset1">
            <div class="form-group">
                <div class="span2"><label for="yookassa_enable_hold_mode"><?= _JSHOP_YOO_ENABLE_HOLD_MODE; ?></label>
                </div>
                <div class="span8">
                    <label>
                        <input type="checkbox" class="form-control input-kassa"
                               name="pm_params[yookassa_enable_hold_mode]"
                               value="1" id="yookassa_enable_hold_mode"
                            <?= $params['yookassa_enable_hold_mode'] == '1' ? "checked" : ''; ?> />
                        <span><?= _JSHOP_YOO_ENABLE_HOLD_MODE_HELP; ?></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div id="yookassa_enable_hold_mode_extended_settings">
        <div class="row">
            <div class="span11 offset1">
                <div class="form-group">
                    <div class="span10  offset2">
                        <p><?= _JSHOP_YOO_HOLD_MODE_STATUSES; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="span11 offset1">
                <div class="form-group">
                    <div class="span2  offset2">
                        <label for="pm_params[yookassa_hold_mode_on_hold_status]"><?= _JSHOP_YOO_HOLD_MODE_ON_HOLD_STATUS; ?></label>
                    </div>
                    <div class="span6">
                        <?= JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                            'pm_params[yookassa_hold_mode_on_hold_status]',
                            'class="inputbox" size="1" data-type="kassa"', 'status_id',
                            'name', $params['yookassa_hold_mode_on_hold_status']); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="span11 offset1">
                <div class="form-group">
                    <div class="span2  offset2">
                        <label for="pm_params[yookassa_hold_mode_cancel_status]"><?= _JSHOP_YOO_HOLD_MODE_CANCEL_STATUS; ?></label>
                    </div>
                    <div class="span6">
                        <?= JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                            'pm_params[yookassa_hold_mode_cancel_status]',
                            'class="inputbox" size="1" data-type="kassa"', 'status_id',
                            'name', $params['yookassa_hold_mode_cancel_status']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="span11 offset1">
            <div class="span2"><label><?php echo _JSHOP_YOO_KASSA_SEND_RECEIPT_LABEL; ?></label></div>
            <div class="span8">
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
            </div>
        </div>
    </div>
    <div class="taxesArea row">
        <div class="span11 offset1">
            <div class="span8 offset2">
                <label for="pm_params[yookassa_default_tax]"><?= _JSHOP_YOO_DEFAULT_TAX_LABEL; ?></label>
                <select name="pm_params[yookassa_default_tax]" class="fixed-width-xl"
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
        </div>
    </div>
    <div class="taxesArea row">
        <div class="span11 offset1">
            <div class="span8 offset2"><label><?= _JSHOP_YOO_TAX_RATES_LABEL ?></label></div>
        </div>
        <div class="span11 offset1">
            <div class="span2 offset2"><?= _JSHOP_YOO_TAX_IN_MODULE ?></div>
            <div class="span6"><?= _JSHOP_YOO_TAX_FOR_CHECKOUT ?></div>
        </div>
        <?php foreach ($taxes as $k => $tax) { ?>
            <div class="span11 offset1 form-group row">
                <div class="span2 offset2"><label for="pm_params[yookassa_tax_<?php echo $k; ?>]"
                                                  class=""><?php echo $tax; ?>%</label></div>
                <div class="span6">
                    <select name="pm_params[yookassa_tax_<?php echo $k; ?>]" class=" fixed-width-xl"
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
            </div>
        <?php } ?>
    </div>

    <div class="taxesArea row">
        <div class="span11 offset1">
            <div class="span8 offset2">
                <label for="pm_params[yookassa_default_tax_system]"><?= _JSHOP_YOO_DEFAULT_TAX_SYSTEM_LABEL; ?></label>
                <select name="pm_params[yookassa_default_tax_system]" class="fixed-width-xl" id="pm_params[yookassa_default_tax_system]">
                    <option value="">-</option>
                    <?php foreach(range(1, 6) as $tax_id) : ?>
                    <option <?php if ($params['yookassa_default_tax_system'] == $tax_id) { ?> selected="selected" <?php } ?>
                            value="<?= $tax_id ?>"><?= constant("_JSHOP_YOO_DEFAULT_TAX_SYSTEM_{$tax_id}_LABEL") ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="help-block"><?= _JSHOP_YOO_DEFAULT_TAX_DESCRIPTION; ?></p>
            </div>
        </div>
    </div>

    <div class="taxesArea row">
        <div class="span11 offset1">
            <div class="span8 offset2">
                <label for="pm_params[yookassa_default_payment_mode]"> Признак способа расчета</label>
                <select name="pm_params[yookassa_default_payment_mode]" class="fixed-width-xl"
                        id="pm_params[yookassa_default_payment_mode]">
                    <?php foreach ($params['paymentModeEnum'] as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $params['yookassa_default_payment_mode'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="taxesArea row">
        <div class="span11 offset1">
            <div class="span8 offset2">
                <label for="pm_params[yookassa_default_payment_subject]"> Признак предмета расчета</label>
                <select name="pm_params[yookassa_default_payment_subject]" class="fixed-width-xl"
                        id="pm_params[yookassa_default_payment_subject]">
                    <?php foreach ($params['paymentSubjectEnum'] as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $params['yookassa_default_payment_subject'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="taxesArea row">
        <div class="span11 offset1">
            <div class="span8 offset2">
                <label for="pm_params[yookassa_default_delivery_payment_mode]"> Признак способа расчета для
                    доставки</label>
                <select name="pm_params[yookassa_default_delivery_payment_mode]" class="fixed-width-xl"
                        id="pm_params[yookassa_default_delivery_payment_mode]">
                    <?php foreach ($params['paymentModeEnum'] as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $params['yookassa_default_delivery_payment_mode'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="taxesArea row">
        <div class="span11 offset1">
            <div class="span8 offset2">
                <label for="pm_params[yookassa_default_delivery_payment_subject]"> Признак предмета расчета для доставки</label>
                <select name="pm_params[yookassa_default_delivery_payment_subject]" class="fixed-width-xl"
                        id="pm_params[yookassa_default_delivery_payment_subject]">
                    <?php foreach ($params['paymentSubjectEnum'] as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $params['yookassa_default_delivery_payment_subject'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="taxesArea row">
        <div class="span11 offset1">
            <div class="span8 offset2">
                <?= _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_LABEL ?>
                <label>
                    <input style="vertical-align:middle;margin:-2px 3px 0 0;cursor: pointer" name="pm_params[send_second_receipt]" <?= $params['send_second_receipt'] ? "checked" : "" ?> type="radio" value="1">
                    <?= _JSHOP_YOO_ENABLE ?>
                </label>

                <label>
                    <input style="vertical-align:middle;margin:-2px 3px 0 0;cursor: pointer" name="pm_params[send_second_receipt]" <?= $params['send_second_receipt'] ? "" : "checked" ?> type="radio" value="0">
                    <?= _JSHOP_YOO_DISABLE ?>
                </label>
            </div>
            <div class="secondReceiptArea row offset2">
                <div class="span8">
                    <?= _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_INFO ?>
                </div>
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
                                '', 'status_id', 'name',
                                $params['kassa_second_receipt_status']);
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <p class="help-block">
                    <?= _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_HELP_BLOCK ?>
                </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="span11 offset1" style="padding-top: 10px;">
            <div class="form-group">
                <div class="span2"><label class=""><?= _JSHOP_YOO_NOTIFICATION_URL_LABEL ?></label></div>
                <div class="span8">
                    <input class="form-control span8 disabled" value="<?= escapeValue($notify_url) ?>" disabled><br>
                    <p class="help-block"><?= _JSHOP_YOO_NOTIFICATION_URL_HELP_TEXT ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="span11 offset1">
            <h4><?= _JSHOP_YOO_COMMON_HEAD ?></h4>
        </div>
    </div>
    <div class="row">
        <div class="span11 offset1">
            <div class="form-group">
                <div class="span2"><label
                            for="pm_params[kassa_transaction_end_status]"><?php echo _JSHOP_YOO_COMMON_STATUS; ?></label>
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
    <div class="row">
        <div class="span11 offset1">
            <div class="form-group">
                <div class="span2"><label for="pm_params-debug_log">Debug log</label></div>
                <div class="span8">
                    <select name="pm_params[debug_log]" id="pm_params-debug_log">
                        <option value="1"<?= $params['debug_log'] == '1' ? ' selected' : '' ?>><?= _JSHOP_YOO_ENABLE ?></option>
                        <option value="0"<?= $params['debug_log'] == '1' ? '' : ' selected' ?>> <?= _JSHOP_YOO_DISABLE ?></option>
                    </select><br/>
                    <a href="javascript://" id="show_module_log"><?= _JSHOP_YOO_LOG_VIEW_LABEL ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal hide fade" id="log-modal-window">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3><?= _JSHOP_YOO_LOGS_LABEL ?></h3>
        </div>
        <div style="overflow:auto;max-height:60vh;" class="modal-body">
            <div style="padding:10px;">
                <pre id="logs-list"></pre>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger pull-left" id="clear-logs"><?= _JSHOP_YOO_CLEAR_LOGS ?></button>
            <button type="button" class="btn" data-dismiss="modal"><?= _JSHOP_YOO_CLOSE ?></button>
        </div>
    </div>

<?php echo JHtml::_('bootstrap.endTab'); ?>