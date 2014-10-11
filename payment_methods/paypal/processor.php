<?php

/*
  Module developed for the Open Source Content Management System WebsiteBaker (http://websitebaker.org)
  Copyright (C) 2012, Christoph Marti

  LICENCE TERMS:
  This module is free software. You can redistribute it and/or modify it 
  under the terms of the GNU General Public License - version 2 or later, 
  as published by the Free Software Foundation: http://www.gnu.org/licenses/gpl.html.

  DISCLAIMER:
  This module is distributed in the hope that it will be useful, 
  but WITHOUT ANY WARRANTY; without even the implied warranty of 
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
  GNU General Public License for more details.
*/


// Prevent this file from being accessed directly
if (defined('WB_PATH') == false) {
	exit("Cannot access this file directly"); 
}

// Use payment gateway sandbox url for testing
$testing = false;

// Include info file
include(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/info.php');

// Look for payment method language file
if (LANGUAGE_LOADED) {
    include(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/languages/EN.php');
    if (file_exists(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/languages/'.LANGUAGE.'.php')) {
        include(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/languages/'.LANGUAGE.'.php');
    }
}

// Get the payment method settings from db
$query_payment_methods = $database->query("SELECT value_1, value_2, value_3, value_4, value_5, value_6 FROM ".TABLE_PREFIX."mod_bakery_payment_methods WHERE directory = '$payment_method'");
if ($query_payment_methods->numRows() > 0) {
	$payment_methods = $query_payment_methods->fetchRow();
	// value_1 to value_6 correspond to the payment method settings field_1 to field_6 in the info.php file
	$value_1 = stripslashes($payment_methods['value_1']);
	$value_2 = stripslashes($payment_methods['value_2']);
	$value_3 = stripslashes($payment_methods['value_3']);
	$value_4 = stripslashes($payment_methods['value_4']);
	$value_5 = stripslashes($payment_methods['value_5']);
	$value_6 = stripslashes($payment_methods['value_6']);
}

// Get customer data from db
$query_customer = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_bakery_customer WHERE order_id = '{$_SESSION['bakery']['order_id']}'");
if ($query_customer->numRows() > 0) {
	$fetch_customer = $query_customer->fetchRow();
	$customer = array_map('stripslashes', $fetch_customer);
}

// Convert state code to state name (except US)
if ($customer['cust_country'] != "US") {
	if (file_exists(WB_PATH.'/modules/bakery/languages/states/'.$setting_shop_country.'.php')) {
		require_once(WB_PATH.'/modules/bakery/languages/states/'.$setting_shop_country.'.php');
	}
	if (defined('DEFAULT_CHARSET')) { $charset = DEFAULT_CHARSET; } else { $charset = 'utf-8'; }
	if ($state_key = array_keys($MOD_BAKERY['TXT_STATE_CODE'], $customer['cust_state'])) {
		$cust_state = $MOD_BAKERY['TXT_STATE_NAME'][$state_key[0]];
		$cust_state = entities_to_umlauts($cust_state, $charset);
	}
}


// ********************************************************************************* //
//
//  MODIFY THE LINES BELOW TO FIT THE REQUIREMENTS OF THE PAYMENT GATEWAY PROVIDER
//
// ********************************************************************************* //

// URL of the payment gateway provider
$payment_gateway_url = "https://www.paypal.com/cgi-bin/webscr";
$sandbox_url = "https://sandbox.paypal.com/cgi-bin/webscr";
$security_info_url = "https://www.paypal.com/".strtolower($setting_shop_country)."/cgi-bin/webscr?cmd=_security-center-outside";

// Make array with data sent to payment gateway
$post_data = array(
	'cmd'           => '_ext-enter',
	'redirect_cmd'  => '_xclick',
	'business'      => $value_1,
	'item_name'     => $MOD_BAKERY[$payment_method]['TXT_AGGREGATED_ITEMS'],
	'invoice'       => $_SESSION['bakery']['order_id'],
	'page_style'    => $value_2,
	'no_shipping'   => 1,
	'currency_code' => $setting_shop_currency,
	'amount'        => $_SESSION['bakery']['order_total'],
	'charset'       => $charset,
	'email'         => $customer['cust_email'],
	'first_name'    => $customer['cust_first_name'],
	'last_name'     => $customer['cust_last_name'],
	'address1'      => $customer['cust_street'],
	'city'          => $customer['cust_city'],
	'state'         => $customer['cust_state'],
	'country'       => $customer['cust_country'],
	'zip'           => $customer['cust_zip'],
	'night_phone_b' => $customer['cust_phone'],
	'return'        => $setting_continue_url.'?pm='.$payment_method,
	'notify_url'    => WB_URL . '/modules/bakery/payment_methods/' . $payment_method . '/ipn.php',
	'cancel_return' => $setting_continue_url.'?pm='.$payment_method.'&status=canceled'
);

// ********************************************************************************* //
//
//    DO NOT CHANGE ANYTHING BELOW THIS LINE UNLESS YOU KNOW WHAT YOU ARE DOING
//
// ********************************************************************************* //




// Set URL for testing or productiv site
$payment_gateway_url = $testing ? $sandbox_url : $payment_gateway_url;


// DIRECT CHECKOUT
// ***************

if ($skip_checkout) {
	// Generate post form
	echo "<form name='post_data' action='$payment_gateway_url' method='post'>";
	foreach ($post_data as $name => $value) {
		echo "\n\t\t\t<input type='hidden' name='$name' value='$value' />";
	}
	?>
		  
	<script language="javascript" type="text/javascript">
		document.write('<div style="margin: 50px 0; text-align: center"><p><?php echo $MOD_BAKERY[$payment_method]['TXT_REDIRECT']; ?>..</p><img src="<?php echo WB_URL; ?>/modules/bakery/images/loader.gif" style="margin-top: 20px;" alt="Loading" width="32" height="32" \/></div>');
		window.setTimeout("document.post_data.submit()", 500);
	</script>
	
	<noscript>
		<div class='mod_bakery_success_f' style='margin: 50px 0 30px 0;'><p><?php echo $MOD_BAKERY[$payment_method]['TXT_REDIRECT']; ?></p></div>
		<input type="submit" class="mod_bakery_bt_pay_<?php echo $payment_method ?>_f" value="<?php echo $MOD_BAKERY[$payment_method]['TXT_REDIRECT_NOW']; ?>" />
	</noscript>
	</form>
	<?PHP



// SHOW CHECKOUT
// *************

} else {

/*	(Not needed for paypal)
	// Check if there has been a payment method error
	$pm_error_msg = '';
	$pay_error = isset($_GET['pay_error']) ? $_GET['pay_error'] : 0;
	switch ($pay_error) {
		case 1:
		$pm_error_msg = "<tr>\n<td colspan='2'><div class='mod_bakery_error_f' style='margin-bottom: 10px'><p>{$MOD_BAKERY[$payment_method]['ERROR_CREATING_PM']}</p></div>\n</td>\n</tr>";
		break;
		
		case 2:
		$pm_error_msg = "<tr>\n<td colspan='2'><div class='mod_bakery_error_f' style='margin-bottom: 10px'><p>{$MOD_BAKERY[$payment_method]['ERROR_NO_BANK_SELECTED']}</p></div>\n</td>\n</tr>";
		break;
	} */

	?>
	<tr>
	  <td colspan="2"><h3 class="mod_bakery_pay_h_f"><?PHP echo $MOD_BAKERY[$payment_method]['TXT_TITLE']; ?> <img src="<?php echo WB_URL ?>/modules/bakery/images/mastercard.gif" alt="Logo Mastercard" width="37" height="21" /><img src="<?php echo WB_URL ?>/modules/bakery/images/visa.gif" alt="Logo Visa" width="37" height="21" /> <img src="<?php echo WB_URL ?>/modules/bakery/images/amex.gif" alt="Logo American Express" width="37" height="21" /></h3></td>
	</tr>
	<tr>
	  <td colspan="2" class="mod_bakery_pay_td_f"><?PHP echo $MOD_BAKERY[$payment_method]['TXT_PAY_ONLINE_1']; ?><br />
		<?PHP echo $MOD_BAKERY[$payment_method]['TXT_SECURITY']; ?><a href="<?php echo $security_info_url ?>" target="_blank"> &raquo; <?PHP echo $MOD_BAKERY[$payment_method]['TXT_WEBSITE']; ?></a>.</td>
	</tr>
	<tr>
	  <td colspan="2"><table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr align="left" valign="top">
			<td width="50%" class="mod_bakery_pay_td_f"><b>1</b>.<br />
			  <?PHP echo $MOD_BAKERY[$payment_method]['TXT_PAY_ONLINE_2']; ?><br />
			  <br />
			  <b>2</b>.<br />
			  <?PHP echo $MOD_BAKERY[$payment_method]['TXT_SECURE']; ?></td>
			<td width="50%" class="mod_bakery_pay_td_f"><b>3</b>.<br />
			  <?PHP echo $MOD_BAKERY[$payment_method]['TXT_CONFIRMATION_NOTICE']; ?><br />
			  <br />
			  <b>4</b>.<br />
			  <?PHP echo $MOD_BAKERY[$payment_method]['TXT_SHIPMENT']; ?></td>
		  </tr>
		</table></td>
	</tr>
	<tr>
	  <td colspan="2" class="mod_bakery_pay_submit_f">
		<form action="<?php echo $payment_gateway_url ?>" method="post">
			<?php 
		  	foreach ($post_data as $name => $value) {
				echo "\n\t\t\t<input type='hidden' name='$name' value='$value' />";
			}
		  	?>		  
			<input type="submit" class="mod_bakery_bt_pay_<?php echo $payment_method ?>_f" value="<?php echo $MOD_BAKERY[$payment_method]['TXT_PAY']; ?>" />
		</form>
	  </td>
	</tr>
	<tr>
	  <td colspan="2"><hr class="mod_bakery_hr_f" /></td>
	</tr>
<?PHP
}
?>