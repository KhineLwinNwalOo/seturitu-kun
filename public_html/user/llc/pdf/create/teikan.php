<?php
/*
 * [���������Ω.JP �ġ���]
 * PDF����
 * �괾(��Ʊ�����)
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
_Log("[/user/llc/pdf/create/teikan.php] start.");

_Log("[/user/llc/pdf/create/teikan.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/llc/pdf/create/teikan.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/llc/pdf/create/teikan.php] SERVER = '".print_r($_SERVER,true)."'");


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

		_Log("[/user/llc/pdf/create/teikan.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
		_Log("[/user/llc/pdf/create/teikan.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
		_Log("[/user/llc/pdf/create/teikan.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

		$undeleteOnly4def = true;

		//��ʬ�Υ桼�������󡢲�Ҿ���Τ�ɽ�����롣
		//�桼����ID�����ID������å����롣

		//���ID�򸡺����롣
		$relationCompanyId = _GetRelationLlcId($loginInfo['usr_user_id']);


		_Log("[/user/llc/pdf/create/teikan.php] {������桼�������½���} ��(������)�桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/llc/pdf/create/teikan.php] {������桼�������½���} ��(������)���ID = '".$relationCompanyId."'");
		_Log("[/user/llc/pdf/create/teikan.php] {������桼�������½���} ��(�ѥ�᡼����)�桼����ID = '".$userId."'");
		_Log("[/user/llc/pdf/create/teikan.php] {������桼�������½���} ��(�ѥ�᡼����)���ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/llc/pdf/create/teikan.php] {������桼�������½���} ��(�����о�)�桼����ID = '".$userId."'");
		_Log("[/user/llc/pdf/create/teikan.php] {������桼�������½���} ��(�����о�)���ID = '".$companyId."'");

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
////ȯ�Բ�ǽ���������
//if (_IsNull($companyInfo['tbl_company']['cmp_stock_total_num'])) $errorList[] = "��ȯ�Բ�ǽ����������٤���Ͽ���Ƥ���������";
////�������
//if (_IsNull($companyInfo['tbl_company']['cmp_board_formation_id'])) $errorList[] = "����������٤���Ͽ���Ƥ���������";
////������Ϳ�
//if (_IsNull($companyInfo['tbl_company']['cmp_director_num'])) {
//	$errorList[] = "�ؼ�����Ϳ��٤���Ͽ���Ƥ���������";
//} else {
//	if ($companyInfo['tbl_company']['cmp_director_num'] < 1) $errorList[] = "�ؼ�����Ϳ��٤���Ͽ���Ƥ���������(1�Ͱʾ�)";
//}
////�����򡦴ƺ����Ǥ��
//if (_IsNull($companyInfo['tbl_company']['cmp_term_year'])) $errorList[] = "�ؼ����򡦴ƺ����Ǥ���٤���Ͽ���Ƥ���������";
//����ǯ��
if (_IsNull($companyInfo['tbl_company']['cmp_business_start_month'])) $errorList[] = "�ػ���ǯ�١٤���Ͽ���Ƥ���������";
////1����ñ��
//if (_IsNull($companyInfo['tbl_company']['cmp_stock_price'])) $errorList[] = "��1����ñ���٤���Ͽ���Ƥ���������";
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
//�괾��̳����ID
if (_IsNull($companyInfo['tbl_company']['cmp_article_business_execution_id'])) $errorList[] = "���괾�����٤Ρض�̳���Ԥ����ꡦ�����٤���Ͽ���Ƥ���������";
//�괾ʬ��ID
if (_IsNull($companyInfo['tbl_company']['cmp_article_share_id'])) $errorList[] = "���괾�����٤Ρ����ס�»������;�⻺��ʬ�ۤ����ꡦ�����٤���Ͽ���Ƥ���������";
//�괾�ѹ�ID
if (_IsNull($companyInfo['tbl_company']['cmp_article_change_id'])) $errorList[] = "���괾�����٤Ρ��괾�ѹ������ꡦ�����٤���Ͽ���Ƥ���������";


if (count($errorList) > 0) {
	//���顼ͭ�ξ��
	_Log("[/user/llc/pdf/create/teikan.php] end. ERR!");


	$buf = "��PDF��������뤿��ξ���­��ޤ��󡣡ع�Ʊ�����ΩLLC������Ͽ�ٲ��̤ǡ���������Ϥ��Ƥ���������";
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
$mstPrefList = _GetMasterList('mst_pref');													//��ƻ�ܸ��ޥ���
unset($mstPrefList[MST_PREF_ID_OVERSEAS]);
$mstPostList = _GetMasterList('mst_post');													//�򿦥ޥ���
$mstArticleBusinessExecutionList = _GetMasterList('mst_article_business_execution');		//�괾��̳���ԥޥ���
$mstArticleShareList = _GetMasterList('mst_article_share');									//�괾ʬ�ۥޥ���
$mstArticleChangeList = _GetMasterList('mst_article_change');								//�괾�ѹ��ޥ���


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


//�裲�ϡ��Ұ��ڤӽл�
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裲�ϡ��Ұ��ڤӽл�",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


//�ʼҰ��λ�̾�ڤӽ��ꡢ�л�ڤ���Ǥ��
$pdf->Cell(0,6,"�ʼҰ��λ�̾�ڤӽ��ꡢ�л�ڤ���Ǥ��",$border,0,"L");
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

			$nameInkind .= "�Ұ�";
			$nameInkind .= " ";

			//�ͳʼ��̤ˤ�äơ�̾�������ꤹ�롣
			switch ($promoterInfo['cmp_prm_personal_type_id']) {
				case MST_PERSONAL_TYPE_ID_PERSONAL:
					//�Ŀ�
					$nameInkind .= $promoterInfo['cmp_prm_family_name'];
					$nameInkind .= " ";
					$nameInkind .= $promoterInfo['cmp_prm_first_name'];
					break;
				case MST_PERSONAL_TYPE_ID_CORPORATION:
					//ˡ��(������ҡ�ͭ�²�ҤΤ�)
					$nameInkind .= $promoterInfo['cmp_prm_company_name'];
					break;
			}

			break;
	}
}

$buf = null;
$buf .= "�Ұ��λ�̾�ڤӽ��ꡢ�л�β��۵ڤ���Ǥ�ϼ��ΤȤ���Ǥ��롣";
if (!_IsNull($nameInkind)) {
	$buf .= "�ʤ�";
	$buf .= $nameInkind;
	$buf .= "�ϡ���������븽ʪ�л�ˤ��л�β��ۤ������Ƥ롣";
}
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	$investmentCash = null;		//����ζ��
	$investmentInkind = null;	//��ʪ�ζ��

	//�л�����Ͽ�Ϥ��뤫��
	if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

		//����νл�򽸷פ��롣
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_CASH])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_CASH]['investment_info'] as $iKey => $investmentInfo) {
				if (_IsNull($investmentCash)) $investmentCash = 0;
				$investmentCash += $investmentInfo['cmp_prm_inv_investment'];
			}

			if (!_IsNull($investmentCash)) {
				//���ͤ���ɽ�����Ѵ����롣
//				$investmentCash = "��"._ConvertNum2Ja($investmentCash * 10000)."��";
				$investmentCash = "��"._ConvertNum2Ja($investmentCash)."��";
			}
		}

		//��ʪ�νл�򽸷פ��롣
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
				if (_IsNull($investmentInkind)) $investmentInkind = 0;
				$investmentInkind += $investmentInfo['cmp_prm_inv_investment'];
			}

			if (!_IsNull($investmentInkind)) {
				//���ͤ���ɽ�����Ѵ����롣
//				$investmentInkind = "��"._ConvertNum2Ja($investmentInkind * 10000)."��";
				$investmentInkind = "��"._ConvertNum2Ja($investmentInkind)."��";
			}
		}
	}

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


	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = null;
	$buf .= $address;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = null;
	$buf .= "ͭ����Ǥ�Ұ�";
	$pdf->Cell(35,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $name;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(80,6,$buf,$border,0,"L");

	$buf = $investmentCash;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(0,6,$buf,$border,0,"R");

	$pdf->Ln();

	if (!_IsNull($investmentInkind)) {
		$x = $pdf->GetX();
		$x += 20;
		$pdf->SetX($x);

		$buf = null;
		$pdf->Cell(35,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "��ʪ�л�";
		$pdf->Cell(80,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $investmentInkind;
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(0,6,$buf,$border,0,"R");

		$pdf->Ln();
	}

	$pdf->Ln();
}


if (!_IsNull($nameInkind)) {
	//�ʸ�ʪ�л��
	$pdf->Cell(0,6,"�ʸ�ʪ�л��",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");

	$buf = "����Ҥ���Ω�˺ݤ��Ƹ�ʪ�л�򤹤�Ԥλ�̾���л����Ū�Ǥ���⻺�����β��ۤϡ����ΤȤ���Ǥ��롣";
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
		$buf .= "ͭ����Ǥ�Ұ�";
		$pdf->Cell(30,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $name;
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


		$totalInvestmentInkind = 0;		//��ʪ�νл�ۤι��

		//�л�����Ͽ�Ϥ��뤫��
		if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
			$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

			//��ʪ�νл�򽸷פ��롣
			if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {

				$countInkind = 0;
				foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
					//�л������ꤹ�롣
//					$investmentInkind = $investmentInfo['cmp_prm_inv_investment'] * 10000;
					$investmentInkind = $investmentInfo['cmp_prm_inv_investment'];

					//�л�ۤι�פ�׻����롣
					$totalInvestmentInkind += $investmentInkind;

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
			}
		}

		//���ͤ���ɽ�����Ѵ����롣
		$totalInvestmentInkind = "��"._ConvertNum2Ja($totalInvestmentInkind)."��";


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
		$buf .= "��ײ���";
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
		$buf .= $totalInvestmentInkind;
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(0,6,$buf,$border,0,"L");

		$pdf->Ln();
		$pdf->Ln();
	}
}


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


//�裳�ϡ���̳�μ��ԡ���̳���ԼҰ��ڤ���ɽ�Ұ�
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裳�ϡ���̳�μ��ԡ���̳���ԼҰ��ڤ���ɽ�Ұ�",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


//�ʶ�̳���Ԥθ�������̳���ԼҰ�����Ǥ�ڤӲ�Ǥ��
$pdf->Cell(0,6,"�ʶ�̳���Ԥθ�������̳���ԼҰ�����Ǥ�ڤӲ�Ǥ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "��̳���Ԥ϶�̳���ԼҰ���";
$buf .= $mstArticleBusinessExecutionList[$companyInfo['tbl_company']['cmp_article_business_execution_id']]['name'];
$buf .= "���äƷ��ꤹ�롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

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


//�裴�ϡ��Ұ��β����ڤ����
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裴�ϡ��Ұ��β����ڤ����",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


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


//�裵�ϡ��Ұ��ν�̾
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裵�ϡ��Ұ��ν�̾",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


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


//�裶�ϡ��ס���
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裶�ϡ��ס���",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


//�ʱĶ�ǯ�١�
$pdf->Cell(0,6,"�ʱĶ�ǯ�١�",$border,0,"L");
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


//�����פ�������
$pdf->Cell(0,6,"�����פ�������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "���פ������ϡ������ǯ�٤��������ߤμҰ���ʬ�ۤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


////��»��ʬ�ۤγ���
//$pdf->Cell(0,6,"��»��ʬ�ۤγ���",$border,0,"L");
//$pdf->Ln();
//$pdf->Cell(20,6,_no(++$no),$border,0,"L");
//
//$buf = "�ƼҰ���»��ʬ�ۤγ��ϡ���Ұ���Ʊ�դˤ�ꡢ�л�β��ۤȰۤʤ���ˤ�뤳�Ȥ��Ǥ��롣";
//$pdf->MultiCell(0,6,$buf,$border,"L");
//$pdf->Ln();


//����������»����ʬ�ۡ�
$pdf->Cell(0,6,"����������»����ʬ�ۡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "�ƼҰ�����������»����ʬ�ۤγ��ϡ�";
$buf .= $mstArticleShareList[$companyInfo['tbl_company']['cmp_article_share_id']]['name'];
$buf .= "��";
$buf .= "����������ô����»���ˤĤ��ƤϽл����Ū�ʳ��ˤϵڤФʤ���ΤȤ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

//�ʻ�;�⻺��ʬ�ۡ�
$pdf->Cell(0,6,"�ʻ�;�⻺��ʬ�ۡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "��;�⻺��ʬ�ۤγ��ϡ�";
$buf .= $mstArticleShareList[$companyInfo['tbl_company']['cmp_article_share_id']]['name'];
$buf .= "��";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//�裷�ϡ���§
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"�裷�ϡ���§",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


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
$buf .= "����Ҥκǽ�λ���ǯ�٤ϡ�����Ҥ���Ω��������";
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


//����Ω�˺ݤ�����ܶ��
$pdf->Cell(0,6,"����Ω�˺ݤ�����ܶ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//���ܶ��ñ�̤����ߤ���ߤˤ��롣
//$capital = $companyInfo['tbl_company']['cmp_capital'] * 10000;
$capital = $companyInfo['tbl_company']['cmp_capital'];
$capital = _ConvertNum2Ja($capital);

$buf = null;
$buf .= "����Ҥ���Ω���λ��ܶ�ϡ���";
$buf .= $capital;
$buf .= "�ߤȤ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//����Ω����̳���ԼҰ���
$pdf->Cell(0,6,"����Ω����̳���ԼҰ���",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "����Ҥ���Ω����̳���ԼҰ��ϡ����ΤȤ���Ȥ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

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

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = null;
	$buf .= "��̳���ԼҰ�";
	$pdf->Cell(35,6,$buf,$border,0,"L");

//��̾��Ĺ����礬����Τǡ�MultiCell���ѹ����롣
//	$buf = null;
//	$buf .= $name;
//	$buf = mb_convert_kana($buf, 'N');
//	$pdf->Cell(0,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $name;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

//	$pdf->Ln();
	$pdf->Ln();
}


//����Ω����ɽ�Ұ���
$pdf->Cell(0,6,"����Ω����ɽ�Ұ���",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "����Ҥ���Ω����ɽ�Ұ��ϡ����ΤȤ���Ȥ��롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

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

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = null;
	$buf .= $address;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = null;
	$buf .= "��ɽ�Ұ�";
	$pdf->Cell(35,6,$buf,$border,0,"L");

//��̾��Ĺ����礬����Τǡ�MultiCell���ѹ����롣
//	$buf = null;
//	$buf .= $name;
//	$buf = mb_convert_kana($buf, 'N');
//	$pdf->Cell(0,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $name;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

//	$pdf->Ln();
	$pdf->Ln();
}


//���괾���ѹ���
$pdf->Cell(0,6,"���괾���ѹ���",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "�괾���ѹ��ϡ�";
$buf .= $mstArticleChangeList[$companyInfo['tbl_company']['cmp_article_change_id']]['name'];
$buf .= "���ꤹ�롣";
$buf = mb_convert_kana($buf, 'N');
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


//$buf = null;
//$buf .= "�ʾ塢";
//$buf .= $companyInfo['tbl_company']['cmp_company_name'];
//$buf .= "����Ω�Τ��ᡢ�����괾���������ͭ����Ǥ�Ұ������˵�̾�������롣";
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
	$promoterCount++;
	if ($promoterCount == 1) {
		$promoterName .= $name;
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
$buf .= "��Ω�ΰ٤ˡ�ͭ����Ǥ�Ұ� ".$promoterName." ��\n�괾���������ͤǤ���".ADMINISTRATIVE_SCRIVENER_NAME."�ϡ��ż�Ū��Ͽ�Ǥ������괾���������\n������Żҽ�̾���롣";
//$buf = mb_convert_kana($buf, 'N');
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

	$buf = "ͭ����Ǥ�Ұ�";
	$pdf->Cell(35,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $name;
	$buf = mb_convert_kana($buf, 'N');
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

_Log("[/user/llc/pdf/create/teikan.php] end. OK!");



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
