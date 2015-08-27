<?php
/*
 * [���������Ω.JP �ġ���]
 * �Խ��оݤβ��ID���ݻ����롣
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
_Log("[/user/set/index.php] start.");


_Log("[/user/set/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/set/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/set/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/set/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");

//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/set/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/user/set/index.php] end.");
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

//�Խ��оݤβ��ID�����ꤵ��Ƥ�������
if (isset($_GET['id']) && !_IsNull($_GET['id'])) {
    $condition = array();
    $condition['usr_cmp_rel_company_id'] = $_GET['id'];
    $condition['usr_cmp_rel_user_id'] = $loginInfo['usr_user_id'];
    $tblRelation = _DB_GetInfo('tbl_user_company_relation', $condition);
    _Log(print_r($tblRelation, true));

    // ��ʬ���ݻ������Ҿ���ǤϤʤ����
    if (empty($tblRelation)) {
        header("Location: /user");
        exit;
    }

	$condition = array();
	$condition['cmp_company_id'] = $_GET['id'];
	$undeleteOnly = true;
	$tblCompanyInfo = _DB_GetInfo('tbl_company', $condition, $undeleteOnly, 'cmp_del_flag');
	if (!_IsNull($tblCompanyInfo)) {
		//�Խ��оݤβ��ID�Ȥ������ꤹ�롣
		$_SESSION[SID_LOGIN_USER_COMPANY][$tblCompanyInfo['cmp_company_type_id']] = $tblCompanyInfo['cmp_company_id'];
		switch ($tblCompanyInfo['cmp_company_type_id']) {
			case MST_COMPANY_TYPE_ID_CMP:
				//�������
				header("Location: ../company/");
				exit;
				break;
			case MST_COMPANY_TYPE_ID_LLC:
				//��Ʊ���
				header("Location: ../llc/");
				exit;
				break;
		}
	}
}

//��ҥ�����ID
$companyTypeId = null;

//������Ͽ�β�ҥ�����ID�����ꤵ��Ƥ�������
if (isset($_GET['type_id']) && !_IsNull($_GET['type_id'])) {
	$companyTypeId = $_GET['type_id'];
}

//�Խ��оݤβ��ID�Ȥ������ꤹ�롣
switch ($companyTypeId) {
	case MST_COMPANY_TYPE_ID_LLC:
		//��Ʊ���
		$_SESSION[SID_LOGIN_USER_COMPANY][MST_COMPANY_TYPE_ID_LLC] = null;
		header("Location: ../llc/");
		exit;
		break;
	case MST_COMPANY_TYPE_ID_CMP:
		//�������
	default:
		$_SESSION[SID_LOGIN_USER_COMPANY][MST_COMPANY_TYPE_ID_CMP] = null;
		header("Location: ../company/");
		exit;
		break;
}

_Log("[/user/set/index.php] end.");
?>
