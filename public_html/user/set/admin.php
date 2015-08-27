<?php
/*
 * [新★会社設立.JP ツール]
 * [管理者用]編集対象の会社IDを保持する。
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
_Log("[/user/set/admin.php] start.");


_Log("[/user/set/admin.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/set/admin.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/set/admin.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/set/admin.php] \$_SESSION = '".print_r($_SESSION,true)."'");

//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/set/admin.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/user/set/admin.php] end.");
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

//リダイレクト先URLが設定されてきたか？
if (!isset($_GET['url']) || _IsNull($_GET['url'])) {
	exit;
}

//編集対象の会社IDが設定されてきたか？
if (isset($_GET['id']) && !_IsNull($_GET['id'])) {
	$condition = array();
	$condition['cmp_company_id'] = $_GET['id'];
	$undeleteOnly = false;
	$tblCompanyInfo = _DB_GetInfo('tbl_company', $condition, $undeleteOnly, 'cmp_del_flag');
	if (!_IsNull($tblCompanyInfo)) {
		//編集対象の会社IDとして設定する。
		$_SESSION[SID_LOGIN_USER_COMPANY][$tblCompanyInfo['cmp_company_type_id']] = $tblCompanyInfo['cmp_company_id'];
		header("Location: ".$_GET['url']);
		exit;
	}
}

_Log("[/user/set/admin.php] end.");
?>
