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

// Direct checkout
if ($skip_checkout) {
	$payment_status = "success";
	include(WB_PATH.'/modules/bakery/view_confirmation.php');
	return;
}

// Include info file
include(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/info.php');

// Look for payment method language file
if (LANGUAGE_LOADED) {
    include(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/languages/EN.php');
    if (file_exists(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/languages/'.LANGUAGE.'.php')) {
        include(WB_PATH.'/modules/bakery/payment_methods/'.$payment_method.'/languages/'.LANGUAGE.'.php');
    }
}
?>


<tr>
  <td colspan="2"><h3 class="mod_bakery_pay_h_f"><?PHP echo $MOD_BAKERY[$payment_method]['TXT_ADVANCE_PAYMENT']; ?></h3></td>
</tr>
<tr>
  <td colspan="2"><table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr align="left" valign="top">
        <td width="33.3%" class="mod_bakery_pay_td_f"><b>1</b>.<br />
          <?PHP echo $MOD_BAKERY[$payment_method]['TXT_SUCCESS']; ?></td>
        <td width="33.3%" class="mod_bakery_pay_td_f"><b>2</b>.<br />
          <?PHP echo $MOD_BAKERY[$payment_method]['TXT_ACCOUNT']; ?></td>
        <td width="33.4%" class="mod_bakery_pay_td_f"><b>3</b>.<br />
          <?PHP echo $MOD_BAKERY[$payment_method]['TXT_SHIPMENT']; ?></td>
      </tr>
    </table></td>
</tr>
<tr>
  <td colspan="2" class="mod_bakery_pay_submit_f">
  	<form action="<?php echo $setting_continue_url ?>" method="post">
  	  <input type="hidden" name="payment_method" value="<?php echo $payment_method ?>">
  	  <input type="submit" class="mod_bakery_bt_pay_<?php echo $payment_method ?>_f" value="<?php echo $MOD_BAKERY[$payment_method]['TXT_PAY']; ?>" />
    </form>
  </td>
</tr>
<tr>
  <td colspan="2"><hr class="mod_bakery_hr_f" /></td>
</tr>