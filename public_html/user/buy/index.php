<?php
/*
 * [���������Ω.JP �ġ���]
 * ����������Τ���ʧ���ڡ���
 *
 * ��������2010/12/21	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/user/buy/index.php] start.");


_Log("[/user/buy/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/buy/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/buy/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/buy/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


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
_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../../common/temp_html/temp_base.txt';
_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


//$tempSidebarLoginFile = '../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"��¸�ߤ����硢ɽ�����롣
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) ��������");
//} else {
//	//�����Ǥ��ʤ��ä����
//	_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) �ڼ��ԡ�");
//}

$tempSidebarUserMenuFile = '../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

_Log("[/user/buy/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_BUY;

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


_Log("[/user/buy/index.php] \$_GET(�ͤ��ؤ���) = '".print_r($_GET,true)."'");

//�ѥ�᡼������������롣
$xmlName = XML_NAME_BUY;//XML�ե�����̾�����ꤹ�롣
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


		_Log("[/user/buy/index.php] {������桼�������½���} �桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/buy/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."'");


		//���¤ˤ�äơ�ɽ������桼������������¤��롣
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://����̵��

				_Log("[/user/buy/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
				_Log("[/user/buy/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
				_Log("[/user/buy/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

				$id = null;

				//��ʬ�Υ桼��������Τ�ɽ�����롣
				//�桼����ID�򸡺����롣
				$id = $loginInfo['usr_user_id'];

				_Log("[/user/buy/index.php] {������桼�������½���} ���桼����ID = '".$id."'");
				break;
		}


		//�����ͤ�������롣
		$info = $_POST;
		_Log("[/user/buy/index.php] POST = '".print_r($info,true)."'");
		//�Хå�����å�����������
		$info = _StripslashesForArray($info);
		_Log("[/user/buy/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");

		//��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣���᡼���Ⱦ�ѥ��ʤ�ʸ����������Τǡ�
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/buy/index.php] POST(��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣) = '".print_r($info,true)."'");


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



		_Log("[/user/buy/index.php] {������桼�������½���} �桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/buy/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."'");


		//���¤ˤ�äơ�ɽ������桼������������¤��롣
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://����̵��

				_Log("[/user/buy/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
				_Log("[/user/buy/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
				_Log("[/user/buy/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

				$id = null;
				$undeleteOnly4def = true;

				//��ʬ�Υ桼��������Τ�ɽ�����롣
				//�桼����ID�򸡺����롣
				$id = $loginInfo['usr_user_id'];


				_Log("[/user/buy/index.php] {������桼�������½���} ���桼����ID = '".$id."'");

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

		//���ܸ��ڡ����򥻥å�������¸���롣
		$_SESSION[SID_PAY_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/user/buy/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/user/buy/index.php] XML�ե�����̾ = '".$xmlName."'");
_Log("[/user/buy/index.php] �������å�ID = '".$id."'");


//�桼��������(���������)�����ꤹ�롣��DB�����˻��Ѥ��롣
$info['update']['tbl_user'] = $loginInfo;

//����������Τ���ʧ������̤����ξ�硢�桼��������(���������)�����ͤȤ������ꤹ�롣
if (!isset($info['update']['tbl_buy'])) {
//	$info['update']['tbl_pay']['pay_tel1'] = $loginInfo['usr_tel1'];
//	$info['update']['tbl_pay']['pay_tel2'] = $loginInfo['usr_tel2'];
//	$info['update']['tbl_pay']['pay_tel3'] = $loginInfo['usr_tel3'];
//
//	$info['update']['tbl_pay']['pay_e_mail'] = $loginInfo['usr_e_mail'];
//	$info['update']['tbl_pay']['pay_e_mail_confirm'] = $loginInfo['usr_e_mail'];
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
//_Log("[/user/buy/index.php] ���ƥå�ID = '".$step."'");
//_Log("[/user/buy/index.php] XML�ե�����̾(���ƥå�ID) = '".$xmlName."'");
//
////���ܥ��󤬲����줿��碪�������ܤ���Τǡ�XML���ɤ߹��ޤʤ���
//if ($_POST['back'] != "") $xmlName = null;


//�����ƥॳ�����ޥ���
$condition4Mst = array();
$condition4Mst['plan_id'] = $loginInfo['usr_plan_id'];		//�ץ��ID
$undeleteOnly4Mst = true;
$order4Mst = "lpad(show_order,10,'0'),id";
$mstSystemCourseList = _DB_GetList('mst_system_course', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
if (!_IsNull($mstSystemCourseList)) {
	$bufList = array();
	foreach ($mstSystemCourseList as $mKey => $mInfo) {
		$bufList[$mInfo['company_type_id']][$mInfo['id']] = $mInfo;
	}
	$mstSystemCourseList = $bufList;
}

//if (!_IsNull($mstSystemCourseList)) {
//	foreach ($mstSystemCourseList as $mKey => $mInfo) {
//		$name = null;
//		$name .= $mInfo['name'];
//		if (!_IsNull($mInfo['price'])) {
//			$name .= " ";
//			$name .= "��";
//			$name .= number_format($mInfo['price']);
//		}
//		$mInfo['name_price'] = $name;
//
//		$nameTag = null;
//		$nameTag = $name;
//		if (!_IsNull($mInfo['comment1'])) {
//			$name .= " ";
//			$name .= $mInfo['comment1'];
//
//			$nameTag .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
//			$nameTag .= "<span class=\"input_comment\">";
//			$nameTag .= $mInfo['comment1'];
//			$nameTag .= "</span>";
//		}
//
//		$tag = null;
//		$tag .= $mInfo['tag'];
//		if (!_IsNull($mInfo['price'])) {
//			$tag .= " ";
//			$tag .= "��";
//			$tag .= number_format($mInfo['price']);
//		}
//		$mInfo['tag_price'] = $tag;
//
//		$mInfo['name_price_comment'] = $name;
//		$mInfo['name_price_comment_tag'] = $nameTag;
//		$mstSystemCourseList[$mKey] = $mInfo;
//	}
//}



//���ID�򸡺����롣
//�桼����_���_��Ϣ�եơ��֥�򸡺����롣
$undeleteOnly = true;
$condition = array();
$condition['usr_cmp_rel_user_id'] = $id;		//�桼����ID
$order = "usr_cmp_rel_company_id";				//�����Ƚ�=���ID�ξ���(�ʤ�Ǥ⤤�����ɡ�)
$tblUserCompanyRelationList = _DB_GetListByAssociative('tbl_user_company_relation', 'usr_cmp_rel_company_id', null, $condition, $undeleteOnly, $order, 'usr_cmp_rel_del_flag');
$bufList = array();

$condition = array('usr_sts_user_id' => $id);
$tblUserStatusList = _DB_GetList('tbl_user_status', $condition);
_Log('***** �����������ơ��������� *****');
_Log(print_r($tblUserStatusList, true));
$companyPaymentInfo = array();
if (!_IsNull($tblUserStatusList)) {
    foreach ($tblUserStatusList as $tblUserStatus) {
        $key = $tblUserStatus['usr_sts_company_id'] . '_' . $tblUserStatus['usr_sts_system_course_id'];
        $companyPaymentInfo[$key] = $tblUserStatus['usr_sts_pay_status_id'];
    }
}
_Log('***** ����������ʧ���� *****');
_Log(print_r($companyPaymentInfo, true));

if (!_IsNull($tblUserCompanyRelationList)) {
	//��ҥơ��֥�򸡺����롣
	$condition = array();
	$condition['cmp_company_id'] = $tblUserCompanyRelationList;			//���ID
	$order = "cmp_company_type_id";										//�����Ƚ�=��ҥ�����ID�ξ���
	$order .= ",cmp_company_id desc";									//�����Ƚ�=���ID�ι߽�
	$tblCompanyList = _DB_GetList('tbl_company', $condition, $undeleteOnly, $order, 'cmp_del_flag', 'cmp_company_id');
	if (!_IsNull($tblCompanyList)) {
		foreach ($tblCompanyList as $tcKey => $tblCompanyInfo) {
			if (isset($mstSystemCourseList[$tblCompanyInfo['cmp_company_type_id']])) {
				foreach ($mstSystemCourseList[$tblCompanyInfo['cmp_company_type_id']] as $mKey => $mInfo) {
					//���ID+�����ƥॳ����ID
					$newId = $tblCompanyInfo['cmp_company_id']."_".$mInfo['id'];

                    // ���Ǥ�����Ѥߤξ��
                    if (!empty($companyPaymentInfo[$newId]) && $companyPaymentInfo[$newId] != '1') {
                        continue;
                    }

					$mInfo['id'] = $newId;

					$companyName = "<����(���̾)��̤����>";
					$companyNameTag = "<strong>&lt;����(���̾)��̤����&gt;</strong>";
					if (!_IsNull($tblCompanyInfo['cmp_company_name'])) {
						$companyName = $tblCompanyInfo['cmp_company_name'];
						$companyNameTag = "<strong>".$tblCompanyInfo['cmp_company_name']."</strong>";
					}

					$name = null;
					$name .= $companyName;
					$name .= "��";
					$name .= $mInfo['name'];
					if (!_IsNull($mInfo['price'])) {
						$name .= " ";
						$name .= "��";
						$name .= number_format($mInfo['price']);
					}
					$mInfo['name_price'] = $name;
			
					$nameTag = null;
					$nameTag = $name;
					if (!_IsNull($mInfo['comment1'])) {
						$name .= " ";
						$name .= $mInfo['comment1'];
			
						$nameTag .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						$nameTag .= "<span class=\"input_comment\">";
						$nameTag .= $mInfo['comment1'];
						$nameTag .= "</span>";
					}
			
					$tag = null;
					$tag .= $companyNameTag;
					$tag .= "��<br />";
					$tag .= "��������";
					$tag .= $mInfo['tag'];
					if (!_IsNull($mInfo['price'])) {
						$tag .= " ";
						$tag .= "��";
						$tag .= number_format($mInfo['price']);
					}
					$mInfo['tag_price'] = $tag;
			
					$mInfo['name_price_comment'] = $name;
					$mInfo['name_price_comment_tag'] = $nameTag;
					$bufList[$newId] = $mInfo;
				}
			}
		}
	}
}
if (count($bufList) > 0) {
	$mstSystemCourseList = $bufList;
} else {
	$mstSystemCourseList = null;
}

if (_IsNull($mstSystemCourseList)) {
	$message = "�ֳ��������Ω��������ϡ��ֹ�Ʊ�����Ω����פ���Ͽ���Ƥ���������\n";
	$message .= "�־���(���̾)�פΤߤǤ�빽�Ǥ��ΤǺǽ�ˤ���Ͽ����������\n";
	$message .= "����Ͽ��˰ʲ��Ρ֤����ѥ������פ�ɽ������ޤ���\n";
	$errorFlag = true;
}




$xmlList = null;
if (!_IsNull($xmlName)) {


	$otherList = null;
	$otherList = array(
		'mst_system_course' => $mstSystemCourseList
	);

	//XML���ɤ߹��ࡣ
	$xmlFile = "../../common/form_xml/".$xmlName.".xml";
	_Log("[/user/buy/index.php] XML�ե����� = '".$xmlFile."'");
	$xmlList = _GetXml($xmlFile, $otherList);

	_Log("[/user/buy/index.php] XML�ե��������� = '".print_r($xmlList,true)."'");

//	switch ($xmlName) {
//		case XML_NAME_SEAL_ALL:
//			//ˡ�Ͱ���ʸ����[�������Ƴ�ǧ]
//
//			//���Ƥ�XML���ɤ߹��ࡣ
//
//			//ˡ�Ͱ���ʸ����[����](��ǧ������)
//			$bufXmlFile = "../../common/form_xml/".XML_NAME_SEAL_SET_4_CONFIRM.".xml";
//			_Log("[/user/buy/index.php] XML�ե����� = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal'] = $bufXmlList['tbl_seal'];
//
//			//ˡ�Ͱ���ʸ����[����]
//			$bufXmlFile = "../../common/form_xml/".XML_NAME_SEAL_IMPRINT.".xml";
//			_Log("[/user/buy/index.php] XML�ե����� = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal_imprint'] = $bufXmlList['tbl_seal_imprint'];
//
//			///ˡ�Ͱ���ʸ����[���̾�����Ϥ���]
//			$bufXmlFile = "../../common/form_xml/".XML_NAME_SEAL_NAME.".xml";
//			_Log("[/user/buy/index.php] XML�ե����� = '".$bufXmlFile."'");
//			$bufXmlList = _GetXml($bufXmlFile, $otherList);
//			$xmlList['tbl_seal_name'] = $bufXmlList['tbl_seal_name'];
//			$xmlList['tbl_seal_deliver'] = $bufXmlList['tbl_seal_deliver'];
//
//
//			_Log("[/user/buy/index.php] XML�ե���������(��XML�ޡ�����) = '".print_r($xmlList,true)."'");
//			_Log("[/user/buy/index.php] ˡ�Ͱ���ʸ����(��XML�ޡ�����) = '".print_r($info,true)."'");
//
//			$mode = 2;
//
//			break;
//	}
}

//�����ܥ��󤬲����줿���
if ($_POST['confirm'] != "") {
	//�����ͥ����å�
	$message .= _CheackInputAll($xmlList, $info);

	if (_IsNull($message)) {
		//���顼��̵����硢��ǧ���̤�ɽ�����롣
		$mode = 2;

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

				$body .= _CreateMailAll($xmlList, $info);//�����λ����Ǥϡ�$info�ˡ����ѵ���פ������ͤϺ������Ƥ��롣���᡼��ˤϻȤ��ʤ���

				_Log("[/user/buy/index.php] �᡼����ʸ(_CreateMailAll) = '".$body."'");


				$body .= "\n";
				$body .= "\n";
				$body .= "��������������������������������������������������������\n";
				$body .= "����ʧ��ˡ�ˤĤ���\n";
				$body .= "��������������������������������������������������������\n";
				switch ($info['update']['tbl_buy']['buy_pay_means_id']) {
					case MST_PAY_MEANS_ID_BANK:
						//��Կ���
						$body .= "�������衧";
						$body .= "\n";
						$body .= COMPANY_BANK_ACCOUNT_BANK_NAME;
						$body .= "\n";
						$body .= COMPANY_BANK_ACCOUNT_BRANCH_NAME;
						$body .= "\n";
						$body .= COMPANY_BANK_ACCOUNT_TYPE;
						$body .= " ";
						$body .= COMPANY_BANK_ACCOUNT_NUMBER;
						$body .= "\n";
						$body .= COMPANY_BANK_ACCOUNT_NAME;
						$body .= "\n";
						$body .= "\n";

//						$body .= "������̾����";
//						$body .= "\n";
//						$body .= $info['update']['tbl_user']['usr_user_id'];
//						$body .= " ";
//						$body .= $info['update']['tbl_user']['usr_family_name_kana'];
//						$body .= $info['update']['tbl_user']['usr_first_name_kana'];
//						$body .= "\n";
//						$body .= "\n";
//						$body .= "���������κݤϡ�����̾�������˥桼����ID��Ĥ��Ƥ���������(�桼��ID + ��̾��)";
//						$body .= "\n";
//						$body .= "��������̾�����������Ͽ�Τ�̾���Ȱۤʤ����ɬ���֤���ʧ�����פ򤪴ꤤ���ޤ���";
//						$body .= "\n";
//						$body .= "�������Ρֲ����˥塼�פΡ֤���ʧ�����פ��餴Ϣ����������";
//						$body .= "\n";
//						$body .= "\n";
						break;
					case MST_PAY_MEANS_ID_CARD:
						//���쥸�åȥ�����
						$body .= COMPANY_CARD_COMMENT;
						$body .= "\n";
						$body .= "\n";
						break;
				}

				$totalPrice = 0;
				$totalPriceComment = null;
				foreach ($info['update']['tbl_buy']['buy_system_course_id'] as $systemCourseId) {
					if (isset($mstSystemCourseList[$systemCourseId]['price']) && !_IsNull($mstSystemCourseList[$systemCourseId]['price'])) {
						$totalPrice += $mstSystemCourseList[$systemCourseId]['price'];
					}
					switch ($systemCourseId) {
						case MST_SYSTEM_COURSE_ID_CMP_ENTRUST://[�������] �Ż��괾��Ǥ��������
						case MST_SYSTEM_COURSE_ID_LLC_ENTRUST://[��Ʊ���] �Ż��괾��Ǥ��������
							$totalPriceComment = SYSTEM_COURSE_COMMENT;
							break;
					}
				}
				$body .= "������ʧ���⡧";
				$body .= "\n";
				$body .= "��".number_format($totalPrice);
				$body .= "\n";
				if (!_IsNull($totalPriceComment)) {
					$body .= "\n";
					$body .= $totalPriceComment;
					$body .= "\n";
				}

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

				$body .= "����������Τ���ʧ��������".date("Yǯn��j�� H��iʬ")."\n";
				$body .= $_SERVER["REMOTE_ADDR"]."\n";

				//�������ѥ᡼����ʸ�����ꤹ�롣
				$adminBody = "";
				//$adminBody .= $siteTitle." \n";
				//$adminBody .= "\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "��".$siteTitle."�٤ˤ���������Τ���ʧ��������ޤ�����\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "\n";
				$adminBody .= $body;

				//��礻�ե�����-GoogleDocϢ��
				include_once("http://www.sin-kaisha.jp/admin/common/request.ini");
				$adminBody .= "\n";
				$adminBody .= "\n";
				$adminBody .= "googlegooglegooglegooglegooglegooglegooglegooglegooglegoogle\n";
				$adminBody .= "\n";

				$info4Req = $info;
				_Log("[/user/buy/index.php] {IDʬ��} ���Ͼ���(�Խ���) = '".print_r($info4Req,true)."'");
				if (isset($info4Req['update']['tbl_buy']['buy_system_course_id'])) {
					foreach ($info4Req['update']['tbl_buy']['buy_system_course_id'] as $scKey => $systemCourseId) {
						$bufIdList = explode("_", $systemCourseId);
						$bufCompanyId = $bufIdList[0];
						$bufSystemCourseId = $bufIdList[1];
						$info4Req['update']['tbl_buy']['buy_system_course_id'][$scKey] = $bufSystemCourseId;
						_Log("[/user/buy/index.php] {IDʬ��} ���ID + �����ƥॳ����ID = '".$systemCourseId."'");
						_Log("[/user/buy/index.php] {IDʬ��} ���ID = '".$bufCompanyId."'");
						_Log("[/user/buy/index.php] {IDʬ��} �����ƥॳ����ID = '".$bufSystemCourseId."'");
					}
				}
				_Log("[/user/buy/index.php] {IDʬ��} ���Ͼ���(�Խ���) = '".print_r($info4Req,true)."'");
				$adminBody .= _SetGoogleDocRequest(1, $info4Req);

				//�������ѥ᡼����ʸ�����ꤹ�롣
				$customerBody = "";
				$customerBody .= $info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." ��\n";
				$customerBody .= "\n";
//				$customerBody .= "**************************************************************************************\n";
//				$customerBody .= "�����٤ϡ���".$siteTitle."�٤ˤ���������Τ���ʧ���򤷤Ƥ����������꤬�Ȥ��������ޤ�����\n";
//				$customerBody .= "��ǧ�Τ��ᡢ�����ˤ����ͤΤ���Ͽ�����Ƥ��Τ餻�������ޤ���\n";
//				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "�����٤ϡ���".$siteTitle."�٤����Ѥ����������꤬�Ȥ��������ޤ���\n";
				$customerBody .= "�����ˡ�����ʧ���ˤĤ��ƤΤ�������Τ餻�������ޤ���\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "\n";
				$customerBody .= $body;


				//�������ѥ����ȥ�����ꤹ�롣
				$adminTitle = "[".$siteTitle."] ����������Τ���ʧ�� (".$info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." ��)";
				//�������ѥ����ȥ�����ꤹ�롣
//				$customerTitle = "[".$siteTitle."] ����������Τ���ʧ�����꤬�Ȥ��������ޤ���";
				$customerTitle = "[".$siteTitle."] ����������Τ���ʧ���ˤĤ���";

				mb_language("Japanese");
				
				$parameter = "-f ".$clientMail;

				//�᡼������
				//�����ͤ��������롣
				$rcd = mb_send_mail($info['update']['tbl_user']['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

				//���饤����Ȥ��������롣
				$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_user']['usr_e_mail']);

				//�ޥ��������������롣
				foreach($masterMailList as $masterMail){
					$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_user']['usr_e_mail']);
				}


				//��å����������ꤹ�롣
				$message .= $info['update']['tbl_user']['usr_family_name']."&nbsp;".$info['update']['tbl_user']['usr_first_name'];
				$message .= "&nbsp;��";
				$message .= "\n";
				$message .= "\n";
//				$message .= "�����٤ϡ���".$siteTitle."�٤ˤ���������Τ���ʧ���򤷤Ƥ����������꤬�Ȥ��������ޤ�����";
//				$message .= "\n";
//				$message .= "�����ͤΥ᡼�륢�ɥ쥹���Ƥˤ���Ͽ���ƤΡֳ�ǧ�᡼��פ���ư��������ޤ�����";
				$message .= "�����٤ϡ���".$siteTitle."�٤����Ѥ����������꤬�Ȥ��������ޤ���";
				$message .= "\n";
				$message .= "�����ͤΥ᡼�륢�ɥ쥹���Ƥˡ�����ʧ���ˤĤ��Ƥ�����Υ᡼������������Ƥ��������ޤ�����";
				$message .= "\n";
				$message .= "������ǧ���衢���Ƥε�ǽ�򤪻Ȥ�����������褦���פ��ޤ���";
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
	//					$message .= "<a href=\"../../item/?back\" title=\"���ʰ��������\">[���ʰ��������]</a>\n";
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
	//							$message .= "<a href=\"../../inquiry_price/?back\" title=\"����۰��������\">[����۰��������]</a>\n";
	//							break;
	//						default:
	//							$message .= "<a href=\"../../inquiry/?back\" title=\"��礻���������\">[��礻���������]</a>\n";
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
_Log("[/user/buy/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/user/buy/index.php] mode = '".$mode."'");




//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "../..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../img/maincontent/pt_buy.jpg\" title=\"\" alt=\"����������Τ���ʧ��\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

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

$paypalInfo = <<<EOT
------------------------------------------
�����ɷ��(PayPal)����³����ˡ
------------------------------------------
1���ʲ��Ρֺ����������ץܥ���򲡤��Ƥ���������
PayPal�Τ���ʧ�ѥڡ������̥�����ɥ���ɽ������ޤ���
����ʧ�ѥڡ����ˤϡֲ�ƣ��ǧ��׻λ�̳��פ�ɽ������Ƥ��ޤ���

2��PayPal�Τ���ʧ�ѥڡ�����ɽ������ޤ��ȡ�
���ˤ���ʧ������������ۤ����Ϥ���Ƥ��ޤ��Τǡ�����³������ʧ����³����λ���Ƥ���������
(PayPal�Τ����Ѥ����Ƥξ��⡢��ѻ�����Ͽ���뤳�Ȥ��Ǥ��ޤ�)

3����Ѥ���λ���ޤ��ȡ��᡼��ˤ����Τ����夤�����ޤ���
EOT;

$paypalSubmit =<<<EOT
function paypalSubmit () {
    var price = 0;
    jQuery("input[type=checkbox]:checked").each(function() {
        var subPrice = parseInt(jQuery(this).attr("_price"));
        price += subPrice;
    });
    console.log("total = " + price);

    if (price == 0) {
        alert("�����ѥ����������򤷤Ƥ���������");
        return false;
    } else if (!confirm("��ѽ�����Ԥ��ޤ���������Ǥ�����")) {
        return false;
    }

    var form = jQuery("#frmUpdate").attr({ method: "POST", action: "/user/buy/paypal/" });
    var hidden = jQuery("<input>").attr({ type: "hidden", name: "Payment_Amount", value: price });
    form.append(hidden);

    form.appendTo("body").submit();
}
EOT;

switch ($mode) {
	case 1:
		//������ץȤ����ꤹ�롣
		$script .= "<script type=\"text/javascript\">";
		$script .= "\n";
		$script .= "<!--";
		$script .= "\n";
		$script .= "window.addEvent('domready', function(){";
		$script .= "\n";
		$script .= "$$('input.system_course').addEvent('click', function(e) {";
		$script .= "\n";
		$script .= "var price = (0).toInt();";
		$script .= "\n";
		$script .= "var test = '';";
		$script .= "\n";

		$script .= "$$('input.system_course').each(function(el){";
		$script .= "\n";
		$script .= "test += 'name=' + el.get('name') + '/value=' + el.get('value') + '/checked=' + el.get('checked') + '/_price=' + el.get('_price') + '\\n';";
		$script .= "\n";
		$script .= "if (el.get('checked')) {";
		$script .= "\n";
		$script .= "price += el.get('_price').toInt();";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
		$script .= "});";
		$script .= "\n";

		$script .= "if (\$defined($('res_price'))) {";
		$script .= "\n";
		$script .= "$('res_price').set('html', price);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

//		$script .= "alert(test);";
//		$script .= "\n";
//		$script .= "alert(price);";
//		$script .= "\n";

		$script .= "});";
		$script .= "\n";

		$script .= "var price = (0).toInt();";
		$script .= "\n";
		$script .= "var test = '';";
		$script .= "\n";

		$script .= "$$('input.system_course').each(function(el){";
		$script .= "\n";
		$script .= "test += 'name=' + el.get('name') + '/value=' + el.get('value') + '/checked=' + el.get('checked') + '/_price=' + el.get('_price') + '\\n';";
		$script .= "\n";
		$script .= "if (el.get('checked')) {";
		$script .= "\n";
		$script .= "price += el.get('_price').toInt();";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
		$script .= "});";
		$script .= "\n";

		$script .= "if (\$defined($('res_price'))) {";
		$script .= "\n";
		$script .= "$('res_price').set('html', price);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

//		$script .= "alert(test);";
//		$script .= "\n";
//		$script .= "alert(price);";
//		$script .= "\n";

		$script .= "$$('input.pay_means').addEvent('click', function(e) {";
		$script .= "\n";

		$script .= "var test = '';";
		$script .= "\n";
		$script .= "$$('input.pay_means').each(function(el){";
		$script .= "\n";
		$script .= "test += 'name=' + el.get('name') + '/value=' + el.get('value') + '/checked=' + el.get('checked') + '' + '\\n';";
		$script .= "\n";

		$script .= "if (\$defined($('pay_means_' + el.get('value')))) {";
		$script .= "\n";
		$script .= "if (el.get('checked')) {";
		$script .= "\n";
		$script .= "$('pay_means_' + el.get('value')).setStyle('display', 'block');";
		$script .= "\n";
		$script .= "} else {";
		$script .= "\n";
		$script .= "$('pay_means_' + el.get('value')).setStyle('display', 'none');";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		$script .= "});";
		$script .= "\n";

//		$script .= "alert(test);";
//		$script .= "\n";

		$script .= "});";
		$script .= "\n";

		$script .= "var test = '';";
		$script .= "\n";

		$script .= "$$('input.pay_means').each(function(el){";
		$script .= "\n";
		$script .= "test += 'name=' + el.get('name') + '/value=' + el.get('value') + '/checked=' + el.get('checked') + '' + '\\n';";
		$script .= "\n";

		$script .= "if (\$defined($('pay_means_' + el.get('value')))) {";
		$script .= "\n";
		$script .= "if (el.get('checked')) {";
		$script .= "\n";
		$script .= "$('pay_means_' + el.get('value')).setStyle('display', 'block');";
		$script .= "\n";
		$script .= "} else {";
		$script .= "\n";
		$script .= "$('pay_means_' + el.get('value')).setStyle('display', 'none');";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		$script .= "});";
		$script .= "\n";

//		$script .= "alert(test);";
//		$script .= "\n";

		$script .= "var pay_means_comment_1 = '';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '��������';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '".COMPANY_BANK_ACCOUNT_BANK_NAME."';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '".COMPANY_BANK_ACCOUNT_BRANCH_NAME."';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '".COMPANY_BANK_ACCOUNT_TYPE."';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '".COMPANY_BANK_ACCOUNT_NUMBER."';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '".COMPANY_BANK_ACCOUNT_NAME."';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '<br />';";
		$script .= "\n";
		$script .= "pay_means_comment_1 += '�������߼�����Ϥ����ͤˤƤ���ô����������';";
		$script .= "\n";
		$script .= "if (\$defined($('pay_means_".MST_PAY_MEANS_ID_BANK."'))) {";
		$script .= "\n";
		$script .= "$('pay_means_".MST_PAY_MEANS_ID_BANK."').set('html', pay_means_comment_1);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		$script .= "var pay_means_comment_2 = '';";
		$script .= "\n";

        foreach (explode(PHP_EOL, $paypalInfo) as $line) {
            $script .= "pay_means_comment_2 += '" . $line . "';";
            $script .= PHP_EOL;
            $script .= "pay_means_comment_2 += '<br/>';";
            $script .= PHP_EOL;
        }

        $script .= "pay_means_comment_2 += '<br/>';";
        $script .= PHP_EOL;
        $script .= "pay_means_comment_2 += '<div>';";
        $script .= PHP_EOL;
        $script .= "pay_means_comment_2 += '<input onclick=\"paypalSubmit(); return false;\"  type=\"image\" src=\"https://www.paypalobjects.com/ja_JP/JP/i/btn/btn_buynowCC_LG.gif\" border=\"0\" name=\"submit\" alt=\"PayPal - ����饤��Ǥ���������ñ�ˤ���ʧ��\" style=\"background-color: #ffffff; border: none;\">';";
        $script .= PHP_EOL;
        $script .= "pay_means_comment_2 += '<img alt=\"\" border=\"0\" src=\"https://www.paypalobjects.com/ja_JP/i/scr/pixel.gif\" width=\"1\" height=\"1\">';";
        $script .= PHP_EOL;
        $script .= "pay_means_comment_2 += '</div>';";
        $script .= PHP_EOL;

//		$script .= "pay_means_comment_2 += '��PayPal����³����ˡ ';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '1.����ʧ��ˡ�ˡ֥��쥸�åȥ�����(PayPal)�פ����򤷤Ƥ���������';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '��';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '2.PayPal�Ǥ���ʧ����������URL��᡼��ˤƤ��Τ餻�������ޤ���';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '��';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '3.�᡼����˵��ܤ��줿URL�˥����������Ƥ��������ޤ��ȡ����ˤ���ʧ������������ۤ����Ϥ���Ƥ��ޤ��Τǡ�����³������ʧ����³����λ���Ƥ��������� (PayPal�Τ����Ѥ��Ϥ�Ƥξ��⡢��ѻ�����Ͽ���뤳�Ȥ��Ǥ��ޤ���) ';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '��';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '4.��Ѥ���λ���ޤ��ȡ��᡼��ˤ����Τ����夤�����ޤ��� ';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '��';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '5.��Ѥδ�λ����ǧ�Ǥ����衢�����ƥ�Τ����Ѥ���ǽ�Ǥ���';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '<br />';";
//		$script .= "\n";
//		$script .= "pay_means_comment_2 += '�������ɷ軻�����������1���ߤ�����ޤ���';";
//		$script .= "\n";

		$script .= "if (\$defined($('pay_means_".MST_PAY_MEANS_ID_CARD."'))) {";
		$script .= "\n";
		$script .= "$('pay_means_".MST_PAY_MEANS_ID_CARD."').set('html', pay_means_comment_2);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		$script .= "});";
		$script .= "\n";
		$script .= "//-->";
		$script .= "\n";

        foreach (explode(PHP_EOL, $paypalSubmit) as $line) {
            $script .= $line;
            $script .= PHP_EOL;
        }

		$script .= "</script>";
		$script .= "\n";
		break;
	case 2:

		$price = 0;
		foreach ($info['update']['tbl_buy']['buy_system_course_id'] as $systemCourseId) {
			if (isset($mstSystemCourseList[$systemCourseId]['price']) && !_IsNull($mstSystemCourseList[$systemCourseId]['price'])) {
				$price += $mstSystemCourseList[$systemCourseId]['price'];
			}
		}

		//������ץȤ����ꤹ�롣
		$script .= "<script type=\"text/javascript\">";
		$script .= "\n";
		$script .= "<!--";
		$script .= "\n";
		$script .= "window.addEvent('domready', function(){";
		$script .= "\n";

		$script .= "var price = '".number_format($price)."';";
		$script .= "\n";
		$script .= "if (\$defined($('res_price'))) {";
		$script .= "\n";
		$script .= "$('res_price').set('html', price);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		$script .= "});";
		$script .= "\n";
		$script .= "//-->";
		$script .= "\n";
		$script .= "</script>";
		$script .= "\n";
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
$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);

$sidebar .= $htmlSidebarUserMenu;


//�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_BUY, '', PAGE_TITLE_BUY, 3);
//�ѥ󤯤��ꥹ�Ȥ�������롣
$breadcrumbs = _GetBreadcrumbs();

//WOOROM�եå�������
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=".$_SERVER['SERVER_NAME']."&php_self=".$_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
	$wooromFooter = null;
}

$script2 =<<<EOT
<script>
    jQuery(function() {
        jQuery(".pay_means").on("change", function() {
            var value = jQuery(this).prop("value");
            console.log(value);
            if (value == "1") {
                jQuery("#frm_button").css("visibility", "visible");
            } else {
                jQuery("#frm_button").css("visibility", "hidden");
            }
        });
    });
</script>
EOT;

$script .= $script2;

//�ƥ�ץ졼�Ȥ��Խ����롣(ɬ�ײս���ִ����롣)
//�����ȥ�
if (!_IsNull($title)) $title = "[".$title."] ";
$title = $siteTitle." ".$title;
$html = str_replace('{title}', $title, $html);
//�᥿����
$html = str_replace ('{keywords}', PAGE_KEYWORDS_HOME, $html);
$html = str_replace ('{description}', PAGE_DESCRIPTION_HOME, $html);
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


_Log("[/user/buy/index.php] end.");
echo $html;

?>
