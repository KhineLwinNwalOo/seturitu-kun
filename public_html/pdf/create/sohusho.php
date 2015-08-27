<?php
/*
 * [管理画面]
 * PDF作成
 * 添付書面送付書
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
_Log("[/pdf/create/sohusho.php] start.");

_Log("[/pdf/create/sohusho.php] POST = '".print_r($_POST,true)."'");
_Log("[/pdf/create/sohusho.php] GET = '".print_r($_GET,true)."'");
_Log("[/pdf/create/sohusho.php] SERVER = '".print_r($_SERVER,true)."'");


//認証チェック----------------------------------------------------------------------start
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/pdf/create/sohusho.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/pdf/create/sohusho.php] end.");
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
//$mstStockPriceList = _GetMasterList('mst_stock_price', $undeleteOnly);			//1株の単価マスタ
$mstJusticeTypeList = _GetMasterList('mst_justice_type', $undeleteOnly);		//士業タイプマスタ

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
$title = "添付書面送付書";


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


//法務局
$pdf->SetFontSize(10);
$buf = "東京法務局　御中";
$pdf->Cell(0,10,$buf,$border,0,"L");
$pdf->Ln(15);


//タイトル
$pdf->SetFontSize(18);
$pdf->Cell(0,10,$title,$border,0,"C");
$pdf->Ln(30);


$pdf->SetFontSize(10);


//会社名・法人名
$buf = "商　　号";
$pdf->Cell(30,6,$buf,$border,0,"L");

$companyName = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_company_name'])) $companyName .= $inquiryInfo['tbl_company']['cmp_company_name'];
$buf = null;
if (!_IsNull($companyName)) {
	$buf .= $companyName;
} else {
	$errorList[] = "『会社名・法人名』を登録してください。";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(5);


//本店所在地
$buf = "本　　店";
$pdf->Cell(30,6,$buf,$border,0,"L");

$companyAddress = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_pref_id'])) $companyAddress .= $mstPrefList[$inquiryInfo['tbl_company']['cmp_pref_id']]['name'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_address1'])) $companyAddress .= $inquiryInfo['tbl_company']['cmp_address1'];
if (!_IsNull($inquiryInfo['tbl_company']['cmp_address2'])) $companyAddress .= $inquiryInfo['tbl_company']['cmp_address2'];
$buf = null;
if (!_IsNull($companyAddress)) {
	$buf .= $companyAddress;
	$buf = mb_convert_kana($buf, 'N');
} else {
	$errorList[] = "『本店所在地』を登録してください。";
}
$pdf->MultiCell(0,6,$buf,$border,"L");
$pdf->Ln(5);


//支店
$buf = "支　　店";
$pdf->Cell(30,6,$buf,$border,0,"L");

//$buf = null;
$buf = " ";
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->SetFontSize(8);
$buf = "（注）「支店」は支店所在地登記所に申請する場合に記載願います。";
$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->Ln(20);

$pdf->SetFontSize(10);


//役員
$board = null;
$boardCount = 0;

//会社タイプ_役職マスタの「表示順」の順で表示する。
foreach ($mstCompanyTypePostList as $key => $mstCompanyTypePostInfo) {
	foreach ($inquiryInfo['tbl_company_board']['board_info'] as $key => $boardInfo) {
		if ($mstCompanyTypePostInfo['id'] == $boardInfo['cmp_bod_post_id']) {
			if (!_IsNull($boardInfo['cmp_bod_name'])) {
				$board .= "設立時".$mstCompanyTypePostInfo['name']." ".$boardInfo['cmp_bod_name']."\n";
				$boardCount++;
			
				$buf = "";
				if ($boardCount == 1) {
					$buf = "申請人等";
				}
				$pdf->Cell(30,6,$buf,$border,0,"L");

				$buf = "資　格";
				$pdf->Cell(20,6,$buf,$border,0,"L");

				$buf = $mstCompanyTypePostInfo['name'];
				//$pdf->Cell(0,6,$buf,$border,0,"L");
				$pdf->MultiCell(0,6,$buf,$border,"L");

				$pdf->Ln(2);

				$buf = "";
				$pdf->Cell(30,6,$buf,$border,0,"L");

				$buf = "氏　名";
				$pdf->Cell(20,6,$buf,$border,0,"L");

				$buf = $boardInfo['cmp_bod_name'];
				//$pdf->Cell(0,6,$buf,$border,0,"L");
				$pdf->MultiCell(0,6,$buf,$border,"L");
				
				$pdf->Ln(5);
			}
		}
	}
}
if (!_IsNull($board)) {
} else {
	$errorList[] = "『役員』を登録してください。";
}


//士業
$justiceName = null;
$justiceAddress = null;
if (!_IsNull($inquiryInfo['tbl_inquiry']['inq_justice_id'])) {
	//士業情報を取得する。
	$condition4jst = array();
	$condition4jst['jst_justice_id'] = $inquiryInfo['tbl_inquiry']['inq_justice_id'];//士業ID
	$tblJusticeInfo = _DB_GetInfo('tbl_justice', $condition4jst, true, 'jst_del_flag');
	if (!_IsNull($tblJusticeInfo)) {
		$justiceName .= $mstJusticeTypeList[$tblJusticeInfo['jst_justice_type_id']]['name'];
		$justiceName .= " ";
		$justiceName .= $tblJusticeInfo['jst_name'];

		$justiceAddress .= $mstPrefList[$tblJusticeInfo['jst_pref_id']]['name'];
		$justiceAddress .= $tblJusticeInfo['jst_address1'];
		$justiceAddress .= $tblJusticeInfo['jst_address2'];
	}
}

$buf = "代理人";
$pdf->Cell(30,6,$buf,$border,0,"L");

$buf = "住　所";
$pdf->Cell(20,6,$buf,$border,0,"L");

$buf = $justiceAddress;
$buf = mb_convert_kana($buf, 'N');
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln(2);

$buf = "";
$pdf->Cell(30,6,$buf,$border,0,"L");

$buf = "氏　名";
$pdf->Cell(20,6,$buf,$border,0,"L");

$buf = $justiceName;
//$pdf->Cell(0,6,$buf,$border,0,"L");
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln(5);

if (!_IsNull($justiceName)) {
} else {
	$errorList[] = "『士業者』を登録してください。";
}


//申請番号
$buf = "申請番号";
$pdf->Cell(30,6,$buf,$border,0,"L");

$applicationNo = null;
if (!_IsNull($inquiryInfo['tbl_company']['cmp_application_no'])) $applicationNo .= $inquiryInfo['tbl_company']['cmp_application_no'];
$buf = null;
//(2008/11/14)必須なし
//if (!_IsNull($applicationNo)) {
//	$buf .= $applicationNo;
//} else {
//	$errorList[] = "『申請番号』を登録してください。";
//}
$buf .= $applicationNo;
$pdf->MultiCell(0,6,$buf,$border,"L");

$pdf->Ln(20);


$pdf->Cell(0,6,"添付書面の内容",$border,0,"C");

$pdf->Ln(10);


$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"就任承諾書",$border,0,"L");
$pdf->Cell(10,6,"1枚",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");

$pdf->Ln();

$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"委任状",$border,0,"L");
$pdf->Cell(10,6,"1枚",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");

$pdf->Ln();

$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"印鑑届書",$border,0,"L");
$pdf->Cell(10,6,"1枚",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");

$pdf->Ln();

$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"払込証明書",$border,0,"L");
$pdf->Cell(10,6,"1枚",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");

$pdf->Ln();

$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"印鑑証明書",$border,0,"L");
$pdf->Cell(10,6,"1枚",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");

$pdf->Ln();

$pdf->Cell(65,6,"",$border,0,"L");
$pdf->Cell(30,6,"定款謄本",$border,0,"L");
$pdf->Cell(10,6,"1部",$border,0,"R");
$pdf->Cell(0,6,"",$border,0,"L");




//DBをクローズする。
_DB_Close($link);


if (count($errorList) > 0) {
	//エラー有の場合

	//PDFを終了する。
	$pdf->Close();

	_Log("[/pdf/create/sohusho.php] end. ERR!");


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

	_Log("[/pdf/create/sohusho.php] end. OK!");
}



?>
