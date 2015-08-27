<?php
/*
 * [管理画面]
 * マスタ更新画面
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
_Log("[/master/index.php] start.");

//認証チェック----------------------------------------------------------------------start
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/master/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/master/index.php] end.");
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
$undeleteOnly = true;

//動作モード{1:入力/2:完了(成功)/3:エラー}
$mode = 1;

//メッセージ
$message = "";
//メッセージ
$message4js = "";


//XMLを読み込む。
$xmlList = _GetXml("../common/form_xml/form_mst.xml");


//問い合わせ情報を格納する配列
$info = array();


//初期値を設定する。
switch ($_GET['mst_name']) {
//	case TBL_NAME_SITE:
//		//相互リンクサイトテーブル
//		$info['condition']['mst_name'] = $_GET['mst_name'];
//		$info['condition']['site_id'] = $_GET['site_id'];	//相互リンクサイトID
//
//		//入力一覧表を垂直方向にするか？{true:垂直方向(デフォルト)/false:水平方向}
//		$info['condition']['condition_vertical_direction_flag'] = false;
//		//入力一覧表の新規追加分を常にN個追加するか？{true:常にN個追加する(デフォルト)/false:合計N個になるように追加する}
//		$info['condition']['condition_add_type_flag'] = false;
//		//入力一覧表の新規追加分は必須項目としない？入力一覧表の全てが新規追加分の場合、1つ目は必須項目とするか？{true:必須としない(デフォルト)/false:1つ目は必須とする}
//		$info['condition']['condition_add_required_flag'] = false;
//		
//		//設定されている場合=更新の場合、新規追加分は追加しない。
//		if (isset($_GET['site_id'])) {
//			//入力一覧表に新規追加分を追加するか？{true:追加する(デフォルト)/false:追加しない}
//			$info['condition']['condition_add_flag'] = false;
//		
//			//動作モードをセッションに保存する。動作モード="他画面経由の表示"
//			$_SESSION[SID_MST_MODE] = MST_MODE_FROM_OTHER;
//		} else {
//			//動作モードをセッションに保存する。動作モード="単独表示"
//			$_SESSION[SID_MST_MODE] = MST_MODE_FROM_MENU;
//		}
// 		
//		break;
//	case MST_NAME_GENRE:
//		//ジャンルマスタ
//		$info['condition']['mst_name'] = $_GET['mst_name'];
//
//		//動作モードをセッションに保存する。動作モード="単独表示"
//		$_SESSION[SID_MST_MODE] = MST_MODE_FROM_MENU;
//
//		break;

	case MST_NAME_STAFF:
		//担当者マスタ
		$info['condition']['mst_name'] = $_GET['mst_name'];
		break;
	case MST_NAME_AFTEREFFECTS_GRADE_01:
		//後遺障害等級(級)マスタ
		$info['condition']['mst_name'] = $_GET['mst_name'];
		break;
	case MST_NAME_STATUS:
		//状況マスタ
		$info['condition']['mst_name'] = $_GET['mst_name'];
		break;
		
	case MST_NAME_SUBCATEGORY:
		//サブカテゴリーマスタ
		$info['condition']['mst_name'] = $_GET['mst_name'];
		break;
	case MST_NAME_CATEGORY:
		//カテゴリーマスタ
	default:
		$info['condition']['mst_name'] = MST_NAME_CATEGORY;
		break;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	//動作モードをセッションに保存する。動作モード="単独表示"
	$_SESSION[SID_MST_MODE] = MST_MODE_FROM_MENU;
}

//メッセージのタグを有効にするか？{true:有効にする/false:無効にする}
$messageTag = false;


//選択ボタンが押された場合
if ($_POST['select'] != "") {
	//入力値を取得する。
	$info = $_POST;
//	_Log("[/master/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/master/index.php] POST = '".print_r($info,true)."'");

//更新ボタンが押された場合
} elseif ($_POST['go'] != "") {
	//入力値を取得する。
	$info = $_POST;
//	_Log("[/master/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/master/index.php] POST(バックスラッシュを取り除く) = '".print_r($info,true)."'");
//	//「全角」数字を「半角」に変換する。
//	$info = _Mb_Convert_KanaForArray($info, 'n');
//	_Log("[/master/index.php] POST(「全角」数字を「半角」に変換する) = '".print_r($info,true)."'");

	//入力値チェック
	$errList = array();
	$message = _CheackMasterTable($xmlList, $info, $errList);
	if (count($errList) > 0) $info['error'] = $errList;

	//エラー入力がない場合、更新・登録をする。
	if (_IsNull($message)) {
		$returnList = array();
		$message = _UpdateMasterTable($xmlList, $info, $returnList);
		if (_IsNull($message)) {
			//入力値をクリアして、再表示する。
			unset($info['update']);
			if ($returnList['count']['update'] == 0 && $returnList['count']['create'] == 0) {
				$message = "変更箇所がありません。";
			} else {
				$message = "更新しました。(更新：".$returnList['count']['update']."件 / 登録：".$returnList['count']['create']."件)";
			}
			
//			//※特別処理---------start
//			//相互リンクサイトテーブルの場合
//			if ($info['condition']['mst_name'] == TBL_NAME_SITE) {
//				//新規登録の場合、登録した情報を再表示するため条件にIDを設定する。
//				//新規登録したIDを取得する。
//				$createIdList = $returnList['create']['id'];
//				
//				_Log("[/master/index.php] 新規登録したID = '".print_r($createIdList,true)."'");
//				_Log("[/master/index.php] 更新したID = '".print_r($info['condition']['site_id'],true)."'");
//				
//				//新規登録したIDに元々条件に設定済みのID(更新したID)を追加する。
//				if (is_array($info['condition']['site_id'])) {
//					//配列の場合、配列のマージをする。
//					$createIdList = array_merge($createIdList, $info['condition']['site_id']);
//				} else {
//					//配列以外の場合、配列に追加する。
//					$createIdList[] = $info['condition']['site_id'];
//				}
//
//				_Log("[/master/index.php] 新規登録したID＋更新したID = '".print_r($createIdList,true)."'");
//
//				//検索条件に上書きする。
//				$info['condition']['site_id'] = $createIdList;
//				
//				//動作モード="他画面経由の表示"の場合、レベルを3にする。
//				if ($_SESSION[SID_MST_MODE] == MST_MODE_FROM_OTHER) {
//					$message .= "\n";
//					$message .= "\n";
//					$message .= "<a href=\"../site/?back\" title=\"相互リンク一覧に戻る\">[相互リンク一覧に戻る]</a>\n";
//					
//					$messageTag = true;
//				}
//			}
//			//※特別処理---------end
			
			
			
		} else {
			//エラー有りを伝えるため。
			$info['error'] = true;
		}

	} else {
		$message = "※入力に誤りがあります。\n".$message;
	}
	$info['message'] = $message;

}





////文字をHTMLエンティティに変換する。
//$info = _HtmlSpecialCharsForArray($info);

//echo ("\$info='".print_r($info,true)."'");

//パンくずリスト情報を設定する。
switch ($info['condition']['mst_name']) {
	case MST_NAME_STAFF:
		//担当者マスタ
		$breadcrumbsLabel = '担当者';
		break;
	case MST_NAME_AFTEREFFECTS_GRADE_01:
		//後遺障害等級(級)マスタ
		$breadcrumbsLabel = '後遺障害等級';
		break;
	case MST_NAME_STATUS:
		//状況マスタ
		$breadcrumbsLabel = '状況';
		break;

	case MST_NAME_SUBCATEGORY:
		//サブカテゴリーマスタ
		$breadcrumbsLabel = 'サブカテゴリー';
		break;
	case MST_NAME_CATEGORY:
		//カテゴリーマスタ
	default:
		$breadcrumbsLabel = 'カテゴリー';
		break;
}


//パンくずリストのレベル(階層)
$level = 2;
//動作モード="他画面経由の表示"の場合、レベルを3にする。
if ($_SESSION[SID_MST_MODE] == MST_MODE_FROM_OTHER) $level = 3;
_SetBreadcrumbs($_SERVER['PHP_SELF'], '', $breadcrumbsLabel, $level);

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
<title><?=$clientName?></title>
</head>

<body id="home" onload="openBox('explain_sub', 'explain', 'explain_close');">
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
		<?=_GetMasterTable($xmlList, $info, $tabindex, $loginInfo['mng_auth_id'], false, $messageTag);?>
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

_Log("[/master/index.php] end.");

?>