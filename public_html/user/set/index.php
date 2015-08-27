<?php
/*
 * [新★会社設立.JP ツール]
 * 編集対象の会社IDを保持する。
 *
 * 更新履歴：2013/02/12	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
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

//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/set/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/user/set/index.php] end.");
	//ログイン画面を表示する。
	header("Location: ".URL_LOGIN);
	exit;
} else {
	//ログイン情報を取得する。
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//認証チェック----------------------------------------------------------------------end


//DBをオープンする。
$cid = _DB_Open();

//編集対象の会社IDが設定されてきたか？
if (isset($_GET['id']) && !_IsNull($_GET['id'])) {
    $condition = array();
    $condition['usr_cmp_rel_company_id'] = $_GET['id'];
    $condition['usr_cmp_rel_user_id'] = $loginInfo['usr_user_id'];
    $tblRelation = _DB_GetInfo('tbl_user_company_relation', $condition);
    _Log(print_r($tblRelation, true));

    // 自分の保持する会社情報ではない場合
    if (empty($tblRelation)) {
        header("Location: /user");
        exit;
    }

	$condition = array();
	$condition['cmp_company_id'] = $_GET['id'];
	$undeleteOnly = true;
	$tblCompanyInfo = _DB_GetInfo('tbl_company', $condition, $undeleteOnly, 'cmp_del_flag');
	if (!_IsNull($tblCompanyInfo)) {
		//編集対象の会社IDとして設定する。
		$_SESSION[SID_LOGIN_USER_COMPANY][$tblCompanyInfo['cmp_company_type_id']] = $tblCompanyInfo['cmp_company_id'];
		switch ($tblCompanyInfo['cmp_company_type_id']) {
			case MST_COMPANY_TYPE_ID_CMP:
				//株式会社
				header("Location: ../company/");
				exit;
				break;
			case MST_COMPANY_TYPE_ID_LLC:
				//合同会社
				header("Location: ../llc/");
				exit;
				break;
		}
	}
}

//会社タイプID
$companyTypeId = null;

//新規登録の会社タイプIDが設定されてきたか？
if (isset($_GET['type_id']) && !_IsNull($_GET['type_id'])) {
	$companyTypeId = $_GET['type_id'];
}

//編集対象の会社IDとして設定する。
switch ($companyTypeId) {
	case MST_COMPANY_TYPE_ID_LLC:
		//合同会社
		$_SESSION[SID_LOGIN_USER_COMPANY][MST_COMPANY_TYPE_ID_LLC] = null;
		header("Location: ../llc/");
		exit;
		break;
	case MST_COMPANY_TYPE_ID_CMP:
		//株式会社
	default:
		$_SESSION[SID_LOGIN_USER_COMPANY][MST_COMPANY_TYPE_ID_CMP] = null;
		header("Location: ../company/");
		exit;
		break;
}

_Log("[/user/set/index.php] end.");
?>
