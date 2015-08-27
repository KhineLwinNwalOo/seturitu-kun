<?php
/*
 * [管理画面]
 * 情報更新画面
 *
 * 更新履歴：2008/05/30	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
//session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/info/index.php] start.");

_Log("[/info/index.php] POST = '".print_r($_POST,true)."'");
_Log("[/info/index.php] GET = '".print_r($_GET,true)."'");
_Log("[/info/index.php] SERVER = '".print_r($_SERVER,true)."'");


//認証チェック----------------------------------------------------------------------start
//ログインしているか？
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/info/index.php] ログインしていないなのでログイン画面を表示する。");
	_Log("[/info/index.php] end.");
	//ログイン画面を表示する。
	header("Location: ".URL_BASE);
	exit;
}
//ログイン情報を取得する。
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
//認証チェック----------------------------------------------------------------------end



//本ファイルの名称を取得する。
$phpName = basename($_SERVER['PHP_SELF']);
//フォームのアクションを設定する。
//$formAction = SSL_URL_THE_LIFEBOAT_COM_INQ.'/'.$phpName;
$formAction = $_SERVER['PHP_SELF'];

//通常のURL(SSLではないURL)
$urlBase = URL_BASE;

//サイト名
$clientName = ADMIN_TITLE;

//タブインデックス
$tabindex = 0;

//DBをオープンする。
$cid = _DB_Open();

//マスタ情報を取得する。
$undeleteOnly = true;

//動作モード{1:入力/2:確認/3:完了/4:エラー}
$mode = 1;

//全て表示するか？hidden項目も表示するか？{true:全て表示する。/false:XML設定、権限による表示有無に従う。}
$allShowFlag = false;

//メッセージ
$message = "";
//エラーフラグ
$errorFlag = false;

//メッセージ
$message4js = "";


//ターゲット情報を格納する配列
$info = array();

//パラメーターを取得する。
$xmlName = null;
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
		//XMLファイル名
		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
		//ターゲットID
		$id = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);

		//入力値を取得する。
		$info = $_POST;
		_Log("[/info/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/info/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");
	
		break;
	case 'GET':
		//XMLファイル名
		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
		//ターゲットID
		$id = (isset($_GET['id'])?$_GET['id']:null);

		//遷移元ページ
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);


		//初期値を設定する。
		$undeleteOnly4def = false;
		switch ($xmlName) {
			case XML_NAME_ITEM:
				//商品情報
				break;
			case XML_NAME_BOTTLE_IMAGE:
				//ボトル画像情報
				$undeleteOnly4def = true;//未削除データのみ
				break;
			case XML_NAME_DESIGN_IMAGE:
				//彫刻パターン画像情報
				$undeleteOnly4def = true;//未削除データのみ
				break;
			case XML_NAME_CHARACTER_J_IMAGE:
				//彫刻文字(和字)画像情報
				$undeleteOnly4def = true;//未削除データのみ
				break;
			case XML_NAME_CHARACTER_E_IMAGE:
				//彫刻文字(英字)画像情報
				$undeleteOnly4def = true;//未削除データのみ
				break;
			case XML_NAME_INQ_FROM_MAIL:
				//問合せ情報(メール本文から登録用)
				break;
			case XML_NAME_INQ:
				//問合せ情報
			default:
				//XMLファイル名を設定する。
				$xmlName = XML_NAME_INQ;
				break;
		}

		
		//権限処理追加
		switch ($loginInfo['mng_auth_id']) {
			case AUTH_NON:
				//権限無し
				
				//扱える情報を制限する。
				switch ($xmlName) {
					case XML_NAME_INQ:
						//問合せ情報
					default:
						//XMLファイル名を設定する。
						$xmlName = XML_NAME_INQ;

						//ターゲットID
						$id = null;
						unset($_GET['id']);//→動作モード="単独表示"にするためにクリアする。
				
						//遷移元ページ
						$pId = null;

						
						$undeleteOnly4def = true;//未削除データのみ
						
						//ユーザーIDから問合せ情報を検索する。→問合せIDを取得する。
						$inquiryId = null;
						if (isset($loginInfo['tbl_user'])) {
							$condition4inq = array();
							$condition4inq['inq_user_id'] = $loginInfo['tbl_user']['usr_user_id'];	//顧客ID
							$tblInquiryList = _DB_GetList('tbl_inquiry', $condition4inq, true, null, 'inq_del_flag');
							if (!_IsNull($tblInquiryList)) {
								//配列の先頭から要素を一つ取り出す
								$tblInquiryInfo = array_shift($tblInquiryList);
								$inquiryId = $tblInquiryInfo['inq_inquiry_id'];
							}
						}
						if (_IsNull($inquiryId)) {
							$message = "※該当の問合せ情報が存在しません。\n";
							$errorFlag = true;
							$mode = 4;
						} else {
							//ターゲットID
							$id = $inquiryId;
						}

						break;
				}
				break;
		}


		$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
		
		//XMLファイル名、ターゲットIDを初期値に追加する。
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;


		//設定されている場合=更新の場合
		if (isset($_GET['id'])) {
			//動作モードをセッションに保存する。動作モード="他画面経由の表示"
			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_OTHER;
		} else {
			//動作モードをセッションに保存する。動作モード="単独表示"
			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_MENU;
		}

		//遷移元ページをセッションに保存する。
		$_SESSION[SID_INFO_FROM_PAGE_ID] = $pId;

		break;	
}

_Log("[/info/index.php] (param) \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/info/index.php] (param) XMLファイル名 = '".$xmlName."'");
_Log("[/info/index.php] (param) ターゲットID = '".$id."'");


//XMLを読み込む。
$xmlFile = "../common/form_xml/".$xmlName.".xml";
_Log("[/info/index.php] XMLファイル = '".$xmlFile."'");
$xmlList = _GetXml($xmlFile);


$mstAftereffectsGrade01List = null;
switch ($xmlName) {
	case XML_NAME_ITEM:
		//商品情報
		break;
	case XML_NAME_BOTTLE_IMAGE:
		//ボトル画像情報
		break;
	case XML_NAME_DESIGN_IMAGE:
		//彫刻パターン画像情報
		break;
	case XML_NAME_CHARACTER_J_IMAGE:
		//彫刻文字(和字)画像情報
		break;
	case XML_NAME_CHARACTER_E_IMAGE:
		//彫刻文字(英字)画像情報
		break;
	case XML_NAME_INQ:
		//問合せ情報
	case XML_NAME_INQ_FROM_MAIL:
		//問合せ情報(メール本文から登録用)
//		$mstAftereffectsGrade01List = _GetMasterList('mst_aftereffects_grade_01', false);		//後遺障害等級(級)マスタ
		break;
}

//確認ボタンが押された場合
if ($_POST['confirm'] != "") {

	//入力値チェック
	$message .= _CheackInputAll($xmlList, $info);
	
	
	switch ($xmlName) {
		case XML_NAME_ITEM:
			//商品情報
			//画像アップロード
//			$message .= _UploadItemImage($info, $_FILES);
			$message .= _UploadImage($info, $_FILES, 'tbl_item_image', 'itm_img_file_name', FILE_DIR_ITEM_IMG_TMP, 2);
			break;
		case XML_NAME_BOTTLE_IMAGE:
			//ボトル画像情報
			//画像アップロード
			$message .= _UploadImage($info, $_FILES, 'tbl_bottle_image', 'btl_img_file_name', FILE_DIR_BOTTLE_IMG_TMP, 3);
			break;
		case XML_NAME_DESIGN_IMAGE:
			//彫刻パターン画像情報
			//画像アップロード
			$message .= _UploadImage($info, $_FILES, 'mst_design', 'file_name', FILE_DIR_DESIGN_IMG_TMP, 4);
			break;
		case XML_NAME_CHARACTER_J_IMAGE:
			//彫刻文字(和字)画像情報
			//画像アップロード
			$message .= _UploadImage($info, $_FILES, 'mst_character_j', 'file_name', FILE_DIR_CHARACTER_J_IMG_TMP, 5);
			break;
		case XML_NAME_CHARACTER_E_IMAGE:
			//彫刻文字(英字)画像情報
			//画像アップロード
			$message .= _UploadImage($info, $_FILES, 'mst_character_e', 'file_name', FILE_DIR_CHARACTER_E_IMG_TMP, 6);
			break;
	}

	
	if (_IsNull($message)) {
		//エラーが無い場合、確認画面を表示する。
		$mode = 2;
		 
		$message .= "※入力内容を確認して、「更新」ボタンを押してください。";
	} else {
		//エラーが有り場合
		$message = "※入力に誤りがあります。\n".$message;
		$errorFlag = true;
	}
}
//戻るボタンが押された場合
elseif ($_POST['back'] != "") {
}
//送信ボタンが押された場合
elseif ($_POST['go'] != "") {

//	//メール本文の共通部分を設定する。
//	$body = "";
	$body .= _CreateMailAll($xmlList, $info);

	_Log("[/info/index.php] _CreateMailAll = '".$body."'");


	switch ($xmlName) {
		case XML_NAME_ITEM:
			//商品情報

			//更新前の商品_画像テーブル情報を取得する。
			$itemId = $info['condition']['_id_'];
			$oldImageList = null;
			if (!_IsNull($itemId)) {
				$condition = array();
				$condition['itm_img_item_id'] = $itemId;
				$order = null;
				$order .= "lpad(itm_img_show_order,10,'0')";		//表示順の昇順
				$order .= ",itm_img_no";							//Noの昇順
				$oldImageList = _DB_GetList('tbl_item_image', $condition, true, $order);
			}

			break;

		case XML_NAME_BOTTLE_IMAGE:
			//ボトル画像情報

			//更新前のボトル_画像テーブル情報を取得する。(削除済みの情報も全件取得する。→アップロード画像の移動、削除に削除済み情報も使用する。)
			$condition = null;
			$order = null;
			$order .= "btl_img_image_id";		//ボトル画像IDの昇順
			$oldImageList = _DB_GetList('tbl_bottle_image', $condition, false, $order, 'btl_img_del_flag');

			break;

		case XML_NAME_DESIGN_IMAGE:
			//彫刻パターン画像情報

			//更新前のデザインマスタ情報を取得する。(削除済みの情報も全件取得する。→アップロード画像の移動、削除に削除済み情報も使用する。)
			$condition = null;
			$order = null;
			$order .= "id";		//IDの昇順
			$oldImageList = _DB_GetList('mst_design', $condition, false, $order, 'del_flag');

			break;
		case XML_NAME_CHARACTER_J_IMAGE:
			//彫刻文字(和字)画像情報

			//更新前の文字_和マスタ情報を取得する。(削除済みの情報も全件取得する。→アップロード画像の移動、削除に削除済み情報も使用する。)
			$condition = null;
			$order = null;
			$order .= "id";		//IDの昇順
			$oldImageList = _DB_GetList('mst_character_j', $condition, false, $order, 'del_flag');

			break;
		case XML_NAME_CHARACTER_E_IMAGE:
			//彫刻文字(英字)画像情報

			//更新前の文字_英マスタ情報を取得する。(削除済みの情報も全件取得する。→アップロード画像の移動、削除に削除済み情報も使用する。)
			$condition = null;
			$order = null;
			$order .= "id";		//IDの昇順
			$oldImageList = _DB_GetList('mst_character_e', $condition, false, $order, 'del_flag');

			break;

	}


	//更新・登録をする。(※$infoは最新情報に更新される。)
	$res = _UpdateInfo($info);
	if ($res === false) {
		//エラーが有り場合
		$message = "更新に失敗しました。";
		$errorFlag = true;	
	} else {
		$message .= "更新しました。\n";
		

		switch ($xmlName) {
			case XML_NAME_ITEM:
				//商品情報
	
				//画像ファイルを一時保存フォルダから本番用フォルダに移動する。
				$oldDir = FILE_DIR_ITEM_IMG.'/'.sprintf(FILE_DIR_NAME_FORMAT, $info['condition']['_id_']);
				$newDir = FILE_DIR_ITEM_IMG_TMP.'/'.((isset($info['condition']['_file_upload_temp_dir_']) && !_IsNull($info['condition']['_file_upload_temp_dir_']))?$info['condition']['_file_upload_temp_dir_']:'dummy');
				$newImageList = $info['update']['tbl_item_image'];
//				$res = _UpdateItemImage($oldImageList, $oldDir, $newImageList, $newDir);
				$res = _UpdateImage('itm_img_no', 'itm_img_file_name', $oldImageList, $oldDir, $newImageList, $newDir);
				
				//一時保存フォルダ名を削除する。
				unset($info['condition']['_file_upload_temp_dir_']);
				break;

			case XML_NAME_BOTTLE_IMAGE:
				//ボトル画像情報

				//更新後のボトル_画像テーブル情報を取得する。(削除済みの情報も全件取得する。→アップロード画像の移動、削除に削除済み情報も使用する。)
				$condition = null;
				$order = null;
				$order .= "btl_img_image_id";		//ボトル画像IDの昇順
				$newImageList = _DB_GetList('tbl_bottle_image', $condition, false, $order, 'btl_img_del_flag');

				//画像ファイルを一時保存フォルダから本番用フォルダに移動する。
				$oldDir = FILE_DIR_BOTTLE_IMG;
				$newDir = FILE_DIR_BOTTLE_IMG_TMP.'/'.((isset($info['condition']['_file_upload_temp_dir_']) && !_IsNull($info['condition']['_file_upload_temp_dir_']))?$info['condition']['_file_upload_temp_dir_']:'dummy');
				$res = _UpdateImage('btl_img_image_id', 'btl_img_file_name', $oldImageList, $oldDir, $newImageList, $newDir);
				
				//一時保存フォルダ名を削除する。
				unset($info['condition']['_file_upload_temp_dir_']);
				break;

			case XML_NAME_DESIGN_IMAGE:
				//彫刻パターン画像情報

				//更新後のデザインマスタ情報を取得する。(削除済みの情報も全件取得する。→アップロード画像の移動、削除に削除済み情報も使用する。)
				$condition = null;
				$order = null;
				$order .= "id";		//IDの昇順
				$newImageList = _DB_GetList('mst_design', $condition, false, $order, 'del_flag');

				//画像ファイルを一時保存フォルダから本番用フォルダに移動する。
				$oldDir = FILE_DIR_DESIGN_IMG;
				$newDir = FILE_DIR_DESIGN_IMG_TMP.'/'.((isset($info['condition']['_file_upload_temp_dir_']) && !_IsNull($info['condition']['_file_upload_temp_dir_']))?$info['condition']['_file_upload_temp_dir_']:'dummy');
				$res = _UpdateImage('id', 'file_name', $oldImageList, $oldDir, $newImageList, $newDir);
				
				//一時保存フォルダ名を削除する。
				unset($info['condition']['_file_upload_temp_dir_']);
				break;

			case XML_NAME_CHARACTER_J_IMAGE:
				//彫刻文字(和字)画像情報
	
				//更新後の文字_和マスタ情報を取得する。(削除済みの情報も全件取得する。→アップロード画像の移動、削除に削除済み情報も使用する。)
				$condition = null;
				$order = null;
				$order .= "id";		//IDの昇順
				$newImageList = _DB_GetList('mst_character_j', $condition, false, $order, 'del_flag');

				//画像ファイルを一時保存フォルダから本番用フォルダに移動する。
				$oldDir = FILE_DIR_CHARACTER_J_IMG;
				$newDir = FILE_DIR_CHARACTER_J_IMG_TMP.'/'.((isset($info['condition']['_file_upload_temp_dir_']) && !_IsNull($info['condition']['_file_upload_temp_dir_']))?$info['condition']['_file_upload_temp_dir_']:'dummy');
				$res = _UpdateImage('id', 'file_name', $oldImageList, $oldDir, $newImageList, $newDir);
				
				//一時保存フォルダ名を削除する。
				unset($info['condition']['_file_upload_temp_dir_']);
				break;

			case XML_NAME_CHARACTER_E_IMAGE:
				//彫刻文字(英字)画像情報

				//更新後の文字_英マスタ情報を取得する。(削除済みの情報も全件取得する。→アップロード画像の移動、削除に削除済み情報も使用する。)
				$condition = null;
				$order = null;
				$order .= "id";		//IDの昇順
				$newImageList = _DB_GetList('mst_character_e', $condition, false, $order, 'del_flag');

				//画像ファイルを一時保存フォルダから本番用フォルダに移動する。
				$oldDir = FILE_DIR_CHARACTER_E_IMG;
				$newDir = FILE_DIR_CHARACTER_E_IMG_TMP.'/'.((isset($info['condition']['_file_upload_temp_dir_']) && !_IsNull($info['condition']['_file_upload_temp_dir_']))?$info['condition']['_file_upload_temp_dir_']:'dummy');
				$res = _UpdateImage('id', 'file_name', $oldImageList, $oldDir, $newImageList, $newDir);
				
				//一時保存フォルダ名を削除する。
				unset($info['condition']['_file_upload_temp_dir_']);
				break;

		}


		
		if ($xmlName == XML_NAME_INQ_FROM_MAIL) {
			$message .= "<strong style=\"color:#f00;\">※新たに別のメールの内容を登録する場合は、ここをクリックしてください。⇒";
			$message .= "<a style=\"color:#f00;\" href=\"./?xml_name=".XML_NAME_INQ_FROM_MAIL."\" title=\"新規登録\">[新規登録]</a>";
			$message .= "</strong>\n";
		}
		
		
		//動作モード="他画面経由の表示"の場合、戻るリンクを表示する。
		if ($_SESSION[SID_INFO_MODE] == MST_MODE_FROM_OTHER) {

			switch ($xmlName) {
				case XML_NAME_ITEM:
					//商品情報
					$message .= "<a href=\"../item/?back\" title=\"商品一覧に戻る\">[商品一覧に戻る]</a>\n";
					break;
				case XML_NAME_BOTTLE_IMAGE:
					//ボトル画像情報
					$message .= "";
					break;
				case XML_NAME_DESIGN_IMAGE:
					//彫刻パターン画像情報
					$message .= "";
					break;
				case XML_NAME_CHARACTER_J_IMAGE:
					//彫刻文字(和字)画像情報
					$message .= "";
					break;
				case XML_NAME_CHARACTER_E_IMAGE:
					//彫刻文字(英字)画像情報
					$message .= "";
					break;
				case XML_NAME_INQ:
					//問合せ情報
					switch ($_SESSION[SID_INFO_FROM_PAGE_ID]) {
						case PAGE_ID_INQ_PRICE:
							$message .= "<a href=\"../inquiry_price/?back\" title=\"請求額一覧に戻る\">[請求額一覧に戻る]</a>\n";
							break;
						default:
							$message .= "<a href=\"../inquiry/?back\" title=\"問合せ一覧に戻る\">[問合せ一覧に戻る]</a>\n";
							break;
					}
					break;
			}

		}
//		//完了画面を表示する。
//		$mode = 3;
	}
}



//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/info/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/info/index.php] mode = '".$mode."'");






////文字をHTMLエンティティに変換する。
//$info = _HtmlSpecialCharsForArray($info);

//echo ("\$info='".print_r($info,true)."'");

//パンくずリスト情報を設定する。
$level = 2;
//動作モード="他画面経由の表示"の場合、レベルを3にする。
if ($_SESSION[SID_INFO_MODE] == MST_MODE_FROM_OTHER) $level = 3;

$breadcrumbsTitle = null;
switch ($xmlName) {
	case XML_NAME_ITEM:
		//商品情報
		$breadcrumbsTitle = '商品情報';
		break;
	case XML_NAME_BOTTLE_IMAGE:
		//ボトル画像情報
		$breadcrumbsTitle = 'ボトル情報';
		break;
	case XML_NAME_DESIGN_IMAGE:
		//彫刻パターン画像情報
		$breadcrumbsTitle = '彫刻パターン情報';
		break;
	case XML_NAME_CHARACTER_J_IMAGE:
		//彫刻文字(和字)画像情報
		$breadcrumbsTitle = '彫刻和字情報';
		break;
	case XML_NAME_CHARACTER_E_IMAGE:
		//彫刻文字(英字)画像情報
		$breadcrumbsTitle = '彫刻英字情報';
		break;
	case XML_NAME_INQ:
		//問合せ情報
	case XML_NAME_INQ_FROM_MAIL:
		//問合せ情報(メール本文から登録用)
		$breadcrumbsTitle = '問合せ情報';
		break;
}
_SetBreadcrumbs($_SERVER['PHP_SELF'], '', $breadcrumbsTitle, $level);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<link rel="stylesheet" href="../css/import.css" type="text/css" />
<script language="javascript" src="../common/js/util.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/libs/mootools.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/tool_tip.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/resizable.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/aftereffects_grade.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/create_inq_info_from_mail.js" type="text/javascript"></script>
<script language="javascript" src="../common/js/search_mastar/search_mastar.js" type="text/javascript" charset="utf-8"></script>
<?
switch ($xmlName) {
	case XML_NAME_ITEM:
		//商品情報
		break;
	case XML_NAME_BOTTLE_IMAGE:
		//ボトル画像情報
		break;
	case XML_NAME_DESIGN_IMAGE:
		//彫刻パターン画像情報
		break;
	case XML_NAME_CHARACTER_J_IMAGE:
		//彫刻文字(和字)画像情報
		break;
	case XML_NAME_CHARACTER_E_IMAGE:
		//彫刻文字(英字)画像情報
		break;
	case XML_NAME_INQ:
		//問合せ情報
	case XML_NAME_INQ_FROM_MAIL:
		//問合せ情報(メール本文から登録用)
		if ($mode == 1) {
?>
<script type="text/javascript">
<!--
window.addEvent('domready', function(){
<?
		//権限処理追加
		switch ($loginInfo['mng_auth_id']) {
			case AUTH_NON:
				//権限無し
				break;
			default:
?>
	//以下の、※1、※2の実行順序は、重要。※2が先だと、『状況』をセットする前に、『設立コース』の選択状態が消えてしまう。
	//『設立コース』から『状況』をセットする。(※1)
	setSelect('inquiry_course', 'inquiry_status', 'mst_inquiry_type_course_status', 'inquiry_type_course_id');
	//『問合せタイプ』から『設立コース』をセットする。(※2)
	setSelect('inquiry_type', 'inquiry_course', 'mst_inquiry_type_course', 'inquiry_type_id');
<?
				break;
		}
?>
	//『会社タイプ』から『役職』をセットする。
	setSelects('company_type', 'company_type_post', 'mst_company_type_post', 'company_type_id');
	//『会社タイプ』から『業務』をセットする。
	setSelects('company_type', 'company_type_duties', 'mst_company_type_duties', 'company_type_id');
});
//-->
</script>
<?
		}
		break;
}
?>
<title><?=$clientName?></title>
</head>

<body id="home" onload="openBox('explain_sub', 'explain', 'explain_close');">
<div id="wrapper">
	<div id="header">
		<?include_once("../common_html/header.php");?>
	</div><!-- End header -->

	<div id="sidebar">
		<?include_once("../common_html/side_menu.php");?>
	</div><!-- End sidebar -->

	<div class="breadcrumbs">
		<?=$breadcrumbs = _GetBreadcrumbs();?>
	</div><!-- End breadcrumbs -->

	<div id="maincontent">
		<?=_GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);?>
	</div><!-- End maincontent -->

	<div class="breadcrumbs">
		<?=$breadcrumbs?>
	</div><!-- End breadcrumbs -->
	
	<div id="footer">
		<?include_once("../common_html/footer.php");?>
	</div><!-- End footer -->

</div><!-- End wrapper -->
</body>
</html>

<?
////DBをクローズする。
//_DB_Close($cid);

_Log("[/info/index.php] end.");

?>