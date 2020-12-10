<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YooMoney
 * @copyright Copyright (C) 2020 YooMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

const INSTALLMENTS_MIN_AMOUNT = 3000;

$cart_data = JSFactory::getModel('cart', 'jshop');
$cart_data->load();
?>

<input type="hidden" name="params[pm_yoomoney_sbbol][payment_type]" value="yoo-b2b-sberbank"
       id="pm_yoomoney_payment_type"/>
