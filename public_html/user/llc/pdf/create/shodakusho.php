<?php
/*
 * [���������Ω.JP �ġ���]
 * PDF����
 * ��Ǥ������(��Ʊ�����)
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
_Log("[/user/llc/pdf/create/shodakusho.php] start.");

_Log("[/user/llc/pdf/create/shodakusho.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/llc/pdf/create/shodakusho.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/llc/pdf/create/shodakusho.php] SERVER = '".print_r($_SERVER,true)."'");


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

//�괾������
$articleCreateYear = ((isset($inData['article_create_year']) && !_IsNull($inData['article_create_year']))?$inData['article_create_year']:null);
$articleCreateMonth = ((isset($inData['article_create_month']) && !_IsNull($inData['article_create_month']))?$inData['article_create_month']:null);
$articleCreateDay = ((isset($inData['article_create_day']) && !_IsNull($inData['article_create_day']))?$inData['article_create_day']:null);


//����ͤ����ꤹ�롣
$undeleteOnly4def = false;

//���¤ˤ�äơ�ɽ������桼������������¤��롣
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://����̵��

		_Log("[/user/llc/pdf/create/shodakusho.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
		_Log("[/user/llc/pdf/create/shodakusho.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
		_Log("[/user/llc/pdf/create/shodakusho.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

		$undeleteOnly4def = true;

		//��ʬ�Υ桼�������󡢲�Ҿ���Τ�ɽ�����롣
		//�桼����ID�����ID������å����롣

		//���ID�򸡺����롣
		$relationCompanyId = _GetRelationLlcId($loginInfo['usr_user_id']);


		_Log("[/user/llc/pdf/create/shodakusho.php] {������桼�������½���} ��(������)�桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/llc/pdf/create/shodakusho.php] {������桼�������½���} ��(������)���ID = '".$relationCompanyId."'");
		_Log("[/user/llc/pdf/create/shodakusho.php] {������桼�������½���} ��(�ѥ�᡼����)�桼����ID = '".$userId."'");
		_Log("[/user/llc/pdf/create/shodakusho.php] {������桼�������½���} ��(�ѥ�᡼����)���ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/llc/pdf/create/shodakusho.php] {������桼�������½���} ��(�����о�)�桼����ID = '".$userId."'");
		_Log("[/user/llc/pdf/create/shodakusho.php] {������桼�������½���} ��(�����о�)���ID = '".$companyId."'");

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
//�괾������
$errFlag = false;
if (_IsNull($articleCreateYear)) $errFlag = true;
if (_IsNull($articleCreateMonth)) $errFlag = true;
if (_IsNull($articleCreateDay)) $errFlag = true;
if ($errFlag)  $errorList[] = "���괾�������٤���Ͽ���Ƥ���������";
//���̾
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "�ؾ���(���̾)�٤���Ͽ���Ƥ���������";
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




if (count($errorList) > 0) {
	//���顼ͭ�ξ��
	_Log("[/user/llc/pdf/create/shodakusho.php] end. ERR!");


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
$title = "��Ǥ������";


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



//�괾������
$articleCreateDate = null;
$articleCreateDate .= _ConvertAD2Jp($articleCreateYear);
$articleCreateDate .= "ǯ";
$articleCreateDate .= $articleCreateMonth;
$articleCreateDate .= "��";
$articleCreateDate .= $articleCreateDay;
$articleCreateDate .= "��";
$articleCreateDate = mb_convert_kana($articleCreateDate, 'N');



//$topMessage = null;
//$topMessage .= "��ϡ�";
//$topMessage .= $articleCreateDate;
//$topMessage .= "�ε����괾�ˤ����ơ�";
//$topMessage .= "��ɽ�Ұ�";
//$topMessage .= "����Ǥ����ޤ����Τǡ����ν�Ǥ�����������ޤ���";


foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
//	//��ɽ�Ұ�ID="��ɽ�Ұ��ˤʤ�"�ʳ��ϡ����ء�
//	if ($promoterInfo['cmp_prm_representative_partner_id'] != MST_REPRESENTATIVE_PARTNER_ID_YES) continue;

	//��̳����ID="��̳���ԼҰ��ˤʤ�"�ʳ��ϡ����ء�
	if ($promoterInfo['cmp_prm_business_execution_id'] != MST_BUSINESS_EXECUTION_ID_YES) continue;

	$topMessage = null;

	//��ɽ�Ұ�ID="��ɽ�Ұ��ˤʤ�"����
	if ($promoterInfo['cmp_prm_representative_partner_id'] == MST_REPRESENTATIVE_PARTNER_ID_YES) {
		$topMessage .= "��ϡ�";
		$topMessage .= $articleCreateDate;
		$topMessage .= "�ε����괾�ˤ����ơ�";
		$topMessage .= "��̳���ԼҰ��ڤ���ɽ�Ұ�";
		$topMessage .= "����Ǥ����ޤ����Τǡ����ν�Ǥ�����������ޤ���";
	} else {
		$topMessage .= "��ϡ�";
		$topMessage .= $articleCreateDate;
		$topMessage .= "�ε����괾�ˤ����ơ�";
		$topMessage .= "��̳���ԼҰ�";
		$topMessage .= "����Ǥ����ޤ����Τǡ����ν�Ǥ�����������ޤ���";
	}

	//�ͳʼ��̤ˤ�äơ�̾������������ꤹ�롣
	$name = null;
	$address = null;
	$nameTitle = null;
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//�Ŀ�
			$name .= $promoterInfo['cmp_prm_family_name'];
			$name .= " ";
			$name .= $promoterInfo['cmp_prm_first_name'];

			$address .= $mstPrefList[$promoterInfo['cmp_prm_pref_id']]['name'];
			$address .= $promoterInfo['cmp_prm_address1'];
//			$address .= $promoterInfo['cmp_prm_address2'];
			if (!_IsNull($promoterInfo['cmp_prm_address2'])) {
				if (!_IsNull($address)) $address .= " ";
				$address .= $promoterInfo['cmp_prm_address2'];
			}

			$nameTitle = "�ᡡ̾";
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//ˡ��(������ҡ�ͭ�²�ҤΤ�)
			$name .= $promoterInfo['cmp_prm_company_name'];

			$address .= $mstPrefList[$promoterInfo['cmp_prm_company_pref_id']]['name'];
			$address .= $promoterInfo['cmp_prm_company_address1'];
			if (!_IsNull($promoterInfo['cmp_prm_company_address2'])) {
				if (!_IsNull($address)) $address .= " ";
				$address .= $promoterInfo['cmp_prm_company_address2'];
			}

			$nameTitle = "���̾";
			break;
	}

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

	$pdf->Ln(20);


	//����
	$buf = "�ʡ������ꡡ��";
	$pdf->Cell(30,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $address;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$pdf->Ln(5);

	//��̾
	$buf = "�ʡ�".$nameTitle."����";
	$pdf->Cell(30,6,$buf,$border,0,"L");

	$bufX = $pdf->GetX();

	$x = 170;
	$pdf->SetX($x);

	$buf = null;
	$buf .= "��";
	$pdf->Cell(20,6,$buf,$border,0,"L");

	$pdf->SetX($bufX);

	$buf = null;
	$buf .= $name;
//	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->MultiCell(120,6,$buf,$border,"L");

	$pdf->Ln(5);


	//����
	$buf = "�ʡ������桡��";
	$pdf->Cell(30,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $companyInfo['tbl_company']['cmp_company_name'];
	$pdf->Cell(100,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= "����";
	$pdf->Cell(20,6,$buf,$border,0,"L");

//	$buf = null;
//	$buf .= "��";
//	$pdf->Cell(0,6,$buf,$border,0,"L");
}













//DB�򥯥������롣
_DB_Close($link);


//PDF����Ϥ��롣
$pdf->Output();

_Log("[/user/llc/pdf/create/shodakusho.php] end. OK!");



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
