<?php
/*
 * [���������Ω.JP �ġ���]
 * �Ƽ������� �����ڡ���
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
_Log("[/user/company/pdf/index.php] start.");


_Log("[/user/company/pdf/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/company/pdf/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/company/pdf/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/company/pdf/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


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
_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../../../common/temp_html/temp_base.txt';
_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


//$tempSidebarLoginFile = '../../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"��¸�ߤ����硢ɽ�����롣
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) ��������");
//} else {
//	//�����Ǥ��ʤ��ä����
//	_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) �ڼ��ԡ�");
//}

$tempSidebarUserMenuFile = '../../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

_Log("[/user/company/pdf/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_COMPANY_PDF;



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

//�ѥ�᡼������������롣
$xmlName = null;//XML�ե�����̾�����ꤹ�롣
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


		_Log("[/user/company/pdf/index.php] {������桼�������½���} �桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."'");


		//���¤ˤ�äơ�ɽ������桼������������¤��롣
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://����̵��

				_Log("[/user/company/pdf/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
				_Log("[/user/company/pdf/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
				_Log("[/user/company/pdf/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

				$id = null;

				//��ʬ�Υ桼��������Τ�ɽ�����롣
				//�桼����ID�򸡺����롣
				$id = $loginInfo['usr_user_id'];

				_Log("[/user/company/pdf/index.php] {������桼�������½���} ���桼����ID = '".$id."'");
				break;
		}


		//�����ͤ�������롣
		$info = $_POST;
		_Log("[/user/company/pdf/index.php] POST = '".print_r($info,true)."'");
		//�Хå�����å�����������
		$info = _StripslashesForArray($info);
		_Log("[/user/company/pdf/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");

		//��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣���᡼���Ⱦ�ѥ��ʤ�ʸ����������Τǡ�
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/company/pdf/index.php] POST(��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣) = '".print_r($info,true)."'");


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



		_Log("[/user/company/pdf/index.php] {������桼�������½���} �桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."'");


		//���¤ˤ�äơ�ɽ������桼������������¤��롣
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://����̵��

				_Log("[/user/company/pdf/index.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
				_Log("[/user/company/pdf/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
				_Log("[/user/company/pdf/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

				$id = null;
				$undeleteOnly4def = true;

				//��ʬ�Υ桼��������Τ�ɽ�����롣
				//�桼����ID�򸡺����롣
				$id = $loginInfo['usr_user_id'];


				_Log("[/user/company/pdf/index.php] {������桼�������½���} ���桼����ID = '".$id."'");

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

_Log("[/user/company/pdf/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/user/company/pdf/index.php] XML�ե�����̾ = '".$xmlName."'");
_Log("[/user/company/pdf/index.php] �������å�ID = '".$id."'");


//�桼����ID�˴�Ϣ������ID�򸡺����롣
$companyId = _GetRelationCompanyId($id, $undeleteOnly4def);


//�桼��������(���������)�����ꤹ�롣��DB�����˻��Ѥ��롣
$info['update']['tbl_user'] = $loginInfo;

////�괾ǧ�ھ���̤����ξ�硢�桼��������(���������)�����ͤȤ������ꤹ�롣
//if (!isset($info['update']['tbl_article_deliver'])) {
//	$info['update']['tbl_article_deliver']['art_dlv_tel1'] = $loginInfo['usr_tel1'];
//	$info['update']['tbl_article_deliver']['art_dlv_tel2'] = $loginInfo['usr_tel2'];
//	$info['update']['tbl_article_deliver']['art_dlv_tel3'] = $loginInfo['usr_tel3'];
//
//	$info['update']['tbl_article_deliver']['art_dlv_e_mail'] = $loginInfo['usr_e_mail'];
//	$info['update']['tbl_article_deliver']['art_dlv_e_mail_confirm'] = $loginInfo['usr_e_mail'];
//
//	$info['update']['tbl_article_deliver']['art_dlv_family_name'] = $loginInfo['usr_family_name'];
//	$info['update']['tbl_article_deliver']['art_dlv_first_name'] = $loginInfo['usr_first_name'];
//
//	$info['update']['tbl_article_charge']['art_chg_family_name'] = $loginInfo['usr_family_name'];
//	$info['update']['tbl_article_charge']['art_chg_first_name'] = $loginInfo['usr_first_name'];
//}


$xmlList = null;
if (!_IsNull($xmlName)) {
}


//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/company/pdf/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/user/company/pdf/index.php] mode = '".$mode."'");




//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "../../..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../../img/maincontent/pt_user_company_pdf.jpg\" title=\"\" alt=\"�Ƽ������� ����\">";
$maincontent .= "</h2>";
$maincontent .= "\n";



//��������å�
//{treu:���顼ͭ��/false:���顼̵��}
$userStatusSystemErrorFlag = false;			//�����ƥ���������
//[�������] ���������Ω (�����ƥ���������)
if (!_CheckUserStatus($id, $companyId, MST_SYSTEM_COURSE_ID_CMP)) {
	$userStatusSystemErrorFlag = true;

	$maincontent .= "<div id=\"system_course_system\" class=\"message payMessage\">";
	$maincontent .= "\n";
	$maincontent .= "���������������ޤ��󡣽���κ���(����)�ϡ�����������η�Ѹ�ˤ����Ѥ���ǽ�Ȥʤ�ޤ���";
	$maincontent .= "<br />";
	$maincontent .= "<br />";
	$maincontent .= "<a href=\"../../buy/\">����ʧ���Ϥ�����</a>";
	$maincontent .= "\n";
	$maincontent .= "</div>";
	$maincontent .= "\n";
}



//����
$buf = _CreateTableInput4Pdf($mode, $xmlList, $info, $tabindex);
$maincontent .= "\n";
$maincontent .= $buf;


//������ץȤ����ꤹ�롣
$script = null;

if ($userStatusSystemErrorFlag) {
	//������ץȤ����ꤹ�롣
	$script .= "<script type=\"text/javascript\">";
	$script .= "\n";
	$script .= "<!--";
	$script .= "\n";
	$script .= "window.addEvent('domready', function(){";
	$script .= "\n";
	$script .= "$$('div.pdfset div.pdf div.output input').setStyle('display','none');";
	$script .= "\n";
	$script .= "$$('div.pdfset div.pdf div.output').setStyle('background','url(../../../img/pdf/pdf_btn_print_03.gif) no-repeat left top');";
	$script .= "\n";
	$script .= "});";
	$script .= "\n";
	$script .= "//-->";
	$script .= "\n";
	$script .= "</script>";
	$script .= "\n";
}

//������ץȤ����ꤹ�롣
$script .= "<script type=\"text/javascript\">";
$script .= "\n";
$script .= "<!--";
$script .= "\n";
$script .= "window.addEvent('domready', function(){";
$script .= "\n";
$script .= "$$('select').addEvent('change', function(e) {";
$script .= "\n";
//$script .= "alert(this.get('name') + '/' + this.get('value'));";
//$script .= "\n";
$script .= "$$('input.' + this.get('name')).set('value', this.get('value'))";
$script .= "\n";
$script .= "});";
$script .= "\n";
$script .= "});";
$script .= "\n";
$script .= "//-->";
$script .= "\n";
$script .= "</script>";
$script .= "\n";




$addStyle = null;





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
_SetBreadcrumbs(PAGE_DIR_COMPANY_PDF, '', PAGE_TITLE_COMPANY_PDF, 4);
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


_Log("[/user/company/pdf/index.php] end.");
echo $html;



































/**
 * PDF������
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
function _CreateTableInput4Pdf($mode, $xmlList, $info, &$tabindex) {


	$yearList = _GetYearArray(SYSTEM_START_YEAR - 1, date('Y') + 2);
	foreach ($yearList as $yearKey => $yearInfo) {
		$jpY = _ConvertAD2Jp($yearInfo['name']);
		$yearInfo['name'] .= "(".$jpY.")";
		$yearList[$yearKey] = $yearInfo;
	}
	$birthYearList = _GetYearArray(date('Y')-100, date('Y'));
	foreach ($birthYearList as $yearKey => $yearInfo) {
		$jpY = _ConvertAD2Jp($yearInfo['name']);
		$yearInfo['name'] .= "(".$jpY.")";
		$birthYearList[$yearKey] = $yearInfo;
	}


	$monthList = _GetMonthArray();
	$dayList = _GetDayArray();


	//�桼����ID��������롣
	$userId = $info['condition']['_id_'];


//	//�桼����ID�˴�Ϣ������ID�򸡺����롣
//	//�桼����_���_��Ϣ�եơ��֥�
//	$condition = null;
//	$condition['usr_cmp_rel_user_id'] = $userId;
//	$order = null;
//	$order .= "usr_cmp_rel_company_id";		//�����Ⱦ��=���ID�ξ���
//	$tblUserCompanyRelationList = _DB_GetListByAssociative('tbl_user_company_relation', 'usr_cmp_rel_company_id', null, $condition, true, $order,'usr_cmp_rel_del_flag');
//	$tblCompanyInfo = null;
//	if (!_IsNull($tblUserCompanyRelationList)) {
//		//��ҥơ��֥�
//		$condition = null;
//		$condition['cmp_company_id'] = $tblUserCompanyRelationList;		//���ID
//		$condition['cmp_company_type_id'] = MST_COMPANY_TYPE_ID_CMP;	//��ҥ�����ID="�������"
//		$order = null;
//		$order .= "cmp_company_id";		//�����Ⱦ��=���ID�ξ���
//		$tblCompanyList = _DB_GetList('tbl_company', $condition, true, $order, 'cmp_del_flag');
//		if (!_IsNull($tblCompanyList)) {
//			//��Ƭ��������롣(1��ΤϤ�)
//			$tblCompanyInfo = $tblCompanyList[0];
//		}
//	}
//
//	//���ID��������롣
//	$companyId = null;
//	if (!_IsNull($tblCompanyInfo)) {
//		$companyId = $tblCompanyInfo['cmp_company_id'];
//	}

	//�桼����ID�˴�Ϣ������ID�򸡺����롣
	$companyId = _GetRelationCompanyId($userId);

	//�桼����ID�˴�Ϣ������ID����Ҿ���򸡺����롣
	$companyInfo = null;
	$companyId = _GetRelationCompanyId($userId);
	if (!_IsNull($companyId)) {
		$companyInfo = _GetCompanyInfo($companyId);
	}

	$tblCompanyBoardInfo = null;
//	if (!_IsNull($tblCompanyInfo)) {
	if (!_IsNull($companyId)) {
		//���_����ơ��֥�
		$condition = null;
		$condition['cmp_bod_company_id'] = $companyId;				//���ID
		$condition['cmp_bod_post_id'] = MST_POST_ID_REP_DIRECTOR;	//��ID="��ɽ������"
		$order = null;
		$order .= "cmp_bod_no";		//�����Ⱦ��=���No�ξ���
		$tblCompanyBoardList = _DB_GetList('tbl_company_board', $condition, true, $order, 'cmp_bod_del_flag');
		if (!_IsNull($tblCompanyBoardList)) {
			//��Ƭ��������롣("��ɽ������"��1�ͤΤϤ���)
			$tblCompanyBoardInfo = $tblCompanyBoardList[0];
		}
	}

	//��ƻ�ܸ��ޥ���
	$condition = null;
	$order = null;
	$order .= "lpad(show_order,10,'0')";	//�����Ⱦ��=ɽ����ξ���
	$order .= ",id";						//�����Ⱦ��=ID�ξ���
	$mstPrefList = _DB_GetList('mst_pref', $condition, true, $order, 'del_flag', 'id');

	//ˡ̳�ɥޥ���
	$condition = null;
	$order = null;
	$order .= "lpad(show_order,10,'0')";	//�����Ⱦ��=ɽ����ξ���
	$order .= ",id";						//�����Ⱦ��=ID�ξ���
	$mstLegalAffairsBureauList = _DB_GetList('mst_legal_affairs_bureau', $condition, true, $order, 'del_flag', 'id');


	//�ƹ��ܤν���ͤ����ꤹ�롣

	//�괾�������ե饰
	$articleCreateFlag = false;
//	//�괾������(ǯ)
//	$articleCreateYear = date('Y');
//	//�괾������(��)
//	$articleCreateMonth = date('n');
//	//�괾������(��)
//	$articleCreateDay = date('j');

	//�괾������(ǯ)
	$articleCreateYear = null;
	//�괾������(��)
	$articleCreateMonth = null;
	//�괾������(��)
	$articleCreateDay = null;

	if (!_IsNull($companyInfo)) {
		//�괾������(ǯ)
		if (isset($companyInfo['tbl_company']['cmp_article_create_year']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_year'])) {
			$articleCreateYear = $companyInfo['tbl_company']['cmp_article_create_year'];
			$articleCreateFlag = true;
		}
		//�괾������(��)
		if (isset($companyInfo['tbl_company']['cmp_article_create_month']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_month'])) {
			$articleCreateMonth = $companyInfo['tbl_company']['cmp_article_create_month'];
			$articleCreateFlag = true;
		}
		//�괾������(��)
		if (isset($companyInfo['tbl_company']['cmp_article_create_day']) && !_IsNull($companyInfo['tbl_company']['cmp_article_create_day'])) {
			$articleCreateDay = $companyInfo['tbl_company']['cmp_article_create_day'];
			$articleCreateFlag = true;
		}
	}

	//�괾��������Ʊ���������ꤹ�롣
	//������(ǯ)
	$createYear = $articleCreateYear;
	//������(��)
	$createMonth = $articleCreateMonth;
	//������(��)
	$createDay = $articleCreateDay;

	//������(ǯ)
	$payYear = date('Y');
	//������(��)
	$payMonth = date('n');
	//������(��)
	$payDay = date('j');

	//��ɽ���������ǯ����(ǯ)
	$birthYear = null;
	//��ɽ���������ǯ����(��)
	$birthMonth = null;
	//��ɽ���������ǯ����(��)
	$birthDay = null;

	//�����Ͻн������(ǯ)
	$inkanCreateYear = date('Y');
	//�����Ͻн������(��)
	$inkanCreateMonth = date('n');
	//�����Ͻн������(��)
	$inkanCreateDay = date('j');

	//��Ź����Ϸ�Ľ������(ǯ)
	$ketugiCreateYear = date('Y');
	//��Ź����Ϸ�Ľ������(��)
	$ketugiCreateMonth = date('n');
	//��Ź����Ϸ�Ľ������(��)
	$ketugiCreateDay = date('j');

	//���������Ω�е������������(ǯ)
	$shinseiCreateYear = date('Y');
	//���������Ω�е������������(��)
	$shinseiCreateMonth = date('n');
	//���������Ω�е������������(��)
	$shinseiCreateDay = date('j');

	$no = 0;

	$res = null;

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".���������Ω�е�������";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfTokiShinseisho\" name=\"frmPdfTokiShinseisho\" action=\"./create/tokishinseisho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "���������Ω�е��������������ޤ���";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
//	$resBuf .= "������";
	$resBuf .= "�����";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($yearList, 'create_year', $shinseiCreateYear);
	$resBuf .= "&nbsp;";
	$resBuf .= "ǯ";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'create_month', $shinseiCreateMonth);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'create_day', $shinseiCreateDay);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
//	$resBuf .= "<br />";
//	$resBuf .= "�����Ρ�4.�פΡֿ������פ�Ʊ�����դˤ��Ƥ���������";
	$resBuf .= "</dd>";
	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"../../../img/pdf/pdf_doc_01.pdf\" target=\"_blank\" title=\"���������Ω�е�������\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;


	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".��Ź����Ϸ�Ľ�";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfHontenKetugisho\" name=\"frmPdfHontenKetugisho\" action=\"./create/honten_ketugisho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "��Ҥ���Ω������硢��Ź����Ϥ����ʤ���Фʤ�ޤ��󡣡�".SITE_TITLE."�פξ��ˤ����Ƥϡ���Τ��Ȥ��θ���괾��ǧ�ڻ��˺Ǿ��������ޤ����Ƥ��ޤ������ξ�硢�е����˶���Ū�����ϤޤǤ����ʤ���Фʤ�ޤ���";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "������";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'create_year', $ketugiCreateYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "ǯ";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'create_month', $ketugiCreateMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'create_day', $ketugiCreateDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "<br />";
//	$resBuf .= "�����Ρ�4.�פΡֿ������פ�Ʊ�����դˤ��Ƥ���������";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_01.jpg\" rel=\"pdf_doc\" title=\"��Ź����Ϸ�Ľ�\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	//������
	$resBuf .= "<input class=\"create_year\" type=\"hidden\" name=\"create_year\" value=\"".$shinseiCreateYear."\" />";
	$resBuf .= "<input class=\"create_month\" type=\"hidden\" name=\"create_month\" value=\"".$shinseiCreateMonth."\" />";
	$resBuf .= "<input class=\"create_day\" type=\"hidden\" name=\"create_day\" value=\"".$shinseiCreateDay."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;


	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
//	$resBuf .= ".�����򡦴ƺ���ν�Ǥ������";
//	$resBuf .= ".��ɽ�Ұ����ƺ���ν�Ǥ������";
	$resBuf .= ".��Ǥ������";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfShodakusho\" name=\"frmPdfShodakusho\" action=\"./create/shodakusho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "������˽�Ǥ���뤳�Ȥ���������̤Ǥ�����ư���괾��������Ʊ�����դǺ������ޤ�����������<span class=\"attention\">���դϼ°�</span>����Ѥ��Ƥ���������(����ɽ������ΰ��վ�����Τߤ�ź��)";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "�괾������";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'article_create_year', $articleCreateYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "ǯ";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'article_create_month', $articleCreateMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'article_create_day', $articleCreateDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	if (!$articleCreateFlag) {
//		$resBuf .= "<br />";
//		$resBuf .= "<span class=\"attention\">����ա��괾�Ϥޤ���������Ƥ��ޤ��󡣾嵭���դ����������դǤϤ���ޤ���</span>";
//	}
//	$resBuf .= "</dd>";
//
//	$resBuf .= "<dt>";
//	$resBuf .= "������";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'create_year', $createYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "ǯ";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'create_month', $createMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'create_day', $createDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "��1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_02.jpg\" rel=\"pdf_doc\" title=\"��Ǥ������\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	//�괾������
	$resBuf .= "<input type=\"hidden\" name=\"article_create_year\" value=\"".$articleCreateYear."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"article_create_month\" value=\"".$articleCreateMonth."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"article_create_day\" value=\"".$articleCreateDay."\" />";

	//������
	$resBuf .= "<input class=\"create_year\" type=\"hidden\" name=\"create_year\" value=\"".$shinseiCreateYear."\" />";
	$resBuf .= "<input class=\"create_month\" type=\"hidden\" name=\"create_month\" value=\"".$shinseiCreateMonth."\" />";
	$resBuf .= "<input class=\"create_day\" type=\"hidden\" name=\"create_day\" value=\"".$shinseiCreateDay."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

//����
if (false) {
	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".��Ω����ɽ�����������Ľ�";
	$resBuf .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�ڢ�����PDF̵��!!!!!����α�接������";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "��ɽ���������Ǥ����ꤷ�����̤Ǥ�����ư���괾��������Ʊ�����դǺ������ޤ�����������<span class=\"attention\">���դϼ°�</span>����Ѥ��Ƥ��������� ";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"��������\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;
}

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".ʧ���ߤ����ä����Ȥξ�����";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfHaraikomi\" name=\"frmPdfHaraikomi\" action=\"./create/haraikomi.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "ȯ�������������ܶ�ο����ߤ�λ��������(��Ģ�˵��ܤ���Ƥ�����)�ޤ��ϡ������������դ���ꡣ";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
	$resBuf .= "������";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($yearList, 'pay_year', $payYear);
	$resBuf .= "&nbsp;";
	$resBuf .= "ǯ";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'pay_month', $payMonth);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'pay_day', $payDay);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "</dd>";
	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_03.jpg\" rel=\"pdf_doc\" title=\"ʧ���ߤ����ä����Ȥξ�����\">[��������]</a>";
//	$resBuf .= "<br />";
//	$resBuf .= "<a href=\"#\" title=\"��������\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<div class=\"point_explain_in\">";
	$resBuf .= "<span class=\"attention\">";
	$resBuf .= "���ܶ�ο������괾ǧ�ڤ���λ���Ƥ���Ԥ��褦�ˤ��Ƥ���������";
	$resBuf .= "</span>";
	$resBuf .= "<br />";
	$resBuf .= "�㿶����ˡ���";
	$resBuf .= "<br />";
//	$resBuf .= "���Ȥ���ȯ����(A���� B���� C�����3��)�ǽл񤷡�A����θ��¤˻��ܶ�򽸤���硢A����⼫ʬ���Ȥθ��¤�A����̾���ǿ����ޤʤ���Фʤ�ޤ���(��Ģ�˿����ͤ�̾�������ܤ����褦��)����ǤϤ���Ǥ���";
	$resBuf .= "ȯ����(���Ĥ��� ���ڤ��� ��ƣ�����3��)�ǽл񤷡����Ĥ���θ��¤˻��ܶ�򽸤���硢���Ĥ���⼫ʬ���Ȥθ��¤˻��Ĥ���̾���ǿ����ޤʤ���Фʤ�ޤ���(��Ģ�˿����ͤ�̾�������ܤ����褦��)������Ǥ�NG�Ǥ���";
	$resBuf .= "<br />";
	$resBuf .= "��ɬ��ȯ����̾�ǿ�����Ǥ���������";
	$resBuf .= "</div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".���ܶ�γۤη׾�˴ؤ��������";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfShihonkinNoGakuNoKeijo\" name=\"frmPdfShihonkinNoGakuNoKeijo\" action=\"./create/shihonkinnogakunokeijo.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">(����ʪ�л��Ԥ����Τ�ɬ��)</span><br />";
//	$resBuf .= "ȯ�������������ܶ�ο����ߤ�λ��������(��Ģ�˵��ܤ���Ƥ�����)�ޤ��ϡ������������դ���ꡣ";
//	$resBuf .= "ȯ�������������ܶ�ο����ߤ�λ��������(��Ģ�˵��ܤ���Ƥ�����)�ޤ��ϡ������ʹߤ����դ���ꡣ";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "������";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'pay_year', $payYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "ǯ";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'pay_month', $payMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'pay_day', $payDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_04.jpg\" rel=\"pdf_doc\" title=\"���ܶ�γۤη׾�˴ؤ��������\">[��������]</a>";
//	$resBuf .= "<br />";
//	$resBuf .= "<a href=\"#\" title=\"��������\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	//������
	$resBuf .= "<input class=\"pay_year\" type=\"hidden\" name=\"pay_year\" value=\"".$payYear."\" />";
	$resBuf .= "<input class=\"pay_month\" type=\"hidden\" name=\"pay_month\" value=\"".$payMonth."\" />";
	$resBuf .= "<input class=\"pay_day\" type=\"hidden\" name=\"pay_day\" value=\"".$payDay."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".Ĵ������";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfChosahokokusho\" name=\"frmPdfChosahokokusho\" action=\"./create/chosahokokusho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">(����ʪ�л��Ԥ����Τ�ɬ��)</span><br />";
//	$resBuf .= "ȯ�������������ܶ�ο����ߤ�λ��������(��Ģ�˵��ܤ���Ƥ�����)�ޤ��ϡ������������դ���ꡣ";
//	$resBuf .= "ȯ�������������ܶ�ο����ߤ�λ��������(��Ģ�˵��ܤ���Ƥ�����)�ޤ��ϡ������ʹߤ����դ���ꡣ";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "������";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'pay_year', $payYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "ǯ";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'pay_month', $payMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'pay_day', $payDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_05.jpg\" rel=\"pdf_doc\" title=\"Ĵ������\">[��������]</a>";
//	$resBuf .= "<br />";
//	$resBuf .= "<a href=\"#\" title=\"��������\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	//������
	$resBuf .= "<input class=\"pay_year\" type=\"hidden\" name=\"pay_year\" value=\"".$payYear."\" />";
	$resBuf .= "<input class=\"pay_month\" type=\"hidden\" name=\"pay_month\" value=\"".$payMonth."\" />";
	$resBuf .= "<input class=\"pay_day\" type=\"hidden\" name=\"pay_day\" value=\"".$payDay."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".�⻺���ѽ�";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfZaisanhikitugisho\" name=\"frmPdfZaisanhikitugisho\" action=\"./create/zaisanhikitugisho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">(����ʪ�л��Ԥ����Τ�ɬ��)</span><br />";
//	$resBuf .= "ȯ�������������ܶ�ο����ߤ�λ��������(��Ģ�˵��ܤ���Ƥ�����)�ޤ��ϡ������������դ���ꡣ";
//	$resBuf .= "ȯ�������������ܶ�ο����ߤ�λ��������(��Ģ�˵��ܤ���Ƥ�����)�ޤ��ϡ������ʹߤ����դ���ꡣ";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "������";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= _GetSelect($yearList, 'pay_year', $payYear);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "ǯ";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($monthList, 'pay_month', $payMonth);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "&nbsp;";
//	$resBuf .= _GetSelect($dayList, 'pay_day', $payDay);
//	$resBuf .= "&nbsp;";
//	$resBuf .= "��";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_06.jpg\" rel=\"pdf_doc\" title=\"�⻺���ѽ�\">[��������]</a>";
//	$resBuf .= "<br />";
//	$resBuf .= "<a href=\"#\" title=\"��������\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	//������
	$resBuf .= "<input class=\"pay_year\" type=\"hidden\" name=\"pay_year\" value=\"".$payYear."\" />";
	$resBuf .= "<input class=\"pay_month\" type=\"hidden\" name=\"pay_month\" value=\"".$payMonth."\" />";
	$resBuf .= "<input class=\"pay_day\" type=\"hidden\" name=\"pay_day\" value=\"".$payDay."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

//����
if (false) {
	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".OCR�ѿ����ѻ�";
	$resBuf .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�ڢ�����PDF̵��!!!!!����α�接������";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "OCR�ѿ����ѻ�(�е���Υ���ԥ塼�������ɼ����)��������ޤ���";
	$resBuf .= "<br />";
	$resBuf .= "�����ѻ���е�������ۤ��Ƥ���ޤ���������B5�ѻ�Ǥ�OK�Ǥ���";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "<div class=\"pdf\">";
	$resBuf .= "<h5>";
	$resBuf .= "���Υ��ԡ��ѻ�B5�˰���������";
	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "���������ѻ������<span class=\"attention\">B5��</span>�����򤷤Ƥ���������";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size b5\">";
	$resBuf .= "B5�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"��������\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->


	$resBuf .= "<div class=\"pdf\">";
	$resBuf .= "<h5>";
	$resBuf .= "�е���Ǥ��ä�OCR�����ѻ�˰���������";
	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "���������ѻ������<span class=\"attention\">B5��</span>�����򤷤Ƥ���������";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size b5\">";
	$resBuf .= "B5�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"��������\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;
}

	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".�����Ͻн�";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "��Ҥ���ɽ������Ͽ���ޤ���";
	$resBuf .= "<br />";
	$resBuf .= "�е���λ����ե����ɤο�����ԤäƤ���������";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "<div class=\"pdf\">";
//����
if (false) {
	$resBuf .= "<h5>";
	$resBuf .= "��ɽ�������е������˹Ԥ����";
	$resBuf .= "</h5>";
}

	$resBuf .= "<form id=\"frmPdfInkantodokesho1\" name=\"frmPdfInkantodokesho1\" action=\"./create/inkantodokesho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">���վ�����Ʊ��ǯ�����Ѥ��Ƥ���������</span>";
//	$resBuf .= "<br />";
//	$resBuf .= "���������ѻ������<span class=\"attention\">B5��</span>�����򤷤Ƥ���������";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
	$resBuf .= "��ǯ����";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($birthYearList, 'birth_year', $birthYear);
	$resBuf .= "&nbsp;";
	$resBuf .= "ǯ";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'birth_month', $birthMonth);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'birth_day', $birthDay);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "<br />";
	$resBuf .= "��ɽ���������ǯ���������Ϥ��Ƥ���������";
	$resBuf .= "</dd>";
	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size b5\">";
	$resBuf .= "B5�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a class=\"smoothbox\" href=\"../../../img/pdf/pdf_doc_07.jpg\" rel=\"pdf_doc\" title=\"�����Ͻн�\">[��������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"mode\" value=\"".PDF_MODE_INKAN_DIRECTOR."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

//����
if (false) {
	$resBuf .= "<div class=\"pdf\">";
	$resBuf .= "<h5>";
	$resBuf .= "�����ͤ��е������˹Ԥ����";
	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfInkantodokesho2\" name=\"frmPdfInkantodokesho2\" action=\"./create/inkantodokesho.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">���վ�����Ʊ��ǯ�����Ѥ��Ƥ���������</span>";
	$resBuf .= "<br />";
	$resBuf .= "���������ѻ������<span class=\"attention\">B5��</span>�����򤷤Ƥ���������";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
	$resBuf .= "��ǯ����";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($birthYearList, 'birth_year', $birthYear);
	$resBuf .= "&nbsp;";
	$resBuf .= "ǯ";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'birth_month', $birthMonth);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'birth_day', $birthDay);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "<br />";
	$resBuf .= "��ɽ���������ǯ���������Ϥ��Ƥ���������";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "������";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($yearList, 'create_year', $inkanCreateYear);
	$resBuf .= "&nbsp;";
	$resBuf .= "ǯ";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'create_month', $inkanCreateMonth);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'create_day', $inkanCreateDay);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "�����ͻ�̾";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "��";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_family_name\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "̾";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_first_name\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(����)";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "�եꥬ��";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "��";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_family_name_kana\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "̾";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_first_name_kana\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(���ѥ�������)";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "�����ͽ���";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "��ƻ�ܸ�";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($mstPrefList, 'agent_pref_id', null, "", true);
	$resBuf .= "<br />";
	$resBuf .= "�Զ�Į¼";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_address1\" size=\"30\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(����)";
	$resBuf .= "<br />";
	$resBuf .= "�嵭�ʹ�";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_address2\" size=\"30\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(����)";
	$resBuf .= "</dd>";

	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size b5\">";
	$resBuf .= "B5�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"��������1\">[��������1]</a>";
	$resBuf .= "<br />";
	$resBuf .= "<a href=\"#\" title=\"��������2\">[��������2]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"mode\" value=\"".PDF_MODE_INKAN_OTHER."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->
}

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;

//����
if (false) {
	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".��Ͽ�ȵ���Ǽ�������";
	$resBuf .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�ڢ�����PDF̵��!!!!!����α�接������";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";
//	$resBuf .= "<h5>";
//	$resBuf .= "xxx";
//	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "��Ͽ�ȵ���(��������)��Ž���դ��뤿��ν��̤Ǥ���";
	$resBuf .= "</div>";

//	$resBuf .= "<div class=\"input\">";
//	$resBuf .= "<dl>";
//	$resBuf .= "<dt>";
//	$resBuf .= "</dt>";
//	$resBuf .= "<dd>";
//	$resBuf .= "</dd>";
//	$resBuf .= "</dl>";
//	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"�������\">[�������]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;
}

//����
if (false) {
	$resBuf = null;
	$resBuf .= "<div class=\"pdfset\">";
	$resBuf .= "<h4>";
	$resBuf .= (++$no);
	$resBuf .= ".��Ω�е�������";
	$resBuf .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�ڢ�����PDF̵��!!!!!����α�接������";
	$resBuf .= "</h4>";

	$resBuf .= "<div class=\"pdf\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "<span class=\"attention\">";
	$resBuf .= "��ɽ�������е������˹Ԥ����ϰ�Ǥ���ϰ�������ޤ�����ɽ������ʳ��������Ԥ������е�������Ȱ�Ǥ������������ޤ���";
	$resBuf .= "</span>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->


	$resBuf .= "<div class=\"pdf\">";
	$resBuf .= "<h5>";
	$resBuf .= "��ɽ�������е������˹Ԥ����";
	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "�ɳ��е���˽���������������ꤷ�ޤ���";
	$resBuf .= "<br />";
	$resBuf .= "�ºݤ˹Ԥ����ˡ̳�ɤ����Ϥ��Ƥ���������";
	$resBuf .= "<br />";
	$resBuf .= "(���ֺǸ�˰����Ǥ��)";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
	$resBuf .= "��������";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($yearList, 'present_year', $info['update']['tbl_pdf']['present_year']);
	$resBuf .= "&nbsp;";
	$resBuf .= "ǯ";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'present_month', $info['update']['tbl_pdf']['present_month']);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'present_day', $info['update']['tbl_pdf']['present_day']);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "ˡ̳��";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "<input type=\"text\" name=\"legal_affairs_bureau\" size=\"25\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(����)ˡ̳��";
	$resBuf .= "<br />";
	$resBuf .= "<input type=\"checkbox\" id=\"head_office_flag\" name=\"head_office_flag\" value=\"1\" />";
	$resBuf .= "<label for=\"head_office_flag\">";
	$resBuf .= "&nbsp;";
	$resBuf .= "�ܶ�(�ٶɡ���ĥ�̵꤬�����ϥ����å�)";
	$resBuf .= "</label>";
	$resBuf .= "<br />";
	$resBuf .= "<input type=\"text\" name=\"branch_office\" size=\"25\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetRadio($mstLegalAffairsBureauList, 'legal_affairs_bureau_id', $info['update']['tbl_pdf']['legal_affairs_bureau_id']);
	$resBuf .= "</dd>";

	$boardName = '�����ߡ�̤��Ͽ�Ǥ���';
	if (!_IsNull($tblCompanyBoardInfo)) {
		if (!_IsNull($tblCompanyBoardInfo['cmp_bod_family_name']) || !_IsNull($tblCompanyBoardInfo['cmp_bod_first_name'])) {
			$boardName = $tblCompanyBoardInfo['cmp_bod_family_name']." ".$tblCompanyBoardInfo['cmp_bod_first_name'];
		}

	}
	$resBuf .= "<dt>";
	$resBuf .= "��ɽ������";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= $boardName;
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "�����ֹ�";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "<input type=\"text\" name=\"board_tel1\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "-";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"board_tel2\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "-";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"board_tel3\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "<br />";
	$resBuf .= "��ɽ������������ֹ�����Ϥ��Ƥ���������";
	$resBuf .= "</dd>";
	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"��������1\">[��������1]</a>";
	$resBuf .= "<br />";
	$resBuf .= "<a href=\"#\" title=\"��������2\">[��������2]</a>";
	$resBuf .= "<br />";
	$resBuf .= "<a href=\"#\" title=\"��������3\">[��������3]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->



	$resBuf .= "<div class=\"pdf\">";
	$resBuf .= "<h5>";
	$resBuf .= "��ɽ������ʳ��������ͤȤ����е������˹Ԥ����";
	$resBuf .= "</h5>";

	$resBuf .= "<form id=\"frmPdfXxx\" name=\"frmPdfXxx\" action=\"./create/xxx.php\" method=\"post\" target=\"_blank\">";

	$resBuf .= "<div class=\"pdf_wrapper1\">";

	$resBuf .= "<div class=\"exp\">";
	$resBuf .= "�ɳ��е���˽���������������ꤷ�ޤ���";
	$resBuf .= "<br />";
	$resBuf .= "�ºݤ˹Ԥ����ˡ̳�ɤ����Ϥ��Ƥ���������";
	$resBuf .= "<br />";
	$resBuf .= "(���ֺǸ�˰����Ǥ��)";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"input\">";
	$resBuf .= "<dl>";
	$resBuf .= "<dt>";
	$resBuf .= "��������";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= _GetSelect($yearList, 'present_year', $info['update']['tbl_pdf']['present_year']);
	$resBuf .= "&nbsp;";
	$resBuf .= "ǯ";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($monthList, 'present_month', $info['update']['tbl_pdf']['present_month']);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($dayList, 'present_day', $info['update']['tbl_pdf']['present_day']);
	$resBuf .= "&nbsp;";
	$resBuf .= "��";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "ˡ̳��";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "<input type=\"text\" name=\"legal_affairs_bureau\" size=\"25\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(����)ˡ̳��";
	$resBuf .= "<br />";
	$resBuf .= "<input type=\"checkbox\" id=\"head_office_flag\" name=\"head_office_flag\" value=\"1\" />";
	$resBuf .= "<label for=\"head_office_flag\">";
	$resBuf .= "&nbsp;";
	$resBuf .= "�ܶ�(�ٶɡ���ĥ�̵꤬�����ϥ����å�)";
	$resBuf .= "</label>";
	$resBuf .= "<br />";
	$resBuf .= "<input type=\"text\" name=\"branch_office\" size=\"25\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetRadio($mstLegalAffairsBureauList, 'legal_affairs_bureau_id', $info['update']['tbl_pdf']['legal_affairs_bureau_id']);
	$resBuf .= "</dd>";

	$resBuf .= "<dt>";
	$resBuf .= "�����ͻ�̾";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "��";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_family_name\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "̾";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_first_name\" size=\"10\" maxlength=\"100\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(����)";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "�����ͽ���";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "��ƻ�ܸ�";
	$resBuf .= "&nbsp;";
	$resBuf .= _GetSelect($mstPrefList, 'agent_pref_id', null, "", true);
	$resBuf .= "<br />";
	$resBuf .= "�Զ�Į¼";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_address1\" size=\"30\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(����)";
	$resBuf .= "<br />";
	$resBuf .= "�嵭�ʹ�";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_address2\" size=\"30\" maxlength=\"200\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "(����)";
	$resBuf .= "</dd>";
	$resBuf .= "<dt>";
	$resBuf .= "�����ֹ�";
	$resBuf .= "</dt>";
	$resBuf .= "<dd>";
	$resBuf .= "<input type=\"text\" name=\"agent_tel1\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "-";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_tel2\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "&nbsp;";
	$resBuf .= "-";
	$resBuf .= "&nbsp;";
	$resBuf .= "<input type=\"text\" name=\"agent_tel3\" size=\"4\" maxlength=\"4\" value=\"\" />";
	$resBuf .= "<br />";
	$resBuf .= "�����ͤ������ֹ�����Ϥ��Ƥ���������";
	$resBuf .= "</dd>";
	$resBuf .= "</dl>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper1 -->";//<!-- End pdf_wrapper1 -->

	$resBuf .= "<div class=\"pdf_wrapper2\">";

	$resBuf .= "<div class=\"size a4\">";
	$resBuf .= "A4�ѻ�";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"num\">";
	$resBuf .= "1��";
	$resBuf .= "</div>";

	$resBuf .= "<div class=\"output\">";
	$resBuf .= "<input type=\"image\" name=\"create_pdf\" src=\"../../../img/pdf/pdf_btn_print_01.gif\" onmouseover=\"this.src='../../../img/pdf/pdf_btn_print_02.gif'\" onmouseout=\"this.src='../../../img/pdf/pdf_btn_print_01.gif'\" alt=\"����\" />";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper2 -->";//<!-- End pdf_wrapper2 -->

	$resBuf .= "<div class=\"pdf_wrapper3\">";

	$resBuf .= "<div class=\"exp_link\">";
	$resBuf .= "<a href=\"#\" title=\"��������1\">[��������1]</a>";
	$resBuf .= "<br />";
	$resBuf .= "<a href=\"#\" title=\"��������2\">[��������2]</a>";
	$resBuf .= "<br />";
	$resBuf .= "<a href=\"#\" title=\"��������3\">[��������3]</a>";
	$resBuf .= "</div>";

	$resBuf .= "</div><!-- End pdf_wrapper3 -->";//<!-- End pdf_wrapper3 -->

	$resBuf .= "<div class=\"pdf_end\"></div>";

	$resBuf .= "<input type=\"hidden\" name=\"user_id\" value=\"".$userId."\" />";
	$resBuf .= "<input type=\"hidden\" name=\"company_id\" value=\"".$companyId."\" />";

	$resBuf .= "</form>";

	$resBuf .= "</div><!-- End pdf -->";//<!-- End pdf -->

	$resBuf .= "</div><!-- End pdfset -->";//<!-- End pdfset -->

	if (!_IsNull($res)) $res .= "\n";
	$res .= $resBuf;
}



	if (!_IsNull($res)) {
		$buf = null;

		$buf .= "<div class=\"adv\">";
		$buf .= "\n";
		$buf .= "<h3>����դ�������!!</h3>";
		$buf .= "\n";
		$buf .= "<div class=\"adv_exp\">";
		$buf .= "\n";
		$buf .= "�Ż��괾��ǧ�ڸ�ˡ��������Ƥ���������<br />";
		$buf .= "\n";
//		$buf .= "�Ż��괾�κ����桢�ޤ���ǧ�����ˡ���ˡ̳�ɤؤο�������פ�������ޤ��ȡ��������е����ब�����Ǥ��ޤ���<br />";
		$buf .= "�Ż��괾�κ����桢�ޤ���ǧ�����ˡ���ˡ̳�ɤؤο�������פ������¹Ԥ��Ƥ��ޤ��ȡ��������е����ब�����Ǥ��ޤ���<br />";
		$buf .= "\n";
		$buf .= "<br />";
		$buf .= "\n";
		$buf .= "�е�����ΰ����ϡ�<span class=\"attention\">ɬ���������Ǥ��Ż��괾��ǧ�ڡ׸�</span>�˹ԤäƤ���������<br />";
		$buf .= "\n";
		$buf .= "<br />";
		$buf .= "\n";
		$buf .= "�嵭���������λ���ξ塢�����ؿʤ�Ǥ���������";
		$buf .= "\n";
		$buf .= "</div>";
		$buf .= "\n";
		$buf .= "</div><!-- End adv -->";
		$buf .= "\n";

		$buf .= "<div class=\"formWrapper\">";
		$buf .= "\n";
		$buf .= "<div class=\"formList\">";
		$buf .= "\n";

		$buf .= "<div id=\"tbl_pdf\">";
		$buf .= "\n";

		$buf .= "<h3>�е������Ф������</h3>";
		$buf .= "\n";
		$buf .= "<div class=\"point_explain_out\">";
		$buf .= "<span class=\"attention\">";
		$buf .= "��§���е��λ�ͳ ��ȯ�������Ȥ����餫��2���ְ���˴ɳ��е�����е������������Ф��Ƥ���������";
		$buf .= "</span>";
		$buf .= "<br />";
//		$buf .= "�е��λ�ͳ�ȤϤ��٤Ƥν��������ä��ʳ��Ȥ������Ȥˤʤ�ޤ��Τǡ�<span class=\"attention\">���ܶ�ο�����ߤ���λ����������2���ְ���</span>�Ȥʤ�ޤ���";
		$buf .= "�е��λ�ͳ��ȯ�������Ȥ��Ȥϡ����٤Ƥν��������ä��Ȥ����̣���ޤ��Τǡ�";
		$buf .= "<br />";
		$buf .= "<span class=\"attention\">��§���ܶ�ο�����ߤ���λ���������2���ְ���</span>�Ȥʤ�ޤ���";
		$buf .= "<br />";
		$buf .= "<br />";
		$buf .= "<span class=\"attention\" style=\"font-weight:bold;\">";
		$buf .= "���դ��괾�������򸵤˼�ưŪ�����ꤵ��ޤ���";
		$buf .= "</span>";
		$buf .= "<br />";
		$buf .= "<br />";
		$buf .= "�����������ϡ�1.�פ����˹ԤäƤ���������";
		$buf .= "</div>";
		$buf .= "\n";
		$buf .= $res;
		$buf .= "\n";
		$buf .= "<div class=\"point_explain_out\">";
		$buf .= "<span class=\"attention\">���е���˹Ԥ��Ȥ���ɬ�פʽ�����</span>";
		$buf .= "<ol>";
		$buf .= "<li>�嵭��".SITE_TITLE."�פǰ����������̡�����������1��</li>";
		$buf .= "<li>�괾����������1��(�괾ǧ�ںѤߤΤ��)(ɽ���ƥ�ܤȵ��ܤ���Ƥ�����)</li>";
//		$buf .= "<li>�����������ΰ��վ���������������1��(3��������ȯ�Ԥ��줿���)(��ɽ������ޤ�)</li>";
		$buf .= "<li>�����������ΰ��վ����񡦡���������1��(3��������ȯ�Ԥ��줿��Τ˸¤�)����ɽ������ޤ�</li>";
//		$buf .= "<li>��Ͽ�ȵ��ǡ���������15����(����ޤ��ϼ�������)</li>";
		$buf .= "<li>��Ͽ�ȵ��ǡ������������ܶ�2,157���߰ʲ��ξ���15����(����ޤ��ϼ�������)</li>";
		$buf .= "</ol>";
		$buf .= "</div>";
		$buf .= "\n";
		$buf .= "</div><!-- End tbl_pdf -->";//<!-- End tbl_pdf -->
		$buf .= "\n";

		$buf .= "</div><!-- End formList -->";//<!-- End formList -->
		$buf .= "\n";
		$buf .= "</div><!-- End formWrapper -->";//<!-- End formWrapper -->
		$buf .= "\n";

		$res = $buf;
	}

	return $res;
}



?>
