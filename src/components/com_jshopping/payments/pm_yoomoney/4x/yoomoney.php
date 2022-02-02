<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YooMoney
 * @copyright Copyright (C) 2020 YooMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('uitab.addTab', 'yamTab', 'money-tab', _JSHOP_YOO_TAB_MONEY);

?>
    <div class="row">
        <p><?php echo _JSHOP_YOO_MONEY_HEAD; ?></p>
    </div>
    <div class="row mb-3">
        <div class="col-3">
            <label class="form-check-label" for="money">
                <?php echo _JSHOP_YOO_MONEY_ON; ?>
            </label>
        </div>
        <div class="col-3">
            <div class="form-check">
                <input class="form-check-input pay-mode" type="checkbox" id="money" name="pm_params[moneymode]"
                       value="1"
                    <?= isSelected($params, 'moneymode') ? "checked" : ''; ?> >
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-3">
            <label>RedirectURL</label>
        </div>
        <div class="col-9">
            <div class="input-group has-success">
                <input type="text" class="form-control valid form-control-success" name="jform[joomlatoken][token]" id="jform_joomlatoken_token" readonly=""
                       value="<?php echo escapeValue($notify_url); ?>" aria-invalid="false">
            </div>
            <div  class="form-text"><?php echo _JSHOP_YOO_MONEY_REDIRECT_HELP; ?></div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <h4><?php echo _JSHOP_YOO_MONEY_SET_HEAD; ?></h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-3">
            <label for="money-account"><?php echo _JSHOP_YOO_MONEY_WALLET; ?></label>
        </div>
        <div class="col-9">
            <input name="pm_params[account]" type="text" class="form-control" id="money-account"
                   value="<?= escapeValue($params['account']); ?>"
            >
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-3">
            <label for="money-password"><?php echo _JSHOP_YOO_MONEY_PSW; ?></label>
        </div>
        <div class="col-9">
            <input name="pm_params[password]" type="text" class="form-control" id="money-password"
                   value="<?= escapeValue($params['password']); ?>"
            >
        </div>
    </div>
    <div class="row  mb-3">
        <div class="col">
            <h4><?php echo _JSHOP_YOO_MONEY_SELECT_HEAD; ?></h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-3">
            <label for="money-password"><?php echo _JSHOP_YOO_MONEY_SELECT_LABEL; ?></label>
        </div>
        <div class="col-9">
            <?php
            foreach (array('ym2'=>'PC','cards2'=>'AC') as $m_long => $m_short){?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name = "pm_params[method_<?php echo $m_long; ?>]" value = "1"
                        <?= $params['method_'.$m_long] == '1' ? "checked" : ''; ?>
                            id="money-payment-method_<?php echo $m_long; ?>"
                    >
                    <label class="form-check-label" for="money-payment-method_<?php echo $m_long; ?>">
                        <?php echo constant('_JSHOP_YOO_METHOD_'.strtoupper($m_long).'_DESCRIPTION');?>
                    </label>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <h4><?php echo _JSHOP_YOO_COMMON_HEAD; ?></h4>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-3">
            <label for="money-password"><?php echo _JSHOP_YOO_COMMON_STATUS; ?></label>
        </div>
        <div class="col-9">
            <?php
            print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[money_transaction_end_status]',
                'class="transaction-end-status form-select form-control form-select-sm" size="1" data-type="money"',
                'status_id', 'name', $params['money_transaction_end_status'] );
            ?>
        </div>
    </div>

<?php echo JHtml::_('uitab.endTab'); ?>