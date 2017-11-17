<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('bootstrap.addTab', 'yamTab', 'payments-tab', _JSHOP_YM_TAB_PAYMENTS);

?>
<div class="row">
    <div class="span11 offset1">
        <p><?php echo _JSHOP_YM_PAYMENTS_HEAD; ?></p>
    </div>
</div>
<div class="row">
    <div class="span4 offset2">
        <div class='form-horizontal'>
            <div class="form-group">
                <input type = "checkbox" id="payments" class = "form-control pay-mode" name = "pm_params[paymentsmode]" value = "1"
                    <?php if(isSelected($params, 'paymentsmode')) echo "checked"; ?> /><?php echo _JSHOP_YM_PAYMENTS_ON; ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <div class="form-group">
            <div class="span2"><label for="pm_params[ym_pay_id]"><?php echo _JSHOP_YM_PAYMENTS_ID_LABEL ?></label></div>
            <div class='span8'>
                <input class="form-control span4" type="text" name="pm_params[ym_pay_id]" id="pm_params[ym_pay_id]" value="<?php echo escapeValue($params['ym_pay_id']); ?>">
                <p class="help-block">&nbsp;</p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <div class="form-group">
            <div class="span2"><label for="pm_params[ym_pay_desc]"><?php echo _JSHOP_YM_PAYMENTS_DESCRIPTION_LABEL; ?></label></div>
            <div class="span8">
                <input name="pm_params[ym_pay_desc]" type="text" class="form-control span8" id="pm_params[ym_pay_desc]"
                       value="<?php echo empty($params['ym_pay_desc']) ? _JSHOP_YM_PAYMENTS_DESCRIPTION_PLACEHOLDER : escapeValue($params['ym_pay_desc']); ?>"><br />
                <p class="help-block"><?php echo _JSHOP_YM_PAYMENTS_DESCRIPTION_INFO; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <div class="form-group">
            <div class="span2"><label for="pm_params[payments_transaction_end_status]" class="span2"><?php echo _JSHOP_YM_PAYMENTS_STATUS_LABEL; ?></label></div>
            <div class="span8">
                <?php
                echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[payments_transaction_end_status]', 'class="inputbox transaction-end-status" size="1" data-type="payments"', 'status_id', 'name', $params['payments_transaction_end_status'] );
                ?><br />
                <p class="help-block"><?php echo _JSHOP_YM_PAYMENTS_STATUS_INFO; ?></p>
            </div>
        </div>
    </div>
</div>
<?php echo JHtml::_('bootstrap.endTab'); ?>