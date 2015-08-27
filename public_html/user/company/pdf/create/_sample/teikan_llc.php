<?php
/*
 * [��������]
 * PDF����
 * �괾(��Ʊ�����)
 *
 * ��������2008/11/05	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");
include_once("../../common/libs/fpdf/mbfpdf.php");


_LogDelete();
//_LogBackup();
_Log("[/pdf/create/teikan_llc.php] start.");

_Log("[/pdf/create/teikan_llc.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/create/teikan_llc.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/create/teikan_llc.php] SERVER = '".print_r($_SERVER,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/create/teikan_llc.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/pdf/create/teikan_llc.php] end.");
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
//��ҥ�����_��̳�ޥ���
$condition4mst = array();
$condition4mst['company_type_id'] = $inquiryInfo['tbl_company']['cmp_company_type_id'];//��ҥ�����ID
$order4mst = "lpad(show_order,10,'0'),id";
$mstCompanyTypeDutiesList = _DB_GetList('mst_company_type_duties', $condition4mst, true, $order4mst, 'del_flag', 'id');




//���--------------------------------------------start
//�ե���ȥ�������������롣
//�̾�
$normalFontSize = 10;

//�����ȥ�
$title = "�괾";


//[�ǥХå���]
//�ܡ�����
$border = 0;
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


//�裲�� �Ұ��ڤӽл�
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裲�� �Ұ��ڤӽл�",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//�ʼҰ��λ�̾�ڤӽ��ꡢ�л�ڤ���Ǥ�� 
$pdf->Cell(0,6,"�ʼҰ��λ�̾�ڤӽ��ꡢ�л�ڤ���Ǥ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$totalStockNum = null;
$investment = null;
$investment4last = null;
foreach ($inquiryInfo['tbl_company_investment']['investment_info'] as $key => $investmentInfo) {
	if (_IsNull($investmentInfo['cmp_inv_name'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_pref_id'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_address1'])) continue;
//	if (_IsNull($investmentInfo['cmp_inv_stock_num'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_investment'])) continue;

//	if (_IsNull($totalStockNum)) $totalStockNum = 0;
//	$totalStockNum += $investmentInfo['cmp_inv_stock_num'];

	if (!_IsNull($investment)) $investment .= "\n";

	$investment .= "��";
	$investment .= $investmentInfo['cmp_inv_investment'];
	$investment .= "���� ";
	$investment .= "\n";

	$investment .= $mstPrefList[$investmentInfo['cmp_inv_pref_id']]['name'];
	$investment .= $investmentInfo['cmp_inv_address1'];
	$investment .= $investmentInfo['cmp_inv_address2'];
	$investment .= "\n";

	$investment .= "ͭ����Ǥ�Ұ�    ";
	$investment .= $investmentInfo['cmp_inv_name'];
	$investment .= "\n";

//	$investment .= $investmentInfo['cmp_inv_stock_num'];
//	$investment .= "�� ";

	if (!_IsNull($investment4last)) $investment4last .= "\n\n";

	$investment4last .= "ͭ����Ǥ�Ұ�    ";
	$investment4last .= $investmentInfo['cmp_inv_name'];
}
if (!_IsNull($investment)) {
	$investment = "�Ұ��λ�̾�ڤӽ��ꡢ�л�β��۵ڤ���Ǥ�ϼ��ΤȤ���Ǥ��롣\n".$investment;

	$investment = mb_convert_kana($investment, 'N');
} else {
	$errorList[] = "�ؽл��ۡ٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$investment,$border,"L");
$pdf->Ln();


//�ʻ�ʬ�ξ��ϡ�
$pdf->Cell(0,6,"�ʻ�ʬ�ξ��ϡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "�Ұ��ϡ���Ұ��ξ������ʤ���С����λ�ʬ���������ϰ�����¾�ͤ˾��Ϥ��뤳�Ȥ��Ǥ��ʤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "����ε���ˤ�����餺������Ҥζ�̳�򼹹Ԥ��ʤ��Ұ������λ�ʬ���������ϰ�����¾�ͤ˾��Ϥ���ˤϡ���̳���ԼҰ��������ξ��������ʤ���Фʤ�ʤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//�裳�� ��̳�μ��ԡ���̳���ԼҰ��ڤ���ɽ�Ұ�
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裳�� ��̳�μ��ԡ���̳���ԼҰ��ڤ���ɽ�Ұ�",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//�ʶ�̳���Ԥθ�������̳���ԼҰ�����Ǥ�ڤӲ�Ǥ��
$pdf->Cell(0,6,"�ʶ�̳���Ԥθ�������̳���ԼҰ�����Ǥ�ڤӲ�Ǥ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥζ�̳�ϡ���̳���ԼҰ������Ԥ����ΤȤ�����Ұ���Ʊ�դˤ�ꡢ�Ұ����椫�餳�����Ǥ���롣";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "��̳���ԼҰ��ϡ�¾�μҰ������᤬������ϡ����ĤǤ⤽�ο�̳�μ��Ԥξ�������𤷡����ο�̳����λ������ϡ����ڤʤ����ηв�ڤӷ�̤���𤷤ʤ���Фʤ�ʤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "��̳���ԼҰ��ϡ���Ұ���Ʊ�դˤ���Ǥ���뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//����ɽ�Ұ���
$pdf->Cell(0,6,"����ɽ�Ұ���",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "��̳���ԼҰ�����̾�ʾ夤���硢��̳���ԼҰ��θ������äơ���ɽ�Ұ���̾�ʾ����뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "��ɽ�Ұ��ϡ���Ҥ���ɽ���롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʶ�̳���ԼҰ��ڤ���ɽ�Ұ���������
$pdf->Cell(0,6,"�ʶ�̳���ԼҰ��ڤ���ɽ�Ұ���������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "��̳���ԼҰ��ڤ���ɽ�Ұ��������ϡ��Ұ��β�Ⱦ����Ʊ�դ��ä����롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//�裴�� �Ұ��β����ڤ����
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裴�� �Ұ��β����ڤ����",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//�ʼҰ��β�����
$pdf->Cell(0,6,"�ʼҰ��β�����",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "�����˼Ұ��������������ϡ���Ұ���Ʊ�դ��פ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʿ������Ұ�����Ǥ��
$pdf->Cell(0,6,"�ʿ������Ұ�����Ǥ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥ���Ω��˲��������Ұ��ϡ����β���������������Ҥκ�̳�ˤĤ��Ƥ���Ǥ���餦��ΤȤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//��Ǥ����ҡ�
$pdf->Cell(0,6,"��Ǥ����ҡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "�ƼҰ��ϡ�����ǯ�٤ν�λ�λ��ˤ�������Ҥ򤹤뤳�Ȥ��Ǥ��롣���ξ�硢�ƼҰ��ϣ��������ޤǤ˲�Ҥ���Ҥ�ͽ��򤷤ʤ���Фʤ�ʤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "�ƼҰ��ϡ�����ε���ˤ�����餺���������ʤ���ͳ������Ȥ��ϡ����ĤǤ���Ҥ��뤳�Ȥ��Ǥ��롣���ξ�硢�ƼҰ��ϣ��������ޤǤ˲�Ҥ���Ҥ�ͽ��򤷤ʤ���Фʤ�ʤ�������������Ҥ������ʻ�������Ҥ�����ϡ���Ҥ��Ф���»�������������Ǥ���餦��";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//��ˡ����ҡ�
$pdf->Cell(0,6,"��ˡ����ҡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "�ƼҰ��ϡ����ˡ�裶������ε���ˤ����Ҥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "����ε���ˤ�����餺���Ұ�����˴����������Ϲ�ʻ�ˤ����Ǥ������ˤ����������Ұ�����³�����Ϥ���¾���̾��ѿͤ������Ұ��λ�ʬ�򾵷Ѥ����ΤȤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//�裵�� �Ұ��ν�̾
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裵�� �Ұ��ν�̾",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//�ʼҰ��ν�̾��
$pdf->Cell(0,6,"�ʼҰ��ν�̾��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥϡ���̳�򼹹Ԥ���������ä������ι԰٤����϶�̳�򼹹Ԥ��븢�����ʤ��Τ˶�̳�μ��Ԥ˴�Ϳ������硢��Ұ���Ʊ�դ��äƼҰ����̾���뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "����ε���ˤ�����餺����������ͳ�����������Ұ���Ʊ�դ��äƼҰ����̾���뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//�裶�� �� ��
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裶�� �� ��",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//�ʱĶ�ǯ�١�
$pdf->Cell(0,6,"�ʱĶ�ǯ�١�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$settleMonth = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_settle_month'])) $settleMonth .= $inquiryInfo['tbl_company']['cmp_settle_month'];
if (!_IsNull($settleMonth)) {
	$startMonth = 0;
	if ($settleMonth == 12) {
		$startMonth = 1;
	} else {
		$startMonth = $settleMonth + 1;
	}
	$settleMonth = "����Ҥλ���ǯ�٤ϡ���ǯ".$startMonth."���������ǯ".$settleMonth."�������ޤǤ�ǯ�����Ȥ��롣";
	
	$settleMonth = mb_convert_kana($settleMonth, 'N');
} else {
	$errorList[] = "�ط軻���٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$settleMonth,$border,"L");
$pdf->Ln();


//�����פ�������
$pdf->Cell(0,6,"�����פ�������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "���פ������ϡ������ǯ�٤��������ߤμҰ���ʬ�ۤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//��»��ʬ�ۤγ���
$pdf->Cell(0,6,"��»��ʬ�ۤγ���",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "�ƼҰ���»��ʬ�ۤγ��ϡ���Ұ���Ʊ�դˤ�ꡢ�л�β��ۤȰۤʤ���ˤ�뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//�裷�� �� §
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裷�� �� §",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//�ʺǽ�λ���ǯ�١�
$pdf->Cell(0,6,"�ʺǽ�λ���ǯ�١�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$settleMonth = null;
$foundYear = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_settle_month'])) $settleMonth .= $inquiryInfo['tbl_company']['cmp_settle_month'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_found_year'])) $foundYear .= $inquiryInfo['tbl_company']['cmp_found_year'];
if (!_IsNull($settleMonth) && !_IsNull($foundYear)) {
	//�����������Ѵ����롣(ʿ���Τ�)
	$nextYear = $foundYear + 1;
	$heiseiYear = _ConvertAD2Jp($nextYear);

	$settleMonth = "����Ҥκǽ�λ���ǯ�٤ϡ�����Ҥ���Ω�������顢".$heiseiYear."ǯ".$settleMonth."�������ޤǤȤ��롣";
	
	$settleMonth = mb_convert_kana($settleMonth, 'N');
} else {
	//�軻���ϡ��嵭�ǥ����å��Ѥߡ�
	//if (_IsNull($settleMonth)) $errorList[] = "�ط軻���٤���Ͽ���Ƥ���������";
	if (_IsNull($foundYear)) $errorList[] = "����Ωǯ�����٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$settleMonth,$border,"L");
$pdf->Ln();


//����Ω�˺ݤ�����ܶ��
$pdf->Cell(0,6,"����Ω�˺ݤ�����ܶ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$capital = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_capital'])) $capital .= $inquiryInfo['tbl_company']['cmp_capital'];
if (!_IsNull($capital)) {
	$capital = "����Ҥ���Ω���λ��ܶ�ϡ���".$capital."���ߤȤ��롣";
	
	$capital = mb_convert_kana($capital, 'N');
} else {
	$errorList[] = "�ػ��ܶ�٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$capital,$border,"L");
$pdf->Ln();


//����Ω����̳���ԼҰ���
$pdf->Cell(0,6,"����Ω����̳���ԼҰ���",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$board1 = null;
$board2 = null;
foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
	//��̳D="��̳���ԼҰ�"�ξ��
	if ($boardInfo['cmp_bod_duties_id'] == MST_COMPANY_TYPE_DUTIES_ID_LLC_EXECUTION) {
		if (!_IsNull($boardInfo['cmp_bod_name'])) {
			$board1 .= $mstCompanyTypeDutiesList[$boardInfo['cmp_bod_duties_id']]['value'];
			$board1 .= "    ";
			$board1 .= $boardInfo['cmp_bod_name'];
			$board1 .= "\n";
		}
	}

	//��ID="��ɽ�Ұ�"�ξ��
	if ($boardInfo['cmp_bod_post_id'] == MST_COMPANY_TYPE_POST_ID_LLC_REP_STAFF) {
		$errFlag = true;
		if (_IsNull($boardInfo['cmp_bod_name'])) $errFlag = false;
		if (_IsNull($boardInfo['cmp_bod_pref_id'])) $errFlag = false;
		if (_IsNull($boardInfo['cmp_bod_address1'])) $errFlag = false;
		if ($errFlag) {
			if (!_IsNull($board2)) $board2 .= "\n";
			$board2 .= "����";
			$board2 .= " ";
			$board2 .= $mstPrefList[$boardInfo['cmp_bod_pref_id']]['name'];
			$board2 .= $boardInfo['cmp_bod_address1'];
			$board2 .= $boardInfo['cmp_bod_address2'];
			$board2 .= "\n";
			
			$board2 .= $mstCompanyTypePostList[$boardInfo['cmp_bod_post_id']]['name'];
			$board2 .= "    ";
			$board2 .= $boardInfo['cmp_bod_name'];
			$board2 .= "\n";
		}
	}
}
if (!_IsNull($board1)) {
	$board1 = "����Ҥ���Ω����̳���ԼҰ��ϡ����ΤȤ���Ȥ��롣\n".$board1;
} else {
	$errorList[] = "�����-��̳���ԼҰ��٤���Ͽ���Ƥ���������";
}
if (!_IsNull($board2)) {
	$board2 = "����Ҥ���Ω����ɽ�Ұ��ϡ����ΤȤ���Ȥ��롣\n".$board2;

	$board2 = mb_convert_kana($board2, 'N');
} else {
	$errorList[] = "�����-��ɽ�Ұ��٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$board1,$border,"L");
$pdf->Ln();


//����Ω����ɽ�Ұ���
$pdf->Cell(0,6,"����Ω����ɽ�Ұ���",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$pdf->MultiCell(0,6,$board2,$border,"L");
$pdf->Ln();


//���괾���ѹ���
$pdf->Cell(0,6,"���괾���ѹ���",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥ��괾���ѹ��ϡ��Ұ��β�Ⱦ����Ʊ�դˤ���ΤȤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//���괾�����Τʤ������
$pdf->Cell(0,6,"���괾�����Τʤ������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "���괾�����Τʤ�����ϡ����٤Ʋ��ˡ�ε���ˤ�롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);





//���θ���
$justiceName = null;
$justiceName4last = null;
if (!_IsNull($inquiryInfo['tbl_inquiry']['inq_justice_id'])) {
	//�ζȾ����������롣
	$condition4jst = array();
	$condition4jst['jst_justice_id'] = $inquiryInfo['tbl_inquiry']['inq_justice_id'];//�ζ�ID
	$tblJusticeInfo = _DB_GetInfo('tbl_justice', $condition4jst, true, 'jst_del_flag');
	if (!_IsNull($tblJusticeInfo)) {
		if (!_IsNull($tblJusticeInfo['jst_name'])) {
			if (!_IsNull($tblJusticeInfo['jst_justice_type_id'])) {
				if (!_IsNull($mstJusticeTypeList[$tblJusticeInfo['jst_justice_type_id']]['name'])) {
					$justiceName .= $mstJusticeTypeList[$tblJusticeInfo['jst_justice_type_id']]['name'];
					$justiceName .= " ";
					$justiceName .= $tblJusticeInfo['jst_name'];
				}
			}
		}
	}
}
$buf = null;
if (_IsNull($justiceName)) {
	//�ζȤ�̤����ξ��
	$buf .= "�ʾ塢";
	$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
	$buf .= "��Ω�ΰ٤ˡ�";
	$buf .= "�����괾���������ͭ����Ǥ�Ұ������˵�̾�������롣";
} else {
	//�ζȤ�����Ѥξ��
	$buf .= "�ʾ塢";
	$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
	$buf .= "��Ω�ΰ٤ˡ�ͭ����Ǥ�Ұ����괾���������ͤǤ���";
	$buf .= $justiceName;
	$buf .= "�ϡ��ż�Ū��Ͽ�Ǥ������괾���������������Żҽ�̾���롣";
	
	
	$justiceName4last .= "�嵭ͭ����Ǥ�Ұ����괾����������";
	$justiceName4last .= "\n";
	$justiceName4last .= $justiceName;
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//������
$buf = null;
$buf .= _ConvertAD2Jp($pdfCreateYear);
$buf .= "ǯ";
$buf .= $pdfCreateMonth;
$buf .= "��";
$buf .= $pdfCreateDay;
$buf .= "��";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);

//ȯ����
$pdf->Cell(10,6,"",$border,0,"L");
$pdf->MultiCell(0,6,$investment4last,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


if (!_IsNull($justiceName4last)) {
	$pdf->Cell(10,6,"",$border,0,"L");
	$pdf->MultiCell(0,6,$justiceName4last,$border,"L");
	$pdf->Ln();
	
	$pdf->Ln(10);
}



//DB�򥯥������롣
_DB_Close($link);


if (count($errorList) > 0) {
	//���顼ͭ�ξ��

	//PDF��λ���롣
	$pdf->Close();

	_Log("[/pdf/create/teikan_llc.php] end. ERR!");


	$buf = "��PDF��������뤿��ξ���­��ޤ���[��礻����]���̤ǡ���������Ϥ��Ƥ���������";
	array_unshift($errorList, $buf);
	
	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//���顼���̤�ɽ�����롣
	header("Location: ../error.php");
	exit;

} else {
	//���顼̵�ξ��

	//PDF����Ϥ��롣
	$pdf->Output();

	_Log("[/pdf/create/teikan_llc.php] end. OK!");
}





function _mb_str_split($str, $length = 1) {
	if ($length < 1) return false;

	$result = array();

	for ($i = 0; $i < mb_strlen($str); $i += $length) {
		$result[] = mb_substr($str, $i, $length);
	}

	return $result;
}

function _no($no) {
	return mb_convert_kana("��".$no."��", 'N');
}

?>
