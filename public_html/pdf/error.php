<?php
/*
 * [管理画面]
 * PDF作成エラー画面
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
_Log("[/pdf/error.php] start.");


//サイト名
$clientName = ADMIN_TITLE;


_Log("[/pdf/error.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/error.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/error.php] SERVER = '".print_r($_SERVER,true)."'");
_Log("[/pdf/error.php] SESSION = '".print_r($_SESSION,true)."'");


//エラーメッセージを取得する。
$errorList = $_SESSION[SID_PDF_ERR_MSG];

_Log("[/pdf/error.php] エラーメッセージ = '".print_r($errorList,true)."'");

//文字をHTMLエンティティに変換する。
$errorList = _HtmlSpecialCharsForArray($errorList);
_Log("[/pdf/error.php] エラーメッセージ(文字をHTMLエンティティに変換する。) = '".print_r($errorList,true)."'");

//メッセージ
$message = "";

foreach ($errorList as $error) {
	if (!_IsNull($message)) $message .= "\n";
	$message .= $error;
}


if (!_IsNull($message)) {
	$message = "<div class=\"message errorMessage\">".nl2br($message)."</div>";
}



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
		<?//include_once("../common_html/side_menu.php");?>
	</div><!-- End sidebar -->

	<div class="breadcrumbs">
		<?=$breadcrumbs = null;//_GetBreadcrumbs();?>
	</div><!-- End breadcrumbs -->

	<div id="maincontent">
		<?=$message?>
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

_Log("[/pdf/error.php] end.");

?>