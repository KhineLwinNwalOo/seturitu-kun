<?php
/*
 * [���������Ω.JP �ġ���]
 * [��������]�Խ��оݤβ��ID���ݻ����롣
 *
 * ��������2013/02/12	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/user/set/admin.php] start.");


_Log("[/user/set/admin.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/set/admin.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/set/admin.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/set/admin.php] \$_SESSION = '".print_r($_SESSION,true)."'");

//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/set/admin.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/user/set/admin.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ".URL_LOGIN);
	exit;
} else {
	//����������������롣
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//ǧ�ڥ����å�----------------------------------------------------------------------end

//DB�򥪡��ץ󤹤롣
$cid = _DB_Open();

//������쥯����URL�����ꤵ��Ƥ�������
if (!isset($_GET['url']) || _IsNull($_GET['url'])) {
	exit;
}

//�Խ��оݤβ��ID�����ꤵ��Ƥ�������
if (isset($_GET['id']) && !_IsNull($_GET['id'])) {
	$condition = array();
	$condition['cmp_company_id'] = $_GET['id'];
	$undeleteOnly = false;
	$tblCompanyInfo = _DB_GetInfo('tbl_company', $condition, $undeleteOnly, 'cmp_del_flag');
	if (!_IsNull($tblCompanyInfo)) {
		//�Խ��оݤβ��ID�Ȥ������ꤹ�롣
		$_SESSION[SID_LOGIN_USER_COMPANY][$tblCompanyInfo['cmp_company_type_id']] = $tblCompanyInfo['cmp_company_id'];
		header("Location: ".$_GET['url']);
		exit;
	}
}

_Log("[/user/set/admin.php] end.");
?>
