<?php
/*
 * [���������Ω.JP �ġ���]
 * ���������Ω������Ͽ�ڡ���
 *
 * ��������2008/12/01	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../common/include.ini");


//_LogDelete();
//_LogBackup();
_Log("[/user/company/info/index.php] start.");


_Log("[/user/company/info/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/company/info/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/company/info/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/company/info/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


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
_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../../../common/temp_html/temp_base.txt';
_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


//$tempSidebarLoginFile = '../../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"��¸�ߤ����硢ɽ�����롣
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) ��������");
//} else {
//	//�����Ǥ��ʤ��ä����
//	_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) �ڼ��ԡ�");
//}

$tempSidebarUserMenuFile = '../../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_COMPANY_INFO;

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


_Log("[/user/company/info/index.php] \$_GET(�ͤ��ؤ���) = '".print_r($_GET,true)."'");

//�ѥ�᡼������������롣
$xmlName = XML_NAME_CMP;//XML�ե�����̾�����ꤹ�롣
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


		_Log("[/user/company/info/index.php] {������桼�������½���} �桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/info/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."'");


		//���¤ˤ�äơ�ɽ������桼������������¤��롣
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://����̵��

				_Log("[/user/company/info/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
				_Log("[/user/company/info/index.php] {������桼�������½���} ����ʬ�γ��������Ω����Τ�ɽ�����롣");
				_Log("[/user/company/info/index.php] {������桼�������½���} �����ID�򸡺����롣");

				$id = null;

				//��ʬ�γ��������Ω����Τ�ɽ�����롣
				//���ID�򸡺����롣
				$id = _GetRelationCompanyId($loginInfo['usr_user_id']);

				_Log("[/user/company/info/index.php] {������桼�������½���} �����ID = '".$id."'");
				break;
		}


		//�����ͤ�������롣
		$info = $_POST;
		_Log("[/user/company/info/index.php] POST = '".print_r($info,true)."'");
		//�Хå�����å�����������
		$info = _StripslashesForArray($info);
		_Log("[/user/company/info/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");


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



		_Log("[/user/company/info/index.php] {������桼�������½���} �桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/info/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."'");


		//���¤ˤ�äơ�ɽ������桼������������¤��롣
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://����̵��

				_Log("[/user/company/info/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
				_Log("[/user/company/info/index.php] {������桼�������½���} ����ʬ�γ��������Ω����Τ�ɽ�����롣");
				_Log("[/user/company/info/index.php] {������桼�������½���} �����ID�򸡺����롣");

				$id = null;
				$undeleteOnly4def = true;

				//��ʬ�γ��������Ω����Τ�ɽ�����롣
				//���ID�򸡺����롣
				$id = _GetRelationCompanyId($loginInfo['usr_user_id']);

				_Log("[/user/company/info/index.php] {������桼�������½���} �����ID = '".$id."'");

//				//���ܸ��ڡ����Ϥɤ�����
//				switch ($pId) {
//					case PAGE_ID_USER://�桼�����ڡ���
//						break;
//				}
				break;
		}



		$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);

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
//		$_SESSION[SID_USER_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/user/company/info/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/user/company/info/index.php] XML�ե�����̾ = '".$xmlName."'");
_Log("[/user/company/info/index.php] �������å�ID = '".$id."'");


//��ҥ�����ID="�������"�����ꤹ�롣
$info['update']['tbl_company']['cmp_company_type_id'] = MST_COMPANY_TYPE_ID_CMP;
//�桼��������(���������)�����ꤹ�롣��DB�����˻��Ѥ��롣
$info['update']['tbl_user'] = $loginInfo;

//��������̤��Ͽ�ΤȤ��������ꤹ�롣
//������Ǥ��
if (!isset($info['update']['tbl_company']['cmp_term_year']) || _IsNull($info['update']['tbl_company']['cmp_term_year'])) {
	$info['update']['tbl_company']['cmp_term_year'] = 10;
}
//�ƺ���Ǥ��
if (!isset($info['update']['tbl_company']['cmp_inspector_term_year']) || _IsNull($info['update']['tbl_company']['cmp_inspector_term_year'])) {
	$info['update']['tbl_company']['cmp_inspector_term_year'] = 4;
}
//����ա��嵭�ι��ܤ�ɽ���������̰ʳ��Ǥ⹹������롣���塢¾�ι��ܤ��ɲä���Ȥ�������ա�(��ȯ�Բ�ǽ����������פ��ɲä����Ȥ�����������Ƥ��ޤäƤ�����)

switch ($step) {
	case 1:
		//���������Ω����[����(���̾)]
		$xmlName = XML_NAME_CMP_NAME;

		$stepId = "cmpn_name";
		break;
	case 2:
		//���������Ω����[���ܶ⡦����ǯ��]
		$xmlName = XML_NAME_CMP_CAPITAL;

		$stepId = "cmpn_capital";
		break;
	case 3:
		//���������Ω����[��Ź�����]
		$xmlName = XML_NAME_CMP_ADDRESS;

		$stepId = "cmpn_address";
		break;
	case 4:
		//���������Ω����[���Ȥ���Ū]
		$xmlName = XML_NAME_CMP_PURPOSE;

		$stepId = "cmpn_purpose";
		break;
	case 5:
		//���������Ω����[���������Ǥ��]
		$xmlName = XML_NAME_CMP_BOARD_BASE;

		$stepId = "cmpn_board_base";
		break;
	case 6:
		//���������Ω����[������]
		$xmlName = XML_NAME_CMP_BOARD_NAME;

		$stepId = "cmpn_board_name";
		break;
	case 7:
		//���������Ω����[ȯ����]
		$xmlName = XML_NAME_CMP_PROMOTER;

		$stepId = "cmpn_promoter";
		break;
	case 8:
		//���������Ω����[�л��]
		//���л��ϡ�XML�����Υե�����ǤϤʤ���ľ�ܽ񤭽Ф���
		$xmlName = XML_NAME_CMP_PROMOTER_INVESTMENT;
		//$xmlName = null;

		$stepId = "cmpn_promoter_investment";
		break;
	case 9:
		//���������Ω����[�������Ƴ�ǧ]
		$xmlName = XML_NAME_CMP_ALL;

		$stepId = "cmpn_confirm";
		break;
	default:
		//���������Ω����[����(���̾)]
		$xmlName = XML_NAME_CMP_NAME;

		$stepId = "cmpn_name";

		$step = 1;
		break;
}
$info['condition']['_step_'] = $step;

_Log("[/user/company/info/index.php] ���ƥå�ID = '".$step."'");
_Log("[/user/company/info/index.php] XML�ե�����̾(���ƥå�ID) = '".$xmlName."'");

//���ܥ��󤬲����줿��碪�������ܤ���Τǡ�XML���ɤ߹��ޤʤ���
if ($_POST['back'] != "") $xmlName = null;

//����ͤ����ꤹ�롣
switch ($xmlName) {
	case XML_NAME_CMP_PROMOTER:
		//���������Ω����[ȯ����]
		//���_ȯ���ͥơ��֥����̤����ξ�硢���_����ơ��֥��������ͤȤ������ꤹ�롣
		if (!isset($info['update']['tbl_company_promoter'])) {
			if (_IsNull($info['update']['tbl_company_promoter'])) {
				//���_����ơ��֥��������Ѥߤξ��
				if (isset($info['update']['tbl_company_board'])) {
					if (!_IsNull($info['update']['tbl_company_board']) && is_array($info['update']['tbl_company_board'])) {
						$bufList = array();
						foreach ($info['update']['tbl_company_board'] as $tcbKey => $tblCompanyBoardInfo) {
							$bufInfo = array();
							$bufInfo['cmp_prm_family_name'] = $tblCompanyBoardInfo['cmp_bod_family_name'];					//ȯ����̾��(��) �� ���̾��(��)
							$bufInfo['cmp_prm_first_name'] = $tblCompanyBoardInfo['cmp_bod_first_name'];					//ȯ����̾��(̾) �� ���̾��(̾)
							$bufInfo['cmp_prm_family_name_kana'] = $tblCompanyBoardInfo['cmp_bod_family_name_kana'];		//ȯ����̾���եꥬ��(��) �� ���̾���եꥬ��(��)
							$bufInfo['cmp_prm_first_name_kana'] = $tblCompanyBoardInfo['cmp_bod_first_name_kana'];			//ȯ����̾���եꥬ��(̾) �� ���̾���եꥬ��(̾)
							$bufInfo['cmp_prm_zip1'] = $tblCompanyBoardInfo['cmp_bod_zip1'];								//ȯ���ͽ���(͹���ֹ�1) �� �������(͹���ֹ�1)
							$bufInfo['cmp_prm_zip2'] = $tblCompanyBoardInfo['cmp_bod_zip2'];								//ȯ���ͽ���(͹���ֹ�2) �� �������(͹���ֹ�2)
							$bufInfo['cmp_prm_pref_id'] = $tblCompanyBoardInfo['cmp_bod_pref_id'];							//ȯ���ͽ���(��ƻ�ܸ�) �� �������(��ƻ�ܸ�)
							$bufInfo['cmp_prm_address1'] = $tblCompanyBoardInfo['cmp_bod_address1'];						//ȯ���ͽ���(�Զ�Į¼) �� �������(�Զ�Į¼)
							$bufInfo['cmp_prm_address2'] = $tblCompanyBoardInfo['cmp_bod_address2'];						//ȯ���ͽ���(�嵭�ʹ�) �� �������(�嵭�ʹ�)
							$bufList[] = $bufInfo;
						}
						if (count($bufList) > 1) {
							$info['update']['tbl_company_promoter'] = $bufList;
							$message .= "���ޤ�ȯ���ͤ���Ͽ����Ƥ��ޤ���\n������ξ���򲾤�ɽ�����Ƥ���ޤ���\n�ʲ������Ƥ��ǧ������������¸���Ƥ���������";
						}
					}
				}
			}
		}
		break;
}

//�ե������Ѥ˥ޥ����ǡ��������ꤹ�롣
//ȯ�Բ�ǽ���������
$mstStockTotalNumList = _GetNumberArray(5000, 30000, 5000);
//��Ͽ��Ρ�ȯ�Բ�ǽ����������פ��ͤ��嵭����ˤ��뤫��̵�����ϡ��ɲä��롣(�����ǡ�����)
if (isset($info['update']['tbl_company']['cmp_stock_total_num']) && !_IsNull($info['update']['tbl_company']['cmp_stock_total_num'])) {
	if (!isset($mstStockTotalNumList[$info['update']['tbl_company']['cmp_stock_total_num']])) {
		$addList = array(
		'id' => $info['update']['tbl_company']['cmp_stock_total_num']
		,'name' => $info['update']['tbl_company']['cmp_stock_total_num'].' (�ڻ����ѹ���5���3�������� ����աۺ��γ��������ѹ�����ȸ����᤻�ޤ���)'
		);
		$mstStockTotalNumList[$info['update']['tbl_company']['cmp_stock_total_num']] = $addList;
	}
}

$otherList = array(
'mst_stock_total_num' => $mstStockTotalNumList
);

$xmlList = null;
if (!_IsNull($xmlName)) {
	//XML���ɤ߹��ࡣ
	$xmlFile = "../../../common/form_xml/".$xmlName.".xml";
	_Log("[/user/company/info/index.php] XML�ե����� = '".$xmlFile."'");
	$xmlList = _GetXml($xmlFile, $otherList);

	_Log("[/user/company/info/index.php] XML�ե��������� = '".print_r($xmlList,true)."'");

	switch ($xmlName) {
		case XML_NAME_CMP_ALL:
			//���������Ω����[�������Ƴ�ǧ]

			//���Ƥ�XML���ɤ߹��ࡣ

			//���������Ω����[����(���̾)]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_NAME.".xml";
			_Log("[/user/company/info/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_name'] = $bufXmlList['tbl_company'];

			//���������Ω����[���ܶ⡦����ǯ��]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_CAPITAL.".xml";
			_Log("[/user/company/info/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile, $otherList);
			$xmlList['tbl_company_capital'] = $bufXmlList['tbl_company'];

			//���������Ω����[��Ź�����]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_ADDRESS.".xml";
			_Log("[/user/company/info/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_address'] = $bufXmlList['tbl_company'];

			//���������Ω����[���Ȥ���Ū]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_PURPOSE.".xml";
			_Log("[/user/company/info/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_purpose'] = $bufXmlList['tbl_company_purpose'];

			//���������Ω����[���������Ǥ��]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_BOARD_BASE.".xml";
			_Log("[/user/company/info/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_board_base'] = $bufXmlList['tbl_company'];

			//���������Ω����[������]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_BOARD_NAME.".xml";
			_Log("[/user/company/info/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_board'] = $bufXmlList['tbl_company_board'];

			//���������Ω����[ȯ����]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_PROMOTER.".xml";
			_Log("[/user/company/info/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_promoter'] = $bufXmlList['tbl_company_promoter'];

			//���������Ω����[�л��]
			$bufXmlFile = "../../../common/form_xml/".XML_NAME_CMP_PROMOTER_INVESTMENT.".xml";
			_Log("[/user/company/info/index.php] XML�ե����� = '".$bufXmlFile."'");
			$bufXmlList = _GetXml($bufXmlFile);
			$xmlList['tbl_company_promoter_investment'] = $bufXmlList['tbl_company_promoter_investment'];


			$info['update']['tbl_company_name'] = $info['update']['tbl_company'];
			$info['update']['tbl_company_capital'] = $info['update']['tbl_company'];
			$info['update']['tbl_company_address'] = $info['update']['tbl_company'];
			$info['update']['tbl_company_board_base'] = $info['update']['tbl_company'];


			_Log("[/user/company/info/index.php] XML�ե���������(��XML�ޡ�����) = '".print_r($xmlList,true)."'");
			_Log("[/user/company/info/index.php] ���������Ω����(��XML�ޡ�����) = '".print_r($info,true)."'");

			$mode = 2;

			break;
	}
}

//��¸�ܥ��󡢼��إܥ��󤬲����줿���
if ($_POST['go'] != "" || $_POST['next'] != "") {
	//�����ͥ����å�
	$message .= _CheackInputAll($xmlList, $info);

	switch ($xmlName) {
		case XML_NAME_CMP_PURPOSE:
			//���������Ω����[���Ȥ���Ū]
			$message .= _CheackInput4CompanyPurpose($xmlList, $info);
			break;
		case XML_NAME_CMP_BOARD_NAME;
			//���������Ω����[������]
			$message .= _CheackInput4CompanyBoard($xmlList, $info);
			break;
		case XML_NAME_CMP_PROMOTER:
			//���������Ω����[ȯ����]
			$message .= _CheackInput4CompanyPromoter($xmlList, $info);
			break;
		case XML_NAME_CMP_PROMOTER_INVESTMENT:
			//���������Ω����[�л��]
			$message .= _CheackInput4CompanyPromoterInvestment($xmlList, $info);

			//�����Υ����å��򤹤롣
			$stockNumErrorFlag = false;
			$bufTabindex = null;
			$buf = _CreateTableInput4CompanyPromoterInvestment($mode, $xmlList, $info, $bufTabindex, $stockNumErrorFlag);
			if ($stockNumErrorFlag) {
				$message .= "ȯ�Գ����Ȱ�����������äƤ��ޤ���\n";
			}
			break;
		default:
			break;
	}

	if (_IsNull($message)) {
		//���顼��̵����硢��Ͽ���롣

		//��������Ͽ�򤹤롣(��$info�Ϻǿ�����˹�������롣)
		$res = _UpdateInfo($info);
		if ($res === false) {
			//���顼��ͭ����
			$message = "��Ͽ�˼��Ԥ��ޤ�����";
			$errorFlag = true;
		} else {

			//��å����������ꤹ�롣
			$message .= "��¸���ޤ�����";

			//������Ͽ�ξ�硢id�����֤����Τǡ����ꤹ�롣
			$id = $info['condition']['_id_'];


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
_Log("[/user/company/info/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/user/company/info/index.php] mode = '".$mode."'");






//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "../../..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../../img/maincontent/pt_user_company_info.jpg\" title=\"\" alt=\"���������Ω������Ͽ\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

//���֥�˥塼�����ꤹ�롣
$maincontent .= "<ul id=\"cmpn\">";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_name\">";
$maincontent .= "<a href=\"?step=1".$addHref."\">����<br />(���̾)</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_capital\">";
$maincontent .= "<a href=\"?step=2".$addHref."\">���ܶ�<br />����ǯ��</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_address\">";
$maincontent .= "<a href=\"?step=3".$addHref."\">��Ź<br />�����</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_purpose\">";
$maincontent .= "<a href=\"?step=4".$addHref."\">���Ȥ�<br />��Ū</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_board_base\">";
$maincontent .= "<a href=\"?step=5".$addHref."\">�������<br />Ǥ��</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_board_name\">";
$maincontent .= "<a href=\"?step=6".$addHref."\">������</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_promoter\">";
$maincontent .= "<a href=\"?step=7".$addHref."\">ȯ����</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_promoter_investment\">";
$maincontent .= "<a href=\"?step=8".$addHref."\">�л��</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "<li id=\"cmpn_confirm\">";
$maincontent .= "<a href=\"?step=9".$addHref."\">��������<br />��ǧ</a>";
$maincontent .= "</li>";
$maincontent .= "\n";
$maincontent .= "</ul>";
$maincontent .= "\n";
$maincontent .= "<div id=\"cmpn_exp\">";
$maincontent .= "\n";
$maincontent .= "����˥塼����ڡ������ư�����硢�������Ƥ���¸����ޤ���";
$maincontent .= "\n";
$maincontent .= "</div>";
$maincontent .= "\n";

switch ($xmlName) {
	case XML_NAME_CMP_ALL:
		//���������Ω����[�������Ƴ�ǧ]
		$maincontent .= "<!--{_message_}-->";
		$maincontent .= "\n";
		break;
}

$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);


//������ץȤ����ꤹ�롣
$script = null;

$addStyle = null;

switch ($xmlName) {
	case XML_NAME_CMP_CAPITAL:
		//���������Ω����[���ܶ⡦����ǯ��]

		//������ץȤ����ꤹ�롣
		$script .= "<script type=\"text/javascript\">";
		$script .= "\n";
		$script .= "<!--";
		$script .= "\n";
		$script .= "window.addEvent('domready', function(){";
		$script .= "\n";

		$script .= "$$('#cmp_business_start_month','#cmp_found_month').addEvent('change', function(e) {";
		$script .= "\n";
		$script .= "calculateMonth();";
		$script .= "\n";
		$script .= "});";
		$script .= "\n";
		$script .= "calculateMonth();";
		$script .= "\n";

		if (!_IsNull(FOUND_DAY_DEADLINE)) {
			$script .= "$$('#cmp_found_year','#cmp_found_month','#cmp_found_day').addEvent('change', function(e) {";
			$script .= "\n";
			$script .= "checkFoundDate();";
			$script .= "\n";
			$script .= "});";
			$script .= "\n";
			$script .= "checkFoundDate();";
			$script .= "\n";
		}

		$script .= "});";
		$script .= "\n";
		$script .= "\n";

		$script .= "function calculateMonth() {";
		$script .= "\n";
		$script .= "var startMonth = $('cmp_business_start_month').value;";
		$script .= "\n";
		$script .= "var foundMonth = $('cmp_found_month').value;";
		$script .= "\n";
		//$script .= "alert('startMonth='+startMonth+'/foundMonth='+foundMonth);";
		//$script .= "\n";
		$script .= "var res = '��XX����';";
		$script .= "\n";
		$script .= "var bgColor = '#ff0';";
		$script .= "\n";
		$script .= "var resMessage = '';";
		$script .= "\n";
		$script .= "if (startMonth != '' && foundMonth != '') {";
		$script .= "\n";
		$script .= "var diff = 12 - (foundMonth - startMonth);";
		$script .= "\n";
		$script .= "if (diff > 12) diff -= 12;";
		$script .= "\n";
		$script .= "res = '��'+diff+'����';";
		$script .= "\n";
		$script .= "if (diff == 1) {";
		$script .= "\n";
		$script .= "bgColor = '#f00';";
		$script .= "\n";
		$script .= "resMessage = '<br /><br />�ǽ�η軻�ޤ�1������ڤäƤ��ޤ���<br />��Ωͽ���������ˤ��뤫������ǯ�٤γ�������1������(�᤯)�ˤ��Ƥ���������<br />�����򤷤���Ƿ軻�������ꤷ�Ƥ�����Ϥ��Τޤޤ��ʤߤ���������';";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";
//		$script .= "$('res_month_1').set('text', res);";//ie6��text���Ȥ��ʤ���
		$script .= "$('res_month_1').set('html', res);";
		$script .= "\n";
		$script .= "$('res_month_1').setStyle('background-color', bgColor);";
		$script .= "\n";
//		$script .= "$('res_month_2').set('text', res);";
		$script .= "$('res_month_2').set('html', res);";
		$script .= "\n";
		$script .= "$('res_month_2').setStyle('background-color', bgColor);";
		$script .= "\n";
		$script .= "$('res_month_advice_1').set('html', resMessage);";
		$script .= "\n";
		$script .= "$('res_month_advice_2').set('html', resMessage);";
		$script .= "\n";
		$script .= "}";
		$script .= "\n";

		if (!_IsNull(FOUND_DAY_DEADLINE)) {
			//������������롣
			$deadlineTime = mktime(0, 0, 0, date('n'), date('j') + FOUND_DAY_DEADLINE + 1, date('Y'));
			$deadlineYmd = date('Ymd', $deadlineTime);
			$deadlineYmdMessage = date('Yǯm��d��', $deadlineTime);

			$script .= "function checkFoundDate() {";
			$script .= "\n";
			$script .= "var foundDateDeadline = ".$deadlineYmd.";";
			$script .= "\n";
			$script .= "var foundYear = $('cmp_found_year').value;";
			$script .= "\n";
			$script .= "var foundMonth = $('cmp_found_month').value;";
			$script .= "\n";
			$script .= "var foundDay = $('cmp_found_day').value;";
			$script .= "\n";
			$script .= "var foundDate = '';";
			$script .= "\n";
			$script .= "var resMessage = '';";
			$script .= "\n";
			$script .= "var resMessageDeadline = '(".$deadlineYmdMessage."�ʹߤ����ꤷ�Ƥ���������)';";
			$script .= "\n";
			$script .= "if (foundYear != '' && foundMonth != '' && foundDay != '') {";
			$script .= "\n";
			$script .= "foundMonth = (foundMonth.length < 2 ? '0'+foundMonth : foundMonth);";
			$script .= "\n";
			$script .= "foundDay = (foundDay.length < 2 ? '0'+foundDay : foundDay);";
			$script .= "\n";
			$script .= "foundDate = foundYear + foundMonth + foundDay;";
			$script .= "\n";
			$script .= "foundDate = Number(foundDate);";
			$script .= "\n";
			$script .= "if (foundDate < foundDateDeadline) {";
			$script .= "\n";
			$script .= "resMessage = '��Ωǯ�����ϡ��������".FOUND_DAY_DEADLINE."����ʹߤ����դ����Ϥ��Ƥ���������<br />(������Ω�Ѥߤξ��ϡ����Τޤޤ��ʤߤ���������)<br /><br />';";
			$script .= "\n";
			$script .= "}";
			$script .= "\n";
			$script .= "}";
			$script .= "\n";
			$script .= "$('res_found_date').set('html', resMessageDeadline);";
			$script .= "\n";
			$script .= "$('res_found_date_advice').set('html', resMessage);";
			$script .= "\n";
			$script .= "}";
			$script .= "\n";
		}

		$script .= "//-->";
		$script .= "\n";
		$script .= "</script>";
		$script .= "\n";


		break;

	case XML_NAME_CMP_BOARD_BASE:
		//���������Ω����[���������Ǥ��]

		//������ץȤ����ꤹ�롣
		$script .= "<script type=\"text/javascript\">";
		$script .= "\n";
		$script .= "<!--";
		$script .= "\n";
		$script .= "window.addEvent('domready', function(){";
		$script .= "\n";

//(����2011/10/25)��1�������=�ּ����������֤��롡������3�Ͱʾ�ȴƺ���1�ͤ�ɬ�פǤ����פǡ�3�Ͱʾ�פȤʤä��Τǡ��Ϳ�������Ǥ���褦�ˤ��롣
//		$script .= "$$('input.board_formation').addEvent('click', function(e) {";
//		$script .= "\n";
//		//$script .= "alert('name='+this.name+'/value='+this.value+'/checked='+this.checked);";
//		//$script .= "\n";
//		$script .= "showNode('director_num', (this.value == '".MST_BOARD_FORMATION_ID_1_10."'));";
//		$script .= "\n";
//		$script .= "});";
//		$script .= "\n";
//		$script .= "\n";
//
//		$script .= "var value = '';";
//		$script .= "\n";
//
//		$script .= "$$('input.board_formation').each(function(el){";
//		$script .= "\n";
//
//		$script .= "if (el.checked) {";
//		$script .= "\n";
//		$script .= "value = el.value;";
//		$script .= "\n";
//		$script .= "}";
//		$script .= "\n";
//
//		//$script .= "alert('name='+el.name+'/value='+el.value+'/checked='+el.checked);";
//		//$script .= "\n";
//		$script .= "});";
//		$script .= "\n";
//
//		$script .= "showNode('director_num', (value == '".MST_BOARD_FORMATION_ID_1_10."'));";
//		$script .= "\n";
		$script .= "showNode('inspector_num', false);";//�ƺ���Ϳ��ϡ���ɽ�����ץ����Ǹ�������ꤹ�롣����ǧ���̤Ǥ�ɽ�����뤿�ᡢ¸�ߤ��Ƥ��롣
		$script .= "\n";

		$script .= "});";
		$script .= "\n";

		$script .= "//-->";
		$script .= "\n";
		$script .= "</script>";
		$script .= "\n";


		break;

	case XML_NAME_CMP_BOARD_NAME:
		//���������Ω����[������]

		$requiredMessage = null;
		if (_IsNull($info['update']['tbl_company_board']) || !is_array($info['update']['tbl_company_board'])) {
			$requiredMessage .= "�ּ�����פ���Ͽ������ϡ�����������פ������Ͽ���Ƥ��������������������Ǥ���ץڡ�������Ͽ���Ƥ���������\n";
		}
		$buf = null;
		if (!_IsNull($requiredMessage)) {
			$buf .= "<div class=\"requiredMessage\">";
			$buf .= nl2br($requiredMessage);
			$buf .= "</div>";
			$buf .= "\n";
		}
		$maincontent = str_replace('{form_info_cmp_board_name}', $buf, $maincontent);
		break;
	case XML_NAME_CMP_PROMOTER_INVESTMENT:
		//���������Ω����[�л��]
		$buf = _CreateTableInput4CompanyPromoterInvestment($mode, $xmlList, $info, $tabindex);
		$maincontent = str_replace('{form_info_cmp_promoter_investment}', $buf, $maincontent);
		break;
	case XML_NAME_CMP_ALL:
		//���������Ω����[�������Ƴ�ǧ]

		$allErrorFlag = false;

		//���������Ω����[������]
		$requiredMessage = null;
		if (_IsNull($info['update']['tbl_company_board']) || !is_array($info['update']['tbl_company_board'])) {
			$requiredMessage .= "�ּ�����פ���Ͽ������ϡ�����������פ������Ͽ���Ƥ��������������������Ǥ���ץڡ�������Ͽ���Ƥ���������\n";
		}
		$buf = null;
		if (!_IsNull($requiredMessage)) {
			$allErrorFlag = true;
			$buf .= "<div class=\"requiredMessage\">";
			$buf .= nl2br($requiredMessage);
			$buf .= "</div>";
			$buf .= "\n";
		}
		$maincontent = str_replace('{form_info_cmp_board_name}', $buf, $maincontent);

		//���������Ω����[�л��]
		$buf = _CreateTableInput4CompanyPromoterInvestment($mode, $xmlList, $info, $tabindex);
		$maincontent = str_replace('{form_info_cmp_promoter_investment}', $buf, $maincontent);
		if (preg_match('/class=\\"requiredMessage\\"/', $buf)) {
			$allErrorFlag = true;
		}

		foreach ($xmlList as $xKey => $xmlInfo) {
			$repKey = null;
			switch ($xKey) {
				case 'tbl_company_name';
					$repKey = '<!--{_form_info_cmp_name_}-->';
					break;
				case 'tbl_company_capital';
					$repKey = '<!--{_form_info_cmp_capital_}-->';
					break;
				case 'tbl_company_address';
					$repKey = '<!--{_form_info_cmp_address_}-->';
					break;
				case 'tbl_company_purpose';
					$repKey = '<!--{_form_info_cmp_purpose_}-->';
					break;
				case 'tbl_company_board_base';
					$repKey = '<!--{_form_info_cmp_board_base_}-->';
					break;
				case 'tbl_company_board';
					$repKey = '<!--{_form_info_cmp_board_name_}-->';
					break;
				case 'tbl_company_promoter';
					$repKey = '<!--{_form_info_cmp_promoter_}-->';
					break;
				case 'tbl_company_promoter_investment';
					$repKey = '<!--{_form_info_cmp_promoter_investment_}-->';
					break;
				default:
					continue 2;
			}

			$bufXmlList = array();
			$bufXmlList[$xKey] = $xmlInfo;
			//�����ͥ����å�
			$bufMessage = null;
			$bufMessage .= _CheackInputAll($bufXmlList, $info);
//			switch ($xKey) {
//				case 'tbl_company_purpose':
//					//���������Ω����[���Ȥ���Ū]
//					$bufMessage .= _CheackInput4CompanyPurpose($bufXmlList, $info);
//					break;
//				case 'tbl_company_promoter':
//					//���������Ω����[ȯ����]
//					$bufMessage .= _CheackInput4CompanyPromoter($bufXmlList, $info);
//					break;
//				case 'tbl_company_promoter_investment':
//					//���������Ω����[�л��]
//					$bufMessage .= _CheackInput4CompanyPromoterInvestment($bufXmlList, $info);
//					break;
//				default:
//					break;
//			}
			if (!_IsNull($bufMessage)) {
				$allErrorFlag = true;
				$buf = null;
				$buf .= "<div class=\"requiredMessage\">";
				$buf .= "ɬ�ܹ��ܤ�̤���Ϥ�����ޤ���";//.$bufMessage;
				$buf .= "</div>";
				$buf .= "\n";
				$maincontent = str_replace($repKey, $buf, $maincontent);
			}
		}

		$buf = null;
		if ($allErrorFlag) {
			$buf .= "<div class=\"message errorMessage\">";
			$buf .= "\n";
			$buf .= "�����Ϥ��ޤ��Ѥ�Ǥ��ʤ����ܤ�����ޤ���<br />�������Ƥ򤴳�ǧ����������";
			$buf .= "\n";
			$buf .= "</div>";
		} else {
			$buf .= "<div class=\"message\">";
			$buf .= "\n";
			$buf .= "�������Ƥ򤴳�ǧ����������";
//			$buf .= "<br />";
//			$buf .= "�������Ƥ��������С����κ�Ȥˤ��ʤߤ���������";
//			$buf .= "<br />";
//			$buf .= "���κ��";
//			$buf .= "&nbsp;&nbsp;&gt;&gt;&gt;&nbsp;&nbsp;";
//			$buf .= "<a href=\"../../../seal/\" class=\"guide_link\">�ְ��պ���(�°�����԰���)��</a>";
//			$buf .= "&nbsp;&nbsp;&gt;&gt;&gt;&nbsp;&nbsp;";
//			$buf .= "<a href=\"../article/\" class=\"guide_link\">���괾ǧ�� �����</a>";
//			$buf .= "&nbsp;&nbsp;&gt;&gt;&gt;&nbsp;&nbsp;";
//			$buf .= "��";
			$buf .= "\n";
			$buf .= "</div>";
		}
		$maincontent = str_replace('<!--{_message_}-->', $buf, $maincontent);


		//��ǧ�Ѳ��̤Ǥ���ɽ���ˤ�����ܤ���ɽ���ˤ��롣�ֺ������׹��ܤʤɡ�
		$addStyle .= ".show_confirm {display: none;}";

		break;
	default:
		break;
}

$script .= "<style type=\"text/css\">";
$script .= "\n";
$script .= "<!--";
$script .= "\n";
$script .= "ul#cmpn li#".$stepId." a:link";
$script .= ",ul#cmpn li#".$stepId." a:visited";
$script .= "\n";
$script .= "{height: 32px;color: #3176af;border-bottom: 3px solid #76b0df;}";
$script .= "\n";
$script .= $addStyle;
$script .= "\n";
$script .= "-->";
$script .= "\n";
$script .= "</style>";
$script .= "\n";


//������ʸ�Ϥ����ꤹ�롣
$tempExpFile = null;
switch ($xmlName) {
	case XML_NAME_CMP_NAME:
		//���������Ω����[����(���̾)]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_01.txt';
		break;
	case XML_NAME_CMP_CAPITAL:
		//���������Ω����[���ܶ⡦����ǯ��]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_02.txt';
		break;
	case XML_NAME_CMP_PURPOSE:
		//���������Ω����[���Ȥ���Ū]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_03.txt';
		break;
	case XML_NAME_CMP_BOARD_NAME:
		//���������Ω����[������]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_04.txt';
		break;
	case XML_NAME_CMP_PROMOTER:
		//���������Ω����[ȯ����]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_05.txt';
		break;
	case XML_NAME_CMP_PROMOTER_INVESTMENT:
		//���������Ω����[�л��]
		$tempExpFile = '../../../common/temp_html/temp_maincontent_company_exp_06.txt';
		break;
}
_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (������ʸ��) HTML�ƥ�ץ졼�ȥե����� = '".$tempExpFile."'");
$htmlExp = null;
if (!_IsNull($tempExpFile)) {
	$htmlExp = @file_get_contents($tempExpFile);
	//"HTML"��¸�ߤ����硢ɽ�����롣
	if ($htmlExp !== false && !_IsNull($htmlExp)) {
		_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (������ʸ��) ��������");
	} else {
		//�����Ǥ��ʤ��ä����
		_Log("[/user/company/info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (������ʸ��) �ڼ��ԡ�");
		$htmlExp = null;
	}
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
_SetBreadcrumbs(PAGE_DIR_COMPANY_INFO, '', PAGE_TITLE_COMPANY_INFO, 4);
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


_Log("[/user/company/info/index.php] end.");
echo $html;



































/**
 * ���_ȯ����_�л�ۥơ��֥������
 * �����ͤΥ����å��򤹤롣
 *
 * @param	array	$xmlList		XML���ɤ߹��������
 * @param	array	$info			���Ϥ����ͤ���Ǽ����Ƥ�������
 * @return	���顼��å�����
 * @access  public
 * @since
 */
function _CheackInput4CompanyPromoterInvestment($xmlList, &$info) {

	_Log("[_CheackInput4CompanyPromoterInvestment] start.");

	_Log("[_CheackInput4CompanyPromoterInvestment] (param) XML���ɤ߹�������� = '".print_r($xmlList,true)."'");
	_Log("[_CheackInput4CompanyPromoterInvestment] (param) ���Ϥ����ͤ���Ǽ����Ƥ������� = '".print_r($info,true)."'");

	$res = null;
	if (isset($info['update']['tbl_company_promoter_investment'])) {
		if (is_array($info['update']['tbl_company_promoter_investment'])) {

			//�ơ��֥�Υե�����ɾ����������롣��maxlength�˻��Ѥ��롣
			$colInfo = _DB_GetColumnsInfo('tbl_company_promoter_investment');

			//�л񥿥��ץޥ���
			$condition = null;
			$order = null;
			$order .= "lpad(show_order,10,'0')";	//�����Ⱦ��=ɽ����ξ���
			$order .= ",id";						//�����Ⱦ��=ID�ξ���
			$mstInvestmentTypeList = _DB_GetList('mst_investment_type', $condition, false, $order, 'del_flag', 'id');

			foreach ($info['update']['tbl_company_promoter_investment'] as $cId => $companyList) {
				foreach ($companyList as $pNo => $promoterList) {
					foreach ($promoterList as $tId => $typeList) {

						$investmentTypeName = $mstInvestmentTypeList[$tId]['name'];

						$messageName1 = null;
						$messageName1 .= "�л��".$pNo."���ܡ���".$investmentTypeName."�׽л�� ";

						$count = 0;
						$delCount = 0;
						foreach ($typeList['investment_info'] as $iKey => $investmentInfo) {

							$count++;

							$messageName2 = null;
							$messageName2 .= $messageName1;
							if (count($typeList['investment_info']) > 1) {
								$messageName2 .= "".$count."���ܤ�";
							} else {
								$messageName2 .= "";
							}

							//����ե饰�������å�ON�ξ�硢�������Τǥ��顼�����å����Ƚ����롣
							if (isset($investmentInfo['cmp_prm_inv_del_flag']) && $investmentInfo['cmp_prm_inv_del_flag'] == DELETE_FLAG_YES) {
								$delCount++;
								continue;
							}

							foreach ($investmentInfo as $name => $value) {
								//����̾��������롣
								$label = $xmlList['tbl_company_promoter_investment']['item_label'][$name];

								//��Ⱦ�ѡ�-�����ѡפ��Ѵ����롣
								if (!_IsNull($colInfo)) {
									switch ($colInfo[$name]['TypeOnly']) {
										case 'int':
										case 'bigint':
										case 'double':
											//�����ѡױѿ������Ⱦ�ѡפ��Ѵ����롣
											$value = mb_convert_kana($value, 'a');
											break;
										default:
											//��Ⱦ�ѡױѿ���������ѡפ��Ѵ����롣'A'
											//��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣'K'
											//�����դ���ʸ�����ʸ�����Ѵ����롣'V'
											//��Ⱦ�ѡץ��ڡ���������ѡפ��Ѵ����롣'S'
											$value = mb_convert_kana($value, 'AKVS');
											//�Ѵ��Ǥ��Ƥʤ�ʸ�����Ѵ����롣(�Ǹ�ΤϡֽᎵ�������)
											$searchList = array( '"', '\'', '\\', chr(hexdec('7E')));
											$replaceList = array('��', '��', '��', chr(hexdec('A1')).chr(hexdec('C1')));
											$value = str_replace($searchList, $replaceList, $value);
											break;
									}
									//�Ѵ������ͤ��᤹��
									$info['update']['tbl_company_promoter_investment'][$cId][$pNo][$tId]['investment_info'][$iKey][$name] = $value;
								}

								switch ($name) {
									case 'cmp_prm_inv_stock_num':
									case 'cmp_prm_inv_in_kind':
										//ɬ�ܥ����å�
										if (_IsNull($value)) {
//											$res .= "�л��".$pNo."���ܤ�".$investmentTypeName."��".$label."".$count."���ܤ����Ϥ��Ƥ���������\n";
											$res .= $messageName2.$label."�����Ϥ��Ƥ���������\n";
										}
										break;
								}

								//ʸ����Ĺ�����å�
								//�ơ��֥뤬¸�ߤ����硢�ե�����ɤΥ����������ꤹ�롣
								if (!_IsNull($colInfo)) {
									$maxlength = null;
									if (isset($colInfo[$name]['Size']) && !_IsNull($colInfo[$name]['Size'])) {
										$maxlength = $colInfo[$name]['Size'];
									}
									if (!_IsNull($maxlength)) {
										if (_IsMaxLength($value, $maxlength)) {
//											$res .= "�л��".$pNo."���ܤ�".$investmentTypeName."��".$label."".$count."���ܤϡ�".$maxlength."ʸ����������Ϥ��Ƥ���������(����ʸ����2ʸ���Ȥ��ư��äƤ��ޤ���)\n";
											$res .= $messageName2.$label."�ϡ�".$maxlength."ʸ����������Ϥ��Ƥ���������(����ʸ����2ʸ���Ȥ��ư��äƤ��ޤ���)\n";
										}
									}
								}

								//Ⱦ�ѿ��������å�
								if (!_IsNull($colInfo)) {
									switch ($colInfo[$name]['TypeOnly']) {
										case 'int':
										case 'bigint':
											//Ⱦ�ѿ����ܥޥ��ʥ�(-)�����å�
											if (!_IsHalfSizeNumericMinus($value)) {
//												$res .= "�л��".$pNo."���ܤ�".$investmentTypeName."��".$label."".$count."���ܤϡ�Ⱦ�ѿ���(����)�����Ϥ��Ƥ���������\n";
												$res .= $messageName2.$label."�ϡ�Ⱦ�ѿ���(����)�����Ϥ��Ƥ���������\n";
											}
											break;
										case 'double':
											//Ⱦ�ѿ����ܥɥå�(.)�ܥޥ��ʥ�(-)�����å�
											if (!_IsHalfSizeNumericDotMinus($value)) {
//												$res .= "�л��".$pNo."���ܤ�".$investmentTypeName."��".$label."".$count."���ܤϡ�Ⱦ�ѿ���(�¿�)�����Ϥ��Ƥ���������\n";
												$res .= $messageName2.$label."�ϡ�Ⱦ�ѿ���(�¿�)�����Ϥ��Ƥ���������\n";
											}
											break;
									}
								}
							}
						}

						if ($count == $delCount) {
							$res .= $messageName1;
							$res .= "".$xmlList['tbl_company_promoter_investment']['item_label']['cmp_prm_inv_stock_num']."��";
							$res .= "".$xmlList['tbl_company_promoter_investment']['item_label']['cmp_prm_inv_in_kind']."";
							$res .= "��1�Ĥ����Ϥ��Ƥ���������";
							$res .= "\n";
						}
					}
				}
			}
		}
	}

	_Log("[_CheackInput4CompanyPromoterInvestment] ��� = '".$res."'");
	_Log("[_CheackInput4CompanyPromoterInvestment] end.");

	return $res;
}

/**
 * ���_��Ū�ơ��֥������
 * �����ͤΥ����å��򤹤롣
 *
 * @param	array	$xmlList		XML���ɤ߹��������
 * @param	array	$info			���Ϥ����ͤ���Ǽ����Ƥ�������
 * @return	���顼��å�����
 * @access  public
 * @since
 */
function _CheackInput4CompanyPurpose($xmlList, $info) {

	_Log("[_CheackInput4CompanyPurpose] start.");

	_Log("[_CheackInput4CompanyPurpose] (param) XML���ɤ߹�������� = '".print_r($xmlList,true)."'");
	_Log("[_CheackInput4CompanyPurpose] (param) ���Ϥ����ͤ���Ǽ����Ƥ������� = '".print_r($info,true)."'");

	$res = null;
	if (isset($info['update']['tbl_company_purpose']['purpose_info'])) {
		if (is_array($info['update']['tbl_company_purpose']['purpose_info'])) {

			$count = 0;
			$delCount = 0;
			foreach ($info['update']['tbl_company_purpose']['purpose_info'] as $pKey => $purposeInfo) {
				$count++;
				//����ե饰�������å�ON�ξ�硢�������Τǥ��顼�����å����Ƚ����롣
				if (isset($purposeInfo['cmp_pps_del_flag']) && $purposeInfo['cmp_pps_del_flag'] == DELETE_FLAG_YES) {
					$delCount++;
					continue;
				}
			}
			if ($count == $delCount) {
				$res .= "��Ū��1�Ĥ����Ϥ��Ƥ���������";
				$res .= "\n";
			}
		}
	}


	_Log("[_CheackInput4CompanyPurpose] ��� = '".$res."'");
	_Log("[_CheackInput4CompanyPurpose] end.");

	return $res;
}

/**
 * ���_ȯ���ͥơ��֥������
 * �����ͤΥ����å��򤹤롣
 *
 * @param	array	$xmlList		XML���ɤ߹��������
 * @param	array	$info			���Ϥ����ͤ���Ǽ����Ƥ�������
 * @return	���顼��å�����
 * @access  public
 * @since
 */
function _CheackInput4CompanyPromoter($xmlList, $info) {

	_Log("[_CheackInput4CompanyPromoter] start.");

	_Log("[_CheackInput4CompanyPromoter] (param) XML���ɤ߹�������� = '".print_r($xmlList,true)."'");
	_Log("[_CheackInput4CompanyPromoter] (param) ���Ϥ����ͤ���Ǽ����Ƥ������� = '".print_r($info,true)."'");

	$res = null;
	if (isset($info['update']['tbl_company_promoter'])) {
		if (is_array($info['update']['tbl_company_promoter'])) {

			$count = 0;
			$delCount = 0;
			foreach ($info['update']['tbl_company_promoter'] as $tcpKey => $tblCompanyPromoterInfo) {
				$count++;
				//����ե饰�������å�ON�ξ�硢�������Τǥ��顼�����å����Ƚ����롣
				if (isset($tblCompanyPromoterInfo['cmp_prm_del_flag']) && $tblCompanyPromoterInfo['cmp_prm_del_flag'] == DELETE_FLAG_YES) {
					$delCount++;
					continue;
				}
			}
			if ($count == $delCount) {
				$res .= "ȯ����(�л��)��1�ͤ����Ϥ��Ƥ���������";
				$res .= "\n";
			}
		}
	}


	_Log("[_CheackInput4CompanyPromoter] ��� = '".$res."'");
	_Log("[_CheackInput4CompanyPromoter] end.");

	return $res;
}

/**
 * ���_����ơ��֥������
 * �����ͤΥ����å��򤹤롣
 *
 * @param	array	$xmlList		XML���ɤ߹��������
 * @param	array	$info			���Ϥ����ͤ���Ǽ����Ƥ�������
 * @return	���顼��å�����
 * @access  public
 * @since
 */
function _CheackInput4CompanyBoard($xmlList, $info) {

	_Log("[_CheackInput4CompanyBoard] start.");

	_Log("[_CheackInput4CompanyBoard] (param) XML���ɤ߹�������� = '".print_r($xmlList,true)."'");
	_Log("[_CheackInput4CompanyBoard] (param) ���Ϥ����ͤ���Ǽ����Ƥ������� = '".print_r($info,true)."'");

	$res = null;
	if (isset($info['update']['tbl_company_board'])) {
		if (is_array($info['update']['tbl_company_board'])) {

			$condition = array();
			$condition['cmp_company_id'] = $info['condition']['_id_'];//���ID
			$undeleteOnly = false;
			$tblCompanyInfo = _DB_GetInfo('tbl_company', $condition, $undeleteOnly, 'cmp_del_flag');
			if (!_IsNull($tblCompanyInfo)) {
				$numList = array();
				foreach ($info['update']['tbl_company_board'] as $tcbKey => $tblCompanyBoardInfo) {
					//��ID
					if (isset($tblCompanyBoardInfo['cmp_bod_post_id']) && !_IsNull($tblCompanyBoardInfo['cmp_bod_post_id'])) {
						if (isset($numList[$tblCompanyBoardInfo['cmp_bod_post_id']])) {
							$numList[$tblCompanyBoardInfo['cmp_bod_post_id']]++;
						} else {
							$numList[$tblCompanyBoardInfo['cmp_bod_post_id']] = 1;
						}
					}
				}

				_Log("[_CheackInput4CompanyBoard] �򿦿Ϳ� = '".print_r($numList,true)."'");
				//��ɽ������
				$repDirectorNum = 0;
				if (isset($numList[MST_POST_ID_REP_DIRECTOR])) $repDirectorNum = $numList[MST_POST_ID_REP_DIRECTOR];
				//������
				$directorNum = 0;
				if (isset($numList[MST_POST_ID_DIRECTOR])) $directorNum = $numList[MST_POST_ID_DIRECTOR];
				//�ƺ���
				$inspectorNum = 0;
				if (isset($numList[MST_POST_ID_INSPECTOR])) $inspectorNum = $numList[MST_POST_ID_INSPECTOR];

				_Log("[_CheackInput4CompanyBoard] (������)��ɽ������ = '".$repDirectorNum."'");
				_Log("[_CheackInput4CompanyBoard] (������)������ = '".$directorNum."'");
				_Log("[_CheackInput4CompanyBoard] (������)�ƺ��� = '".$inspectorNum."'");

				_Log("[_CheackInput4CompanyBoard] (������)������ = '".$tblCompanyInfo['cmp_director_num']."'");
				_Log("[_CheackInput4CompanyBoard] (������)�ƺ��� = '".$tblCompanyInfo['cmp_inspector_num']."'");
				
				if ($repDirectorNum == 0) {
					$res .= "��ɽ�������1�ͤ����Ϥ��Ƥ���������";
					$res .= "\n";
				}
				if (!_IsNull($tblCompanyInfo['cmp_director_num'])) {
					if ($tblCompanyInfo['cmp_director_num'] != ($repDirectorNum + $directorNum)) {
						$res .= "��ɽ������ȼ�����Ϲ��".$tblCompanyInfo['cmp_director_num']."�ͤˤ��Ƥ���������";
						$res .= "\n";
					}
				}
				if (!_IsNull($tblCompanyInfo['cmp_inspector_num'])) {
					if ($tblCompanyInfo['cmp_inspector_num'] != $inspectorNum) {
						$res .= "�ƺ���Ϲ��".$tblCompanyInfo['cmp_inspector_num']."�ͤˤ��Ƥ���������";
						$res .= "\n";
					}
				}
			}
		}
	}

	_Log("[_CheackInput4CompanyBoard] ��� = '".$res."'");
	_Log("[_CheackInput4CompanyBoard] end.");

	return $res;
}
?>
