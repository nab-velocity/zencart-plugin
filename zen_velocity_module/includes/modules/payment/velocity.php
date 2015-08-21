<?php

/**
 * Velcity payment method class
 *
 * @package paymentMethod
 * @copyright Copyright velocity.
 * @copyright Portions Copyright 2003 osCommerce
 * @license 
 * @version GIT: $Id: Author: Ashish  Tue Aug 1 2015
 */
 
/**
 * Velocity payment method class
 *
 */
class velocity extends base {
    
    /**
     * $code determines the internal 'code' name used to designate "this" payment module
     *
     * @var string
     */
    var $code;
    /**
     * $title is the displayed name for this payment method
     *
     * @var string
     */
    var $title;
    /**
     * $description is a soft name for this payment method
     *
     * @var string
     */
    var $description;
    /**
     * $enabled determines whether this module shows or not... in catalog.
     *
     * @var boolean
     */
    var $enabled;
    /**
     * log file folder
     *
     * @var string
     */
    var $_logDir = '';
    /**
     * vars
     */
    var $reportable_submit_data;
    var $velocity;
    var $auth_code;
    var $transaction_id;
    var $order_status;


    /**
     * @return velocity
     */
    function velocity() {        
        global $order;

        $this->code = 'velocity';
        if (IS_ADMIN_FLAG === true) {
                $this->title = MODULE_PAYMENT_VELOCITY_TEXT_ADMIN_TITLE; // Payment module title in Admin
                if (MODULE_PAYMENT_VELOCITY_STATUS == 'True' && (MODULE_PAYMENT_VELOCITY_WORKFLOWID != '2317000001' || MODULE_PAYMENT_VELOCITY_MERCHANTPROFILEID != 'PrestaShop Global HC')) {
                        $this->title .=  '<span class="alert"> (Not Configured)</span>';
                } elseif (MODULE_PAYMENT_VELOCITY_TESTMODE == 'Test') {
                        $this->title .= '<span class="alert"> (in Testing mode)</span>';
                }
        } else {
                $this->title = MODULE_PAYMENT_VELOCITY_TEXT_CATALOG_TITLE; // Payment module title in Catalog
        }
        $this->description = MODULE_PAYMENT_VELOCITY_TEXT_DESCRIPTION;
        $this->enabled = ((MODULE_PAYMENT_VELOCITY_STATUS == 'True') ? true : false);
        $this->sort_order = MODULE_PAYMENT_VELOCITY_SORT_ORDER;

        if ((int)MODULE_PAYMENT_VELOCITY_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_VELOCITY_ORDER_STATUS_ID;
        }
  
        $this->notify('NOTIFY_ORDER_INSTANTIATE', array(), $order_id);
        //if (is_object($order)) $this->update_status();


        // Determine default/supported currencies
        if (in_array(DEFAULT_CURRENCY, array('USD', 'CAD', 'GBP', 'EUR', 'AUD', 'NZD'))) {
                $this->gateway_currency = DEFAULT_CURRENCY;
        } else {
                $this->gateway_currency = 'USD';
        }

        if (!is_array($_POST) || empty($_POST) && !isset($_GET['main_page'])) {
        echo '<script type="text/javascript"> (function(funcName, baseObj) {
            // The public function name defaults to window.docReady
            // but you can pass in your own object and own function name and those will be used
            // if you want to put them in a different namespace
            funcName = funcName || "docReady";
            baseObj = baseObj || window;
            var readyList = [];
            var readyFired = false;
            var readyEventHandlersInstalled = false;

            // call this when the document is ready
            // this function protects itself against being called more than once
            function ready() {
                if (!readyFired) {
                    // this must be set to true before we start calling callbacks
                    readyFired = true;
                    for (var i = 0; i < readyList.length; i++) {
                        // if a callback here happens to add new ready handlers,
                        // the docReady() function will see that it already fired
                        // and will schedule the callback to run right after
                        // this event loop finishes so all handlers will still execute
                        // in order and no new ones will be added to the readyList
                        // while we are processing the list
                        readyList[i].fn.call(window, readyList[i].ctx);
                    }
                    // allow any closures held by these functions to free
                    readyList = [];
                }
            }

            function readyStateChange() {
                if ( document.readyState === "complete" ) {
                    ready();
                }
            }

                // This is the one public interface
                // docReady(fn, context);
                // the context argument is optional - if present, it will be passed
                // as an argument to the callback
                baseObj[funcName] = function(callback, context) {
                    // if ready has already fired, then just schedule the callback
                    // to fire asynchronously, but right away
                    if (readyFired) {
                        setTimeout(function() {callback(context);}, 1);
                        return;
                    } else {
                        // add the function and context to the list
                        readyList.push({fn: callback, ctx: context});
                    }
                    // if document already ready to go, schedule the ready function to run
                    if (document.readyState === "complete") {
                        setTimeout(ready, 1);
                    } else if (!readyEventHandlersInstalled) {
                        // otherwise if we don"t have event handlers installed, install them
                        if (document.addEventListener) {
                            // first choice is DOMContentLoaded event
                            document.addEventListener("DOMContentLoaded", ready, false);
                            // backup is window load event
                            window.addEventListener("load", ready, false);
                        } else {
                            // must be IE
                            document.attachEvent("onreadystatechange", readyStateChange);
                            window.attachEvent("onload", ready);
                        }
                        readyEventHandlersInstalled = true;
                    }
                }
            })("docReady", window);
            docReady(function() {
                    var e = document.getElementsByTagName("select")[0];

                    if (typeof(e) == "object") {
                        try {
                            e.addEventListener("change", Obj.updatestatus , false);
                        } catch (es) {
                            alert(es);
                        }
                    }
            });

            var Obj = {
                updatestatus:function(e) {
                    var e = document.getElementsByTagName("select")[0].value;
                    var form_event = document.getElementsByTagName("form")[0];
                    var form_action = form_event.action;

                    if(e == 5) {    
                        form_event.setAttribute("id", form_action);
                        form_event.setAttribute("action", "'.zen_href_link(FILENAME_VELOCITY_REFUND).'");
                        hiddenInput = document.createElement("input");
                        hiddenInput.type = "hidden";
                        hiddenInput.name = "form_url";
                        hiddenInput.value = form_action;
                        form_event.appendChild(hiddenInput);
                        hiddenInput1 = document.createElement("input");
                        hiddenInput1.type = "hidden";
                        hiddenInput1.name = "oID";
                        hiddenInput1.value = "'.$_REQUEST["oID"].'";
                        form_event.appendChild(hiddenInput1);
                        hiddenInput2 = document.createElement("input");
                        hiddenInput2.type = "hidden";
                        hiddenInput2.name = "velocity_table";
                        hiddenInput2.value = "'.TABLE_PAYMENT_VELOCITY_TRANSACTIONS.'";
                        form_event.appendChild(hiddenInput2);
                    } else {
                        form_action = form_event.id;
                        if(form_action != "") {
                            form_event.setAttribute("action", form_action);
                            form_event.removeChild(document.getElementsByName("form_url")[0]);
                            form_event.removeChild(document.getElementsByName("oID")[0]);
                            form_event.removeChild(document.getElementsByName("velocity_table")[0]);
                        }
                    }
                }
            }</script>';
        }
    }

    /**
     * JS validation which does error-checking of data-entry if this module is selected for use
     * (Number, Owner Lengths)
     *
     * @return string
     */
    function javascript_validation() {

        $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
                  '    var cc_owner = document.checkout_payment.velocity_cc_owner.value;' . "\n" .
                  '    var cc_number = document.checkout_payment.velocity_cc_number.value;' . "\n";
        if ('True') {
                $js .= '    var cc_cvv = document.checkout_payment.velocity_cc_cvv.value;' . "\n";
        }
        $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
                  '      error_message = error_message + "' . MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_OWNER . '";' . "\n" .
                  '      error = 1;' . "\n" .
                  '    }' . "\n" .
                  '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
                  '      error_message = error_message + "' . MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_NUMBER . '";' . "\n" .
                  '      error = 1;' . "\n" .
                  '    }' . "\n";
        if ('True') {
                $js .= '    if (cc_cvv == "" || !(/^\+?(0|[1-9]\d*)$/.test(cc_cvv)) || cc_cvv.length < "3" || cc_cvv.length > "4") {' . "\n".
                '      error_message = error_message + "' . MODULE_PAYMENT_VELOCITY_TEXT_JS_CC_CVV . '";' . "\n" .
                '      error = 1;' . "\n" .
                '    }' . "\n" ;
        }
        $js .= '  }' . "\n";

        return $js;
    }

    /**
     * Display Velocity Credit Card Information Submission Fields on the Checkout Payment Page
     *
     * @return array
     */
    function selection() {
        global $order;

        for ($i=1; $i<13; $i++) {
          $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B - (%m)',mktime(0,0,0,$i,1,2000)));
        }

        $today = getdate();
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
          $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
        }

        $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

        $selection = array( 'id'     => $this->code,
                                                'module' => $this->title,
                                                'fields' => array(
                                                                        array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_OWNER,
                                                                                         'field' => zen_draw_input_field('velocity_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'id="'.$this->code.'-cc-owner"' . $onFocus . ' autocomplete="off"'),
                                                                                         'tag' => $this->code.'-cc-owner'),
                                                                        array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_NUMBER,
                                                                                   'field' => zen_draw_input_field('velocity_cc_number', '', 'id="'.$this->code.'-cc-number"' . $onFocus . ' autocomplete="off"'),
                                                                                   'tag' => $this->code.'-cc-number'),
                                                                        array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_EXPIRES,
                                                                                   'field' => zen_draw_pull_down_menu('velocity_cc_expires_month', $expires_month, strftime('%m'), 'id="'.$this->code.'-cc-expires-month"' . $onFocus) . '&nbsp;' . zen_draw_pull_down_menu('velocity_cc_expires_year', $expires_year, '', 'id="'.$this->code.'-cc-expires-year"' . $onFocus),
                                                                                   'tag' => $this->code.'-cc-expires-month'),
                                                                        array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CVV,
                                                                                   'field' => zen_draw_input_field('velocity_cc_cvv', '', 'size="4" maxlength="4"' . ' id="'.$this->code.'-cc-cvv"' . $onFocus . ' autocomplete="off"') . ' ' . '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . MODULE_PAYMENT_VELOCITY_TEXT_POPUP_CVV_LINK . '</a>',
                                                                                   'tag' => $this->code.'-cc-cvv')	   
                                                )
        );

        return $selection;
    }

    /**
     * Evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
     *
     */
    function pre_confirmation_check() {
        global $messageStack;
        if (isset($_POST['velocity_cc_number'])) {
                include(DIR_WS_CLASSES . 'cc_validation.php');

                $cc_validation = new cc_validation();
                $result = $cc_validation->validate($_POST['velocity_cc_number'], $_POST['velocity_cc_expires_month'], $_POST['velocity_cc_expires_year']);
                $error = '';
                switch ($result) {
                        case -1:
                        $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
                        break;
                        case -2:
                        case -3:
                        case -4:
                        $error = TEXT_CCVAL_ERROR_INVALID_DATE;
                        break;
                        case false:
                        $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
                        break;
                }

                if ( ($result == false) || ($result < 1) ) {
                        $messageStack->add_session('checkout_payment', $error . '<!-- ['.$this->code.'] -->', 'error');
                        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
                }

                $this->cc_card_type    = $cc_validation->cc_type;
                $this->cc_card_number  = $cc_validation->cc_number;
                $this->cc_expiry_month = $cc_validation->cc_expiry_month;
                $this->cc_expiry_year  = $cc_validation->cc_expiry_year;
        }
    }

    /**
     * Display Credit Card Information on the Checkout Confirmation Page
     *
     * @return array
     */
    function confirmation() {
        if (isset($_POST['velocity_cc_number'])) {
                $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                                                        'fields' => array(array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_OWNER,
                                                                                                        'field' => $_POST['velocity_cc_owner']),
                                                                                          array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_NUMBER,
                                                                                                        'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                                                                          array('title' => MODULE_PAYMENT_VELOCITY_TEXT_CREDIT_CARD_EXPIRES,
                                                                                                        'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['velocity_cc_expires_month'], 1, '20' . $_POST['velocity_cc_expires_year'])))));
        } else {
                $confirmation = array();
        }
        return $confirmation;
    }

    /**
     * Build the data and actions to process when the "Submit" button is pressed on the order-confirmation screen.
     * This prepare the card detail and address detail for the verify transaction.
     *
     * @return string
     */
    function process_button() {

        global $order;

        $avsData = array (
                'Street'        => $order->billing['street_address'],
                'City'          => $order->billing['city'],
                'StateProvince' => '',
                'PostalCode'    => $order->billing['postcode'],
                'Country'       => 'USA'
        );

        $cardData = array (
                'cardtype'    => str_replace(' ', '', $this->cc_card_type), 
                'pan'         => $this->cc_card_number, 
                'expire'      => $this->cc_expiry_month.substr($this->cc_expiry_year, -2), 
                'cvv'         => $_POST['velocity_cc_cvv'],
                'track1data'  => '', 
                'track2data'  => ''
        );

        $_SESSION['avsdata'] = base64_encode(serialize($avsData));
        $_SESSION['carddata'] = base64_encode(serialize($cardData));

        return false;
    }

    /**
     * @return booolen
     *
     */
    function before_process() {
        return false;
    }

    /**
     * Post-processing activities for send detail to velocity gateway for the verify the detail and process the payment 
     * Trought velocity gateway and return response.
     * 
     * @return boolean
     */
    function after_process() {

        include_once('includes/sdk/Velocity.php');
        global $order, $insert_id, $db, $messageStack;
        
        if (MODULE_PAYMENT_VELOCITY_TESTMODE == 'Test') {
            $identitytoken        = "PHNhbWw6QXNzZXJ0aW9uIE1ham9yVmVyc2lvbj0iMSIgTWlub3JWZXJzaW9uPSIxIiBBc3NlcnRpb25JRD0iXzdlMDhiNzdjLTUzZWEtNDEwZC1hNmJiLTAyYjJmMTAzMzEwYyIgSXNzdWVyPSJJcGNBdXRoZW50aWNhdGlvbiIgSXNzdWVJbnN0YW50PSIyMDE0LTEwLTEwVDIwOjM2OjE4LjM3OVoiIHhtbG5zOnNhbWw9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjEuMDphc3NlcnRpb24iPjxzYW1sOkNvbmRpdGlvbnMgTm90QmVmb3JlPSIyMDE0LTEwLTEwVDIwOjM2OjE4LjM3OVoiIE5vdE9uT3JBZnRlcj0iMjA0NC0xMC0xMFQyMDozNjoxOC4zNzlaIj48L3NhbWw6Q29uZGl0aW9ucz48c2FtbDpBZHZpY2U+PC9zYW1sOkFkdmljZT48c2FtbDpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PHNhbWw6U3ViamVjdD48c2FtbDpOYW1lSWRlbnRpZmllcj5GRjNCQjZEQzU4MzAwMDAxPC9zYW1sOk5hbWVJZGVudGlmaWVyPjwvc2FtbDpTdWJqZWN0PjxzYW1sOkF0dHJpYnV0ZSBBdHRyaWJ1dGVOYW1lPSJTQUsiIEF0dHJpYnV0ZU5hbWVzcGFjZT0iaHR0cDovL3NjaGVtYXMuaXBjb21tZXJjZS5jb20vSWRlbnRpdHkiPjxzYW1sOkF0dHJpYnV0ZVZhbHVlPkZGM0JCNkRDNTgzMDAwMDE8L3NhbWw6QXR0cmlidXRlVmFsdWU+PC9zYW1sOkF0dHJpYnV0ZT48c2FtbDpBdHRyaWJ1dGUgQXR0cmlidXRlTmFtZT0iU2VyaWFsIiBBdHRyaWJ1dGVOYW1lc3BhY2U9Imh0dHA6Ly9zY2hlbWFzLmlwY29tbWVyY2UuY29tL0lkZW50aXR5Ij48c2FtbDpBdHRyaWJ1dGVWYWx1ZT5iMTVlMTA4MS00ZGY2LTQwMTYtODM3Mi02NzhkYzdmZDQzNTc8L3NhbWw6QXR0cmlidXRlVmFsdWU+PC9zYW1sOkF0dHJpYnV0ZT48c2FtbDpBdHRyaWJ1dGUgQXR0cmlidXRlTmFtZT0ibmFtZSIgQXR0cmlidXRlTmFtZXNwYWNlPSJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcyI+PHNhbWw6QXR0cmlidXRlVmFsdWU+RkYzQkI2REM1ODMwMDAwMTwvc2FtbDpBdHRyaWJ1dGVWYWx1ZT48L3NhbWw6QXR0cmlidXRlPjwvc2FtbDpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PFNpZ25hdHVyZSB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnIyI+PFNpZ25lZEluZm8+PENhbm9uaWNhbGl6YXRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiPjwvQ2Fub25pY2FsaXphdGlvbk1ldGhvZD48U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3JzYS1zaGExIj48L1NpZ25hdHVyZU1ldGhvZD48UmVmZXJlbmNlIFVSST0iI183ZTA4Yjc3Yy01M2VhLTQxMGQtYTZiYi0wMmIyZjEwMzMxMGMiPjxUcmFuc2Zvcm1zPjxUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjZW52ZWxvcGVkLXNpZ25hdHVyZSI+PC9UcmFuc2Zvcm0+PFRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMTAveG1sLWV4Yy1jMTRuIyI+PC9UcmFuc2Zvcm0+PC9UcmFuc2Zvcm1zPjxEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjc2hhMSI+PC9EaWdlc3RNZXRob2Q+PERpZ2VzdFZhbHVlPnl3NVZxWHlUTUh5NUNjdmRXN01TV2RhMDZMTT08L0RpZ2VzdFZhbHVlPjwvUmVmZXJlbmNlPjwvU2lnbmVkSW5mbz48U2lnbmF0dXJlVmFsdWU+WG9ZcURQaUorYy9IMlRFRjNQMWpQdVBUZ0VDVHp1cFVlRXpESERwMlE2ZW92T2lhN0pkVjI1bzZjTk1vczBTTzRISStSUGRUR3hJUW9xa0paeEtoTzZHcWZ2WHFDa2NNb2JCemxYbW83NUFSWU5jMHdlZ1hiQUVVQVFCcVNmeGwxc3huSlc1ZHZjclpuUytkSThoc2lZZW4vT0VTOUdtZUpsZVd1WUR4U0xmQjZJZnd6dk5LQ0xlS0FXenBkTk9NYmpQTjJyNUJWQUhQZEJ6WmtiSGZwdUlablp1Q2l5OENvaEo1bHU3WGZDbXpHdW96VDVqVE0wU3F6bHlzeUpWWVNSbVFUQW5WMVVGMGovbEx6SU14MVJmdWltWHNXaVk4c2RvQ2IrZXpBcVJnbk5EVSs3NlVYOEZFSEN3Q2c5a0tLSzQwMXdYNXpLd2FPRGJJUFpEYitBPT08L1NpZ25hdHVyZVZhbHVlPjxLZXlJbmZvPjxvOlNlY3VyaXR5VG9rZW5SZWZlcmVuY2UgeG1sbnM6bz0iaHR0cDovL2RvY3Mub2FzaXMtb3Blbi5vcmcvd3NzLzIwMDQvMDEvb2FzaXMtMjAwNDAxLXdzcy13c3NlY3VyaXR5LXNlY2V4dC0xLjAueHNkIj48bzpLZXlJZGVudGlmaWVyIFZhbHVlVHlwZT0iaHR0cDovL2RvY3Mub2FzaXMtb3Blbi5vcmcvd3NzL29hc2lzLXdzcy1zb2FwLW1lc3NhZ2Utc2VjdXJpdHktMS4xI1RodW1icHJpbnRTSEExIj5ZREJlRFNGM0Z4R2dmd3pSLzBwck11OTZoQ2M9PC9vOktleUlkZW50aWZpZXI+PC9vOlNlY3VyaXR5VG9rZW5SZWZlcmVuY2U+PC9LZXlJbmZvPjwvU2lnbmF0dXJlPjwvc2FtbDpBc3NlcnRpb24+";
            $workflowid           = '2317000001';
            $applicationprofileid = 14644;  // applicationprofileid provided velocity
            $merchantprofileid    = 'PrestaShop Global HC';
            $isTestAccount        = TRUE;
        } else {
            $identitytoken        = MODULE_PAYMENT_VELOCITY_IDENTITYTOKEN;
            $workflowid           = MODULE_PAYMENT_VELOCITY_WORKFLOWID;
            $applicationprofileid = MODULE_PAYMENT_VELOCITY_APPLICATIONPROFILEID;
            $merchantprofileid    = MODULE_PAYMENT_VELOCITY_MERCHANTPROFILEID;
            $isTestAccount        = FALSE;
        }

        try {            
            $velocityProcessor = new VelocityProcessor( $applicationprofileid, $merchantprofileid, $workflowid, $isTestAccount, $identitytoken );    
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $messageStack->add_session(FILENAME_CHECKOUT_FAILURE, $e->getMessage() . '<!-- ['.$this->code.'] -->', 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_FAILURE, '', 'SSL', true, false));
        }

        $avsData = unserialize(base64_decode($_SESSION['avsdata']));
        $cardData = unserialize(base64_decode($_SESSION['carddata']));

        /* Request for the verify avsdata and card data*/
        try {
            
            $response = $velocityProcessor->verify(array(  
                    'amount'       => $order->info['total'],
                    'avsdata'      => $avsData, 
                    'carddata'     => $cardData,
                    'entry_mode'   => 'Keyed',
                    'IndustryType' => 'Ecommerce',
                    'Reference'    => 'xyz',
                    'EmployeeId'   => '11'
            ));

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $messageStack->add_session(FILENAME_CHECKOUT_FAILURE, $e->getMessage() . '<!-- ['.$this->code.'] -->', 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_FAILURE, '', 'SSL', true, false));
        }

        $errors = '';
        if (is_array($response) && isset($response['Status']) && $response['Status'] == 'Successful') {

                /* Request for the authrizeandcapture transaction */
                try {
                        $cap_response = $velocityProcessor->authorizeAndCapture( array(
                                'amount'       => $order->info['total'], 
                                'avsdata'      => $avsData,
                                'token'        => $response['PaymentAccountDataToken'], 
                                'order_id'     => $insert_id,
                                'entry_mode'   => 'Keyed',
                                'IndustryType' => 'Ecommerce',
                                'Reference'    => 'xyz',
                                'EmployeeId'   => '11'
                        ));
 echo 'ashish';
                        if ( is_array($cap_response) && !empty($cap_response) && isset($cap_response['Status']) && $cap_response['Status'] == 'Successful') {

                                /* save the transaction detail with that order.*/ 
                                $sql = "insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, customer_notified, date_added) values (:orderComments, :orderID, :orderStatus, 1, now() )";
                                $sql = $db->bindVars($sql, ':orderComments', 'Credit Card - Velocity payment.  ApprovalCode: ' . $cap_response['ApprovalCode'] . '. TransID: ' . $cap_response['TransactionId'] . '.', 'string');
                                $sql = $db->bindVars($sql, ':orderID', $insert_id, 'integer');
                                $sql = $db->bindVars($sql, ':orderStatus', 2, 'integer');
                                $db->Execute($sql);

                                /* save the authandcap response into 'zen_velocity_transactions' custom table.*/ 
                                $sql = "insert into " . TABLE_PAYMENT_VELOCITY_TRANSACTIONS . " (transaction_id, transaction_status, order_id, response_obj) values (:transactionId, :transactionStatus, :orderID, :responseOBJ)";
                                $sql = $db->bindVars($sql, ':transactionId', $cap_response['TransactionId'], 'string');
                                $sql = $db->bindVars($sql, ':transactionStatus', $cap_response['Status'], 'string');
                                $sql = $db->bindVars($sql, ':orderID', $insert_id, 'string');
                                $sql = $db->bindVars($sql, ':responseOBJ', json_encode($cap_response), 'string');
                                $db->Execute($sql);

                                /* for update the order status */
                                $db->Execute("update " . TABLE_ORDERS . " set orders_status = 2 where orders_id='" . $insert_id . "'"); 

                        } else if ( is_array($cap_response) && !empty($cap_response) ) {
                                $errors .= $cap_response['StatusMessage'];
                        } else if (is_string($cap_response)) {
                                $errors .= $cap_response;
                        } else {
                                $errors .= 'Unknown Error in authandcap process please contact the site admin';
                        }
                } catch(Exception $e) {
                        $errors .= $e->getMessage();
                }

        } else if (is_array($response) &&(isset($response['Status']) && $response['Status'] != 'Successful')) {
                $errors .= $response['StatusMessage'];
        } else if (is_string($response)) {
                $errors .= $response;
        } else {
                $errors .= 'Unknown Error in verification process please contact the site admin';
        }

        if ($errors != '') {
                $_SESSION['error'] = $errors;
                $messageStack->add_session(FILENAME_CHECKOUT_FAILURE, $errors . '<!-- ['.$this->code.'] -->', 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_FAILURE, '', 'SSL', true, false));
        }

        return true;
    }

    /**
     * Check to see whether module is installed
     *
     * @return boolean
     */
    function check() {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_VELOCITY_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    /**
     * Install the payment module and its configuration settings
     *
     */
    function install() {
        global $db, $messageStack;
        if (defined('MODULE_PAYMENT_VELOCITY_STATUS')) {
            $messageStack->add_session('Velocity module already installed.', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=velocity', 'NONSSL'));
            return 'failed';
        }
        $identitytoken = 'PHNhbWw6QXNzZXJ0aW9uIE1ham9yVmVyc2lvbj0iMSIgTWlub3JWZXJzaW9uPSIxIiBBc3NlcnRpb25JRD0iXzdlMDhiNzdjLTUzZWEtNDEwZC1hNmJiLTAyYjJmMTAzMzEwYyIgSXNzdWVyPSJJcGNBdXRoZW50aWNhdGlvbiIgSXNzdWVJbnN0YW50PSIyMDE0LTEwLTEwVDIwOjM2OjE4LjM3OVoiIHhtbG5zOnNhbWw9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjEuMDphc3NlcnRpb24iPjxzYW1sOkNvbmRpdGlvbnMgTm90QmVmb3JlPSIyMDE0LTEwLTEwVDIwOjM2OjE4LjM3OVoiIE5vdE9uT3JBZnRlcj0iMjA0NC0xMC0xMFQyMDozNjoxOC4zNzlaIj48L3NhbWw6Q29uZGl0aW9ucz48c2FtbDpBZHZpY2U+PC9zYW1sOkFkdmljZT48c2FtbDpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PHNhbWw6U3ViamVjdD48c2FtbDpOYW1lSWRlbnRpZmllcj5GRjNCQjZEQzU4MzAwMDAxPC9zYW1sOk5hbWVJZGVudGlmaWVyPjwvc2FtbDpTdWJqZWN0PjxzYW1sOkF0dHJpYnV0ZSBBdHRyaWJ1dGVOYW1lPSJTQUsiIEF0dHJpYnV0ZU5hbWVzcGFjZT0iaHR0cDovL3NjaGVtYXMuaXBjb21tZXJjZS5jb20vSWRlbnRpdHkiPjxzYW1sOkF0dHJpYnV0ZVZhbHVlPkZGM0JCNkRDNTgzMDAwMDE8L3NhbWw6QXR0cmlidXRlVmFsdWU+PC9zYW1sOkF0dHJpYnV0ZT48c2FtbDpBdHRyaWJ1dGUgQXR0cmlidXRlTmFtZT0iU2VyaWFsIiBBdHRyaWJ1dGVOYW1lc3BhY2U9Imh0dHA6Ly9zY2hlbWFzLmlwY29tbWVyY2UuY29tL0lkZW50aXR5Ij48c2FtbDpBdHRyaWJ1dGVWYWx1ZT5iMTVlMTA4MS00ZGY2LTQwMTYtODM3Mi02NzhkYzdmZDQzNTc8L3NhbWw6QXR0cmlidXRlVmFsdWU+PC9zYW1sOkF0dHJpYnV0ZT48c2FtbDpBdHRyaWJ1dGUgQXR0cmlidXRlTmFtZT0ibmFtZSIgQXR0cmlidXRlTmFtZXNwYWNlPSJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcyI+PHNhbWw6QXR0cmlidXRlVmFsdWU+RkYzQkI2REM1ODMwMDAwMTwvc2FtbDpBdHRyaWJ1dGVWYWx1ZT48L3NhbWw6QXR0cmlidXRlPjwvc2FtbDpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PFNpZ25hdHVyZSB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnIyI+PFNpZ25lZEluZm8+PENhbm9uaWNhbGl6YXRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiPjwvQ2Fub25pY2FsaXphdGlvbk1ldGhvZD48U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3JzYS1zaGExIj48L1NpZ25hdHVyZU1ldGhvZD48UmVmZXJlbmNlIFVSST0iI183ZTA4Yjc3Yy01M2VhLTQxMGQtYTZiYi0wMmIyZjEwMzMxMGMiPjxUcmFuc2Zvcm1zPjxUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjZW52ZWxvcGVkLXNpZ25hdHVyZSI+PC9UcmFuc2Zvcm0+PFRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMTAveG1sLWV4Yy1jMTRuIyI+PC9UcmFuc2Zvcm0+PC9UcmFuc2Zvcm1zPjxEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjc2hhMSI+PC9EaWdlc3RNZXRob2Q+PERpZ2VzdFZhbHVlPnl3NVZxWHlUTUh5NUNjdmRXN01TV2RhMDZMTT08L0RpZ2VzdFZhbHVlPjwvUmVmZXJlbmNlPjwvU2lnbmVkSW5mbz48U2lnbmF0dXJlVmFsdWU+WG9ZcURQaUorYy9IMlRFRjNQMWpQdVBUZ0VDVHp1cFVlRXpESERwMlE2ZW92T2lhN0pkVjI1bzZjTk1vczBTTzRISStSUGRUR3hJUW9xa0paeEtoTzZHcWZ2WHFDa2NNb2JCemxYbW83NUFSWU5jMHdlZ1hiQUVVQVFCcVNmeGwxc3huSlc1ZHZjclpuUytkSThoc2lZZW4vT0VTOUdtZUpsZVd1WUR4U0xmQjZJZnd6dk5LQ0xlS0FXenBkTk9NYmpQTjJyNUJWQUhQZEJ6WmtiSGZwdUlablp1Q2l5OENvaEo1bHU3WGZDbXpHdW96VDVqVE0wU3F6bHlzeUpWWVNSbVFUQW5WMVVGMGovbEx6SU14MVJmdWltWHNXaVk4c2RvQ2IrZXpBcVJnbk5EVSs3NlVYOEZFSEN3Q2c5a0tLSzQwMXdYNXpLd2FPRGJJUFpEYitBPT08L1NpZ25hdHVyZVZhbHVlPjxLZXlJbmZvPjxvOlNlY3VyaXR5VG9rZW5SZWZlcmVuY2UgeG1sbnM6bz0iaHR0cDovL2RvY3Mub2FzaXMtb3Blbi5vcmcvd3NzLzIwMDQvMDEvb2FzaXMtMjAwNDAxLXdzcy13c3NlY3VyaXR5LXNlY2V4dC0xLjAueHNkIj48bzpLZXlJZGVudGlmaWVyIFZhbHVlVHlwZT0iaHR0cDovL2RvY3Mub2FzaXMtb3Blbi5vcmcvd3NzL29hc2lzLXdzcy1zb2FwLW1lc3NhZ2Utc2VjdXJpdHktMS4xI1RodW1icHJpbnRTSEExIj5ZREJlRFNGM0Z4R2dmd3pSLzBwck11OTZoQ2M9PC9vOktleUlkZW50aWZpZXI+PC9vOlNlY3VyaXR5VG9rZW5SZWZlcmVuY2U+PC9LZXlJbmZvPjwvU2lnbmF0dXJlPjwvc2FtbDpBc3NlcnRpb24+';
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Velocity Module', 'MODULE_PAYMENT_VELOCITY_STATUS', 'True', 'Do you want to accept Velocity payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('IdentityToken', 'MODULE_PAYMENT_VELOCITY_IDENTITYTOKEN', '" . $identitytoken . "', 'Identity Token provided by the velocity gateway', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ServiceId/WorkflowId', 'MODULE_PAYMENT_VELOCITY_WORKFLOWID', '2317000001', 'serviceId or workflowid provided by the velocity gateway', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ApplicationProfileId', 'MODULE_PAYMENT_VELOCITY_APPLICATIONPROFILEID', '14644', 'Applicationprofileid is provided by the velocity gateway', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('MerchantProfileId', 'MODULE_PAYMENT_VELOCITY_MERCHANTPROFILEID', 'PrestaShop Global HC', 'Merchantprofileid is also provided by the velocity gateways.', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_VELOCITY_TESTMODE', 'Test', 'Transaction mode used for processing orders', '6', '0', 'zen_cfg_select_option(array(\'Test\', \'Production\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Method', 'MODULE_PAYMENT_VELOCITY_METHOD', 'Credit Card - Velocity', 'Transaction method used for processing orders', '6', '0', 'zen_cfg_select_option(array(\'Credit Card - Velocity\'), ', now())");//, \'eCheck\'
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Customer Notifications', 'MODULE_PAYMENT_VELOCITY_EMAIL_CUSTOMER', 'False', 'Should nabvelocity.com email a receipt to the customer?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_VELOCITY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_VELOCITY_ORDER_STATUS_ID', '1', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Debug Mode', 'MODULE_PAYMENT_VELOCITY_DEBUGGING', 'Alerts Only', 'Would you like to enable debug mode?  A  detailed log of failed transactions may be emailed to the store owner.', '6', '0', 'zen_cfg_select_option(array(\'Off\', \'Alerts Only\', \'Log File\', \'Log and Email\'), ', now())");

        /* For add custom refund order status */
        $v_status = $db->Execute("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Velocity Refund'");

        if (!isset($v_status->fields['orders_status_name']) && $v_status->fields['orders_status_name'] != 'Velocity Refund') {
                $max_order_id = $db->Execute("select max(orders_status_id) from " . TABLE_ORDERS_STATUS );
                $v_order_id = (int)$max_order_id->fields['max(orders_status_id)'] + 1;
                $db->Execute("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values (" . $v_order_id . ",1,'Velocity Refund')");
        }
    }

    /**
     * Remove the module and all its settings
     *
     */
    function remove() {
        global $db;
        $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->confg_keys()) . "')");
    }

    /**
     * Internal list of configuration keys used for configuration of the module
     *
     * @return array
     */
    function keys() {
        return array('MODULE_PAYMENT_VELOCITY_STATUS', 'MODULE_PAYMENT_VELOCITY_IDENTITYTOKEN', 'MODULE_PAYMENT_VELOCITY_WORKFLOWID', 'MODULE_PAYMENT_VELOCITY_APPLICATIONPROFILEID', 'MODULE_PAYMENT_VELOCITY_MERCHANTPROFILEID', 'MODULE_PAYMENT_VELOCITY_TESTMODE');
    }

    /**
     * Internal list of configuration keys used for remove at uninstallation.
     *
     * @return array
     */
    function confg_keys() {
        return array('MODULE_PAYMENT_VELOCITY_STATUS', 'MODULE_PAYMENT_VELOCITY_IDENTITYTOKEN', 'MODULE_PAYMENT_VELOCITY_WORKFLOWID', 'MODULE_PAYMENT_VELOCITY_APPLICATIONPROFILEID', 'MODULE_PAYMENT_VELOCITY_MERCHANTPROFILEID', 'MODULE_PAYMENT_VELOCITY_TESTMODE', 'MODULE_PAYMENT_VELOCITY_METHOD', 'MODULE_PAYMENT_VELOCITY_EMAIL_CUSTOMER', 'MODULE_PAYMENT_VELOCITY_SORT_ORDER', 'MODULE_PAYMENT_VELOCITY_ORDER_STATUS_ID', 'MODULE_PAYMENT_VELOCITY_DEBUGGING');
    }
  
}
