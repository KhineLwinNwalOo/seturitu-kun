<?php
/*
 * [���������Ω.JP �ġ���]
 * PDF����
 * Ĵ������(��Ʊ�����)
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
_Log("[/user/llc/pdf/create/chosahokokusho.php] start.");

_Log("[/user/llc/pdf/create/chosahokokusho.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/llc/pdf/create/chosahokokusho.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/llc/pdf/create/chosahokokusho.php] SERVER = '".print_r($_SERVER,true)."'");


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

		_Log("[/user/llc/pdf/create/chosahokokusho.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
		_Log("[/user/llc/pdf/create/chosahokokusho.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
		_Log("[/user/llc/pdf/create/chosahokokusho.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

		$undeleteOnly4def = true;

		//��ʬ�Υ桼�������󡢲�Ҿ���Τ�ɽ�����롣
		//�桼����ID�����ID������å����롣

		//���ID�򸡺����롣
		$relationCompanyId = _GetRelationLlcId($loginInfo['usr_user_id']);


		_Log("[/user/llc/pdf/create/chosahokokusho.php] {������桼�������½���} ��(������)�桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/llc/pdf/create/chosahokokusho.php] {������桼�������½���} ��(������)���ID = '".$relationCompanyId."'");
		_Log("[/user/llc/pdf/create/chosahokokusho.php] {������桼�������½���} ��(�ѥ�᡼����)�桼����ID = '".$userId."'");
		_Log("[/user/llc/pdf/create/chosahokokusho.php] {������桼�������½���} ��(�ѥ�᡼����)���ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/llc/pdf/create/chosahokokusho.php] {������桼�������½���} ��(�����о�)�桼����ID = '".$userId."'");
		_Log("[/user/llc/pdf/create/chosahokokusho.php] {������桼�������½���} ��(�����о�)���ID = '".$companyId."'");

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
////1����ñ��
//if (_IsNull($companyInfo['tbl_company']['cmp_stock_price'])) $errorList[] = "��1����ñ���٤���Ͽ���Ƥ���������";
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
$errFlag = false;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	//��ɽ�Ұ�ID="��ɽ�Ұ��ˤʤ�"��1�ͤǤ⤤�뤫��
	if ($promoterInfo['cmp_prm_representative_partner_id'] == MST_REPRESENTATIVE_PARTNER_ID_YES) {
		$representativePartnerFlag = true;
	}

	//�ͳʼ��̤ˤ�äơ������å����ܤ��ڤ��ؤ��롣
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//�Ŀ�
			if (_IsNull($promoterInfo['cmp_prm_family_name']) || _IsNull($promoterInfo['cmp_prm_first_name'])) {
				$errFlag = true;
			}
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//ˡ��(������ҡ�ͭ�²�ҤΤ�)
			if (_IsNull($promoterInfo['cmp_prm_company_name'])) {
				$errFlag = true;
			}
			break;
	}
}
if ($errFlag) $errorList[] = "�ؼҰ�(�л��)�٤Ρؤ�̾�������ϡ��ز��̾(ˡ��)�٤���Ͽ���Ƥ���������";
if (!$representativePartnerFlag) $errorList[] = "�ؼҰ�(�л��)�٤Ρ���ɽ�Ұ��٤ǡ���ɽ�Ұ��ˤʤ�٤�1�Ͱʾ���Ͽ���Ƥ���������";
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
if (!$inkindFlag) {
	//��ʪ�л�Ԥ����ʤ���硢����������ס�
	$errorList = array();
	$errorList[] = "����ա۸�ʪ�л񤬤���ޤ��󡣡�Ĵ������٤ϡ���������ޤ���";
}


if (count($errorList) > 0) {
	//���顼ͭ�ξ��
	_Log("[/user/llc/pdf/create/chosahokokusho.php] end. ERR!");


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
$title = "Ĵ������";


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


$buf = null;
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$buf .= "���괾���ä���Ω����ɽ�Ұ�����Ǥ���줿�Τǡ����ˡ��46��ε���˴�Ť���Ĵ�����������η�̤ϼ��ΤȤ���Ǥ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(20);


$pdf->SetFontSize(13);

$buf = "��";
$pdf->Cell(0,6,$buf,$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(10);

$no = 0;

$buf = (++$no);
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(13,6,$buf,$border,0,"L");

//���ܶ�(����)
//$capital = $companyInfo['tbl_company']['cmp_capital'] * 10000;
$capital = $companyInfo['tbl_company']['cmp_capital'];
$capital = _ConvertNum2Ja($capital);

//������(ǯ)��������ѹ����롣
$payYearJp = _ConvertAD2Jp($payYear);

$buf = null;
$buf .= "��Ω�����ܶ�";
$buf .= $capital;
$buf .= "�ߤϡ�";
$buf .= $payYearJp."ǯ";
$buf .= $payMonth."��";
$buf .= $payDay."��";
$buf .= "�ޤǤ�";
//$buf .= "ȯ���ͤˤ������������ä����Ȥ�ǧ����롣";
$buf .= "�����������ä����Ȥ�ǧ����롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln();

$buf = (++$no);
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(13,6,$buf,$border,0,"L");


$buf = null;
$buf .= "�괾�˵��ܤ��줿��ʪ�л�⻺�β��ۤ˴ؤ������";
$pdf->MultiCell(0,6,$buf,$border,"L");

$buf = null;
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = null;
$buf .= "�ʲ��ˡ�裳�����裱����˳�����������";
$pdf->MultiCell(0,6,$buf,$border,"L");

$buf = null;
$pdf->Cell(13,6,$buf,$border,0,"L");


//�л��򽸷פ��롣
$totalInvestmentCash = 0;				//����ζ�۹��
$totalInvestmentCash4Show = null;		//����ζ�۹��(ɽ����)
$totalInvestmentInkind = 0;				//��ʪ�ζ�۹��
$totalInvestmentInkind4Show = null;		//��ʪ�ζ�۹��(ɽ����)

$totalCurrentPriceInkind = null;		//��ʪ�λ������
$totalCurrentPriceInkind4Show = null;	//��ʪ�λ������(ɽ����)

$totalInvestment = 0;					//�л��ι��
$totalInvestment4Show = null;			//�л��ι��(ɽ����)

//��ʪ�л�Ԥ�̾�������֤��롣
$nameInkind = null;

foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	//ȯ����ñ�̤ξ���
	$investmentCash = 0;				//����νл��
	$investmentInkind = 0;				//��ʪ�νл��

	//�л�����Ͽ�Ϥ��뤫��
	if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

		//����νл�򽸷פ��롣
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_CASH])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_CASH]['investment_info'] as $iKey => $investmentInfo) {
//				$investmentCash += $investmentInfo['cmp_prm_inv_investment'] * 10000;
				$investmentCash += $investmentInfo['cmp_prm_inv_investment'];
			}
		}

		//��ʪ�νл�򽸷פ��롣
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
//				$investmentInkind += $investmentInfo['cmp_prm_inv_investment'] * 10000;
				$investmentInkind += $investmentInfo['cmp_prm_inv_investment'];
			}
		}
	}

	$totalInvestmentCash += $investmentCash;
	$totalInvestmentInkind += $investmentInkind;


	//�л����(����Τߡ���ʪ�л�Τߡ�����ܸ�ʪ)�ˤ�äơ���ʪ�νл�Ԥ�Ƚ�Ǥ��롣
	switch ($promoterInfo['cmp_prm_investment_shape_id']) {
		case MST_INVESTMENT_SHAPE_ID_CASH:
			//����Τ�
			break;
		case MST_INVESTMENT_SHAPE_ID_INKIND:
			//��ʪ�л�Τ�
		case MST_INVESTMENT_SHAPE_ID_CASH_INKIND:
			//����ܸ�ʪ

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

			if (!_IsNull($nameInkind)) $nameInkind .= "��";
//			$nameInkind .= "ȯ����";
			$nameInkind .= "ͭ����Ǥ�Ұ�";
			$nameInkind .= " ";
			$nameInkind .= $name;
			break;
	}
}

//���ͤ���ɽ�����Ѵ����롣
$totalInvestmentCash4Show = "��"._ConvertNum2Ja($totalInvestmentCash)."��";

//���ͤ���ɽ�����Ѵ����롣
$totalInvestmentInkind4Show = "��"._ConvertNum2Ja($totalInvestmentInkind)."��";

//��ʪ�λ�����׻����롣��10��
$totalCurrentPriceInkind = $totalInvestmentInkind + 100000;
$totalCurrentPriceInkind4Show = "��"._ConvertNum2Ja($totalCurrentPriceInkind)."��";

//�л��ι��
$totalInvestment = $totalInvestmentCash + $totalInvestmentInkind;
//���ͤ���ɽ�����Ѵ����롣
$totalInvestment4Show = "��"._ConvertNum2Ja($totalInvestment)."��";


$buf = null;
$buf .= "�괾����᤿����ʪ�л�򤹤�Ԥλ�̾��";
$buf .= $nameInkind;
$buf .= "�Ǥ��ꡢ";
$buf .= "\n";
$buf .= "�л����Ū�Ǥ���⻺�ڤӤ��β��ۤϡ������ΤȤ���Ǥ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln();

$buf = null;
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = "�ʣ���";
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = null;
$buf .= "�л�⻺�ڤӤ��β���";
$pdf->MultiCell(0,6,$buf,$border,"L");

foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	//�л����(����Τߡ���ʪ�л�Τߡ�����ܸ�ʪ)�ˤ�äơ���ʪ�νл�Ԥ�Ƚ�Ǥ��롣
	switch ($promoterInfo['cmp_prm_investment_shape_id']) {
		case MST_INVESTMENT_SHAPE_ID_CASH:
			//����Τ�
			continue 2;
		case MST_INVESTMENT_SHAPE_ID_INKIND:
			//��ʪ�л�Τ�
		case MST_INVESTMENT_SHAPE_ID_CASH_INKIND:
			//����ܸ�ʪ
			break;
	}

	//�л�����Ͽ�Ϥ��뤫��
	if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

		//��ʪ�νл�򽸷פ��롣
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
				$buf = null;
				$pdf->Cell(13,6,$buf,$border,0,"L");

				$buf = null;
				$pdf->Cell(13,6,$buf,$border,0,"L");

				$buf = null;
				$buf .= $investmentInfo['cmp_prm_inv_in_kind'];
				$buf = mb_convert_kana($buf, 'N');
				$pdf->MultiCell(0,6,$buf,$border,"L");

				$pdf->Ln();
			}
		}
	}
}

$buf = null;
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = "�ʣ���";
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = null;
$buf .= "�괾�˵��ܤ��줿����";
$pdf->Cell(70,6,$buf,$border,0,"L");

//��ʪ���
$buf = null;
$buf .= $totalInvestmentInkind4Show;
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(50,6,$buf,$border,0,"R");

$pdf->Ln();
$pdf->Ln();


$buf = null;
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = null;
$buf .= "�嵭�ˤĤ��Ƥϡ�����";
$buf .= $totalCurrentPriceInkind4Show;
$buf .= "�ȸ��Ѥ����٤��Ȥ����괾�˵��ܤ���ɾ�����ʤ�";
$buf .= $totalInvestmentInkind4Show;
$buf .= "�Ǥ��ꡢ";
$buf .= "�����괾�����������ʤ�Τ�ǧ����롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln();

$buf = (++$no);
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = null;
$buf .= $nameInkind;
$buf .= "�ΰ������ˤ�����";
$buf .= $totalInvestmentInkind4Show;
$buf .= "�ˤĤ��ơ����θ�ʪ�л����Ū����⻺�ε��դ����ä����Ȥϡ�";
$buf .= $payYearJp."ǯ";
$buf .= $payMonth."��";
$buf .= $payDay."��";
$buf .= "���̻�⻺���ѽ�ˤ��ǧ����롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln();

$buf = (++$no);
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = null;
$buf .= "��Ҥ���Ω�����ܶ�Τ�������ʪ�л�ˤ��";
$buf .= $totalInvestmentInkind4Show;
$buf .= "�����";
$buf .= $totalInvestmentCash4Show;
$buf .= "�ˤĤ���";
$buf .= $payYearJp."ǯ";
$buf .= $payMonth."��";
$buf .= $payDay."��";
$buf .= "�ޤǤˤ���ȯ�Բ��ۤ����ۤ�ʧ���ߤ���λ���Ƥ��뤳�Ȥϡ��̻�ʧ��������ˤ��ǧ����롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln();

$buf = (++$no);
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = null;
$buf .= "�嵭����ʳ�����Ω�˴ؤ����³��ˡ�������괾�˰�ȿ���Ƥ�����¤Ϥʤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln(30);

$buf = null;
$buf .= "�嵭�ΤȤ�����ˡ�ε���˽�����𤹤롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(24);


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
$errFlag = false;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	//��ɽ�Ұ�ID="��ɽ�Ұ��ˤʤ�"�ʳ��ϡ����ء�
	if ($promoterInfo['cmp_prm_representative_partner_id'] != MST_REPRESENTATIVE_PARTNER_ID_YES) continue;

	//�ͳʼ��̤ˤ�äơ�̾������������ꤹ�롣
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
	$pdf->Cell(20,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $name;
	$pdf->Cell(120,6,$buf,$border,0,"L");

	$buf = "��";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln(15);
}











//DB�򥯥������롣
_DB_Close($link);


//PDF����Ϥ��롣
$pdf->Output();

_Log("[/user/llc/pdf/create/chosahokokusho.php] end. OK!");



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
