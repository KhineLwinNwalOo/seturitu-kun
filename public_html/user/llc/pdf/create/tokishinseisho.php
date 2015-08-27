<?php
/*
 * [���������Ω.JP �ġ���]
 * PDF����
 * ���������Ω�е�������(��Ʊ�����)
 *
 * ��������2011/12/07	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../../common/include.ini");
include_once("../../../../common/libs/fpdf/mbfpdf.php");


_LogDelete();
//_LogBackup();
_Log("[/user/llc/pdf/create/tokishinseisho.php] start.");

_Log("[/user/llc/pdf/create/tokishinseisho.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/llc/pdf/create/tokishinseisho.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/llc/pdf/create/tokishinseisho.php] SERVER = '".print_r($_SERVER,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/user/index.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ".URL_LOGIN);
	exit;
} else {
	//����������������롣
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
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

//�桼����ID
$userId = (isset($inData['user_id'])?$inData['user_id']:null);
//���ID
$companyId = (isset($inData['company_id'])?$inData['company_id']:null);

//������
$pdfCreateYear = ((isset($inData['create_year']) && !_IsNull($inData['create_year']))?$inData['create_year']:date('Y'));
$pdfCreateMonth = ((isset($inData['create_month']) && !_IsNull($inData['create_month']))?$inData['create_month']:date('n'));
$pdfCreateDay = ((isset($inData['create_day']) && !_IsNull($inData['create_day']))?$inData['create_day']:date('j'));



//����ͤ����ꤹ�롣
$undeleteOnly4def = false;

//���¤ˤ�äơ�ɽ������桼������������¤��롣
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://����̵��

		_Log("[/user/llc/pdf/create/tokishinseisho.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

		$undeleteOnly4def = true;

		//��ʬ�Υ桼�������󡢲�Ҿ���Τ�ɽ�����롣
		//�桼����ID�����ID������å����롣

		//���ID�򸡺����롣
		$relationCompanyId = _GetRelationLlcId($loginInfo['usr_user_id']);


		_Log("[/user/llc/pdf/create/tokishinseisho.php] {������桼�������½���} ��(������)�桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {������桼�������½���} ��(������)���ID = '".$relationCompanyId."'");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {������桼�������½���} ��(�ѥ�᡼����)�桼����ID = '".$userId."'");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {������桼�������½���} ��(�ѥ�᡼����)���ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/llc/pdf/create/tokishinseisho.php] {������桼�������½���} ��(�����о�)�桼����ID = '".$userId."'");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {������桼�������½���} ��(�����о�)���ID = '".$companyId."'");

		break;
}

//��������å�
if (!_IsNull($companyId)) {
	if (!_CheckUserStatus($userId, $companyId, MST_SYSTEM_COURSE_ID_LLC)) {
		$errorList[] = "���������������ޤ��󡣽���κ���(����)�ϡ�����������η�Ѹ�ˤ����Ѥ���ǽ�Ȥʤ�ޤ���";
		$_SESSION[SID_PDF_ERR_MSG] = $errorList;
		//���顼���̤�ɽ�����롣
		header("Location: ../error.php");
		exit;
	}
}

$companyInfo = null;
if (!_IsNull($companyId)) {
	//��Ҿ����������롣
	$companyInfo = _GetCompanyInfo($companyId, $undeleteOnly4def);
}

if (_IsNull($companyInfo)) {
	$errorList[] = "�������β�Ҿ���¸�ߤ��ޤ���";

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//���顼���̤�ɽ�����롣
	header("Location: ../error.php");
	exit;
}

//��Ͽ���������å����롣
//���̾
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "�ؾ���(���̾)�٤���Ͽ���Ƥ���������";
//��Ź�����
$errFlag = false;
if (_IsNull($companyInfo['tbl_company']['cmp_pref_id'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_address1'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_address2'])) $errFlag = true;
if ($errFlag)  $errorList[] = "����Ź����ϡ٤���Ͽ���Ƥ���������(�ؾ嵭�ʹߡ٤ξܺ���ʬ����Ͽ���Ƥ���������)";
//���ܶ�
if (_IsNull($companyInfo['tbl_company']['cmp_capital'])) $errorList[] = "�ػ��ܶ�٤���Ͽ���Ƥ���������";
////������
//$errFlag = false;
//foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
//	if (_IsNull($boardInfo['cmp_bod_family_name']) || _IsNull($boardInfo['cmp_bod_first_name'])) {
//		$errFlag = true;
//		break;
//	}
//	if (_IsNull($boardInfo['cmp_bod_pref_id']) || _IsNull($boardInfo['cmp_bod_address1'])) {
//		$errFlag = true;
//		break;
//	}
//}
//if ($errFlag) $errorList[] = "�ؼ�����٤Ρؤ�̾���١��ؽ���٤���Ͽ���Ƥ���������";
//ȯ����
$representativePartnerFlag = false;
$businessExecutionFlag = false;
$errFlag = false;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	//��ɽ�Ұ�ID="��ɽ�Ұ��ˤʤ�"��1�ͤǤ⤤�뤫��
	if ($promoterInfo['cmp_prm_representative_partner_id'] == MST_REPRESENTATIVE_PARTNER_ID_YES) {
		$representativePartnerFlag = true;
	}
	//��̳����ID="��̳���ԼҰ��ˤʤ�"��1�ͤǤ⤤�뤫��
	if ($promoterInfo['cmp_prm_business_execution_id'] == MST_BUSINESS_EXECUTION_ID_YES) {
		$businessExecutionFlag = true;
	}
	//�ͳʼ��̤ˤ�äơ������å����ܤ��ڤ��ؤ��롣
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//�Ŀ�
			if (_IsNull($promoterInfo['cmp_prm_family_name']) || _IsNull($promoterInfo['cmp_prm_first_name'])) {
				$errFlag = true;
			}
			if (_IsNull($promoterInfo['cmp_prm_pref_id']) || _IsNull($promoterInfo['cmp_prm_address1'])) {
				$errFlag = true;
			}
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//ˡ��(������ҡ�ͭ�²�ҤΤ�)
			if (_IsNull($promoterInfo['cmp_prm_company_name'])) {
				$errFlag = true;
			}
			if (_IsNull($promoterInfo['cmp_prm_company_pref_id']) || _IsNull($promoterInfo['cmp_prm_company_address1'])) {
				$errFlag = true;
			}
			break;
	}
}
if ($errFlag) $errorList[] = "�ؼҰ�(�л��)�٤Ρؤ�̾���١��ؽ�������ϡ��ز��̾(ˡ��)�١�����Ź����ϡ٤���Ͽ���Ƥ���������";
if (!$representativePartnerFlag) $errorList[] = "�ؼҰ�(�л��)�٤Ρ���ɽ�Ұ��٤ǡ���ɽ�Ұ��ˤʤ�٤�1�Ͱʾ���Ͽ���Ƥ���������";
if (!$businessExecutionFlag) $errorList[] = "�ؼҰ�(�л��)�٤Ρض�̳���ԡ٤ǡض�̳���ԼҰ��ˤʤ�٤�1�Ͱʾ���Ͽ���Ƥ���������";
//��Ū
$errFlag = true;
foreach ($companyInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (!_IsNull($purposeInfo['cmp_pps_purpose'])) {
		$errFlag = false;
		break;
	}
}
if ($errFlag) $errorList[] = "����Ū�٤���Ͽ���Ƥ���������";
////ȯ�Բ�ǽ���������
//if (_IsNull($companyInfo['tbl_company']['cmp_stock_total_num'])) $errorList[] = "��ȯ�Բ�ǽ����������٤���Ͽ���Ƥ���������";
////1����ñ��
//if (_IsNull($companyInfo['tbl_company']['cmp_stock_price'])) $errorList[] = "��1����ñ���٤���Ͽ���Ƥ���������";

if (count($errorList) > 0) {
	//���顼ͭ�ξ��
	_Log("[/user/llc/pdf/create/tokishinseisho.php] end. ERR!");


	$buf = "��PDF��������뤿��ξ���­��ޤ��󡣡ع�Ʊ�����ΩLLC������Ͽ�ٲ��̤ǡ���������Ϥ��Ƥ������������ϡ��سƼ������� �����ٲ��̤ǡ���������Ϥ��Ƥ���������";
	array_unshift($errorList, $buf);

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//���顼���̤�ɽ�����롣
	header("Location: ../error.php");
	exit;
}


//�ޥ��������������롣
$undeleteOnly = false;
$mstPrefList = _GetMasterList('mst_pref');		//��ƻ�ܸ��ޥ���
unset($mstPrefList[MST_PREF_ID_OVERSEAS]);
$mstPostList = _GetMasterList('mst_post');		//�򿦥ޥ���

//���--------------------------------------------start
//�ե���ȥ�������������롣
//�̾�
$normalFontSize = 10;

//�����ȥ�
$title = "��Ʊ�����Ω�е�������";


//[�ǥХå���]
//�ܡ�����
$border = 0;
//���--------------------------------------------end


// EUC-JP->SJIS �Ѵ���ưŪ�˹Ԥʤ碌����� mbfpdf.php ��� $EUC2SJIS ��
// true �˽������뤫�����Τ褦�˼¹Ի��� true �����ꤷ�Ƥ��Ѵ����Ƥ��ޤ���
//$GLOBALS['EUC2SJIS'] = true;

//PDF�Υ����������ꤹ�롣�ǥե����=FPDF($orientation='P',$unit='mm',$format='A4')
$pdf = new MBFPDF();

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

//�����ȥ�
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


$pdf->SetFontSize(10);


$buf = "��������������";
$pdf->Cell(30,6,$buf,$border,0,"L");

//����(���̾)
$buf = null;
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$buf = "�ܡ���������Ź";
$pdf->Cell(30,6,$buf,$border,0,"L");

//��Ź�����
$buf = null;
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
$buf .= $companyInfo['tbl_company']['cmp_address2'];
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$buf = "�� �� �� �� ͳ";
$pdf->Cell(30,6,$buf,$border,0,"L");

//��Ź�����
$buf = null;
$buf .= _ConvertAD2Jp($pdfCreateYear);
$buf .= "ǯ";
$buf .= $pdfCreateMonth;
$buf .= "��";
$buf .= $pdfCreateDay;
$buf .= "��";
$buf .= "ȯ����Ω�μ�³����λ";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$buf = "�е����٤�����";
$pdf->Cell(30,6,$buf,$border,0,"L");

//��Ź�����
$buf = null;
$buf .= "�̻�ΤȤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln();

$buf = "����ɸ����";
$pdf->Cell(30,6,$buf,$border,0,"L");

//���ܶ�
//���ܶ��ñ�̤����ߤ���ߤˤ��롣
//$capital = $companyInfo['tbl_company']['cmp_capital'] * 10000;
$capital = $companyInfo['tbl_company']['cmp_capital'];

$buf = null;
$buf .= "��";
$buf .= " ";
$buf .= number_format($capital);
$buf .= " ";
$buf .= "��";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$buf = "��Ͽ�ȵ��ǳ�";
$pdf->Cell(30,6,$buf,$border,0,"L");

$buf = null;
$buf .= "��";
$buf .= " ";
$buf .= number_format(LICENSE_TAX_LLC);
$buf .= " ";
$buf .= "��";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

//$buf = null;
//$pdf->Cell(30,6,$buf,$border,0,"L");
//$buf = "��������Ϸڸ�����";
//$pdf->Cell(40,6,$buf,$border,0,"L");
//$buf = "������������ˡ�裸����Σ�";
//$pdf->Cell(0,6,$buf,$border,0,"L");
//$pdf->Ln();

$pdf->Ln();
//$pdf->Ln();

$buf = "ź���ա�����";
$pdf->Cell(30,6,$buf,$border,0,"L");
$buf = "���վ�����";
$pdf->Cell(100,6,$buf,$border,0,"L");
$buf = "����";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(30,6,$buf,$border,0,"L");
$buf = "�괾";
$pdf->Cell(100,6,$buf,$border,0,"L");
$buf = "����";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(30,6,$buf,$border,0,"L");
$buf = "��Ǥ������";
$pdf->Cell(100,6,$buf,$border,0,"L");
$buf = "����";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(30,6,$buf,$border,0,"L");
$buf = "ʧ��������";
$pdf->Cell(100,6,$buf,$border,0,"L");
$buf = "����";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(30,6,$buf,$border,0,"L");
$buf = "��Ź����Ϸ����Ľ�";
$pdf->Cell(100,6,$buf,$border,0,"L");
$buf = "����";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

//$buf = null;
//$pdf->Cell(30,6,$buf,$border,0,"L");
//$buf = "��Ǥ��";
//$pdf->Cell(100,6,$buf,$border,0,"L");
//$buf = "����";
//$pdf->Cell(0,6,$buf,$border,0,"L");
//$pdf->Ln();

//��ʪ�л�Ԥ����뤫��
$inkindFlag = false;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	//�л����(����Τߡ���ʪ�л�Τߡ�����ܸ�ʪ)�ˤ�äơ���ʪ�νл�Ԥ�Ƚ�Ǥ��롣
	switch ($promoterInfo['cmp_prm_investment_shape_id']) {
		case MST_INVESTMENT_SHAPE_ID_CASH:
			//����Τ�
			break;
		case MST_INVESTMENT_SHAPE_ID_INKIND:
			//��ʪ�л�Τ�
		case MST_INVESTMENT_SHAPE_ID_CASH_INKIND:
			//����ܸ�ʪ
			$inkindFlag = true;
			break 2;
	}
}
if ($inkindFlag) {
	//��ʪ�л�Ԥ������硢ź�ս�����ɲä��롣
	$buf = null;
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = "���ܶ�γۤη׾�˴ؤ��������";
	$pdf->Cell(100,6,$buf,$border,0,"L");
	$buf = "����";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln();
	
	$buf = null;
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = "Ĵ������";
	$pdf->Cell(100,6,$buf,$border,0,"L");
	$buf = "����";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln();
	
	$buf = null;
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = "�⻺���ѽ�";
	$pdf->Cell(100,6,$buf,$border,0,"L");
	$buf = "����";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln();
}

//$pdf->Ln();
//
//$buf = "�����ϽФ�̵ͭ";
//$pdf->Cell(30,6,$buf,$border,0,"L");
//$buf = "ͭ";
//$pdf->Cell(20,6,$buf,$border,0,"L");
//$buf = "���ɳ��е�����������";
//$pdf->Cell(0,6,$buf,$border,0,"L");
//$pdf->Ln();


$pdf->Ln(15);


$buf = null;
$buf .= "�嵭�ΤȤ����е��������롣";
$pdf->MultiCell(0,6,$buf,$border,"L");


//������
$pdfCreateYearJp = _ConvertAD2Jp($pdfCreateYear);
$buf = $pdfCreateYearJp."ǯ";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(20,6,$buf,$border,0,"L");
$buf = $pdfCreateMonth."��";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"R");
$buf = $pdfCreateDay."��";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"R");
$buf = null;
$pdf->Cell(0,6,$buf,$border,0,"R");

$pdf->Ln();
$pdf->Ln();


$buf = "����������";
$pdf->Cell(30,6,$buf,$border,0,"L");

//��Ź�����
$buf = null;
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
$buf .= $companyInfo['tbl_company']['cmp_address2'];
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$buf = null;
$pdf->Cell(30,6,$buf,$border,0,"L");

//����(���̾)
$buf = null;
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$pdf->MultiCell(0,6,$buf,$border,"L");


////��ɽ�������������롣
//$repBoardInfo = null;
//foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
//	switch ($boardInfo['cmp_bod_post_id']) {
//		case MST_POST_ID_REP_DIRECTOR:
//			//��ɽ������
//			$repBoardInfo = $boardInfo;
//			break 2;
//	}
//}
//
//$buf = null;
//$pdf->Cell(30,6,$buf,$border,0,"L");
//
////�������
//$buf = null;
//$buf .= $mstPrefList[$repBoardInfo['cmp_bod_pref_id']]['name'];
//$buf .= $repBoardInfo['cmp_bod_address1'];
//$buf .= $repBoardInfo['cmp_bod_address2'];
//$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
//$buf = null;
//$pdf->Cell(30,6,$buf,$border,0,"L");
//
////���̾��
//$buf = null;
//$buf .= $mstPostList[$boardInfo['cmp_bod_post_id']]['name'];
//$buf .= " ";
//$buf .= $repBoardInfo['cmp_bod_family_name'];
//$buf .= " ";
//$buf .= $repBoardInfo['cmp_bod_first_name'];
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
//$pdf->Ln();

foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	//��ɽ�Ұ�ID="��ɽ�Ұ��ˤʤ�"�ʳ��ϡ����ء�
	if ($promoterInfo['cmp_prm_representative_partner_id'] != MST_REPRESENTATIVE_PARTNER_ID_YES) continue;

	//�ͳʼ��̤ˤ�äơ�̾������������ꤹ�롣
	$name = null;
	$address = null;
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//�Ŀ�
			$name .= $promoterInfo['cmp_prm_family_name'];
			$name .= " ";
			$name .= $promoterInfo['cmp_prm_first_name'];

			$address .= $mstPrefList[$promoterInfo['cmp_prm_pref_id']]['name'];
			$address .= $promoterInfo['cmp_prm_address1'];
			$address .= $promoterInfo['cmp_prm_address2'];
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//ˡ��(������ҡ�ͭ�²�ҤΤ�)
			$name .= $promoterInfo['cmp_prm_company_name'];

			$address .= $mstPrefList[$promoterInfo['cmp_prm_company_pref_id']]['name'];
			$address .= $promoterInfo['cmp_prm_company_address1'];
			$address .= $promoterInfo['cmp_prm_company_address2'];
			break;
	}

	$buf = null;
	$pdf->Cell(30,6,$buf,$border,0,"L");
	
	//��ɽ�Ұ�����
	$buf = null;
	$buf .= $address;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$buf = null;
	$pdf->Cell(30,6,$buf,$border,0,"L");
	
	//��ɽ�Ұ�̾��
	$buf = null;
	$buf .= "��ɽ�Ұ�";
	$buf .= " ";
	$buf .= $name;
	$pdf->MultiCell(0,6,$buf,$border,"L");

	//��ͤ�����OK��
	break;
}
$pdf->Ln();


//$buf = "�� �� �� �� ��";
//$pdf->Cell(30,6,$buf,$border,0,"L");
//
//$buf = null;
//$buf .= " ";
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
//$buf = null;
//$pdf->Cell(30,6,$buf,$border,0,"L");
//
//$buf = null;
//$buf .= " ";
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
//$pdf->Ln();


$buf = "�� �� �� �� ��";
$pdf->Cell(30,6,$buf,$border,0,"L");

$buf = null;
$buf .= "����������������������ˡ̳�ɡ�������������������������������������";
$pdf->Cell(0,6,$buf,$border,0,"L");

$pdf->Ln();


$buf = "�� �� �� ������";
$pdf->Cell(30,6,$buf,$border,0,"L");

$buf = null;
$buf .= " ";
$pdf->Cell(0,6,$buf,$border,0,"L");



//$pdf->Ln(30);


$pdf->AddPage();


$pdf->SetX(150);

$buf = "��������Ž�����";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();
$pdf->Ln();

$pdf->SetX(170);

$buf = null;
$buf .= "������\n������";
$pdf->MultiCell(20,10,$buf,1,"C");


$pdf->AddPage();


$buf = "�̡��桡���С����������١������������";
$pdf->Cell(0,6,$buf,$border,0,"L");

$pdf->Ln();
$pdf->Ln();

//����(���̾)
$buf = null;
$buf .= "�־����";
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$pdf->MultiCell(0,6,$buf,$border,"L");

//��Ź�����
$buf = null;
$buf .= "����Ź��";
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
$buf .= $companyInfo['tbl_company']['cmp_address2'];
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

//����򤹤���ˡ
$buf = null;
$buf .= "�ָ���򤹤���ˡ�״���˷Ǻܤ�����ˡ�ˤ��Ԥ���";
$pdf->MultiCell(0,6,$buf,$border,"L");

//��Ū
$buf = null;
$buf .= "����Ū��";
$pdf->MultiCell(0,6,$buf,$border,"L");

$i = 0;
foreach ($companyInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (_IsNull($purposeInfo['cmp_pps_purpose'])) continue;

	$buf = (++$i)."��";
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(12,6,$buf,$border,0,"L");

	$buf = $purposeInfo['cmp_pps_purpose'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");
}

$buf = (++$i)."��";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"L");

$buf = null;
if (count($companyInfo['tbl_company_purpose']['purpose_info']) == 1) {
	$buf = "�嵭�����Ӥ�����ڤζ�̳";
} else {
	$buf = "�嵭�ƹ�����Ӥ�����ڤζ�̳";
}
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

////ȯ�Բ�ǽ�������
//$buf = null;
//$buf .= "��ȯ�Բ�ǽ���������";
//$buf .= $companyInfo['tbl_company']['cmp_stock_total_num'];
//$buf .= "��";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
////ȯ�Ժѳ��������
////��Ω��ȯ�Գ����ο� = ���ܶ�(����) / 1����ñ��(��)
//$stockNum = ($companyInfo['tbl_company']['cmp_capital'] * 10000) / $companyInfo['tbl_company']['cmp_stock_price'];
//$stockNum = floor($stockNum);//ü�����ڤ�Τ�
//$stockNum = _ConvertNum2Ja($stockNum);
//
//$buf = null;
//$buf .= "��ȯ�Ժѳ����������";
//$buf .= $stockNum;
//$buf .= "��";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");

//���ܶ�γ�
//���ܶ��ñ�̤����ߤ���ߤˤ��롣
//$capital = $companyInfo['tbl_company']['cmp_capital'] * 10000;
$capital = $companyInfo['tbl_company']['cmp_capital'];
$capital = _ConvertNum2Ja($capital);

$buf = null;
$buf .= "�ֻ��ܶ�γۡ�";
$buf .= "��";
$buf .= $capital;
$buf .= "��";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

////�����ξ������¤˴ؤ��뵬��
//$buf = null;
//$buf .= "�ֳ����ξ������¤˴ؤ��뵬�������Ҥ�ȯ�Ԥ�������Ϥ��٤ƾ������³����Ȥ����������Ϥˤ���������ˤϡ��������ξ�ǧ���פ��롣";
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
////����˴ؤ������
//foreach ($mstPostList as $mpKey => $mstPostInfo) {
//	foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
//		if ($mstPostInfo['id'] != $boardInfo['cmp_bod_post_id']) continue;
//
//		$buf = null;
//		$buf .= "������˴ؤ�������";
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//
//		$buf = null;
//		$buf .= "�ֻ�ʡ�";
//		if ($mstPostInfo['id'] == MST_POST_ID_REP_DIRECTOR) {
//			//��ɽ������ξ�硢"������"�Ȥ���ɽ�����롣
//			$buf .= $mstPostList[MST_POST_ID_DIRECTOR]['name'];
//		} else {
//			$buf .= $mstPostInfo['name'];
//		}
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//
//		$buf = null;
//		$buf .= "�ֻ�̾��";
//		$buf .= $boardInfo['cmp_bod_family_name'];
//		$buf .= " ";
//		$buf .= $boardInfo['cmp_bod_first_name'];
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//	}
//}
//
////����˴ؤ������
//$buf = null;
//$buf .= "������˴ؤ�������";
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
//$buf = null;
//$buf .= "�ֻ�ʡ�";
//$buf .= $mstPostList[$repBoardInfo['cmp_bod_post_id']]['name'];
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
////�������
//$buf = null;
//$buf .= "�ֽ����";
//$buf .= $mstPrefList[$repBoardInfo['cmp_bod_pref_id']]['name'];
//$buf .= $repBoardInfo['cmp_bod_address1'];
//$buf .= $repBoardInfo['cmp_bod_address2'];
//$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
////���̾��
//$buf = null;
//$buf .= "�ֻ�̾��";
//$buf .= $repBoardInfo['cmp_bod_family_name'];
//$buf .= " ";
//$buf .= $repBoardInfo['cmp_bod_first_name'];
//$pdf->MultiCell(0,6,$buf,$border,"L");

//�Ұ��˴ؤ������
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	//��̳����ID="��̳���ԼҰ��ˤʤ�"�ʳ��ϡ����ء�
	if ($promoterInfo['cmp_prm_business_execution_id'] != MST_BUSINESS_EXECUTION_ID_YES) continue;

	//�ͳʼ��̤ˤ�äơ�̾�������ꤹ�롣
	$name = null;
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//�Ŀ�
			$name .= $promoterInfo['cmp_prm_family_name'];
			$name .= " ";
			$name .= $promoterInfo['cmp_prm_first_name'];
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//ˡ��(������ҡ�ͭ�²�ҤΤ�)
			$name .= $promoterInfo['cmp_prm_company_name'];
			break;
	}

	$buf = null;
	$buf .= "�ּҰ��˴ؤ�������";
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$buf = null;
	$buf .= "�ֻ�ʡ�";
	$buf .= "��̳���ԼҰ�";
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$buf = null;
	$buf .= "�ֻ�̾��";
	$buf .= $name;
	$pdf->MultiCell(0,6,$buf,$border,"L");
}

//�Ұ��˴ؤ������
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	//��ɽ�Ұ�ID="��ɽ�Ұ��ˤʤ�"�ʳ��ϡ����ء�
	if ($promoterInfo['cmp_prm_representative_partner_id'] != MST_REPRESENTATIVE_PARTNER_ID_YES) continue;

	//�ͳʼ��̤ˤ�äơ�̾������������ꤹ�롣
	$name = null;
	$address = null;
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//�Ŀ�
			$name .= $promoterInfo['cmp_prm_family_name'];
			$name .= " ";
			$name .= $promoterInfo['cmp_prm_first_name'];

			$address .= $mstPrefList[$promoterInfo['cmp_prm_pref_id']]['name'];
			$address .= $promoterInfo['cmp_prm_address1'];
			$address .= $promoterInfo['cmp_prm_address2'];
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//ˡ��(������ҡ�ͭ�²�ҤΤ�)
			$name .= $promoterInfo['cmp_prm_company_name'];

			$address .= $mstPrefList[$promoterInfo['cmp_prm_company_pref_id']]['name'];
			$address .= $promoterInfo['cmp_prm_company_address1'];
			$address .= $promoterInfo['cmp_prm_company_address2'];
			break;
	}

	$buf = null;
	$buf .= "�ּҰ��˴ؤ�������";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$buf = null;
	$buf .= "�ֻ�ʡ�";
	$buf .= "��ɽ�Ұ�";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	//�������
	$buf = null;
	$buf .= "�ֽ����";
	$buf .= $address;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	//���̾��
	$buf = null;
	$buf .= "�ֻ�̾��";
	$buf .= $name;
	$pdf->MultiCell(0,6,$buf,$border,"L");
}

//switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
//	case MST_BOARD_FORMATION_ID_1_10:
//		//�����������֤��ʤ������1��10�ͤ���Ω
//		break;
//	case MST_BOARD_FORMATION_ID_3_1:
//		//�����������֤��롡���3�ͤȴƺ���1�ͤ���Ω
//
//		//����������ֲ�Ҥ˴ؤ������
//		$buf = null;
//		$buf .= "�ּ���������ֲ�Ҥ˴ؤ������׼���������ֲ��";
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//
//		//�ƺ������ֲ�Ҥ˴ؤ������
//		$buf = null;
//		$buf .= "�ִƺ������ֲ�Ҥ˴ؤ������״ƺ������ֲ��";
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//
//		//���������Ǥ�Ƚ������
//		$buf = null;
//		$buf .= "�ּ��������Ǥ�Ƚ�����������Ҥϡ����ˡ�裴�������裱��ι԰٤˴ؤ�����������Ǥ�ˤĤ��ơ����������򤬿�̳��Ԥ��ˤĤ����դǤ��Ľ���ʲἺ���ʤ����ˤ����ơ���Ǥ�θ����Ȥʤä����¤����ơ�����������ο�̳�μ��Ԥξ�������¾�λ���򴪰Ƥ����ä�ɬ�פ�ǧ���Ȥ���ˡ��������׷�˳���������ˤϡ����ˡ�裴�������裱��������ϰϤǼ������η�Ĥˤ���Ƚ����뤳�Ȥ��Ǥ��롣";
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//		break;
//}

//�е���Ͽ�˴ؤ������
$buf = null;
$buf .= "���е���Ͽ�˴ؤ���������Ω";
$pdf->MultiCell(0,6,$buf,$border,"L");


//DB�򥯥������롣
_DB_Close($link);


//PDF����Ϥ��롣
$pdf->Output();

_Log("[/user/llc/pdf/create/tokishinseisho.php] end. OK!");



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
