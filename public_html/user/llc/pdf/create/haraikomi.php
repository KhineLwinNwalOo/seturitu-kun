<?php
/*
 * [���������Ω.JP �ġ���]
 * PDF����
 * ʧ��������(��Ʊ�����)
 *
 * ��������2008/12/01	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../../common/include.ini");
include_once("../../../../common/libs/fpdf/mbfpdf.php");


_LogDelete();
//_LogBackup();
_Log("[/user/llc/pdf/create/haraikomi.php] start.");

_Log("[/user/llc/pdf/create/haraikomi.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/llc/pdf/create/haraikomi.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/llc/pdf/create/haraikomi.php] SERVER = '".print_r($_SERVER,true)."'");


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
$payYear = ((isset($inData['pay_year']) && !_IsNull($inData['pay_year']))?$inData['pay_year']:null);
$payMonth = ((isset($inData['pay_month']) && !_IsNull($inData['pay_month']))?$inData['pay_month']:null);
$payDay = ((isset($inData['pay_day']) && !_IsNull($inData['pay_day']))?$inData['pay_day']:null);


//����ͤ����ꤹ�롣
$undeleteOnly4def = false;

//���¤ˤ�äơ�ɽ������桼������������¤��롣
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://����̵��

		_Log("[/user/llc/pdf/create/haraikomi.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
		_Log("[/user/llc/pdf/create/haraikomi.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
		_Log("[/user/llc/pdf/create/haraikomi.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

		$undeleteOnly4def = true;

		//��ʬ�Υ桼�������󡢲�Ҿ���Τ�ɽ�����롣
		//�桼����ID�����ID������å����롣

		//���ID�򸡺����롣
		$relationCompanyId = _GetRelationLlcId($loginInfo['usr_user_id']);


		_Log("[/user/llc/pdf/create/haraikomi.php] {������桼�������½���} ��(������)�桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/llc/pdf/create/haraikomi.php] {������桼�������½���} ��(������)���ID = '".$relationCompanyId."'");
		_Log("[/user/llc/pdf/create/haraikomi.php] {������桼�������½���} ��(�ѥ�᡼����)�桼����ID = '".$userId."'");
		_Log("[/user/llc/pdf/create/haraikomi.php] {������桼�������½���} ��(�ѥ�᡼����)���ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/llc/pdf/create/haraikomi.php] {������桼�������½���} ��(�����о�)�桼����ID = '".$userId."'");
		_Log("[/user/llc/pdf/create/haraikomi.php] {������桼�������½���} ��(�����о�)���ID = '".$companyId."'");

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
//������
$errFlag = false;
if (_IsNull($payYear)) $errFlag = true;
if (_IsNull($payMonth)) $errFlag = true;
if (_IsNull($payDay)) $errFlag = true;
if ($errFlag)  $errorList[] = "�ؿ������٤���Ͽ���Ƥ���������";
//���̾
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "�ؾ���(���̾)�٤���Ͽ���Ƥ���������";
//���ܶ�
if (_IsNull($companyInfo['tbl_company']['cmp_capital'])) $errorList[] = "�ػ��ܶ�٤���Ͽ���Ƥ���������";
////������
//$errFlag = false;
//foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
//	if (_IsNull($boardInfo['cmp_bod_family_name']) || _IsNull($boardInfo['cmp_bod_first_name'])) {
//		$errFlag = true;
//		break;
//	}
//}
//if ($errFlag) $errorList[] = "�ؼ�����٤Ρؤ�̾���٤���Ͽ���Ƥ���������";
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
				break 2;
			}
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//ˡ��(������ҡ�ͭ�²�ҤΤ�)
			if (_IsNull($promoterInfo['cmp_prm_company_name'])) {
				$errFlag = true;
				break 2;
			}
			break;
	}
}
if ($errFlag) $errorList[] = "�ؼҰ�(�л��)�٤Ρؤ�̾�������ϡ��ز��̾(ˡ��)�٤���Ͽ���Ƥ���������";
if (!$representativePartnerFlag) $errorList[] = "�ؼҰ�(�л��)�٤Ρ���ɽ�Ұ��٤ǡ���ɽ�Ұ��ˤʤ�٤�1�Ͱʾ���Ͽ���Ƥ���������";
if (!$businessExecutionFlag) $errorList[] = "�ؼҰ�(�л��)�٤Ρض�̳���ԡ٤ǡض�̳���ԼҰ��ˤʤ�٤�1�Ͱʾ���Ͽ���Ƥ���������";
//�л��
$errFlag = false;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	//�л�����Ͽ�Ϥ��뤫��
	if (!isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$errFlag = true;
		break;
	}
	$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

	//�л����(����Τߡ���ʪ�л�Τߡ�����ܸ�ʪ)�ˤ�äơ����⡦��ʪ����Ͽ�Ϥ��뤫��
	switch ($promoterInfo['cmp_prm_investment_shape_id']) {
		case MST_INVESTMENT_SHAPE_ID_CASH:
			//����Τ�
			//�л񥿥��ץޥ���="����"����Ͽ�Ϥ��뤫��
			if (!isset($investmentList[MST_INVESTMENT_TYPE_ID_CASH])) {
				$errFlag = true;
				break 2;
			}
			break;
		case MST_INVESTMENT_SHAPE_ID_INKIND:
			//��ʪ�л�Τ�
			//�л񥿥��ץޥ���="��ʪ"����Ͽ�Ϥ��뤫��
			if (!isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
				$errFlag = true;
				break 2;
			}
			break;
		case MST_INVESTMENT_SHAPE_ID_CASH_INKIND:
			//����ܸ�ʪ
			//�л񥿥��ץޥ���="����"����Ͽ�Ϥ��뤫��
			if (!isset($investmentList[MST_INVESTMENT_TYPE_ID_CASH])) {
				$errFlag = true;
				break 2;
			}
			//�л񥿥��ץޥ���="��ʪ"����Ͽ�Ϥ��뤫��
			if (!isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
				$errFlag = true;
				break 2;
			}
			break;
	}

	//���⡦��ʪ�γ�������ʪ�л����̾����Ͽ�Ϥ��뤫��
	foreach ($investmentList as $investmentTypeId => $investmentTypeList) {
		foreach ($investmentTypeList['investment_info'] as $itKey => $investmentInfo) {
			//�л��
			if (_IsNull($investmentInfo['cmp_prm_inv_investment'])) {
				$errFlag = true;
				break 3;
			}

			//�л񥿥��ץޥ����ˤ�äƥ����å����롣
			switch ($investmentTypeId) {
				case MST_INVESTMENT_TYPE_ID_INKIND:
					//��ʪ�л����̾
					if (_IsNull($investmentInfo['cmp_prm_inv_in_kind'])) {
						$errFlag = true;
						break 4;
					}
					break;
			}
		}
	}
}
if ($errFlag) $errorList[] = "�ؽл��٤Ρؽл�ۡ١��ظ�ʪ�л����̾�٤���Ͽ���Ƥ���������";




if (count($errorList) > 0) {
	//���顼ͭ�ξ��
	_Log("[/user/llc/pdf/create/haraikomi.php] end. ERR!");


	$buf = "��PDF��������뤿��ξ���­��ޤ��󡣡ع�Ʊ�����ΩLLC������Ͽ�ٲ��̤ǡ���������Ϥ��Ƥ������������ϡ��سƼ������� �����ٲ��̤ǡ���������Ϥ��Ƥ���������";
	array_unshift($errorList, $buf);

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//���顼���̤�ɽ�����롣
	header("Location: ../error.php");
	exit;
}


//�ޥ��������������롣
$undeleteOnly = false;
$mstPostList = _GetMasterList('mst_post');		//�򿦥ޥ���

//���--------------------------------------------start
//�ե���ȥ�������������롣
//�̾�
$normalFontSize = 10;

//�����ȥ�
$title = "ʧ��������";


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


$buf = "����Ҥλ��ܶ�ˤĤ��Ƥϰʲ��ΤȤ��ꡢ���ۤ�ʧ���ߤ����ä����Ȥ�������ޤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$buf = "ʧ���ߤ���������";
$pdf->Cell(50,6,$buf,$border,0,"L");

//���ܶ�
$buf = null;
//ñ�̡����ߢ��ߤ��Ѵ����롣
//$buf = $companyInfo['tbl_company']['cmp_capital'] * 10000;
$buf = $companyInfo['tbl_company']['cmp_capital'];

//��ʪ�ι�׶�ۤ�׻����롣
$totalInvestmentInkind = 0;	//��ʪ�ζ��
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	//�л�����Ͽ�Ϥ��뤫��
	if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

		//��ʪ�νл�򽸷פ��롣
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
				$totalInvestmentInkind += $investmentInfo['cmp_prm_inv_investment'];
			}
		}
	}
}
if ($totalInvestmentInkind > 0) {
	//���ܶ⤫�鸽ʪ�ι�׶�ۤ������
//	$buf -= $totalInvestmentInkind * 10000;
	$buf -= $totalInvestmentInkind;
}

//���ͤ���ɽ�����Ѵ����롣
$buf = _ConvertNum2Ja($buf);

$buf = "��".$buf."��";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln(30);


////������
//$buf = null;
//$buf .= _ConvertAD2Jp($pdfCreateYear);
//$buf .= "ǯ";
//$buf .= $pdfCreateMonth;
//$buf .= "��";
//$buf .= $pdfCreateDay;
//$buf .= "��";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");
//$pdf->Ln();
//
//$pdf->Ln(10);

//������
$payYearJp = _ConvertAD2Jp($payYear);
$buf = $payYearJp."ǯ";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(20,6,$buf,$border,0,"L");
$buf = $payMonth."��";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"R");
$buf = $payDay."��";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"R");
$buf = null;
$pdf->Cell(0,6,$buf,$border,0,"R");

$pdf->Ln(20);


//����(���̾)
$buf = null;
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//ȯ����
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	//��ɽ�Ұ�ID="��ɽ�Ұ��ˤʤ�"�ʳ��ϡ����ء�
	if ($promoterInfo['cmp_prm_representative_partner_id'] != MST_REPRESENTATIVE_PARTNER_ID_YES) continue;

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

	$buf = "��ɽ�Ұ�";
	$pdf->Cell(30,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $name;
	$pdf->Cell(120,6,$buf,$border,0,"L");

	$buf = "��";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln(15);

	//��ͤ�����OK��
	break;
}











//DB�򥯥������롣
_DB_Close($link);


//PDF����Ϥ��롣
$pdf->Output();

_Log("[/user/llc/pdf/create/haraikomi.php] end. OK!");



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
