<?php
/*
 * [新★会社設立.JP ツール]
 * ユーザーページ
 *
 * 以下のページもまとめた。
 * ユーザー登録ページ
 * 登録解除ページ
 *
 * 更新履歴：2008/12/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/user/index.php] start.");


_Log("[/user/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/user/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/user/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/user/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


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



//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("[/user/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/user/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/user/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

$tempMaincontentUserMenuFile = '../common/temp_html/temp_maincontent_user_menu.txt';
_Log("[/user/index.php] {HTMLテンプレートを読み込み} (メインコンテンツユーザーページ) HTMLテンプレートファイル = '".$tempMaincontentUserMenuFile."'");

$htmlMaincontentUserMenu = @file_get_contents($tempMaincontentUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlMaincontentUserMenu !== false && !_IsNull($htmlMaincontentUserMenu)) {
	_Log("[/user/index.php] {HTMLテンプレートを読み込み} (メインコンテンツユーザーページ) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/user/index.php] {HTMLテンプレートを読み込み} (メインコンテンツユーザーページ) 【失敗】");
}


_Log("[/user/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_USER;

//クライアント様メールアドレス
$clientMail = COMPANY_E_MAIL;
//マスター用メールアドレス
$masterMailList = $_COMPANY_MASTER_MAIL_LIST;

//テスト用
if (false) {
//if (true) {
	//クライアント様メールアドレス
	$clientMail = "ishikawa@woorom.com";
	//マスター用メールアドレス
	//「,」でくぎって送信先を追加して下さい。
	$masterMailList = array("ishikawa@woorom.com", "ishikawa@woorom.com");
}







//タブインデックス
$tabindex = 0;

//DBをオープンする。
$cid = _DB_Open();

//動作モード{1:入力/2:確認/3:完了/4:エラー}
$mode = 1;
$mode4Cancel = 1;

//全て表示するか？hidden項目も表示するか？{true:全て表示する。/false:XML設定、権限による表示有無に従う。}
$allShowFlag = false;

//メッセージ
$message = "";
$message4Cancel = "";
//エラーフラグ
$errorFlag = false;
$errorFlag4Cancel = false;

//入力情報を格納する配列
$info = array();
$info4Cancel = array();

//フォームモード
$formMode = XML_NAME_USER;

//パラメーターを取得する。
$xmlName = XML_NAME_USER;				//XMLファイル名を設定する。
$xmlName4Cancel = XML_NAME_CANCEL;		//XMLファイル名を設定する。
$id = null;
switch ($_SERVER["REQUEST_METHOD"]) {
	case 'POST':
//		//XMLファイル名
//		$xmlName = (isset($_POST['condition']['_xml_name_'])?$_POST['condition']['_xml_name_']:null);
		//ターゲットID
		$id = (isset($_POST['condition']['_id_'])?$_POST['condition']['_id_']:null);

		//初期値を設定する。
		$undeleteOnly4def = false;

		_Log("[/user/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");

		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/user/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/user/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
				_Log("[/user/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

				$id = null;
				$undeleteOnly4def = true;

				//自分のユーザー情報のみ表示する。
				//ユーザーIDを検索する。
				$id = $loginInfo['usr_user_id'];

				_Log("[/user/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");
				break;
		}

		//入力値を取得する。
		$info = $_POST;
		_Log("[/user/index.php] POST = '".print_r($info,true)."'");
		//バックスラッシュを取り除く。
		$info = _StripslashesForArray($info);
		_Log("[/user/index.php] POST(バックスラッシュを取り除く。) = '".print_r($info,true)."'");

		//「半角カタカナ」を「全角カタカナ」に変換する。→メールで半角カナが文字化けするので。
		$info =_Mb_Convert_KanaForArray($info);
		_Log("[/user/pay/index.php] POST(「半角カタカナ」を「全角カタカナ」に変換する。) = '".print_r($info,true)."'");


		$formMode = $info['condition']['_xml_name_'];



		switch ($formMode) {
			case XML_NAME_USER:
				$info['condition']['_xml_name_'] = $xmlName;
				$info['condition']['_id_'] = $id;

				$info4Cancel['update'] = null;
				$info4Cancel['condition']['_xml_name_'] = $xmlName4Cancel;
				$info4Cancel['condition']['_id_'] = $id;
				break;
			case XML_NAME_CANCEL:
				$info4Cancel = $info;
				$info4Cancel['condition']['_xml_name_'] = $xmlName4Cancel;
				$info4Cancel['condition']['_id_'] = $id;

				$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
				$info['condition']['_xml_name_'] = $xmlName;
				$info['condition']['_id_'] = $id;
				break;
		}

		break;
	case 'GET':
//		//XMLファイル名
//		$xmlName = (isset($_GET['xml_name'])?$_GET['xml_name']:null);
		//ターゲットID
		$id = (isset($_GET['id'])?$_GET['id']:null);

		//遷移元ページ
		$pId = (isset($_GET['p_id'])?$_GET['p_id']:null);

		//初期値を設定する。
		$undeleteOnly4def = false;

		_Log("[/user/index.php] {ログインユーザー権限処理} ユーザーID = '".$loginInfo['usr_user_id']."'");
		_Log("[/user/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."'");

		//権限によって、表示するユーザー情報を制限する。
		switch($loginInfo['usr_auth_id']){
			case AUTH_NON://権限無し

				_Log("[/user/index.php] {ログインユーザー権限処理} 権限ID = '".$loginInfo['usr_auth_id']."' = '権限無し'");
				_Log("[/user/index.php] {ログインユーザー権限処理} →自分のユーザー情報のみ表示する。");
				_Log("[/user/index.php] {ログインユーザー権限処理} →ユーザーIDを設定する。");

				$id = null;
				$undeleteOnly4def = true;

				//自分のユーザー情報のみ表示する。
				//ユーザーIDを検索する。
				$id = $loginInfo['usr_user_id'];

				_Log("[/user/index.php] {ログインユーザー権限処理} →ユーザーID = '".$id."'");
				break;
		}

		$info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);
		$info['condition']['_xml_name_'] = $xmlName;
		$info['condition']['_id_'] = $id;

		$info4Cancel['update'] = null;
		$info4Cancel['condition']['_xml_name_'] = $xmlName4Cancel;
		$info4Cancel['condition']['_id_'] = $id;


//		//設定されている場合=更新の場合
//		if (isset($_GET['id'])) {
//			//動作モードをセッションに保存する。動作モード="他画面経由の表示"
//			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_OTHER;
//		} else {
//			//動作モードをセッションに保存する。動作モード="単独表示"
//			$_SESSION[SID_INFO_MODE] = MST_MODE_FROM_MENU;
//		}
//

		//遷移元ページをセッションに保存する。
		$_SESSION[SID_USER_FROM_PAGE_ID] = $pId;

		break;
}

_Log("[/user/index.php] \$_SERVER[\"REQUEST_METHOD\"] = '".$_SERVER["REQUEST_METHOD"]."'");
_Log("[/user/index.php] XMLファイル名 = '".$xmlName."'");
_Log("[/user/index.php] XMLファイル名(Cancel) = '".$xmlName4Cancel."'");
_Log("[/user/index.php] フォームモード = '".$formMode."'");
_Log("[/user/index.php] ターゲットID = '".$id."'");

//ユーザー情報(ログイン情報)を設定する。→DB更新に使用する。画面に表示する。
$info4Cancel['update']['tbl_user'] = $loginInfo;


//XMLを読み込む。
$xmlFile = "../common/form_xml/".$xmlName.".xml";
_Log("[/user/index.php] XMLファイル = '".$xmlFile."'");
$xmlList = _GetXml($xmlFile);

$xmlFile4Cancel = "../common/form_xml/".$xmlName4Cancel.".xml";
_Log("[/user/index.php] XMLファイル = '".$xmlFile4Cancel."'");
$xmlList4Cancel = _GetXml($xmlFile4Cancel);

_Log("[/user/index.php] XMLファイル配列 = '".print_r($xmlList,true)."'");
_Log("[/user/index.php] XMLファイル配列(Cancel) = '".print_r($xmlList4Cancel,true)."'");


//「利用規約」項目を削除する。
$xmlList = _DeleteXmlByTagAndValue($xmlList, 'item_id', 'usr_rule');
_Log("[/user/index.php] XMLファイル配列(「利用規約」項目を削除後) = '".print_r($xmlList,true)."'");

//XMLファイル配列を保持する。→メール送信に使う。
$bufXmlList4Cancel = $xmlList4Cancel;
$xmlList4Cancel = _DeleteXmlByTag($xmlList4Cancel, 'tbl_user');
_Log("[/user/index.php] XMLファイル配列(Cancel)(「ユーザー情報」項目を削除後) = '".print_r($xmlList4Cancel,true)."'");


switch ($formMode) {
	case XML_NAME_USER:
		//確認ボタンが押された場合
		if ($_POST['confirm'] != "") {
			//入力値チェック
			$message .= _CheackInputAll($xmlList, $info);
			//メールアドレスの重複チェック
			if (isset($info['update']['tbl_user']['usr_e_mail']) && !_IsNull($info['update']['tbl_user']['usr_e_mail'])) {
				$condition4email = array();
				$condition4email['usr_e_mail'] = $info['update']['tbl_user']['usr_e_mail'];
				$bufList = _DB_GetList('tbl_user', $condition4email, true, null, 'usr_del_flag', 'usr_user_id');
				if (!_IsNull($bufList)) {
					//ユーザーIDが設定済みの場合、検索結果から自分自身のデータを削除する。
					if (isset($info['update']['tbl_user']['usr_user_id']) && !_IsNull($info['update']['tbl_user']['usr_user_id'])) {
						unset($bufList[$info['update']['tbl_user']['usr_user_id']]);
					}
					if (count($bufList) > 0) {
						$message .= "メールアドレスは既に登録済みです。\n";
					}
				}
			}
			if (_IsNull($message)) {
				//エラーが無い場合、確認画面を表示する。
				$mode = 2;
				//$message .= "※入力内容を確認して、「更新」ボタンを押してください。";
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
			//メールアドレスの重複チェック(再チェック)
			if (isset($info['update']['tbl_user']['usr_e_mail']) && !_IsNull($info['update']['tbl_user']['usr_e_mail'])) {
				$condition4email = array();
				$condition4email['usr_e_mail'] = $info['update']['tbl_user']['usr_e_mail'];
				$bufList = _DB_GetList('tbl_user', $condition4email, true, null, 'usr_del_flag', 'usr_user_id');
				if (!_IsNull($bufList)) {
					//ユーザーIDが設定済みの場合、検索結果から自分自身のデータを削除する。
					if (isset($info['update']['tbl_user']['usr_user_id']) && !_IsNull($info['update']['tbl_user']['usr_user_id'])) {
						unset($bufList[$info['update']['tbl_user']['usr_user_id']]);
					}
					if (count($bufList) > 0) {
						$message .= "メールアドレスは既に登録済みです。\n";
					}
				}
			}
			if (_IsNull($message)) {
				//エラーが無い場合、登録する。
				//更新・登録をする。(※$infoは最新情報に更新される。)
				$res = _UpdateInfo($info);
				if ($res === false) {
					//エラーが有り場合
					$message = "登録に失敗しました。";
					$errorFlag = true;
				} else {
					//メッセージを設定する。
					$message .= "更新しました。";
					//自分のユーザー情報を更新した場合、セッションのログイン情報を上書きする。
					if ($info['condition']['_id_'] == $loginInfo['usr_user_id']) {
						_Log("[/user/index.php] セッション情報 \$_SESSION (ログイン情報更新前) = '".print_r($_SESSION,true)."'");
						$_SESSION[SID_LOGIN_USER_INFO] = $info['update']['tbl_user'];
						_Log("[/user/index.php] セッション情報 \$_SESSION (ログイン情報更新後) = '".print_r($_SESSION,true)."'");
					}
//					//完了画面を表示する。
//					$mode = 3;
				}
			} else {
				//エラーが有り場合
				$message = "※入力に誤りがあります。\n".$message;
				$errorFlag = true;
			}
		}
		break;
	case XML_NAME_CANCEL:
		//送信ボタンが押された場合
		if ($_POST['confirm'] != "") {
			//入力値チェック
			$message4Cancel .= _CheackInputAll($xmlList4Cancel, $info4Cancel);
			if (_IsNull($message4Cancel)) {
				//エラーが無い場合、確認画面を表示する。
				$mode4Cancel = 2;

				//$message4Cancel .= "※入力内容を確認して、「更新」ボタンを押してください。";
			} else {
				//エラーが有り場合
				$message4Cancel = "※入力に誤りがあります。\n".$message4Cancel;
				$errorFlag4Cancel = true;
			}
		}
		//戻るボタンが押された場合
		elseif ($_POST['back'] != "") {
		}
		//送信ボタンが押された場合
		elseif ($_POST['go'] != "") {
			if (_IsNull($message4Cancel)) {
				//エラーが無い場合、登録する。
				//削除フラグを"削除済"に設定する。
				$info4Cancel['update']['tbl_user']['usr_del_flag'] = DELETE_FLAG_YES;
				//登録解除の情報を保持する。
				$bufCancelInfo = $info4Cancel['update']['tbl_cancel'];
				//更新・登録をする。(※$info4Cancelは最新情報に更新される。)
				$res = _UpdateInfo($info4Cancel);
				if ($res === false) {
					//エラーが有り場合
					$message4Cancel = "登録に失敗しました。";
					$errorFlag4Cancel = true;
				} else {
					//登録解除の情報を戻す。→メール本文に使用する。
					$info4Cancel['update']['tbl_cancel'] = $bufCancelInfo;

					$xmlList4Cancel = $bufXmlList4Cancel;

					//メール本文の共通部分を設定する。
					$body = null;

					$body .= _CreateMailAll($xmlList4Cancel, $info4Cancel);

					_Log("[/user/index.php] メール本文(_CreateMailAll) = '".$body."'");

					$body .= "\n";
					$body .= "\n";
					$body .= "\n";
					$body .= "\n";

					$body .= "--------------------------------------------------------\n";
					$body .= $siteTitle."\n";
					if (!_IsNull(COMPANY_NAME)) $body .= COMPANY_NAME."\n";
					if (!_IsNull(COMPANY_ZIP)) $body .= COMPANY_ZIP."\n";
					if (!_IsNull(COMPANY_ADDRESS)) $body .= COMPANY_ADDRESS."\n";
					if (!_IsNull(COMPANY_TEL)) $body .= "TEL：".COMPANY_TEL."\n";
					if (!_IsNull(COMPANY_FAX)) $body .= "FAX：".COMPANY_FAX."\n";
					$body .= "E-mail：".$clientMail." \n";
					if (!_IsNull(COMPANY_BUSINESS_HOURS)) $body .= "営業時間：".COMPANY_BUSINESS_HOURS."\n";
					$body .= "--------------------------------------------------------\n\n";

					$body .= "登録解除日時：".date("Y年n月j日 H時i分")."\n";
					$body .= $_SERVER["REMOTE_ADDR"]."\n";

					//管理者用メール本文を設定する。
					$adminBody = "";
					//$adminBody .= $siteTitle." \n";
					//$adminBody .= "\n";
					$adminBody .= "**************************************************************************************\n";
					$adminBody .= "『".$siteTitle."』の登録解除がありました。\n";
					$adminBody .= "**************************************************************************************\n";
					$adminBody .= "\n";
					$adminBody .= $body;

					//お客様用メール本文を設定する。
					$customerBody = "";
					$customerBody .= $info4Cancel['update']['tbl_user']['usr_family_name']." ".$info4Cancel['update']['tbl_user']['usr_first_name']." 様\n";
					$customerBody .= "\n";
					$customerBody .= "**************************************************************************************\n";
					$customerBody .= "『".$siteTitle."』のご利用ありがとうございました。\n";
					$customerBody .= "登録解除を承りました。\n";
					$customerBody .= "**************************************************************************************\n";
					$customerBody .= "\n";
					$customerBody .= $body;


					//管理者用タイトルを設定する。
					$adminTitle = "[".$siteTitle."] 登録解除 (".$info4Cancel['update']['tbl_user']['usr_family_name']." ".$info4Cancel['update']['tbl_user']['usr_first_name']." 様)";
					//お客様用タイトルを設定する。
					$customerTitle = "[".$siteTitle."] 登録解除承りました";

					mb_language("Japanese");
					
					$parameter = "-f ".$clientMail;

					//メール送信
					//お客様に送信する。
					$rcd = mb_send_mail($info4Cancel['update']['tbl_user']['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

					//クライアントに送信する。
					$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$info4Cancel['update']['tbl_user']['usr_e_mail']);

					//マスターに送信する。
					foreach($masterMailList as $masterMail){
						$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$info4Cancel['update']['tbl_user']['usr_e_mail']);
					}


					//メッセージを設定する。
					$message4Cancel .= $info4Cancel['update']['tbl_user']['usr_family_name']."&nbsp;".$info4Cancel['update']['tbl_user']['usr_first_name'];
					$message4Cancel .= "&nbsp;様";
					$message4Cancel .= "\n";
					$message4Cancel .= "\n";
					$message4Cancel .= "『".$siteTitle."』のご利用ありがとうございました。";
					$message4Cancel .= "\n";
					$message4Cancel .= "登録解除を承りました。";
					$message4Cancel .= "\n";
					$message4Cancel .= "お客様のメールアドレス宛てに登録解除の「確認メール」が自動送信されました。";
					$message4Cancel .= "\n";
					$message4Cancel .= "\n";
//					$message4Cancel .= "※「確認メール」が届かない場合は、メールアドレスがご登録ミスの可能性がありますので、";
//					$message4Cancel .= "\n";
//					$message4Cancel .= "&nbsp;&nbsp;&nbsp;お手数ですが&nbsp;";
					$message4Cancel .= "メールが届かない場合は、お手数ですが&nbsp;";
					$message4Cancel .= "<a href=\"mailto:".$clientMail."\">".$clientMail."</a>";
					$message4Cancel .= "&nbsp;までメールでお問い合わせください。";

					//完了画面を表示する。
					$mode4Cancel = 3;

					_Log("[/user/index.php] セッション情報 \$_SESSION (ログイン情報削除前) = '".print_r($_SESSION,true)."'");
					//セッションからログイン情報を削除する。
					unset($_SESSION[SID_LOGIN_USER_INFO]);
					_Log("[/user/index.php] セッション情報 \$_SESSION (ログイン情報削除後) = '".print_r($_SESSION,true)."'");
				}
			} else {
				//エラーが有り場合
				$message4Cancel = "※入力に誤りがあります。\n".$message4Cancel;
				$errorFlag4Cancel = true;
			}

		}
		break;
}




//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
$info4Cancel = _HtmlSpecialCharsForArray($info4Cancel);
_Log("[/user/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");
_Log("[/user/index.php] POST(Cancel)(文字をHTMLエンティティに変換する。) = '".print_r($info4Cancel,true)."'");

_Log("[/user/index.php] mode = '".$mode."'");
_Log("[/user/index.php] mode(Cancel) = '".$mode4Cancel."'");






//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../img/maincontent/pt_user.jpg\" title=\"\" alt=\"ユーザーページ\">";
$maincontent .= "</h2>";
$maincontent .= "\n";


//基本URL
$htmlMaincontentUserMenu = str_replace('{base_url}', $basePath, $htmlMaincontentUserMenu);


//お知らせ
$userNews = null;
$userNews .= "<ul>";
//$userNews .= "<li>ご利用ありがとうございます。こちらにお知らせが表示されます。</li>";
//$userNews .= "<li>現在、開発中！！！！！！！！！！！</li>";
//$userNews .= "<li>法務局・公証役場の年末年始のお休みは12月27日〜1月4日まで</li>";

//プランIDによって、お知らせを設定する。
switch($loginInfo['usr_plan_id']){
	case MST_PLAN_ID_NORMAL://通常プラン
		$userNews .= "<li>ご利用ありがとうございます。こちらにお知らせが表示されます。</li>";
		break;
	case MST_PLAN_ID_STANDARD://スタンダードパートナープラン
	case MST_PLAN_ID_PLATINUM://プラチナパートナープラン
//		$userNews .= "<li>【OEM制度をご利用のスタンダード・ゴールドパートナープランお客様にお知らせ】<br />通常プラン(スタンダード・ゴールドパートナープラン以外の通常のお客様用プラン)のシステム使用料が2,800円(1,000円OFF)になりました。</li>";
		$userNews .= "<li>【OEM制度をご利用のスタンダード・ゴールドパートナープランお客様にお知らせ】<br />システム使用料が2,800円(1,000円OFF)になりました。</li>";
		break;
}
$userNews .= "</ul>";


$htmlMaincontentUserMenu = str_replace('{user_news}', $userNews, $htmlMaincontentUserMenu);


//ご利用ステータス
$userStatus = null;
//$userStatus .= "<ul>";
//$userStatus .= "<li>株式会社設立 → 2009-01-27までご利用できます。印刷OK</li>";
//$userStatus .= "<li>合同会社設立(LLC) → 未入金-印刷できません。ご入金後ご利用になれます。印刷NO</li>";
//$userStatus .= "</ul>";

$userStatus .= _GetUserStatusHtml($loginInfo['usr_user_id']);

$htmlMaincontentUserMenu = str_replace('{user_status}', $userStatus, $htmlMaincontentUserMenu);

//株式会社一覧
$userCompanyRelation = _GetUserCompanyRelationHtml($loginInfo['usr_user_id'], MST_COMPANY_TYPE_ID_CMP);
$htmlMaincontentUserMenu = str_replace('{company_list}', $userCompanyRelation, $htmlMaincontentUserMenu);

//合同会社一覧
$userCompanyRelation = _GetUserCompanyRelationHtml($loginInfo['usr_user_id'], MST_COMPANY_TYPE_ID_LLC);
$htmlMaincontentUserMenu = str_replace('{llc_list}', $userCompanyRelation, $htmlMaincontentUserMenu);

//登録情報の設定
//更新
$userInfoUpdate = null;
$userInfoUpdate .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);
//解除
$userInfoCancel = null;
$userInfoCancel .= _GetFormTable($mode4Cancel, $xmlList4Cancel, $info4Cancel, $tabindex, $loginInfo, $message4Cancel, $errorFlag4Cancel, $allShowFlag);

$htmlMaincontentUserMenu = str_replace('{user_info_update}', $userInfoUpdate, $htmlMaincontentUserMenu);
$htmlMaincontentUserMenu = str_replace('{user_info_cancel}', $userInfoCancel, $htmlMaincontentUserMenu);


$maincontent .= $htmlMaincontentUserMenu;



//スクリプトを設定する。
$script = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	$script .= "<style type=\"text/css\">";
	$script .= "\n";
	$script .= "<!--";
	$script .= "\n";

	$script .= "#mc_user_news";
	$script .= "\n";
	$script .= ",#mc_user_status";
	$script .= "\n";
	$script .= ",#mc_user_menu";
	$script .= "\n";
	$script .= ",#mc_ui_update";
	$script .= "\n";
	$script .= ",#mc_ui_end_update";
	$script .= "\n";
	$script .= "{display:none;}";
	$script .= "\n";

	$script .= "-->";
	$script .= "\n";
	$script .= "</style>";
	$script .= "\n";
}


//サイドメニューを設定する。
$sidebar = null;

//ログインしているか？
if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
	//基本URL
	$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
	//ログインユーザー名
	$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
	//現在の入力状況
	$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);

	$sidebar .= $htmlSidebarUserMenu;
} else {
	//基本URL
	$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);

	$sidebar .= $htmlSidebarLogin;
}



//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
//パンくずリストを取得する。
$breadcrumbs = _GetBreadcrumbs();

//WOOROMフッター管理
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=".$_SERVER['SERVER_NAME']."&php_self=".$_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
	$wooromFooter = null;
}



//テンプレートを編集する。(必要箇所を置換する。)
//タイトル
if (!_IsNull($title)) $title = "[".$title."] ";
$title = $siteTitle." ".$title;
$html = str_replace('{title}', $title, $html);
//メタ情報
$html = str_replace ('{keywords}', PAGE_KEYWORDS_HOME, $html);
$html = str_replace ('{description}', PAGE_DESCRIPTION_HOME, $html);
//コンテンツ
$html = str_replace('{maincontent}', $maincontent, $html);
//サイドメニュー
$html = str_replace('{sidebar}', $sidebar, $html);
//スクリプト
$html = str_replace('{script}', $script, $html);
//基本URL
$html = str_replace('{base_url}', $basePath, $html);
//パンくずリスト
$html = str_replace('{breadcrumbs}', $breadcrumbs, $html);
//WOOROMフッター管理
$html = str_replace('{woorom_footer}', $wooromFooter, $html);


_Log("[/user/index.php] end.");
echo $html;

?>
