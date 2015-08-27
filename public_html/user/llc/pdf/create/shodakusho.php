<?php
/*
 * [新★会社設立.JP ツール]
 * PDF作成
 * 就任承諾書(合同会社用)
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
_Log("[/user/llc/pdf/create/shodakusho.php] start.");

_Log("[/user/llc/pdf/create/shodakusho.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/llc/pdf/create/shodakusho.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/llc/pdf/create/shodakusho.php] SERVER = '".print_r($_SERVER,true)."'");


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

//定款作成日
$articleCreateYear = ((isset($inData['article_create_year']) && !_IsNull($inData['article_create_year']))?$inData['article_create_year']:null);
$articleCreateMonth = ((isset($inData['article_create_month']) && !_IsNull($inData['article_create_month']))?$inData['article_create_month']:null);
$articleCreateDay = ((isset($inData['article_create_day']) && !_IsNull($inData['article_create_day']))?$inData['article_create_day']:null);


//初期値を設定する。
$undeleteOnly4def = false;

//権限によって、表示するユーザー情報を制限する。
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://権限無し

		_Log("[/user/llc/pdf/create/shodakusho.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
		_Log("[/user/llc/pdf/create/shodakusho.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
		_Log("[/user/llc/pdf/create/shodakusho.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

		$undeleteOnly4def = true;

		//自分のユーザー情報、会社情報のみ表示する。
		//ユーザーID、会社IDをチェックする。

		//会社IDを検索する。
		$relationCompanyId = _GetRelationLlcId($loginInfo['usr_user_id']);


		_Log("[/user/llc/pdf/create/shodakusho.php] {ログインユーザー権限処理} →(ログイン)ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/llc/pdf/create/shodakusho.php] {ログインユーザー権限処理} →(ログイン)会社ID = '".$relationCompanyId."'");
		_Log("[/user/llc/pdf/create/shodakusho.php] {ログインユーザー権限処理} →(パラメーター)ユーザーID = '".$userId."'");
		_Log("[/user/llc/pdf/create/shodakusho.php] {ログインユーザー権限処理} →(パラメーター)会社ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/llc/pdf/create/shodakusho.php] {ログインユーザー権限処理} →(処理対象)ユーザーID = '".$userId."'");
		_Log("[/user/llc/pdf/create/shodakusho.php] {ログインユーザー権限処理} →(処理対象)会社ID = '".$companyId."'");

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
//定款作成日
$errFlag = false;
if (_IsNull($articleCreateYear)) $errFlag = true;
if (_IsNull($articleCreateMonth)) $errFlag = true;
if (_IsNull($articleCreateDay)) $errFlag = true;
if ($errFlag)  $errorList[] = "『定款作成日』を登録してください。";
//会社名
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "『商号(会社名)』を登録してください。";
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




if (count($errorList) > 0) {
	//エラー有の場合
	_Log("[/user/llc/pdf/create/shodakusho.php] end. ERR!");


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
$title = "就任承諾書";


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



//定款作成日
$articleCreateDate = null;
$articleCreateDate .= _ConvertAD2Jp($articleCreateYear);
$articleCreateDate .= "年";
$articleCreateDate .= $articleCreateMonth;
$articleCreateDate .= "月";
$articleCreateDate .= $articleCreateDay;
$articleCreateDate .= "日";
$articleCreateDate = mb_convert_kana($articleCreateDate, 'N');



//$topMessage = null;
//$topMessage .= "私は、";
//$topMessage .= $articleCreateDate;
//$topMessage .= "の貴社定款において、";
//$topMessage .= "代表社員";
//$topMessage .= "に選任されましたので、その就任を承諾いたします。";


foreach ($companyInfo['tbl_company_promoter'] as $key => $promoterInfo) {
//	//代表社員ID="代表社員になる"以外は、次へ。
//	if ($promoterInfo['cmp_prm_representative_partner_id'] != MST_REPRESENTATIVE_PARTNER_ID_YES) continue;

	//業務執行ID="業務執行社員になる"以外は、次へ。
	if ($promoterInfo['cmp_prm_business_execution_id'] != MST_BUSINESS_EXECUTION_ID_YES) continue;

	$topMessage = null;

	//代表社員ID="代表社員になる"か？
	if ($promoterInfo['cmp_prm_representative_partner_id'] == MST_REPRESENTATIVE_PARTNER_ID_YES) {
		$topMessage .= "私は、";
		$topMessage .= $articleCreateDate;
		$topMessage .= "の貴社定款において、";
		$topMessage .= "業務執行社員及び代表社員";
		$topMessage .= "に選任されましたので、その就任を承諾いたします。";
	} else {
		$topMessage .= "私は、";
		$topMessage .= $articleCreateDate;
		$topMessage .= "の貴社定款において、";
		$topMessage .= "業務執行社員";
		$topMessage .= "に選任されましたので、その就任を承諾いたします。";
	}

	//人格種別によって、名前、住所を設定する。
	$name = null;
	$address = null;
	$nameTitle = null;
	switch ($promoterInfo['cmp_prm_personal_type_id']) {
		case MST_PERSONAL_TYPE_ID_PERSONAL:
			//個人
			$name .= $promoterInfo['cmp_prm_family_name'];
			$name .= " ";
			$name .= $promoterInfo['cmp_prm_first_name'];

			$address .= $mstPrefList[$promoterInfo['cmp_prm_pref_id']]['name'];
			$address .= $promoterInfo['cmp_prm_address1'];
//			$address .= $promoterInfo['cmp_prm_address2'];
			if (!_IsNull($promoterInfo['cmp_prm_address2'])) {
				if (!_IsNull($address)) $address .= " ";
				$address .= $promoterInfo['cmp_prm_address2'];
			}

			$nameTitle = "氏　名";
			break;
		case MST_PERSONAL_TYPE_ID_CORPORATION:
			//法人(株式会社・有限会社のみ)
			$name .= $promoterInfo['cmp_prm_company_name'];

			$address .= $mstPrefList[$promoterInfo['cmp_prm_company_pref_id']]['name'];
			$address .= $promoterInfo['cmp_prm_company_address1'];
			if (!_IsNull($promoterInfo['cmp_prm_company_address2'])) {
				if (!_IsNull($address)) $address .= " ";
				$address .= $promoterInfo['cmp_prm_company_address2'];
			}

			$nameTitle = "会社名";
			break;
	}

	$pdf->AddPage();

	//タイトル
	$pdf->SetFontSize(18);
	$pdf->Cell(0,10,$title,$border,0,"C");
	$pdf->Ln(30);


	$pdf->SetFontSize(10);

	//メッセージ
	$pdf->MultiCell(0,6,$topMessage,$border,"L");
	$pdf->Ln(90);


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

	$pdf->Ln(20);


	//住所
	$buf = "（　住　所　）";
	$pdf->Cell(30,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $address;
	$buf = mb_convert_kana($buf, 'N');
	$pdf->MultiCell(0,6,$buf,$border,"L");

	$pdf->Ln(5);

	//氏名
	$buf = "（　".$nameTitle."　）";
	$pdf->Cell(30,6,$buf,$border,0,"L");

	$bufX = $pdf->GetX();

	$x = 170;
	$pdf->SetX($x);

	$buf = null;
	$buf .= "印";
	$pdf->Cell(20,6,$buf,$border,0,"L");

	$pdf->SetX($bufX);

	$buf = null;
	$buf .= $name;
//	$pdf->MultiCell(0,6,$buf,$border,"L");
	$pdf->MultiCell(120,6,$buf,$border,"L");

	$pdf->Ln(5);


	//商号
	$buf = "（　商　号　）";
	$pdf->Cell(30,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= $companyInfo['tbl_company']['cmp_company_name'];
	$pdf->Cell(100,6,$buf,$border,0,"L");

	$buf = null;
	$buf .= "御中";
	$pdf->Cell(20,6,$buf,$border,0,"L");

//	$buf = null;
//	$buf .= "印";
//	$pdf->Cell(0,6,$buf,$border,0,"L");
}













//DBをクローズする。
_DB_Close($link);


//PDFを出力する。
$pdf->Output();

_Log("[/user/llc/pdf/create/shodakusho.php] end. OK!");



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
