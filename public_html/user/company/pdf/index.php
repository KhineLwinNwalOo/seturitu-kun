<?php
/*
 * [新★会社設立.JP ツール]
 * 各種申請書類 印刷ページ
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
_Log("[/user/company/pdf/index.php] start.");


_Log("[/user/company/pdf/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/company/pdf/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/company/pdf/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/company/pdf/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


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
_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../../../common/temp_html/temp_base.txt';
_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


//$tempSidebarLoginFile = '../../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"が存在する場合、表示する。
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
//} else {
//	//取得できなかった場合
//	_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
//}

$tempSidebarUserMenuFile = '../../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("[/user/company/pdf/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_COMPANY_PDF;



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

//パラメーターを取得する。
$xmlName = null;//XMLファイル名を設定する。
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


		_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
				_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

				$id = null;

				//自分のユーザー情報のみ表示する。
				//ユーザーIDを検索する。
				$id = $loginInfo['usr_user_id'];

				_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");
				break;
		}


		//入力値を取得する。
		$info = $_POST;
		_Log("[/user/company/pdf/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/user/company/pdf/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

		//「半角カタカナ」を「全角カタカナ」に変換する。→メールで半角カナが文字化けするので。
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/company/pdf/index.php] POST(「半角カタカナ」を「全角カタカナ」に変換する。) = '".print_r($info,true)."'");


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



		_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");


		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
				_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

				$id = null;
				$undeleteOnly4def = true;

				//自分のユーザー情報のみ表示する。
				//ユーザーIDを検索する。
				$id = $loginInfo['usr_user_id'];


				_Log("[/user/company/pdf/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");

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

_Log("[/user/company/pdf/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/user/company/pdf/index.php] XMLファイル名 = '".$xmlName."'");
_Log("[/user/company/pdf/index.php] ターゲットID = '".$id."'");


//ユーザーIDに関連する会社IDを検索する。
$companyId = _GetRelationCompanyId($id, $undeleteOnly4def);


//ユーザー情報(ログイン情報)を設定する。→DB更新に使用する。
$info['update']['tbl_user'] = $loginInfo;

////定款認証情報が未設定の場合、ユーザー情報(ログイン情報)を初期値として設定する。
//if (!isset($info['update']['tbl_article_deliver'])) {
//	$info['update']['tbl_article_deliver']['art_dlv_tel1'] = $loginInfo['usr_tel1'];
//	$info['update']['tbl_article_deliver']['art_dlv_tel2'] = $loginInfo['usr_tel2'];
//	$info['update']['tbl_article_deliver']['art_dlv_tel3'] = $loginInfo['usr_tel3'];
//
//	$info['update']['tbl_article_deliver']['art_dlv_e_mail'] = $loginInfo['usr_e_mail'];
//	$info['update']['tbl_article_deliver']['art_dlv_e_mail_confirm'] = $loginInfo['usr_e_mail'];
//
//	$info['update']['tbl_article_deliver']['art_dlv_family_name'] = $loginInfo['usr_family_name'];
//	$info['update']['tbl_article_deliver']['art_dlv_first_name'] = $loginInfo['usr_first_name'];
//
//	$info['update']['tbl_article_charge']['art_chg_family_name'] = $loginInfo['usr_family_name'];
//	$info['update']['tbl_article_charge']['art_chg_first_name'] = $loginInfo['usr_first_name'];
//}


$xmlList = null;
if (!_IsNull($xmlName)) {
}


//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/company/pdf/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/user/company/pdf/index.php] mode = '".$mode."'");




//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "../../..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../../img/maincontent/pt_user_company_pdf.jpg\" title=\"\" alt=\"各種申請書類 印刷\">";
$maincontent .= "</h2>";
$maincontent .= "\n";



//入金チェック
//{treu:エラー有り/false:エラー無し}
$userStatusSystemErrorFlag = false;			//システム利用料金
//[株式会社] 株式会社設立 (システム利用料金)
if (!_CheckUserStatus($id, $companyId, MST_SYSTEM_COURSE_ID_CMP)) {
	$userStatusSystemErrorFlag = true;

	$maincontent .= "<div id=\"system_course_system\" class=\"message payMessage\">";
	$maincontent .= "\n";
	$maincontent .= "※申し訳ございません。書類の作成(印刷)は、ご利用料金の決済後にご利用が可能となります。";
	$maincontent .= "<br />";
	$maincontent .= "<br />";
	$maincontent .= "<a href=\"../../buy/\">お支払いはこちら</a>";
	$maincontent .= "\n";
	$maincontent .= "</div>";
	$maincontent .= "\n";
}



//印刷
$buf = _CreateTableInput4Pdf($mode, $xmlList, $info, $tabindex);
$maincontent .= "\n";
$maincontent .= $buf;


//スクリプトを設定する。
$script = null;

if ($userStatusSystemErrorFlag) {
	//スクリプトを設定する。
	$script .= "<script type=\"text/javascript\">";
	$script .= "\n";
	$script .= "<!--";
	$script .= "\n";
	$script .= "window.addEvent('domready', function(){";
	$script .= "\n";
	$script .= "$$('div.pdfset div.pdf div.output input').setStyle('display','none');";
	$script .= "\n";
	$script .= "$$('div.pdfset div.pdf div.output').setStyle('background','url(../../../img/pdf/pdf_btn_print_03.gif) no-repeat left top');";
	$script .= "\n";
	$script .= "});";
	$script .= "\n";
	$script .= "//-->";
	$script .= "\n";
	$script .= "</script>";
	$script .= "\n";
}

//スクリプトを設定する。
$script .= "<script type=\"text/javascript\">";
$script .= "\n";
$script .= "<!--";
$script .= "\n";
$script .= "window.addEvent('domready', function(){";
$script .= "\n";
$script .= "$$('select').addEvent('change', function(e) {";
$script .= "\n";
//$script .= "alert(this.get('name') + '/' + this.get('value'));";
//$script .= "\n";
$script .= "$$('input.' + this.get('name')).set('value', this.get('value'))";
$script .= "\n";
$script .= "});";
$script .= "\n";
$script .= "});";
$script .= "\n";
$script .= "//-->";
$script .= "\n";
$script .= "</script>";
$script .= "\n";




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
$htmlSidebarUserMenu = str_replace('{company_info}', _GetCompanyInfoHtml($loginInfo), $htmlSidebarUserMenu);

$sidebar .= $htmlSidebarUserMenu;


//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_COMPANY, '', PAGE_TITLE_COMPANY, 3);
_SetBreadcrumbs(PAGE_DIR_COMPANY_PDF, '', PAGE_TITLE_COMPANY_PDF, 4);
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


_Log("[/user/company/pdf/index.php] end.");
echo $html;



































/**
 * PDF印刷用
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
function _CreateTableInput4Pdf($mode, $xmlList, $info, &$tabindex) {


	$yearList = _GetYearArray(SYSTEM_START_YEAR - 1, date('Y') + 2);
	foreach ($yearList as $yearKey => $yearInfo) {
		$jpY = _ConvertAD2Jp($yearInfo['name']);
		$yearInfo['name'] .= "(".$jpY.")";
		$yearList[$yearKey] = $yearInfo;
	}
	$birthYearList = _GetYearArray(date('Y')-100, date('Y'));
	foreach ($birthYearList as $yearKey => $yearInfo) {
		$jpY = _ConvertAD2Jp($yearInfo['name']);
		$yearInfo['name'] .= "(".$jpY.")";
		$birthYearList[$yearKey] = $yearInfo;
	}


	$monthList = _GetMonthArray();
	$dayList = _GetDayArray();


	//ユーザーIDを取得する。
	$userId = $info['condition']['_id_'];


//	//ユーザーIDに関連する会社IDを検索する。
//	//ユーザー_会社_関連付テーブル
//	$condition = null;
//	$condition['usr_cmp_rel_user_id'] = $userId;
//	$order = null;
//	$order .= "usr_cmp_rel_company_id";		//ソート条件=会社IDの昇順
//	$tblUserCompanyRelationList = _DB_GetListByAssociative('tbl_user_company_relation', 'usr_cmp_rel_company_id', null, $condition, true, $order,'usr_cmp_rel_del_flag');
//	$tblCompanyInfo = null;
//	if (!_IsNull($tblUserCompanyRelationList)) {
//		//会社テーブル
//		$condition = null;
//		$condition['cmp_company_id'] = $tblUserCompanyRelationList;		//会社ID
//		$condition['cmp_company_type_id'] = MST_COMPANY_TYPE_ID_CMP;	//会社タイプID="株式会社"
//		$order = null;
//		$order .= "cmp_company_id";		//ソート条件=会社IDの昇順
//		$tblCompanyList = _DB_GetList('tbl_company', $condition, true, $order, 'cmp_del_flag');
//		if (!_IsNull($tblCompanyList)) {
//			//先頭を取得する。(1件のはず)
//			$tblCompanyInfo = $tblCompanyList[0];
//		}
//	}
//
//	//会社IDを取得する。
//	$companyId = null;
//	if (!_IsNull($tblCompanyInfo)) {
//		$companyId = $tblCompanyInfo['cmp_company_id'];
//	}

	//ユーザーIDに関連する会社IDを検索する。
	$companyId = _GetRelationCompanyId($userId);

	//ユーザーIDに関連する会社ID、会社情報を検索する。
	$companyInfo = null;
	$companyId = _GetRelationCompanyId($userId);
	if (!_IsNull($companyId)) {
		$companyInfo = _GetCompanyInfo($companyId);
	}

	$tblCompanyBoardInfo = null;
//	if (!_IsNull($tblCompanyInfo)) {
	if (!_IsNull($companyId)) {
		//会社_役員テーブル
		$condition = null;
		$condition['cmp_bod_company_id'] = $companyId;				//会社ID
		$condition['cmp_bod_post_id'] = MST_POST_ID_REP_DIRECTOR;	//役職ID="代表取締役"
		$order = null;
		$order .= "cmp_bod_no";		//ソート条件=役員Noの昇順
		$tblCompanyBoardList = _DB_GetList('tbl_company_board', $condition, true, $order, 'cmp_bod_del_flag');
		if (!_IsNull($tblCompanyBoardList)) {
			//先頭を取得する。("代表取締役"は1人のはず。)
			$tblCompanyBoardInfo = $tblCompanyBoardList[0];
		}
	}

	//都道府県マスタ
	$condition = null;
	$order = null;
	$order .= "lpad(show_order,10,'0')";	//ソート条件=表示順の昇順
	$order .= ",id";						//ソート条件=IDの昇順
	$mstPrefList = _DB_GetList('mst_pref', $condition, true, $order, 'del_flag', 'id');

	//法務局マスタ
	$condition = null;
	$order = null;
	$order .= "lpad(show_order,10,'0')";	//ソート条件=表示順の昇順
	$order .= ",id";						//ソート条件=IDの昇順
	$mstLegalAffairsBureauList = _DB_GetList('mst_legal_affairs_bureau', $condition, true, $order, 'del_flag', 'id');


	//各項目の初期値を設定する。

	//定款作成日フラグ
	$articleCreateFlag = false;
//	//定款作成日(年)
//	$articleCreateYear = date('Y');
//	//定款作成日(月)
//	$articleCreateMonth = date('n');
//	//定款作成日(日)
//	$articleCreateDay = date('j');

	//定款作成日(年)
	$articleCreateYear = null;
	//定款作成日(月)
	$articleCreateMonth = null;
	//定款作成日(日)
	$articleCreateDay = null;

	if (!_IsNull($companyInfo)) {
		//定款作成日(年)
		if (isset($companyInfo['tbl_company']['cmp_article_create_year']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_year'])) {
			$articleCreateYear = $companyInfo['tbl_company']['cmp_article_create_year'];
			$articleCreateFlag = true;
		}
		//定款作成日(月)
		if (isset($companyInfo['tbl_company']['cmp_article_create_month']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_month'])) {
			$articleCreateMonth = $companyInfo['tbl_company']['cmp_article_create_month'];
			$articleCreateFlag = true;
		}
		//定款作成日(日)
		if (isset($companyInfo['tbl_company']['cmp_article_create_day']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_day'])) {
			$articleCreateDay = $companyInfo['tbl_company']['cmp_article_create_day'];
			$articleCreateFlag = true;
		}
	}

	//定款作成日と同じ日を設定する。
	//作成日(年)
	$createYear = $articleCreateYear;
	//作成日(月)
	$createMonth = $articleCreateMonth;
	//作成日(日)
	$createDay = $articleCreateDay;

	//振込日(年)
	$payYear = date('Y');
	//振込日(月)
	$payMonth = date('n');
	//振込日(日)
	$payDay = date('j');

	//代表取締役の生年月日(年)
	$birthYear = null;
	//代表取締役の生年月日(月)
	$birthMonth = null;
	//代表取締役の生年月日(日)
	$birthDay = null;

	//印鑑届出書作成日(年)
	$inkanCreateYear = date('Y');
	//印鑑届出書作成日(月)
	$inkanCreateMonth = date('n');
	//印鑑届出書作成日(日)
	$inkanCreateDay = date('j');

	//本店所在地決議書作成日(年)
	$ketugiCreateYear = date('Y');
	//本店所在地決議書作成日(月)
	$ketugiCreateMonth = date('n');
	//本店所在地決議書作成日(日)
	$ketugiCreateDay = date('j');

	//株式会社設立登記申請書作成日(年)
	$shinseiCreateYear = date('Y');
	//株式会社設立登記申請書作成日(月)
	$shinseiCreateMonth = date('n');
	//株式会社設立登記申請書作成日(日)
	$shinseiCreateDay = date('j');

	$no = 0;

	$res = null;

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".株式会社設立登記申請書";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfTokiShinseisho\" name=\"frmPdfTokiShinseisho\" action=\"./create/tokishinseisho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "株式会社設立登記申請書を印刷します。";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
//	$resBuf .= "作成日";
	$resBuf .= "提出日";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($yearList, 'create_year', $shinseiCreateYear);
	$resBuf .= "&nbsp;";
	$resBuf .= "年";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'create_month', $shinseiCreateMonth);
	$resBuf .= "&nbsp;";
	$resBuf .= "月";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'create_day', $shinseiCreateDay);
	$resBuf .= "&nbsp;";
	$resBuf .= "日";
//	$resBuf .= "<br />";
//	$resBuf .= "※下の「4.」の「振込日」と同じ日付にしてください。";
	$resBuf .= "</dd>";
	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"../../../img/pdf/pdf_doc_01.pdf\" target=\"_blank\" title=\"株式会社設立登記申請書\">[印鑑説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;


	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".本店所在地決議書";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfHontenKetugisho\" name=\"frmPdfHontenKetugisho\" action=\"./create/honten_ketugisho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "会社を設立した場合、本店所在地を定めなければなりません。「".SITE_TITLE."」の場合においては、後のことを考慮し定款の認証時に最小行政区画まで定めています。その場合、登記時に具体的な番地までを定めなければなりません。";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "作成日";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'create_year', $ketugiCreateYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "年";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'create_month', $ketugiCreateMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "月";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'create_day', $ketugiCreateDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "日";
//	$resBuf .= "<br />";
//	$resBuf .= "※下の「4.」の「振込日」と同じ日付にしてください。";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_01.jpg\" rel=\"pdf_doc\" title=\"本店所在地決議書\">[印鑑説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	//作成日
	$resBuf .= "<input class=\"create_year\" type=\"hidden\" name=\"create_year\" value=\"".$shinseiCreateYear."\" />";
	$resBuf .= "<input class=\"create_month\" type=\"hidden\" name=\"create_month\" value=\"".$shinseiCreateMonth."\" />";
	$resBuf .= "<input class=\"create_day\" type=\"hidden\" name=\"create_day\" value=\"".$shinseiCreateDay."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;


	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
//	$resBuf .= ".取締役・監査役の就任承諾書";
//	$resBuf .= ".代表社員・監査役の就任承諾書";
	$resBuf .= ".就任承諾書";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfShodakusho\" name=\"frmPdfShodakusho\" action=\"./create/shodakusho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "取締役に就任することを承諾する書面です。自動で定款作成日と同じ日付で作成します。押印する<span class=\"attention\">印鑑は実印</span>を使用してください。(※代表取締役の印鑑証明書のみを添付)";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "定款作成日";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'article_create_year', $articleCreateYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "年";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'article_create_month', $articleCreateMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "月";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'article_create_day', $articleCreateDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "日";
//	if (!$articleCreateFlag) {
//		$resBuf .= "<br />";
//		$resBuf .= "<span class=\"attention\">【注意】定款はまだ作成されていません。上記日付は正式な日付ではありません。</span>";
//	}
//	$resBuf .= "</dd>";
//
//	$resBuf .= "<dt>";
//	$resBuf .= "作成日";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'create_year', $createYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "年";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'create_month', $createMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "月";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'create_day', $createDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "日";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "各1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_02.jpg\" rel=\"pdf_doc\" title=\"就任承諾書\">[印鑑説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	//定款作成日
	$resBuf .= "<input type=\"hidden\" name=\"article_create_year\" value=\"".$articleCreateYear."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"article_create_month\" value=\"".$articleCreateMonth."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"article_create_day\" value=\"".$articleCreateDay."\" />";

	//作成日
	$resBuf .= "<input class=\"create_year\" type=\"hidden\" name=\"create_year\" value=\"".$shinseiCreateYear."\" />";
	$resBuf .= "<input class=\"create_month\" type=\"hidden\" name=\"create_month\" value=\"".$shinseiCreateMonth."\" />";
	$resBuf .= "<input class=\"create_day\" type=\"hidden\" name=\"create_day\" value=\"".$shinseiCreateDay."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

//不要
if (false) {
	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".設立時代表取締役選定決議書";
	$resBuf .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;【※参考PDF無し!!!!!→保留中･･･】";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "代表取締役の選任を決定した書面です。自動で定款作成日と同じ日付で作成します。押印する<span class=\"attention\">印鑑は実印</span>を使用してください。 ";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明\">[印鑑説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;
}

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".払込みがあったことの証明書";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfHaraikomi\" name=\"frmPdfHaraikomi\" action=\"./create/haraikomi.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "発起人全員が資本金の振込みを完了した日付(通帳に記載されている日)または、それより後の日付を指定。";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
	$resBuf .= "振込日";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($yearList, 'pay_year', $payYear);
	$resBuf .= "&nbsp;";
	$resBuf .= "年";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'pay_month', $payMonth);
	$resBuf .= "&nbsp;";
	$resBuf .= "月";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'pay_day', $payDay);
	$resBuf .= "&nbsp;";
	$resBuf .= "日";
	$resBuf .= "</dd>";
	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_03.jpg\" rel=\"pdf_doc\" title=\"払込みがあったことの証明書\">[印鑑説明]</a>";
//	$resBuf .= "<br />";
//	$resBuf .= "<a href=\"#\" title=\"綴方説明\">[綴方説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<div class=\"point_explain_in\">";
	$resBuf .= "<span class=\"attention\">";
	$resBuf .= "資本金の振込は定款認証が完了してから行うようにしてください。";
	$resBuf .= "</span>";
	$resBuf .= "<br />";
	$resBuf .= "＜振込方法例＞";
	$resBuf .= "<br />";
//	$resBuf .= "たとえば発起人(Aさん Bさん Cさんの3人)で出資し、Aさんの口座に資本金を集める場合、Aさんも自分自身の口座にAさん名義で振込まなければなりません。(通帳に振込人の名前が記載されるように)入金ではだめです。";
	$resBuf .= "発起人(山田さん 鈴木さん 佐藤さんの3人)で出資し、山田さんの口座に資本金を集める場合、山田さんも自分自身の口座に山田さん名義で振込まなければなりません(通帳に振込人の名前が記載されるように)。入金ではNGです。";
	$resBuf .= "<br />";
	$resBuf .= "※必ず発起人名で振込んでください。";
	$resBuf .= "</div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".資本金の額の計上に関する証明書";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfShihonkinNoGakuNoKeijo\" name=\"frmPdfShihonkinNoGakuNoKeijo\" action=\"./create/shihonkinnogakunokeijo.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">(※現物出資を行う場合のみ必要)</span><br />";
//	$resBuf .= "発起人全員が資本金の振込みを完了した日付(通帳に記載されている日)または、それより後の日付を指定。";
//	$resBuf .= "発起人全員が資本金の振込みを完了した日付(通帳に記載されている日)または、翌日以降の日付を指定。";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "振込日";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'pay_year', $payYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "年";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'pay_month', $payMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "月";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'pay_day', $payDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "日";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_04.jpg\" rel=\"pdf_doc\" title=\"資本金の額の計上に関する証明書\">[印鑑説明]</a>";
//	$resBuf .= "<br />";
//	$resBuf .= "<a href=\"#\" title=\"綴方説明\">[綴方説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	//振込日
	$resBuf .= "<input class=\"pay_year\" type=\"hidden\" name=\"pay_year\" value=\"".$payYear."\" />";
	$resBuf .= "<input class=\"pay_month\" type=\"hidden\" name=\"pay_month\" value=\"".$payMonth."\" />";
	$resBuf .= "<input class=\"pay_day\" type=\"hidden\" name=\"pay_day\" value=\"".$payDay."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".調査報告書";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfChosahokokusho\" name=\"frmPdfChosahokokusho\" action=\"./create/chosahokokusho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">(※現物出資を行う場合のみ必要)</span><br />";
//	$resBuf .= "発起人全員が資本金の振込みを完了した日付(通帳に記載されている日)または、それより後の日付を指定。";
//	$resBuf .= "発起人全員が資本金の振込みを完了した日付(通帳に記載されている日)または、翌日以降の日付を指定。";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "振込日";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'pay_year', $payYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "年";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'pay_month', $payMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "月";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'pay_day', $payDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "日";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_05.jpg\" rel=\"pdf_doc\" title=\"調査報告書\">[印鑑説明]</a>";
//	$resBuf .= "<br />";
//	$resBuf .= "<a href=\"#\" title=\"綴方説明\">[綴方説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	//振込日
	$resBuf .= "<input class=\"pay_year\" type=\"hidden\" name=\"pay_year\" value=\"".$payYear."\" />";
	$resBuf .= "<input class=\"pay_month\" type=\"hidden\" name=\"pay_month\" value=\"".$payMonth."\" />";
	$resBuf .= "<input class=\"pay_day\" type=\"hidden\" name=\"pay_day\" value=\"".$payDay."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".財産引継書";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfZaisanhikitugisho\" name=\"frmPdfZaisanhikitugisho\" action=\"./create/zaisanhikitugisho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">(※現物出資を行う場合のみ必要)</span><br />";
//	$resBuf .= "発起人全員が資本金の振込みを完了した日付(通帳に記載されている日)または、それより後の日付を指定。";
//	$resBuf .= "発起人全員が資本金の振込みを完了した日付(通帳に記載されている日)または、翌日以降の日付を指定。";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "振込日";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'pay_year', $payYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "年";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'pay_month', $payMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "月";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'pay_day', $payDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "日";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_06.jpg\" rel=\"pdf_doc\" title=\"財産引継書\">[印鑑説明]</a>";
//	$resBuf .= "<br />";
//	$resBuf .= "<a href=\"#\" title=\"綴方説明\">[綴方説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	//振込日
	$resBuf .= "<input class=\"pay_year\" type=\"hidden\" name=\"pay_year\" value=\"".$payYear."\" />";
	$resBuf .= "<input class=\"pay_month\" type=\"hidden\" name=\"pay_month\" value=\"".$payMonth."\" />";
	$resBuf .= "<input class=\"pay_day\" type=\"hidden\" name=\"pay_day\" value=\"".$payDay."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

//不要
if (false) {
	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".OCR用申請用紙";
	$resBuf .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;【※参考PDF無し!!!!!→保留中･･･】";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "OCR用申請用紙(登記所のコンピューターで読取るもの)を印刷します。";
	$resBuf .= "<br />";
	$resBuf .= "専用用紙は登記所で配布しておりますが、白紙のB5用紙でもOKです。";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "<div class=\"pdf\">";
	$resBuf .= "<h5>";
	$resBuf .= "白紙のコピー用紙B5に印刷する場合";
	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "印刷時の用紙設定は<span class=\"attention\">B5縦</span>を選択してください。";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size b5\">";
	$resBuf .= "B5用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明\">[印鑑説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->


	$resBuf .= "<div class=\"pdf\">";
	$resBuf .= "<h5>";
	$resBuf .= "登記所でもらったOCR専用用紙に印刷する場合";
	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "印刷時の用紙設定は<span class=\"attention\">B5縦</span>を選択してください。";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size b5\">";
	$resBuf .= "B5用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明\">[印鑑説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;
}

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".印鑑届出書";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "会社の代表印を登録します。";
	$resBuf .= "<br />";
	$resBuf .= "登記完了後印鑑カードの申請を行ってください。";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "<div class=\"pdf\">";
//不要
if (false) {
	$resBuf .= "<h5>";
	$resBuf .= "代表取締役が登記申請に行く場合";
	$resBuf .= "</h5>";
}

	$resBuf .= "<form id=\"frmPdfInkantodokesho1\" name=\"frmPdfInkantodokesho1\" action=\"./create/inkantodokesho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">印鑑証明と同じ年号を使用してください。</span>";
//	$resBuf .= "<br />";
//	$resBuf .= "印刷時の用紙設定は<span class=\"attention\">B5縦</span>を選択してください。";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
	$resBuf .= "生年月日";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($birthYearList, 'birth_year', $birthYear);
	$resBuf .= "&nbsp;";
	$resBuf .= "年";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'birth_month', $birthMonth);
	$resBuf .= "&nbsp;";
	$resBuf .= "月";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'birth_day', $birthDay);
	$resBuf .= "&nbsp;";
	$resBuf .= "日";
	$resBuf .= "<br />";
	$resBuf .= "代表取締役の生年月日を入力してください。";
	$resBuf .= "</dd>";
	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size b5\">";
	$resBuf .= "B5用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_07.jpg\" rel=\"pdf_doc\" title=\"印鑑届出書\">[印鑑説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"mode\" value=\"".PDF_MODE_INKAN_DIRECTOR."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

//不要
if (false) {
	$resBuf .= "<div class=\"pdf\">";
	$resBuf .= "<h5>";
	$resBuf .= "代理人が登記申請に行く場合";
	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfInkantodokesho2\" name=\"frmPdfInkantodokesho2\" action=\"./create/inkantodokesho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">印鑑証明と同じ年号を使用してください。</span>";
	$resBuf .= "<br />";
	$resBuf .= "印刷時の用紙設定は<span class=\"attention\">B5縦</span>を選択してください。";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
	$resBuf .= "生年月日";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($birthYearList, 'birth_year', $birthYear);
	$resBuf .= "&nbsp;";
	$resBuf .= "年";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'birth_month', $birthMonth);
	$resBuf .= "&nbsp;";
	$resBuf .= "月";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'birth_day', $birthDay);
	$resBuf .= "&nbsp;";
	$resBuf .= "日";
	$resBuf .= "<br />";
	$resBuf .= "代表取締役の生年月日を入力してください。";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "作成日";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($yearList, 'create_year', $inkanCreateYear);
	$resBuf .= "&nbsp;";
	$resBuf .= "年";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'create_month', $inkanCreateMonth);
	$resBuf .= "&nbsp;";
	$resBuf .= "月";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'create_day', $inkanCreateDay);
	$resBuf .= "&nbsp;";
	$resBuf .= "日";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "代理人氏名";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "姓";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_family_name\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "名";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_first_name\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(全角)";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "フリガナ";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "姓";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_family_name_kana\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "名";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_first_name_kana\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(全角カタカナ)";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "代理人住所";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "都道府県";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($mstPrefList, 'agent_pref_id', null, "", true);
	$resBuf .= "<br />";
	$resBuf .= "市区町村";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_address1\" size=\"30\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(全角)";
	$resBuf .= "<br />";
	$resBuf .= "上記以降";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_address2\" size=\"30\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(全角)";
	$resBuf .= "</dd>";

	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size b5\">";
	$resBuf .= "B5用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明1\">[印鑑説明1]</a>";
	$resBuf .= "<br />";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明2\">[印鑑説明2]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"mode\" value=\"".PDF_MODE_INKAN_OTHER."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->
}

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

//不要
if (false) {
	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".登録免許税納付用台紙";
	$resBuf .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;【※参考PDF無し!!!!!→保留中･･･】";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "登録免許税(収入印紙)を貼り付けるための書面です。";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"割印説明\">[割印説明]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;
}

//不要
if (false) {
	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".設立登記申請書";
	$resBuf .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;【※参考PDF無し!!!!!→保留中･･･】";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">";
	$resBuf .= "代表取締役が登記申請に行く場合は委任状は印刷されません。代表取締役以外の方が行く場合は登記申請書と委任状が印刷されます。";
	$resBuf .= "</span>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->


	$resBuf .= "<div class=\"pdf\">";
	$resBuf .= "<h5>";
	$resBuf .= "代表取締役が登記申請に行く場合";
	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "管轄登記所に書類を持込む日を指定します。";
	$resBuf .= "<br />";
	$resBuf .= "実際に行かれる法務局を入力してください。";
	$resBuf .= "<br />";
	$resBuf .= "(一番最後に印刷でも可)";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
	$resBuf .= "持込む日";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($yearList, 'present_year', $info['update']['tbl_pdf']['present_year']);
	$resBuf .= "&nbsp;";
	$resBuf .= "年";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'present_month', $info['update']['tbl_pdf']['present_month']);
	$resBuf .= "&nbsp;";
	$resBuf .= "月";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'present_day', $info['update']['tbl_pdf']['present_day']);
	$resBuf .= "&nbsp;";
	$resBuf .= "日";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "法務局";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "<input type=\"text\" name=\"legal_affairs_bureau\" size=\"25\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(地方)法務局";
	$resBuf .= "<br />";
	$resBuf .= "<input type=\"checkbox\" id=\"head_office_flag\" name=\"head_office_flag\" value=\"1\" />";
	$resBuf .= "<label for=\"head_office_flag\">";
	$resBuf .= "&nbsp;";
	$resBuf .= "本局(支局・出張所が無い場合はチェック)";
	$resBuf .= "</label>";
	$resBuf .= "<br />";
	$resBuf .= "<input type=\"text\" name=\"branch_office\" size=\"25\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetRadio($mstLegalAffairsBureauList, 'legal_affairs_bureau_id', $info['update']['tbl_pdf']['legal_affairs_bureau_id']);
	$resBuf .= "</dd>";

	$boardName = '※現在、未登録です。';
	if (!_IsNull($tblCompanyBoardInfo)) {
		if (!_IsNull($tblCompanyBoardInfo['cmp_bod_family_name']) || !_IsNull($tblCompanyBoardInfo['cmp_bod_first_name'])) {
			$boardName = $tblCompanyBoardInfo['cmp_bod_family_name']." ".$tblCompanyBoardInfo['cmp_bod_first_name'];
		}

	}
	$resBuf .= "<dt>";
	$resBuf .= "代表取締役";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= $boardName;
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "電話番号";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "<input type=\"text\" name=\"board_tel1\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "-";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"board_tel2\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "-";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"board_tel3\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "<br />";
	$resBuf .= "代表取締役の電話番号を入力してください。";
	$resBuf .= "</dd>";
	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明1\">[印鑑説明1]</a>";
	$resBuf .= "<br />";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明2\">[印鑑説明2]</a>";
	$resBuf .= "<br />";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明3\">[印鑑説明3]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->



	$resBuf .= "<div class=\"pdf\">";
	$resBuf .= "<h5>";
	$resBuf .= "代表取締役以外が代理人として登記申請に行く場合";
	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "管轄登記所に書類を持込む日を指定します。";
	$resBuf .= "<br />";
	$resBuf .= "実際に行かれる法務局を入力してください。";
	$resBuf .= "<br />";
	$resBuf .= "(一番最後に印刷でも可)";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
	$resBuf .= "持込む日";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($yearList, 'present_year', $info['update']['tbl_pdf']['present_year']);
	$resBuf .= "&nbsp;";
	$resBuf .= "年";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'present_month', $info['update']['tbl_pdf']['present_month']);
	$resBuf .= "&nbsp;";
	$resBuf .= "月";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'present_day', $info['update']['tbl_pdf']['present_day']);
	$resBuf .= "&nbsp;";
	$resBuf .= "日";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "法務局";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "<input type=\"text\" name=\"legal_affairs_bureau\" size=\"25\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(地方)法務局";
	$resBuf .= "<br />";
	$resBuf .= "<input type=\"checkbox\" id=\"head_office_flag\" name=\"head_office_flag\" value=\"1\" />";
	$resBuf .= "<label for=\"head_office_flag\">";
	$resBuf .= "&nbsp;";
	$resBuf .= "本局(支局・出張所が無い場合はチェック)";
	$resBuf .= "</label>";
	$resBuf .= "<br />";
	$resBuf .= "<input type=\"text\" name=\"branch_office\" size=\"25\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetRadio($mstLegalAffairsBureauList, 'legal_affairs_bureau_id', $info['update']['tbl_pdf']['legal_affairs_bureau_id']);
	$resBuf .= "</dd>";

	$resBuf .= "<dt>";
	$resBuf .= "代理人氏名";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "姓";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_family_name\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "名";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_first_name\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(全角)";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "代理人住所";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "都道府県";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($mstPrefList, 'agent_pref_id', null, "", true);
	$resBuf .= "<br />";
	$resBuf .= "市区町村";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_address1\" size=\"30\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(全角)";
	$resBuf .= "<br />";
	$resBuf .= "上記以降";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_address2\" size=\"30\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(全角)";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "電話番号";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "<input type=\"text\" name=\"agent_tel1\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "-";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_tel2\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "-";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_tel3\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "<br />";
	$resBuf .= "代理人の電話番号を入力してください。";
	$resBuf .= "</dd>";
	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4用紙";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1通";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"印刷\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明1\">[印鑑説明1]</a>";
	$resBuf .= "<br />";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明2\">[印鑑説明2]</a>";
	$resBuf .= "<br />";
	$resBuf .= "<a href=\"#\" title=\"印鑑説明3\">[印鑑説明3]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;
}



	if (!_IsNull($res)) {
		$buf = null;

		$buf .= "<div class=\"adv\">";
		$buf .= "\n";
		$buf .= "<h3>ご注意ください!!</h3>";
		$buf .= "\n";
		$buf .= "<div class=\"adv_exp\">";
		$buf .= "\n";
		$buf .= "電子定款の認証後に、印刷してください。<br />";
		$buf .= "\n";
//		$buf .= "電子定款の作成中、または認証前に、「法務局への申請書類」を印刷しますと、正しい登記書類が作成できません。<br />";
		$buf .= "電子定款の作成中、または認証前に、「法務局への申請書類」を印刷を実行してしまうと、正しい登記書類が作成できません。<br />";
		$buf .= "\n";
		$buf .= "<br />";
		$buf .= "\n";
		$buf .= "登記書類の印刷は、<span class=\"attention\">必ず公証役場での電子定款「認証」後</span>に行ってください。<br />";
		$buf .= "\n";
		$buf .= "<br />";
		$buf .= "\n";
		$buf .= "上記の注意点を了承の上、印刷へ進んでください。";
		$buf .= "\n";
		$buf .= "</div>";
		$buf .= "\n";
		$buf .= "</div><!-- End adv -->";
		$buf .= "\n";

		$buf .= "<div class=\"formWrapper\">";
		$buf .= "\n";
		$buf .= "<div class=\"formList\">";
		$buf .= "\n";

		$buf .= "<div id=\"tbl_pdf\">";
		$buf .= "\n";

		$buf .= "<h3>登記所に提出する書類</h3>";
		$buf .= "\n";
		$buf .= "<div class=\"point_explain_out\">";
		$buf .= "<span class=\"attention\">";
		$buf .= "原則、登記の事由 が発生したときからから2週間以内に管轄登記所に登記申請書類の提出してください。";
		$buf .= "</span>";
		$buf .= "<br />";
//		$buf .= "登記の事由とはすべての準備が整った段階ということになりますので、<span class=\"attention\">資本金の振り込みが完了した時から2週間以内</span>となります。";
		$buf .= "登記の事由が発生したときとは、すべての準備が整ったときを意味しますので、";
		$buf .= "<br />";
		$buf .= "<span class=\"attention\">原則資本金の振り込みが完了した日より2週間以内</span>となります。";
		$buf .= "<br />";
		$buf .= "<br />";
		$buf .= "<span class=\"attention\" style=\"font-weight:bold;\">";
		$buf .= "日付は定款作成日を元に自動的に設定されます。";
		$buf .= "</span>";
		$buf .= "<br />";
		$buf .= "<br />";
		$buf .= "印刷する書類は「1.」から順に行ってください。";
		$buf .= "</div>";
		$buf .= "\n";
		$buf .= $res;
		$buf .= "\n";
		$buf .= "<div class=\"point_explain_out\">";
		$buf .= "<span class=\"attention\">★登記所に行くときに必要な書類等</span>";
		$buf .= "<ol>";
		$buf .= "<li>上記「".SITE_TITLE."」で印刷した書面・・・・・各1部</li>";
		$buf .= "<li>定款・・・・・1通(定款認証済みのもの)(表紙に謄本と記載されているもの)</li>";
//		$buf .= "<li>取締役全員の印鑑証明・・・・・各1通(3ヶ月以内に発行されたもの)(代表取締役含む)</li>";
		$buf .= "<li>取締役全員の印鑑証明書・・・・・各1通(3ヶ月以内に発行されたものに限る)※代表取締役含む</li>";
//		$buf .= "<li>登録免許税・・・・・15万円(現金または収入印紙)</li>";
		$buf .= "<li>登録免許税・・・・・資本金2,157万円以下の場合は15万円(現金または収入印紙)</li>";
		$buf .= "</ol>";
		$buf .= "</div>";
		$buf .= "\n";
		$buf .= "</div><!-- End tbl_pdf -->";//<!-- End tbl_pdf -->
		$buf .= "\n";

		$buf .= "</div><!-- End formList -->";//<!-- End formList -->
		$buf .= "\n";
		$buf .= "</div><!-- End formWrapper -->";//<!-- End formWrapper -->
		$buf .= "\n";

		$res = $buf;
	}

	return $res;
}



?>
