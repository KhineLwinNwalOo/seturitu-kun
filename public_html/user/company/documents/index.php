<?php
/*
 * [新★会社設立.JP ツール]
 * 登記完了後に必要な書類ページ
 *
 * 更新履歴：2008/12/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/user/company/documents/index.php] start.");


_Log("[/user/company/documents/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/company/documents/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/company/documents/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/company/documents/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/company/documents/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/user/company/documents/index.php] end.");
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



//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../../../common/temp_html/temp_base.txt';
_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


//$tempSidebarLoginFile = '../../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"が存在する場合、表示する。
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
//} else {
//	//取得できなかった場合
//	_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
//}

$tempSidebarUserMenuFile = '../../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}


$tempMaincontentCompanyDocsFile = '../../../common/temp_html/temp_maincontent_company_docs.txt';
_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (メインコンテンツ株式会社設立_登記完了後に必要な書類) HTMLテンプレートファイル = '".$tempMaincontentCompanyDocsFile."'");

$htmlMaincontentCompanyDocs = @file_get_contents($tempMaincontentCompanyDocsFile);
//"HTML"が存在する場合、表示する。
if ($htmlMaincontentCompanyDocs !== false && !_IsNull($htmlMaincontentCompanyDocs)) {
	_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (メインコンテンツ株式会社設立_登記完了後に必要な書類) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} (メインコンテンツ株式会社設立_登記完了後に必要な書類) 【失敗】");
}




_Log("[/user/company/documents/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_COMPANY_DOCUMENTS;



//タブインデックス
$tabindex = 0;

//DBをオープンする。
$cid = _DB_Open();

//動作モード{1:入力/2:確認/3:完了/4:エラー}
$mode = 1;

//全て表示するか？hidden項目も表示するか？{true:全て表示する。/false:XML設定、権限による表示有無に従う。}
$allShowFlag = false;

//メッセージ
$message = "";
//エラーフラグ
$errorFlag = false;


//入力情報を格納する配列
$info = array();





//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/company/documents/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/user/company/documents/index.php] mode = '".$mode."'");






//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "../../..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../../img/maincontent/pt_user_company_documents.jpg\" title=\"\" alt=\"登記完了後に必要な書類\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

//基本URL
$htmlMaincontentCompanyDocs = str_replace('{base_url}', $basePath, $htmlMaincontentCompanyDocs);

$maincontent .= $htmlMaincontentCompanyDocs;

//$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//スクリプトを設定する。
$script = null;

//サイドメニューを設定する。
$sidebar = null;

////基本URL
//$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);
//
//$sidebar .= $htmlSidebarLogin;

//基本URL
$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
//ログインユーザー名
$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
//現在の入力状況
switch ($_GET['p_id']) {
	case PAGE_ID_LLC:
		//合同会社設立LLCメニュー
		$htmlSidebarUserMenu = str_replace('{company_info}', _GetCompanyInfoHtml($loginInfo, MST_COMPANY_TYPE_ID_LLC), $htmlSidebarUserMenu);
		break;
	default:
		$htmlSidebarUserMenu = str_replace('{company_info}', _GetCompanyInfoHtml($loginInfo), $htmlSidebarUserMenu);
		break;
}

$sidebar .= $htmlSidebarUserMenu;


//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);

switch ($_GET['p_id']) {
	case PAGE_ID_LLC:
		//合同会社設立LLCメニュー
		_SetBreadcrumbs(PAGE_DIR_LLC, '', PAGE_TITLE_LLC, 3);
		break;
	default:
		_SetBreadcrumbs(PAGE_DIR_COMPANY, '', PAGE_TITLE_COMPANY, 3);
		break;
}

_SetBreadcrumbs(PAGE_DIR_COMPANY_DOCUMENTS, '', PAGE_TITLE_COMPANY_DOCUMENTS, 4);
//パンくずリストを取得する。
$breadcrumbs = _GetBreadcrumbs();

//WOOROMフッター管理
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=".$_SERVER['SERVER_NAME']."&php_self=".$_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
	$wooromFooter = null;
}



//テンプレートを編集する。(必要箇所を置換する。)
//タイトル
if (!_IsNull($title)) $title = "[".$title."] ";
$title = $siteTitle." ".$title;
$html = str_replace('{title}', $title, $html);
//コンテンツ
$html = str_replace('{maincontent}', $maincontent, $html);
//サイドメニュー
$html = str_replace('{sidebar}', $sidebar, $html);
//スクリプト
$html = str_replace('{script}', $script, $html);
//基本URL
$html = str_replace('{base_url}', $basePath, $html);
//パンくずリスト
$html = str_replace('{breadcrumbs}', $breadcrumbs, $html);
//WOOROMフッター管理
$html = str_replace('{woorom_footer}', $wooromFooter, $html);


_Log("[/user/company/documents/index.php] end.");
echo $html;

?>
