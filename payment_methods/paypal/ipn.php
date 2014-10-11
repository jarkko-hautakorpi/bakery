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


// PayPal IPN (Instant Payment Notification)
// *****************************************

// Sample code provided by PayPal as a starting point
// https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_admin_IPNImplementation



// Testing
$active     = true;   // IPN on = true, IPN off = false
$testing    = false;  // Use testing mode for detailed success / error messages
$sandbox    = false;  // Use paypal sandbox
$delay      = false;  // Delay IPN respond to push up PDT
$test_email = '';     // Set a test email address to get success / error messages



// Deactivate IPN
if (!$active) {
	exit();
}
// Delay IPN
if ($delay) {
	sleep(15);
}

// IPN URL
$ipn_url = $sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';

// Include WB config.php file and WB admin class
require('../../../../config.php');
require_once(WB_PATH.'/framework/class.admin.php');

// Initialize or set vars
$header          = '';
$errors          = array();
$magic_quotes_on = false;

// Get setting of magic quotes
if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() == 1) {
	$magic_quotes_on = true;
}

// Check POST vars
// First handle escape characters, which depends on setting of magic quotes
if (isset($_POST)) {
	$_POST          = $magic_quotes_on                ? array_map('stripslashes', $_POST)    : $_POST;
	$payment_status = isset($_POST['payment_status']) ? strip_tags($_POST['payment_status']) : '';
	$txn_id         = isset($_POST['txn_id'])         ? strip_tags($_POST['txn_id'])         : '';
	$business       = isset($_POST['business'])       ? strip_tags($_POST['business'])       : '';
	$invoice        = isset($_POST['invoice'])        ? strip_tags($_POST['invoice'])        : '';
	$order_id       = is_numeric($invoice)            ? $invoice                             : 0;
	$mc_gross       = isset($_POST['mc_gross'])       ? strip_tags($_POST['mc_gross'])       : '';
}
else {
	exit();
}

// Get PayPal email (business var) from db
$query_payment_methods = $database->query("SELECT value_1, value_3 FROM ".TABLE_PREFIX."mod_bakery_payment_methods WHERE directory = 'paypal'");
if ($query_payment_methods->numRows() > 0) {
	$payment_method = $query_payment_methods->fetchRow();
	$paypal_email   = stripslashes($payment_method['value_1']);
}

// Get payment type (submitted as), transaction id, transaction status and payment status from db
$query_customers = $database->query("SELECT submitted, transaction_id, transaction_status, status FROM ".TABLE_PREFIX."mod_bakery_customer WHERE order_id = '$order_id'");
if ($query_customers->numRows() > 0) {
	$customer = $query_customers->fetchRow();
	$submitted          = stripslashes($customer['submitted']);
	$transaction_id     = stripslashes($customer['transaction_id']);
	$transaction_status = stripslashes($customer['transaction_status']);
	$status             = stripslashes($customer['status']);
}

// Prepare test email subject and start body
$email_subject = 'PayPal Instant Payment Notification (IPN)';
$email_body    = "\n" . 'PAYPAL INSTANT PAYMENT NOTIFICATION (IPN)' . "\n\n";
$email_body   .= 'The PayPal payment with order id ' . $order_id . ' and transaction id ' . $txn_id;


// Only make checkings if not verified yet 
if ($submitted == 'no' || $transaction_status != 'paid') {

	// Read the post from PayPal and add 'cmd' var
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value) {
		$value  = urlencode($value);
		$req   .= "&$key=$value";
	}
	
	// Post back to PayPal to validate 
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	
	$fp = fsockopen($ipn_url, 80, $errno, $errstr, 30);
	
	// Process validation from PayPal
	if (!$fp) {
		// HTTP error
		if ($testing) {
			$email_body .= ' could not be verified by Bakery.' . "\n";
			$email_body .= 'ERROR: Unable to connect to PayPal IPN server ('.$errno.': '.$errstr.').' . "\n\n";
		}
	}
	else {
		// Connected to PayPal IPN server
		fputs($fp, $header . $req);
		while (!feof($fp)) {
			$res = fgets($fp, 1024);
		}
		fclose($fp);

		// VERIFIED
		if (strcmp($res, "VERIFIED") == 0) {
	
			// Confirm that the payment status is Completed
			if ($payment_status != 'Completed') {
				$errors[] = 'The payment status returned by PayPal is "' . $payment_status . '".';
				$errors[] = 'The payment status should be "Completed".';
			}
			// Check if the transaction id is correct
			if ($transaction_id != $txn_id && $transaction_id != 'none') {
				$errors[] = 'The transaction id did not match.';
			}
			// Validate if the receiver’s email address is registered to Bakery
			if ($business != $paypal_email) {
				$errors[] = 'The receiver\'s PayPal email address (business var) is not registered to Bakery.';
			}
	
			// If no errors occured set payment status to successfull
			if (count($errors) == 0) {
				$email_body    .= ' has been completed successfull.' . "\n";
				
				// Set payment status success and update db
				$database->query("UPDATE ".TABLE_PREFIX."mod_bakery_customer SET transaction_id = '$txn_id', transaction_status = 'paid' WHERE order_id = '$order_id'");
				$payment_method  = 'paypal';
				$payment_status  = 'success';
				$no_confirmation = true;
				include '../../view_confirmation.php';
			}
	
			// ERROR
			else {					
				$email_body     .= ' has not been completed yet.' . "\n";
				$email_body     .= 'Please see the list below for transaction-specific details:' . "\n\n";
				foreach ($errors as $value) {
					$email_body .= ' - ' . $value . "\n";
				}
				$email_body     .= "\n";
	
				// Set payment status pending and update db
				$database->query("UPDATE ".TABLE_PREFIX."mod_bakery_customer SET transaction_id = '$txn_id', transaction_status = 'pending' WHERE order_id = '$order_id'");
				$payment_method  = 'paypal';
				$payment_status  = 'pending';
				$no_confirmation = true;
				include '../../view_confirmation.php';
			}
		}

		// INVALID
		elseif (strcmp($res, "INVALID") == 0) {
			$email_body .= ' is invalid and has not been completed.' . "\n";
		}
	}
}

// Payment already completed successfully
else {
	$email_body .= ' has already been completed successfully.' . "\n";
	$email_body .= 'Transaction has already been saved in data base.' . "\n\n";
}



// Send email for testing
if ($testing) {
	// Select email address
	$email_to = $test_email == '' ? $business : $test_email;

	// Transaction details
	$email_body .= 'To see all the transaction details, please log in to your PayPal account.' . "\n\n";
	$email_body .= "Find further information on this transaction below:";
	
	$email_body .= "\n\n" . $res . "\n\n";
	foreach ($_POST as $key => $value) {
		$email_body .= $key . " = " .$value ."\n\n";
	}
	mail($email_to, $email_subject, $email_body);
}

?>