<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/common/include.ini';
include_once 'paypalfunctions.php';

if (empty($_REQUEST['token']) || !isset($_SESSION[SID_LOGIN_USER_INFO]) || empty($_SESSION['payment_amount'])) {
    exit;
}
_Log('********** checkout START **********');

$token = $_REQUEST['token'];
$resArray = GetShippingDetails($token);

_Log('PayPal API (GetExpressCheckoutDetails) 返却値');
_Log(print_r($resArray, true));

$ack = strtoupper($resArray['ACK']);
$result = array();

if (!($ack == 'SUCCESS' || $ack == 'SUCESSWITHWARNING')) {
    //Display a user friendly Error on the page using any of the following error information returned by PayPal
    $ErrorCode = urldecode($resArray['L_ERRORCODE0']);
    $ErrorShortMsg = urldecode($resArray['L_SHORTMESSAGE0']);
    $ErrorLongMsg = urldecode($resArray['L_LONGMESSAGE0']);
    $ErrorSeverityCode = urldecode($resArray['L_SEVERITYCODE0']);

    $result[] = 'GetExpressCheckoutDetails API call failed. ';
    $result[] = 'Detailed Error Message: ' . $ErrorLongMsg;
    $result[] = 'Short Error Message: ' . $ErrorShortMsg;
    $result[] = 'Error Code: ' . $ErrorCode;
    $result[] = 'Error Severity Code: ' . $ErrorSeverityCode;
    $_SESSION['payment_error'] = $result;
    header('Location: /user/buy/paypal/complete.php');
    exit;
}

$finalPaymentAmount = $_SESSION['payment_amount'];

$resArray = ConfirmPayment($finalPaymentAmount);
$ack = strtoupper($resArray['ACK']);

if (!($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING')) {
    $ErrorCode = urldecode($resArray['L_ERRORCODE0']);
    $ErrorShortMsg = urldecode($resArray['L_SHORTMESSAGE0']);
    $ErrorLongMsg = urldecode($resArray['L_LONGMESSAGE0']);
    $ErrorSeverityCode = urldecode($resArray['L_SEVERITYCODE0']);

    $result[] = 'DoExpressCheckoutPayment API call failed. ';
    $result[] = 'Detailed Error Message: ' . $ErrorLongMsg;
    $result[] = 'Short Error Message: ' . $ErrorShortMsg;
    $result[] = 'Error Code: ' . $ErrorCode;
    $result[] = 'Error Severity Code: ' . $ErrorSeverityCode;
    $_SESSION['payment_error'] = $result;
    header('Location: /user/buy/paypal/complete.php');
    exit;
}

_DB_Open();

$paidCompanies = $_SESSION['paid_companies'];

_Log('PayPal API (DoExpressCheckoutPayment) 返却値');
_Log(print_r($resArray, true));

_Log('会社情報をセッションから取り出す START');
_Log(print_r($paidCompanies, true));
_Log('会社情報をセッションから取り出す END');

$sqlFormat = <<<EOT
UPDATE tbl_user_status
SET
    usr_sts_pay_status_id = '%s',
    usr_sts_pay_year = '%s',
    usr_sts_pay_month = '%s',
    usr_sts_pay_day = '%s',
    usr_sts_update_ip = '%s',
    usr_sts_update_date = '%s'
WHERE
    usr_sts_company_id = '%s' AND
    usr_sts_system_course_id = '%s' AND
    usr_sts_pay_status_id = '%s'
EOT;

foreach ($paidCompanies as $paidCompany) {
    $sql = sprintf($sqlFormat, MST_PAY_STATUS_ID_OK, date('Y'), date('n'), date('j'), $_SERVER['REMOTE_ADDR'], date('YmdHis'),
        $paidCompany['company_id'], $paidCompany['course_id'], MST_PAY_STATUS_ID_NON);
    _Log("sql = {$sql}");
    if (mysql_query($sql) === false) {
        _Log(mysql_error());
        break;
    }
}

unset($_SESSION['payment_amount']);
unset($_SESSION['paid_companies']);

_Log('********** checkout END **********');

header('Location: /user/buy/paypal/complete.php');
