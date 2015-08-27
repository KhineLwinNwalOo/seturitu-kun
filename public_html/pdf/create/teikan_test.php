<?php
/*
 * [��������]
 * PDF����
 * �괾
 *
 * ��������2008/11/05	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");
//include_once("../../common/libs/fpdf/mbfpdf.php");
include_once("../../common/libs/fpdf/mbfpdf_test.php");


_LogDelete();
//_LogBackup();
_Log("[/pdf/create/teikan.php] start.");

_Log("[/pdf/create/teikan.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/create/teikan.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/create/teikan.php] SERVER = '".print_r($_SERVER,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/create/teikan.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/pdf/create/teikan.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ".URL_BASE);
	exit;
}
//����������������롣
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
//ǧ�ڥ����å�----------------------------------------------------------------------end



//DB�򥪡��ץ󤹤롣
$link = _DB_Open();

//���顼��å�����
$errorList = array();

$inData = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		$inData = $_POST;
		break;
	case 'GET':
		$inData = $_GET;
		break;
}

//��礻ID
$inquiryId = (isset($inData['id'])?$inData['id']:null);

//������
$pdfCreateYear = (isset($inData['year'])?$inData['year']:date('Y'));
$pdfCreateMonth = (isset($inData['month'])?$inData['month']:date('n'));
$pdfCreateDay = (isset($inData['day'])?$inData['day']:date('j'));



//���½����ɲ�
switch ($loginInfo['mng_auth_id']) {
	case AUTH_NON:
		//����̵��
		
		//�桼����ID������礻����򸡺����롣����礻ID��������롣
		if (isset($loginInfo['tbl_user'])) {
			$condition4inq = array();
			$condition4inq['inq_user_id'] = $loginInfo['tbl_user']['usr_user_id'];	//�ܵ�ID
			$tblInquiryList = _DB_GetList('tbl_inquiry', $condition4inq, true, null, 'inq_del_flag');
			if (!_IsNull($tblInquiryList)) {
				//�������Ƭ�������Ǥ��ļ��Ф�
				$tblInquiryInfo = array_shift($tblInquiryList);
				$inquiryId = $tblInquiryInfo['inq_inquiry_id'];
			} else {
				$inquiryId = null;
			}
		} else {
			$inquiryId = null;
		}
		
		break;
}

$inquiryInfo = null;
if (!_IsNull($inquiryId)) {
	//��礻�����������롣
	$inquiryInfo = _GetInquiryInfo($inquiryId, false);
}

if (_IsNull($inquiryInfo)) {
	$errorList[] = "����������礻����¸�ߤ��ޤ���";

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//���顼���̤�ɽ�����롣
	header("Location: ../error.php");
	exit;
}

//�ޥ��������������롣
$undeleteOnly = false;
$mstPrefList = _GetMasterList('mst_pref', $undeleteOnly);						//��ƻ�ܸ��ޥ���
//$mstStockTotalNumList = _GetMasterList('mst_stock_total_num', $undeleteOnly);	//ȯ�Բ�ǽ��������ޥ���
$mstStockPriceList = _GetMasterList('mst_stock_price', $undeleteOnly);			//1����ñ���ޥ���
$mstJusticeTypeList = _GetMasterList('mst_justice_type', $undeleteOnly);		//�ζȥ����ץޥ���

//��ҥ�����_�򿦥ޥ���
$condition4mst = array();
$condition4mst['company_type_id'] = $inquiryInfo['tbl_company']['cmp_company_type_id'];//��ҥ�����ID
$order4mst = "lpad(show_order,10,'0'),id";
$mstCompanyTypePostList = _DB_GetList('mst_company_type_post', $condition4mst, true, $order4mst, 'del_flag', 'id');




//���--------------------------------------------start
//�ե���ȥ�������������롣
//�̾�
$normalFontSize = 10;

//�����ȥ�
$title = "�괾";


//[�ǥХå���]
//�ܡ�����
$border = 1;
//���--------------------------------------------end


// EUC-JP->SJIS �Ѵ���ưŪ�˹Ԥʤ碌����� mbfpdf.php ��� $EUC2SJIS ��
// true �˽������뤫�����Τ褦�˼¹Ի��� true �����ꤷ�Ƥ��Ѵ����Ƥ��ޤ���
//$GLOBALS['EUC2SJIS'] = true;

//PDF�Υ����������ꤹ�롣�ǥե����=FPDF($orientation='P',$unit='mm',$format='A4')
$pdf=new MBFPDF();

//�ե���Ȥ����ꤹ�롣
$pdf->AddMBFont(GOTHIC ,'SJIS');
$pdf->AddMBFont(PGOTHIC,'SJIS');
$pdf->AddMBFont(MINCHO ,'SJIS');
$pdf->AddMBFont(PMINCHO,'SJIS');
$pdf->AddMBFont(KOZMIN ,'SJIS');

//�ޡ���������ꤹ�롣
$pdf->SetLeftMargin(20);
$pdf->SetRightMargin(20);
$pdf->SetTopMargin(20);


$pdf->SetFont(MINCHO,'',$normalFontSize);

//��ư���ڡ����⡼�ɤ�ON(true)���ڡ����β�ü����ε�Υ�ʥޡ�����ˤ�2 mm�ˤʤä���硢���Ԥ���褦�����ꤹ�롣
$pdf->SetAutoPageBreak(true, 20);

//�ɥ�����ȤΥ����ȥ�����ꤹ�롣
$pdf->SetTitle($title);
//�ɥ�����Ȥμ���(subject)�����ꤹ�롣
$pdf->SetSubject($title);



$pdf->AddPage();

//���̾
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$inquiryInfo['tbl_company']['cmp_company_name'],$border,0,"C");
$pdf->Ln(20);


//�����ȥ�
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


//�裱�� �� §
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裱�� �� §",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);



//test start
$pdf->Cell(0,6,$pdf->CurrentFont['type']."/1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890",$border,0,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345��67890123456789012345678901234567890",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234��567890123456789012345678901234567890",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"��������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������������",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"����Ҥ�ȯ�Ԥ�������Ϥ��٤ƾ������³����Ȥ����������Ϥˤ���������ˤϡ���ɽ������Ρ�����ǧ�����פ��롣",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"����Ҥ�ȯ�Ԥ�������Ϥ��٤ƾ������³����Ȥ����������Ϥˤ��������뤢�������������������ˤϡ���ɽ������ξ�ǧ���פ��롣",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"�����������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ���������������������������������",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"�����������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ����������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ���������������������������������",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"�����������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ����������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ����������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ����������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ���������������������������������",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"�����������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ����������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ����������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ����������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ���������������������������������",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"�����������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ������������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ������������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ������������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ���������������������������������",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"�����������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ���������������������������������",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"�����������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ����������������������������������������סˡ��������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ����������������������������������������סˡ��������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ����������������������������������������סˡ��������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ��������������������������������������������������������������������ĤƤȤʤˤ̤ͤΤϤҤդؤۤ���������������������������������",$border,"L");
$pdf->Ln(10);
//test end


$no = 0;

//�ʾ����
$pdf->Cell(0,6,"�ʾ����",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$companyName = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name'])) $companyName .= $inquiryInfo['tbl_company']['cmp_company_name'];
$companyNameEn = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name_en'])) $companyNameEn .= $inquiryInfo['tbl_company']['cmp_company_name_en'];

$buf = null;
if (!_IsNull($companyName)) {
	$buf .= "����Ҥϡ�";
	$buf .= $companyName;
	if (!_IsNull($companyNameEn)) {
		$buf .= "��";
		$buf .= "��ʸ̾��";
		$buf .= $companyNameEn;
		$buf .= "��";
		$buf .= "��";
	}
	$buf .= "�ȾΤ��롣";
} else {
	$errorList[] = "�ز��̾��ˡ��̾�٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//����Ū��
$pdf->Cell(0,6,"����Ū��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");


//�ʲ��ϡ����̤ʤ�Ρ���礻ID=19�ѡ�
$purpose = null;
if ($inquiryId == 19) {
	$purpose .= "���� ����ݸ�ˡ�˴�Ť����ε��𥵡��ӥ����ȵڤӲ��ͽ�ɥ����ӥ�����\n";
	$purpose .= "��(1)ˬ����ڤӲ��ͽ��ˬ����\n";
	$purpose .= "��(2)ˬ��������ڤӲ��ͽ��ˬ��������\n";
	$purpose .= "��(3)ˬ��Ǹ�ڤӲ��ͽ��ˬ��Ǹ�\n";
	$purpose .= "��(4)�̽���ڤӲ��ͽ���̽���\n";
	$purpose .= "��(5)û������������ڤӲ��ͽ��û������������\n";
	$purpose .= "��(6)������������������ڤӲ��ͽ��������������������\n";
	$purpose .= "��(7)ʡ���Ѷ���Ϳ�ڤӲ��ͽ��ʡ���Ѷ���Ϳ\n";
	$purpose .= "��(8)����ʡ���Ѷ�����ڤ�������ͽ��ʡ���Ѷ�����\n";
	$purpose .= "���� ����ݸ�ˡ�˴�Ť�������ٱ����\n";
	$purpose .= "���� ����ݸ�ˡ�˴�Ť��ϰ�̩�巿�����ӥ��ڤ��ϰ�̩�巿���ͽ�ɥ����ӥ�\n";
	$purpose .= "��(1)�����б���ˬ����\n";
	$purpose .= "��(2)ǧ�ξ��б����̽���ڤӲ��ͽ��ǧ�ξ��б����̽���\n";
	$purpose .= "��(3)������¿��ǽ��������ڤӲ��ͽ�ɾ�����¿��ǽ��������\n";
	$purpose .= "��(4)ǧ�ξ��б�����Ʊ������ڤӲ��ͽ��ǧ�ξ��б�����Ʊ������\n";
	$purpose .= "��(5)�ϰ�̩�巿������������������\n";
	$purpose .= "���� �㳲�Լ�Ω�ٱ�ˡ�˴�Ť����ξ㳲ʡ�㥵���ӥ�����\n";
	$purpose .= "��(1)������\n";
	$purpose .= "��(2)����ˬ����\n";
	$purpose .= "��(3)��ư���\n";
	$purpose .= "���� �㳲�Լ�Ω�ٱ�ˡ�˴�Ť��ϰ�����ٱ���Ȥΰ�ư�ٱ����\n";
	$purpose .= "���� ���̾���ι�Ҽ�ư�ֱ�������\n";
	$purpose .= "���� ���ƹ�����Ӥ�����ڤζ�̳\n";

	$purpose = mb_convert_kana($purpose, 'N');
} else {
	$i = 0;
	foreach ($inquiryInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
		if (!_IsNull($purposeInfo['cmp_pps_purpose'])) {
			$purpose .= (++$i).". ".$purposeInfo['cmp_pps_purpose']."\n";
		}
	}
	if (!_IsNull($purpose)) {
		$purpose = "����Ҥϡ����λ��Ȥ�Ĥळ�Ȥ���Ū�Ȥ��롣\n".$purpose;
		$purpose .= (++$i).". "."�嵭�ƹ�����Ӵ�Ϣ������ڤλ���\n";
		
		$purpose = mb_convert_kana($purpose, 'N');
	} else {
		$errorList[] = "����Ū�٤���Ͽ���Ƥ���������";
	}
}


//�ʲ��������ʤ�Ρ�
if (false) {

$purpose = null;
$i = 0;
foreach ($inquiryInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (!_IsNull($purposeInfo['cmp_pps_purpose'])) {
		$purpose .= (++$i).". ".$purposeInfo['cmp_pps_purpose']."\n";
	}
}
if (!_IsNull($purpose)) {
	$purpose = "����Ҥϡ����λ��Ȥ�Ĥळ�Ȥ���Ū�Ȥ��롣\n".$purpose;
	$purpose .= (++$i).". "."�嵭�ƹ�����Ӵ�Ϣ������ڤλ���\n";
	
	$purpose = mb_convert_kana($purpose, 'N');
} else {
	$errorList[] = "����Ū�٤���Ͽ���Ƥ���������";
}

}

$pdf->MultiCell(0,6,$purpose,$border,"L");
$pdf->Ln();


//����Ź�ν���ϡ�
$pdf->Cell(0,6,"����Ź�ν���ϡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$companyAddress = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_pref_id'])) $companyAddress .= $mstPrefList[$inquiryInfo['tbl_company']['cmp_pref_id']]['name'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_address1'])) $companyAddress .= $inquiryInfo['tbl_company']['cmp_address1'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_address2'])) $companyAddress .= $inquiryInfo['tbl_company']['cmp_address2'];
if (!_IsNull($companyAddress)) {
	$companyAddress = "����Ҥϡ���Ź��".$companyAddress."���֤���";

	$companyAddress = mb_convert_kana($companyAddress, 'N');
} else {
	$errorList[] = "����Ź����ϡ٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$companyAddress,$border,"L");
$pdf->Ln();


//�ʸ������ˡ��
$pdf->Cell(0,6,"�ʸ������ˡ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥθ���ϡ�����˷Ǻܤ�����ˡ�ˤ��Ԥ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//�裲�� �� ��
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裲�� �� ��",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//��ȯ�Բ�ǽ���������
$pdf->Cell(0,6,"��ȯ�Բ�ǽ���������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$stockTotalNum = null;
//if (!_IsNull($inquiryInfo['tbl_company']['cmp_stock_total_num_id'])) $stockTotalNum .= $mstStockTotalNumList[$inquiryInfo['tbl_company']['cmp_stock_total_num_id']]['name'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_stock_total_num'])) $stockTotalNum .= $inquiryInfo['tbl_company']['cmp_stock_total_num'];
if (!_IsNull($stockTotalNum)) {
//	$stockTotalNum = "����Ҥ�ȯ�Բ�ǽ����������ϡ�".$stockTotalNum."�Ȥ��롣";
	//��nn��nnnn���פΤ褦�˴�����ɽ���ˤ��롣
	$stockTotalNum = _ConvertNum2Ja($stockTotalNum);
	$