<?php

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");

_LogDelete();
_Log("[/user/option_service/index.php] start.");
_Log("[/user/option_service/index.php] \$_POST = '" . print_r($_POST, true) . "'");
_Log("[/user/option_service/index.php] \$_GET = '" . print_r($_GET, true) . "'");
_Log("[/user/option_service/index.php] \$_SERVER = '" . print_r($_SERVER, true) . "'");
_Log("[/user/option_service/index.php] \$_SESSION = '" . print_r($_SESSION, true) . "'");

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
_Log("[/user/option_service/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../../common/temp_html/temp_base.txt';
_Log("[/user/option_service/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '" . $tempFile . "'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
    _Log("[/user/option_service/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
    //取得できなかった場合
    _Log("[/user/option_service/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
    $html .= "HTMLテンプレートファイルを取得できません。\n";
}

$tempSidebarUserMenuFile = '../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/option_service/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '" . $tempSidebarUserMenuFile . "'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
    _Log("[/user/option_service/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
    //取得できなかった場合
    _Log("[/user/option_service/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("[/user/option_service/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end

//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_OPTION_SERVICE;

//クライアント様メールアドレス
$clientMail = COMPANY_E_MAIL;
//マスター用メールアドレス
$masterMailList = $_COMPANY_MASTER_MAIL_LIST;

//テスト用
if (false) {
//if (true) {
    //クライアント様メールアドレス
    $clientMail = "takahashi@woorom.com";
    //マスター用メールアドレス
    //「,」でくぎって送信先を追加して下さい。
    $masterMailList = array("takahashi@woorom.com", "takahashi@woorom.com");
}

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

$requestMethod = $_SERVER["REQUEST_METHOD"];

_Log("[/user/option_service/index.php] \$_GET(詰め替え後) = '" . print_r($_GET, true) . "'");

//パラメーターを取得する。
$xmlName = XML_NAME_OPTION_SERVICE;//XMLファイル名を設定する。
$id = null;
$step = null;
$stepId = null;
switch ($requestMethod) {
    case 'POST':
        //ターゲットID
        $id = (isset($_POST['condition']['_id_']) ? $_POST['condition']['_id_'] : null);

        _Log("[/user/option_service/index.php] {ログインユーザー権限処理} ユーザーID = '" . $loginInfo['usr_user_id'] . "'");
        _Log("[/user/option_service/index.php] {ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "'");

        //権限によって、表示するユーザー情報を制限する。
        switch ($loginInfo['usr_auth_id']) {
            case AUTH_NON://権限無し

                _Log("[/user/option_service/index.php] {ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "' = '権限無し'");
                _Log("[/user/option_service/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
                _Log("[/user/option_service/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

                $id = null;

                //自分のユーザー情報のみ表示する。
                //ユーザーIDを検索する。
                $id = $loginInfo['usr_user_id'];

                _Log("[/user/option_service/index.php] {ログインユーザー権限処理} →ユーザーID = '" . $id . "'");
                break;
        }


        //入力値を取得する。
        $info = $_POST;
        _Log("[/user/option_service/index.php] POST = '" . print_r($info, true) . "'");
        //バックスラッシュを取り除く。
        $info = _StripslashesForArray($info);
        _Log("[/user/option_service/index.php] POST(バックスラッシュを取り除く。) = '" . print_r($info, true) . "'");

        //「半角カタカナ」を「全角カタカナ」に変換する。→メールで半角カナが文字化けするので。
        $info = _Mb_Convert_KanaForArray($info);
        _Log("[/user/option_service/index.php] POST(「半角カタカナ」を「全角カタカナ」に変換する。) = '" . print_r($info, true) . "'");

        include_once '../../common/constant.php';

        $adminTitle = '[' . SITE_TITLE . '] オプションサービスのご利用ありがとうございます';
        $adminBody = <<<EOT
**************************************************************************************
この度は、『%s』にてオプションサービスのご連絡いただき誠にありがとうございます。
確認のため、下記にお客様の選択された内容をお知らせいたします。
オプションサービスに関しての詳細は、担当者からご連絡させていただく場合がございます。
**************************************************************************************

%s

--------------------------------------------------------
株式会社WOOROM.
〒106-0032
東京都港区六本木5-16-50 六本木デュープレックスM's407
TEL：03-3586-1523
FAX：03-3586-1521
E-mail：info@seturitu-kun.com
営業時間：10:00~19:00
--------------------------------------------------------
EOT;

        $data = array();
        foreach ($info['option_service'] as $i => $optionService) {
            $str = "【{$optionServices[$i]['name']}】" . PHP_EOL . $optionServices[$i]['options'][$optionService['option']];
            if (!empty($optionServices[$i]['checkbox_options']) && !empty($optionService['checkbox_options'])) {
                $options = array();
                foreach ($optionService['checkbox_options'] as $checkboxOption) {
                    $options[] = $optionServices[$i]['checkbox_options'][$checkboxOption];
                }
                if (!empty($options)) {
                    $str .= PHP_EOL . '( ' . implode(', ', $options) . ' )';
                }
            }
            $data[] = $str;
        }

        $adminBody = sprintf($adminBody, SITE_TITLE, implode(str_repeat(PHP_EOL, 2), $data));

        mb_language("Japanese");
        $param = "-f " . $clientMail;

        mb_send_mail($loginInfo['usr_e_mail'], $adminTitle, $adminBody, "from:{$clientMail}", $param);
        mb_send_mail($clientMail, $adminTitle, $adminBody, "from:{$clientMail}", $param);

        break;
    case 'GET':
//		//XMLファイル名
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
        //ターゲットID
        $id = (isset($_GET['id']) ? $_GET['id'] : null);
//		//ステップID
//		$step = (isset($_GET['step'])?$_GET['step']:null);

        //遷移元ページ
        $pId = (isset($_GET['p_id']) ? $_GET['p_id'] : null);


        //初期値を設定する。
        $undeleteOnly4def = false;


        _Log("[/user/option_service/index.php] {ログインユーザー権限処理} ユーザーID = '" . $loginInfo['usr_user_id'] . "'");
        _Log("[/user/option_service/index.php] {ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "'");


        //権限によって、表示するユーザー情報を制限する。
        switch ($loginInfo['usr_auth_id']) {
            case AUTH_NON://権限無し

                _Log("[/user/option_service/index.php] {ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "' = '権限無し'");
                _Log("[/user/option_service/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
                _Log("[/user/option_service/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

                $id = null;
                $undeleteOnly4def = true;

                //自分のユーザー情報のみ表示する。
                //ユーザーIDを検索する。
                $id = $loginInfo['usr_user_id'];


                _Log("[/user/option_service/index.php] {ログインユーザー権限処理} →ユーザーID = '" . $id . "'");

                break;
        }

        $info['update'] = null;

        //XMLファイル名、ターゲットIDを初期値に追加する。
        $info['condition']['_xml_name_'] = $xmlName;
        $info['condition']['_id_'] = $id;

        //遷移元ページをセッションに保存する。
        $_SESSION[SID_PAY_FROM_PAGE_ID] = $pId;

        break;
}

_Log("[/user/option_service/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '" . $_SERVER["REQUEST_METHOD"] . "'");
_Log("[/user/option_service/index.php] XMLファイル名 = '" . $xmlName . "'");
_Log("[/user/option_service/index.php] ターゲットID = '" . $id . "'");


//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/option_service/index.php] POST(文字をHTMLエンティティに変換する。) = '" . print_r($info, true) . "'");

_Log("[/user/option_service/index.php] mode = '" . $mode . "'");

//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "../..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../img/maincontent/pt_option.jpg\" title=\"\" alt=\"オプションサービス\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

$includeContents = _get_include_contents('./_form.php');
$maincontent .= $includeContents;

//スクリプトを設定する。
$script = null;

$addStyle = null;

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
$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);

$sidebar .= $htmlSidebarUserMenu;


//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_OPTION_SERVICE, '', PAGE_TITLE_OPTION_SERVICE, 3);
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
//メタ情報
$html = str_replace('{keywords}', PAGE_KEYWORDS_HOME, $html);
$html = str_replace('{description}', PAGE_DESCRIPTION_HOME, $html);
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


_Log("[/user/option_service/index.php] end.");
echo $html;
