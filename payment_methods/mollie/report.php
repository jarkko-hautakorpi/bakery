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

// Get the payment method settings from db
$query_payment_methods = $database->query("SELECT value_1 FROM ".TABLE_PREFIX."mod_bakery_payment_methods WHERE directory = 'mollie'");
if ($query_payment_methods->numRows() > 0) {
	$payment_methods = $query_payment_methods->fetchRow();
	$partner_id = stripslashes($payment_methods['value_1']);  // Mollie partner id
}

// Check if payment is completed
if (isset($_GET['transaction_id'])) {
	$transaction_id = $_GET['transaction_id'];
	$iDEAL = new iDEAL_Payment ($partner_id);
	$iDEAL->checkPayment($transaction_id);

	// If payment status is payed write it into db
	if ($iDEAL->getPaidStatus() == true) {
		$database->query("UPDATE ".TABLE_PREFIX."mod_bakery_customer SET transaction_status = 'paid' WHERE transaction_id = '$transaction_id'");
	}
}
?>