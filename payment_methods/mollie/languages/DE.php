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


// PAYMENT METHOD MOLLIE
// *********************

// SETTINGS - USED BY BACKEND
$MOD_BAKERY[$payment_method]['TXT_PARTNER_ID'] = 'Mollie Partner ID';

// USED BY FILE bakery/payment_methods/mollie/processor.php
$MOD_BAKERY[$payment_method]['TXT_TITLE'] = 'Mollie (iDEAL)';
$MOD_BAKERY[$payment_method]['TXT_PAY_ONLINE_1'] = 'iDEAL Online-Zahlung &uuml;ber Ihr eBanking Konto auf vertraute, sichere und einfache Art und Weise.';
$MOD_BAKERY[$payment_method]['TXT_PAY_ONLINE_2'] = 'W&auml;hlen Sie Ihre Bank aus.';
$MOD_BAKERY[$payment_method]['TXT_SECURITY'] = 'Mehr Informationen zur Zahlungssicherheit finden Sie auf der';
$MOD_BAKERY[$payment_method]['TXT_SECURE'] = 'Sie werden direkt zur vertrauten und sicheren eBanking-Seite Ihrer Bank weitergeleitet, wo Sie die Zahlung genehmigen.';
$MOD_BAKERY[$payment_method]['TXT_CONFIRMATION_NOTICE'] = 'Nach Ihrer Transaktion erhalten Sie per E-Mail unsere Auftragsbest&auml;tigung und von Ihrer Bank eine Zahlungsbest&auml;tigung.';
$MOD_BAKERY[$payment_method]['TXT_WEBSITE'] = 'iDEAL Website';
$MOD_BAKERY[$payment_method]['TXT_SELECT_BANK'] = 'Bank ausw&auml;hlen';
$MOD_BAKERY[$payment_method]['TXT_PAY'] = 'Ich bezahle per iDEAL';
$MOD_BAKERY[$payment_method]['TXT_REDIRECT'] = 'Zur Zahlungsabwicklung werden Sie zu einem sicheren Server Ihrer Onlinebank weitergeleitet.';
$MOD_BAKERY[$payment_method]['TXT_REDIRECT_NOW'] = 'Jetzt zu iDEAL wechseln';
$MOD_BAKERY[$payment_method]['TXT_AGGREGATED_ITEMS'] = 'Summe inkl. Mwst &amp; Versand';
$MOD_BAKERY[$payment_method]['ERROR_CREATING_PM'] = 'FEHLERMELDUNG: Die Zahlung konnte nicht erstellt werden.';
$MOD_BAKERY[$payment_method]['ERROR_NO_BANK_SELECTED'] = 'FEHLERMELDUNG: Bitte w&auml;hlen Sie eine Bank aus.';

// USED BY FILE bakery/view_confirmation.php
$MOD_BAKERY[$payment_method]['TXT_SUCCESS'] = 'Besten Danke f&uuml;r Ihre online Zahlung. Ihre Transaktion wurde abgeschlossen.<br />Unsere Auftragsbest&auml;tigung wurde Ihnen per E-Mail zugesandt.';
$MOD_BAKERY[$payment_method]['TXT_SHIPMENT'] = 'Die gew&uuml;nschten Artikel senden wir Ihnen unverz&uuml;glich zu.';
$MOD_BAKERY[$payment_method]['ERROR'] = 'Es ist ein Problem aufgetreten. Ihre Transaktion konnte nicht abgeschlossen werden.<br />Bitte wenden Sie sich an den Shop-Betreiber.';
$MOD_BAKERY[$payment_method]['CANCELED'] = 'Sie haben Ihre iDEAL Zahlung abgebrochen.<br />M&ouml;chten Sie Ihren Einkauf trotzdem fortsetzen?';

// EMAIL CUSTOMER
$MOD_BAKERY[$payment_method]['EMAIL_SUBJECT_CUSTOMER'] = 'Bestätigung für Ihre [SHOP_NAME] Bestellung';
$MOD_BAKERY[$payment_method]['EMAIL_BODY_CUSTOMER'] = 'Guten Tag [CUSTOMER_NAME]

Herzlichen Dank für Ihren Einkauf bei [SHOP_NAME].
Sie haben die unten stehenden Artikel aus unserem Sortiment bestellt:
[ITEM_LIST]

Die gewünschten Artikel werden wir Ihnen unverzüglich an folgende Adresse senden:

[ADDRESS]


Wir danken für das uns entgegengebrachte Vertrauen.

Mit freundlichen Grüssen
[SHOP_NAME]


';

// EMAIL SHOP
$MOD_BAKERY[$payment_method]['EMAIL_SUBJECT_SHOP'] = 'Neue [SHOP_NAME] Bestellung';
$MOD_BAKERY[$payment_method]['EMAIL_BODY_SHOP'] = 'Hallo [SHOP_NAME] Admin

NEUE BESTELLUNG BEI [SHOP_NAME]:
	Bestellnummer: [ORDER_ID]
	Zahlungsart: Mollie (iDEAL)

Lieferadresse:
[ADDRESS]

Folgende Artikel wurden bestellt: 
[ITEM_LIST]


Mit freundlichen Grüssen
[SHOP_NAME]


';



// If iso-8859-1 is set as WB default charset convert some utf-8 strings to iso-8859-1
if (defined('DEFAULT_CHARSET') && DEFAULT_CHARSET == 'iso-8859-1') {
	$MOD_BAKERY[$payment_method]['EMAIL_SUBJECT_CUSTOMER'] = utf8_decode($MOD_BAKERY[$payment_method]['EMAIL_SUBJECT_CUSTOMER']);
	$MOD_BAKERY[$payment_method]['EMAIL_BODY_CUSTOMER'] = utf8_decode($MOD_BAKERY[$payment_method]['EMAIL_BODY_CUSTOMER']);
	$MOD_BAKERY[$payment_method]['EMAIL_SUBJECT_SHOP'] = utf8_decode($MOD_BAKERY[$payment_method]['EMAIL_SUBJECT_SHOP']);
	$MOD_BAKERY[$payment_method]['EMAIL_BODY_SHOP'] = utf8_decode($MOD_BAKERY[$payment_method]['EMAIL_BODY_SHOP']);
}

?>