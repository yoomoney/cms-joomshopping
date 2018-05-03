<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('bootstrap.addTab', 'yamTab', 'kassa-tab', _JSHOP_YM_TAB_KASSA);

?>
<div class="row">
    <div class="span4 offset1">
        <p><?php echo _JSHOP_YM_LICENSE_TEXT; ?></p>
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
                <input type = "checkbox" id="kassa" class = "form-control pay-mode" name = "pm_params[kassamode]" value = "1"
                    <?php if(isSelected($params, 'kassamode')) echo "checked"; ?> /> <?php echo _JSHOP_YM_KASSA_ON; ?>
            </div>
        </div>
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
            <div class="span2"><label for="pm_params-shop-id"><?php echo _JSHOP_YM_KASSA_SHOP_ID_LABEL;?></label></div>
            <div class="span8">
                <input name="pm_params[shop_id]" type="text" class="form-control" id="pm_params-shop-id"
                       value="<?php echo escapeValue($params['shop_id']); ?>">
                <p class="help-block"><?php echo _JSHOP_YM_KASSA_SHOP_ID_DESCRIPTION; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <div class="form-group">
            <div class="span2"><label for="pm-params-shop-password" class=""><?php echo _JSHOP_YM_KASSA_PASSWORD_LABEL; ?></label></div>
            <div class="span8">
                <input name="pm_params[shop_password]" type="text" class="form-control" id="pm-params-shop-password"
                       value="<?php echo escapeValue($params['shop_password']); ?>">
                <p class="help-block"><?php echo _JSHOP_YM_KASSA_PASSWORD_DESCRIPTION; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <h4><?php echo _JSHOP_YM_KASSA_PAYMODE_HEAD; ?></h4>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <div class="span2"><label><?php echo _JSHOP_YM_KASSA_PAYMODE_LABEL; ?></label></div>
        <div class="span8">
            <input type = "radio" class = "paymode" name = "pm_params[paymode]" value = "1" onclick="yandex_validate_mode(1);"
                <?php if($params['paymode']=='1') echo "checked"; ?> /> <?php echo _JSHOP_YM_KASSA_PAYMODE_KASSA; ?><br>
            <div class="with-kassa">
            </div>
            <input type = "radio" class = "paymode" name = "pm_params[paymode]" value = "0" onclick="yandex_validate_mode(0);"
                <?php if($params['paymode']=='0') echo "checked"; ?> /> <?php echo _JSHOP_YM_KASSA_PAYMODE_SHOP; ?>
            <p class="help-block"><?php echo _JSHOP_YM_KASSA_PAYMODE_LINK; ?></p>
        </div>
    </div>
</div>
<div class="row with-select">
    <div class="span10 offset3">
        <p><?php echo _JSHOP_YM_KASSA_SELECT_TEXT; ?></p>
    </div>
</div>
<?php foreach (\YandexCheckout\Model\PaymentMethodType::getEnabledValues() as $value) : ?>
    <div class="row with-select">
        <div class="span11 offset1">
            <div class="span8 offset2">
                <input type = "checkbox" class = "form-control input-kassa" name = "pm_params[method_<?php echo $value; ?>]" value = "1"
                    <?php if($params['method_'.$value]=='1') echo "checked"; ?> />
                <?php echo constant('_JSHOP_YM_METHOD_'.strtoupper($value).'_DESCRIPTION');?>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div class="row">
    <div class="span11 offset1">
        <div class="span2"><label><?php echo _JSHOP_YM_KASSA_SEND_RECEIPT_LABEL; ?></label></div>
        <div class="span8">
            <input onclick="taxes_validate_mode(1)" type = "radio" class = "ya_kassa_send_check" name = "pm_params[ya_kassa_send_check]" value = "1"
                <?php if($params['ya_kassa_send_check']=='1') echo "checked"; ?> /> Включить <br>
            <input onclick="taxes_validate_mode(0)" type = "radio" class = "ya_kassa_send_check" name = "pm_params[ya_kassa_send_check]" value = "0"
                <?php if($params['ya_kassa_send_check']=='0') echo "checked"; ?> /> Выключить
        </div>
    </div>
</div>
<div class="taxesArea row">
    <div class="span11 offset1">
        <div class="span8 offset2"><label>Сопоставьте ставки</label></div>
    </div>
    <div class="span11 offset1">
        <div class="span2 offset2">Ставка в вашем магазине</div>
        <div class="span6">Ставка для чека в налоговую</div>
    </div>
    <?php foreach ($taxes as $k => $tax) { ?>
        <div class="span11 offset1 form-group row">
            <div class="span2 offset2"><label for="pm_params[ya_kassa_tax_<?php echo $k; ?>]" class=""><?php echo $tax; ?>%</label></div>
            <div class="span6">
                <select name="pm_params[ya_kassa_tax_<?php echo $k; ?>]" class=" fixed-width-xl" id="pm_params[ya_kassa_tax_<?php echo $k; ?>]">
                    <option <?php if ($params['ya_kassa_tax_'.$k] == 1) { ?> selected="selected" <?php } ?> value="1">Без НДС</option>
                    <option <?php if ($params['ya_kassa_tax_'.$k] == 2) { ?> selected="selected" <?php } ?> value="2">0%</option>
                    <option <?php if ($params['ya_kassa_tax_'.$k] == 3) { ?> selected="selected" <?php } ?> value="3">10%</option>
                    <option <?php if ($params['ya_kassa_tax_'.$k] == 4) { ?> selected="selected" <?php } ?> value="4">18%</option>
                    <option <?php if ($params['ya_kassa_tax_'.$k] == 5) { ?> selected="selected" <?php } ?> value="5">Расчётная ставка 10/110</option>
                    <option <?php if ($params['ya_kassa_tax_'.$k] == 6) { ?> selected="selected" <?php } ?> value="6">Расчётная ставка 18/118</option>
                </select>
            </div>
        </div>
    <?php } ?>
</div>

<div class="row">
    <div class="span11 offset1" style="padding-top: 10px;">
        <div class="form-group">
            <div class="span2"><label class="">Адрес для уведомлений</label></div>
            <div class="span8">
                <input class="form-control span8 disabled" value="<?php echo escapeValue($notify_url); ?>" disabled><br>
                <p class="help-block"><?php echo _JSHOP_YM_KASSA_HELP_CHECKURL; ?></p>
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
            <div class="span2"><label for="pm_params[kassa_transaction_end_status]"><?php echo _JSHOP_YM_COMMON_STATUS; ?></label></div>
            <div class="span8">
                <?php
                print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[kassa_transaction_end_status]', 'class="inputbox transaction-end-status" size="1" data-type="kassa"', 'status_id', 'name', $params['kassa_transaction_end_status'] );
                ?>
            </div>
        </div>
    </div>
    <div class="span11 offset1">
        <div class="form-group">
            <div class="span2"><label for="pm_params-debug_log">Debug log</label></div>
            <div class="span8">
                <select name="pm_params[debug_log]" id="pm_params-debug_log">
                    <option value="1"<?php $params['debug_log'] == '1' ? ' selected' : '' ?>>Включить</option>
                    <option value="0"<?php $params['debug_log'] == '1' ? '' : ' selected' ?>>Выключить</option>
                </select><br />
                <a href="javascript://" id="show_module_log">Просмотр логов модуля</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="log-modal-window" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Журнал сообщений модуля</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div style="padding:10px;">
                    <pre id="logs-list" style="overflow:scroll;"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="clear-logs">Очистить журнал</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<?php echo JHtml::_('bootstrap.endTab'); ?>