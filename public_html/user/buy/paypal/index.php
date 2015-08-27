<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    exit;
}

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/common/include.ini';

if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
    header('Location: ' . URL_LOGIN);
    exit;
} else {
    $loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
}

$info = $_POST;
$info['update']['tbl_user'] = $loginInfo;
_Log('********** POST START **********');
_Log(print_r($_POST, true));
_Log('********** POST END **********');

_DB_Open();
_UpdateInfo($info);

include_once 'paypalfunctions.php';

// ==================================
// PayPal Express Checkout Module
// ==================================

//'------------------------------------
//' The paymentAmount is the total value of 
//' the shopping cart, that was set 
//' earlier in a session variable 
//' by the shopping cart page
//'------------------------------------
$paymentAmount = $_POST['Payment_Amount'];
$_SESSION['payment_amount'] = $paymentAmount;

$paidCompanies = array();
foreach ($_POST['update']['tbl_buy']['buy_system_course_id'] as $companyId) {
    $arr = explode('_', $companyId);
    $paidCompanies[] = array(
        'company_id' => $arr[0],
        'course_id' => $arr[1],
    );
}

_Log('会社情報をセッションに保存する START');
_Log(print_r($paidCompanies, true));
_Log('会社情報をセッションに保存する END');

$_SESSION['paid_companies'] = $paidCompanies;

//'------------------------------------
//' The currencyCodeType and paymentType 
//' are set to the selections made on the Integration Assistant 
//'------------------------------------
$currencyCodeType = 'JPY';
$paymentType = 'Sale';

//'------------------------------------
//' The returnURL is the location where buyers return to when a
//' payment has been succesfully authorized.
//'
//' This is set to the value entered on the Integration Assistant 
//'------------------------------------
$returnURL = 'http://www.seturitu-kun.com/user/buy/paypal/checkout.php';

//'------------------------------------
//' The cancelURL is the location buyers are sent to when they hit the
//' cancel button during authorization of payment during the PayPal flow
//'
//' This is set to the value entered on the Integration Assistant 
//'------------------------------------
$cancelURL = 'http://www.seturitu-kun.com/user/buy/';

//'------------------------------------
//' Calls the SetExpressCheckout API call
//'
//' The CallShortcutExpressCheckout function is defined in the file PayPalFunctions.php,
//' it is included at the top of this file.
//'-------------------------------------------------
$resArray = CallShortcutExpressCheckout($paymentAmount, $currencyCodeType, $paymentType, $returnURL, $cancelURL);
$ack = strtoupper($resArray['ACK']);
if ($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING') {
    RedirectToPayPal($resArray['TOKEN']);
} else {
    //Display a user friendly Error on the page using any of the following error information returned by PayPal
    $ErrorCode = urldecode($resArray['L_ERRORCODE0']);
    $ErrorShortMsg = urldecode($resArray['L_SHORTMESSAGE0']);
    $ErrorLongMsg = urldecode($resArray['L_LONGMESSAGE0']);
    $ErrorSeverityCode = urldecode($resArray['L_SEVERITYCODE0']);

    echo 'SetExpressCheckout API call failed. ';
    echo 'Detailed Error Message: ' . $ErrorLongMsg;
    echo 'Short Error Message: ' . $ErrorShortMsg;
    echo 'Error Code: ' . $ErrorCode;
    echo 'Error Severity Code: ' . $ErrorSeverityCode;
}
?>
