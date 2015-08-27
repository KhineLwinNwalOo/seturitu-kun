<?php
/*
 * [��������]
 * PDF��������
 *
 * ��������2008/11/05	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
//session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/pdf/index.php] start.");

_Log("[/pdf/index.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/index.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/index.php] SERVER = '".print_r($_SERVER,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/pdf/index.php] end.");
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

$yearList = _GetYearArray(date('Y') - 2, date('Y') + 2);	//ǯ
$monthList = _GetMonthArray();								//��
$dayList = _GetDayArray();									//��


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

//������
$info['year'] = date('Y');
$info['month'] = date('n');
$info['day'] = date('j');
//�괾������
$info['teikan_year'] = date('Y');
$info['teikan_month'] = date('n');
$info['teikan_day'] = date('j');



//�ѥ�᡼������������롣
$xmlName = null;
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
	
		break;
	case 'GET':
		//XML�ե�����̾
		$xmlName = XML_NAME_INQ;

		//�������å�ID
		$id = (isset($_GET['id'])?$_GET['id']:null);

		//���ܸ��ڡ���
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);


		//����ͤ����ꤹ�롣
		$undeleteOnly4def = false;

		
		//���½����ɲ�
		switch ($loginInfo['mng_auth_id']) {
			case AUTH_NON:
				//����̵��
				
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

_Log("[/pdf/index.php] (param) \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/pdf/index.php] (param) XML�ե�����̾ = '".$xmlName."'");
_Log("[/pdf/index.php] (param) �������å�ID = '".$id."'");


//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
_Log("[/pdf/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");

_Log("[/pdf/index.php] mode = '".$mode."'");


//��ҥ����ץޥ����ˤ�äơ�������򤹤롣
$teikanFile = null;
switch ($info['update']['tbl_company']['cmp_company_type_id']) {
	case MST_COMPANY_TYPE_ID_LLC://LLC
		$teikanFile = "./create/teikan_llc.php";
		break;
	case MST_COMPANY_TYPE_ID_NPO://NPO
	case MST_COMPANY_TYPE_ID_CMP://�������
	default:
		$teikanFile = "./create/teikan.php";
		break;
}




////ʸ����HTML����ƥ��ƥ����Ѵ����롣
//$info = _HtmlSpecialCharsForArray($info);

//echo ("\$info='".print_r($info,true)."'");

//�ѥ󤯤��ꥹ�Ⱦ�������ꤹ�롣
$level = 2;
//ư��⡼��="¾���̷�ͳ��ɽ��"�ξ�硢��٥��3�ˤ��롣
if ($_SESSION[SID_INFO_MODE] == MST_MODE_FROM_OTHER) $level = 3;

$breadcrumbsTitle = 'PDF����';
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

<title><?=$clientName?></title>
</head>

<body id="home">
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
		<h2>PDF����</h2>
		
		<div id="pdfTeikan" class="pdf">
			<h3>�괾</h3>
			<form id="frmPdfTeikan" name="frmPdfTeikan" action="<?=$teikanFile?>" method="post" target="_blank">
				<div class="input">
					�괾��������ޤ���
					<br />
					�����������ꤷ�ơ�PDF�����ܥ���򲡤��Ƥ���������
					<br />
					<br />
					��������
					<br />
					<?_WriteSelect($yearList, 'year', $info['year'], (++$tabindex), false, '&nbsp;');?>ǯ
					<?_WriteSelect($monthList, 'month', $info['month'], (++$tabindex), false, '&nbsp;');?>��
					<?_WriteSelect($dayList, 'day', $info['day'], (++$tabindex), false, '&nbsp;');?>��
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF����" />
				</div>
				<div class="end"></div>
			</form>
		</div>

		<div id="pdfHaraikomi" class="pdf">
			<h3>ʧ��������</h3>
			<form id="frmPdfHaraikomi" name="frmPdfHaraikomi" action="./create/haraikomi.php" method="post" target="_blank">
				<div class="input">
					ʧ���������������ޤ���
					<br />
					�����������ꤷ�ơ�PDF�����ܥ���򲡤��Ƥ���������
					<br />
					<br />
					��������
					<br />
					<?_WriteSelect($yearList, 'year', $info['year'], (++$tabindex), false, '&nbsp;');?>ǯ
					<?_WriteSelect($monthList, 'month', $info['month'], (++$tabindex), false, '&nbsp;');?>��
					<?_WriteSelect($dayList, 'day', $info['day'], (++$tabindex), false, '&nbsp;');?>��
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF����" />
				</div>
				<div class="end"></div>
			</form>
		</div>

		<div id="pdfIninjo" class="pdf">
			<h3>��Ǥ��</h3>
			<form id="frmPdfIninjo" name="frmPdfIninjo" action="./create/ininjo.php" method="post" target="_blank">
				<div class="input">
					��Ǥ����������ޤ���
					<br />
					�����������ꤷ�ơ�PDF�����ܥ���򲡤��Ƥ���������
					<br />
					<br />
					��������
					<br />
					<?_WriteSelect($yearList, 'year', $info['year'], (++$tabindex), false, '&nbsp;');?>ǯ
					<?_WriteSelect($monthList, 'month', $info['month'], (++$tabindex), false, '&nbsp;');?>��
					<?_WriteSelect($dayList, 'day', $info['day'], (++$tabindex), false, '&nbsp;');?>��
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF����" />
				</div>
				<div class="end"></div>
			</form>
		</div>

		<div id="pdfSohusho" class="pdf">
			<h3>ź�ս������ս�</h3>
			<form id="frmPdfSohusho" name="frmPdfSohusho" action="./create/sohusho.php" method="post" target="_blank">
				<div class="input">
					ź�ս������ս��������ޤ���
					<br />
					PDF�����ܥ���򲡤��Ƥ���������
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF����" />
				</div>
				<div class="end"></div>
			</form>
		</div>

		<div id="pdfShodakusho" class="pdf">
			<h3>��Ǥ������</h3>
			<form id="frmPdfShodakusho" name="frmPdfShodakusho" action="./create/shodakusho.php" method="post" target="_blank">
				<div class="input">
					��Ǥ�������������ޤ���
					<br />
					���������괾�����������ꤷ�ơ�PDF�����ܥ���򲡤��Ƥ���������
					<br />
					<br />
					��������
					<br />
					<?_WriteSelect($yearList, 'year', $info['year'], (++$tabindex), false, '&nbsp;');?>ǯ
					<?_WriteSelect($monthList, 'month', $info['month'], (++$tabindex), false, '&nbsp;');?>��
					<?_WriteSelect($dayList, 'day', $info['day'], (++$tabindex), false, '&nbsp;');?>��
					<br />
					�괾��������
					<br />
					<?_WriteSelect($yearList, 'teikan_year', $info['teikan_year'], (++$tabindex), false, '&nbsp;');?>ǯ
					<?_WriteSelect($monthList, 'teikan_month', $info['teikan_month'], (++$tabindex), false, '&nbsp;');?>��
					<?_WriteSelect($dayList, 'teikan_day', $info['teikan_day'], (++$tabindex), false, '&nbsp;');?>��
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF����" />
				</div>
				<div class="end"></div>
			</form>
		</div>

		<div id="pdfInkantodokesho" class="pdf">
			<h3>�����Ͻ�</h3>
			<form id="frmPdfInkantodokesho" name="frmPdfInkantodokesho" action="./create/inkantodokesho.php" method="post" target="_blank">
				<div class="input">
					�����Ͻ��������ޤ���
					<br />
					PDF�����ܥ���򲡤��Ƥ���������
					<input type="hidden" name="id" value="<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" />
				</div>
				<div class="output">
					<input type="image" src="../img/bt_pdf1_1.gif" onmouseover="this.src='../img/bt_printer1_1.gif'" onmouseout="this.src='../img/bt_pdf1_1.gif'" alt="PDF����" />
				</div>
				<div class="end"></div>
			</form>
		</div>

<?
if (false) {
?>
		<a href="./create/teikan.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">�괾</a>
		<br />
		<a href="./create/haraikomi.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">ʧ��������</a>
		<br />
		<a href="./create/ininjo.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">��Ǥ��</a>
		<br />
		<a href="./create/sohusho.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">ź�ս������ս�</a>
		<br />
		<a href="./create/shodakusho.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">��Ǥ������</a>
		<br />
		<a href="./create/inkantodokesho.php?id=<?=$info['update']['tbl_inquiry']['inq_inquiry_id']?>" target="_blank">�����Ͻ�</a>
<?
}
?>
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

_Log("[/pdf/index.php] end.");

?>