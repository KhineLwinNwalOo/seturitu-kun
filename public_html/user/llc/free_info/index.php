<?php
/*
 * [���������Ω.JP �ġ���]
 * ��Ʊ�����ΩLLC������Ͽ�ڡ���
 *
 * ��������2008/12/01	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

$commonPath = $_SERVER['DOCUMENT_ROOT'] . '/common/';

include_once($commonPath . "include.ini");

_LogDelete();
//_LogBackup();
_Log("[/user/llc/free_info/index.php] start.");
_Log("[/user/llc/free_info/index.php] \$_POST = '" . print_r($_POST, true) . "'");
_Log("[/user/llc/free_info/index.php] \$_GET = '" . print_r($_GET, true) . "'");
_Log("[/user/llc/free_info/index.php] \$_SERVER = '" . print_r($_SERVER, true) . "'");
_Log("[/user/llc/free_info/index.php] \$_SESSION = '" . print_r($_SESSION, true) . "'");

//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = array();
$loginInfo['usr_user_id'] = NOLOGIN_USER_ID;
$loginInfo['usr_auth_id'] = AUTH_NON;
//ǧ�ڥ����å�----------------------------------------------------------------------end

//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- start
_Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = $commonPath . 'temp_html/temp_base.txt';
_Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '" . $tempFile . "'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
    _Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
    //�����Ǥ��ʤ��ä����
    _Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
    $html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}

$tempSidebarUserMenuFile = $commonPath . 'temp_html/temp_sidebar_login.txt';
_Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '" . $tempSidebarUserMenuFile . "'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
    _Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
    //�����Ǥ��ʤ��ä����
    _Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

_Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end

//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_LLC_INFO;

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

_Log("[/user/llc/free_info/index.php] \$_GET(�ͤ��ؤ���) = '" . print_r($_GET, true) . "'");

//�ѥ�᡼������������롣
$xmlName = XML_NAME_LLC;//XML�ե�����̾�����ꤹ�롣
$id = null;
$step = null;
$stepId = null;
switch ($requestMethod) {
    case 'POST':
        //�������å�ID
        $id = (isset($_POST['condition']['_id_']) ? $_POST['condition']['_id_'] : null);
        //���ƥå�ID
        $step = (isset($_POST['condition']['_step_']) ? $_POST['condition']['_step_'] : null);

        _Log("[/user/llc/free_info/index.php] {������桼�������½���} �桼����ID = '" . $loginInfo['usr_user_id'] . "'");
        _Log("[/user/llc/free_info/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "'");

        //���¤ˤ�äơ�ɽ������桼������������¤��롣
        switch ($loginInfo['usr_auth_id']) {
            case AUTH_NON://����̵��

                _Log("[/user/llc/free_info/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "' = '����̵��'");
                _Log("[/user/llc/free_info/index.php] {������桼�������½���} ����ʬ�ι�Ʊ�����Ω����Τ�ɽ�����롣");
                _Log("[/user/llc/free_info/index.php] {������桼�������½���} �����ID�򸡺����롣");

                $id = null;

                //��ʬ�ι�Ʊ�����Ω����Τ�ɽ�����롣
                //���ID�򸡺����롣
                $id = _GetRelationLlcId($loginInfo['usr_user_id']);

                _Log("[/user/llc/free_info/index.php] {������桼�������½���} �����ID = '" . $id . "'");
                break;
        }

        //�����ͤ�������롣
        $info = $_POST;
        _Log("[/user/llc/free_info/index.php] POST = '" . print_r($info, true) . "'");
        //�Хå�����å�����������
        $info = _StripslashesForArray($info);
        _Log("[/user/llc/free_info/index.php] POST(�Хå�����å�����������) = '" . print_r($info, true) . "'");

        //XML�ե�����̾���������å�ID���񤭤��롣
        $info['condition']['_xml_name_'] = $xmlName;
        $info['condition']['_id_'] = $id;

        break;
    case 'GET':
        //�������å�ID
        $id = (isset($_GET['id']) ? $_GET['id'] : null);
        //���ƥå�ID
        $step = (isset($_GET['step']) ? $_GET['step'] : null);

        //���ܸ��ڡ���
        $pId = (isset($_GET['p_id']) ? $_GET['p_id'] : null);

        //�괾������
        if (isset($_GET['article'])) {
            $step = 9000;
        }

        //����ͤ����ꤹ�롣
        $undeleteOnly4def = false;

        _Log("[/user/llc/free_info/index.php] {������桼�������½���} �桼����ID = '" . $loginInfo['usr_user_id'] . "'");
        _Log("[/user/llc/free_info/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "'");

        //���¤ˤ�äơ�ɽ������桼������������¤��롣
        switch ($loginInfo['usr_auth_id']) {
            case AUTH_NON://����̵��
                _Log("[/user/llc/free_info/index.php] {������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "' = '����̵��'");
                _Log("[/user/llc/free_info/index.php] {������桼�������½���} ����ʬ�ι�Ʊ�����Ω����Τ�ɽ�����롣");
                _Log("[/user/llc/free_info/index.php] {������桼�������½���} �����ID�򸡺����롣");

                $id = null;
                $undeleteOnly4def = true;

                //��ʬ�ι�Ʊ�����Ω����Τ�ɽ�����롣
                //���ID�򸡺����롣
                $id = _GetRelationLlcId($loginInfo['usr_user_id']);

                _Log("[/user/llc/free_info/index.php] {������桼�������½���} �����ID = '" . $id . "'");

                break;
        }

        $info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);

        //XML�ե�����̾���������å�ID�����ͤ��ɲä��롣
        $info['condition']['_xml_name_'] = $xmlName;
        $info['condition']['_id_'] = $id;

        break;
}

_Log("[/user/llc/free_info/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '" . $_SERVER["REQUEST_METHOD"] . "'");
_Log("[/user/llc/free_info/index.php] XML�ե�����̾ = '" . $xmlName . "'");
_Log("[/user/llc/free_info/index.php] �������å�ID = '" . $id . "'");

//��ҥ�����ID="��Ʊ���"�����ꤹ�롣
$info['update']['tbl_company']['cmp_company_type_id'] = MST_COMPANY_TYPE_ID_LLC;
//�桼��������(���������)�����ꤹ�롣��DB�����˻��Ѥ��롣
$info['update']['tbl_user'] = $loginInfo;

switch ($step) {
    case 2:
        //��Ʊ�����Ω����[���ܶ⡦����ǯ��]
        $xmlName = XML_NAME_LLC_CAPITAL;
        $stepId = "cmpn_capital";
        break;
    case 3:
        //��Ʊ�����Ω����[��Ź�����]
        $xmlName = XML_NAME_LLC_ADDRESS;
        $stepId = "cmpn_address";
        break;
    case 9000:
        //��Ʊ�����Ω����[�괾����]
        $xmlName = XML_NAME_LLC_ARTICLE;
        $stepId = "cmpn_article";
        break;
    case 4:
        //��Ʊ�����Ω����[���Ȥ���Ū]
        $xmlName = XML_NAME_LLC_PURPOSE;
        $stepId = "cmpn_purpose";
        break;
    case 5:
        //��Ʊ�����Ω����[ȯ����]
        $xmlName = XML_NAME_LLC_PROMOTER;
        $stepId = "cmpn_promoter";
        break;
    case 6:
        //��Ʊ�����Ω����[�л��]
        //���л��ϡ�XML�����Υե�����ǤϤʤ���ľ�ܽ񤭽Ф���
        $xmlName = XML_NAME_LLC_PROMOTER_INVESTMENT;
        $stepId = "cmpn_promoter_investment";
        break;
    case 7:
        //��Ʊ�����Ω����[�������Ƴ�ǧ]
        $xmlName = XML_NAME_LLC_ALL;
        $stepId = "cmpn_confirm";
        break;
    default:
        //��Ʊ�����Ω����[����(���̾)]
        $xmlName = XML_NAME_LLC_NAME;
        $stepId = "cmpn_name";
        $step = 1;
        break;
}
$info['condition']['_step_'] = $step;

_Log("[/user/llc/free_info/index.php] ���ƥå�ID = '" . $step . "'");
_Log("[/user/llc/free_info/index.php] XML�ե�����̾(���ƥå�ID) = '" . $xmlName . "'");
$_SESSION['llc_free_info_step'] = $step;

//���ܥ��󤬲����줿��碪�������ܤ���Τǡ�XML���ɤ߹��ޤʤ���
//if ($_POST['back'] != "") $xmlName = null;
if (!empty($_POST['back']) && $step == 7) {
    $xmlName = null;
}

$xmlList = null;
if (!_IsNull($xmlName)) {
    //XML���ɤ߹��ࡣ
    $xmlFile = $commonPath . "form_xml/" . $xmlName . ".xml";
    _Log("[/user/llc/free_info/index.php] XML�ե����� = '" . $xmlFile . "'");
    $xmlList = _GetXml($xmlFile);
    _Log("[/user/llc/free_info/index.php] XML�ե��������� = '" . print_r($xmlList, true) . "'");

    switch ($xmlName) {
        case XML_NAME_LLC_ALL:
            //��Ʊ�����Ω����[�������Ƴ�ǧ]
            //���Ƥ�XML���ɤ߹��ࡣ

            //��Ʊ�����Ω����[����(���̾)]
            $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_LLC_NAME . ".xml";
            _Log("[/user/llc/free_info/index.php] XML�ե����� = '" . $bufXmlFile . "'");
            $bufXmlList = _GetXml($bufXmlFile);
            $xmlList['tbl_company_name'] = $bufXmlList['tbl_company'];

            //��Ʊ�����Ω����[���ܶ⡦����ǯ��]
            $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_LLC_CAPITAL . ".xml";
            _Log("[/user/llc/free_info/index.php] XML�ե����� = '" . $bufXmlFile . "'");
            $bufXmlList = _GetXml($bufXmlFile);
            $xmlList['tbl_company_capital'] = $bufXmlList['tbl_company'];

            //��Ʊ�����Ω����[��Ź�����]
            $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_LLC_ADDRESS . ".xml";
            _Log("[/user/llc/free_info/index.php] XML�ե����� = '" . $bufXmlFile . "'");
            $bufXmlList = _GetXml($bufXmlFile);
            $xmlList['tbl_company_address'] = $bufXmlList['tbl_company'];

            //��Ʊ�����Ω����[���Ȥ���Ū]
            $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_LLC_PURPOSE . ".xml";
            _Log("[/user/llc/free_info/index.php] XML�ե����� = '" . $bufXmlFile . "'");
            $bufXmlList = _GetXml($bufXmlFile);
            $xmlList['tbl_company_purpose'] = $bufXmlList['tbl_company_purpose'];

            //��Ʊ�����Ω����[ȯ����]
            $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_LLC_PROMOTER . ".xml";
            _Log("[/user/llc/free_info/index.php] XML�ե����� = '" . $bufXmlFile . "'");
            $bufXmlList = _GetXml($bufXmlFile);
            $xmlList['tbl_company_promoter'] = $bufXmlList['tbl_company_promoter'];

            //��Ʊ�����Ω����[�л��]
            $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_LLC_PROMOTER_INVESTMENT . ".xml";
            _Log("[/user/llc/free_info/index.php] XML�ե����� = '" . $bufXmlFile . "'");
            $bufXmlList = _GetXml($bufXmlFile);
            $xmlList['tbl_company_promoter_investment'] = $bufXmlList['tbl_company_promoter_investment'];

            $info['update']['tbl_company_name'] = $info['update']['tbl_company'];
            $info['update']['tbl_company_capital'] = $info['update']['tbl_company'];
            $info['update']['tbl_company_address'] = $info['update']['tbl_company'];
            $info['update']['tbl_company_article'] = $info['update']['tbl_company'];
            $info['update']['tbl_company_board_base'] = $info['update']['tbl_company'];

            _Log("[/user/llc/free_info/index.php] XML�ե���������(��XML�ޡ�����) = '" . print_r($xmlList, true) . "'");
            _Log("[/user/llc/free_info/index.php] ��Ʊ�����Ω����(��XML�ޡ�����) = '" . print_r($info, true) . "'");

            $mode = 2;
            break;
    }
}

//��¸�ܥ��󡢼��إܥ��󤬲����줿���
if ($_POST['go'] != "" || $_POST['next'] != "") {
    //�����ͥ����å�
    switch ($xmlName) {
        case XML_NAME_LLC_PROMOTER:
            //��Ʊ�����Ω����[ȯ����]
            $message .= _CheackInput4LlcPromoter($xmlList, $info);
            break;
        default:
            $message .= _CheackInputAll($xmlList, $info);
            break;
    }
    switch ($xmlName) {
        case XML_NAME_LLC_PURPOSE:
            //��Ʊ�����Ω����[���Ȥ���Ū]
            $message .= _CheackInput4CompanyPurpose($xmlList, $info);
            break;
        case XML_NAME_LLC_PROMOTER_INVESTMENT:
            //��Ʊ�����Ω����[�л��]
            $message .= _CheackInput4CompanyPromoterInvestment($xmlList, $info);

            //�л��Υ����å��򤹤롣
            $investmentErrorFlag = false;
            $bufTabindex = null;
            $buf = _CreateTableInput4LlcPromoterInvestment($mode, $xmlList, $info, $bufTabindex, $investmentErrorFlag);
            if ($investmentErrorFlag) {
                $message .= "���ܶ�Ƚл�⤬��äƤ��ޤ���\n";
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
        }
    } else {
        //���顼��ͭ����
        $message = "�����Ϥ˸�꤬����ޤ���\n" . $message;
        $errorFlag = true;
    }
}

$addHref = null;
switch ($loginInfo['usr_auth_id']) {
    case AUTH_NON://����̵��
        break;
    default:
        if (!_IsNull($id)) {
            $addHref = "&amp;id=" . $id;
        }
        break;
}

//���إܥ��󤬲����줿���
if ($_POST['next'] != "") {
    if (!$errorFlag) {
        switch ($xmlName) {
            case XML_NAME_LLC_ARTICLE:
                //��Ʊ�����Ω����[�괾����]
                //���Υڡ�����ɽ�����롣
                header("Location: ../article/");
                break;
            default:
                //���Υڡ�����ɽ�����롣
                $step++;
                header("Location: ./?step=" . $step . $addHref);
                exit;
                break;
        }
    }
} //���ܥ��󤬲����줿���
elseif ($_POST['back'] != "") {
    //���Υڡ�����ɽ�����롣
    $step--;
    header("Location: ./?step=" . $step . $addHref);
    exit;
} elseif (!empty($_POST['go'])) {
    header('Location: /regist/');
    exit;
}

//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/user/llc/free_info/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '" . print_r($info, true) . "'");
_Log("[/user/llc/free_info/index.php] mode = '" . $mode . "'");

switch ($step) {
    case 9000:
        //��Ʊ�����Ω����[�괾����]
        $pageTitle = PAGE_TITLE_LLC_INFO_ARTICLE;
        break;
    default:
        break;
}

//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "../../..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"/img/maincontent/pt_user_llc_info.jpg\" title=\"\" alt=\"��Ʊ�����ΩLLC������Ͽ\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

//���֥�˥塼�����ꤹ�롣
switch ($step) {
    case 9000:
        //��Ʊ�����Ω����[�괾����]
        break;
    default:
        $maincontent .= "<ul id=\"cmpn\">";
        $maincontent .= "\n";
        $maincontent .= "<li id=\"cmpn_name\">";
        $maincontent .= "<a href=\"?step=1" . $addHref . "\">����<br />(���̾)</a>";
        $maincontent .= "</li>";
        $maincontent .= "\n";
        $maincontent .= "<li id=\"cmpn_capital\">";
        $maincontent .= "<a href=\"?step=2" . $addHref . "\">���ܶ�<br />����ǯ��</a>";
        $maincontent .= "</li>";
        $maincontent .= "\n";
        $maincontent .= "<li id=\"cmpn_address\">";
        $maincontent .= "<a href=\"?step=3" . $addHref . "\">��Ź<br />�����</a>";
        $maincontent .= "</li>";
        $maincontent .= "\n";
        $maincontent .= "<li id=\"cmpn_purpose\">";
        $maincontent .= "<a href=\"?step=4" . $addHref . "\">���Ȥ�<br />��Ū</a>";
        $maincontent .= "</li>";
        $maincontent .= "\n";
        $maincontent .= "<li id=\"cmpn_promoter\">";
        $maincontent .= "<a href=\"?step=5" . $addHref . "\">�Ұ�<br />(�л��)</a>";
        $maincontent .= "</li>";
        $maincontent .= "\n";
        $maincontent .= "<li id=\"cmpn_promoter_investment\">";
        $maincontent .= "<a href=\"?step=6" . $addHref . "\">�л��</a>";
        $maincontent .= "</li>";
        $maincontent .= "\n";
        $maincontent .= "<li id=\"cmpn_confirm\">";
        $maincontent .= "<a href=\"?step=7" . $addHref . "\">��������<br />��ǧ</a>";
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
        break;
}

switch ($xmlName) {
    case XML_NAME_LLC_ALL:
        //��Ʊ�����Ω����[�������Ƴ�ǧ]
        $maincontent .= "<!--{_message_}-->";
        $maincontent .= "\n";
        break;
}

$info['nologin_input'] = true;
$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//������ץȤ����ꤹ�롣
$script = null;

$addStyle = null;

switch ($xmlName) {
    case XML_NAME_LLC_CAPITAL:
        //��Ʊ�����Ω����[���ܶ⡦����ǯ��]

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
        $script .= "$('res_month_1').set('html', res);";
        $script .= "\n";
        $script .= "$('res_month_1').setStyle('background-color', bgColor);";
        $script .= "\n";
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
            $script .= "var foundDateDeadline = " . $deadlineYmd . ";";
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
            $script .= "var resMessageDeadline = '(" . $deadlineYmdMessage . "�ʹߤ����ꤷ�Ƥ���������)';";
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
            $script .= "resMessage = '��Ωǯ�����ϡ��������" . FOUND_DAY_DEADLINE . "����ʹߤ����դ����Ϥ��Ƥ���������<br />(������Ω�Ѥߤξ��ϡ����Τޤޤ��ʤߤ���������)<br /><br />';";
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
    case XML_NAME_LLC_PROMOTER:
        //��Ʊ�����Ω����[ȯ����]
        //������ץȤ����ꤹ�롣
        $script .= "<script language=\"javascript\" src=\"" . $basePath . "/common/js/personal_type/personal_type.js\" type=\"text/javascript\"></script>";
        $script .= "\n";
        break;
    case XML_NAME_LLC_PROMOTER_INVESTMENT:
        //��Ʊ�����Ω����[�л��]
        $buf = _CreateTableInput4LlcPromoterInvestment($mode, $xmlList, $info, $tabindex);
        $maincontent = str_replace('{form_info_llc_promoter_investment}', $buf, $maincontent);
        break;
    case XML_NAME_LLC_ALL:
        //��Ʊ�����Ω����[�������Ƴ�ǧ]
        $allErrorFlag = false;
        $maincontent = str_replace('{form_info_cmp_board_name}', $buf, $maincontent);

        //��Ʊ�����Ω����[�л��]
        $buf = _CreateTableInput4LlcPromoterInvestment($mode, $xmlList, $info, $tabindex);
        $maincontent = str_replace('{form_info_llc_promoter_investment}', $buf, $maincontent);
        if (preg_match('/class=\\"requiredMessage\\"/', $buf)) {
            $allErrorFlag = true;
        }

        foreach ($xmlList as $xKey => $xmlInfo) {
            $repKey = null;
            switch ($xKey) {
                case 'tbl_company_name';
                    $repKey = '<!--{_form_info_llc_name_}-->';
                    break;
                case 'tbl_company_capital';
                    $repKey = '<!--{_form_info_llc_capital_}-->';
                    break;
                case 'tbl_company_address';
                    $repKey = '<!--{_form_info_llc_address_}-->';
                    break;
                case 'tbl_company_purpose';
                    $repKey = '<!--{_form_info_llc_purpose_}-->';
                    break;
                case 'tbl_company_board_base';
                    $repKey = '<!--{_form_info_llc_board_base_}-->';
                    break;
                case 'tbl_company_board';
                    $repKey = '<!--{_form_info_llc_board_name_}-->';
                    break;
                case 'tbl_company_promoter';
                    $repKey = '<!--{_form_info_llc_promoter_}-->';
                    break;
                case 'tbl_company_promoter_investment';
                    $repKey = '<!--{_form_info_llc_promoter_investment_}-->';
                    break;
                default:
                    continue 2;
            }

            $bufXmlList = array();
            $bufXmlList[$xKey] = $xmlInfo;
            //�����ͥ����å�
            $bufMessage = null;
            switch ($xKey) {
                case 'tbl_company_promoter':
                    //���������Ω����[ȯ����]
                    $bufMessage .= _CheackInput4LlcPromoter($bufXmlList, $info);
                    break;
                default:
                    $bufMessage .= _CheackInputAll($bufXmlList, $info);
                    break;
            }
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
            $buf .= "\n";
            $buf .= "</div>";
        }
        $maincontent = str_replace('<!--{_message_}-->', $buf, $maincontent);

        //��ǧ�Ѳ��̤Ǥ���ɽ���ˤ�����ܤ���ɽ���ˤ��롣�ֺ������׹��ܤʤɡ�
        $addStyle .= ".show_confirm {display: none;}";

        //������ץȤ����ꤹ�롣
        $script .= "<script language=\"javascript\" src=\"" . $basePath . "/common/js/personal_type/personal_type_4_confirm.js\" type=\"text/javascript\"></script>";
        $script .= "\n";

        break;
    default:
        break;
}

$script .= "<style type=\"text/css\">";
$script .= "\n";
$script .= "<!--";
$script .= "\n";
$script .= "ul#cmpn li#" . $stepId . " a:link";
$script .= ",ul#cmpn li#" . $stepId . " a:visited";
$script .= "\n";
$script .= "{height: 32px;color: #3176af;border-bottom: 3px solid #76b0df;}";
$script .= "\n";
$script .= $addStyle;
$script .= "\n";
$script .= "-->";
$script .= "\n";
$script .= "</style>";
$script .= "\n";

$script2 = <<<EOT
<script type="text/javascript">
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

function submitBack() {
    jQuery("<input>").attr("type", "hidden").attr("name", "back").attr("value", "1").appendTo("#frmUpdate");
    jQuery("#frmUpdate").submit();
}
function submitGo() {
    jQuery("<input>").attr("type", "hidden").attr("name", "go").attr("value", "1").appendTo("#frmUpdate");
    jQuery("#frmUpdate").submit();
}
function submitNext() {
    jQuery("<input>").attr("type", "hidden").attr("name", "next").attr("value", "1").appendTo("#frmUpdate");
    jQuery("#frmUpdate").submit();
}

jQuery(function() {
    MM_preloadImages("/img/free_info/btn_enter_ov.png", "/img/free_info/btn_next_ov.png");
});
</script>
EOT;

$script .= $script2;

//������ʸ�Ϥ����ꤹ�롣
$tempExpFile = null;
switch ($xmlName) {
    case XML_NAME_LLC_NAME:
        //��Ʊ�����Ω����[����(���̾)]
        $tempExpFile = $commonPath . 'temp_html/temp_maincontent_llc_exp_01.txt';
        break;
    case XML_NAME_LLC_CAPITAL:
        //��Ʊ�����Ω����[���ܶ⡦����ǯ��]
        $tempExpFile = $commonPath . 'temp_html/temp_maincontent_llc_exp_02.txt';
        break;
    case XML_NAME_LLC_PURPOSE:
        //��Ʊ�����Ω����[���Ȥ���Ū]
        $tempExpFile = $commonPath . 'temp_html/temp_maincontent_llc_exp_03.txt';
        break;
    case XML_NAME_LLC_PROMOTER:
        //��Ʊ�����Ω����[ȯ����]
        $tempExpFile = $commonPath . 'temp_html/temp_maincontent_llc_exp_04.txt';
        break;
    case XML_NAME_LLC_PROMOTER_INVESTMENT:
        //��Ʊ�����Ω����[�л��]
        $tempExpFile = $commonPath . 'temp_html/temp_maincontent_llc_exp_05.txt';
        break;
    case XML_NAME_LLC_ARTICLE:
        //��Ʊ�����Ω����[�괾����]
        $tempExpFile = $commonPath . 'temp_html/temp_maincontent_llc_exp_06.txt';
        break;
}
_Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (������ʸ��) HTML�ƥ�ץ졼�ȥե����� = '" . $tempExpFile . "'");
$htmlExp = null;
if (!_IsNull($tempExpFile)) {
    $htmlExp = @file_get_contents($tempExpFile);
    //"HTML"��¸�ߤ����硢ɽ�����롣
    if ($htmlExp !== false && !_IsNull($htmlExp)) {
        _Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (������ʸ��) ��������");
    } else {
        //�����Ǥ��ʤ��ä����
        _Log("[/user/llc/free_info/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (������ʸ��) �ڼ��ԡ�");
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

//����URL
$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
//������桼����̾
$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
//���ߤ����Ͼ���
$htmlSidebarUserMenu = str_replace('{company_info}', _GetCompanyInfoHtml($loginInfo, MST_COMPANY_TYPE_ID_LLC), $htmlSidebarUserMenu);

$sidebar .= $htmlSidebarUserMenu;

//�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_LLC_FREE_INFO, '', PAGE_TITLE_LLC_FREE_INFO, 2);
/*
switch ($step) {
    case 9000:
        //��Ʊ�����Ω����[�괾����]
        _SetBreadcrumbs(PAGE_DIR_LLC_INFO_ARTICLE, '', PAGE_TITLE_LLC_INFO_ARTICLE, 4);
        break;
    default:
        _SetBreadcrumbs(PAGE_DIR_LLC_INFO, '', PAGE_TITLE_LLC_INFO, 4);
        break;
}
*/
//�ѥ󤯤��ꥹ�Ȥ�������롣
$breadcrumbs = _GetBreadcrumbs();

//WOOROM�եå�������
$wooromFooter = getWooromFooter();

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

_Log("[/user/llc/free_info/index.php] end.");
echo $html;

/**
 * ���_ȯ����_�л�ۥơ��֥������
 * �����ͤΥ����å��򤹤롣
 *
 * @param    array $xmlList XML���ɤ߹��������
 * @param    array $info ���Ϥ����ͤ���Ǽ����Ƥ�������
 *
 * @return    ���顼��å�����
 * @access  public
 * @since
 */
function _CheackInput4CompanyPromoterInvestment($xmlList, &$info)
{
    _Log("[_CheackInput4CompanyPromoterInvestment] start.");
    _Log("[_CheackInput4CompanyPromoterInvestment] (param) XML���ɤ߹�������� = '" . print_r($xmlList, true) . "'");
    _Log("[_CheackInput4CompanyPromoterInvestment] (param) ���Ϥ����ͤ���Ǽ����Ƥ������� = '" . print_r($info, true) . "'");

    $res = null;
    if (isset($info['update']['tbl_company_promoter_investment'])) {
        if (is_array($info['update']['tbl_company_promoter_investment'])) {

            //�ơ��֥�Υե�����ɾ����������롣��maxlength�˻��Ѥ��롣
            $colInfo = _DB_GetColumnsInfo('tbl_company_promoter_investment');

            //�л񥿥��ץޥ���
            $condition = null;
            $order = null;
            $order .= "lpad(show_order,10,'0')";    //�����Ⱦ��=ɽ����ξ���
            $order .= ",id";                        //�����Ⱦ��=ID�ξ���
            $mstInvestmentTypeList = _DB_GetList('mst_investment_type', $condition, false, $order, 'del_flag', 'id');

            foreach ($info['update']['tbl_company_promoter_investment'] as $cId => $companyList) {
                foreach ($companyList as $pNo => $promoterList) {
                    foreach ($promoterList as $tId => $typeList) {

                        $investmentTypeName = $mstInvestmentTypeList[$tId]['name'];

                        $messageName1 = null;
                        $messageName1 .= "�л��" . $pNo . "���ܡ���" . $investmentTypeName . "�׽л�� ";

                        $count = 0;
                        $delCount = 0;
                        foreach ($typeList['investment_info'] as $iKey => $investmentInfo) {

                            $count++;

                            $messageName2 = null;
                            $messageName2 .= $messageName1;
                            if (count($typeList['investment_info']) > 1) {
                                $messageName2 .= "" . $count . "���ܤ�";
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
                                            $searchList = array('"', '\'', '\\', chr(hexdec('7E')));
                                            $replaceList = array('��', '��', '��', chr(hexdec('A1')) . chr(hexdec('C1')));
                                            $value = str_replace($searchList, $replaceList, $value);
                                            break;
                                    }
                                    //�Ѵ������ͤ��᤹��
                                    $info['update']['tbl_company_promoter_investment'][$cId][$pNo][$tId]['investment_info'][$iKey][$name] = $value;
                                }

                                switch ($name) {
                                    case 'cmp_prm_inv_investment':
                                    case 'cmp_prm_inv_in_kind':
                                        //ɬ�ܥ����å�
                                        if (_IsNull($value)) {
//											$res .= "�л��".$pNo."���ܤ�".$investmentTypeName."��".$label."".$count."���ܤ����Ϥ��Ƥ���������\n";
                                            $res .= $messageName2 . $label . "�����Ϥ��Ƥ���������\n";
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
                                            $res .= $messageName2 . $label . "�ϡ�" . $maxlength . "ʸ����������Ϥ��Ƥ���������(����ʸ����2ʸ���Ȥ��ư��äƤ��ޤ���)\n";
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
                                                $res .= $messageName2 . $label . "�ϡ�Ⱦ�ѿ���(����)�����Ϥ��Ƥ���������\n";
                                            }
                                            break;
                                        case 'double':
                                            //Ⱦ�ѿ����ܥɥå�(.)�ܥޥ��ʥ�(-)�����å�
                                            if (!_IsHalfSizeNumericDotMinus($value)) {
//												$res .= "�л��".$pNo."���ܤ�".$investmentTypeName."��".$label."".$count."���ܤϡ�Ⱦ�ѿ���(�¿�)�����Ϥ��Ƥ���������\n";
                                                $res .= $messageName2 . $label . "�ϡ�Ⱦ�ѿ���(�¿�)�����Ϥ��Ƥ���������\n";
                                            }
                                            break;
                                    }
                                }
                            }
                        }

                        if ($count == $delCount) {
                            $res .= $messageName1;
                            $res .= "" . $xmlList['tbl_company_promoter_investment']['item_label']['cmp_prm_inv_investment'] . "��";
                            $res .= "" . $xmlList['tbl_company_promoter_investment']['item_label']['cmp_prm_inv_in_kind'] . "";
                            $res .= "��1�Ĥ����Ϥ��Ƥ���������";
                            $res .= "\n";
                        }
                    }
                }
            }
        }
    }

    _Log("[_CheackInput4CompanyPromoterInvestment] ��� = '" . $res . "'");
    _Log("[_CheackInput4CompanyPromoterInvestment] end.");

    return $res;
}

/**
 * ���_��Ū�ơ��֥������
 * �����ͤΥ����å��򤹤롣
 *
 * @param    array $xmlList XML���ɤ߹��������
 * @param    array $info ���Ϥ����ͤ���Ǽ����Ƥ�������
 *
 * @return    ���顼��å�����
 * @access  public
 * @since
 */
function _CheackInput4CompanyPurpose($xmlList, $info)
{

    _Log("[_CheackInput4CompanyPurpose] start.");

    _Log("[_CheackInput4CompanyPurpose] (param) XML���ɤ߹�������� = '" . print_r($xmlList, true) . "'");
    _Log("[_CheackInput4CompanyPurpose] (param) ���Ϥ����ͤ���Ǽ����Ƥ������� = '" . print_r($info, true) . "'");

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


    _Log("[_CheackInput4CompanyPurpose] ��� = '" . $res . "'");
    _Log("[_CheackInput4CompanyPurpose] end.");

    return $res;
}
