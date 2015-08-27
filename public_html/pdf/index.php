<?php
/*
 * [管理画面]
 * PDF作成画面
 *
 * 更新履歴：2008/11/05	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
//session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/pdf/index.php] start.");

_Log("[/pdf/index.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/index.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/index.php] SERVER = '".print_r($_SERVER,true)."'");


//認証チェック----------------------------------------------------------------------start
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/pdf/index.php] end.");
	//ログイン画面を表示する。
	header("Location: ".URL_BASE);
	exit;
}
//ログイン情報を取得する。
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
//認証チェック----------------------------------------------------------------------end



//本ファイルの名称を取得する。
$phpName = basename($_SERVER['PHP_SELF']);
//フォームのアクションを設定する。
//$formAction = SSL_URL_THE_LIFEBOAT_COM_INQ.'/'.$phpName;
$formAction = $_SERVER['PHP_SELF'];

//通常のURL(SSLではないURL)
$urlBase = URL_BASE;

//サイト名
$clientName = ADMIN_TITLE;

//タブインデックス
$tabindex = 0;

//DBをオープンする。
$cid = _DB_Open();

//マスタ情報を取得する。
$undeleteOnly = true;

$yearList = _GetYearArray(date('Y') - 2, date('Y') + 2);	//年
$monthList = _GetMonthArray();								//月
$dayList = _GetDayArray();									//日


//動作モード{1:入力/2:確認/3:完了/4:エラー}
$mode = 1;

//全て表示するか？hidden項目も表示するか？{true:全て表示する。/false:XML設定、権限による表示有無に従う。}
$allShowFlag = false;

//メッセージ
$message = "";
//エラーフラグ
$errorFlag = false;

//メッセージ
$message4js = "";


//ターゲット情報を格納する配列
$info = array();

//作成日
$info['year'] = date('Y');
$info['month'] = date('n');
$info['day'] = date('j');
//定款作成日
$info['teikan_year'] = date('Y');
$info['teikan_month'] = date('n');
$info['teikan_day'] = date('j');



//パラメーターを取得する。
$xmlName = null;
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
	
		break;
	case 'GET':
		//XMLファイル名
		$xmlName = XML_NAME_INQ;

		//ターゲットID
		$id = (isset($_GET['id'])?$_GET['id']:null);

		//遷移元ページ
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);


		//初期値を設定する。
		$undeleteOnly4def = false;

		
		//権限処理追加
		switch ($loginInfo['mng_auth_id']) {
			case AUTH_NON:
				//権限無し
				
				//ターゲットID
				$id = null;
				unset($_GET['id']);//→動作モード="単独表示"にするためにクリアする。
		
				//遷移元ページ
				$pId = null;

				$undeleteOnly4def = true;//未削除データのみ
				
				//ユーザーIDから問合せ情報を検索する。→問合せIDを取得する。
				$inquiryId = null;
				if (isset($loginInfo['tbl_user'])) {
					$condition4inq = array();
					$condition4inq['inq_user_id'] = $loginInfo['tbl_user']['usr_user_id'];	//顧客ID
					$tblInquiryList = _DB_GetList('tbl_inquiry', $condition4inq, true, null, 'inq_del_flag');
					if (!_IsNull($tblInquiryList)) {
						//配列の先頭から要素を一つ取り出す
						$tblInquiryInfo = array_shift($tblInquiryList);
						$inquiryId = $tblInquiryInfo['inq_inquiry_id'];
					}
				}
				if (_IsNull($inquiryId)) {
					$message = "※該当の問合せ情報が存在しません。\n";
					$errorFlag = true;
					$mode = 4;
				} else {
					//ターゲットID
					$id = $inquiryId;
				}
				break;
		}


		$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
		
		//XMLファイル名、ターゲットIDを初期値に追加する。
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;


		//設定されている場合=更新の場合
		if (isset($_GET['id'])) {
			//動作モードをセッションに保存する。動作モード="他画面経由の表示"
			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_OTHER;
		} else {
			//動作モードをセッションに保存する。動作モード="単独表示"
			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_MENU;
		}

		//遷移元ページをセッションに保存する。
		$_SESSION[SID_INFO_FROM_PAGE_ID] = $pId;

		break;	
}

_Log("[/pdf/index.php] (param) \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/pdf/index.php] (param) XMLファイル名 = '".$xmlName."'");
_Log("[/pdf/index.php] (param) ターゲットID = '".$id."'");


//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/pdf/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/pdf/index.php] mode = '".$mode."'");


//会社タイプマスタによって、各設定をする。
$teikanFile = null;
switch ($info['update']['tbl_company']['cmp_company_type_id']) {
	case MST_COMPANY_TYPE_ID_LLC://LLC
		$teikanFile = "./create/teikan_llc.php";
		break;
	case MST_COMPANY_TYPE_ID_NPO://NPO
	case MST_COMPANY_TYPE_ID_CMP://株式会社
	default:
		$teikanFile = "./create/teikan.php";
		break;
}




////文字をHTMLエンティティに変換する。
//$info = _HtmlSpecialCharsForArray($info);

//echo ("\$info='".print_r($info,true)."'");

//パンくずリスト情報を設定する。
$level = 2;
//動作モード="他画面経由の表示"の場合、レベルを3にする。
if ($_SESSION[SID_INFO_MODE] == MST_MODE_FROM_OTHER) $level = 3;

$breadcrumbsTitle = 'PDF作成';
_SetBreadcrumbs($_SERVER['PHP_SELF'], '', $breadcrumbsTitle, $level);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<link rel="stylesheet" href="../css/import.css" type="text/css" />
<script language="javascript" src="../common/js/util.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/libs/mootools.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/tool_tip.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/resizable.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/aftereffects_grade.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/create_inq_info_from_mail.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/search_mastar/search_mastar.js" type="text/javascript" charset="utf-8"></script>

<title><?=$clientName?></title>
</head>

<body id="home">
<div id="wrapper">
	<div id="header">
		<?include_once("../common_html/header.php");?>
	</div><!-- End header -->

	<div id="sidebar">
		<?include_once("../common_html/side_menu.php");?>
	</div><!-- End sidebar -->

	<div class="breadcrumbs">
		<?=$breadcrumbs = _GetBreadcrumbs();?>
	</div><!-- End breadcrumbs -->

	<div id="maincontent">
		<h2>PDF作成</h2>
		
		<div id="pdfTeikan" class="pdf">
			<h3>定款</h3>
			<form id="frmPdfTeikan" name="frmPdfTeikan" action="<?=$teikanFile?>" method="post" target="_blank">
				<div class="input">
					定款を作成します。
					<br />
					作成日を設定して、PDF作成ボタンを押してください。
					<br />
					<br />
					作成日：
					<br />
					<?_WriteSelect($yearList, 'year', $info['year'], (++$tabindex), false, '&nbsp;');?>年
					<?_WriteSelect($monthList, 'month', $info['month'], (++$tabindex), false, '&nbsp;');?>月
					<?_WriteSelect($dayList, 'day', $info['day'], (++$tabindex), false, '&nbsp;');?>日
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF作成" />
				</div>
				<div class="end"></div>
			</form>
		</div>

		<div id="pdfHaraikomi" class="pdf">
			<h3>払込証明書</h3>
			<form id="frmPdfHaraikomi" name="frmPdfHaraikomi" action="./create/haraikomi.php" method="post" target="_blank">
				<div class="input">
					払込証明書を作成します。
					<br />
					作成日を設定して、PDF作成ボタンを押してください。
					<br />
					<br />
					作成日：
					<br />
					<?_WriteSelect($yearList, 'year', $info['year'], (++$tabindex), false, '&nbsp;');?>年
					<?_WriteSelect($monthList, 'month', $info['month'], (++$tabindex), false, '&nbsp;');?>月
					<?_WriteSelect($dayList, 'day', $info['day'], (++$tabindex), false, '&nbsp;');?>日
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF作成" />
				</div>
				<div class="end"></div>
			</form>
		</div>

		<div id="pdfIninjo" class="pdf">
			<h3>委任状</h3>
			<form id="frmPdfIninjo" name="frmPdfIninjo" action="./create/ininjo.php" method="post" target="_blank">
				<div class="input">
					委任状を作成します。
					<br />
					作成日を設定して、PDF作成ボタンを押してください。
					<br />
					<br />
					作成日：
					<br />
					<?_WriteSelect($yearList, 'year', $info['year'], (++$tabindex), false, '&nbsp;');?>年
					<?_WriteSelect($monthList, 'month', $info['month'], (++$tabindex), false, '&nbsp;');?>月
					<?_WriteSelect($dayList, 'day', $info['day'], (++$tabindex), false, '&nbsp;');?>日
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF作成" />
				</div>
				<div class="end"></div>
			</form>
		</div>

		<div id="pdfSohusho" class="pdf">
			<h3>添付書面送付書</h3>
			<form id="frmPdfSohusho" name="frmPdfSohusho" action="./create/sohusho.php" method="post" target="_blank">
				<div class="input">
					添付書面送付書を作成します。
					<br />
					PDF作成ボタンを押してください。
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF作成" />
				</div>
				<div class="end"></div>
			</form>
		</div>

		<div id="pdfShodakusho" class="pdf">
			<h3>就任承諾書</h3>
			<form id="frmPdfShodakusho" name="frmPdfShodakusho" action="./create/shodakusho.php" method="post" target="_blank">
				<div class="input">
					就任承諾書を作成します。
					<br />
					作成日と定款作成日を設定して、PDF作成ボタンを押してください。
					<br />
					<br />
					作成日：
					<br />
					<?_WriteSelect($yearList, 'year', $info['year'], (++$tabindex), false, '&nbsp;');?>年
					<?_WriteSelect($monthList, 'month', $info['month'], (++$tabindex), false, '&nbsp;');?>月
					<?_WriteSelect($dayList, 'day', $info['day'], (++$tabindex), false, '&nbsp;');?>日
					<br />
					定款作成日：
					<br />
					<?_WriteSelect($yearList, 'teikan_year', $info['teikan_year'], (++$tabindex), false, '&nbsp;');?>年
					<?_WriteSelect($monthList, 'teikan_month', $info['teikan_month'], (++$tabindex), false, '&nbsp;');?>月
					<?_WriteSelect($dayList, 'teikan_day', $info['teikan_day'], (++$tabindex), false, '&nbsp;');?>日
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF作成" />
				</div>
				<div class="end"></div>
			</form>
		</div>

		<div id="pdfInkantodokesho" class="pdf">
			<h3>印鑑届書</h3>
			<form id="frmPdfInkantodokesho" name="frmPdfInkantodokesho" action="./create/inkantodokesho.php" method="post" target="_blank">
				<div class="input">
					印鑑届書を作成します。
					<br />
					PDF作成ボタンを押してください。
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF作成" />
				</div>
				<div class="end"></div>
			</form>
		</div>

<?
if (false) {
?>
		<a href="./create/teikan.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">定款</a>
		<br />
		<a href="./create/haraikomi.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">払込証明書</a>
		<br />
		<a href="./create/ininjo.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">委任状</a>
		<br />
		<a href="./create/sohusho.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">添付書面送付書</a>
		<br />
		<a href="./create/shodakusho.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">就任承諾書</a>
		<br />
		<a href="./create/inkantodokesho.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">印鑑届書</a>
<?
}
?>
	</div><!-- End maincontent -->

	<div class="breadcrumbs">
		<?=$breadcrumbs?>
	</div><!-- End breadcrumbs -->
	
	<div id="footer">
		<?include_once("../common_html/footer.php");?>
	</div><!-- End footer -->

</div><!-- End wrapper -->
</body>
</html>

<?
////DBをクローズする。
//_DB_Close($cid);

_Log("[/pdf/index.php] end.");

?>