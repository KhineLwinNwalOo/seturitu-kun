<?php
/*
 * [新★会社設立.JP ツール]
 * ユーザーパスワード確認ページ
 *
 * 更新履歴：2008/12/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/login_remind/index.php] start.");


_Log("[/login_remind/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/login_remind/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/login_remind/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/login_remind/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_Log("[/login_remind/index.php] ログインしていないなのでログイン画面を表示する。");
//	_Log("[/login_remind/index.php] end.");
//	//ログイン画面を表示する。
//	header("Location: ".URL_LOGIN);
//	exit;

	//ダミーログイン情報を設定する。→新規登録用。
	$loginInfo['usr_auth_id'] = AUTH_NON;
} else {
	//ログイン情報を取得する。
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//認証チェック----------------------------------------------------------------------end



//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("[/login_remind/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/login_remind/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/login_remind/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/login_remind/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/login_remind/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/login_remind/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/login_remind/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
}
_Log("[/login_remind/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_LOGIN_REMIND;

//クライアント様メールアドレス
$clientMail = COMPANY_E_MAIL;
//マスター用メールアドレス
$masterMailList = $_COMPANY_MASTER_MAIL_LIST;

//テスト用
if (false) {
//if (true) {
	//クライアント様メールアドレス
	$clientMail = "ishikawa@woorom.com";
	//マスター用メールアドレス
	//「,」でくぎって送信先を追加して下さい。
	$masterMailList = array("ishikawa@woorom.com", "ishikawa@woorom.com");
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


//パラメーターを取得する。
$xmlName = XML_NAME_LOGIN_REMIND;//XMLファイル名を設定する。
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		//入力値を取得する。
		$info = $_POST;
		_Log("[/login_remind/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/login_remind/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

		break;
	case 'GET':
		//XMLファイル名、ターゲットIDを初期値に追加する。
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;

		break;
}

_Log("[/login_remind/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/login_remind/index.php] XMLファイル名 = '".$xmlName."'");
_Log("[/login_remind/index.php] ターゲットID = '".$id."'");


//XMLを読み込む。
$xmlFile = "../common/form_xml/".$xmlName.".xml";
_Log("[/login_remind/index.php] XMLファイル = '".$xmlFile."'");
$xmlList = _GetXml($xmlFile);

_Log("[/login_remind/index.php] XMLファイル配列 = '".print_r($xmlList,true)."'");


//送信ボタンが押された場合
if ($_POST['confirm'] != "") {
	//入力値チェック
	$message .= _CheackInputAll($xmlList, $info);

	$userInfo = null;

	if (_IsNull($message)) {
		//エラーが無い場合、認証チェックを表示する。
		$condition = array();
		$condition = $info['update']['tbl_user'];
		$userList = _DB_GetList('tbl_user', $condition, true, null, 'usr_del_flag');
		if (!_IsNull($userList)) {
			if (count($userList) == 1) {
				//メールアドレスで検索すると1件のみ見つかるはず！
				$userInfo = $userList[0];
			} elseif (count($userList) > 1) {
				//複数見つかった場合、データエラー!!!
				_Log("[/login_remind/index.php] {ERROR} ユーザーテーブルに重複データ有!!! ⇒ tbl_use.usr_e_mail='".$info['update']['tbl_user']['usr_e_mail']."'", 1);
			}
		}
		if (_IsNull($userInfo)) {
			$message .= "メールアドレスが異なります。\n";
		}
	}

	if (_IsNull($message)) {
		//エラーが無い場合、メール送信する。

		//メール本文の共通部分を設定する。
		$body = null;
		$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
		$body .= "ユーザー情報\n";
		$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
//		$body .= "ユーザーID：";
//		$body .= $userInfo['usr_user_id'];
//		$body .= "\n";
		$body .= "お名前：";
		$body .= $userInfo['usr_family_name'];
		$body .= " ";
		$body .= $userInfo['usr_first_name'];
		$body .= "\n";
		$body .= "メールアドレス：";
		$body .= $userInfo['usr_e_mail'];
		$body .= "\n";
		$body .= "パスワード：";
		$body .= $userInfo['usr_pass'];
		$body .= "\n";

		$body .= "\n";
		$body .= "\n";
		$body .= "\n";
		$body .= "\n";

		$body .= "--------------------------------------------------------\n";
		$body .= $siteTitle."\n";
		if (!_IsNull(COMPANY_NAME)) $body .= COMPANY_NAME."\n";
		if (!_IsNull(COMPANY_ZIP)) $body .= COMPANY_ZIP."\n";
		if (!_IsNull(COMPANY_ADDRESS)) $body .= COMPANY_ADDRESS."\n";
		if (!_IsNull(COMPANY_TEL)) $body .= "TEL：".COMPANY_TEL."\n";
		if (!_IsNull(COMPANY_FAX)) $body .= "FAX：".COMPANY_FAX."\n";
		$body .= "E-mail：".$clientMail." \n";
		if (!_IsNull(COMPANY_BUSINESS_HOURS)) $body .= "営業時間：".COMPANY_BUSINESS_HOURS."\n";
		$body .= "--------------------------------------------------------\n\n";

		$body .= "登録日時：".date("Y年n月j日 H時i分")."\n";
		$body .= $_SERVER["REMOTE_ADDR"]."\n";

		//管理者用メール本文を設定する。
		$adminBody = "";
		//$adminBody .= $siteTitle." \n";
		//$adminBody .= "\n";
		$adminBody .= "**************************************************************************************\n";
		$adminBody .= "『".$siteTitle."』にパスワード確認がありました。\n";
		$adminBody .= "**************************************************************************************\n";
		$adminBody .= "\n";
		$adminBody .= $body;

		//お客様用メール本文を設定する。
		$customerBody = "";
		$customerBody .= $userInfo['usr_family_name']." ".$userInfo['usr_first_name']." 様\n";
		$customerBody .= "\n";
		$customerBody .= "**************************************************************************************\n";
		$customerBody .= "『".$siteTitle."』からパスワード確認のお知らせです。\n";
		$customerBody .= "下記にお客様のご登録の内容をお知らせいたします。\n";
		$customerBody .= "パスワードをご確認の上、再ログインしてください。\n";
		$customerBody .= "**************************************************************************************\n";
		$customerBody .= "\n";
		$customerBody .= $body;


		//管理者用タイトルを設定する。
		$adminTitle = "[".$siteTitle."] パスワード確認 (".$userInfo['usr_family_name']." ".$userInfo['usr_first_name']." 様)";
		//お客様用タイトルを設定する。
		$customerTitle = "[".$siteTitle."] パスワード確認のお知らせ";

		mb_language("Japanese");
		
		$parameter = "-f ".$clientMail;

		//メール送信
		//お客様に送信する。
		$rcd = mb_send_mail($userInfo['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

		//クライアントに送信する。
		$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$userInfo['usr_e_mail']);

		//マスターに送信する。
		foreach($masterMailList as $masterMail){
			$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$userInfo['usr_e_mail']);
		}


		//メッセージを設定する。
		$message .= "『".$siteTitle."』から「パスワード確認のお知らせメール」を送信いたしました。";
		$message .= "\n";
		$message .= "パスワードをご確認の上、再ログインしてください。";
		$message .= "\n";


		//完了画面を表示する。
		$mode = 3;

	} else {
		//エラーが有り場合
		$message = "※入力に誤りがあります。\n".$message;
		$errorFlag = true;
	}
}
//戻るボタンが押された場合
elseif ($_POST['back'] != "") {
}
//送信ボタンが押された場合
elseif ($_POST['go'] != "") {
}



//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/login_remind/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/login_remind/index.php] mode = '".$mode."'");






//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= $pageTitle;
$maincontent .= "</h2>";
$maincontent .= "\n";

$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//スクリプトを設定する。
$script = null;

//サイドメニューを設定する。
$sidebar = null;

//基本URL
$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);

$sidebar .= $htmlSidebarLogin;


//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_LOGIN_REMIND, '', PAGE_TITLE_LOGIN_REMIND, 2);
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
$html = str_replace ('{keywords}', PAGE_KEYWORDS_HOME, $html);
$html = str_replace ('{description}', PAGE_DESCRIPTION_HOME, $html);
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


_Log("[/login_remind/index.php] end.");
echo $html;

?>
