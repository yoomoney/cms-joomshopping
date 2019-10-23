<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

$uri = JURI::getInstance();
$liveurlhost = $uri->toString(array("scheme",'host', 'port'));
$sslurlhost = $uri->toString(array('host', 'port'));

$notify_url = 'https://'.$sslurlhost.SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_yandex_money&no_lang=1");
$notify_url = htmlspecialchars_decode($notify_url);

function isSelected($params, $type)
{
    $result = null;
    foreach (array('kassamode', 'moneymode', 'paymentsmode') as $mode) {
        if ($mode === $type) {
            if (!empty($params[$mode]) && $params[$mode] == 1) {
                $result = true;
            }
        } elseif (!empty($params[$mode]) && $params[$mode] == 1) {
            $result = false;
            break;
        }
    }
    return $result === true;
}

function escapeValue($value)
{
    return htmlspecialchars($value);
}

?>
<div class="col100">
    <fieldset class="adminform">
        <p><?php echo _JSHOP_YM_LICENSE_TEXT2; ?></p>
        <p><?php echo _JSHOP_YM_VERSION_DESCRIPTION; ?> <?php echo _JSHOP_YM_VERSION; ?></p>

        <?php echo JHtml::_('bootstrap.startTabSet', 'yamTab', array('active' => 'kassa-tab')); ?>

        <?php include(dirname(__FILE__).'/3x/yandex_kassa.php'); ?>
        <?php include(dirname(__FILE__).'/3x/yandex_money.php'); ?>
        <?php include(dirname(__FILE__).'/3x/yandex_payments.php'); ?>
        <?php if (isset($newVersionInfo)) : ?>
            <?php include(dirname(__FILE__).'/3x/yandex_update.php'); ?>
        <?php else: ?>
            <?php include(dirname(__FILE__).'/3x/yandex_update_disable.php'); ?>
        <?php endif; ?>

        <input type="hidden" name="pm_params[transaction_end_status]" id="transaction-end-status" />

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </fieldset>
</div>
<div class="clr"></div>
<script type="text/javascript">
    function yandex_validate_mode(paymode) {
        jQuery(function($) {
            if (paymode == 1) {
                $(".with-kassa").slideDown();
                $(".with-select").slideUp();
            } else {
                $(".with-kassa").slideUp();
                $(".with-select").slideDown();
            }
        });
    }

    function toggleShowSecondReceipt(isShow)
    {
        jQuery(function($) {
            if (isShow == 1) {
                $(".secondReceiptArea").show();
            } else {
                $(".secondReceiptArea").hide();
            }
        });
    }

    function taxes_validate_mode(paymode) {
        jQuery(function($) {
            if (paymode == 1) {
                $(".taxesArea").show();
            } else {
                $(".taxesArea").hide();
            }
        });
    }

    function yaKassaEnableHoldModeChangeHandler() {
        document.getElementById('ya_kassa_enable_hold_mode_extended_settings').style.display
            = document.getElementById('ya_kassa_enable_hold_mode').checked
            ? 'block'
            : 'none';
    }

    window.addEvent('domready', function() {
        yaKassaEnableHoldModeChangeHandler();
        document.getElementById('ya_kassa_enable_hold_mode').addEventListener("change", yaKassaEnableHoldModeChangeHandler);
        yandex_validate_mode(<?php if ($params['paymode']=='1') echo "1"; ?>);
        taxes_validate_mode(<?php if ($params['ya_kassa_send_check']=='1') echo "1"; ?>);
        jQuery('.pay-mode').change(function () {
            if (this.checked) {
                var self = this;
                jQuery('.pay-mode').each(function () {
                    if (this != self) {
                        this.checked = false;
                    } else {
                        var id = 'pm_params' + this.getAttribute('id') + '_transaction_end_status';
                        document.getElementById('transaction-end-status').value = document.getElementById(id).value;
                    }
                });
            }
        });

        jQuery(function ($) {
            toggleShowSecondReceipt($("input[name='pm_params[send_second_receipt]']:checked").val());

            $("input[name='pm_params[send_second_receipt]']").on('change', function (e) {
                toggleShowSecondReceipt($(this).val());
            })
        });

        jQuery('.transaction-end-status').change(function () {
            var id = this.dataset.type;
            if (document.getElementById(id).checked) {
                document.getElementById('transaction-end-status').value = this.value;
            }
        });
        var payModes = jQuery('.pay-mode');
        for (var i = 0; i < payModes.length; ++i) {
            if (payModes[i].checked) {
                var id = 'pm_params' + payModes[i].getAttribute('id') + '_transaction_end_status';
                document.getElementById('transaction-end-status').value = document.getElementById(id).value;
            }
        }
        jQuery('#show_module_log').click(showLogsHandler);
        jQuery('#clear-logs').click(clearLogsHandler);

        jQuery('#adminForm > ul.nav-tabs a:last').tab('show');
    });

    function showLogsHandler() {
        var form = document.getElementById('adminForm');
        var paymentId = form.payment_id.value;
        jQuery.ajax({
            method: 'GET',
            url: 'index.php',
            data: {
                option: 'com_jshopping',
                controller: 'payments',
                task: 'edit',
                payment_id: paymentId,
                subaction: 'get_log_messages'
            },
            dataType: 'json',
            success: function (logs) {
                if (logs.length > 0) {
                    jQuery('#logs-list').html(logs.join("\n"));
                    jQuery('#clear-logs').css('display', 'block');
                } else {
                    jQuery('#logs-list').html('Сообщений нет');
                    jQuery('#clear-logs').css('display', 'none');
                }
                jQuery('#log-modal-window').modal({
                    show: true
                });
            }
        });
    }

    function clearLogsHandler() {
        var form = document.getElementById('adminForm');
        var paymentId = form.payment_id.value;

        if (window.confirm('Вы действительно хотите очистить журнал сообщений?')) {
            jQuery.ajax({
                method: 'GET',
                url: 'index.php',
                data: {
                    option: 'com_jshopping',
                    controller: 'payments',
                    task: 'edit',
                    payment_id: paymentId,
                    subaction: 'clear_log_messages'
                },
                dataType: 'json',
                success: function (logs) {
                    jQuery('#log-modal-window').modal({
                        show: false
                    });
                }
            });
        }
    }
</script>