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

$commonPath = $_SERVER['DOCUMENT_ROOT'] . '/common/';

include_once($commonPath . "include.ini");

_Log("start.");
_Log("\$_POST = '" . print_r($_POST, true) . "'");
_Log("\$_GET = '" . print_r($_GET, true) . "'");
_Log("\$_SERVER = '" . print_r($_SERVER, true) . "'");
_Log("\$_SESSION = '" . print_r($_SESSION, true) . "'");

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
_Log("{HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = $commonPath . 'temp_html/temp_base.txt';
_Log("{HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '" . $tempFile . "'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
    _Log("{HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
    //�����Ǥ��ʤ��ä����
    _Log("{HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
    $html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}

$tempSidebarUserMenuFile = $commonPath . 'temp_html/temp_sidebar_user_menu.txt';
_Log("{HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '" . $tempSidebarUserMenuFile . "'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
    _Log("{HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
    //�����Ǥ��ʤ��ä����
    _Log("{HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

_Log("{HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end

_DB_Open();

//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_BUY;

//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "../../..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"/img/maincontent/pt_buy.jpg\" title=\"\" alt=\"����������Τ���ʧ��\">";
$maincontent .= "</h2>";
$maincontent .= PHP_EOL;

$successFormat = <<<EOT
 <div class="message ">
    %s&nbsp;��<br/>
    <br/>
    �����٤ϡ��ؿ��������Ω�����ˡ����Ω�٤����Ѥ����������꤬�Ȥ��������ޤ���<br/>
    ����ʧ������λ���ޤ����Τǡ����Ƥε�ǽ�Τ����Ѥ���ǽ�Ǥ���<br/>
    <br/>
    �������������������ޤ����顢������Ǥ���&nbsp;<a href="mailto:info@seturitu-kun.com">info@seturitu-kun.com</a>&nbsp;�ޤǥ᡼��Ǥ��䤤��碌����������
</div>
EOT;

$errorFormat = <<<EOT
 <div class="message ">
    %s&nbsp;��<br/>
    <br/>
    �����٤ϡ��ؿ��������Ω�����ˡ����Ω�٤����Ѥ����������꤬�Ȥ��������ޤ���<br/>
    ����ʧ�������ǥ��顼��ȯ�����ޤ�����<br/>
    ���顼�ܺ١�
    <br/>
    %s
    <br/>
    <br/>
    ������Ǥ������嵭���顼�ܺ٤򤴳�ǧ�ξ塢&nbsp;<a href="mailto:info@seturitu-kun.com">info@seturitu-kun.com</a>&nbsp;�ޤǥ᡼��Ǥ��䤤��碌����������
</div>
EOT;

$message = '';

if (!isset($_SESSION['payment_error'])) {
    $message = sprintf($successFormat, $loginInfo['usr_family_name'] . '&nbsp;' . $loginInfo['usr_first_name']);
} else {
    $detail = implode('<br/>', $_SESSION['payment_error']);
    $message = sprintf($errorFormat, $loginInfo['usr_family_name'] . '&nbsp;' . $loginInfo['usr_first_name'], $detail);
    unset($_SESSION['payment_error']);
}

$maincontent .= $message;

//������ץȤ����ꤹ�롣
$script = null;


//�����ɥ�˥塼�����ꤹ�롣
$sidebar = null;

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
_SetBreadcrumbs(PAGE_DIR_BUY, '', PAGE_TITLE_BUY, 3);

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

_Log("end.");
echo $html;
