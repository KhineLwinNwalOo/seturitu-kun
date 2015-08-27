<?php
/*
 * [新★会社設立.JP ツール]
 * 定款認証ページ
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
_Log("[/user/company/article/index.php] start.");


_Log("[/user/company/article/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/company/article/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/company/article/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/company/article/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/user/index.php] end.");
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
_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../../../common/temp_html/temp_base.txt';
_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


//$tempSidebarLoginFile = '../../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"が存在する場合、表示する。
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
//} else {
//	//取得できなかった場合
//	_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
//}

$tempSidebarUserMenuFile = '../../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_COMPANY_ARTICLE;

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
	$masterMailList = null;
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


_Log("[/user/company/article/index.php] \$_GET(詰め替え後) = '".print_r($_GET,true)."'");

//パラメーターを取得する。
$xmlName = XML_NAME_ARTICLE;//XMLファイル名を設定する。
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


		//初期値を設定する。
		$undeleteOnly4def = false;

		_Log("[/user/company/article/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/article/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/user/company/article/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/user/company/article/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
				_Log("[/user/company/article/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

				$id = null;
				$undeleteOnly4def = true;

				//自分のユーザー情報のみ表示する。
				//ユーザーIDを検索する。
				$id = $loginInfo['usr_user_id'];

				_Log("[/user/company/article/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");
				break;
		}


		//入力値を取得する。
		$info = $_POST;
		_Log("[/user/company/article/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/user/company/article/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

		//「半角カタカナ」を「全角カタカナ」に変換する。→メールで半角カナが文字化けするので。
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/company/article/index.php] POST(「半角カタカナ」を「全角カタカナ」に変換する。) = '".print_r($info,true)."'");


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

		_Log("[/user/company/article/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/article/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");

		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/user/company/article/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/user/company/article/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
				_Log("[/user/company/article/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

				$id = null;
				$undeleteOnly4def = true;

				//自分のユーザー情報のみ表示する。
				//ユーザーIDを検索する。
				$id = $loginInfo['usr_user_id'];


				_Log("[/user/company/article/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");

//				//遷移元ページはどこか？
//				switch ($pId) {
//					case PAGE_ID_USER://ユーザーページ
//						break;
//				}
				break;
		}


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

//		//遷移元ページをセッションに保存する。
//		$_SESSION[SID_PAY_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/user/company/article/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/user/company/article/index.php] XMLファイル名 = '".$xmlName."'");
_Log("[/user/company/article/index.php] ターゲットID = '".$id."'");


//ユーザーIDに関連する会社ID、会社情報を検索する。
$companyInfo = null;
$companyId = _GetRelationCompanyId($id, $undeleteOnly4def);
if (!_IsNull($companyId)) {
	$companyInfo = _GetCompanyInfo($companyId, $undeleteOnly4def);
}


//ユーザー情報(ログイン情報)を設定する。→DB更新に使用する。
$info['update']['tbl_user'] = $loginInfo;
//会社情報を設定する。→DB更新に使用する。
if (!_IsNull($companyInfo)) {
	$info['update']['tbl_company'] = $companyInfo['tbl_company'];
}


//定款認証情報が未設定の場合、ユーザー情報(ログイン情報)を初期値として設定する。
if (!isset($info['update']['tbl_article_deliver'])) {
	$info['update']['tbl_article_deliver']['art_dlv_tel1'] = $loginInfo['usr_tel1'];
	$info['update']['tbl_article_deliver']['art_dlv_tel2'] = $loginInfo['usr_tel2'];
	$info['update']['tbl_article_deliver']['art_dlv_tel3'] = $loginInfo['usr_tel3'];

	$info['update']['tbl_article_deliver']['art_dlv_e_mail'] = $loginInfo['usr_e_mail'];
	$info['update']['tbl_article_deliver']['art_dlv_e_mail_confirm'] = $loginInfo['usr_e_mail'];

	$info['update']['tbl_article_deliver']['art_dlv_family_name'] = $loginInfo['usr_family_name'];
	$info['update']['tbl_article_deliver']['art_dlv_first_name'] = $loginInfo['usr_first_name'];

	$info['update']['tbl_article_deliver']['art_dlv_zip1'] = $loginInfo['usr_zip1'];
	$info['update']['tbl_article_deliver']['art_dlv_zip2'] = $loginInfo['usr_zip2'];

	$info['update']['tbl_article_deliver']['art_dlv_pref_id'] = $loginInfo['usr_pref_id'];
	$info['update']['tbl_article_deliver']['art_dlv_address1'] = $loginInfo['usr_address1'];
	$info['update']['tbl_article_deliver']['art_dlv_address2'] = $loginInfo['usr_address2'];
}
if (!isset($info['update']['tbl_article_charge'])) {
	$info['update']['tbl_article_charge']['art_chg_family_name'] = $loginInfo['usr_family_name'];
	$info['update']['tbl_article_charge']['art_chg_first_name'] = $loginInfo['usr_first_name'];

	$info['update']['tbl_article_charge']['art_chg_pref_id'] = $loginInfo['usr_pref_id'];
	$info['update']['tbl_article_charge']['art_chg_address1'] = $loginInfo['usr_address1'];
	$info['update']['tbl_article_charge']['art_chg_address2'] = $loginInfo['usr_address2'];
}
if (!isset($info['update']['tbl_article_notary'])) {
	$info['update']['tbl_article_notary']['art_ntr_pref_id'] = $loginInfo['usr_pref_id'];
}

//定款PDF情報が未設定の場合、会社情報を初期値として設定する。
if (!isset($info['update']['tbl_article_pdf'])) {
	//定款作成日(年)
	$info['update']['tbl_article_pdf']['create_year'] = date('Y');
	//定款作成日(月)
	$info['update']['tbl_article_pdf']['create_month'] = date('n');
	//定款作成日(日)
	$info['update']['tbl_article_pdf']['create_day'] = date('j');

	if (!_IsNull($companyInfo)) {
		//定款作成日(年)
		if (isset($companyInfo['tbl_company']['cmp_article_create_year']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_year'])) {
			$info['update']['tbl_article_pdf']['create_year'] = $companyInfo['tbl_company']['cmp_article_create_year'];
		}
		//定款作成日(月)
		if (isset($companyInfo['tbl_company']['cmp_article_create_month']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_month'])) {
			$info['update']['tbl_article_pdf']['create_month'] = $companyInfo['tbl_company']['cmp_article_create_month'];
		}
		//定款作成日(日)
		if (isset($companyInfo['tbl_company']['cmp_article_create_day']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_day'])) {
			$info['update']['tbl_article_pdf']['create_day'] = $companyInfo['tbl_company']['cmp_article_create_day'];
		}
	}
}

//定款作成日情報が未設定の場合、会社情報を初期値として設定する。
if (!isset($info['update']['tbl_article_date'])) {
	//定款作成日(年)
	$info['update']['tbl_article_date']['art_dat_create_year'] = date('Y');
	//定款作成日(月)
	$info['update']['tbl_article_date']['art_dat_create_month'] = date('n');
	//定款作成日(日)
	$info['update']['tbl_article_date']['art_dat_create_day'] = date('j');

	if (!_IsNull($companyInfo)) {
		//定款作成日(年)
		if (isset($companyInfo['tbl_company']['cmp_article_create_year']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_year'])) {
			$info['update']['tbl_article_date']['art_dat_create_year'] = $companyInfo['tbl_company']['cmp_article_create_year'];
		}
		//定款作成日(月)
		if (isset($companyInfo['tbl_company']['cmp_article_create_month']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_month'])) {
			$info['update']['tbl_article_date']['art_dat_create_month'] = $companyInfo['tbl_company']['cmp_article_create_month'];
		}
		//定款作成日(日)
		if (isset($companyInfo['tbl_company']['cmp_article_create_day']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_day'])) {
			$info['update']['tbl_article_date']['art_dat_create_day'] = $companyInfo['tbl_company']['cmp_article_create_day'];
		}
	}
}

//定款認証コース選択情報が未設定の場合、会社情報を初期値として設定する。
if (!isset($info['update']['tbl_article_course'])) {
	if (!_IsNull($companyInfo)) {
		//定款認証コースID
		if (isset($companyInfo['tbl_company']['cmp_article_course_id']) && !_IsNull($companyInfo['tbl_company']['cmp_article_course_id'])) {
			$info['update']['tbl_article_course']['art_crs_article_course_id'] = $companyInfo['tbl_company']['cmp_article_course_id'];
		}
	}
}


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
//_Log("[/user/company/article/index.php] ステップID = '".$step."'");
//_Log("[/user/company/article/index.php] XMLファイル名(ステップID) = '".$xmlName."'");
//
////戻るボタンが押された場合→すぐ遷移するので、XMLは読み込まない。
//if ($_POST['back'] != "") $xmlName = null;


//定款認証コースマスタ
$condition4Mst = null;
$undeleteOnly4Mst = true;
$order4Mst = "lpad(show_order,10,'0'),id";
$mstArticleCourseList = _DB_GetList('mst_article_course', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
if (!_IsNull($mstArticleCourseList)) {
	foreach ($mstArticleCourseList as $mKey => $mInfo) {
		$name = null;
		$name .= $mInfo['name'];

		$nameTag = null;
		$nameTag = $name;
		if (!_IsNull($mInfo['comment'])) {
			$name .= " ";
			$name .= $mInfo['comment'];

//			$nameTag .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$nameTag .= " ";
			$nameTag .= "<span class=\"input_comment\">";
			$nameTag .= $mInfo['comment'];
			$nameTag .= "</span>";
		}
		$mInfo['name_comment'] = $name;
		$mInfo['name_comment_tag'] = $nameTag;
		$mstArticleCourseList[$mKey] = $mInfo;
	}
}

//システムコースマスタ
$condition4Mst = null;
$undeleteOnly4Mst = true;
$order4Mst = "lpad(show_order,10,'0'),id";
$mstSystemCourseList = _DB_GetList('mst_system_course', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
if (!_IsNull($mstSystemCourseList)) {
	//システムコースマスタの未削除・削除により、定款認証コースマスタを編集する。
	if (!_IsNull($mstArticleCourseList)) {
		//[株式会社] 電子定款スタンダードコース=削除→[電子認証]スタンダードコースを削除する。
		if (!isset($mstSystemCourseList[MST_SYSTEM_COURSE_ID_CMP_STANDARD])) unset($mstArticleCourseList[MST_ARTICLE_COURSE_ID_STANDARD]);
		//[株式会社] 電子定款お任せコース=削除→[電子認証]お任せコースを削除する。
		if (!isset($mstSystemCourseList[MST_SYSTEM_COURSE_ID_CMP_ENTRUST])) unset($mstArticleCourseList[MST_ARTICLE_COURSE_ID_ENTRUST]);
	}
}


$xmlList = null;
if (!_IsNull($xmlName)) {


	$otherList = null;
	$otherList = array(
		'mst_article_course' => $mstArticleCourseList
	);

	//XMLを読み込む。
	$xmlFile = "../../../common/form_xml/".$xmlName.".xml";
	_Log("[/user/company/article/index.php] XMLファイル = '".$xmlFile."'");
	$xmlList = _GetXml($xmlFile, $otherList);

	_Log("[/user/company/article/index.php] XMLファイル配列 = '".print_r($xmlList,true)."'");

//	switch ($xmlName) {
//		case XML_NAME_SEAL_ALL:
//			//法人印注文情報[入力内容確認]
//
//			//全てのXMLを読み込む。
//
//			//法人印注文情報[印鑑](確認画面用)
//			$bufXmlFile = "../../../common/form_xml/".XML_NAME_SEAL_SET_4_CONFIRM.".xml";
//			_Log("[/user/company/article/index.php] XMLファイル = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal'] = $bufXmlList['tbl_seal'];
//
//			//法人印注文情報[印影]
//			$bufXmlFile = "../../../common/form_xml/".XML_NAME_SEAL_IMPRINT.".xml";
//			_Log("[/user/company/article/index.php] XMLファイル = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal_imprint'] = $bufXmlList['tbl_seal_imprint'];
//
//			///法人印注文情報[会社名・お届け先]
//			$bufXmlFile = "../../../common/form_xml/".XML_NAME_SEAL_NAME.".xml";
//			_Log("[/user/company/article/index.php] XMLファイル = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal_name'] = $bufXmlList['tbl_seal_name'];
//			$xmlList['tbl_seal_deliver'] = $bufXmlList['tbl_seal_deliver'];
//
//
//			_Log("[/user/company/article/index.php] XMLファイル配列(全XMLマージ後) = '".print_r($xmlList,true)."'");
//			_Log("[/user/company/article/index.php] 法人印注文情報(全XMLマージ後) = '".print_r($info,true)."'");
//
//			$mode = 2;
//
//			break;
//	}
}

//送信ボタンが押された場合
if ($_POST['confirm'] != "") {

	//選択されているサービスに対応する情報のみにする。
	$bufXmlList = _GetSelectedServiceOnly($xmlList, $info);

	//入力値チェック
	$message .= _CheackInputAll($bufXmlList, $info);

	//許認可
	//"希望する"場合
	if (isset($info['update']['tbl_article_option']['art_opt_option_permission_id']) && $info['update']['tbl_article_option']['art_opt_option_permission_id'] == MST_OPTION_ID_YES) {
		$inFlag = false;
		for ($i = 1; $i <= 9; $i++) {
			if (isset($info['update']['tbl_article_option']['art_opt_permission_'.$i.'_id'])) {
				$inFlag = true;
			}
		}
		if (isset($info['update']['tbl_article_option']['art_opt_permission_note']) && !_IsNull($info['update']['tbl_article_option']['art_opt_permission_note'])) {
			$inFlag = true;
		}
		if (!$inFlag) {
			$message .= "許認可を希望する場合は、希望する許認可を選択してください。\n";
		}
	}
	//保険
	//"希望する"場合
	if (isset($info['update']['tbl_article_option']['art_opt_option_insurance_id']) && $info['update']['tbl_article_option']['art_opt_option_insurance_id'] == MST_OPTION_ID_YES) {
		if (!isset($info['update']['tbl_article_option']['art_opt_insurance_id'])) {
			$message .= "保険を希望する場合は、希望する保険を選択してください。\n";
		}
	}
	//就業規則
	//"希望する"場合
	if (isset($info['update']['tbl_article_option']['art_opt_option_regulations_id']) && $info['update']['tbl_article_option']['art_opt_option_regulations_id'] == MST_OPTION_ID_YES) {
		if (!isset($info['update']['tbl_article_option']['art_opt_regulations_id'])) {
			$message .= "就業規則を希望する場合は、希望するプランを選択してください。\n";
		}
	}
	//ホームページ制作
	//"希望する"場合
	if (isset($info['update']['tbl_article_option']['art_opt_option_micro_web_id']) && $info['update']['tbl_article_option']['art_opt_option_micro_web_id'] == MST_OPTION_ID_YES) {
		if (!isset($info['update']['tbl_article_option']['art_opt_micro_web_id'])) {
			$message .= "ホームページ制作を希望する場合は、希望するプランを選択してください。\n";
		}
	}

	if (_IsNull($message)) {
		//エラーが無い場合、確認画面を表示する。
		$mode = 2;

		//選択されたサービスに対応する項目のみ表示する。
		$xmlList = $bufXmlList;

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
		$res = _UpdateInfo($info);
//		$res = true;
		if ($res === false) {
			//エラーが有り場合
			$message = "登録に失敗しました。";
			$errorFlag = true;
		} else {

//			//メッセージを設定する。
//			$message .= "保存しました。";

			//送信ボタンが押された場合
			if ($_POST['go'] != "") {

				//選択されているサービスに対応する情報のみにする。
				$bufXmlList = _GetSelectedServiceOnly($xmlList, $info);

				//メール本文の共通部分を設定する。
				$body = null;

				$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
				$body .= "ユーザー情報\n";
				$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
//				$body .= "ユーザーID：";
//				$body .= $info['update']['tbl_user']['usr_user_id'];
//				$body .= "\n";
				$body .= "お名前：";
				$body .= $info['update']['tbl_user']['usr_family_name'];
				$body .= " ";
				$body .= $info['update']['tbl_user']['usr_first_name'];
				$body .= "\n";
				$body .= "メールアドレス：";
				$body .= $info['update']['tbl_user']['usr_e_mail'];
				$body .= "\n";
				$body .= "\n";

				$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
				$body .= "会社情報\n";
				$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
				$body .= "商号(会社名)：";
				$body .= $companyInfo['tbl_company']['cmp_company_name'];
				$body .= "\n";
				$body .= "設立日：";

				$cmpFoundYear = null;
				if (!_IsNull($companyInfo['tbl_company']['cmp_found_year'])) {
					$jpY = _ConvertAD2Jp($companyInfo['tbl_company']['cmp_found_year']);
					$cmpFoundYear = $companyInfo['tbl_company']['cmp_found_year']."(".$jpY.")";
				}
				$body .= $cmpFoundYear." 年 ".$companyInfo['tbl_company']['cmp_found_month']." 月 ".$companyInfo['tbl_company']['cmp_found_day']." 日";
				$body .= "\n";
				$body .= "\n";

				$body .= _CreateMailAll($bufXmlList, $info);//※この時点では、$infoに「利用規約」の入力値は削除されている。→メールには使えない。

				_Log("[/user/company/article/index.php] メール本文(_CreateMailAll) = '".$body."'");

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

				$body .= "定款認証依頼日時：".date("Y年n月j日 H時i分")."\n";
				$body .= $_SERVER["REMOTE_ADDR"]."\n";

				//管理者用メール本文を設定する。
				$adminBody = "";
				//$adminBody .= $siteTitle." \n";
				//$adminBody .= "\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "『".$siteTitle."』に定款認証の依頼が入りました。\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "\n";
				$adminBody .= $body;

				//お客様用メール本文を設定する。
				$customerBody = "";
				$customerBody .= $info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." 様\n";
				$customerBody .= "\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "この度は、『".$siteTitle."』に定款認証のご依頼をしていただきありがとうございました。\n";
				$customerBody .= "確認のため、下記にお客様のご登録の内容をお知らせいたします。\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "\n";
				$customerBody .= $body;


				//管理者用タイトルを設定する。
				$adminTitle = "[".$siteTitle."] 定款認証依頼 (".$info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." 様 / ".$companyInfo['tbl_company']['cmp_company_name'].")";
				//お客様用タイトルを設定する。
				$customerTitle = "[".$siteTitle."] 定款認証依頼ありがとうございました (".$companyInfo['tbl_company']['cmp_company_name'].")";

				mb_language("Japanese");
				
				$parameter = "-f ".$clientMail;

				//メール送信
				//お客様に送信する。
				$rcd = mb_send_mail($info['update']['tbl_article_deliver']['art_dlv_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

				//クライアントに送信する。
				$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_article_deliver']['art_dlv_e_mail']);

				//マスターに送信する。
				foreach($masterMailList as $masterMail){
					$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_article_deliver']['art_dlv_e_mail']);
				}


				//メッセージを設定する。
				$message .= $info['update']['tbl_user']['usr_family_name']."&nbsp;".$info['update']['tbl_user']['usr_first_name'];
				$message .= "&nbsp;様";
				$message .= "\n";
				$message .= "\n";
				$message .= "この度は、『".$siteTitle."』に定款認証のご依頼をしていただきありがとうございました。";
				$message .= "\n";
				$message .= "お客様のメールアドレス宛てにご登録内容の「確認メール」が自動送信されました。";
				$message .= "\n";
				$message .= "定款認証書類のお届けまで2〜3日お待ちください。";
				$message .= "\n";
				$message .= "\n";
//				$message .= "※「確認メール」が届かない場合は、メールアドレスがご登録ミスの可能性がありますので、";
//				$message .= "\n";
//				$message .= "&nbsp;&nbsp;&nbsp;お手数ですが&nbsp;";
				$message .= "メールが届かない場合は、お手数ですが&nbsp;";
				$message .= "<a href=\"mailto:".$clientMail."\">".$clientMail."</a>";
				$message .= "&nbsp;までメールでお問い合わせください。";

				//完了画面を表示する。
				$mode = 3;
			}


	//		//動作モード="他画面経由の表示"の場合、戻るリンクを表示する。
	//		if ($_SESSION[SID_INFO_MODE] == MST_MODE_FROM_OTHER) {
	//
	//			switch ($xmlName) {
	//				case XML_NAME_ITEM:
	//					//商品情報
	//					$message .= "<a href=\"../../../item/?back\" title=\"商品一覧に戻る\">[商品一覧に戻る]</a>\n";
	//					break;
	//				case XML_NAME_BOTTLE_IMAGE:
	//					//ボトル画像情報
	//					$message .= "";
	//					break;
	//				case XML_NAME_DESIGN_IMAGE:
	//					//彫刻パターン画像情報
	//					$message .= "";
	//					break;
	//				case XML_NAME_CHARACTER_J_IMAGE:
	//					//彫刻文字(和字)画像情報
	//					$message .= "";
	//					break;
	//				case XML_NAME_CHARACTER_E_IMAGE:
	//					//彫刻文字(英字)画像情報
	//					$message .= "";
	//					break;
	//				case XML_NAME_INQ:
	//					//問合せ情報
	//					switch ($_SESSION[SID_INFO_FROM_PAGE_ID]) {
	//						case PAGE_ID_INQ_PRICE:
	//							$message .= "<a href=\"../../../inquiry_price/?back\" title=\"請求額一覧に戻る\">[請求額一覧に戻る]</a>\n";
	//							break;
	//						default:
	//							$message .= "<a href=\"../../../inquiry/?back\" title=\"問合せ一覧に戻る\">[問合せ一覧に戻る]</a>\n";
	//							break;
	//					}
	//					break;
	//			}
	//
	//		}

//			//完了画面を表示する。
//			$mode = 3;
		}

	} else {
		//エラーが有り場合
		$message = "※入力に誤りがあります。\n".$message;
		$errorFlag = true;
	}

}


//$addHref = null;
//switch($loginInfo['usr_auth_id']){
//	case AUTH_NON://権限無し
//		break;
//	default:
//		if (!_IsNull($id)) {
//			$addHref = "&amp;id=".$id;
//		}
//		break;
//}
//
////次へボタンが押された場合
//if ($_POST['next'] != "") {
//	if (!$errorFlag) {
//		//次のページを表示する。
//		$step++;
//		header("Location: ./?step=".$step.$addHref);
//		exit;
//	}
//}
////戻るボタンが押された場合
//elseif ($_POST['back'] != "") {
//	//前のページを表示する。
//	$step--;
//	header("Location: ./?step=".$step.$addHref);
//	exit;
//}


//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/company/article/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/user/company/article/index.php] mode = '".$mode."'");




//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "../../..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../../img/maincontent/pt_user_company_article.jpg\" title=\"\" alt=\"定款認証　設定\">";
$maincontent .= "</h2>";
$maincontent .= "\n";



//入金チェック
//{treu:エラー有り/false:エラー無し}
$userStatusSystemErrorFlag = false;			//システム利用料金
$userStatusStandardErrorFlag = false;		//スタンダードコース
$userStatusEntrustErrorFlag = false;		//お任せコース
//[株式会社] 株式会社設立 (システム利用料金)
if (!_CheckUserStatus($id, $companyId, MST_SYSTEM_COURSE_ID_CMP)) {
	$userStatusSystemErrorFlag = true;

	$maincontent .= "<div id=\"system_course_system\" class=\"message payMessage\">";
	$maincontent .= "\n";
	$maincontent .= "※申し訳ございません。書類の作成(印刷)は、ご利用料金の決済後にご利用が可能となります。";
	$maincontent .= "<br />";
	$maincontent .= "<br />";
	$maincontent .= "<a href=\"../../buy/\">&gt;&gt;お支払いはこちら</a>";
	$maincontent .= "\n";
	$maincontent .= "</div>";
	$maincontent .= "\n";
}
//[株式会社] 電子定款スタンダードコース
if (isset($mstArticleCourseList[MST_ARTICLE_COURSE_ID_STANDARD])) {
	if (!_CheckUserStatus($id, $companyId, MST_SYSTEM_COURSE_ID_CMP_STANDARD)) {
		$userStatusStandardErrorFlag = true;

		$maincontent .= "<div id=\"system_course_standard\" class=\"message payMessage\" style=\"display:none;\">";
		$maincontent .= "\n";
		$maincontent .= "※申し訳ございません。「 ".$mstArticleCourseList[MST_ARTICLE_COURSE_ID_STANDARD]['name']." 」は、ご利用料金の決済後にご利用が可能となります。";
		$maincontent .= "<br />";
		$maincontent .= "<br />";
		$maincontent .= "<a href=\"../../buy/\">お支払いはこちら</a>";
		$maincontent .= "\n";
		$maincontent .= "</div>";
		$maincontent .= "\n";
	}
}
//[株式会社] 電子定款お任せコース
if (isset($mstArticleCourseList[MST_ARTICLE_COURSE_ID_ENTRUST])) {
	if (!_CheckUserStatus($id, $companyId, MST_SYSTEM_COURSE_ID_CMP_ENTRUST)) {
		$userStatusEntrustErrorFlag = true;

		$maincontent .= "<div id=\"system_course_entrust\" class=\"message payMessage\" style=\"display:none;\">";
		$maincontent .= "\n";
		$maincontent .= "※申し訳ございません。「 ".$mstArticleCourseList[MST_ARTICLE_COURSE_ID_ENTRUST]['name']." 」は、ご利用料金の決済後にご利用が可能となります。";
		$maincontent .= "<br />";
		$maincontent .= "<br />";
		$maincontent .= "<a href=\"../../buy/\">お支払いはこちら</a>";
		$maincontent .= "\n";
		$maincontent .= "</div>";
		$maincontent .= "\n";
	}
}



////サブメニューを設定する。
//$maincontent .= "<ul id=\"sealn\">";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_set\">";
//$maincontent .= "<a href=\"?step=1".$addHref."\">印鑑選択</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_imprint\">";
//$maincontent .= "<a href=\"?step=2".$addHref."\">印影選択</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_name\">";
//$maincontent .= "<a href=\"?step=3".$addHref."\">会社名・お届け先</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_confirm\">";
//$maincontent .= "<a href=\"?step=4".$addHref."\">入力内容確認</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "</ul>";
//$maincontent .= "\n";


$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);


//スクリプトを設定する。
$script = null;

$addStyle = null;

switch ($mode) {
	case 1:
//		$script .= "<script language=\"javascript\" src=\"".$basePath."/common/js/search_notary/search_notary.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
//		$script .= "\n";
//		$script .= "<script language=\"javascript\" src=\"".$basePath."/common/js/article_course/article_course.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
//		$script .= "\n";

		$script .= "<script language=\"javascript\" src=\"".$basePath."/common/js/article_option/article_option.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
		$script .= "\n";

		//スクリプトを設定する。
		$script .= "<script type=\"text/javascript\">";
		$script .= "\n";
		$script .= "<!--";
		$script .= "\n";
		$script .= "window.addEvent('domready', function(){";
		$script .= "\n";

		//(2011/10/26) 未使用になった。→これだけ必要。追加した。
		if ($userStatusSystemErrorFlag) {
			$script .= "$$('div.pdfset div.pdf div.output input').setStyle('display','none');";
			$script .= "\n";
			$script .= "$$('div.pdfset div.pdf div.output').setStyle('background','url(../../../img/pdf/pdf_btn_print_03.gif) no-repeat left top');";
			$script .= "\n";

			$script .= "$$('div#frm_button input').set('value','×ご利用不可');";
			$script .= "\n";
			$script .= "$$('div#frm_button input').set('disabled',true);";
			$script .= "\n";
			$script .= "$$('div#frm_button input').setStyle('background-color','#f00');";
			$script .= "\n";
			$script .= "$$('div#frm_button input').setStyle('color','#fff');";
			$script .= "\n";
		}



//		$script .= "$$('input.article_course').addEvent('click', function(e) {";
//		$script .= "\n";
//		$script .= "updateArticleCourse(this, '".$companyId."');";
//		$script .= "\n";
//		$script .= "});";
//		$script .= "\n";
//		$script .= "$$('input.article_course').addEvent('click', function(e) {";
//		$script .= "\n";
//		//$script .= "alert('name='+this.name+'/value='+this.value+'/checked='+this.checked);";
//		//$script .= "\n";
//		$script .= "setArticleCourse(this.value);";
//		$script .= "\n";
//		$script .= "});";
//		$script .= "\n";
//		$script .= "\n";
//		$script .= "var value = '';";
//		$script .= "\n";
//		$script .= "$$('input.article_course').each(function(el){";
//		$script .= "\n";
//		//$script .= "alert('name='+el.name+'/value='+el.value+'/checked='+el.checked);";
//		//$script .= "\n";
//		$script .= "if (el.checked) {";
//		$script .= "\n";
//		$script .= "value = el.value;";
//		$script .= "\n";
//		$script .= "}";
//		$script .= "\n";
//		$script .= "});";
//		$script .= "\n";
//		$script .= "setArticleCourse(value);";
//		$script .= "\n";

		$script .= "});";
		$script .= "\n";

//		$script .= "function setArticleCourse(value) {";
//		$script .= "\n";
//		$script .= "showNode('frm_button', false);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_pdf', false);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_date', false);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_deliver', false);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_charge', false);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_notary', false);";
//		$script .= "\n";
//
//		if ($userStatusStandardErrorFlag) {
//			$script .= "showNode('system_course_standard', false);";
//			$script .= "\n";
//
//			$script .= "$$('div#frm_button input').set('value','確　認');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').set('disabled',false);";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('background-color','#fafafa');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('color','#333');";
//			$script .= "\n";
//		}
//		if ($userStatusEntrustErrorFlag) {
//			$script .= "showNode('system_course_entrust', false);";
//			$script .= "\n";
//
//			$script .= "$$('div#frm_button input').set('value','確　認');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').set('disabled',false);";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('background-color','#fafafa');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('color','#333');";
//			$script .= "\n";
//		}
//		if ($userStatusSystemErrorFlag) {
//			$script .= "$$('div.pdfset div.pdf div.output input').setStyle('display','none');";
//			$script .= "\n";
//			$script .= "$$('div.pdfset div.pdf div.output').setStyle('background','url(../../../img/pdf/pdf_btn_print_03.gif) no-repeat left top');";
//			$script .= "\n";
//
//			$script .= "$$('div#frm_button input').set('value','×ご利用不可');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').set('disabled',true);";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('background-color','#f00');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('color','#fff');";
//			$script .= "\n";
//		}
//
//		$script .= "switch (value) {";
//		$script .= "\n";
//		$script .= "case '".MST_ARTICLE_COURSE_ID_NORMAL."':";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_pdf', true);";
//		$script .= "\n";
//		$script .= "break;";
//		$script .= "\n";
//		$script .= "case '".MST_ARTICLE_COURSE_ID_STANDARD."':";
//		$script .= "\n";
//		$script .= "showNode('frm_button', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_date', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_deliver', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_charge', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_notary', true);";
//		$script .= "\n";
//
//		if ($userStatusStandardErrorFlag) {
//			$script .= "showNode('system_course_standard', true);";
//			$script .= "\n";
//
//			$script .= "$$('div#frm_button input').set('value','×ご利用不可');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').set('disabled',true);";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('background-color','#f00');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('color','#fff');";
//			$script .= "\n";
//		}
//
//		$script .= "break;";
//		$script .= "\n";
//		$script .= "case '".MST_ARTICLE_COURSE_ID_ENTRUST."':";
//		$script .= "\n";
//		$script .= "showNode('frm_button', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_date', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_deliver', true);";
//		$script .= "\n";
//
//		if ($userStatusEntrustErrorFlag) {
//			$script .= "showNode('system_course_entrust', true);";
//			$script .= "\n";
//
//			$script .= "$$('div#frm_button input').set('value','×ご利用不可');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').set('disabled',true);";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('background-color','#f00');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('color','#fff');";
//			$script .= "\n";
//		}
//
//		$script .= "break;";
//		$script .= "\n";
//		$script .= "}";
//		$script .= "\n";
//		$script .= "}";
//		$script .= "\n";

		$script .= "//-->";
		$script .= "\n";
		$script .= "</script>";
		$script .= "\n";


//(2011/10/26) 仮処理です。定款を印刷手段が必要！
//権限によって、制限する。
switch($loginInfo['usr_auth_id']){
	case AUTH_WOOROM://WOOROM権限
		//定款印刷
		$buf = _CreateTableInput4Article($mode, $xmlList, $info, $tabindex);
		$maincontent .= "\n";
		$maincontent .= $buf;
	break;
}

		//説明用文章を設定する。
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_07.txt';
		_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (説明用文章) HTMLテンプレートファイル = '".$tempExpFile."'");
		$htmlExp = @file_get_contents($tempExpFile);
		//"HTML"が存在する場合、表示する。
		if ($htmlExp !== false && !_IsNull($htmlExp)) {
			_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (説明用文章) 【成功】");
		} else {
			//取得できなかった場合
			_Log("[/user/company/article/index.php] {HTMLテンプレートを読み込み} (説明用文章) 【失敗】");
			$htmlExp = null;
		}
		if (!_IsNull($htmlExp)) {
			$buf = null;
			$buf .= $maincontent;
			$buf .= "\n";
			$buf .= "\n";
			$buf .= "\n";
			$buf .= $htmlExp;

			$maincontent = $buf;
		}
		break;
}

//switch ($xmlName) {
//	case XML_NAME_SEAL_SET:
//		//法人印注文情報[印鑑]
//		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
//		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
//		break;
//	case XML_NAME_SEAL_ALL:
//		//法人印注文情報[入力内容確認]
////		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
////		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
//		break;
//	default:
//		break;
//}
//
//$script .= "<style type=\"text/css\">";
//$script .= "\n";
//$script .= "<!--";
//$script .= "\n";
//$script .= "ul#sealn li#".$stepId." a:link";
//$script .= ",ul#sealn li#".$stepId." a:visited";
//$script .= "\n";
//$script .= "{height: 32px;color: #3176af;border-bottom: 3px solid #76b0df;}";
//$script .= "\n";
//$script .= $addStyle;
//$script .= "\n";
//$script .= "-->";
//$script .= "\n";
//$script .= "</style>";
//$script .= "\n";






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
$htmlSidebarUserMenu = str_replace('{company_info}', _GetCompanyInfoHtml($loginInfo), $htmlSidebarUserMenu);

$sidebar .= $htmlSidebarUserMenu;


//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_COMPANY, '', PAGE_TITLE_COMPANY, 3);
_SetBreadcrumbs(PAGE_DIR_COMPANY_ARTICLE, '', PAGE_TITLE_COMPANY_ARTICLE, 4);
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


_Log("[/user/company/article/index.php] end.");
echo $html;



































//選択されているサービスに対応する情報のみにする。
function _GetSelectedServiceOnly($xmlList, $info) {
	_Log("[_GetSelectedServiceOnly] start.");

	$bufXmlList = $xmlList;

	//許認可
	//"希望する"場合
	if (isset($info['update']['tbl_article_option']['art_opt_option_permission_id']) && $info['update']['tbl_article_option']['art_opt_option_permission_id'] == MST_OPTION_ID_YES) {
	} else {
		for ($i = 1; $i <= 9; $i++) {
			$bufXmlList = _DeleteXmlByTagAndValue($bufXmlList, 'name', 'art_opt_permission_'.$i.'_id');
		}
		$bufXmlList = _DeleteXmlByTagAndValue($bufXmlList, 'name', 'art_opt_permission_note');
	}
	//保険
	//"希望する"場合
	if (isset($info['update']['tbl_article_option']['art_opt_option_insurance_id']) && $info['update']['tbl_article_option']['art_opt_option_insurance_id'] == MST_OPTION_ID_YES) {
	} else {
		$bufXmlList = _DeleteXmlByTagAndValue($bufXmlList, 'name', 'art_opt_insurance_id');
	}
	//就業規則
	//"希望する"場合
	if (isset($info['update']['tbl_article_option']['art_opt_option_regulations_id']) && $info['update']['tbl_article_option']['art_opt_option_regulations_id'] == MST_OPTION_ID_YES) {
	} else {
		$bufXmlList = _DeleteXmlByTagAndValue($bufXmlList, 'name', 'art_opt_regulations_id');
	}
	//ホームページ制作
	//"希望する"場合
	if (isset($info['update']['tbl_article_option']['art_opt_option_micro_web_id']) && $info['update']['tbl_article_option']['art_opt_option_micro_web_id'] == MST_OPTION_ID_YES) {
	} else {
		$bufXmlList = _DeleteXmlByTagAndValue($bufXmlList, 'name', 'art_opt_micro_web_id');
	}

	_Log("[_GetSelectedServiceOnly] XMLを読み込んだ配列(編集前) = '".print_r($xmlList,true)."'");
	_Log("[_GetSelectedServiceOnly] XMLを読み込んだ配列(編集後) = '".print_r($bufXmlList,true)."'");
	_Log("[_GetSelectedServiceOnly] end.");
	return $bufXmlList;




//	return $xmlList;

	//(2011/10/26) 未使用になった。
	
	$bufXmlList = $xmlList;
	

	//選択されたサービスを取得する。
	$serviceId = null;
	if (isset($info['update']['tbl_article_course']['art_crs_article_course_id']) && !_IsNull($info['update']['tbl_article_course']['art_crs_article_course_id'])) {
		$serviceId = $info['update']['tbl_article_course']['art_crs_article_course_id'];
	}
	switch ($serviceId) {
		case MST_ARTICLE_COURSE_ID_NORMAL:
			//[書面認証]従来型の紙ベースコース
			$bufXmlList = _DeleteXmlByTag($bufXmlList, 'tbl_article_date');
			$bufXmlList = _DeleteXmlByTag($bufXmlList, 'tbl_article_deliver');
			$bufXmlList = _DeleteXmlByTag($bufXmlList, 'tbl_article_charge');
			$bufXmlList = _DeleteXmlByTag($bufXmlList, 'tbl_article_notary');
			break;
		case MST_ARTICLE_COURSE_ID_STANDARD:
		