<?php
/*
 * [���������Ω.JP �ġ���]
 * �괾ǧ�ڥڡ���
 *
 * ��������2008/12/01	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/user/company/article/index.php] start.");


_Log("[/user/company/article/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/company/article/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/company/article/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/company/article/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/user/index.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ".URL_LOGIN);
	exit;
} else {
	//����������������롣
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//ǧ�ڥ����å�----------------------------------------------------------------------end



//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- start
_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../../../common/temp_html/temp_base.txt';
_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


//$tempSidebarLoginFile = '../../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"��¸�ߤ����硢ɽ�����롣
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) ��������");
//} else {
//	//�����Ǥ��ʤ��ä����
//	_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) �ڼ��ԡ�");
//}

$tempSidebarUserMenuFile = '../../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_COMPANY_ARTICLE;

//���饤������ͥ᡼�륢�ɥ쥹
$clientMail = COMPANY_E_MAIL;
//�ޥ������ѥ᡼�륢�ɥ쥹
$masterMailList = $_COMPANY_MASTER_MAIL_LIST;

//�ƥ�����
if (false) {
//if (true) {
	//���饤������ͥ᡼�륢�ɥ쥹
	$clientMail = "ishikawa@woorom.com";
	//�ޥ������ѥ᡼�륢�ɥ쥹
	//��,�פǤ����ä���������ɲä��Ʋ�������
	$masterMailList = array("ishikawa@woorom.com", "ishikawa@woorom.com");
	$masterMailList = null;
}







//���֥���ǥå���
$tabindex = 0;

//DB�򥪡��ץ󤹤롣
$cid = _DB_Open();

//ư��⡼��{1:����/2:��ǧ/3:��λ/4:���顼}
$mode = 1;

//����ɽ�����뤫��hidden���ܤ�ɽ�����뤫��{true:����ɽ�����롣/false:XML���ꡢ���¤ˤ��ɽ��̵ͭ�˽�����}
$allShowFlag = false;

//��å�����
$message = "";
//���顼�ե饰
$errorFlag = false;


//���Ͼ�����Ǽ��������
$info = array();


$requestMethod = $_SERVER["REQUEST_METHOD"];

////���ء����ܥ��󤬲����줿��碪GET�ν�����Ԥ���
//if ($_POST['next'] != "" || $_POST['back'] != "") {
//	$requestMethod = 'GET';
//
//	//���ƥå�ID
//	$step = (isset($_POST['condition']['_step_'])?$_POST['condition']['_step_']:null);
//
//	//���إܥ��󤬲����줿���
//	if ($_POST['next'] != "") {
//		if (_IsNull($step)) {
//			$step = 1;
//		} else {
//			$step++;
//		}
//	}
//	//���ܥ��󤬲����줿���
//	elseif ($_POST['back'] != "") {
//		if (_IsNull($step)) {
//			$step = 1;
//		} else {
//			$step--;
//		}
//	}
//
//
//	//�������å�ID
//	$_GET['id'] = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);
//	//���ƥå�ID
//	$_GET['step'] = $step;
//}


_Log("[/user/company/article/index.php] \$_GET(�ͤ��ؤ���) = '".print_r($_GET,true)."'");

//�ѥ�᡼������������롣
$xmlName = XML_NAME_ARTICLE;//XML�ե�����̾�����ꤹ�롣
$id = null;
$step = null;
$stepId = null;
switch ($requestMethod) {
	case 'POST':
//		//XML�ե�����̾
//		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
		//�������å�ID
		$id = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);
//		//���ƥå�ID
//		$step = (isset($_POST['condition']['_step_'])?$_POST['condition']['_step_']:null);


		//����ͤ����ꤹ�롣
		$undeleteOnly4def = false;

		_Log("[/user/company/article/index.php] {������桼�������½���} �桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/article/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."'");


		//���¤ˤ�äơ�ɽ������桼������������¤��롣
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://����̵��

				_Log("[/user/company/article/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
				_Log("[/user/company/article/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
				_Log("[/user/company/article/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

				$id = null;
				$undeleteOnly4def = true;

				//��ʬ�Υ桼��������Τ�ɽ�����롣
				//�桼����ID�򸡺����롣
				$id = $loginInfo['usr_user_id'];

				_Log("[/user/company/article/index.php] {������桼�������½���} ���桼����ID = '".$id."'");
				break;
		}


		//�����ͤ�������롣
		$info = $_POST;
		_Log("[/user/company/article/index.php] POST = '".print_r($info,true)."'");
		//�Хå�����å�����������
		$info = _StripslashesForArray($info);
		_Log("[/user/company/article/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");

		//��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣���᡼���Ⱦ�ѥ��ʤ�ʸ����������Τǡ�
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/company/article/index.php] POST(��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣) = '".print_r($info,true)."'");


		//XML�ե�����̾���������å�ID���񤭤��롣
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;

		break;
	case 'GET':
//		//XML�ե�����̾
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
		//�������å�ID
		$id = (isset($_GET['id'])?$_GET['id']:null);
//		//���ƥå�ID
//		$step = (isset($_GET['step'])?$_GET['step']:null);

		//���ܸ��ڡ���
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);


		//����ͤ����ꤹ�롣
		$undeleteOnly4def = false;

		_Log("[/user/company/article/index.php] {������桼�������½���} �桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/article/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."'");

		//���¤ˤ�äơ�ɽ������桼������������¤��롣
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://����̵��

				_Log("[/user/company/article/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
				_Log("[/user/company/article/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
				_Log("[/user/company/article/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

				$id = null;
				$undeleteOnly4def = true;

				//��ʬ�Υ桼��������Τ�ɽ�����롣
				//�桼����ID�򸡺����롣
				$id = $loginInfo['usr_user_id'];


				_Log("[/user/company/article/index.php] {������桼�������½���} ���桼����ID = '".$id."'");

//				//���ܸ��ڡ����Ϥɤ�����
//				switch ($pId) {
//					case PAGE_ID_USER://�桼�����ڡ���
//						break;
//				}
				break;
		}


//		$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
//		$info['update'] = $_SESSION[SID_SEAL_INFO];
		$info['update'] = null;

		//XML�ե�����̾���������å�ID�����ͤ��ɲä��롣
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;



//		//���ꤵ��Ƥ�����=�����ξ��
//		if (isset($_GET['id'])) {
//			//ư��⡼�ɤ򥻥å�������¸���롣ư��⡼��="¾���̷�ͳ��ɽ��"
//			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_OTHER;
//		} else {
//			//ư��⡼�ɤ򥻥å�������¸���롣ư��⡼��="ñ��ɽ��"
//			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_MENU;
//		}
//

//		//���ܸ��ڡ����򥻥å�������¸���롣
//		$_SESSION[SID_PAY_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/user/company/article/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/user/company/article/index.php] XML�ե�����̾ = '".$xmlName."'");
_Log("[/user/company/article/index.php] �������å�ID = '".$id."'");


//�桼����ID�˴�Ϣ������ID����Ҿ���򸡺����롣
$companyInfo = null;
$companyId = _GetRelationCompanyId($id, $undeleteOnly4def);
if (!_IsNull($companyId)) {
	$companyInfo = _GetCompanyInfo($companyId, $undeleteOnly4def);
}


//�桼��������(���������)�����ꤹ�롣��DB�����˻��Ѥ��롣
$info['update']['tbl_user'] = $loginInfo;
//��Ҿ�������ꤹ�롣��DB�����˻��Ѥ��롣
if (!_IsNull($companyInfo)) {
	$info['update']['tbl_company'] = $companyInfo['tbl_company'];
}


//�괾ǧ�ھ���̤����ξ�硢�桼��������(���������)�����ͤȤ������ꤹ�롣
if (!isset($info['update']['tbl_article_deliver'])) {
	$info['update']['tbl_article_deliver']['art_dlv_tel1'] = $loginInfo['usr_tel1'];
	$info['update']['tbl_article_deliver']['art_dlv_tel2'] = $loginInfo['usr_tel2'];
	$info['update']['tbl_article_deliver']['art_dlv_tel3'] = $loginInfo['usr_tel3'];

	$info['update']['tbl_article_deliver']['art_dlv_e_mail'] = $loginInfo['usr_e_mail'];
	$info['update']['tbl_article_deliver']['art_dlv_e_mail_confirm'] = $loginInfo['usr_e_mail'];

	$info['update']['tbl_article_deliver']['art_dlv_family_name'] = $loginInfo['usr_family_name'];
	$info['update']['tbl_article_deliver']['art_dlv_first_name'] = $loginInfo['usr_first_name'];

	$info['update']['tbl_article_deliver']['art_dlv_zip1'] = $loginInfo['usr_zip1'];
	$info['update']['tbl_article_deliver']['art_dlv_zip2'] = $loginInfo['usr_zip2'];

	$info['update']['tbl_article_deliver']['art_dlv_pref_id'] = $loginInfo['usr_pref_id'];
	$info['update']['tbl_article_deliver']['art_dlv_address1'] = $loginInfo['usr_address1'];
	$info['update']['tbl_article_deliver']['art_dlv_address2'] = $loginInfo['usr_address2'];
}
if (!isset($info['update']['tbl_article_charge'])) {
	$info['update']['tbl_article_charge']['art_chg_family_name'] = $loginInfo['usr_family_name'];
	$info['update']['tbl_article_charge']['art_chg_first_name'] = $loginInfo['usr_first_name'];

	$info['update']['tbl_article_charge']['art_chg_pref_id'] = $loginInfo['usr_pref_id'];
	$info['update']['tbl_article_charge']['art_chg_address1'] = $loginInfo['usr_address1'];
	$info['update']['tbl_article_charge']['art_chg_address2'] = $loginInfo['usr_address2'];
}
if (!isset($info['update']['tbl_article_notary'])) {
	$info['update']['tbl_article_notary']['art_ntr_pref_id'] = $loginInfo['usr_pref_id'];
}

//�괾PDF����̤����ξ�硢��Ҿ�������ͤȤ������ꤹ�롣
if (!isset($info['update']['tbl_article_pdf'])) {
	//�괾������(ǯ)
	$info['update']['tbl_article_pdf']['create_year'] = date('Y');
	//�괾������(��)
	$info['update']['tbl_article_pdf']['create_month'] = date('n');
	//�괾������(��)
	$info['update']['tbl_article_pdf']['create_day'] = date('j');

	if (!_IsNull($companyInfo)) {
		//�괾������(ǯ)
		if (isset($companyInfo['tbl_company']['cmp_article_create_year']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_year'])) {
			$info['update']['tbl_article_pdf']['create_year'] = $companyInfo['tbl_company']['cmp_article_create_year'];
		}
		//�괾������(��)
		if (isset($companyInfo['tbl_company']['cmp_article_create_month']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_month'])) {
			$info['update']['tbl_article_pdf']['create_month'] = $companyInfo['tbl_company']['cmp_article_create_month'];
		}
		//�괾������(��)
		if (isset($companyInfo['tbl_company']['cmp_article_create_day']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_day'])) {
			$info['update']['tbl_article_pdf']['create_day'] = $companyInfo['tbl_company']['cmp_article_create_day'];
		}
	}
}

//�괾����������̤����ξ�硢��Ҿ�������ͤȤ������ꤹ�롣
if (!isset($info['update']['tbl_article_date'])) {
	//�괾������(ǯ)
	$info['update']['tbl_article_date']['art_dat_create_year'] = date('Y');
	//�괾������(��)
	$info['update']['tbl_article_date']['art_dat_create_month'] = date('n');
	//�괾������(��)
	$info['update']['tbl_article_date']['art_dat_create_day'] = date('j');

	if (!_IsNull($companyInfo)) {
		//�괾������(ǯ)
		if (isset($companyInfo['tbl_company']['cmp_article_create_year']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_year'])) {
			$info['update']['tbl_article_date']['art_dat_create_year'] = $companyInfo['tbl_company']['cmp_article_create_year'];
		}
		//�괾������(��)
		if (isset($companyInfo['tbl_company']['cmp_article_create_month']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_month'])) {
			$info['update']['tbl_article_date']['art_dat_create_month'] = $companyInfo['tbl_company']['cmp_article_create_month'];
		}
		//�괾������(��)
		if (isset($companyInfo['tbl_company']['cmp_article_create_day']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_day'])) {
			$info['update']['tbl_article_date']['art_dat_create_day'] = $companyInfo['tbl_company']['cmp_article_create_day'];
		}
	}
}

//�괾ǧ�ڥ������������̤����ξ�硢��Ҿ�������ͤȤ������ꤹ�롣
if (!isset($info['update']['tbl_article_course'])) {
	if (!_IsNull($companyInfo)) {
		//�괾ǧ�ڥ�����ID
		if (isset($companyInfo['tbl_company']['cmp_article_course_id']) && !_IsNull($companyInfo['tbl_company']['cmp_article_course_id'])) {
			$info['update']['tbl_article_course']['art_crs_article_course_id'] = $companyInfo['tbl_company']['cmp_article_course_id'];
		}
	}
}


//switch ($step) {
//	case 1:
//		//ˡ�Ͱ���ʸ����[����]
//		//��XML�����Υե�����ǤϤʤ���ľ�ܽ񤭽Ф���
//		$xmlName = XML_NAME_SEAL_SET;
//
//		$stepId = "sealn_set";
//		break;
//	case 2:
//		//ˡ�Ͱ���ʸ����[����]
//		$xmlName = XML_NAME_SEAL_IMPRINT;
//
//		$stepId = "sealn_imprint";
//		break;
//	case 3:
//		//ˡ�Ͱ���ʸ����[���̾�����Ϥ���]
//		$xmlName = XML_NAME_SEAL_NAME;
//
//		$stepId = "sealn_name";
//		break;
//	case 4:
//		//ˡ�Ͱ���ʸ����[�������Ƴ�ǧ]
//		$xmlName = XML_NAME_SEAL_ALL;
//
//		$stepId = "sealn_confirm";
//		break;
//	default:
//		//ˡ�Ͱ���ʸ����[����]
//		//��XML�����Υե�����ǤϤʤ���ľ�ܽ񤭽Ф���
//		$xmlName = XML_NAME_SEAL_SET;
//
//		$stepId = "sealn_set";
//
//		$step = 1;
//		break;
//}
//$info['condition']['_step_'] = $step;
//
//_Log("[/user/company/article/index.php] ���ƥå�ID = '".$step."'");
//_Log("[/user/company/article/index.php] XML�ե�����̾(���ƥå�ID) = '".$xmlName."'");
//
////���ܥ��󤬲����줿��碪�������ܤ���Τǡ�XML���ɤ߹��ޤʤ���
//if ($_POST['back'] != "") $xmlName = null;


//�괾ǧ�ڥ������ޥ���
$condition4Mst = null;
$undeleteOnly4Mst = true;
$order4Mst = "lpad(show_order,10,'0'),id";
$mstArticleCourseList = _DB_GetList('mst_article_course', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
if (!_IsNull($mstArticleCourseList)) {
	foreach ($mstArticleCourseList as $mKey => $mInfo) {
		$name = null;
		$name .= $mInfo['name'];

		$nameTag = null;
		$nameTag = $name;
		if (!_IsNull($mInfo['comment'])) {
			$name .= " ";
			$name .= $mInfo['comment'];

//			$nameTag .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$nameTag .= " ";
			$nameTag .= "<span class=\"input_comment\">";
			$nameTag .= $mInfo['comment'];
			$nameTag .= "</span>";
		}
		$mInfo['name_comment'] = $name;
		$mInfo['name_comment_tag'] = $nameTag;
		$mstArticleCourseList[$mKey] = $mInfo;
	}
}

//�����ƥॳ�����ޥ���
$condition4Mst = null;
$undeleteOnly4Mst = true;
$order4Mst = "lpad(show_order,10,'0'),id";
$mstSystemCourseList = _DB_GetList('mst_system_course', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
if (!_IsNull($mstSystemCourseList)) {
	//�����ƥॳ�����ޥ�����̤���������ˤ�ꡢ�괾ǧ�ڥ������ޥ������Խ����롣
	if (!_IsNull($mstArticleCourseList)) {
		//[�������] �Ż��괾����������ɥ�����=�����[�Ż�ǧ��]����������ɥ������������롣
		if (!isset($mstSystemCourseList[MST_SYSTEM_COURSE_ID_CMP_STANDARD])) unset($mstArticleCourseList[MST_ARTICLE_COURSE_ID_STANDARD]);
		//[�������] �Ż��괾��Ǥ��������=�����[�Ż�ǧ��]��Ǥ���������������롣
		if (!isset($mstSystemCourseList[MST_SYSTEM_COURSE_ID_CMP_ENTRUST])) unset($mstArticleCourseList[MST_ARTICLE_COURSE_ID_ENTRUST]);
	}
}


$xmlList = null;
if (!_IsNull($xmlName)) {


	$otherList = null;
	$otherList = array(
		'mst_article_course' => $mstArticleCourseList
	);

	//XML���ɤ߹��ࡣ
	$xmlFile = "../../../common/form_xml/".$xmlName.".xml";
	_Log("[/user/company/article/index.php] XML�ե����� = '".$xmlFile."'");
	$xmlList = _GetXml($xmlFile, $otherList);

	_Log("[/user/company/article/index.php] XML�ե��������� = '".print_r($xmlList,true)."'");

//	switch ($xmlName) {
//		case XML_NAME_SEAL_ALL:
//			//ˡ�Ͱ���ʸ����[�������Ƴ�ǧ]
//
//			//���Ƥ�XML���ɤ߹��ࡣ
//
//			//ˡ�Ͱ���ʸ����[����](��ǧ������)
//			$bufXmlFile = "../../../common/form_xml/".XML_NAME_SEAL_SET_4_CONFIRM.".xml";
//			_Log("[/user/company/article/index.php] XML�ե����� = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal'] = $bufXmlList['tbl_seal'];
//
//			//ˡ�Ͱ���ʸ����[����]
//			$bufXmlFile = "../../../common/form_xml/".XML_NAME_SEAL_IMPRINT.".xml";
//			_Log("[/user/company/article/index.php] XML�ե����� = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal_imprint'] = $bufXmlList['tbl_seal_imprint'];
//
//			///ˡ�Ͱ���ʸ����[���̾�����Ϥ���]
//			$bufXmlFile = "../../../common/form_xml/".XML_NAME_SEAL_NAME.".xml";
//			_Log("[/user/company/article/index.php] XML�ե����� = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal_name'] = $bufXmlList['tbl_seal_name'];
//			$xmlList['tbl_seal_deliver'] = $bufXmlList['tbl_seal_deliver'];
//
//
//			_Log("[/user/company/article/index.php] XML�ե���������(��XML�ޡ�����) = '".print_r($xmlList,true)."'");
//			_Log("[/user/company/article/index.php] ˡ�Ͱ���ʸ����(��XML�ޡ�����) = '".print_r($info,true)."'");
//
//			$mode = 2;
//
//			break;
//	}
}

//�����ܥ��󤬲����줿���
if ($_POST['confirm'] != "") {

	//���򤵤�Ƥ��륵���ӥ����б��������Τߤˤ��롣
	$bufXmlList = _GetSelectedServiceOnly($xmlList, $info);

	//�����ͥ����å�
	$message .= _CheackInputAll($bufXmlList, $info);

	//��ǧ��
	//"��˾����"���
	if (isset($info['update']['tbl_article_option']['art_opt_option_permission_id']) && $info['update']['tbl_article_option']['art_opt_option_permission_id'] == MST_OPTION_ID_YES) {
		$inFlag = false;
		for ($i = 1; $i <= 9; $i++) {
			if (isset($info['update']['tbl_article_option']['art_opt_permission_'.$i.'_id'])) {
				$inFlag = true;
			}
		}
		if (isset($info['update']['tbl_article_option']['art_opt_permission_note']) && !_IsNull($info['update']['tbl_article_option']['art_opt_permission_note'])) {
			$inFlag = true;
		}
		if (!$inFlag) {
			$message .= "��ǧ�Ĥ��˾������ϡ���˾�����ǧ�Ĥ����򤷤Ƥ���������\n";
		}
	}
	//�ݸ�
	//"��˾����"���
	if (isset($info['update']['tbl_article_option']['art_opt_option_insurance_id']) && $info['update']['tbl_article_option']['art_opt_option_insurance_id'] == MST_OPTION_ID_YES) {
		if (!isset($info['update']['tbl_article_option']['art_opt_insurance_id'])) {
			$message .= "�ݸ����˾������ϡ���˾�����ݸ������򤷤Ƥ���������\n";
		}
	}
	//���ȵ�§
	//"��˾����"���
	if (isset($info['update']['tbl_article_option']['art_opt_option_regulations_id']) && $info['update']['tbl_article_option']['art_opt_option_regulations_id'] == MST_OPTION_ID_YES) {
		if (!isset($info['update']['tbl_article_option']['art_opt_regulations_id'])) {
			$message .= "���ȵ�§���˾������ϡ���˾����ץ������򤷤Ƥ���������\n";
		}
	}
	//�ۡ���ڡ�������
	//"��˾����"���
	if (isset($info['update']['tbl_article_option']['art_opt_option_micro_web_id']) && $info['update']['tbl_article_option']['art_opt_option_micro_web_id'] == MST_OPTION_ID_YES) {
		if (!isset($info['update']['tbl_article_option']['art_opt_micro_web_id'])) {
			$message .= "�ۡ���ڡ���������˾������ϡ���˾����ץ������򤷤Ƥ���������\n";
		}
	}

	if (_IsNull($message)) {
		//���顼��̵����硢��ǧ���̤�ɽ�����롣
		$mode = 2;

		//���򤵤줿�����ӥ����б�������ܤΤ�ɽ�����롣
		$xmlList = $bufXmlList;

		//$message .= "���������Ƥ��ǧ���ơ��ֹ����ץܥ���򲡤��Ƥ���������";
	} else {
		//���顼��ͭ����
		$message = "�����Ϥ˸�꤬����ޤ���\n".$message;
		$errorFlag = true;
	}
}
//���ܥ��󤬲����줿���
elseif ($_POST['back'] != "") {
}
//�����ܥ��󡢼��إܥ��󤬲����줿���
elseif ($_POST['go'] != "" || $_POST['next'] != "") {
//	//�����ͥ����å�
//	$message .= _CheackInputAll($xmlList, $info);
//
//	switch ($xmlName) {
//		case XML_NAME_SEAL_SET:
//			//ˡ�Ͱ���ʸ����[����]
//			$message .= _CheackInput4SealSet($xmlList, $info);
//			break;
//		case XML_NAME_SEAL_NAME:
//			//ˡ�Ͱ���ʸ����[���̾�����Ϥ���]
//			$message .= _CheackInput4SealName($xmlList, $info);
//			break;
//		case XML_NAME_SEAL_ALL:
//			//ˡ�Ͱ���ʸ����[�������Ƴ�ǧ]
////			$message .= _CheackInput4SealSet($xmlList, $info);
//			$message .= _CheackInput4SealName($xmlList, $info);
//			break;
//		default:
//			break;
//	}
//
//	//���å�������¸���롣
//	switch ($xmlName) {
//		case XML_NAME_SEAL_SET:
//			//ˡ�Ͱ���ʸ����[����]
//			$_SESSION[SID_SEAL_INFO]['tbl_seal'] = $info['update']['tbl_seal'];
//			break;
//		case XML_NAME_SEAL_IMPRINT:
//			//ˡ�Ͱ���ʸ����[����]
//			$_SESSION[SID_SEAL_INFO]['tbl_seal_imprint'] = $info['update']['tbl_seal_imprint'];
//			break;
//		case XML_NAME_SEAL_NAME:
//			//ˡ�Ͱ���ʸ����[���̾�����Ϥ���]
//			$_SESSION[SID_SEAL_INFO]['tbl_seal_name'] = $info['update']['tbl_seal_name'];
//			$_SESSION[SID_SEAL_INFO]['tbl_seal_deliver'] = $info['update']['tbl_seal_deliver'];
//			break;
//	}

	if (_IsNull($message)) {
		//���顼��̵����硢��Ͽ���롣

		//��������Ͽ�򤹤롣(��$info�Ϻǿ�����˹�������롣)
		$res = _UpdateInfo($info);
//		$res = true;
		if ($res === false) {
			//���顼��ͭ����
			$message = "��Ͽ�˼��Ԥ��ޤ�����";
			$errorFlag = true;
		} else {

//			//��å����������ꤹ�롣
//			$message .= "��¸���ޤ�����";

			//�����ܥ��󤬲����줿���
			if ($_POST['go'] != "") {

				//���򤵤�Ƥ��륵���ӥ����б��������Τߤˤ��롣
				$bufXmlList = _GetSelectedServiceOnly($xmlList, $info);

				//�᡼����ʸ�ζ�����ʬ�����ꤹ�롣
				$body = null;

				$body .= "��������������������������������������������������������\n";
				$body .= "�桼��������\n";
				$body .= "��������������������������������������������������������\n";
//				$body .= "�桼����ID��";
//				$body .= $info['update']['tbl_user']['usr_user_id'];
//				$body .= "\n";
				$body .= "��̾����";
				$body .= $info['update']['tbl_user']['usr_family_name'];
				$body .= " ";
				$body .= $info['update']['tbl_user']['usr_first_name'];
				$body .= "\n";
				$body .= "�᡼�륢�ɥ쥹��";
				$body .= $info['update']['tbl_user']['usr_e_mail'];
				$body .= "\n";
				$body .= "\n";

				$body .= "��������������������������������������������������������\n";
				$body .= "��Ҿ���\n";
				$body .= "��������������������������������������������������������\n";
				$body .= "����(���̾)��";
				$body .= $companyInfo['tbl_company']['cmp_company_name'];
				$body .= "\n";
				$body .= "��Ω����";

				$cmpFoundYear = null;
				if (!_IsNull($companyInfo['tbl_company']['cmp_found_year'])) {
					$jpY = _ConvertAD2Jp($companyInfo['tbl_company']['cmp_found_year']);
					$cmpFoundYear = $companyInfo['tbl_company']['cmp_found_year']."(".$jpY.")";
				}
				$body .= $cmpFoundYear." ǯ ".$companyInfo['tbl_company']['cmp_found_month']." �� ".$companyInfo['tbl_company']['cmp_found_day']." ��";
				$body .= "\n";
				$body .= "\n";

				$body .= _CreateMailAll($bufXmlList, $info);//�����λ����Ǥϡ�$info�ˡ����ѵ���פ������ͤϺ������Ƥ��롣���᡼��ˤϻȤ��ʤ���

				_Log("[/user/company/article/index.php] �᡼����ʸ(_CreateMailAll) = '".$body."'");

				$body .= "\n";
				$body .= "\n";
				$body .= "\n";
				$body .= "\n";

				$body .= "--------------------------------------------------------\n";
				$body .= $siteTitle."\n";
				if (!_IsNull(COMPANY_NAME)) $body .= COMPANY_NAME."\n";
				if (!_IsNull(COMPANY_ZIP)) $body .= COMPANY_ZIP."\n";
				if (!_IsNull(COMPANY_ADDRESS)) $body .= COMPANY_ADDRESS."\n";
				if (!_IsNull(COMPANY_TEL)) $body .= "TEL��".COMPANY_TEL."\n";
				if (!_IsNull(COMPANY_FAX)) $body .= "FAX��".COMPANY_FAX."\n";
				$body .= "E-mail��".$clientMail." \n";
				if (!_IsNull(COMPANY_BUSINESS_HOURS)) $body .= "�ĶȻ��֡�".COMPANY_BUSINESS_HOURS."\n";
				$body .= "--------------------------------------------------------\n\n";

				$body .= "�괾ǧ�ڰ���������".date("Yǯn��j�� H��iʬ")."\n";
				$body .= $_SERVER["REMOTE_ADDR"]."\n";

				//�������ѥ᡼����ʸ�����ꤹ�롣
				$adminBody = "";
				//$adminBody .= $siteTitle." \n";
				//$adminBody .= "\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "��".$siteTitle."�٤��괾ǧ�ڤΰ��꤬����ޤ�����\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "\n";
				$adminBody .= $body;

				//�������ѥ᡼����ʸ�����ꤹ�롣
				$customerBody = "";
				$customerBody .= $info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." ��\n";
				$customerBody .= "\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "�����٤ϡ���".$siteTitle."�٤��괾ǧ�ڤΤ�����򤷤Ƥ����������꤬�Ȥ��������ޤ�����\n";
				$customerBody .= "��ǧ�Τ��ᡢ�����ˤ����ͤΤ���Ͽ�����Ƥ��Τ餻�������ޤ���\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "\n";
				$customerBody .= $body;


				//�������ѥ����ȥ�����ꤹ�롣
				$adminTitle = "[".$siteTitle."] �괾ǧ�ڰ��� (".$info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." �� / ".$companyInfo['tbl_company']['cmp_company_name'].")";
				//�������ѥ����ȥ�����ꤹ�롣
				$customerTitle = "[".$siteTitle."] �괾ǧ�ڰ��ꤢ�꤬�Ȥ��������ޤ��� (".$companyInfo['tbl_company']['cmp_company_name'].")";

				mb_language("Japanese");
				
				$parameter = "-f ".$clientMail;

				//�᡼������
				//�����ͤ��������롣
				$rcd = mb_send_mail($info['update']['tbl_article_deliver']['art_dlv_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

				//���饤����Ȥ��������롣
				$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_article_deliver']['art_dlv_e_mail']);

				//�ޥ��������������롣
				foreach($masterMailList as $masterMail){
					$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_article_deliver']['art_dlv_e_mail']);
				}


				//��å����������ꤹ�롣
				$message .= $info['update']['tbl_user']['usr_family_name']."&nbsp;".$info['update']['tbl_user']['usr_first_name'];
				$message .= "&nbsp;��";
				$message .= "\n";
				$message .= "\n";
				$message .= "�����٤ϡ���".$siteTitle."�٤��괾ǧ�ڤΤ�����򤷤Ƥ����������꤬�Ȥ��������ޤ�����";
				$message .= "\n";
				$message .= "�����ͤΥ᡼�륢�ɥ쥹���Ƥˤ���Ͽ���ƤΡֳ�ǧ�᡼��פ���ư��������ޤ�����";
				$message .= "\n";
				$message .= "�괾ǧ�ڽ���Τ��Ϥ��ޤ�2��3�����Ԥ�����������";
				$message .= "\n";
				$message .= "\n";
//				$message .= "���ֳ�ǧ�᡼��פ��Ϥ��ʤ����ϡ��᡼�륢�ɥ쥹������Ͽ�ߥ��β�ǽ��������ޤ��Τǡ�";
//				$message .= "\n";
//				$message .= "&nbsp;&nbsp;&nbsp;������Ǥ���&nbsp;";
				$message .= "�᡼�뤬�Ϥ��ʤ����ϡ�������Ǥ���&nbsp;";
				$message .= "<a href=\"mailto:".$clientMail."\">".$clientMail."</a>";
				$message .= "&nbsp;�ޤǥ᡼��Ǥ��䤤��碌����������";

				//��λ���̤�ɽ�����롣
				$mode = 3;
			}


	//		//ư��⡼��="¾���̷�ͳ��ɽ��"�ξ�硢����󥯤�ɽ�����롣
	//		if ($_SESSION[SID_INFO_MODE] == MST_MODE_FROM_OTHER) {
	//
	//			switch ($xmlName) {
	//				case XML_NAME_ITEM:
	//					//���ʾ���
	//					$message .= "<a href=\"../../../item/?back\" title=\"���ʰ��������\">[���ʰ��������]</a>\n";
	//					break;
	//				case XML_NAME_BOTTLE_IMAGE:
	//					//�ܥȥ��������
	//					$message .= "";
	//					break;
	//				case XML_NAME_DESIGN_IMAGE:
	//					//Ħ��ѥ������������
	//					$message .= "";
	//					break;
	//				case XML_NAME_CHARACTER_J_IMAGE:
	//					//Ħ��ʸ��(�»�)��������
	//					$message .= "";
	//					break;
	//				case XML_NAME_CHARACTER_E_IMAGE:
	//					//Ħ��ʸ��(�ѻ�)��������
	//					$message .= "";
	//					break;
	//				case XML_NAME_INQ:
	//					//��礻����
	//					switch ($_SESSION[SID_INFO_FROM_PAGE_ID]) {
	//						case PAGE_ID_INQ_PRICE:
	//							$message .= "<a href=\"../../../inquiry_price/?back\" title=\"����۰��������\">[����۰��������]</a>\n";
	//							break;
	//						default:
	//							$message .= "<a href=\"../../../inquiry/?back\" title=\"��礻���������\">[��礻���������]</a>\n";
	//							break;
	//					}
	//					break;
	//			}
	//
	//		}

//			//��λ���̤�ɽ�����롣
//			$mode = 3;
		}

	} else {
		//���顼��ͭ����
		$message = "�����Ϥ˸�꤬����ޤ���\n".$message;
		$errorFlag = true;
	}

}


//$addHref = null;
//switch($loginInfo['usr_auth_id']){
//	case AUTH_NON://����̵��
//		break;
//	default:
//		if (!_IsNull($id)) {
//			$addHref = "&amp;id=".$id;
//		}
//		break;
//}
//
////���إܥ��󤬲����줿���
//if ($_POST['next'] != "") {
//	if (!$errorFlag) {
//		//���Υڡ�����ɽ�����롣
//		$step++;
//		header("Location: ./?step=".$step.$addHref);
//		exit;
//	}
//}
////���ܥ��󤬲����줿���
//elseif ($_POST['back'] != "") {
//	//���Υڡ�����ɽ�����롣
//	$step--;
//	header("Location: ./?step=".$step.$addHref);
//	exit;
//}


//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/company/article/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/user/company/article/index.php] mode = '".$mode."'");




//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "../../..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../../img/maincontent/pt_user_company_article.jpg\" title=\"\" alt=\"�괾ǧ�ڡ�����\">";
$maincontent .= "</h2>";
$maincontent .= "\n";



//��������å�
//{treu:���顼ͭ��/false:���顼̵��}
$userStatusSystemErrorFlag = false;			//�����ƥ���������
$userStatusStandardErrorFlag = false;		//����������ɥ�����
$userStatusEntrustErrorFlag = false;		//��Ǥ��������
//[�������] ���������Ω (�����ƥ���������)
if (!_CheckUserStatus($id, $companyId, MST_SYSTEM_COURSE_ID_CMP)) {
	$userStatusSystemErrorFlag = true;

	$maincontent .= "<div id=\"system_course_system\" class=\"message payMessage\">";
	$maincontent .= "\n";
	$maincontent .= "���������������ޤ��󡣽���κ���(����)�ϡ�����������η�Ѹ�ˤ����Ѥ���ǽ�Ȥʤ�ޤ���";
	$maincontent .= "<br />";
	$maincontent .= "<br />";
	$maincontent .= "<a href=\"../../buy/\">&gt;&gt;����ʧ���Ϥ�����</a>";
	$maincontent .= "\n";
	$maincontent .= "</div>";
	$maincontent .= "\n";
}
//[�������] �Ż��괾����������ɥ�����
if (isset($mstArticleCourseList[MST_ARTICLE_COURSE_ID_STANDARD])) {
	if (!_CheckUserStatus($id, $companyId, MST_SYSTEM_COURSE_ID_CMP_STANDARD)) {
		$userStatusStandardErrorFlag = true;

		$maincontent .= "<div id=\"system_course_standard\" class=\"message payMessage\" style=\"display:none;\">";
		$maincontent .= "\n";
		$maincontent .= "���������������ޤ��󡣡� ".$mstArticleCourseList[MST_ARTICLE_COURSE_ID_STANDARD]['name']." �פϡ�����������η�Ѹ�ˤ����Ѥ���ǽ�Ȥʤ�ޤ���";
		$maincontent .= "<br />";
		$maincontent .= "<br />";
		$maincontent .= "<a href=\"../../buy/\">����ʧ���Ϥ�����</a>";
		$maincontent .= "\n";
		$maincontent .= "</div>";
		$maincontent .= "\n";
	}
}
//[�������] �Ż��괾��Ǥ��������
if (isset($mstArticleCourseList[MST_ARTICLE_COURSE_ID_ENTRUST])) {
	if (!_CheckUserStatus($id, $companyId, MST_SYSTEM_COURSE_ID_CMP_ENTRUST)) {
		$userStatusEntrustErrorFlag = true;

		$maincontent .= "<div id=\"system_course_entrust\" class=\"message payMessage\" style=\"display:none;\">";
		$maincontent .= "\n";
		$maincontent .= "���������������ޤ��󡣡� ".$mstArticleCourseList[MST_ARTICLE_COURSE_ID_ENTRUST]['name']." �פϡ�����������η�Ѹ�ˤ����Ѥ���ǽ�Ȥʤ�ޤ���";
		$maincontent .= "<br />";
		$maincontent .= "<br />";
		$maincontent .= "<a href=\"../../buy/\">����ʧ���Ϥ�����</a>";
		$maincontent .= "\n";
		$maincontent .= "</div>";
		$maincontent .= "\n";
	}
}



////���֥�˥塼�����ꤹ�롣
//$maincontent .= "<ul id=\"sealn\">";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_set\">";
//$maincontent .= "<a href=\"?step=1".$addHref."\">��������</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_imprint\">";
//$maincontent .= "<a href=\"?step=2".$addHref."\">��������</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_name\">";
//$maincontent .= "<a href=\"?step=3".$addHref."\">���̾�����Ϥ���</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_confirm\">";
//$maincontent .= "<a href=\"?step=4".$addHref."\">�������Ƴ�ǧ</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "</ul>";
//$maincontent .= "\n";


$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);


//������ץȤ����ꤹ�롣
$script = null;

$addStyle = null;

switch ($mode) {
	case 1:
//		$script .= "<script language=\"javascript\" src=\"".$basePath."/common/js/search_notary/search_notary.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
//		$script .= "\n";
//		$script .= "<script language=\"javascript\" src=\"".$basePath."/common/js/article_course/article_course.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
//		$script .= "\n";

		$script .= "<script language=\"javascript\" src=\"".$basePath."/common/js/article_option/article_option.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
		$script .= "\n";

		//������ץȤ����ꤹ�롣
		$script .= "<script type=\"text/javascript\">";
		$script .= "\n";
		$script .= "<!--";
		$script .= "\n";
		$script .= "window.addEvent('domready', function(){";
		$script .= "\n";

		//(2011/10/26) ̤���Ѥˤʤä������������ɬ�ס��ɲä�����
		if ($userStatusSystemErrorFlag) {
			$script .= "$$('div.pdfset div.pdf div.output input').setStyle('display','none');";
			$script .= "\n";
			$script .= "$$('div.pdfset div.pdf div.output').setStyle('background','url(../../../img/pdf/pdf_btn_print_03.gif) no-repeat left top');";
			$script .= "\n";

			$script .= "$$('div#frm_button input').set('value','�ߤ������Բ�');";
			$script .= "\n";
			$script .= "$$('div#frm_button input').set('disabled',true);";
			$script .= "\n";
			$script .= "$$('div#frm_button input').setStyle('background-color','#f00');";
			$script .= "\n";
			$script .= "$$('div#frm_button input').setStyle('color','#fff');";
			$script .= "\n";
		}



//		$script .= "$$('input.article_course').addEvent('click', function(e) {";
//		$script .= "\n";
//		$script .= "updateArticleCourse(this, '".$companyId."');";
//		$script .= "\n";
//		$script .= "});";
//		$script .= "\n";
//		$script .= "$$('input.article_course').addEvent('click', function(e) {";
//		$script .= "\n";
//		//$script .= "alert('name='+this.name+'/value='+this.value+'/checked='+this.checked);";
//		//$script .= "\n";
//		$script .= "setArticleCourse(this.value);";
//		$script .= "\n";
//		$script .= "});";
//		$script .= "\n";
//		$script .= "\n";
//		$script .= "var value = '';";
//		$script .= "\n";
//		$script .= "$$('input.article_course').each(function(el){";
//		$script .= "\n";
//		//$script .= "alert('name='+el.name+'/value='+el.value+'/checked='+el.checked);";
//		//$script .= "\n";
//		$script .= "if (el.checked) {";
//		$script .= "\n";
//		$script .= "value = el.value;";
//		$script .= "\n";
//		$script .= "}";
//		$script .= "\n";
//		$script .= "});";
//		$script .= "\n";
//		$script .= "setArticleCourse(value);";
//		$script .= "\n";

		$script .= "});";
		$script .= "\n";

//		$script .= "function setArticleCourse(value) {";
//		$script .= "\n";
//		$script .= "showNode('frm_button', false);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_pdf', false);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_date', false);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_deliver', false);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_charge', false);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_notary', false);";
//		$script .= "\n";
//
//		if ($userStatusStandardErrorFlag) {
//			$script .= "showNode('system_course_standard', false);";
//			$script .= "\n";
//
//			$script .= "$$('div#frm_button input').set('value','�Ρ�ǧ');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').set('disabled',false);";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('background-color','#fafafa');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('color','#333');";
//			$script .= "\n";
//		}
//		if ($userStatusEntrustErrorFlag) {
//			$script .= "showNode('system_course_entrust', false);";
//			$script .= "\n";
//
//			$script .= "$$('div#frm_button input').set('value','�Ρ�ǧ');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').set('disabled',false);";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('background-color','#fafafa');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('color','#333');";
//			$script .= "\n";
//		}
//		if ($userStatusSystemErrorFlag) {
//			$script .= "$$('div.pdfset div.pdf div.output input').setStyle('display','none');";
//			$script .= "\n";
//			$script .= "$$('div.pdfset div.pdf div.output').setStyle('background','url(../../../img/pdf/pdf_btn_print_03.gif) no-repeat left top');";
//			$script .= "\n";
//
//			$script .= "$$('div#frm_button input').set('value','�ߤ������Բ�');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').set('disabled',true);";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('background-color','#f00');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('color','#fff');";
//			$script .= "\n";
//		}
//
//		$script .= "switch (value) {";
//		$script .= "\n";
//		$script .= "case '".MST_ARTICLE_COURSE_ID_NORMAL."':";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_pdf', true);";
//		$script .= "\n";
//		$script .= "break;";
//		$script .= "\n";
//		$script .= "case '".MST_ARTICLE_COURSE_ID_STANDARD."':";
//		$script .= "\n";
//		$script .= "showNode('frm_button', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_date', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_deliver', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_charge', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_notary', true);";
//		$script .= "\n";
//
//		if ($userStatusStandardErrorFlag) {
//			$script .= "showNode('system_course_standard', true);";
//			$script .= "\n";
//
//			$script .= "$$('div#frm_button input').set('value','�ߤ������Բ�');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').set('disabled',true);";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('background-color','#f00');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('color','#fff');";
//			$script .= "\n";
//		}
//
//		$script .= "break;";
//		$script .= "\n";
//		$script .= "case '".MST_ARTICLE_COURSE_ID_ENTRUST."':";
//		$script .= "\n";
//		$script .= "showNode('frm_button', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_date', true);";
//		$script .= "\n";
//		$script .= "showNode('tbl_article_deliver', true);";
//		$script .= "\n";
//
//		if ($userStatusEntrustErrorFlag) {
//			$script .= "showNode('system_course_entrust', true);";
//			$script .= "\n";
//
//			$script .= "$$('div#frm_button input').set('value','�ߤ������Բ�');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').set('disabled',true);";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('background-color','#f00');";
//			$script .= "\n";
//			$script .= "$$('div#frm_button input').setStyle('color','#fff');";
//			$script .= "\n";
//		}
//
//		$script .= "break;";
//		$script .= "\n";
//		$script .= "}";
//		$script .= "\n";
//		$script .= "}";
//		$script .= "\n";

		$script .= "//-->";
		$script .= "\n";
		$script .= "</script>";
		$script .= "\n";


//(2011/10/26) �������Ǥ����괾��������ʤ�ɬ�ס�
//���¤ˤ�äơ����¤��롣
switch($loginInfo['usr_auth_id']){
	case AUTH_WOOROM://WOOROM����
		//�괾����
		$buf = _CreateTableInput4Article($mode, $xmlList, $info, $tabindex);
		$maincontent .= "\n";
		$maincontent .= $buf;
	break;
}

		//������ʸ�Ϥ����ꤹ�롣
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_07.txt';
		_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (������ʸ��) HTML�ƥ�ץ졼�ȥե����� = '".$tempExpFile."'");
		$htmlExp = @file_get_contents($tempExpFile);
		//"HTML"��¸�ߤ����硢ɽ�����롣
		if ($htmlExp !== false && !_IsNull($htmlExp)) {
			_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (������ʸ��) ��������");
		} else {
			//�����Ǥ��ʤ��ä����
			_Log("[/user/company/article/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (������ʸ��) �ڼ��ԡ�");
			$htmlExp = null;
		}
		if (!_IsNull($htmlExp)) {
			$buf = null;
			$buf .= $maincontent;
			$buf .= "\n";
			$buf .= "\n";
			$buf .= "\n";
			$buf .= $htmlExp;

			$maincontent = $buf;
		}
		break;
}

//switch ($xmlName) {
//	case XML_NAME_SEAL_SET:
//		//ˡ�Ͱ���ʸ����[����]
//		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
//		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
//		break;
//	case XML_NAME_SEAL_ALL:
//		//ˡ�Ͱ���ʸ����[�������Ƴ�ǧ]
////		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
////		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
//		break;
//	default:
//		break;
//}
//
//$script .= "<style type=\"text/css\">";
//$script .= "\n";
//$script .= "<!--";
//$script .= "\n";
//$script .= "ul#sealn li#".$stepId." a:link";
//$script .= ",ul#sealn li#".$stepId." a:visited";
//$script .= "\n";
//$script .= "{height: 32px;color: #3176af;border-bottom: 3px solid #76b0df;}";
//$script .= "\n";
//$script .= $addStyle;
//$script .= "\n";
//$script .= "-->";
//$script .= "\n";
//$script .= "</style>";
//$script .= "\n";






//�����ɥ�˥塼�����ꤹ�롣
$sidebar = null;

////����URL
//$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);
//
//$sidebar .= $htmlSidebarLogin;

//����URL
$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
//������桼����̾
$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
//���ߤ����Ͼ���
$htmlSidebarUserMenu = str_replace('{company_info}', _GetCompanyInfoHtml($loginInfo), $htmlSidebarUserMenu);

$sidebar .= $htmlSidebarUserMenu;


//�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_COMPANY, '', PAGE_TITLE_COMPANY, 3);
_SetBreadcrumbs(PAGE_DIR_COMPANY_ARTICLE, '', PAGE_TITLE_COMPANY_ARTICLE, 4);
//�ѥ󤯤��ꥹ�Ȥ�������롣
$breadcrumbs = _GetBreadcrumbs();

//WOOROM�եå�������
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=".$_SERVER['SERVER_NAME']."&php_self=".$_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
	$wooromFooter = null;
}



//�ƥ�ץ졼�Ȥ��Խ����롣(ɬ�ײս���ִ����롣)
//�����ȥ�
if (!_IsNull($title)) $title = "[".$title."] ";
$title = $siteTitle." ".$title;
$html = str_replace('{title}', $title, $html);
//����ƥ��
$html = str_replace('{maincontent}', $maincontent, $html);
//�����ɥ�˥塼
$html = str_replace('{sidebar}', $sidebar, $html);
//������ץ�
$html = str_replace('{script}', $script, $html);
//����URL
$html = str_replace('{base_url}', $basePath, $html);
//�ѥ󤯤��ꥹ��
$html = str_replace('{breadcrumbs}', $breadcrumbs, $html);
//WOOROM�եå�������
$html = str_replace('{woorom_footer}', $wooromFooter, $html);


_Log("[/user/company/article/index.php] end.");
echo $html;



































//���򤵤�Ƥ��륵���ӥ����б��������Τߤˤ��롣
function _GetSelectedServiceOnly($xmlList, $info) {
	_Log("[_GetSelectedServiceOnly] start.");

	$bufXmlList = $xmlList;

	//��ǧ��
	//"��˾����"���
	if (isset($info['update']['tbl_article_option']['art_opt_option_permission_id']) && $info['update']['tbl_article_option']['art_opt_option_permission_id'] == MST_OPTION_ID_YES) {
	} else {
		for ($i = 1; $i <= 9; $i++) {
			$bufXmlList = _DeleteXmlByTagAndValue($bufXmlList, 'name', 'art_opt_permission_'.$i.'_id');
		}
		$bufXmlList = _DeleteXmlByTagAndValue($bufXmlList, 'name', 'art_opt_permission_note');
	}
	//�ݸ�
	//"��˾����"���
	if (isset($info['update']['tbl_article_option']['art_opt_option_insurance_id']) && $info['update']['tbl_article_option']['art_opt_option_insurance_id'] == MST_OPTION_ID_YES) {
	} else {
		$bufXmlList = _DeleteXmlByTagAndValue($bufXmlList, 'name', 'art_opt_insurance_id');
	}
	//���ȵ�§
	//"��˾����"���
	if (isset($info['update']['tbl_article_option']['art_opt_option_regulations_id']) && $info['update']['tbl_article_option']['art_opt_option_regulations_id'] == MST_OPTION_ID_YES) {
	} else {
		$bufXmlList = _DeleteXmlByTagAndValue($bufXmlList, 'name', 'art_opt_regulations_id');
	}
	//�ۡ���ڡ�������
	//"��˾����"���
	if (isset($info['update']['tbl_article_option']['art_opt_option_micro_web_id']) && $info['update']['tbl_article_option']['art_opt_option_micro_web_id'] == MST_OPTION_ID_YES) {
	} else {
		$bufXmlList = _DeleteXmlByTagAndValue($bufXmlList, 'name', 'art_opt_micro_web_id');
	}

	_Log("[_GetSelectedServiceOnly] XML���ɤ߹��������(�Խ���) = '".print_r($xmlList,true)."'");
	_Log("[_GetSelectedServiceOnly] XML���ɤ߹��������(�Խ���) = '".print_r($bufXmlList,true)."'");
	_Log("[_GetSelectedServiceOnly] end.");
	return $bufXmlList;




//	return $xmlList;

	//(2011/10/26) ̤���Ѥˤʤä���
	
	$bufXmlList = $xmlList;
	

	//���򤵤줿�����ӥ���������롣
	$serviceId = null;
	if (isset($info['update']['tbl_article_course']['art_crs_article_course_id']) && !_IsNull($info['update']['tbl_article_course']['art_crs_article_course_id'])) {
		$serviceId = $info['update']['tbl_article_course']['art_crs_article_course_id'];
	}
	switch ($serviceId) {
		case MST_ARTICLE_COURSE_ID_NORMAL:
			//[����ǧ��]���跿�λ�١���������
			$bufXmlList = _DeleteXmlByTag($bufXmlList, 'tbl_article_date');
			$bufXmlList = _DeleteXmlByTag($bufXmlList, 'tbl_article_deliver');
			$bufXmlList = _DeleteXmlByTag($bufXmlList, 'tbl_article_charge');
			$bufXmlList = _DeleteXmlByTag($bufXmlList, 'tbl_article_notary');
			break;
		case MST_ARTICLE_COURSE_ID_STANDARD:
		