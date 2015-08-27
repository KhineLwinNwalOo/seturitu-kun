<?php
/*
 * [新★会社設立.JP ツール]
 * 株式会社設立情報登録ページ
 *
 * 更新履歴：2008/12/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

$commonPath = $_SERVER['DOCUMENT_ROOT'] . '/common/';

include_once($commonPath . "include.ini");

_Log("start.");
_Log("\$_POST = '" . print_r($_POST, true) . "'");
_Log("\$_GET = '" . print_r($_GET, true) . "'");
_Log("\$_SERVER = '" . print_r($_SERVER, true) . "'");
_Log("\$_SESSION = '" . print_r($_SESSION, true) . "'");

//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
    _Log("[/user/index.php] ログインしていないなのでログイン画面を表示する。");
    _Log("[/user/index.php] end.");
    //ログイン画面を表示する。
    header("Location: " . URL_LOGIN);
    exit;
} else {
    //ログイン情報を取得する。
    $loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

    //本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
    _CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//認証チェック----------------------------------------------------------------------end

//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("{HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = $commonPath . 'temp_html/temp_base.txt';
_Log("{HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '" . $tempFile . "'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
    _Log("{HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
    //取得できなかった場合
    _Log("{HTMLテンプレートを読み込み} (基本) 【失敗】");
    $html .= "HTMLテンプレートファイルを取得できません。\n";
}

$tempSidebarUserMenuFile = $commonPath . 'temp_html/temp_sidebar_user_menu.txt';
_Log("{HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '" . $tempSidebarUserMenuFile . "'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
    _Log("{HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
    //取得できなかった場合
    _Log("{HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("{HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end

_DB_Open();

//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_BUY;

//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "../../..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"/img/maincontent/pt_buy.jpg\" title=\"\" alt=\"ご利用料金のお支払い\">";
$maincontent .= "</h2>";
$maincontent .= PHP_EOL;

$successFormat = <<<EOT
 <div class="message ">
    %s&nbsp;様<br/>
    <br/>
    この度は、『新★会社設立くんで法人設立』をご利用いただきありがとうございます。<br/>
    お支払いが完了しましたので、全ての機能のご利用が可能です。<br/>
    <br/>
    ご不明な点がございましたら、お手数ですが&nbsp;<a href="mailto:info@seturitu-kun.com">info@seturitu-kun.com</a>&nbsp;までメールでお問い合わせください。
</div>
EOT;

$errorFormat = <<<EOT
 <div class="message ">
    %s&nbsp;様<br/>
    <br/>
    この度は、『新★会社設立くんで法人設立』をご利用いただきありがとうございます。<br/>
    お支払い処理でエラーが発生しました。<br/>
    エラー詳細：
    <br/>
    %s
    <br/>
    <br/>
    お手数ですが、上記エラー詳細をご確認の上、&nbsp;<a href="mailto:info@seturitu-kun.com">info@seturitu-kun.com</a>&nbsp;までメールでお問い合わせください。
</div>
EOT;

$message = '';

if (!isset($_SESSION['payment_error'])) {
    $message = sprintf($successFormat, $loginInfo['usr_family_name'] . '&nbsp;' . $loginInfo['usr_first_name']);
} else {
    $detail = implode('<br/>', $_SESSION['payment_error']);
    $message = sprintf($errorFormat, $loginInfo['usr_family_name'] . '&nbsp;' . $loginInfo['usr_first_name'], $detail);
    unset($_SESSION['payment_error']);
}

$maincontent .= $message;

//スクリプトを設定する。
$script = null;


//サイドメニューを設定する。
$sidebar = null;

//基本URL
$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
//ログインユーザー名
$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
//現在の入力状況
$htmlSidebarUserMenu = str_replace('{company_info}', _GetCompanyInfoHtml($loginInfo), $htmlSidebarUserMenu);

$sidebar .= $htmlSidebarUserMenu;

//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_BUY, '', PAGE_TITLE_BUY, 3);

//パンくずリストを取得する。
$breadcrumbs = _GetBreadcrumbs();

//WOOROMフッター管理
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=" . $_SERVER['SERVER_NAME'] . "&php_self=" . $_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
    $wooromFooter = null;
}

//テンプレートを編集する。(必要箇所を置換する。)
//タイトル
if (!_IsNull($title)) $title = "[" . $title . "] ";
$title = $siteTitle . " " . $title;
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

_Log("end.");
echo $html;
