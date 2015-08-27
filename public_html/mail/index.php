<?php
/*
 * [相互リンク管理画面]
 * 打診メール送信画面
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
_Log("[/mail/index.php] start.");

//本ファイルの名称を取得する。
$phpName = basename($_SERVER['PHP_SELF']);
//フォームのアクションを設定する。
//$formAction = SSL_URL_THE_LIFEBOAT_COM_INQ.'/'.$phpName;
$formAction = $_SERVER['PHP_SELF'];

_Log("[/mail/index.php] dirname(\$_SERVER['PHP_SELF']) = '".dirname($_SERVER['PHP_SELF'])."'");

//通常のURL(SSLではないURL)
$urlBase = URL_BASE;

//サイト名
$clientName = ADMIN_TITLE;


//マスター用メールアドレス
//「,」でくぎって送信先を追加して下さい。
$listMasterMail = array("ishikawa@woorom.com");


//タブインデックス
$tabindex = 0;

//DBをオープンする。
$cid = _DB_Open();

//マスタ情報を取得する。
$undeleteOnly = true;

//動作モード{1:入力/2:確認/3:完了/4:エラー}
$mode = 1;

//認証チェック
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/mail/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/mail/index.php] end.");
	//ログイン画面を表示する。
	header("Location: ../");
	exit;
}
//ログイン情報を取得する。
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//メッセージ
$message = "";
//エラーフラグ
$errorFlag = false;

//メッセージ
$message4js = "";


//----------------------------------------------------------------------------------------
//パラメーターを取得する。
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		//相互リンクサイトID
		$siteId = $_POST['site_id'];
		//自社サイトID
		$ownSiteId = $_POST['own_site_id'];
		break;
	case 'GET':
		//相互リンクサイトID
		$siteId = $_GET['site_id'];
		//自社サイトID
		$ownSiteId = $_GET['own_site_id'];
		break;	
}

//パラメーターからDB情報を取得する。
//相互リンクサイト情報を取得する。
$condition = array();
$condition['site_id'] = $siteId;
$siteInfo = _DB_GetInfo('tbl_site', $condition, false);

//自社サイトマスタ情報を取得する。
$condition = array();
$condition['id'] = $ownSiteId;
$mstOwnSiteInfo = _DB_GetInfo('mst_own_site', $condition, false);
//----------------------------------------------------------------------------------------

//差出人のE-Mailを設定する。
$fromEmailList = array(
 '2' => array('id' => '2', 'name' => 'yamada@woorom.com')
,'3' => array('id' => '3', 'name' => 'ishikawa@woorom.com')
);

//自社サイトマスタ情報が取得できた場合、自社サイトのメールアドレスも追加設定する。
if (!_IsNull($mstOwnSiteInfo)) {
	if (isset($mstOwnSiteInfo['e_mail']) && !_IsNull($mstOwnSiteInfo['e_mail']))	{
		$fromEmailList['1'] = array('id' => '1', 'name' => $mstOwnSiteInfo['e_mail']);
		ksort($fromEmailList);
	}
}


$otherList = array();
$otherList['from_e_mail_list'] = $fromEmailList;

//XMLを読み込む。
$xmlList = _GetXml("../common/form_xml/form_mail.xml", $otherList);



//問い合わせ情報を格納する配列
$info = array();
//初期値を設定する。
$info['mail_template_id'] = MST_MAIL_TEMPLATE_ID_CORPORATION;//メールテンプレート="法人用"

//メールテンプレート読み込みフラグ{true:読み込む/false:読み込まない}
$mailTemplateFlag = false;



//選択ボタンが押された場合
if ($_POST['select'] != "") {
	//入力値を取得する。
	$info = $_POST;
	_Log("[/mail/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/mail/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

	//打診メールテンプレートが選択された場合
	if (isset($info['mail_template_id'])) {
		if (_IsNull($info['mail_template_id'])) {
			//空を選択した場合、以下の項目をクリアする。
			$info['mail_subject'] = null;		//メール件名	
			$info['mail_body'] = null;		//メール本文
		} else {
			$mailTemplateFlag = true;
		}
	}

//確認ボタンが押された場合
} elseif ($_POST['confirm'] != "") {
	//入力値を取得する。
	$info = $_POST;
	_Log("[/mail/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/mail/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

	//入力値チェック

//	//利用規約
//	//必須
//	if (_IsNull($info['rule'])) $message .= "利用規約に同意するにチェックをして下さい。<br />";

	$message .= _CheackInputAll($xmlList, $info);


	if (_IsNull($message)) {
		//エラーが無い場合、確認画面を表示する。
		$mode = 2;
		 
		$message .= "※入力内容を確認して、「送信」ボタンを押してください。";
	} else {
		//エラーが有り場合
		$message = "※入力に誤りがあります。\n".$message;
		$errorFlag = true;
	}
}
//戻るボタンが押された場合
elseif ($_POST['back'] != "") {
	//入力値を取得する。
	$info = $_POST;
	_Log("[/mail/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/mail/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");
}
//送信ボタンが押された場合
elseif ($_POST['go'] != "") {
	//入力値を取得する。
	$info = $_POST;
	_Log("[/mail/index.php] POST = '".print_r($info,true)."'");
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/mail/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

//	//メール本文の共通部分を設定する。
//	$body = "";
//	$body .= _CreateMailAll($xmlList, $info);
//

	//メール件名を設定する。
	$mailSubject = $info['mail_subject'];
	//メール本文を設定する。
	$mailBody = $info['mail_body'];
	//差出人E-Mailを設定する。
	$fromEMail =  $fromEmailList[$info['from_e_mail_id']]['name'];

	//メール本文中の差出人E-Mailを置換する。
	$mailBody = str_replace ('{from_e_mail}', $fromEMail, $mailBody);


	mb_language("Japanese");


	//メール送信
	//相互リンクサイト担当者様(お客様)に送信する。
	$rcd = mb_send_mail($info['e_mail'], $mailSubject, $mailBody, "from:".$fromEMail);
	//選択した差出人E-Mailに送信する。
	$rcd = mb_send_mail($fromEMail, $mailSubject, $mailBody, "from:".$fromEMail);

	//マスターに送信する。
	if (!_IsNull($listMasterMail)) {
		foreach($listMasterMail as $masterMail){
			$rcd = mb_send_mail($masterMail, $mailSubject, $mailBody, "from:".$fromEMail);
		}
	}
	
	$message .= "打診メールを送信しました。\n";

	_Log("[/mail/index.php] {自社サイト-相互リンクサイト関連付}-----------------------------------開始");
	//DB更新
	//自社サイト-相互リンクサイト関連付情報を取得する。
	$condition = array();
	$condition['site_id'] = $info['site_id'];//相互リンクサイトID
	$condition['own_site_id'] = $info['own_site_id'];//自社サイトID
	$tblSiteRelationInfo = _DB_GetInfo('tbl_site_relation', $condition, false);
	if (_IsNull($tblSiteRelationInfo)) {
		//自社サイト-相互リンクサイト関連付情報が取得できなかった場合→新規登録する。
		_Log("[/mail/index.php] {自社サイト-相互リンクサイト関連付} 1.自社サイト-相互リンクサイト関連付情報が取得できなかった場合→新規登録する。");

		$createInfo = array();
		$createInfo['site_id'] = $info['site_id'];					//相互リンクサイトID
		$createInfo['own_site_id'] = $info['own_site_id'];			//自社サイトID
		$createInfo['link_status_id'] = MST_LINK_STATUS_ID_SENT;		//リンク状況ID="打診メール送信済み"(2)
		$createInfo['del_flag'] = DELETE_FLAG_NO;						//削除フラグ
		$createInfo['create_ip'] = $_SERVER["REMOTE_ADDR"];			//作成IP
		$createInfo['create_date'] = null;							//作成日					
		$createInfo['update_ip'] = $_SERVER["REMOTE_ADDR"];			//更新IP
		$createInfo['update_date'] = null;							//更新日					
		
		//登録する。
		$res = _DB_CreateInfo('tbl_site_relation', $createInfo);
		if ($res === false) {
			$message .= "自社サイト-相互リンクサイト関連付情報の登録に失敗しました。再登録をお願いします。\n";
			$errorFlag = true;
			_Log("[/mail/index.php] {自社サイト-相互リンクサイト関連付} 1-2.【登録失敗】");
		} else {
			$message .= "自社サイト-相互リンクサイト関連付情報を登録しました。\n";
			_Log("[/mail/index.php] {自社サイト-相互リンクサイト関連付} 1-1.【登録成功】");
		}

	} else {
		//自社サイト-相互リンクサイト関連付情報が取得できた場合→更新する。
		_Log("[/mail/index.php] {自社サイト-相互リンクサイト関連付} 2.自社サイト-相互リンクサイト関連付情報が取得できた場合→更新する。");

		$tblSiteRelationInfo['link_status_id'] = MST_LINK_STATUS_ID_SENT;		//リンク状況ID="打診メール送信済み"(2)
		$tblSiteRelationInfo['update_ip'] = $_SERVER["REMOTE_ADDR"];			//更新IP
		$tblSiteRelationInfo['update_date'] = null;							//更新日			

		//更新する。
		$res = _DB_SaveInfo('tbl_site_relation', $tblSiteRelationInfo);
		if ($res === false) {
			$message .= "自社サイト-相互リンクサイト関連付情報の更新に失敗しました。再更新をお願いします。\n";
			$errorFlag = true;
			_Log("[/mail/index.php] {自社サイト-相互リンクサイト関連付} 2-2.【更新失敗】");
		} else {
			$message .= "自社サイト-相互リンクサイト関連付情報を更新しました。\n";
			_Log("[/mail/index.php] {自社サイト-相互リンクサイト関連付} 2-1.【更新成功】");
		}
	}
	_Log("[/mail/index.php] {自社サイト-相互リンクサイト関連付}-----------------------------------終了");


	$message .= "\n";
	$message .= "<a href=\"../site/?back\" title=\"相互リンク一覧に戻る\">[相互リンク一覧に戻る]</a>\n";

	$mode = 3;
	

//初期表示の場合
} else {
	//相互リンクサイト情報を取得できたか？
	if (_IsNull($siteInfo)) {
		//取得できなかった場合
		$message .= "パラメーターが不正です。(相互リンクサイト情報を取得できません。)\n";
		$errorFlag = true;
		$mode = 4;
	} else {
		//取得できた場合
		//初期値を設定する。
		$info['site_id'] = $siteInfo['site_id'];							//相互リンクサイトID
		$info['title'] = $siteInfo['title'];								//サイトタイトル
		$info['url'] = $siteInfo['url'];									//サイトURL
		$info['e_mail'] = $siteInfo['e_mail'];							//E-Mail
		$info['family_name'] = $siteInfo['family_name'];					//担当者名(姓)
		$info['first_name'] = $siteInfo['first_name'];					//担当者名(名)
		$info['family_name_kana'] = $siteInfo['family_name_kana'];		//担当者名(姓)(カナ)
		$info['first_name_kana'] = $siteInfo['first_name_kana'];		//担当者名(名)(カナ)
	}
	//自社サイトマスタ情報を取得できたか？
	if (_IsNull($mstOwnSiteInfo)) {
		//取得できなかった場合
		$message .= "パラメーターが不正です。(自社サイト情報を取得できません。)\n";
		$errorFlag = true;
		$mode = 4;
	} else {
		//取得できた場合
		//初期値を設定する。
		$info['own_site_id'] = $mstOwnSiteInfo['id'];						//自社サイトID
		$info['own_site_title'] = $mstOwnSiteInfo['name'];					//サイトタイトル
		$info['own_site_url'] = $mstOwnSiteInfo['url'];						//サイトURL
		$info['own_site_e_mail'] = $mstOwnSiteInfo['e_mail'];				//E-Mail
	}

	//打診メールテンプレートに初期値が設定された場合
	if (isset($info['mail_template_id']) && !_IsNull($info['mail_template_id'])) {
		$mailTemplateFlag = true;
	}
}


if ($mailTemplateFlag) {
	//メールテンプレートマスタ情報を取得する。
	$condition = array();
	$condition['id'] = $info['mail_template_id'];
	$mstMailTemplateInfo = _DB_GetInfo('mst_mail_template', $condition, false);
	if (_IsNull($mstMailTemplateInfo)) {
		//取得できなかった場合
		$message .= "メールテンプレートマスタ情報を取得できません。\n";
		$errorFlag = true;
	} else {
		//件名用メールテンプレートを取得する。
		$mailSubjectFileName = sprintf(MAIL_TEMP_SUBJECT_FILE, $mstMailTemplateInfo['value']);
		$mailSubject = @file_get_contents($mailSubjectFileName);
		//"件名"が存在する場合、表示する。
		if ($mailSubject !== false && !_IsNull($mailSubject)) {
			$info['mail_subject'] = $mailSubject;//メール件名
		} else {
			//取得できなかった場合
			$message .= "打診メールの件名テンプレートファイルを取得できません。(ファイル名 = '".$mailSubjectFileName."')\n";
			$errorFlag = true;
		}
	
		//本文用メールテンプレートを取得する。
		$mailBodyFileName = sprintf(MAIL_TEMP_BODY_FILE, $mstMailTemplateInfo['value']);
		$mailBody = @file_get_contents($mailBodyFileName);
		//"本文"が存在する場合、表示する。
		if ($mailBody !== false && !_IsNull($mailBody)) {
			//テンプレートを編集する。(必要箇所を置換する。)
			//相互リンクサイト情報を置換する。
			//サイトタイトル
			$mailBody = str_replace ('{site_title}', $info['title'], $mailBody);
			//サイトURL
			$mailBody = str_replace ('{url}', $info['url'], $mailBody);
			//担当者名
			$mailBody = str_replace ('{staff_name}', $info['family_name']." ".$info['first_name'], $mailBody);
	
			//自社サイトマスタ情報を置換する。
			//サイトタイトル
			$mailBody = str_replace ('{own_site_title}', $info['own_site_title'], $mailBody);
			//サイトURL
			$mailBody = str_replace ('{own_site_url}', $info['own_site_url'], $mailBody);
			
			$info['mail_body'] = $mailBody;//メール本文
		} else {
			//取得できなかった場合
			$message .= "打診メールの本文テンプレートファイルを取得できません。(ファイル名 = '".$mailBodyFileName."')\n";
			$errorFlag = true;
		}
	
		_Log("[/mail/index.php] 選択された打診メールテンプレート = '".$mstMailTemplateInfo['name']."'");
		_Log("[/mail/index.php] (件名)打診メールテンプレートファイル名 = '".$mailSubjectFileName."'");
		_Log("[/mail/index.php] (件名)打診メールテンプレートファイル内容 = '".$mailSubject."'");
		_Log("[/mail/index.php] (本文)打診メールテンプレートファイル名 = '".$mailBodyFileName."'");
		_Log("[/mail/index.php] (本文)打診メールテンプレートファイル内容 = '".$mailBody."'");
	
	
	}
}


//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/mail/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/mail/index.php] mode = '".$mode."'");






////文字をHTMLエンティティに変換する。
//$info = _HtmlSpecialCharsForArray($info);

//echo ("\$info='".print_r($info,true)."'");

//パンくずリスト情報を設定する。
_SetBreadcrumbs($_SERVER['PHP_SELF'], '', '打診メール', 3);

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

	<div class="breadcrumbs">
		<?=$breadcrumbs = _GetBreadcrumbs();?>
	</div><!-- End breadcrumbs -->
	
	<div id="sidebar">
		<?include_once("../common_html/side_menu.php");?>
	</div><!-- End sidebar -->

	<div id="maincontent">
		<?=_GetFormTable($mode, $xmlList, $info, $tabindex, $message, $errorFlag);?>
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

_Log("[/mail/index.php] end.");

?>