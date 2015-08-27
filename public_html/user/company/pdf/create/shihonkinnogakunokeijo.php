<?php
/*
 * [新★会社設立.JP ツール]
 * PDF作成
 * 資本金の額の計上に関する証明書
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
_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] start.");

_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] SERVER = '".print_r($_SERVER,true)."'");


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

//振込日
$payYear = ((isset($inData['pay_year']) && !_IsNull($inData['pay_year']))?$inData['pay_year']:null);
$payMonth = ((isset($inData['pay_month']) && !_IsNull($inData['pay_month']))?$inData['pay_month']:null);
$payDay = ((isset($inData['pay_day']) && !_IsNull($inData['pay_day']))?$inData['pay_day']:null);


//初期値を設定する。
$undeleteOnly4def = false;

//権限によって、表示するユーザー情報を制限する。
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://権限無し

		_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
		_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
		_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

		$undeleteOnly4def = true;

		//自分のユーザー情報、会社情報のみ表示する。
		//ユーザーID、会社IDをチェックする。

		//会社IDを検索する。
		$relationCompanyId = _GetRelationCompanyId($loginInfo['usr_user_id']);


		_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] {ログインユーザー権限処理} →(ログイン)ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] {ログインユーザー権限処理} →(ログイン)会社ID = '".$relationCompanyId."'");
		_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] {ログインユーザー権限処理} →(パラメーター)ユーザーID = '".$userId."'");
		_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] {ログインユーザー権限処理} →(パラメーター)会社ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] {ログインユーザー権限処理} →(処理対象)ユーザーID = '".$userId."'");
		_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] {ログインユーザー権限処理} →(処理対象)会社ID = '".$companyId."'");

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
//振込日
$errFlag = false;
if (_IsNull($payYear)) $errFlag = true;
if (_IsNull($payMonth)) $errFlag = true;
if (_IsNull($payDay)) $errFlag = true;
if ($errFlag)  $errorList[] = "『振込日』を登録してください。";
//会社名
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "『商号(会社名)』を登録してください。";
//本店所在地
$errFlag = false;
if (_IsNull($companyInfo['tbl_company']['cmp_pref_id'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_address1'])) $errFlag = true;
if ($errFlag) $errorList[] = "『本店所在地』を登録してください。";
////資本金
//if (_IsNull($companyInfo['tbl_company']['cmp_capital'])) $errorList[] = "『資本金』を登録してください。";
//1株の単価
if (_IsNull($companyInfo['tbl_company']['cmp_stock_price'])) $errorList[] = "『1株の単価』を登録してください。";
//取締役
$errFlag = true;
foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
	switch ($boardInfo['cmp_bod_post_id']) {
		case MST_POST_ID_REP_DIRECTOR:
			//代表取締役

			//代表取締役のデータがあったことで、まずOK!!!
			$errFlag = false;

			if (_IsNull($boardInfo['cmp_bod_family_name']) || _IsNull($boardInfo['cmp_bod_first_name'])) {
				$errFlag = true;
				break 2;
			}

			//代表取締役が複数登録可能になったので、先頭1件を代表取締役の代表とする。1件チェックしたら処理を抜ける。
			break 2;
	}
}
if ($errFlag) $errorList[] = "『代表取締役』の『お名前』を登録してください。";
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
		}
	}
}
if ($errFlag) $errorList[] = "『出資金』の『株数』を登録してください。";



if (count($errorList) > 0) {
	//エラー有の場合
	_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] end. ERR!");


	$buf = "※PDFを作成するための情報が足りません。『株式会社設立情報登録』画面で、情報を入力してください。又は、『各種申請書類 印刷』画面で、情報を入力してください。";
	array_unshift($errorList, $buf);

	$_SESSION[SID_PDF_ERR_MSG] = $errorList;

	//エラー画面を表示する。
	header("Location: ../error.php");
	exit;
}


//マスタ情報を取得する。
$undeleteOnly = false;
$mstPostList = _GetMasterList('mst_post');		//役職マスタ
$mstPrefList = _GetMasterList('mst_pref');		//都道府県マスタ
unset($mstPrefList[MST_PREF_ID_OVERSEAS]);

//定数--------------------------------------------start
//フォントサイズを定義する。
//通常
$normalFontSize = 10;

//タイトル
$title = "資本金の額の計上に関する証明書";


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



$pdf->AddPage();

//タイトル
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


$pdf->SetFontSize(10);


//出資金を集計する。
$totalStockNumCash = 0;					//現金の株数合計
$totalInvestmentCash = 0;				//現金の金額合計
$totalInvestmentCash4Show = null;		//現金の金額合計(表示用)
$totalStockNumInkind = 0;				//現物の株数合計
$totalInvestmentInkind = 0;				//現物の金額合計
$totalInvestmentInkind4Show = null;		//現物の金額合計(表示用)

$totalInvestment = 0;					//出資金の合計
$totalInvestment4Show = null;			//出資金の合計(表示用)


foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {

	//発起人単位の小計
	$stockNumCash = 0;					//現金の株数
	$stockNumInkind = 0;				//現物の株数

	//出資金の登録はあるか？
	if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

		//現金の出資を集計する。
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_CASH])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_CASH]['investment_info'] as $iKey => $investmentInfo) {
				$stockNumCash += $investmentInfo['cmp_prm_inv_stock_num'];
			}
		}

		//現物の出資を集計する。
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {
				$stockNumInkind += $investmentInfo['cmp_prm_inv_stock_num'];
			}
		}
	}

	$totalStockNumCash += $stockNumCash;
	$totalStockNumInkind += $stockNumInkind;
}

//株数から出資金額を計算する。1株の単価(円)×現金の株数
$totalInvestmentCash = $companyInfo['tbl_company']['cmp_stock_price'] * $totalStockNumCash;
//数値を和表記に変換する。
$totalInvestmentCash4Show = "金"._ConvertNum2Ja($totalInvestmentCash)."円";

//株数から出資金額を計算する。1株の単価(円)×現物の株数
$totalInvestmentInkind = $companyInfo['tbl_company']['cmp_stock_price'] * $totalStockNumInkind;
//数値を和表記に変換する。
$totalInvestmentInkind4Show = "金"._ConvertNum2Ja($totalInvestmentInkind)."円";

//出資金の合計
$totalInvestment = $totalInvestmentCash + $totalInvestmentInkind;
//数値を和表記に変換する。
$totalInvestment4Show = "金"._ConvertNum2Ja($totalInvestment)."円";
$totalInvestment4Show2 = _ConvertNum2Ja($totalInvestment)."円";


$buf = "【１】";
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = "払込みを受けた金銭の額";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = "（会社計算規則第４３条第１項第１号）";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(13,6,$buf,$border,0,"L");

//現金金額
$buf = $totalInvestmentCash4Show;
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(130,6,$buf,$border,0,"R");
$pdf->Ln();


$buf = "【２】";
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = "給付を受けた金銭以外の財産の給付があった日における当該財産の価額";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = "（会社計算規則第４３条第１項第２号）";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(13,6,$buf,$border,0,"L");

//現物金額
$buf = $totalInvestmentInkind4Show;
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(130,6,$buf,$border,0,"R");
$pdf->Ln();


$buf = "【３】";
$pdf->Cell(13,6,$buf,$border,0,"L");

$buf = "【１】+【２】";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(13,6,$buf,$border,0,"L");

//金額
$buf = $totalInvestment4Show;
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(130,6,$buf,$border,0,"R");
$pdf->Ln(30);

$buf = null;
$buf .= "資本金の額";
$buf .= $totalInvestment4Show2;
$buf .= "は，会社法第４４５条及び会社計算規則第４３条の規定に従って計上されたことに相違ないことを証明する。";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(24);


//振込日
$payYearJp = _ConvertAD2Jp($payYear);
$buf = $payYearJp."年";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(20,6,$buf,$border,0,"L");
$buf = $payMonth."月";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"R");
$buf = $payDay."日";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"R");
$buf = null;
$pdf->Cell(0,6,$buf,$border,0,"R");

$pdf->Ln(20);


//本店所在地
$buf = null;
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
if (!_IsNull($companyInfo['tbl_company']['cmp_address2'])) {
	if (!_IsNull($buf)) $buf .= " ";
	$buf .= $companyInfo['tbl_company']['cmp_address2'];
}
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


//商号(会社名)
$buf = null;
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();


foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
	//"代表取締役"1人のみ表示する。
	if ($boardInfo['cmp_bod_post_id'] != MST_POST_ID_REP_DIRECTOR) continue;

	$buf = $mstPostList[$boardInfo['cmp_bod_post_id']]['name'];
	$pdf->Cell(20,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $boardInfo['cmp_bod_family_name'];
	$buf .= " ";
	$buf .= $boardInfo['cmp_bod_first_name'];
	$pdf->Cell(120,6,$buf,$border,0,"L");

	$buf = "印";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln(15);
	
	break;
}











//DBをクローズする。
_DB_Close($link);


//PDFを出力する。
$pdf->Output();

_Log("[/user/company/pdf/create/shihonkinnogakunokeijo.php] end. OK!");



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
