<?php
/*
 * [相互リンク管理画面]
 * 相互リンクサイト一覧画面
 *
 * 更新履歴：2007/10/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/site/index.php] start.");

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
$mstOwnSiteList = _GetMasterList('mst_own_site', $undeleteOnly);			//自社サイトマスタ
$mstGenreList = _GetMasterList('mst_genre', $undeleteOnly);				//ジャンルマスタ
$mstLinkStatusList = _GetMasterList('mst_link_status', $undeleteOnly);	//リンク状況マスタ

$yearList = _GetYearArray(SYSTEM_START_YEAR, date('Y') + 3);				//年
$monthList = _GetMonthArray();												//月
$dayList = _GetDayArray();													//日
//削除フラグ
$delFlagList = array(
			 DELETE_FLAG_NO => array('id' => DELETE_FLAG_NO, 'name' => DELETE_FLAG_NO_NAME, 'no_name' => '')
			,DELETE_FLAG_YES => array('id' => DELETE_FLAG_YES, 'name' => DELETE_FLAG_YES_NAME, 'no_name' => '')
			);
//関連付けフラグ
$siteRelationFlagList = array(
			 SITE_RELATION_FLAG_YES => array('id' => SITE_RELATION_FLAG_YES, 'name' => SITE_RELATION_FLAG_YES_NAME, 'no_name' => '')
			,SITE_RELATION_FLAG_NO => array('id' => SITE_RELATION_FLAG_NO, 'name' => SITE_RELATION_FLAG_NO_NAME, 'no_name' => '')
			);
//相互リンクサイト一覧表示モード
$siteListModeList = array(
			 SITE_LIST_MODE_NORMAL => array('id' => SITE_LIST_MODE_NORMAL, 'name' => SITE_LIST_MODE_NORMAL_NAME, 'no_name' => '')
			,SITE_LIST_MODE_MATRIX => array('id' => SITE_LIST_MODE_MATRIX, 'name' => SITE_LIST_MODE_MATRIX_NAME, 'no_name' => '')
			);


//動作モード{1:入力/2:完了(成功)/3:エラー}
$mode = 1;

//認証チェック
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/site/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/site/index.php] end.");
	//ログイン画面を表示する。
	header("Location: ../");
	exit;
}
//ログイン情報を取得する。
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//メッセージ
$message = "";
//メッセージ
$message4js = "";


//問い合わせ情報を格納する配列
$info = array();
//初期値を設定する。
//[自社サイト検索条件]
$info['condition']['own_site_del_flag'] = array(DELETE_FLAG_NO);					//自社サイト.削除フラグ="未削除"
//[相互リンクサイト検索条件]
$info['condition']['site_del_flag'] = array(DELETE_FLAG_NO);						//相互リンクサイト.削除フラグ="未削除"
//[リンク状況検索条件]
$info['condition']['site_relation_flag'] = array(SITE_RELATION_FLAG_YES);			//関連付け状況="済"
$info['condition']['site_relation_link_status_id'] = MST_LINK_STATUS_ID_SENT;		//リンク状況="打診メール送信済み"
$info['condition']['site_relation_total_days_from'] = 14;							//リンク登録日="14日以上(2週間経過)"


//選択ボタンが押された場合
if ($_POST['select'] != "") {
	//入力値を取得する。
	$info = $_POST;
	_Log("[/site/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/site/index.php] POST(バックスラッシュを取り除く) = '".print_r($info,true)."'");
	
	//入力値変換
	//「全角」数字を「半角」に変換する。------------------------------------------------------------
	//登録日数From
	$info['condition']['site_relation_total_days_from'] = mb_convert_kana($info['condition']['site_relation_total_days_from'], "n");
	//登録日数To
	$info['condition']['site_relation_total_days_to'] = mb_convert_kana($info['condition']['site_relation_total_days_to'], "n");
	
	//入力値チェック
	//半角数字チェック------------------------------------------------------------------------------
	//登録日数From
	if (!_IsHalfSizeNumeric($info['condition']['site_relation_total_days_from'])) $info['condition']['site_relation_total_days_from'] = null;
	//登録日数To
	if (!_IsHalfSizeNumeric($info['condition']['site_relation_total_days_to'])) $info['condition']['site_relation_total_days_to'] = null;
	
	
	//相互リンクを検索する。
	$order = null;
	$siteList = _GetSite($info['condition'], $order, false);
	
	if (_IsNull($siteList)) {
		$message = "検索条件に該当する情報が存在しません。";
	}
	
	//検索結果をセッションに保存する。
	$_SESSION[SID_SRCH_SITE_LIST] = $siteList;
	
//更新ボタンが押された場合
} elseif ($_POST['go'] != "") {
	//入力値を取得する。
	$info = $_POST;
	_Log("[/site/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/site/index.php] POST(バックスラッシュを取り除く) = '".print_r($info,true)."'");
//	//「全角」数字を「半角」に変換する。
//	$info = _Mb_Convert_KanaForArray($info, 'n');
//	_Log("[/site/index.php] POST(「全角」数字を「半角」に変換する) = '".print_r($info,true)."'");

	//入力値チェック

	//セッションから検索結果を取得する。
	$siteList = $_SESSION[SID_SRCH_SITE_LIST];

	if (isset($info['update'])) {
		_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} -------------------- 開始");
		$count = 0;
		foreach ($info['update'] as $key => $updateInfo) {
			$count++;
			
			//登録、更新、削除後に最新のDB情報で検索結果を上書きする。{true:上書きする/false:しない}
			$overwriteFlag = false;

			_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} key = '".$key."' ================================");
			_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} (更新情報)関連付けフラグ = '".$updateInfo['site_relation_flag']."'");
			_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} (更新情報)リンク状況ID = '".$updateInfo['link_status_id']."'");

			//更新前の情報を取得する。
			$oldInfo = $siteList[$key];
			_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} (DB情報)関連付けフラグ = '".$oldInfo['site_relation_flag']."'");
			_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} (DB情報)リンク状況ID = '".$oldInfo['link_status_id']."'");

			
			//関連付けフラグをチェックする。
			if (isset($updateInfo['site_relation_flag'])) {
				//関連付けフラグ=ONの場合

				_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 1.関連付けフラグ=ONの場合");


				if ($oldInfo['site_relation_flag'] == SITE_RELATION_FLAG_YES) {
					//元々、関連付け済の場合
					_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 1-1.元々、関連付け済の場合");

					//変更有無をチェックする。
					$updateFlag = false;
					//リンク状況
					if ($oldInfo['link_status_id'] != $updateInfo['link_status_id']) $updateFlag = true;
					
					if ($updateFlag) {
						_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 1-1-1.変更有り---更新する。");
						
						//更新情報を追加設定する。
						$updateInfo['del_flag'] = DELETE_FLAG_NO;				//削除フラグ
						$updateInfo['update_ip'] = $_SERVER["REMOTE_ADDR"];	//更新IP
						$updateInfo['update_date'] = null;					//更新日
						
						//更新する。
						$res = _DB_SaveInfo('tbl_site_relation', $updateInfo);
						if ($res === false) {
							$message .= "No."._FormatNo($count)."の更新に失敗しました。再度実行をお願いします。\n";
							_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 1-1-1-2.【更新失敗】");
						} else {
							$message .= "No."._FormatNo($count)."を更新しました。\n";
							_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 1-1-1-1.【更新成功】");
							
							$overwriteFlag = true;
						}
					} else {
						_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 1-1-2.変更無し---更新無し、次へ。");
					}

				} else {
					//元々、未関連付けの場合
					_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 1-2.元々、未関連付けの場合---新規登録する。");
					
					//リンク状況が未選択の場合、初期値として"自社サイト掲載済み"(1)を設定する。
					if (_IsNull($updateInfo['link_status_id'])) $updateInfo['link_status_id'] = MST_LINK_STATUS_ID_CARRIED;

					//更新情報を追加設定する。
					$updateInfo['del_flag'] = DELETE_FLAG_NO;				//削除フラグ
					$updateInfo['create_ip'] = $_SERVER["REMOTE_ADDR"];	//作成IP
					$updateInfo['create_date'] = null;					//作成日					
					$updateInfo['update_ip'] = $_SERVER["REMOTE_ADDR"];	//更新IP
					$updateInfo['update_date'] = null;					//更新日					
					
					//登録する。
					$res = _DB_CreateInfo('tbl_site_relation', $updateInfo);
					if ($res === false) {
						$message .= "No."._FormatNo($count)."の登録に失敗しました。再度実行をお願いします。\n";
						_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 1-2-2.【登録失敗】");
					} else {
						$message .= "No."._FormatNo($count)."を登録しました。\n";
						_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 1-2-1.【登録成功】");
						
						$overwriteFlag = true;
					}
				}

			} else {
				//関連付けフラグ=OFFの場合

				_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 2.関連付けフラグ=OFFの場合");

				if ($oldInfo['site_relation_flag'] == SITE_RELATION_FLAG_YES) {
					//元々、関連付け済の場合
					_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 2-1.元々、関連付け済の場合---削除する。");

					//削除情報を追加設定する。(主キーのみ)
					$deleteInfo = array();
					$deleteInfo['site_id'] = $updateInfo['site_id'];				//相互リンクサイトID
					$deleteInfo['own_site_id'] = $updateInfo['own_site_id'];		//自社サイトID
					
					//削除する。
					$res = _DB_DeleteInfo('tbl_site_relation', $deleteInfo);
					if ($res === false) {
						$message .= "No."._FormatNo($count)."の削除に失敗しました。再度実行をお願いします。\n";
						_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 2-1-2.【削除失敗】");
					} else {
						$message .= "No."._FormatNo($count)."を削除しました。\n";
						_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 2-1-1.【削除成功】");
						
						$overwriteFlag = true;
					}
				} else {
					//元々、未関連付けの場合
					_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 2-2.元々、未関連付けの場合---削除無し、次へ。");
				}
			}
			
			//登録、更新、削除があった場合、最新のDB情報で検索結果を上書きする。
			if ($overwriteFlag) {
				_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 3.登録、更新、削除があった場合、最新のDB情報で検索結果を上書きする。");
				
				$condition4new = array();
				$condition4new['site_id'] = $updateInfo['site_id'];//相互リンクサイトID
				$condition4new['own_site_id'] = $updateInfo['own_site_id'];//自社サイトID
				$newSiteList = _GetSite($condition4new, null, false);
				
				if (_IsNull($newSiteList)) {
					_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 3-2.【上書き失敗】");
				} else {
					$siteList[$key] = $newSiteList[0];
					_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} 3-1.【上書き成功】");
				}
				

				//上書きした検索結果をセッションに保存する。
				$_SESSION[SID_SRCH_SITE_LIST] = $siteList;
			}
			
		}
		
		_Log("[/site/index.php] {自社サイト-相互リンクサイト関連付} -------------------- 終了");

		//セッションから検索結果を再取得する。
		$siteList = $_SESSION[SID_SRCH_SITE_LIST];

		//ここまででメッセージが空の場合、登録、更新、削除がなかった。
		if (_IsNull($message)) {
			$message = "変更箇所がありません。";
		}

	}

//検索条件クリアボタンが押された場合
} elseif ($_POST['clear'] != "") {
	unset($info['condition']);
	unset($_SESSION[SID_SRCH_SITE_LIST]);
//初回起動時
} else {
	//相互リンクを検索する。
	$order = null;
	$siteList = _GetSite($info['condition'], $order, false);
	
	if (_IsNull($siteList)) {
		$message = "検索条件に該当する情報が存在しません。";
	}
	
	//検索結果をセッションに保存する。
	$_SESSION[SID_SRCH_SITE_LIST] = $siteList;
}



//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
////文字をHTMLエンティティに変換する。
//$siteList = _HtmlSpecialCharsForArray($siteList);

//echo ("\$info='".print_r($info,true)."'");


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
<title><?=$clientName?></title>
</head>

<body id="home" onload="openBox('explain_sub', 'explain', 'explain_close');">
<div id="wrapper">
	<div id="header">
		<?include_once("../common_html/header.php");?>
	</div><!-- End header -->

	<div id="sidebar">
		<?include_once("../common_html/side_menu.php");?>
	</div><!-- End sidebar -->

	<div id="maincontent">
		<h2>相互リンクサイト一覧</h2>
		<h3>検索条件</h3>
		
		<form id="frmSelect" name="frmSelect" action="<?=$_SERVER['PHP_SELF']?>" method="post">
			<h4 id="ownSite">[自社サイト検索条件]</h4>
			<table class="siteConditionTable">
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				
				<tr>
					<td class="colHead">
						サイトタイトル
					</td>
					<td>
						<input type="text" name="condition[own_site_title]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['own_site_title']?>" />
					</td>
					<td class="colHead" rowspan="3">
						自社サイト
					</td>
					<td rowspan="3">
						<?_WriteSelect($mstOwnSiteList, 'condition[own_site_id]', $info['condition']['own_site_id'], (++$tabindex), false, "&nbsp;", 4, true, 'id', 'name_del_2', 'id', 'class="multiple"');?>
					</td>
					<td class="colHead" rowspan="3">
						ジャンル
					</td>
					<td rowspan="3">
						<?_WriteSelect($mstGenreList, 'condition[own_site_genre_id]', $info['condition']['own_site_genre_id'], (++$tabindex), false, "&nbsp;", 4, true, 'id', 'name_del_2', 'id', 'class="multiple"');?>
					</td>
				</tr>
				<tr>
					<td class="colHead">
						サイトURL
					</td>
					<td>
						<input type="text" name="condition[own_site_url]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['own_site_url']?>" />
					</td>
				</tr>
				<tr>
					<td class="colHead">
						削除フラグ
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($delFlagList, 'condition[own_site_del_flag]', $info['condition']['own_site_del_flag'], (++$tabindex));?>
					</td>
				</tr>
			</table>

			<h4 id="linkSite">[相互リンクサイト検索条件]</h4>
			<table class="siteConditionTable">
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
								
				<tr>
					<td class="colHead">
						サイトタイトル
					</td>
					<td>
						<input type="text" name="condition[site_title]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['site_title']?>" />
					</td>
					<td class="colHead">
						担当者名
					</td>
					<td>
						<input type="text" name="condition[site_staff_name]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['site_staff_name']?>" />
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
						サイトURL
					</td>
					<td>
						<input type="text" name="condition[site_url]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['site_url']?>" />
					</td>
					<td class="colHead">
						削除フラグ
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($delFlagList, 'condition[site_del_flag]', $info['condition']['site_del_flag'], (++$tabindex));?>
					</td>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
			</table>
			
			<h4 id="linkStatus">[リンク状況検索条件]</h4>
			<table class="siteConditionTable">
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
								
				<tr>
					<td class="colHead">
						関連付け状況
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($siteRelationFlagList, 'condition[site_relation_flag]', $info['condition']['site_relation_flag'], (++$tabindex));?>
					</td>
					<td class="colHead" rowspan="3">
						登録日
					</td>
					<td rowspan="3">
						<?_WriteSelect($yearList, 'condition[site_relation_create_date_year_from]', $info['condition']['site_relation_create_date_year_from'], (++$tabindex), true, '年');?>
						<?_WriteSelect($monthList, 'condition[site_relation_create_date_month_from]', $info['condition']['site_relation_create_date_month_from'], (++$tabindex), true, '月');?>
						<?_WriteSelect($dayList, 'condition[site_relation_create_date_day_from]', $info['condition']['site_relation_create_date_day_from'], (++$tabindex), true, '日');?>
						<br />
						〜
						<br />
						<?_WriteSelect($yearList, 'condition[site_relation_create_date_year_to]', $info['condition']['site_relation_create_date_year_to'], (++$tabindex), true, '年');?>
						<?_WriteSelect($monthList, 'condition[site_relation_create_date_month_to]', $info['condition']['site_relation_create_date_month_to'], (++$tabindex), true, '月');?>
						<?_WriteSelect($dayList, 'condition[site_relation_create_date_day_to]', $info['condition']['site_relation_create_date_day_to'], (++$tabindex), true, '日');?>
					</td>
					<td class="colHead" rowspan="3">
						リンク状況
					</td>
					<td rowspan="3">
						<?_WriteSelect($mstLinkStatusList, 'condition[site_relation_link_status_id]', $info['condition']['site_relation_link_status_id'], (++$tabindex), false, "&nbsp;", 4, true, 'id', 'name_del_2', 'id', 'class="multiple"');?>
					</td>
				</tr>
				<tr>
					<td class="colHead">
						登録日数
					</td>
					<td>
						<input type="text" name="condition[site_relation_total_days_from]" size="4" maxlength="4" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['site_relation_total_days_from']?>" />
						日
						〜
						<input type="text" name="condition[site_relation_total_days_to]" size="4" maxlength="4" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['site_relation_total_days_to']?>" />
						日
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

			<h4 id="listMode">[表示条件]</h4>
			<table class="siteConditionTable">
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>

				<tr>
					<td class="colHead">
						表示モード
					</td>
					<td colspan="5">
						<?$tabindex = _WriteRadio($siteListModeList, 'condition[site_list_mode]', $info['condition']['site_list_mode'], (++$tabindex));?>
					</td>
				</tr>

			</table>

			<div class="button">
				<input class="submit" type="submit" name="select" value="　検　索　" tabindex="<?=(++$tabindex)?>" />
				&nbsp;
				<input class="submit" type="submit" name="clear" value="　クリア　" tabindex="<?=(++$tabindex)?>" />
			</div>
		</form>

<?
//検索結果又は、メッセージが存在する場合、見出しを表示する。
if (!_IsNull($siteList) || !_IsNull($message)) {
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
if (!_IsNull($siteList)) {
?>	
		<form id="frmUpdate" name="frmUpdate" action="<?=$_SERVER['PHP_SELF']?>" method="post">
			<div class="formWrapper">
				<div class="formList">
					<table id="siteListTable">
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

						<thead>
							<tr>
								<td rowspan="2">No</td>
								<td class="colOwnSite" rowspan="2">自社サイト</td>
								<td class="colLinkSite" colspan="3">相互リンクサイト</td>
								<td class="colLinkStatus" colspan="5">リンク情報</td>
							</tr>
							<tr>
								<td class="colLinkSite">サイトタイトル</td>
								<td class="colLinkSite">サイトURL</td>
								<td class="colLinkSite">担当者名</td>
								<td class="colLinkStatus" colspan="2">関連付(登録日)</td>
								<td class="colLinkStatus">日数</td>
								<td class="colLinkStatus">リンク状況</td>
								<td class="colLinkStatus">&nbsp;</td>
							</tr>
						</thead>

						<tbody>
<?
	$count = 0;
	foreach ($siteList as $key => $siteInfo) {
		$count++;
		
		$rowColorClass = null;
		if ($count % 2 == 0) {
			$rowColorClass = 'rowColor02';
		} else {
			$rowColorClass = 'rowColor01';
		}

		//以下の項目は、文字数を短くし、HTMLエンティティに変換する。
		//自社サイトタイトル
		$ownSiteTitle = _SubStr($siteInfo['own_site_title'], 10);
		$ownSiteTitle = htmlspecialchars($ownSiteTitle);
		//相互リンクサイトタイトル
		$siteTitle = _SubStr($siteInfo['title'], 10);
		$siteTitle = htmlspecialchars($siteTitle);
		//相互リンクサイトURl
		$siteUrl = _SubStr($siteInfo['url'],20);
		$siteUrl = htmlspecialchars($siteUrl);
		//相互リンクサイト担当者
		$siteStaffName = _SubStr($siteInfo['family_name']." ".$siteInfo['first_name'], 6);
		$siteStaffName = htmlspecialchars($siteStaffName);
		
		//文字をHTMLエンティティに変換する。
		$siteInfo = _HtmlSpecialCharsForArray($siteInfo);
		
?>
							<tr class="<?=$rowColorClass?>">
								<td class="colWidth01 colCenter"><?=_FormatNo($count)?></td>
								<td class="colWidth02" title="<?=$siteInfo['own_site_title']?>"><?=$ownSiteTitle?></td>
								<td class="colWidth03" title="<?=$siteInfo['title']?>"><a href="../master/?mst_name=<?=TBL_NAME_SITE?>&amp;site_id=<?=$siteInfo['site_id']?>" title="相互リンクサイト更新"><?=$siteTitle?></a></td>
								<td class="colWidth04"><a href="<?=$siteInfo['url']?>" title="<?=$siteInfo['url']?>" target="_blank"><?=$siteUrl?></a></td>
								<td class="colWidth05" title="<?=$siteInfo['family_name']." ".$siteInfo['first_name']?>"><?=$siteStaffName?></td>
								<td class="colWidth06 colBorderDotted">
									<input type="checkbox" id="site_relation_flag_<?=$key?>" name="update[<?=$key?>][site_relation_flag]" value="<?=SITE_RELATION_FLAG_YES?>" tabindex="<?=(++$tabindex)?>" <?=($siteInfo['site_relation_flag']==SITE_RELATION_FLAG_YES?"checked=\"checked\"":"")?> />
									<input type="hidden" name="update[<?=$key?>][site_id]" value="<?=$siteInfo['site_id']?>" />
									<input type="hidden" name="update[<?=$key?>][own_site_id]" value="<?=$siteInfo['own_site_id']?>" />
								</td>
								<td class="colWidth07"><label for="site_relation_flag_<?=$key?>" style="display:block;width:100%;height:100%;"><?=(_IsNull($siteInfo['site_relation_create_date_yymmdd'])?"&nbsp;":$siteInfo['site_relation_create_date_yymmdd'])?></label></td>
								<td class="colWidth08 colNum"><?=$siteInfo['total_days']?></td>
								<td class="colWidth09"><?_WriteSelect($mstLinkStatusList, 'update['.$key.'][link_status_id]', $siteInfo['link_status_id'], (++$tabindex), ($siteInfo['site_relation_flag']==SITE_RELATION_FLAG_YES?false:true), "&nbsp;", 1, false, 'id', 'name_del_2');?></td>
								<td class="colWidth10"><a class="mail" href="../mail/?site_id=<?=$siteInfo['site_id']?>&amp;own_site_id=<?=$siteInfo['own_site_id']?>" title="打診メール">[打診]</a></td>
							</tr>
<?
	}
?>
						
						</tbody>
					</table>


					<table>
						<thead>
						</thead>
						<tbody>
						</tbody>
					</table>

				</div>
			</div>

			<div class="button">
				<input class="submit" type="submit" name="go" value="　更　新　" tabindex="<?=(++$tabindex)?>" />
			</div>

<?
//検索条件をhiddenで設定する。
$condition4hidden = array();
$condition4hidden['condition'] = $info['condition'];
echo _CreateHidden($condition4hidden);
?>

		</form>
<?	
}
?>
	</div><!-- End maincontent -->

	<div id="footer">
		<?include_once("../common_html/footer.php");?>
	</div><!-- End footer -->

</div><!-- End wrapper -->
</body>
</html>

<?
//DBをクローズする。
_DB_Close($cid);

_Log("[/site/index.php] end.");

?>