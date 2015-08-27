<?php
/*
 * [���������Ω.JP �ġ���]
 * PDF����
 * Ĵ������
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
_Log("[/user/company/pdf/create/zaisanhikitugisho.php] start.");

_Log("[/user/company/pdf/create/zaisanhikitugisho.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/company/pdf/create/zaisanhikitugisho.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/company/pdf/create/zaisanhikitugisho.php] SERVER = '".print_r($_SERVER,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�������󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/index.php] �������󤷤Ƥ��ʤ��ʤΤǥ���������̤�ɽ�����롣");
	_Log("[/user/index.php] end.");
	//����������̤�ɽ�����롣
	header("Location: ".URL_LOGIN);
	exit;
} else {
	//������������������롣
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢����������̤����ܤ��롣
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

		_Log("[/user/company/pdf/create/zaisanhikitugisho.php] {��������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
		_Log("[/user/company/pdf/create/zaisanhikitugisho.php] {��������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
		_Log("[/user/company/pdf/create/zaisanhikitugisho.php] {��������桼�������½���} ���桼����ID�����ꤹ�롣");

		$undeleteOnly4def = true;

		//��ʬ�Υ桼�������󡢲�Ҿ���Τ�ɽ�����롣
		//�桼����ID�����ID������å����롣

		//���ID�򸡺����롣
		$relationCompanyId = _GetRelationCompanyId($loginInfo['usr_user_id']);


		_Log("[/user/company/pdf/create/zaisanhikitugisho.php] {��������桼�������½���} ��(��������)�桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/create/zaisanhikitugisho.php] {��������桼�������½���} ��(��������)���ID = '".$relationCompanyId."'");
		_Log("[/user/company/pdf/create/zaisanhikitugisho.php] {��������桼�������½���} ��(�ѥ�᡼����)�桼����ID = '".$userId."'");
		_Log("[/user/company/pdf/create/zaisanhikitugisho.php] {��������桼�������½���} ��(�ѥ�᡼����)���ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/company/pdf/create/zaisanhikitugisho.php] {��������桼�������½���} ��(�����о�)�桼����ID = '".$userId."'");
		_Log("[/user/company/pdf/create/zaisanhikitugisho.php] {��������桼�������½���} ��(�����о�)���ID = '".$companyId."'");

		break;
}

//��������å�
if (!_IsNull($companyId)) {
	if (!_CheckUserStatus($userId, $companyId, MST_SYSTEM_COURSE_ID_CMP)) {
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
//1����ñ��
if (_IsNull($companyInfo['tbl_company']['cmp_stock_price'])) $errorList[] = "��1����ñ���٤���Ͽ���Ƥ���������";
//ȯ����
$errFlag = false;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	if (_IsNull($promoterInfo['cmp_prm_family_name']) || _IsNull($promoterInfo['cmp_prm_first_name'])) {
		$errFlag = true;
		break;
	}
	if (_IsNull($promoterInfo['cmp_prm_pref_id']) || _IsNull($promoterInfo['cmp_prm_address1'])) {
		$errFlag = true;
		break;
	}
}
if ($errFlag) $errorList[] = "��ȯ���͡٤Ρؤ�̾���١��ؽ���٤���Ͽ���Ƥ���������";
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
			//����
			if (_IsNull($investmentInfo['cmp_prm_inv_stock_num'])) {
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
if ($errFlag) $errorList[] = "�ؽл��٤Ρس����١��ظ�ʪ�л����̾�٤���Ͽ���Ƥ���������";

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
	$errorList[] = "�����ա۸�ʪ�л񤬤���ޤ��󡣡غ⻺���ѽ�٤ϡ���������ޤ���";
}


if (count($errorList) > 0) {
	//���顼ͭ�ξ��
	_Log("[/user/company/pdf/create/zaisanhikitugisho.php] end. ERR!");


	$buf = "��PDF��������뤿��ξ���­��ޤ��󡣡س��������Ω������Ͽ�ٲ��̤ǡ���������Ϥ��Ƥ������������ϡ��سƼ������� �����ٲ��̤ǡ���������Ϥ��Ƥ���������";
	array_unshift($errorList, $buf);

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//���顼���̤�ɽ�����롣
	header("Location: ../error.php");
	exit;
}


//�ޥ��������������롣
$undeleteOnly = false;
$mstPostList = _GetMasterList('mst_post');		//�򿦥ޥ���
$mstPrefList = _GetMasterList('mst_pref');		//��ƻ�ܸ��ޥ���
unset($mstPrefList[MST_PREF_ID_OVERSEAS]);

//���--------------------------------------------start
//�ե���ȥ�������������롣
//�̾�
$normalFontSize = 10;

//�����ȥ�
$title = "�⻺���ѽ�";


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

	$pdf->AddPage();

	//�����ȥ�
	$pdf->SetFontSize(18);
	$pdf->Cell(0,10,$title,$border,0,"C");
	$pdf->Ln(30);


	$pdf->SetFontSize(10);


	$no = 0;

	$buf = (++$no);
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(13,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= "��ʪ�л�⻺���ڤӤ��β���";
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$pdf->Ln();

	//�л��򽸷פ��롣
	$totalStockNumInkind = 0;				//��ʪ�γ������
	$totalStockNumInkind4Show = null;		//��ʪ�γ������(ɽ����)
	$totalInvestmentInkind = 0;				//��ʪ�ζ�۹��
	$totalInvestmentInkind4Show = null;		//��ʪ�ζ�۹��(ɽ����)


	//�л�����Ͽ�Ϥ��뤫��
	if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

		//��ʪ�νл�򽸷פ��롣
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {

			$countInkind = 0;
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {

				//�����ι�פ�׻����롣
				$totalStockNumInkind += $investmentInfo['cmp_prm_inv_stock_num'];

				$investmentInkind = 0;				//��ʪ�ζ��
				$investmentInkind4Show = null;		//��ʪ�ζ��(ɽ����)

				//��������л��ۤ�׻����롣1����ñ��(��)�߸���γ���
				$investmentInkind = $companyInfo['tbl_company']['cmp_stock_price'] * $investmentInfo['cmp_prm_inv_stock_num'];
				//���ͤ���ɽ�����Ѵ����롣
				$investmentInkind4Show = "��"._ConvertNum2Ja($investmentInkind)."��";


				$countInkind++;

				$buf = null;
				$pdf->Cell(13,6,$buf,$border,0,"L");

				$buf = null;
				$buf .= "��";
				$buf .= $countInkind;
				$buf .= "��";
				$buf = mb_convert_kana($buf, 'N');
				$pdf->Cell(13,6,$buf,$border,0,"L");

				$buf = null;
				$buf .= $investmentInfo['cmp_prm_inv_in_kind'];
				$buf = mb_convert_kana($buf, 'N');
				$pdf->MultiCell(0,6,$buf,$border,"L");

				$buf = null;
				$pdf->Cell(13,6,$buf,$border,0,"L");

				$buf = null;
				$pdf->Cell(13,6,$buf,$border,0,"L");

				$buf = null;
				$buf .= "���β���";
				$pdf->Cell(20,6,$buf,$border,0,"L");

				$buf = null;
				$buf .= $investmentInkind4Show;
				$buf = mb_convert_kana($buf, 'N');
				$pdf->Cell(50,6,$buf,$border,0,"R");

				$pdf->Ln();
				$pdf->Ln();
			}
		}
	}

	//��������л��ۤ�׻����롣1����ñ��(��)�߸�ʪ�γ���
	$totalInvestmentInkind = $companyInfo['tbl_company']['cmp_stock_price'] * $totalStockNumInkind;
	//���ͤ���ɽ�����Ѵ����롣
	$totalInvestmentInkind4Show = "��"._ConvertNum2Ja($totalInvestmentInkind)."��";
	$totalStockNumInkind4Show = _ConvertNum2Ja($totalStockNumInkind)."��";


	$buf = null;
	$pdf->Cell(13,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= "��ײ���";
	$pdf->Cell(33,6,$buf,$border,0,"L");

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
	$buf .= "Ϳ��������ο�";
	$pdf->Cell(33,6,$buf,$border,0,"L");

	//��ʪ����
	$buf = null;
	$buf .= $totalStockNumInkind4Show;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(50,6,$buf,$border,0,"R");

	//��ʪ���
	$buf = null;
	$buf .= $totalInvestmentInkind4Show;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(50,6,$buf,$border,0,"R");


	$pdf->Ln(30);

	$buf = null;
	$buf .= "���ͭ�ξ嵭�⻺��ʪ�л�⻺�Ȥ��Ƶ��դ��ޤ���";
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


	$buf = "������";
	$pdf->Cell(20,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $mstPrefList[$promoterInfo['cmp_prm_pref_id']]['name'];
	$buf .= $promoterInfo['cmp_prm_address1'];
	$buf .= $promoterInfo['cmp_prm_address2'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$buf = "ȯ����";
	$pdf->Cell(20,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $promoterInfo['cmp_prm_family_name'];
	$buf .= " ";
	$buf .= $promoterInfo['cmp_prm_first_name'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(120,6,$buf,$border,0,"L");

	$buf = "��";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln(15);


	//����(���̾)
	$buf = null;
	$buf .= $companyInfo['tbl_company']['cmp_company_name'];
	$buf .= " ";
	$buf .= "ȯ����";
	$buf .= " ";
	$buf .= "����";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();
}














//DB�򥯥��������롣
_DB_Close($link);


//PDF����Ϥ��롣
$pdf->Output();

_Log("[/user/company/pdf/create/zaisanhikitugisho.php] end. OK!");



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