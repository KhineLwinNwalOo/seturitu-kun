<?php
/*
 * [���������Ω.JP �ġ���]
 * �褯�������ڡ���
 *
 * ��������2010/11/20	d.ishikawa	��������
 *         ��2011/10/27	d.ishikawa	������ǽ�ɲ�
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/faq/index.php] start.");


_Log("[/faq/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/faq/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/faq/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/faq/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_Log("[/faq/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
//	_Log("[/faq/index.php] end.");
//	//��������̤�ɽ�����롣
//	header("Location: ".URL_BASE);
//	exit;
} else {
	//����������������롣
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
//
//	//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
//	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//ǧ�ڥ����å�----------------------------------------------------------------------end



//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- start
_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) �ڼ��ԡ�");
}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}


$tempMaincontentFaqFile = '../common/temp_html/temp_maincontent_faq.txt';
_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�ᥤ�󥳥�ƥ�Ĥ褯�������) HTML�ƥ�ץ졼�ȥե����� = '".$tempMaincontentFaqFile."'");

$htmlMaincontentFaq = @file_get_contents($tempMaincontentFaqFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlMaincontentFaq !== false && !_IsNull($htmlMaincontentFaq)) {
	_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�ᥤ�󥳥�ƥ�Ĥ褯�������) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�ᥤ�󥳥�ƥ�Ĥ褯�������) �ڼ��ԡ�");
}




_Log("[/faq/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_FAQ;



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





//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/faq/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/faq/index.php] mode = '".$mode."'");

///////////////////////////////////////////////////

$key = $_GET['_@_key_@_'];
$key = trim($key);
$key = trim($key, '/');
$keyList = explode('/', $key);

_Log("[/index.php] ���� = '".$key."'");
_Log("[/index.php] ���� = '\n".print_r($keyList,true)."\n'");



//ǧ�ڥ����å�----------------------------------------------------------------------start
//$loginInfo = null;
//$loginFlag = false;
////�����󤷤Ƥ��뤫��
//if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
//	$loginFlag = true;
//}

$loginFlag = false;
if (!_IsNull($loginInfo)) {
	//���¤ˤ�äơ�
	switch($loginInfo['usr_auth_id']){
		case AUTH_WOOROM://WOOROM����
			$loginFlag = true;
			break;
	}
}
//ǧ�ڥ����å�----------------------------------------------------------------------end


////HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- start
//_Log("[/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
//$tempFile = './common/temp_html/temp_base.txt';
//_Log("[/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");
//
//$html = @file_get_contents($tempFile);
////"HTML"��¸�ߤ����硢ɽ�����롣
//if ($html !== false && !_IsNull($html)) {
//	_Log("[/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
//} else {
//	//�����Ǥ��ʤ��ä����
//	_Log("[/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
//	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
//}
//_Log("[/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
////HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


$baseUrl = URL;
$baseUrl .= '/faq';


//���Ƥθ������Ф��Ƥκ���ե饰�����ꤹ�롣�����Ԥξ�硢�����ɽ�����롣
$undeleteOnly4def = true;
if ($loginFlag) $undeleteOnly4def = false;


//DB�򥪡��ץ󤹤롣
$link = _DB_Open();

//�ޥ��������ꤹ�롣
$condition = null;
$order = null;
$order .= "lpad(show_order,10,'0')";
$order .= ",id";
//���ƥ��꡼�ޥ���
$mstCategoryList = _DB_GetList('mst_category', $condition, $undeleteOnly4def, $order, 'del_flag', 'id');

//$keyList�Υ���
//0:�ȥåץڡ���
//1:���ƥ��꡼�ڡ���
//2:FAQ�ڡ���(�ܺ٥ڡ���)
//3:�ʹ�̵��

//ư��⡼��
//1:�ȥåץڡ���
//2:���ƥ��꡼�ڡ���
//3:FAQ�ڡ���(�ܺ٥ڡ���)
//4:�����ڡ���
//5:������ڡ���
//6:�������ȥڡ���

$mode = 1;
//¸�ߤ��ʤ�URL�ʤ顢�ȥåץڡ�����ɽ�����롣
$modeErrorFlag = false;

if (count($keyList) > 2) {
	$mode = 3;
} elseif (count($keyList) == 2) {
	switch ($keyList[1]) {
		case 'search':
			$mode = 4;
			break;
		case 'login':
			$mode = 5;
			break;
		case 'logout':
			$mode = 6;
			break;
		default:
			$mode = 2;
			break;
	}
} else {
	$mode = 1;
}

//���ƥ��꡼�򸡺����롣
$keyMstCategoryInfo = null;
if (isset($keyList[1]) && !_IsNull($keyList[1])) {
	switch ($keyList[1]) {
		case 'search':
		case 'login':
		case 'logout':
			break;
		default:
			foreach ($mstCategoryList as $mcKey => $mstCategoryInfo) {
				if ($keyList[1] == $mstCategoryInfo['value']) {
					$keyMstCategoryInfo = $mstCategoryInfo;
					break;
				}
			}
			if (_IsNull($keyMstCategoryInfo)) {
				$modeErrorFlag = true;
				_Log("[/index.php] {���������å�} �ڥ��顼�ۥ��ƥ��꡼¸�ߤ��ʤ���");
			}
			break;
	}
}

//FAQ����򸡺����롣
$keyFaqInfo = null;
if (isset($keyList[2]) && !_IsNull($keyList[2])) {
	_Log("[/index.php] FAQ�ڡ��� ���� = '".$keyList[2]."'");
	$bufKey = str_replace(FAQ_DIR_FORMAT_1, '', $keyList[2]);
	_Log("[/index.php] FAQ�ڡ��� ����(ID����) = '".$bufKey."'");
	$bufKey = (int)$bufKey;
	_Log("[/index.php] FAQ�ڡ��� ����(ID���Ͳ�) = '".$bufKey."'");
	if ($bufKey != 0) {
		//�ܺپ����������롣
		$keyFaqInfo = _GetFaqInfo($bufKey, $undeleteOnly4def);
	}
	if (_IsNull($keyFaqInfo)) {
		$modeErrorFlag = true;
		_Log("[/index.php] {���������å�} �ڥ��顼��FAQ¸�ߤ��ʤ���");
	}
}

if (!_IsNull($keyMstCategoryInfo) && !_IsNull($keyFaqInfo)) {
	if ($keyMstCategoryInfo['id'] != $keyFaqInfo['tbl_faq']['faq_category_id']) {
		$modeErrorFlag = true;
		_Log("[/index.php] {���������å�} �ڥ��顼�ۥ��ƥ��꡼��FAQ�Υ��ƥ��꡼���԰��ס�");
		_Log("[/index.php] {���������å�} �ڥ��顼�ۥ��ƥ��꡼ = '".$keyMstCategoryInfo['id']."'");
		_Log("[/index.php] {���������å�} �ڥ��顼��FAQ�Υ��ƥ��꡼ = '".$keyFaqInfo['tbl_faq']['faq_category_id']."'");
	}
}

_Log("[/index.php] {ư��⡼��} �⡼�� = '".$mode."'");
_Log("[/index.php] {ư��⡼��} ���顼 = '".$modeErrorFlag."'");


//���顼�ξ�硢�ȥåץڡ�����ɽ�����롣
if ($modeErrorFlag) {
	header("Location: ".$baseUrl."/");
	exit;
}


$keyFaqInfoNoSpecial = $keyFaqInfo;


//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$keyFaqInfo = _HtmlSpecialCharsForArray($keyFaqInfo);
$keyMstCategoryInfo = _HtmlSpecialCharsForArray($keyMstCategoryInfo);
$mstCategoryList = _HtmlSpecialCharsForArray($mstCategoryList);


////�����ȥ����ȥ�
//$siteTitle = SITE_TITLE;

////�ڡ��������ȥ�
//$pageTitle = PAGE_TITLE_HOME;



////�����ȥ�����ꤹ�롣
//$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = URL;


$info = null;
$ambiguousList = null;
$message = null;
if (isset($_POST['bt_search'])) {
	$info = $_POST;
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/index.php] \$_POST(�Хå�����å�������) = '".print_r($info,true)."'");

	if (isset($info['search']['ambiguous']) && !_IsNull($info['search']['ambiguous'])) {
		$ambiguous = $info['search']['ambiguous'];
		_Log("[/index.php] {����} ������� = '".$ambiguous."'");
		//�ȥ�ह�롣
		$ambiguous = trim($ambiguous);
		_Log("[/index.php] {����} �������(�ȥ��) = '".$ambiguous."'");
		$ambiguous = trim($ambiguous, '��');
		_Log("[/index.php] {����} �������(�ȥ�ࡧ����SP) = '".$ambiguous."'");

		//�᤹��
		$info['search']['ambiguous'] = $ambiguous;

		//�����ѡץ��ڡ������Ⱦ�ѡפ��Ѵ����롣
		$ambiguous = mb_convert_kana($ambiguous, 's');
		_Log("[/index.php] {����} �������(���Ѣ�Ⱦ��SP) = '".$ambiguous."'");
		//ʣ��SP��ñ��ˤ��롣
		$ambiguous = preg_replace('/ +/', ' ', $ambiguous);
		_Log("[/index.php] {����} �������(ʣ����ñ��SP) = '".$ambiguous."'");
		//�����ʬ�䤹�롣
		$ambiguousList = explode(' ', $ambiguous);
		_Log("[/index.php] {����} �������(����) = '".print_r($ambiguousList, true)."'");

		$message .= "��".$info['search']['ambiguous']."��";
	}
}

if (isset($_POST['bt_login'])) {
	$info = $_POST;
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/index.php] \$_POST(�Хå�����å�������) = '".print_r($info,true)."'");

	if (!isset($info['login']['usr_e_mail']) || _IsNull($info['login']['usr_e_mail'])) {
		$message .= "E-Mail�����Ϥ��Ƥ���������\n";
	}
	if (!isset($info['login']['usr_pass']) || _IsNull($info['login']['usr_pass'])) {
		$message .= "�ѥ���ɤ����Ϥ��Ƥ���������\n";
	}
	if (_IsNull($message)) {
		//�桼�����ơ��֥�򸡺����롣
		$condition = array();
		$condition['usr_e_mail'] = $info['login']['usr_e_mail'];
		$condition['usr_pass'] = $info['login']['usr_pass'];
		$order = null;
		$tblUserList = _DB_GetList('tbl_user', $condition, true, $order, 'usr_del_flag');
		if (_IsNull($tblUserList)) {
			$message .= "E-Mail���ϥѥ���ɤ������Ǥ���\n";
		} else {
			//���å����˥桼�����������¸���롣����Ƭ�����ꤹ�롣1��ΤϤ���
			$_SESSION[SID_LOGIN_USER_INFO] = $tblUserList[0];
			//�ȥåץڡ�����ɽ�����롣
			header("Location: ".$baseUrl."/");
			exit;
		}
	}
}


$from = null;
$from .= "<form id=\"frm_search\" name=\"frm_search\" action=\"".$basePath."/faq/search/\" method=\"post\" enctype=\"multipart/form-data\">";
$from .= "\n";
$from .= "<input class=\"txt\" type=\"text\" name=\"search[ambiguous]\" maxlength=\"500\" value=\"".$info['search']['ambiguous']."\" />";
$from .= "\n";
$from .= "<input class=\"btn\" type=\"submit\" name=\"bt_search\" value=\"������\" />";
$from .= "\n";
$from .= "<span>����Ƕ��ڤä�ʣ���Υ�����ɤ򸡺��Ǥ��ޤ����㡧��Ω������</span>";
$from .= "\n";
$from .= "</form>";
$from .= "\n";

$contact = null;
$contact .= "<div class=\"contact\">";
$contact .= "\n";
$contact .= "<h3>";
$contact .= "��褷�ޤ�������";
$contact .= "</h3>";
$contact .= "\n";
$contact .= "<p>";
$contact .= "��褷�ʤ�����";
//$contact .= "<a href=\"mailto:info@sin-kaisha.jp\">";
$contact .= "<a href=\"/inquiry/\">";
$contact .= "���ݡ���";
$contact .= "</a>";
$contact .= "�ؤ��䤤��碌����������";
$contact .= "</p>";
$contact .= "\n";
$contact .= "</div><!-- class=\"contact\" -->";
$contact .= "\n";



$content = null;
$category = null;
switch ($mode) {
	case 6:
		//�������ȥڡ���

		//���å���󤫤�桼��������������롣
		unset($_SESSION[SID_LOGIN_USER_INFO]);
		$loginInfo = null;
		$loginFlag = false;

		$message .= "�������Ȥ��ޤ�����\n";
		$message .= "\n";
		$message .= "\n";
		$message .= "<a href=\"".$baseUrl."/\">";
		$message .= "�ȥåץڡ����Ϥ�����&nbsp;&gt;&gt;";
		$message .= "</a>";
		$message .= "\n";
		$message .= "<a href=\"".$baseUrl."/login/\">";
		$message .= "�ƥ�����Ϥ�����&nbsp;&gt;&gt;";
		$message .= "</a>";

		$from = null;
		$from .= "<form id=\"frm_login\" name=\"frm_login\" action=\"".$basePath."/faq/login/\" method=\"post\" enctype=\"multipart/form-data\">";
		$from .= "\n";
		$from .= "<h3>";
		$from .= "��������";
		$from .= "</h3>";
		$from .= "\n";
		$from .= "<div class=\"message\">";
		$from .= "\n";
		$from .= nl2br($message);
		$from .= "</div>";
		$from .= "\n";
		$from .= "</form>";
		$from .= "\n";


		$contact = null;
		break;
	case 5:
		//������ڡ���

		$from = null;
		$from .= "<form id=\"frm_login\" name=\"frm_login\" action=\"".$basePath."/faq/login/\" method=\"post\" enctype=\"multipart/form-data\">";
		$from .= "\n";
		$from .= "<h3>";
		$from .= "������";
		$from .= "</h3>";
		$from .= "\n";
		$from .= "E-Mail��<br />";
		$from .= "\n";
		$from .= "<input class=\"txt\" type=\"text\" name=\"login[usr_e_mail]\" maxlength=\"200\" value=\"".$info['login']['usr_e_mail']."\" />";
		$from .= "\n";
		$from .= "<br />";
		$from .= "\n";
		$from .= "�ѥ���ɡ�<br />";
		$from .= "\n";
		$from .= "<input class=\"txt\" type=\"password\" name=\"login[usr_pass]\" maxlength=\"200\" value=\"".$info['login']['usr_pass']."\" />";
		$from .= "\n";
		$from .= "<br />";
		$from .= "\n";
		$from .= "<input class=\"btn\" type=\"submit\" name=\"bt_login\" value=\"������\" />";
		$from .= "\n";
		$from .= "<div class=\"message\">";
		$from .= "\n";
		$from .= nl2br($message);
		$from .= "</div>";
		$from .= "\n";
		$from .= "</form>";
		$from .= "\n";


		$contact = null;
		break;
	case 4:
		//�����ڡ���

		//������ɤǸ������롣
		$tblFaqList = null;
		if (!_IsNull($ambiguousList)) {
			$condition = array();
			$condition['ambiguous'] = $ambiguousList;//�������
			$order = null;
			$tblFaqList = _GetFaq($condition, $order, $undeleteOnly4def);
		}
		if (!_IsNull($tblFaqList)) {
			$message .= "�θ������ (".number_format(count($tblFaqList)).")";


			$tblFaqListNoSpecial = $tblFaqList;
			//ʸ����HTML����ƥ��ƥ����Ѵ����롣
			$tblFaqList = _HtmlSpecialCharsForArray($tblFaqList);

			$category .= "<div class=\"category category_long\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= "����";
			$category .= "</h3>";
			$category .= "\n";
			$category .= "<div class=\"message\">";
			$category .= $message;
			$category .= "</div>";
			$category .= "\n";
			$category .= "<ul class=\"search\">";
			$category .= "\n";

			$snui = uniqid('s_');
			$enui = uniqid('e_');
			_Log("[/index.php] {�������} ������������ = '".$snui."'");
			_Log("[/index.php] {�������} ����������λ = '".$enui."'");


			foreach ($tblFaqList as $tfKey => $tblFaqInfo) {
				$faqTitle = $tblFaqListNoSpecial[$tfKey]['faq_title'];
				_Log("[/index.php] {�������} �����ȥ� = '".$faqTitle."'");
				$faqTitle = _SubStr($faqTitle, 50);
				_Log("[/index.php] {�������} �����ȥ�(��Ƭ80ʸ��) = '".$faqTitle."'");
				//HTML����ƥ��ƥ����Ѵ����롣
				$faqTitle = htmlspecialchars($faqTitle, ENT_QUOTES);
				_Log("[/index.php] {�������} �����ȥ�(HTML����ƥ��ƥ��Ѵ���) = '".$faqTitle."'");
				foreach ($ambiguousList as $aKey => $ambiguous) {
					$faqTitle = preg_replace('/'.htmlspecialchars($ambiguous).'/', ''.$snui.''.htmlspecialchars($ambiguous).''.$enui.'', $faqTitle);
				}
				_Log("[/index.php] {�������} �����ȥ�(������) = '".$faqTitle."'");
				foreach ($ambiguousList as $aKey => $ambiguous) {
					$faqTitle = preg_replace('/'.$snui.'/', '<span class="amb">', $faqTitle);
					$faqTitle = preg_replace('/'.$enui.'/', '</span>', $faqTitle);
				}
				_Log("[/index.php] {�������} �����ȥ�(�ϥ��饤�ȸ�) = '".$faqTitle."'");


				$faqContent = $tblFaqListNoSpecial[$tfKey]['faq_content'];
				_Log("[/index.php] {�������} ���� = '".$faqContent."'");
				//���Ԥ������롣
				$faqContent = preg_replace('/[\r\n]/', '', $faqContent);
				_Log("[/index.php] {�������} ����(���Ժ��) = '".$faqContent."'");
				//HTML�����������롣
				$faqContent = strip_tags($faqContent);
				_Log("[/index.php] {�������} ����(HTML�������) = '".$faqContent."'");
				$faqContent = _SubStr($faqContent, 300);
				_Log("[/index.php] {�������} ����(��Ƭ300ʸ��) = '".$faqContent."'");
				//HTML����ƥ��ƥ����Ѵ����롣
				$faqContent = htmlspecialchars($faqContent, ENT_QUOTES);
				_Log("[/index.php] {�������} ����(HTML����ƥ��ƥ��Ѵ���) = '".$faqContent."'");
				foreach ($ambiguousList as $aKey => $ambiguous) {
					$faqContent = preg_replace('/'.htmlspecialchars($ambiguous).'/', ''.$snui.''.htmlspecialchars($ambiguous).''.$enui.'', $faqContent);
				}
				_Log("[/index.php] {�������} ����(������) = '".$faqContent."'");
				foreach ($ambiguousList as $aKey => $ambiguous) {
					$faqContent = preg_replace('/'.$snui.'/', '<span class="amb">', $faqContent);
					$faqContent = preg_replace('/'.$enui.'/', '</span>', $faqContent);
				}
				_Log("[/index.php] {�������} ����(�ϥ��饤�ȸ�) = '".$faqContent."'");


				$category .= "<li>";
				$category .= "<a href=\"".$baseUrl."/".$tblFaqInfo['faq_category_value']."/".sprintf(FAQ_DIR_FORMAT, $tblFaqInfo['faq_id'])."/\">";
				$category .= $faqTitle;
				$category .= "</a>";
				$category .= "<span class=\"min\">";
				$category .= $faqContent;
				$category .= "</span>";
				$category .= "</li>";
				$category .= "\n";

				if ($loginFlag) {
					$category .= "<li class=\"edit\">";
					$category .= "<a href=\"\" title=\"�Խ�\" class=\"faq\" _faq_id=\"".$tblFaqInfo['faq_id']."\">[�Խ�]</a>";
					$category .= "&nbsp;|&nbsp;";
					$category .= "ɽ���硧";
					$category .= (_IsNull($tblFaqInfo['faq_show_order']) ? '-' : $tblFaqInfo['faq_show_order']);
					$category .= "&nbsp;|&nbsp;";
					$category .= ($tblFaqInfo['faq_del_flag'] == DELETE_FLAG_YES ? DELETE_FLAG_YES_NAME : '');
					$category .= "</li>";
					$category .= "\n";
				}
			}
			$category .= "</ul>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		} else {
			if (!_IsNull($ambiguousList)) {
				$message .= "�˰��פ�����ܤϸ��Ĥ���ޤ���Ǥ�����";
			} else {
				$message = "�����������Ϥ��Ƥ���������";
			}

			$category .= "<div class=\"category category_long\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= "����";
			$category .= "</h3>";
			$category .= "\n";
			$category .= "<div class=\"message\">";
			$category .= $message;
			$category .= "</div>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}
		break;
	case 3:
		//FAQ�ڡ���(�ܺ٥ڡ���)

		//���Ƥ�HTML�������Ѳġ�
		$faqContent = $keyFaqInfoNoSpecial['tbl_faq']['faq_content'];
		$faqContent = nl2br($faqContent);

		$category .= "<div class=\"info\">";
		$category .= "\n";
		$category .= "<h3>";
		$category .= $keyFaqInfo['tbl_faq']['faq_title'];
		$category .= "</h3>";
		$category .= "\n";

		if ($loginFlag) {
			$category .= "<div class=\"edit\">";
			$category .= "<a href=\"\" title=\"�Խ�\" class=\"faq\" _faq_id=\"".$keyFaqInfo['tbl_faq']['faq_id']."\">[�Խ�]</a>";
			$category .= "&nbsp;|&nbsp;";
			$category .= "ɽ���硧";
			$category .= (_IsNull($keyFaqInfo['tbl_faq']['faq_show_order']) ? '-' : $keyFaqInfo['tbl_faq']['faq_show_order']);
			$category .= "&nbsp;|&nbsp;";
			$category .= ($keyFaqInfo['tbl_faq']['faq_del_flag'] == DELETE_FLAG_YES ? DELETE_FLAG_YES_NAME : '');
			$category .= "</div>";
			$category .= "\n";
		}

		$category .= "<div class=\"navi\">";
		$category .= "<span>";
		$category .= "���ƥ��꡼��";
		$category .= "<a href=\"".$baseUrl."/".$keyMstCategoryInfo['value']."/\">";
		$category .= $keyMstCategoryInfo['name'];
		$category .= "</a>";
		$category .= "</span>";
		$category .= "</div>";
		$category .= "\n";
		$category .= "<div class=\"answer\">";
		$category .= "\n";
		$category .= $faqContent;
		$category .= "\n";
		$category .= "</div>";
		$category .= "\n";
		$category .= "</div><!-- class=\"info\" -->";
		$category .= "\n";
		break;
	case 2:
		//���ƥ��꡼�ڡ���

		//�����Υ��ƥ��꡼�����ꤹ�롣
		$condition = array();
		$condition['faq_category_id'] = $keyMstCategoryInfo['id'];//���ƥ��꡼ID
		$order = null;
		$tblFaqList = _GetFaq($condition, $order, $undeleteOnly4def);
		if (!_IsNull($tblFaqList)) {
			$tblFaqListNoSpecial = $tblFaqList;
			//ʸ����HTML����ƥ��ƥ����Ѵ����롣
			$tblFaqList = _HtmlSpecialCharsForArray($tblFaqList);

			$category .= "<div class=\"category category_long\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= $keyMstCategoryInfo['name'];
			$category .= "</h3>";
			$category .= "\n";
			$category .= "<ul>";
			$category .= "\n";
			foreach ($tblFaqList as $tfKey => $tblFaqInfo) {
				$faqTitle = $tblFaqListNoSpecial[$tfKey]['faq_title'];
				$faqTitle = _SubStr($faqTitle, 50);
				$faqTitle = htmlspecialchars($faqTitle, ENT_QUOTES);

				$category .= "<li>";
				$category .= "<a href=\"".$baseUrl."/".$tblFaqInfo['faq_category_value']."/".sprintf(FAQ_DIR_FORMAT, $tblFaqInfo['faq_id'])."/\">";
				$category .= $faqTitle;
				$category .= "</a>";
				$category .= "</li>";
				$category .= "\n";

				if ($loginFlag) {
					$category .= "<li class=\"edit\">";
					$category .= "<a href=\"\" title=\"�Խ�\" class=\"faq\" _faq_id=\"".$tblFaqInfo['faq_id']."\">[�Խ�]</a>";
					$category .= "&nbsp;|&nbsp;";
					$category .= "ɽ���硧";
					$category .= (_IsNull($tblFaqInfo['faq_show_order']) ? '-' : $tblFaqInfo['faq_show_order']);
					$category .= "&nbsp;|&nbsp;";
					$category .= ($tblFaqInfo['faq_del_flag'] == DELETE_FLAG_YES ? DELETE_FLAG_YES_NAME : '');
					$category .= "</li>";
					$category .= "\n";
				}
			}
			$category .= "</ul>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}

		if (_IsNull($category)) {
			$category .= "<div class=\"category category_long\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= $keyMstCategoryInfo['name'];
			$category .= "</h3>";
			$category .= "\n";
			$category .= "<div class=\"message\">";
			$category .= "�������";
			$category .= "</div>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}
		break;
	case 1:
	default:
		//�ȥåץڡ���

		//��뤢���������ꤹ�롣
		$condition = array();
		$condition['faq_frequently_flag'] = FREQUENTLY_FLAG_YES;//�褯�������ե饰="�֤褯�������פ�ɽ�����롣"
		$order = null;
		$tblFaqList = _GetFaq($condition, $order, $undeleteOnly4def);
		if (!_IsNull($tblFaqList)) {
			$tblFaqListNoSpecial = $tblFaqList;
			//ʸ����HTML����ƥ��ƥ����Ѵ����롣
			$tblFaqList = _HtmlSpecialCharsForArray($tblFaqList);

			$category .= "<div class=\"category category_long\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= FREQUENTLY_FLAG_YES_NAME;
			$category .= "</h3>";
			$category .= "\n";
			$category .= "<ul>";
			$category .= "\n";
			foreach ($tblFaqList as $tfKey => $tblFaqInfo) {
				$faqTitle = $tblFaqListNoSpecial[$tfKey]['faq_title'];
				$faqTitle = _SubStr($faqTitle, 50);
				$faqTitle = htmlspecialchars($faqTitle, ENT_QUOTES);

				$category .= "<li>";
				$category .= "<a href=\"".$baseUrl."/".$tblFaqInfo['faq_category_value']."/".sprintf(FAQ_DIR_FORMAT, $tblFaqInfo['faq_id'])."/\">";
				$category .= $faqTitle;
				$category .= "</a>";
				$category .= "</li>";
				$category .= "\n";

				if ($loginFlag) {
					$category .= "<li class=\"edit\">";
					$category .= "<a href=\"\" title=\"�Խ�\" class=\"faq\" _faq_id=\"".$tblFaqInfo['faq_id']."\">[�Խ�]</a>";
					$category .= "&nbsp;|&nbsp;";
					$category .= "ɽ���硧";
					$category .= (_IsNull($tblFaqInfo['faq_show_order']) ? '-' : $tblFaqInfo['faq_show_order']);
					$category .= "&nbsp;|&nbsp;";
					$category .= ($tblFaqInfo['faq_del_flag'] == DELETE_FLAG_YES ? DELETE_FLAG_YES_NAME : '');
					$category .= "</li>";
					$category .= "\n";
				}
			}
			$category .= "</ul>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}

		//�����ƥ��꡼�����ꤹ�롣
		foreach ($mstCategoryList as $mcKey => $mstCategoryInfo) {
			$condition = array();
			$condition['faq_category_id'] = $mstCategoryInfo['id'];//���ƥ��꡼ID
			$order = null;
			$count = _GetFaq($condition, $order, $undeleteOnly4def, true);
			if ($count == 0) {
				continue;
			}
			$tblFaqList = _GetFaq($condition, $order, $undeleteOnly4def, false, 1, FAQ_TOP_CATEGORY_NUM);
			$tblFaqListNoSpecial = $tblFaqList;
			//ʸ����HTML����ƥ��ƥ����Ѵ����롣
			$tblFaqList = _HtmlSpecialCharsForArray($tblFaqList);

			$category .= "<div class=\"category\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= "<a href=\"".$baseUrl."/".$mstCategoryInfo['value']."/\">";
			$category .= $mstCategoryInfo['name'];
			$category .= "&nbsp;";
			$category .= "(";
			$category .= number_format($count);
			$category .= "��";
			$category .= ")";
			$category .= "</a>";
			$category .= "</h3>";
			$category .= "\n";

			if ($loginFlag) {
				$category .= "<ul class=\"edit\">";
			} else {
				$category .= "<ul>";
			}

			$category .= "\n";
			foreach ($tblFaqList as $tfKey => $tblFaqInfo) {
				$faqTitle = $tblFaqListNoSpecial[$tfKey]['faq_title'];
				$faqTitle = _SubStr($faqTitle, 20);
				$faqTitle = htmlspecialchars($faqTitle, ENT_QUOTES);

				$category .= "<li>";
				$category .= "<a href=\"".$baseUrl."/".$mstCategoryInfo['value']."/".sprintf(FAQ_DIR_FORMAT, $tblFaqInfo['faq_id'])."/\">";
				$category .= $faqTitle;
				$category .= "</a>";
				$category .= "</li>";
				$category .= "\n";

				if ($loginFlag) {
					$category .= "<li class=\"edit\">";
					$category .= "<a href=\"\" title=\"�Խ�\" class=\"faq\" _faq_id=\"".$tblFaqInfo['faq_id']."\">[�Խ�]</a>";
					$category .= "&nbsp;|&nbsp;";
					$category .= "ɽ���硧";
					$category .= (_IsNull($tblFaqInfo['faq_show_order']) ? '-' : $tblFaqInfo['faq_show_order']);
					$category .= "&nbsp;|&nbsp;";
					$category .= ($tblFaqInfo['faq_del_flag'] == DELETE_FLAG_YES ? DELETE_FLAG_YES_NAME : '');
					$category .= "</li>";
					$category .= "\n";
				}
			}
			$category .= "</ul>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}

		if (_IsNull($category)) {
			$category .= "<div class=\"category\">";
			$category .= "\n";
			$category .= "<div class=\"message\">";
			$category .= "�������";
			$category .= "</div>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}
		break;
}




$content .= "<div class=\"content\">";
$content .= "\n";
$content .= $from;
$content .= "<div class=\"end\"></div>";
$content .= "\n";
$content .= $category;
$content .= "<div class=\"end\"></div>";
$content .= "\n";
$content .= $contact;
$content .= "<div class=\"end\"></div>";
$content .= "\n";
$content .= "</div><!-- class=\"content\" -->";
$content .= "\n";



///////////////////////////////////////////////////



//�����ȥ�����ꤹ�롣
$title = $pageTitle;

////����URL�����ꤹ�롣
//$basePath = "..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;

$maincontent .= "<div id=\"main\">";
$maincontent .= "\n";
$maincontent .= "\n";

$maincontent .= "<h2>";
$maincontent .= "<img src=\"".$basePath."/img/maincontent/pt_faq.jpg\" title=\"\" alt=\"�褯�������\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

////����URL
//$htmlMaincontentFaq = str_replace('{base_url}', $basePath, $htmlMaincontentFaq);
//
//$maincontent .= $htmlMaincontentFaq;

//$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);


if ($loginFlag) {
	$admin = null;
	$admin .= "<div id=\"admin\">";
	$admin .= "\n";
//	$admin .= "������桼������";
//	$admin .= htmlspecialchars($loginInfo['usr_family_name'], ENT_QUOTES);
//	$admin .= "&nbsp;";
//	$admin .= htmlspecialchars($loginInfo['usr_first_name'], ENT_QUOTES);
//	$admin .= "\n";
//	$admin .= "&nbsp;|&nbsp;";
//	$admin .= "\n";
	$admin .= "<a href=\"\" title=\"������Ͽ\" class=\"faq\" _faq_id=\"\">[������Ͽ]</a>";
	$admin .= "\n";
	$admin .= "&nbsp;|&nbsp;";
	$admin .= "\n";
	$admin .= "<a href=\"\" title=\"���ƥ��꡼��Ͽ\" class=\"mst\" _mst_name=\"mst_category\">[���ƥ��꡼��Ͽ]</a>";
	$admin .= "\n";
//	$admin .= "&nbsp;|&nbsp;";
//	$admin .= "\n";
//	$admin .= "<a href=\"".$baseUrl."/logout/\" title=\"��������\">[��������]</a>";
//	$admin .= "\n";
	$admin .= "</div>";
	$admin .= "\n";
	$admin .= "<div id=\"form\"></div>";
	$admin .= "\n";
	$admin .= "\n";

	$maincontent .= $admin;
}


$maincontent .= $content;


$maincontent .= "\n";
$maincontent .= "</div><!-- id=\"admin\" -->";
$maincontent .= "\n";


//������ץȤ����ꤹ�롣
$script = null;


$script .= "<link rel=\"stylesheet\" href=\"".$basePath."/faq/css/import.css\" type=\"text/css\" />";
$script .= "\n";
$script .= "<script language=\"javascript\" src=\"".$basePath."/common/js/faq/faq.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
$script .= "\n";
$script .= "<script language=\"javascript\" src=\"".$basePath."/common/js/mst/mst.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
$script .= "\n";


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



////�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
//_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
//_SetBreadcrumbs(PAGE_DIR_FAQ, '', PAGE_TITLE_FAQ, 2);
////�ѥ󤯤��ꥹ�Ȥ�������롣
//$breadcrumbs = _GetBreadcrumbs();


//�����ȥ�����ꤹ�롣
//$title = $siteTitle;
$title = PAGE_TITLE_FAQ;

//�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_FAQ, '', PAGE_TITLE_FAQ, 2);
switch ($mode) {
	case 4:
		//�����ڡ���
		$title = PAGE_TITLE_SEARCH.' - '.$title;

		_SetBreadcrumbs(PAGE_DIR_SEARCH, '', PAGE_TITLE_SEARCH, 3);
		break;
	case 3:
		//FAQ�ڡ���(�ܺ٥ڡ���)
		$title = $keyFaqInfo['tbl_faq']['faq_title'].' - '.$keyMstCategoryInfo['name'].' - '.$title;

		$faqTitle = $keyFaqInfoNoSpecial['tbl_faq']['faq_title'];
		$faqTitle = _SubStr($faqTitle, 30);
		$faqTitle = htmlspecialchars($faqTitle, ENT_QUOTES);

		_SetBreadcrumbs(PAGE_DIR_FAQ.$keyMstCategoryInfo['value'].'/', '', $keyMstCategoryInfo['name'], 3);
		_SetBreadcrumbs(PAGE_DIR_FAQ.$keyMstCategoryInfo['value'].'/'.sprintf(FAQ_DIR_FORMAT, $keyFaqInfo['tbl_faq']['faq_id']).'/', '', $faqTitle, 4);
		break;
	case 2:
		//���ƥ��꡼�ڡ���
		$title = $keyMstCategoryInfo['name'].' - '.$title;

		_SetBreadcrumbs(PAGE_DIR_FAQ.$keyMstCategoryInfo['value'].'/', '', $keyMstCategoryInfo['name'], 3);
		break;
	case 1:
	default:
		//�ȥåץڡ���
		break;
}


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
$html = str_replace ('{keywords}', PAGE_KEYWORDS_FAQ, $html);
$html = str_replace ('{description}', PAGE_DESCRIPTION_FAQ, $html);
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


_Log("[/faq/index.php] end.");
echo $html;

?>
