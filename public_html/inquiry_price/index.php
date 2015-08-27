<?php
/*
 * [管理画面]
 * 請求額一覧画面
 *
 * 更新履歴：2007/12/03	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/inquiry_price/index.php] start.");

//認証チェック----------------------------------------------------------------------start
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/inquiry_price/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/inquiry_price/index.php] end.");
	//ログイン画面を表示する。
	header("Location: ".URL_LOGIN);
	exit;
}
//ログイン情報を取得する。
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
_CheckAuth($loginInfo, AUTH_WOOROM);
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
$undeleteOnly = false;
$mstStatusList = _GetMasterList('mst_status', $undeleteOnly);										//状況マスタ
$mstAftereffectsGrade01List = _GetMasterList('mst_aftereffects_grade_01', $undeleteOnly);		//後遺障害等級(級)マスタ
$yearList = _GetYearArray(SYSTEM_START_YEAR, date('Y') + 3);										//年
$monthList = _GetMonthArray();																		//月
$dayList = _GetDayArray();																			//日
//削除フラグ
$delFlagList = array(
			 DELETE_FLAG_NO => array('id' => DELETE_FLAG_NO, 'name' => DELETE_FLAG_NO_NAME, 'no_name' => '')
			,DELETE_FLAG_YES => array('id' => DELETE_FLAG_YES, 'name' => DELETE_FLAG_YES_NAME, 'no_name' => '')
			);


//動作モード{1:入力/2:完了(成功)/3:エラー}
$mode = 1;

//メッセージ
$message = "";
//エラーフラグ
$errorFlag = false;

//メッセージ
$message4js = "";


//入力情報を格納する配列
$info = array();
//初期値を設定する。
$info['condition']['iuq_del_flag'] = array(DELETE_FLAG_NO);						//問合せテーブル.削除フラグ="未削除"
//$y = date('Y');
//$m = date('n');
//$info['condition']['iuq_agd_create_date_year_from'] = $y;						//状況更新日From(年)
//$info['condition']['iuq_agd_create_date_month_from'] = $m;						//状況更新日From(月)
//$info['condition']['iuq_agd_create_date_day_from'] = 1;						//状況更新日From(日)
//$info['condition']['iuq_agd_create_date_year_to'] = $y;						//状況更新日To(年)
//$info['condition']['iuq_agd_create_date_month_to'] = $m;						//状況更新日To(月)
//$info['condition']['iuq_agd_create_date_day_to'] = date('j' ,mktime(0, 0, 0, ($m==12?1:$m+1), 0, ($m==12?$y+1:$y)));	//状況更新日To(日)←当月末尾を設定する。


$y = date('Y');
$info['condition']['iuq_agd_create_date_year_from'] = $y;					//状況更新日From(年)
$info['condition']['iuq_agd_create_date_month_from'] = 1;					//状況更新日From(月)
$info['condition']['iuq_agd_create_date_day_from'] = 1;					//状況更新日From(日)
$info['condition']['iuq_agd_create_date_year_to'] = $y;					//状況更新日To(年)
$info['condition']['iuq_agd_create_date_month_to'] = 12;					//状況更新日To(月)
$info['condition']['iuq_agd_create_date_day_to'] = 31;						//状況更新日To(日)

//状況="着手金受領"、"交渉"、"成功報酬受領"、"終了"
$info['condition']['iuq_agd_status_id'] = array(MST_STATUS_ID_START_MONEY, MST_STATUS_ID_NEGOTIATION, MST_STATUS_ID_SUCCESS_MONEY, MST_STATUS_ID_END);

//問合せ_後遺障害等級(級)_確定テーブルを検索対象に追加する。
$info['condition']['add_tbl_inquiry_aftereffects_grade_decision'] = true;


//検索結果を格納する配列
$inquiryList = null;
//検索件数を格納する
$maxCount = 0;
//デフォルトの選択ページを設定する。
$activePage = 1;

//検索のソート条件
$order .= " t_inq.iuq_inquiry_id";		//問合せテーブル.問合せIDの昇順
$order .= ",t_iuq_agd.iuq_agd_no";		//問合せ_後遺障害等級(級)_確定テーブル.Noの昇順


//選択ボタンが押された場合
if ($_POST['select'] != "") {
	//入力値を取得する。
	$info = $_POST;
	_Log("[/inquiry_price/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/inquiry_price/index.php] POST(バックスラッシュを取り除く) = '".print_r($info,true)."'");

	//入力値変換
//	//「全角」数字を「半角」に変換する。------------------------------------------------------------
//	//登録日数From
//	$info['condition']['site_relation_total_days_from'] = mb_convert_kana($info['condition']['site_relation_total_days_from'], "n");
//	//登録日数To
//	$info['condition']['site_relation_total_days_to'] = mb_convert_kana($info['condition']['site_relation_total_days_to'], "n");
//
//	//入力値チェック
//	//半角数字チェック------------------------------------------------------------------------------
//	//登録日数From
//	if (!_IsHalfSizeNumeric($info['condition']['site_relation_total_days_from'])) $info['condition']['site_relation_total_days_from'] = null;
//	//登録日数To
//	if (!_IsHalfSizeNumeric($info['condition']['site_relation_total_days_to'])) $info['condition']['site_relation_total_days_to'] = null;

	//問合せ情報を検索する。
	$inquiryList = _GetInquiry($info['condition'], $order, false, false, $activePage, null, 2);
//	//件数を取得する。
//	$maxCount = _GetInquiry($info['condition'], $order, false, true);

	if (_IsNull($inquiryList)) {
		$message = "検索条件に該当する情報が存在しません。";
	}


//更新ボタンが押された場合
} elseif ($_POST['go'] != "") {
	//入力値を取得する。
	$info = $_POST;
	_Log("[/inquiry_price/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/inquiry_price/index.php] POST(バックスラッシュを取り除く) = '".print_r($info,true)."'");
//	//「全角」数字を「半角」に変換する。
//	$info = _Mb_Convert_KanaForArray($info, 'n');
//	_Log("[/inquiry_price/index.php] POST(「全角」数字を「半角」に変換する) = '".print_r($info,true)."'");


//検索条件クリアボタンが押された場合
} elseif ($_POST['clear'] != "") {
	unset($info['condition']);
	unset($_SESSION[SID_PRICE_INQ_CONDITION]);
//	unset($_SESSION[SID_SRCH_INQ_LIST]);
//	unset($_SESSION[SID_SRCH_INQ_COUNT]);
//	unset($_SESSION[SID_SRCH_INQ_ACTIVE_PAGE]);


	$y = date('Y');
	$info['condition']['iuq_agd_create_date_year_from'] = $y;					//状況更新日From(年)
	$info['condition']['iuq_agd_create_date_month_from'] = 1;					//状況更新日From(月)
	$info['condition']['iuq_agd_create_date_day_from'] = 1;					//状況更新日From(日)
	$info['condition']['iuq_agd_create_date_year_to'] = $y;					//状況更新日To(年)
	$info['condition']['iuq_agd_create_date_month_to'] = 12;					//状況更新日To(月)
	$info['condition']['iuq_agd_create_date_day_to'] = 31;						//状況更新日To(日)

	//WOOROM権限以外の場合、未削除の情報のみ表示する。
	if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] != AUTH_WOOROM) {
		//初期値を設定する。
		$info['condition']['iuq_del_flag'] = array(DELETE_FLAG_NO);					//問合せテーブル.削除フラグ="未削除"
	}

//他ページから戻ってきた場合
} elseif (isset($_GET['back'])) {

	//セッションから検索条件を取得する。
	$info['condition'] = $_SESSION[SID_PRICE_INQ_CONDITION];

	//問合せ情報を検索する。
	$inquiryList = _GetInquiry($info['condition'], $order, false, false, $activePage, null, 2);
//	//件数を取得する。
//	$maxCount = _GetInquiry($info['condition'], $order, false, true);

	if (_IsNull($inquiryList)) {
		$message = "検索条件に該当する情報が存在しません。";
	}

//ページリンクが押された場合
} elseif (isset($_GET['page']) && $_GET['page'] != "") {

//	//セッションから検索条件を取得する。
//	$info['condition'] = $_SESSION[SID_PRICE_INQ_CONDITION];
//	//選択ページを取得する。
//	$activePage = $_GET['page'];
//
//	//問合せ情報を検索する。
//	$order = null;		//ソート条件
//	$inquiryList = _GetInquiry($info['condition'], $order, false, false, $activePage, null);
//	//件数を取得する。
//	$maxCount = _GetInquiry($info['condition'], $order, false, true);
//
//	if (_IsNull($inquiryList)) {
//		$message = "検索条件に該当する情報が存在しません。";
//	}

//初回起動時
} else {
	//問合せ情報を検索する。
	$inquiryList = _GetInquiry($info['condition'], $order, false, false, $activePage, null, 2);
//	//件数を取得する。
//	$maxCount = _GetInquiry($info['condition'], $order, false, true);

	if (_IsNull($inquiryList)) {
		$message = "検索条件に該当する情報が存在しません。";
	}
}

//検索条件をセッションに保存する。
$_SESSION[SID_PRICE_INQ_CONDITION] = $info['condition'];
////検索結果をセッションに保存する。
//$_SESSION[SID_SRCH_INQ_LIST] = $inquiryList;
////検索件数をセッションに保存する。
//$_SESSION[SID_SRCH_INQ_COUNT] = $maxCount;
////選択ページをセッションに保存する。
//$_SESSION[SID_SRCH_INQ_ACTIVE_PAGE] = $activePage;

//基本となる年月を取得する。
$ymdList = _GetYmdList($info['condition'], INQUIRY_PRICE_START_MONTH, INQUIRY_PRICE_TOTAL_MONTH);

//////////////////////////////////////////////////////////////////////////////////////////////////////// start
//基本となる年月を設定する。
function _GetYmdList($list, $monthStart, $limit) {
	_Log("[_GetYmdList] start.");

	_Log("[_GetYmdList] (引数)条件 ='".print_r($list,true)."'");
	_Log("[_GetYmdList] (引数)開始月 ='".$monthStart."'");
	_Log("[_GetYmdList] (引数)リミット ='".$limit."'");


	$yearFrom = $list['iuq_agd_create_date_year_from'];
	$monthFrom = $list['iuq_agd_create_date_month_from'];
	$dayFrom = $list['iuq_agd_create_date_day_from'];
	$yearTo = $list['iuq_agd_create_date_year_to'];
	$monthTo = $list['iuq_agd_create_date_month_to'];
	$dayTo = $list['iuq_agd_create_date_day_to'];

	$resList = array();

	$buf1List = array();
	for ($y = $yearFrom; $y <= $yearTo; $y++) {

		if ($y == $yearFrom) {
//			$mFrom = $monthFrom;

			//開始月を設定する。
			$flg = true;
			$bufMonthStart = $monthStart;

			while ($flg) {
				$bufList = array();
				for ($i = 0; $i < $limit; $i++) {
					_Log("[_GetYmdList] {開始月設定} 基準月 = '".$bufMonthStart."'");

					$bufList[] = $bufMonthStart;
					$bufMonthStart++;
					if ($bufMonthStart > 12) $bufMonthStart = 1;
				}

				_Log("[_GetYmdList] {開始月設定} 基準月配列 = '".print_r($bufList,true)."'");

				if (in_array($monthFrom, $bufList)) {
					$flg = false;
					$mFrom = $bufList[0];

					_Log("[_GetYmdList] {開始月設定} 基準月決定 = '".$mFrom."'");
				}
			}

		} else {
			$mFrom = 1;
		}
		if ($y == $yearTo) {
			$mTo = $monthTo;
		} else {
			$mTo = 12;
		}

		for ($m = $mFrom; $m <= $mTo; $m++) {

			$buf2List = array();
			$buf2List['year'] = $y;
			$buf2List['month'] = $m;
			$buf1List[] = $buf2List;

			if (count($buf1List) == $limit) {
				$resList[] = $buf1List;
				$buf1List = array();
			}
		}
	}

	//中途半場の場合、足りない分を足す。
	while (count($buf1List) > 0) {
		//最後に設定された年月を取得する。
		$buf = $buf1List[count($buf1List)-1];
		//最後に設定された年月の次の年月を設定する。
		if ($buf['month'] == 12) {
			$y = $buf['year'] + 1;
			$m = 1;
		} else {
			$y = $buf['year'];
			$m = $buf['month'] + 1;
		}

		$buf2List = array();
		$buf2List['year'] = $y;
		$buf2List['month'] = $m;
		$buf1List[] = $buf2List;

		if (count($buf1List) == $limit) {
			$resList[] = $buf1List;
			$buf1List = array();
		}
	}

	_Log("[_GetYmdList] 結果 ='".print_r($resList,true)."'");
	_Log("[_GetYmdList] end.");

	return $resList;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////// end


//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
////文字をHTMLエンティティに変換する。
//$inquiryList = _HtmlSpecialCharsForArray($inquiryList);

//echo ("\$info='".print_r($info,true)."'");

$onlode = null;

//パンくずリスト情報を設定する。
_SetBreadcrumbs($_SERVER['PHP_SELF'], 'back', '請求額一覧', 2);

//ページリンクを設定する。
//$link = _GetPageLink($maxCount, $activePage, INQ_PAGE_LINK_TOP_MESSAGE, INQ_PAGE_LINK_ACTIVE_PAGE_MESSAGE, INQ_PAGE_LINK_COUNT_MESSAGE, null, INQ_PAGE_LINK_LIMIT, INQ_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE, INQ_PAGE_LINK_FRONT_TEXT, INQ_PAGE_LINK_REAR_TEXT);

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
<script language="javascript" src="../common/js/status_update_date.js" type="text/javascript"></script>

<script type="text/javascript">
<!--
//-->
</script>


<title><?=$clientName?></title>
</head>

<body id="home" onload="openBox('explain_sub', 'explain', 'explain_close');<?=$onlode?>">
<div id="wrapper">
	<div id="header">
		<?include_once("../common_html/header.php");?>
	</div><!-- End header -->

	<div class="breadcrumbs">
		<?=$breadcrumbs = _GetBreadcrumbs();?>
	</div><!-- End breadcrumbs -->

	<div id="sidebar">
		<?include_once("../common_html/side_menu.php");?>
	</div><!-- End sidebar -->

	<div id="maincontent">
		<h2>請求額一覧</h2>
		<h3>検索条件</h3>

		<form id="frmSelect" name="frmSelect" action="<?=$_SERVER['PHP_SELF']?>" method="post">
<!--			<h4 id="listMode">[表示条件]</h4>-->
			<table class="priceConditionTable">
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>

				<tr>
					<td class="colHead">
						等級確定日&nbsp;※
					</td>
					<td colspan="5">
						<?_WriteSelect($yearList, 'condition[iuq_agd_create_date_year_from]', $info['condition']['iuq_agd_create_date_year_from'], (++$tabindex), false, '&nbsp;');?>年
						<?_WriteSelect($monthList, 'condition[iuq_agd_create_date_month_from]', $info['condition']['iuq_agd_create_date_month_from'], (++$tabindex), false, '&nbsp;');?>月
						<?_WriteSelect($dayList, 'condition[iuq_agd_create_date_day_from]', $info['condition']['iuq_agd_create_date_day_from'], (++$tabindex), false, '&nbsp;');?>日
						〜
						<?_WriteSelect($yearList, 'condition[iuq_agd_create_date_year_to]', $info['condition']['iuq_agd_create_date_year_to'], (++$tabindex), false, '&nbsp;');?>年
						<?_WriteSelect($monthList, 'condition[iuq_agd_create_date_month_to]', $info['condition']['iuq_agd_create_date_month_to'], (++$tabindex), false, '&nbsp;');?>月
						<?_WriteSelect($dayList, 'condition[iuq_agd_create_date_day_to]', $info['condition']['iuq_agd_create_date_day_to'], (++$tabindex), false, '&nbsp;');?>日
						<br />
						※<?=INQUIRY_PRICE_START_MONTH?>月から<?=INQUIRY_PRICE_TOTAL_MONTH?>ヶ月単位に合計・1ヶ月平均を計算します。
					</td>
				</tr>
				<tr>
					<td class="colHead">
						名前
					</td>
					<td>
						<input type="text" name="condition[usr_name]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['usr_name']?>" />
					</td>
					<td class="colHead" rowspan="4">
						状況
					</td>
					<td rowspan="4">
						<?_WriteSelect($mstStatusList, 'condition[iuq_agd_status_id]', $info['condition']['iuq_agd_status_id'], (++$tabindex), false, "&nbsp;", 6, true, 'id', 'name_del_2', 'id', 'class="multiple"', true);?>
					</td>
					<td class="colHead" rowspan="4">
						等級
					</td>
					<td rowspan="4">
						<?_WriteSelect($mstAftereffectsGrade01List, 'condition[iuq_agd_aftereffects_grade_01_id]', $info['condition']['iuq_agd_aftereffects_grade_01_id'], (++$tabindex), false, "&nbsp;", 6, true, 'id', 'name_del_2', 'id', 'class="multiple"', true);?>
					</td>
				</tr>
				<tr>
<?if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id']==AUTH_WOOROM) {?>
					<td class="colHead">
						削除フラグ
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($delFlagList, 'condition[iuq_del_flag]', $info['condition']['iuq_del_flag'], (++$tabindex));?>
					</td>
<?} else {?>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
<?
	//検索条件をhiddenで設定する。
	$condition4hidden = array();
	$condition4hidden['condition']['iuq_del_flag'] = $info['condition']['iuq_del_flag'];
	echo _CreateHidden($condition4hidden);
?>
						&nbsp;
					</td>
<?}?>
				</tr>
				<tr>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
			</table>

			<div class="button">
				<input class="submit" type="submit" name="select" value="　検　索　" tabindex="<?=(++$tabindex)?>" />
				&nbsp;
				<input class="submit" type="submit" name="reset" value="　初期条件検索　" tabindex="<?=(++$tabindex)?>" />
				&nbsp;
				<input class="submit" type="submit" name="clear" value="　クリア　" tabindex="<?=(++$tabindex)?>" />
			</div>

<?
	//検索条件をhiddenで設定する。
	$condition4hidden = array();
	$condition4hidden['condition']['add_tbl_inquiry_aftereffects_grade_decision'] = $info['condition']['add_tbl_inquiry_aftereffects_grade_decision'];
	echo _CreateHidden($condition4hidden);
?>


		</form>

<?
//検索結果又は、メッセージが存在する場合、見出しを表示する。
if (!_IsNull($inquiryList) || !_IsNull($message)) {
?>
		<h3>検索結果</h3>
<?
}
?>

<?
if (!_IsNull($message)) {
	$addClass = null;
	//エラーが有る場合、文字色を変更する。
	if ($errorFlag) $addClass = "errorMessage";
?>
		<div class="message <?=$addClass?>">
			<?=nl2br($message)?>
		</div>
<?
}
?>


<?
if (!_IsNull($inquiryList)) {
?>
		<form id="frmUpdate" name="frmUpdate" action="<?=$_SERVER['PHP_SELF']?>" method="post">
			<div class="formWrapper">
				<div class="formList">
<!--
					<div class="page"><?=$link?></div>
-->
					<table id="priceResultListTable">
<!--
						<colgroup class="colWidth01"></colgroup>
						<colgroup class="colWidth02"></colgroup>
						<colgroup class="colWidth03"></colgroup>
						<colgroup class="colWidth04"></colgroup>
						<colgroup class="colWidth05"></colgroup>
						<colgroup class="colWidth06"></colgroup>
						<colgroup class="colWidth07"></colgroup>
						<colgroup class="colWidth08"></colgroup>
						<colgroup class="colWidth09"></colgroup>
						<colgroup class="colWidth10"></colgroup>
-->
						<thead>
							<tr>
								<td colspan="2" rowspan="2"></td>
								<td rowspan="2">ID</td>
<!--								<td rowspan="2">確定<br />回数</td>-->
								<td rowspan="2">名前</td>
								<td colspan="3">等級確定日</td>
								<td rowspan="2">状況</td>
								<td rowspan="2">障害等級</td>
								<td colspan="2">請求金額</td>
								<td rowspan="2">1ヶ月平均</td>
								<td rowspan="2">備考</td>
								<td rowspan="2">詳細</td>
<!--								<td class="tips" title="等級::問合せの等級を登録します。">等級<br />/&nbsp;等級金額</td>-->
							</tr>
							<tr>
								<td class="bdTop">年</td>
								<td class="bdTop">月</td>
								<td class="bdTop">日</td>
								<td class="bdTop">レギュラー</td>
								<td class="bdTop">プレミア</td>
							</tr>
						</thead>

						<tbody>
<?
			$LF = "\n";
			$data = null;
			foreach ($ymdList as $ymdKey1 =>  $oneSetYmdList) {
				$oneSetTotalAftereffectsGrade01Price = 0;	//基準月数単位の合計等級金額
				$oneSetTotalStartPrice = 0;					//基準月数単位の合計着手金額
				$oneSetTotalYm = "";
				$data2 = null;
				foreach ($oneSetYmdList as $ymdKey2 =>  $ymdInfo) {
					if ($ymdKey2 == 0) {
						//最初の年月
						$oneSetTotalYm .= sprintf('%04d/%02d', $ymdInfo['year'], $ymdInfo['month']);
						$oneSetTotalYm .= "〜";
					} elseif ($ymdKey2 == count($oneSetYmdList) - 1) {
						//最後の年月
						$oneSetTotalYm .= sprintf('%04d/%02d', $ymdInfo['year'], $ymdInfo['month']);
					}

					$inquiryYmList = array();
					if (isset($inquiryList[$ymdInfo['year']][$ymdInfo['month']])) {
						$inquiryYmList = $inquiryList[$ymdInfo['year']][$ymdInfo['month']];
					} else {
						$dummy = array();
						$dummy[] = array();
						$inquiryYmList[] = $dummy; //ダミー
					}

					$ymTotalYm = sprintf('%04d/%02d', $ymdInfo['year'], $ymdInfo['month']);
					$ymTotalAftereffectsGrade01Price = 0;	//月単位の合計等級金額
					$ymTotalStartPrice = 0;					//月単位の合計着手金額
					$data3 = null;
					$count = 0;
					$count4TrColor = 0;
					foreach ($inquiryYmList as $day => $inquiryYmdList) {
						foreach ($inquiryYmdList as $key => $inquiryInfo) {
							$count++;

							$rowColorClass = null;
							if ($count % 2 == 0) {
								$rowColorClass = 'rowColor02';
							} else {
								$rowColorClass = 'rowColor01';
							}

							//年
							$year = $ymdInfo['year'];
							//月
							$month = sprintf('%02d', $ymdInfo['month']);
							//日
							$day = null;
							if (isset($inquiryInfo['iuq_agd_create_date_d']) && !_IsNull($inquiryInfo['iuq_agd_create_date_d'])) {
								$day = sprintf('%02d', $inquiryInfo['iuq_agd_create_date_d']);
							} else {
								$year = "&nbsp;";
								$month = "&nbsp;";
							}
							//問合せID
							//URL
							$inquiryId = null;
							$inquiryIdShow = null;
							$url = null;
							$trId = null;
							$trIdPrefix = "inq";
							if (isset($inquiryInfo['iuq_inquiry_id']) && !_IsNull($inquiryInfo['iuq_inquiry_id'])) {
								$inquiryId = $inquiryInfo['iuq_inquiry_id'];
								$inquiryIdShow = _FormatNo($inquiryId);
								$url = "<a class=\"edit\" href=\"../info/?xml_name=".XML_NAME_INQ."&amp;p_id=".PAGE_ID_INQ_PRICE."&amp;id=".$inquiryId."\" title=\"詳細\">[詳細]</a>";


								$trId = $trIdPrefix.$inquiryId."_".$inquiryInfo['iuq_agd_no'];
							}

							//名前
							$usrName = _SubStr($inquiryInfo['usr_name'], 10);
							//名前ページ内リンク
							$usrNameLink = "%s";

							//状況
							$statusName = null;
							if (isset($inquiryInfo['iuq_agd_status_id']) && !_IsNull($inquiryInfo['iuq_agd_status_id'])) {
								$statusName = $mstStatusList[$inquiryInfo['iuq_agd_status_id']]['name'];
							}
							//後遺障害等級(級)
							$aftereffectsGrade01Name = null;
							if (isset($inquiryInfo['iuq_agd_aftereffects_grade_01_id']) && !_IsNull($inquiryInfo['iuq_agd_aftereffects_grade_01_id'])) {
								$aftereffectsGrade01Name = $mstAftereffectsGrade01List[$inquiryInfo['iuq_agd_aftereffects_grade_01_id']]['name'];
							}


							//後遺障害等級(級)金額
							$aftereffectsGrade01PriceFlag = false;
							$aftereffectsGrade01Price = 0;
							$aftereffectsGrade01PriceShow = null;
							if (isset($inquiryInfo['iuq_agd_aftereffects_grade_01_price']) && !_IsNull($inquiryInfo['iuq_agd_aftereffects_grade_01_price'])) {
								$aftereffectsGrade01PriceShow = number_format($inquiryInfo['iuq_agd_aftereffects_grade_01_price']);
								$aftereffectsGrade01Price = $inquiryInfo['iuq_agd_aftereffects_grade_01_price'];
								$aftereffectsGrade01PriceFlag = true;
							}
							//着手金額
							$startPriceFlag = false;
							$startPrice = 0;
							$startPriceShow = null;
							if (isset($inquiryInfo['iuq_tac_start_price']) && !_IsNull($inquiryInfo['iuq_tac_start_price'])) {
								$startPriceShow = number_format($inquiryInfo['iuq_tac_start_price']);
								$startPrice = $inquiryInfo['iuq_tac_start_price'];
								$startPriceFlag = true;
							}


							$iuqAgdNo = null;

							$preAftereffectsGrade01PriceFlag = false;
							$preStartPriceFlag = false;
							$preAftereffectsGrade01Price = 0;
							$preStartPrice = 0;
							$note = null;
							$rowColorClassDifferencePrice = null;
							if (isset($inquiryInfo['iuq_agd_no']) && !_IsNull($inquiryInfo['iuq_agd_no'])) {

								$iuqAgdNo = $inquiryInfo['iuq_agd_no'];

								//2回目以降の等級確定の場合
								if ($inquiryInfo['iuq_agd_no'] > 1) {
									//一つ前の等級確定のNoを設定する。
									$preNo = $inquiryInfo['iuq_agd_no'] - 1;
									//一つ前の問合せ情報を取得する。
									$condition4pre = array();
									$condition4pre['iuq_inquiry_id'] = $inquiryId;									//問合せID
									$condition4pre['iuq_agd_no'] = $preNo;												//一つ前の等級確定のNo
									$condition4pre['add_tbl_inquiry_aftereffects_grade_decision'] = true;		//問合せ_後遺障害等級(級)_確定テーブルを検索対象に追加する。
									$preInquiryList = _GetInquiry($condition4pre, null, false, false, $activePage, null);

									$preInquiryInfo = null;
									if (!_IsNull($preInquiryList)) $preInquiryInfo = $preInquiryList[0];//1件のみ検索される。

									if (!_IsNull($preInquiryInfo)) {
										//一つ前の…
										//後遺障害等級(級)金額
										if (isset($preInquiryInfo['iuq_agd_aftereffects_grade_01_price']) && !_IsNull($preInquiryInfo['iuq_agd_aftereffects_grade_01_price'])) {
											$preAftereffectsGrade01Price = $preInquiryInfo['iuq_agd_aftereffects_grade_01_price'];
											$preAftereffectsGrade01PriceFlag = true;
										}
										//着手金額
										if (isset($preInquiryInfo['iuq_tac_start_price']) && !_IsNull($preInquiryInfo['iuq_tac_start_price'])) {
											$preStartPrice = $preInquiryInfo['iuq_tac_start_price'];
											$preStartPriceFlag = true;
										}


										//備考を設定する。
										$note .= "前回：";
										//$note .= sprintf('%04d/%02d/%02d', $preInquiryInfo['iuq_agd_create_date_y'], $preInquiryInfo['iuq_agd_create_date_m'], $preInquiryInfo['iuq_agd_create_date_d']);
										$note .= $preInquiryInfo['iuq_agd_create_date_yymmdd'];
										$note .= "、";
										$note .= $mstAftereffectsGrade01List[$preInquiryInfo['iuq_agd_aftereffects_grade_01_id']]['name'];
										$note .= "、";
										$note .= "プレミア=";
										if (isset($preInquiryInfo['iuq_agd_aftereffects_grade_01_price']) && !_IsNull($preInquiryInfo['iuq_agd_aftereffects_grade_01_price'])) {
											$note .= number_format($preInquiryInfo['iuq_agd_aftereffects_grade_01_price']);
										}
										$note .= " /";
										$note .= "今回：";
										$note .= $aftereffectsGrade01PriceShow;

										//一つ前の等級確定へページ内リンクを張る。
										$usrNameLink = "<a href=\"#".$trIdPrefix.$preInquiryInfo['iuq_inquiry_id']."_".$preInquiryInfo['iuq_agd_no']."\">%s</a>";
									}

									//2回目以降の等級確定の行の文字色を変更する。
									$rowColorClassDifferencePrice = "clrDifferencePrice";

								//初回の等級確定の場合
								} else {
									//2回目の等級確定のNoを設定する。
									$nextNo = $inquiryInfo['iuq_agd_no'] + 1;
									//2回目の問合せ情報を取得する。
									$condition4pre = array();
									$condition4pre['iuq_inquiry_id'] = $inquiryId;									//問合せID
									$condition4pre['iuq_agd_no'] = $nextNo;											//2回目の等級確定のNo
									$condition4pre['add_tbl_inquiry_aftereffects_grade_decision'] = true;		//問合せ_後遺障害等級(級)_確定テーブルを検索対象に追加する。
									$nextInquiryList = _GetInquiry($condition4pre, null, false, false, $activePage, null);

									$nextInquiryInfo = null;
									if (!_IsNull($nextInquiryList)) $nextInquiryInfo = $nextInquiryList[0];//1件のみ検索される。
									if (!_IsNull($nextInquiryInfo)) {
										//2回目の等級確定へページ内リンクを張る。
										$usrNameLink = "<a href=\"#".$trIdPrefix.$nextInquiryInfo['iuq_inquiry_id']."_".$nextInquiryInfo['iuq_agd_no']."\">%s</a>";
									}

								}
							}

							//後遺障害等級(級)金額 - (一つ前の後遺障害等級(級)金額)  (※2回目以降の等級確定の場合は差額を加算する。)
							$subTotalAftereffectsGrade01Price = $aftereffectsGrade01Price - $preAftereffectsGrade01Price;
							$subTotalAftereffectsGrade01PriceShow = null;
							if ($aftereffectsGrade01PriceFlag || $preAftereffectsGrade01PriceFlag) {
								$subTotalAftereffectsGrade01PriceShow = number_format($subTotalAftereffectsGrade01Price);
							}

							//着手金額 - (一つ前の着手金額)  (※2回目以降の等級確定の場合は差額を加算する。)
							$subTotalStartPrice = $startPrice - $preStartPrice;
							$subTotalStartPriceShow = null;
							if ($startPriceFlag || $preStartPriceFlag) {
								$subTotalStartPriceShow = number_format($subTotalStartPrice);
							}



							//月単位の合計を計算する。
							//等級金額
							$ymTotalAftereffectsGrade01Price += $subTotalAftereffectsGrade01Price;
							//着手金額
							$ymTotalStartPrice += $subTotalStartPrice;


							//着手金額、等級金額が0の場合、非表示にする。
							if (((!_IsNull($subTotalStartPriceShow) && $subTotalStartPriceShow != "0"))
							     ||
							     ((!_IsNull($subTotalAftereffectsGrade01PriceShow) && $subTotalAftereffectsGrade01PriceShow != "0"))
							    ) {
//							if (true) {
								$count4TrColor++;
								$rowColorClass = null;
								if ($count4TrColor % 2 == 0) {
									$rowColorClass = 'rowColor02';
								} else {
									$rowColorClass = 'rowColor01';
								}

								$data3 .= "<tr class=\"".$rowColorClass." ".$rowColorClassDifferencePrice."\" ".(_IsNull($trId)?"":"id=\"".$trId."\"").">".$LF;
								$data3 .= "<td class=\"colWidth01 bgOneSetTotal colCenter\"></td>".$LF;
								$data3 .= "<td class=\"colWidth02 bgYmTotal colCenter\"></td>".$LF;
								$data3 .= "<td class=\"colWidth03 bdTop colCenter\">".$inquiryIdShow."</td>".$LF;
	//							$data3 .= "<td class=\"colWidth04 bdTop colCenter\">".$iuqAgdNo."</td>".$LF;
								$data3 .= "<td class=\"colWidth05 bdTop\" title=\"".htmlspecialchars($inquiryInfo['usr_name'])."\">".sprintf($usrNameLink, htmlspecialchars($usrName))."</td>".$LF;
								$data3 .= "<td class=\"colWidth06 bdTop colCenter\">".$year."</td>".$LF;
								$data3 .= "<td class=\"colWidth07 bdTop colCenter\">".$month."</td>".$LF;
								$data3 .= "<td class=\"colWidth08 bdTop colCenter\">".$day."</td>".$LF;
								$data3 .= "<td class=\"colWidth09 bdTop colCenter\">".htmlspecialchars($statusName)."</td>".$LF;
								$data3 .= "<td class=\"colWidth10 bdTop colCenter\">".htmlspecialchars($aftereffectsGrade01Name)."</td>".$LF;
								$data3 .= "<td class=\"colWidth11 bdTop colNum\">".$subTotalStartPriceShow."</td>".$LF;
								$data3 .= "<td class=\"colWidth12 bdTop colNum\">".$subTotalAftereffectsGrade01PriceShow."</td>".$LF;
								$data3 .= "<td class=\"colWidth13 bdTop colNum\"></td>".$LF;
								$data3 .= "<td class=\"colWidth14 bdTop\">".htmlspecialchars($note)."</td>".$LF;
								$data3 .= "<td class=\"colWidth15 bdTop colCenter\">".$url."</td>".$LF;
								$data3 .= "</tr>".$LF;

						    }
						}

					}

					//基準月数単位の合計を計算する。(2007/12/18現在：3ヶ月単位)
					//等級金額
					$oneSetTotalAftereffectsGrade01Price += $ymTotalAftereffectsGrade01Price;
					//着手金額
					$oneSetTotalStartPrice += $ymTotalStartPrice;

//					//月単位の平均金額を計算する。
//					$ymAveragePrice = ($ymTotalStartPrice + $ymTotalAftereffectsGrade01Price) / $count;


					$data2 .= "<tr>".$LF;
					$data2 .= "<td class=\"bgOneSetTotal\"></td>".$LF;
//					$data2 .= "<td class=\"bgYmTotal bdTop\" colspan=\"9\">".$ymTotalYm."の合計</td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop\" colspan=\"8\">".$ymTotalYm."の合計</td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop colNum\">".number_format($ymTotalStartPrice)."</td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop colNum\">".number_format($ymTotalAftereffectsGrade01Price)."</td>".$LF;
//					$data2 .= "<td class=\"bgYmTotal bdTop colNum\">".number_format($ymAveragePrice, 2)."</td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop\"></td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop\"></td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop\"></td>".$LF;
					$data2 .= "</tr>".$LF;

					$data2 .= $data3;
				}

				//基準月数単位の合計金額 = 等級金額 + 着手金額 を計算する。
				$oneSetTotalPrice = $oneSetTotalStartPrice + $oneSetTotalAftereffectsGrade01Price;

				//基準月数単位の平均金額を計算する。
				$oneSetAveragePrice = $oneSetTotalPrice / INQUIRY_PRICE_TOTAL_MONTH;

				//100円単位を切り捨てる。
				$floorOneSetAveragePrice = $oneSetAveragePrice / 1000;
				$floorOneSetAveragePrice = floor($floorOneSetAveragePrice);
				$floorOneSetAveragePrice *= 1000;

				$data1 = null;
				$data1 .= "<tr>".$LF;
//				$data1 .= "<td class=\"bgOneSetTotal bdTop\" colspan=\"10\" rowspan=\"2\">".$oneSetTotalYm."の合計・1ヶ月平均</td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop\" colspan=\"9\" rowspan=\"2\">".$oneSetTotalYm."の合計・1ヶ月平均</td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop clrOneSetTotalPrice colNum\" colspan=\"2\">".number_format($oneSetTotalPrice)."</td>".$LF;
//				$data1 .= "<td class=\"bgOneSetTotal bdTop clrOneSetAveragePrice colNum\" rowspan=\"2\">".number_format($oneSetAveragePrice, 2)."</td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop clrOneSetAveragePrice colNum\" rowspan=\"2\">".number_format($floorOneSetAveragePrice)."</td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop\" rowspan=\"2\"></td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop\" rowspan=\"2\"></td>".$LF;
				$data1 .= "</tr>".$LF;

				$data1 .= "<tr>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop clrOneSetTotalPrice colNum\">".number_format($oneSetTotalStartPrice)."</td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop clrOneSetTotalPrice colNum\">".number_format($oneSetTotalAftereffectsGrade01Price)."</td>".$LF;
				$data1 .= "</tr>".$LF;

				$data .= $data1;
				$data .= $data2;
			}
			echo $data;
?>
						</tbody>
					</table>
<!--
					<div class="page"><?=$link?></div>
-->
				</div>
			</div>
<!--
			<div class="button">
				<input class="submit" type="submit" name="go" value="　更　新　" tabindex="<?=(++$tabindex)?>" />
			</div>
-->
<?
//セッションに保持する。
//	//検索条件をhiddenで設定する。
//	$condition4hidden = array();
//	$condition4hidden['condition'] = $info['condition'];
//	echo _CreateHidden($condition4hidden);
?>

		</form>
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


	<div id="debug"></div>

</div><!-- End wrapper -->
</body>
</html>

<?
//DBをクローズする。
_DB_Close($cid);

_Log("[/inquiry_price/index.php] end.");

?>