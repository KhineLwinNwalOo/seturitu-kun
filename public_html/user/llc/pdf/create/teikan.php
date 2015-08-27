<?php
/*
 * [新★会社設立.JP ツール]
 * PDF作成
 * 定款(合同会社用)
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
_Log("[/user/llc/pdf/create/teikan.php] start.");

_Log("[/user/llc/pdf/create/teikan.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/llc/pdf/create/teikan.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/llc/pdf/create/teikan.php] SERVER = '".print_r($_SERVER,true)."'");


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

		_Log("[/user/llc/pdf/create/teikan.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
		_Log("[/user/llc/pdf/create/teikan.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
		_Log("[/user/llc/pdf/create/teikan.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

		$undeleteOnly4def = true;

		//自分のユーザー情報、会社情報のみ表示する。
		//ユーザーID、会社IDをチェックする。

		//会社IDを検索する。
		$relationCompanyId = _GetRelationLlcId($loginInfo['usr_user_id']);


		_Log("[/user/llc/pdf/create/teikan.php] {ログインユーザー権限処理} →(ログイン)ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/llc/pdf/create/teikan.php] {ログインユーザー権限処理} →(ログイン)会社ID = '".$relationCompanyId."'");
		_Log("[/user/llc/pdf/create/teikan.php] {ログインユーザー権限処理} →(パラメーター)ユーザーID = '".$userId."'");
		_Log("[/user/llc/pdf/create/teikan.php] {ログインユーザー権限処理} →(パラメーター)会社ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/llc/pdf/create/teikan.php] {ログインユーザー権限処理} →(処理対象)ユーザーID = '".$userId."'");
		_Log("[/user/llc/pdf/create/teikan.php] {ログインユーザー権限処理} →(処理対象)会社ID = '".$companyId."'");

		break;
}

//入金チェック
if (!_IsNull($companyId)) {
	if (!_CheckUserStatus($userId, $companyId, MST_SYSTEM_COURSE_ID_LLC)) {
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
////発行可能株式の総数
//if (_IsNull($companyInfo['tbl_company']['cmp_stock_total_num'])) $errorList[] = "『発行可能株式の総数』を登録してください。";
////役員構成
//if (_IsNull($companyInfo['tbl_company']['cmp_board_formation_id'])) $errorList[] = "『役員構成』を登録してください。";
////取締役人数
//if (_IsNull($companyInfo['tbl_company']['cmp_director_num'])) {
//	$errorList[] = "『取締役人数』を登録してください。";
//} else {
//	if ($companyInfo['tbl_company']['cmp_director_num'] < 1) $errorList[] = "『取締役人数』を登録してください。(1人以上)";
//}
////取締役・監査役の任期
//if (_IsNull($companyInfo['tbl_company']['cmp_term_year'])) $errorList[] = "『取締役・監査役の任期』を登録してください。";
//事業年度
if (_IsNull($companyInfo['tbl_company']['cmp_business_start_month'])) $errorList[] = "『事業年度』を登録してください。";
////1株の単価
//if (_IsNull($companyInfo['tbl_company']['cmp_stock_price'])) $errorList[] = "『1株の単価』を登録してください。";
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
$representativePartnerFlag = false;
$businessExecutionFlag = false;
$errFlag = false;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	//代表社員ID="代表社員になる"が1人でもいるか？
	if ($promoterInfo['cmp_prm_representative_partner_id'] == MST_REPRESENTATIVE_PARTNER_ID_YES) {
		$representativePartnerFlag = true;
	}
	//業務執行ID="業務執行社員になる"が1人でもいるか？
	if ($promoterInfo['cmp_prm_business_execution_id'] == MST_BUSINESS_EXECUTION_ID_YES) {
		$businessExecutionFlag = true;
	}
	//人格種別によって、チェック項目を切り替える。
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//個人
			if (_IsNull($promoterInfo['cmp_prm_family_name']) || _IsNull($promoterInfo['cmp_prm_first_name'])) {
				$errFlag = true;
			}
			if (_IsNull($promoterInfo['cmp_prm_pref_id']) || _IsNull($promoterInfo['cmp_prm_address1'])) {
				$errFlag = true;
			}
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//法人(株式会社・有限会社のみ)
			if (_IsNull($promoterInfo['cmp_prm_company_name'])) {
				$errFlag = true;
			}
			if (_IsNull($promoterInfo['cmp_prm_company_pref_id']) || _IsNull($promoterInfo['cmp_prm_company_address1'])) {
				$errFlag = true;
			}
			break;
	}
}
if ($errFlag) $errorList[] = "『社員(出資者)』の『お名前』、『住所』又は、『会社名(法人)』、『本店所在地』を登録してください。";
if (!$representativePartnerFlag) $errorList[] = "『社員(出資者)』の『代表社員』で『代表社員になる』を1人以上登録してください。";
if (!$businessExecutionFlag) $errorList[] = "『社員(出資者)』の『業務執行』で『業務執行社員になる』を1人以上登録してください。";
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
			//出資額
			if (_IsNull($investmentInfo['cmp_prm_inv_investment'])) {
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
if ($errFlag) $errorList[] = "『出資金』の『出資額』、『現物出資の品名』を登録してください。";
//定款業務執行ID
if (_IsNull($companyInfo['tbl_company']['cmp_article_business_execution_id'])) $errorList[] = "『定款自治』の『業務執行の設定・修正』を登録してください。";
//定款分配ID
if (_IsNull($companyInfo['tbl_company']['cmp_article_share_id'])) $errorList[] = "『定款自治』の『利益・損失・残余財産の分配の設定・修正』を登録してください。";
//定款変更ID
if (_IsNull($companyInfo['tbl_company']['cmp_article_change_id'])) $errorList[] = "『定款自治』の『定款変更の設定・修正』を登録してください。";


if (count($errorList) > 0) {
	//エラー有の場合
	_Log("[/user/llc/pdf/create/teikan.php] end. ERR!");


	$buf = "※PDFを作成するための情報が足りません。『合同会社設立LLC情報登録』画面で、情報を入力してください。";
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
$mstPrefList = _GetMasterList('mst_pref');													//都道府県マスタ
unset($mstPrefList[MST_PREF_ID_OVERSEAS]);
$mstPostList = _GetMasterList('mst_post');													//役職マスタ
$mstArticleBusinessExecutionList = _GetMasterList('mst_article_business_execution');		//定款業務執行マスタ
$mstArticleShareList = _GetMasterList('mst_article_share');									//定款分配マスタ
$mstArticleChangeList = _GetMasterList('mst_article_change');								//定款変更マスタ


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


//第２章　社員及び出資
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第２章　社員及び出資",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


//（社員の氏名及び住所、出資及び責任）
$pdf->Cell(0,6,"（社員の氏名及び住所、出資及び責任）",$border,0,"L");
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

			$nameInkind .= "社員";
			$nameInkind .= " ";

			//人格種別によって、名前を設定する。
			switch ($promoterInfo['cmp_prm_personal_type_id']) {
				case MST_PERSONAL_TYPE_ID_PERSONAL:
					//個人
					$nameInkind .= $promoterInfo['cmp_prm_family_name'];
					$nameInkind .= " ";
					$nameInkind .= $promoterInfo['cmp_prm_first_name'];
					break;
				case MST_PERSONAL_TYPE_ID_CORPORATION:
					//法人(株式会社・有限会社のみ)
					$nameInkind .= $promoterInfo['cmp_prm_company_name'];
					break;
			}

			break;
	}
}

$buf = null;
$buf .= "社員の氏名及び住所、出資の価額及び責任は次のとおりである。";
if (!_IsNull($nameInkind)) {
	$buf .= "なお";
	$buf .= $nameInkind;
	$buf .= "は、次条に定める現物出資により出資の価額を割り当てる。";
}
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	$investmentCash = null;		//現金の金額
	$investmentInkind = null;	//現物の金額

	//出資金の登録はあるか？
	if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

		//現金の出資を集計する。
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_CASH])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_CASH]['investment_info'] as $iKey => $investmentInfo) {
				if (_IsNull($investmentCash)) $investmentCash = 0;
				$investmentCash += $investmentInfo['cmp_prm_inv_investment'];
			}

			if (!_IsNull($investmentCash)) {
				//数値を和表記に変換する。
//				$investmentCash = "金"._ConvertNum2Ja($investmentCash * 10000)."円";
				$investmentCash = "金"._ConvertNum2Ja($investmentCash)."円";
			}
		}

		//現物の出資を集計する。
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
				if (_IsNull($investmentInkind)) $investmentInkind = 0;
				$investmentInkind += $investmentInfo['cmp_prm_inv_investment'];
			}

			if (!_IsNull($investmentInkind)) {
				//数値を和表記に変換する。
//				$investmentInkind = "金"._ConvertNum2Ja($investmentInkind * 10000)."円";
				$investmentInkind = "金"._ConvertNum2Ja($investmentInkind)."円";
			}
		}
	}

	//人格種別によって、名前、住所を設定する。
	$name = null;
	$address = null;
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//個人
			$name .= $promoterInfo['cmp_prm_family_name'];
			$name .= " ";
			$name .= $promoterInfo['cmp_prm_first_name'];

			$address .= $mstPrefList[$promoterInfo['cmp_prm_pref_id']]['name'];
			$address .= $promoterInfo['cmp_prm_address1'];
			$address .= $promoterInfo['cmp_prm_address2'];
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//法人(株式会社・有限会社のみ)
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
	$buf .= "有限責任社員";
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
		$buf .= "現物出資";
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
	//（現物出資）
	$pdf->Cell(0,6,"（現物出資）",$border,0,"L");
	$pdf->Ln();
	$pdf->Cell(20,6,_no(++$no),$border,0,"L");

	$buf = "当会社の設立に際して現物出資をする者の氏名、出資の目的である財産、その価額は、次のとおりである。";
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

		//人格種別によって、名前を設定する。
		$name = null;
		switch ($promoterInfo['cmp_prm_personal_type_id']) {
			case MST_PERSONAL_TYPE_ID_PERSONAL:
				//個人
				$name .= $promoterInfo['cmp_prm_family_name'];
				$name .= " ";
				$name .= $promoterInfo['cmp_prm_first_name'];
				break;
			case MST_PERSONAL_TYPE_ID_CORPORATION:
				//法人(株式会社・有限会社のみ)
				$name .= $promoterInfo['cmp_prm_company_name'];
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
		$buf .= "有限責任社員";
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
		$buf .= "（";
		$buf .= "2";
		$buf .= "）";
		$buf = mb_convert_kana($buf, 'N');
		$pdf->Cell(15,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "出資財産及びその価額";
		$pdf->Cell(0,6,$buf,$border,0,"L");

		$pdf->Ln();


		$totalInvestmentInkind = 0;		//現物の出資額の合計

		//出資金の登録はあるか？
		if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
			$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

			//現物の出資を集計する。
			if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {

				$countInkind = 0;
				foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
					//出資金を設定する。
//					$investmentInkind = $investmentInfo['cmp_prm_inv_investment'] * 10000;
					$investmentInkind = $investmentInfo['cmp_prm_inv_investment'];

					//出資額の合計を計算する。
					$totalInvestmentInkind += $investmentInkind;

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
			}
		}

		//数値を和表記に変換する。
		$totalInvestmentInkind = "金"._ConvertNum2Ja($totalInvestmentInkind)."円";


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
		$buf .= "合計価額";
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


//第３章　業務の執行、業務執行社員及び代表社員
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第３章　業務の執行、業務執行社員及び代表社員",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


//（業務執行の権利、業務執行社員の選任及び解任）
$pdf->Cell(0,6,"（業務執行の権利、業務執行社員の選任及び解任）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "業務執行は業務執行社員の";
$buf .= $mstArticleBusinessExecutionList[$companyInfo['tbl_company']['cmp_article_business_execution_id']]['name'];
$buf .= "をもって決定する。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　２",$border,0,"L");

$buf = "当会社の業務は、業務執行社員が執行するものとし、総社員の同意により、社員の中からこれを選任する。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　３",$border,0,"L");

$buf = "業務執行社員は、他の社員の請求がある時は、いつでもその職務の執行の状況を報告し、その職務が終了した後は、遅滞なくその経過及び結果を報告しなければならない。";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Cell(20,6,"　　　４",$border,0,"L");

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


//第４章　社員の加入及び退社
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第４章　社員の加入及び退社",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


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


//第５章　社員の除名
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第５章　社員の除名",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


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


//第６章　計　算
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第６章　計　算",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


//（営業年度）
$pdf->Cell(0,6,"（営業年度）",$border,0,"L");
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


//（利益の配当）
$pdf->Cell(0,6,"（利益の配当）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = "利益の配当は、毎事業年度の末日現在の社員に分配する。";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


////（損益分配の割合）
//$pdf->Cell(0,6,"（損益分配の割合）",$border,0,"L");
//$pdf->Ln();
//$pdf->Cell(20,6,_no(++$no),$border,0,"L");
//
//$buf = "各社員の損益分配の割合は、総社員の同意により、出資の価額と異なる割合によることができる。";
//$pdf->MultiCell(0,6,$buf,$border,"L");
//$pdf->Ln();


//（利益又は損失の分配）
$pdf->Cell(0,6,"（利益又は損失の分配）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "各社員の利益又は損失の分配の割合は、";
$buf .= $mstArticleShareList[$companyInfo['tbl_company']['cmp_article_share_id']]['name'];
$buf .= "。";
$buf .= "ただし、負担する損失については出資の目的以外には及ばないものとする。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

//（残余財産の分配）
$pdf->Cell(0,6,"（残余財産の分配）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "残余財産の分配の割合は、";
$buf .= $mstArticleShareList[$companyInfo['tbl_company']['cmp_article_share_id']]['name'];
$buf .= "。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


$pdf->Ln(10);


//第７章　附　則
$pdf->SetFontSize(14);
$pdf->Cell(0,10,"第７章　附　則",$border,0,"C");
$pdf->Ln(20);

$pdf->SetFontSize(12);


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
$buf .= "当会社の最初の事業年度は、当会社の設立の日から";
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


//（設立に際する資本金）
$pdf->Cell(0,6,"（設立に際する資本金）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

//資本金の単位を万円から円にする。
//$capital = $companyInfo['tbl_company']['cmp_capital'] * 10000;
$capital = $companyInfo['tbl_company']['cmp_capital'];
$capital = _ConvertNum2Ja($capital);

$buf = null;
$buf .= "当会社の設立時の資本金は、金";
$buf .= $capital;
$buf .= "円とする。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//（設立時業務執行社員）
$pdf->Cell(0,6,"（設立時業務執行社員）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "当会社の設立時業務執行社員は、次のとおりとする。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	//業務執行ID="業務執行社員になる"以外は、次へ。
	if ($promoterInfo['cmp_prm_business_execution_id'] != MST_BUSINESS_EXECUTION_ID_YES) continue;

	//人格種別によって、名前を設定する。
	$name = null;
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//個人
			$name .= $promoterInfo['cmp_prm_family_name'];
			$name .= " ";
			$name .= $promoterInfo['cmp_prm_first_name'];
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//法人(株式会社・有限会社のみ)
			$name .= $promoterInfo['cmp_prm_company_name'];
			break;
	}

	$x = $pdf->GetX();
	$x += 20;
	$pdf->SetX($x);

	$buf = null;
	$buf .= "業務執行社員";
	$pdf->Cell(35,6,$buf,$border,0,"L");

//氏名が長い場合があるので、MultiCellに変更する。
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


//（設立時代表社員）
$pdf->Cell(0,6,"（設立時代表社員）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "当会社の設立時代表社員は、次のとおりとする。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	//代表社員ID="代表社員になる"以外は、次へ。
	if ($promoterInfo['cmp_prm_representative_partner_id'] != MST_REPRESENTATIVE_PARTNER_ID_YES) continue;

	//人格種別によって、名前、住所を設定する。
	$name = null;
	$address = null;
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//個人
			$name .= $promoterInfo['cmp_prm_family_name'];
			$name .= " ";
			$name .= $promoterInfo['cmp_prm_first_name'];

			$address .= $mstPrefList[$promoterInfo['cmp_prm_pref_id']]['name'];
			$address .= $promoterInfo['cmp_prm_address1'];
			$address .= $promoterInfo['cmp_prm_address2'];
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//法人(株式会社・有限会社のみ)
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
	$buf .= "代表社員";
	$pdf->Cell(35,6,$buf,$border,0,"L");

//氏名が長い場合があるので、MultiCellに変更する。
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


//（定款の変更）
$pdf->Cell(0,6,"（定款の変更）",$border,0,"L");
$pdf->Ln();
$pdf->Cell(20,6,_no(++$no),$border,0,"L");

$buf = null;
$buf .= "定款の変更は、";
$buf .= $mstArticleChangeList[$companyInfo['tbl_company']['cmp_article_change_id']]['name'];
$buf .= "決定する。";
$buf = mb_convert_kana($buf, 'N');
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


//$buf = null;
//$buf .= "以上、";
//$buf .= $companyInfo['tbl_company']['cmp_company_name'];
//$buf .= "を設立のため、この定款を作成し、有限責任社員が次に記名押印する。";
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

	//人格種別によって、名前を設定する。
	$name = null;
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//個人
			$name .= $promoterInfo['cmp_prm_family_name'];
			$name .= " ";
			$name .= $promoterInfo['cmp_prm_first_name'];
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//法人(株式会社・有限会社のみ)
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
	$promoterName .= "他".($promoterCount - 1)."名";
}

$buf = null;
$buf .= "以上、";
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
//$buf .= "設立の為に、発起人 ".$promoterName." の\n定款作成代理人である".ADMINISTRATIVE_SCRIVENER_NAME."は、電磁的記録である本定款を作成し、\nこれに電子署名する。";
$buf .= "設立の為に、有限責任社員 ".$promoterName." の\n定款作成代理人である".ADMINISTRATIVE_SCRIVENER_NAME."は、電磁的記録である本定款を作成し、\nこれに電子署名する。";
//$buf = mb_convert_kana($buf, 'N');
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

	//人格種別によって、名前を設定する。
	$name = null;
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//個人
			$name .= $promoterInfo['cmp_prm_family_name'];
			$name .= " ";
			$name .= $promoterInfo['cmp_prm_first_name'];
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//法人(株式会社・有限会社のみ)
			$name .= $promoterInfo['cmp_prm_company_name'];
			break;
	}

	$buf = "有限責任社員";
	$pdf->Cell(35,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $name;
	$buf = mb_convert_kana($buf, 'N');
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
	return mb_convert_kana("第".$no."条", 'N');
}

?>
