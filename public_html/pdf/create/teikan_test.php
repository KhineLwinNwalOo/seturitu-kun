<?php
/*
 * [管理画面]
 * PDF作成
 * 定款
 *
 * 更新履歴：2008/11/05	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");
//include_once("../../common/libs/fpdf/mbfpdf.php");
include_once("../../common/libs/fpdf/mbfpdf_test.php");


_LogDelete();
//_LogBackup();
_Log("[/pdf/create/teikan.php] start.");

_Log("[/pdf/create/teikan.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/create/teikan.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/create/teikan.php] SERVER = '".print_r($_SERVER,true)."'");


//認証チェック----------------------------------------------------------------------start
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/create/teikan.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/pdf/create/teikan.php] end.");
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
$mstStockPriceList = _GetMasterList('mst_stock_price', $undeleteOnly);			//1株の単価マスタ
$mstJusticeTypeList = _GetMasterList('mst_justice_type', $undeleteOnly);		//士業タイプマスタ

//会社タイプ_役職マスタ
$condition4mst = array();
$condition4mst['company_type_id'] = $inquiryInfo['tbl_company']['cmp_company_type_id'];//会社タイプID
$order4mst = "lpad(show_order,10,'0'),id";
$mstCompanyTypePostList = _DB_GetList('mst_company_type_post', $condition4mst, true, $order4mst, 'del_flag', 'id');




//定数--------------------------------------------start
//フォントサイズを定義する。
//通常
$normalFontSize = 10;

//タイトル
$title = "定款";


//[デバッグ用]
//ボーダー
$border = 1;
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

//会社名
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$inquiryInfo['tbl_company']['cmp_company_name'],$border,0,"C");
$pdf->Ln(20);


//タイトル
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


//第１章 総 則
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第１章 総 則",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);



//test start
$pdf->Cell(0,6,$pdf->CurrentFont['type']."/1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890",$border,0,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345。67890123456789012345678901234567890",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234。567890123456789012345678901234567890",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４。５。６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３。４。５。６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"当会社の発行する株式はすべて譲渡制限株式とし、これを譲渡により取得するには、代表取締役の。承。認。を。要する。",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"当会社の発行する株式はすべて譲渡制限株式とし、これを譲渡により取得するああああああああああには、代表取締役の承認を要する。",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち、あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち、あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち、あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち、あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち。あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち。あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち。あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち。。あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち。。あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち。。あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそた。ち。。あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそた。ち。。あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそた。ち。。あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち",$border,"L");
$pdf->Ln(10);
$pdf->MultiCell(0,6,"あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち。。、」）・？あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち。。、」）・？あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち。。、」）・？あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたちあいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほあいうえおかきくけこさしすせそたち",$border,"L");
$pdf->Ln(10);
//test end


$no = 0;

//（商号）
$pdf->Cell(0,6,"（商号）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$companyName = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name'])) $companyName .= $inquiryInfo['tbl_company']['cmp_company_name'];
$companyNameEn = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name_en'])) $companyNameEn .= $inquiryInfo['tbl_company']['cmp_company_name_en'];

$buf = null;
if (!_IsNull($companyName)) {
	$buf .= "当会社は、";
	$buf .= $companyName;
	if (!_IsNull($companyNameEn)) {
		$buf .= "（";
		$buf .= "英文名「";
		$buf .= $companyNameEn;
		$buf .= "」";
		$buf .= "）";
	}
	$buf .= "と称する。";
} else {
	$errorList[] = "『会社名・法人名』を登録してください。";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（目的）
$pdf->Cell(0,6,"（目的）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");


//以下は、特別なもの。問合せID=19用。
$purpose = null;
if ($inquiryId == 19) {
	$purpose .= "１、 介護保険法に基づく次の居宅サービス事業及び介護予防サービス事業\n";
	$purpose .= "　(1)訪問介護及び介護予防訪問介護\n";
	$purpose .= "　(2)訪問入浴介護及び介護予防訪問入浴介護\n";
	$purpose .= "　(3)訪問看護及び介護予防訪問看護\n";
	$purpose .= "　(4)通所介護及び介護予防通所介護\n";
	$purpose .= "　(5)短期入所生活介護及び介護予防短期入所生活介護\n";
	$purpose .= "　(6)特定施設入居者生活介護及び介護予防特定施設入居者生活介護\n";
	$purpose .= "　(7)福祉用具貸与及び介護予防福祉用具貸与\n";
	$purpose .= "　(8)特定福祉用具販売及び特定介護予防福祉用具販売\n";
	$purpose .= "２、 介護保険法に基づく居宅介護支援事業\n";
	$purpose .= "３、 介護保険法に基づく地域密着型サービス及び地域密着型介護予防サービス\n";
	$purpose .= "　(1)夜問対応型訪問介護\n";
	$purpose .= "　(2)認知症対応型通所介護及び介護予防認知症対応型通所介護\n";
	$purpose .= "　(3)小規模多機能型居宅介護及び介護予防小規模多機能型居宅介護\n";
	$purpose .= "　(4)認知症対応型共同生活介護及び介護予防認知症対応型共同生活介護\n";
	$purpose .= "　(5)地域密着型特定施設入居者生活介護\n";
	$purpose .= "４、 障害者自立支援法に基づく次の障害福祉サービス事業\n";
	$purpose .= "　(1)居宅介護\n";
	$purpose .= "　(2)重度訪問介護\n";
	$purpose .= "　(3)行動援護\n";
	$purpose .= "５、 障害者自立支援法に基づく地域生活支援事業の移動支援事業\n";
	$purpose .= "６、 一般乗用旅客自動車運送事業\n";
	$purpose .= "７、 前各号に付帯する一切の業務\n";

	$purpose = mb_convert_kana($purpose, 'N');
} else {
	$i = 0;
	foreach ($inquiryInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
		if (!_IsNull($purposeInfo['cmp_pps_purpose'])) {
			$purpose .= (++$i).". ".$purposeInfo['cmp_pps_purpose']."\n";
		}
	}
	if (!_IsNull($purpose)) {
		$purpose = "当会社は、次の事業を営むことを目的とする。\n".$purpose;
		$purpose .= (++$i).". "."上記各号に付帯関連する一切の事業\n";
		
		$purpose = mb_convert_kana($purpose, 'N');
	} else {
		$errorList[] = "『目的』を登録してください。";
	}
}


//以下が正式なもの。
if (false) {

$purpose = null;
$i = 0;
foreach ($inquiryInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (!_IsNull($purposeInfo['cmp_pps_purpose'])) {
		$purpose .= (++$i).". ".$purposeInfo['cmp_pps_purpose']."\n";
	}
}
if (!_IsNull($purpose)) {
	$purpose = "当会社は、次の事業を営むことを目的とする。\n".$purpose;
	$purpose .= (++$i).". "."上記各号に付帯関連する一切の事業\n";
	
	$purpose = mb_convert_kana($purpose, 'N');
} else {
	$errorList[] = "『目的』を登録してください。";
}

}

$pdf->MultiCell(0,6,$purpose,$border,"L");
$pdf->Ln();


//（本店の所在地）
$pdf->Cell(0,6,"（本店の所在地）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$companyAddress = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_pref_id'])) $companyAddress .= $mstPrefList[$inquiryInfo['tbl_company']['cmp_pref_id']]['name'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_address1'])) $companyAddress .= $inquiryInfo['tbl_company']['cmp_address1'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_address2'])) $companyAddress .= $inquiryInfo['tbl_company']['cmp_address2'];
if (!_IsNull($companyAddress)) {
	$companyAddress = "当会社は、本店を".$companyAddress."に置く。";

	$companyAddress = mb_convert_kana($companyAddress, 'N');
} else {
	$errorList[] = "『本店所在地』を登録してください。";
}
$pdf->MultiCell(0,6,$companyAddress,$border,"L");
$pdf->Ln();


//（公告の方法）
$pdf->Cell(0,6,"（公告の方法）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社の公告は、官報に掲載する方法により行う。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//第２章 株 式
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第２章 株 式",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//（発行可能株式総数）
$pdf->Cell(0,6,"（発行可能株式総数）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$stockTotalNum = null;
//if (!_IsNull($inquiryInfo['tbl_company']['cmp_stock_total_num_id'])) $stockTotalNum .= $mstStockTotalNumList[$inquiryInfo['tbl_company']['cmp_stock_total_num_id']]['name'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_stock_total_num'])) $stockTotalNum .= $inquiryInfo['tbl_company']['cmp_stock_total_num'];
if (!_IsNull($stockTotalNum)) {
//	$stockTotalNum = "当会社の発行可能株式の総数は、".$stockTotalNum."とする。";
	//「nn億nnnn万」のように漢字の表記にする。
	$stockTotalNum = _ConvertNum2Ja($stockTotalNum);
	$