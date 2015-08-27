<?php
/*
 * [管理画面]
 * PDF作成
 * 定款(合同会社用)
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
_Log("[/pdf/create/teikan_llc.php] start.");

_Log("[/pdf/create/teikan_llc.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/create/teikan_llc.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/create/teikan_llc.php] SERVER = '".print_r($_SERVER,true)."'");


//認証チェック----------------------------------------------------------------------start
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/create/teikan_llc.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/pdf/create/teikan_llc.php] end.");
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
//会社タイプ_業務マスタ
$condition4mst = array();
$condition4mst['company_type_id'] = $inquiryInfo['tbl_company']['cmp_company_type_id'];//会社タイプID
$order4mst = "lpad(show_order,10,'0'),id";
$mstCompanyTypeDutiesList = _DB_GetList('mst_company_type_duties', $condition4mst, true, $order4mst, 'del_flag', 'id');




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


//第２章 社員及び出資
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第２章 社員及び出資",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//（社員の氏名及び住所、出資及び責任） 
$pdf->Cell(0,6,"（社員の氏名及び住所、出資及び責任）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$totalStockNum = null;
$investment = null;
$investment4last = null;
foreach ($inquiryInfo['tbl_company_investment']['investment_info'] as $key => $investmentInfo) {
	if (_IsNull($investmentInfo['cmp_inv_name'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_pref_id'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_address1'])) continue;
//	if (_IsNull($investmentInfo['cmp_inv_stock_num'])) continue;
	if (_IsNull($investmentInfo['cmp_inv_investment'])) continue;

//	if (_IsNull($totalStockNum)) $totalStockNum = 0;
//	$totalStockNum += $investmentInfo['cmp_inv_stock_num'];

	if (!_IsNull($investment)) $investment .= "\n";

	$investment .= "金";
	$investment .= $investmentInfo['cmp_inv_investment'];
	$investment .= "万円 ";
	$investment .= "\n";

	$investment .= $mstPrefList[$investmentInfo['cmp_inv_pref_id']]['name'];
	$investment .= $investmentInfo['cmp_inv_address1'];
	$investment .= $investmentInfo['cmp_inv_address2'];
	$investment .= "\n";

	$investment .= "有限責任社員    ";
	$investment .= $investmentInfo['cmp_inv_name'];
	$investment .= "\n";

//	$investment .= $investmentInfo['cmp_inv_stock_num'];
//	$investment .= "株 ";

	if (!_IsNull($investment4last)) $investment4last .= "\n\n";

	$investment4last .= "有限責任社員    ";
	$investment4last .= $investmentInfo['cmp_inv_name'];
}
if (!_IsNull($investment)) {
	$investment = "社員の氏名及び住所、出資の価額及び責任は次のとおりである。\n".$investment;

	$investment = mb_convert_kana($investment, 'N');
} else {
	$errorList[] = "『出資金額』を登録してください。";
}
$pdf->MultiCell(0,6,$investment,$border,"L");
$pdf->Ln();


//（持分の譲渡）
$pdf->Cell(0,6,"（持分の譲渡）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "社員は、総社員の承諾がなければ、その持分の全部又は一部を他人に譲渡することができない。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "前項の規定にかかわらず、当会社の業務を執行しない社員がその持分の全部又は一部を他人に譲渡するには、業務執行社員の全員の承諾を得なければならない。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//第３章 業務の執行、業務執行社員及び代表社員
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第３章 業務の執行、業務執行社員及び代表社員",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//（業務執行の権利、業務執行社員の選任及び解任）
$pdf->Cell(0,6,"（業務執行の権利、業務執行社員の選任及び解任）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社の業務は、業務執行社員が執行するものとし、総社員の同意により、社員の中からこれを選任する。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "業務執行社員は、他の社員の請求がある時は、いつでもその職務の執行の状況を報告し、その職務が終了した後は、遅滞なくその経過及び結果を報告しなければならない。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　３",$border,0,"L");

$buf = "業務執行社員は、総社員の同意により解任することができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（代表社員）
$pdf->Cell(0,6,"（代表社員）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "業務執行社員が２名以上いる場合、業務執行社員の互選をもって、代表社員を１名以上定めることができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "代表社員は、会社を代表する。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（業務執行社員及び代表社員の報酬等）
$pdf->Cell(0,6,"（業務執行社員及び代表社員の報酬等）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "業務執行社員及び代表社員の報酬等は、社員の過半数の同意をもって定める。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//第４章 社員の加入及び退社
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第４章 社員の加入及び退社",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//（社員の加入）
$pdf->Cell(0,6,"（社員の加入）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "新たに社員を加入させる場合は、総社員の同意を要する。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（新加入社員の責任）
$pdf->Cell(0,6,"（新加入社員の責任）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社の設立後に加入した社員は、その加入前に生じた会社の債務についても責任を負うものとする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（任意退社）
$pdf->Cell(0,6,"（任意退社）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "各社員は、事業年度の終了の時において退社をすることができる。この場合、各社員は６ヶ月前までに会社に退社の予告をしなければならない。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "各社員は、前項の規定にかかわらず、やむを得ない事由があるときは、いつでも退社することができる。この場合、各社員は２ヶ月前までに会社に退社の予告をしなければならない。ただし、会社に不利な時期に退社する場合は、会社に対して損害を賠償する責任を負う。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（法定退社）
$pdf->Cell(0,6,"（法定退社）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "各社員は、会社法第６０７条の規定により退社する。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "前項の規定にかかわらず、社員が死亡した場合又は合併により消滅した場合における当該社員の相続人又はその他一般承継人が当該社員の持分を承継するものとする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//第５章 社員の除名
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第５章 社員の除名",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//（社員の除名）
$pdf->Cell(0,6,"（社員の除名）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社は、業務を執行するに当たって不正の行為をし又は業務を執行する権利がないのに業務の執行に関与した場合、総社員の同意をもって社員を除名することができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "前項の規定にかかわらず、正当な理由がある場合に総社員の同意をもって社員を除名することができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln(10);


//第６章 計 算
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第６章 計 算",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


//（営業年度）
$pdf->Cell(0,6,"（営業年度）",$border,0,"L");
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


//（利益の配当）
$pdf->Cell(0,6,"（利益の配当）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "利益の配当は、毎事業年度の末日現在の社員に分配する。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（損益分配の割合）
$pdf->Cell(0,6,"（損益分配の割合）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "各社員の損益分配の割合は、総社員の同意により、出資の価額と異なる割合によることができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//第７章 附 則
$pdf->SetFontSize(12);
$pdf->Cell(0,10,"第７章 附 則",$border,0,"C");
$pdf->Ln(10);


$pdf->SetFontSize(10);


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


//（設立に際する資本金）
$pdf->Cell(0,6,"（設立に際する資本金）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$capital = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_capital'])) $capital .= $inquiryInfo['tbl_company']['cmp_capital'];
if (!_IsNull($capital)) {
	$capital = "当会社の設立時の資本金は、金".$capital."万円とする。";
	
	$capital = mb_convert_kana($capital, 'N');
} else {
	$errorList[] = "『資本金』を登録してください。";
}
$pdf->MultiCell(0,6,$capital,$border,"L");
$pdf->Ln();


//（設立時業務執行社員）
$pdf->Cell(0,6,"（設立時業務執行社員）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$board1 = null;
$board2 = null;
foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
	//業務D="業務執行社員"の場合
	if ($boardInfo['cmp_bod_duties_id'] == MST_COMPANY_TYPE_DUTIES_ID_LLC_EXECUTION) {
		if (!_IsNull($boardInfo['cmp_bod_name'])) {
			$board1 .= $mstCompanyTypeDutiesList[$boardInfo['cmp_bod_duties_id']]['value'];
			$board1 .= "    ";
			$board1 .= $boardInfo['cmp_bod_name'];
			$board1 .= "\n";
		}
	}

	//役職ID="代表社員"の場合
	if ($boardInfo['cmp_bod_post_id'] == MST_COMPANY_TYPE_POST_ID_LLC_REP_STAFF) {
		$errFlag = true;
		if (_IsNull($boardInfo['cmp_bod_name'])) $errFlag = false;
		if (_IsNull($boardInfo['cmp_bod_pref_id'])) $errFlag = false;
		if (_IsNull($boardInfo['cmp_bod_address1'])) $errFlag = false;
		if ($errFlag) {
			if (!_IsNull($board2)) $board2 .= "\n";
			$board2 .= "住所";
			$board2 .= " ";
			$board2 .= $mstPrefList[$boardInfo['cmp_bod_pref_id']]['name'];
			$board2 .= $boardInfo['cmp_bod_address1'];
			$board2 .= $boardInfo['cmp_bod_address2'];
			$board2 .= "\n";
			
			$board2 .= $mstCompanyTypePostList[$boardInfo['cmp_bod_post_id']]['name'];
			$board2 .= "    ";
			$board2 .= $boardInfo['cmp_bod_name'];
			$board2 .= "\n";
		}
	}
}
if (!_IsNull($board1)) {
	$board1 = "当会社の設立時業務執行社員は、次のとおりとする。\n".$board1;
} else {
	$errorList[] = "『役員-業務執行社員』を登録してください。";
}
if (!_IsNull($board2)) {
	$board2 = "当会社の設立時代表社員は、次のとおりとする。\n".$board2;

	$board2 = mb_convert_kana($board2, 'N');
} else {
	$errorList[] = "『役員-代表社員』を登録してください。";
}
$pdf->MultiCell(0,6,$board1,$border,"L");
$pdf->Ln();


//（設立時代表社員）
$pdf->Cell(0,6,"（設立時代表社員）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$pdf->MultiCell(0,6,$board2,$border,"L");
$pdf->Ln();


//（定款の変更）
$pdf->Cell(0,6,"（定款の変更）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社の定款の変更は、社員の過半数の同意によるものとする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（定款に定めのない事項）
$pdf->Cell(0,6,"（定款に定めのない事項）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "本定款に定めのない事項は、すべて会社法の規定による。";
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
$buf = null;
if (_IsNull($justiceName)) {
	//士業が未設定の場合
	$buf .= "以上、";
	$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
	$buf .= "設立の為に、";
	$buf .= "この定款を作成し、有限責任社員が次に記名押印する。";
} else {
	//士業が設定済の場合
	$buf .= "以上、";
	$buf .= $inquiryInfo['tbl_company']['cmp_company_name'];
	$buf .= "設立の為に、有限責任社員の定款作成代理人である";
	$buf .= $justiceName;
	$buf .= "は、電磁的記録である本定款を作成し、これに電子署名する。";
	
	
	$justiceName4last .= "上記有限責任社員の定款作成代理人";
	$justiceName4last .= "\n";
	$justiceName4last .= $justiceName;
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

	_Log("[/pdf/create/teikan_llc.php] end. ERR!");


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

	_Log("[/pdf/create/teikan_llc.php] end. OK!");
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
