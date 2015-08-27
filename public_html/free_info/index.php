<?php
/*
 * [新★会社設立.JP ツール]
 * プライバシーポリシーページ
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
_Log("[/privacy/index.php] start.");


_Log("[/privacy/index.php] \$_POST = '" . print_r($_POST, true) . "'");
_Log("[/privacy/index.php] \$_GET = '" . print_r($_GET, true) . "'");
_Log("[/privacy/index.php] \$_SERVER = '" . print_r($_SERVER, true) . "'");
_Log("[/privacy/index.php] \$_SESSION = '" . print_r($_SESSION, true) . "'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;
//ログイン情報を取得する。
$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
//認証チェック----------------------------------------------------------------------end


//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("[/privacy/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '" . $tempFile . "'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
    _Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
    //取得できなかった場合
    _Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
    $html .= "HTMLテンプレートファイルを取得できません。\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '" . $tempSidebarLoginFile . "'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
    _Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
} else {
    //取得できなかった場合
    _Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '" . $tempSidebarUserMenuFile . "'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
    _Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
    //取得できなかった場合
    _Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}


$tempMaincontentPrivacyFile = '../common/temp_html/temp_maincontent_free_info.txt';
_Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (メインコンテンツプライバシーポリシー) HTMLテンプレートファイル = '" . $tempMaincontentPrivacyFile . "'");

$htmlMaincontentPrivacy = @file_get_contents($tempMaincontentPrivacyFile);
//"HTML"が存在する場合、表示する。
if ($htmlMaincontentPrivacy !== false && !_IsNull($htmlMaincontentPrivacy)) {
    _Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (メインコンテンツプライバシーポリシー) 【成功】");
} else {
    //取得できなかった場合
    _Log("[/privacy/index.php] {HTMLテンプレートを読み込み} (メインコンテンツプライバシーポリシー) 【失敗】");
}


_Log("[/privacy/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_FREE_INFO;


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
_Log("[/privacy/index.php] POST(文字をHTMLエンティティに変換する。) = '" . print_r($info, true) . "'");

_Log("[/privacy/index.php] mode = '" . $mode . "'");


//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<br/><br/>";
$maincontent .= "\n";

//基本URL
$htmlMaincontentPrivacy = str_replace('{base_url}', $basePath, $htmlMaincontentPrivacy);

$maincontent .= $htmlMaincontentPrivacy;

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
_SetBreadcrumbs(PAGE_DIR_FREE_INFO, '', PAGE_TITLE_FREE_INFO, 2);
//パンくずリストを取得する。
$breadcrumbs = _GetBreadcrumbs();

//WOOROMフッター管理
$wooromFooter = getWooromFooter();

//テンプレートを編集する。(必要箇所を置換する。)
//タイトル
if (!_IsNull($title)) $title = "[" . $title . "] ";
$title = $siteTitle . " " . $title;
$html = str_replace('{title}', $title, $html);
//メタ情報
$html = str_replace('{keywords}', PAGE_KEYWORDS_PRIVACY, $html);
$html = str_replace('{description}', PAGE_DESCRIPTION_PRIVACY, $html);
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


_Log("[/privacy/index.php] end.");
echo $html;
