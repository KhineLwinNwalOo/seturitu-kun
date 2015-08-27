<?php
/*
 * [新★会社設立.JP ツール]
 * リンクカテゴリー毎ページ
 *
 * 更新履歴：2012/01/11	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/link/category_NN.php] start.");


_Log("[/link/category_NN.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/link/category_NN.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/link/category_NN.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/link/category_NN.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_Log("[/link/category_NN.php] ログインしていないなのでログイン画面を表示する。");
//	_Log("[/link/category_NN.php] end.");
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
_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}


$tempMaincontentLinkFile = '../common/temp_html/temp_maincontent_link_category_05.txt';//[※1]リンクのカテゴリー追加の場合は、ここと[※2]を修正する。
_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (メインコンテンツリンク) HTMLテンプレートファイル = '".$tempMaincontentLinkFile."'");

$htmlMaincontentLink = @file_get_contents($tempMaincontentLinkFile);
//"HTML"が存在する場合、表示する。
if ($htmlMaincontentLink !== false && !_IsNull($htmlMaincontentLink)) {
	_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (メインコンテンツリンク) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} (メインコンテンツリンク) 【失敗】");
}




_Log("[/link/category_NN.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = "役立つ法律関係";//[※2]



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
_Log("[/link/category_NN.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/link/category_NN.php] mode = '".$mode."'");






//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../img/maincontent/pt_links.jpg\" title=\"\" alt=\"リンク\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

//基本URL
$htmlMaincontentLink = str_replace('{base_url}', $basePath, $htmlMaincontentLink);

$maincontent .= $htmlMaincontentLink;

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
_SetBreadcrumbs(PAGE_DIR_LINK, '', PAGE_TITLE_LINK, 2);
_SetBreadcrumbs('', '', $pageTitle, 3);

//パンくずリストを取得する。
$breadcrumbs = _GetBreadcrumbs();

//WOOROMフッター管理
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=".$_SERVER['SERVER_NAME']."&php_self=".$_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
	$wooromFooter = null;
}



//テンプレートを編集する。(必要箇所を置換する。)
//タイトル
if (!_IsNull($title)) $title = "[リンク - ".$title."] ";
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


_Log("[/link/category_NN.php] end.");
echo $html;

?>
