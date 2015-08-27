<?php
/*
 * [��������]
 * ���󹹿�����
 *
 * ��������2008/05/30	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
//session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/info/index.php] start.");

_Log("[/info/index.php] POST = '".print_r($_POST,true)."'");
_Log("[/info/index.php] GET = '".print_r($_GET,true)."'");
_Log("[/info/index.php] SERVER = '".print_r($_SERVER,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/info/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/info/index.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ".URL_BASE);
	exit;
}
//����������������롣
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
//ǧ�ڥ����å�----------------------------------------------------------------------end



//�ܥե������̾�Τ�������롣
$phpName = basename($_SERVER['PHP_SELF']);
//�ե�����Υ������������ꤹ�롣
//$formAction = SSL_URL_THE_LIFEBOAT_COM_INQ.'/'.$phpName;
$formAction = $_SERVER['PHP_SELF'];

//�̾��URL(SSL�ǤϤʤ�URL)
$urlBase = URL_BASE;

//������̾
$clientName = ADMIN_TITLE;

//���֥���ǥå���
$tabindex = 0;

//DB�򥪡��ץ󤹤롣
$cid = _DB_Open();

//�ޥ��������������롣
$undeleteOnly = true;

//ư��⡼��{1:����/2:��ǧ/3:��λ/4:���顼}
$mode = 1;

//����ɽ�����뤫��hidden���ܤ�ɽ�����뤫��{true:����ɽ�����롣/false:XML���ꡢ���¤ˤ��ɽ��̵ͭ�˽�����}
$allShowFlag = false;

//��å�����
$message = "";
//���顼�ե饰
$errorFlag = false;

//��å�����
$message4js = "";


//�������åȾ�����Ǽ��������
$info = array();

//�ѥ�᡼������������롣
$xmlName = null;
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		//XML�ե�����̾
		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
		//�������å�ID
		$id = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);

		//�����ͤ�������롣
		$info = $_POST;
		_Log("[/info/index.php] POST = '".print_r($info,true)."'");
		//�Хå�����å�����������
		$info = _StripslashesForArray($info);
		_Log("[/info/index.php] POST(�Хå�����å�����������) = '".print_r($info,true)."'");
	
		break;
	case 'GET':
		//XML�ե�����̾
		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
		//�������å�ID
		$id = (isset($_GET['id'])?$_GET['id']:null);

		//���ܸ��ڡ���
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);


		//����ͤ����ꤹ�롣
		$undeleteOnly4def = false;
		switch ($xmlName) {
			case XML_NAME_ITEM:
				//���ʾ���
				break;
			case XML_NAME_BOTTLE_IMAGE:
				//�ܥȥ��������
				$undeleteOnly4def = true;//̤����ǡ����Τ�
				break;
			case XML_NAME_DESIGN_IMAGE:
				//Ħ��ѥ������������
				$undeleteOnly4def = true;//̤����ǡ����Τ�
				break;
			case XML_NAME_CHARACTER_J_IMAGE:
				//Ħ��ʸ��(�»�)��������
				$undeleteOnly4def = true;//̤����ǡ����Τ�
				break;
			case XML_NAME_CHARACTER_E_IMAGE:
				//Ħ��ʸ��(�ѻ�)��������
				$undeleteOnly4def = true;//̤����ǡ����Τ�
				break;
			case XML_NAME_INQ_FROM_MAIL:
				//��礻����(�᡼����ʸ������Ͽ��)
				break;
			case XML_NAME_INQ:
				//��礻����
			default:
				//XML�ե�����̾�����ꤹ�롣
				$xmlName = XML_NAME_INQ;
				break;
		}

		
		//���½����ɲ�
		switch ($loginInfo['mng_auth_id']) {
			case AUTH_NON:
				//����̵��
				
				//�������������¤��롣
				switch ($xmlName) {
					case XML_NAME_INQ:
						//��礻����
					default:
						//XML�ե�����̾�����ꤹ�롣
						$xmlName = XML_NAME_INQ;

						//�������å�ID
						$id = null;
						unset($_GET['id']);//��ư��⡼��="ñ��ɽ��"�ˤ��뤿��˥��ꥢ���롣
				
						//���ܸ��ڡ���
						$pId = null;

						
						$undeleteOnly4def = true;//̤����ǡ����Τ�
						
						//�桼����ID������礻����򸡺����롣����礻ID��������롣
						$inquiryId = null;
						if (isset($loginInfo['tbl_user'])) {
							$condition4inq = array();
							$condition4inq['inq_user_id'] = $loginInfo['tbl_user']['usr_user_id'];	//�ܵ�ID
							$tblInquiryList = _DB_GetList('tbl_inquiry', $condition4inq, true, null, 'inq_del_flag');
							if (!_IsNull($tblInquiryList)) {
								//�������Ƭ�������Ǥ��ļ��Ф�
								$tblInquiryInfo = array_shift($tblInquiryList);
								$inquiryId = $tblInquiryInfo['inq_inquiry_id'];
							}
						}
						if (_IsNull($inquiryId)) {
							$message = "����������礻����¸�ߤ��ޤ���\n";
							$errorFlag = true;
							$mode = 4;
						} else {
							//�������å�ID
							$id = $inquiryId;
						}

						break;
				}
				break;
		}


		$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
		
		//XML�ե�����̾���������å�ID�����ͤ��ɲä��롣
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;


		//���ꤵ��Ƥ�����=�����ξ��
		if (isset($_GET['id'])) {
			//ư��⡼�ɤ򥻥å�������¸���롣ư��⡼��="¾���̷�ͳ��ɽ��"
			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_OTHER;
		} else {
			//ư��⡼�ɤ򥻥å�������¸���롣ư��⡼��="ñ��ɽ��"
			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_MENU;
		}

		//���ܸ��ڡ����򥻥å�������¸���롣
		$_SESSION[SID_INFO_FROM_PAGE_ID] = $pId;

		break;	
}

_Log("[/info/index.php] (param) \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/info/index.php] (param) XML�ե�����̾ = '".$xmlName."'");
_Log("[/info/index.php] (param) �������å�ID = '".$id."'");


//XML���ɤ߹��ࡣ
$xmlFile = "../common/form_xml/".$xmlName.".xml";
_Log("[/info/index.php] XML�ե����� = '".$xmlFile."'");
$xmlList = _GetXml($xmlFile);


$mstAftereffectsGrade01List = null;
switch ($xmlName) {
	case XML_NAME_ITEM:
		//���ʾ���
		break;
	case XML_NAME_BOTTLE_IMAGE:
		//�ܥȥ��������
		break;
	case XML_NAME_DESIGN_IMAGE:
		//Ħ��ѥ������������
		break;
	case XML_NAME_CHARACTER_J_IMAGE:
		//Ħ��ʸ��(�»�)��������
		break;
	case XML_NAME_CHARACTER_E_IMAGE:
		//Ħ��ʸ��(�ѻ�)��������
		break;
	case XML_NAME_INQ:
		//��礻����
	case XML_NAME_INQ_FROM_MAIL:
		//��礻����(�᡼����ʸ������Ͽ��)
//		$mstAftereffectsGrade01List = _GetMasterList('mst_aftereffects_grade_01', false);		//���㳲����(��)�ޥ���
		break;
}

//��ǧ�ܥ��󤬲����줿���
if ($_POST['confirm'] != "") {

	//�����ͥ����å�
	$message .= _CheackInputAll($xmlList, $info);
	
	
	switch ($xmlName) {
		case XML_NAME_ITEM:
			//���ʾ���
			//�������åץ���
//			$message .= _UploadItemImage($info, $_FILES);
			$message .= _UploadImage($info, $_FILES, 'tbl_item_image', 'itm_img_file_name', FILE_DIR_ITEM_IMG_TMP, 2);
			break;
		case XML_NAME_BOTTLE_IMAGE:
			//�ܥȥ��������
			//�������åץ���
			$message .= _UploadImage($info, $_FILES, 'tbl_bottle_image', 'btl_img_file_name', FILE_DIR_BOTTLE_IMG_TMP, 3);
			break;
		case XML_NAME_DESIGN_IMAGE:
			//Ħ��ѥ������������
			//�������åץ���
			$message .= _UploadImage($info, $_FILES, 'mst_design', 'file_name', FILE_DIR_DESIGN_IMG_TMP, 4);
			break;
		case XML_NAME_CHARACTER_J_IMAGE:
			//Ħ��ʸ��(�»�)��������
			//�������åץ���
			$message .= _UploadImage($info, $_FILES, 'mst_character_j', 'file_name', FILE_DIR_CHARACTER_J_IMG_TMP, 5);
			break;
		case XML_NAME_CHARACTER_E_IMAGE:
			//Ħ��ʸ��(�ѻ�)��������
			//�������åץ���
			$message .= _UploadImage($info, $_FILES, 'mst_character_e', 'file_name', FILE_DIR_CHARACTER_E_IMG_TMP, 6);
			break;
	}

	
	if (_IsNull($message)) {
		//���顼��̵����硢��ǧ���̤�ɽ�����롣
		$mode = 2;
		 
		$message .= "���������Ƥ��ǧ���ơ��ֹ����ץܥ���򲡤��Ƥ���������";
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

//	//�᡼����ʸ�ζ�����ʬ�����ꤹ�롣
//	$body = "";
	$body .= _CreateMailAll($xmlList, $info);

	_Log("[/info/index.php] _CreateMailAll = '".$body."'");


	switch ($xmlName) {
		case XML_NAME_ITEM:
			//���ʾ���

			//�������ξ���_�����ơ��֥�����������롣
			$itemId = $info['condition']['_id_'];
			$oldImageList = null;
			if (!_IsNull($itemId)) {
				$condition = array();
				$condition['itm_img_item_id'] = $itemId;
				$order = null;
				$order .= "lpad(itm_img_show_order,10,'0')";		//ɽ����ξ���
				$order .= ",itm_img_no";							//No�ξ���
				$oldImageList = _DB_GetList('tbl_item_image', $condition, true, $order);
			}

			break;

		case XML_NAME_BOTTLE_IMAGE:
			//�ܥȥ��������

			//�������Υܥȥ�_�����ơ��֥�����������롣(����Ѥߤξ��������������롣�����åץ��ɲ����ΰ�ư������˺���Ѥ߾������Ѥ��롣)
			$condition = null;
			$order = null;
			$order .= "btl_img_image_id";		//�ܥȥ����ID�ξ���
			$oldImageList = _DB_GetList('tbl_bottle_image', $condition, false, $order, 'btl_img_del_flag');

			break;

		case XML_NAME_DESIGN_IMAGE:
			//Ħ��ѥ������������

			//�������Υǥ�����ޥ��������������롣(����Ѥߤξ��������������롣�����åץ��ɲ����ΰ�ư������˺���Ѥ߾������Ѥ��롣)
			$condition = null;
			$order = null;
			$order .= "id";		//ID�ξ���
			$oldImageList = _DB_GetList('mst_design', $condition, false, $order, 'del_flag');

			break;
		case XML_NAME_CHARACTER_J_IMAGE:
			//Ħ��ʸ��(�»�)��������

			//��������ʸ��_�¥ޥ��������������롣(����Ѥߤξ��������������롣�����åץ��ɲ����ΰ�ư������˺���Ѥ߾������Ѥ��롣)
			$condition = null;
			$order = null;
			$order .= "id";		//ID�ξ���
			$oldImageList = _DB_GetList('mst_character_j', $condition, false, $order, 'del_flag');

			break;
		case XML_NAME_CHARACTER_E_IMAGE:
			//Ħ��ʸ��(�ѻ�)��������

			//��������ʸ��_�ѥޥ��������������롣(����Ѥߤξ��������������롣�����åץ��ɲ����ΰ�ư������˺���Ѥ߾������Ѥ��롣)
			$condition = null;
			$order = null;
			$order .= "id";		//ID�ξ���
			$oldImageList = _DB_GetList('mst_character_e', $condition, false, $order, 'del_flag');

			break;

	}


	//��������Ͽ�򤹤롣(��$info�Ϻǿ�����˹�������롣)
	$res = _UpdateInfo($info);
	if ($res === false) {
		//���顼��ͭ����
		$message = "�����˼��Ԥ��ޤ�����";
		$errorFlag = true;	
	} else {
		$message .= "�������ޤ�����\n";
		

		switch ($xmlName) {
			case XML_NAME_ITEM:
				//���ʾ���
	
				//�����ե����������¸�ե�������������ѥե�����˰�ư���롣
				$oldDir = FILE_DIR_ITEM_IMG.'/'.sprintf(FILE_DIR_NAME_FORMAT, $info['condition']['_id_']);
				$newDir = FILE_DIR_ITEM_IMG_TMP.'/'.((isset($info['condition']['_file_upload_temp_dir_']) && !_IsNull($info['condition']['_file_upload_temp_dir_']))?$info['condition']['_file_upload_temp_dir_']:'dummy');
				$newImageList = $info['update']['tbl_item_image'];
//				$res = _UpdateItemImage($oldImageList, $oldDir, $newImageList, $newDir);
				$res = _UpdateImage('itm_img_no', 'itm_img_file_name', $oldImageList, $oldDir, $newImageList, $newDir);
				
				//�����¸�ե����̾�������롣
				unset($info['condition']['_file_upload_temp_dir_']);
				break;

			case XML_NAME_BOTTLE_IMAGE:
				//�ܥȥ��������

				//������Υܥȥ�_�����ơ��֥�����������롣(����Ѥߤξ��������������롣�����åץ��ɲ����ΰ�ư������˺���Ѥ߾������Ѥ��롣)
				$condition = null;
				$order = null;
				$order .= "btl_img_image_id";		//�ܥȥ����ID�ξ���
				$newImageList = _DB_GetList('tbl_bottle_image', $condition, false, $order, 'btl_img_del_flag');

				//�����ե����������¸�ե�������������ѥե�����˰�ư���롣
				$oldDir = FILE_DIR_BOTTLE_IMG;
				$newDir = FILE_DIR_BOTTLE_IMG_TMP.'/'.((isset($info['condition']['_file_upload_temp_dir_']) && !_IsNull($info['condition']['_file_upload_temp_dir_']))?$info['condition']['_file_upload_temp_dir_']:'dummy');
				$res = _UpdateImage('btl_img_image_id', 'btl_img_file_name', $oldImageList, $oldDir, $newImageList, $newDir);
				
				//�����¸�ե����̾�������롣
				unset($info['condition']['_file_upload_temp_dir_']);
				break;

			case XML_NAME_DESIGN_IMAGE:
				//Ħ��ѥ������������

				//������Υǥ�����ޥ��������������롣(����Ѥߤξ��������������롣�����åץ��ɲ����ΰ�ư������˺���Ѥ߾������Ѥ��롣)
				$condition = null;
				$order = null;
				$order .= "id";		//ID�ξ���
				$newImageList = _DB_GetList('mst_design', $condition, false, $order, 'del_flag');

				//�����ե����������¸�ե�������������ѥե�����˰�ư���롣
				$oldDir = FILE_DIR_DESIGN_IMG;
				$newDir = FILE_DIR_DESIGN_IMG_TMP.'/'.((isset($info['condition']['_file_upload_temp_dir_']) && !_IsNull($info['condition']['_file_upload_temp_dir_']))?$info['condition']['_file_upload_temp_dir_']:'dummy');
				$res = _UpdateImage('id', 'file_name', $oldImageList, $oldDir, $newImageList, $newDir);
				
				//�����¸�ե����̾�������롣
				unset($info['condition']['_file_upload_temp_dir_']);
				break;

			case XML_NAME_CHARACTER_J_IMAGE:
				//Ħ��ʸ��(�»�)��������
	
				//�������ʸ��_�¥ޥ��������������롣(����Ѥߤξ��������������롣�����åץ��ɲ����ΰ�ư������˺���Ѥ߾������Ѥ��롣)
				$condition = null;
				$order = null;
				$order .= "id";		//ID�ξ���
				$newImageList = _DB_GetList('mst_character_j', $condition, false, $order, 'del_flag');

				//�����ե����������¸�ե�������������ѥե�����˰�ư���롣
				$oldDir = FILE_DIR_CHARACTER_J_IMG;
				$newDir = FILE_DIR_CHARACTER_J_IMG_TMP.'/'.((isset($info['condition']['_file_upload_temp_dir_']) && !_IsNull($info['condition']['_file_upload_temp_dir_']))?$info['condition']['_file_upload_temp_dir_']:'dummy');
				$res = _UpdateImage('id', 'file_name', $oldImageList, $oldDir, $newImageList, $newDir);
				
				//�����¸�ե����̾�������롣
				unset($info['condition']['_file_upload_temp_dir_']);
				break;

			case XML_NAME_CHARACTER_E_IMAGE:
				//Ħ��ʸ��(�ѻ�)��������

				//�������ʸ��_�ѥޥ��������������롣(����Ѥߤξ��������������롣�����åץ��ɲ����ΰ�ư������˺���Ѥ߾������Ѥ��롣)
				$condition = null;
				$order = null;
				$order .= "id";		//ID�ξ���
				$newImageList = _DB_GetList('mst_character_e', $condition, false, $order, 'del_flag');

				//�����ե����������¸�ե�������������ѥե�����˰�ư���롣
				$oldDir = FILE_DIR_CHARACTER_E_IMG;
				$newDir = FILE_DIR_CHARACTER_E_IMG_TMP.'/'.((isset($info['condition']['_file_upload_temp_dir_']) && !_IsNull($info['condition']['_file_upload_temp_dir_']))?$info['condition']['_file_upload_temp_dir_']:'dummy');
				$res = _UpdateImage('id', 'file_name', $oldImageList, $oldDir, $newImageList, $newDir);
				
				//�����¸�ե����̾�������롣
				unset($info['condition']['_file_upload_temp_dir_']);
				break;

		}


		
		if ($xmlName == XML_NAME_INQ_FROM_MAIL) {
			$message .= "<strong style=\"color:#f00;\">���������̤Υ᡼������Ƥ���Ͽ������ϡ������򥯥�å����Ƥ�����������";
			$message .= "<a style=\"color:#f00;\" href=\"./?xml_name=".XML_NAME_INQ_FROM_MAIL."\" title=\"������Ͽ\">[������Ͽ]</a>";
			$message .= "</strong>\n";
		}
		
		
		//ư��⡼��="¾���̷�ͳ��ɽ��"�ξ�硢����󥯤�ɽ�����롣
		if ($_SESSION[SID_INFO_MODE] == MST_MODE_FROM_OTHER) {

			switch ($xmlName) {
				case XML_NAME_ITEM:
					//���ʾ���
					$message .= "<a href=\"../item/?back\" title=\"���ʰ��������\">[���ʰ��������]</a>\n";
					break;
				case XML_NAME_BOTTLE_IMAGE:
					//�ܥȥ��������
					$message .= "";
					break;
				case XML_NAME_DESIGN_IMAGE:
					//Ħ��ѥ������������
					$message .= "";
					break;
				case XML_NAME_CHARACTER_J_IMAGE:
					//Ħ��ʸ��(�»�)��������
					$message .= "";
					break;
				case XML_NAME_CHARACTER_E_IMAGE:
					//Ħ��ʸ��(�ѻ�)��������
					$message .= "";
					break;
				case XML_NAME_INQ:
					//��礻����
					switch ($_SESSION[SID_INFO_FROM_PAGE_ID]) {
						case PAGE_ID_INQ_PRICE:
							$message .= "<a href=\"../inquiry_price/?back\" title=\"����۰��������\">[����۰��������]</a>\n";
							break;
						default:
							$message .= "<a href=\"../inquiry/?back\" title=\"��礻���������\">[��礻���������]</a>\n";
							break;
					}
					break;
			}

		}
//		//��λ���̤�ɽ�����롣
//		$mode = 3;
	}
}



//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/info/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/info/index.php] mode = '".$mode."'");






////ʸ����HTML����ƥ��ƥ����Ѵ����롣
//$info = _HtmlSpecialCharsForArray($info);

//echo ("\$info='".print_r($info,true)."'");

//�ѥ󤯤��ꥹ�Ⱦ�������ꤹ�롣
$level = 2;
//ư��⡼��="¾���̷�ͳ��ɽ��"�ξ�硢��٥��3�ˤ��롣
if ($_SESSION[SID_INFO_MODE] == MST_MODE_FROM_OTHER) $level = 3;

$breadcrumbsTitle = null;
switch ($xmlName) {
	case XML_NAME_ITEM:
		//���ʾ���
		$breadcrumbsTitle = '���ʾ���';
		break;
	case XML_NAME_BOTTLE_IMAGE:
		//�ܥȥ��������
		$breadcrumbsTitle = '�ܥȥ����';
		break;
	case XML_NAME_DESIGN_IMAGE:
		//Ħ��ѥ������������
		$breadcrumbsTitle = 'Ħ��ѥ��������';
		break;
	case XML_NAME_CHARACTER_J_IMAGE:
		//Ħ��ʸ��(�»�)��������
		$breadcrumbsTitle = 'Ħ���»�����';
		break;
	case XML_NAME_CHARACTER_E_IMAGE:
		//Ħ��ʸ��(�ѻ�)��������
		$breadcrumbsTitle = 'Ħ��ѻ�����';
		break;
	case XML_NAME_INQ:
		//��礻����
	case XML_NAME_INQ_FROM_MAIL:
		//��礻����(�᡼����ʸ������Ͽ��)
		$breadcrumbsTitle = '��礻����';
		break;
}
_SetBreadcrumbs($_SERVER['PHP_SELF'], '', $breadcrumbsTitle, $level);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<link rel="stylesheet" href="../css/import.css" type="text/css" />
<script language="javascript" src="../common/js/util.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/libs/mootools.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/tool_tip.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/resizable.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/aftereffects_grade.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/create_inq_info_from_mail.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/search_mastar/search_mastar.js" type="text/javascript" charset="utf-8"></script>
<?
switch ($xmlName) {
	case XML_NAME_ITEM:
		//���ʾ���
		break;
	case XML_NAME_BOTTLE_IMAGE:
		//�ܥȥ��������
		break;
	case XML_NAME_DESIGN_IMAGE:
		//Ħ��ѥ������������
		break;
	case XML_NAME_CHARACTER_J_IMAGE:
		//Ħ��ʸ��(�»�)��������
		break;
	case XML_NAME_CHARACTER_E_IMAGE:
		//Ħ��ʸ��(�ѻ�)��������
		break;
	case XML_NAME_INQ:
		//��礻����
	case XML_NAME_INQ_FROM_MAIL:
		//��礻����(�᡼����ʸ������Ͽ��)
		if ($mode == 1) {
?>
<script type="text/javascript">
<!--
window.addEvent('domready', function(){
<?
		//���½����ɲ�
		switch ($loginInfo['mng_auth_id']) {
			case AUTH_NON:
				//����̵��
				break;
			default:
?>
	//�ʲ��Ρ���1����2�μ¹Խ���ϡ����ס���2������ȡ��ؾ����٤򥻥åȤ������ˡ�����Ω�������٤�������֤��ä��Ƥ��ޤ���
	//����Ω�������٤���ؾ����٤򥻥åȤ��롣(��1)
	setSelect('inquiry_course', 'inquiry_status', 'mst_inquiry_type_course_status', 'inquiry_type_course_id');
	//����礻�����ס٤������Ω�������٤򥻥åȤ��롣(��2)
	setSelect('inquiry_type', 'inquiry_course', 'mst_inquiry_type_course', 'inquiry_type_id');
<?
				break;
		}
?>
	//�ز�ҥ����ס٤�����򿦡٤򥻥åȤ��롣
	setSelects('company_type', 'company_type_post', 'mst_company_type_post', 'company_type_id');
	//�ز�ҥ����ס٤���ض�̳�٤򥻥åȤ��롣
	setSelects('company_type', 'company_type_duties', 'mst_company_type_duties', 'company_type_id');
});
//-->
</script>
<?
		}
		break;
}
?>
<title><?=$clientName?></title>
</head>

<body id="home" onload="openBox('explain_sub', 'explain', 'explain_close');">
<div id="wrapper">
	<div id="header">
		<?include_once("../common_html/header.php");?>
	</div><!-- End header -->

	<div id="sidebar">
		<?include_once("../common_html/side_menu.php");?>
	</div><!-- End sidebar -->

	<div class="breadcrumbs">
		<?=$breadcrumbs = _GetBreadcrumbs();?>
	</div><!-- End breadcrumbs -->

	<div id="maincontent">
		<?=_GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);?>
	</div><!-- End maincontent -->

	<div class="breadcrumbs">
		<?=$breadcrumbs?>
	</div><!-- End breadcrumbs -->
	
	<div id="footer">
		<?include_once("../common_html/footer.php");?>
	</div><!-- End footer -->

</div><!-- End wrapper -->
</body>
</html>

<?
////DB�򥯥������롣
//_DB_Close($cid);

_Log("[/info/index.php] end.");

?>