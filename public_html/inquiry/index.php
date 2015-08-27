<?php
/*
 * [新★会社設立.JP ツール]
 * お問い合わせページ
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
_Log("[/inquiry/index.php] start.");


_Log("[/inquiry/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/inquiry/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/inquiry/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/inquiry/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_Log("[/user/index.php] ログインしていないなのでログイン画面を表示する。");
//	_Log("[/user/index.php] end.");
//	//ログイン画面を表示する。
//	header("Location: ".URL_LOGIN);
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
_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("[/inquiry/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_INQUIRY;

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


$requestMethod = $_SERVER["REQUEST_METHOD"];

////次へ、戻るボタンが押された場合→GETの処理を行う。
//if ($_POST['next'] != "" || $_POST['back'] != "") {
//	$requestMethod = 'GET';
//
//	//ステップID
//	$step = (isset($_POST['condition']['_step_'])?$_POST['condition']['_step_']:null);
//
//	//次へボタンが押された場合
//	if ($_POST['next'] != "") {
//		if (_IsNull($step)) {
//			$step = 1;
//		} else {
//			$step++;
//		}
//	}
//	//戻るボタンが押された場合
//	elseif ($_POST['back'] != "") {
//		if (_IsNull($step)) {
//			$step = 1;
//		} else {
//			$step--;
//		}
//	}
//
//
//	//ターゲットID
//	$_GET['id'] = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);
//	//ステップID
//	$_GET['step'] = $step;
//}


_Log("[/inquiry/index.php] \$_GET(詰め替え後) = '".print_r($_GET,true)."'");

//パラメーターを取得する。
$xmlName = XML_NAME_INQ;//XMLファイル名を設定する。
$id = null;
$step = null;
$stepId = null;
switch ($requestMethod) {
	case 'POST':
//		//XMLファイル名
//		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
		//ターゲットID
		$id = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);
//		//ステップID
//		$step = (isset($_POST['condition']['_step_'])?$_POST['condition']['_step_']:null);


		_Log("[/inquiry/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/inquiry/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


//		//権限によって、表示するユーザー情報を制限する。
//		switch($loginInfo['usr_auth_id']){
//			case AUTH_NON://権限無し
//
//				_Log("[/inquiry/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
//				_Log("[/inquiry/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
//				_Log("[/inquiry/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");
//
//				$id = null;
//
//				//自分のユーザー情報のみ表示する。
//				//ユーザーIDを検索する。
//				$id = $loginInfo['usr_user_id'];
//
//				_Log("[/inquiry/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");
//				break;
//		}


		//入力値を取得する。
		$info = $_POST;
		_Log("[/inquiry/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/inquiry/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

		//「半角カタカナ」を「全角カタカナ」に変換する。→メールで半角カナが文字化けするので。
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/inquiry/index.php] POST(「半角カタカナ」を「全角カタカナ」に変換する。) = '".print_r($info,true)."'");


		//XMLファイル名、ターゲットIDを上書きする。
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;

		break;
	case 'GET':
//		//XMLファイル名
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
		//ターゲットID
		$id = (isset($_GET['id'])?$_GET['id']:null);
//		//ステップID
//		$step = (isset($_GET['step'])?$_GET['step']:null);

		//遷移元ページ
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);


		//初期値を設定する。
		$undeleteOnly4def = false;



		_Log("[/inquiry/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/inquiry/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


//		//権限によって、表示するユーザー情報を制限する。
//		switch($loginInfo['usr_auth_id']){
//			case AUTH_NON://権限無し
//
//				_Log("[/inquiry/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
//				_Log("[/inquiry/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
//				_Log("[/inquiry/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");
//
//				$id = null;
//				$undeleteOnly4def = true;
//
//				//自分のユーザー情報のみ表示する。
//				//ユーザーIDを検索する。
//				$id = $loginInfo['usr_user_id'];
//
//
//				_Log("[/inquiry/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");
//
////				//遷移元ページはどこか？
////				switch ($pId) {
////					case PAGE_ID_USER://ユーザーページ
////						break;
////				}
//				break;
//		}



//		$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
//		$info['update'] = $_SESSION[SID_SEAL_INFO];
		$info['update'] = null;

		//XMLファイル名、ターゲットIDを初期値に追加する。
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;



//		//設定されている場合=更新の場合
//		if (isset($_GET['id'])) {
//			//動作モードをセッションに保存する。動作モード="他画面経由の表示"
//			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_OTHER;
//		} else {
//			//動作モードをセッションに保存する。動作モード="単独表示"
//			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_MENU;
//		}
//

		//遷移元ページをセッションに保存する。
		$_SESSION[SID_INQ_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/inquiry/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/inquiry/index.php] XMLファイル名 = '".$xmlName."'");
_Log("[/inquiry/index.php] ターゲットID = '".$id."'");


////ユーザー情報(ログイン情報)を設定する。→DB更新に使用する。
//$info['update']['tbl_user'] = $loginInfo;
//
////お支払い報告情報が未設定の場合、ユーザー情報(ログイン情報)を初期値として設定する。
//if (!isset($info['update']['tbl_pay'])) {
//	$info['update']['tbl_pay']['pay_tel1'] = $loginInfo['usr_tel1'];
//	$info['update']['tbl_pay']['pay_tel2'] = $loginInfo['usr_tel2'];
//	$info['update']['tbl_pay']['pay_tel3'] = $loginInfo['usr_tel3'];
//
//	$info['update']['tbl_pay']['pay_e_mail'] = $loginInfo['usr_e_mail'];
//	$info['update']['tbl_pay']['pay_e_mail_confirm'] = $loginInfo['usr_e_mail'];
//}

//switch ($step) {
//	case 1:
//		//法人印注文情報[印鑑]
//		//→XML形式のフォームではなく。直接書き出す。
//		$xmlName = XML_NAME_SEAL_SET;
//
//		$stepId = "sealn_set";
//		break;
//	case 2:
//		//法人印注文情報[印影]
//		$xmlName = XML_NAME_SEAL_IMPRINT;
//
//		$stepId = "sealn_imprint";
//		break;
//	case 3:
//		//法人印注文情報[会社名・お届け先]
//		$xmlName = XML_NAME_SEAL_NAME;
//
//		$stepId = "sealn_name";
//		break;
//	case 4:
//		//法人印注文情報[入力内容確認]
//		$xmlName = XML_NAME_SEAL_ALL;
//
//		$stepId = "sealn_confirm";
//		break;
//	default:
//		//法人印注文情報[印鑑]
//		//→XML形式のフォームではなく。直接書き出す。
//		$xmlName = XML_NAME_SEAL_SET;
//
//		$stepId = "sealn_set";
//
//		$step = 1;
//		break;
//}
//$info['condition']['_step_'] = $step;
//
//_Log("[/inquiry/index.php] ステップID = '".$step."'");
//_Log("[/inquiry/index.php] XMLファイル名(ステップID) = '".$xmlName."'");
//
////戻るボタンが押された場合→すぐ遷移するので、XMLは読み込まない。
//if ($_POST['back'] != "") $xmlName = null;


$xmlList = null;
if (!_IsNull($xmlName)) {


	$otherList = null;

	//XMLを読み込む。
	$xmlFile = "../common/form_xml/".$xmlName.".xml";
	_Log("[/inquiry/index.php] XMLファイル = '".$xmlFile."'");
	$xmlList = _GetXml($xmlFile, $otherList);

	_Log("[/inquiry/index.php] XMLファイル配列 = '".print_r($xmlList,true)."'");

//	switch ($xmlName) {
//		case XML_NAME_SEAL_ALL:
//			//法人印注文情報[入力内容確認]
//
//			//全てのXMLを読み込む。
//
//			//法人印注文情報[印鑑](確認画面用)
//			$bufXmlFile = "../common/form_xml/".XML_NAME_SEAL_SET_4_CONFIRM.".xml";
//			_Log("[/inquiry/index.php] XMLファイル = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal'] = $bufXmlList['tbl_seal'];
//
//			//法人印注文情報[印影]
//			$bufXmlFile = "../common/form_xml/".XML_NAME_SEAL_IMPRINT.".xml";
//			_Log("[/inquiry/index.php] XMLファイル = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal_imprint'] = $bufXmlList['tbl_seal_imprint'];
//
//			///法人印注文情報[会社名・お届け先]
//			$bufXmlFile = "../common/form_xml/".XML_NAME_SEAL_NAME.".xml";
//			_Log("[/inquiry/index.php] XMLファイル = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal_name'] = $bufXmlList['tbl_seal_name'];
//			$xmlList['tbl_seal_deliver'] = $bufXmlList['tbl_seal_deliver'];
//
//
//			_Log("[/inquiry/index.php] XMLファイル配列(全XMLマージ後) = '".print_r($xmlList,true)."'");
//			_Log("[/inquiry/index.php] 法人印注文情報(全XMLマージ後) = '".print_r($info,true)."'");
//
//			$mode = 2;
//
//			break;
//	}
}

//送信ボタンが押された場合
if ($_POST['confirm'] != "") {
	//入力値チェック
	$message .= _CheackInputAll($xmlList, $info);

	if (_IsNull($message)) {
		//エラーが無い場合、確認画面を表示する。
		$mode = 2;

		//$message .= "※入力内容を確認して、「更新」ボタンを押してください。";
	} else {
		//エラーが有り場合
		$message = "※入力に誤りがあります。\n".$message;
		$errorFlag = true;
	}
}
//戻るボタンが押された場合
elseif ($_POST['back'] != "") {
}
//送信ボタン、次へボタンが押された場合
elseif ($_POST['go'] != "" || $_POST['next'] != "") {
//	//入力値チェック
//	$message .= _CheackInputAll($xmlList, $info);
//
//	switch ($xmlName) {
//		case XML_NAME_SEAL_SET:
//			//法人印注文情報[印鑑]
//			$message .= _CheackInput4SealSet($xmlList, $info);
//			break;
//		case XML_NAME_SEAL_NAME:
//			//法人印注文情報[会社名・お届け先]
//			$message .= _CheackInput4SealName($xmlList, $info);
//			break;
//		case XML_NAME_SEAL_ALL:
//			//法人印注文情報[入力内容確認]
////			$message .= _CheackInput4SealSet($xmlList, $info);
//			$message .= _CheackInput4SealName($xmlList, $info);
//			break;
//		default:
//			break;
//	}
//
//	//セッションに保存する。
//	switch ($xmlName) {
//		case XML_NAME_SEAL_SET:
//			//法人印注文情報[印鑑]
//			$_SESSION[SID_SEAL_INFO]['tbl_seal'] = $info['update']['tbl_seal'];
//			break;
//		case XML_NAME_SEAL_IMPRINT:
//			//法人印注文情報[印影]
//			$_SESSION[SID_SEAL_INFO]['tbl_seal_imprint'] = $info['update']['tbl_seal_imprint'];
//			break;
//		case XML_NAME_SEAL_NAME:
//			//法人印注文情報[会社名・お届け先]
//			$_SESSION[SID_SEAL_INFO]['tbl_seal_name'] = $info['update']['tbl_seal_name'];
//			$_SESSION[SID_SEAL_INFO]['tbl_seal_deliver'] = $info['update']['tbl_seal_deliver'];
//			break;
//	}

	if (_IsNull($message)) {
		//エラーが無い場合、登録する。

		//更新・登録をする。(※$infoは最新情報に更新される。)
//		$res = _UpdateInfo($info);
		$res = true;
		if ($res === false) {
			//エラーが有り場合
			$message = "登録に失敗しました。";
			$errorFlag = true;
		} else {

//			//メッセージを設定する。
//			$message .= "保存しました。";

			//送信ボタンが押された場合
			if ($_POST['go'] != "") {
				//メール本文の共通部分を設定する。
				$body = null;
				$body .= _CreateMailAll($xmlList, $info);//※この時点では、$infoに「利用規約」の入力値は削除されている。→メールには使えない。

				_Log("[/inquiry/index.php] メール本文(_CreateMailAll) = '".$body."'");

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

				$body .= "お問い合わせ日時：".date("Y年n月j日 H時i分")."\n";
				$body .= $_SERVER["REMOTE_ADDR"]."\n";

				//管理者用メール本文を設定する。
				$adminBody = "";
				//$adminBody .= $siteTitle." \n";
				//$adminBody .= "\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "『".$siteTitle."』にお問い合わせが入りました。\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "\n";
				$adminB