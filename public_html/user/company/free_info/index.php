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
$loginInfo = array();
$loginInfo['usr_user_id'] = NOLOGIN_USER_ID;
$loginInfo['usr_auth_id'] = AUTH_NON;
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

$tempSidebarUserMenuFile = $commonPath . 'temp_html/temp_sidebar_login.txt';
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

//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_COMPANY_INFO;

//���饤������ͥ᡼�륢�ɥ쥹
$clientMail = COMPANY_E_MAIL;
//�ޥ������ѥ᡼�륢�ɥ쥹
$masterMailList = $_COMPANY_MASTER_MAIL_LIST;

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
$xmlName = XML_NAME_CMP;//XML�ե�����̾�����ꤹ�롣
$id = null;
$step = null;
$stepId = null;

switch ($_SERVER["REQUEST_METHOD"]) {
    case 'POST':
        //�������å�ID
        $id = (isset($_POST['condition']['_id_']) ? $_POST['condition']['_id_'] : null);
        //���ƥå�ID
        $step = (isset($_POST['condition']['_step_']) ? $_POST['condition']['_step_'] : null);

        _Log("{������桼�������½���} �桼����ID = '" . $loginInfo['usr_user_id'] . "'");
        _Log("{������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "'");

        //���¤ˤ�äơ�ɽ������桼������������¤��롣
        switch ($loginInfo['usr_auth_id']) {
            case AUTH_NON://����̵��

                _Log("{������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "' = '����̵��'");
                _Log("{������桼�������½���} ����ʬ�γ��������Ω����Τ�ɽ�����롣");
                _Log("{������桼�������½���} �����ID�򸡺����롣");

                $id = null;

                //��ʬ�γ��������Ω����Τ�ɽ�����롣
                //���ID�򸡺����롣
                $id = _GetRelationCompanyId($loginInfo['usr_user_id']);

                _Log("{������桼�������½���} �����ID = '" . $id . "'");
                break;
        }

        //�����ͤ�������롣
        $info = $_POST;
        _Log("POST = '" . print_r($info, true) . "'");
        //�Хå�����å�����������
        $info = _StripslashesForArray($info);
        _Log("POST(�Хå�����å�����������) = '" . print_r($info, true) . "'");

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

        //����ͤ����ꤹ�롣
        $undeleteOnly4def = false;

        _Log("{������桼�������½���} �桼����ID = '" . $loginInfo['usr_user_id'] . "'");
        _Log("{������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "'");

        //���¤ˤ�äơ�ɽ������桼������������¤��롣
        switch ($loginInfo['usr_auth_id']) {
            case AUTH_NON://����̵��

                _Log("{������桼�������½���} ����ID = '" . $loginInfo['usr_auth_id'] . "' = '����̵��'");
                _Log("{������桼�������½���} ����ʬ�γ��������Ω����Τ�ɽ�����롣");
                _Log("{������桼�������½���} �����ID�򸡺����롣");

                $id = null;
                $undeleteOnly4def = true;

                //��ʬ�γ��������Ω����Τ�ɽ�����롣
                //���ID�򸡺����롣
                $id = _GetRelationCompanyId($loginInfo['usr_user_id']);

                _Log("{������桼�������½���} �����ID = '" . $id . "'");

                break;
        }

        $info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);

        //XML�ե�����̾���������å�ID�����ͤ��ɲä��롣
        $info['condition']['_xml_name_'] = $xmlName;
        $info['condition']['_id_'] = $id;

        break;
}

_Log("\$_SERVER[\"REQUEST_METHOD\"] = '" . $_SERVER["REQUEST_METHOD"] . "'");
_Log("XML�ե�����̾ = '" . $xmlName . "'");
_Log("�������å�ID = '" . $id . "'");

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

_Log("���ƥå�ID = '" . $step . "'");
_Log("XML�ե�����̾(���ƥå�ID) = '" . $xmlName . "'");
$_SESSION['free_info_step'] = $step;

// ��ǧ���̤����ܥ��󤬲����줿��碪�������ܤ���Τǡ�XML���ɤ߹��ޤʤ���
if (!empty($_POST['back']) && $step == 9) {
    $xmlName = null;
}

//����ͤ����ꤹ�롣
if ($xmlName == XML_NAME_CMP_PROMOTER) {
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
                        $bufInfo['cmp_prm_family_name'] = $tblCompanyBoardInfo['cmp_bod_family_name'];                    //ȯ����̾��(��) �� ���̾��(��)
                        $bufInfo['cmp_prm_first_name'] = $tblCompanyBoardInfo['cmp_bod_first_name'];                    //ȯ����̾��(̾) �� ���̾��(̾)
                        $bufInfo['cmp_prm_family_name_kana'] = $tblCompanyBoardInfo['cmp_bod_family_name_kana'];        //ȯ����̾���եꥬ��(��) �� ���̾���եꥬ��(��)
                        $bufInfo['cmp_prm_first_name_kana'] = $tblCompanyBoardInfo['cmp_bod_first_name_kana'];            //ȯ����̾���եꥬ��(̾) �� ���̾���եꥬ��(̾)
                        $bufInfo['cmp_prm_zip1'] = $tblCompanyBoardInfo['cmp_bod_zip1'];                                //ȯ���ͽ���(͹���ֹ�1) �� �������(͹���ֹ�1)
                        $bufInfo['cmp_prm_zip2'] = $tblCompanyBoardInfo['cmp_bod_zip2'];                                //ȯ���ͽ���(͹���ֹ�2) �� �������(͹���ֹ�2)
                        $bufInfo['cmp_prm_pref_id'] = $tblCompanyBoardInfo['cmp_bod_pref_id'];                            //ȯ���ͽ���(��ƻ�ܸ�) �� �������(��ƻ�ܸ�)
                        $bufInfo['cmp_prm_address1'] = $tblCompanyBoardInfo['cmp_bod_address1'];                        //ȯ���ͽ���(�Զ�Į¼) �� �������(�Զ�Į¼)
                        $bufInfo['cmp_prm_address2'] = $tblCompanyBoardInfo['cmp_bod_address2'];                        //ȯ���ͽ���(�嵭�ʹ�) �� �������(�嵭�ʹ�)
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
}

//�ե������Ѥ˥ޥ����ǡ��������ꤹ�롣
//ȯ�Բ�ǽ���������
$mstStockTotalNumList = _GetNumberArray(5000, 30000, 5000);
//��Ͽ��Ρ�ȯ�Բ�ǽ����������פ��ͤ��嵭����ˤ��뤫��̵�����ϡ��ɲä��롣(�����ǡ�����)
if (isset($info['update']['tbl_company']['cmp_stock_total_num']) && !_IsNull($info['update']['tbl_company']['cmp_stock_total_num'])) {
    if (!isset($mstStockTotalNumList[$info['update']['tbl_company']['cmp_stock_total_num']])) {
        $addList = array(
            'id' => $info['update']['tbl_company']['cmp_stock_total_num']
        , 'name' => $info['update']['tbl_company']['cmp_stock_total_num'] . ' (�ڻ����ѹ���5���3�������� ����աۺ��γ��������ѹ�����ȸ����᤻�ޤ���)'
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
    $xmlFile = $commonPath . "form_xml/" . $xmlName . ".xml";
    _Log("XML�ե����� = '" . $xmlFile . "'");
    $xmlList = _GetXml($xmlFile, $otherList);

    _Log("XML�ե��������� = '" . print_r($xmlList, true) . "'");

    if ($xmlName == XML_NAME_CMP_ALL) {
        //���������Ω����[�������Ƴ�ǧ]

        //���Ƥ�XML���ɤ߹��ࡣ

        //���������Ω����[����(���̾)]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_NAME . ".xml";
        _Log("XML�ե����� = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_name'] = $bufXmlList['tbl_company'];

        //���������Ω����[���ܶ⡦����ǯ��]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_CAPITAL . ".xml";
        _Log("XML�ե����� = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile, $otherList);
        $xmlList['tbl_company_capital'] = $bufXmlList['tbl_company'];

        //���������Ω����[��Ź�����]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_ADDRESS . ".xml";
        _Log("XML�ե����� = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_address'] = $bufXmlList['tbl_company'];

        //���������Ω����[���Ȥ���Ū]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_PURPOSE . ".xml";
        _Log("XML�ե����� = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_purpose'] = $bufXmlList['tbl_company_purpose'];

        //���������Ω����[���������Ǥ��]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_BOARD_BASE . ".xml";
        _Log("XML�ե����� = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_board_base'] = $bufXmlList['tbl_company'];

        //���������Ω����[������]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_BOARD_NAME . ".xml";
        _Log("XML�ե����� = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_board'] = $bufXmlList['tbl_company_board'];

        //���������Ω����[ȯ����]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_PROMOTER . ".xml";
        _Log("XML�ե����� = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_promoter'] = $bufXmlList['tbl_company_promoter'];

        //���������Ω����[�л��]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_PROMOTER_INVESTMENT . ".xml";
        _Log("XML�ե����� = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_promoter_investment'] = $bufXmlList['tbl_company_promoter_investment'];

        $info['update']['tbl_company_name'] = $info['update']['tbl_company'];
        $info['update']['tbl_company_capital'] = $info['update']['tbl_company'];
        $info['update']['tbl_company_address'] = $info['update']['tbl_company'];
        $info['update']['tbl_company_board_base'] = $info['update']['tbl_company'];

        _Log("XML�ե���������(��XML�ޡ�����) = '" . print_r($xmlList, true) . "'");
        _Log("���������Ω����(��XML�ޡ�����) = '" . print_r($info, true) . "'");

        $mode = 2;
    }
}

//��¸�ܥ��󡢼��إܥ��󤬲����줿���
if (!empty($_POST['go']) || !empty($_POST['back']) || !empty($_POST['next'])) {
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
     