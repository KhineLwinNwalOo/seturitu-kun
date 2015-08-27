<?php
/*
 * [新★会社設立.JP ツール]
 * PDF作成
 * 株式会社設立登記申請書(合同会社用)
 *
 * 更新履歴：2011/12/07	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../../common/include.ini");
include_once("../../../../common/libs/fpdf/mbfpdf.php");


_LogDelete();
//_LogBackup();
_Log("[/user/llc/pdf/create/tokishinseisho.php] start.");

_Log("[/user/llc/pdf/create/tokishinseisho.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/llc/pdf/create/tokishinseisho.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/llc/pdf/create/tokishinseisho.php] SERVER = '".print_r($_SERVER,true)."'");


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

		_Log("[/user/llc/pdf/create/tokishinseisho.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

		$undeleteOnly4def = true;

		//自分のユーザー情報、会社情報のみ表示する。
		//ユーザーID、会社IDをチェックする。

		//会社IDを検索する。
		$relationCompanyId = _GetRelationLlcId($loginInfo['usr_user_id']);


		_Log("[/user/llc/pdf/create/tokishinseisho.php] {ログインユーザー権限処理} →(ログイン)ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {ログインユーザー権限処理} →(ログイン)会社ID = '".$relationCompanyId."'");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {ログインユーザー権限処理} →(パラメーター)ユーザーID = '".$userId."'");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {ログインユーザー権限処理} →(パラメーター)会社ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/llc/pdf/create/tokishinseisho.php] {ログインユーザー権限処理} →(処理対象)ユーザーID = '".$userId."'");
		_Log("[/user/llc/pdf/create/tokishinseisho.php] {ログインユーザー権限処理} →(処理対象)会社ID = '".$companyId."'");

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
//本店所在地
$errFlag = false;
if (_IsNull($companyInfo['tbl_company']['cmp_pref_id'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_address1'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_address2'])) $errFlag = true;
if ($errFlag)  $errorList[] = "『本店所在地』を登録してください。(『上記以降』の詳細部分も登録してください。)";
//資本金
if (_IsNull($companyInfo['tbl_company']['cmp_capital'])) $errorList[] = "『資本金』を登録してください。";
////取締役
//$errFlag = false;
//foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
//	if (_IsNull($boardInfo['cmp_bod_family_name']) || _IsNull($boardInfo['cmp_bod_first_name'])) {
//		$errFlag = true;
//		break;
//	}
//	if (_IsNull($boardInfo['cmp_bod_pref_id']) || _IsNull($boardInfo['cmp_bod_address1'])) {
//		$errFlag = true;
//		break;
//	}
//}
//if ($errFlag) $errorList[] = "『取締役』の『お名前』、『住所』を登録してください。";
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
//目的
$errFlag = true;
foreach ($companyInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (!_IsNull($purposeInfo['cmp_pps_purpose'])) {
		$errFlag = false;
		break;
	}
}
if ($errFlag) $errorList[] = "『目的』を登録してください。";
////発行可能株式の総数
//if (_IsNull($companyInfo['tbl_company']['cmp_stock_total_num'])) $errorList[] = "『発行可能株式の総数』を登録してください。";
////1株の単価
//if (_IsNull($companyInfo['tbl_company']['cmp_stock_price'])) $errorList[] = "『1株の単価』を登録してください。";

if (count($errorList) > 0) {
	//エラー有の場合
	_Log("[/user/llc/pdf/create/tokishinseisho.php] end. ERR!");


	$buf = "※PDFを作成するための情報が足りません。『合同会社設立LLC情報登録』画面で、情報を入力してください。又は、『各種申請書類 印刷』画面で、情報を入力してください。";
	array_unshift($errorList, $buf);

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
$title = "合同会社設立登記申請書";


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


$buf = "商　　　　　号";
$pdf->Cell(30,6,$buf,$border,0,"L");

//商号(会社名)
$buf = null;
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$buf = "本　　　　　店";
$pdf->Cell(30,6,$buf,$border,0,"L");

//本店所在地
$buf = null;
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
$buf .= $companyInfo['tbl_company']['cmp_address2'];
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$buf = "登 記 の 事 由";
$pdf->Cell(30,6,$buf,$border,0,"L");

//本店所在地
$buf = null;
$buf .= _ConvertAD2Jp($pdfCreateYear);
$buf .= "年";
$buf .= $pdfCreateMonth;
$buf .= "月";
$buf .= $pdfCreateDay;
$buf .= "日";
$buf .= "発起設立の手続き終了";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$buf = "登記すべき事項";
$pdf->Cell(30,6,$buf,$border,0,"L");

//本店所在地
$buf = null;
$buf .= "別紙のとおり";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$pdf->Ln();

$buf = "課税標準金額";
$pdf->Cell(30,6,$buf,$border,0,"L");

//資本金
//資本金の単位を万円から円にする。
//$capital = $companyInfo['tbl_company']['cmp_capital'] * 10000;
$capital = $companyInfo['tbl_company']['cmp_capital'];

$buf = null;
$buf .= "金";
$buf .= " ";
$buf .= number_format($capital);
$buf .= " ";
$buf .= "円";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

$buf = "登録免許税額";
$pdf->Cell(30,6,$buf,$border,0,"L");

$buf = null;
$buf .= "金";
$buf .= " ";
$buf .= number_format(LICENSE_TAX_LLC);
$buf .= " ";
$buf .= "円";
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln();

//$buf = null;
//$pdf->Cell(30,6,$buf,$border,0,"L");
//$buf = "非課税又は軽減措置";
//$pdf->Cell(40,6,$buf,$border,0,"L");
//$buf = "租税特別措置法第８４条の５";
//$pdf->Cell(0,6,$buf,$border,0,"L");
//$pdf->Ln();

$pdf->Ln();
//$pdf->Ln();

$buf = "添　付　書　類";
$pdf->Cell(30,6,$buf,$border,0,"L");
$buf = "印鑑証明書";
$pdf->Cell(100,6,$buf,$border,0,"L");
$buf = "１通";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(30,6,$buf,$border,0,"L");
$buf = "定款";
$pdf->Cell(100,6,$buf,$border,0,"L");
$buf = "１通";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(30,6,$buf,$border,0,"L");
$buf = "就任承諾書";
$pdf->Cell(100,6,$buf,$border,0,"L");
$buf = "１通";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(30,6,$buf,$border,0,"L");
$buf = "払込証明書";
$pdf->Cell(100,6,$buf,$border,0,"L");
$buf = "１通";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

$buf = null;
$pdf->Cell(30,6,$buf,$border,0,"L");
$buf = "本店所在地決定決議書";
$pdf->Cell(100,6,$buf,$border,0,"L");
$buf = "１通";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();

//$buf = null;
//$pdf->Cell(30,6,$buf,$border,0,"L");
//$buf = "委任状";
//$pdf->Cell(100,6,$buf,$border,0,"L");
//$buf = "１通";
//$pdf->Cell(0,6,$buf,$border,0,"L");
//$pdf->Ln();

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
if ($inkindFlag) {
	//現物出資者がいる場合、添付書類を追加する。
	$buf = null;
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = "資本金の額の計上に関する証明書";
	$pdf->Cell(100,6,$buf,$border,0,"L");
	$buf = "１通";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln();
	
	$buf = null;
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = "調査報告書";
	$pdf->Cell(100,6,$buf,$border,0,"L");
	$buf = "１通";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln();
	
	$buf = null;
	$pdf->Cell(30,6,$buf,$border,0,"L");
	$buf = "財産引継書";
	$pdf->Cell(100,6,$buf,$border,0,"L");
	$buf = "１通";
	$pdf->Cell(0,6,$buf,$border,0,"L");
	$pdf->Ln();
}

//$pdf->Ln();
//
//$buf = "印鑑届出の有無";
//$pdf->Cell(30,6,$buf,$border,0,"L");
//$buf = "有";
//$pdf->Cell(20,6,$buf,$border,0,"L");
//$buf = "※管轄登記所に別途提出";
//$pdf->Cell(0,6,$buf,$border,0,"L");
//$pdf->Ln();


$pdf->Ln(15);


$buf = null;
$buf .= "上記のとおり登記を申請する。";
$pdf->MultiCell(0,6,$buf,$border,"L");


//作成日
$pdfCreateYearJp = _ConvertAD2Jp($pdfCreateYear);
$buf = $pdfCreateYearJp."年";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(20,6,$buf,$border,0,"L");
$buf = $pdfCreateMonth."月";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"R");
$buf = $pdfCreateDay."日";
$buf = mb_convert_kana($buf, 'N');
$pdf->Cell(12,6,$buf,$border,0,"R");
$buf = null;
$pdf->Cell(0,6,$buf,$border,0,"R");

$pdf->Ln();
$pdf->Ln();


$buf = "申　請　人";
$pdf->Cell(30,6,$buf,$border,0,"L");

//本店所在地
$buf = null;
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
$buf .= $companyInfo['tbl_company']['cmp_address2'];
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$buf = null;
$pdf->Cell(30,6,$buf,$border,0,"L");

//商号(会社名)
$buf = null;
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$pdf->MultiCell(0,6,$buf,$border,"L");


////代表取締役を取得する。
//$repBoardInfo = null;
//foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
//	switch ($boardInfo['cmp_bod_post_id']) {
//		case MST_POST_ID_REP_DIRECTOR:
//			//代表取締役
//			$repBoardInfo = $boardInfo;
//			break 2;
//	}
//}
//
//$buf = null;
//$pdf->Cell(30,6,$buf,$border,0,"L");
//
////役員住所
//$buf = null;
//$buf .= $mstPrefList[$repBoardInfo['cmp_bod_pref_id']]['name'];
//$buf .= $repBoardInfo['cmp_bod_address1'];
//$buf .= $repBoardInfo['cmp_bod_address2'];
//$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
//$buf = null;
//$pdf->Cell(30,6,$buf,$border,0,"L");
//
////役員名前
//$buf = null;
//$buf .= $mstPostList[$boardInfo['cmp_bod_post_id']]['name'];
//$buf .= " ";
//$buf .= $repBoardInfo['cmp_bod_family_name'];
//$buf .= " ";
//$buf .= $repBoardInfo['cmp_bod_first_name'];
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
//$pdf->Ln();

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

	$buf = null;
	$pdf->Cell(30,6,$buf,$border,0,"L");
	
	//代表社員住所
	$buf = null;
	$buf .= $address;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$buf = null;
	$pdf->Cell(30,6,$buf,$border,0,"L");
	
	//代表社員名前
	$buf = null;
	$buf .= "代表社員";
	$buf .= " ";
	$buf .= $name;
	$pdf->MultiCell(0,6,$buf,$border,"L");

	//一人だけでOK。
	break;
}
$pdf->Ln();


//$buf = "上 記 代 理 人";
//$pdf->Cell(30,6,$buf,$border,0,"L");
//
//$buf = null;
//$buf .= " ";
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
//$buf = null;
//$pdf->Cell(30,6,$buf,$border,0,"L");
//
//$buf = null;
//$buf .= " ";
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
//$pdf->Ln();


$buf = "宛 先 登 記 所";
$pdf->Cell(30,6,$buf,$border,0,"L");

$buf = null;
$buf .= "　　　　　　　　　　　法務局　　　　　　　　　　　　　　　　　御中";
$pdf->Cell(0,6,$buf,$border,0,"L");

$pdf->Ln();


$buf = "登 記 所 コード";
$pdf->Cell(30,6,$buf,$border,0,"L");

$buf = null;
$buf .= " ";
$pdf->Cell(0,6,$buf,$border,0,"L");



//$pdf->Ln(30);


$pdf->AddPage();


$pdf->SetX(150);

$buf = "収入印紙貼付台紙";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln();
$pdf->Ln();

$pdf->SetX(170);

$buf = null;
$buf .= "収　入\n印　紙";
$pdf->MultiCell(20,10,$buf,1,"C");


$pdf->AddPage();


$buf = "別　紙　（登　記　す　べ　き　事　項）";
$pdf->Cell(0,6,$buf,$border,0,"L");

$pdf->Ln();
$pdf->Ln();

//商号(会社名)
$buf = null;
$buf .= "「商号」";
$buf .= $companyInfo['tbl_company']['cmp_company_name'];
$pdf->MultiCell(0,6,$buf,$border,"L");

//本店所在地
$buf = null;
$buf .= "「本店」";
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
$buf .= $companyInfo['tbl_company']['cmp_address2'];
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

//公告をする方法
$buf = null;
$buf .= "「公告をする方法」官報に掲載する方法により行う。";
$pdf->MultiCell(0,6,$buf,$border,"L");

//目的
$buf = null;
$buf .= "「目的」";
$pdf->MultiCell(0,6,$buf,$border,"L");

$i = 0;
foreach ($companyInfo['tbl_company_purpose']['purpose_info'] as $key => $purposeInfo) {
	if (_IsNull($purposeInfo['cmp_pps_purpose'])) continue;

	$buf = (++$i)."．";
	$buf = mb_convert_kana($buf, 'N');
	$pdf->Cell(12,6,$buf,$border,0,"L");

	$buf = $purposeInfo['cmp_pps_purpose'];
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");
}

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

////発行可能株式総数
//$buf = null;
//$buf .= "「発行可能株式総数」";
//$buf .= $companyInfo['tbl_company']['cmp_stock_total_num'];
//$buf .= "株";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
////発行済株式の総数
////設立時発行株式の数 = 資本金(万円) / 1株の単価(円)
//$stockNum = ($companyInfo['tbl_company']['cmp_capital'] * 10000) / $companyInfo['tbl_company']['cmp_stock_price'];
//$stockNum = floor($stockNum);//端数の切り捨て
//$stockNum = _ConvertNum2Ja($stockNum);
//
//$buf = null;
//$buf .= "「発行済株式の総数」";
//$buf .= $stockNum;
//$buf .= "株";
//$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");

//資本金の額
//資本金の単位を万円から円にする。
//$capital = $companyInfo['tbl_company']['cmp_capital'] * 10000;
$capital = $companyInfo['tbl_company']['cmp_capital'];
$capital = _ConvertNum2Ja($capital);

$buf = null;
$buf .= "「資本金の額」";
$buf .= "金";
$buf .= $capital;
$buf .= "円";
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

////株式の譲渡制限に関する規定
//$buf = null;
//$buf .= "「株式の譲渡制限に関する規定」当会社の発行する株式はすべて譲渡制限株式とし、これを譲渡により取得するには、株主総会の承認を要する。";
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
////役員に関する事項
//foreach ($mstPostList as $mpKey => $mstPostInfo) {
//	foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
//		if ($mstPostInfo['id'] != $boardInfo['cmp_bod_post_id']) continue;
//
//		$buf = null;
//		$buf .= "「役員に関する事項」";
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//
//		$buf = null;
//		$buf .= "「資格」";
//		if ($mstPostInfo['id'] == MST_POST_ID_REP_DIRECTOR) {
//			//代表取締役の場合、"取締役"として表示する。
//			$buf .= $mstPostList[MST_POST_ID_DIRECTOR]['name'];
//		} else {
//			$buf .= $mstPostInfo['name'];
//		}
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//
//		$buf = null;
//		$buf .= "「氏名」";
//		$buf .= $boardInfo['cmp_bod_family_name'];
//		$buf .= " ";
//		$buf .= $boardInfo['cmp_bod_first_name'];
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//	}
//}
//
////役員に関する事項
//$buf = null;
//$buf .= "「役員に関する事項」";
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
//$buf = null;
//$buf .= "「資格」";
//$buf .= $mstPostList[$repBoardInfo['cmp_bod_post_id']]['name'];
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
////役員住所
//$buf = null;
//$buf .= "「住所」";
//$buf .= $mstPrefList[$repBoardInfo['cmp_bod_pref_id']]['name'];
//$buf .= $repBoardInfo['cmp_bod_address1'];
//$buf .= $repBoardInfo['cmp_bod_address2'];
//$buf = mb_convert_kana($buf, 'N');
//$pdf->MultiCell(0,6,$buf,$border,"L");
//
////役員名前
//$buf = null;
//$buf .= "「氏名」";
//$buf .= $repBoardInfo['cmp_bod_family_name'];
//$buf .= " ";
//$buf .= $repBoardInfo['cmp_bod_first_name'];
//$pdf->MultiCell(0,6,$buf,$border,"L");

//社員に関する事項
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

	$buf = null;
	$buf .= "「社員に関する事項」";
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$buf = null;
	$buf .= "「資格」";
	$buf .= "業務執行社員";
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$buf = null;
	$buf .= "「氏名」";
	$buf .= $name;
	$pdf->MultiCell(0,6,$buf,$border,"L");
}

//社員に関する事項
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

	$buf = null;
	$buf .= "「社員に関する事項」";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	$buf = null;
	$buf .= "「資格」";
	$buf .= "代表社員";
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	//役員住所
	$buf = null;
	$buf .= "「住所」";
	$buf .= $address;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");
	
	//役員名前
	$buf = null;
	$buf .= "「氏名」";
	$buf .= $name;
	$pdf->MultiCell(0,6,$buf,$border,"L");
}

//switch ($companyInfo['tbl_company']['cmp_board_formation_id']) {
//	case MST_BOARD_FORMATION_ID_1_10:
//		//取締役会を設置しない　役員1〜10人で設立
//		break;
//	case MST_BOARD_FORMATION_ID_3_1:
//		//取締役会を設置する　役員3人と監査役1人で設立
//
//		//取締役会設置会社に関する事項
//		$buf = null;
//		$buf .= "「取締役会設置会社に関する事項」取締役会設置会社";
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//
//		//監査役設置会社に関する事項
//		$buf = null;
//		$buf .= "「監査役設置会社に関する事項」監査役設置会社";
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//
//		//取締役の責任免除の定め
//		$buf = null;
//		$buf .= "「取締役の責任免除の定め」当会社は、会社法第４２３条第１項の行為に関する取締役の責任について、当該取締役が職務を行うにつき善意でかつ重大な過失がない場合において、責任の原因となった事実の内容、当該取締役の職務の執行の状況その他の事情を勘案して特に必要と認めるとき等法令に定める要件に該当する場合には、会社法第４２５条第１項に定める範囲で取締役会の決議により免除することができる。";
//		$pdf->MultiCell(0,6,$buf,$border,"L");
//		break;
//}

//登記記録に関する事項
$buf = null;
$buf .= "「登記記録に関する事項」設立";
$pdf->MultiCell(0,6,$buf,$border,"L");


//DBをクローズする。
_DB_Close($link);


//PDFを出力する。
$pdf->Output();

_Log("[/user/llc/pdf/create/tokishinseisho.php] end. OK!");



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
