<?php
/*
 * [管理画面]
 * PDF作成
 * 委任状
 *
 * 更新履歴：2008/11/05	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");
include_once("../../common/libs/fpdf/mbfpdf.php");


_LogDelete();
//_LogBackup();
_Log("[/pdf/create/ininjo.php] start.");

_Log("[/pdf/create/ininjo.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/create/ininjo.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/create/ininjo.php] SERVER = '".print_r($_SERVER,true)."'");


//認証チェック----------------------------------------------------------------------start
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/create/ininjo.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/pdf/create/ininjo.php] end.");
	//ログイン画面を表示する。
	header("Location: ".URL_BASE);
	exit;
}
//ログイン情報を取得する。
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
//認証チェック----------------------------------------------------------------------end



//DBをオープンする。
$link = _DB_Open();

//エラーメッセージ
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

//問合せID
$inquiryId = (isset($inData['id'])?$inData['id']:null);

//作成日
$pdfCreateYear = (isset($inData['year'])?$inData['year']:date('Y'));
$pdfCreateMonth = (isset($inData['month'])?$inData['month']:date('n'));
$pdfCreateDay = (isset($inData['day'])?$inData['day']:date('j'));



//権限処理追加
switch ($loginInfo['mng_auth_id']) {
	case AUTH_NON:
		//権限無し
		
		//ユーザーIDから問合せ情報を検索する。→問合せIDを取得する。
		if (isset($loginInfo['tbl_user'])) {
			$condition4inq = array();
			$condition4inq['inq_user_id'] = $loginInfo['tbl_user']['usr_user_id'];	//顧客ID
			$tblInquiryList = _DB_GetList('tbl_inquiry', $condition4inq, true, null, 'inq_del_flag');
			if (!_IsNull($tblInquiryList)) {
				//配列の先頭から要素を一つ取り出す
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
	//問合せ情報を取得する。
	$inquiryInfo = _GetInquiryInfo($inquiryId, false);
}

if (_IsNull($inquiryInfo)) {
	$errorList[] = "※該当の問合せ情報が存在しません。";

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//エラー画面を表示する。
	header("Location: ../error.php");
	exit;
}

//マスタ情報を取得する。
$undeleteOnly = false;
$mstPrefList = _GetMasterList('mst_pref', $undeleteOnly);						//都道府県マスタ
//$mstStockTotalNumList = _GetMasterList('mst_stock_total_num', $undeleteOnly);	//発行可能株式総数マスタ
//$mstStockPriceList = _GetMasterList('mst_stock_price', $undeleteOnly);			//1株の単価マスタ
$mstJusticeTypeList = _GetMasterList('mst_justice_type', $undeleteOnly);		//士業タイプマスタ

////会社タイプ_役職マスタ
//$condition4mst = array();
//$condition4mst['company_type_id'] = $inquiryInfo['tbl_company']['cmp_company_type_id'];//会社タイプID
//$order4mst = "lpad(show_order,10,'0'),id";
//$mstCompanyTypePostList = _DB_GetList('mst_company_type_post', $condition4mst, true, $order4mst, 'del_flag', 'id');




//定数--------------------------------------------start
//フォントサイズを定義する。
//通常
$normalFontSize = 10;

//タイトル
$title = "委任状";


//[デバッグ用]
//ボーダー
$border = 0;
//定数--------------------------------------------end


// EUC-JP->SJIS 変換を自動的に行なわせる場合に mbfpdf.php 内の $EUC2SJIS を
// true に修正するか、このように実行時に true に設定しても変換してくます。
//$GLOBALS['EUC2SJIS'] = true;

//PDFのサイズを設定する。デフォルト=FPDF($orientation='P',$unit='mm',$format='A4')
$pdf=new MBFPDF();

//フォントを設定する。
$pdf->AddMBFont(GOTHIC ,'SJIS');
$pdf->AddMBFont(PGOTHIC,'SJIS');
$pdf->AddMBFont(MINCHO ,'SJIS');
$pdf->AddMBFont(PMINCHO,'SJIS');
$pdf->AddMBFont(KOZMIN ,'SJIS');

//マージンを設定する。
$pdf->SetLeftMargin(20);
$pdf->SetRightMargin(20);
$pdf->SetTopMargin(20);


$pdf->SetFont(MINCHO,'',$normalFontSize);

//自動改ページモードをON(true)、ページの下端からの距離（マージン）が2 mmになった場合、改行するように設定する。
$pdf->SetAutoPageBreak(true, 20);

//ドキュメントのタイトルを設定する。
$pdf->SetTitle($title);
//ドキュメントの主題(subject)を設定する。
$pdf->SetSubject($title);



$pdf->AddPage();


$pdf->Ln(30);


//タイトル
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


$pdf->SetFontSize(10);


//士業
$justiceName = null;
if (!_IsNull($inquiryInfo['tbl_inquiry']['inq_justice_id'])) {
	//士業情報を取得する。
	$condition4jst = array();
	$condition4jst['jst_justice_id'] = $inquiryInfo['tbl_inquiry']['inq_justice_id'];//士業ID
	$tblJusticeInfo = _DB_GetInfo('tbl_justice', $condition4jst, true, 'jst_del_flag');
	if (!_IsNull($tblJusticeInfo)) {
		$justiceName .= $mstJusticeTypeList[$tblJusticeInfo['jst_justice_type_id']]['name'];
		$justiceName .= " ";
		$justiceName .= $tblJusticeInfo['jst_name'];
		$justiceName .= " ";
		$justiceName .= "（事務所所在地：";
		$justiceName .= $mstPrefList[$tblJusticeInfo['jst_pref_id']]['name'];
		$justiceName .= $tblJusticeInfo['jst_address1'];
		$justiceName .= $tblJusticeInfo['jst_address2'];
		$justiceName .= "）";
		
		$justiceName .= "\n";
	}
}
$buf = null;
if (!_IsNull($justiceName)) {
	$buf .= "私は、";
	$buf .= $justiceName;
	$buf .= "を代理人と定め、下記権限を委任する。";
	
	$buf = mb_convert_kana($buf, 'N');
} else {
	$errorList[] = "『士業者』を登録してください。";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(20);


$pdf->SetFontSize(13);

$buf = "記";
$pdf->Cell(0,6,$buf,$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(10);

//会社名・法人名
$companyName = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name'])) $companyName .= $inquiryInfo['tbl_company']['cmp_company_name'];
$buf = null;
if (!_IsNull($companyName)) {
	$buf .= $companyName;
	$buf .= "の設立に関し、添付のとおり電磁的記録であるその原始定款を作成する手続等に関する一切の件。";
} else {
	$errorList[] = "『会社名・法人名』を登録してください。";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(20);


//作成日
$buf = null;
$buf .= _ConvertAD2Jp($pdfCreateYear);
$buf .= "年";
$buf .= $pdfCreateMonth;
$buf .= "月";
$buf .= $pdfCreateDay;
$buf .= "日";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//発起人
$investment = null;
foreach ($inquiryInfo['tbl_company_investment']['investment_info'] as $key => $investmentInfo) {
	if (_IsNull($investmentInfo['cmp_inv_name'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_pref_id'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_address1'])) continue;

	if (!_IsNull($investment)) $investment .= "\n";

	$investment .= "住所 ";
	$investment .= $mstPrefList[$investmentInfo['cmp_inv_pref_id']]['name'];
	$investment .= $investmentInfo['cmp_inv_address1'];
	$investment .= $investmentInfo['cmp_inv_address2'];
	$investment .= "\n";
	$investment .= $investmentInfo['cmp_inv_stock_num'];
	$investment .= "株 ";
	$investment .= "金";
	$investment .= $investmentInfo['cmp_inv_investment'];
	$investment .= "万円 ";
	$investment .= $investmentInfo['cmp_inv_name'];
	$investment .= "\n";


	$buf = null;
	$buf .= "発起人 住所";
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = null;
	$buf .= $mstPrefList[$investmentInfo['cmp_inv_pref_id']]['name'];
	$buf .= $investmentInfo['cmp_inv_address1'];
	$buf .= $investmentInfo['cmp_inv_address2'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$buf = null;
	$buf .= "氏名又は名称";
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = null;
	$buf .= $investmentInfo['cmp_inv_name'];
	$pdf->Cell(100,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= "印";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln(15);
}
if (!_IsNull($investment)) {
} else {
	$errorList[] = "『出資金額』を登録してください。";
}


//DBをクローズする。
_DB_Close($link);


if (count($errorList) > 0) {
	//エラー有の場合

	//PDFを終了する。
	$pdf->Close();

	_Log("[/pdf/create/ininjo.php] end. ERR!");


	$buf = "※PDFを作成するための情報が足りません。[問合せ情報]画面で、情報を入力してください。";
	array_unshift($errorList, $buf);
	
	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//エラー画面を表示する。
	header("Location: ../error.php");
	exit;

} else {
	//エラー無の場合

	//PDFを出力する。
	$pdf->Output();

	_Log("[/pdf/create/ininjo.php] end. OK!");
}



?>
