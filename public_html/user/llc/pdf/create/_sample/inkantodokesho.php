<?php
/*
 * [管理画面]
 * PDF作成
 * 印鑑（改印）届書
 *
 * 更新履歴：2008/11/05	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");
//include_once("../../common/libs/fpdf/mbfpdf.php");
include_once("../../common/libs/fpdf/mbfpdf_fpdi.php");


_LogDelete();
//_LogBackup();
_Log("[/pdf/create/shodakusho.php] start.");

_Log("[/pdf/create/shodakusho.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/create/shodakusho.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/create/shodakusho.php] SERVER = '".print_r($_SERVER,true)."'");


//認証チェック----------------------------------------------------------------------start
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/create/shodakusho.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/pdf/create/shodakusho.php] end.");
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

//定款作成日
$pdfTeikanCreateYear = (isset($inData['teikan_year'])?$inData['teikan_year']:date('Y'));
$pdfTeikanCreateMonth = (isset($inData['teikan_month'])?$inData['teikan_month']:date('n'));
$pdfTeikanCreateDay = (isset($inData['teikan_day'])?$inData['teikan_day']:date('j'));


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
//$mstStockPriceList = _GetMasterList('mst_stock_price', $undeleteOnly);			//1株の単価マスタ
//$mstJusticeTypeList = _GetMasterList('mst_justice_type', $undeleteOnly);		//士業タイプマスタ

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
$pdf=new MBFPDF('P', 'mm', array(182.0, 257.0));

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
$pagecount = $pdf->setSourceFile("../../common/temp_pdf/inkantodokesho.pdf");

//雛形の1ページ目を取得する。(1ページしかない。)
$tplidx = $pdf->ImportPage(1);
$pdf->addPage();
//雛形をセットする。
$pdf->useTemplate($tplidx);


$pdf->SetFillColor($bgR, $bgG, $bgB);


$pdf->SetFontSize(10);

//会社名・法人名
$companyName = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name'])) $companyName .= $inquiryInfo['tbl_company']['cmp_company_name'];
$buf = null;
if (!_IsNull($companyName)) {
	$buf = $companyName;
} else {
	$errorList[] = "『会社名・法人名』を登録してください。";
}
$pdf->SetXY(101, 35);
$pdf->MultiCell(67,5,$buf,$border,"L",$fill);


//住所
$companyAddress = null;
$buf = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_pref_id']) && !_IsNull($inquiryInfo['tbl_company']['cmp_address1'])) {
	$companyAddress .= $mstPrefList[$inquiryInfo['tbl_company']['cmp_pref_id']]['name'];
	$companyAddress .= $inquiryInfo['tbl_company']['cmp_address1'];
	if (!_IsNull($inquiryInfo['tbl_company']['cmp_address2'])) {
		if (!_IsNull($companyAddress)) $companyAddress .= " ";
		$companyAddress .= $inquiryInfo['tbl_company']['cmp_address2'];
	}
	$companyAddress = mb_convert_kana($companyAddress, 'N');
	$buf = $companyAddress;
} else {
	$errorList[] = "『本店所在地』を登録してください。";
}
$pdf->SetFontSize(8);
$pdf->SetXY(101, 46);
$pdf->MultiCell(67,3,$buf,$border,"L",$fill);


$pdf->SetFontSize(10);


//代表取締役

$repBoardInfo = null;
if (is_array($inquiryInfo['tbl_company_board']['board_info'])) {
	//先頭の代表取締役を取得する。
	foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
		switch ($boardInfo['cmp_bod_post_id']) {
			case MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR:		//代表取締役
				$repBoardInfo = $boardInfo;
				break 2;
			default:
				continue 2;
		}
	}
}
if (!_IsNull($repBoardInfo)) {
	//役員名前
	$name = null;
	if (!_IsNull($repBoardInfo['cmp_bod_name'])) $name .= $repBoardInfo['cmp_bod_name'];
	$buf = null;
	if (!_IsNull($name)) {
		$buf = $name;
	} else {
		$errorList[] = "『役員名前』を登録してください。";
	}
	//印鑑提出者の氏名
	$pdf->SetXY(101, 69);
	$pdf->MultiCell(67,5,$buf,$border,"L",$fill);

	//届出人の氏名
	$pdf->SetXY(34, 135);
	$pdf->MultiCell(95,5,$buf,$border,"L",$fill);

	//生年月日
	$birth = null;
	$buf = null;
	if (!_IsNull($repBoardInfo['cmp_bod_birth_year']) && !_IsNull($repBoardInfo['cmp_bod_birth_month']) && !_IsNull($repBoardInfo['cmp_bod_birth_day'])) {
		//国籍IDをチェックする。
		if (_IsNull($repBoardInfo['cmp_bod_nationality_id']) || $repBoardInfo['cmp_bod_nationality_id'] == MST_NATIONALITY_ID_JAPAN) {
			//未設定、又は、"日本国籍"の場合、和暦にする。
			$birth .= _ConvertAD2Jp($repBoardInfo['cmp_bod_birth_year']);
			$birth .= "年";
			$birth .= $repBoardInfo['cmp_bod_birth_month'];
			$birth .= "月";
			$birth .= $repBoardInfo['cmp_bod_birth_day'];
			$birth .= "日";
			$birth .= "生";
		} else {
			//"日本国籍"以外の場合、西暦にする。
			$birth .= "西暦";
			$birth .= $repBoardInfo['cmp_bod_birth_year'];
			$birth .= "年";
			$birth .= $repBoardInfo['cmp_bod_birth_month'];
			$birth .= "月";
			$birth .= $repBoardInfo['cmp_bod_birth_day'];
			$birth .= "日";
			$birth .= "生";
		}
		$birth = mb_convert_kana($birth, 'N');
		$buf = $birth;
	} else {
		$errorList[] = "『役員生年月日』を登録してください。";
	}
	$pdf->SetFillColor(255, 255, 255);
	$pdf->SetXY(101, 81);
	$pdf->MultiCell(67,6,$buf,$border,"L",1);


	$pdf->SetFillColor($bgR, $bgG, $bgB);


	//届出人の印鑑提出者本人
	$buf = "レ";
	$pdf->SetXY(43, 113);
	$pdf->MultiCell(3,3,$buf,$border,"L",$fill);


	//役員住所
	$address = null;
	$buf = null;
	if (!_IsNull($repBoardInfo['cmp_bod_pref_id']) && !_IsNull($repBoardInfo['cmp_bod_address1'])) {
		$address .= $mstPrefList[$repBoardInfo['cmp_bod_pref_id']]['name'];
		$address .= $repBoardInfo['cmp_bod_address1'];
		if (!_IsNull($repBoardInfo['cmp_bod_address2'])) {
			if (!_IsNull($address)) $address .= " ";
			$address .= $repBoardInfo['cmp_bod_address2'];
		}
		$address = mb_convert_kana($address, 'N');
		$buf = $address;
	} else {
		$errorList[] = "『役員住所』を登録してください。";
	}
	//届出人の住所
	$pdf->SetFontSize(8);
	$pdf->SetXY(34, 118);
	$pdf->MultiCell(95,3,$buf,$border,"L",$fill);


	$pdf->SetFontSize(10);


	//役員名前(ふりがな)
	$nameKana = null;
	if (!_IsNull($repBoardInfo['cmp_bod_name_kana'])) $nameKana .= $repBoardInfo['cmp_bod_name_kana'];
	$buf = null;
	if (!_IsNull($nameKana)) {
		//全角カタナカに変換する。
		$nameKana = mb_convert_kana($nameKana, 'KVC');
		$buf = $nameKana;
	} else {
		$errorList[] = "『役員名前(ふりがな)』を登録してください。";
	}
	//届出人のフリガナ
	$pdf->SetFontSize(8);
	$pdf->SetXY(34, 129);
	$pdf->MultiCell(95,3,$buf,$border,"L",$fill);


	$pdf->SetFontSize(10);


	
} else {
	$errorList[] = "『役員(代表取締役)』を登録してください。";
}





//DBをクローズする。
_DB_Close($link);


if (count($errorList) > 0) {
	//エラー有の場合

	//PDFを終了する。
	$pdf->Close();

	_Log("[/pdf/create/shodakusho.php] end. ERR!");


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

	_Log("[/pdf/create/shodakusho.php] end. OK!");
}



?>
