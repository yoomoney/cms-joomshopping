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
    <style>
        table.adminFormKassaTable td {
            vertical-align: top !important;
        }
        table.adminFormKassaTable td.key {
            width: 250px;
        }
    </style>
    <table class="admintable adminFormKassaTable" width = "100%" >
        <tr>
            <td colspan="2">
                <p><?php echo _JSHOP_YOO_MONEY_HEAD; ?></p>
            </td>
        </tr>
        <tr>
            <td class="key">
                <?php echo _JSHOP_YOO_MONEY_ON; ?>
            </td>
            <td>
                <input type = "checkbox" id="money" class="pay-mode" name = "pm_params[moneymode]" value = "1"
                    <?php if(isSelected($params, 'moneymode')) echo "checked"; ?> />
            </td>
        </tr>
        <tr>
            <td class="key">
                <label class="">RedirectURL</label>
            </td>
            <td>
                <div class="input-group has-success">
                    <input type="text" class="form-control valid form-control-success" name="jform[joomlatoken][token]" id="jform_joomlatoken_token" readonly=""
                           value="<?php echo escapeValue($notify_url); ?>" aria-invalid="false">
                </div>
                <p class="help-block"><?php echo _JSHOP_YOO_MONEY_REDIRECT_HELP; ?></p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h4><?php echo _JSHOP_YOO_MONEY_SET_HEAD; ?></h4>
            </td>
        </tr>
        <tr>
            <td class="key">
                <label for="pm_params[account]"><?php echo _JSHOP_YOO_MONEY_WALLET; ?></label>
            </td>
            <td>
                <input name='pm_params[account]' type="text" class="form-control" id="pm_params[account]"
                       value="<?php echo escapeValue($params['account']); ?>">
            </td>
        </tr>
        <tr>
            <td class="key">
                <label for="pm_params[password]"><?php echo _JSHOP_YOO_MONEY_PSW; ?></label>
            </td>
            <td>
                <input name='pm_params[password]' type="text" class="form-control" id="pm_params[account]"
                       value="<?php echo escapeValue($params['password']); ?>">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h4><?php echo _JSHOP_YOO_MONEY_SELECT_HEAD; ?></h4>
            </td>
        </tr>
        <tr>
            <td class="key">
                <?php echo _JSHOP_YOO_MONEY_SELECT_LABEL; ?>
            </td>
            <td>
                <?php
                $list_methods=array('ym2'=>'PC','cards2'=>'AC');
                foreach ($list_methods as $m_long => $m_short){?>
                    <div class="">
                        <input type = "checkbox" name = "pm_params[method_<?php echo $m_long; ?>]" value = "1"
                            <?php if($params['method_'.$m_long]=='1') echo "checked"; ?> />
                        <?php echo constant('_JSHOP_YOO_METHOD_'.strtoupper($m_long).'_DESCRIPTION');?>

                    </div>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h4><?php echo _JSHOP_YOO_COMMON_HEAD; ?></h4>
            </td>
        </tr>
        <tr>
            <td class="key">
                <label for="pm_params[money_transaction_end_status]"><?php echo _JSHOP_YOO_COMMON_STATUS; ?></label>
            </td>
            <td>
                <?php
                print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[money_transaction_end_status]',
                    'class="transaction-end-status form-select form-control form-select-sm" size="1" data-type="money"',
                    'status_id', 'name', $params['money_transaction_end_status'] );
                ?>
            </td>
        </tr>
    </table>

<?php echo JHtml::_('uitab.endTab'); ?>