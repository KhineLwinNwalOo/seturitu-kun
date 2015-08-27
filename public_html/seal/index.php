<?php
/*
 * [新★会社設立.JP ツール]
 * 法人印注文ページ
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
_Log("[/seal/index.php] start.");


_Log("[/seal/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/seal/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/seal/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/seal/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


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
_Log("[/seal/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/seal/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/seal/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/seal/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


//$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
//_Log("[/seal/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"が存在する場合、表示する。
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/seal/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
//} else {
//	//取得できなかった場合
//	_Log("[/seal/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
//}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/seal/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/seal/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/seal/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("[/seal/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_SEAL;

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


_Log("[/seal/index.php] \$_GET(詰め替え後) = '".print_r($_GET,true)."'");

//パラメーターを取得する。
$xmlName = XML_NAME_SEAL;//XMLファイル名を設定する。
$id = null;
$step = null;
$stepId = null;
switch ($requestMethod) {
	case 'POST':
//		//XMLファイル名
//		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
		//ターゲットID
		$id = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);
		//ステップID
		$step = (isset($_POST['condition']['_step_'])?$_POST['condition']['_step_']:null);


		_Log("[/seal/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/seal/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/seal/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/seal/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
				_Log("[/seal/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

				$id = null;

				//自分のユーザー情報のみ表示する。
				//ユーザーIDを検索する。
				$id = $loginInfo['usr_user_id'];

				_Log("[/seal/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");
				break;
		}


		//入力値を取得する。
		$info = $_POST;
		_Log("[/seal/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/seal/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

		//「半角カタカナ」を「全角カタカナ」に変換する。→メールで半角カナが文字化けするので。
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/pay/index.php] POST(「半角カタカナ」を「全角カタカナ」に変換する。) = '".print_r($info,true)."'");


		//XMLファイル名、ターゲットIDを上書きする。
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;

		break;
	case 'GET':
//		//XMLファイル名
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
		//ターゲットID
		$id = (isset($_GET['id'])?$_GET['id']:null);
		//ステップID
		$step = (isset($_GET['step'])?$_GET['step']:null);

		//遷移元ページ
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);


		//初期値を設定する。
		$undeleteOnly4def = false;



		_Log("[/seal/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/seal/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/seal/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/seal/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
				_Log("[/seal/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

				$id = null;
				$undeleteOnly4def = true;

				//自分のユーザー情報のみ表示する。
				//ユーザーIDを検索する。
				$id = $loginInfo['usr_user_id'];


				_Log("[/seal/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");

//				//遷移元ページはどこか？
//				switch ($pId) {
//					case PAGE_ID_USER://ユーザーページ
//						break;
//				}
				break;
		}



//		$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
		$info['update'] = $_SESSION[SID_SEAL_INFO];

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
		$_SESSION[SID_SEAL_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/seal/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/seal/index.php] XMLファイル名 = '".$xmlName."'");
_Log("[/seal/index.php] ターゲットID = '".$id."'");


//ユーザー情報(ログイン情報)を設定する。→DB更新に使用する。
$info['update']['tbl_user'] = $loginInfo;

//お届け先情報が未設定の場合、ユーザー情報(ログイン情報)を初期値として設定する。
if (!isset($info['update']['tbl_seal_deliver'])) {
	$info['update']['tbl_seal_deliver']['sel_dlv_zip1'] = $loginInfo['usr_zip1'];
	$info['update']['tbl_seal_deliver']['sel_dlv_zip2'] = $loginInfo['usr_zip2'];

	$info['update']['tbl_seal_deliver']['sel_dlv_pref_id'] = $loginInfo['usr_pref_id'];
	$info['update']['tbl_seal_deliver']['sel_dlv_address1'] = $loginInfo['usr_address1'];
	$info['update']['tbl_seal_deliver']['sel_dlv_address2'] = $loginInfo['usr_address2'];

	$info['update']['tbl_seal_deliver']['sel_dlv_tel1'] = $loginInfo['usr_tel1'];
	$info['update']['tbl_seal_deliver']['sel_dlv_tel2'] = $loginInfo['usr_tel2'];
	$info['update']['tbl_seal_deliver']['sel_dlv_tel3'] = $loginInfo['usr_tel3'];

	$info['update']['tbl_seal_deliver']['sel_dlv_e_mail'] = $loginInfo['usr_e_mail'];
	$info['update']['tbl_seal_deliver']['sel_dlv_e_mail_confirm'] = $loginInfo['usr_e_mail'];

	$info['update']['tbl_seal_deliver']['sel_dlv_family_name'] = $loginInfo['usr_family_name'];
	$info['update']['tbl_seal_deliver']['sel_dlv_first_name'] = $loginInfo['usr_first_name'];
}

switch ($step) {
	case 1:
		//法人印注文情報[印鑑]
		//→XML形式のフォームではなく。直接書き出す。
		$xmlName = XML_NAME_SEAL_SET;

		$stepId = "sealn_set";
		break;
	case 2:
		//法人印注文情報[印影]
		$xmlName = XML_NAME_SEAL_IMPRINT;

		$stepId = "sealn_imprint";
		break;
	case 3:
		//法人印注文情報[会社名・お届け先]
		$xmlName = XML_NAME_SEAL_NAME;

		$stepId = "sealn_name";
		break;
	case 4:
		//法人印注文情報[入力内容確認]
		$xmlName = XML_NAME_SEAL_ALL;

		$stepId = "sealn_confirm";
		break;
	default:
		//法人印注文情報[印鑑]
		//→XML形式のフォームではなく。直接書き出す。
		$xmlName = XML_NAME_SEAL_SET;

		$stepId = "sealn_set";

		$step = 1;
		break;
}
$info['condition']['_step_'] = $step;

_Log("[/seal/index.php] ステップID = '".$step."'");
_Log("[/seal/index.php] XMLファイル名(ステップID) = '".$xmlName."'");

//戻るボタンが押された場合→すぐ遷移するので、XMLは読み込まない。
if ($_POST['back'] != "") $xmlName = null;


//デフォルトの会社名を設定する。
$defSelNamCompanyName = null;

$xmlList = null;
if (!_IsNull($xmlName)) {


	$otherList = null;

	//ユーザーIDに関連する会社IDを検索する。
	//ユーザー_会社_関連付テーブル
	$condition = null;
	$condition['usr_cmp_rel_user_id'] = $id;
	$order = null;
	$order .= "usr_cmp_rel_company_id";		//ソート条件=会社IDの昇順
	$tblUserCompanyRelationList = _DB_GetListByAssociative('tbl_user_company_relation', 'usr_cmp_rel_company_id', null, $condition, true, $order,'usr_cmp_rel_del_flag');
	$tblCompanyList = null;
	if (!_IsNull($tblUserCompanyRelationList)) {
		//会社テーブル
		$condition = null;
		$condition['cmp_company_id'] = $tblUserCompanyRelationList;//会社ID
		$order = null;
		$order .= "cmp_company_id";		//ソート条件=会社IDの昇順
		$tblCompanyList = _DB_GetList('tbl_company', $condition, true, $order, 'cmp_del_flag', 'cmp_company_id');
		foreach ($tblCompanyList as $cKey => $tblCompanyInfo) {
			if (_IsNull($tblCompanyInfo['cmp_company_name'])) {
				$bufId = null;
				$bufName = '※会社名が未登録です。先に会社名を登録してください。';
				$tblCompanyInfo['cmp_company_id'] = $bufId;
				$tblCompanyInfo['cmp_company_name'] = $bufName;
				$tblCompanyList[$cKey] = $tblCompanyInfo;
			} else {
				if (_IsNull($defSelNamCompanyName)) {
					$defSelNamCompanyName = $tblCompanyInfo['cmp_company_id'];
				}
			}
		}
	}
	if (_IsNull($tblCompanyList)) {
		$tblCompanyList[] = array('cmp_company_id' => '', 'cmp_company_name' => '※現在、未登録です。');
	}

	$planExplanation = null;
	//プランID
	switch($loginInfo['usr_plan_id']){
		case MST_PLAN_ID_NORMAL://通常プラン
			break;
		default:
			//プランマスタ
			$condition4Mst = null;
			$undeleteOnly4Mst = true;
			$order4Mst = "lpad(show_order,10,'0'),id";
			$mstPlanList = _DB_GetList('mst_plan', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
			if (!_IsNull($mstPlanList)) {
				foreach ($mstPlanList as $mKey => $mstPlanInfo) {
					//割引率が未設定は次へ。
					if (_IsNull($mstPlanInfo['value'])) continue;
					if (!_IsNull($planExplanation)) $planExplanation .= "<br />";
					$planExplanation .= "【";
					$planExplanation .= $mstPlanInfo['name'];
					$planExplanation .= " ";
					$planExplanation .= "印鑑全品";
					$planExplanation .= $mstPlanInfo['value'];
					$planExplanation .= "%OFF";
					$planExplanation .= "】";
				}
			}
			break;
	}

	//印鑑テーブル
	$condition = null;
	$order = null;
	$order .= "lpad(sel_show_order,10,'0')";	//ソート条件=表示順の昇順
	$order .= ",sel_seal_id";					//ソート条件=IDの昇順
	$tblSealList = _DB_GetList('tbl_seal', $condition, true, $order, 'sel_del_flag', 'sel_seal_id');
	if (!_IsNull($tblSealList)) {
		foreach ($tblSealList as $sKey => $tblSealInfo) {

			$bufTag = null;
			$bufName = null;

			//商品価格
			$selPriceShow = null;
			$selPrice = $tblSealInfo['sel_price'];
//			if (!_IsNull($selPrice)) $selPriceShow = "￥".number_format($selPrice)."- (消費税・送料・代引手数料込み)";
			if (!_IsNull($selPrice)) $selPriceShow = "￥".number_format($selPrice)."- (消費税込み)";

			$bufTag .= $tblSealInfo['sel_name'];
			$bufTag .= "&nbsp;";
			$bufTag .= $selPriceShow;
			$bufTag .= "<br />";
			$bufTag .= "<img src=\"../img/seal/".sprintf('%03d', $tblSealInfo['sel_seal_id']).".jpg\" alt=\"".htmlspecialchars($tblSealInfo['sel_name'])."\" />";
			$bufTag .= "<p>";
			$bufTag .= nl2br($tblSealInfo['sel_explanation']);
			$bufTag .= "</p>";

			if (!_IsNull($planExplanation)) {
				$bufTag .= "<p class=\"sealset_plan\">";
				$bufTag .= $planExplanation;
				$bufTag .= "<p>";
			}

			$bufName .= $tblSealInfo['sel_name'];
			$bufName .= " ";
			$bufName .= $selPriceShow;

			$tblSealInfo['tag'] = $bufTag;
			$tblSealInfo['name_price'] = $bufName;
			$tblSealList[$sKey] = $tblSealInfo;
		}
	}

	$otherList = array(
		 'tbl_company' => $tblCompanyList
		,'tbl_seal' => $tblSealList
	);


	//XMLを読み込む。
	$xmlFile = "../common/form_xml/".$xmlName.".xml";
	_Log("[/seal/index.php] XMLファイル = '".$xmlFile."'");
	$xmlList = _GetXml($xmlFile, $otherList);

	_Log("[/seal/index.php] XMLファイル配列 = '".print_r($xmlList,true)."'");

	switch ($xmlName) {
		case XML_NAME_SEAL_ALL:
			//法人印注文情報[入力内容確認]

			//全てのXMLを読み込む。

			//法人印注文情報[印鑑](確認画面用)
			$bufXmlFile = "../common/form_xml/".XML_NAME_SEAL_SET_4_CONFIRM.".xml";
			_Log("[/seal/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile, $otherList);
			$xmlList['tbl_seal'] = $bufXmlList['tbl_seal'];

			//法人印注文情報[印影]
			$bufXmlFile = "../common/form_xml/".XML_NAME_SEAL_IMPRINT.".xml";
			_Log("[/seal/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile, $otherList);
			$xmlList['tbl_seal_imprint'] = $bufXmlList['tbl_seal_imprint'];

			///法人印注文情報[会社名・お届け先]
			$bufXmlFile = "../common/form_xml/".XML_NAME_SEAL_NAME.".xml";
			_Log("[/seal/index.php] XMLファイル = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile, $otherList);
			$xmlList['tbl_seal_name'] = $bufXmlList['tbl_seal_name'];
			$xmlList['tbl_seal_deliver'] = $bufXmlList['tbl_seal_deliver'];


			_Log("[/seal/index.php] XMLファイル配列(全XMLマージ後) = '".print_r($xmlList,true)."'");
			_Log("[/seal/index.php] 法人印注文情報(全XMLマージ後) = '".print_r($info,true)."'");

			$mode = 2;

			break;
	}
}


//会社名が未選択の場合、初期値を設定する。
if (!isset($info['update']['tbl_seal_name'])) {
	$info['update']['tbl_seal_name']['sel_nam_company_name'] = $defSelNamCompanyName;
}



//送信ボタン、次へボタンが押された場合
if ($_POST['go'] != "" || $_POST['next'] != "") {
	//入力値チェック
	$message .= _CheackInputAll($xmlList, $info);

	switch ($xmlName) {
		case XML_NAME_SEAL_SET:
			//法人印注文情報[印鑑]
			$message .= _CheackInput4SealSet($xmlList, $info);
			break;
		case XML_NAME_SEAL_NAME:
			//法人印注文情報[会社名・お届け先]
			$message .= _CheackInput4SealName($xmlList, $info);
			break;
		case XML_NAME_SEAL_ALL:
			//法人印注文情報[入力内容確認]
//			$message .= _CheackInput4SealSet($xmlList, $info);
			$message .= _CheackInput4SealName($xmlList, $info);
			break;
		default:
			break;
	}

	//セッションに保存する。
	switch ($xmlName) {
		case XML_NAME_SEAL_SET:
			//法人印注文情報[印鑑]
			$_SESSION[SID_SEAL_INFO]['tbl_seal'] = $info['update']['tbl_seal'];
			break;
		case XML_NAME_SEAL_IMPRINT:
			//法人印注文情報[印影]
			$_SESSION[SID_SEAL_INFO]['tbl_seal_imprint'] = $info['update']['tbl_seal_imprint'];
			break;
		case XML_NAME_SEAL_NAME:
			//法人印注文情報[会社名・お届け先]
			$_SESSION[SID_SEAL_INFO]['tbl_seal_name'] = $info['update']['tbl_seal_name'];
			$_SESSION[SID_SEAL_INFO]['tbl_seal_deliver'] = $info['update']['tbl_seal_deliver'];
			break;
	}

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

				_Log("[/seal/index.php] メール本文(_CreateMailAll) = '".$body."'");

				$body .= "\n";
				$body .= "\n";
				$body .= "\n";
				$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
				$body .= "お支払方法について\n";
				$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
				//銀行振込
				$body .= "■振込先：";
				$body .= "\n";
				$body .= SEAL_BANK_ACCOUNT_BANK_NAME;
				$body .= "\n";
				$body .= SEAL_BANK_ACCOUNT_BRANCH_NAME;
				$body .= "\n";
				$body .= SEAL_BANK_ACCOUNT_TYPE;
				$body .= " ";
				$body .= SEAL_BANK_ACCOUNT_NUMBER;
				$body .= "\n";
				$body .= SEAL_BANK_ACCOUNT_NAME;
				$body .= "\n";
				$body .= "\n";

				//プランマスタ
				$condition4Mail = array();
				$condition4Mail['id'] = $info['update']['tbl_user']['usr_plan_id'];					//プランID
				$undeleteOnly4Mail = true;
				$mstPlanInfo4Mail = _DB_GetInfo('mst_plan', $condition4Mail, $undeleteOnly4Mail, 'del_flag');

				//印鑑テーブル
				$condition4Mail = array();
				$condition4Mail['sel_seal_id'] = $info['update']['tbl_seal']['sel_seal_id'];		//印鑑ID
				$undeleteOnly4Mail = true;
				$tblSealInfo4Mail = _DB_GetInfo('tbl_seal', $condition4Mail, $undeleteOnly4Mail, 'sel_del_flag');

				//商品価格
				$sealPrice = $tblSealInfo4Mail['sel_price'];
				//割引率(単位:%)
				$offRate = $mstPlanInfo4Mail['value'];
				//割引価格
				$offPrice = 0;
				//販売価格
				$sellPrice = $sealPrice;
				//コメント
				$sealComment = null;

				if (!_IsNull($offRate)) {
					$offPrice = floor($sealPrice * $offRate / 100);//端数の切り捨て
					$sellPrice = $sealPrice - $offPrice;
					$sealComment .= "【";
					$sealComment .= $mstPlanInfo4Mail['name'];
					$sealComment .= " ";
					$sealComment .= "印鑑全品";
					$sealComment .= $offRate;
					$sealComment .= "%OFF";
					$sealComment .= "】";
				}

				_Log("[/seal/index.php] 割引率(単位:%) = '".$offRate."'");
				_Log("[/seal/index.php] 商品価格 = '".$sealPrice."'");
				_Log("[/seal/index.php] 割引価格 = '".$offPrice."'");
				_Log("[/seal/index.php] 販売価格 = '".$sellPrice."'");
				_Log("[/seal/index.php] コメント = '".$sealComment."'");


				$body .= "■振込金額：";
				$body .= "\n";
				$body .= "￥".number_format($sellPrice)."- (消費税込み)";
				$body .= "\n";
				if (!_IsNull($sealComment)) {
					$body .= $sealComment;
					$body .= "\n";
				}

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

				$body .= "法人印注文日時：".date("Y年n月j日 H時i分")."\n";
				$body .= $_SERVER["REMOTE_ADDR"]."\n";

				//管理者用メール本文を設定する。
				$adminBody = "";
				//$adminBody .= $siteTitle." \n";
				//$adminBody .= "\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "『".$siteTitle."』に法人印の注文が入りました。\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "\n";
				$adminBody .= $body;

				//問合せフォーム-GoogleDoc連携
				include_once("http://www.sin-kaisha.jp/admin/common/request.ini");
				$adminBody .= "\n";
				$adminBody .= "\n";
				$adminBody .= "googlegooglegooglegooglegooglegooglegooglegooglegooglegoogle\n";
				$adminBody .= "\n";
				$adminBody .= _SetGoogleDocRequest(1, $info);

				//お客様用メール本文を設定する。
				$customerBody = "";
				$customerBody .= $info['update']['tbl_seal_deliver']['sel_dlv_family_name']." ".$info['update']['tbl_seal_deliver']['sel_dlv_first_name']." 様\n";
				$customerBody .= "\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "この度は、『".$siteTitle."』に法人印の注文をしていただきありがとうございました。\n";
				$customerBody .= "確認のため、下記にお客様のご登録の内容をお知らせいたします。\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "\n";
				$customerBody .= $body;


				//管理者用タイトルを設定する。
				$adminTitle = "[".$siteTitle."] 法人印注文 (".$info['update']['tbl_seal_deliver']['sel_dlv_family_name']." ".$info['update']['tbl_seal_deliver']['sel_dlv_first_name']." 様)";
				//お客様用タイトルを設定する。
				$customerTitle = "[".$siteTitle."] 法人印注文ありがとうございました";

				mb_language("Japanese");
				
				$parameter = "-f ".$clientMail;

				//メール送信
				//お客様に送信する。
				$rcd = mb_send_mail($info['update']['tbl_seal_deliver']['sel_dlv_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

				//クライアントに送信する。
				$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_seal_deliver']['sel_dlv_e_mail']);

				//マスターに送信する。
				foreach($masterMailList as $masterMail){
					$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_seal_deliver']['sel_dlv_e_mail']);
				}


				//メッセージを設定する。
				$message .= $info['update']['tbl_seal_deliver']['sel_dlv_family_name']."&nbsp;".$info['update']['tbl_seal_deliver']['sel_dlv_first_name'];
				$message .= "&nbsp;様";
				$message .= "\n";
				$message .= "\n";
				$message .= "この度は、『".$siteTitle."』に法人印の注文をしていただきありがとうございました。";
				$message .= "\n";
				$message .= "お客様のメールアドレス宛てにご登録内容の「確認メール」が自動送信されました。";
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
	//					$message .= "<a href=\"../item/?back\" title=\"商品一覧に戻る\">[商品一覧に戻る]</a>\n";
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
	//							$message .= "<a href=\"../inquiry_price/?back\" title=\"請求額一覧に戻る\">[請求額一覧に戻る]</a>\n";
	//							break;
	//						default:
	//							$message .= "<a href=\"../inquiry/?back\" title=\"問合せ一覧に戻る\">[問合せ一覧に戻る]</a>\n";
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


$addHref = null;
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://権限無し
		break;
	default:
		if (!_IsNull($id)) {
			$addHref = "&amp;id=".$id;
		}
		break;
}

//次へボタンが押された場合
if ($_POST['next'] != "") {
	if (!$errorFlag) {
		//次のページを表示する。
		$step++;
		header("Location: ./?step=".$step.$addHref);
		exit;
	}
}
//戻るボタンが押された場合
elseif ($_POST['back'] != "") {
	//前のページを表示する。
	$step--;
	header("Location: ./?step=".$step.$addHref);
	exit;
}


//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/seal/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/seal/index.php] mode = '".$mode."'");




//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../img/maincontent/pt_seal.jpg\" title=\"\" alt=\"印鑑作成（実印・銀行印等）\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

//サブメニューを設定する。
$maincontent .= "<ul id=\"sealn\">";
$maincontent .= "\n";
$maincontent .= "<li id=\"sealn_set\">";
$maincontent .= "<a href=\"?step=1".$addHref."\">印鑑選択</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"sealn_imprint\">";
$maincontent .= "<a href=\"?step=2".$addHref."\">印影選択</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"sealn_name\">";
$maincontent .= "<a href=\"?step=3".$addHref."\">会社名・お届け先</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"sealn_confirm\">";
$maincontent .= "<a href=\"?step=4".$addHref."\">入力内容確認</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "</ul>";
$maincontent .= "\n";


$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);


//スクリプトを設定する。
$script = null;

$addStyle = null;

switch ($xmlName) {
	case XML_NAME_SEAL_SET:
		//法人印注文情報[印鑑]
		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
		break;
	case XML_NAME_SEAL_ALL:
		//法人印注文情報[入力内容確認]
//		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
//		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
		break;
	default:
		break;
}

$script .= "<style type=\"text/css\">";
$script .= "\n";
$script .= "<!--";
$script .= "\n";
$script .= "ul#sealn li#".$stepId." a:link";
$script .= ",ul#sealn li#".$stepId." a:visited";
$script .= "\n";
$script .= "{height: 32px;color: #3176af;border-bottom: 3px solid #76b0df;}";
$script .= "\n";
$script .= $addStyle;
$script .= "\n";
$script .= "-->";
$script .= "\n";
$script .= "</style>";
$script .= "\n";






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
_SetBreadcrumbs(PAGE_DIR_SEAL, '', PAGE_TITLE_SEAL, 3);
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


_Log("[/seal/index.php] end.");
echo $html;



























/**
 * 印鑑会社名情報用
 * 入力値のチェックをする。
 *
 * @param	array	$xmlList		XMLを読み込んだ配列
 * @param	array	$info			入力した値が格納されている配列
 * @return	エラーメッセージ
 * @access  public
 * @since
 */
function _CheackInput4SealName($xmlList, $info) {
	$res = null;
//	if (!isset($info['update']['tbl_seal_name']['sel_nam_company_name']) || _IsNull($info['update']['tbl_seal_name']['sel_nam_company_name'])) {
//		if (!isset($info['update']['tbl_seal_name']['sel_nam_other_company_name']) || _IsNull($info['update']['tbl_seal_name']['sel_nam_other_company_name'])) {
//			$res .= "会社名又は、別の会社名を入力してください。";
//		}
//	}
	return $res;
}


/**
 * 印鑑テーブル情報用
 * 入力値のチェックをする。
 *
 * @param	array	$xmlList		XMLを読み込んだ配列
 * @param	array	$info			入力した値が格納されている配列
 * @return	エラーメッセージ
 * @access  public
 * @since
 */
function _CheackInput4SealSet($xmlList, $info) {
	$res = null;
	if (!isset($info['update']['tbl_seal']['sel_seal_id']) || _IsNull($info['update']['tbl_seal']['sel_seal_id'])) {
		$label = $xmlList['tbl_seal']['item_label']['sel_seal_id'];
		$res .= $label."を選択してください。";
	}
	return $res;
}





/**
 * 印鑑テーブル情報用
 * 入力用に表示するテーブル(フォーム)を作成する。
 *
 * @param	int		$mode			動作モード{1:入力/2:確認/3:完了/4:エラー}
 * @param	array	$xmlList		XMLを読み込んだ配列
 * @param	array	$info			入力した値が格納されている配列
 * @param	int		&$tabindex		タブインデックス
 * @return	テーブル(フォーム)HTML文字列
 * @access  public
 * @since
 */
function _CreateTableInput4SealSet($mode, $xmlList, $info, &$tabindex) {

	//印鑑テーブル
	$condition = null;
	$order = null;
	$order .= "lpad(sel_show_order,10,'0')";	//ソート条件=表示順の昇順
	$order .= ",sel_seal_id";					//ソート条件=IDの昇順
	$tblSealList = _DB_GetList('tbl_seal', $condition, true, $order, 'sel_del_flag', 'sel_seal_id');

	//印鑑セットマスタ
	$condition = null;
	$order = null;
	$order .= "lpad(show_order,10,'0')";	//ソート条件=表示順の昇順
	$order .= ",id";						//ソート条件=IDの昇順
	$mstSealSetList = _DB_GetList('mst_seal_set', $condition, true, $order, 'del_flag', 'id');

	if (_IsNull($tblSealList)) return null;
	if (_IsNull($mstSealSetList)) return null;


	$planExplanation = null;
	//プランID
	switch($info['update']['tbl_user']['usr_plan_id']){
		case MST_PLAN_ID_NORMAL://通常プラン
			break;
		default:
			//プランマスタ
			$condition4Mst = null;
			$undeleteOnly4Mst = true;
			$order4Mst = "lpad(show_order,10,'0'),id";
			$mstPlanList = _DB_GetList('mst_plan', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
			if (!_IsNull($mstPlanList)) {
				foreach ($mstPlanList as $mKey => $mstPlanInfo) {
					//割引率が未設定は次へ。
					if (_IsNull($mstPlanInfo['value'])) continue;
					if (!_IsNull($planExplanation)) $planExplanation .= "<br />";
					$planExplanation .= "【";
					$planExplanation .= $mstPlanInfo['name'];
					$planExplanation .= " ";
					$planExplanation .= "印鑑全品";
					$planExplanation .= $mstPlanInfo['value'];
					$planExplanation .= "%OFF";
					$planExplanation .= "】";
				}
			}
			break;
	}


	$res = null;
	$message = null;

	switch ($mode) {
		case 1:
			foreach ($mstSealSetList as $msKey => $mstSealSetInfo) {
				$resBufSet = null;
				foreach ($tblSealList as $tsKey => $tblSealInfo) {
					if ($mstSealSetInfo['id'] != $tblSealInfo['sel_seal_set_id']) continue;
					$resBuf = null;

					//商品価格
					$selPriceShow = null;
					$selPrice = $tblSealInfo['sel_price'];
//					if (!_IsNull($selPrice)) $selPriceShow = "￥".number_format($selPrice)."- (消費税・送料・代引手数料込み)";
					if (!_IsNull($selPrice)) $selPriceShow = "￥".number_format($selPrice)."- (消費税込み)";

					$id = "sel_seal_id_".$tblSealInfo['sel_seal_id'];

					$checked = null;
					if (isset($info['update']['tbl_seal']['sel_seal_id']) && $info['update']['tbl_seal']['sel_seal_id'] == $tblSealInfo['sel_seal_id']) {
						$checked = "checked=\"checked\"";
					}

					$resBuf .= "<div class=\"seal\">";
					$resBuf .= "<h5>";
					$resBuf .= "<input type=\"radio\" name=\"update[tbl_seal][sel_seal_id]\" id=\"".$id."\" value=\"".$tblSealInfo['sel_seal_id']."\" ".$checked." />";
					$resBuf .= "<label for=\"".$id."\">";
					$resBuf .= $tblSealInfo['sel_name'];
					$resBuf .= "&nbsp;";
					$resBuf .= $selPriceShow;
					$resBuf .= "</label>";
					$resBuf .= "</h5>";

					$resBuf .= "<img src=\"../img/seal/".sprintf('%03d', $tblSealInfo['sel_seal_id']).".jpg\" alt=\"".htmlspecialchars($tblSealInfo['sel_name'])."\" />";

					$resBuf .= "<p>";
					$resBuf .= nl2br($tblSealInfo['sel_explanation']);
					$resBuf .= "</p>";

					if (!_IsNull($planExplanation)) {
						$resBuf .= "<p class=\"sealset_plan\">";
						$resBuf .= $planExplanation;
						$resBuf .= "</p>";
					}

					$resBuf .= "<div class=\"seal_end\"></div>";

					$resBuf .= "</div><!-- End seal -->";//<!-- End seal -->

					if (!_IsNull($resBufSet)) $resBufSet .= "\n";
					$resBufSet .= $resBuf;
				}

				if (!_IsNull($resBufSet)) {
					$resBuf = null;
					$resBuf .= "<div class=\"sealset\" id=\"seal_set_id_".$mstSealSetInfo['id']."\">";
					$resBuf .= "<h4>";
					$resBuf .= $mstSealSetInfo['name'];
					$resBuf .= "</h4>";
					$resBuf .= $resBufSet;
					$resBuf .= "</div><!-- End sealset -->";//<!-- End sealset -->

					if (!_IsNull($res)) $res .= "\n";
					$res .= $resBuf;
				}
			}

			break;
		case 2:
			$resBufSet = null;

			$tblSealInfo = null;
			if (isset($info['update']['tbl_seal']['sel_seal_id']) && !_IsNull($info['update']['tbl_seal']['sel_seal_id'])) {
				if (isset($tblSealList[$info['update']['tbl_seal']['sel_seal_id']])) {
					$tblSealInfo = $tblSealList[$info['update']['tbl_seal']['sel_seal_id']];

					$resBuf = null;

					//商品価格
					$selPriceShow = null;
					$selPrice = $tblSealInfo['sel_price'];
//					if (!_IsNull($selPrice)) $selPriceShow = "￥".number_format($selPrice)."- (消費税・送料・代引手数料込み)";
					if (!_IsNull($selPrice)) $selPriceShow = "￥".number_format($selPrice)."- (消費税込み)";

					$resBuf .= "<div class=\"seal\">";
					$resBuf .= "<h5>";
					$resBuf .= $tblSealInfo['sel_name'];
					$resBuf .= "&nbsp;";
					$resBuf .= $selPriceShow;
					$resBuf .= "</h5>";

					$resBuf .= "<img src=\"../img/seal/".sprintf('%03d', $tblSealInfo['sel_seal_id']).".jpg\" alt=\"".htmlspecialchars($tblSealInfo['sel_name'])."\" />";

					$resBuf .= "<p>";
					$resBuf .= nl2br($tblSealInfo['sel_explanation']);
					$resBuf .= "</p>";

					$resBuf .= "<div class=\"seal_end\"></div>";

					$resBuf .= "</div><!-- End seal -->";//<!-- End seal -->

					$resBufSet .= $resBuf;
				}
			}
			if (_IsNull($resBufSet)) {
				$resBuf = null;
				$resBuf .= "<div class=\"requiredMessage\">";
				$resBuf .= "印鑑を選択してください。";
				$resBuf .= "</div>";

				if (!_IsNull($res)) $res .= "\n";
				$res .= $resBuf;
			} else {
				$resBuf = null;
				$resBuf .= "<div class=\"sealset\" id=\"seal_set_id_".$tblSealInfo['sel_seal_set_id']."\">";
				$resBuf .= "<h4>";
				$resBuf .= $mstSealSetList[$tblSealInfo['sel_seal_set_id']]['name'];
				$resBuf .= "</h4>";
				$resBuf .= $resBufSet;
				$resBuf .= "</div><!-- End sealset -->";//<!-- End sealset -->

				if (!_IsNull($res)) $res .= "\n";
				$res .= $resBuf;
			}
			break;
		case 3:
			break;
	}

	return $res;
}


?>
