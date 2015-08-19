<?php
/**
 * @package admin
 * @copyright Copyright 2015 Velocity, Chetu Development Team
 * @license 
 * @version GIT: $Id: Author: Ashish  Tue Aug 1 2015
 */
define('TITLE', 'Velocity Refund Page');
require('includes/application_top.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
function init()
{
  cssjsmenu('navbar');
  if (document.getElementById)
  {
    var kill = document.getElementById('hoverJS');
    kill.disabled = true;
  }
}
// -->
</script>
</head>
<body onload="init()">
<!-- header //-->
<?php

if (isset($_POST)) {
    
    if(isset($_POST['securityToken']))
        $_SESSION['refund'] = $_POST;

    $refundD = $_SESSION['refund'];

    define('TABLE_PAYMENT_VELOCITY_TRANSACTIONS', $refundD['velocity_table']);
    include(DIR_WS_CLASSES . 'order.php');
    $order = new order((int)$refundD['oID']);


    // return to order edit page without refund.
    if (isset($_POST['notnow']) && $_POST['notnow'] == 'Not Now') {
        unset($_SESSION['refund']);
        zen_redirect(str_replace('update_order', 'edit', $refundD['form_url']));
    }

    $errors = '';
    // Process the refund. 
    if ( isset($_POST['processrefund']) && $_POST['processrefund'] == 'Process Refund' ) {
        
        if(isset($_POST['refundamount']) && $_POST['refundamount'] != '') {

            $refund_am = $_POST['refundamount'];
            $txnid = $db->Execute("SELECT `transaction_id` FROM " . TABLE_PAYMENT_VELOCITY_TRANSACTIONS . " WHERE `order_id` =" . $refundD['oID']);

            if (is_numeric($refund_am) && isset($txnid->fields['transaction_id'])) {

                $refund_ship = isset($_POST['shippingamount']) ? $_POST['shippingamount'] : 0;
                $refund_amount = (float)$refund_am + (float)$refund_ship;

                include('Velocity.php');
                if (MODULE_PAYMENT_VELOCITY_TESTMODE == 'Test') {
                    $identitytoken        = "PHNhbWw6QXNzZXJ0aW9uIE1ham9yVmVyc2lvbj0iMSIgTWlub3JWZXJzaW9uPSIxIiBBc3NlcnRpb25JRD0iXzdlMDhiNzdjLTUzZWEtNDEwZC1hNmJiLTAyYjJmMTAzMzEwYyIgSXNzdWVyPSJJcGNBdXRoZW50aWNhdGlvbiIgSXNzdWVJbnN0YW50PSIyMDE0LTEwLTEwVDIwOjM2OjE4LjM3OVoiIHhtbG5zOnNhbWw9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjEuMDphc3NlcnRpb24iPjxzYW1sOkNvbmRpdGlvbnMgTm90QmVmb3JlPSIyMDE0LTEwLTEwVDIwOjM2OjE4LjM3OVoiIE5vdE9uT3JBZnRlcj0iMjA0NC0xMC0xMFQyMDozNjoxOC4zNzlaIj48L3NhbWw6Q29uZGl0aW9ucz48c2FtbDpBZHZpY2U+PC9zYW1sOkFkdmljZT48c2FtbDpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PHNhbWw6U3ViamVjdD48c2FtbDpOYW1lSWRlbnRpZmllcj5GRjNCQjZEQzU4MzAwMDAxPC9zYW1sOk5hbWVJZGVudGlmaWVyPjwvc2FtbDpTdWJqZWN0PjxzYW1sOkF0dHJpYnV0ZSBBdHRyaWJ1dGVOYW1lPSJTQUsiIEF0dHJpYnV0ZU5hbWVzcGFjZT0iaHR0cDovL3NjaGVtYXMuaXBjb21tZXJjZS5jb20vSWRlbnRpdHkiPjxzYW1sOkF0dHJpYnV0ZVZhbHVlPkZGM0JCNkRDNTgzMDAwMDE8L3NhbWw6QXR0cmlidXRlVmFsdWU+PC9zYW1sOkF0dHJpYnV0ZT48c2FtbDpBdHRyaWJ1dGUgQXR0cmlidXRlTmFtZT0iU2VyaWFsIiBBdHRyaWJ1dGVOYW1lc3BhY2U9Imh0dHA6Ly9zY2hlbWFzLmlwY29tbWVyY2UuY29tL0lkZW50aXR5Ij48c2FtbDpBdHRyaWJ1dGVWYWx1ZT5iMTVlMTA4MS00ZGY2LTQwMTYtODM3Mi02NzhkYzdmZDQzNTc8L3NhbWw6QXR0cmlidXRlVmFsdWU+PC9zYW1sOkF0dHJpYnV0ZT48c2FtbDpBdHRyaWJ1dGUgQXR0cmlidXRlTmFtZT0ibmFtZSIgQXR0cmlidXRlTmFtZXNwYWNlPSJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcyI+PHNhbWw6QXR0cmlidXRlVmFsdWU+RkYzQkI2REM1ODMwMDAwMTwvc2FtbDpBdHRyaWJ1dGVWYWx1ZT48L3NhbWw6QXR0cmlidXRlPjwvc2FtbDpBdHRyaWJ1dGVTdGF0ZW1lbnQ+PFNpZ25hdHVyZSB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnIyI+PFNpZ25lZEluZm8+PENhbm9uaWNhbGl6YXRpb25NZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiPjwvQ2Fub25pY2FsaXphdGlvbk1ldGhvZD48U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3JzYS1zaGExIj48L1NpZ25hdHVyZU1ldGhvZD48UmVmZXJlbmNlIFVSST0iI183ZTA4Yjc3Yy01M2VhLTQxMGQtYTZiYi0wMmIyZjEwMzMxMGMiPjxUcmFuc2Zvcm1zPjxUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjZW52ZWxvcGVkLXNpZ25hdHVyZSI+PC9UcmFuc2Zvcm0+PFRyYW5zZm9ybSBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvMTAveG1sLWV4Yy1jMTRuIyI+PC9UcmFuc2Zvcm0+PC9UcmFuc2Zvcm1zPjxEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjc2hhMSI+PC9EaWdlc3RNZXRob2Q+PERpZ2VzdFZhbHVlPnl3NVZxWHlUTUh5NUNjdmRXN01TV2RhMDZMTT08L0RpZ2VzdFZhbHVlPjwvUmVmZXJlbmNlPjwvU2lnbmVkSW5mbz48U2lnbmF0dXJlVmFsdWU+WG9ZcURQaUorYy9IMlRFRjNQMWpQdVBUZ0VDVHp1cFVlRXpESERwMlE2ZW92T2lhN0pkVjI1bzZjTk1vczBTTzRISStSUGRUR3hJUW9xa0paeEtoTzZHcWZ2WHFDa2NNb2JCemxYbW83NUFSWU5jMHdlZ1hiQUVVQVFCcVNmeGwxc3huSlc1ZHZjclpuUytkSThoc2lZZW4vT0VTOUdtZUpsZVd1WUR4U0xmQjZJZnd6dk5LQ0xlS0FXenBkTk9NYmpQTjJyNUJWQUhQZEJ6WmtiSGZwdUlablp1Q2l5OENvaEo1bHU3WGZDbXpHdW96VDVqVE0wU3F6bHlzeUpWWVNSbVFUQW5WMVVGMGovbEx6SU14MVJmdWltWHNXaVk4c2RvQ2IrZXpBcVJnbk5EVSs3NlVYOEZFSEN3Q2c5a0tLSzQwMXdYNXpLd2FPRGJJUFpEYitBPT08L1NpZ25hdHVyZVZhbHVlPjxLZXlJbmZvPjxvOlNlY3VyaXR5VG9rZW5SZWZlcmVuY2UgeG1sbnM6bz0iaHR0cDovL2RvY3Mub2FzaXMtb3Blbi5vcmcvd3NzLzIwMDQvMDEvb2FzaXMtMjAwNDAxLXdzcy13c3NlY3VyaXR5LXNlY2V4dC0xLjAueHNkIj48bzpLZXlJZGVudGlmaWVyIFZhbHVlVHlwZT0iaHR0cDovL2RvY3Mub2FzaXMtb3Blbi5vcmcvd3NzL29hc2lzLXdzcy1zb2FwLW1lc3NhZ2Utc2VjdXJpdHktMS4xI1RodW1icHJpbnRTSEExIj5ZREJlRFNGM0Z4R2dmd3pSLzBwck11OTZoQ2M9PC9vOktleUlkZW50aWZpZXI+PC9vOlNlY3VyaXR5VG9rZW5SZWZlcmVuY2U+PC9LZXlJbmZvPjwvU2lnbmF0dXJlPjwvc2FtbDpBc3NlcnRpb24+";
                    $workflowid           = '2317000001';
                    $applicationprofileid = 14644;  // applicationprofileid provided velocity
                    $merchantprofileid    = 'PrestaShop Global HC';
                    $isTestAccount        = TRUE;
                } else {
                    $identitytoken        = MODULE_PAYMENT_VELOCITY_IDENTITYTOKEN;
                    $workflowid           = MODULE_PAYMENT_VELOCITY_MERCHANTPROFILEID;
                    $applicationprofileid = MODULE_PAYMENT_VELOCITY_APPLICATIONPROFILEID;
                    $merchantprofileid    = MODULE_PAYMENT_VELOCITY_WORKFLOWID;
                    $isTestAccount        = FALSE;
                }
                try {            
                        $velocityProcessor = new VelocityProcessor( $applicationprofileid, $merchantprofileid, $workflowid, $isTestAccount, $identitytoken );    
                } catch (Exception $e) {
                        $messageStack->add_session($e->getMessage(), 'error');
                        zen_redirect(zen_href_link('velocityRefund'));
                }

                if ($refund_amount <= $order->info['total']) {

                    try {
                        // request for refund
                        $response = $velocityProcessor->returnById(array(  
                            'amount'        => $refund_amount,
                            'TransactionId' => $txnid->fields['transaction_id']
                        ));
                        
                        $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
                        $txt = print_r($response, 1);
                        fwrite($myfile, $txt);
                        fclose($myfile);
                        if ( is_array($response) && !empty($response) && isset($response['Status']) && $response['Status'] == 'Successful') {

                            /* save the returnbyid response into 'zen_velocity_transactions' custom table.*/ 
                            $sql = "insert into " . TABLE_PAYMENT_VELOCITY_TRANSACTIONS . " (transaction_id, transaction_status, order_id, response_obj) values (:transactionId, :transactionStatus, :orderID, :responseOBJ)";
                            $sql = $db->bindVars($sql, ':transactionId', $response['TransactionId'], 'string');
                            $sql = $db->bindVars($sql, ':transactionStatus', $response['Status'], 'string');
                            $sql = $db->bindVars($sql, ':orderID', $refundD['oID'], 'string');
                            $sql = $db->bindVars($sql, ':responseOBJ', serialize($response), 'string');
                            $db->Execute($sql);

                            /* Update the refund detail into comment table at admin order detail..*/
                            if($refundD['comments'] == '')
                                $refundD['comments'] .= "Refund Amount is $" . $refund_amount .", Approval Code is " . $response['ApprovalCode'] ." and Refund Transaction Id is " . $response['TransactionId'];
                            else 
                                $refundD['comments'] .= "\r\n Refund Amount is $" . $refund_amount .", Approval Code is " . $response['ApprovalCode'] ." and Refund Transaction Id is " . $response['TransactionId'];
                            
                            /* update order status.*/ 
                            echo '<form method="post" id="update_refund" action="' . $refundD['form_url'] . '" />
                            <input type="hidden" name="securityToken" value="' . $refundD['securityToken'] . '" />
                            <input type="hidden" name="comments" value="' . $refundD['comments'] . '" />
                            <input type="hidden" name="status" value="' . $refundD['status'] . '" />
                            <input type="hidden" name="notify" value="' . $refundD['notify'] . '" />
                            <input type="hidden" name="notify_comments" value="' . $refundD['notify_comments'] . '" />
                            <input type="hidden" name="x" value="' . $refundD['x'] . '" />
                            <input type="hidden" name="y" value="' . $refundD['y'] . '" />
                            </form>';

                            // unset all session
                            unset($_SESSION['refund']);
                            
                            $messageStack->add_session('Refund has been successfully done.', 'success');
                            echo '<script>document.getElementById("update_refund").submit();</script>';

                        } else if (is_array($response) && !empty($response)) {
                            $errors .= $response['StatusMessage'];
                        } else if (is_string($response)) {
                            $errors .= $response;
                        } else {
                            $errors .= 'Unknown Error please contact the site admin';
                        }

                    } catch(Exception $e) {
                        $messageStack->add_session($e->getMessage(), 'error');
                        zen_redirect(zen_href_link('velocityRefund'));
                    }

                } else {
                    $errors .= 'Max refund amount is ' . $order->info['total'];
                }
            } else {
                if (!is_numeric($refund_am)) {
                    $errors .= 'Must be numeric value for refund.';   
                } else if(!isset($txnid->fields['transaction_id'])) {
                    $errors .= 'This transaction payment not done by the velocity gateway or payment not completed';
                }
            } 

        } else {
            $errors .= 'Must be put some refund amount before refund process.';
        }

        if($errors != '') {
            $messageStack->add_session($errors , 'error');
            zen_redirect(zen_href_link('velocityRefund'));
        }
    }
}
require(DIR_WS_INCLUDES . 'header.php');
?>
<!-- header_eof //-->
<!-- body //-->
<table style="width:40%; height: 20%; padding:10px">
    <form method="post">
        <tr><td>Refund Amount :</td><td><input type="text" name="refundamount" /></td></tr>
        <tr><td>Refund Shipping :</td><td><input type="checkbox" name="shippingamount" value="<?php if(isset($order->totals[1]['value'])) echo $order->totals[1]['value']; ?>" /></td></tr>
        <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>&nbsp;</td><td><input type="submit" name="notnow" value="Not Now" />&nbsp;&nbsp;&nbsp;<input type="submit" name="processrefund" value="Process Refund" /></td></tr>
    </form>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>