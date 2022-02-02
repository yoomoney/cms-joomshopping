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
    <div class="row mb-3">
        <div class="col-3">
            <label class="form-check-label" for="kassa">
                <?php echo _JSHOP_YOO_KASSA_ON; ?>
            </label>
        </div>
        <div class="col-3">
            <div class="form-check">
                <input class="form-check-input pay-mode" type="checkbox" id="kassa" name="pm_params[kassamode]"
                       value="1"
                    <?php if (isSelected($params, 'kassamode')) {
                        echo "checked";
                    } ?> >
            </div>
        </div>
    </div>

    <div class="row  mb-3">
        <div class="col">
            <h4><?php echo _JSHOP_YOO_KASSA_HEAD_LK; ?></h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-3">
            <label for="pm_params-shop-id">
                <?php echo _JSHOP_YOO_KASSA_SHOP_ID_LABEL; ?>
            </label>
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
    <div class="row  mb-3">
        <div class="col">
            <h4><?php echo _JSHOP_YOO_KASSA_PAYMODE_HEAD; ?></h4>
        </div>
    </div>

    <fieldset  class="row  mb-3">
        <legend class="col-form-label col-3 pt-0"><?php echo _JSHOP_YOO_KASSA_PAYMODE_LABEL; ?></legend>
        <div class="col-9">
            <div class="form-check">
                <input class="form-check-input paymode" type="radio" name="pm_params[paymode]"
                       id="paymode-yookassa" value="1" onclick="yoomoney_validate_mode(1);"
                    <?php echo $params['paymode'] == '1' ? "checked" : '' ?>
                >
                <label class="form-check-label" for="paymode-yookassa">
                    <?php echo _JSHOP_YOO_KASSA_PAYMODE_KASSA; ?>
                </label>
            </div>
            <div class="with-kassa">
            </div>
            <div class="form-check">
                <input class="form-check-input paymode" type="radio" name="pm_params[paymode]"
                       id="paymode-website" value="0" onclick="yoomoney_validate_mode(0);"
                    <?php echo $params['paymode'] == '0' ? "checked" : '' ?>
                >
                <label class="form-check-label" for="paymode-website">
                    <?php echo _JSHOP_YOO_KASSA_PAYMODE_SHOP; ?>
                </label>
                <div  class="form-text"><?php echo _JSHOP_YOO_KASSA_PAYMODE_LINK; ?></div>
            </div>
        </div>
    </fieldset>

    <div class="row mb-3 with-select">
        <div class="col-3">
            <label for="pm_params-shop-password">
                <?php echo _JSHOP_YOO_KASSA_SELECT_TEXT; ?>
            </label>
        </div>

        <div class="col-9">
            <div id="warning_for_verify_file_install" class="hidden alert alert-warning"></div>
            <div id="success_for_verify_file_install" class="hidden alert alert-success"></div>
        <?php foreach ($params['paymentMethods'] as $value) : ?>
            <div class="form-check">
                <input class="form-check-input input-kassa" type="checkbox" id="payment-method_<?php echo $value; ?>"
                       name="pm_params[method_<?php echo $value; ?>]" value="1"
                    <?php echo $params['method_'.$value] == '1' ? "checked" : '' ?>
                >
                <label class="form-check-label" for="payment-method_<?php echo $value; ?>">
                    <?php echo constant('_JSHOP_YOO_METHOD_'.strtoupper($value).'_DESCRIPTION'); ?>
                </label>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-3">
            <label for="yookassa_description_template">
                <?php echo _JSHOP_YOO_DESCRIPTION_TITLE; ?>
            </label>
        </div>

        <div class="col-9">
            <input name="pm_params[yookassa_description_template]" type="text" class="form-control"
                   id="yookassa_description_template"
                   value="<?= !empty($params['yookassa_description_template'])
                       ? $params['yookassa_description_template']
                       : _JSHOP_YOO_DESCRIPTION_DEFAULT_PLACEHOLDER; ?>"
            >
            <div  class="form-text"><?php echo _JSHOP_YOO_DESCRIPTION_HELP; ?></div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-3">
            <label for="yookassa_enable_hold_mode">
                <?php echo _JSHOP_YOO_ENABLE_HOLD_MODE; ?>
            </label>
        </div>
        <div class="col-9">
            <div class="form-check">
                <input class="form-check-input input-kassa" type="checkbox" id="yookassa_enable_hold_mode"
                       name="pm_params[yookassa_enable_hold_mode]"
                       value="1" id="yookassa_enable_hold_mode"
                    <?= $params['yookassa_enable_hold_mode'] == '1' ? "checked" : ''; ?>
                >
            </div>
            <div  class="form-text"><?php echo _JSHOP_YOO_ENABLE_HOLD_MODE_HELP; ?></div>
            <div id="yookassa_enable_hold_mode_extended_settings">
                <div class="row mt-3 mb-0">
                    <p><?php echo _JSHOP_YOO_HOLD_MODE_STATUSES; ?></p>
                </div>
                <div class="row mb-3">
                    <div class="col-2">
                        <label for="yookassa_hold_mode_on_hold_status">
                            <?php echo _JSHOP_YOO_HOLD_MODE_ON_HOLD_STATUS; ?>
                        </label>
                    </div>
                    <div class="col-3">
                        <?= JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                            'pm_params[yookassa_hold_mode_on_hold_status]',
                            'class="form-select form-control form-select-sm" size="1" data-type="kassa"', 'status_id',
                            'name', $params['yookassa_hold_mode_on_hold_status']); ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-2">
                        <label for="yookassa_hold_mode_cancel_status">
                            <?php echo _JSHOP_YOO_HOLD_MODE_CANCEL_STATUS; ?>
                        </label>
                    </div>
                    <div class="col-3">
                        <?= JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                            'pm_params[yookassa_hold_mode_cancel_status]',
                            'class="form-select form-control form-select-sm" size="1" data-type="kassa"', 'status_id',
                            'name', $params['yookassa_hold_mode_cancel_status']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <fieldset  class="row mb-3">
        <legend class="col-form-label col-3 pt-0"><?php echo _JSHOP_YOO_KASSA_SEND_RECEIPT_LABEL; ?></legend>
        <div class="col-9">
            <div class="form-check">
                <input class="form-check-input yookassa_send_check" type="radio" name="pm_params[yookassa_send_check]"
                       id="yookassa_send_check-enable" value="1" onclick="taxes_validate_mode(1)"
                    <?php echo $params['yookassa_send_check'] == '1' ? "checked" : '' ?>
                >
                <label class="form-check-label" for="yookassa_send_check-enable">
                    <?php echo _JSHOP_YOO_ENABLE; ?>
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input yookassa_send_check" type="radio" name="pm_params[yookassa_send_check]"
                       id="yookassa_send_check-disable" value="0" onclick="taxes_validate_mode(0)"
                    <?php echo $params['yookassa_send_check'] == '0' ? "checked" : '' ?>
                >
                <label class="form-check-label" for="yookassa_send_check-disable">
                    <?php echo _JSHOP_YOO_DISABLE; ?>
                </label>
            </div>
            <div class="taxesArea">
                <div class="row mt-3">
                    <div class="col-auto">
                        <label for="yookassa_default_tax"
                               class="col-form-label"><?= _JSHOP_YOO_DEFAULT_TAX_LABEL; ?></label>
                    </div>
                    <div class="col-auto">
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
                    </div>
                    <div class="form-text"><?php echo _JSHOP_YOO_DEFAULT_TAX_DESCRIPTION; ?></div>
                </div>
                <p class="mt-3 mb-1"><?= _JSHOP_YOO_TAX_RATES_LABEL ?>:</p>
                <div class="row mt-1 mb-1">
                    <div class="col-4">
                        <?= _JSHOP_YOO_TAX_IN_MODULE ?>:
                    </div>
                    <div class="col-4">
                        <?= _JSHOP_YOO_TAX_FOR_CHECKOUT ?>:
                    </div>
                </div>
                <?php foreach ($taxes as $k => $tax) { ?>
                    <div class="row mb-2">
                        <div class="col-4">
                            <label class=""><?php echo $tax; ?>%</label>
                        </div>
                        <div class="col-4">
                            <select name="pm_params[yookassa_tax_<?php echo $k; ?>]"
                                    class="form-select form-control form-select-sm"
                            >
                                <option <?php if ($params['yookassa_tax_' . $k] == 1) { ?> selected="selected" <?php } ?>
                                        value="1"><?= _JSHOP_YOO_WITHOUT_VAT ?></option>
                                <option <?php if ($params['yookassa_tax_' . $k] == 2) { ?> selected="selected" <?php } ?>
                                        value="2"><?= _JSHOP_YOO_VAT_0 ?></option>
                                <option <?php if ($params['yookassa_tax_' . $k] == 3) { ?> selected="selected" <?php } ?>
                                        value="3"><?= _JSHOP_YOO_VAT_10 ?></option>
                                <option <?php if ($params['yookassa_tax_' . $k] == 4) { ?> selected="selected" <?php } ?>
                                        value="4"><?= _JSHOP_YOO_VAT_20 ?></option>
                                <option <?php if ($params['yookassa_tax_' . $k] == 5) { ?> selected="selected" <?php } ?>
                                        value="5"><?= _JSHOP_YOO_VAT_10_100 ?></option>
                                <option <?php if ($params['yookassa_tax_' . $k] == 6) { ?> selected="selected" <?php } ?>
                                        value="6"><?= _JSHOP_YOO_VAT_20_120 ?></option>
                            </select>
                        </div>
                    </div>
                <?php } ?>
                <div class="row mt-4">
                    <div class="col-auto">
                        <label for="pm_params[yookassa_default_tax_system]"><?= _JSHOP_YOO_DEFAULT_TAX_SYSTEM_LABEL; ?></label>
                    </div>
                    <div class="col-auto">
                        <select name="pm_params[yookassa_default_tax_system]"
                                class="form-select form-control form-select-sm"
                                id="pm_params[yookassa_default_tax_system]">
                            <option value="">-</option>
                            <?php foreach (range(1, 6) as $tax_id) : ?>
                                <option <?php if ($params['yookassa_default_tax_system'] == $tax_id) { ?> selected="selected" <?php } ?>
                                        value="<?= $tax_id ?>"><?= constant("_JSHOP_YOO_DEFAULT_TAX_SYSTEM_{$tax_id}_LABEL") ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-text"><?php echo _JSHOP_YOO_DEFAULT_TAX_DESCRIPTION; ?></div>
                </div>
                <div class="row mt-4 mb-3">
                    <div class="col-auto">
                        <label for="pm_params[yookassa_default_payment_mode]"> Признак способа расчета</label>
                    </div>
                    <div class="col-auto">
                        <select name="pm_params[yookassa_default_payment_mode]"
                                class="form-select form-control form-select-sm"
                                id="pm_params[yookassa_default_payment_mode]">
                            <?php foreach ($params['paymentModeEnum'] as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $params['yookassa_default_payment_mode'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-auto">
                        <label for="pm_params[yookassa_default_payment_subject]">Признак предмета расчета</label>
                    </div>
                    <div class="col-auto">
                        <select name="pm_params[yookassa_default_payment_subject]"
                                class="form-select form-control form-select-sm"
                                id="pm_params[yookassa_default_payment_subject]">
                            <?php foreach ($params['paymentSubjectEnum'] as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $params['yookassa_default_payment_subject'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-auto">
                        <label for="pm_params[yookassa_default_delivery_payment_mode]"> Признак способа расчета для
                            доставки</label>
                    </div>
                    <div class="col-auto">
                        <select name="pm_params[yookassa_default_delivery_payment_mode]"
                                class="form-select form-control form-select-sm"
                                id="pm_params[yookassa_default_delivery_payment_mode]">
                            <?php foreach ($params['paymentModeEnum'] as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $params['yookassa_default_delivery_payment_mode'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-auto">
                        <label for="pm_params[yookassa_default_delivery_payment_subject]">
                            Признак предмета расчета для доставки
                        </label>
                    </div>
                    <div class="col-auto">
                        <select name="pm_params[yookassa_default_delivery_payment_subject]"
                                class="form-select form-control form-select-sm"
                                id="pm_params[yookassa_default_delivery_payment_subject]">
                            <?php foreach ($params['paymentSubjectEnum'] as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $params['yookassa_default_delivery_payment_subject'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mt-3 mb-3">
                    <div class="col-auto">
                        <label>
                            <?= _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_LABEL ?>
                        </label>
                    </div>
                    <div class="col-auto">
                        <div class="form-check">
                            <input class="form-check-input"
                                   name="pm_params[send_second_receipt]"
                                <?= $params['send_second_receipt'] ? "checked" : "" ?>
                                   type="radio" value="1" id="send_second_receipt-on"
                            >
                            <label class="form-check-label" for="send_second_receipt-on">
                                <?= _JSHOP_YOO_ENABLE ?>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input"
                                   name="pm_params[send_second_receipt]"
                                <?= $params['send_second_receipt'] ? "" : "checked" ?>
                                   type="radio" value="0" id="send_second_receipt-off"
                            >
                            <label class="form-check-label" for="send_second_receipt-off">
                                <?= _JSHOP_YOO_DISABLE ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3 secondReceiptArea">
                    <div class="mb-3 form-text"><?php echo _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_INFO; ?></div>
                    <div class="col-auto">
                        <label>
                            <?= _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_STATUS_LABEL; ?>
                        </label>
                    </div>
                    <div class="col-auto">
                        <?php
                        print JHTML::_('select.genericlist', $orders->getAllOrderStatus(),
                            'pm_params[kassa_second_receipt_status]',
                            'class="form-select form-control form-select-sm" size="1" data-type="kassa"', 'status_id', 'name',
                            $params['kassa_second_receipt_status']);
                        ?>
                    </div>
                    <div class="mt-3 form-text"><?php echo _JSHOP_YOO_KASSA_SEND_SECOND_RECEIPT_HELP_BLOCK; ?></div>
                </div>
            </div>
        </div>
    </fieldset>

    <div class="row mb-3">
        <div class="col-3">
            <label for="jform_joomlatoken_token">
                <?php echo _JSHOP_YOO_NOTIFICATION_URL_LABEL; ?>
            </label>
        </div>

        <div class="col-9 ">
            <div class="input-group has-success">
                <input type="text" class="form-control valid form-control-success"
                       name="jform[joomlatoken][token]" id="jform_joomlatoken_token" readonly=""
                       value="<?= escapeValue($notify_url) ?>" aria-invalid="false"
                >
            </div>
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
    <div class="row mb-3">
        <div class="col-3">
            <label for="Debug log">Debug log</label>
        </div>
        <div class="col-9">
            <select class="form-select form-control form-select-sm" name="pm_params[debug_log]" id="pm_params-debug_log">
                <option value="1"<?= $params['debug_log'] == '1' ? ' selected' : '' ?>><?= _JSHOP_YOO_ENABLE ?></option>
                <option value="0"<?= $params['debug_log'] == '1' ? '' : ' selected' ?>> <?= _JSHOP_YOO_DISABLE ?></option>
            </select>
            <div  class="mb-3 form-text">
                <a href="javascript://" id="show_module_log"><?= _JSHOP_YOO_LOG_VIEW_LABEL ?></a>
            </div>

        </div>
    </div>

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