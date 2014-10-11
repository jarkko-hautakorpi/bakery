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


/*
  Define the payment method settings that the shop admin will have to set in the Bakery backend.
  Make sure that every var set below has its counterpart in the payment method language files:
  eg. $field_1 = 'email';
      $MOD_BAKERY[$payment_method]['TXT_EMAIL'] = 'E-Mail';
  =>  'email' will be converted to uppercase 'TXT_EMAIL'
*/  
	$field_1 = 'email';
	$field_2 = 'page';
	$field_3 = 'auth_token';
	$field_4 = '';
	$field_5 = '';
	$field_6 = '';



// Payment method info
$payment_method_name    = 'PayPal';
$payment_method_version = '0.4';
$payment_method_author  = 'Christoph Marti';
$requires_bakery_module = '1.1';


?>