<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('bootstrap.addTab', 'yamTab', 'money-tab', _JSHOP_YM_TAB_MONEY);

?>
<div class="row">
    <div class="span11 offset1">
        <p><?php echo _JSHOP_YM_MONEY_HEAD; ?></p>
    </div>
</div>
<div class="row">
    <div class="span4 offset2">
        <div class='form-horizontal'>
            <div class="form-group">
                <input type = "checkbox" id="money" class = "form-control pay-mode" name = "pm_params[moneymode]" value = "1"
                <?php if(isSelected($params, 'moneymode')) echo "checked"; ?> /><?php echo _JSHOP_YM_MONEY_ON; ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <div class="form-group">
            <div class="span2"><label class="">RedirectURL</label></div>
            <div class='span8'>
                <input class='form-control span8 disabled' value='<?php echo escapeValue($notify_url); ?>' disabled><br>
                <p class="help-block"><?php echo _JSHOP_YM_MONEY_REDIRECT_HELP; ?></p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <h4><?php echo _JSHOP_YM_MONEY_SET_HEAD; ?></h4>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <div class="form-group">
            <div class="span2"><label for="pm_params[account]"><?php echo _JSHOP_YM_MONEY_WALLET; ?></label></div>
            <div class="span8">
                <input name='pm_params[account]' type="text" class="form-control" id="pm_params[account]"
                       value="<?php echo escapeValue($params['account']); ?>">
                <p class="help-block">&nbsp;</p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <div class="form-group">
            <div class="span2"><label for="pm_params[password]" class="span2"><?php echo _JSHOP_YM_MONEY_PSW; ?></label></div>
            <div class="span8">
                <input name='pm_params[password]' type="text" class="form-control" id="pm_params[password]"
                       value="<?php echo escapeValue($params['password']); ?>">
                <p class="help-block">&nbsp;</p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <h4><?php echo _JSHOP_YM_MONEY_SELECT_HEAD; ?></h4>
    </div>
</div>
<div class="row">
    <div class="span11 offset1">
        <div class="span2"><label><?php echo _JSHOP_YM_MONEY_SELECT_LABEL; ?></label></div>
        <div class="span8">
            <?php
            $list_methods=array('ym2'=>'PC','cards2'=>'AC');
            foreach ($list_methods as $m_long => $m_short){?>
                <div class="">
                    <input type = "checkbox" class = "form-control input-p2p" name = "pm_params[method_<?php echo $m_long; ?>]" value = "1"
                        <?php if($params['method_'.$m_long]=='1') echo "checked"; ?> />
                    <?php echo constant('_JSHOP_YM_METHOD_'.strtoupper($m_long).'_DESCRIPTION');?>

                </div>
            <?php } ?>
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
            <div class="span2"><label for="pm_params[money_transaction_end_status]"><?php echo _JSHOP_YM_COMMON_STATUS; ?></label></div>
            <div class="span8">
                <?php
                print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[money_transaction_end_status]', 'class="inputbox transaction-end-status" size="1" data-type="money"', 'status_id', 'name', $params['money_transaction_end_status'] );
                ?>
            </div>
        </div>
    </div>
</div>

<?php echo JHtml::_('bootstrap.endTab'); ?>