<?php
/*
 * [���������Ω.JP �ġ���]
 * �桼������������ڡ���
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
_Log("[/login/index.php] start.");


_Log("[/login/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/login/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/login/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/login/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�������󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_Log("[/login/index.php] �������󤷤Ƥ��ʤ��ʤΤǥ���������̤�ɽ�����롣");
//	_Log("[/login/index.php] end.");
//	//����������̤�ɽ�����롣
//	header("Location: ".URL_LOGIN);
//	exit;

	//���ߡ����������������ꤹ�롣��������Ͽ�ѡ�
	$loginInfo['usr_auth_id'] = AUTH_NON;
} else {
	//������������������롣
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢����������̤����ܤ��롣
	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//ǧ�ڥ����å�----------------------------------------------------------------------end



//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- start
_Log("[/login/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/login/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/login/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/login/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/login/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼��������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/login/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼��������) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/login/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼��������) �ڼ��ԡ�");
}
_Log("[/login/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_LOGIN;



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


//�ѥ�᡼������������롣
$xmlName = XML_NAME_LOGIN;//XML�ե�����̾�����ꤹ�롣
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		//�����ͤ�������롣
		$info = $_POST;
		_Log("[/login/index.php] POST = '".print_r($info,true)."'");
		//�Хå�����å�����������
		$info = _StripslashesForArray($info);
		_Log("[/login/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");

		break;
	case 'GET':
		//XML�ե�����̾���������å�ID�����ͤ��ɲä��롣
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;

		break;
}

_Log("[/login/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/login/index.php] XML�ե�����̾ = '".$xmlName."'");
_Log("[/login/index.php] �������å�ID = '".$id."'");


//XML���ɤ߹��ࡣ
$xmlFile = "../common/form_xml/".$xmlName.".xml";
_Log("[/login/index.php] XML�ե����� = '".$xmlFile."'");
$xmlList = _GetXml($xmlFile);

_Log("[/login/index.php] XML�ե��������� = '".print_r($xmlList,true)."'");

//�̲��̤Υ�������ܥ��󤬲����줿���
if ($_POST['login'] != "" || $_POST['login_x'] != "" || $_POST['login_y'] != "") {
	//�����ͤ�ͤ��ؤ��롣
	$info['update']['tbl_user']['usr_e_mail'] = $info['e_mail'];
	$info['update']['tbl_user']['usr_pass'] = $info['pass'];

	$_POST['confirm'] = "��������";
}

//�ܲ��̤Υ�������ܥ��󤬲����줿���
if ($_POST['confirm'] != "") {
	//�����ͥ����å�
	$message .= _CheackInputAll($xmlList, $info);

	$userInfo = null;

	if (_IsNull($message)) {
		//���顼��̵����硢ǧ�ڥ����å���ɽ�����롣
		$condition = array();
		$condition = $info['update']['tbl_user'];
		$userList = _DB_GetList('tbl_user', $condition, true, null, 'usr_del_flag');
		if (!_IsNull($userList)) {
			if (count($userList) == 1) {
				//�᡼�륢�ɥ쥹���ѥ���ɤǸ��������1��Τ߸��Ĥ���Ϥ���
				$userInfo = $userList[0];
			} elseif (count($userList) > 1) {
				//ʣ�����Ĥ��ä���硢�ǡ������顼!!!
				_Log("[/login/index.php] {ERROR} �桼�����ơ��֥�˽�ʣ�ǡ���ͭ!!! �� tbl_use.usr_e_mail='".$info['update']['tbl_user']['usr_e_mail']."' / tbl_use.usr_pass='".$info['update']['tbl_user']['usr_pass']."'", 1);
			}
		}
		if (_IsNull($userInfo)) {
			$message .= "�᡼�륢�ɥ쥹�⤷���ϥѥ���ɤ��ۤʤ�ޤ���\n";
		}
	}

	if (_IsNull($message)) {
		//���顼��̵����硢�桼�����ڡ�����ɽ�����롣
		$mode = 2;

        activateCompany($userInfo);

		//���ID�򸡺����롣
		//�桼����_���_��Ϣ�եơ��֥�򸡺����롣
		$undeleteOnly = true;
		$condition = array();
		$condition['usr_cmp_rel_user_id'] = $userInfo['usr_user_id'];	//�桼����ID
		$order = "usr_cmp_rel_company_id";								//�����Ƚ�=���ID�ξ���(�ʤ�Ǥ⤤�����ɡ�)
		$tblUserCompanyRelationList = _DB_GetListByAssociative('tbl_user_company_relation', 'usr_cmp_rel_company_id', null, $condition, $undeleteOnly, $order, 'usr_cmp_rel_del_flag');
		$companyId = null;
		$llcId = null;
		if (!_IsNull($tblUserCompanyRelationList)) {
			//��ҥơ��֥�򸡺����롣
			$order = "cmp_company_id desc";									//�����Ƚ�=���ID�ι߽�
			$condition = array();
			$condition['cmp_company_id'] = $tblUserCompanyRelationList;		//���ID
			$condition['cmp_company_type_id'] = MST_COMPANY_TYPE_ID_CMP;	//��ҥ�����ID="�������"
			$tblCompanyList = _DB_GetList('tbl_company', $condition, $undeleteOnly, $order, 'cmp_del_flag');
			if (!_IsNull($tblCompanyList)) {
				//��Ƭ��������롣
				$companyId = $tblCompanyList[0]['cmp_company_id'];
			}

			$condition = array();
			$condition['cmp_company_id'] = $tblUserCompanyRelationList;		//���ID
			$condition['cmp_company_type_id'] = MST_COMPANY_TYPE_ID_LLC;	//��ҥ�����ID="��Ʊ���"
			$tblCompanyList = _DB_GetList('tbl_company', $condition, $undeleteOnly, $order, 'cmp_del_flag');
			if (!_IsNull($tblCompanyList)) {
				//��Ƭ��������롣
				$llcId = $tblCompanyList[0]['cmp_company_id'];
			}
		}
		//�Խ��оݤβ��ID�Ȥ������ꤹ�롣
		$_SESSION[SID_LOGIN_USER_COMPANY][MST_COMPANY_TYPE_ID_CMP] = $companyId;
		$_SESSION[SID_LOGIN_USER_COMPANY][MST_COMPANY_TYPE_ID_LLC] = $llcId;

		_Log("[/login/index.php] ��������桼�������� = '".print_r($userInfo,true)."'");
		//���å����˥��������������ꤹ�롣
		$_SESSION[SID_LOGIN_USER_INFO] = $userInfo;
		//�桼�����ڡ�����ɽ�����롣
		header("Location: ../user/");
		exit;
	} else {
		//���顼��ͭ����
		$message = "�����Ϥ˸��꤬����ޤ���\n".$message;
		$errorFlag = true;
	}
}
//���ܥ��󤬲����줿���
elseif ($_POST['back'] != "") {
}
//�����ܥ��󤬲����줿���
elseif ($_POST['go'] != "") {
}



//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/login/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/login/index.php] mode = '".$mode."'");






//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= $pageTitle;
$maincontent .= "</h2>";
$maincontent .= "\n";

$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//������ץȤ����ꤹ�롣
$script = null;

//�����ɥ�˥塼�����ꤹ�롣
$sidebar = null;

//����URL
$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);

$sidebar .= $htmlSidebarLogin;


//�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_LOGIN, '', PAGE_TITLE_LOGIN, 2);
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
//�᥿����
$html = str_replace ('{keywords}', PAGE_KEYWORDS_LOGIN, $html);
$html = str_replace ('{description}', PAGE_DESCRIPTION_LOGIN, $html);
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


_Log("[/login/index.php] end.");
echo $html;

?>