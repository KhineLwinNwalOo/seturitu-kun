<?php

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");

_LogDelete();
_Log("[/user/option_service/index.php] start.");
_Log("[/user/option_service/index.php] \$_POST = '" . print_r($_POST, true) . "'");
_Log("[/user/option_service/index.php] \$_GET = '" . print_r($_GET, true) . "'");
_Log("[/user/option_service/index.php] \$_SERVER = '" . print_r($_SERVER, true) . "'");
_Log("[/user/option_service/index.php] \$_SESSION = '" . print_r($_SESSION, true) . "'");

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
_Log("[/user/option_service/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../../common/temp_html/temp_base.txt';
_Log("[/user/option_service/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '" . $tempFile . "'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
    _Log("[/user/option_service/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
    //�����Ǥ��ʤ��ä����
    _Log("[/user/option_service/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
    $html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}

$tempSidebarUserMenuFile = '../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/option_service/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '" . $tempSidebarUserMenuFile . "'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
    _Log("[/user/option_service/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
    //�����Ǥ��ʤ��ä����
    _Log("[/user/option_service/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

_Log("[/user/option_service/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end

//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_OPTION_SERVICE;

//���饤������ͥ᡼�륢�ɥ쥹
$clientMail = COMPANY_E_MAIL;
//�ޥ������ѥ᡼�륢�ɥ쥹
$masterMailList = $_COMPANY_MASTER_MAIL_LIST;

//�ƥ�����
if (false) {
//if (true) {
    //���饤������ͥ᡼�륢�ɥ쥹
    $clientMail = "takahashi@woorom.com";
    //�ޥ������ѥ᡼�륢�ɥ쥹
    //��,�פǤ����ä���������ɲä��Ʋ�������
    $masterMailList = array("takahashi@woorom.com", "takahashi@woorom.com");
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

_Log("[/user/option_service/index.php] \$_GET(�ͤ��ؤ���) = '" . print_r($_GET, true) . "'");

//�ѥ�᡼������������롣
$xmlName = XML_NAME_OPTION_SERVICE;//XML�ե�����̾�����ꤹ�롣
$id = null;
$step = null;
$stepId = null;
switch ($requestMethod) {
    case 'POST':
        //�������å�ID
        $id = (isset($_POST['condition']['_id_']) ? $_POST['condition']['_id_'] : null);

        _Log("[/user/option_service/index.php] {������桼�������½���} �桼����ID = '" . $loginInfo['usr_user_id'] . "'");
        _Log("[/user/option_service/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "'");

        //���¤ˤ�äơ�ɽ������桼������������¤��롣
        switch ($loginInfo['usr_auth_id']) {
            case AUTH_NON://����̵��

                _Log("[/user/option_service/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "' = '����̵��'");
                _Log("[/user/option_service/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
                _Log("[/user/option_service/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

                $id = null;

                //��ʬ�Υ桼��������Τ�ɽ�����롣
                //�桼����ID�򸡺����롣
                $id = $loginInfo['usr_user_id'];

                _Log("[/user/option_service/index.php] {������桼�������½���} ���桼����ID = '" . $id . "'");
                break;
        }


        //�����ͤ�������롣
        $info = $_POST;
        _Log("[/user/option_service/index.php] POST = '" . print_r($info, true) . "'");
        //�Хå�����å�����������
        $info = _StripslashesForArray($info);
        _Log("[/user/option_service/index.php] POST(�Хå�����å�����������) = '" . print_r($info, true) . "'");

        //��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣���᡼���Ⱦ�ѥ��ʤ�ʸ����������Τǡ�
        $info = _Mb_Convert_KanaForArray($info);
        _Log("[/user/option_service/index.php] POST(��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣) = '" . print_r($info, true) . "'");

        include_once '../../common/constant.php';

        $adminTitle = '[' . SITE_TITLE . '] ���ץ���󥵡��ӥ��Τ����Ѥ��꤬�Ȥ��������ޤ�';
        $adminBody = <<<EOT
**************************************************************************************
�����٤ϡ���%s�٤ˤƥ��ץ���󥵡��ӥ��Τ�Ϣ�����������ˤ��꤬�Ȥ��������ޤ���
��ǧ�Τ��ᡢ�����ˤ����ͤ����򤵤줿���Ƥ��Τ餻�������ޤ���
���ץ���󥵡��ӥ��˴ؤ��Ƥξܺ٤ϡ�ô���Ԥ��餴Ϣ�����Ƥ���������礬�������ޤ���
**************************************************************************************

%s

--------------------------------------------------------
�������WOOROM.
��106-0032
����Թ���ϻ����5-16-50 ϻ���ڥǥ塼�ץ�å���M's407
TEL��03-3586-1523
FAX��03-3586-1521
E-mail��info@seturitu-kun.com
�ĶȻ��֡�10:00���19:00
--------------------------------------------------------
EOT;

        $data = array();
        foreach ($info['option_service'] as $i => $optionService) {
            $str = "��{$optionServices[$i]['name']}��" . PHP_EOL . $optionServices[$i]['options'][$optionService['option']];
            if (!empty($optionServices[$i]['checkbox_options']) && !empty($optionService['checkbox_options'])) {
                $options = array();
                foreach ($optionService['checkbox_options'] as $checkboxOption) {
                    $options[] = $optionServices[$i]['checkbox_options'][$checkboxOption];
                }
                if (!empty($options)) {
                    $str .= PHP_EOL . '( ' . implode(', ', $options) . ' )';
                }
            }
            $data[] = $str;
        }

        $adminBody = sprintf($adminBody, SITE_TITLE, implode(str_repeat(PHP_EOL, 2), $data));

        mb_language("Japanese");
        $param = "-f " . $clientMail;

        mb_send_mail($loginInfo['usr_e_mail'], $adminTitle, $adminBody, "from:{$clientMail}", $param);
        mb_send_mail($clientMail, $adminTitle, $adminBody, "from:{$clientMail}", $param);

        break;
    case 'GET':
//		//XML�ե�����̾
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
        //�������å�ID
        $id = (isset($_GET['id']) ? $_GET['id'] : null);
//		//���ƥå�ID
//		$step = (isset($_GET['step'])?$_GET['step']:null);

        //���ܸ��ڡ���
        $pId = (isset($_GET['p_id']) ? $_GET['p_id'] : null);


        //����ͤ����ꤹ�롣
        $undeleteOnly4def = false;


        _Log("[/user/option_service/index.php] {������桼�������½���} �桼����ID = '" . $loginInfo['usr_user_id'] . "'");
        _Log("[/user/option_service/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "'");


        //���¤ˤ�äơ�ɽ������桼������������¤��롣
        switch ($loginInfo['usr_auth_id']) {
            case AUTH_NON://����̵��

                _Log("[/user/option_service/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "' = '����̵��'");
                _Log("[/user/option_service/index.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
                _Log("[/user/option_service/index.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

                $id = null;
                $undeleteOnly4def = true;

                //��ʬ�Υ桼��������Τ�ɽ�����롣
                //�桼����ID�򸡺����롣
                $id = $loginInfo['usr_user_id'];


                _Log("[/user/option_service/index.php] {������桼�������½���} ���桼����ID = '" . $id . "'");

                break;
        }

        $info['update'] = null;

        //XML�ե�����̾���������å�ID�����ͤ��ɲä��롣
        $info['condition']['_xml_name_'] = $xmlName;
        $info['condition']['_id_'] = $id;

        //���ܸ��ڡ����򥻥å�������¸���롣
        $_SESSION[SID_PAY_FROM_PAGE_ID] = $pId;

        break;
}

_Log("[/user/option_service/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '" . $_SERVER["REQUEST_METHOD"] . "'");
_Log("[/user/option_service/index.php] XML�ե�����̾ = '" . $xmlName . "'");
_Log("[/user/option_service/index.php] �������å�ID = '" . $id . "'");


//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/option_service/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '" . print_r($info, true) . "'");

_Log("[/user/option_service/index.php] mode = '" . $mode . "'");

//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "../..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../img/maincontent/pt_option.jpg\" title=\"\" alt=\"���ץ���󥵡��ӥ�\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

$includeContents = _get_include_contents('./_form.php');
$maincontent .= $includeContents;

//������ץȤ����ꤹ�롣
$script = null;

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
$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);

$sidebar .= $htmlSidebarUserMenu;


//�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_OPTION_SERVICE, '', PAGE_TITLE_OPTION_SERVICE, 3);
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


_Log("[/user/option_service/index.php] end.");
echo $html;
