<?php
/*
 * [���������Ω.JP �ġ���]
 * �桼������Ͽ�ڡ���
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
_Log("[/regist/index.php] start.");


_Log("[/regist/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/regist/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/regist/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/regist/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
// ��������Ѥߤξ�硢�桼�����ȥåץڡ��������ܤ���
$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
if (!empty($loginInfo)) {
    header('Location: /user');
    exit;
}

$loginInfo = null;

//���Υڡ����ϡ�������Ͽ�Τߤˤʤä���
//���ߡ����������������ꤹ�롣��������Ͽ�ѡ�
$loginInfo['usr_auth_id'] = AUTH_NON;

////�������󤷤Ƥ��뤫��
//if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
////	_Log("[/regist/index.php] �������󤷤Ƥ��ʤ��ʤΤǥ���������̤�ɽ�����롣");
////	_Log("[/regist/index.php] end.");
////	//����������̤�ɽ�����롣
////	header("Location: ".URL_LOGIN);
////	exit;
//
//	//���ߡ����������������ꤹ�롣��������Ͽ�ѡ�
//	$loginInfo['usr_auth_id'] = AUTH_NON;
//} else {
//	//������������������롣
//	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
//
//	//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢����������̤����ܤ��롣
//	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
//}
//ǧ�ڥ����å�----------------------------------------------------------------------end



//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- start
_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼��������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼��������) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼��������) �ڼ��ԡ�");
}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

_Log("[/regist/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = null;
////�������󤷤Ƥ��뤫��
//if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	$pageTitle = PAGE_TITLE_REGIST_LOGIN;
//} else {
//	$pageTitle = PAGE_TITLE_REGIST;
//}
$pageTitle = PAGE_TITLE_REGIST;

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
$xmlName = XML_NAME_REGIST;//XML�ե�����̾�����ꤹ�롣
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
//		//XML�ե�����̾
//		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
		//�������å�ID
		$id = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);

		//�����ͤ�������롣
		$info = $_POST;
		_Log("[/regist/index.php] POST = '".print_r($info,true)."'");
		//�Хå�����å�����������
		$info = _StripslashesForArray($info);
		_Log("[/regist/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");

		//��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣���᡼���Ⱦ�ѥ��ʤ�ʸ����������Τǡ�
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/pay/index.php] POST(��Ⱦ�ѥ������ʡפ�����ѥ������ʡפ��Ѵ����롣) = '".print_r($info,true)."'");


		break;
	case 'GET':
//		//XML�ե�����̾
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
		//�������å�ID
		$id = (isset($_GET['id'])?$_GET['id']:null);

		//���ܸ��ڡ���
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);



//		//���¤ˤ�äơ�ɽ������桼������������¤��롣
//		switch($loginInfo['usr_auth_id']){
//			case AUTH_NON://����̵��
//
//				$id = null;
//
//				//���ܸ��ڡ����Ϥɤ�����
//				switch ($pId) {
//					case PAGE_ID_USER://�桼�����ڡ���
//						//��ʬ�Υ桼��������Τ�ɽ�����롣
//						if (isset($loginInfo['usr_user_id']) && !_IsNull($loginInfo['usr_user_id'])) {
//							$id = $loginInfo['usr_user_id'];
//						}
//						break;
//				}
//
//				//�������󤷤Ƥ��뤫��
//				if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
//					//��ʬ�Υ桼��������Τ�ɽ�����롣
//					if (isset($loginInfo['usr_user_id']) && !_IsNull($loginInfo['usr_user_id'])) {
//						$id = $loginInfo['usr_user_id'];
//					}
//				}
//				break;
//		}
		$id = null;

		//����ͤ����ꤹ�롣
		$undeleteOnly4def = false;

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

		//���ܸ��ڡ����򥻥å�������¸���롣
		$_SESSION[SID_USER_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/regist/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/regist/index.php] XML�ե�����̾ = '".$xmlName."'");
_Log("[/regist/index.php] �������å�ID = '".$id."'");


//XML���ɤ߹��ࡣ
$xmlFile = "../common/form_xml/".$xmlName.".xml";
_Log("[/regist/index.php] XML�ե����� = '".$xmlFile."'");
$xmlList = _GetXml($xmlFile);

_Log("[/regist/index.php] XML�ե��������� = '".print_r($xmlList,true)."'");

//�桼����ID�����ꤵ��Ƥ����硢�����ʤΤǡ������ѵ���׹��ܤ������롣
if (!_IsNull($id)) {
	$xmlList = _DeleteXmlByTagAndValue($xmlList, 'item_id', 'usr_rule');
	_Log("[/regist/index.php] XML�ե���������(�����ѵ���׹��ܤ�����) = '".print_r($xmlList,true)."'");
}


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
		$message = "�����Ϥ˸��꤬����ޤ���\n".$message;
		$errorFlag = true;
	}
}
//���ܥ��󤬲����줿���
elseif ($_POST['back'] != "") {
}
//�����ܥ��󤬲����줿���
elseif ($_POST['go'] != "") {

	//�᡼�륢�ɥ쥹�ν�ʣ�����å�(�ƥ����å�)
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
		//���顼��̵����硢��Ͽ���롣

		//������Ͽ����
		$newFlag = false;
		if (_IsNull($info['condition']['_id_'])) $newFlag = true;


		//��������Ͽ�򤹤롣(��$info�Ϻǿ�����˹�������롣)
		$res = _UpdateInfo($info);
		if ($res === false) {
			//���顼��ͭ����
			$message = "��Ͽ�˼��Ԥ��ޤ�����";
			$errorFlag = true;
		} else {

			if ($newFlag) {
				//�᡼����ʸ�ζ�����ʬ�����ꤹ�롣
				$body = null;
				$body .= _CreateMailAll($xmlList, $info);//�����λ����Ǥϡ�$info�ˡ����ѵ���פ������ͤϺ������Ƥ��롣���᡼��ˤϻȤ��ʤ���

				_Log("[/regist/index.php] �᡼����ʸ(_CreateMailAll) = '".$body."'");

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
				$adminBody .= "��".$siteTitle."�٤˥桼������Ͽ������ޤ�����\n";
				$adminBody .= "**************************************************************************************\n";
				$adminBody .= "\n";
				$adminBody .= $body;

				$adminBody .= "\n";
				$adminBody .= "\n";
				$adminBody .= "postpostpostpostpostpostpostpostpostpostpostpostpostpostpost\n";
				$adminBody .= "\n";
				$adminBody .= _GetPostAddress($info);



				//�������ѥ᡼����ʸ�����ꤹ�롣
				$customerBody = "";
				$customerBody .= $info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." ��\n";
				$customerBody .= "\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "�����٤ϡ���".$siteTitle."�٤˥桼������Ͽ���Ƥ����������꤬�Ȥ��������ޤ�����\n";
				$customerBody .= "��ǧ�Τ��ᡢ�����ˤ����ͤΤ���Ͽ�����Ƥ��Τ餻�������ޤ���\n";
				$customerBody .= "**************************************************************************************\n";
				$customerBody .= "\n";
				$customerBody .= $body;


				//�������ѥ����ȥ�����ꤹ�롣
				$adminTitle = "[".$siteTitle."] �桼������Ͽ (".$info['update']['tbl_user']['usr_family_name']." ".$info['update']['tbl_user']['usr_first_name']." ��)";
				//�������ѥ����ȥ�����ꤹ�롣
				$customerTitle = "[".$siteTitle."] �桼������Ͽ���꤬�Ȥ��������ޤ���";

				mb_language("Japanese");

				$parameter = "-f ".$clientMail;

				//�᡼������
				//�����ͤ��������롣
				$rcd = mb_send_mail($info['update']['tbl_user']['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

				//���饤����Ȥ��������롣
				$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_user']['usr_e_mail']);

				//�ޥ��������������롣
				foreach($masterMailList as $masterMail){
					$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$info['update']['tbl_user']['usr_e_mail']);
				}


				//��å����������ꤹ�롣
				$message .= $info['update']['tbl_user']['usr_family_name']."&nbsp;".$info['update']['tbl_user']['usr_first_name'];
				$message .= "&nbsp;��";
				$message .= "\n";
				$message .= "\n";
				$message .= "�����٤ϡ���".$siteTitle."�٤˥桼������Ͽ���Ƥ����������꤬�Ȥ��������ޤ�����";
				$message .= "\n";
				$message .= "�����ͤΥ᡼�륢�ɥ쥹���Ƥˤ���Ͽ���ƤΡֳ�ǧ�᡼��פ���ư��������ޤ�����";
				$message .= "\n";
				$message .= "\n";
//				$message .= "���ֳ�ǧ�᡼��פ��Ϥ��ʤ����ϡ��᡼�륢�ɥ쥹������Ͽ�ߥ��β�ǽ��������ޤ��Τǡ�";
//				$message .= "\n";
//				$message .= "&nbsp;&nbsp;&nbsp;������Ǥ���&nbsp;";
				$message .= "�᡼�뤬�Ϥ��ʤ����ϡ�������Ǥ���&nbsp;";
				$message .= "<a href=\"mailto:".$clientMail."\">".$clientMail."</a>";
				$message .= "&nbsp;�ޤǥ᡼��Ǥ��䤤��碌����������";

				$message .= "\n";
				$message .= "\n";
				$message .= "\n";
				$message .= "<a href=\"../login/\" class=\"guide_link\">��������Ϥ����餫��&nbsp;&nbsp;&gt;&gt;&gt;</a>";


				//�����ֹ�˥桼����ID�����ꤹ�롣
				$a8No = $info['update']['tbl_user']['usr_user_id'];
				$message .= "<img src=\"https://px.a8.net/cgi-bin/a8fly/sales?pid=s00000004712020&so=".$a8No."&si=1.1.1.a8\" width=\"1\" height=\"1\">";

				//Yahoo�ꥹ�ƥ��󥰥���С������¬�꥿�� (2012/11/29�ɲ�)
				$message .= "<!-- Yahoo Code for &#26032;&#20250;&#31038;&#35373;&#31435;&#12367;&#12435; Conversion Page -->";
				$message .= "<script type=\"text/javascript\">";
				$message .= "/* <![CDATA[ */";
				$message .= "var yahoo_conversion_id = 1000024393;";
				$message .= "var yahoo_conversion_label = \"zKY4CKjqhAUQsKjEygM\";";
				$message .= "var yahoo_conversion_value = 0;";
				$message .= "/* ]]> */";
				$message .= "</script>";
				$message .= "<script type=\"text/javascript\" src=\"http://i.yimg.jp/images/listing/tool/cv/conversion.js\">";
				$message .= "</script>";
				$message .= "<noscript>";
				$message .= "<div style=\"display:inline;\">";
				$message .= "<img height=\"1\" width=\"1\" style=\"border-style:none;\" alt=\"\" src=\"http://b91.yahoo.co.jp/pagead/conversion/1000024393/?value=0&amp;label=zKY4CKjqhAUQsKjEygM&amp;guid=ON&amp;script=0&amp;disvt=true\"/>";
				$message .= "</div>";
				$message .= "</noscript>";

                activateCompany($info['update']['tbl_user']);

                // sponsors.vc�Υ�������Ȥ��������
                createSponsorsAccount($info);

			} else {
				//��å����������ꤹ�롣
				$message .= "�������ޤ�����";


				//��ʬ�Υ桼��������򹹿�������硢���å����Υ������������񤭤��롣
				if ($info['condition']['_id_'] == $loginInfo['usr_user_id']) {
					_Log("[/regist/index.php] ���å������� \$_SESSION (����������󹹿���) = '".print_r($_SESSION,true)."'");
					$_SESSION[SID_LOGIN_USER_INFO] = $info['update']['tbl_user'];
					_Log("[/regist/index.php] ���å������� \$_SESSION (����������󹹿���) = '".print_r($_SESSION,true)."'");
				}
			}






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

			//��λ���̤�ɽ�����롣
			$mode = 3;
		}

	} else {
		//���顼��ͭ����
		$message = "�����Ϥ˸��꤬����ޤ���\n".$message;
		$errorFlag = true;
	}

}



//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/regist/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/regist/index.php] mode = '".$mode."'");






//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../img/maincontent/pt_regist.jpg\" title=\"\" alt=\"�����ѿ�����\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//������ץȤ����ꤹ�롣
$script = null;

//�����ɥ�˥塼�����ꤹ�롣
$sidebar = null;

//if (isset($_SESSION[SID_USER_FROM_PAGE_ID]) && !_IsNull($_SESSION[SID_USER_FROM_PAGE_ID])) {
//�������󤷤Ƥ��뤫��
if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
	//����URL
	$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
	//��������桼����̾
	$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($_SESSION[SID_LOGIN_USER_INFO]), $htmlSidebarUserMenu);
	//���ߤ����Ͼ���
	$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);

	$sidebar .= $htmlSidebarUserMenu;
} else {
	//����URL
	$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);

	$sidebar .= $htmlSidebarLogin;
}


//�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
////if (isset($_SESSION[SID_USER_FROM_PAGE_ID]) && !_IsNull($_SESSION[SID_USER_FROM_PAGE_ID])) {
//if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
//	_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
//	_SetBreadcrumbs(PAGE_DIR_REGIST, '', PAGE_TITLE_REGIST_LOGIN, 3);
//} else {
//	_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
//	_SetBreadcrumbs(PAGE_DIR_REGIST, '', PAGE_TITLE_REGIST, 2);
//}
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_REGIST, '', PAGE_TITLE_REGIST, 2);

//�ѥ󤯤��ꥹ�Ȥ�������롣
$breadcrumbs = _GetBreadcrumbs();

//WOOROM�եå�������
$wooromFooter = getWooromFooter();


//�ƥ�ץ졼�Ȥ��Խ����롣(ɬ�ײս���ִ����롣)
//�����ȥ�
if (!_IsNull($title)) $title = "[".$title."] ";
$title = $siteTitle." ".$title;
$html = str_replace('{title}', $title, $html);
//�᥿����
$html = str_replace ('{keywords}', PAGE_KEYWORDS_REGIST, $html);
$html = str_replace ('{description}', PAGE_DESCRIPTION_REGIST, $html);
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


_Log("[/regist/index.php] end.");
echo $html;




















function _GetPostAddress($info) {

	$condition = array();
	$condition['id'] = $info['update']['tbl_user']['usr_pref_id'];
	$mstPrefInfo = _DB_GetInfo('mst_pref', $condition, true, 'del_flag');

	$name = $info['update']['tbl_user']['usr_family_name'].$info['update']['tbl_user']['usr_first_name'];
	$name = str_replace(' ', '', $name);
	$name = str_replace('��', '', $name);
	$zip = $info['update']['tbl_user']['usr_zip1']."-".$info['update']['tbl_user']['usr_zip2'];
	$address = $mstPrefInfo['name'].$info['update']['tbl_user']['usr_address1'].$info['update']['tbl_user']['usr_address2'];
	$tel = $info['update']['tbl_user']['usr_tel1']."-".$info['update']['tbl_user']['usr_tel2']."-".$info['update']['tbl_user']['usr_tel3'];


	//���Ϥ���˾��
	$deliverDate = null;
	//���Ϥ���˾������
	$deliverTime = null;
//	switch ($info['update']['tbl_user']['time_assign']) {
//		case '������';
//			$deliverTime = '01';
//			break;
//		case '12��00��14��00';
//			$deliverTime = '12';
//			break;
//		case '14��00��16��00';
//			$deliverTime = '14';
//			break;
//		case '16��00��18��00';
//			$deliverTime = '16';
//			break;
//		case '18��00��21��00';
//			$deliverTime = '04';
//			break;
//	}

	//����פ����ꤹ�롣
	$totalPrice = null;
	$totalPriceTax = null;

	$cmpZip = "104-0061";
	$cmpAddress = "������������1����15-7�ޥå���¥ӥ�503";
	$cmpName = "������ξ�����ʸ��̳��";
	$cmpTel = "03-3564-1156";


	$res = null;
	$res .= $deliverDate;
	$res .= "\t";
	$res .= $deliverTime;
	$res .= "\t";
	$res .= $zip;
	$res .= "\t";
	$res .= $address;
	$res .= "\t";
	$res .= $name;
	$res .= "\t";
	$res .= $tel;
	$res .= "\t";
	$res .= "\t";
	$res .= $cmpZip;
	$res .= "\t";
	$res .= $cmpAddress;
	$res .= "\t";
	$res .= $cmpName;
	$res .= "\t";
	$res .= $cmpTel;
	$res .= "\t";
	$res .= $totalPrice;
	$res .= "\t";
	$res .= $totalPriceTax;

	return $res;
}

/**
 * sponsors.vc�Υ�������Ȥ��������
 *
 * @param $info
 *
 * @return bool
 */
function createSponsorsAccount($info)
{
    _Log(print_r($info, true));
    $siteId = 3;
    $key = 'c8a2430da8900159db021ae06040d5b746f15327';
    $email = $info['update']['tbl_user']['usr_e_mail'];
    $password = $info['update']['tbl_user']['usr_pass'];

    require 'Crypt/Blowfish.php';

    $text = serialize(array('email' => $email, 'password' => $password));
    _Log(print_r($text, true));

    $blowfish = new Crypt_Blowfish($key);

    if (($encrypted = $blowfish->encrypt($text)) === false) {
        return false;
    }
    $string = urlencode(base64_encode($encrypted));
    _Log("string = {$string}");

    $postData = array('site' => $siteId, 'string' => $string);

    $url = 'http://www.sponsors.vc/api/create_user.php';
    if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
        $url = 'http://dev.sponsors.vc/api/create_user.php';
    }
    _Log("url = {$url}");

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errNo = curl_errno($ch);
    $errStr = curl_error($ch);
    curl_close($ch);
    _Log($res);
    _Log("httpCode = {$httpCode}");
    _Log("errNo = {$errNo}");
    _Log("errStr = {$errStr}");

    if ($httpCode >= 400 || $errNo != 0) {
        return false;
    }

    return true;
}