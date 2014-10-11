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


// Include info file and ideal class
include(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/info.php');
require_once('ideal.class.php');



// CUSTOMER INFORMATION AND SELECT BANK
// ************************************

// Prevent this file from being accessed directly
if (defined('WB_PATH') == false) {
	exit("Cannot access this file directly"); 
}

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
	$value_1 = stripslashes($payment_methods['value_1']);  // Mollie partner id
}


// ********************************************************************************* //
//
//  MODIFY THE LINES BELOW TO FIT THE REQUIREMENTS OF THE PAYMENT GATEWAY PROVIDER
//
// ********************************************************************************* //

// Use payment gateway testmode
// => Set the testmode on = true / off = false in the ideal.class.php file

// URL of the payment gateway provider
$security_info_url = "http://www.ideal.nl/consument/?s=extra&a=veiliga";

$partner_id = $value_1;  // Mollie partner id
$amount = 100 * $_SESSION['bakery']['order_total'];  // Amount in cents
$description = $MOD_BAKERY[$payment_method]['TXT_AGGREGATED_ITEMS'];
// Background HTTP request to confirm customer payment
$report_url = WB_URL.'/modules/bakery/payment_methods/'.$payment_method.'/report.php';
// Direct customer back to shop upon payment completion
$return_url = $setting_continue_url.'?pm='.$payment_method;

// ********************************************************************************* //
//
//    DO NOT CHANGE ANYTHING BELOW THIS LINE UNLESS YOU KNOW WHAT YOU ARE DOING
//
// ********************************************************************************* //



// DIRECT CHECKOUT
// ***************

// Not possible since the user has to select a bank



// SHOW CHECKOUT
// *************

// Put the payment data into the session var for later use with redirect.php
$_SESSION['bakery'][$payment_method]['partner_id'] = $partner_id;
$_SESSION['bakery'][$payment_method]['amount'] = $amount;
$_SESSION['bakery'][$payment_method]['description'] = $description;
$_SESSION['bakery'][$payment_method]['return_url'] = $return_url;
$_SESSION['bakery'][$payment_method]['report_url'] = $report_url;

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
}


?>
<tr>
  <td colspan="2"><h3 class="mod_bakery_pay_h_f"><?php echo $MOD_BAKERY[$payment_method]['TXT_TITLE']; ?></h3></td>
</tr>
<?php echo $pm_error_msg; ?>
<tr>
  <td colspan="2" class="mod_bakery_pay_td_f"><?php echo $MOD_BAKERY[$payment_method]['TXT_PAY_ONLINE_1']; ?><br />
	<?php echo $MOD_BAKERY[$payment_method]['TXT_SECURITY']; ?><a href="<?php echo $security_info_url ?>" target="_blank"> &raquo; <?php echo $MOD_BAKERY[$payment_method]['TXT_WEBSITE']; ?></a>.</td>
</tr>
<tr>
  <td colspan="2"><table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr align="left" valign="top">
		<td width="50%" class="mod_bakery_pay_td_f"><b>1</b>.<br />
		  <?php echo $MOD_BAKERY[$payment_method]['TXT_PAY_ONLINE_2']; ?><br />
		  <br />
		  <b>2</b>.<br />
		  <?php echo $MOD_BAKERY[$payment_method]['TXT_SECURE']; ?></td>
		<td width="50%" class="mod_bakery_pay_td_f"><b>3</b>.<br />
		  <?php echo $MOD_BAKERY[$payment_method]['TXT_CONFIRMATION_NOTICE']; ?><br />
		  <br />
		  <b>4</b>.<br />
		  <?php echo $MOD_BAKERY[$payment_method]['TXT_SHIPMENT']; ?></td>
	  </tr>
	</table></td>
</tr>
<tr>
  <td colspan="2" class="mod_bakery_pay_submit_f">
	<?php
	// Process payment
	$iDEAL = new iDEAL_Payment ($partner_id);
	// Get available banks
	$bank_array = $iDEAL->getBanks();
	
	// Make dropdown menu ?>
	<form method="post" action="<?php echo WB_URL ?>/modules/bakery/payment_methods/<?php echo $payment_method ?>/redirect.php">
		<select name="bank_id">
			<option value=''><?php echo $MOD_BAKERY[$payment_method]['TXT_SELECT_BANK']; ?></option>
			<?php foreach ($bank_array as $bank_id => $bank_name) {
			echo "<option value='$bank_id'>$bank_name</option>\n";
			} ?>
		</select>
		<input type="hidden" name="payment_method" value="<?php echo $payment_method ?>" />
		<input type="hidden" name="setting_continue_url" value="<?php echo $setting_continue_url ?>" />
		<input type="submit" name="submit" class="mod_bakery_bt_pay_<?php echo $payment_method ?>_f" value="<?php echo $MOD_BAKERY[$payment_method]['TXT_PAY']; ?>" />
	</form>
  </td>
</tr>
<tr>
  <td colspan="2"><hr class="mod_bakery_hr_f" /></td>
</tr>