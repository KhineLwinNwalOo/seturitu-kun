<?php
/*
 * [��������]
 * PDF����
 * �괾
 *
 * ��������2008/11/05	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");
include_once("../../common/libs/fpdf/mbfpdf.php");


_LogDelete();
//_LogBackup();
_Log("[/pdf/create/teikan.php] start.");

_Log("[/pdf/create/teikan.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/create/teikan.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/create/teikan.php] SERVER = '".print_r($_SERVER,true)."'");


//ǧ�ڥ����å�----------------------------------------------------------------------start
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/create/teikan.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/pdf/create/teikan.php] end.");
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
$mstStockPriceList = _GetMasterList('mst_stock_price', $undeleteOnly);			//1����ñ���ޥ���
$mstJusticeTypeList = _GetMasterList('mst_justice_type', $undeleteOnly);		//�ζȥ����ץޥ���

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
$title = "�괾";


//[�ǥХå���]
//�ܡ�����
$border = 0;
//���--------------------------------------------end


// EUC-JP->SJIS �Ѵ���ưŪ�˹Ԥʤ碌����� mbfpdf.php ��� $EUC2SJIS ��
// true �˽������뤫�����Τ褦�˼¹Ի��� true �����ꤷ�Ƥ��Ѵ����Ƥ��ޤ���
//$GLOBALS['EUC2SJIS'] = true;

//PDF�Υ����������ꤹ�롣�ǥե����=FPDF($orientation='P',$unit='mm',$format='A4')
$pdf=new MBFPDF();

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

//���̾
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$inquiryInfo['tbl_company']['cmp_company_name'],$border,0,"C");
$pdf->Ln(20);


//�����ȥ�
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


//�裱�� �� §
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裱�� �� §",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);

$no = 0;

//�ʾ����
$pdf->Cell(0,6,"�ʾ����",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$companyName = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name'])) $companyName .= $inquiryInfo['tbl_company']['cmp_company_name'];
$companyNameEn = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name_en'])) $companyNameEn .= $inquiryInfo['tbl_company']['cmp_company_name_en'];

$buf = null;
if (!_IsNull($companyName)) {
	$buf .= "����Ҥϡ�";
	$buf .= $companyName;
	if (!_IsNull($companyNameEn)) {
		$buf .= "��";
		$buf .= "��ʸ̾��";
		$buf .= $companyNameEn;
		$buf .= "��";
		$buf .= "��";
	}
	$buf .= "�ȾΤ��롣";
} else {
	$errorList[] = "�ز��̾��ˡ��̾�٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//����Ū��
$pdf->Cell(0,6,"����Ū��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");


//�ʲ��ϡ����̤ʤ�Ρ���礻ID=19�ѡ�
$purpose = null;
if ($inquiryId == 19) {
	$purpose .= "���� ����ݸ�ˡ�˴�Ť����ε��𥵡��ӥ����ȵڤӲ��ͽ�ɥ����ӥ�����\n";
	$purpose .= "��(1)ˬ����ڤӲ��ͽ��ˬ����\n";
	$purpose .= "��(2)ˬ��������ڤӲ��ͽ��ˬ��������\n";
	$purpose .= "��(3)ˬ��Ǹ�ڤӲ��ͽ��ˬ��Ǹ�\n";
	$purpose .= "��(4)�̽���ڤӲ��ͽ���̽���\n";
	$purpose .= "��(5)û������������ڤӲ��ͽ��û������������\n";
	$purpose .= "��(6)������������������ڤӲ��ͽ��������������������\n";
	$purpose .= "��(7)ʡ���Ѷ���Ϳ�ڤӲ��ͽ��ʡ���Ѷ���Ϳ\n";
	$purpose .= "��(8)����ʡ���Ѷ�����ڤ�������ͽ��ʡ���Ѷ�����\n";
	$purpose .= "���� ����ݸ�ˡ�˴�Ť�������ٱ����\n";
	$purpose .= "���� ����ݸ�ˡ�˴�Ť��ϰ�̩�巿�����ӥ��ڤ��ϰ�̩�巿���ͽ�ɥ����ӥ�\n";
	$purpose .= "��(1)�����б���ˬ����\n";
	$purpose .= "��(2)ǧ�ξ��б����̽���ڤӲ��ͽ��ǧ�ξ��б����̽���\n";
	$purpose .= "��(3)������¿��ǽ��������ڤӲ��ͽ�ɾ�����¿��ǽ��������\n";
	$purpose .= "��(4)ǧ�ξ��б�����Ʊ������ڤӲ��ͽ��ǧ�ξ��б�����Ʊ������\n";
	$purpose .= "��(5)�ϰ�̩�巿������������������\n";
	$purpose .= "���� �㳲�Լ�Ω�ٱ�ˡ�˴�Ť����ξ㳲ʡ�㥵���ӥ�����\n";
	$purpose .= "��(1)������\n";
	$purpose .= "��(2)����ˬ����\n";
	$purpose .= "��(3)��ư���\n";
	$purpose .= "���� �㳲�Լ�Ω�ٱ�ˡ�˴�Ť��ϰ�����ٱ���Ȥΰ�ư�ٱ����\n";
	$purpose .= "���� ���̾���ι�Ҽ�ư�ֱ�������\n";
	$purpose .= "���� ���ƹ�����Ӥ�����ڤζ�̳\n";

	$purpose = mb_convert_kana($purpose, 'N');
} else {
	$i = 0;
	foreach ($inquiryInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
		if (!_IsNull($purposeInfo['cmp_pps_purpose'])) {
			$purpose .= (++$i).". ".$purposeInfo['cmp_pps_purpose']."\n";
		}
	}
	if (!_IsNull($purpose)) {
		$purpose = "����Ҥϡ����λ��Ȥ�Ĥळ�Ȥ���Ū�Ȥ��롣\n".$purpose;
		$purpose .= (++$i).". "."�嵭�ƹ�����Ӵ�Ϣ������ڤλ���\n";
		
		$purpose = mb_convert_kana($purpose, 'N');
	} else {
		$errorList[] = "����Ū�٤���Ͽ���Ƥ���������";
	}
}


//�ʲ��������ʤ�Ρ�
if (false) {

$purpose = null;
$i = 0;
foreach ($inquiryInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (!_IsNull($purposeInfo['cmp_pps_purpose'])) {
		$purpose .= (++$i).". ".$purposeInfo['cmp_pps_purpose']."\n";
	}
}
if (!_IsNull($purpose)) {
	$purpose = "����Ҥϡ����λ��Ȥ�Ĥळ�Ȥ���Ū�Ȥ��롣\n".$purpose;
	$purpose .= (++$i).". "."�嵭�ƹ�����Ӵ�Ϣ������ڤλ���\n";
	
	$purpose = mb_convert_kana($purpose, 'N');
} else {
	$errorList[] = "����Ū�٤���Ͽ���Ƥ���������";
}

}

$pdf->MultiCell(0,6,$purpose,$border,"L");
$pdf->Ln();


//����Ź�ν���ϡ�
$pdf->Cell(0,6,"����Ź�ν���ϡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$companyAddress = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_pref_id'])) $companyAddress .= $mstPrefList[$inquiryInfo['tbl_company']['cmp_pref_id']]['name'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_address1'])) $companyAddress .= $inquiryInfo['tbl_company']['cmp_address1'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_address2'])) $companyAddress .= $inquiryInfo['tbl_company']['cmp_address2'];
if (!_IsNull($companyAddress)) {
	$companyAddress = "����Ҥϡ���Ź��".$companyAddress."���֤���";

	$companyAddress = mb_convert_kana($companyAddress, 'N');
} else {
	$errorList[] = "����Ź����ϡ٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$companyAddress,$border,"L");
$pdf->Ln();


//�ʸ������ˡ��
$pdf->Cell(0,6,"�ʸ������ˡ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥθ���ϡ�����˷Ǻܤ�����ˡ�ˤ��Ԥ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//�裲�� �� ��
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裲�� �� ��",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//��ȯ�Բ�ǽ���������
$pdf->Cell(0,6,"��ȯ�Բ�ǽ���������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$stockTotalNum = null;
//if (!_IsNull($inquiryInfo['tbl_company']['cmp_stock_total_num_id'])) $stockTotalNum .= $mstStockTotalNumList[$inquiryInfo['tbl_company']['cmp_stock_total_num_id']]['name'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_stock_total_num'])) $stockTotalNum .= $inquiryInfo['tbl_company']['cmp_stock_total_num'];
if (!_IsNull($stockTotalNum)) {
//	$stockTotalNum = "����Ҥ�ȯ�Բ�ǽ����������ϡ�".$stockTotalNum."�Ȥ��롣";
	//��nn��nnnn���פΤ褦�˴�����ɽ���ˤ��롣
	$stockTotalNum = _ConvertNum2Ja($stockTotalNum);
	$stockTotalNum = "����Ҥ�ȯ�Բ�ǽ����������ϡ�".$stockTotalNum."���Ȥ��롣";
	
	$stockTotalNum = mb_convert_kana($stockTotalNum, 'N');
} else {
	$errorList[] = "��ȯ�Բ�ǽ��������٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$stockTotalNum,$border,"L");
$pdf->Ln();


//�ʳ����ξ������¡�
$pdf->Cell(0,6,"�ʳ����ξ������¡�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥ�ȯ�Ԥ�������Ϥ��٤ƾ������³����Ȥ����������Ϥˤ���������ˤϡ���ɽ������ξ�ǧ���פ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʳ�������ȯ�ԡ�
$pdf->Cell(0,6,"�ʳ�������ȯ�ԡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥγ����ˤĤ��Ƥϡ�������ȯ�Ԥ��ʤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʳ���̾���ܻ���ε������ϵ�Ͽ�������
$pdf->Cell(0,6,"�ʳ���̾���ܻ���ε������ϵ�Ͽ�������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "���������Ԥ�����̾���ܻ�������̾��˵������ϵ�Ͽ�򤹤뤳�Ȥ����᤹��ˤϡ�����ҽ���ν񼰤ˤ�������ˡ����μ������������γ���Ȥ��Ƴ���̾��˵��ܼ㤷���ϵ�Ͽ���줿�����Ϥ�����³�ͤ���¾�ΰ��̾��ѿ͵ڤӳ��������Ԥ���̾���ϵ�̾����������Ʊ���Ƥ��ʤ���Фʤ�ʤ��������������ˡ�ܹԵ�§�裲�����裱��ƹ��������ˤϡ����������Ԥ�ñ�Ȥ����᤹�뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʼ�������Ͽ�ڤӿ����⻺��ɽ����
$pdf->Cell(0,6,"�ʼ�������Ͽ�ڤӿ����⻺��ɽ����",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥγ����ˤĤ��Ƽ�������Ͽ���Ͽ����⻺��ɽ�������᤹��ˤϡ�����ҽ���ν񼰤ˤ�������������Ԥ���̾���ϵ�̾�������Ƥ��ʤ���Фʤ�ʤ���������Ͽ����ɽ�������äˤĤ��Ƥ�Ʊ�ͤȤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʼ������
$pdf->Cell(0,6,"�ʼ������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "���������������򤹤���ˤϡ�����ҽ���μ�������ʧ��ʤ���Фʤ�ʤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʴ������
$pdf->Cell(0,6,"�ʴ������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥϡ������ǯ�������κǽ��γ���̾��˵������ϵ�Ͽ���줿�ķ踢��ͭ���������äơ����λ���ǯ�٤˴ؤ�������������ˤ����Ƹ�����ԻȤ��뤳�Ȥ��Ǥ������Ȥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "����Τۤ�������������Ͽ���������ԤȤ��Ƹ�����ԻȤ��뤳�Ȥ��Ǥ���Ԥ���ꤹ�뤿��ɬ�פ�����Ȥ��ϡ����餫������𤷤��׻��˴���������뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʳ���ν��������ϽС�
$pdf->Cell(0,6,"�ʳ���ν��������ϽС�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥγ���ڤ���Ͽ�������������Ϥ���ˡ�������ͤ⤷������ɽ�Ԥϡ�����ҽ���ν񼰤ˤ�ꡢ���λ�̾������ڤӰ��դ�����Ҥ��Ϥ��Фʤ���Фʤ�ʤ����Ͻл�����ѹ����������Ȥ��⡢���λ���ˤĤ���Ʊ�ͤȤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//�裳�� �� �� �� ��
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裳�� �� �� �� ��",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//�ʾ�����
$pdf->Cell(0,6,"�ʾ�����",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����Ҥγ������ϡ�����������ڤ��׻��������Ȥ�������������������ǯ���������������飳�������˾��������׻���������ɬ�פ˱����ƾ������롣";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "�������򾷽�����Ȥ��ˤϡ������Σ��������ޤǤˤ������Τ�ȯ���롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʵķ踢�������Իȡ�
$pdf->Cell(0,6,"�ʵķ踢�������Իȡ�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "����ϡ�����Ҥεķ踢��ԻȤǤ���¾�γ���������ͤȤ��Ƥ��εķ踢��ԻȤ��뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "����ξ��ˤϡ��������������ͤϡ���������ڤ�����̤������񤴤Ȥ�����Ҥ���Ф��ʤ���Фʤ�ʤ���";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "�������������ͤ�����ν��̤���Ф��夨�ơ�ˡ�������Ȥ���ˤ������Ҥξ��������ơ���������ڤ�����̤˵��ܤ��٤�������ż�Ū��ˡ�ˤ���󶡤��뤳�Ȥ��Ǥ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʵ�Ĺ��
$pdf->Cell(0,6,"�ʵ�Ĺ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "�������ε�Ĺ�ϡ���Ĺ������ˤ����롣��Ĺ�˻��Τ�����Ȥ��ϡ��������ˤ����Ƥ��餫������᤿����ˤ��¾�μ����򤬵�Ĺ�Ȥʤ롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʷ�Ĥ���ˡ��
$pdf->Cell(0,6,"�ʷ�Ĥ���ˡ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "�������η�Ĥϡ�ˡ�������괾�����ʤ���᤬���������������ʤ����ķ踢�Τ������εķ踢�β�Ⱦ���ˤ�äƤ����褹�롣";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "���ˡ�裳�������裲������ˤ���Ĥϡ��ķ踢��ԻȤǤ������εķ踢�Σ�ʬ�Σ��ʾ��ͭ������礬���ʤ������εķ踢�Σ�ʬ�Σ��ʾ���äƤ����Ԥ���";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//�ʵĻ�Ͽ��
$pdf->Cell(0,6,"�ʵĻ�Ͽ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "�������εĻ��ˤĤ��Ƥϡ����ηв�����εڤӤ��η�̤򵭺����ϵ�Ͽ�����Ļ�Ͽ�����������Ĺ�ڤӽ��ʤ��������򤬤���˵�̾���������Żҽ�̾��Ԥ����������Ź�ˤ����ƣ���ǯ����¸�����ΤȤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);




//��������γ�ǧ
//������1�͏��10�ͤ���Ω���뤫��������3�͡��ƺ���1�ͤ���Ω���뤫��

//�������ե饰{true:�����������֤��롣/false:�����������֤��ʤ���}
$boardOfDirectorsFlag = false;
foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
	//"�ƺ���"�����뤫��
	if ($boardInfo['cmp_bod_post_id'] == MST_COMPANY_TYPE_POST_ID_CMP_INSPECTOR) {
		$boardOfDirectorsFlag = true;
	}
}

if ($boardOfDirectorsFlag) {
	//�����������֤�����

	//�裴�� �����򡢼��������ɽ������ڤӴƺ���
	$pdf->SetFontSize(12);
	$pdf->Cell(0,10,"�裴�� �����򡢼��������ɽ������ڤӴƺ���",$border,0,"C");
	$pdf->Ln(10);


	$pdf->SetFontSize(10);


	//�ʼ���������ֲ�ҡ�
	$pdf->Cell(0,6,"�ʼ���������ֲ�ҡ�",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "����Ҥˤϡ����������֤���";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//�ʼ�����ΰ�����
	$pdf->Cell(0,6,"�ʼ�����ΰ�����",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "����Ҥˤϡ�������̾�ʾ���֤���";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();
	

	//�ʴƺ������ֲ�ҡ�
	$pdf->Cell(0,6,"�ʴƺ������ֲ�ҡ�",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "����Ҥˤϴƺ�����֤������ΰ����ϣ�̾����Ȥ��롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//�ʼ�����ڤӴƺ������Ǥ��
	$pdf->Cell(0,6,"�ʼ�����ڤӴƺ������Ǥ��",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "����Ҥμ�����ڤӴƺ���ϳ������ˤ����������εķ踢�Σ�ʬ�Σ��ʾ��ͭ������礬���ʤ������εķ踢�β�Ⱦ���η�Ĥˤ�ä���Ǥ���롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
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
	
	$termYear = null;
	$termYear1 = null;
	$termYear2 = null;
	if (!_IsNull($inquiryInfo['tbl_company']['cmp_term_year'])) $termYear .= $inquiryInfo['tbl_company']['cmp_term_year'];
	if (!_IsNull($termYear)) {
		$termYear1 = "�������Ǥ���ϡ���Ǥ��".$termYear."ǯ����˽�λ����ǽ��λ���ǯ�٤˴ؤ�������������ν�����ޤǤȤ��롣";
		$termYear2 = "�ƺ����Ǥ���ϡ���Ǥ��".$termYear."ǯ����˽�λ����ǽ��λ���ǯ�٤˴ؤ�������������ν�����ޤǤȤ��롣";
		
		$termYear1 = mb_convert_kana($termYear1, 'N');
		$termYear2 = mb_convert_kana($termYear2, 'N');
	} else {
		$errorList[] = "�����Ǥ���٤���Ͽ���Ƥ���������";
	}
	$pdf->MultiCell(0,6,$termYear1,$border,"L");
	
	$pdf->Cell(20,6,"��������",$border,0,"L");
	
	$pdf->MultiCell(0,6,$termYear2,$border,"L");

	$pdf->Cell(20,6,"��������",$border,0,"L");
	
	$buf = "Ǥ����λ������Ǥ��������������Ȥ��ơ����������ˤ����Ǥ���줿�������Ǥ���ϡ���Ǥ������¾�κ�Ǥ�������Ǥ���λ�¸���֤�Ʊ��Ȥ��롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");

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
	
	$pdf->Cell(20,6,"��������",$border,0,"L");
	
	$buf = "�������Ĺ�˷�����ϻ��Τ�����Ȥ��ϡ��������ˤ�����ͽ����᤿����ǡ�¾�μ����򤬤�������롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
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
	
	$pdf->Cell(20,6,"��������",$border,0,"L");
	
	$buf = "��ɽ������ϲ�Ҥ���ɽ������Ҥζ�̳�򼹹Ԥ��롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"��������",$border,0,"L");
	
	$buf = "�������η�Ĥ��äƼ�������椫�顢��Ĺ��̾�����ꤷ��ɬ�פ˱����ơ�����������Ĺ����̳�����򡢾�̳������Ƽ㴳̾�����ꤹ�뤳�Ȥ��Ǥ��롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//��������
	$pdf->Cell(0,6,"��������",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "������ڤӴƺ�����󽷵ڤ��࿦��ϫ�����ϡ����줾��������η�Ĥ��ä����롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();
	
} else {
	//�����������֤��ʤ����

	
	//�裴�� ������ڤ���ɽ������
	$pdf->SetFontSize(12);
	$pdf->Cell(0,10,"�裴�� ������ڤ���ɽ������",$border,0,"C");
	$pdf->Ln(10);
	
	
	$pdf->SetFontSize(10);


	//�ʼ�����ΰ�����
	$pdf->Cell(0,6,"�ʼ�����ΰ�����",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "����Ҥˤϡ�������1̾�ʾ���֤���";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//�ʼ��������Ǥ��
	$pdf->Cell(0,6,"�ʼ��������Ǥ��",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "����Ҥμ�����ϡ�����Ҥγ�����椫��������ˤ�������Ǥ���롣â����ɬ�פ�����Ȥ��ϡ�����ʳ��μԤ�����Ǥ���뤳�Ȥ�˸���ʤ���";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"��������",$border,0,"L");
	
	$buf = "����Ҥμ�����ϡ��������ˤ����ơ��ķ踢��ԻȤǤ������εķ踢�Σ�ʬ�Σ��ʾ� �������������ͭ������礬���ʤ������εķ踢�β�Ⱦ���η�Ĥˤ�äƤ������Ǥ���롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"��������",$border,0,"L");
	
	$buf = "���������Ǥ�ϡ�������ɼ����ˡ�ˤ��ʤ���";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//�ʼ������Ǥ����
	$pdf->Cell(0,6,"�ʼ������Ǥ����",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$termYear = null;
	if (!_IsNull($inquiryInfo['tbl_company']['cmp_term_year'])) $termYear .= $inquiryInfo['tbl_company']['cmp_term_year'];
	if (!_IsNull($termYear)) {
		$termYear = "�������Ǥ���ϡ���Ǥ��".$termYear."ǯ����κǽ�����ǯ�٤˴ؤ�������������ν���λ��ޤǤȤ��롣";
		
		$termYear = mb_convert_kana($termYear, 'N');
	} else {
		$errorList[] = "�����Ǥ���٤���Ͽ���Ƥ���������";
	}
	$pdf->MultiCell(0,6,$termYear,$border,"L");
	
	$pdf->Cell(20,6,"��������",$border,0,"L");
	
	$buf = "���Ȥ������������ˤ����Ǥ���줿�������Ǥ���ϡ���Ǥ����������¾�κ�Ǥ�������Ǥ���ν�λ�λ��ޤǤȤ��롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//�ʼ�Ĺ�ڤ���ɽ�������
	$pdf->Cell(0,6,"�ʼ�Ĺ�ڤ���ɽ�������",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "����Ҥ˼�����̾�ʾ夢��Ȥ��ϡ�������θ����ˤ����ɽ������̾�����뤳�ȤȤ��롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"��������",$border,0,"L");
	
	$buf = "����Ҥ���ɽ���������ϼ�Ĺ�Ȥ��롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//��������
	$pdf->Cell(0,6,"��������",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "������������ϡ��������ˤ����Ƥ�������롣";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();
}


$pdf->Ln(10);


//�裵�� �� ��
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裵�� �� ��",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//�ʻ���ǯ�١�
$pdf->Cell(0,6,"�ʻ���ǯ�١�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$settleMonth = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_settle_month'])) $settleMonth .= $inquiryInfo['tbl_company']['cmp_settle_month'];
if (!_IsNull($settleMonth)) {
	$startMonth = 0;
	if ($settleMonth == 12) {
		$startMonth = 1;
	} else {
		$startMonth = $settleMonth + 1;
	}
	$settleMonth = "����Ҥλ���ǯ�٤ϡ���ǯ".$startMonth."���������ǯ".$settleMonth."�������ޤǤ�ǯ�����Ȥ��롣";
	
	$settleMonth = mb_convert_kana($settleMonth, 'N');
} else {
	$errorList[] = "�ط軻���٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$settleMonth,$border,"L");
$pdf->Ln();


//�ʾ�;���������
$pdf->Cell(0,6,"�ʾ�;���������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "��;��������ϡ������ǯ�٤��������ߤˤ�����ǽ��γ���̾��˵������ϵ�Ͽ���줿����������Ͽ���������Ԥ��Ф��ƻ�ʧ����";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$buf = "��;������������λ�ʧ�󶡤�����������ǯ��вᤷ�Ƥ���Τ���ʤ����ϡ�����ҤϤ��λ�ʧ�ε�̳���Ȥ���ΤȤ��롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//�裶�� �� §
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"�裶�� �� §",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//����Ω�˺ݤ���ȯ�Ԥ������������ 
$pdf->Cell(0,6,"����Ω�˺ݤ���ȯ�Ԥ������������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$totalStockNum = null;
$investment = null;
$investment4last = null;
foreach ($inquiryInfo['tbl_company_investment']['investment_info'] as $key => $investmentInfo) {
	if (_IsNull($investmentInfo['cmp_inv_name'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_pref_id'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_address1'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_stock_num'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_investment'])) continue;

	if (_IsNull($totalStockNum)) $totalStockNum = 0;
	$totalStockNum += $investmentInfo['cmp_inv_stock_num'];

	if (!_IsNull($investment)) $investment .= "\n";

	$investment .= "���� ";
	$investment .= $mstPrefList[$investmentInfo['cmp_inv_pref_id']]['name'];
	$investment .= $investmentInfo['cmp_inv_address1'];
	$investment .= $investmentInfo['cmp_inv_address2'];
	$investment .= "\n";
	$investment .= $investmentInfo['cmp_inv_stock_num'];
	$investment .= "�� ";
	$investment .= "��";
	$investment .= $investmentInfo['cmp_inv_investment'];
	$investment .= "���� ";
	$investment .= $investmentInfo['cmp_inv_name'];
	$investment .= "\n";


	if (!_IsNull($investment4last)) $investment4last .= "\n\n";

	$investment4last .= "ȯ���� ";
	$investment4last .= $investmentInfo['cmp_inv_name'];
}
if (!_IsNull($investment)) {
} else {
	$errorList[] = "�ؽл��ۡ٤���Ͽ���Ƥ���������";
}

$stockPrice = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_stock_price'])) $stockPrice .= $inquiryInfo['tbl_company']['cmp_stock_price'];
if (!_IsNull($stockPrice)) {
} else {
	$errorList[] = "��1����ñ���٤���Ͽ���Ƥ���������";
}

if (!_IsNull($totalStockNum) && !_IsNull($stockPrice)) {
	$totalStockNum = _ConvertNum2Ja($totalStockNum);
	$stockPrice = _ConvertNum2Ja($stockPrice);
	
	$totalStockNum = "����Ҥ���Ω�˺ݤ���ȯ�Ԥ����������".$totalStockNum."����1����ȯ�Բ��ۤ϶�".$stockPrice."�ߤȤ��롣";
	
	$totalStockNum = mb_convert_kana($totalStockNum, 'N');
}
$pdf->MultiCell(0,6,$totalStockNum,$border,"L");
$pdf->Ln();


//����Ω�˺ݤ��ƽл񤵤��⻺�β��۵ڤӻ��ܶ��
$pdf->Cell(0,6,"����Ω�˺ݤ��ƽл񤵤��⻺�β��۵ڤӻ��ܶ��",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$capital = null;
$capital1 = null;
$capital2 = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_capital'])) $capital .= $inquiryInfo['tbl_company']['cmp_capital'];
if (!_IsNull($capital)) {
	$capital1 = "����Ҥ���Ω�˺ݤ��л񤵤��⻺�β��ۤ϶�".$capital."���ߤȤ��롣";
	$capital2 = "����Ҥ���Ω��λ��ܶ�϶�".$capital."���ߤȤ��롣";
	
	$capital1 = mb_convert_kana($capital1, 'N');
	$capital2 = mb_convert_kana($capital2, 'N');
} else {
	$errorList[] = "�ػ��ܶ�٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$capital1,$border,"L");

$pdf->Cell(20,6,"��������",$border,0,"L");

$pdf->MultiCell(0,6,$capital2,$border,"L");
$pdf->Ln();


//�ʺǽ�λ���ǯ�١�
$pdf->Cell(0,6,"�ʺǽ�λ���ǯ�١�",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$settleMonth = null;
$foundYear = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_settle_month'])) $settleMonth .= $inquiryInfo['tbl_company']['cmp_settle_month'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_found_year'])) $foundYear .= $inquiryInfo['tbl_company']['cmp_found_year'];
if (!_IsNull($settleMonth) && !_IsNull($foundYear)) {
	//�����������Ѵ����롣(ʿ���Τ�)
	$nextYear = $foundYear + 1;
	$heiseiYear = _ConvertAD2Jp($nextYear);

	$settleMonth = "����Ҥκǽ�λ���ǯ�٤ϡ�����Ҥ���Ω�������顢".$heiseiYear."ǯ".$settleMonth."�������ޤǤȤ��롣";
	
	$settleMonth = mb_convert_kana($settleMonth, 'N');
} else {
	//�軻���ϡ��嵭�ǥ����å��Ѥߡ�
	//if (_IsNull($settleMonth)) $errorList[] = "�ط軻���٤���Ͽ���Ƥ���������";
	if (_IsNull($foundYear)) $errorList[] = "����Ωǯ�����٤���Ͽ���Ƥ���������";
}
$pdf->MultiCell(0,6,$settleMonth,$border,"L");
$pdf->Ln();


if ($boardOfDirectorsFlag) {
	//�����������֤�����

	//����Ω��������ڤ���Ω���ƺ����
	$pdf->Cell(0,6,"����Ω��������ڤ���Ω���ƺ����",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$board = null;
	//��ҥ�����_�򿦥ޥ����Ρ�ɽ����פν��ɽ�����롣
	foreach ($mstCompanyTypePostList as $key => $mstCompanyTypePostInfo) {
		$boardCmpDirector = null;
		foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
			if ($mstCompanyTypePostInfo['id'] == $boardInfo['cmp_bod_post_id']) {
				if (!_IsNull($boardInfo['cmp_bod_name'])) {
					$board .= "��Ω��".$mstCompanyTypePostInfo['name']." ".$boardInfo['cmp_bod_name']."\n";

					//"��ɽ������"�ξ�硢"������"�Ȥ��Ƥ�ɽ�����롣
					if ($mstCompanyTypePostInfo['id'] == MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR) {
						$boardCmpDirector .= "��Ω��".$mstCompanyTypePostList[MST_COMPANY_TYPE_POST_ID_CMP_DIRECTOR]['name']." ".$boardInfo['cmp_bod_name']."\n";
					}
				}
			}
		}
		//"��ɽ������"�ξ�硢"������"�Ȥ��Ƥ�ɽ�����롣
		if ($mstCompanyTypePostInfo['id'] == MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR) {
			if (!_IsNull($boardCmpDirector)) {
				$board .= $boardCmpDirector;
			}
		}
	}
	if (!_IsNull($board)) {
		$board = "����Ҥ���Ω��������ڤ���Ω���ƺ���ϡ����ΤȤ���Ȥ��롣\n".$board;
	} else {
		$errorList[] = "������٤���Ͽ���Ƥ���������";
	}
	$pdf->MultiCell(0,6,$board,$border,"L");
	$pdf->Ln();

} else {
	//�����������֤��ʤ����

	//����Ω��������ڤ���Ω����ɽ�������
	$pdf->Cell(0,6,"����Ω��������ڤ���Ω����ɽ�������",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
//	$board = null;
//	//��ҥ�����_�򿦥ޥ����Ρ�ɽ����פν��ɽ�����롣
//	foreach ($mstCompanyTypePostList as $key => $mstCompanyTypePostInfo) {
//		$boardCmpDirector = null;
//		foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
//			if ($mstCompanyTypePostInfo['id'] == $boardInfo['cmp_bod_post_id']) {
//				if (!_IsNull($boardInfo['cmp_bod_name'])) {
//					$board .= "��Ω��".$mstCompanyTypePostInfo['name']." ".$boardInfo['cmp_bod_name']."\n";
//					
//					//"��ɽ������"�ξ�硢"������"�Ȥ��Ƥ�ɽ�����롣
//					if ($mstCompanyTypePostInfo['id'] == MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR) {
//						$boardCmpDirector .= "��Ω��".$mstCompanyTypePostList[MST_COMPANY_TYPE_POST_ID_CMP_DIRECTOR]['name']." ".$boardInfo['cmp_bod_name']."\n";
//					}
//				}
//			}
//		}
//		//"��ɽ������"�ξ�硢"������"�Ȥ��Ƥ�ɽ�����롣
//		if ($mstCompanyTypePostInfo['id'] == MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR) {
//			if (!_IsNull($boardCmpDirector)) {
//				$board .= $boardCmpDirector;
//			}
//		}
//	}
	
	//(�������̤��硢"��ɽ������"��"������"�Τ�)
	//"������"��"��ɽ������"�ν��ɽ�����롣
	$board = null;
	$boardCmpRepDirector = null;
	//��ҥ�����_�򿦥ޥ����Ρ�ɽ����פν��ɽ�����롣
	foreach ($mstCompanyTypePostList as $key => $mstCompanyTypePostInfo) {
		foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
			if ($mstCompanyTypePostInfo['id'] == $boardInfo['cmp_bod_post_id']) {
				if (!_IsNull($boardInfo['cmp_bod_name'])) {
					
					//"��ɽ������"�ξ�硢"������"�Ȥ��Ƥ�ɽ�����롣
					if ($mstCompanyTypePostInfo['id'] == MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR) {
						$board .= "��Ω��".$mstCompanyTypePostList[MST_COMPANY_TYPE_POST_ID_CMP_DIRECTOR]['name']." ".$boardInfo['cmp_bod_name']."\n";
						
						$boardCmpRepDirector .= "��Ω��".$mstCompanyTypePostInfo['name']." ".$boardInfo['cmp_bod_name']."\n";
					} else {
						$board .= "��Ω��".$mstCompanyTypePostInfo['name']." ".$boardInfo['cmp_bod_name']."\n";
					}
				}
			}
		}
	}
	if (!_IsNull($boardCmpRepDirector)) {
		$board .= $boardCmpRepDirector;
	}

	if (!_IsNull($board)) {
		$board = "����Ҥ���Ω��������ڤ���Ω����ɽ������ϡ����ΤȤ���Ȥ��롣\n".$board;
	} else {
		$errorList[] = "������٤���Ͽ���Ƥ���������";
	}
	$pdf->MultiCell(0,6,$board,$border,"L");
	$pdf->Ln();

}


//��ȯ���ͤλ�̾������ڤӰ�����������
$pdf->Cell(0,6,"��ȯ���ͤλ�̾������ڤӰ�����������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

if (!_IsNull($investment)) {
	$investment = "ȯ���ͤλ�̾������ڤӰ��������������Ȱ�������ʧ����������γۤϼ��ΤȤ���Ǥ��롣\n".$investment;

	$investment = mb_convert_kana($investment, 'N');
}
$pdf->MultiCell(0,6,$investment,$border,"L");
$pdf->Ln();


//�����ʤ������
$pdf->Cell(0,6,"�����ʤ������",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "���괾�����Τʤ�����ˤĤ��Ƥϡ����Ʋ��ˡ����¾�δط�ˡ��ˤ�롣";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);





//���θ���
$justiceName = null;
$justiceName4last = null;
if (!_IsNull($inquiryInfo['tbl_inquiry']['inq_justice_id'])) {
	//�ζȾ����������롣
	$condition4jst = array();
	$condition4jst['jst_justice_id'] = $inquiryInfo['tbl_inquiry']['inq_justice_id'];//�ζ�ID
	$tblJusticeInfo = _DB_GetInfo('tbl_justice', $condition4jst, true, 'jst_del_flag');
	if (!_IsNull($tblJusticeInfo)) {
		if (!_IsNull($tblJusticeInfo['jst_name'])) {
			if (!_IsNull($tblJusticeInfo['jst_justice_type_id'])) {
				if (!_IsNull($mstJusticeTypeList[$tblJusticeInfo['jst_justice_type_id']]['name'])) {
					$justiceName .= $mstJusticeTypeList[$tblJusticeInfo['jst_justice_type_id']]['name'];
					$justiceName .= " ";
					$justiceName .= $tblJusticeInfo['jst_name'];
				}
			}
		}
	}
}

//�ʲ��ϡ����̤ʤ�Ρ���礻ID=19�ѡ�
$buf = null;
if ($inquiryId == 19) {
	if (_IsNull($justiceName)) {
		//�ζȤ�̤����ξ��
		$buf .= "�ʾ塢";
		$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
		$buf .= "��Ω�ΰ٤ˡ�";
		$buf .= "�����괾���������ȯ���ͤ����˵�̾�������롣";
	} else {
		//�ζȤ�����Ѥξ��
		$buf .= "�ʾ塢";
		$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
//		$buf .= "��Ω�ΰ٤ˡ�ȯ���ͤ��괾���������ͤǤ���";
		$buf .= "��Ω�ΰ٤�ȯ���ͤ��괾���������ͤǤ���";
		$buf .= $justiceName;
		$buf .= "�ϡ��ż�Ū��Ͽ�Ǥ������괾���������������Żҽ�̾���롣";
		
		
		$justiceName4last .= "�嵭ȯ���ͤ��괾����������";
		$justiceName4last .= "\n";
		$justiceName4last .= $justiceName;
	}
} else {
	if (_IsNull($justiceName)) {
		//�ζȤ�̤����ξ��
		$buf .= "�ʾ塢";
		$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
		$buf .= "��Ω�ΰ٤ˡ�";
		$buf .= "�����괾���������ȯ���ͤ����˵�̾�������롣";
	} else {
		//�ζȤ�����Ѥξ��
		$buf .= "�ʾ塢";
		$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
		$buf .= "��Ω�ΰ٤ˡ�ȯ���ͤ��괾���������ͤǤ���";
		$buf .= $justiceName;
		$buf .= "�ϡ��ż�Ū��Ͽ�Ǥ������괾���������������Żҽ�̾���롣";
		
		
		$justiceName4last .= "�嵭ȯ���ͤ��괾����������";
		$justiceName4last .= "\n";
		$justiceName4last .= $justiceName;
	}
}



//�ʲ��������ʤ�Ρ�
if (false) {

$buf = null;
if (_IsNull($justiceName)) {
	//�ζȤ�̤����ξ��
	$buf .= "�ʾ塢";
	$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
	$buf .= "��Ω�ΰ٤ˡ�";
	$buf .= "�����괾���������ȯ���ͤ����˵�̾�������롣";
} else {
	//�ζȤ�����Ѥξ��
	$buf .= "�ʾ塢";
	$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
	$buf .= "��Ω�ΰ٤ˡ�ȯ���ͤ��괾���������ͤǤ���";
	$buf .= $justiceName;
	$buf .= "�ϡ��ż�Ū��Ͽ�Ǥ������괾���������������Żҽ�̾���롣";
	
	
	$justiceName4last .= "�嵭ȯ���ͤ��괾����������";
	$justiceName4last .= "\n";
	$justiceName4last .= $justiceName;
}

}

$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//������
$buf = null;
$buf .= _ConvertAD2Jp($pdfCreateYear);
$buf .= "ǯ";
$buf .= $pdfCreateMonth;
$buf .= "��";
$buf .= $pdfCreateDay;
$buf .= "��";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);

//ȯ����
$pdf->Cell(10,6,"",$border,0,"L");
$pdf->MultiCell(0,6,$investment4last,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


if (!_IsNull($justiceName4last)) {
	$pdf->Cell(10,6,"",$border,0,"L");
	$pdf->MultiCell(0,6,$justiceName4last,$border,"L");
	$pdf->Ln();
	
	$pdf->Ln(10);
}


//DB�򥯥������롣
_DB_Close($link);


if (count($errorList) > 0) {
	//���顼ͭ�ξ��

	//PDF��λ���롣
	$pdf->Close();

	_Log("[/pdf/create/teikan.php] end. ERR!");


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

	_Log("[/pdf/create/teikan.php] end. OK!");
}





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
