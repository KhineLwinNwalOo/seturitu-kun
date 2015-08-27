<?php
/*
 * [���������Ω.JP �ġ���]
 * PDF����
 * ���աʲ������Ͻ�
 *
 * ��������2008/12/01	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../../common/include.ini");
//include_once("../../../../common/libs/fpdf/mbfpdf.php");
include_once("../../../../common/libs/fpdf/mbfpdf_fpdi.php");


_LogDelete();
//_LogBackup();
_Log("[/user/company/pdf/create/inkantodokesho.php] start.");

_Log("[/user/company/pdf/create/inkantodokesho.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/company/pdf/create/inkantodokesho.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/company/pdf/create/inkantodokesho.php] SERVER = '".print_r($_SERVER,true)."'");


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

//��ǯ����
$birthYear = ((isset($inData['birth_year']) && !_IsNull($inData['birth_year']))?$inData['birth_year']:null);
$birthMonth = ((isset($inData['birth_month']) && !_IsNull($inData['birth_month']))?$inData['birth_month']:null);
$birthDay = ((isset($inData['birth_day']) && !_IsNull($inData['birth_day']))?$inData['birth_day']:null);


//ư��⡼��
$mode = (isset($inData['mode'])?$inData['mode']:null);

//����ͤ����ꤹ�롣
$undeleteOnly4def = false;

//���¤ˤ�äơ�ɽ������桼������������¤��롣
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://����̵��

		_Log("[/user/company/pdf/create/inkantodokesho.php] {������桼�������½���} ����ID = '".$loginInfo['usr_auth_id']."' = '����̵��'");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {������桼�������½���} ����ʬ�Υ桼��������Τ�ɽ�����롣");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {������桼�������½���} ���桼����ID�����ꤹ�롣");

		$undeleteOnly4def = true;

		//��ʬ�Υ桼�������󡢲�Ҿ���Τ�ɽ�����롣
		//�桼����ID�����ID������å����롣

		//���ID�򸡺����롣
		$relationCompanyId = _GetRelationCompanyId($loginInfo['usr_user_id']);


		_Log("[/user/company/pdf/create/inkantodokesho.php] {������桼�������½���} ��(������)�桼����ID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {������桼�������½���} ��(������)���ID = '".$relationCompanyId."'");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {������桼�������½���} ��(�ѥ�᡼����)�桼����ID = '".$userId."'");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {������桼�������½���} ��(�ѥ�᡼����)���ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/company/pdf/create/inkantodokesho.php] {������桼�������½���} ��(�����о�)�桼����ID = '".$userId."'");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {������桼�������½���} ��(�����о�)���ID = '".$companyId."'");

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
//��ɽ���������ǯ����
$errFlag = false;
if (_IsNull($birthYear)) $errFlag = true;
if (_IsNull($birthMonth)) $errFlag = true;
if (_IsNull($birthDay)) $errFlag = true;
if ($errFlag)  $errorList[] = "����ɽ���������ǯ�����٤���Ͽ���Ƥ���������";
//������
switch ($mode) {
	case PDF_MODE_INKAN_DIRECTOR:
		//��ɽ�������е������˹Ԥ����
		break;
	case PDF_MODE_INKAN_OTHER:
		//�����ͤ��е������˹Ԥ����
		//��̾
		$errFlag = false;
		if (!isset($inData['agent_family_name']) || _IsNull($inData['agent_family_name'])) $errFlag = true;
		if (!isset($inData['agent_first_name']) || _IsNull($inData['agent_first_name'])) $errFlag = true;
		if ($errFlag)  $errorList[] = "�������͡٤Ρػ�̾�٤���Ͽ���Ƥ���������";
		//��̾(�եꥬ��)
		$errFlag = false;
		if (!isset($inData['agent_family_name_kana']) || _IsNull($inData['agent_family_name_kana'])) $errFlag = true;
		if (!isset($inData['agent_first_name_kana']) || _IsNull($inData['agent_first_name_kana'])) $errFlag = true;
		if ($errFlag)  $errorList[] = "�������͡٤Ρػ�̾(�եꥬ��)�٤���Ͽ���Ƥ���������";
		//����
		$errFlag = false;
		if (!isset($inData['agent_pref_id']) || _IsNull($inData['agent_pref_id'])) $errFlag = true;
		if (!isset($inData['agent_address1']) || _IsNull($inData['agent_address1'])) $errFlag = true;
		if ($errFlag)  $errorList[] = "�������͡٤Ρؽ���٤���Ͽ���Ƥ���������";
		break;
	default:
		$errorList[] = "�������͡٤���ꤷ�Ƥ���������";
		break;
}
//���̾
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "�ؾ���(���̾)�٤���Ͽ���Ƥ���������";
//��Ź�����
$errFlag = false;
if (_IsNull($companyInfo['tbl_company']['cmp_pref_id'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_address1'])) $errFlag = true;
if ($errFlag)  $errorList[] = "����Ź����ϡ٤���Ͽ���Ƥ���������";
//���
$errFlag = true;
foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
	switch ($boardInfo['cmp_bod_post_id']) {
		case MST_POST_ID_REP_DIRECTOR:
			//��ɽ������

			//��ɽ������Υǡ��������ä����Ȥǡ��ޤ�OK!!!
			$errFlag = false;

			if (_IsNull($boardInfo['cmp_bod_family_name']) || _IsNull($boardInfo['cmp_bod_first_name'])) {
				$errFlag = true;
				break 2;
			}
			if (_IsNull($boardInfo['cmp_bod_family_name_kana']) || _IsNull($boardInfo['cmp_bod_first_name_kana'])) {
				$errFlag = true;
				break 2;
			}
			if (_IsNull($boardInfo['cmp_bod_pref_id']) || _IsNull($boardInfo['cmp_bod_address1'])) {
				$errFlag = true;
				break 2;
			}
			
			//��ɽ������ʣ����Ͽ��ǽ�ˤʤä��Τǡ���Ƭ1�����ɽ���������ɽ�Ȥ��롣1������å������������ȴ���롣
			break 2;
	}
}
if ($errFlag) $errorList[] = "����ɽ������٤Ρؤ�̾���١��ؤ�̾��(�եꥬ��)�١��ؽ���٤���Ͽ���Ƥ���������";




if (count($errorList) > 0) {
	//���顼ͭ�ξ��
	_Log("[/user/company/pdf/create/inkantodokesho.php] end. ERR!");


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
$pdf = new MBFPDF('P', 'mm', array(182.0, 257.0));

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
$pagecount = $pdf->setSourceFile("../../../../common/temp_pdf/inkantodokesho.pdf");

//������1�ڡ����ܤ�������롣(1�ڡ��������ʤ���)
$tplidx = $pdf->ImportPage(1);
$pdf->addPage();
//�����򥻥åȤ��롣
$pdf->useTemplate($tplidx);


$pdf->SetFillColor($bgR, $bgG, $bgB);


$pdf->SetFontSize(10);

//���桦̾��
$buf = $companyInfo['tbl_company']['cmp_company_name'];
$pdf->SetXY(101, 35);
$pdf->MultiCell(67,5,$buf,$border,"L",$fill);


//��Ź���礿���̳��
$buf = null;
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
if (!_IsNull($companyInfo['tbl_company']['cmp_address2'])) {
	if (!_IsNull($buf)) $buf .= " ";
	$buf .= $companyInfo['tbl_company']['cmp_address2'];
}
$buf = mb_convert_kana($buf, 'N');
$pdf->SetFontSize(8);
$pdf->SetXY(101, 46);
$pdf->MultiCell(67,3,$buf,$border,"L",$fill);


$pdf->SetFontSize(10);


//��ɽ�������������롣
$repBoardInfo = null;
foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
	switch ($boardInfo['cmp_bod_post_id']) {
		case MST_POST_ID_REP_DIRECTOR:
			//��ɽ������
			$repBoardInfo = $boardInfo;
			break 2;
	}
}


//������мԤλ�̾
$buf = null;
$buf .= $repBoardInfo['cmp_bod_family_name'];
$buf .= " ";
$buf .= $repBoardInfo['cmp_bod_first_name'];
$buf = mb_convert_kana($buf, 'N');
$pdf->SetXY(101, 69);
$pdf->MultiCell(67,5,$buf,$border,"L",$fill);


//������мԤ���ǯ����
$buf = null;
$buf .= _ConvertAD2Jp($birthYear);
$buf .= "ǯ";
$buf .= $birthMonth;
$buf .= "��";
$buf .= $birthDay;
$buf .= "��";
$buf .= "��";
$buf = mb_convert_kana($buf, 'N');
$pdf->SetFillColor(255, 255, 255);
$pdf->SetXY(101, 81);
$pdf->MultiCell(67,6,$buf,$border,"L",1);


$pdf->SetFillColor($bgR, $bgG, $bgB);



$noticeName = null;
$noticeNameKana = null;
$noticeAddress = null;
$noticeX = 0;

//������
switch ($mode) {
	case PDF_MODE_INKAN_DIRECTOR:
		//��ɽ�������е������˹Ԥ����

		$noticeName .= $repBoardInfo['cmp_bod_family_name'];
		$noticeName .= " ";
		$noticeName .= $repBoardInfo['cmp_bod_first_name'];

		$noticeNameKana .= $repBoardInfo['cmp_bod_family_name_kana'];
		$noticeNameKana .= " ";
		$noticeNameKana .= $repBoardInfo['cmp_bod_first_name_kana'];

		$noticeAddress .= $mstPrefList[$repBoardInfo['cmp_bod_pref_id']]['name'];
		$noticeAddress .= $repBoardInfo['cmp_bod_address1'];
		if (!_IsNull($repBoardInfo['cmp_bod_address2'])) {
			if (!_IsNull($noticeAddress)) $noticeAddress .= " ";
			$noticeAddress .= $repBoardInfo['cmp_bod_address2'];
		}

		$noticeX = 43;

		break;
	case PDF_MODE_INKAN_OTHER:
		//�����ͤ��е������˹Ԥ����

		$noticeName .= $inData['agent_family_name'];
		$noticeName .= " ";
		$noticeName .= $inData['agent_first_name'];

		$noticeNameKana .= $inData['agent_family_name_kana'];
		$noticeNameKana .= " ";
		$noticeNameKana .= $inData['agent_first_name_kana'];

		$noticeAddress .= $mstPrefList[$inData['agent_pref_id']]['name'];
		$noticeAddress .= $inData['agent_address1'];
		if (!_IsNull($inData['agent_address2'])) {
			if (!_IsNull($noticeAddress)) $noticeAddress .= " ";
			$noticeAddress .= $inData['agent_address2'];
		}

		$noticeX = 80.7;

		break;
}

//�Ͻпͤΰ�����м��ܿ� or ������
$buf = "��";
$pdf->SetXY($noticeX, 113);
$pdf->MultiCell(3,3,$buf,$border,"L",$fill);


//�Ͻпͤν���
$buf = $noticeAddress;
$buf = mb_convert_kana($buf, 'N');
$pdf->SetFontSize(8);
$pdf->SetXY(34, 118);
$pdf->MultiCell(95,3,$buf,$border,"L",$fill);


//�ϽпͤΥեꥬ��
$buf = $noticeNameKana;
//���ѥ����ʥ����Ѵ����롣
$buf = mb_convert_kana($buf, 'KVCN');
$pdf->SetFontSize(8);
$pdf->SetXY(34, 129);
$pdf->MultiCell(95,3,$buf,$border,"L",$fill);


$pdf->SetFontSize(10);


//�Ͻпͤλ�̾
$buf = $noticeName;
$buf = mb_convert_kana($buf, 'N');
$pdf->SetXY(34, 135);
$pdf->MultiCell(95,5,$buf,$border,"L",$fill);


//������
switch ($mode) {
	case PDF_MODE_INKAN_DIRECTOR:
		//��ɽ�������е������˹Ԥ����

		break;
	case PDF_MODE_INKAN_OTHER:
		//�����ͤ��е������˹Ԥ����

		//��Ǥ��

		//�����ͤν���
		$buf = null;
		$buf .= $mstPrefList[$inData['agent_pref_id']]['name'];
		$buf .= $inData['agent_address1'];
		if (!_IsNull($inData['agent_address2'])) {
			if (!_IsNull($buf)) $buf .= " ";
			$buf .= $inData['agent_address2'];
		}
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(45, 151.6);
		$pdf->MultiCell(115,5,$buf,$border,"L",$fill);


		//�����ͤλ�̾
		$buf = null;
		$buf .= $inData['agent_family_name'];
		$buf .= " ";
		$buf .= $inData['agent_first_name'];
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(45, 157.8);
		$pdf->MultiCell(115,5,$buf,$border,"L",$fill);


		//������(ǯ)
		$buf = _ConvertAD2Jp($pdfCreateYear, false);
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(33, 169.5);
		$pdf->MultiCell(10,5,$buf,$border,"R",$fill);


		//������(��)
		$buf = $pdfCreateMonth;
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(46, 169.5);
		$pdf->MultiCell(10,5,$buf,$border,"R",$fill);


		//������(��)
		$buf = $pdfCreateDay;
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(59.2, 169.5);
		$pdf->MultiCell(10,5,$buf,$border,"R",$fill);


		//��ɽ������ν���
		$buf = null;
		$buf .= $mstPrefList[$repBoardInfo['cmp_bod_pref_id']]['name'];
		$buf .= $repBoardInfo['cmp_bod_address1'];
		if (!_IsNull($repBoardInfo['cmp_bod_address2'])) {
			if (!_IsNull($buf)) $buf .= " ";
			$buf .= $repBoardInfo['cmp_bod_address2'];
		}
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(37, 175.7);
		$pdf->MultiCell(98,5,$buf,$border,"L",$fill);


		//��ɽ������λ�̾
		$buf = null;
		$buf .= $repBoardInfo['cmp_bod_family_name'];
		$buf .= " ";
		$buf .= $repBoardInfo['cmp_bod_first_name'];
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(37, 181);
		$pdf->MultiCell(88,5,$buf,$border,"L",$fill);

		break;
}









//DB�򥯥������롣
_DB_Close($link);


//PDF����Ϥ��롣
$pdf->Output();

_Log("[/user/company/pdf/create/inkantodokesho.php] end. OK!");



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
