<?php
/*
 * [��������]
 * �ޥ�����������
 *
 * ��������2007/12/03	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/master/index.php] start.");

//ǧ�ڥ����å�----------------------------------------------------------------------start
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/master/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/master/index.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ".URL_BASE);
	exit;
}
//����������������롣
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
_CheckAuth($loginInfo, AUTH_CLIENT, AUTH_WOOROM);
//ǧ�ڥ����å�----------------------------------------------------------------------end



//�ܥե������̾�Τ�������롣
$phpName = basename($_SERVER['PHP_SELF']);
//�ե�����Υ������������ꤹ�롣
//$formAction = SSL_URL_THE_LIFEBOAT_COM_INQ.'/'.$phpName;
$formAction = $_SERVER['PHP_SELF'];

//�̾��URL(SSL�ǤϤʤ�URL)
$urlBase = URL_BASE;

//������̾
$clientName = ADMIN_TITLE;

//���֥���ǥå���
$tabindex = 0;

//DB�򥪡��ץ󤹤롣
$cid = _DB_Open();

//�ޥ��������������롣
$undeleteOnly = true;

//ư��⡼��{1:����/2:��λ(����)/3:���顼}
$mode = 1;

//��å�����
$message = "";
//��å�����
$message4js = "";


//XML���ɤ߹��ࡣ
$xmlList = _GetXml("../common/form_xml/form_mst.xml");


//�䤤��碌������Ǽ��������
$info = array();


//����ͤ����ꤹ�롣
switch ($_GET['mst_name']) {
//	case TBL_NAME_SITE:
//		//��ߥ�󥯥����ȥơ��֥�
//		$info['condition']['mst_name'] = $_GET['mst_name'];
//		$info['condition']['site_id'] = $_GET['site_id'];	//��ߥ�󥯥�����ID
//
//		//���ϰ���ɽ���ľ�����ˤ��뤫��{true:��ľ����(�ǥե����)/false:��ʿ����}
//		$info['condition']['condition_vertical_direction_flag'] = false;
//		//���ϰ���ɽ�ο����ɲ�ʬ����N���ɲä��뤫��{true:���N���ɲä���(�ǥե����)/false:���N�Ĥˤʤ�褦���ɲä���}
//		$info['condition']['condition_add_type_flag'] = false;
//		//���ϰ���ɽ�ο����ɲ�ʬ��ɬ�ܹ��ܤȤ��ʤ������ϰ���ɽ�����Ƥ������ɲ�ʬ�ξ�硢1���ܤ�ɬ�ܹ��ܤȤ��뤫��{true:ɬ�ܤȤ��ʤ�(�ǥե����)/false:1���ܤ�ɬ�ܤȤ���}
//		$info['condition']['condition_add_required_flag'] = false;
//		
//		//���ꤵ��Ƥ�����=�����ξ�硢�����ɲ�ʬ���ɲä��ʤ���
//		if (isset($_GET['site_id'])) {
//			//���ϰ���ɽ�˿����ɲ�ʬ���ɲä��뤫��{true:�ɲä���(�ǥե����)/false:�ɲä��ʤ�}
//			$info['condition']['condition_add_flag'] = false;
//		
//			//ư��⡼�ɤ򥻥å�������¸���롣ư��⡼��="¾���̷�ͳ��ɽ��"
//			$_SESSION[SID_MST_MODE] = MST_MODE_FROM_OTHER;
//		} else {
//			//ư��⡼�ɤ򥻥å�������¸���롣ư��⡼��="ñ��ɽ��"
//			$_SESSION[SID_MST_MODE] = MST_MODE_FROM_MENU;
//		}
// 		
//		break;
//	case MST_NAME_GENRE:
//		//������ޥ���
//		$info['condition']['mst_name'] = $_GET['mst_name'];
//
//		//ư��⡼�ɤ򥻥å�������¸���롣ư��⡼��="ñ��ɽ��"
//		$_SESSION[SID_MST_MODE] = MST_MODE_FROM_MENU;
//
//		break;

	case MST_NAME_STAFF:
		//ô���ԥޥ���
		$info['condition']['mst_name'] = $_GET['mst_name'];
		break;
	case MST_NAME_AFTEREFFECTS_GRADE_01:
		//���㳲����(��)�ޥ���
		$info['condition']['mst_name'] = $_GET['mst_name'];
		break;
	case MST_NAME_STATUS:
		//�����ޥ���
		$info['condition']['mst_name'] = $_GET['mst_name'];
		break;
		
	case MST_NAME_SUBCATEGORY:
		//���֥��ƥ��꡼�ޥ���
		$info['condition']['mst_name'] = $_GET['mst_name'];
		break;
	case MST_NAME_CATEGORY:
		//���ƥ��꡼�ޥ���
	default:
		$info['condition']['mst_name'] = MST_NAME_CATEGORY;
		break;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	//ư��⡼�ɤ򥻥å�������¸���롣ư��⡼��="ñ��ɽ��"
	$_SESSION[SID_MST_MODE] = MST_MODE_FROM_MENU;
}

//��å������Υ�����ͭ���ˤ��뤫��{true:ͭ���ˤ���/false:̵���ˤ���}
$messageTag = false;


//����ܥ��󤬲����줿���
if ($_POST['select'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
//	_Log("[/master/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/master/index.php] POST = '".print_r($info,true)."'");

//�����ܥ��󤬲����줿���
} elseif ($_POST['go'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
//	_Log("[/master/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/master/index.php] POST(�Хå�����å���������) = '".print_r($info,true)."'");
//	//�����ѡ׿������Ⱦ�ѡפ��Ѵ����롣
//	$info = _Mb_Convert_KanaForArray($info, 'n');
//	_Log("[/master/index.php] POST(�����ѡ׿������Ⱦ�ѡפ��Ѵ�����) = '".print_r($info,true)."'");

	//�����ͥ����å�
	$errList = array();
	$message = _CheackMasterTable($xmlList, $info, $errList);
	if (count($errList) > 0) $info['error'] = $errList;

	//���顼���Ϥ��ʤ���硢��������Ͽ�򤹤롣
	if (_IsNull($message)) {
		$returnList = array();
		$message = _UpdateMasterTable($xmlList, $info, $returnList);
		if (_IsNull($message)) {
			//�����ͤ򥯥ꥢ���ơ���ɽ�����롣
			unset($info['update']);
			if ($returnList['count']['update'] == 0 && $returnList['count']['create'] == 0) {
				$message = "�ѹ��ս꤬����ޤ���";
			} else {
				$message = "�������ޤ�����(������".$returnList['count']['update']."�� / ��Ͽ��".$returnList['count']['create']."��)";
			}
			
//			//�����̽���---------start
//			//��ߥ�󥯥����ȥơ��֥�ξ��
//			if ($info['condition']['mst_name'] == TBL_NAME_SITE) {
//				//������Ͽ�ξ�硢��Ͽ����������ɽ�����뤿�����ID�����ꤹ�롣
//				//������Ͽ����ID��������롣
//				$createIdList = $returnList['create']['id'];
//				
//				_Log("[/master/index.php] ������Ͽ����ID = '".print_r($createIdList,true)."'");
//				_Log("[/master/index.php] ��������ID = '".print_r($info['condition']['site_id'],true)."'");
//				
//				//������Ͽ����ID�˸�����������Ѥߤ�ID(��������ID)���ɲä��롣
//				if (is_array($info['condition']['site_id'])) {
//					//����ξ�硢����Υޡ����򤹤롣
//					$createIdList = array_merge($createIdList, $info['condition']['site_id']);
//				} else {
//					//����ʳ��ξ�硢������ɲä��롣
//					$createIdList[] = $info['condition']['site_id'];
//				}
//
//				_Log("[/master/index.php] ������Ͽ����ID�ܹ�������ID = '".print_r($createIdList,true)."'");
//
//				//�������˾�񤭤��롣
//				$info['condition']['site_id'] = $createIdList;
//				
//				//ư��⡼��="¾���̷�ͳ��ɽ��"�ξ�硢��٥��3�ˤ��롣
//				if ($_SESSION[SID_MST_MODE] == MST_MODE_FROM_OTHER) {
//					$message .= "\n";
//					$message .= "\n";
//					$message .= "<a href=\"../site/?back\" title=\"��ߥ�󥯰��������\">[��ߥ�󥯰��������]</a>\n";
//					
//					$messageTag = true;
//				}
//			}
//			//�����̽���---------end
			
			
			
		} else {
			//���顼ͭ��������뤿�ᡣ
			$info['error'] = true;
		}

	} else {
		$message = "�����Ϥ˸�꤬����ޤ���\n".$message;
	}
	$info['message'] = $message;

}





////ʸ����HTML����ƥ��ƥ����Ѵ����롣
//$info = _HtmlSpecialCharsForArray($info);

//echo ("\$info='".print_r($info,true)."'");

//�ѥ󤯤��ꥹ�Ⱦ�������ꤹ�롣
switch ($info['condition']['mst_name']) {
	case MST_NAME_STAFF:
		//ô���ԥޥ���
		$breadcrumbsLabel = 'ô����';
		break;
	case MST_NAME_AFTEREFFECTS_GRADE_01:
		//���㳲����(��)�ޥ���
		$breadcrumbsLabel = '���㳲����';
		break;
	case MST_NAME_STATUS:
		//�����ޥ���
		$breadcrumbsLabel = '����';
		break;

	case MST_NAME_SUBCATEGORY:
		//���֥��ƥ��꡼�ޥ���
		$breadcrumbsLabel = '���֥��ƥ��꡼';
		break;
	case MST_NAME_CATEGORY:
		//���ƥ��꡼�ޥ���
	default:
		$breadcrumbsLabel = '���ƥ��꡼';
		break;
}


//�ѥ󤯤��ꥹ�ȤΥ�٥�(����)
$level = 2;
//ư��⡼��="¾���̷�ͳ��ɽ��"�ξ�硢��٥��3�ˤ��롣
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
////DB�򥯥������롣
//_DB_Close($cid);

_Log("[/master/index.php] end.");

?>