<?php
/*
 * [管理画面]
 * 商品一覧画面
 *
 * 更新履歴：2008/02/22	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/item/index.php] start.");

//認証チェック----------------------------------------------------------------------start
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/item/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/item/index.php] end.");
	//ログイン画面を表示する。
	header("Location: ".URL_BASE);
	exit;
}
//ログイン情報を取得する。
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
_CheckAuth($loginInfo, AUTH_CLIENT, AUTH_WOOROM);
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
$mstCategoryList = _GetMasterList('mst_category', $undeleteOnly);			//カテゴリーマスタ
$mstSubcategoryList = _GetMasterList('mst_subcategory', $undeleteOnly);		//サブカテゴリーマスタ
$yearList = _GetYearArray(SYSTEM_START_YEAR, date('Y') + 2);				//年
$monthList = _GetMonthArray();												//月
$dayList = _GetDayArray();													//日
//削除フラグ
$delFlagList = array(
			 DELETE_FLAG_NO => array('id' => DELETE_FLAG_NO, 'name' => DELETE_FLAG_NO_NAME, 'name2' => '○')
			,DELETE_FLAG_YES => array('id' => DELETE_FLAG_YES, 'name' => DELETE_FLAG_YES_NAME, 'name2' => '×')
			);
//掲載フラグ
$showFlagList = array(
			 SHOW_FLAG_YES => array('id' => SHOW_FLAG_YES, 'name' => SHOW_FLAG_YES_NAME, 'name2' => '○')
			,SHOW_FLAG_NO => array('id' => SHOW_FLAG_NO, 'name' => SHOW_FLAG_NO_NAME, 'name2' => '×')
			);


//動作モード{1:入力/2:完了(成功)/3:エラー}
$mode = 1;

//メッセージ
$message = "";
//エラーフラグ
$errorFlag = false;

//メッセージ
$message4js = "";


//二重submit対策
if (!isset($_SESSION['token'])) $_SESSION['token'] = uniqid("itm_");


//入力情報を格納する配列
$info = array();
//初期値を設定する。
$info['condition']['itm_del_flag'] = array(DELETE_FLAG_NO);				//商品テーブル.削除フラグ="未削除"
//$info['condition']['itm_show_flag'] = array(SHOW_FLAG_YES);			//商品テーブル.掲載フラグ="未削除"
//$y = date('Y');
//$m = date('n');
//$info['condition']['itm_create_date_year_from'] = $y;					//商品テーブル.作成日From(年)
//$info['condition']['itm_create_date_month_from'] = $m;				//商品テーブル.作成日From(月)
//$info['condition']['itm_create_date_day_from'] = 1;					//商品テーブル.作成日From(日)
//$info['condition']['itm_create_date_year_to'] = $y;					//商品テーブル.作成日To(年)
//$info['condition']['itm_create_date_month_to'] = $m;					//商品テーブル.作成日To(月)
//$info['condition']['itm_create_date_day_to'] = date('j' ,mktime(0, 0, 0, ($m==12?1:$m+1), 0, ($m==12?$y+1:$y)));	//商品テーブル.作成日To(日)←当月末尾を設定する。
//
//$info['condition']['itm_update_date_year_from'] = $y;					//商品テーブル.更新日From(年)
//$info['condition']['itm_update_date_month_from'] = $m;				//商品テーブル.更新日From(月)
//$info['condition']['itm_update_date_day_from'] = 1;					//商品テーブル.更新日From(日)
//$info['condition']['itm_update_date_year_to'] = $y;					//商品テーブル.更新日To(年)
//$info['condition']['itm_update_date_month_to'] = $m;					//商品テーブル.更新日To(月)
//$info['condition']['itm_update_date_day_to'] = date('j' ,mktime(0, 0, 0, ($m==12?1:$m+1), 0, ($m==12?$y+1:$y)));	//商品テーブル.更新日To(日)←当月末尾を設定する。

//ソート条件
$order = null;
$order .= "lpad(m_ctg.show_order,10,'0')";	//カテゴリーマスタ.表示順(昇順)
$order .= ",lpad(m_sbc.show_order,10,'0')";	//サブカテゴリーマスタ.表示順(昇順)
$order .= ",t_itm.itm_code";				//商品テーブル.商品コード(昇順)

//検索結果を格納する配列
$itemList = null;
//検索件数を格納する
$maxCount = 0;
//デフォルトの選択ページを設定する。
$activePage = 1;

//選択ボタンが押された場合
if ($_POST['select'] != "") {
	//入力値を取得する。
	$info = $_POST;
	_Log("[/item/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/item/index.php] POST(バックスラッシュを取り除く) = '".print_r($info,true)."'");
	
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
	

	//商品情報を検索する。
	$itemList = _GetItem($info['condition'], $order, false, false, $activePage, ITM_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE);
	//件数を取得する。
	$maxCount = _GetItem($info['condition'], $order, false, true);
	
	if (_IsNull($itemList)) {
		$message = "検索条件に該当する情報が存在しません。";
	}
	
	
//更新ボタンが押された場合
} elseif ($_POST['go'] != "") {
	//入力値を取得する。
	$info = $_POST;
	_Log("[/item/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/item/index.php] POST(バックスラッシュを取り除く) = '".print_r($info,true)."'");
//	//「全角」数字を「半角」に変換する。
//	$info = _Mb_Convert_KanaForArray($info, 'n');
//	_Log("[/item/index.php] POST(「全角」数字を「半角」に変換する) = '".print_r($info,true)."'");

	//入力値チェック

	//セッションから検索条件を取得する。
	$info['condition'] = $_SESSION[SID_SRCH_ITM_CONDITION];
	//セッションから検索結果を取得する。
	$itemList = $_SESSION[SID_SRCH_ITM_LIST];
	//セッションから検索件数を取得する。
	$maxCount = $_SESSION[SID_SRCH_ITM_COUNT];
	//セッションから選択ページを取得する。
	$activePage = $_SESSION[SID_SRCH_ITM_ACTIVE_PAGE];

	_Log("[/item/index.php] 二重submit対策 SESSION値 = '".$_SESSION['token']."'");
	_Log("[/item/index.php] 二重submit対策    POST値 = '".$info['token']."'");

	//二重submit対策をする。
	if ($_SESSION['token'] == $info['token']) {
		if (isset($info['update'])) {
			_Log("[/item/index.php] {商品情報更新} -------------------- 開始");
			
			//入力値チェック
			$message .= "エラーメッセージ";
			foreach ($info['update'] as $key => $newInfo) {
				//チェック...
				
				//検索結果に入力値を上書きする。→エラー時の再表示のため。※
				foreach ($newInfo as $name => $value) {
					$itemList[$key][$name] = $value;	
				}
			}
	
			if (_IsNull($message)) {
				//セッションから検索結果を再取得する。→上記※で、上書きしているため。
				$itemList = $_SESSION[SID_SRCH_ITM_LIST];
	
				$count = 0;
				foreach ($info['update'] as $key => $newInfo) {
					$count++;
		
					//変更有無をチェックする。
					$updateFlag = false;							//全項目の更新有無フラグ
	

					//更新後に最新のDB情報で検索結果を上書きする。{true:上書きする/false:しない}
					$overwriteFlag = false;
					
					if ($updateFlag) {

						//更新処理...
						$overwriteFlag = true;

					}
		
					//更新があった場合、最新のDB情報で検索結果を上書きする。
					if ($overwriteFlag) {
						_Log("[/item/index.php] {商品情報更新} 3.更新があった場合、最新のDB情報で検索結果を上書きする。");
						
						//商品情報を検索する。
						$condition4new = array();
						$condition4new['itm_item_id'] = $newInfo['itm_item_id'];//商品ID
						$newItemList = _GetItem($condition4new, $order, false);	
						
						if (_IsNull($newItemList)) {
						} else {
							$itemList[$key] = $newItemList[0];
						}
						
						//上書きした検索結果をセッションに保存する。
						$_SESSION[SID_SRCH_ITM_LIST] = $itemList;
					}
				}
		
				//セッションから検索結果を再取得する。
				$itemList = $_SESSION[SID_SRCH_ITM_LIST];
		
				//ここまででメッセージが空の場合、登録、更新、削除がなかった。
				if (_IsNull($message)) {
					$message = "変更箇所がありません。";
				} else {
					//エラー無しの場合、二重submit対策のユニークキーを更新する。
					$_SESSION['token'] = uniqid("itm_");
				}
				
			} else {
				//エラーが有り場合
				$message = "※入力に誤りがあります。\n".$message;
				$errorFlag = true;
			}
	
		}
		
	} else {
		$message = "※二重更新です。更新をする場合は、「更新」ボタンを押してください。";
		$errorFlag = true;
	}
	


//検索条件クリアボタンが押された場合
} elseif ($_POST['clear'] != "") {

	unset($info['condition']);
	unset($_SESSION[SID_SRCH_ITM_CONDITION]);
	unset($_SESSION[SID_SRCH_ITM_LIST]);
	unset($_SESSION[SID_SRCH_ITM_COUNT]);
	unset($_SESSION[SID_SRCH_ITM_ACTIVE_PAGE]);


	//WOOROM権限以外の場合、未削除の情報のみ表示する。
	if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] != AUTH_WOOROM) {
		//初期値を設定する。
		$info['condition']['itm_del_flag'] = array(DELETE_FLAG_NO);		//商品テーブル.削除フラグ="未削除"
	}


//他ページから戻ってきた場合
} elseif (isset($_GET['back'])) {

	//セッションから検索条件を取得する。
	$info['condition'] = $_SESSION[SID_SRCH_ITM_CONDITION];
	//セッションから検索結果を取得する。
	$itemList = $_SESSION[SID_SRCH_ITM_LIST];
	//セッションから検索件数を取得する。
	$maxCount = $_SESSION[SID_SRCH_ITM_COUNT];
	//セッションから選択ページを取得する。
	$activePage = $_SESSION[SID_SRCH_ITM_ACTIVE_PAGE];


	//商品情報を再検索する。→前のページで更新されている可能性があるため。
	//検索条件を設定する。前のページで検索条件となる項目が更新されている可能性があるため、前回表示していた情報のキーのみで検索する。

	$condition4new = array();
	foreach ($itemList as $key => $itemInfo) {
		$condition4new['itm_item_id'][] = $itemInfo['itm_item_id'];	//商品ID
	}

	//商品情報を検索する。
	$itemList = _GetItem($condition4new, $order, false);
	
	if (_IsNull($itemList)) {
		$message = "検索条件に該当する情報が存在しません。";
	}

//ページリンクが押された場合
} elseif (isset($_GET['page']) && $_GET['page'] != "") {

	//セッションから検索条件を取得する。
	$info['condition'] = $_SESSION[SID_SRCH_ITM_CONDITION];
	//選択ページを取得する。
	$activePage = $_GET['page'];

	//商品情報を検索する。
	$itemList = _GetItem($info['condition'], $order, false, false, $activePage, ITM_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE);
	//件数を取得する。
	$maxCount = _GetItem($info['condition'], $order, false, true);
	
	if (_IsNull($itemList)) {
		$message = "検索条件に該当する情報が存在しません。";
	}

//初回起動時
} else {
	//商品情報を検索する。
	$itemList = _GetItem($info['condition'], $order, false, false, $activePage, ITM_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE);
	//件数を取得する。
	$maxCount = _GetItem($info['condition'], $order, false, true);
	
	if (_IsNull($itemList)) {
		$message = "検索条件に該当する情報が存在しません。";
	}
}


//検索条件をセッションに保存する。
$_SESSION[SID_SRCH_ITM_CONDITION] = $info['condition'];
//検索結果をセッションに保存する。
$_SESSION[SID_SRCH_ITM_LIST] = $itemList;
//検索件数をセッションに保存する。
$_SESSION[SID_SRCH_ITM_COUNT] = $maxCount;
//選択ページをセッションに保存する。
$_SESSION[SID_SRCH_ITM_ACTIVE_PAGE] = $activePage;



//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
////文字をHTMLエンティティに変換する。
//$itemList = _HtmlSpecialCharsForArray($itemList);

//echo ("\$info='".print_r($info,true)."'");

$onlode = null;
	
//パンくずリスト情報を設定する。
_SetBreadcrumbs($_SERVER['PHP_SELF'], 'back', '商品一覧', 2);

//ページリンクを設定する。
$link = _GetPageLink($maxCount, $activePage, ITM_PAGE_LINK_TOP_MESSAGE, ITM_PAGE_LINK_ACTIVE_PAGE_MESSAGE, ITM_PAGE_LINK_COUNT_MESSAGE, null, ITM_PAGE_LINK_LIMIT, ITM_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE, ITM_PAGE_LINK_FRONT_TEXT, ITM_PAGE_LINK_REAR_TEXT);

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
		<h2>商品一覧</h2>
		<h3>検索条件</h3>
		
		<form id="frmSelect" name="frmSelect" action="<?=$_SERVER['PHP_SELF']?>" method="post">
<!--			<h4 id="listMode">[表示条件]</h4>-->
			<table class="searchConditionTable">
<!--
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
-->
				<colgroup class="colgroupHeadItem"></colgroup>
				<colgroup class="colgroupBodyItem1"></colgroup>
				<colgroup class="colgroupHeadItem"></colgroup>
				<colgroup class="colgroupBodyItem2"></colgroup>

				<tr>
					<td class="colHead">
						カテゴリー
					</td>
					<td colspan="3">
						<?$tabindex = _WriteCheckbox($mstCategoryList, 'condition[itm_category_id]', $info['condition']['itm_category_id'], (++$tabindex), 4, 'id', 'name_del_2', 'id', false, null);?>
					</td>
				</tr>
				<tr>
					<td class="colHead">
						サブカテゴリー
					</td>
					<td colspan="3">
						<?$tabindex = _WriteCheckbox($mstSubcategoryList, 'condition[itm_subcategory_id]', $info['condition']['itm_subcategory_id'], (++$tabindex), 4, 'id', 'name_del_2', 'id', false, null);?>
					</td>
				</tr>
				<tr>
					<td class="colHead">
						商品コード
					</td>
					<td>
						<input type="text" name="condition[itm_code]" size="70" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['itm_code']?>" />
					</td>
					<td class="colHead">
						掲載フラグ
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($showFlagList, 'condition[itm_show_flag]', $info['condition']['itm_show_flag'], (++$tabindex));?>
					</td>
				</tr>
				<tr>
					<td class="colHead">
						商品名
					</td>
					<td>
						<input type="text" name="condition[itm_name]" size="70" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['itm_name']?>" />
					</td>
<?if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] == AUTH_WOOROM) {?>
					<td class="colHead">
						削除フラグ
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($delFlagList, 'condition[itm_del_flag]', $info['condition']['itm_del_flag'], (++$tabindex));?>
					</td>
<?} else {?>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
<?}?>
				</tr>
				<tr>
					<td class="colHead">
						登録日
					</td>
					<td>
						<?_WriteSelect($yearList, 'condition[itm_create_date_year_from]', $info['condition']['itm_create_date_year_from'], (++$tabindex), true, '&nbsp;');?>年
						<?_WriteSelect($monthList, 'condition[itm_create_date_month_from]', $info['condition']['itm_create_date_month_from'], (++$tabindex), true, '&nbsp;');?>月
						<?_WriteSelect($dayList, 'condition[itm_create_date_day_from]', $info['condition']['itm_create_date_day_from'], (++$tabindex), true, '&nbsp;');?>日
						〜
						<?_WriteSelect($yearList, 'condition[itm_create_date_year_to]', $info['condition']['itm_create_date_year_to'], (++$tabindex), true, '&nbsp;');?>年
						<?_WriteSelect($monthList, 'condition[itm_create_date_month_to]', $info['condition']['itm_create_date_month_to'], (++$tabindex), true, '&nbsp;');?>月
						<?_WriteSelect($dayList, 'condition[itm_create_date_day_to]', $info['condition']['itm_create_date_day_to'], (++$tabindex), true, '&nbsp;');?>日
					</td>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td class="colHead">
						更新日
					</td>
					<td>
						<?_WriteSelect($yearList, 'condition[itm_update_date_year_from]', $info['condition']['itm_update_date_year_from'], (++$tabindex), true, '&nbsp;');?>年
						<?_WriteSelect($monthList, 'condition[itm_update_date_month_from]', $info['condition']['itm_update_date_month_from'], (++$tabindex), true, '&nbsp;');?>月
						<?_WriteSelect($dayList, 'condition[itm_update_date_day_from]', $info['condition']['itm_update_date_day_from'], (++$tabindex), true, '&nbsp;');?>日
						〜
						<?_WriteSelect($yearList, 'condition[itm_update_date_year_to]', $info['condition']['itm_update_date_year_to'], (++$tabindex), true, '&nbsp;');?>年
						<?_WriteSelect($monthList, 'condition[itm_update_date_month_to]', $info['condition']['itm_update_date_month_to'], (++$tabindex), true, '&nbsp;');?>月
						<?_WriteSelect($dayList, 'condition[itm_update_date_day_to]', $info['condition']['itm_update_date_day_to'], (++$tabindex), true, '&nbsp;');?>日
					</td>
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
if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] != AUTH_WOOROM) {
	//検索条件をhiddenで設定する。
	$condition4hidden = array();
	$condition4hidden['condition']['itm_del_flag'] = $info['condition']['itm_del_flag'];
	echo _CreateHidden($condition4hidden);
}
?>
		</form>

<?
//検索結果又は、メッセージが存在する場合、見出しを表示する。
if (!_IsNull($itemList) || !_IsNull($message)) {
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
if (!_IsNull($itemList)) {
?>
		<form id="frmUpdate" name="frmUpdate" action="<?=$_SERVER['PHP_SELF']?>" method="post">
			<div class="formWrapper">
				<div class="formList">
					<div class="page"><?=$link?></div>
					<table id="searchResultListTable">
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
								<td>商品ID</td>
								<td>カテゴリー</td>
								<td>サブカテゴリー</td>
								<td>商品コード</td>
								<td>商品名</td>
								<td>掲載</td>
<?if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] == AUTH_WOOROM) {?>
								<td>削除</td>
<?}?>
								<td>登録日</td>
								<td>更新日</td>
								<td>編集</td>
							</tr>
						</thead>

						<tbody>
<?
			$count = 0;
			foreach ($itemList as $key => $itemInfo) {
				$count++;
				
				$rowColorClass = null;
				if ($count % 2 == 0) {
					$rowColorClass = 'rowColor02';
				} else {
					$rowColorClass = 'rowColor01';
				}
		
				//以下の項目は、文字数を短くし、HTMLエンティティに変換する。
				//商品名	itm_name
				$itmName = _SubStr($itemInfo['itm_name'], 20);
				$itmName = htmlspecialchars($itmName);

				
				//背景色を設定する。
				//カテゴリー
				$bgColor4Category = null;
				if (isset($mstCategoryList[$itemInfo['itm_category_id']]['color']) && !_IsNull($mstCategoryList[$itemInfo['itm_category_id']]['color'])) {
					$bgColor4Category = "style=\"background-color:".$mstCategoryList[$itemInfo['itm_category_id']]['color'].";\"";
				}
				//サブカテゴリー
				$bgColor4Subcategory = null;
				if (isset($mstSubcategoryList[$itemInfo['itm_subcategory_id']]['color']) && !_IsNull($mstSubcategoryList[$itemInfo['itm_subcategory_id']]['color'])) {
					$bgColor4Subcategory = "style=\"background-color:".$mstSubcategoryList[$itemInfo['itm_subcategory_id']]['color'].";\"";
				}




				//文字をHTMLエンティティに変換する。
				$itemInfo = _HtmlSpecialCharsForArray($itemInfo);
?>
							<tr class="<?=$rowColorClass?>">
								<td class="colWidthItem01 colCenter"><?=_FormatNo($itemInfo['itm_item_id'])?></td>
								<td class="colWidthItem02" <?=$bgColor4Category?>><?=$itemInfo['itm_category_name']?></td>
								<td class="colWidthItem03" <?=$bgColor4Subcategory?>><?=$itemInfo['itm_subcategory_name']?></td>
								<td class="colWidthItem04"><?=$itemInfo['itm_code']?></td>
								<td class="colWidthItem05" title="<?=$itemInfo['itm_name']?>"><?=$itmName?></td>
								<td class="colWidthItem06 colCenter"><?=$showFlagList[$itemInfo['itm_show_flag']]['name2']?></td>
<?
				if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] == AUTH_WOOROM) {?>
								<td class="colWidthItem07 colCenter"><?=$delFlagList[$itemInfo['itm_del_flag']]['name2']?></td>
<?
				}
?>
								<td class="colWidthItem08 colCenter" title="<?=$itemInfo['itm_create_date_yyyymmddhhmmss']?>"><?=$itemInfo['itm_create_date_yymmdd']?></td>
								<td class="colWidthItem09 colCenter" title="<?=$itemInfo['itm_update_date_yyyymmddhhmmss']?>"><?=$itemInfo['itm_update_date_yymmdd']?></td>
								<td class="colWidthItem10 colCenter"><a class="edit" href="../info/?xml_name=<?=XML_NAME_ITEM?>&amp;id=<?=$itemInfo['itm_item_id']?>" title="編集">[編集]</a></td>
							</tr>
<?
			}
?>
						</tbody>
					</table>
					<div class="page"><?=$link?></div>
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

		<input type="hidden" name="token" value="<?=$_SESSION['token']?>" />
		
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

_Log("[/item/index.php] end.");

?>