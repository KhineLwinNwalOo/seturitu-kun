<?php
/*
 * [��������]
 * PDF����
 * ź�ս������ս�
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
_Log("[/pdf/create/sohusho.php] start.");

_Log("[/pdf/create/sohusho.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/create/sohusho.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/create/sohusho.php] SERVER = '".print_r($_SERVER,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/create/sohusho.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/pdf/create/sohusho.php] end.");
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
//$mstStockPriceList = _GetMasterList('mst_stock_price', $undeleteOnly);			//1����ñ���ޥ���
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
$title = "ź�ս������ս�";


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


//ˡ̳��
$pdf->SetFontSize(10);
$buf = "���ˡ̳�ɡ�����";
$pdf->Cell(0,10,$buf,$border,0,"L");
$pdf->Ln(15);


//�����ȥ�
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


$pdf->SetFontSize(10);


//���̾��ˡ��̾
$buf = "��������";
$pdf->Cell(30,6,$buf,$border,0,"L");

$companyName = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name'])) $companyName .= $inquiryInfo['tbl_company']['cmp_company_name'];
$buf = null;
if (!_IsNull($companyName)) {
	$buf .= $companyName;
} else {
	$errorList[] = "�ز��̾��ˡ��̾�٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(5);


//��Ź�����
$buf = "�ܡ���Ź";
$pdf->Cell(30,6,$buf,$border,0,"L");

$companyAddress = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_pref_id'])) $companyAddress .= $mstPrefList[$inquiryInfo['tbl_company']['cmp_pref_id']]['name'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_address1'])) $companyAddress .= $inquiryInfo['tbl_company']['cmp_address1'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_address2'])) $companyAddress .= $inquiryInfo['tbl_company']['cmp_address2'];
$buf = null;
if (!_IsNull($companyAddress)) {
	$buf .= $companyAddress;
	$buf = mb_convert_kana($buf, 'N');
} else {
	$errorList[] = "����Ź����ϡ٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(5);


//��Ź
$buf = "�١���Ź";
$pdf->Cell(30,6,$buf,$border,0,"L");

//$buf = null;
$buf = " ";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->SetFontSize(8);
$buf = "����ˡֻ�Ź�פϻ�Ź������е���˿���������˵��ܴꤤ�ޤ���";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln(20);

$pdf->SetFontSize(10);


//���
$board = null;
$boardCount = 0;

//��ҥ�����_�򿦥ޥ����Ρ�ɽ����פν��ɽ�����롣
foreach ($mstCompanyTypePostList as $key => $mstCompanyTypePostInfo) {
	foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
		if ($mstCompanyTypePostInfo['id'] == $boardInfo['cmp_bod_post_id']) {
			if (!_IsNull($boardInfo['cmp_bod_name'])) {
				$board .= "��Ω��".$mstCompanyTypePostInfo['name']." ".$boardInfo['cmp_bod_name']."\n";
				$boardCount++;
			
				$buf = "";
				if ($boardCount == 1) {
					$buf = "��������";
				}
				$pdf->Cell(30,6,$buf,$border,0,"L");

				$buf = "�񡡳�";
				$pdf->Cell(20,6,$buf,$border,0,"L");

				$buf = $mstCompanyTypePostInfo['name'];
				//$pdf->Cell(0,6,$buf,$border,0,"L");
				$pdf->MultiCell(0,6,$buf,$border,"L");

				$pdf->Ln(2);

				$buf = "";
				$pdf->Cell(30,6,$buf,$border,0,"L");

				$buf = "�ᡡ̾";
				$pdf->Cell(20,6,$buf,$border,0,"L");

				$buf = $boardInfo['cmp_bod_name'];
				//$pdf->Cell(0,6,$buf,$border,0,"L");
				$pdf->MultiCell(0,6,$buf,$border,"L");
				
				$pdf->Ln(5);
			}
		}
	}
}
if (!_IsNull($board)) {
} else {
	$errorList[] = "������٤���Ͽ���Ƥ���������";
}


//�ζ�
$justiceName = null;
$justiceAddress = null;
if (!_IsNull($inquiryInfo['tbl_inquiry']['inq_justice_id'])) {
	//�ζȾ����������롣
	$condition4jst = array();
	$condition4jst['jst_justice_id'] = $inquiryInfo['tbl_inquiry']['inq_justice_id'];//�ζ�ID
	$tblJusticeInfo = _DB_GetInfo('tbl_justice', $condition4jst, true, 'jst_del_flag');
	if (!_IsNull($tblJusticeInfo)) {
		$justiceName .= $mstJusticeTypeList[$tblJusticeInfo['jst_justice_type_id']]['name'];
		$justiceName .= " ";
		$justiceName .= $tblJusticeInfo['jst_name'];

		$justiceAddress .= $mstPrefList[$tblJusticeInfo['jst_pref_id']]['name'];
		$justiceAddress .= $tblJusticeInfo['jst_address1'];
		$justiceAddress .= $tblJusticeInfo['jst_address2'];
	}
}

$buf = "������";
$pdf->Cell(30,6,$buf,$border,0,"L");

$buf = "������";
$pdf->Cell(20,6,$buf,$border,0,"L");

$buf = $justiceAddress;
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln(2);

$buf = "";
$pdf->Cell(30,6,$buf,$border,0,"L");

$buf = "�ᡡ̾";
$pdf->Cell(20,6,$buf,$border,0,"L");

$buf = $justiceName;
//$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln(5);

if (!_IsNull($justiceName)) {
} else {
	$errorList[] = "�ػζȼԡ٤���Ͽ���Ƥ���������";
}


//�����ֹ�
$buf = "�����ֹ�";
$pdf->Cell(30,6,$buf,$border,0,"L");

$applicationNo = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_application_no'])) $applicationNo .= $inquiryInfo['tbl_company']['cmp_application_no'];
$buf = null;
//(2008/11/14)ɬ�ܤʤ�
//if (!_IsNull($applicationNo)) {
//	$buf .= $applicationNo;
//} else {
//	$errorList[] = "�ؿ����ֹ�٤���Ͽ���Ƥ���������";
//}
$buf .= $applicationNo;
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln(20);


$pdf->Cell(0,6,"ź�ս��̤�����",$border,0,"C");

$pdf->Ln(10);


$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"��Ǥ������",$border,0,"L");
$pdf->Cell(10,6,"1��",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");

$pdf->Ln();

$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"��Ǥ��",$border,0,"L");
$pdf->Cell(10,6,"1��",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");

$pdf->Ln();

$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"�����Ͻ�",$border,0,"L");
$pdf->Cell(10,6,"1��",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");

$pdf->Ln();

$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"ʧ��������",$border,0,"L");
$pdf->Cell(10,6,"1��",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");

$pdf->Ln();

$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"���վ�����",$border,0,"L");
$pdf->Cell(10,6,"1��",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");

$pdf->Ln();

$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"�괾ƥ��",$border,0,"L");
$pdf->Cell(10,6,"1��",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");




//DB�򥯥������롣
_DB_Close($link);


if (count($errorList) > 0) {
	//���顼ͭ�ξ��

	//PDF��λ���롣
	$pdf->Close();

	_Log("[/pdf/create/sohusho.php] end. ERR!");


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

	_Log("[/pdf/create/sohusho.php] end. OK!");
}



?>
