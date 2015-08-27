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
include_once("../../common/libs/fpdf/mbfpdf.php");


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
	$stockTotalNum = "当会社の発行可能株式の総数は、".$stockTotalNum."株とする。";
	
	$stockTotalNum = mb_convert_kana($stockTotalNum, 'N');
} else {
	$errorList[] = "『発行可能株式総数』を登録してください。";
}
$pdf->MultiCell(0,6,$stockTotalNum,$border,"L");
$pdf->Ln();


//（株式の譲渡制限）
$pdf->Cell(0,6,"（株式の譲渡制限）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社の発行する株式はすべて譲渡制限株式とし、これを譲渡により取得するには、代表取締役の承認を要する。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（株券の不発行）
$pdf->Cell(0,6,"（株券の不発行）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社の株式については、株券を発行しない。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（株主名簿記載事項の記載又は記録の請求）
$pdf->Cell(0,6,"（株主名簿記載事項の記載又は記録の請求）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "株式取得者が株主名簿記載事項を株主名簿に記載又は記録をすることを請求するには、当会社所定の書式による請求書に、その取得した株式の株主として株主名簿に記載若しくは記録された者又はその相続人その他の一般承継人及び株式取得者が署名又は記名押印し、共同してしなければならない。ただし、会社法施行規則第２２条第１項各号に定める場合には，株式取得者が単独で請求することができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（質権の登録及び信託財産の表示）
$pdf->Cell(0,6,"（質権の登録及び信託財産の表示）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社の株式について質権の登録又は信託財産の表示を請求するには、当会社所定の書式による請求書に当事者が署名又は記名押印してしなければならない。その登録又は表示の抹消についても同様とする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（手数料）
$pdf->Cell(0,6,"（手数料）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "前２条に定める請求をする場合には、当会社所定の手数料を支払わなければならない。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（基準日）
$pdf->Cell(0,6,"（基準日）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社は、毎事業年度末日の最終の株主名簿に記載又は記録された議決権を有する株主をもって、その事業年度に関する定時株主総会において権利を行使することができる株主とする。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "前項のほか、株主又は登録株式質権者として権利を行使することができる者を確定するため必要があるときは、あらかじめ公告して臨時に基準日を定めることができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（株主の住所等の届出）
$pdf->Cell(0,6,"（株主の住所等の届出）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社の株主及び登録株式質権者又はその法定代理人もしくは代表者は、当会社所定の書式により、その氏名、住所及び印鑑を当会社に届け出なければならない。届出事項に変更を生じたときも、その事項につき、同様とする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//第３章 株 主 総 会
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第３章 株 主 総 会",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//（招集）
$pdf->Cell(0,6,"（招集）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社の株主総会は、定時株主総会及び臨時株主総会とし、定時株主総会は毎事業年度末日の翌日から３カ月以内に招集し、臨時株主総会は必要に応じて招集する。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "株主総会を招集するときには、会日の１週間前までにその通知を発する。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（議決権の代理行使）
$pdf->Cell(0,6,"（議決権の代理行使）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "株主は、当会社の議決権を行使できる他の株主を代理人としてその議決権を行使することができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "前項の場合には、株主又は代理人は、代理権を証する書面を株主総会ごとに当会社に提出しなければならない。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　３",$border,0,"L");

$buf = "株主又は代理人は前項の書面の提出に代えて、法令に定めるところにより当会社の承諾を得て、代理権を証する書面に記載すべき事項を電磁的方法により提供することができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（議長）
$pdf->Cell(0,6,"（議長）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "株主総会の議長は、社長がこれにあたる。社長に事故があるときは、株主総会においてあらかじめ定めた順序により他の取締役が議長となる。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（決議の方法）
$pdf->Cell(0,6,"（決議の方法）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "株主総会の決議は、法令又は定款に別段の定めがある場合を除き、出席した議決権のある株主の議決権の過半数によってこれを決する。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "会社法第３０９条第２項の定めによる決議は、議決権を行使できる株主の議決権の３分の１以上を有する株主が出席し、その議決権の３分の２以上をもってこれを行う。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（議事録）
$pdf->Cell(0,6,"（議事録）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "株主総会の議事については、その経過の要領及びその結果を記載又は記録した議事録を作成し、議長及び出席した取締役がこれに記名押印又は電子署名を行い、当会社本店において１０年間保存するものとする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);




//役員構成の確認
//取締役1人~10人で設立するか、取締役3人・監査役1人で設立するか？

//取締役会フラグ{true:取締役会を設置する。/false:取締役会を設置しない。}
$boardOfDirectorsFlag = false;
foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
	//"監査役"がいるか？
	if ($boardInfo['cmp_bod_post_id'] == MST_COMPANY_TYPE_POST_ID_CMP_INSPECTOR) {
		$boardOfDirectorsFlag = true;
	}
}

if ($boardOfDirectorsFlag) {
	//取締役会を設置する場合

	//第４章 取締役、取締役会、代表取締役及び監査役
	$pdf->SetFontSize(12);
	$pdf->Cell(0,10,"第４章 取締役、取締役会、代表取締役及び監査役",$border,0,"C");
	$pdf->Ln(10);


	$pdf->SetFontSize(10);


	//（取締役会設置会社）
	$pdf->Cell(0,6,"（取締役会設置会社）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "当会社には、取締役会を置く。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//（取締役の員数）
	$pdf->Cell(0,6,"（取締役の員数）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "当会社には、取締役３名以上を置く。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();
	

	//（監査役設置会社）
	$pdf->Cell(0,6,"（監査役設置会社）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "当会社には監査役を置き、その員数は３名以内とする。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//（取締役及び監査役の選任）
	$pdf->Cell(0,6,"（取締役及び監査役の選任）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "当会社の取締役及び監査役は株主総会において総株主の議決権の３分の１以上を有する株主が出席し、その議決権の過半数の決議によって選任する。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"　　　２",$border,0,"L");
	
	$buf = "取締役の選任については、累積投票によらない。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();
	

	//（取締役の解任）
	$pdf->Cell(0,6,"（取締役の解任）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "取締役の解任決議は、議決権を行使することができる株主の議決権の過半数を有する株主が出席し、その議決権の３分の２以上をもって行う。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//（取締役及び監査役の任期）
	$pdf->Cell(0,6,"（取締役及び監査役の任期）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$termYear = null;
	$termYear1 = null;
	$termYear2 = null;
	if (!_IsNull($inquiryInfo['tbl_company']['cmp_term_year'])) $termYear .= $inquiryInfo['tbl_company']['cmp_term_year'];
	if (!_IsNull($termYear)) {
		$termYear1 = "取締役の任期は、選任後".$termYear."年以内に終了する最終の事業年度に関する定時株主総会の終結時までとする。";
		$termYear2 = "監査役の任期は、選任後".$termYear."年以内に終了する最終の事業年度に関する定時株主総会の終結時までとする。";
		
		$termYear1 = mb_convert_kana($termYear1, 'N');
		$termYear2 = mb_convert_kana($termYear2, 'N');
	} else {
		$errorList[] = "『役員任期』を登録してください。";
	}
	$pdf->MultiCell(0,6,$termYear1,$border,"L");
	
	$pdf->Cell(20,6,"　　　２",$border,0,"L");
	
	$pdf->MultiCell(0,6,$termYear2,$border,"L");

	$pdf->Cell(20,6,"　　　３",$border,0,"L");
	
	$buf = "任期満了前に退任した取締役の補欠として、又は増員により選任された取締役の任期は、前任者又は他の在任取締役の任期の残存期間と同一とする。";
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$pdf->Cell(20,6,"　　　４",$border,0,"L");
	
	$buf = "任期満了前に退任した監査役の補欠として選任された監査役の任期は前任者の任期の残存期間と同一とする。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//（取締役会の招集及び議長）
	$pdf->Cell(0,6,"（取締役会の招集及び議長）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "取締役会は、法令に別段の定めがある場合を除き、取締役社長が招集し、議長となる。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"　　　２",$border,0,"L");
	
	$buf = "取締役社長に欠員又は事故があるときは、取締役会において予め定めた順序で、他の取締役がこれに代わる。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"　　　３",$border,0,"L");
	
	$buf = "取締役会の招集通知は、会日の３日前までに各取締役及び各監査役に対して発する。ただし取締役及び監査役の全員の同意があるときは、招集の手続を経ないで取締役会を開くことができる。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//（代表取締役及び役付取締役）
	$pdf->Cell(0,6,"（代表取締役及び役付取締役）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "代表取締役は、取締役会の決議によって選定する。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"　　　２",$border,0,"L");
	
	$buf = "代表取締役は会社を代表し、会社の業務を執行する。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"　　　３",$border,0,"L");
	
	$buf = "取締役会の決議をもって取締役の中から、社長１名を選定し、必要に応じて、取締役副社長、専務取締役、常務取締役各若干名を選定することができる。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//（報酬等）
	$pdf->Cell(0,6,"（報酬等）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "取締役及び監査役の報酬及び退職慰労金等は、それぞれ株主総会の決議をもって定める。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();
	
} else {
	//取締役会を設置しない場合

	
	//第４章 取締役及び代表取締役
	$pdf->SetFontSize(12);
	$pdf->Cell(0,10,"第４章 取締役及び代表取締役",$border,0,"C");
	$pdf->Ln(10);
	
	
	$pdf->SetFontSize(10);


	//（取締役の員数）
	$pdf->Cell(0,6,"（取締役の員数）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "当会社には、取締役1名以上を置く。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//（取締役の選任）
	$pdf->Cell(0,6,"（取締役の選任）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "当会社の取締役は、当会社の株主の中から株主総会において選任する。但し、必要があるときは、株主以外の者から選任することを妨げない。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"　　　２",$border,0,"L");
	
	$buf = "当会社の取締役は、株主総会において、議決権を行使できる株主の議決権の３分の１以上 に当たる株式を有する株主が出席し、その議決権の過半数の決議によってこれを選任する。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"　　　３",$border,0,"L");
	
	$buf = "取締役の選任は、累積投票の方法によらない。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//（取締役の任期）
	$pdf->Cell(0,6,"（取締役の任期）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$termYear = null;
	if (!_IsNull($inquiryInfo['tbl_company']['cmp_term_year'])) $termYear .= $inquiryInfo['tbl_company']['cmp_term_year'];
	if (!_IsNull($termYear)) {
		$termYear = "取締役の任期は、選任後".$termYear."年以内の最終事業年度に関する定時株主総会の終結の時までとする。";
		
		$termYear = mb_convert_kana($termYear, 'N');
	} else {
		$errorList[] = "『役員任期』を登録してください。";
	}
	$pdf->MultiCell(0,6,$termYear,$border,"L");
	
	$pdf->Cell(20,6,"　　　２",$border,0,"L");
	
	$buf = "補欠として又は増員により選任された取締役の任期は、前任取締役又は他の在任取締役の任期の終了の時までとする。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//（社長及び代表取締役）
	$pdf->Cell(0,6,"（社長及び代表取締役）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "当会社に取締役２名以上あるときは、取締役の互選により代表取締役１名を定めることとする。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$pdf->Cell(20,6,"　　　２",$border,0,"L");
	
	$buf = "当会社を代表する取締役は社長とする。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();


	//（報酬等）
	$pdf->Cell(0,6,"（報酬等）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$buf = "取締役の報酬等は、株主総会においてこれを定める。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();
}


$pdf->Ln(10);


//第５章 計 算
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第５章 計 算",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//（事業年度）
$pdf->Cell(0,6,"（事業年度）",$border,0,"L");
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
	$settleMonth = "当会社の事業年度は、毎年".$startMonth."月１日から翌年".$settleMonth."月末日までの年１期とする。";
	
	$settleMonth = mb_convert_kana($settleMonth, 'N');
} else {
	$errorList[] = "『決算日』を登録してください。";
}
$pdf->MultiCell(0,6,$settleMonth,$border,"L");
$pdf->Ln();


//（剰余金の配当）
$pdf->Cell(0,6,"（剰余金の配当）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "剰余金の配当は、毎事業年度の末日現在における最終の株主名簿に記載又は記録された株主又は登録株式質権者に対して支払う。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "剰余金の配当がその支払提供の日から満３年を経過しても受領されない時は、当会社はその支払の義務を免れるものとする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//第６章 附 則
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第６章 附 則",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//（設立に際して発行する株式数等） 
$pdf->Cell(0,6,"（設立に際して発行する株式数等）",$border,0,"L");
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


	if (!_IsNull($investment4last)) $investment4last .= "\n\n";

	$investment4last .= "発起人 ";
	$investment4last .= $investmentInfo['cmp_inv_name'];
}
if (!_IsNull($investment)) {
} else {
	$errorList[] = "『出資金額』を登録してください。";
}

$stockPrice = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_stock_price'])) $stockPrice .= $inquiryInfo['tbl_company']['cmp_stock_price'];
if (!_IsNull($stockPrice)) {
} else {
	$errorList[] = "『1株の単価』を登録してください。";
}

if (!_IsNull($totalStockNum) && !_IsNull($stockPrice)) {
	$totalStockNum = _ConvertNum2Ja($totalStockNum);
	$stockPrice = _ConvertNum2Ja($stockPrice);
	
	$totalStockNum = "当会社が設立に際して発行する株式数は".$totalStockNum."株、1株の発行価額は金".$stockPrice."円とする。";
	
	$totalStockNum = mb_convert_kana($totalStockNum, 'N');
}
$pdf->MultiCell(0,6,$totalStockNum,$border,"L");
$pdf->Ln();


//（設立に際して出資される財産の価額及び資本金）
$pdf->Cell(0,6,"（設立に際して出資される財産の価額及び資本金）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$capital = null;
$capital1 = null;
$capital2 = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_capital'])) $capital .= $inquiryInfo['tbl_company']['cmp_capital'];
if (!_IsNull($capital)) {
	$capital1 = "当会社の設立に際し出資される財産の価額は金".$capital."万円とする。";
	$capital2 = "当会社の成立後の資本金は金".$capital."万円とする。";
	
	$capital1 = mb_convert_kana($capital1, 'N');
	$capital2 = mb_convert_kana($capital2, 'N');
} else {
	$errorList[] = "『資本金』を登録してください。";
}
$pdf->MultiCell(0,6,$capital1,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$pdf->MultiCell(0,6,$capital2,$border,"L");
$pdf->Ln();


//（最初の事業年度）
$pdf->Cell(0,6,"（最初の事業年度）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$settleMonth = null;
$foundYear = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_settle_month'])) $settleMonth .= $inquiryInfo['tbl_company']['cmp_settle_month'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_found_year'])) $foundYear .= $inquiryInfo['tbl_company']['cmp_found_year'];
if (!_IsNull($settleMonth) && !_IsNull($foundYear)) {
	//西暦を和暦に変換する。(平成のみ)
	$nextYear = $foundYear + 1;
	$heiseiYear = _ConvertAD2Jp($nextYear);

	$settleMonth = "当会社の最初の事業年度は、当会社の設立の日から、".$heiseiYear."年".$settleMonth."月末日までとする。";
	
	$settleMonth = mb_convert_kana($settleMonth, 'N');
} else {
	//決算日は、上記でチェック済み。
	//if (_IsNull($settleMonth)) $errorList[] = "『決算日』を登録してください。";
	if (_IsNull($foundYear)) $errorList[] = "『設立年月日』を登録してください。";
}
$pdf->MultiCell(0,6,$settleMonth,$border,"L");
$pdf->Ln();


if ($boardOfDirectorsFlag) {
	//取締役会を設置する場合

	//（設立時取締役及び設立時監査役）
	$pdf->Cell(0,6,"（設立時取締役及び設立時監査役）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
	$board = null;
	//会社タイプ_役職マスタの「表示順」の順で表示する。
	foreach ($mstCompanyTypePostList as $key => $mstCompanyTypePostInfo) {
		$boardCmpDirector = null;
		foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
			if ($mstCompanyTypePostInfo['id'] == $boardInfo['cmp_bod_post_id']) {
				if (!_IsNull($boardInfo['cmp_bod_name'])) {
					$board .= "設立時".$mstCompanyTypePostInfo['name']." ".$boardInfo['cmp_bod_name']."\n";

					//"代表取締役"の場合、"取締役"としても表示する。
					if ($mstCompanyTypePostInfo['id'] == MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR) {
						$boardCmpDirector .= "設立時".$mstCompanyTypePostList[MST_COMPANY_TYPE_POST_ID_CMP_DIRECTOR]['name']." ".$boardInfo['cmp_bod_name']."\n";
					}
				}
			}
		}
		//"代表取締役"の場合、"取締役"としても表示する。
		if ($mstCompanyTypePostInfo['id'] == MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR) {
			if (!_IsNull($boardCmpDirector)) {
				$board .= $boardCmpDirector;
			}
		}
	}
	if (!_IsNull($board)) {
		$board = "当会社の設立時取締役及び設立時監査役は、次のとおりとする。\n".$board;
	} else {
		$errorList[] = "『役員』を登録してください。";
	}
	$pdf->MultiCell(0,6,$board,$border,"L");
	$pdf->Ln();

} else {
	//取締役会を設置しない場合

	//（設立時取締役及び設立時代表取締役）
	$pdf->Cell(0,6,"（設立時取締役及び設立時代表取締役）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");
	
//	$board = null;
//	//会社タイプ_役職マスタの「表示順」の順で表示する。
//	foreach ($mstCompanyTypePostList as $key => $mstCompanyTypePostInfo) {
//		$boardCmpDirector = null;
//		foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
//			if ($mstCompanyTypePostInfo['id'] == $boardInfo['cmp_bod_post_id']) {
//				if (!_IsNull($boardInfo['cmp_bod_name'])) {
//					$board .= "設立時".$mstCompanyTypePostInfo['name']." ".$boardInfo['cmp_bod_name']."\n";
//					
//					//"代表取締役"の場合、"取締役"としても表示する。
//					if ($mstCompanyTypePostInfo['id'] == MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR) {
//						$boardCmpDirector .= "設立時".$mstCompanyTypePostList[MST_COMPANY_TYPE_POST_ID_CMP_DIRECTOR]['name']." ".$boardInfo['cmp_bod_name']."\n";
//					}
//				}
//			}
//		}
//		//"代表取締役"の場合、"取締役"としても表示する。
//		if ($mstCompanyTypePostInfo['id'] == MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR) {
//			if (!_IsNull($boardCmpDirector)) {
//				$board .= $boardCmpDirector;
//			}
//		}
//	}
	
	//(ここを通る場合、"代表取締役"と"取締役"のみ)
	//"取締役"→"代表取締役"の順で表示する。
	$board = null;
	$boardCmpRepDirector = null;
	//会社タイプ_役職マスタの「表示順」の順で表示する。
	foreach ($mstCompanyTypePostList as $key => $mstCompanyTypePostInfo) {
		foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
			if ($mstCompanyTypePostInfo['id'] == $boardInfo['cmp_bod_post_id']) {
				if (!_IsNull($boardInfo['cmp_bod_name'])) {
					
					//"代表取締役"の場合、"取締役"としても表示する。
					if ($mstCompanyTypePostInfo['id'] == MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR) {
						$board .= "設立時".$mstCompanyTypePostList[MST_COMPANY_TYPE_POST_ID_CMP_DIRECTOR]['name']." ".$boardInfo['cmp_bod_name']."\n";
						
						$boardCmpRepDirector .= "設立時".$mstCompanyTypePostInfo['name']." ".$boardInfo['cmp_bod_name']."\n";
					} else {
						$board .= "設立時".$mstCompanyTypePostInfo['name']." ".$boardInfo['cmp_bod_name']."\n";
					}
				}
			}
		}
	}
	if (!_IsNull($boardCmpRepDirector)) {
		$board .= $boardCmpRepDirector;
	}

	if (!_IsNull($board)) {
		$board = "当会社の設立時取締役及び設立時代表取締役は、次のとおりとする。\n".$board;
	} else {
		$errorList[] = "『役員』を登録してください。";
	}
	$pdf->MultiCell(0,6,$board,$border,"L");
	$pdf->Ln();

}


//（発起人の氏名、住所及び引受株数等）
$pdf->Cell(0,6,"（発起人の氏名、住所及び引受株数等）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

if (!_IsNull($investment)) {
	$investment = "発起人の氏名、住所及び引受株数、株式と引換えに払い込む金銭の額は次のとおりである。\n".$investment;

	$investment = mb_convert_kana($investment, 'N');
}
$pdf->MultiCell(0,6,$investment,$border,"L");
$pdf->Ln();


//（定めなき事項）
$pdf->Cell(0,6,"（定めなき事項）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "本定款に定めのない事項については、全て会社法その他の関係法令による。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);





//〆の言葉
$justiceName = null;
$justiceName4last = null;
if (!_IsNull($inquiryInfo['tbl_inquiry']['inq_justice_id'])) {
	//士業情報を取得する。
	$condition4jst = array();
	$condition4jst['jst_justice_id'] = $inquiryInfo['tbl_inquiry']['inq_justice_id'];//士業ID
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

//以下は、特別なもの。問合せID=19用。
$buf = null;
if ($inquiryId == 19) {
	if (_IsNull($justiceName)) {
		//士業が未設定の場合
		$buf .= "以上、";
		$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
		$buf .= "設立の為に、";
		$buf .= "この定款を作成し、発起人が次に記名押印する。";
	} else {
		//士業が設定済の場合
		$buf .= "以上、";
		$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
//		$buf .= "設立の為に、発起人の定款作成代理人である";
		$buf .= "設立の為に発起人の定款作成代理人である";
		$buf .= $justiceName;
		$buf .= "は、電磁的記録である本定款を作成し、これに電子署名する。";
		
		
		$justiceName4last .= "上記発起人の定款作成代理人";
		$justiceName4last .= "\n";
		$justiceName4last .= $justiceName;
	}
} else {
	if (_IsNull($justiceName)) {
		//士業が未設定の場合
		$buf .= "以上、";
		$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
		$buf .= "設立の為に、";
		$buf .= "この定款を作成し、発起人が次に記名押印する。";
	} else {
		//士業が設定済の場合
		$buf .= "以上、";
		$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
		$buf .= "設立の為に、発起人の定款作成代理人である";
		$buf .= $justiceName;
		$buf .= "は、電磁的記録である本定款を作成し、これに電子署名する。";
		
		
		$justiceName4last .= "上記発起人の定款作成代理人";
		$justiceName4last .= "\n";
		$justiceName4last .= $justiceName;
	}
}



//以下が正式なもの。
if (false) {

$buf = null;
if (_IsNull($justiceName)) {
	//士業が未設定の場合
	$buf .= "以上、";
	$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
	$buf .= "設立の為に、";
	$buf .= "この定款を作成し、発起人が次に記名押印する。";
} else {
	//士業が設定済の場合
	$buf .= "以上、";
	$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
	$buf .= "設立の為に、発起人の定款作成代理人である";
	$buf .= $justiceName;
	$buf .= "は、電磁的記録である本定款を作成し、これに電子署名する。";
	
	
	$justiceName4last .= "上記発起人の定款作成代理人";
	$justiceName4last .= "\n";
	$justiceName4last .= $justiceName;
}

}

$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


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


//DBをクローズする。
_DB_Close($link);


if (count($errorList) > 0) {
	//エラー有の場合

	//PDFを終了する。
	$pdf->Close();

	_Log("[/pdf/create/teikan.php] end. ERR!");


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
	return mb_convert_kana("第".$no."条", 'N');
}

?>
