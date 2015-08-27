<?php
/*
 * [新★会社設立.JP ツール]
 * PDF作成
 * 財産引継書(合同会社用)
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
_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] start.");

_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] SERVER = '".print_r($_SERVER,true)."'");


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

		_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
		_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
		_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

		$undeleteOnly4def = true;

		//自分のユーザー情報、会社情報のみ表示する。
		//ユーザーID、会社IDをチェックする。

		//会社IDを検索する。
		$relationCompanyId = _GetRelationLlcId($loginInfo['usr_user_id']);


		_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] {ログインユーザー権限処理} →(ログイン)ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] {ログインユーザー権限処理} →(ログイン)会社ID = '".$relationCompanyId."'");
		_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] {ログインユーザー権限処理} →(パラメーター)ユーザーID = '".$userId."'");
		_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] {ログインユーザー権限処理} →(パラメーター)会社ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] {ログインユーザー権限処理} →(処理対象)ユーザーID = '".$userId."'");
		_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] {ログインユーザー権限処理} →(処理対象)会社ID = '".$companyId."'");

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
//振込日
$errFlag = false;
if (_IsNull($payYear)) $errFlag = true;
if (_IsNull($payMonth)) $errFlag = true;
if (_IsNull($payDay)) $errFlag = true;
if ($errFlag)  $errorList[] = "『振込日』を登録してください。";
//会社名
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "『商号(会社名)』を登録してください。";
////1株の単価
//if (_IsNull($companyInfo['tbl_company']['cmp_stock_price'])) $errorList[] = "『1株の単価』を登録してください。";
//発起人
$errFlag = false;
foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
	//人格種別によって、チェック項目を切り替える。
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//個人
			if (_IsNull($promoterInfo['cmp_prm_family_name']) || _IsNull($promoterInfo['cmp_prm_first_name'])) {
				$errFlag = true;
				break 2;
			}
			if (_IsNull($promoterInfo['cmp_prm_pref_id']) || _IsNull($promoterInfo['cmp_prm_address1'])) {
				$errFlag = true;
				break 2;
			}
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//法人(株式会社・有限会社のみ)
			if (_IsNull($promoterInfo['cmp_prm_company_name'])) {
				$errFlag = true;
				break 2;
			}
			if (_IsNull($promoterInfo['cmp_prm_company_pref_id']) || _IsNull($promoterInfo['cmp_prm_company_address1'])) {
				$errFlag = true;
				break 2;
			}
			break;
	}
}
if ($errFlag) $errorList[] = "『社員(出資者)』の『お名前』、『住所』又は、『会社名(法人)』、『本店所在地』を登録してください。";
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

//現物出資者がいるか？
$inkindFlag = false;
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
			$inkindFlag = true;
			break 2;
	}
}
if (!$inkindFlag) {
	//現物出資者がいない場合、本報告書は不要。
	$errorList = array();
	$errorList[] = "【注意】現物出資がありません。『財産引継書』は、作成されません。";
}


if (count($errorList) > 0) {
	//エラー有の場合
	_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] end. ERR!");


	$buf = "※PDFを作成するための情報が足りません。『合同会社設立LLC情報登録』画面で、情報を入力してください。又は、『各種申請書類 印刷』画面で、情報を入力してください。";
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
$title = "財産引継書";


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

	$pdf->AddPage();

	//タイトル
	$pdf->SetFontSize(18);
	$pdf->Cell(0,10,$title,$border,0,"C");
	$pdf->Ln(30);


	$pdf->SetFontSize(10);


	$no = 0;

	$buf = (++$no);
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(13,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= "現物出資財産等及びその価額";
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$pdf->Ln();

	//出資金を集計する。
	$totalInvestmentInkind = 0;				//現物の金額合計
	$totalInvestmentInkind4Show = null;		//現物の金額合計(表示用)

	//出資金の登録はあるか？
	if (isset($companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']])) {
		$investmentList = $companyInfo['tbl_company_promoter_investment'][$companyId][$promoterInfo['cmp_prm_no']];

		//現物の出資を集計する。
		if (isset($investmentList[MST_INVESTMENT_TYPE_ID_INKIND])) {

			$countInkind = 0;
			foreach ($investmentList[MST_INVESTMENT_TYPE_ID_INKIND]['investment_info'] as $iKey => $investmentInfo) {

				$investmentInkind = 0;				//現物の金額
				$investmentInkind4Show = null;		//現物の金額(表示用)

				//出資金額を計算する。単位を万円から円にする。
//				$investmentInkind = $investmentInfo['cmp_prm_inv_investment'] * 10000;
				$investmentInkind = $investmentInfo['cmp_prm_inv_investment'];
				//数値を和表記に変換する。
				$investmentInkind4Show = "金"._ConvertNum2Ja($investmentInkind)."円";

				//出資金額の合計を計算する。
				$totalInvestmentInkind += $investmentInkind;



				$countInkind++;

				$buf = null;
				$pdf->Cell(13,6,$buf,$border,0,"L");

				$buf = null;
				$buf .= "（";
				$buf .= $countInkind;
				$buf .= "）";
				$buf = mb_convert_kana($buf, 'N');
				$pdf->Cell(13,6,$buf,$border,0,"L");

				$buf = null;
				$buf .= $investmentInfo['cmp_prm_inv_in_kind'];
				$buf = mb_convert_kana($buf, 'N');
				$pdf->MultiCell(0,6,$buf,$border,"L");

				$buf = null;
				$pdf->Cell(13,6,$buf,$border,0,"L");

				$buf = null;
				$pdf->Cell(13,6,$buf,$border,0,"L");

				$buf = null;
				$buf .= "この価額";
				$pdf->Cell(20,6,$buf,$border,0,"L");

				$buf = null;
				$buf .= $investmentInkind4Show;
				$buf = mb_convert_kana($buf, 'N');
				$pdf->Cell(50,6,$buf,$border,0,"R");

				$pdf->Ln();
				$pdf->Ln();
			}
		}
	}

	//数値を和表記に変換する。
	$totalInvestmentInkind4Show = "金"._ConvertNum2Ja($totalInvestmentInkind)."円";


	$buf = null;
	$pdf->Cell(13,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= "合計価額";
	$pdf->Cell(33,6,$buf,$border,0,"L");

	//現物金額
	$buf = null;
	$buf .= $totalInvestmentInkind4Show;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(50,6,$buf,$border,0,"R");


	$pdf->Ln(30);

	$buf = null;
	$buf .= "私所有の上記財産を現物出資財産として給付します。";
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


	$buf = "住　所";
	$pdf->Cell(30,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $address;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

//	$buf = "発起人";
	$buf = "有限責任社員";
	$pdf->Cell(30,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $name;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(120,6,$buf,$border,0,"L");

	$buf = "印";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln(15);


	//商号(会社名)
	$buf = null;
	$buf .= $companyInfo['tbl_company']['cmp_company_name'];
	$buf .= " ";
	$buf .= "発起人";
	$buf .= " ";
	$buf .= "御中";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->Ln();
}














//DBをクローズする。
_DB_Close($link);


//PDFを出力する。
$pdf->Output();

_Log("[/user/llc/pdf/create/zaisanhikitugisho.php] end. OK!");



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
