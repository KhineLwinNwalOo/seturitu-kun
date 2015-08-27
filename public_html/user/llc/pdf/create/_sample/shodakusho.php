<?php
/*
 * [��������]
 * PDF����
 * ��Ǥ������
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
_Log("[/pdf/create/shodakusho.php] start.");

_Log("[/pdf/create/shodakusho.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/create/shodakusho.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/create/shodakusho.php] SERVER = '".print_r($_SERVER,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/create/shodakusho.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/pdf/create/shodakusho.php] end.");
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

//�괾������
$pdfTeikanCreateYear = (isset($inData['teikan_year'])?$inData['teikan_year']:date('Y'));
$pdfTeikanCreateMonth = (isset($inData['teikan_month'])?$inData['teikan_month']:date('n'));
$pdfTeikanCreateDay = (isset($inData['teikan_day'])?$inData['teikan_day']:date('j'));


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
//$mstJusticeTypeList = _GetMasterList('mst_justice_type', $undeleteOnly);		//�ζȥ����ץޥ���

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
$title = "��Ǥ������";


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




//������
$createDate = null;
$createDate .= _ConvertAD2Jp($pdfCreateYear);
$createDate .= "ǯ";
$createDate .= $pdfCreateMonth;
$createDate .= "��";
$createDate .= $pdfCreateDay;
$createDate .= "��";
$createDate = mb_convert_kana($createDate, 'N');

//�괾������
$teikanCreateDate = null;
$teikanCreateDate .= _ConvertAD2Jp($pdfTeikanCreateYear);
$teikanCreateDate .= "ǯ";
$teikanCreateDate .= $pdfTeikanCreateMonth;
$teikanCreateDate .= "��";
$teikanCreateDate .= $pdfTeikanCreateDay;
$teikanCreateDate .= "��";
$teikanCreateDate = mb_convert_kana($teikanCreateDate, 'N');


//���̾��ˡ��̾
$companyName = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name'])) $companyName .= $inquiryInfo['tbl_company']['cmp_company_name'];
if (!_IsNull($companyName)) {
} else {
	$errorList[] = "�ز��̾��ˡ��̾�٤���Ͽ���Ƥ���������";
}


//���
$boardFlag = false;

$boardPostFlag = true;		//��
$boardNameFlag = true;		//��̾
$boardAddressFlag = true;	//����


if (is_array($inquiryInfo['tbl_company_board']['board_info'])) {
	if (count($inquiryInfo['tbl_company_board']['board_info']) > 0) {
		$boardFlag = true;
	}
}

//��ҥ�����_�򿦥ޥ����Ρ�ɽ����פν��ɽ�����롣
foreach ($mstCompanyTypePostList as $key => $mstCompanyTypePostInfo) {

	$topMessage = null;
	$topMessage .= "��ϡ�";
	$topMessage .= $teikanCreateDate;
	$topMessage .= "�ε����괾�ˤ����ơ�";
	switch ($mstCompanyTypePostInfo['id']) {
		case MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR:		//��ɽ������
			$topMessage .= "������ڤ���ɽ������";
			break;
		case MST_COMPANY_TYPE_POST_ID_CMP_INSPECTOR:		//�ƺ���
			$topMessage .= "�ƺ���";
			break;
		case MST_COMPANY_TYPE_POST_ID_CMP_DIRECTOR:			//������
		default:
			$topMessage .= "������";
			break;
	}
	$topMessage .= "����Ǥ����ޤ����Τǡ����ν�Ǥ�����������ޤ���";


	foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
		if ($mstCompanyTypePostInfo['id'] != $boardInfo['cmp_bod_post_id']) continue;
		
		if (_IsNull($boardInfo['cmp_bod_post_id'])) $boardPostFlag = false;
		if (_IsNull($boardInfo['cmp_bod_name'])) $boardNameFlag = false;
		if (_IsNull($boardInfo['cmp_bod_pref_id'])) $boardAddressFlag = false;
		if (_IsNull($boardInfo['cmp_bod_address1'])) $boardAddressFlag = false;
		
		
		$pdf->AddPage();
		
		//�����ȥ�
		$pdf->SetFontSize(18);
		$pdf->Cell(0,10,$title,$border,0,"C");
		$pdf->Ln(30);
		
		
		$pdf->SetFontSize(10);

		//��å�����
		$pdf->MultiCell(0,6,$topMessage,$border,"L");
		$pdf->Ln(90);


		//������
		$pdf->MultiCell(0,6,$createDate,$border,"L");
		$pdf->Ln(10);


		//����
		$buf = "�ʡ������ꡡ��";
		$pdf->Cell(30,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $mstPrefList[$boardInfo['cmp_bod_pref_id']]['name'];
		$buf .= $boardInfo['cmp_bod_address1'];
		if (!_IsNull($boardInfo['cmp_bod_address2'])) {
			if (!_IsNull($buf)) $buf .= " ";
			$buf .= $boardInfo['cmp_bod_address2'];
		}
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");

		$pdf->Ln(5);

		//��̾
		$buf = "�ʡ��ᡡ̾����";
		$pdf->Cell(30,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $boardInfo['cmp_bod_name'];
		$pdf->MultiCell(0,6,$buf,$border,"L");

		$pdf->Ln(5);


		//����
		$buf = "�ʡ������桡��";
		$pdf->Cell(30,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $companyName;
		$pdf->Cell(100,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "����";
		$pdf->Cell(20,6,$buf,$border,0,"L");
	
		$buf = null;
		$buf .= "��";
		$pdf->Cell(0,6,$buf,$border,0,"L");
	}
}

if (!$boardFlag) {
	$errorList[] = "������٤���Ͽ���Ƥ���������";
}
if (!$boardPostFlag) {
	$errorList[] = "�����-����򿦡٤���Ͽ���Ƥ���������";
}
if (!$boardNameFlag) {
	$errorList[] = "�����-���̾���٤���Ͽ���Ƥ���������";
}
if (!$boardAddressFlag) {
	$errorList[] = "�����-����٤���Ͽ���Ƥ���������";
}


//DB�򥯥������롣
_DB_Close($link);


if (count($errorList) > 0) {
	//���顼ͭ�ξ��

	//PDF��λ���롣
	$pdf->Close();

	_Log("[/pdf/create/shodakusho.php] end. ERR!");


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

	_Log("[/pdf/create/shodakusho.php] end. OK!");
}



?>
