<?php
/*
 * [新★会社設立.JP ツール]
 * PDF作成
 * 印鑑（改印）届書
 *
 * 更新履歴：2008/12/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../../../common/include.ini");
//include_once("../../../../common/libs/fpdf/mbfpdf.php");
include_once("../../../../common/libs/fpdf/mbfpdf_fpdi.php");


_LogDelete();
//_LogBackup();
_Log("[/user/company/pdf/create/inkantodokesho.php] start.");

_Log("[/user/company/pdf/create/inkantodokesho.php] POST = '".print_r($_POST,true)."'");
_Log("[/user/company/pdf/create/inkantodokesho.php] GET = '".print_r($_GET,true)."'");
_Log("[/user/company/pdf/create/inkantodokesho.php] SERVER = '".print_r($_SERVER,true)."'");


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

//生年月日
$birthYear = ((isset($inData['birth_year']) && !_IsNull($inData['birth_year']))?$inData['birth_year']:null);
$birthMonth = ((isset($inData['birth_month']) && !_IsNull($inData['birth_month']))?$inData['birth_month']:null);
$birthDay = ((isset($inData['birth_day']) && !_IsNull($inData['birth_day']))?$inData['birth_day']:null);


//動作モード
$mode = (isset($inData['mode'])?$inData['mode']:null);

//初期値を設定する。
$undeleteOnly4def = false;

//権限によって、表示するユーザー情報を制限する。
switch($loginInfo['usr_auth_id']){
	case AUTH_NON://権限無し

		_Log("[/user/company/pdf/create/inkantodokesho.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

		$undeleteOnly4def = true;

		//自分のユーザー情報、会社情報のみ表示する。
		//ユーザーID、会社IDをチェックする。

		//会社IDを検索する。
		$relationCompanyId = _GetRelationCompanyId($loginInfo['usr_user_id']);


		_Log("[/user/company/pdf/create/inkantodokesho.php] {ログインユーザー権限処理} →(ログイン)ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {ログインユーザー権限処理} →(ログイン)会社ID = '".$relationCompanyId."'");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {ログインユーザー権限処理} →(パラメーター)ユーザーID = '".$userId."'");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {ログインユーザー権限処理} →(パラメーター)会社ID = '".$companyId."'");

		if ($userId != $loginInfo['usr_user_id']) $userId = $loginInfo['usr_user_id'];
		if ($companyId != $relationCompanyId) $companyId = $relationCompanyId;

		_Log("[/user/company/pdf/create/inkantodokesho.php] {ログインユーザー権限処理} →(処理対象)ユーザーID = '".$userId."'");
		_Log("[/user/company/pdf/create/inkantodokesho.php] {ログインユーザー権限処理} →(処理対象)会社ID = '".$companyId."'");

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
//動作モード
if (_IsNull($mode)) $errorList[] = "『代理人』を指定してください。";
//代表取締役の生年月日
$errFlag = false;
if (_IsNull($birthYear)) $errFlag = true;
if (_IsNull($birthMonth)) $errFlag = true;
if (_IsNull($birthDay)) $errFlag = true;
if ($errFlag)  $errorList[] = "『代表取締役の生年月日』を登録してください。";
//代理人
switch ($mode) {
	case PDF_MODE_INKAN_DIRECTOR:
		//代表取締役が登記申請に行く場合
		break;
	case PDF_MODE_INKAN_OTHER:
		//代理人が登記申請に行く場合
		//氏名
		$errFlag = false;
		if (!isset($inData['agent_family_name']) || _IsNull($inData['agent_family_name'])) $errFlag = true;
		if (!isset($inData['agent_first_name']) || _IsNull($inData['agent_first_name'])) $errFlag = true;
		if ($errFlag)  $errorList[] = "『代理人』の『氏名』を登録してください。";
		//氏名(フリガナ)
		$errFlag = false;
		if (!isset($inData['agent_family_name_kana']) || _IsNull($inData['agent_family_name_kana'])) $errFlag = true;
		if (!isset($inData['agent_first_name_kana']) || _IsNull($inData['agent_first_name_kana'])) $errFlag = true;
		if ($errFlag)  $errorList[] = "『代理人』の『氏名(フリガナ)』を登録してください。";
		//住所
		$errFlag = false;
		if (!isset($inData['agent_pref_id']) || _IsNull($inData['agent_pref_id'])) $errFlag = true;
		if (!isset($inData['agent_address1']) || _IsNull($inData['agent_address1'])) $errFlag = true;
		if ($errFlag)  $errorList[] = "『代理人』の『住所』を登録してください。";
		break;
	default:
		$errorList[] = "『代理人』を指定してください。";
		break;
}
//会社名
if (_IsNull($companyInfo['tbl_company']['cmp_company_name'])) $errorList[] = "『商号(会社名)』を登録してください。";
//本店所在地
$errFlag = false;
if (_IsNull($companyInfo['tbl_company']['cmp_pref_id'])) $errFlag = true;
if (_IsNull($companyInfo['tbl_company']['cmp_address1'])) $errFlag = true;
if ($errFlag)  $errorList[] = "『本店所在地』を登録してください。";
//役員
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
			if (_IsNull($boardInfo['cmp_bod_family_name_kana']) || _IsNull($boardInfo['cmp_bod_first_name_kana'])) {
				$errFlag = true;
				break 2;
			}
			if (_IsNull($boardInfo['cmp_bod_pref_id']) || _IsNull($boardInfo['cmp_bod_address1'])) {
				$errFlag = true;
				break 2;
			}
			
			//代表取締役が複数登録可能になったので、先頭1件を代表取締役の代表とする。1件チェックしたら処理を抜ける。
			break 2;
	}
}
if ($errFlag) $errorList[] = "『代表取締役』の『お名前』、『お名前(フリガナ)』、『住所』を登録してください。";




if (count($errorList) > 0) {
	//エラー有の場合
	_Log("[/user/company/pdf/create/inkantodokesho.php] end. ERR!");


	$buf = "※PDFを作成するための情報が足りません。『株式会社設立情報登録』画面で、情報を入力してください。又は、『定款認証』画面で、情報を入力してください。";
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


//定数--------------------------------------------start
//フォントサイズを定義する。
//通常
$normalFontSize = 10;

//タイトル
$title = "印鑑（改印）届書";


//[デバッグ用]
//ボーダー
$border = 0;

//背景色
$fill = 0;

//背景色
$bgR = 239;
$bgG = 194;
$bgB = 238;

//定数--------------------------------------------end


// EUC-JP->SJIS 変換を自動的に行なわせる場合に mbfpdf.php 内の $EUC2SJIS を
// true に修正するか、このように実行時に true に設定しても変換してくます。
//$GLOBALS['EUC2SJIS'] = true;

//PDFのサイズを設定する。デフォルト=FPDF($orientation='P',$unit='mm',$format='A4')
//'B5' = 182.0mm×257.0mm
$pdf = new MBFPDF('P', 'mm', array(182.0, 257.0));

//フォントを設定する。
$pdf->AddMBFont(GOTHIC ,'SJIS');
$pdf->AddMBFont(PGOTHIC,'SJIS');
$pdf->AddMBFont(MINCHO ,'SJIS');
$pdf->AddMBFont(PMINCHO,'SJIS');
$pdf->AddMBFont(KOZMIN ,'SJIS');

//マージンを設定する。
$pdf->SetLeftMargin(0);
$pdf->SetRightMargin(0);
$pdf->SetTopMargin(0);


$pdf->SetFont(MINCHO,'',$normalFontSize);

//自動改ページモードをON(true)、ページの下端からの距離（マージン）が2 mmになった場合、改行するように設定する。
$pdf->SetAutoPageBreak(true, 0);

//ドキュメントのタイトルを設定する。
$pdf->SetTitle($title);
//ドキュメントの主題(subject)を設定する。
$pdf->SetSubject($title);


//印鑑届書の雛形を読み込む。→読み込んだPDFに各値を埋め込んでいく。
$pagecount = $pdf->setSourceFile("../../../../common/temp_pdf/inkantodokesho.pdf");

//雛形の1ページ目を取得する。(1ページしかない。)
$tplidx = $pdf->ImportPage(1);
$pdf->addPage();
//雛形をセットする。
$pdf->useTemplate($tplidx);


$pdf->SetFillColor($bgR, $bgG, $bgB);


$pdf->SetFontSize(10);

//商号・名称
$buf = $companyInfo['tbl_company']['cmp_company_name'];
$pdf->SetXY(101, 35);
$pdf->MultiCell(67,5,$buf,$border,"L",$fill);


//本店・主たる事務所
$buf = null;
$buf .= $mstPrefList[$companyInfo['tbl_company']['cmp_pref_id']]['name'];
$buf .= $companyInfo['tbl_company']['cmp_address1'];
if (!_IsNull($companyInfo['tbl_company']['cmp_address2'])) {
	if (!_IsNull($buf)) $buf .= " ";
	$buf .= $companyInfo['tbl_company']['cmp_address2'];
}
$buf = mb_convert_kana($buf, 'N');
$pdf->SetFontSize(8);
$pdf->SetXY(101, 46);
$pdf->MultiCell(67,3,$buf,$border,"L",$fill);


$pdf->SetFontSize(10);


//代表取締役を取得する。
$repBoardInfo = null;
foreach ($companyInfo['tbl_company_board'] as $key => $boardInfo) {
	switch ($boardInfo['cmp_bod_post_id']) {
		case MST_POST_ID_REP_DIRECTOR:
			//代表取締役
			$repBoardInfo = $boardInfo;
			break 2;
	}
}


//印鑑提出者の氏名
$buf = null;
$buf .= $repBoardInfo['cmp_bod_family_name'];
$buf .= " ";
$buf .= $repBoardInfo['cmp_bod_first_name'];
$buf = mb_convert_kana($buf, 'N');
$pdf->SetXY(101, 69);
$pdf->MultiCell(67,5,$buf,$border,"L",$fill);


//印鑑提出者の生年月日
$buf = null;
$buf .= _ConvertAD2Jp($birthYear);
$buf .= "年";
$buf .= $birthMonth;
$buf .= "月";
$buf .= $birthDay;
$buf .= "日";
$buf .= "生";
$buf = mb_convert_kana($buf, 'N');
$pdf->SetFillColor(255, 255, 255);
$pdf->SetXY(101, 81);
$pdf->MultiCell(67,6,$buf,$border,"L",1);


$pdf->SetFillColor($bgR, $bgG, $bgB);



$noticeName = null;
$noticeNameKana = null;
$noticeAddress = null;
$noticeX = 0;

//代理人
switch ($mode) {
	case PDF_MODE_INKAN_DIRECTOR:
		//代表取締役が登記申請に行く場合

		$noticeName .= $repBoardInfo['cmp_bod_family_name'];
		$noticeName .= " ";
		$noticeName .= $repBoardInfo['cmp_bod_first_name'];

		$noticeNameKana .= $repBoardInfo['cmp_bod_family_name_kana'];
		$noticeNameKana .= " ";
		$noticeNameKana .= $repBoardInfo['cmp_bod_first_name_kana'];

		$noticeAddress .= $mstPrefList[$repBoardInfo['cmp_bod_pref_id']]['name'];
		$noticeAddress .= $repBoardInfo['cmp_bod_address1'];
		if (!_IsNull($repBoardInfo['cmp_bod_address2'])) {
			if (!_IsNull($noticeAddress)) $noticeAddress .= " ";
			$noticeAddress .= $repBoardInfo['cmp_bod_address2'];
		}

		$noticeX = 43;

		break;
	case PDF_MODE_INKAN_OTHER:
		//代理人が登記申請に行く場合

		$noticeName .= $inData['agent_family_name'];
		$noticeName .= " ";
		$noticeName .= $inData['agent_first_name'];

		$noticeNameKana .= $inData['agent_family_name_kana'];
		$noticeNameKana .= " ";
		$noticeNameKana .= $inData['agent_first_name_kana'];

		$noticeAddress .= $mstPrefList[$inData['agent_pref_id']]['name'];
		$noticeAddress .= $inData['agent_address1'];
		if (!_IsNull($inData['agent_address2'])) {
			if (!_IsNull($noticeAddress)) $noticeAddress .= " ";
			$noticeAddress .= $inData['agent_address2'];
		}

		$noticeX = 80.7;

		break;
}

//届出人の印鑑提出者本人 or 代理人
$buf = "レ";
$pdf->SetXY($noticeX, 113);
$pdf->MultiCell(3,3,$buf,$border,"L",$fill);


//届出人の住所
$buf = $noticeAddress;
$buf = mb_convert_kana($buf, 'N');
$pdf->SetFontSize(8);
$pdf->SetXY(34, 118);
$pdf->MultiCell(95,3,$buf,$border,"L",$fill);


//届出人のフリガナ
$buf = $noticeNameKana;
//全角カタナカに変換する。
$buf = mb_convert_kana($buf, 'KVCN');
$pdf->SetFontSize(8);
$pdf->SetXY(34, 129);
$pdf->MultiCell(95,3,$buf,$border,"L",$fill);


$pdf->SetFontSize(10);


//届出人の氏名
$buf = $noticeName;
$buf = mb_convert_kana($buf, 'N');
$pdf->SetXY(34, 135);
$pdf->MultiCell(95,5,$buf,$border,"L",$fill);


//代理人
switch ($mode) {
	case PDF_MODE_INKAN_DIRECTOR:
		//代表取締役が登記申請に行く場合

		break;
	case PDF_MODE_INKAN_OTHER:
		//代理人が登記申請に行く場合

		//委任状

		//代理人の住所
		$buf = null;
		$buf .= $mstPrefList[$inData['agent_pref_id']]['name'];
		$buf .= $inData['agent_address1'];
		if (!_IsNull($inData['agent_address2'])) {
			if (!_IsNull($buf)) $buf .= " ";
			$buf .= $inData['agent_address2'];
		}
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(45, 151.6);
		$pdf->MultiCell(115,5,$buf,$border,"L",$fill);


		//代理人の氏名
		$buf = null;
		$buf .= $inData['agent_family_name'];
		$buf .= " ";
		$buf .= $inData['agent_first_name'];
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(45, 157.8);
		$pdf->MultiCell(115,5,$buf,$border,"L",$fill);


		//作成日(年)
		$buf = _ConvertAD2Jp($pdfCreateYear, false);
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(33, 169.5);
		$pdf->MultiCell(10,5,$buf,$border,"R",$fill);


		//作成日(月)
		$buf = $pdfCreateMonth;
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(46, 169.5);
		$pdf->MultiCell(10,5,$buf,$border,"R",$fill);


		//作成日(日)
		$buf = $pdfCreateDay;
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(59.2, 169.5);
		$pdf->MultiCell(10,5,$buf,$border,"R",$fill);


		//代表取締役の住所
		$buf = null;
		$buf .= $mstPrefList[$repBoardInfo['cmp_bod_pref_id']]['name'];
		$buf .= $repBoardInfo['cmp_bod_address1'];
		if (!_IsNull($repBoardInfo['cmp_bod_address2'])) {
			if (!_IsNull($buf)) $buf .= " ";
			$buf .= $repBoardInfo['cmp_bod_address2'];
		}
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(37, 175.7);
		$pdf->MultiCell(98,5,$buf,$border,"L",$fill);


		//代表取締役の氏名
		$buf = null;
		$buf .= $repBoardInfo['cmp_bod_family_name'];
		$buf .= " ";
		$buf .= $repBoardInfo['cmp_bod_first_name'];
		$buf = mb_convert_kana($buf, 'N');
		$pdf->SetXY(37, 181);
		$pdf->MultiCell(88,5,$buf,$border,"L",$fill);

		break;
}









//DBをクローズする。
_DB_Close($link);


//PDFを出力する。
$pdf->Output();

_Log("[/user/company/pdf/create/inkantodokesho.php] end. OK!");



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
