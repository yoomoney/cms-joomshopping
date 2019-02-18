<?php

/**
 * @package JoomShopping for Joomla!
 * @subpackage payment
 * @author YandexMoney
 * @copyright Copyright (C) 2012-2017 YandexMoney. All rights reserved.
 */

defined('_JEXEC') or die('Restricted access');

const INSTALLMENTS_MIN_AMOUNT = 3000;

$cart_data = JSFactory::getModel('cart', 'jshop');
$cart_data->load();
?>

<input type="hidden" name="params[pm_yandex_money_sbbol][payment_type]" value="ym-b2b-sberbank"
       id="pm_yandex_money_payment_type"/>
