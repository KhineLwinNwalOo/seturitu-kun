<?php
/*
 * [��ߥ�󥯴�������]
 * �ǿǥ᡼����������
 *
 * ��������2007/10/01	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/mail/index.php] start.");

//�ܥե������̾�Τ�������롣
$phpName = basename($_SERVER['PHP_SELF']);
//�ե�����Υ������������ꤹ�롣
//$formAction = SSL_URL_THE_LIFEBOAT_COM_INQ.'/'.$phpName;
$formAction = $_SERVER['PHP_SELF'];

_Log("[/mail/index.php] dirname(\$_SERVER['PHP_SELF']) = '".dirname($_SERVER['PHP_SELF'])."'");

//�̾��URL(SSL�ǤϤʤ�URL)
$urlBase = URL_BASE;

//������̾
$clientName = ADMIN_TITLE;


//�ޥ������ѥ᡼�륢�ɥ쥹
//��,�פǤ����ä���������ɲä��Ʋ�������
$listMasterMail = array("ishikawa@woorom.com");


//���֥���ǥå���
$tabindex = 0;

//DB�򥪡��ץ󤹤롣
$cid = _DB_Open();

//�ޥ��������������롣
$undeleteOnly = true;

//ư��⡼��{1:����/2:��ǧ/3:��λ/4:���顼}
$mode = 1;

//ǧ�ڥ����å�
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/mail/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/mail/index.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ../");
	exit;
}
//����������������롣
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//��å�����
$message = "";
//���顼�ե饰
$errorFlag = false;

//��å�����
$message4js = "";


//----------------------------------------------------------------------------------------
//�ѥ�᡼������������롣
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		//��ߥ�󥯥�����ID
		$siteId = $_POST['site_id'];
		//���ҥ�����ID
		$ownSiteId = $_POST['own_site_id'];
		break;
	case 'GET':
		//��ߥ�󥯥�����ID
		$siteId = $_GET['site_id'];
		//���ҥ�����ID
		$ownSiteId = $_GET['own_site_id'];
		break;	
}

//�ѥ�᡼��������DB�����������롣
//��ߥ�󥯥����Ⱦ����������롣
$condition = array();
$condition['site_id'] = $siteId;
$siteInfo = _DB_GetInfo('tbl_site', $condition, false);

//���ҥ����ȥޥ��������������롣
$condition = array();
$condition['id'] = $ownSiteId;
$mstOwnSiteInfo = _DB_GetInfo('mst_own_site', $condition, false);
//----------------------------------------------------------------------------------------

//���пͤ�E-Mail�����ꤹ�롣
$fromEmailList = array(
 '2' => array('id' => '2', 'name' => 'yamada@woorom.com')
,'3' => array('id' => '3', 'name' => 'ishikawa@woorom.com')
);

//���ҥ����ȥޥ������󤬼����Ǥ�����硢���ҥ����ȤΥ᡼�륢�ɥ쥹���ɲ����ꤹ�롣
if (!_IsNull($mstOwnSiteInfo)) {
	if (isset($mstOwnSiteInfo['e_mail']) && !_IsNull($mstOwnSiteInfo['e_mail']))	{
		$fromEmailList['1'] = array('id' => '1', 'name' => $mstOwnSiteInfo['e_mail']);
		ksort($fromEmailList);
	}
}


$otherList = array();
$otherList['from_e_mail_list'] = $fromEmailList;

//XML���ɤ߹��ࡣ
$xmlList = _GetXml("../common/form_xml/form_mail.xml", $otherList);



//�䤤��碌������Ǽ��������
$info = array();
//����ͤ����ꤹ�롣
$info['mail_template_id'] = MST_MAIL_TEMPLATE_ID_CORPORATION;//�᡼��ƥ�ץ졼��="ˡ����"

//�᡼��ƥ�ץ졼���ɤ߹��ߥե饰{true:�ɤ߹���/false:�ɤ߹��ޤʤ�}
$mailTemplateFlag = false;



//����ܥ��󤬲����줿���
if ($_POST['select'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
	_Log("[/mail/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/mail/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");

	//�ǿǥ᡼��ƥ�ץ졼�Ȥ����򤵤줿���
	if (isset($info['mail_template_id'])) {
		if (_IsNull($info['mail_template_id'])) {
			//�������򤷤���硢�ʲ��ι��ܤ򥯥ꥢ���롣
			$info['mail_subject'] = null;		//�᡼���̾	
			$info['mail_body'] = null;		//�᡼����ʸ
		} else {
			$mailTemplateFlag = true;
		}
	}

//��ǧ�ܥ��󤬲����줿���
} elseif ($_POST['confirm'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
	_Log("[/mail/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/mail/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");

	//�����ͥ����å�

//	//���ѵ���
//	//ɬ��
//	if (_IsNull($info['rule'])) $message .= "���ѵ����Ʊ�դ���˥����å��򤷤Ʋ�������<br />";

	$message .= _CheackInputAll($xmlList, $info);


	if (_IsNull($message)) {
		//���顼��̵����硢��ǧ���̤�ɽ�����롣
		$mode = 2;
		 
		$message .= "���������Ƥ��ǧ���ơ��������ץܥ���򲡤��Ƥ���������";
	} else {
		//���顼��ͭ����
		$message = "�����Ϥ˸�꤬����ޤ���\n".$message;
		$errorFlag = true;
	}
}
//���ܥ��󤬲����줿���
elseif ($_POST['back'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
	_Log("[/mail/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/mail/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");
}
//�����ܥ��󤬲����줿���
elseif ($_POST['go'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
	_Log("[/mail/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/mail/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");

//	//�᡼����ʸ�ζ�����ʬ�����ꤹ�롣
//	$body = "";
//	$body .= _CreateMailAll($xmlList, $info);
//

	//�᡼���̾�����ꤹ�롣
	$mailSubject = $info['mail_subject'];
	//�᡼����ʸ�����ꤹ�롣
	$mailBody = $info['mail_body'];
	//���п�E-Mail�����ꤹ�롣
	$fromEMail =  $fromEmailList[$info['from_e_mail_id']]['name'];

	//�᡼����ʸ��κ��п�E-Mail���ִ����롣
	$mailBody = str_replace ('{from_e_mail}', $fromEMail, $mailBody);


	mb_language("Japanese");


	//�᡼������
	//��ߥ�󥯥�����ô������(������)���������롣
	$rcd = mb_send_mail($info['e_mail'], $mailSubject, $mailBody, "from:".$fromEMail);
	//���򤷤����п�E-Mail���������롣
	$rcd = mb_send_mail($fromEMail, $mailSubject, $mailBody, "from:".$fromEMail);

	//�ޥ��������������롣
	if (!_IsNull($listMasterMail)) {
		foreach($listMasterMail as $masterMail){
			$rcd = mb_send_mail($masterMail, $mailSubject, $mailBody, "from:".$fromEMail);
		}
	}
	
	$message .= "�ǿǥ᡼����������ޤ�����\n";

	_Log("[/mail/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��}-----------------------------------����");
	//DB����
	//���ҥ�����-��ߥ�󥯥����ȴ�Ϣ�վ����������롣
	$condition = array();
	$condition['site_id'] = $info['site_id'];//��ߥ�󥯥�����ID
	$condition['own_site_id'] = $info['own_site_id'];//���ҥ�����ID
	$tblSiteRelationInfo = _DB_GetInfo('tbl_site_relation', $condition, false);
	if (_IsNull($tblSiteRelationInfo)) {
		//���ҥ�����-��ߥ�󥯥����ȴ�Ϣ�վ��󤬼����Ǥ��ʤ��ä���碪������Ͽ���롣
		_Log("[/mail/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1.���ҥ�����-��ߥ�󥯥����ȴ�Ϣ�վ��󤬼����Ǥ��ʤ��ä���碪������Ͽ���롣");

		$createInfo = array();
		$createInfo['site_id'] = $info['site_id'];					//��ߥ�󥯥�����ID
		$createInfo['own_site_id'] = $info['own_site_id'];			//���ҥ�����ID
		$createInfo['link_status_id'] = MST_LINK_STATUS_ID_SENT;		//��󥯾���ID="�ǿǥ᡼�������Ѥ�"(2)
		$createInfo['del_flag'] = DELETE_FLAG_NO;						//����ե饰
		$createInfo['create_ip'] = $_SERVER["REMOTE_ADDR"];			//����IP
		$createInfo['create_date'] = null;							//������					
		$createInfo['update_ip'] = $_SERVER["REMOTE_ADDR"];			//����IP
		$createInfo['update_date'] = null;							//������					
		
		//��Ͽ���롣
		$res = _DB_CreateInfo('tbl_site_relation', $createInfo);
		if ($res === false) {
			$message .= "���ҥ�����-��ߥ�󥯥����ȴ�Ϣ�վ������Ͽ�˼��Ԥ��ޤ���������Ͽ�򤪴ꤤ���ޤ���\n";
			$errorFlag = true;
			_Log("[/mail/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1-2.����Ͽ���ԡ�");
		} else {
			$message .= "���ҥ�����-��ߥ�󥯥����ȴ�Ϣ�վ������Ͽ���ޤ�����\n";
			_Log("[/mail/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1-1.����Ͽ������");
		}

	} else {
		//���ҥ�����-��ߥ�󥯥����ȴ�Ϣ�վ��󤬼����Ǥ�����碪�������롣
		_Log("[/mail/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 2.���ҥ�����-��ߥ�󥯥����ȴ�Ϣ�վ��󤬼����Ǥ�����碪�������롣");

		$tblSiteRelationInfo['link_status_id'] = MST_LINK_STATUS_ID_SENT;		//��󥯾���ID="�ǿǥ᡼�������Ѥ�"(2)
		$tblSiteRelationInfo['update_ip'] = $_SERVER["REMOTE_ADDR"];			//����IP
		$tblSiteRelationInfo['update_date'] = null;							//������			

		//�������롣
		$res = _DB_SaveInfo('tbl_site_relation', $tblSiteRelationInfo);
		if ($res === false) {
			$message .= "���ҥ�����-��ߥ�󥯥����ȴ�Ϣ�վ���ι����˼��Ԥ��ޤ������ƹ����򤪴ꤤ���ޤ���\n";
			$errorFlag = true;
			_Log("[/mail/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 2-2.�ڹ������ԡ�");
		} else {
			$message .= "���ҥ�����-��ߥ�󥯥����ȴ�Ϣ�վ���򹹿����ޤ�����\n";
			_Log("[/mail/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 2-1.�ڹ���������");
		}
	}
	_Log("[/mail/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��}-----------------------------------��λ");


	$message .= "\n";
	$message .= "<a href=\"../site/?back\" title=\"��ߥ�󥯰��������\">[��ߥ�󥯰��������]</a>\n";

	$mode = 3;
	

//���ɽ���ξ��
} else {
	//��ߥ�󥯥����Ⱦ��������Ǥ�������
	if (_IsNull($siteInfo)) {
		//�����Ǥ��ʤ��ä����
		$message .= "�ѥ�᡼�����������Ǥ���(��ߥ�󥯥����Ⱦ��������Ǥ��ޤ���)\n";
		$errorFlag = true;
		$mode = 4;
	} else {
		//�����Ǥ������
		//����ͤ����ꤹ�롣
		$info['site_id'] = $siteInfo['site_id'];							//��ߥ�󥯥�����ID
		$info['title'] = $siteInfo['title'];								//�����ȥ����ȥ�
		$info['url'] = $siteInfo['url'];									//������URL
		$info['e_mail'] = $siteInfo['e_mail'];							//E-Mail
		$info['family_name'] = $siteInfo['family_name'];					//ô����̾(��)
		$info['first_name'] = $siteInfo['first_name'];					//ô����̾(̾)
		$info['family_name_kana'] = $siteInfo['family_name_kana'];		//ô����̾(��)(����)
		$info['first_name_kana'] = $siteInfo['first_name_kana'];		//ô����̾(̾)(����)
	}
	//���ҥ����ȥޥ������������Ǥ�������
	if (_IsNull($mstOwnSiteInfo)) {
		//�����Ǥ��ʤ��ä����
		$message .= "�ѥ�᡼�����������Ǥ���(���ҥ����Ⱦ��������Ǥ��ޤ���)\n";
		$errorFlag = true;
		$mode = 4;
	} else {
		//�����Ǥ������
		//����ͤ����ꤹ�롣
		$info['own_site_id'] = $mstOwnSiteInfo['id'];						//���ҥ�����ID
		$info['own_site_title'] = $mstOwnSiteInfo['name'];					//�����ȥ����ȥ�
		$info['own_site_url'] = $mstOwnSiteInfo['url'];						//������URL
		$info['own_site_e_mail'] = $mstOwnSiteInfo['e_mail'];				//E-Mail
	}

	//�ǿǥ᡼��ƥ�ץ졼�Ȥ˽���ͤ����ꤵ�줿���
	if (isset($info['mail_template_id']) && !_IsNull($info['mail_template_id'])) {
		$mailTemplateFlag = true;
	}
}


if ($mailTemplateFlag) {
	//�᡼��ƥ�ץ졼�ȥޥ��������������롣
	$condition = array();
	$condition['id'] = $info['mail_template_id'];
	$mstMailTemplateInfo = _DB_GetInfo('mst_mail_template', $condition, false);
	if (_IsNull($mstMailTemplateInfo)) {
		//�����Ǥ��ʤ��ä����
		$message .= "�᡼��ƥ�ץ졼�ȥޥ������������Ǥ��ޤ���\n";
		$errorFlag = true;
	} else {
		//��̾�ѥ᡼��ƥ�ץ졼�Ȥ�������롣
		$mailSubjectFileName = sprintf(MAIL_TEMP_SUBJECT_FILE, $mstMailTemplateInfo['value']);
		$mailSubject = @file_get_contents($mailSubjectFileName);
		//"��̾"��¸�ߤ����硢ɽ�����롣
		if ($mailSubject !== false && !_IsNull($mailSubject)) {
			$info['mail_subject'] = $mailSubject;//�᡼���̾
		} else {
			//�����Ǥ��ʤ��ä����
			$message .= "�ǿǥ᡼��η�̾�ƥ�ץ졼�ȥե����������Ǥ��ޤ���(�ե�����̾ = '".$mailSubjectFileName."')\n";
			$errorFlag = true;
		}
	
		//��ʸ�ѥ᡼��ƥ�ץ졼�Ȥ�������롣
		$mailBodyFileName = sprintf(MAIL_TEMP_BODY_FILE, $mstMailTemplateInfo['value']);
		$mailBody = @file_get_contents($mailBodyFileName);
		//"��ʸ"��¸�ߤ����硢ɽ�����롣
		if ($mailBody !== false && !_IsNull($mailBody)) {
			//�ƥ�ץ졼�Ȥ��Խ����롣(ɬ�ײս���ִ����롣)
			//��ߥ�󥯥����Ⱦ�����ִ����롣
			//�����ȥ����ȥ�
			$mailBody = str_replace ('{site_title}', $info['title'], $mailBody);
			//������URL
			$mailBody = str_replace ('{url}', $info['url'], $mailBody);
			//ô����̾
			$mailBody = str_replace ('{staff_name}', $info['family_name']." ".$info['first_name'], $mailBody);
	
			//���ҥ����ȥޥ���������ִ����롣
			//�����ȥ����ȥ�
			$mailBody = str_replace ('{own_site_title}', $info['own_site_title'], $mailBody);
			//������URL
			$mailBody = str_replace ('{own_site_url}', $info['own_site_url'], $mailBody);
			
			$info['mail_body'] = $mailBody;//�᡼����ʸ
		} else {
			//�����Ǥ��ʤ��ä����
			$message .= "�ǿǥ᡼�����ʸ�ƥ�ץ졼�ȥե����������Ǥ��ޤ���(�ե�����̾ = '".$mailBodyFileName."')\n";
			$errorFlag = true;
		}
	
		_Log("[/mail/index.php] ���򤵤줿�ǿǥ᡼��ƥ�ץ졼�� = '".$mstMailTemplateInfo['name']."'");
		_Log("[/mail/index.php] (��̾)�ǿǥ᡼��ƥ�ץ졼�ȥե�����̾ = '".$mailSubjectFileName."'");
		_Log("[/mail/index.php] (��̾)�ǿǥ᡼��ƥ�ץ졼�ȥե��������� = '".$mailSubject."'");
		_Log("[/mail/index.php] (��ʸ)�ǿǥ᡼��ƥ�ץ졼�ȥե�����̾ = '".$mailBodyFileName."'");
		_Log("[/mail/index.php] (��ʸ)�ǿǥ᡼��ƥ�ץ졼�ȥե��������� = '".$mailBody."'");
	
	
	}
}


//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/mail/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/mail/index.php] mode = '".$mode."'");






////ʸ����HTML����ƥ��ƥ����Ѵ����롣
//$info = _HtmlSpecialCharsForArray($info);

//echo ("\$info='".print_r($info,true)."'");

//�ѥ󤯤��ꥹ�Ⱦ�������ꤹ�롣
_SetBreadcrumbs($_SERVER['PHP_SELF'], '', '�ǿǥ᡼��', 3);

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
////DB�򥯥������롣
//_DB_Close($cid);

_Log("[/mail/index.php] end.");

?>