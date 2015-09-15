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
                
                $identitytoken        = MODULE_PAYMENT_VELOCITY_IDENTITYTOKEN;
                $workflowid           = MODULE_PAYMENT_VELOCITY_WORKFLOWID;
                $applicationprofileid = MODULE_PAYMENT_VELOCITY_APPLICATIONPROFILEID;
                $merchantprofileid    = MODULE_PAYMENT_VELOCITY_MERCHANTPROFILEID;
                
                if (MODULE_PAYMENT_VELOCITY_TESTMODE == 'Test') 
                    $isTestAccount        = TRUE;
                else
                    $isTestAccount        = FALSE;
                
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
                        
                        $xml = VelocityXmlCreator::returnByIdXML(number_format($refund_amount, 2, '.', ''), $txnid->fields['transaction_id']);  // got ReturnById xml object.  
                        $req = $xml->saveXML();
                        $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
                        $txt = print_r($response, 1);
                        fwrite($myfile, $txt);
                        fclose($myfile);
                        if ( is_array($response) && !empty($response) && isset($response['Status']) && $response['Status'] == 'Successful') {

                            /* save the returnbyid response into 'zen_velocity_transactions' custom table.*/ 
                            $sql = "insert into " . TABLE_PAYMENT_VELOCITY_TRANSACTIONS . " (transaction_id, transaction_status, order_id, request_obj, response_obj) values (:transactionId, :transactionStatus, :orderID, :requestOBJ, :responseOBJ)";
                            $sql = $db->bindVars($sql, ':transactionId', $response['TransactionId'], 'string');
                            $sql = $db->bindVars($sql, ':transactionStatus', $response['Status'], 'string');
                            $sql = $db->bindVars($sql, ':orderID', $refundD['oID'], 'string');
                            $sql = $db->bindVars($sql, ':requestOBJ', serialize($req), 'string');
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