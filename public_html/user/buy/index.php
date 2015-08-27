<?php
/*
 * [新★会社設立.JP ツール]
 * ご利用料金のお支払いページ
 *
 * 更新履歴：2010/12/21	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/user/buy/index.php] start.");


_Log("[/user/buy/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/buy/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/buy/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/buy/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


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
_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../../common/temp_html/temp_base.txt';
_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


//$tempSidebarLoginFile = '../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"が存在する場合、表示する。
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
//} else {
//	//取得できなかった場合
//	_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
//}

$tempSidebarUserMenuFile = '../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("[/user/buy/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_BUY;

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


_Log("[/user/buy/index.php] \$_GET(詰め替え後) = '".print_r($_GET,true)."'");

//パラメーターを取得する。
$xmlName = XML_NAME_BUY;//XMLファイル名を設定する。
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


		_Log("[/user/buy/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/buy/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/user/buy/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/user/buy/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
				_Log("[/user/buy/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

				$id = null;

				//自分のユーザー情報のみ表示する。
				//ユーザーIDを検索する。
				$id = $loginInfo['usr_user_id'];

				_Log("[/user/buy/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");
				break;
		}


		//入力値を取得する。
		$info = $_POST;
		_Log("[/user/buy/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/user/buy/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

		//「半角カタカナ」を「全角カタカナ」に変換する。→メールで半角カナが文字化けするので。
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/buy/index.php] POST(「半角カタカナ」を「全角カタカナ」に変換する。) = '".print_r($info,true)."'");


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



		_Log("[/user/buy/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/buy/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/user/buy/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/user/buy/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
				_Log("[/user/buy/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

				$id = null;
				$undeleteOnly4def = true;

				//自分のユーザー情報のみ表示する。
				//ユーザーIDを検索する。
				$id = $loginInfo['usr_user_id'];


				_Log("[/user/buy/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");

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

		//遷移元ページをセッションに保存する。
		$_SESSION[SID_PAY_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/user/buy/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/user/buy/index.php] XMLファイル名 = '".$xmlName."'");
_Log("[/user/buy/index.php] ターゲットID = '".$id."'");


//ユーザー情報(ログイン情報)を設定する。→DB更新に使用する。
$info['update']['tbl_user'] = $loginInfo;

//ご利用料金のお支払い情報が未設定の場合、ユーザー情報(ログイン情報)を初期値として設定する。
if (!isset($info['update']['tbl_buy'])) {
//	$info['update']['tbl_pay']['pay_tel1'] = $loginInfo['usr_tel1'];
//	$info['update']['tbl_pay']['pay_tel2'] = $loginInfo['usr_tel2'];
//	$info['update']['tbl_pay']['pay_tel3'] = $loginInfo['usr_tel3'];
//
//	$info['update']['tbl_pay']['pay_e_mail'] = $loginInfo['usr_e_mail'];
//	$info['update']['tbl_pay']['pay_e_mail_confirm'] = $loginInfo['usr_e_mail'];
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
//_Log("[/user/buy/index.php] ステップID = '".$step."'");
//_Log("[/user/buy/index.php] XMLファイル名(ステップID) = '".$xmlName."'");
//
////戻るボタンが押された場合→すぐ遷移するので、XMLは読み込まない。
//if ($_POST['back'] != "") $xmlName = null;


//システムコースマスタ
$condition4Mst = array();
$condition4Mst['plan_id'] = $loginInfo['usr_plan_id'];		//プランID
$undeleteOnly4Mst = true;
$order4Mst = "lpad(show_order,10,'0'),id";
$mstSystemCourseList = _DB_GetList('mst_system_course', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
if (!_IsNull($mstSystemCourseList)) {
	$bufList = array();
	foreach ($mstSystemCourseList as $mKey => $mInfo) {
		$bufList[$mInfo['company_type_id']][$mInfo['id']] = $mInfo;
	}
	$mstSystemCourseList = $bufList;
}

//if (!_IsNull($mstSystemCourseList)) {
//	foreach ($mstSystemCourseList as $mKey => $mInfo) {
//		$name = null;
//		$name .= $mInfo['name'];
//		if (!_IsNull($mInfo['price'])) {
//			$name .= " ";
//			$name .= "￥";
//			$name .= number_format($mInfo['price']);
//		}
//		$mInfo['name_price'] = $name;
//
//		$nameTag = null;
//		$nameTag = $name;
//		if (!_IsNull($mInfo['comment1'])) {
//			$name .= " ";
//			$name .= $mInfo['comment1'];
//
//			$nameTag .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
//			$nameTag .= "<span class=\"input_comment\">";
//			$nameTag .= $mInfo['comment1'];
//			$nameTag .= "</span>";
//		}
//
//		$tag = null;
//		$tag .= $mInfo['tag'];
//		if (!_IsNull($mInfo['price'])) {
//			$tag .= " ";
//			$tag .= "￥";
//			$tag .= number_format($mInfo['price']);
//		}
//		$mInfo['tag_price'] = $tag;
//
//		$mInfo['name_price_comment'] = $name;
//		$mInfo['name_price_comment_tag'] = $nameTag;
//		$mstSystemCourseList[$mKey] = $mInfo;
//	}
//}



//会社IDを検索する。
//ユーザー_会社_関連付テーブルを検索する。
$undeleteOnly = true;
$condition = array();
$condition['usr_cmp_rel_user_id'] = $id;		//ユーザーID
$order = "usr_cmp_rel_company_id";				//ソート順=会社IDの昇順(なんでもいいけど…)
$tblUserCompanyRelationList = _DB_GetListByAssociative('tbl_user_company_relation', 'usr_cmp_rel_company_id', null, $condition, $undeleteOnly, $order, 'usr_cmp_rel_del_flag');
$bufList = array();

$condition = array('usr_sts_user_id' => $id);
$tblUserStatusList = _DB_GetList('tbl_user_status', $condition);
_Log('***** 取得したステータス情報 *****');
_Log(print_r($tblUserStatusList, true));
$companyPaymentInfo = array();
if (!_IsNull($tblUserStatusList)) {
    foreach ($tblUserStatusList as $tblUserStatus) {
        $key = $tblUserStatus['usr_sts_company_id'] . '_' . $tblUserStatus['usr_sts_system_course_id'];
        $companyPaymentInfo[$key] = $tblUserStatus['usr_sts_pay_status_id'];
    }
}
_Log('***** 取得した支払情報 *****');
_Log(print_r($companyPaymentInfo, true));

if (!_IsNull($tblUserCompanyRelationList)) {
	//会社テーブルを検索する。
	$condition = array();
	$condition['cmp_company_id'] = $tblUserCompanyRelationList;			//会社ID
	$order = "cmp_company_type_id";										//ソート順=会社タイプIDの昇順
	$order .= ",cmp_company_id desc";									//ソート順=会社IDの降順
	$tblCompanyList = _DB_GetList('tbl_company', $condition, $undeleteOnly, $order, 'cmp_del_flag', 'cmp_company_id');
	if (!_IsNull($tblCompanyList)) {
		foreach ($tblCompanyList as $tcKey => $tblCompanyInfo) {
			if (isset($mstSystemCourseList[$tblCompanyInfo['cmp_company_type_id']])) {
				foreach ($mstSystemCourseList[$tblCompanyInfo['cmp_company_type_id']] as $mKey => $mInfo) {
					//会社ID+システムコースID
					$newId = $tblCompanyInfo['cmp_company_id']."_".$mInfo['id'];

                    // すでに入金済みの場合
                    if (!empty($companyPaymentInfo[$newId]) && $companyPaymentInfo[$newId] != '1') {
                        continue;
                    }

					$mInfo['id'] = $newId;

					$companyName = "<商号(会社名)が未設定>";
					$companyNameTag = "<strong>&lt;商号(会社名)が未設定&gt;</strong>";
					if (!_IsNull($tblCompanyInfo['cmp_company_name'])) {
						$companyName = $tblCompanyInfo['cmp_company_name'];
						$companyNameTag = "<strong>".$tblCompanyInfo['cmp_company_name']."</strong>";
					}

					$name = null;
					$name .= $companyName;
					$name .= "：";
					$name .= $mInfo['name'];
					if (!_IsNull($mInfo['price'])) {
						$name .= " ";
						$name .= "￥";
						$name .= number_format($mInfo['price']);
					}
					$mInfo['name_price'] = $name;
			
					$nameTag = null;
					$nameTag = $name;
					if (!_IsNull($mInfo['comment1'])) {
						$name .= " ";
						$name .= $mInfo['comment1'];
			
						$nameTag .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						$nameTag .= "<span class=\"input_comment\">";
						$nameTag .= $mInfo['comment1'];
						$nameTag .= "</span>";
					}
			
					$tag = null;
					$tag .= $companyNameTag;
					$tag .= "：<br />";
					$tag .= "　　　　";
					$tag .= $mInfo['tag'];
					if (!_IsNull($mInfo['price'])) {
						$tag .= " ";
						$tag .= "￥";
						$tag .= number_format($mInfo['price']);
					}
					$mInfo['tag_price'] = $tag;
			
					$mInfo['name_price_comment'] = $name;
					$mInfo['name_price_comment_tag'] = $nameTag;
					$bufList[$newId] = $mInfo;
				}
			}
		}
	}
}
if (count($bufList) > 0) {
	$mstSystemCourseList = $bufList;
} else {
	$mstSystemCourseList = null;
}

if (_IsNull($mstSystemCourseList)) {
	$message = "「株式会社設立情報」又は、「合同会社設立情報」をご登録してください。\n";
	$message .= "「商号(会社名)」のみでも結構ですので最初にご登録ください。\n";
	$message .= "ご登録後に以下の「ご利用コース」が表示されます。\n";
	$errorFlag = true;
}




$xmlList = null;
if (!_IsNull($xmlName)) {


	$otherList = null;
	$otherList = array(
		'mst_system_course' => $mstSystemCourseList
	);

	//XMLを読み込む。
	$xmlFile = "../../common/form_xml/".$xmlName.".xml";
	_Log("[/user/buy/index.php] XMLファイル = '".$xmlFile."'");
	$xmlList = _GetXml($xmlFile, $otherList);

	_Log("[/user/buy/index.php] XMLファイル配列 = '".print_r($xmlList,true)."'");

//	switch ($xmlName) {
//		case XML_NAME_SEAL_ALL:
//			//法人印注文情報[入力内容確認]
//
//			//全てのXMLを読み込む。
//
//			//法人印注文情報[印鑑](確認画面用)
//			$bufXmlFile = "../../common/form_xml/".XML_NAME_SEAL_SET_4_CONFIRM.".xml";
//			_Log("[/user/buy/index.php] XMLファイル = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal'] = $bufXmlList['tbl_seal'];
//
//			//法人印注文情報[印影]
//			$bufXmlFile = "../../common/form_xml/".XML_NAME_SEAL_IMPRINT.".xml";
//			_Log("[/user/buy/index.php] XMLファイル = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal_imprint'] = $bufXmlList['tbl_seal_imprint'];
//
//			///法人印注文情報[会社名・お届け先]
//			$bufXmlFile = "../../common/form_xml/".XML_NAME_SEAL_NAME.".xml";
//			_Log("[/user/buy/index.php] XMLファイル = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal_name'] = $bufXmlList['tbl_seal_name'];
//			$xmlList['tbl_seal_deliver'] = $bufXmlList['tbl_seal_deliver'];
//
//
//			_Log("[/user/buy/index.php] XMLファイル配列(全XMLマージ後) = '".print_r($xmlList,true)."'");
//			_Log("[/user/buy/index.php] 法人印注文情報(全XMLマージ後) = '".print_r($info,true)."'");
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

				$body .= _CreateMailAll($xmlList, $info);//※この時点では、$infoに「利用規約」の入力値は削除されている。→メールには使えない。

				_Log("[/user/buy/index.php] メール本文(_CreateMailAll) = '".$body."'");


				$body .= "\n";
				$body .= "\n";
				$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
				$body .= "お支払方法について\n";
				$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
				switch ($info['update']['tbl_buy']['buy_pay_means_id']) {
					case MST_PAY_MEANS_ID_BANK:
						//銀行振込
						$body .= "■振込先：";
						$body .= "\n";
						$body .= COMPANY_BANK_ACCOUNT_BANK_NAME;
						$body .= "\n";
						$body .= COMPANY_BANK_ACCOUNT_BRANCH_NAME;
						$body .= "\n";
						$body .= COMPANY_BANK_ACCOUNT_TYPE;
						$body .= " ";
						$body .= COMPANY_BANK_ACCOUNT_NUMBER;
						$body .= "\n";
						$body .= COMPANY_BANK_ACCOUNT_NAME;
						$body .= "\n";
						$body .= "\n";

//						$body .= "■振込名義：";
//						$body .= "\n";
//						$body .= $info['update']['tbl_user']['usr_user_id'];
//						$body .= " ";
//						$body .= $info['update']['tbl_user']['usr_family_name_kana'];
//						$body .= $info['update']['tbl_user']['usr_first_name_kana'];
//						$body .= "\n";
//						$body .= "\n";
//						$body .= "※お振込の際は、振込名義の前にユーザーIDをつけてください。(ユーザID + お名前)";
//						$body .= "\n";
//						$body .= "※振込人名義が、会員登録のお名前と異なる場合は必ず「お支払い報告」をお願いします。";
//						$body .= "\n";
//						$body .= "ログイン後の「会員メニュー」の「お支払い報告」からご連絡ください。";
//						$body .= "\n";
//						$body .= "\n";
						break;
					case MST_PAY_MEANS_ID_CARD:
						//クレジットカード
						$body .= COMPANY_CARD_COMMENT;
						$body .= "\n";
						$body .= "\n";
						break;
				}

				$totalPrice = 0;
				$totalPriceComment = null;
				foreach ($info['update']['tbl_buy']['buy_system_course_id'] as $systemCourseId) {
					if (isset($mstSystemCourseList[$systemCourseId]['price']) && !_IsNull($mstSystemCourseList[$systemCourseId]['price'])) {
						$totalPrice += $mstSystemCourseList[$systemCourseId]['price'];
					}
					switch ($systemCourseId) {
						case MST_SYSTEM_COURSE_ID_CMP_ENTRUST://[株式会社] 電子定款お任せコース
						case MST_SYSTEM_COURSE_ID_LLC_ENTRUST://[合同会社] 電子定款お任せコース
							$totalPriceComment = SYSTEM_COURSE_COMMENT;
							break;
					}
				}
				$body .= "■お支払料金：";
				$body .= "\n";
				$body .= "￥".number_format($totalPrice);
				$body .= "\n";
				if (!_IsNull($totalPriceComment)) {
					$body .= "\n";
					$body .= $totalPriceComment;
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

				$body .= "ご利用料金のお支払い日時：".date("Y年n月j日 H時i分")."\n";
				$body .= $_SERVER["REMOTE_ADDR"]."\n";

				//管理者用メール本文を設定する。
				$adminBody = "";
				//$adminBody .= $siteTitle." \n";
				//$adminBody .= "\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "『".$siteTitle."』にご利用料金のお支払いが入りました。\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "\n";
				$adminBody .= $body;

				//問合せフォーム-GoogleDoc連携
				include_once("http://www.sin-kaisha.jp/admin/common/request.ini");
				$adminBody .= "\n";
				$adminBody .= "\n";
				$adminBody .= "googlegooglegooglegooglegooglegooglegooglegooglegooglegoogle\n";
				$adminBody .= "\n";

				$info4Req = $info;
				_Log("[/user/buy/index.php] {ID分割} 入力情報(編集前) = '".print_r($info4Req,true)."'");
				if (isset($info4Req['update']['tbl_buy']['buy_system_course_id'])) {
					foreach ($info4Req['update']['tbl_buy']['buy_system_course_id'] as $scKey => $systemCourseId) {
						$bufIdList = explode("_", $systemCourseId);
						$bufCompanyId = $bufIdList[0];
						$bufSystemCourseId = $bufIdList[1];
						$info4Req['update']['tbl_buy']['buy_system_course_id'][$scKey] = $bufSystemCourseId;
						_Log("[/user/buy/index.php] {ID分割} 会社ID + システムコースID = '".$systemCourseId."'");
						_Log("[/user/buy/index.php] {ID分割} 会社ID = '".$bufCompanyId."'");
						_Log("[/user/buy/index.php] {ID分割} システムコースID = '".$bufSystemCourseId."'");
					}
				}
				_Log("[/user/buy/index.php] {ID分割} 入力情報(編集後) = '".print_r($info4Req,true)."'");
				$adminBody .= _SetGoogleDocRequest(1, $info4Req);

				//お客様用メール本文を設定する。
				$customerBody = "";
				$customerBody .= $info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." 様\n";
				$customerBody .= "\n";
//				$customerBody .= "**************************************************************************************\n";
//				$customerBody .= "この度は、『".$siteTitle."』にご利用料金のお支払いをしていただきありがとうございました。\n";
//				$customerBody .= "確認のため、下記にお客様のご登録の内容をお知らせいたします。\n";
//				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "この度は、『".$siteTitle."』をご利用いただきありがとうございます。\n";
				$customerBody .= "下記に、お支払いについてのご案内をお知らせいたします。\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "\n";
				$customerBody .= $body;


				//管理者用タイトルを設定する。
				$adminTitle = "[".$siteTitle."] ご利用料金のお支払い (".$info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." 様)";
				//お客様用タイトルを設定する。
//				$customerTitle = "[".$siteTitle."] ご利用料金のお支払いありがとうございました";
				$customerTitle = "[".$siteTitle."] ご利用料金のお支払いについて";

				mb_language("Japanese");
				
				$parameter = "-f ".$clientMail;

				//メール送信
				//お客様に送信する。
				$rcd = mb_send_mail($info['update']['tbl_user']['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

				//クライアントに送信する。
				$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_user']['usr_e_mail']);

				//マスターに送信する。
				foreach($masterMailList as $masterMail){
					$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_user']['usr_e_mail']);
				}


				//メッセージを設定する。
				$message .= $info['update']['tbl_user']['usr_family_name']."&nbsp;".$info['update']['tbl_user']['usr_first_name'];
				$message .= "&nbsp;様";
				$message .= "\n";
				$message .= "\n";
//				$message .= "この度は、『".$siteTitle."』にご利用料金のお支払いをしていただきありがとうございました。";
//				$message .= "\n";
//				$message .= "お客様のメールアドレス宛てにご登録内容の「確認メール」が自動送信されました。";
				$message .= "この度は、『".$siteTitle."』をご利用いただきありがとうございます。";
				$message .= "\n";
				$message .= "お客様のメールアドレス宛てに、お支払いについてご案内のメールを送信させていただきました。";
				$message .= "\n";
				$message .= "入金を確認次第、全ての機能をお使いいただけるように致します。";
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
	//					$message .= "<a href=\"../../item/?back\" title=\"商品一覧に戻る\">[商品一覧に戻る]</a>\n";
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
	//							$message .= "<a href=\"../../inquiry_price/?back\" title=\"請求額一覧に戻る\">[請求額一覧に戻る]</a>\n";
	//							break;
	//						default:
	//							$message .= "<a href=\"../../inquiry/?back\" title=\"問合せ一覧に戻る\">[問合せ一覧に戻る]</a>\n";
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
_Log("[/user/buy/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/user/buy/index.php] mode = '".$mode."'");




//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "../..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../img/maincontent/pt_buy.jpg\" title=\"\" alt=\"ご利用料金のお支払い\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

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

$paypalInfo = <<<EOT
------------------------------------------
カード決済(PayPal)お手続き方法
------------------------------------------
1、以下の「今すぐ購入」ボタンを押してください。
PayPalのお支払用ページが別ウィンドウで表示されます。
お支払用ページには「加藤公認会計士事務所」と表示されています。

2、PayPalのお支払用ページが表示されますと、
既にお支払いいただく金額が入力されていますので、引き続きお支払い手続きを完了してください。
(PayPalのご利用が初めての場合も、決済時に登録することができます)

3、決済が完了しますと、メールにて通知が到着いたします。
EOT;

$paypalSubmit =<<<EOT
function paypalSubmit () {
    var price = 0;
    jQuery("input[type=checkbox]:checked").each(function() {
        var subPrice = parseInt(jQuery(this).attr("_price"));
        price += subPrice;
    });
    console.log("total = " + price);

    if (price == 0) {
        alert("ご利用コースを選択してください。");
        return false;
    } else if (!confirm("決済処理を行います。よろしいですか？")) {
        return false;
    }

    var form = jQuery("#frmUpdate").attr({ method: "POST", action: "/user/buy/paypal/" });
    var hidden = jQuery("<input>").attr({ type: "hidden", name: "Payment_Amount", value: price });
    form.append(hidden);

    form.appendTo("body").submit();
}
EOT;

switch ($mode) {
	case 1:
		//スクリプトを設定する。
		$script .= "<script type=\"text/javascript\">";
		$script .= "\n";
		$script .= "<!--";
		$script .= "\n";
		$script .= "window.addEvent('domready', function(){";
		$script .= "\n";
		$script .= "$$('input.system_course').addEvent('click', function(e) {";
		$script .= "\n";
		$script .= "var price = (0).toInt();";
		$script .= "\n";
		$script .= "var test = '';";
		$script .= "\n";

		$script .= "$$('input.system_course').each(function(el){";
		$script .= "\n";
		$script .= "test += 'name=' + el.get('name') + '/value=' + el.get('value') + '/checked=' + el.get('checked') + '/_price=' + el.get('_price') + '\\n';";
		$script .= "\n";
		$script .= "if (el.get('checked')) {";
		$script .= "\n";
		$script .= "price += el.get('_price').toInt();";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
		$script .= "});";
		$script .= "\n";

		$script .= "if (\$defined($('res_price'))) {";
		$script .= "\n";
		$script .= "$('res_price').set('html', price);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

//		$script .= "alert(test);";
//		$script .= "\n";
//		$script .= "alert(price);";
//		$script .= "\n";

		$script .= "});";
		$script .= "\n";

		$script .= "var price = (0).toInt();";
		$script .= "\n";
		$script .= "var test = '';";
		$script .= "\n";

		$script .= "$$('input.system_course').each(function(el){";
		$script .= "\n";
		$script .= "test += 'name=' + el.get('name') + '/value=' + el.get('value') + '/checked=' + el.get('checked') + '/_price=' + el.get('_price') + '\\n';";
		$script .= "\n";
		$script .= "if (el.get('checked')) {";
		$script .= "\n";
		$script .= "price += el.get('_price').toInt();";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
		$script .= "});";
		$script .= "\n";

		$script .= "if (\$defined($('res_price'))) {";
		$script .= "\n";
		$script .= "$('res_price').set('html', price);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

//		$script .= "alert(test);";
//		$script .= "\n";
//		$script .= "alert(price);";
//		$script .= "\n";

		$script .= "$$('input.pay_means').addEvent('click', function(e) {";
		$script .= "\n";

		$script .= "var test = '';";
		$script .= "\n";
		$script .= "$$('input.pay_means').each(function(el){";
		$script .= "\n";
		$script .= "test += 'name=' + el.get('name') + '/value=' + el.get('value') + '/checked=' + el.get('checked') + '' + '\\n';";
		$script .= "\n";

		$script .= "if (\$defined($('pay_means_' + el.get('value')))) {";
		$script .= "\n";
		$script .= "if (el.get('checked')) {";
		$script .= "\n";
		$script .= "$('pay_means_' + el.get('value')).setStyle('display', 'block');";
		$script .= "\n";
		$script .= "} else {";
		$script .= "\n";
		$script .= "$('pay_means_' + el.get('value')).setStyle('display', 'none');";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		$script .= "});";
		$script .= "\n";

//		$script .= "alert(test);";
//		$script .= "\n";

		$script .= "});";
		$script .= "\n";

		$script .= "var test = '';";
		$script .= "\n";

		$script .= "$$('input.pay_means').each(function(el){";
		$script .= "\n";
		$script .= "test += 'name=' + el.get('name') + '/value=' + el.get('value') + '/checked=' + el.get('checked') + '' + '\\n';";
		$script .= "\n";

		$script .= "if (\$defined($('pay_means_' + el.get('value')))) {";
		$script .= "\n";
		$script .= "if (el.get('checked')) {";
		$script .= "\n";
		$script .= "$('pay_means_' + el.get('value')).setStyle('display', 'block');";
		$script .= "\n";
		$script .= "} else {";
		$script .= "\n";
		$script .= "$('pay_means_' + el.get('value')).setStyle('display', 'none');";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		$script .= "});";
		$script .= "\n";

//		$script .= "alert(test);";
//		$script .= "\n";

		$script .= "var pay_means_comment_1 = '';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '■振込先';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '".COMPANY_BANK_ACCOUNT_BANK_NAME."';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '".COMPANY_BANK_ACCOUNT_BRANCH_NAME."';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '".COMPANY_BANK_ACCOUNT_TYPE."';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '".COMPANY_BANK_ACCOUNT_NUMBER."';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '".COMPANY_BANK_ACCOUNT_NAME."';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '※振込み手数料はお客様にてご負担ください。';";
		$script .= "\n";
		$script .= "if (\$defined($('pay_means_".MST_PAY_MEANS_ID_BANK."'))) {";
		$script .= "\n";
		$script .= "$('pay_means_".MST_PAY_MEANS_ID_BANK."').set('html', pay_means_comment_1);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		$script .= "var pay_means_comment_2 = '';";
		$script .= "\n";

        foreach (explode(PHP_EOL, $paypalInfo) as $line) {
            $script .= "pay_means_comment_2 += '" . $line . "';";
            $script .= PHP_EOL;
            $script .= "pay_means_comment_2 += '<br/>';";
            $script .= PHP_EOL;
        }

        $script .= "pay_means_comment_2 += '<br/>';";
        $script .= PHP_EOL;
        $script .= "pay_means_comment_2 += '<div>';";
        $script .= PHP_EOL;
        $script .= "pay_means_comment_2 += '<input onclick=\"paypalSubmit(); return false;\"  type=\"image\" src=\"https://www.paypalobjects.com/ja_JP/JP/i/btn/btn_buynowCC_LG.gif\" border=\"0\" name=\"submit\" alt=\"PayPal - オンラインでより安全・簡単にお支払い\" style=\"background-color: #ffffff; border: none;\">';";
        $script .= PHP_EOL;
        $script .= "pay_means_comment_2 += '<img alt=\"\" border=\"0\" src=\"https://www.paypalobjects.com/ja_JP/i/scr/pixel.gif\" width=\"1\" height=\"1\">';";
        $script .= PHP_EOL;
        $script .= "pay_means_comment_2 += '</div>';";
        $script .= PHP_EOL;

//		$script .= "pay_means_comment_2 += '■PayPalお手続き方法 ';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '1.お支払方法に「クレジットカード(PayPal)」を選択してください。';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '↓';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '2.PayPalでお支払いいただくURLをメールにてお知らせいたします。';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '↓';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '3.メール内に記載されたURLにアクセスしていただきますと、既にお支払いいただく金額が入力されていますので、引き続きお支払い手続きを完了してください。 (PayPalのご利用が始めての場合も、決済時に登録することができます。) ';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '↓';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '4.決済が完了しますと、メールにて通知が到着いたします。 ';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '↓';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '5.決済の完了が確認でき次第、システムのご利用が可能です。';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '※カード決算手数料が別途1万円かかります。';";
//		$script .= "\n";

		$script .= "if (\$defined($('pay_means_".MST_PAY_MEANS_ID_CARD."'))) {";
		$script .= "\n";
		$script .= "$('pay_means_".MST_PAY_MEANS_ID_CARD."').set('html', pay_means_comment_2);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		$script .= "});";
		$script .= "\n";
		$script .= "//-->";
		$script .= "\n";

        foreach (explode(PHP_EOL, $paypalSubmit) as $line) {
            $script .= $line;
            $script .= PHP_EOL;
        }

		$script .= "</script>";
		$script .= "\n";
		break;
	case 2:

		$price = 0;
		foreach ($info['update']['tbl_buy']['buy_system_course_id'] as $systemCourseId) {
			if (isset($mstSystemCourseList[$systemCourseId]['price']) && !_IsNull($mstSystemCourseList[$systemCourseId]['price'])) {
				$price += $mstSystemCourseList[$systemCourseId]['price'];
			}
		}

		//スクリプトを設定する。
		$script .= "<script type=\"text/javascript\">";
		$script .= "\n";
		$script .= "<!--";
		$script .= "\n";
		$script .= "window.addEvent('domready', function(){";
		$script .= "\n";

		$script .= "var price = '".number_format($price)."';";
		$script .= "\n";
		$script .= "if (\$defined($('res_price'))) {";
		$script .= "\n";
		$script .= "$('res_price').set('html', price);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		$script .= "});";
		$script .= "\n";
		$script .= "//-->";
		$script .= "\n";
		$script .= "</script>";
		$script .= "\n";
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
$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);

$sidebar .= $htmlSidebarUserMenu;


//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_BUY, '', PAGE_TITLE_BUY, 3);
//パンくずリストを取得する。
$breadcrumbs = _GetBreadcrumbs();

//WOOROMフッター管理
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=".$_SERVER['SERVER_NAME']."&php_self=".$_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
	$wooromFooter = null;
}

$script2 =<<<EOT
<script>
    jQuery(function() {
        jQuery(".pay_means").on("change", function() {
            var value = jQuery(this).prop("value");
            console.log(value);
            if (value == "1") {
                jQuery("#frm_button").css("visibility", "visible");
            } else {
                jQuery("#frm_button").css("visibility", "hidden");
            }
        });
    });
</script>
EOT;

$script .= $script2;

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


_Log("[/user/buy/index.php] end.");
echo $html;

?>
