<?php

/**
 * すべてお任せプランに変更ページ
 * 2014/11/07 Created By Koichi Takahashi
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../common/include.ini");

_LogDelete();
//_LogBackup();
_Log("[/user/index.php] start.");
_Log("[/user/index.php] \$_POST = '" . print_r($_POST, true) . "'");
_Log("[/user/index.php] \$_GET = '" . print_r($_GET, true) . "'");
_Log("[/user/index.php] \$_SERVER = '" . print_r($_SERVER, true) . "'");
_Log("[/user/index.php] \$_SESSION = '" . print_r($_SESSION, true) . "'");

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
_Log("[/user/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../../../common/temp_html/temp_base.txt';
_Log("[/user/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '" . $tempFile . "'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
    _Log("[/user/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
    //取得できなかった場合
    _Log("[/user/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
    $html .= "HTMLテンプレートファイルを取得できません。\n";
}

$tempSidebarLoginFile = '../../../common/temp_html/temp_sidebar_login.txt';
_Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '" . $tempSidebarLoginFile . "'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
    _Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
} else {
    //取得できなかった場合
    _Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
}

$tempSidebarUserMenuFile = '../../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '" . $tempSidebarUserMenuFile . "'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
    _Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
    //取得できなかった場合
    _Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

$tempMaincontentUserMenuFile = '../../../common/temp_html/temp_maincontent_user_llc_plan.txt';
_Log("[/user/index.php] {HTMLテンプレートを読み込み} (メインコンテンツユーザーページ) HTMLテンプレートファイル = '" . $tempMaincontentUserMenuFile . "'");

$htmlMaincontentUserMenu = @file_get_contents($tempMaincontentUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlMaincontentUserMenu !== false && !_IsNull($htmlMaincontentUserMenu)) {
    _Log("[/user/index.php] {HTMLテンプレートを読み込み} (メインコンテンツユーザーページ) 【成功】");
} else {
    //取得できなかった場合
    _Log("[/user/index.php] {HTMLテンプレートを読み込み} (メインコンテンツユーザーページ) 【失敗】");
}

_Log("[/user/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end

//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_USER;

// 担当者メールアドレス
$masterEmail = MASTER_EMAIL_COMPANY_LLC;

//テスト用
if (false) {
    $masterEmail = "koochang@gmail.com";
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
$message4Cancel = "";
//エラーフラグ
$errorFlag = false;

//入力情報を格納する配列
$info = array();

//フォームモード
$formMode = XML_NAME_LLC_PLAN;

//パラメーターを取得する。
$xmlName = XML_NAME_LLC_PLAN;                //XMLファイル名を設定する。
$id = null;

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
//		//XMLファイル名
//		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
    //ターゲットID
    $id = (isset($_POST['condition']['_id_']) ? $_POST['condition']['_id_'] : null);

    //初期値を設定する。
    $undeleteOnly4def = false;

    _Log("[/user/index.php] {ログインユーザー権限処理} ユーザーID = '" . $loginInfo['usr_user_id'] . "'");
    _Log("[/user/index.php] {ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "'");

    //権限によって、表示するユーザー情報を制限する。
    if ($loginInfo['usr_auth_id'] == AUTH_NON) {
        _Log("[/user/index.php] {ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "' = '権限無し'");
        _Log("[/user/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
        _Log("[/user/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

        $id = null;
        $undeleteOnly4def = true;

        //自分のユーザー情報のみ表示する。
        //ユーザーIDを検索する。
        $id = $loginInfo['usr_user_id'];

        _Log("[/user/index.php] {ログインユーザー権限処理} →ユーザーID = '" . $id . "'");
    }

    //入力値を取得する。
    $info = $_POST;
    _Log("[/user/index.php] POST = '" . print_r($info, true) . "'");
    //バックスラッシュを取り除く。
    $info = _StripslashesForArray($info);
    _Log("[/user/index.php] POST(バックスラッシュを取り除く。) = '" . print_r($info, true) . "'");

    //「半角カタカナ」を「全角カタカナ」に変換する。→メールで半角カナが文字化けするので。
    $info = _Mb_Convert_KanaForArray($info);
    _Log("[/user/pay/index.php] POST(「半角カタカナ」を「全角カタカナ」に変換する。) = '" . print_r($info, true) . "'");

    $formMode = $info['condition']['_xml_name_'];

    if ($formMode == XML_NAME_LLC_PLAN) {
        $info['condition']['_xml_name_'] = $xmlName;
        $info['condition']['_id_'] = $id;
    }
} elseif ($_SERVER["REQUEST_METHOD"] == 'GET') {
//		//XMLファイル名
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
    //ターゲットID
    $id = (isset($_GET['id']) ? $_GET['id'] : null);

    //遷移元ページ
    $pId = (isset($_GET['p_id']) ? $_GET['p_id'] : null);

    //初期値を設定する。
    $undeleteOnly4def = false;

    _Log("[/user/index.php] {ログインユーザー権限処理} ユーザーID = '" . $loginInfo['usr_user_id'] . "'");
    _Log("[/user/index.php] {ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "'");

    //権限によって、表示するユーザー情報を制限する。
    if ($loginInfo['usr_auth_id'] == AUTH_NON) {
        _Log("[/user/index.php] {ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "' = '権限無し'");
        _Log("[/user/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
        _Log("[/user/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

        $id = null;
        $undeleteOnly4def = true;

        //自分のユーザー情報のみ表示する。
        //ユーザーIDを検索する。
        $id = $loginInfo['usr_user_id'];

        _Log("[/user/index.php] {ログインユーザー権限処理} →ユーザーID = '" . $id . "'");
    }

    $info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
    $info['condition']['_xml_name_'] = $xmlName;
    $info['condition']['_id_'] = $id;

    //遷移元ページをセッションに保存する。
    $_SESSION[SID_USER_FROM_PAGE_ID] = $pId;
}

_Log("[/user/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '" . $_SERVER["REQUEST_METHOD"] . "'");
_Log("[/user/index.php] XMLファイル名 = '" . $xmlName . "'");
_Log("[/user/index.php] フォームモード = '" . $formMode . "'");
_Log("[/user/index.php] ターゲットID = '" . $id . "'");

//XMLを読み込む。
$xmlFile = "../../../common/form_xml/" . $xmlName . ".xml";
_Log("[/user/index.php] XMLファイル = '" . $xmlFile . "'");
$xmlList = _GetXml($xmlFile);

_Log("[/user/index.php] XMLファイル配列 = '" . print_r($xmlList, true) . "'");

if ($formMode == XML_NAME_LLC_PLAN) {
    //確認ボタンが押された場合
    if ($_POST['confirm'] != "") {
        //入力値チェック
        $message .= _CheackInputAll($xmlList, $info);
        //メールアドレスの重複チェック
        if (isset($info['update']['tbl_user']['usr_e_mail']) && !_IsNull($info['update']['tbl_user']['usr_e_mail'])) {
            $condition4email = array();
            $condition4email['usr_e_mail'] = $info['update']['tbl_user']['usr_e_mail'];
            $bufList = _DB_GetList('tbl_user', $condition4email, true, null, 'usr_del_flag', 'usr_user_id');
            if (!_IsNull($bufList)) {
                //ユーザーIDが設定済みの場合、検索結果から自分自身のデータを削除する。
                if (isset($info['update']['tbl_user']['usr_user_id']) && !_IsNull($info['update']['tbl_user']['usr_user_id'])) {
                    unset($bufList[$info['update']['tbl_user']['usr_user_id']]);
                }
                if (count($bufList) > 0) {
                    $message .= "メールアドレスは既に登録済みです。\n";
                }
            }
        }
        if (_IsNull($message)) {
            //エラーが無い場合、確認画面を表示する。
            $mode = 2;
            //$message .= "※入力内容を確認して、「更新」ボタンを押してください。";
        } else {
            //エラーが有り場合
            $message = "※入力に誤りがあります。\n" . $message;
            $errorFlag = true;
        }
    } //戻るボタンが押された場合
    elseif ($_POST['back'] != "") {
    } //送信ボタンが押された場合
    elseif ($_POST['go'] != "") {
        //メール本文の共通部分を設定する。
        $body = _CreateMailAll($xmlList, $info);//※この時点では、$infoに「利用規約」の入力値は削除されている。→メールには使えない。
        _Log("[/regist/index.php] メール本文(_CreateMailAll) = '" . $body . "'");

        $companyInfo = "--------------------------------------------------------\n";
        $companyInfo .= SITE_TITLE . "\n";
        if (!_IsNull(COMPANY_NAME)) $companyInfo .= COMPANY_NAME . "\n";
        if (!_IsNull(COMPANY_ZIP)) $companyInfo .= COMPANY_ZIP . "\n";
        if (!_IsNull(COMPANY_ADDRESS)) $companyInfo .= COMPANY_ADDRESS . "\n";
        if (!_IsNull(COMPANY_TEL)) $companyInfo .= "TEL：" . COMPANY_TEL . "\n";
        if (!_IsNull(COMPANY_FAX)) $companyInfo .= "FAX：" . COMPANY_FAX . "\n";
        $companyInfo .= "E-mail：" . $clientMail . " \n";
        if (!_IsNull(COMPANY_BUSINESS_HOURS)) $companyInfo .= "営業時間：" . COMPANY_BUSINESS_HOURS . "\n";
        $companyInfo .= "--------------------------------------------------------\n\n";

        $submitInfo = "送信日時：" . date("Y年n月j日 H時i分") . "\n";
        $submitInfo .= $_SERVER["REMOTE_ADDR"] . "\n";

        //お客様用メール本文を設定する。
        $customerBody = $info['update']['tbl_user']['usr_family_name'] . " " . $info['update']['tbl_user']['usr_first_name'] . " 様\n";
        $customerBody .= "\n";
        $customerBody .= "**************************************************************************************\n";
        $customerBody .= "『" . $siteTitle . "』からのプラン変更を承りました。\n";
        $customerBody .= "確認のため、下記にお客様のご登録の内容をお知らせいたします。\n";
        $customerBody .= "**************************************************************************************\n";
        $customerBody .= "\n";
        $customerBody .= $body;
        $customerBody .= str_repeat("\n", 2);
        $customerBody .= $companyInfo;
        $customerBody .= $submitInfo;

        _Log('お客様用メール文言');
        _Log($customerBody);

        //管理者用メール本文を設定する。
        $adminBody = "**************************************************************************************\n";
        $adminBody .= "『" . $siteTitle . "』からのプラン変更依頼がありました。\n";
        $adminBody .= "**************************************************************************************\n";
        $adminBody .= "\n";
        $adminBody .= $body;
        $adminBody .= str_repeat("\n", 2);
        $adminBody .= $submitInfo;

        _Log('管理者用メール文言');
        _Log($adminBody);

        // 件名を設定する。
        $subject = "『{$siteTitle}』 プラン変更";

        mb_language("Japanese");

        $parameter = "-f " . $masterEmail;

        // メール送信
        // お客様に送信する。
        mb_send_mail($info['update']['tbl_user']['usr_e_mail'], $subject, $customerBody, "from:" . $masterEmail, $parameter);

        // 担当者に送信する。
        mb_send_mail($masterEmail, $subject, $adminBody, "from:" . $info['update']['tbl_user']['usr_e_mail']);

        // 完了画面を表示する
        $mode = 3;
    }
}

//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/index.php] POST(文字をHTMLエンティティに変換する。) = '" . print_r($info, true) . "'");

_Log("[/user/index.php] mode = '" . $mode . "'");

//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "../../..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"/img/maincontent/pt_user_llc_info_plan.jpg\" title=\"\" alt=\"すべてお任せプランに変更\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

//基本URL
$htmlMaincontentUserMenu = str_replace('{base_url}', $basePath, $htmlMaincontentUserMenu);

//お知らせ
$userNews = null;
$userNews .= "<ul>";
//$userNews .= "<li>ご利用ありがとうございます。こちらにお知らせが表示されます。</li>";
//$userNews .= "<li>現在、開発中！！！！！！！！！！！</li>";
//$userNews .= "<li>法務局・公証役場の年末年始のお休みは12月27日〜1月4日まで</li>";

//プランIDによって、お知らせを設定する。
switch ($loginInfo['usr_plan_id']) {
    case MST_PLAN_ID_NORMAL://通常プラン
        $userNews .= "<li>ご利用ありがとうございます。こちらにお知らせが表示されます。</li>";
        break;
    case MST_PLAN_ID_STANDARD://スタンダードパートナープラン
    case MST_PLAN_ID_PLATINUM://プラチナパートナープラン
//		$userNews .= "<li>【OEM制度をご利用のスタンダード・ゴールドパートナープランお客様にお知らせ】<br />通常プラン(スタンダード・ゴールドパートナープラン以外の通常のお客様用プラン)のシステム使用料が2,800円(1,000円OFF)になりました。</li>";
        $userNews .= "<li>【OEM制度をご利用のスタンダード・ゴールドパートナープランお客様にお知らせ】<br />システム使用料が2,800円(1,000円OFF)になりました。</li>";
        break;
}
$userNews .= "</ul>";
$htmlMaincontentUserMenu = str_replace('{user_news}', $userNews, $htmlMaincontentUserMenu);

//ご利用ステータス
$userStatus = _GetUserStatusHtml($loginInfo['usr_user_id']);

$htmlMaincontentUserMenu = str_replace('{user_status}', $userStatus, $htmlMaincontentUserMenu);

//株式会社一覧
$userCompanyRelation = _GetUserCompanyRelationHtml($loginInfo['usr_user_id'], MST_COMPANY_TYPE_ID_CMP);
$htmlMaincontentUserMenu = str_replace('{company_list}', $userCompanyRelation, $htmlMaincontentUserMenu);

//合同会社一覧
$userCompanyRelation = _GetUserCompanyRelationHtml($loginInfo['usr_user_id'], MST_COMPANY_TYPE_ID_LLC);
$htmlMaincontentUserMenu = str_replace('{llc_list}', $userCompanyRelation, $htmlMaincontentUserMenu);

//登録情報の設定
//更新
$userInfoUpdate = _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

$htmlMaincontentUserMenu = str_replace('{user_info_update}', $userInfoUpdate, $htmlMaincontentUserMenu);

$maincontent .= $htmlMaincontentUserMenu;

//スクリプトを設定する。
$script = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
    $script .= "<style type=\"text/css\">";
    $script .= "\n";
    $script .= "<!--";
    $script .= "\n";

    $script .= "#mc_user_news";
    $script .= "\n";
    $script .= ",#mc_user_status";
    $script .= "\n";
    $script .= ",#mc_user_menu";
    $script .= "\n";
    $script .= ",#mc_ui_update";
    $script .= "\n";
    $script .= ",#mc_ui_end_update";
    $script .= "\n";
    $script .= "{display:none;}";
    $script .= "\n";

    $script .= "-->";
    $script .= "\n";
    $script .= "</style>";
    $script .= "\n";
}

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
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_LLC, '', PAGE_TITLE_LLC, 3);
_SetBreadcrumbs(PAGE_DIR_LLC_PLAN, '', PAGE_TITLE_LLC_PLAN, 4);
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

_Log("[/user/index.php] end.");
echo $html;
