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

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');


// Assign page filename for tracking with Google Analytics _trackPageview() function
global $ga_page;
$ga_page = '/view_pay.php';

// DIRECT CHECKOUT if required in general settings and if max 1 payment method
if ($skip_checkout) {
	$payment_method = $setting_payment_methods[0];
	if (is_file(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/processor.php')) {
		include(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/processor.php');
	}
} else {

	// VIEW PAYMENT METHODS
	// Show header
	echo "<h2 class='mod_bakery_h_f'><img src='".WB_URL."/modules/bakery/images/$step_img_dir/step_3.gif' />\n {$MOD_BAKERY['TXT_CHECKOUT']}</h2>";
	
	// Only show paragraph if we have >1 payment methods
	if ($num_payment_methods > 1) {
		echo "<p class='mod_bakery_pay_method_f'>{$MOD_BAKERY['TXT_PAY_METHOD']}:</p>";
	}

	echo "<table width='98%' border='0' cellspacing='0' cellpadding='0'>
	  <tr>
		<td colspan='2'><hr class='mod_bakery_hr_f' /></td>
	  </tr>";

	// Only show payment method/payment gateway if we have to
	if ($num_payment_methods > 0) {
		foreach ($setting_payment_methods as $payment_method) {
			if (is_file(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/processor.php')) {
				include(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/processor.php');
			}
		}
	} else {
		echo "<div class='mod_bakery_error_f' style='margin-top: 20px'><p>{$MOD_BAKERY['TXT_NO_PAYMENT_METHOD']}</p></div>";
	}
	echo "</table>";
}

// Code below is deprecated and stoped droplets working (only used for WB < 2.8.1)
if (version_compare(WB_VERSION, '2.8.1') < 0) {
	
	// AVOID OUTPUT FILTER
	// Obtain the settings of the output filter module
	if (file_exists(WB_PATH.'/modules/output_filter/filter-routines.php')) {
		include_once(WB_PATH.'/modules/output_filter/filter-routines.php');
		if (function_exists('getOutputFilterSettings')) {
			$filter_settings = getOutputFilterSettings();
		} else {
			$filter_settings = get_output_filter_settings();
		}
	} else {
		// No output filter used, define default settings
		$filter_settings['email_filter'] = 0;
	}
	
	/*
		NOTE:
		With ob_end_flush() the output filter will be disabled for Bakery checkout page
		If you are using e.g. ob_start in the index.php of your template it is possible that you will indicate problems
	*/
	if ($filter_settings['email_filter'] && !($filter_settings['at_replacement']=='@' && $filter_settings['dot_replacement']=='.')) { 
		ob_end_flush();
	}
}
?>