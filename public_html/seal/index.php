<?php
/*
 * [���������Ω.JP �ġ���]
 * ˡ�Ͱ���ʸ�ڡ���
 *
 * ��������2008/12/01	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/seal/index.php] start.");


_Log("[/seal/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/seal/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/seal/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/seal/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


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
_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


//$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
//_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"��¸�ߤ����硢ɽ�����롣
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) ��������");
//} else {
//	//�����Ǥ��ʤ��ä����
//	_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) �ڼ��ԡ�");
//}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

_Log("[/seal/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_SEAL;

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


_Log("[/seal/index.php] \$_GET(�ͤ��ؤ���) = '".print_r($_GET,true)."'");

//�ѥ�᡼������������롣
$xmlName = XML_NAME_SEAL;//XML�ե�����̾�����ꤹ�롣
$id = null;
$step = null;
$stepId = null;
switch ($requestMethod) {
	case 'POST':
//		//XML�ե�����̾
//		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
		//�������å�ID
		$id = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);
		//���ƥå�ID
		$step = (isset($_POST['condition']['_step_'])?$_POST['condition']['_step_']:null);


		_Log("[/seal/index.php] {������桼�������½���} �桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/seal/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."'");


		//���¤ˤ�äơ�ɽ������桼������������¤��롣
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://����̵��

				_Log("[/seal/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
				_Log("[/seal/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
				_Log("[/seal/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

				$id = null;

				//��ʬ�Υ桼��������Τ�ɽ�����롣
				//�桼����ID�򸡺����롣
				$id = $loginInfo['usr_user_id'];

				_Log("[/seal/index.php] {������桼�������½���} ���桼����ID = '".$id."'");
				break;
		}


		//�����ͤ�������롣
		$info = $_POST;
		_Log("[/seal/index.php] POST = '".print_r($info,true)."'");
		//�Хå�����å�����������
		$info = _StripslashesForArray($info);
		_Log("[/seal/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");

		//��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣���᡼���Ⱦ�ѥ��ʤ�ʸ����������Τǡ�
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/pay/index.php] POST(��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣) = '".print_r($info,true)."'");


		//XML�ե�����̾���������å�ID���񤭤��롣
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;

		break;
	case 'GET':
//		//XML�ե�����̾
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
		//�������å�ID
		$id = (isset($_GET['id'])?$_GET['id']:null);
		//���ƥå�ID
		$step = (isset($_GET['step'])?$_GET['step']:null);

		//���ܸ��ڡ���
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);


		//����ͤ����ꤹ�롣
		$undeleteOnly4def = false;



		_Log("[/seal/index.php] {������桼�������½���} �桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/seal/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."'");


		//���¤ˤ�äơ�ɽ������桼������������¤��롣
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://����̵��

				_Log("[/seal/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
				_Log("[/seal/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
				_Log("[/seal/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

				$id = null;
				$undeleteOnly4def = true;

				//��ʬ�Υ桼��������Τ�ɽ�����롣
				//�桼����ID�򸡺����롣
				$id = $loginInfo['usr_user_id'];


				_Log("[/seal/index.php] {������桼�������½���} ���桼����ID = '".$id."'");

//				//���ܸ��ڡ����Ϥɤ�����
//				switch ($pId) {
//					case PAGE_ID_USER://�桼�����ڡ���
//						break;
//				}
				break;
		}



//		$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
		$info['update'] = $_SESSION[SID_SEAL_INFO];

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
		$_SESSION[SID_SEAL_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/seal/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/seal/index.php] XML�ե�����̾ = '".$xmlName."'");
_Log("[/seal/index.php] �������å�ID = '".$id."'");


//�桼��������(���������)�����ꤹ�롣��DB�����˻��Ѥ��롣
$info['update']['tbl_user'] = $loginInfo;

//���Ϥ������̤����ξ�硢�桼��������(���������)�����ͤȤ������ꤹ�롣
if (!isset($info['update']['tbl_seal_deliver'])) {
	$info['update']['tbl_seal_deliver']['sel_dlv_zip1'] = $loginInfo['usr_zip1'];
	$info['update']['tbl_seal_deliver']['sel_dlv_zip2'] = $loginInfo['usr_zip2'];

	$info['update']['tbl_seal_deliver']['sel_dlv_pref_id'] = $loginInfo['usr_pref_id'];
	$info['update']['tbl_seal_deliver']['sel_dlv_address1'] = $loginInfo['usr_address1'];
	$info['update']['tbl_seal_deliver']['sel_dlv_address2'] = $loginInfo['usr_address2'];

	$info['update']['tbl_seal_deliver']['sel_dlv_tel1'] = $loginInfo['usr_tel1'];
	$info['update']['tbl_seal_deliver']['sel_dlv_tel2'] = $loginInfo['usr_tel2'];
	$info['update']['tbl_seal_deliver']['sel_dlv_tel3'] = $loginInfo['usr_tel3'];

	$info['update']['tbl_seal_deliver']['sel_dlv_e_mail'] = $loginInfo['usr_e_mail'];
	$info['update']['tbl_seal_deliver']['sel_dlv_e_mail_confirm'] = $loginInfo['usr_e_mail'];

	$info['update']['tbl_seal_deliver']['sel_dlv_family_name'] = $loginInfo['usr_family_name'];
	$info['update']['tbl_seal_deliver']['sel_dlv_first_name'] = $loginInfo['usr_first_name'];
}

switch ($step) {
	case 1:
		//ˡ�Ͱ���ʸ����[����]
		//��XML�����Υե�����ǤϤʤ���ľ�ܽ񤭽Ф���
		$xmlName = XML_NAME_SEAL_SET;

		$stepId = "sealn_set";
		break;
	case 2:
		//ˡ�Ͱ���ʸ����[����]
		$xmlName = XML_NAME_SEAL_IMPRINT;

		$stepId = "sealn_imprint";
		break;
	case 3:
		//ˡ�Ͱ���ʸ����[���̾�����Ϥ���]
		$xmlName = XML_NAME_SEAL_NAME;

		$stepId = "sealn_name";
		break;
	case 4:
		//ˡ�Ͱ���ʸ����[�������Ƴ�ǧ]
		$xmlName = XML_NAME_SEAL_ALL;

		$stepId = "sealn_confirm";
		break;
	default:
		//ˡ�Ͱ���ʸ����[����]
		//��XML�����Υե�����ǤϤʤ���ľ�ܽ񤭽Ф���
		$xmlName = XML_NAME_SEAL_SET;

		$stepId = "sealn_set";

		$step = 1;
		break;
}
$info['condition']['_step_'] = $step;

_Log("[/seal/index.php] ���ƥå�ID = '".$step."'");
_Log("[/seal/index.php] XML�ե�����̾(���ƥå�ID) = '".$xmlName."'");

//���ܥ��󤬲����줿��碪�������ܤ���Τǡ�XML���ɤ߹��ޤʤ���
if ($_POST['back'] != "") $xmlName = null;


//�ǥե���Ȥβ��̾�����ꤹ�롣
$defSelNamCompanyName = null;

$xmlList = null;
if (!_IsNull($xmlName)) {


	$otherList = null;

	//�桼����ID�˴�Ϣ������ID�򸡺����롣
	//�桼����_���_��Ϣ�եơ��֥�
	$condition = null;
	$condition['usr_cmp_rel_user_id'] = $id;
	$order = null;
	$order .= "usr_cmp_rel_company_id";		//�����Ⱦ��=���ID�ξ���
	$tblUserCompanyRelationList = _DB_GetListByAssociative('tbl_user_company_relation', 'usr_cmp_rel_company_id', null, $condition, true, $order,'usr_cmp_rel_del_flag');
	$tblCompanyList = null;
	if (!_IsNull($tblUserCompanyRelationList)) {
		//��ҥơ��֥�
		$condition = null;
		$condition['cmp_company_id'] = $tblUserCompanyRelationList;//���ID
		$order = null;
		$order .= "cmp_company_id";		//�����Ⱦ��=���ID�ξ���
		$tblCompanyList = _DB_GetList('tbl_company', $condition, true, $order, 'cmp_del_flag', 'cmp_company_id');
		foreach ($tblCompanyList as $cKey => $tblCompanyInfo) {
			if (_IsNull($tblCompanyInfo['cmp_company_name'])) {
				$bufId = null;
				$bufName = '�����̾��̤��Ͽ�Ǥ�����˲��̾����Ͽ���Ƥ���������';
				$tblCompanyInfo['cmp_company_id'] = $bufId;
				$tblCompanyInfo['cmp_company_name'] = $bufName;
				$tblCompanyList[$cKey] = $tblCompanyInfo;
			} else {
				if (_IsNull($defSelNamCompanyName)) {
					$defSelNamCompanyName = $tblCompanyInfo['cmp_company_id'];
				}
			}
		}
	}
	if (_IsNull($tblCompanyList)) {
		$tblCompanyList[] = array('cmp_company_id' => '', 'cmp_company_name' => '�����ߡ�̤��Ͽ�Ǥ���');
	}

	$planExplanation = null;
	//�ץ��ID
	switch($loginInfo['usr_plan_id']){
		case MST_PLAN_ID_NORMAL://�̾�ץ��
			break;
		default:
			//�ץ��ޥ���
			$condition4Mst = null;
			$undeleteOnly4Mst = true;
			$order4Mst = "lpad(show_order,10,'0'),id";
			$mstPlanList = _DB_GetList('mst_plan', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
			if (!_IsNull($mstPlanList)) {
				foreach ($mstPlanList as $mKey => $mstPlanInfo) {
					//���Ψ��̤����ϼ��ء�
					if (_IsNull($mstPlanInfo['value'])) continue;
					if (!_IsNull($planExplanation)) $planExplanation .= "<br />";
					$planExplanation .= "��";
					$planExplanation .= $mstPlanInfo['name'];
					$planExplanation .= " ";
					$planExplanation .= "��������";
					$planExplanation .= $mstPlanInfo['value'];
					$planExplanation .= "%OFF";
					$planExplanation .= "��";
				}
			}
			break;
	}

	//���եơ��֥�
	$condition = null;
	$order = null;
	$order .= "lpad(sel_show_order,10,'0')";	//�����Ⱦ��=ɽ����ξ���
	$order .= ",sel_seal_id";					//�����Ⱦ��=ID�ξ���
	$tblSealList = _DB_GetList('tbl_seal', $condition, true, $order, 'sel_del_flag', 'sel_seal_id');
	if (!_IsNull($tblSealList)) {
		foreach ($tblSealList as $sKey => $tblSealInfo) {

			$bufTag = null;
			$bufName = null;

			//���ʲ���
			$selPriceShow = null;
			$selPrice = $tblSealInfo['sel_price'];
//			if (!_IsNull($selPrice)) $selPriceShow = "��".number_format($selPrice)."- (�����ǡ�������������������)";
			if (!_IsNull($selPrice)) $selPriceShow = "��".number_format($selPrice)."- (�����ǹ���)";

			$bufTag .= $tblSealInfo['sel_name'];
			$bufTag .= "&nbsp;";
			$bufTag .= $selPriceShow;
			$bufTag .= "<br />";
			$bufTag .= "<img src=\"../img/seal/".sprintf('%03d', $tblSealInfo['sel_seal_id']).".jpg\" alt=\"".htmlspecialchars($tblSealInfo['sel_name'])."\" />";
			$bufTag .= "<p>";
			$bufTag .= nl2br($tblSealInfo['sel_explanation']);
			$bufTag .= "</p>";

			if (!_IsNull($planExplanation)) {
				$bufTag .= "<p class=\"sealset_plan\">";
				$bufTag .= $planExplanation;
				$bufTag .= "<p>";
			}

			$bufName .= $tblSealInfo['sel_name'];
			$bufName .= " ";
			$bufName .= $selPriceShow;

			$tblSealInfo['tag'] = $bufTag;
			$tblSealInfo['name_price'] = $bufName;
			$tblSealList[$sKey] = $tblSealInfo;
		}
	}

	$otherList = array(
		 'tbl_company' => $tblCompanyList
		,'tbl_seal' => $tblSealList
	);


	//XML���ɤ߹��ࡣ
	$xmlFile = "../common/form_xml/".$xmlName.".xml";
	_Log("[/seal/index.php] XML�ե����� = '".$xmlFile."'");
	$xmlList = _GetXml($xmlFile, $otherList);

	_Log("[/seal/index.php] XML�ե��������� = '".print_r($xmlList,true)."'");

	switch ($xmlName) {
		case XML_NAME_SEAL_ALL:
			//ˡ�Ͱ���ʸ����[�������Ƴ�ǧ]

			//���Ƥ�XML���ɤ߹��ࡣ

			//ˡ�Ͱ���ʸ����[����](��ǧ������)
			$bufXmlFile = "../common/form_xml/".XML_NAME_SEAL_SET_4_CONFIRM.".xml";
			_Log("[/seal/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile, $otherList);
			$xmlList['tbl_seal'] = $bufXmlList['tbl_seal'];

			//ˡ�Ͱ���ʸ����[����]
			$bufXmlFile = "../common/form_xml/".XML_NAME_SEAL_IMPRINT.".xml";
			_Log("[/seal/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile, $otherList);
			$xmlList['tbl_seal_imprint'] = $bufXmlList['tbl_seal_imprint'];

			///ˡ�Ͱ���ʸ����[���̾�����Ϥ���]
			$bufXmlFile = "../common/form_xml/".XML_NAME_SEAL_NAME.".xml";
			_Log("[/seal/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile, $otherList);
			$xmlList['tbl_seal_name'] = $bufXmlList['tbl_seal_name'];
			$xmlList['tbl_seal_deliver'] = $bufXmlList['tbl_seal_deliver'];


			_Log("[/seal/index.php] XML�ե���������(��XML�ޡ�����) = '".print_r($xmlList,true)."'");
			_Log("[/seal/index.php] ˡ�Ͱ���ʸ����(��XML�ޡ�����) = '".print_r($info,true)."'");

			$mode = 2;

			break;
	}
}


//���̾��̤����ξ�硢����ͤ����ꤹ�롣
if (!isset($info['update']['tbl_seal_name'])) {
	$info['update']['tbl_seal_name']['sel_nam_company_name'] = $defSelNamCompanyName;
}



//�����ܥ��󡢼��إܥ��󤬲����줿���
if ($_POST['go'] != "" || $_POST['next'] != "") {
	//�����ͥ����å�
	$message .= _CheackInputAll($xmlList, $info);

	switch ($xmlName) {
		case XML_NAME_SEAL_SET:
			//ˡ�Ͱ���ʸ����[����]
			$message .= _CheackInput4SealSet($xmlList, $info);
			break;
		case XML_NAME_SEAL_NAME:
			//ˡ�Ͱ���ʸ����[���̾�����Ϥ���]
			$message .= _CheackInput4SealName($xmlList, $info);
			break;
		case XML_NAME_SEAL_ALL:
			//ˡ�Ͱ���ʸ����[�������Ƴ�ǧ]
//			$message .= _CheackInput4SealSet($xmlList, $info);
			$message .= _CheackInput4SealName($xmlList, $info);
			break;
		default:
			break;
	}

	//���å�������¸���롣
	switch ($xmlName) {
		case XML_NAME_SEAL_SET:
			//ˡ�Ͱ���ʸ����[����]
			$_SESSION[SID_SEAL_INFO]['tbl_seal'] = $info['update']['tbl_seal'];
			break;
		case XML_NAME_SEAL_IMPRINT:
			//ˡ�Ͱ���ʸ����[����]
			$_SESSION[SID_SEAL_INFO]['tbl_seal_imprint'] = $info['update']['tbl_seal_imprint'];
			break;
		case XML_NAME_SEAL_NAME:
			//ˡ�Ͱ���ʸ����[���̾�����Ϥ���]
			$_SESSION[SID_SEAL_INFO]['tbl_seal_name'] = $info['update']['tbl_seal_name'];
			$_SESSION[SID_SEAL_INFO]['tbl_seal_deliver'] = $info['update']['tbl_seal_deliver'];
			break;
	}

	if (_IsNull($message)) {
		//���顼��̵����硢��Ͽ���롣

		//��������Ͽ�򤹤롣(��$info�Ϻǿ�����˹�������롣)
//		$res = _UpdateInfo($info);
		$res = true;
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
				$body .= _CreateMailAll($xmlList, $info);//�����λ����Ǥϡ�$info�ˡ����ѵ���פ������ͤϺ������Ƥ��롣���᡼��ˤϻȤ��ʤ���

				_Log("[/seal/index.php] �᡼����ʸ(_CreateMailAll) = '".$body."'");

				$body .= "\n";
				$body .= "\n";
				$body .= "\n";
				$body .= "��������������������������������������������������������\n";
				$body .= "����ʧ��ˡ�ˤĤ���\n";
				$body .= "��������������������������������������������������������\n";
				//��Կ���
				$body .= "�������衧";
				$body .= "\n";
				$body .= SEAL_BANK_ACCOUNT_BANK_NAME;
				$body .= "\n";
				$body .= SEAL_BANK_ACCOUNT_BRANCH_NAME;
				$body .= "\n";
				$body .= SEAL_BANK_ACCOUNT_TYPE;
				$body .= " ";
				$body .= SEAL_BANK_ACCOUNT_NUMBER;
				$body .= "\n";
				$body .= SEAL_BANK_ACCOUNT_NAME;
				$body .= "\n";
				$body .= "\n";

				//�ץ��ޥ���
				$condition4Mail = array();
				$condition4Mail['id'] = $info['update']['tbl_user']['usr_plan_id'];					//�ץ��ID
				$undeleteOnly4Mail = true;
				$mstPlanInfo4Mail = _DB_GetInfo('mst_plan', $condition4Mail, $undeleteOnly4Mail, 'del_flag');

				//���եơ��֥�
				$condition4Mail = array();
				$condition4Mail['sel_seal_id'] = $info['update']['tbl_seal']['sel_seal_id'];		//����ID
				$undeleteOnly4Mail = true;
				$tblSealInfo4Mail = _DB_GetInfo('tbl_seal', $condition4Mail, $undeleteOnly4Mail, 'sel_del_flag');

				//���ʲ���
				$sealPrice = $tblSealInfo4Mail['sel_price'];
				//���Ψ(ñ��:%)
				$offRate = $mstPlanInfo4Mail['value'];
				//�������
				$offPrice = 0;
				//�������
				$sellPrice = $sealPrice;
				//������
				$sealComment = null;

				if (!_IsNull($offRate)) {
					$offPrice = floor($sealPrice * $offRate / 100);//ü�����ڤ�Τ�
					$sellPrice = $sealPrice - $offPrice;
					$sealComment .= "��";
					$sealComment .= $mstPlanInfo4Mail['name'];
					$sealComment .= " ";
					$sealComment .= "��������";
					$sealComment .= $offRate;
					$sealComment .= "%OFF";
					$sealComment .= "��";
				}

				_Log("[/seal/index.php] ���Ψ(ñ��:%) = '".$offRate."'");
				_Log("[/seal/index.php] ���ʲ��� = '".$sealPrice."'");
				_Log("[/seal/index.php] ������� = '".$offPrice."'");
				_Log("[/seal/index.php] ������� = '".$sellPrice."'");
				_Log("[/seal/index.php] ������ = '".$sealComment."'");


				$body .= "��������ۡ�";
				$body .= "\n";
				$body .= "��".number_format($sellPrice)."- (�����ǹ���)";
				$body .= "\n";
				if (!_IsNull($sealComment)) {
					$body .= $sealComment;
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

				$body .= "ˡ�Ͱ���ʸ������".date("Yǯn��j�� H��iʬ")."\n";
				$body .= $_SERVER["REMOTE_ADDR"]."\n";

				//�������ѥ᡼����ʸ�����ꤹ�롣
				$adminBody = "";
				//$adminBody .= $siteTitle." \n";
				//$adminBody .= "\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "��".$siteTitle."�٤�ˡ�Ͱ�����ʸ������ޤ�����\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "\n";
				$adminBody .= $body;

				//��礻�ե�����-GoogleDocϢ��
				include_once("http://www.sin-kaisha.jp/admin/common/request.ini");
				$adminBody .= "\n";
				$adminBody .= "\n";
				$adminBody .= "googlegooglegooglegooglegooglegooglegooglegooglegooglegoogle\n";
				$adminBody .= "\n";
				$adminBody .= _SetGoogleDocRequest(1, $info);

				//�������ѥ᡼����ʸ�����ꤹ�롣
				$customerBody = "";
				$customerBody .= $info['update']['tbl_seal_deliver']['sel_dlv_family_name']." ".$info['update']['tbl_seal_deliver']['sel_dlv_first_name']." ��\n";
				$customerBody .= "\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "�����٤ϡ���".$siteTitle."�٤�ˡ�Ͱ�����ʸ�򤷤Ƥ����������꤬�Ȥ��������ޤ�����\n";
				$customerBody .= "��ǧ�Τ��ᡢ�����ˤ����ͤΤ���Ͽ�����Ƥ��Τ餻�������ޤ���\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "\n";
				$customerBody .= $body;


				//�������ѥ����ȥ�����ꤹ�롣
				$adminTitle = "[".$siteTitle."] ˡ�Ͱ���ʸ (".$info['update']['tbl_seal_deliver']['sel_dlv_family_name']." ".$info['update']['tbl_seal_deliver']['sel_dlv_first_name']." ��)";
				//�������ѥ����ȥ�����ꤹ�롣
				$customerTitle = "[".$siteTitle."] ˡ�Ͱ���ʸ���꤬�Ȥ��������ޤ���";

				mb_language("Japanese");
				
				$parameter = "-f ".$clientMail;

				//�᡼������
				//�����ͤ��������롣
				$rcd = mb_send_mail($info['update']['tbl_seal_deliver']['sel_dlv_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

				//���饤����Ȥ��������롣
				$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_seal_deliver']['sel_dlv_e_mail']);

				//�ޥ��������������롣
				foreach($masterMailList as $masterMail){
					$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_seal_deliver']['sel_dlv_e_mail']);
				}


				//��å����������ꤹ�롣
				$message .= $info['update']['tbl_seal_deliver']['sel_dlv_family_name']."&nbsp;".$info['update']['tbl_seal_deliver']['sel_dlv_first_name'];
				$message .= "&nbsp;��";
				$message .= "\n";
				$message .= "\n";
				$message .= "�����٤ϡ���".$siteTitle."�٤�ˡ�Ͱ�����ʸ�򤷤Ƥ����������꤬�Ȥ��������ޤ�����";
				$message .= "\n";
				$message .= "�����ͤΥ᡼�륢�ɥ쥹���Ƥˤ���Ͽ���ƤΡֳ�ǧ�᡼��פ���ư��������ޤ�����";
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
	//					$message .= "<a href=\"../item/?back\" title=\"���ʰ��������\">[���ʰ��������]</a>\n";
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
	//							$message .= "<a href=\"../inquiry_price/?back\" title=\"����۰��������\">[����۰��������]</a>\n";
	//							break;
	//						default:
	//							$message .= "<a href=\"../inquiry/?back\" title=\"��礻���������\">[��礻���������]</a>\n";
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


$addHref = null;
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://����̵��
		break;
	default:
		if (!_IsNull($id)) {
			$addHref = "&amp;id=".$id;
		}
		break;
}

//���إܥ��󤬲����줿���
if ($_POST['next'] != "") {
	if (!$errorFlag) {
		//���Υڡ�����ɽ�����롣
		$step++;
		header("Location: ./?step=".$step.$addHref);
		exit;
	}
}
//���ܥ��󤬲����줿���
elseif ($_POST['back'] != "") {
	//���Υڡ�����ɽ�����롣
	$step--;
	header("Location: ./?step=".$step.$addHref);
	exit;
}


//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/seal/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/seal/index.php] mode = '".$mode."'");




//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../img/maincontent/pt_seal.jpg\" title=\"\" alt=\"���պ����ʼ°�����԰�����\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

//���֥�˥塼�����ꤹ�롣
$maincontent .= "<ul id=\"sealn\">";
$maincontent .= "\n";
$maincontent .= "<li id=\"sealn_set\">";
$maincontent .= "<a href=\"?step=1".$addHref."\">��������</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"sealn_imprint\">";
$maincontent .= "<a href=\"?step=2".$addHref."\">��������</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"sealn_name\">";
$maincontent .= "<a href=\"?step=3".$addHref."\">���̾�����Ϥ���</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"sealn_confirm\">";
$maincontent .= "<a href=\"?step=4".$addHref."\">�������Ƴ�ǧ</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "</ul>";
$maincontent .= "\n";


$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);


//������ץȤ����ꤹ�롣
$script = null;

$addStyle = null;

switch ($xmlName) {
	case XML_NAME_SEAL_SET:
		//ˡ�Ͱ���ʸ����[����]
		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
		break;
	case XML_NAME_SEAL_ALL:
		//ˡ�Ͱ���ʸ����[�������Ƴ�ǧ]
//		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
//		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
		break;
	default:
		break;
}

$script .= "<style type=\"text/css\">";
$script .= "\n";
$script .= "<!--";
$script .= "\n";
$script .= "ul#sealn li#".$stepId." a:link";
$script .= ",ul#sealn li#".$stepId." a:visited";
$script .= "\n";
$script .= "{height: 32px;color: #3176af;border-bottom: 3px solid #76b0df;}";
$script .= "\n";
$script .= $addStyle;
$script .= "\n";
$script .= "-->";
$script .= "\n";
$script .= "</style>";
$script .= "\n";






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
_SetBreadcrumbs(PAGE_DIR_SEAL, '', PAGE_TITLE_SEAL, 3);
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


_Log("[/seal/index.php] end.");
echo $html;



























/**
 * ���ղ��̾������
 * �����ͤΥ����å��򤹤롣
 *
 * @param	array	$xmlList		XML���ɤ߹��������
 * @param	array	$info			���Ϥ����ͤ���Ǽ����Ƥ�������
 * @return	���顼��å�����
 * @access  public
 * @since
 */
function _CheackInput4SealName($xmlList, $info) {
	$res = null;
//	if (!isset($info['update']['tbl_seal_name']['sel_nam_company_name']) || _IsNull($info['update']['tbl_seal_name']['sel_nam_company_name'])) {
//		if (!isset($info['update']['tbl_seal_name']['sel_nam_other_company_name']) || _IsNull($info['update']['tbl_seal_name']['sel_nam_other_company_name'])) {
//			$res .= "���̾���ϡ��̤β��̾�����Ϥ��Ƥ���������";
//		}
//	}
	return $res;
}


/**
 * ���եơ��֥������
 * �����ͤΥ����å��򤹤롣
 *
 * @param	array	$xmlList		XML���ɤ߹��������
 * @param	array	$info			���Ϥ����ͤ���Ǽ����Ƥ�������
 * @return	���顼��å�����
 * @access  public
 * @since
 */
function _CheackInput4SealSet($xmlList, $info) {
	$res = null;
	if (!isset($info['update']['tbl_seal']['sel_seal_id']) || _IsNull($info['update']['tbl_seal']['sel_seal_id'])) {
		$label = $xmlList['tbl_seal']['item_label']['sel_seal_id'];
		$res .= $label."�����򤷤Ƥ���������";
	}
	return $res;
}





/**
 * ���եơ��֥������
 * �����Ѥ�ɽ������ơ��֥�(�ե�����)��������롣
 *
 * @param	int		$mode			ư��⡼��{1:����/2:��ǧ/3:��λ/4:���顼}
 * @param	array	$xmlList		XML���ɤ߹��������
 * @param	array	$info			���Ϥ����ͤ���Ǽ����Ƥ�������
 * @param	int		&$tabindex		���֥���ǥå���
 * @return	�ơ��֥�(�ե�����)HTMLʸ����
 * @access  public
 * @since
 */
function _CreateTableInput4SealSet($mode, $xmlList, $info, &$tabindex) {

	//���եơ��֥�
	$condition = null;
	$order = null;
	$order .= "lpad(sel_show_order,10,'0')";	//�����Ⱦ��=ɽ����ξ���
	$order .= ",sel_seal_id";					//�����Ⱦ��=ID�ξ���
	$tblSealList = _DB_GetList('tbl_seal', $condition, true, $order, 'sel_del_flag', 'sel_seal_id');

	//���ե��åȥޥ���
	$condition = null;
	$order = null;
	$order .= "lpad(show_order,10,'0')";	//�����Ⱦ��=ɽ����ξ���
	$order .= ",id";						//�����Ⱦ��=ID�ξ���
	$mstSealSetList = _DB_GetList('mst_seal_set', $condition, true, $order, 'del_flag', 'id');

	if (_IsNull($tblSealList)) return null;
	if (_IsNull($mstSealSetList)) return null;


	$planExplanation = null;
	//�ץ��ID
	switch($info['update']['tbl_user']['usr_plan_id']){
		case MST_PLAN_ID_NORMAL://�̾�ץ��
			break;
		default:
			//�ץ��ޥ���
			$condition4Mst = null;
			$undeleteOnly4Mst = true;
			$order4Mst = "lpad(show_order,10,'0'),id";
			$mstPlanList = _DB_GetList('mst_plan', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
			if (!_IsNull($mstPlanList)) {
				foreach ($mstPlanList as $mKey => $mstPlanInfo) {
					//���Ψ��̤����ϼ��ء�
					if (_IsNull($mstPlanInfo['value'])) continue;
					if (!_IsNull($planExplanation)) $planExplanation .= "<br />";
					$planExplanation .= "��";
					$planExplanation .= $mstPlanInfo['name'];
					$planExplanation .= " ";
					$planExplanation .= "��������";
					$planExplanation .= $mstPlanInfo['value'];
					$planExplanation .= "%OFF";
					$planExplanation .= "��";
				}
			}
			break;
	}


	$res = null;
	$message = null;

	switch ($mode) {
		case 1:
			foreach ($mstSealSetList as $msKey => $mstSealSetInfo) {
				$resBufSet = null;
				foreach ($tblSealList as $tsKey => $tblSealInfo) {
					if ($mstSealSetInfo['id'] != $tblSealInfo['sel_seal_set_id']) continue;
					$resBuf = null;

					//���ʲ���
					$selPriceShow = null;
					$selPrice = $tblSealInfo['sel_price'];
//					if (!_IsNull($selPrice)) $selPriceShow = "��".number_format($selPrice)."- (�����ǡ�������������������)";
					if (!_IsNull($selPrice)) $selPriceShow = "��".number_format($selPrice)."- (�����ǹ���)";

					$id = "sel_seal_id_".$tblSealInfo['sel_seal_id'];

					$checked = null;
					if (isset($info['update']['tbl_seal']['sel_seal_id']) && $info['update']['tbl_seal']['sel_seal_id'] == $tblSealInfo['sel_seal_id']) {
						$checked = "checked=\"checked\"";
					}

					$resBuf .= "<div class=\"seal\">";
					$resBuf .= "<h5>";
					$resBuf .= "<input type=\"radio\" name=\"update[tbl_seal][sel_seal_id]\" id=\"".$id."\" value=\"".$tblSealInfo['sel_seal_id']."\" ".$checked." />";
					$resBuf .= "<label for=\"".$id."\">";
					$resBuf .= $tblSealInfo['sel_name'];
					$resBuf .= "&nbsp;";
					$resBuf .= $selPriceShow;
					$resBuf .= "</label>";
					$resBuf .= "</h5>";

					$resBuf .= "<img src=\"../img/seal/".sprintf('%03d', $tblSealInfo['sel_seal_id']).".jpg\" alt=\"".htmlspecialchars($tblSealInfo['sel_name'])."\" />";

					$resBuf .= "<p>";
					$resBuf .= nl2br($tblSealInfo['sel_explanation']);
					$resBuf .= "</p>";

					if (!_IsNull($planExplanation)) {
						$resBuf .= "<p class=\"sealset_plan\">";
						$resBuf .= $planExplanation;
						$resBuf .= "</p>";
					}

					$resBuf .= "<div class=\"seal_end\"></div>";

					$resBuf .= "</div><!-- End seal -->";//<!-- End seal -->

					if (!_IsNull($resBufSet)) $resBufSet .= "\n";
					$resBufSet .= $resBuf;
				}

				if (!_IsNull($resBufSet)) {
					$resBuf = null;
					$resBuf .= "<div class=\"sealset\" id=\"seal_set_id_".$mstSealSetInfo['id']."\">";
					$resBuf .= "<h4>";
					$resBuf .= $mstSealSetInfo['name'];
					$resBuf .= "</h4>";
					$resBuf .= $resBufSet;
					$resBuf .= "</div><!-- End sealset -->";//<!-- End sealset -->

					if (!_IsNull($res)) $res .= "\n";
					$res .= $resBuf;
				}
			}

			break;
		case 2:
			$resBufSet = null;

			$tblSealInfo = null;
			if (isset($info['update']['tbl_seal']['sel_seal_id']) && !_IsNull($info['update']['tbl_seal']['sel_seal_id'])) {
				if (isset($tblSealList[$info['update']['tbl_seal']['sel_seal_id']])) {
					$tblSealInfo = $tblSealList[$info['update']['tbl_seal']['sel_seal_id']];

					$resBuf = null;

					//���ʲ���
					$selPriceShow = null;
					$selPrice = $tblSealInfo['sel_price'];
//					if (!_IsNull($selPrice)) $selPriceShow = "��".number_format($selPrice)."- (�����ǡ�������������������)";
					if (!_IsNull($selPrice)) $selPriceShow = "��".number_format($selPrice)."- (�����ǹ���)";

					$resBuf .= "<div class=\"seal\">";
					$resBuf .= "<h5>";
					$resBuf .= $tblSealInfo['sel_name'];
					$resBuf .= "&nbsp;";
					$resBuf .= $selPriceShow;
					$resBuf .= "</h5>";

					$resBuf .= "<img src=\"../img/seal/".sprintf('%03d', $tblSealInfo['sel_seal_id']).".jpg\" alt=\"".htmlspecialchars($tblSealInfo['sel_name'])."\" />";

					$resBuf .= "<p>";
					$resBuf .= nl2br($tblSealInfo['sel_explanation']);
					$resBuf .= "</p>";

					$resBuf .= "<div class=\"seal_end\"></div>";

					$resBuf .= "</div><!-- End seal -->";//<!-- End seal -->

					$resBufSet .= $resBuf;
				}
			}
			if (_IsNull($resBufSet)) {
				$resBuf = null;
				$resBuf .= "<div class=\"requiredMessage\">";
				$resBuf .= "���դ����򤷤Ƥ���������";
				$resBuf .= "</div>";

				if (!_IsNull($res)) $res .= "\n";
				$res .= $resBuf;
			} else {
				$resBuf = null;
				$resBuf .= "<div class=\"sealset\" id=\"seal_set_id_".$tblSealInfo['sel_seal_set_id']."\">";
				$resBuf .= "<h4>";
				$resBuf .= $mstSealSetList[$tblSealInfo['sel_seal_set_id']]['name'];
				$resBuf .= "</h4>";
				$resBuf .= $resBufSet;
				$resBuf .= "</div><!-- End sealset -->";//<!-- End sealset -->

				if (!_IsNull($res)) $res .= "\n";
				$res .= $resBuf;
			}
			break;
		case 3:
			break;
	}

	return $res;
}


?>
