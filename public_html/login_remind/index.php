<?php
/*
 * [���������Ω.JP �ġ���]
 * �桼�����ѥ���ɳ�ǧ�ڡ���
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
_Log("[/login_remind/index.php] start.");


_Log("[/login_remind/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/login_remind/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/login_remind/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/login_remind/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_Log("[/login_remind/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
//	_Log("[/login_remind/index.php] end.");
//	//��������̤�ɽ�����롣
//	header("Location: ".URL_LOGIN);
//	exit;

	//���ߡ��������������ꤹ�롣��������Ͽ�ѡ�
	$loginInfo['usr_auth_id'] = AUTH_NON;
} else {
	//����������������롣
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//ǧ�ڥ����å�----------------------------------------------------------------------end



//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- start
_Log("[/login_remind/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/login_remind/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/login_remind/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/login_remind/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/login_remind/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/login_remind/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/login_remind/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) �ڼ��ԡ�");
}
_Log("[/login_remind/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_LOGIN_REMIND;

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


//�ѥ�᡼������������롣
$xmlName = XML_NAME_LOGIN_REMIND;//XML�ե�����̾�����ꤹ�롣
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		//�����ͤ�������롣
		$info = $_POST;
		_Log("[/login_remind/index.php] POST = '".print_r($info,true)."'");
		//�Хå�����å�����������
		$info = _StripslashesForArray($info);
		_Log("[/login_remind/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");

		break;
	case 'GET':
		//XML�ե�����̾���������å�ID�����ͤ��ɲä��롣
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;

		break;
}

_Log("[/login_remind/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/login_remind/index.php] XML�ե�����̾ = '".$xmlName."'");
_Log("[/login_remind/index.php] �������å�ID = '".$id."'");


//XML���ɤ߹��ࡣ
$xmlFile = "../common/form_xml/".$xmlName.".xml";
_Log("[/login_remind/index.php] XML�ե����� = '".$xmlFile."'");
$xmlList = _GetXml($xmlFile);

_Log("[/login_remind/index.php] XML�ե��������� = '".print_r($xmlList,true)."'");


//�����ܥ��󤬲����줿���
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
				//�᡼�륢�ɥ쥹�Ǹ��������1��Τ߸��Ĥ���Ϥ���
				$userInfo = $userList[0];
			} elseif (count($userList) > 1) {
				//ʣ�����Ĥ��ä���硢�ǡ������顼!!!
				_Log("[/login_remind/index.php] {ERROR} �桼�����ơ��֥�˽�ʣ�ǡ���ͭ!!! �� tbl_use.usr_e_mail='".$info['update']['tbl_user']['usr_e_mail']."'", 1);
			}
		}
		if (_IsNull($userInfo)) {
			$message .= "�᡼�륢�ɥ쥹���ۤʤ�ޤ���\n";
		}
	}

	if (_IsNull($message)) {
		//���顼��̵����硢�᡼���������롣

		//�᡼����ʸ�ζ�����ʬ�����ꤹ�롣
		$body = null;
		$body .= "��������������������������������������������������������\n";
		$body .= "�桼��������\n";
		$body .= "��������������������������������������������������������\n";
//		$body .= "�桼����ID��";
//		$body .= $userInfo['usr_user_id'];
//		$body .= "\n";
		$body .= "��̾����";
		$body .= $userInfo['usr_family_name'];
		$body .= " ";
		$body .= $userInfo['usr_first_name'];
		$body .= "\n";
		$body .= "�᡼�륢�ɥ쥹��";
		$body .= $userInfo['usr_e_mail'];
		$body .= "\n";
		$body .= "�ѥ���ɡ�";
		$body .= $userInfo['usr_pass'];
		$body .= "\n";

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

		$body .= "��Ͽ������".date("Yǯn��j�� H��iʬ")."\n";
		$body .= $_SERVER["REMOTE_ADDR"]."\n";

		//�������ѥ᡼����ʸ�����ꤹ�롣
		$adminBody = "";
		//$adminBody .= $siteTitle." \n";
		//$adminBody .= "\n";
		$adminBody .= "**************************************************************************************\n";
		$adminBody .= "��".$siteTitle."�٤˥ѥ���ɳ�ǧ������ޤ�����\n";
		$adminBody .= "**************************************************************************************\n";
		$adminBody .= "\n";
		$adminBody .= $body;

		//�������ѥ᡼����ʸ�����ꤹ�롣
		$customerBody = "";
		$customerBody .= $userInfo['usr_family_name']." ".$userInfo['usr_first_name']." ��\n";
		$customerBody .= "\n";
		$customerBody .= "**************************************************************************************\n";
		$customerBody .= "��".$siteTitle."�٤���ѥ���ɳ�ǧ�Τ��Τ餻�Ǥ���\n";
		$customerBody .= "�����ˤ����ͤΤ���Ͽ�����Ƥ��Τ餻�������ޤ���\n";
		$customerBody .= "�ѥ���ɤ򤴳�ǧ�ξ塢�ƥ����󤷤Ƥ���������\n";
		$customerBody .= "**************************************************************************************\n";
		$customerBody .= "\n";
		$customerBody .= $body;


		//�������ѥ����ȥ�����ꤹ�롣
		$adminTitle = "[".$siteTitle."] �ѥ���ɳ�ǧ (".$userInfo['usr_family_name']." ".$userInfo['usr_first_name']." ��)";
		//�������ѥ����ȥ�����ꤹ�롣
		$customerTitle = "[".$siteTitle."] �ѥ���ɳ�ǧ�Τ��Τ餻";

		mb_language("Japanese");
		
		$parameter = "-f ".$clientMail;

		//�᡼������
		//�����ͤ��������롣
		$rcd = mb_send_mail($userInfo['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

		//���饤����Ȥ��������롣
		$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$userInfo['usr_e_mail']);

		//�ޥ��������������롣
		foreach($masterMailList as $masterMail){
			$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$userInfo['usr_e_mail']);
		}


		//��å����������ꤹ�롣
		$message .= "��".$siteTitle."�٤���֥ѥ���ɳ�ǧ�Τ��Τ餻�᡼��פ������������ޤ�����";
		$message .= "\n";
		$message .= "�ѥ���ɤ򤴳�ǧ�ξ塢�ƥ����󤷤Ƥ���������";
		$message .= "\n";


		//��λ���̤�ɽ�����롣
		$mode = 3;

	} else {
		//���顼��ͭ����
		$message = "�����Ϥ˸�꤬����ޤ���\n".$message;
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
_Log("[/login_remind/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/login_remind/index.php] mode = '".$mode."'");






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
_SetBreadcrumbs(PAGE_DIR_LOGIN_REMIND, '', PAGE_TITLE_LOGIN_REMIND, 2);
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


_Log("[/login_remind/index.php] end.");
echo $html;

?>
