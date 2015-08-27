<?php
/*
 * [���������Ω.JP �ġ���]
 * PDF����
 * ��Ǥ��
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
_Log("[/user/company/pdf/create/ininjo.php] start.");

_Log("[/user/company/pdf/create/ininjo.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/company/pdf/create/ininjo.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/company/pdf/create/ininjo.php] SERVER = '".print_r($_SERVER,true)."'");


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

//ư��⡼��
$mode = (isset($inData['mode'])?$inData['mode']:null);

//����ͤ����ꤹ�롣
$undeleteOnly4def = false;

//���¤ˤ�äơ�ɽ������桼������������¤��롣
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://����̵��

		_Log("[/user/company/pdf/create/ininjo.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
		_Log("[/user/company/pdf/create/ininjo.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
		_Log("[/user/company/pdf/create/ininjo.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

		$undeleteOnly4def = true;

		//��ʬ�Υ桼�������󡢲�Ҿ���Τ�ɽ�����롣
		//�桼����ID�����ID������å����롣

		//���ID�򸡺����롣
		$relationCompanyId = _GetRelationCompanyId($loginInfo['usr_user_id']);


		_Log("[/user/company/pdf/create/ininjo.php] {������桼�������½���} ��(������)�桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/create/ininjo.php] {������桼�������½���} ��(������)���ID = '".$relationCompanyId."'");
		_Log("[/user/company/pdf/create/ininjo.php] {������桼�������½���} ��(�ѥ�᡼����)�桼����ID = '".$userId."'");
		_Log("[/user/company/pdf/create/ininjo.php] {������桼�������½���} ��(�ѥ�᡼����)���ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/company/pdf/create/ininjo.php] {������桼�������½���} ��(�����о�)�桼����ID = '".$userId."'");
		_Log("[/user/company/pdf/create/ininjo.php] {������桼�������½���} ��(�����о�)���ID = '".$companyId."'");

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
//ư��⡼��
if (_IsNull($mode)) $errorList[] = "�������͡٤���ꤷ�Ƥ���������";
//������
switch ($mode) {
	case PDF_MODE_ININJO_PROMOTER:
		//ȯ���ͤΰ�ͤ������ͤȤ����е������˹Ԥ����
		//ȯ����
		if (!isset($inData['cmp_prm_no']) || _IsNull($inData['cmp_prm_no'])) $errorList[] = "��ȯ���͡٤����򤷤Ƥ���������";
		break;
	case PDF_MODE_ININJO_OTHER:
		//ȯ���Ͱʳ�����3�Ԥ������ͤȤ����е������˹Ԥ����
		//��̾
		$errFlag = false;
		if (!isset($inData['family_name']) || _IsNull($inData['family_name'])) $errFlag = true;
		if (!isset($inData['first_name']) || _IsNull($inData['first_name'])) $errFlag = true;
		if ($errFlag)  $errorList[] = "�������͡٤Ρػ�̾�٤���Ͽ���Ƥ���������";
		//����
		$errFlag = false;
		if (!isset($inData['pref_id']) || _IsNull($inData['pref_id'])) $errFlag = true;
		if (!isset($inData['address1']) || _IsNull($inData['address1'])) $errFlag = true;
		if ($errFlag)  $errorList[] = "�������͡٤Ρؽ���٤���Ͽ���Ƥ���������";
		break;
	default:
		$errorList[] = "�������͡٤���ꤷ�Ƥ���������";
		break;
}
//���̾
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "�ؾ���(���̾)�٤���Ͽ���Ƥ���������";
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




if (count($errorList) > 0) {
	//���顼ͭ�ξ��
	_Log("[/user/company/pdf/create/ininjo.php] end. ERR!");


	$buf = "��PDF��������뤿��ξ���­��ޤ��󡣡س��������Ω������Ͽ�ٲ��̤ǡ���������Ϥ��Ƥ������������ϡ����괾ǧ�ڡٲ��̤ǡ���������Ϥ��Ƥ���������";
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


//���--------------------------------------------start
//�ե���ȥ�������������롣
//�̾�
$normalFontSize = 10;

//�����ȥ�
$title = "�ѡ�Ǥ����";


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


$pdf->Ln(30);


//�����ȥ�
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


$pdf->SetFontSize(10);


//�����ͤ����ꤹ�롣
$agent = null;
switch ($mode) {
	case PDF_MODE_ININJO_PROMOTER:
		//ȯ���ͤΰ�ͤ������ͤȤ����е������˹Ԥ����
		//���򤵤줿ȯ���;����������롣
		$promoterInfo4Agent = null;
		foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
			if ($promoterInfo['cmp_prm_no'] == $inData['cmp_prm_no']) {
				$promoterInfo4Agent = $promoterInfo;
				break;
			}
		}
		$agent .= "ȯ����";
		$agent .= " ";
		$agent .= $promoterInfo4Agent['cmp_prm_family_name'];
		$agent .= " ";
		$agent .= $promoterInfo4Agent['cmp_prm_first_name'];
		$agent .= " ";
		$agent .= "��";
		$agent .= "����ϡ�";
		$agent .= $mstPrefList[$promoterInfo4Agent['cmp_prm_pref_id']]['name'];
		$agent .= $promoterInfo4Agent['cmp_prm_address1'];
		if (!_IsNull($promoterInfo4Agent['cmp_prm_address2'])) {
			//$agent .= " ";
			$agent .= $promoterInfo4Agent['cmp_prm_address2'];
		}
		$agent .= "��";
		break;
	case PDF_MODE_ININJO_OTHER:
		//ȯ���Ͱʳ�����3�Ԥ������ͤȤ����е������˹Ԥ����
		$agent .= $inData['family_name'];
		$agent .= " ";
		$agent .= $inData['first_name'];
		$agent .= " ";
		$agent .= "��";
		$agent .= "����ϡ�";
		$agent .= $mstPrefList[$inData['pref_id']]['name'];
		$agent .= $inData['address1'];
		if (!_IsNull($inData['address2'])) {
			//$agent .= " ";
			$agent .= $inData['address2'];
		}
		$agent .= "��";
		break;
	default:
		break;
}

$buf = null;
$buf .= "��ϡ�";
$buf .= $agent;
$buf .= "�������ͤ���ᡢ�������¤��Ǥ���롣";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(20);


$pdf->SetFontSize(13);

$buf = "��";
$pdf->Cell(0,6,$buf,$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(10);

//���̾��ˡ��̾
$buf = null;
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$buf .= "����Ω�˴ؤ���ź�դΤȤ����ż�Ū��Ͽ�Ǥ��뤽�θ����괾����������³���˴ؤ�����ڤη";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(20);


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


//ȯ����
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	$buf = null;
	$buf .= "ȯ���� ����";
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = null;
	$buf .= $mstPrefList[$promoterInfo['cmp_prm_pref_id']]['name'];
	$buf .= $promoterInfo['cmp_prm_address1'];
	$buf .= $promoterInfo['cmp_prm_address2'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$buf = null;
	$buf .= "��̾����̾��";
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = null;
	$buf .= $promoterInfo['cmp_prm_family_name'];
	$buf .= " ";
	$buf .= $promoterInfo['cmp_prm_first_name'];
	$pdf->Cell(100,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= "��";
	$pdf->Cell(0,6,$buf,$border,0,"L");

	$pdf->Ln(15);
}











//DB�򥯥������롣
_DB_Close($link);


//PDF����Ϥ��롣
$pdf->Output();

_Log("[/user/company/pdf/create/ininjo.php] end. OK!");



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
