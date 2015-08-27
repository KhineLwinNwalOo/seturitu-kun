<?php
/*
 * [新★会社設立.JP ツール]
 * サイトマップページ
 *
 * 更新履歴：2010/11/20	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/about/index.php] start.");


_Log("[/about/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/about/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/about/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/about/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_Log("[/about/index.php] ログインしていないなのでログイン画面を表示する。");
//	_Log("[/about/index.php] end.");
//	//ログイン画面を表示する。
//	header("Location: ".URL_BASE);
//	exit;
} else {
	//ログイン情報を取得する。
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
//
//	//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
//	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//認証チェック----------------------------------------------------------------------end



//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("[/about/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/about/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/about/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/about/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/about/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/about/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/about/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/about/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/about/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/about/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}


$tempMaincontentAboutFile = '../common/temp_html/temp_maincontent_about.txt';
_Log("[/about/index.php] {HTMLテンプレートを読み込み} (メインコンテンツサイトマップ) HTMLテンプレートファイル = '".$tempMaincontentAboutFile."'");

$htmlMaincontentAbout = @file_get_contents($tempMaincontentAboutFile);
//"HTML"が存在する場合、表示する。
if ($htmlMaincontentAbout !== false && !_IsNull($htmlMaincontentAbout)) {
	_Log("[/about/index.php] {HTMLテンプレートを読み込み} (メインコンテンツサイトマップ) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/about/index.php] {HTMLテンプレートを読み込み} (メインコンテンツサイトマップ) 【失敗】");
}




_Log("[/about/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_ABOUT;



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
_Log("[/about/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/about/index.php] mode = '".$mode."'");






//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../img/maincontent/pt_about.jpg\" title=\"\" alt=\"サイトマップ\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

//基本URL
$htmlMaincontentAbout = str_replace('{base_url}', $basePath, $htmlMaincontentAbout);

$maincontent .= $htmlMaincontentAbout;

//$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//スクリプトを設定する。
$script = null;

//サイドメニューを設定する。
$sidebar = null;

//ログインしているか？
if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
	//基本URL
	$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
	//ログインユーザー名
	$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
	//現在の入力状況
	$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);

	$sidebar .= $htmlSidebarUserMenu;
} else {
	//基本URL
	$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);

	$sidebar .= $htmlSidebarLogin;
}



//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_ABOUT, '', PAGE_TITLE_ABOUT, 2);
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
//メタ情報
$html = str_replace ('{keywords}', PAGE_KEYWORDS_ABOUT, $html);
$html = str_replace ('{description}', PAGE_DESCRIPTION_ABOUT, $html);
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


_Log("[/about/index.php] end.");
echo $html;

?>
