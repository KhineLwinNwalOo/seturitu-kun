<?php

/**
 * ���٤Ƥ�Ǥ���ץ����ѹ��ڡ���
 * 2014/11/07 Created By Koichi Takahashi
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../common/include.ini");

_LogDelete();
//_LogBackup();
_Log("[/user/index.php] start.");
_Log("[/user/index.php] \$_POST = '" . print_r($_POST, true) . "'");
_Log("[/user/index.php] \$_GET = '" . print_r($_GET, true) . "'");
_Log("[/user/index.php] \$_SERVER = '" . print_r($_SERVER, true) . "'");
_Log("[/user/index.php] \$_SESSION = '" . print_r($_SESSION, true) . "'");

//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
    _Log("[/user/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
    _Log("[/user/index.php] end.");
    //��������̤�ɽ�����롣
    header("Location: " . URL_LOGIN);
    exit;
} else {
    //����������������롣
    $loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

    //�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
    _CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//ǧ�ڥ����å�----------------------------------------------------------------------end


//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- start
_Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../../../common/temp_html/temp_base.txt';
_Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '" . $tempFile . "'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
    _Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
    //�����Ǥ��ʤ��ä����
    _Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
    $html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}

$tempSidebarLoginFile = '../../../common/temp_html/temp_sidebar_login.txt';
_Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) HTML�ƥ�ץ졼�ȥե����� = '" . $tempSidebarLoginFile . "'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
    _Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) ��������");
} else {
    //�����Ǥ��ʤ��ä����
    _Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) �ڼ��ԡ�");
}

$tempSidebarUserMenuFile = '../../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '" . $tempSidebarUserMenuFile . "'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
    _Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
    //�����Ǥ��ʤ��ä����
    _Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

$tempMaincontentUserMenuFile = '../../../common/temp_html/temp_maincontent_user_llc_plan.txt';
_Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�ᥤ�󥳥�ƥ�ĥ桼�����ڡ���) HTML�ƥ�ץ졼�ȥե����� = '" . $tempMaincontentUserMenuFile . "'");

$htmlMaincontentUserMenu = @file_get_contents($tempMaincontentUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlMaincontentUserMenu !== false && !_IsNull($htmlMaincontentUserMenu)) {
    _Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�ᥤ�󥳥�ƥ�ĥ桼�����ڡ���) ��������");
} else {
    //�����Ǥ��ʤ��ä����
    _Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�ᥤ�󥳥�ƥ�ĥ桼�����ڡ���) �ڼ��ԡ�");
}

_Log("[/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end

//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_USER;

// ô���ԥ᡼�륢�ɥ쥹
$masterEmail = MASTER_EMAIL_COMPANY_LLC;

//�ƥ�����
if (false) {
    $masterEmail = "koochang@gmail.com";
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
$message4Cancel = "";
//���顼�ե饰
$errorFlag = false;

//���Ͼ�����Ǽ��������
$info = array();

//�ե�����⡼��
$formMode = XML_NAME_LLC_PLAN;

//�ѥ�᡼������������롣
$xmlName = XML_NAME_LLC_PLAN;                //XML�ե�����̾�����ꤹ�롣
$id = null;

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
//		//XML�ե�����̾
//		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
    //�������å�ID
    $id = (isset($_POST['condition']['_id_']) ? $_POST['condition']['_id_'] : null);

    //����ͤ����ꤹ�롣
    $undeleteOnly4def = false;

    _Log("[/user/index.php] {������桼�������½���} �桼����ID = '" . $loginInfo['usr_user_id'] . "'");
    _Log("[/user/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "'");

    //���¤ˤ�äơ�ɽ������桼������������¤��롣
    if ($loginInfo['usr_auth_id'] == AUTH_NON) {
        _Log("[/user/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "' = '����̵��'");
        _Log("[/user/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
        _Log("[/user/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

        $id = null;
        $undeleteOnly4def = true;

        //��ʬ�Υ桼��������Τ�ɽ�����롣
        //�桼����ID�򸡺����롣
        $id = $loginInfo['usr_user_id'];

        _Log("[/user/index.php] {������桼�������½���} ���桼����ID = '" . $id . "'");
    }

    //�����ͤ�������롣
    $info = $_POST;
    _Log("[/user/index.php] POST = '" . print_r($info, true) . "'");
    //�Хå�����å�����������
    $info = _StripslashesForArray($info);
    _Log("[/user/index.php] POST(�Хå�����å�����������) = '" . print_r($info, true) . "'");

    //��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣���᡼���Ⱦ�ѥ��ʤ�ʸ����������Τǡ�
    $info = _Mb_Convert_KanaForArray($info);
    _Log("[/user/pay/index.php] POST(��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣) = '" . print_r($info, true) . "'");

    $formMode = $info['condition']['_xml_name_'];

    if ($formMode == XML_NAME_LLC_PLAN) {
        $info['condition']['_xml_name_'] = $xmlName;
        $info['condition']['_id_'] = $id;
    }
} elseif ($_SERVER["REQUEST_METHOD"] == 'GET') {
//		//XML�ե�����̾
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
    //�������å�ID
    $id = (isset($_GET['id']) ? $_GET['id'] : null);

    //���ܸ��ڡ���
    $pId = (isset($_GET['p_id']) ? $_GET['p_id'] : null);

    //����ͤ����ꤹ�롣
    $undeleteOnly4def = false;

    _Log("[/user/index.php] {������桼�������½���} �桼����ID = '" . $loginInfo['usr_user_id'] . "'");
    _Log("[/user/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "'");

    //���¤ˤ�äơ�ɽ������桼������������¤��롣
    if ($loginInfo['usr_auth_id'] == AUTH_NON) {
        _Log("[/user/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "' = '����̵��'");
        _Log("[/user/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
        _Log("[/user/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

        $id = null;
        $undeleteOnly4def = true;

        //��ʬ�Υ桼��������Τ�ɽ�����롣
        //�桼����ID�򸡺����롣
        $id = $loginInfo['usr_user_id'];

        _Log("[/user/index.php] {������桼�������½���} ���桼����ID = '" . $id . "'");
    }

    $info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
    $info['condition']['_xml_name_'] = $xmlName;
    $info['condition']['_id_'] = $id;

    //���ܸ��ڡ����򥻥å�������¸���롣
    $_SESSION[SID_USER_FROM_PAGE_ID] = $pId;
}

_Log("[/user/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '" . $_SERVER["REQUEST_METHOD"] . "'");
_Log("[/user/index.php] XML�ե�����̾ = '" . $xmlName . "'");
_Log("[/user/index.php] �ե�����⡼�� = '" . $formMode . "'");
_Log("[/user/index.php] �������å�ID = '" . $id . "'");

//XML���ɤ߹��ࡣ
$xmlFile = "../../../common/form_xml/" . $xmlName . ".xml";
_Log("[/user/index.php] XML�ե����� = '" . $xmlFile . "'");
$xmlList = _GetXml($xmlFile);

_Log("[/user/index.php] XML�ե��������� = '" . print_r($xmlList, true) . "'");

if ($formMode == XML_NAME_LLC_PLAN) {
    //��ǧ�ܥ��󤬲����줿���
    if ($_POST['confirm'] != "") {
        //�����ͥ����å�
        $message .= _CheackInputAll($xmlList, $info);
        //�᡼�륢�ɥ쥹�ν�ʣ�����å�
        if (isset($info['update']['tbl_user']['usr_e_mail']) && !_IsNull($info['update']['tbl_user']['usr_e_mail'])) {
            $condition4email = array();
            $condition4email['usr_e_mail'] = $info['update']['tbl_user']['usr_e_mail'];
            $bufList = _DB_GetList('tbl_user', $condition4email, true, null, 'usr_del_flag', 'usr_user_id');
            if (!_IsNull($bufList)) {
                //�桼����ID������Ѥߤξ�硢������̤��鼫ʬ���ȤΥǡ����������롣
                if (isset($info['update']['tbl_user']['usr_user_id']) && !_IsNull($info['update']['tbl_user']['usr_user_id'])) {
                    unset($bufList[$info['update']['tbl_user']['usr_user_id']]);
                }
                if (count($bufList) > 0) {
                    $message .= "�᡼�륢�ɥ쥹�ϴ�����Ͽ�ѤߤǤ���\n";
                }
            }
        }
        if (_IsNull($message)) {
            //���顼��̵����硢��ǧ���̤�ɽ�����롣
            $mode = 2;
            //$message .= "���������Ƥ��ǧ���ơ��ֹ����ץܥ���򲡤��Ƥ���������";
        } else {
            //���顼��ͭ����
            $message = "�����Ϥ˸�꤬����ޤ���\n" . $message;
            $errorFlag = true;
        }
    } //���ܥ��󤬲����줿���
    elseif ($_POST['back'] != "") {
    } //�����ܥ��󤬲����줿���
    elseif ($_POST['go'] != "") {
        //�᡼����ʸ�ζ�����ʬ�����ꤹ�롣
        $body = _CreateMailAll($xmlList, $info);//�����λ����Ǥϡ�$info�ˡ����ѵ���פ������ͤϺ������Ƥ��롣���᡼��ˤϻȤ��ʤ���
        _Log("[/regist/index.php] �᡼����ʸ(_CreateMailAll) = '" . $body . "'");

        $companyInfo = "--------------------------------------------------------\n";
        $companyInfo .= SITE_TITLE . "\n";
        if (!_IsNull(COMPANY_NAME)) $companyInfo .= COMPANY_NAME . "\n";
        if (!_IsNull(COMPANY_ZIP)) $companyInfo .= COMPANY_ZIP . "\n";
        if (!_IsNull(COMPANY_ADDRESS)) $companyInfo .= COMPANY_ADDRESS . "\n";
        if (!_IsNull(COMPANY_TEL)) $companyInfo .= "TEL��" . COMPANY_TEL . "\n";
        if (!_IsNull(COMPANY_FAX)) $companyInfo .= "FAX��" . COMPANY_FAX . "\n";
        $companyInfo .= "E-mail��" . $clientMail . " \n";
        if (!_IsNull(COMPANY_BUSINESS_HOURS)) $companyInfo .= "�ĶȻ��֡�" . COMPANY_BUSINESS_HOURS . "\n";
        $companyInfo .= "--------------------------------------------------------\n\n";

        $submitInfo = "����������" . date("Yǯn��j�� H��iʬ") . "\n";
        $submitInfo .= $_SERVER["REMOTE_ADDR"] . "\n";

        //�������ѥ᡼����ʸ�����ꤹ�롣
        $customerBody = $info['update']['tbl_user']['usr_family_name'] . " " . $info['update']['tbl_user']['usr_first_name'] . " ��\n";
        $customerBody .= "\n";
        $customerBody .= "**************************************************************************************\n";
        $customerBody .= "��" . $siteTitle . "�٤���Υץ���ѹ��򾵤�ޤ�����\n";
        $customerBody .= "��ǧ�Τ��ᡢ�����ˤ����ͤΤ���Ͽ�����Ƥ��Τ餻�������ޤ���\n";
        $customerBody .= "**************************************************************************************\n";
        $customerBody .= "\n";
        $customerBody .= $body;
        $customerBody .= str_repeat("\n", 2);
        $customerBody .= $companyInfo;
        $customerBody .= $submitInfo;

        _Log('�������ѥ᡼��ʸ��');
        _Log($customerBody);

        //�������ѥ᡼����ʸ�����ꤹ�롣
        $adminBody = "**************************************************************************************\n";
        $adminBody .= "��" . $siteTitle . "�٤���Υץ���ѹ����꤬����ޤ�����\n";
        $adminBody .= "**************************************************************************************\n";
        $adminBody .= "\n";
        $adminBody .= $body;
        $adminBody .= str_repeat("\n", 2);
        $adminBody .= $submitInfo;

        _Log('�������ѥ᡼��ʸ��');
        _Log($adminBody);

        // ��̾�����ꤹ�롣
        $subject = "��{$siteTitle}�� �ץ���ѹ�";

        mb_language("Japanese");

        $parameter = "-f " . $masterEmail;

        // �᡼������
        // �����ͤ��������롣
        mb_send_mail($info['update']['tbl_user']['usr_e_mail'], $subject, $customerBody, "from:" . $masterEmail, $parameter);

        // ô���Ԥ��������롣
        mb_send_mail($masterEmail, $subject, $adminBody, "from:" . $info['update']['tbl_user']['usr_e_mail']);

        // ��λ���̤�ɽ������
        $mode = 3;
    }
}

//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '" . print_r($info, true) . "'");

_Log("[/user/index.php] mode = '" . $mode . "'");

//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "../../..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"/img/maincontent/pt_user_llc_info_plan.jpg\" title=\"\" alt=\"���٤Ƥ�Ǥ���ץ����ѹ�\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

//����URL
$htmlMaincontentUserMenu = str_replace('{base_url}', $basePath, $htmlMaincontentUserMenu);

//���Τ餻
$userNews = null;
$userNews .= "<ul>";
//$userNews .= "<li>�����Ѥ��꤬�Ȥ��������ޤ���������ˤ��Τ餻��ɽ������ޤ���</li>";
//$userNews .= "<li>���ߡ���ȯ�桪��������������������</li>";
//$userNews .= "<li>ˡ̳�ɡ���������ǯ��ǯ�ϤΤ��٤ߤ�12��27����1��4���ޤ�</li>";

//�ץ��ID�ˤ�äơ����Τ餻�����ꤹ�롣
switch ($loginInfo['usr_plan_id']) {
    case MST_PLAN_ID_NORMAL://�̾�ץ��
        $userNews .= "<li>�����Ѥ��꤬�Ȥ��������ޤ���������ˤ��Τ餻��ɽ������ޤ���</li>";
        break;
    case MST_PLAN_ID_STANDARD://����������ɥѡ��ȥʡ��ץ��
    case MST_PLAN_ID_PLATINUM://�ץ���ʥѡ��ȥʡ��ץ��
//		$userNews .= "<li>��OEM���٤����ѤΥ���������ɡ�������ɥѡ��ȥʡ��ץ�󤪵��ͤˤ��Τ餻��<br />�̾�ץ��(����������ɡ�������ɥѡ��ȥʡ��ץ��ʳ����̾�Τ������ѥץ��)�Υ����ƥ��������2,800��(1,000��OFF)�ˤʤ�ޤ�����</li>";
        $userNews .= "<li>��OEM���٤����ѤΥ���������ɡ�������ɥѡ��ȥʡ��ץ�󤪵��ͤˤ��Τ餻��<br />�����ƥ��������2,800��(1,000��OFF)�ˤʤ�ޤ�����</li>";
        break;
}
$userNews .= "</ul>";
$htmlMaincontentUserMenu = str_replace('{user_news}', $userNews, $htmlMaincontentUserMenu);

//�����ѥ��ơ�����
$userStatus = _GetUserStatusHtml($loginInfo['usr_user_id']);

$htmlMaincontentUserMenu = str_replace('{user_status}', $userStatus, $htmlMaincontentUserMenu);

//������Ұ���
$userCompanyRelation = _GetUserCompanyRelationHtml($loginInfo['usr_user_id'], MST_COMPANY_TYPE_ID_CMP);
$htmlMaincontentUserMenu = str_replace('{company_list}', $userCompanyRelation, $htmlMaincontentUserMenu);

//��Ʊ��Ұ���
$userCompanyRelation = _GetUserCompanyRelationHtml($loginInfo['usr_user_id'], MST_COMPANY_TYPE_ID_LLC);
$htmlMaincontentUserMenu = str_replace('{llc_list}', $userCompanyRelation, $htmlMaincontentUserMenu);

//��Ͽ���������
//����
$userInfoUpdate = _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

$htmlMaincontentUserMenu = str_replace('{user_info_update}', $userInfoUpdate, $htmlMaincontentUserMenu);

$maincontent .= $htmlMaincontentUserMenu;

//������ץȤ����ꤹ�롣
$script = null;

//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
    $script .= "<style type=\"text/css\">";
    $script .= "\n";
    $script .= "<!--";
    $script .= "\n";

    $script .= "#mc_user_news";
    $script .= "\n";
    $script .= ",#mc_user_status";
    $script .= "\n";
    $script .= ",#mc_user_menu";
    $script .= "\n";
    $script .= ",#mc_ui_update";
    $script .= "\n";
    $script .= ",#mc_ui_end_update";
    $script .= "\n";
    $script .= "{display:none;}";
    $script .= "\n";

    $script .= "-->";
    $script .= "\n";
    $script .= "</style>";
    $script .= "\n";
}

//�����ɥ�˥塼�����ꤹ�롣
$sidebar = null;

//�����󤷤Ƥ��뤫��
if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
    //����URL
    $htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
    //������桼����̾
    $htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
    //���ߤ����Ͼ���
    $htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);

    $sidebar .= $htmlSidebarUserMenu;
} else {
    //����URL
    $htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);

    $sidebar .= $htmlSidebarLogin;
}

//�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_LLC, '', PAGE_TITLE_LLC, 3);
_SetBreadcrumbs(PAGE_DIR_LLC_PLAN, '', PAGE_TITLE_LLC_PLAN, 4);
//�ѥ󤯤��ꥹ�Ȥ�������롣
$breadcrumbs = _GetBreadcrumbs();

//WOOROM�եå�������
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=" . $_SERVER['SERVER_NAME'] . "&php_self=" . $_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
    $wooromFooter = null;
}

//�ƥ�ץ졼�Ȥ��Խ����롣(ɬ�ײս���ִ����롣)
//�����ȥ�
if (!_IsNull($title)) $title = "[" . $title . "] ";
$title = $siteTitle . " " . $title;
$html = str_replace('{title}', $title, $html);
//�᥿����
$html = str_replace('{keywords}', PAGE_KEYWORDS_HOME, $html);
$html = str_replace('{description}', PAGE_DESCRIPTION_HOME, $html);
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

_Log("[/user/index.php] end.");
echo $html;
