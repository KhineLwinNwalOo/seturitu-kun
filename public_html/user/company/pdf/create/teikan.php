<?php
/*
 * [���������Ω.JP �ġ���]
 * PDF����
 * �괾
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
_Log("[/user/company/pdf/create/teikan.php] start.");

_Log("[/user/company/pdf/create/teikan.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/company/pdf/create/teikan.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/company/pdf/create/teikan.php] SERVER = '".print_r($_SERVER,true)."'");


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

		_Log("[/user/company/pdf/create/teikan.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
		_Log("[/user/company/pdf/create/teikan.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
		_Log("[/user/company/pdf/create/teikan.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

		$undeleteOnly4def = true;

		//��ʬ�Υ桼�������󡢲�Ҿ���Τ�ɽ�����롣
		//�桼����ID�����ID������å����롣

		//���ID�򸡺����롣
		$relationCompanyId = _GetRelationCompanyId($loginInfo['usr_user_id']);


		_Log("[/user/company/pdf/create/teikan.php] {������桼�������½���} ��(������)�桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/create/teikan.php] {������桼�������½���} ��(������)���ID = '".$relationCompanyId."'");
		_Log("[/user/company/pdf/create/teikan.php] {������桼�������½���} ��(�ѥ�᡼����)�桼����ID = '".$userId."'");
		_Log("[/user/company/pdf/create/teikan.php] {������桼�������½���} ��(�ѥ�᡼����)���ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/company/pdf/create/teikan.php] {������桼�������½���} ��(�����о�)�桼����ID = '".$userId."'");
		_Log("[/user/company/pdf/create/teikan.php] {������桼�������½���} ��(�����о�)���ID = '".$companyId."'");

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
//���̾
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "�ؾ���(���̾)�٤���Ͽ���Ƥ���������";
//��Ū
$errFlag = true;
foreach ($companyInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (!_IsNull($purposeInfo['cmp_pps_purpose'])) {
		$errFlag = false;
		break;
	}
}
if ($errFlag) $errorList[] = "����Ū�٤���Ͽ���Ƥ���������";
//��Ź�����
$errFlag = false;
if (_IsNull($companyInfo['tbl_company']['cmp_pref_id'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_address1'])) $errFlag = true;
if ($errFlag) $errorList[] = "����Ź����ϡ٤���Ͽ���Ƥ���������";
//ȯ�Բ�ǽ���������
if (_IsNull($companyInfo['tbl_company']['cmp_stock_total_num'])) $errorList[] = "��ȯ�Բ�ǽ����������٤���Ͽ���Ƥ���������";
//�������
if (_IsNull($companyInfo['tbl_company']['cmp_board_formation_id'])) $errorList[] = "����������٤���Ͽ���Ƥ���������";
//������Ϳ�
if (_IsNull($companyInfo['tbl_company']['cmp_director_num'])) {
	$errorList[] = "�ؼ�����Ϳ��٤���Ͽ���Ƥ���������";
} else {
	if ($companyInfo['tbl_company']['cmp_director_num'] < 1) $errorList[] = "�ؼ�����Ϳ��٤���Ͽ���Ƥ���������(1�Ͱʾ�)";
}
//�����򡦴ƺ����Ǥ��
if (_IsNull($companyInfo['tbl_company']['cmp_term_year'])) $errorList[] = "�ؼ������Ǥ���٤���Ͽ���Ƥ���������";
//�ƺ����Ǥ��
if (_IsNull($companyInfo['tbl_company']['cmp_inspector_term_year'])) $errorList[] = "�شƺ����Ǥ���٤���Ͽ���Ƥ���������";
//����ǯ��
if (_IsNull($companyInfo['tbl_company']['cmp_business_start_month'])) $errorList[] = "�ػ���ǯ�١٤���Ͽ���Ƥ���������";
//1����ñ��
if (_IsNull($companyInfo['tbl_company']['cmp_stock_price'])) $errorList[] = "��1����ñ���٤���Ͽ���Ƥ���������";
//���ܶ�
if (_IsNull($companyInfo['tbl_company']['cmp_capital'])) $errorList[] = "�ػ��ܶ�٤���Ͽ���Ƥ���������";
//��Ωǯ����
$errFlag = false;
if (_IsNull($companyInfo['tbl_company']['cmp_found_year'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_found_month'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_found_day'])) $errFlag = true;
if ($errFlag) $errorList[] = "����Ωǯ�����٤���Ͽ���Ƥ���������";
//���
$errFlag = false;
foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
	if (_IsNull($boardInfo['cmp_bod_family_name']) || _IsNull($boardInfo['cmp_bod_first_name'])) {
		$errFlag = true;
		break;
	}
}
if ($errFlag) $errorList[] = "������٤Ρؤ�̾���٤���Ͽ���Ƥ���������";
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



if (count($errorList) > 0) {
	//���顼ͭ�ξ��
	_Log("[/user/company/pdf/create/teikan.php] end. ERR!");


	$buf = "��PDF��������뤿��ξ���­��ޤ��󡣡س��������Ω������Ͽ�ٲ��̤ǡ���������Ϥ��Ƥ���������";
	array_unshift($errorList, $buf);

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//���顼���̤�ɽ�����롣
	header("Location: ../error.php");
	exit;
}


//�괾����������Ͽ���롣
$updInfo = array();
$updInfo['tbl_company']['cmp_company_id'] = $companyId;										//���ID
$updInfo['tbl_company']['cmp_article_create_year'] = $pdfCreateYear;						//�괾������(ǯ)
$updInfo['tbl_company']['cmp_article_create_month'] = $pdfCreateMonth;						//�괾������(��)
$updInfo['tbl_company']['cmp_article_create_day'] = $pdfCreateDay;							//�괾������(��)
$updInfo['tbl_company']['cmp_del_flag'] = $companyInfo['tbl_company']['cmp_del_flag'];		//����ե饰
$res = _CreateCompanyInfo($updInfo);
if ($res === false) {
	$errorList[] = "���괾�������٤ι����˼��Ԥ��ޤ��������١�������¹Ԥ��Ƥ���������";
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
$title = "�ꡡ��";


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


//ɽ����
//$pdf->AddPage();
//
//
////���̾
//$y = $pdf->GetY();
//$y += 50;
//$pdf->SetY($y);
//$pdf->SetFontSize(18);
//$buf = $companyInfo['tbl_company']['cmp_company_name']."�괾";
//$pdf->Cell(0,10,$buf,$border,0,"C");
//$pdf->Ln(20);
//
//
////������
//$y = $pdf->GetY();
//$y += 150;
//$pdf->SetY($y);
//$pdf->SetFontSize(14);
//
//$buf = "ʿ��";
//$pdf->Cell(70,10,$buf,$border,0,"R");
//$pdfCreateYearJp = _ConvertAD2Jp($pdfCreateYear, false);
//$buf = $pdfCreateYearJp."ǯ";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = $pdfCreateMonth."��";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = $pdfCreateDay."��";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = null;
//$pdf->Cell(10,10,$buf,$border,0,"R");
//$buf = "����";
//$pdf->Cell(0,10,$buf,$border,0,"L");
//
//$pdf->Ln();
//
////���ڿ�ǧ��
//$buf = "ʿ��";
//$pdf->Cell(70,10,$buf,$border,0,"R");
//$buf = "ǯ";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = "��";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = "��";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = null;
//$pdf->Cell(10,10,$buf,$border,0,"R");
//$buf = "���ڿ�ǧ��";
//$pdf->Cell(0,10,$buf,$border,0,"L");
//
//$pdf->Ln();
//
////�����Ω
//$buf = "ʿ��";
//$pdf->Cell(70,10,$buf,$border,0,"R");
//$buf = "ǯ";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = "��";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = "��";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = null;
//$pdf->Cell(10,10,$buf,$border,0,"R");
//$buf = "�����Ω";
//$pdf->Cell(0,10,$buf,$border,0,"L");
//
//$pdf->Ln();

$pdf->AddPage();

//���̾
$pdf->SetFontSize(18);
$buf = $companyInfo['tbl_company']['cmp_company_name'];
$pdf->Cell(0,10,$buf,$border,0,"C");
$pdf->Ln(15);

//�����ȥ�
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


//�裱�ϡ���§
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裱�ϡ���§",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);

$no = 0;


//�ʾ������
$pdf->Cell(0,6,"�ʾ������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "����Ҥϡ�";
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
if (_IsNull($companyInfo['tbl_company']['cmp_company_name_en'])) {
	$buf .= "�ȾΤ��롣";
} else {
	$buf .= "�ȾΤ�����ʸ�Ǥ�";
	$buf .= $companyInfo['tbl_company']['cmp_company_name_en'];
	$buf .= "��ɽ�����롣";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//���ܡ�Ū��
$pdf->Cell(0,6,"���ܡ�Ū��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "����Ҥϡ����λ��Ȥ�Ĥळ�Ȥ���Ū�Ȥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$i = 0;
foreach ($companyInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (_IsNull($purposeInfo['cmp_pps_purpose'])) continue;

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = (++$i)."��";
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(12,6,$buf,$border,0,"L");

	$buf = $purposeInfo['cmp_pps_purpose'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");
//	$pdf->Ln();
}

$x = $pdf->GetX();
$x += 20;
$pdf->SetX($x);

$buf = (++$i)."��";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"L");

$buf = null;
if (count($companyInfo['tbl_company_purpose']['purpose_info']) == 1) {
	$buf = "�嵭�����Ӥ�����ڤζ�̳";
} else {
	$buf = "�嵭�ƹ�����Ӥ�����ڤζ�̳";
//	$buf = "���ƹ�����Ӵ�Ϣ������ڤλ���";
}
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//����Ź�ν���ϡ�
$pdf->Cell(0,6,"����Ź�ν���ϡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "����Ҥϡ���Ź��";
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
$buf .= "���֤���";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʸ������ˡ��
$pdf->Cell(0,6,"�ʸ������ˡ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "����Ҥθ���ϡ�����˷Ǻܤ�����ˡ�ˤ��Ԥ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//�裲�ϡ�������
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裲�ϡ�������",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


//��ȯ�Բ�ǽ���������
$pdf->Cell(0,6,"��ȯ�Բ�ǽ���������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
//$buf .= "����Ҥ�ȯ�Բ�ǽ���������";
$buf .= "����Ҥ�ȯ�Բ�ǽ��������ϡ�";
$buf .= $companyInfo['tbl_company']['cmp_stock_total_num'];
$buf .= "���Ȥ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʳ�������ȯ�ԡ�
$pdf->Cell(0,6,"�ʳ�������ȯ�ԡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "����Ҥγ����ˤĤ��Ƥϡ�������ȯ�Ԥ��ʤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʳ����ξ������¡�
$pdf->Cell(0,6,"�ʳ����ξ������¡�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
	case MST_BOARD_FORMATION_ID_1_10:
		//�����������֤��ʤ������1��10�ͤ���Ω
		$buf .= "����Ҥγ�������Ϥˤ���������ˤϡ��������ξ�ǧ������ʤ���Фʤ�ʤ���";
//		$buf .= "����Ҥγ�������Ϥ���ˤϡ��������ξ�ǧ������ʤ���Фʤ�ʤ���";
//		$buf .= "����Ҥγ�������Ϥˤ���������ˤϡ���ɽ������ξ�ǧ������ʤ���Фʤ�ʤ���";
//		$buf .= "����Ҥγ�������Ϥˤ���������ˤϡ���ɽ�������Ĺ�ξ�ǧ������ʤ���Фʤ�ʤ���";
		break;
	case MST_BOARD_FORMATION_ID_3_1:
		//�����������֤��롡���3�ͤȴƺ���1�ͤ���Ω
		$buf .= "����Ҥγ�������Ϥˤ���������ˤϡ��������ξ�ǧ������ʤ���Фʤ�ʤ���";
		break;
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//����³�������Ф�����������������
$pdf->Cell(0,6,"����³�������Ф�����������������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "����Ҥϡ���³����¾�ΰ��̾��Ѥˤ������Ҥγ�������������Ԥ��Ф�����������������Ҥ�����Ϥ����Ȥ����᤹�뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʳ���̾���ܻ���ε������������
$pdf->Cell(0,6,"�ʳ���̾���ܻ���ε������������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "���������Ԥ�����̾���ܻ�������̾��˵������ϵ�Ͽ���뤳�Ȥ����᤹��ˤϡ�����ҽ���ν񼰤ˤ�������ˡ����μ������������γ���Ȥ���";
//$buf .= "����̾��˵������ϵ�Ͽ���줿�����Ϥ�����³�ͤ���¾�ΰ��̾��ѿ͵ڤӳ��������Ԥ���̾���ϵ�̾��������Ʊ�������ᤷ�ʤ���Фʤ�ʤ���";
//$buf .= "����̾��˵������ϵ�Ͽ���줿�����Ϥ�����³�ͤ���¾�ΰ��̾��ѿ͵ڤӳ��������Ԥ���̾���ϵ�̾��������Ʊ���Ƥ��ʤ���Фʤ�ʤ���";
$buf .= "����̾��˵������ϵ�Ͽ���줿�����Ϥ�����³�ͤ���¾�ΰ��̾��ѿ͵ڤӳ��������Ԥ���̾���ϵ�̾����������Ʊ���Ƥ��ʤ���Фʤ�ʤ���";
$buf .= "�����������ˡ�ܹԵ�§�����򣱹�ƹ��������ˤϡ����������Ԥ�ñ�Ȥ����᤹�뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʼ�������Ͽ�ڤӿ����⻺��ɽ����
$pdf->Cell(0,6,"�ʼ�������Ͽ�ڤӿ����⻺��ɽ����",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
//$buf .= "����Ҥγ����ˤĤ���������Ͽ���Ͽ����⻺��ɽ�������᤹��ˤϡ�����ҽ���ν񼰤ˤ�������������Ԥ���̾���ϵ�̾��������Ʊ�������ᤷ�ʤ���Фʤ�ʤ���������Ͽ����ɽ�������äˤĤ��Ƥ�Ʊ�ͤȤ��롣";
//$buf .= "����Ҥγ����ˤĤ���������Ͽ���Ͽ����⻺��ɽ�������᤹��ˤϡ�����ҽ���ν񼰤ˤ�������������Ԥ���̾���ϵ�̾�����������ᤷ�ʤ���Фʤ�ʤ���������Ͽ����ɽ�������äˤĤ��Ƥ�Ʊ�ͤȤ��롣";
$buf .= "����Ҥγ����ˤĤ���������Ͽ���Ͽ����⻺��ɽ�������᤹��ˤϡ�����ҽ���ν񼰤ˤ�������������Ԥ���̾���ϵ�̾�������Ƥ��ʤ���Фʤ�ʤ���������Ͽ����ɽ�������äˤĤ��Ƥ�Ʊ�ͤȤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʼ������
$pdf->Cell(0,6,"�ʼ������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "��������������򤹤���ˤϡ�����ҽ���μ�������ʧ��ʤ���Фʤ�ʤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʴ������
$pdf->Cell(0,6,"�ʴ������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥϡ������ǯ�������κǽ��γ���̾��˵������ϵ�Ͽ���줿�ķ踢��ͭ���������äơ����λ���ǯ�٤˴ؤ�������������ˤ����Ƹ�����ԻȤ��뤳�Ȥ��Ǥ������Ȥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "����Τۤ�����������Ͽ���������ԤȤ��Ƹ�����ԻȤ��뤳�Ȥ��Ǥ���Ԥ���ꤹ�뤿��ɬ�פ�����Ȥ��ϡ����餫������𤷤Ƥ��Τ���δ���������뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//�裳�ϡ��������
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裳�ϡ��������",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
	case MST_BOARD_FORMATION_ID_1_10:
		//�����������֤��ʤ������1��10�ͤ���Ω

		//�ʾ����ڤӾ������ԡ�
		$pdf->Cell(0,6,"�ʾ����ڤӾ������ԡ�",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "����Ҥ�����������ϡ������ǯ���������������飳�������˾��������׻��������ϡ�ɬ�פ˱����ƾ������롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

//		$buf = "�������ϡ�ˡ������ʤ���᤬�����������ۤ�����ɽ�������Ĺ������򾷽����롣��ɽ�������Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ�ͽ����᤿��̤ˤ��¾�μ����򤬤���򾷽����롣";
//		$buf = "�������ϡ�ˡ������ʤ���᤬��������������ɽ�������Ĺ������򾷽����롣��ɽ�������Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ�ͽ����᤿��̤ˤ��¾�μ����򤬤���򾷽����롣";
//		$buf = "�������ϡ�ˡ������ʤ���᤬������������������β�Ⱦ���η���ˤ����ɽ�������Ĺ������򾷽����롣��ɽ�������Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ�ͽ����᤿��̤ˤ��¾�μ����򤬤���򾷽����롣";
//		$buf = "�������ϡ�ˡ������ʤ���᤬������������������β�Ⱦ���η���ˤ����ɽ�������Ĺ������򾷽����롣��ɽ�������Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ����餫������᤿��̤ˤ��¾�μ����򤬤���򾷽����롣";
		$buf = "�������ϡ�ˡ������ʤ���᤬������������������β�Ⱦ���η���ˤ����ɽ�������Ĺ������򾷽����롣��ɽ�������Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ����餫������᤿����ˤ��¾�μ����򤬤���򾷽����롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

//		$buf = "�������򾷽�����ˤϡ�������ꣳ�����ޤǤˡ��ķ踢��ͭ����Ƴ�����Ф��ƾ������Τ�ȯ�����ΤȤ��롣���������ķ踢��ͭ���뤹�٤Ƥγ����Ʊ�դ�����Ȥ��Ͼ�����³����Ф��������򳫺Ť��뤳�Ȥ��Ǥ��롣";
//		$buf = "�������򾷽�����ˤϡ�������ꣳ�����ޤǤˡ��ķ踢��ͭ����Ƴ�����Ф��ƾ������Τ�ȯ�����ΤȤ��롣���������ķ踢��ͭ���뤹�٤Ƥγ����Ʊ�դ�����Ȥ��Ͼ�����³����Ф��������򳫺Ť��뤳�Ȥ��Ǥ��롣��������ˡ������ʤ���᤬������ϡ����θ¤�Ǥʤ���";
//		$buf = "�������򾷽�����ˤϡ�������ꣳ�����ޤǤˡ��ķ踢��ԻȤ��뤳�Ȥ��Ǥ���Ƴ�����Ф��ƾ������Τ�ȯ�����ΤȤ��롣���������ķ踢��ԻȤ��뤳�Ȥ��Ǥ��뤹�٤Ƥγ����Ʊ�դ�����Ȥ��Ͼ�����³����Ф��������򳫺Ť��뤳�Ȥ��Ǥ��롣��������ˡ������ʤ���᤬������ϡ����θ¤�Ǥʤ���";
		$buf = "�������򾷽�����ˤϡ�������ꣳ�����ޤǤˡ��ķ踢��ԻȤ��뤳�Ȥ��Ǥ���Ƴ�����Ф��ƾ������Τ�ȯ�����ΤȤ��롣���������ķ踢��ԻȤ��뤳�Ȥ��Ǥ��뤹�٤Ƥγ����Ʊ�դ�����Ȥ��Ͼ�����³��Ф��������򳫺Ť��뤳�Ȥ��Ǥ��롣��������ˡ������ʤ���᤬������ϡ����θ¤�Ǥʤ���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

//		$buf = "����ξ������Τϡ����̤Ǥ��뤳�Ȥ��פ��ʤ���";
		$buf = "����ξ������Τϡ�ˡ������ʤ���᤬���������������̤Ǥ��뤳�Ȥ��פ��ʤ���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʵ�Ĺ��
		$pdf->Cell(0,6,"�ʵ�Ĺ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "�������ε�Ĺ�ϡ���ɽ�������Ĺ������������롣��ɽ�������Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ�¾�μ����򤬵�Ĺ�ˤʤꡢ�����������˻��Τ�����Ȥ��ϡ����ˤ����ƽ��ʳ���Τ��������Ĺ�����Ф��롣";
//		$buf = "�������ε�Ĺ�ϡ���ɽ�������Ĺ������������롣��ɽ�������Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ�¾�μ����򤬵�Ĺ�ˤʤꡢ�����������˻��Τ�����Ȥ��ϡ��������ˤ����ƽ��ʳ���Τ��������Ĺ�����Ф��롣";
//		$buf = "�������ε�Ĺ�ϡ���ɽ�������Ĺ������������롣��ɽ�������Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ�ͽ����᤿��̤ˤ��¾�μ����򤬵�Ĺ�ˤʤꡢ�����������˻��Τ�����Ȥ��ϡ��������ˤ����ƽ��ʳ���Τ��������Ĺ�����Ф��롣";
		$buf = "�������ε�Ĺ�ϡ���ɽ�������Ĺ������������롣��ɽ�������Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ����餫������᤿����ˤ��¾�μ����򤬵�Ĺ�ˤʤꡢ�����������˻��Τ�����Ȥ��ϡ��������ˤ����ƽ��ʳ���Τ��������Ĺ�����Ф��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʷ�Ĥ���ˡ��
		$pdf->Cell(0,6,"�ʷ�Ĥ���ˡ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "�����������̷�Ĥϡ�ˡ�������괾�����ʤ���᤬���������������ʤ����ķ踢��ԻȤ��뤳�Ȥ��Ǥ������εķ踢�β�Ⱦ�����äƹԤ���";
		$buf = "�������η�Ĥϡ�ˡ�������괾�����ʤ���᤬���������������ʤ����ķ踢��ԻȤ��뤳�Ȥ��Ǥ������εķ踢�β�Ⱦ�����äƹԤ���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�����Ļ�Ͽ��
		$pdf->Cell(0,6,"�����Ļ�Ͽ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "�������ˤ�����Ļ��ηв�����εڤӤ��η���¤Ӥˤ���¾ˡ����������ϡ��Ļ�Ͽ�˵������ϵ�Ͽ������Ĺ�ڤӽ��ʤ��������򤬤���˽�̾�㤷���ϵ�̾���������Żҽ�̾�򤷡�����ǯ����Ź�������֤���";
		$buf = "�������ˤ�����Ļ��ηв�����εڤӤ��η���¤Ӥˤ���¾ˡ����������ϡ��Ļ�Ͽ�˵������ϵ�Ͽ������Ĺ�ڤӽ��ʤ��������򤬤���˽�̾�㤷���ϵ�̾���������Żҽ�̾�򤷡��������������飱��ǯ����Ź�������֤���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		break;
	case MST_BOARD_FORMATION_ID_3_1:
		//�����������֤��롡���3�ͤȴƺ���1�ͤ���Ω

		//�ʾ����ڤӾ������ԡ�
		$pdf->Cell(0,6,"�ʾ����ڤӾ������ԡ�",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "����Ҥ�����������ϡ������ǯ���������������飳�������˾������׻��������ϡ����ɬ�פ˱����ƾ������롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

//		$buf = "�������ϡ�ˡ������ʤ���᤬�����������ۤ����������η�Ĥ˴�Ť�����Ĺ������򾷽����롣��Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ�ͽ����᤿����ˤ��¾�μ����򤬤���򾷽����롣";
		$buf = "�������ϡ�ˡ������ʤ���᤬�����������ۤ����������η�Ĥ˴�Ť�����Ĺ������򾷽����롣��Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ����餫������᤿����ˤ��¾�μ����򤬤���򾷽����롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

//		$buf = "�������򾷽�����ˤϡ�������꣱���֤ޤǤˡ��ķ踢��ͭ����Ƴ�����Ф��ƾ������Τ�ȯ�����ΤȤ��롣��������������ɼ�����Ż���ɼ��ǧ�����ϡ������Σ��������ޤǤ�ȯ�����ΤȤ��롣";
//		$buf = "�������򾷽�����ˤϡ�������꣱���֤ޤǤˡ��ķ踢��ԻȤ��뤳�Ȥ��Ǥ���Ƴ�����Ф��ƾ������Τ�ȯ�����ΤȤ��롣��������������ɼ�����Ż���ɼ��ǧ�����ϡ������Σ��������ޤǤ�ȯ�����ΤȤ��롣";
		$buf = "�������򾷽�����ˤϡ�������꣱�������ޤǤˡ��ķ踢��ԻȤ��뤳�Ȥ��Ǥ���Ƴ�����Ф��ƾ������Τ�ȯ�����ΤȤ��롣��������������ɼ�����Ż���ɼ��ǧ�����ϡ������Σ��������ޤǤ�ȯ�����ΤȤ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʾ�����³���ξ�ά��
		$pdf->Cell(0,6,"�ʾ�����³���ξ�ά��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "�������ϡ��������ˤ����Ƶķ踢��ԻȤ��뤳�Ȥ��Ǥ��뤹�٤Ƥγ����Ʊ�դ�����Ȥ��ϡ�������³��Ф��˳��Ť��뤳�Ȥ��Ǥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʵ�Ĺ��
		$pdf->Cell(0,6,"�ʵ�Ĺ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "�������ε�Ĺ�ϡ���Ĺ������������롣��Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ����餫������������������ˤ�ꡢ¾�μ����򤬵�Ĺ�ˤʤꡢ�����������˻��Τ�����Ȥ��ϡ����ˤ����ƽ��ʳ���Τ��������Ĺ�����Ф��롣";
		$buf = "�������ε�Ĺ�ϡ���Ĺ������������롣��Ĺ�˻��μ㤷���ϻپ㤬����Ȥ��ϡ����餫������������������ˤ�ꡢ¾�μ����򤬵�Ĺ�ˤʤꡢ�����������˻��Τ�����Ȥ��ϡ��������ˤ����ƽ��ʳ���Τ��������Ĺ�����Ф��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʷ�Ĥ���ˡ��
		$pdf->Cell(0,6,"�ʷ�Ĥ���ˡ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "�����������̷�Ĥϡ�ˡ�������괾�����ʤ���᤬���������������ʤ����ķ踢��ԻȤ��뤳�Ȥ��Ǥ������εķ踢�β�Ⱦ�����äƷ褹�롣";
		$buf = "�������η�Ĥϡ�ˡ�������괾�����ʤ���᤬���������������ʤ����ķ踢��ԻȤ��뤳�Ȥ��Ǥ������εķ踢�β�Ⱦ�����äƷ褹�롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʵķ踢�������Իȡ�
		$pdf->Cell(0,6,"�ʵķ踢�������Իȡ�",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "����ϡ������ͤˤ�äƵķ踢��ԻȤ��뤳�Ȥ��Ǥ��롣���ξ��ˤ���񤴤Ȥ���������ڤ�����̤���Ф��ʤ���Фʤ�ʤ���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "����������ͤϡ�����Ҥεķ踢��ͭ�������˸¤��ΤȤ������ġ����Ͱʾ�������ͤ���Ǥ���뤳�ȤϤǤ��ʤ���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�����Ļ�Ͽ��
		$pdf->Cell(0,6,"�����Ļ�Ͽ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "�������ˤ�����Ļ��ηв�����εڤӤ��η���¤Ӥˤ���¾ˡ����������ϡ��Ļ�Ͽ�˵������ϵ�Ͽ������Ĺ�ڤӽ��ʤ��������򤬤���˽�̾�㤷���ϵ�̾���������Żҽ�̾�򤷡�����ǯ����Ź�������֤���";
		$buf = "�������ˤ�����Ļ��ηв�����εڤӤ��η���¤Ӥˤ���¾ˡ����������ϡ��Ļ�Ͽ�˵������ϵ�Ͽ������Ĺ�ڤӽ��ʤ��������򤬤���˽�̾�㤷���ϵ�̾���������Żҽ�̾�򤷡��������������飱��ǯ����Ź�������֤���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		break;
}


$pdf->Ln(10);


switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
	case MST_BOARD_FORMATION_ID_1_10:
		//�����������֤��ʤ������1���10�ͤ���Ω

		//�裴�ϡ�������
		$pdf->SetFontSize(14);
//		$pdf->Cell(0,10,"�裴�ϡ�������",$border,0,"C");
		$pdf->Cell(0,10,"�裴�ϡ�������ڤ���ɽ������",$border,0,"C");
		$pdf->Ln(20);

		$pdf->SetFontSize(12);


		//�ʼ�����ΰ�����
		$pdf->Cell(0,6,"�ʼ�����ΰ�����",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = null;
		$buf .= "����Ҥϼ�����";
//		$buf .= $companyInfo['tbl_company']['cmp_director_num'];
		$buf .= "1";
		$buf .= "̾�ʾ���֤���";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʼ��������Ǥ��
		$pdf->Cell(0,6,"�ʼ��������Ǥ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "����Ҥμ�����ϡ��������ˤ����ơ��ķ踢��ԻȤ��뤳�Ȥ��Ǥ������εķ踢�Σ�ʬ�Σ��ʾ��ͭ������礬���ʤ������εķ踢�β�Ⱦ���η�Ĥˤ�ä���Ǥ���롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "�������Ǥ�ˤĤ��Ƥϡ�������ɼ����ˡ�ˤ��ʤ���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʼ������Ǥ����
		$pdf->Cell(0,6,"�ʼ������Ǥ����",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = null;
		$buf .= "�������Ǥ���ϡ���Ǥ��";
		$buf .= $companyInfo['tbl_company']['cmp_term_year'];
//		$buf .= "ǯ����˽�λ����ǽ��λ���ǯ�٤˴ؤ�������������ν�����ޤǤȤ��롣";
		$buf .= "ǯ����˽�λ�������ǯ�٤Τ����ǽ��Τ�Τ˴ؤ�������������ν�����ޤǤȤ��롣";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "������������ˤ����Ǥ�����������Ǥ���ϡ���Ǥ������¾�κ�Ǥ�������Ǥ���λ�¸���֤�Ʊ��Ȥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		//��ɽ������ʣ����Ͽ��ǽ�ˤʤä��Τǡ�����ɽ������ڤӼ�Ĺ�ˤ�ʸ�Ϥ��ѹ��ˤʤä���
		$countRepDirector = 0;
		foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
			if ($boardInfo['cmp_bod_post_id'] != MST_POST_ID_REP_DIRECTOR) continue;
			$countRepDirector++;
		}

		//����ɽ������ڤӼ�Ĺ��
		$pdf->Cell(0,6,"����ɽ������ڤӼ�Ĺ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");
		
		$buf = null;
		if ($countRepDirector >= 2) {
			$buf = "����Ҥ˼������ʣ��̾�֤����ˤϡ�������θ����ˤ����ɽ���������ᡢ��ɽ��������äƼ�Ĺ�Ȥ��롣";
		} else {
			$buf = "����Ҥ˼������ʣ��̾�֤����ˤϡ�������θ����ˤ����ɽ������̾����ᡢ��ɽ��������äƼ�Ĺ�Ȥ��롣";
		}
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "����Ҥ��֤������򤬣�̾�ξ��ˤϡ����μ��������ɽ�������Ĺ�Ȥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "��Ĺ������Ҥ���ɽ���롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʼ�������Ф���������
		$pdf->Cell(0,6,"�ʼ�������Ф���������",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "��������Ф����󽷵ڤ��࿦��ϫ�����ϡ��������η�Ĥˤ�����롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		break;
	case MST_BOARD_FORMATION_ID_3_1:
		//�����������֤��롡���3�ͤȴƺ���1�ͤ���Ω

		//�裴�ϡ������򡢼��������ɽ������ڤӴƺ���
		$pdf->SetFontSize(14);
		$pdf->Cell(0,10,"�裴�ϡ������򡢼��������ɽ������ڤӴƺ���",$border,0,"C");
		$pdf->Ln(20);

		$pdf->SetFontSize(12);


		//�ʼ���������ֲ�ҡ�
		$pdf->Cell(0,6,"�ʼ���������ֲ�ҡ�",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "����Ҥϼ��������֤���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʼ�����ΰ�����
		$pdf->Cell(0,6,"�ʼ�����ΰ�����",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = null;
		$buf .= "����Ҥϼ�����";
		$buf .= $companyInfo['tbl_company']['cmp_director_num'];
		$buf .= "̾�ʾ���֤���";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʴƺ������ֲ�ҡ�
		$pdf->Cell(0,6,"�ʴƺ������ֲ�ҡ�",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "����Ҥϴƺ�����֤������ΰ����ϣ�̾����Ȥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʼ�����ڤӴƺ������Ǥ��
		$pdf->Cell(0,6,"�ʼ�����ڤӴƺ������Ǥ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "����Ҥμ�����ڤӴƺ���ϳ������ˤ����������εķ踢�Σ�ʬ�Σ��ʾ��ͭ������礬���ʤ������εķ踢�β�Ⱦ���η�Ĥˤ�ä���Ǥ���롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "���������Ǥ�ˤĤ��Ƥϡ�������ɼ�ˤ��ʤ���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʼ�����β�Ǥ��
		$pdf->Cell(0,6,"�ʼ�����β�Ǥ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "������β�Ǥ��Ĥϡ��ķ踢��ԻȤ��뤳�Ȥ��Ǥ������εķ踢�β�Ⱦ����ͭ������礬���ʤ������εķ踢�Σ�ʬ�Σ��ʾ���äƹԤ���";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʼ�����ڤӴƺ����Ǥ����
		$pdf->Cell(0,6,"�ʼ�����ڤӴƺ����Ǥ����",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = null;
		$buf .= "�������Ǥ���ϡ���Ǥ��";
		$buf .= $companyInfo['tbl_company']['cmp_term_year'];
//		$buf .= "ǯ����˽�λ����ǽ��λ���ǯ�٤˴ؤ�������������ν�����ޤǤȤ��롣";
		$buf .= "ǯ����˽�λ�������ǯ�٤Τ����ǽ��Τ�Τ˴ؤ�������������ν�����ޤǤȤ��롣";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = null;
		$buf .= "�ƺ����Ǥ���ϡ���Ǥ��";
		$buf .= $companyInfo['tbl_company']['cmp_inspector_term_year'];
//		$buf .= "ǯ����˽�λ����ǽ��λ���ǯ�٤˴ؤ�������������ν�����ޤǤȤ��롣";
		$buf .= "ǯ����˽�λ�������ǯ�٤Τ����ǽ��Τ�Τ˴ؤ�������������ν�����ޤǤȤ��롣";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "Ǥ����λ������Ǥ��������������Ȥ��ơ����������ˤ����Ǥ���줿�������Ǥ���ϡ���Ǥ������¾�κ�Ǥ�������Ǥ���λ�¸���֤�Ʊ��Ȥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "Ǥ����λ������Ǥ�����ƺ�������Ȥ�����Ǥ���줿�ƺ����Ǥ������Ǥ�Ԥ�Ǥ���λ�¸���֤�Ʊ��Ȥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//�ʼ������ξ����ڤӵ�Ĺ��
		$pdf->Cell(0,6,"�ʼ������ξ����ڤӵ�Ĺ��",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "�������ϡ�ˡ������ʤ���᤬�������������������Ĺ������������Ĺ�Ȥʤ롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "�������Ĺ�˷�����ϻ��Τ�����Ȥ��ϡ��������ˤ�����ͽ����᤿����ǡ�¾�μ����򤬤�������롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "�������ξ������Τϡ������Σ������ޤǤ˳Ƽ�����ڤӳƴƺ�����Ф���ȯ���롣������������ڤӴƺ����������Ʊ�դ�����Ȥ��ϡ������μ�³��Фʤ��Ǽ������򳫤����Ȥ��Ǥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//����ɽ������ڤ����ռ������
		$pdf->Cell(0,6,"����ɽ������ڤ����ռ������",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "��ɽ������ϡ��������η�Ĥˤ�ä����ꤹ�롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "��ɽ������ϲ�Ҥ���ɽ������Ҥζ�̳�򼹹Ԥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"��������",$border,0,"L");

		$buf = "�������η�Ĥ��äƼ�������椫�顢��Ĺ��̾�����ꤷ��ɬ�פ˱����ơ�����������Ĺ����̳�����򡢾�̳������Ƽ㴳̾�����ꤹ�뤳�Ȥ��Ǥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//���󡡽���
		$pdf->Cell(0,6,"���󡡽���",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "������ڤӴƺ�����󽷵ڤ��࿦��ϫ�����ϡ����줾��������η�Ĥ��ä����롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		break;
}


$pdf->Ln(10);


//�裵�ϡ��ס���
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裵�ϡ��ס���",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


//�ʻ���ǯ�١�
$pdf->Cell(0,6,"�ʻ���ǯ�١�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//12�����η��������������롣
$businessEndMonth = 0;
if ($companyInfo['tbl_company']['cmp_business_start_month'] == 1) {
	$businessEndMonth = 12;
} else {
	$businessEndMonth = $companyInfo['tbl_company']['cmp_business_start_month'] - 1;
}
$lastDayList = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
$businessEndDay = $lastDayList[$businessEndMonth - 1];

//��$businessEndMonth��$businessEndDay�ϡ��ʺǽ�λ���ǯ�١ˤǤ���Ѥ��롣

$buf = null;
$buf .= "����Ҥλ���ǯ�٤ϡ�";
$buf .= "��ǯ";
$buf .= $companyInfo['tbl_company']['cmp_business_start_month'];
$buf .= "��1������";
$buf .= ($businessEndMonth == 12?"Ʊǯ":"��ǯ");
$buf .= $businessEndMonth;
$buf .= "��";
//$buf .= $businessEndDay;
//$buf .= "��";
$buf .= "����";
$buf .= "�ޤǤ�ǯ1���Ȥ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʾ�;���������
$pdf->Cell(0,6,"�ʾ�;���������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//$buf = "��;��������ϡ������ǯ���������ߤκǽ��γ���̾��˵������ϵ�Ͽ���줿����ڤ���Ͽ���������Ԥ��Ф��ƹԤ���";
$buf = "��;��������ϡ������ǯ���������ߤκǽ��γ���̾��˵������ϵ�Ͽ���줿����������Ͽ���������Ԥ��Ф��ƹԤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Cell(20,6,"��������",$border,0,"L");

//$buf = "��;�������������ʧ�����󶡤򤷤������飳ǯ��вᤷ�Ƥ���Τ���ʤ��Ȥ��ϡ�����Ҥϡ����λ�ʧ���ε�̳���Ȥ���ΤȤ��롣";
$buf = "��;�������������ʧ���󶡤򤷤������飳ǯ��вᤷ�Ƥ���Τ���ʤ��Ȥ��ϡ�����Ҥϡ����λ�ʧ�ε�̳���Ȥ���ΤȤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//�裶�ϡ���§
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裶�ϡ���§",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
	case MST_BOARD_FORMATION_ID_1_10:
		//�����������֤��ʤ������1��10�ͤ���Ω

		//����Ω�κݤ�ȯ�Ԥ�������ο���
		$pdf->Cell(0,6,"����Ω�κݤ�ȯ�Ԥ�������ο���",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		//��Ω��ȯ�Գ����ο� = ���ܶ�(����) / 1����ñ��(��)
		$stockNum = ($companyInfo['tbl_company']['cmp_capital'] * 10000) / $companyInfo['tbl_company']['cmp_stock_price'];
		$stockNum = floor($stockNum);//ü�����ڤ�Τ�
		$stockNum = _ConvertNum2Ja($stockNum);

		$buf = null;
		$buf .= "����Ҥ���Ω��ȯ�Գ����ο���";
		$buf .= $stockNum;
		$buf .= "��������ȯ�Բ��ۤ�1���ˤĤ���";
		$buf .= _ConvertNum2Ja($companyInfo['tbl_company']['cmp_stock_price']);
		$buf .= "�ߤȤ��롣";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		break;
	case MST_BOARD_FORMATION_ID_3_1:
		//�����������֤��롡���3�ͤȴƺ���1�ͤ���Ω
		break;
}


//����Ω�˺ݤ��ƽл񤵤��⻺�β��۵ڤ���Ω��λ��ܶ�γۡ�
$pdf->Cell(0,6,"����Ω�˺ݤ��ƽл񤵤��⻺�β��۵ڤ���Ω��λ��ܶ�γۡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//���ܶ��ñ�̤����ߤ���ߤˤ��롣
$capital = $companyInfo['tbl_company']['cmp_capital'] * 10000;
$capital = _ConvertNum2Ja($capital);

$buf = null;
$buf .= "����Ҥ���Ω�˺ݤ��ƽл񤵤��⻺�β��ۤ϶�";
$buf .= $capital;
$buf .= "�ߤȤ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = null;
$buf .= "����Ҥ���Ω��λ��ܶ�γۤϡ���";
$buf .= $capital;
$buf .= "�ߤȤ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʺǽ�λ���ǯ�١�
$pdf->Cell(0,6,"�ʺǽ�λ���ǯ�١�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//�ǽ�λ���ǯ�٤κǽ����ǯ��������롣
//$businessEndYear = null;
//if ($businessEndMonth == 12) {
//	$businessEndYear = $companyInfo['tbl_company']['cmp_found_year'];
//} else {
//	$businessEndYear = $companyInfo['tbl_company']['cmp_found_year'] + 1;
//}
//$businessEndYear = _ConvertAD2Jp($businessEndYear);

//�嵭����ǯ������ǯ�ˤʤäƤ��ޤ���Ʊ��ǯ�ξ���ͭ�ꡣ���̾�Ρ�N�����ɽ����Js��Ʊ�������ˤ��롣
//Js��Ʊ������
$startMonth = $companyInfo['tbl_company']['cmp_business_start_month'];
$foundMonth = $companyInfo['tbl_company']['cmp_found_month'];
$diff = 12 - ($foundMonth - $startMonth);
if ($diff > 12) $diff -= 12;
$bufY = $companyInfo['tbl_company']['cmp_found_year'];
$bufM = $companyInfo['tbl_company']['cmp_found_month'];
//for ($diffIdx = 0; $diffIdx < $diff; $diffIdx++) {
for ($diffIdx = 1; $diffIdx < $diff; $diffIdx++) { //�ǽ�η��N��������롣�Τǡ��롼�פ��ĸ��餹��
	$bufM++;
	if ($bufM > 12) {
		$bufY++;
		$bufM = 1;
	}
}
$businessEndYear = $bufY;
$businessEndYear = _ConvertAD2Jp($businessEndYear);


$buf = null;
$buf .= "����Ҥκǽ�λ���ǯ�٤ϡ��������Ω��������";
$buf .= $businessEndYear;
$buf .= "ǯ";
$buf .= $businessEndMonth;
$buf .= "��";
//$buf .= $businessEndDay;
//$buf .= "��";
$buf .= "����";
$buf .= "�ޤǤȤ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
	case MST_BOARD_FORMATION_ID_1_10:
		//�����������֤��ʤ������1��10�ͤ���Ω

		//����Ω���������
//		$pdf->Cell(0,6,"����Ω���������",$border,0,"L");
//		$pdf->Cell(0,6,"����Ω��������ڤ���ɽ�������",$border,0,"L");
//		$pdf->Cell(0,6,"����Ω�������ڤ���Ω����ɽ�������",$border,0,"L");
		$pdf->Cell(0,6,"����Ω��������ڤ���Ω����ɽ�������",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "����Ҥ���Ω��������ϡ����ΤȤ���Ȥ��롣";
		$buf = "����Ҥ���Ω��������ڤ���Ω����ɽ������ϡ����ΤȤ���Ȥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();
		break;
	case MST_BOARD_FORMATION_ID_3_1:
		//�����������֤��롡���3�ͤȴƺ���1�ͤ���Ω

		//����Ω��������ڤ���Ω���ƺ����
		$pdf->Cell(0,6,"����Ω��������ڤ���Ω���ƺ����",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "����Ҥ���Ω��������ڤ���Ω���ƺ���ϡ����ΤȤ���Ȥ��롣";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();
		break;
}

$repBoardInfo = null;
foreach ($mstPostList as $mpKey => $mstPostInfo) {
	foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
		if ($mstPostInfo['id'] != $boardInfo['cmp_bod_post_id']) continue;

		$x = $pdf->GetX();
		$x += 20;
		$pdf->SetX($x);

		$buf = null;
		$buf .= "��Ω��";
		if ($mstPostInfo['id'] == MST_POST_ID_REP_DIRECTOR) {
			//��ɽ������ξ�硢"������"�Ȥ���ɽ�����롣
			$buf .= $mstPostList[MST_POST_ID_DIRECTOR]['name'];
			$repBoardInfo = $boardInfo;
		} else {
			$buf .= $mstPostInfo['name'];
		}
		$pdf->Cell(40,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $boardInfo['cmp_bod_family_name'];
		$buf .= " ";
		$buf .= $boardInfo['cmp_bod_first_name'];
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();
	}
}
////��ɽ�������Ǹ���ɲä��롣
//$x = $pdf->GetX();
//$x += 20;
//$pdf->SetX($x);
//
//$buf = null;
//$buf .= "��Ω��";
//$buf .= $mstPostList[$repBoardInfo['cmp_bod_post_id']]['name'];
//$pdf->Cell(40,6,$buf,$border,0,"L");
//
//$buf = null;
//$buf .= $repBoardInfo['cmp_bod_family_name'];
//$buf .= " ";
//$buf .= $repBoardInfo['cmp_bod_first_name'];
//$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");
//$pdf->Ln();

//��ɽ�������Ǹ���ɲä��롣
//��ɽ������ʣ����Ͽ��ǽ�ˤʤä��Τǡ�ʣ���ͤ�Ǹ���ɲä��롣
foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
	if ($boardInfo['cmp_bod_post_id'] != MST_POST_ID_REP_DIRECTOR) continue;

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = null;
	$buf .= "��Ω��";
	$buf .= $mstPostList[$boardInfo['cmp_bod_post_id']]['name'];
	$pdf->Cell(40,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $boardInfo['cmp_bod_family_name'];
	$buf .= " ";
	$buf .= $boardInfo['cmp_bod_first_name'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();
}


//��ȯ���ͤλ�̾�ۤ���
$pdf->Cell(0,6,"��ȯ���ͤλ�̾�ۤ���",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//��ʪ�л�Ԥ�̾�������֤��롣
$nameInkind = null;
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
			if (!_IsNull($nameInkind)) $nameInkind .= "��";
//			if (!_IsNull($nameInkind)) $nameInkind .= "�� ";//���Խ����Τ��ᡢ������ɲä�����
			$nameInkind .= "ȯ����";
			$nameInkind .= $promoterInfo['cmp_prm_family_name'];
//			$nameInkind .= " ";//���Խ����Τ��ᡢ�������������
			$nameInkind .= $promoterInfo['cmp_prm_first_name'];
			break;
	}
}

$buf = null;
$buf .= "ȯ���ͤλ�̾������ڤ���Ω�˺ݤ��Ƴ����Ƥ������������¤Ӥ˳����Ȱ�������ʧ����������γۤϡ����ΤȤ���Ǥ��롣";
if (!_IsNull($nameInkind)) {
	$buf .= "�ʤ�";
	$buf .= $nameInkind;
	$buf .= "�ϡ���������븽ʪ�л�ˤ�������Ƥ�������������������롣";
//	$buf .= "�ϡ���������븽ʪ�л�ˤ�������Ƥ����������� ���������롣";//���Խ����Τ��ᡢ������ɲä�����
}
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	$stockNumCash = null;		//����γ���
	$investmentCash = null;		//����ζ��
	$stockNumInkind = null;		//��ʪ�γ���
	$investmentInkind = null;	//��ʪ�ζ��

	//�л�����Ͽ�Ϥ��뤫��
	if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

		//����νл�򽸷פ��롣
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_CASH])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_CASH]['investment_info'] as $iKey => $investmentInfo) {
				if (_IsNull($stockNumCash)) $stockNumCash = 0;
				$stockNumCash += $investmentInfo['cmp_prm_inv_stock_num'];
			}

			if (!_IsNull($stockNumCash)) {
				//��������л��ۤ�׻����롣1����ñ��(��)�߸���γ���
				$investmentCash = $companyInfo['tbl_company']['cmp_stock_price'] * $stockNumCash;

				//���ͤ���ɽ�����Ѵ����롣
				$investmentCash = "��"._ConvertNum2Ja($investmentCash)."��";
				$stockNumCash = _ConvertNum2Ja($stockNumCash)."��";
			}
		}

		//��ʪ�νл�򽸷פ��롣
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
				if (_IsNull($stockNumInkind)) $stockNumInkind = 0;
				$stockNumInkind += $investmentInfo['cmp_prm_inv_stock_num'];
			}

			if (!_IsNull($stockNumInkind)) {
				//��������л��ۤ�׻����롣1����ñ��(��)�߸���γ���
				$investmentInkind = $companyInfo['tbl_company']['cmp_stock_price'] * $stockNumInkind;

				//���ͤ���ɽ�����Ѵ����롣
				$investmentInkind = "��"._ConvertNum2Ja($investmentInkind)."��";
				$stockNumInkind = _ConvertNum2Ja($stockNumInkind)."��";
			}
		}
	}

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = null;
	$buf .= $mstPrefList[$promoterInfo['cmp_prm_pref_id']]['name'];
	$buf .= $promoterInfo['cmp_prm_address1'];
	$buf .= $promoterInfo['cmp_prm_address2'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = null;
	$buf .= "ȯ����";
	$pdf->Cell(20,6,$buf,$border,0,"L");

//��̾��Ĺ����礬����Τǡ�MultiCell���ѹ����롣
//	$buf = null;
//	$buf .= $promoterInfo['cmp_prm_family_name'];
//	$buf .= " ";
//	$buf .= $promoterInfo['cmp_prm_first_name'];
//	$buf = mb_convert_kana($buf, 'N');
//	$pdf->Cell(50,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $promoterInfo['cmp_prm_family_name'];
	$buf .= " ";
	$buf .= $promoterInfo['cmp_prm_first_name'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$x = $pdf->GetX();
	$x += 40;
	$pdf->SetX($x);


	$buf = $stockNumCash;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(35,6,$buf,$border,0,"L");
	$buf = $investmentCash;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(0,6,$buf,$border,0,"L");

	$pdf->Ln();

	if (!_IsNull($stockNumInkind)) {
		$x = $pdf->GetX();
		$x += 20;
		$pdf->SetX($x);

		$buf = null;
		$pdf->Cell(20,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "��ʪ�л�";
		$pdf->Cell(50,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $stockNumInkind;
		$buf .= "�ʳ�����Ƥ����������ο���";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(0,6,$buf,$border,0,"L");

		$pdf->Ln();
	}

	$pdf->Ln();
}


if (!_IsNull($nameInkind)) {
	//�ʸ�ʪ�л��
	$pdf->Cell(0,6,"�ʸ�ʪ�л��",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");

	$buf = "����Ҥ���Ω�˺ݤ��Ƹ�ʪ�л�򤹤�Ԥλ�̾���л����Ū�Ǥ���⻺�����β��۵ڤӤ�����Ф��Ƴ�����Ƥ�����ο��ϡ����ΤȤ���Ǥ��롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();

	$countPromoter = 0;
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

		$countPromoter++;
		
	}
	$countPromoterNoFlag = false;
	if ($countPromoter > 1) {
		$countPromoterNoFlag = true;
	}

	$countPromoter = 0;
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

		$countPromoter++;

		$x = $pdf->GetX();
		$x += 20;
		$pdf->SetX($x);

		$buf = null;
		if ($countPromoterNoFlag) {
			$buf .= "��";
			$buf .= $countPromoter;
			$buf .= "��";
		}
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "��";
		$buf .= "1";
		$buf .= "��";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "�л��";
		$pdf->Cell(20,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "ȯ����";
		$pdf->Cell(20,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $promoterInfo['cmp_prm_family_name'];
		$buf .= " ";
		$buf .= $promoterInfo['cmp_prm_first_name'];
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(0,6,$buf,$border,0,"L");

		$pdf->Ln();

		$x = $pdf->GetX();
		$x += 20;
		$pdf->SetX($x);

		$buf = null;
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "��";
		$buf .= "2";
		$buf .= "��";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "�л�⻺�ڤӤ��β���";
		$pdf->Cell(0,6,$buf,$border,0,"L");

		$pdf->Ln();


		$stockNumInkind = null;		//��ʪ�γ���

		//�л�����Ͽ�Ϥ��뤫��
		if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
			$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

			//��ʪ�νл�򽸷פ��롣
			if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {

				$countInkind = 0;
				foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
					if (_IsNull($stockNumInkind)) $stockNumInkind = 0;

					//�����ι�פ�׻����롣
					$stockNumInkind += $investmentInfo['cmp_prm_inv_stock_num'];

					//��������л��ۤ�׻����롣1����ñ��(��)�߸���γ���
					$investmentInkind = $companyInfo['tbl_company']['cmp_stock_price'] * $investmentInfo['cmp_prm_inv_stock_num'];
					//���ͤ���ɽ�����Ѵ����롣
					$investmentInkind = "��"._ConvertNum2Ja($investmentInkind)."��";

					$countInkind++;

					$x = $pdf->GetX();
					$x += 20;
					$pdf->SetX($x);

					$buf = null;
					$pdf->Cell(15,6,$buf,$border,0,"L");

					$buf = null;
					$pdf->Cell(15,6,$buf,$border,0,"L");

					$buf = null;
					$buf .= "";
					$buf .= $countInkind;
					$buf .= "��";
					$buf = mb_convert_kana($buf, 'N');
					$pdf->Cell(12,6,$buf,$border,0,"L");

					$buf = null;
					$buf .= $investmentInfo['cmp_prm_inv_in_kind'];
					$buf = mb_convert_kana($buf, 'N');
					$pdf->MultiCell(0,6,$buf,$border,"L");

					$x = $pdf->GetX();
					$x += 20;
					$pdf->SetX($x);

					$buf = null;
					$pdf->Cell(15,6,$buf,$border,0,"L");

					$buf = null;
					$pdf->Cell(15,6,$buf,$border,0,"L");

					$buf = null;
					$pdf->Cell(12,6,$buf,$border,0,"L");

					$buf = null;
					$buf .= $investmentInkind;
					$buf = mb_convert_kana($buf, 'N');
					$pdf->Cell(0,6,$buf,$border,0,"L");

					$pdf->Ln();
				}

				if (!_IsNull($stockNumInkind)) {
					//���ͤ���ɽ�����Ѵ����롣
					$stockNumInkind = _ConvertNum2Ja($stockNumInkind)."��";
				}
			}
		}

		$x = $pdf->GetX();
		$x += 20;
		$pdf->SetX($x);

		$buf = null;
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "��";
		$buf .= "3";
		$buf .= "��";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "������Ƥ�����ο�";
		$pdf->Cell(0,6,$buf,$border,0,"L");

		$pdf->Ln();

		$x = $pdf->GetX();
		$x += 20;
		$pdf->SetX($x);

		$buf = null;
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $stockNumInkind;
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(0,6,$buf,$border,0,"L");

		$pdf->Ln();
		$pdf->Ln();
	}
}


//��ˡ��ν���
$pdf->Cell(0,6,"��ˡ��ν���",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "���괾�����Τʤ�����ϡ����٤Ʋ��ˡ����¾�δط�ˡ��˽�����";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//$buf = null;
//$buf .= "�ʾ塢";
//$buf .= $companyInfo['tbl_company']['cmp_company_name'];
//$buf .= "����Ω�Τ��ᡢ�����괾���������ȯ���ͤ����˵�̾�������롣";
////$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");

//$buf = null;
//$buf .= "�ʾ塢";
//$buf .= $companyInfo['tbl_company']['cmp_company_name'];
//$buf .= "��Ω�ΰ٤ˡ�ȯ���ͤ��괾���������ͤǤ���".ADMINISTRATIVE_SCRIVENER_NAME."�ϡ��ż�Ū��Ͽ�Ǥ������괾���������������Żҽ�̾���롣";
////$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");

//ȯ����
$promoterName = null;
$promoterCount = 0;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	$promoterCount++;
	if ($promoterCount == 1) {
		$promoterName .= $promoterInfo['cmp_prm_family_name'];
		$promoterName .= " ";
		$promoterName .= $promoterInfo['cmp_prm_first_name'];
	}
}
if ($promoterCount > 1) {
	$promoterName .= " ";
	$promoterName .= "¾".($promoterCount - 1)."̾";
}

$buf = null;
$buf .= "�ʾ塢";
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
//$buf .= "��Ω�ΰ٤ˡ�ȯ���� ".$promoterName." ��\n�괾���������ͤǤ���".ADMINISTRATIVE_SCRIVENER_NAME."�ϡ��ż�Ū��Ͽ�Ǥ������괾���������\n������Żҽ�̾���롣";
//$buf .= "��Ω�ΰ٤ˡ�ȯ���� ".$promoterName."���괾���������ͤǤ���\n".ADMINISTRATIVE_SCRIVENER_NAME."�ϡ��ż�Ū��Ͽ�Ǥ������괾���������������Żҽ�̾���롣";
//$buf .= "��Ω�ΰ٤ˡ�ȯ���� ".$promoterName."���괾���������� �Ǥ���".ADMINISTRATIVE_SCRIVENER_NAME."�ϡ��ż�Ū��Ͽ�Ǥ������괾���������������Żҽ�̾���롣";
//$buf .= "��Ω�ΰ٤ˡ�ȯ���� ".$promoterName."���괾���������ͤǤ��� ".ADMINISTRATIVE_SCRIVENER_NAME."�ϡ��ż�Ū��Ͽ�Ǥ������괾���������������Żҽ�̾���롣";
$buf .= "��Ω�ΰ٤ˡ�ȯ���� ".$promoterName."���괾���������ͤǤ���".ADMINISTRATIVE_SCRIVENER_NAME."�ϡ��ż�Ū��Ͽ�Ǥ������괾���������������Żҽ�̾���롣";
//$buf = mb_convert_kana($buf, 'N');
$buf = str_replace(" ", "��", $buf);
$pdf->MultiCell(0,6,$buf,$border,"L");



$pdf->Ln(20);

////������
//$buf = "ʿ��";
//$pdf->Cell(12,6,$buf,$border,0,"L");
//$pdfCreateYearJp = _ConvertAD2Jp($pdfCreateYear, false);
//$buf = $pdfCreateYearJp."ǯ";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(14,6,$buf,$border,0,"R");
//$buf = $pdfCreateMonth."��";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(14,6,$buf,$border,0,"R");
//$buf = $pdfCreateDay."��";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(14,6,$buf,$border,0,"R");
//$buf = null;
//$pdf->Cell(0,6,$buf,$border,0,"R");

//������
$pdfCreateYearJp = _ConvertAD2Jp($pdfCreateYear);
$buf = $pdfCreateYearJp."ǯ";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(24,6,$buf,$border,0,"L");
$buf = $pdfCreateMonth."��";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(14,6,$buf,$border,0,"R");
$buf = $pdfCreateDay."��";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(14,6,$buf,$border,0,"R");
$buf = null;
$pdf->Cell(0,6,$buf,$border,0,"R");

$pdf->Ln(20);


//ȯ����
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	$nameInkind .= "ȯ����";

	$buf = "ȯ����";
	$pdf->Cell(20,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $promoterInfo['cmp_prm_family_name'];
	$buf .= " ";
	$buf .= $promoterInfo['cmp_prm_first_name'];
	$pdf->Cell(0,6,$buf,$border,0,"L");

	$pdf->Ln(15);
}



$buf = null;
$buf .= "�嵭ȯ���ͤ��괾����������";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();
$buf = null;
$buf .= ADMINISTRATIVE_SCRIVENER_ADDRESS;
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();
$buf = null;
$buf .= ADMINISTRATIVE_SCRIVENER_NAME;
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();






//DB�򥯥������롣
_DB_Close($link);


//PDF����Ϥ��롣
$pdf->Output();

_Log("[/user/company/pdf/create/teikan.php] end. OK!");



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
