<?php
/**
 * Nab Velocity
 *
 * @package languageDefines
 * @copyright Copyright velocity.
 * @copyright Portions Copyright 2003 osCommerce
 * @license 
 * @version GIT: $Id: Author: Ashish  Tue Aug 1 2015
 */

define('MODULE_PAYMENT_VELOCITY_TEXT_ADMIN_TITLE', 'velocity');
define('MODULE_PAYMENT_VELOCITY_TEXT_CATALOG_TITLE', 'Credit Card - velocity');  // Payment option title as displayed to the customer

if (MODULE_PAYMENT_VELOCITY_STATUS == 'True') {
    define('MODULE_PAYMENT_VELOCITY_TEXT_DESCRIPTION', '' . (MODULE_PAYMENT_VELOCITY_TESTMODE != 'Production' ? '<br />Testing Info:<br /><b>Automatic Approval Credit Card Numbers:</b><br />Visa#: 4007000000027<br />MC#: 5424000000000015<br />Discover#: 6011000000000012<br />AMEX#: 370000000000002<br /><br /><b>Note:</b> These credit card numbers will return a decline in live mode, and an approval in test mode.  Any future date can be used for the expiration date and any 3 or 4 (AMEX) digit number can be used for the CVV Code.<br /><br />' : '') . '<br /><br /><strong>SETTINGS</strong><br />Your velocity credential settings workflowid/serviceid and merchantprofileid ');
} else {
    define('MODULE_PAYMENT_VELOCITY_TEXT_DESCRIPTION', '<strong>Requirements:</strong><br /><hr />*<strong>Velocity Credential</strong><br />ServiceId/workflowid<br>merchantprofileid');
}

define('MODULE_PAYMENT_VELOCITY_TEXT_TYPE', 'Type:');
define('MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_OWNER', 'Credit Card Owner:');
define('MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
define('MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
define('MODULE_PAYMENT_VELOCITY_TEXT_CVV', 'CVV Number:');
define('MODULE_PAYMENT_VELOCITY_TEXT_POPUP_CVV_LINK', 'What\'s this?');
define('MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_OWNER', '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
define('MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
define('MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_CVV', '* The 3 or 4 digit CVV number must be entered from the back of the credit card.\n');
define('MODULE_PAYMENT_VELOCITY_TEXT_ERROR_MESSAGE', 'There has been an error processing your credit card. Please try again.');
define('MODULE_PAYMENT_VELOCITY_TEXT_DECLINED_MESSAGE', 'Your credit card was declined. Please try another card or contact your bank for more info.');
define('MODULE_PAYMENT_VELOCITY_TEXT_ERROR', 'Credit Card Error!');
define('TABLE_PAYMENT_VELOCITY_TRANSACTIONS', DB_PREFIX.'velocity_transactions');
define('FILENAME_VELOCITY_REFUND', 'velocityRefund');
define('FILENAME_CHECKOUT_FAILURE', 'checkout_failure');
