<?php
/*
 * [新★会社設立.JP ツール]
 * よくある質問ページ
 *
 * 更新履歴：2010/11/20	d.ishikawa	新規作成
 *         ：2011/10/27	d.ishikawa	更新機能追加
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/faq/index.php] start.");


_Log("[/faq/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/faq/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/faq/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/faq/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


//認証チェック----------------------------------------------------------------------start
$loginInfo = null;

//ログインしているか？
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	_Log("[/faq/index.php] ログインしていないなのでログイン画面を表示する。");
//	_Log("[/faq/index.php] end.");
//	//ログイン画面を表示する。
//	header("Location: ".URL_BASE);
//	exit;
} else {
	//ログイン情報を取得する。
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
//
//	//本画面を使用可能な権限かチェックする。使用不可の場合、ログイン画面に遷移する。
//	_CheckAuth($loginInfo, AUTH_NON, AUTH_CLIENT, AUTH_WOOROM);
}
//認証チェック----------------------------------------------------------------------end



//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("[/faq/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../common/temp_html/temp_base.txt';
_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


$tempSidebarLoginFile = '../common/temp_html/temp_sidebar_login.txt';
_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");

$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
	_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
}

$tempSidebarUserMenuFile = '../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}


$tempMaincontentFaqFile = '../common/temp_html/temp_maincontent_faq.txt';
_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (メインコンテンツよくある質問) HTMLテンプレートファイル = '".$tempMaincontentFaqFile."'");

$htmlMaincontentFaq = @file_get_contents($tempMaincontentFaqFile);
//"HTML"が存在する場合、表示する。
if ($htmlMaincontentFaq !== false && !_IsNull($htmlMaincontentFaq)) {
	_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (メインコンテンツよくある質問) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/faq/index.php] {HTMLテンプレートを読み込み} (メインコンテンツよくある質問) 【失敗】");
}




_Log("[/faq/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_FAQ;



//タブインデックス
$tabindex = 0;

//DBをオープンする。
$cid = _DB_Open();

//動作モード{1:入力/2:確認/3:完了/4:エラー}
$mode = 1;

//全て表示するか？hidden項目も表示するか？{true:全て表示する。/false:XML設定、権限による表示有無に従う。}
$allShowFlag = false;

//メッセージ
$message = "";
//エラーフラグ
$errorFlag = false;


//入力情報を格納する配列
$info = array();





//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
_Log("[/faq/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");

_Log("[/faq/index.php] mode = '".$mode."'");

///////////////////////////////////////////////////

$key = $_GET['_@_key_@_'];
$key = trim($key);
$key = trim($key, '/');
$keyList = explode('/', $key);

_Log("[/index.php] キー = '".$key."'");
_Log("[/index.php] キー = '\n".print_r($keyList,true)."\n'");



//認証チェック----------------------------------------------------------------------start
//$loginInfo = null;
//$loginFlag = false;
////ログインしているか？
//if (isset($_SESSION[SID_LOGIN_USER_INFO])) {
//	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
//	$loginFlag = true;
//}

$loginFlag = false;
if (!_IsNull($loginInfo)) {
	//権限によって…
	switch($loginInfo['usr_auth_id']){
		case AUTH_WOOROM://WOOROM権限
			$loginFlag = true;
			break;
	}
}
//認証チェック----------------------------------------------------------------------end


////HTMLテンプレートを読み込む。------------------------------------------------------- start
//_Log("[/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
//$tempFile = './common/temp_html/temp_base.txt';
//_Log("[/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");
//
//$html = @file_get_contents($tempFile);
////"HTML"が存在する場合、表示する。
//if ($html !== false && !_IsNull($html)) {
//	_Log("[/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
//} else {
//	//取得できなかった場合
//	_Log("[/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
//	$html .= "HTMLテンプレートファイルを取得できません。\n";
//}
//_Log("[/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
////HTMLテンプレートを読み込む。------------------------------------------------------- end


$baseUrl = URL;
$baseUrl .= '/faq';


//全ての検索に対しての削除フラグを設定する。管理者の場合、全件を表示する。
$undeleteOnly4def = true;
if ($loginFlag) $undeleteOnly4def = false;


//DBをオープンする。
$link = _DB_Open();

//マスタを設定する。
$condition = null;
$order = null;
$order .= "lpad(show_order,10,'0')";
$order .= ",id";
//カテゴリーマスタ
$mstCategoryList = _DB_GetList('mst_category', $condition, $undeleteOnly4def, $order, 'del_flag', 'id');

//$keyListのキー
//0:トップページ
//1:カテゴリーページ
//2:FAQページ(詳細ページ)
//3:以降無視

//動作モード
//1:トップページ
//2:カテゴリーページ
//3:FAQページ(詳細ページ)
//4:検索ページ
//5:ログインページ
//6:ログアウトページ

$mode = 1;
//存在しないURLなら、トップページを表示する。
$modeErrorFlag = false;

if (count($keyList) > 2) {
	$mode = 3;
} elseif (count($keyList) == 2) {
	switch ($keyList[1]) {
		case 'search':
			$mode = 4;
			break;
		case 'login':
			$mode = 5;
			break;
		case 'logout':
			$mode = 6;
			break;
		default:
			$mode = 2;
			break;
	}
} else {
	$mode = 1;
}

//カテゴリーを検索する。
$keyMstCategoryInfo = null;
if (isset($keyList[1]) && !_IsNull($keyList[1])) {
	switch ($keyList[1]) {
		case 'search':
		case 'login':
		case 'logout':
			break;
		default:
			foreach ($mstCategoryList as $mcKey => $mstCategoryInfo) {
				if ($keyList[1] == $mstCategoryInfo['value']) {
					$keyMstCategoryInfo = $mstCategoryInfo;
					break;
				}
			}
			if (_IsNull($keyMstCategoryInfo)) {
				$modeErrorFlag = true;
				_Log("[/index.php] {キーチェック} 【エラー】カテゴリー存在しない。");
			}
			break;
	}
}

//FAQ情報を検索する。
$keyFaqInfo = null;
if (isset($keyList[2]) && !_IsNull($keyList[2])) {
	_Log("[/index.php] FAQページ キー = '".$keyList[2]."'");
	$bufKey = str_replace(FAQ_DIR_FORMAT_1, '', $keyList[2]);
	_Log("[/index.php] FAQページ キー(ID取得) = '".$bufKey."'");
	$bufKey = (int)$bufKey;
	_Log("[/index.php] FAQページ キー(ID数値化) = '".$bufKey."'");
	if ($bufKey != 0) {
		//詳細情報を取得する。
		$keyFaqInfo = _GetFaqInfo($bufKey, $undeleteOnly4def);
	}
	if (_IsNull($keyFaqInfo)) {
		$modeErrorFlag = true;
		_Log("[/index.php] {キーチェック} 【エラー】FAQ存在しない。");
	}
}

if (!_IsNull($keyMstCategoryInfo) && !_IsNull($keyFaqInfo)) {
	if ($keyMstCategoryInfo['id'] != $keyFaqInfo['tbl_faq']['faq_category_id']) {
		$modeErrorFlag = true;
		_Log("[/index.php] {キーチェック} 【エラー】カテゴリーとFAQのカテゴリーが不一致。");
		_Log("[/index.php] {キーチェック} 【エラー】カテゴリー = '".$keyMstCategoryInfo['id']."'");
		_Log("[/index.php] {キーチェック} 【エラー】FAQのカテゴリー = '".$keyFaqInfo['tbl_faq']['faq_category_id']."'");
	}
}

_Log("[/index.php] {動作モード} モード = '".$mode."'");
_Log("[/index.php] {動作モード} エラー = '".$modeErrorFlag."'");


//エラーの場合、トップページを表示する。
if ($modeErrorFlag) {
	header("Location: ".$baseUrl."/");
	exit;
}


$keyFaqInfoNoSpecial = $keyFaqInfo;


//文字をHTMLエンティティに変換する。
$keyFaqInfo = _HtmlSpecialCharsForArray($keyFaqInfo);
$keyMstCategoryInfo = _HtmlSpecialCharsForArray($keyMstCategoryInfo);
$mstCategoryList = _HtmlSpecialCharsForArray($mstCategoryList);


////サイトタイトル
//$siteTitle = SITE_TITLE;

////ページタイトル
//$pageTitle = PAGE_TITLE_HOME;



////タイトルを設定する。
//$title = $pageTitle;

//基本URLを設定する。
$basePath = URL;


$info = null;
$ambiguousList = null;
$message = null;
if (isset($_POST['bt_search'])) {
	$info = $_POST;
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/index.php] \$_POST(バックスラッシュ除去後) = '".print_r($info,true)."'");

	if (isset($info['search']['ambiguous']) && !_IsNull($info['search']['ambiguous'])) {
		$ambiguous = $info['search']['ambiguous'];
		_Log("[/index.php] {検索} キーワード = '".$ambiguous."'");
		//トリムする。
		$ambiguous = trim($ambiguous);
		_Log("[/index.php] {検索} キーワード(トリム) = '".$ambiguous."'");
		$ambiguous = trim($ambiguous, '　');
		_Log("[/index.php] {検索} キーワード(トリム：全角SP) = '".$ambiguous."'");

		//戻す。
		$info['search']['ambiguous'] = $ambiguous;

		//「全角」スペースを「半角」に変換する。
		$ambiguous = mb_convert_kana($ambiguous, 's');
		_Log("[/index.php] {検索} キーワード(全角→半角SP) = '".$ambiguous."'");
		//複数SPを単一にする。
		$ambiguous = preg_replace('/ +/', ' ', $ambiguous);
		_Log("[/index.php] {検索} キーワード(複数→単一SP) = '".$ambiguous."'");
		//配列に分割する。
		$ambiguousList = explode(' ', $ambiguous);
		_Log("[/index.php] {検索} キーワード(配列) = '".print_r($ambiguousList, true)."'");

		$message .= "「".$info['search']['ambiguous']."」";
	}
}

if (isset($_POST['bt_login'])) {
	$info = $_POST;
	//バックスラッシュを取り除く。
	$info = _StripslashesForArray($info);
	_Log("[/index.php] \$_POST(バックスラッシュ除去後) = '".print_r($info,true)."'");

	if (!isset($info['login']['usr_e_mail']) || _IsNull($info['login']['usr_e_mail'])) {
		$message .= "E-Mailを入力してください。\n";
	}
	if (!isset($info['login']['usr_pass']) || _IsNull($info['login']['usr_pass'])) {
		$message .= "パスワードを入力してください。\n";
	}
	if (_IsNull($message)) {
		//ユーザーテーブルを検索する。
		$condition = array();
		$condition['usr_e_mail'] = $info['login']['usr_e_mail'];
		$condition['usr_pass'] = $info['login']['usr_pass'];
		$order = null;
		$tblUserList = _DB_GetList('tbl_user', $condition, true, $order, 'usr_del_flag');
		if (_IsNull($tblUserList)) {
			$message .= "E-Mail又はパスワードが不正です。\n";
		} else {
			//セッションにユーザー情報を保存する。→先頭を設定する。1件のはず。
			$_SESSION[SID_LOGIN_USER_INFO] = $tblUserList[0];
			//トップページを表示する。
			header("Location: ".$baseUrl."/");
			exit;
		}
	}
}


$from = null;
$from .= "<form id=\"frm_search\" name=\"frm_search\" action=\"".$basePath."/faq/search/\" method=\"post\" enctype=\"multipart/form-data\">";
$from .= "\n";
$from .= "<input class=\"txt\" type=\"text\" name=\"search[ambiguous]\" maxlength=\"500\" value=\"".$info['search']['ambiguous']."\" />";
$from .= "\n";
$from .= "<input class=\"btn\" type=\"submit\" name=\"bt_search\" value=\"検　索\" />";
$from .= "\n";
$from .= "<span>空白で区切って複数のキーワードを検索できます。例：設立　費用</span>";
$from .= "\n";
$from .= "</form>";
$from .= "\n";

$contact = null;
$contact .= "<div class=\"contact\">";
$contact .= "\n";
$contact .= "<h3>";
$contact .= "解決しましたか？";
$contact .= "</h3>";
$contact .= "\n";
$contact .= "<p>";
$contact .= "解決しない場合は";
//$contact .= "<a href=\"mailto:info@sin-kaisha.jp\">";
$contact .= "<a href=\"/inquiry/\">";
$contact .= "サポート";
$contact .= "</a>";
$contact .= "へお問い合わせください。";
$contact .= "</p>";
$contact .= "\n";
$contact .= "</div><!-- class=\"contact\" -->";
$contact .= "\n";



$content = null;
$category = null;
switch ($mode) {
	case 6:
		//ログアウトページ

		//セッションからユーザー情報を削除する。
		unset($_SESSION[SID_LOGIN_USER_INFO]);
		$loginInfo = null;
		$loginFlag = false;

		$message .= "ログアウトしました。\n";
		$message .= "\n";
		$message .= "\n";
		$message .= "<a href=\"".$baseUrl."/\">";
		$message .= "トップページはこちら&nbsp;&gt;&gt;";
		$message .= "</a>";
		$message .= "\n";
		$message .= "<a href=\"".$baseUrl."/login/\">";
		$message .= "再ログインはこちら&nbsp;&gt;&gt;";
		$message .= "</a>";

		$from = null;
		$from .= "<form id=\"frm_login\" name=\"frm_login\" action=\"".$basePath."/faq/login/\" method=\"post\" enctype=\"multipart/form-data\">";
		$from .= "\n";
		$from .= "<h3>";
		$from .= "ログアウト";
		$from .= "</h3>";
		$from .= "\n";
		$from .= "<div class=\"message\">";
		$from .= "\n";
		$from .= nl2br($message);
		$from .= "</div>";
		$from .= "\n";
		$from .= "</form>";
		$from .= "\n";


		$contact = null;
		break;
	case 5:
		//ログインページ

		$from = null;
		$from .= "<form id=\"frm_login\" name=\"frm_login\" action=\"".$basePath."/faq/login/\" method=\"post\" enctype=\"multipart/form-data\">";
		$from .= "\n";
		$from .= "<h3>";
		$from .= "ログイン";
		$from .= "</h3>";
		$from .= "\n";
		$from .= "E-Mail：<br />";
		$from .= "\n";
		$from .= "<input class=\"txt\" type=\"text\" name=\"login[usr_e_mail]\" maxlength=\"200\" value=\"".$info['login']['usr_e_mail']."\" />";
		$from .= "\n";
		$from .= "<br />";
		$from .= "\n";
		$from .= "パスワード：<br />";
		$from .= "\n";
		$from .= "<input class=\"txt\" type=\"password\" name=\"login[usr_pass]\" maxlength=\"200\" value=\"".$info['login']['usr_pass']."\" />";
		$from .= "\n";
		$from .= "<br />";
		$from .= "\n";
		$from .= "<input class=\"btn\" type=\"submit\" name=\"bt_login\" value=\"ログイン\" />";
		$from .= "\n";
		$from .= "<div class=\"message\">";
		$from .= "\n";
		$from .= nl2br($message);
		$from .= "</div>";
		$from .= "\n";
		$from .= "</form>";
		$from .= "\n";


		$contact = null;
		break;
	case 4:
		//検索ページ

		//キーワードで検索する。
		$tblFaqList = null;
		if (!_IsNull($ambiguousList)) {
			$condition = array();
			$condition['ambiguous'] = $ambiguousList;//キーワード
			$order = null;
			$tblFaqList = _GetFaq($condition, $order, $undeleteOnly4def);
		}
		if (!_IsNull($tblFaqList)) {
			$message .= "の検索結果 (".number_format(count($tblFaqList)).")";


			$tblFaqListNoSpecial = $tblFaqList;
			//文字をHTMLエンティティに変換する。
			$tblFaqList = _HtmlSpecialCharsForArray($tblFaqList);

			$category .= "<div class=\"category category_long\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= "検索";
			$category .= "</h3>";
			$category .= "\n";
			$category .= "<div class=\"message\">";
			$category .= $message;
			$category .= "</div>";
			$category .= "\n";
			$category .= "<ul class=\"search\">";
			$category .= "\n";

			$snui = uniqid('s_');
			$enui = uniqid('e_');
			_Log("[/index.php] {検索結果} 仮タグ：開始 = '".$snui."'");
			_Log("[/index.php] {検索結果} 仮タグ：終了 = '".$enui."'");


			foreach ($tblFaqList as $tfKey => $tblFaqInfo) {
				$faqTitle = $tblFaqListNoSpecial[$tfKey]['faq_title'];
				_Log("[/index.php] {検索結果} タイトル = '".$faqTitle."'");
				$faqTitle = _SubStr($faqTitle, 50);
				_Log("[/index.php] {検索結果} タイトル(先頭80文字) = '".$faqTitle."'");
				//HTMLエンティティに変換する。
				$faqTitle = htmlspecialchars($faqTitle, ENT_QUOTES);
				_Log("[/index.php] {検索結果} タイトル(HTMLエンティティ変換後) = '".$faqTitle."'");
				foreach ($ambiguousList as $aKey => $ambiguous) {
					$faqTitle = preg_replace('/'.htmlspecialchars($ambiguous).'/', ''.$snui.''.htmlspecialchars($ambiguous).''.$enui.'', $faqTitle);
				}
				_Log("[/index.php] {検索結果} タイトル(仮タグ) = '".$faqTitle."'");
				foreach ($ambiguousList as $aKey => $ambiguous) {
					$faqTitle = preg_replace('/'.$snui.'/', '<span class="amb">', $faqTitle);
					$faqTitle = preg_replace('/'.$enui.'/', '</span>', $faqTitle);
				}
				_Log("[/index.php] {検索結果} タイトル(ハイライト後) = '".$faqTitle."'");


				$faqContent = $tblFaqListNoSpecial[$tfKey]['faq_content'];
				_Log("[/index.php] {検索結果} 内容 = '".$faqContent."'");
				//改行を削除する。
				$faqContent = preg_replace('/[\r\n]/', '', $faqContent);
				_Log("[/index.php] {検索結果} 内容(改行削除) = '".$faqContent."'");
				//HTMLタグを削除する。
				$faqContent = strip_tags($faqContent);
				_Log("[/index.php] {検索結果} 内容(HTMLタグ削除) = '".$faqContent."'");
				$faqContent = _SubStr($faqContent, 300);
				_Log("[/index.php] {検索結果} 内容(先頭300文字) = '".$faqContent."'");
				//HTMLエンティティに変換する。
				$faqContent = htmlspecialchars($faqContent, ENT_QUOTES);
				_Log("[/index.php] {検索結果} 内容(HTMLエンティティ変換後) = '".$faqContent."'");
				foreach ($ambiguousList as $aKey => $ambiguous) {
					$faqContent = preg_replace('/'.htmlspecialchars($ambiguous).'/', ''.$snui.''.htmlspecialchars($ambiguous).''.$enui.'', $faqContent);
				}
				_Log("[/index.php] {検索結果} 内容(仮タグ) = '".$faqContent."'");
				foreach ($ambiguousList as $aKey => $ambiguous) {
					$faqContent = preg_replace('/'.$snui.'/', '<span class="amb">', $faqContent);
					$faqContent = preg_replace('/'.$enui.'/', '</span>', $faqContent);
				}
				_Log("[/index.php] {検索結果} 内容(ハイライト後) = '".$faqContent."'");


				$category .= "<li>";
				$category .= "<a href=\"".$baseUrl."/".$tblFaqInfo['faq_category_value']."/".sprintf(FAQ_DIR_FORMAT, $tblFaqInfo['faq_id'])."/\">";
				$category .= $faqTitle;
				$category .= "</a>";
				$category .= "<span class=\"min\">";
				$category .= $faqContent;
				$category .= "</span>";
				$category .= "</li>";
				$category .= "\n";

				if ($loginFlag) {
					$category .= "<li class=\"edit\">";
					$category .= "<a href=\"\" title=\"編集\" class=\"faq\" _faq_id=\"".$tblFaqInfo['faq_id']."\">[編集]</a>";
					$category .= "&nbsp;|&nbsp;";
					$category .= "表示順：";
					$category .= (_IsNull($tblFaqInfo['faq_show_order']) ? '-' : $tblFaqInfo['faq_show_order']);
					$category .= "&nbsp;|&nbsp;";
					$category .= ($tblFaqInfo['faq_del_flag'] == DELETE_FLAG_YES ? DELETE_FLAG_YES_NAME : '');
					$category .= "</li>";
					$category .= "\n";
				}
			}
			$category .= "</ul>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		} else {
			if (!_IsNull($ambiguousList)) {
				$message .= "に一致する項目は見つかりませんでした。";
			} else {
				$message = "検索条件を入力してください。";
			}

			$category .= "<div class=\"category category_long\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= "検索";
			$category .= "</h3>";
			$category .= "\n";
			$category .= "<div class=\"message\">";
			$category .= $message;
			$category .= "</div>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}
		break;
	case 3:
		//FAQページ(詳細ページ)

		//内容はHTMLタグ使用可。
		$faqContent = $keyFaqInfoNoSpecial['tbl_faq']['faq_content'];
		$faqContent = nl2br($faqContent);

		$category .= "<div class=\"info\">";
		$category .= "\n";
		$category .= "<h3>";
		$category .= $keyFaqInfo['tbl_faq']['faq_title'];
		$category .= "</h3>";
		$category .= "\n";

		if ($loginFlag) {
			$category .= "<div class=\"edit\">";
			$category .= "<a href=\"\" title=\"編集\" class=\"faq\" _faq_id=\"".$keyFaqInfo['tbl_faq']['faq_id']."\">[編集]</a>";
			$category .= "&nbsp;|&nbsp;";
			$category .= "表示順：";
			$category .= (_IsNull($keyFaqInfo['tbl_faq']['faq_show_order']) ? '-' : $keyFaqInfo['tbl_faq']['faq_show_order']);
			$category .= "&nbsp;|&nbsp;";
			$category .= ($keyFaqInfo['tbl_faq']['faq_del_flag'] == DELETE_FLAG_YES ? DELETE_FLAG_YES_NAME : '');
			$category .= "</div>";
			$category .= "\n";
		}

		$category .= "<div class=\"navi\">";
		$category .= "<span>";
		$category .= "カテゴリー：";
		$category .= "<a href=\"".$baseUrl."/".$keyMstCategoryInfo['value']."/\">";
		$category .= $keyMstCategoryInfo['name'];
		$category .= "</a>";
		$category .= "</span>";
		$category .= "</div>";
		$category .= "\n";
		$category .= "<div class=\"answer\">";
		$category .= "\n";
		$category .= $faqContent;
		$category .= "\n";
		$category .= "</div>";
		$category .= "\n";
		$category .= "</div><!-- class=\"info\" -->";
		$category .= "\n";
		break;
	case 2:
		//カテゴリーページ

		//該当のカテゴリーを設定する。
		$condition = array();
		$condition['faq_category_id'] = $keyMstCategoryInfo['id'];//カテゴリーID
		$order = null;
		$tblFaqList = _GetFaq($condition, $order, $undeleteOnly4def);
		if (!_IsNull($tblFaqList)) {
			$tblFaqListNoSpecial = $tblFaqList;
			//文字をHTMLエンティティに変換する。
			$tblFaqList = _HtmlSpecialCharsForArray($tblFaqList);

			$category .= "<div class=\"category category_long\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= $keyMstCategoryInfo['name'];
			$category .= "</h3>";
			$category .= "\n";
			$category .= "<ul>";
			$category .= "\n";
			foreach ($tblFaqList as $tfKey => $tblFaqInfo) {
				$faqTitle = $tblFaqListNoSpecial[$tfKey]['faq_title'];
				$faqTitle = _SubStr($faqTitle, 50);
				$faqTitle = htmlspecialchars($faqTitle, ENT_QUOTES);

				$category .= "<li>";
				$category .= "<a href=\"".$baseUrl."/".$tblFaqInfo['faq_category_value']."/".sprintf(FAQ_DIR_FORMAT, $tblFaqInfo['faq_id'])."/\">";
				$category .= $faqTitle;
				$category .= "</a>";
				$category .= "</li>";
				$category .= "\n";

				if ($loginFlag) {
					$category .= "<li class=\"edit\">";
					$category .= "<a href=\"\" title=\"編集\" class=\"faq\" _faq_id=\"".$tblFaqInfo['faq_id']."\">[編集]</a>";
					$category .= "&nbsp;|&nbsp;";
					$category .= "表示順：";
					$category .= (_IsNull($tblFaqInfo['faq_show_order']) ? '-' : $tblFaqInfo['faq_show_order']);
					$category .= "&nbsp;|&nbsp;";
					$category .= ($tblFaqInfo['faq_del_flag'] == DELETE_FLAG_YES ? DELETE_FLAG_YES_NAME : '');
					$category .= "</li>";
					$category .= "\n";
				}
			}
			$category .= "</ul>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}

		if (_IsNull($category)) {
			$category .= "<div class=\"category category_long\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= $keyMstCategoryInfo['name'];
			$category .= "</h3>";
			$category .= "\n";
			$category .= "<div class=\"message\">";
			$category .= "準備中…";
			$category .= "</div>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}
		break;
	case 1:
	default:
		//トップページ

		//よるある質問を設定する。
		$condition = array();
		$condition['faq_frequently_flag'] = FREQUENTLY_FLAG_YES;//よくある質問フラグ="「よくある質問」に表示する。"
		$order = null;
		$tblFaqList = _GetFaq($condition, $order, $undeleteOnly4def);
		if (!_IsNull($tblFaqList)) {
			$tblFaqListNoSpecial = $tblFaqList;
			//文字をHTMLエンティティに変換する。
			$tblFaqList = _HtmlSpecialCharsForArray($tblFaqList);

			$category .= "<div class=\"category category_long\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= FREQUENTLY_FLAG_YES_NAME;
			$category .= "</h3>";
			$category .= "\n";
			$category .= "<ul>";
			$category .= "\n";
			foreach ($tblFaqList as $tfKey => $tblFaqInfo) {
				$faqTitle = $tblFaqListNoSpecial[$tfKey]['faq_title'];
				$faqTitle = _SubStr($faqTitle, 50);
				$faqTitle = htmlspecialchars($faqTitle, ENT_QUOTES);

				$category .= "<li>";
				$category .= "<a href=\"".$baseUrl."/".$tblFaqInfo['faq_category_value']."/".sprintf(FAQ_DIR_FORMAT, $tblFaqInfo['faq_id'])."/\">";
				$category .= $faqTitle;
				$category .= "</a>";
				$category .= "</li>";
				$category .= "\n";

				if ($loginFlag) {
					$category .= "<li class=\"edit\">";
					$category .= "<a href=\"\" title=\"編集\" class=\"faq\" _faq_id=\"".$tblFaqInfo['faq_id']."\">[編集]</a>";
					$category .= "&nbsp;|&nbsp;";
					$category .= "表示順：";
					$category .= (_IsNull($tblFaqInfo['faq_show_order']) ? '-' : $tblFaqInfo['faq_show_order']);
					$category .= "&nbsp;|&nbsp;";
					$category .= ($tblFaqInfo['faq_del_flag'] == DELETE_FLAG_YES ? DELETE_FLAG_YES_NAME : '');
					$category .= "</li>";
					$category .= "\n";
				}
			}
			$category .= "</ul>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}

		//全カテゴリーを設定する。
		foreach ($mstCategoryList as $mcKey => $mstCategoryInfo) {
			$condition = array();
			$condition['faq_category_id'] = $mstCategoryInfo['id'];//カテゴリーID
			$order = null;
			$count = _GetFaq($condition, $order, $undeleteOnly4def, true);
			if ($count == 0) {
				continue;
			}
			$tblFaqList = _GetFaq($condition, $order, $undeleteOnly4def, false, 1, FAQ_TOP_CATEGORY_NUM);
			$tblFaqListNoSpecial = $tblFaqList;
			//文字をHTMLエンティティに変換する。
			$tblFaqList = _HtmlSpecialCharsForArray($tblFaqList);

			$category .= "<div class=\"category\">";
			$category .= "\n";
			$category .= "<h3>";
			$category .= "<a href=\"".$baseUrl."/".$mstCategoryInfo['value']."/\">";
			$category .= $mstCategoryInfo['name'];
			$category .= "&nbsp;";
			$category .= "(";
			$category .= number_format($count);
			$category .= "件";
			$category .= ")";
			$category .= "</a>";
			$category .= "</h3>";
			$category .= "\n";

			if ($loginFlag) {
				$category .= "<ul class=\"edit\">";
			} else {
				$category .= "<ul>";
			}

			$category .= "\n";
			foreach ($tblFaqList as $tfKey => $tblFaqInfo) {
				$faqTitle = $tblFaqListNoSpecial[$tfKey]['faq_title'];
				$faqTitle = _SubStr($faqTitle, 20);
				$faqTitle = htmlspecialchars($faqTitle, ENT_QUOTES);

				$category .= "<li>";
				$category .= "<a href=\"".$baseUrl."/".$mstCategoryInfo['value']."/".sprintf(FAQ_DIR_FORMAT, $tblFaqInfo['faq_id'])."/\">";
				$category .= $faqTitle;
				$category .= "</a>";
				$category .= "</li>";
				$category .= "\n";

				if ($loginFlag) {
					$category .= "<li class=\"edit\">";
					$category .= "<a href=\"\" title=\"編集\" class=\"faq\" _faq_id=\"".$tblFaqInfo['faq_id']."\">[編集]</a>";
					$category .= "&nbsp;|&nbsp;";
					$category .= "表示順：";
					$category .= (_IsNull($tblFaqInfo['faq_show_order']) ? '-' : $tblFaqInfo['faq_show_order']);
					$category .= "&nbsp;|&nbsp;";
					$category .= ($tblFaqInfo['faq_del_flag'] == DELETE_FLAG_YES ? DELETE_FLAG_YES_NAME : '');
					$category .= "</li>";
					$category .= "\n";
				}
			}
			$category .= "</ul>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}

		if (_IsNull($category)) {
			$category .= "<div class=\"category\">";
			$category .= "\n";
			$category .= "<div class=\"message\">";
			$category .= "準備中…";
			$category .= "</div>";
			$category .= "\n";
			$category .= "</div><!-- class=\"category\" -->";
			$category .= "\n";
		}
		break;
}




$content .= "<div class=\"content\">";
$content .= "\n";
$content .= $from;
$content .= "<div class=\"end\"></div>";
$content .= "\n";
$content .= $category;
$content .= "<div class=\"end\"></div>";
$content .= "\n";
$content .= $contact;
$content .= "<div class=\"end\"></div>";
$content .= "\n";
$content .= "</div><!-- class=\"content\" -->";
$content .= "\n";



///////////////////////////////////////////////////



//タイトルを設定する。
$title = $pageTitle;

////基本URLを設定する。
//$basePath = "..";

//コンテンツを設定する。
$maincontent = null;

$maincontent .= "<div id=\"main\">";
$maincontent .= "\n";
$maincontent .= "\n";

$maincontent .= "<h2>";
$maincontent .= "<img src=\"".$basePath."/img/maincontent/pt_faq.jpg\" title=\"\" alt=\"よくある質問\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

////基本URL
//$htmlMaincontentFaq = str_replace('{base_url}', $basePath, $htmlMaincontentFaq);
//
//$maincontent .= $htmlMaincontentFaq;

//$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);


if ($loginFlag) {
	$admin = null;
	$admin .= "<div id=\"admin\">";
	$admin .= "\n";
//	$admin .= "ログインユーザー：";
//	$admin .= htmlspecialchars($loginInfo['usr_family_name'], ENT_QUOTES);
//	$admin .= "&nbsp;";
//	$admin .= htmlspecialchars($loginInfo['usr_first_name'], ENT_QUOTES);
//	$admin .= "\n";
//	$admin .= "&nbsp;|&nbsp;";
//	$admin .= "\n";
	$admin .= "<a href=\"\" title=\"新規登録\" class=\"faq\" _faq_id=\"\">[新規登録]</a>";
	$admin .= "\n";
	$admin .= "&nbsp;|&nbsp;";
	$admin .= "\n";
	$admin .= "<a href=\"\" title=\"カテゴリー登録\" class=\"mst\" _mst_name=\"mst_category\">[カテゴリー登録]</a>";
	$admin .= "\n";
//	$admin .= "&nbsp;|&nbsp;";
//	$admin .= "\n";
//	$admin .= "<a href=\"".$baseUrl."/logout/\" title=\"ログアウト\">[ログアウト]</a>";
//	$admin .= "\n";
	$admin .= "</div>";
	$admin .= "\n";
	$admin .= "<div id=\"form\"></div>";
	$admin .= "\n";
	$admin .= "\n";

	$maincontent .= $admin;
}


$maincontent .= $content;


$maincontent .= "\n";
$maincontent .= "</div><!-- id=\"admin\" -->";
$maincontent .= "\n";


//スクリプトを設定する。
$script = null;


$script .= "<link rel=\"stylesheet\" href=\"".$basePath."/faq/css/import.css\" type=\"text/css\" />";
$script .= "\n";
$script .= "<script language=\"javascript\" src=\"".$basePath."/common/js/faq/faq.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
$script .= "\n";
$script .= "<script language=\"javascript\" src=\"".$basePath."/common/js/mst/mst.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
$script .= "\n";


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



////パンくずリストを設定する。
//_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
//_SetBreadcrumbs(PAGE_DIR_FAQ, '', PAGE_TITLE_FAQ, 2);
////パンくずリストを取得する。
//$breadcrumbs = _GetBreadcrumbs();


//タイトルを設定する。
//$title = $siteTitle;
$title = PAGE_TITLE_FAQ;

//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_FAQ, '', PAGE_TITLE_FAQ, 2);
switch ($mode) {
	case 4:
		//検索ページ
		$title = PAGE_TITLE_SEARCH.' - '.$title;

		_SetBreadcrumbs(PAGE_DIR_SEARCH, '', PAGE_TITLE_SEARCH, 3);
		break;
	case 3:
		//FAQページ(詳細ページ)
		$title = $keyFaqInfo['tbl_faq']['faq_title'].' - '.$keyMstCategoryInfo['name'].' - '.$title;

		$faqTitle = $keyFaqInfoNoSpecial['tbl_faq']['faq_title'];
		$faqTitle = _SubStr($faqTitle, 30);
		$faqTitle = htmlspecialchars($faqTitle, ENT_QUOTES);

		_SetBreadcrumbs(PAGE_DIR_FAQ.$keyMstCategoryInfo['value'].'/', '', $keyMstCategoryInfo['name'], 3);
		_SetBreadcrumbs(PAGE_DIR_FAQ.$keyMstCategoryInfo['value'].'/'.sprintf(FAQ_DIR_FORMAT, $keyFaqInfo['tbl_faq']['faq_id']).'/', '', $faqTitle, 4);
		break;
	case 2:
		//カテゴリーページ
		$title = $keyMstCategoryInfo['name'].' - '.$title;

		_SetBreadcrumbs(PAGE_DIR_FAQ.$keyMstCategoryInfo['value'].'/', '', $keyMstCategoryInfo['name'], 3);
		break;
	case 1:
	default:
		//トップページ
		break;
}


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
$html = str_replace ('{keywords}', PAGE_KEYWORDS_FAQ, $html);
$html = str_replace ('{description}', PAGE_DESCRIPTION_FAQ, $html);
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


_Log("[/faq/index.php] end.");
echo $html;

?>
