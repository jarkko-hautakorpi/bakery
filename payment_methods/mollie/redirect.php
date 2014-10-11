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


// Include WB config.php file, WB admin class and ideal class
require('../../../../config.php');
require_once(WB_PATH.'/framework/class.admin.php');
require_once('ideal.class.php');



// CREATE PAYMENT AND REDIRECT TO BANK
// ***********************************

// Create ideal payment using ideal class
if (isset($_POST['bank_id']) AND !empty($_POST['bank_id']) AND isset($_POST['payment_method']) AND $_POST['payment_method'] == 'mollie') {

	$payment_method = $_POST['payment_method'];
	$partner_id = $_SESSION['bakery'][$payment_method]['partner_id'];
	$bank_id = $_POST['bank_id'];
	$amount = $_SESSION['bakery'][$payment_method]['amount'];
	$description = $_SESSION['bakery'][$payment_method]['description'];
	$return_url = $_SESSION['bakery'][$payment_method]['return_url'];
	$report_url = $_SESSION['bakery'][$payment_method]['report_url'];
	$order_id = $_SESSION['bakery']['order_id'];

	// Process payment
	$iDEAL = new iDEAL_Payment ($partner_id);
	if ($iDEAL->createPayment($bank_id, $amount, $description, $return_url, $report_url)) {
	
		// Update transaction_id in customer table 
		$transaction_id = $iDEAL->getTransactionId();
		$database->query("UPDATE ".TABLE_PREFIX."mod_bakery_customer SET transaction_id = '$transaction_id' WHERE order_id = '$order_id'");
		// Send customer to the bank payment page
		header('location: ' . $iDEAL->getBankURL());
	} else {
		header('location: '.$_POST['setting_continue_url'].'?pay_error=1');
	}
} else {
	header('location: '.$_POST['setting_continue_url'].'?pay_error=2');
}