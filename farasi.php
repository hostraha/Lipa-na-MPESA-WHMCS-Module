<?php
/**
 * WHMCS Sample Merchant Gateway Module
 *
 * This sample file demonstrates how a merchant gateway module supporting
 * 3D Secure Authentication, Captures and Refunds can be structured.
 *
 * If your merchant gateway does not support 3D Secure Authentication, you can
 * simply omit that function and the callback file from your own module.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "merchantgateway" and therefore all functions
 * begin "merchantgateway_".
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2019
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}


function kavatec_mpesa_MetaData()
{
    return array(
        'DisplayName' => 'KAVATEC',
        'APIVersion' => '1.1', // Use API Version 1.1
    );
}


function kavatec_mpesa_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Kavatec Mpesa',
        ),
        
     
         'lisence' => array(
            'FriendlyName' => 'Your License',
            'Type' => 'text',
            'Size' => '300',
            'Default' => '',
            'Description' => 'Enter Your License (Get it from Kavatec.co.ke)',
        ),
        
        'shortCodeType' => array(
            'FriendlyName' => 'Short Code Type',
            'Type' => 'radio',
            'Options' => 'Paybill,Till',
            'Default' => 'Paybill',
            'Description' => 'Choose Short Code Type',
        ),
        
        'shortCode' => array(
            'FriendlyName' => 'Short Code',
            'Type' => 'text',
            'Size' => '6',
            'Default' => '',
            'Description' => 'Enter Short Code',
        ),
        
         'storeNumber' => array(
            'FriendlyName' => 'Store Number',
            'Type' => 'text',
            'Size' => '6',
            'Default' => '',
            'Description' => 'Store Number (Applicable for till numbers)',
        ),
        
        'consumerKey' => array(
            'FriendlyName' => 'Consumer Key',
            'Type' => 'text',
            'Size' => '300',
            'Default' => '',
            'Description' => 'Enter Consumer Key',
        ),
        
        
        'consumerSecret' => array(
            'FriendlyName' => 'Consumer Secret',
            'Type' => 'text',
            'Size' => '300',
            'Default' => '',
            'Description' => 'Enter Consumer Secret',
        ),
        
        'lipaNaMpesaPasskey' => array(
            'FriendlyName' => 'Mpesa Passkey',
            'Type' => 'text',
            'Size' => '400',
            'Default' => '',
            'Description' => 'Enter Mpesa Passkey (Used in STK Push)',
        ),
        
         'mpesaApiVersion' => array(
            'FriendlyName' => 'Mpesa Api Version',
            'Type' => 'radio',
            'Options' => 'v1,v2',
            'Default' => 'v2',
            'Description' => 'Mpesa Api Version',
        ),
        

    );
}



function kavatec_mpesa_link($params)
{
    // Gateway Configuration Parameters
    $lisence = $params['lisence'];
    $shortCode = $params['shortCode'];
    $consumerKey = $params['consumerKey'];
    $consumerSecret = $params['consumerSecret'];
    $lipaNaMpesaPasskey = $params['lipaNaMpesaPasskey'];
    
    $shortCodeType = $params['shortCodeType'];
    $mpesaApiVersion = $params['mpesaApiVersion'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Credit Card Parameters
   /* $cardType = $params['cardtype'];
    $cardNumber = $params['cardnum'];
    $cardExpiry = $params['cardexp'];
    $cardStart = $params['cardstart'];
    $cardIssueNumber = $params['cardissuenum'];
    $cardCvv = $params['cccvv'];*/

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];
    
    $currentlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    
    $addfundslink=str_replace('//clientarea','/clientarea', ''.$systemUrl.'/clientarea.php?action=addfunds');
    
    $invoicelinkredirect=str_replace('//viewinvoice', '/viewinvoice', ''.$systemUrl.'/viewinvoice.php?id='.$invoiceId.'');
    
    
    if($currentlink==$addfundslink)
    {
        header('Location: ' . $invoicelinkredirect);
        exit();
    };

    
    // perform API call to capture payment and interpret result

    
    if($_POST['verifypayment'])
    {
        if($shortCodeType=='Till'){
            
            $url='https://pay.kavatec.co.ke/Pay/checkpaymentstill/'.$lisence.'/'.$shortCode.'/'.$amount.'/'.$params['invoiceid'].'/'.$_SERVER["SERVER_NAME"].'/'.$consumerKey.'/'.$consumerSecret.'/'.$mpesaApiVersion.'/'.$_POST['trasactioncode'].'/'.$params['storeNumber'].'';
            $obj=json_decode(file_get_contents($url), true);
            
            if($obj['Success'])
            {
               
                 $returnData="<div class='alert alert-success'><strong>Success! You have paid (Kshs ".$obj["amount"].").</strong></div>";
               //$returnData="";
               
               addInvoicePayment(
                    $invoiceId,
                    $obj['transid'],
                    $obj['amount'],
                    0,
                    $moduleName
                );
                
                header("Refresh:0");
                
               // header("Location: '.$returnUrl.'");
            }
            else
            {

                $returnData="<div class='alert alert-danger'><strong> Error! </strong> ".$obj['Error']."</div>";

            }
        }
        else
        {
   
            $url='https://pay.kavatec.co.ke/Pay/checkpayments/'.$lisence.'/'.$shortCode.'/'.$amount.'/'.$params['invoiceid'].'/'.$_SERVER["SERVER_NAME"].'/'.$consumerKey.'/'.$consumerSecret.'/'.$mpesaApiVersion.'';
            $obj=json_decode(file_get_contents($url), true);
            
            if($obj['Success'])
            {
                $returnData="<div class='alert alert-success'><strong>Success! ".$obj['Success']."</strong></div>";
               //$returnData="";
               
               addInvoicePayment(
                    $invoiceId,
                    $obj['transid'],
                    $obj['amount'],
                    0,
                    $moduleName
                );
                
                header("Refresh:0");
                
                //header("Location: '.$returnUrl.'");
     
            }
            else
            {
                $returnData="<div class='alert alert-danger'><strong> Error! </strong> ".$obj['Error']."</div>";
         
            }
        }
        
     
    }
    else if($_POST['sendstkpush'])
    {
        
        if ($shortCodeType=='Till'){$TransactionType='CustomerBuyGoodsOnline';}else{$TransactionType='CustomerPayBillOnline';}
        
        $url='https://hostraha.co.ke/process/send.php/'.$_POST['phone'].'/';
        $obj=json_decode(file_get_contents($url), true);
        
        if($obj['Success'])
        {
           $returnData="<div class='alert alert-success'><strong>Success! Payment Request has been sent to ".$_POST['phone'].".Check your phone and enter Pin </strong></div>";
        }
        else
        {
            $returnData="<div class='alert alert-danger'><strong> Error! </strong> ".$obj['Error']."</div>";
        }
        
        
    }
    else
    {
        //$returnData="<div class='alert alert-danger'><strong> Error! </strong> No payment received</div>";
        $returnData="";
        
      
    }
    
    
     $inst= "<img src='https://pay.kavatec.co.ke/mpesalogo.png' alt='' style='width:200px;'><br>
        <mpesaprocess>
            <h6> <b>Enter business no <strong style='color:red'>".$shortCode."</strong></b></h6>  
            <h6> <b>Enter account no <strong style='color:red'>".$params['invoiceid']."</strong></b></h6> 
            <h6> <b>Enter amount <strong style='color:red'>".$amount.' '.$currencyCode."</strong></b></h6>  
            <form method='post' action=".$invoiceurl."><br>
            <!--<input type='number' name='amountmpesa' class='form-control input-lg' placeholder='e.g 100'>
            <input type='text' name='paymentphone' class='form-control input-lg' placeholder='e.g 0700000000'>-->
            <p> </P>
            <span class='inline-form-element'>
               <button type='submit' name='verifypayment' value='verifypayment' class='btn btn-block btn-lg btn-primary'>Verify Payment</button>
            </span>
            <p> </P>
           </form>
            
           <form method='post' action=".$invoiceurl.">
           
              <h6><b>SEND STK PUSH</b></h6>  
                <span class='inline-form-element'>
                    <input type='text' name='phone' value='phone' class='btn btn-block btn-lg' placeholder='e.g 0700000000' required>
                </span>
                <p> </P>
                <span class='inline-form-element'>
                   <button type='submit' name='sendstkpush' value='sendstkpush'  class='btn btn-block btn-lg btn-primary'>Send STK Push</button>
                </span>
            </form>";
            
        $inst2= "<img src='https://pay.kavatec.co.ke/mpesalogo.png' alt='' style='width:200px;'><br>
        <mpesaprocess>
            <h6> <b>Buy Goods and Services</b></h6>  
            <h6> <b>Enter Till Number <strong style='color:red'>".$shortCode."</strong></b></h6> 
            <h6> <b>Enter amount <strong style='color:red'>".$amount.' '.$currencyCode."</strong></b></h6>  
            <form method='post' action=".$invoiceurl."><br>
            <!--<input type='number' name='amountmpesa' class='form-control input-lg' placeholder='e.g 100'>
            <input type='text' name='paymentphone' class='form-control input-lg' placeholder='e.g 0700000000'>-->
            <p> </P>
            
            <div class='form-group'>
               <input type='text' name='trasactioncode' class='btn btn-lg' placeholder='QDA75TKUCV' style='background-color:white' required><hr>
               <button type='submit' name='verifypayment' value='verifypayment' class='btn btn-block btn-lg btn-primary'>Verify Payment</button>
            </div>
            <p> </P>
           </form>
           
           <form method='post' action=".$invoiceurl.">
           
              <h6><b>SEND STK PUSH</b></h6>  
                <span class='inline-form-element'>
                    <input type='text' name='phone' value='0$phone' class='btn btn-block btn-lg' placeholder='e.g 0700000000' required  style='background-color:white'>
                    
                </span>
                <p> </P>
                <span class='inline-form-element'>
                   <button type='submit' name='sendstkpush' value='sendstkpush'  class='btn btn-block btn-lg btn-primary'>Send STK Push</button>
                </span>
            </form>";
            
        if($shortCodeType=='Till'){
            $instructions=$inst2;
        }
        else
        {
            $instructions=$inst;
        }
            
        
                 
    $returnData.=$instructions;

    return $returnData;
}




/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
/*function kavatec_mpesa_refund($params)
{
    // Gateway Configuration Parameters
    $accountId = $params['accountID'];
    $secretKey = $params['secretKey'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];

    // Transaction Parameters
    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // perform API call to initiate refund and interpret result

    return array(
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => 'success',
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $responseData,
        // Unique Transaction ID for the refund transaction
        'transid' => $refundTransactionId,
        // Optional fee amount for the fee value refunded
        'fee' => $feeAmount,
    );
}*/
