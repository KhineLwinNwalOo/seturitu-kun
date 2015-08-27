<?php
/*
 * [新★会社設立.JP ツール]
 * PDF作成
 * 定款
 *
 * 更新履歴：2008/12/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
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


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/user/index.php] end.");
	//ログイン画面を表示する。
	header("Location: ".URL_LOGIN);
	exit;
} else {
	//ログイン情報を取得する。
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
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

//ユーザーID
$userId = (isset($inData['user_id'])?$inData['user_id']:null);
//会社ID
$companyId = (isset($inData['company_id'])?$inData['company_id']:null);

//作成日
$pdfCreateYear = ((isset($inData['create_year']) && !_IsNull($inData['create_year']))?$inData['create_year']:date('Y'));
$pdfCreateMonth = ((isset($inData['create_month']) && !_IsNull($inData['create_month']))?$inData['create_month']:date('n'));
$pdfCreateDay = ((isset($inData['create_day']) && !_IsNull($inData['create_day']))?$inData['create_day']:date('j'));



//初期値を設定する。
$undeleteOnly4def = false;

//権限によって、表示するユーザー情報を制限する。
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://権限無し

		_Log("[/user/company/pdf/create/teikan.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
		_Log("[/user/company/pdf/create/teikan.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
		_Log("[/user/company/pdf/create/teikan.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

		$undeleteOnly4def = true;

		//自分のユーザー情報、会社情報のみ表示する。
		//ユーザーID、会社IDをチェックする。

		//会社IDを検索する。
		$relationCompanyId = _GetRelationCompanyId($loginInfo['usr_user_id']);


		_Log("[/user/company/pdf/create/teikan.php] {ログインユーザー権限処理} →(ログイン)ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/create/teikan.php] {ログインユーザー権限処理} →(ログイン)会社ID = '".$relationCompanyId."'");
		_Log("[/user/company/pdf/create/teikan.php] {ログインユーザー権限処理} →(パラメーター)ユーザーID = '".$userId."'");
		_Log("[/user/company/pdf/create/teikan.php] {ログインユーザー権限処理} →(パラメーター)会社ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/company/pdf/create/teikan.php] {ログインユーザー権限処理} →(処理対象)ユーザーID = '".$userId."'");
		_Log("[/user/company/pdf/create/teikan.php] {ログインユーザー権限処理} →(処理対象)会社ID = '".$companyId."'");

		break;
}

//入金チェック
if (!_IsNull($companyId)) {
	if (!_CheckUserStatus($userId, $companyId, MST_SYSTEM_COURSE_ID_CMP)) {
		$errorList[] = "※申し訳ございません。書類の作成(印刷)は、ご利用料金の決済後にご利用が可能となります。";
		$_SESSION[SID_PDF_ERR_MSG] = $errorList;
		//エラー画面を表示する。
		header("Location: ../error.php");
		exit;
	}
}

$companyInfo = null;
if (!_IsNull($companyId)) {
	//会社情報を取得する。
	$companyInfo = _GetCompanyInfo($companyId, $undeleteOnly4def);
}

if (_IsNull($companyInfo)) {
	$errorList[] = "※該当の会社情報が存在しません。";

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//エラー画面を表示する。
	header("Location: ../error.php");
	exit;
}

//登録情報をチェックする。
//会社名
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "『商号(会社名)』を登録してください。";
//目的
$errFlag = true;
foreach ($companyInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (!_IsNull($purposeInfo['cmp_pps_purpose'])) {
		$errFlag = false;
		break;
	}
}
if ($errFlag) $errorList[] = "『目的』を登録してください。";
//本店所在地
$errFlag = false;
if (_IsNull($companyInfo['tbl_company']['cmp_pref_id'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_address1'])) $errFlag = true;
if ($errFlag) $errorList[] = "『本店所在地』を登録してください。";
//発行可能株式の総数
if (_IsNull($companyInfo['tbl_company']['cmp_stock_total_num'])) $errorList[] = "『発行可能株式の総数』を登録してください。";
//役員構成
if (_IsNull($companyInfo['tbl_company']['cmp_board_formation_id'])) $errorList[] = "『役員構成』を登録してください。";
//取締役人数
if (_IsNull($companyInfo['tbl_company']['cmp_director_num'])) {
	$errorList[] = "『取締役人数』を登録してください。";
} else {
	if ($companyInfo['tbl_company']['cmp_director_num'] < 1) $errorList[] = "『取締役人数』を登録してください。(1人以上)";
}
//取締役・監査役の任期
if (_IsNull($companyInfo['tbl_company']['cmp_term_year'])) $errorList[] = "『取締役の任期』を登録してください。";
//監査役の任期
if (_IsNull($companyInfo['tbl_company']['cmp_inspector_term_year'])) $errorList[] = "『監査役の任期』を登録してください。";
//事業年度
if (_IsNull($companyInfo['tbl_company']['cmp_business_start_month'])) $errorList[] = "『事業年度』を登録してください。";
//1株の単価
if (_IsNull($companyInfo['tbl_company']['cmp_stock_price'])) $errorList[] = "『1株の単価』を登録してください。";
//資本金
if (_IsNull($companyInfo['tbl_company']['cmp_capital'])) $errorList[] = "『資本金』を登録してください。";
//設立年月日
$errFlag = false;
if (_IsNull($companyInfo['tbl_company']['cmp_found_year'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_found_month'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_found_day'])) $errFlag = true;
if ($errFlag) $errorList[] = "『設立年月日』を登録してください。";
//役員
$errFlag = false;
foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
	if (_IsNull($boardInfo['cmp_bod_family_name']) || _IsNull($boardInfo['cmp_bod_first_name'])) {
		$errFlag = true;
		break;
	}
}
if ($errFlag) $errorList[] = "『役員』の『お名前』を登録してください。";
//発起人
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
if ($errFlag) $errorList[] = "『発起人』の『お名前』、『住所』を登録してください。";
//出資金
$errFlag = false;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	//出資金の登録はあるか？
	if (!isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$errFlag = true;
		break;
	}
	$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

	//出資形態(現金のみ・現物出資のみ・現金＋現物)によって、現金・現物の登録はあるか？
	switch ($promoterInfo['cmp_prm_investment_shape_id']) {
		case MST_INVESTMENT_SHAPE_ID_CASH:
			//現金のみ
			//出資タイプマスタ="現金"の登録はあるか？
			if (!isset($investmentList[MST_INVESTMENT_TYPE_ID_CASH])) {
				$errFlag = true;
				break 2;
			}
			break;
		case MST_INVESTMENT_SHAPE_ID_INKIND:
			//現物出資のみ
			//出資タイプマスタ="現物"の登録はあるか？
			if (!isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
				$errFlag = true;
				break 2;
			}
			break;
		case MST_INVESTMENT_SHAPE_ID_CASH_INKIND:
			//現金＋現物
			//出資タイプマスタ="現金"の登録はあるか？
			if (!isset($investmentList[MST_INVESTMENT_TYPE_ID_CASH])) {
				$errFlag = true;
				break 2;
			}
			//出資タイプマスタ="現物"の登録はあるか？
			if (!isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
				$errFlag = true;
				break 2;
			}
			break;
	}

	//現金・現物の株数、現物出資の品名の登録はあるか？
	foreach ($investmentList as $investmentTypeId => $investmentTypeList) {
		foreach ($investmentTypeList['investment_info'] as $itKey => $investmentInfo) {
			//株数
			if (_IsNull($investmentInfo['cmp_prm_inv_stock_num'])) {
				$errFlag = true;
				break 3;
			}

			//出資タイプマスタによってチェックする。
			switch ($investmentTypeId) {
				case MST_INVESTMENT_TYPE_ID_INKIND:
					//現物出資の品名
					if (_IsNull($investmentInfo['cmp_prm_inv_in_kind'])) {
						$errFlag = true;
						break 4;
					}
					break;
			}
		}
	}
}
if ($errFlag) $errorList[] = "『出資金』の『株数』、『現物出資の品名』を登録してください。";



if (count($errorList) > 0) {
	//エラー有の場合
	_Log("[/user/company/pdf/create/teikan.php] end. ERR!");


	$buf = "※PDFを作成するための情報が足りません。『株式会社設立情報登録』画面で、情報を入力してください。";
	array_unshift($errorList, $buf);

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//エラー画面を表示する。
	header("Location: ../error.php");
	exit;
}


//定款作成日を登録する。
$updInfo = array();
$updInfo['tbl_company']['cmp_company_id'] = $companyId;										//会社ID
$updInfo['tbl_company']['cmp_article_create_year'] = $pdfCreateYear;						//定款作成日(年)
$updInfo['tbl_company']['cmp_article_create_month'] = $pdfCreateMonth;						//定款作成日(月)
$updInfo['tbl_company']['cmp_article_create_day'] = $pdfCreateDay;							//定款作成日(日)
$updInfo['tbl_company']['cmp_del_flag'] = $companyInfo['tbl_company']['cmp_del_flag'];		//削除フラグ
$res = _CreateCompanyInfo($updInfo);
if ($res === false) {
	$errorList[] = "『定款作成日』の更新に失敗しました。再度、印刷を実行してください。";
	$_SESSION[SID_PDF_ERR_MSG] = $errorList;
	//エラー画面を表示する。
	header("Location: ../error.php");
	exit;
}


//マスタ情報を取得する。
$undeleteOnly = false;
$mstPrefList = _GetMasterList('mst_pref');		//都道府県マスタ
unset($mstPrefList[MST_PREF_ID_OVERSEAS]);
$mstPostList = _GetMasterList('mst_post');		//役職マスタ


//定数--------------------------------------------start
//フォントサイズを定義する。
//通常
$normalFontSize = 10;

//タイトル
$title = "定　款";


//[デバッグ用]
//ボーダー
$border = 0;
//定数--------------------------------------------end


// EUC-JP->SJIS 変換を自動的に行なわせる場合に mbfpdf.php 内の $EUC2SJIS を
// true に修正するか、このように実行時に true に設定しても変換してくます。
//$GLOBALS['EUC2SJIS'] = true;

//PDFのサイズを設定する。デフォルト=FPDF($orientation='P',$unit='mm',$format='A4')
$pdf = new MBFPDF();

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


//表紙削除
//$pdf->AddPage();
//
//
////会社名
//$y = $pdf->GetY();
//$y += 50;
//$pdf->SetY($y);
//$pdf->SetFontSize(18);
//$buf = $companyInfo['tbl_company']['cmp_company_name']."定款";
//$pdf->Cell(0,10,$buf,$border,0,"C");
//$pdf->Ln(20);
//
//
////作成日
//$y = $pdf->GetY();
//$y += 150;
//$pdf->SetY($y);
//$pdf->SetFontSize(14);
//
//$buf = "平成";
//$pdf->Cell(70,10,$buf,$border,0,"R");
//$pdfCreateYearJp = _ConvertAD2Jp($pdfCreateYear, false);
//$buf = $pdfCreateYearJp."年";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = $pdfCreateMonth."月";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = $pdfCreateDay."日";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = null;
//$pdf->Cell(10,10,$buf,$border,0,"R");
//$buf = "作成";
//$pdf->Cell(0,10,$buf,$border,0,"L");
//
//$pdf->Ln();
//
////公証人認証
//$buf = "平成";
//$pdf->Cell(70,10,$buf,$border,0,"R");
//$buf = "年";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = "月";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = "日";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = null;
//$pdf->Cell(10,10,$buf,$border,0,"R");
//$buf = "公証人認証";
//$pdf->Cell(0,10,$buf,$border,0,"L");
//
//$pdf->Ln();
//
////会社成立
//$buf = "平成";
//$pdf->Cell(70,10,$buf,$border,0,"R");
//$buf = "年";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = "月";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = "日";
//$pdf->Cell(18,10,$buf,$border,0,"R");
//$buf = null;
//$pdf->Cell(10,10,$buf,$border,0,"R");
//$buf = "会社成立";
//$pdf->Cell(0,10,$buf,$border,0,"L");
//
//$pdf->Ln();

$pdf->AddPage();

//会社名
$pdf->SetFontSize(18);
$buf = $companyInfo['tbl_company']['cmp_company_name'];
$pdf->Cell(0,10,$buf,$border,0,"C");
$pdf->Ln(15);

//タイトル
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


//第１章　総　則
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第１章　総　則",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);

$no = 0;


//（商　号）
$pdf->Cell(0,6,"（商　号）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "当会社は、";
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
if (_IsNull($companyInfo['tbl_company']['cmp_company_name_en'])) {
	$buf .= "と称する。";
} else {
	$buf .= "と称し、英文では";
	$buf .= $companyInfo['tbl_company']['cmp_company_name_en'];
	$buf .= "と表示する。";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（目　的）
$pdf->Cell(0,6,"（目　的）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "当会社は、次の事業を営むことを目的とする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$i = 0;
foreach ($companyInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (_IsNull($purposeInfo['cmp_pps_purpose'])) continue;

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = (++$i)."．";
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

$buf = (++$i)."．";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"L");

$buf = null;
if (count($companyInfo['tbl_company_purpose']['purpose_info']) == 1) {
	$buf = "上記に附帯する一切の業務";
} else {
	$buf = "上記各号に附帯する一切の業務";
//	$buf = "前各号に附帯関連する一切の事業";
}
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（本店の所在地）
$pdf->Cell(0,6,"（本店の所在地）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "当会社は、本店を";
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
$buf .= "に置く。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（公告の方法）
$pdf->Cell(0,6,"（公告の方法）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "当会社の公告は、官報に掲載する方法により行う。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//第２章　株　式
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第２章　株　式",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


//（発行可能株式総数）
$pdf->Cell(0,6,"（発行可能株式総数）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
//$buf .= "当会社の発行可能株式総数は";
$buf .= "当会社の発行可能株式総数は、";
$buf .= $companyInfo['tbl_company']['cmp_stock_total_num'];
$buf .= "株とする。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（株券の不発行）
$pdf->Cell(0,6,"（株券の不発行）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "当会社の株式については、株券を発行しない。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（株式の譲渡制限）
$pdf->Cell(0,6,"（株式の譲渡制限）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
	case MST_BOARD_FORMATION_ID_1_10:
		//取締役会を設置しない　役員1〜10人で設立
		$buf .= "当会社の株式を譲渡により取得するには、株主総会の承認を受けなければならない。";
//		$buf .= "当会社の株式を譲渡するには、株主総会の承認を受けなければならない。";
//		$buf .= "当会社の株式を譲渡により取得するには、代表取締役の承認を受けなければならない。";
//		$buf .= "当会社の株式を譲渡により取得するには、代表取締役社長の承認を受けなければならない。";
		break;
	case MST_BOARD_FORMATION_ID_3_1:
		//取締役会を設置する　役員3人と監査役1人で設立
		$buf .= "当会社の株式を譲渡により取得するには、取締役会の承認を受けなければならない。";
		break;
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（相続人等に対する株式の売渡請求）
$pdf->Cell(0,6,"（相続人等に対する株式の売渡請求）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "当会社は、相続その他の一般承継により当会社の株式を取得した者に対し、当該株式を当会社に売り渡すことを請求することができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（株主名簿記載事項の記載等の請求）
$pdf->Cell(0,6,"（株主名簿記載事項の記載等の請求）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "株式取得者が株主名簿記載事項を株主名簿に記載又は記録することを請求するには、当会社所定の書式による請求書に、その取得した株式の株主として";
//$buf .= "株主名簿に記載又は記録された者又はその相続人その他の一般承継人及び株式取得者が署名又は記名押印し共同して請求しなければならない。";
//$buf .= "株主名簿に記載又は記録された者又はその相続人その他の一般承継人及び株式取得者が署名又は記名押印し共同してしなければならない。";
$buf .= "株主名簿に記載又は記録された者又はその相続人その他の一般承継人及び株式取得者が署名又は記名押印し、共同してしなければならない。";
$buf .= "ただし、会社法施行規則２２条１項各号に定める場合には、株式取得者が単独で請求することができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（質権の登録及び信託財産の表示）
$pdf->Cell(0,6,"（質権の登録及び信託財産の表示）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
//$buf .= "当会社の株式につき質権の登録又は信託財産の表示を請求するには、当会社所定の書式による請求書に当事者が署名又は記名押印し共同して請求しなければならない。その登録又は表示の抹消についても同様とする。";
//$buf .= "当会社の株式につき質権の登録又は信託財産の表示を請求するには、当会社所定の書式による請求書に当事者が署名又は記名押印して請求しなければならない。その登録又は表示の抹消についても同様とする。";
$buf .= "当会社の株式につき質権の登録又は信託財産の表示を請求するには、当会社所定の書式による請求書に当事者が署名又は記名押印してしなければならない。その登録又は表示の抹消についても同様とする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（手数料）
$pdf->Cell(0,6,"（手数料）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "前二条に定める請求をする場合には、当会社所定の手数料を支払わなければならない。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（基準日）
$pdf->Cell(0,6,"（基準日）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "当会社は、毎事業年度末日の最終の株主名簿に記載又は記録された議決権を有する株主をもって、その事業年度に関する定時株主総会において権利を行使することができる株主とする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "前項のほか株主又は登録株式質権者として権利を行使することができる者を確定するため必要があるときは、あらかじめ公告してそのための基準日を定めることができる。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//第３章　株主総会
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第３章　株主総会",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
	case MST_BOARD_FORMATION_ID_1_10:
		//取締役会を設置しない　役員1〜10人で設立

		//（招集及び招集権者）
		$pdf->Cell(0,6,"（招集及び招集権者）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "当会社の定時株主総会は、毎事業年度末日の翌日から３か月以内に招集し、臨時株主総会は、必要に応じて招集する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　２",$border,0,"L");

//		$buf = "株主総会は、法令に別段の定めがある場合を除くほか、代表取締役社長がこれを招集する。代表取締役社長に事故若しくは支障があるときは、予め定めた順位により他の取締役がこれを招集する。";
//		$buf = "株主総会は、法令に別段の定めがある場合を除き、代表取締役社長がこれを招集する。代表取締役社長に事故若しくは支障があるときは、予め定めた順位により他の取締役がこれを招集する。";
//		$buf = "株主総会は、法令に別段の定めがある場合を除き、取締役の過半数の決定により代表取締役社長がこれを招集する。代表取締役社長に事故若しくは支障があるときは、予め定めた順位により他の取締役がこれを招集する。";
//		$buf = "株主総会は、法令に別段の定めがある場合を除き、取締役の過半数の決定により代表取締役社長がこれを招集する。代表取締役社長に事故若しくは支障があるときは、あらかじめ定めた順位により他の取締役がこれを招集する。";
		$buf = "株主総会は、法令に別段の定めがある場合を除き、取締役の過半数の決定により代表取締役社長がこれを招集する。代表取締役社長に事故若しくは支障があるときは、あらかじめ定めた順序により他の取締役がこれを招集する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　３",$border,0,"L");

//		$buf = "株主総会を招集するには、会日より３日前までに、議決権を有する各株主に対して招集通知を発するものとする。ただし、議決権を有するすべての株主の同意があるときは招集手続きを経ず株主総会を開催することができる。";
//		$buf = "株主総会を招集するには、会日より３日前までに、議決権を有する各株主に対して招集通知を発するものとする。ただし、議決権を有するすべての株主の同意があるときは招集手続きを経ず株主総会を開催することができる。ただし、法令に別段の定めがある場合は、この限りでない。";
//		$buf = "株主総会を招集するには、会日より３日前までに、議決権を行使することができる各株主に対して招集通知を発するものとする。ただし、議決権を行使することができるすべての株主の同意があるときは招集手続きを経ず株主総会を開催することができる。ただし、法令に別段の定めがある場合は、この限りでない。";
		$buf = "株主総会を招集するには、会日より３日前までに、議決権を行使することができる各株主に対して招集通知を発するものとする。ただし、議決権を行使することができるすべての株主の同意があるときは招集手続を経ず株主総会を開催することができる。ただし、法令に別段の定めがある場合は、この限りでない。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　４",$border,0,"L");

//		$buf = "前項の招集通知は、書面ですることを要しない。";
		$buf = "前項の招集通知は、法令に別段の定めがある場合を除き、書面ですることを要しない。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（議長）
		$pdf->Cell(0,6,"（議長）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "株主総会の議長は、代表取締役社長がこれに当たる。代表取締役社長に事故若しくは支障があるときは、他の取締役が議長になり、取締役全員に事故があるときは、総会において出席株主のうちから議長を選出する。";
//		$buf = "株主総会の議長は、代表取締役社長がこれに当たる。代表取締役社長に事故若しくは支障があるときは、他の取締役が議長になり、取締役全員に事故があるときは、株主総会において出席株主のうちから議長を選出する。";
//		$buf = "株主総会の議長は、代表取締役社長がこれに当たる。代表取締役社長に事故若しくは支障があるときは、予め定めた順位により他の取締役が議長になり、取締役全員に事故があるときは、株主総会において出席株主のうちから議長を選出する。";
		$buf = "株主総会の議長は、代表取締役社長がこれに当たる。代表取締役社長に事故若しくは支障があるときは、あらかじめ定めた順序により他の取締役が議長になり、取締役全員に事故があるときは、株主総会において出席株主のうちから議長を選出する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（決議の方法）
		$pdf->Cell(0,6,"（決議の方法）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "株主総会の普通決議は、法令又は定款に別段の定めがある場合を除き、出席した議決権を行使することができる株主の議決権の過半数をもって行う。";
		$buf = "株主総会の決議は、法令又は定款に別段の定めがある場合を除き、出席した議決権を行使することができる株主の議決権の過半数をもって行う。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（総会議事録）
		$pdf->Cell(0,6,"（総会議事録）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "株主総会における議事の経過の要領及びその結果並びにその他法令に定める事項は、議事録に記載又は記録し、議長及び出席した取締役がこれに署名若しくは記名押印又は電子署名をし、１０年間本店に備え置く。";
		$buf = "株主総会における議事の経過の要領及びその結果並びにその他法令に定める事項は、議事録に記載又は記録し、議長及び出席した取締役がこれに署名若しくは記名押印又は電子署名をし、株主総会の日から１０年間本店に備え置く。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		break;
	case MST_BOARD_FORMATION_ID_3_1:
		//取締役会を設置する　役員3人と監査役1人で設立

		//（招集及び招集権者）
		$pdf->Cell(0,6,"（招集及び招集権者）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "当会社の定時株主総会は、毎事業年度末日の翌日から３か月以内に招集し臨時株主総会は、随時必要に応じて招集する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　２",$border,0,"L");

//		$buf = "株主総会は、法令に別段の定めがある場合を除くほか、取締役会の決議に基づき、社長がこれを招集する。社長に事故若しくは支障があるときは、予め定めた順序により他の取締役がこれを招集する。";
		$buf = "株主総会は、法令に別段の定めがある場合を除くほか、取締役会の決議に基づき、社長がこれを招集する。社長に事故若しくは支障があるときは、あらかじめ定めた順序により他の取締役がこれを招集する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　３",$border,0,"L");

//		$buf = "株主総会を招集するには、会日より１週間までに、議決権を有する各株主に対して招集通知を発するものとする。ただし、書面投票又は電子投票を認める場合は、会日の２週間前までに発するものとする。";
//		$buf = "株主総会を招集するには、会日より１週間までに、議決権を行使することができる各株主に対して招集通知を発するものとする。ただし、書面投票又は電子投票を認める場合は、会日の２週間前までに発するものとする。";
		$buf = "株主総会を招集するには、会日より１週間前までに、議決権を行使することができる各株主に対して招集通知を発するものとする。ただし、書面投票又は電子投票を認める場合は、会日の２週間前までに発するものとする。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（招集手続きの省略）
		$pdf->Cell(0,6,"（招集手続きの省略）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "株主総会は、その総会において議決権を行使することができるすべての株主の同意があるときは、招集手続を経ずに開催することができる。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（議長）
		$pdf->Cell(0,6,"（議長）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "株主総会の議長は、社長がこれに当たる。社長に事故若しくは支障があるときは、あらかじめ取締役会の定める順序により、他の取締役が議長になり、取締役全員に事故があるときは、総会において出席株主のうちから議長を選出する。";
		$buf = "株主総会の議長は、社長がこれに当たる。社長に事故若しくは支障があるときは、あらかじめ取締役会の定める順序により、他の取締役が議長になり、取締役全員に事故があるときは、株主総会において出席株主のうちから議長を選出する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（決議の方法）
		$pdf->Cell(0,6,"（決議の方法）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "株主総会の普通決議は、法令又は定款に別段の定めがある場合を除き、出席した議決権を行使することができる株主の議決権の過半数をもって決する。";
		$buf = "株主総会の決議は、法令又は定款に別段の定めがある場合を除き、出席した議決権を行使することができる株主の議決権の過半数をもって決する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（議決権の代理行使）
		$pdf->Cell(0,6,"（議決権の代理行使）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "株主は、代理人によって議決権を行使することができる。この場合には総会ごとに代理権を証する書面を提出しなければならない。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　２",$border,0,"L");

		$buf = "前項の代理人は、当会社の議決権を有する株主に限るものとし、かつ、２人以上の代理人を選任することはできない。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（総会議事録）
		$pdf->Cell(0,6,"（総会議事録）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "株主総会における議事の経過の要領及びその結果並びにその他法令に定める事項は、議事録に記載又は記録し、議長及び出席した取締役がこれに署名若しくは記名押印又は電子署名をし、１０年間本店に備え置く。";
		$buf = "株主総会における議事の経過の要領及びその結果並びにその他法令に定める事項は、議事録に記載又は記録し、議長及び出席した取締役がこれに署名若しくは記名押印又は電子署名をし、株主総会の日から１０年間本店に備え置く。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		break;
}


$pdf->Ln(10);


switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
	case MST_BOARD_FORMATION_ID_1_10:
		//取締役会を設置しない　役員1~10人で設立

		//第４章　取締役
		$pdf->SetFontSize(14);
//		$pdf->Cell(0,10,"第４章　取締役",$border,0,"C");
		$pdf->Cell(0,10,"第４章　取締役及び代表取締役",$border,0,"C");
		$pdf->Ln(20);

		$pdf->SetFontSize(12);


		//（取締役の員数）
		$pdf->Cell(0,6,"（取締役の員数）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = null;
		$buf .= "当会社は取締役";
//		$buf .= $companyInfo['tbl_company']['cmp_director_num'];
		$buf .= "1";
		$buf .= "名以上を置く。";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（取締役の選任）
		$pdf->Cell(0,6,"（取締役の選任）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "当会社の取締役は、株主総会において、議決権を行使することができる株主の議決権の３分の１以上を有する株主が出席し、その議決権の過半数の決議によって選任する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　２",$border,0,"L");

		$buf = "前項の選任については、累積投票の方法によらない。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（取締役の任期）
		$pdf->Cell(0,6,"（取締役の任期）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = null;
		$buf .= "取締役の任期は、選任後";
		$buf .= $companyInfo['tbl_company']['cmp_term_year'];
//		$buf .= "年以内に終了する最終の事業年度に関する定時株主総会の終結時までとする。";
		$buf .= "年以内に終了する事業年度のうち最終のものに関する定時株主総会の終結時までとする。";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　２",$border,0,"L");

		$buf = "補欠又は増員により選任した取締役の任期は、前任者又は他の在任取締役の任期の残存期間と同一とする。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		//代表取締役が複数登録可能になったので、（代表取締役及び社長）の文章が変更になった。
		$countRepDirector = 0;
		foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
			if ($boardInfo['cmp_bod_post_id'] != MST_POST_ID_REP_DIRECTOR) continue;
			$countRepDirector++;
		}

		//（代表取締役及び社長）
		$pdf->Cell(0,6,"（代表取締役及び社長）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");
		
		$buf = null;
		if ($countRepDirector >= 2) {
			$buf = "当会社に取締役を複数名置く場合には、取締役の互選により代表取締役を定め、代表取締役をもって社長とする。";
		} else {
			$buf = "当会社に取締役を複数名置く場合には、取締役の互選により代表取締役１名を定め、代表取締役をもって社長とする。";
		}
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　２",$border,0,"L");

		$buf = "当会社に置く取締役が１名の場合には、その取締役を代表取締役社長とする。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　３",$border,0,"L");

		$buf = "社長は当会社を代表する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（取締役に対する報酬等）
		$pdf->Cell(0,6,"（取締役に対する報酬等）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "取締役に対する報酬及び退職慰労金等は、株主総会の決議により定める。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		break;
	case MST_BOARD_FORMATION_ID_3_1:
		//取締役会を設置する　役員3人と監査役1人で設立

		//第４章　取締役、取締役会、代表取締役及び監査役
		$pdf->SetFontSize(14);
		$pdf->Cell(0,10,"第４章　取締役、取締役会、代表取締役及び監査役",$border,0,"C");
		$pdf->Ln(20);

		$pdf->SetFontSize(12);


		//（取締役会設置会社）
		$pdf->Cell(0,6,"（取締役会設置会社）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "当会社は取締役会を置く。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（取締役の員数）
		$pdf->Cell(0,6,"（取締役の員数）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = null;
		$buf .= "当会社は取締役";
		$buf .= $companyInfo['tbl_company']['cmp_director_num'];
		$buf .= "名以上を置く。";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（監査役設置会社）
		$pdf->Cell(0,6,"（監査役設置会社）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "当会社は監査役を置き、その員数は３名以内とする。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（取締役及び監査役の選任）
		$pdf->Cell(0,6,"（取締役及び監査役の選任）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "当会社の取締役及び監査役は株主総会において総株主の議決権の３分の１以上を有する株主が出席し、その議決権の過半数の決議によって選任する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

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

		$buf = null;
		$buf .= "取締役の任期は、選任後";
		$buf .= $companyInfo['tbl_company']['cmp_term_year'];
//		$buf .= "年以内に終了する最終の事業年度に関する定時株主総会の終結時までとする。";
		$buf .= "年以内に終了する事業年度のうち最終のものに関する定時株主総会の終結時までとする。";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　２",$border,0,"L");

		$buf = null;
		$buf .= "監査役の任期は、選任後";
		$buf .= $companyInfo['tbl_company']['cmp_inspector_term_year'];
//		$buf .= "年以内に終了する最終の事業年度に関する定時株主総会の終結時までとする。";
		$buf .= "年以内に終了する事業年度のうち最終のものに関する定時株主総会の終結時までとする。";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　３",$border,0,"L");

		$buf = "任期満了前に退任した取締役の補欠として、又は増員により選任された取締役の任期は、前任者又は他の在任取締役の任期の残存期間と同一とする。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

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
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　２",$border,0,"L");

		$buf = "取締役社長に欠員又は事故があるときは、取締役会において予め定めた順序で、他の取締役がこれに代わる。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

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
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　２",$border,0,"L");

		$buf = "代表取締役は会社を代表し、会社の業務を執行する。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		$pdf->Cell(20,6,"　　　３",$border,0,"L");

		$buf = "取締役会の決議をもって取締役の中から、社長１名を選定し、必要に応じて、取締役副社長、専務取締役、常務取締役各若干名を選定することができる。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		//（報　酬）
		$pdf->Cell(0,6,"（報　酬）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "取締役及び監査役の報酬及び退職慰労金等は、それぞれ株主総会の決議をもって定める。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();


		break;
}


$pdf->Ln(10);


//第５章　計　算
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第５章　計　算",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


//（事業年度）
$pdf->Cell(0,6,"（事業年度）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//12ヵ月後の月と末日を取得する。
$businessEndMonth = 0;
if ($companyInfo['tbl_company']['cmp_business_start_month'] == 1) {
	$businessEndMonth = 12;
} else {
	$businessEndMonth = $companyInfo['tbl_company']['cmp_business_start_month'] - 1;
}
$lastDayList = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
$businessEndDay = $lastDayList[$businessEndMonth - 1];

//※$businessEndMonth、$businessEndDayは、（最初の事業年度）でも使用する。

$buf = null;
$buf .= "当会社の事業年度は、";
$buf .= "毎年";
$buf .= $companyInfo['tbl_company']['cmp_business_start_month'];
$buf .= "月1日から";
$buf .= ($businessEndMonth == 12?"同年":"翌年");
$buf .= $businessEndMonth;
$buf .= "月";
//$buf .= $businessEndDay;
//$buf .= "日";
$buf .= "末日";
$buf .= "までの年1期とする。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（剰余金の配当）
$pdf->Cell(0,6,"（剰余金の配当）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//$buf = "剰余金の配当は、毎事業年度末日現在の最終の株主名簿に記載又は記録された株主及び登録株式質権者に対して行う。";
$buf = "剰余金の配当は、毎事業年度末日現在の最終の株主名簿に記載又は記録された株主又は登録株式質権者に対して行う。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Cell(20,6,"　　　２",$border,0,"L");

//$buf = "剰余金の配当が、支払いの提供をした日から３年を経過しても受領されないときは、当会社は、その支払いの義務を免れるものとする。";
$buf = "剰余金の配当が、支払の提供をした日から３年を経過しても受領されないときは、当会社は、その支払の義務を免れるものとする。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//第６章　附　則
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第６章　附　則",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
	case MST_BOARD_FORMATION_ID_1_10:
		//取締役会を設置しない　役員1〜10人で設立

		//（設立の際に発行する株式の数）
		$pdf->Cell(0,6,"（設立の際に発行する株式の数）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		//設立時発行株式の数 = 資本金(万円) / 1株の単価(円)
		$stockNum = ($companyInfo['tbl_company']['cmp_capital'] * 10000) / $companyInfo['tbl_company']['cmp_stock_price'];
		$stockNum = floor($stockNum);//端数の切り捨て
		$stockNum = _ConvertNum2Ja($stockNum);

		$buf = null;
		$buf .= "当会社の設立時発行株式の数は";
		$buf .= $stockNum;
		$buf .= "株、その発行価額は1株につき金";
		$buf .= _ConvertNum2Ja($companyInfo['tbl_company']['cmp_stock_price']);
		$buf .= "円とする。";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();

		break;
	case MST_BOARD_FORMATION_ID_3_1:
		//取締役会を設置する　役員3人と監査役1人で設立
		break;
}


//（設立に際して出資される財産の価額及び成立後の資本金の額）
$pdf->Cell(0,6,"（設立に際して出資される財産の価額及び成立後の資本金の額）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//資本金の単位を万円から円にする。
$capital = $companyInfo['tbl_company']['cmp_capital'] * 10000;
$capital = _ConvertNum2Ja($capital);

$buf = null;
$buf .= "当会社の設立に際して出資される財産の価額は金";
$buf .= $capital;
$buf .= "円とする。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = null;
$buf .= "当会社の成立後の資本金の額は、金";
$buf .= $capital;
$buf .= "円とする。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（最初の事業年度）
$pdf->Cell(0,6,"（最初の事業年度）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//最初の事業年度の最終月の年を取得する。
//$businessEndYear = null;
//if ($businessEndMonth == 12) {
//	$businessEndYear = $companyInfo['tbl_company']['cmp_found_year'];
//} else {
//	$businessEndYear = $companyInfo['tbl_company']['cmp_found_year'] + 1;
//}
//$businessEndYear = _ConvertAD2Jp($businessEndYear);

//上記だと年が次の年になってします。同一年の場合も有り。画面上の「Nヶ月」表示のJsと同じ処理にする。
//Jsと同じ処理
$startMonth = $companyInfo['tbl_company']['cmp_business_start_month'];
$foundMonth = $companyInfo['tbl_company']['cmp_found_month'];
$diff = 12 - ($foundMonth - $startMonth);
if ($diff > 12) $diff -= 12;
$bufY = $companyInfo['tbl_company']['cmp_found_year'];
$bufM = $companyInfo['tbl_company']['cmp_found_month'];
//for ($diffIdx = 0; $diffIdx < $diff; $diffIdx++) {
for ($diffIdx = 1; $diffIdx < $diff; $diffIdx++) { //最初の月からNヵ月後を求める。ので、ループを一個減らす。
	$bufM++;
	if ($bufM > 12) {
		$bufY++;
		$bufM = 1;
	}
}
$businessEndYear = $bufY;
$businessEndYear = _ConvertAD2Jp($businessEndYear);


$buf = null;
$buf .= "当会社の最初の事業年度は、当会社成立の日から";
$buf .= $businessEndYear;
$buf .= "年";
$buf .= $businessEndMonth;
$buf .= "月";
//$buf .= $businessEndDay;
//$buf .= "日";
$buf .= "末日";
$buf .= "までとする。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
	case MST_BOARD_FORMATION_ID_1_10:
		//取締役会を設置しない　役員1〜10人で設立

		//（設立時取締役）
//		$pdf->Cell(0,6,"（設立時取締役）",$border,0,"L");
//		$pdf->Cell(0,6,"（設立時取締役及び代表取締役）",$border,0,"L");
//		$pdf->Cell(0,6,"（設立時取締及び設立時代表取締役）",$border,0,"L");
		$pdf->Cell(0,6,"（設立時取締役及び設立時代表取締役）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//		$buf = "当会社の設立時取締役は、次のとおりとする。";
		$buf = "当会社の設立時取締役及び設立時代表取締役は、次のとおりとする。";
		$pdf->MultiCell(0,6,$buf,$border,"L");
		$pdf->Ln();
		break;
	case MST_BOARD_FORMATION_ID_3_1:
		//取締役会を設置する　役員3人と監査役1人で設立

		//（設立時取締役及び設立時監査役）
		$pdf->Cell(0,6,"（設立時取締役及び設立時監査役）",$border,0,"L");
		$pdf->Ln();
		$pdf->Cell(20,6,_no(++$no),$border,0,"L");

		$buf = "当会社の設立時取締役及び設立時監査役は、次のとおりとする。";
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
		$buf .= "設立時";
		if ($mstPostInfo['id'] == MST_POST_ID_REP_DIRECTOR) {
			//代表取締役の場合、"取締役"として表示する。
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
////代表取締役を最後に追加する。
//$x = $pdf->GetX();
//$x += 20;
//$pdf->SetX($x);
//
//$buf = null;
//$buf .= "設立時";
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

//代表取締役を最後に追加する。
//代表取締役が複数登録可能になったので、複数人を最後に追加する。
foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
	if ($boardInfo['cmp_bod_post_id'] != MST_POST_ID_REP_DIRECTOR) continue;

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = null;
	$buf .= "設立時";
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


//（発起人の氏名ほか）
$pdf->Cell(0,6,"（発起人の氏名ほか）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//現物出資者の名前を設置する。
$nameInkind = null;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	//出資形態(現金のみ・現物出資のみ・現金＋現物)によって、現物の出資者を判断する。
	switch ($promoterInfo['cmp_prm_investment_shape_id']) {
		case MST_INVESTMENT_SHAPE_ID_CASH:
			//現金のみ
			break;
		case MST_INVESTMENT_SHAPE_ID_INKIND:
			//現物出資のみ
		case MST_INVESTMENT_SHAPE_ID_CASH_INKIND:
			//現金＋現物
			if (!_IsNull($nameInkind)) $nameInkind .= "、";
//			if (!_IsNull($nameInkind)) $nameInkind .= "、 ";//改行修正のため、空白を追加した。
			$nameInkind .= "発起人";
			$nameInkind .= $promoterInfo['cmp_prm_family_name'];
//			$nameInkind .= " ";//改行修正のため、空白を削除した。
			$nameInkind .= $promoterInfo['cmp_prm_first_name'];
			break;
	}
}

$buf = null;
$buf .= "発起人の氏名、住所及び設立に際して割当てを受ける株式数並びに株式と引換えに払い込む金銭の額は、次のとおりである。";
if (!_IsNull($nameInkind)) {
	$buf .= "なお";
	$buf .= $nameInkind;
	$buf .= "は、次条に定める現物出資により割り当てを受ける株式を引き受ける。";
//	$buf .= "は、次条に定める現物出資により割り当てを受ける株式を 引き受ける。";//改行修正のため、空白を追加した。
}
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	$stockNumCash = null;		//現金の株数
	$investmentCash = null;		//現金の金額
	$stockNumInkind = null;		//現物の株数
	$investmentInkind = null;	//現物の金額

	//出資金の登録はあるか？
	if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

		//現金の出資を集計する。
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_CASH])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_CASH]['investment_info'] as $iKey => $investmentInfo) {
				if (_IsNull($stockNumCash)) $stockNumCash = 0;
				$stockNumCash += $investmentInfo['cmp_prm_inv_stock_num'];
			}

			if (!_IsNull($stockNumCash)) {
				//株数から出資金額を計算する。1株の単価(円)×現金の株数
				$investmentCash = $companyInfo['tbl_company']['cmp_stock_price'] * $stockNumCash;

				//数値を和表記に変換する。
				$investmentCash = "金"._ConvertNum2Ja($investmentCash)."円";
				$stockNumCash = _ConvertNum2Ja($stockNumCash)."株";
			}
		}

		//現物の出資を集計する。
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
				if (_IsNull($stockNumInkind)) $stockNumInkind = 0;
				$stockNumInkind += $investmentInfo['cmp_prm_inv_stock_num'];
			}

			if (!_IsNull($stockNumInkind)) {
				//株数から出資金額を計算する。1株の単価(円)×現金の株数
				$investmentInkind = $companyInfo['tbl_company']['cmp_stock_price'] * $stockNumInkind;

				//数値を和表記に変換する。
				$investmentInkind = "金"._ConvertNum2Ja($investmentInkind)."円";
				$stockNumInkind = _ConvertNum2Ja($stockNumInkind)."株";
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
	$buf .= "発起人";
	$pdf->Cell(20,6,$buf,$border,0,"L");

//氏名が長い場合があるので、MultiCellに変更する。
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
		$buf .= "現物出資";
		$pdf->Cell(50,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $stockNumInkind;
		$buf .= "（割り当てを受ける株式の数）";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(0,6,$buf,$border,0,"L");

		$pdf->Ln();
	}

	$pdf->Ln();
}


if (!_IsNull($nameInkind)) {
	//（現物出資）
	$pdf->Cell(0,6,"（現物出資）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");

	$buf = "当会社の設立に際して現物出資をする者の氏名、出資の目的である財産、その価額及びこれに対して割り当てる株式の数は、次のとおりである。";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();

	$countPromoter = 0;
	foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

		//出資形態(現金のみ・現物出資のみ・現金＋現物)によって、現物の出資者を判断する。
		switch ($promoterInfo['cmp_prm_investment_shape_id']) {
			case MST_INVESTMENT_SHAPE_ID_CASH:
				//現金のみ
				continue 2;
			case MST_INVESTMENT_SHAPE_ID_INKIND:
				//現物出資のみ
			case MST_INVESTMENT_SHAPE_ID_CASH_INKIND:
				//現金＋現物
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

		//出資形態(現金のみ・現物出資のみ・現金＋現物)によって、現物の出資者を判断する。
		switch ($promoterInfo['cmp_prm_investment_shape_id']) {
			case MST_INVESTMENT_SHAPE_ID_CASH:
				//現金のみ
				continue 2;
			case MST_INVESTMENT_SHAPE_ID_INKIND:
				//現物出資のみ
			case MST_INVESTMENT_SHAPE_ID_CASH_INKIND:
				//現金＋現物
				break;
		}

		$countPromoter++;

		$x = $pdf->GetX();
		$x += 20;
		$pdf->SetX($x);

		$buf = null;
		if ($countPromoterNoFlag) {
			$buf .= "【";
			$buf .= $countPromoter;
			$buf .= "】";
		}
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "（";
		$buf .= "1";
		$buf .= "）";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "出資者";
		$pdf->Cell(20,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "発起人";
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
		$buf .= "（";
		$buf .= "2";
		$buf .= "）";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "出資財産及びその価額";
		$pdf->Cell(0,6,$buf,$border,0,"L");

		$pdf->Ln();


		$stockNumInkind = null;		//現物の株数

		//出資金の登録はあるか？
		if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
			$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

			//現物の出資を集計する。
			if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {

				$countInkind = 0;
				foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
					if (_IsNull($stockNumInkind)) $stockNumInkind = 0;

					//株数の合計を計算する。
					$stockNumInkind += $investmentInfo['cmp_prm_inv_stock_num'];

					//株数から出資金額を計算する。1株の単価(円)×現金の株数
					$investmentInkind = $companyInfo['tbl_company']['cmp_stock_price'] * $investmentInfo['cmp_prm_inv_stock_num'];
					//数値を和表記に変換する。
					$investmentInkind = "金"._ConvertNum2Ja($investmentInkind)."円";

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
					$buf .= "．";
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
					//数値を和表記に変換する。
					$stockNumInkind = _ConvertNum2Ja($stockNumInkind)."株";
				}
			}
		}

		$x = $pdf->GetX();
		$x += 20;
		$pdf->SetX($x);

		$buf = null;
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "（";
		$buf .= "3";
		$buf .= "）";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "割り当てる株式の数";
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


//（法令の準拠）
$pdf->Cell(0,6,"（法令の準拠）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "本定款に定めのない事項は、すべて会社法その他の関係法令に従う。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//$buf = null;
//$buf .= "以上、";
//$buf .= $companyInfo['tbl_company']['cmp_company_name'];
//$buf .= "を設立のため、この定款を作成し、発起人が次に記名押印する。";
////$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");

//$buf = null;
//$buf .= "以上、";
//$buf .= $companyInfo['tbl_company']['cmp_company_name'];
//$buf .= "設立の為に、発起人の定款作成代理人である".ADMINISTRATIVE_SCRIVENER_NAME."は、電磁的記録である本定款を作成し、これに電子署名する。";
////$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");

//発起人
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
	$promoterName .= "他".($promoterCount - 1)."名";
}

$buf = null;
$buf .= "以上、";
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
//$buf .= "設立の為に、発起人 ".$promoterName." の\n定款作成代理人である".ADMINISTRATIVE_SCRIVENER_NAME."は、電磁的記録である本定款を作成し、\nこれに電子署名する。";
//$buf .= "設立の為に、発起人 ".$promoterName."の定款作成代理人である\n".ADMINISTRATIVE_SCRIVENER_NAME."は、電磁的記録である本定款を作成し、これに電子署名する。";
//$buf .= "設立の為に、発起人 ".$promoterName."の定款作成代理人 である".ADMINISTRATIVE_SCRIVENER_NAME."は、電磁的記録である本定款を作成し、これに電子署名する。";
//$buf .= "設立の為に、発起人 ".$promoterName."の定款作成代理人である ".ADMINISTRATIVE_SCRIVENER_NAME."は、電磁的記録である本定款を作成し、これに電子署名する。";
$buf .= "設立の為に、発起人 ".$promoterName."の定款作成代理人である".ADMINISTRATIVE_SCRIVENER_NAME."は、電磁的記録である本定款を作成し、これに電子署名する。";
//$buf = mb_convert_kana($buf, 'N');
$buf = str_replace(" ", "　", $buf);
$pdf->MultiCell(0,6,$buf,$border,"L");



$pdf->Ln(20);

////作成日
//$buf = "平成";
//$pdf->Cell(12,6,$buf,$border,0,"L");
//$pdfCreateYearJp = _ConvertAD2Jp($pdfCreateYear, false);
//$buf = $pdfCreateYearJp."年";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(14,6,$buf,$border,0,"R");
//$buf = $pdfCreateMonth."月";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(14,6,$buf,$border,0,"R");
//$buf = $pdfCreateDay."日";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->Cell(14,6,$buf,$border,0,"R");
//$buf = null;
//$pdf->Cell(0,6,$buf,$border,0,"R");

//作成日
$pdfCreateYearJp = _ConvertAD2Jp($pdfCreateYear);
$buf = $pdfCreateYearJp."年";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(24,6,$buf,$border,0,"L");
$buf = $pdfCreateMonth."月";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(14,6,$buf,$border,0,"R");
$buf = $pdfCreateDay."日";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(14,6,$buf,$border,0,"R");
$buf = null;
$pdf->Cell(0,6,$buf,$border,0,"R");

$pdf->Ln(20);


//発起人
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	$nameInkind .= "発起人";

	$buf = "発起人";
	$pdf->Cell(20,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $promoterInfo['cmp_prm_family_name'];
	$buf .= " ";
	$buf .= $promoterInfo['cmp_prm_first_name'];
	$pdf->Cell(0,6,$buf,$border,0,"L");

	$pdf->Ln(15);
}



$buf = null;
$buf .= "上記発起人の定款作成代理人";
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






//DBをクローズする。
_DB_Close($link);


//PDFを出力する。
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
	return mb_convert_kana("第".$no."条", 'N');
}

?>
