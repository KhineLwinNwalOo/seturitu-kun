<?php
/*
 * [管理画面]
 * PDF作成
 * 就任承諾書
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
$title = "就任承諾書";


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




//作成日
$createDate = null;
$createDate .= _ConvertAD2Jp($pdfCreateYear);
$createDate .= "年";
$createDate .= $pdfCreateMonth;
$createDate .= "月";
$createDate .= $pdfCreateDay;
$createDate .= "日";
$createDate = mb_convert_kana($createDate, 'N');

//定款作成日
$teikanCreateDate = null;
$teikanCreateDate .= _ConvertAD2Jp($pdfTeikanCreateYear);
$teikanCreateDate .= "年";
$teikanCreateDate .= $pdfTeikanCreateMonth;
$teikanCreateDate .= "月";
$teikanCreateDate .= $pdfTeikanCreateDay;
$teikanCreateDate .= "日";
$teikanCreateDate = mb_convert_kana($teikanCreateDate, 'N');


//会社名・法人名
$companyName = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name'])) $companyName .= $inquiryInfo['tbl_company']['cmp_company_name'];
if (!_IsNull($companyName)) {
} else {
	$errorList[] = "『会社名・法人名』を登録してください。";
}


//役員
$boardFlag = false;

$boardPostFlag = true;		//役職
$boardNameFlag = true;		//氏名
$boardAddressFlag = true;	//住所


if (is_array($inquiryInfo['tbl_company_board']['board_info'])) {
	if (count($inquiryInfo['tbl_company_board']['board_info']) > 0) {
		$boardFlag = true;
	}
}

//会社タイプ_役職マスタの「表示順」の順で表示する。
foreach ($mstCompanyTypePostList as $key => $mstCompanyTypePostInfo) {

	$topMessage = null;
	$topMessage .= "私は、";
	$topMessage .= $teikanCreateDate;
	$topMessage .= "の貴社定款において、";
	switch ($mstCompanyTypePostInfo['id']) {
		case MST_COMPANY_TYPE_POST_ID_CMP_REP_DIRECTOR:		//代表取締役
			$topMessage .= "取締役及び代表取締役";
			break;
		case MST_COMPANY_TYPE_POST_ID_CMP_INSPECTOR:		//監査役
			$topMessage .= "監査役";
			break;
		case MST_COMPANY_TYPE_POST_ID_CMP_DIRECTOR:			//取締役
		default:
			$topMessage .= "取締役";
			break;
	}
	$topMessage .= "に選任されましたので、その就任を承諾いたします。";


	foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
		if ($mstCompanyTypePostInfo['id'] != $boardInfo['cmp_bod_post_id']) continue;
		
		if (_IsNull($boardInfo['cmp_bod_post_id'])) $boardPostFlag = false;
		if (_IsNull($boardInfo['cmp_bod_name'])) $boardNameFlag = false;
		if (_IsNull($boardInfo['cmp_bod_pref_id'])) $boardAddressFlag = false;
		if (_IsNull($boardInfo['cmp_bod_address1'])) $boardAddressFlag = false;
		
		
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
		$pdf->MultiCell(0,6,$createDate,$border,"L");
		$pdf->Ln(10);


		//住所
		$buf = "（　住　所　）";
		$pdf->Cell(30,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $mstPrefList[$boardInfo['cmp_bod_pref_id']]['name'];
		$buf .= $boardInfo['cmp_bod_address1'];
		if (!_IsNull($boardInfo['cmp_bod_address2'])) {
			if (!_IsNull($buf)) $buf .= " ";
			$buf .= $boardInfo['cmp_bod_address2'];
		}
		$buf = mb_convert_kana($buf, 'N');
		$pdf->MultiCell(0,6,$buf,$border,"L");

		$pdf->Ln(5);

		//氏名
		$buf = "（　氏　名　）";
		$pdf->Cell(30,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $boardInfo['cmp_bod_name'];
		$pdf->MultiCell(0,6,$buf,$border,"L");

		$pdf->Ln(5);


		//商号
		$buf = "（　商　号　）";
		$pdf->Cell(30,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= $companyName;
		$pdf->Cell(100,6,$buf,$border,0,"L");

		$buf = null;
		$buf .= "御中";
		$pdf->Cell(20,6,$buf,$border,0,"L");
	
		$buf = null;
		$buf .= "印";
		$pdf->Cell(0,6,$buf,$border,0,"L");
	}
}

if (!$boardFlag) {
	$errorList[] = "『役員』を登録してください。";
}
if (!$boardPostFlag) {
	$errorList[] = "『役員-会社役職』を登録してください。";
}
if (!$boardNameFlag) {
	$errorList[] = "『役員-役員名前』を登録してください。";
}
if (!$boardAddressFlag) {
	$errorList[] = "『役員-住所』を登録してください。";
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
