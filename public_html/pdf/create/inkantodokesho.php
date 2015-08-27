<?php
/*
 * [��������]
 * PDF����
 * ���աʲ������Ͻ�
 *
 * ��������2008/11/05	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");
//include_once("../../common/libs/fpdf/mbfpdf.php");
include_once("../../common/libs/fpdf/mbfpdf_fpdi.php");


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
$title = "���աʲ������Ͻ�";


//[�ǥХå���]
//�ܡ�����
$border = 0;

//�طʿ�
$fill = 0;

//�طʿ�
$bgR = 239;
$bgG = 194;
$bgB = 238;

//���--------------------------------------------end


// EUC-JP->SJIS �Ѵ���ưŪ�˹Ԥʤ碌����� mbfpdf.php ��� $EUC2SJIS ��
// true �˽������뤫�����Τ褦�˼¹Ի��� true �����ꤷ�Ƥ��Ѵ����Ƥ��ޤ���
//$GLOBALS['EUC2SJIS'] = true;

//PDF�Υ����������ꤹ�롣�ǥե����=FPDF($orientation='P',$unit='mm',$format='A4')
//'B5' = 182.0mm��257.0mm
$pdf=new MBFPDF('P', 'mm', array(182.0, 257.0));

//�ե���Ȥ����ꤹ�롣
$pdf->AddMBFont(GOTHIC ,'SJIS');
$pdf->AddMBFont(PGOTHIC,'SJIS');
$pdf->AddMBFont(MINCHO ,'SJIS');
$pdf->AddMBFont(PMINCHO,'SJIS');
$pdf->AddMBFont(KOZMIN ,'SJIS');

//�ޡ���������ꤹ�롣
$pdf->SetLeftMargin(0);
$pdf->SetRightMargin(0);
$pdf->SetTopMargin(0);


$pdf->SetFont(MINCHO,'',$normalFontSize);

//��ư���ڡ����⡼�ɤ�ON(true)���ڡ����β�ü����ε�Υ�ʥޡ�����ˤ�2 mm�ˤʤä���硢���Ԥ���褦�����ꤹ�롣
$pdf->SetAutoPageBreak(true, 0);

//�ɥ�����ȤΥ����ȥ�����ꤹ�롣
$pdf->SetTitle($title);
//�ɥ�����Ȥμ���(subject)�����ꤹ�롣
$pdf->SetSubject($title);


//�����Ͻ�ο������ɤ߹��ࡣ���ɤ߹����PDF�˳��ͤ�������Ǥ�����
$pagecount = $pdf->setSourceFile("../../common/temp_pdf/inkantodokesho.pdf");

//������1�ڡ����ܤ�������롣(1�ڡ��������ʤ���)
$tplidx = $pdf->ImportPage(1);
$pdf->addPage();
//�����򥻥åȤ��롣
$pdf->useTemplate($tplidx);


$pdf->SetFillColor($bgR, $bgG, $bgB);


$pdf->SetFontSize(10);

//���̾��ˡ��̾
$companyName = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name'])) $companyName .= $inquiryInfo['tbl_company']['cmp_company_name'];
$buf = null;
if (!_IsNull($companyName)) {
	$buf = $companyName;
} else {
	$errorList[] = "�ز��̾��ˡ��̾�٤���Ͽ���Ƥ���������";
}
$pdf->SetXY(101, 35);
$pdf->MultiCell(67,5,$buf,$border,"L",$fill);


//����
$companyAddress = null;
$buf = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_pref_id']) && !_IsNull($inquiryInfo['tbl_company']['cmp_address1'])) {
	$companyAddress .= $mstPrefList[$inquiryInfo['tbl_company']['cmp_pref_id']]['name'];
	$companyAddress .= $inquiryInfo['tbl_company']['cmp_address1'];
	if (!_IsNull($inquiryInfo['tbl_company']['cmp_address2'])) {
		if (!_IsNull($companyAddress)) $companyAddress .= " ";
		$companyAddress .= $inquiryInfo['tbl_company']['cmp_address2'];
	}
	$companyAddress = mb_convert_kana($companyAddress, 'N');
	$buf = $companyAddress;
} else {
	$errorList[] = "����Ź����ϡ٤���Ͽ���Ƥ���������";
}
$pdf->SetFontSize(8);
$pdf->SetXY(101, 46);
$pdf->MultiCell(67,3,$buf,$border,"L",$fill);


$pdf->SetFontSize(10);


//��ɽ������

$repBoardInfo = null;
if (is_array($inquiryInfo['tbl_company_board']['board_info'])) {
	//��Ƭ����ɽ�������������롣
	foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
		switch ($boardInfo['cmp_bod_post_id']) {
			case MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR:		//��ɽ������
				$repBoardInfo = $boardInfo;
				break 2;
			default:
				continue 2;
		}
	}
}
if (!_IsNull($repBoardInfo)) {
	//���̾��
	$name = null;
	if (!_IsNull($repBoardInfo['cmp_bod_name'])) $name .= $repBoardInfo['cmp_bod_name'];
	$buf = null;
	if (!_IsNull($name)) {
		$buf = $name;
	} else {
		$errorList[] = "�����̾���٤���Ͽ���Ƥ���������";
	}
	//������мԤλ�̾
	$pdf->SetXY(101, 69);
	$pdf->MultiCell(67,5,$buf,$border,"L",$fill);

	//�Ͻпͤλ�̾
	$pdf->SetXY(34, 135);
	$pdf->MultiCell(95,5,$buf,$border,"L",$fill);

	//��ǯ����
	$birth = null;
	$buf = null;
	if (!_IsNull($repBoardInfo['cmp_bod_birth_year']) && !_IsNull($repBoardInfo['cmp_bod_birth_month']) && !_IsNull($repBoardInfo['cmp_bod_birth_day'])) {
		//����ID������å����롣
		if (_IsNull($repBoardInfo['cmp_bod_nationality_id']) || $repBoardInfo['cmp_bod_nationality_id'] == MST_NATIONALITY_ID_JAPAN) {
			//̤���ꡢ���ϡ�"���ܹ���"�ξ�硢����ˤ��롣
			$birth .= _ConvertAD2Jp($repBoardInfo['cmp_bod_birth_year']);
			$birth .= "ǯ";
			$birth .= $repBoardInfo['cmp_bod_birth_month'];
			$birth .= "��";
			$birth .= $repBoardInfo['cmp_bod_birth_day'];
			$birth .= "��";
			$birth .= "��";
		} else {
			//"���ܹ���"�ʳ��ξ�硢����ˤ��롣
			$birth .= "����";
			$birth .= $repBoardInfo['cmp_bod_birth_year'];
			$birth .= "ǯ";
			$birth .= $repBoardInfo['cmp_bod_birth_month'];
			$birth .= "��";
			$birth .= $repBoardInfo['cmp_bod_birth_day'];
			$birth .= "��";
			$birth .= "��";
		}
		$birth = mb_convert_kana($birth, 'N');
		$buf = $birth;
	} else {
		$errorList[] = "�������ǯ�����٤���Ͽ���Ƥ���������";
	}
	$pdf->SetFillColor(255, 255, 255);
	$pdf->SetXY(101, 81);
	$pdf->MultiCell(67,6,$buf,$border,"L",1);


	$pdf->SetFillColor($bgR, $bgG, $bgB);


	//�Ͻпͤΰ�����м��ܿ�
	$buf = "��";
	$pdf->SetXY(43, 113);
	$pdf->MultiCell(3,3,$buf,$border,"L",$fill);


	//�������
	$address = null;
	$buf = null;
	if (!_IsNull($repBoardInfo['cmp_bod_pref_id']) && !_IsNull($repBoardInfo['cmp_bod_address1'])) {
		$address .= $mstPrefList[$repBoardInfo['cmp_bod_pref_id']]['name'];
		$address .= $repBoardInfo['cmp_bod_address1'];
		if (!_IsNull($repBoardInfo['cmp_bod_address2'])) {
			if (!_IsNull($address)) $address .= " ";
			$address .= $repBoardInfo['cmp_bod_address2'];
		}
		$address = mb_convert_kana($address, 'N');
		$buf = $address;
	} else {
		$errorList[] = "���������٤���Ͽ���Ƥ���������";
	}
	//�Ͻпͤν���
	$pdf->SetFontSize(8);
	$pdf->SetXY(34, 118);
	$pdf->MultiCell(95,3,$buf,$border,"L",$fill);


	$pdf->SetFontSize(10);


	//���̾��(�դ꤬��)
	$nameKana = null;
	if (!_IsNull($repBoardInfo['cmp_bod_name_kana'])) $nameKana .= $repBoardInfo['cmp_bod_name_kana'];
	$buf = null;
	if (!_IsNull($nameKana)) {
		//���ѥ����ʥ����Ѵ����롣
		$nameKana = mb_convert_kana($nameKana, 'KVC');
		$buf = $nameKana;
	} else {
		$errorList[] = "�����̾��(�դ꤬��)�٤���Ͽ���Ƥ���������";
	}
	//�ϽпͤΥեꥬ��
	$pdf->SetFontSize(8);
	$pdf->SetXY(34, 129);
	$pdf->MultiCell(95,3,$buf,$border,"L",$fill);


	$pdf->SetFontSize(10);


	
} else {
	$errorList[] = "�����(��ɽ������)�٤���Ͽ���Ƥ���������";
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
