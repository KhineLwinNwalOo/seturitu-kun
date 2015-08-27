<?php
/*
 * [新★会社設立.JP ツール]
 * 株式会社設立情報登録ページ
 *
 * 更新履歴：2008/12/01	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

$commonPath = $_SERVER['DOCUMENT_ROOT'] . '/common/';

include_once($commonPath . "include.ini");

_Log("start.");
_Log("\$_POST = '" . print_r($_POST, true) . "'");
_Log("\$_GET = '" . print_r($_GET, true) . "'");
_Log("\$_SERVER = '" . print_r($_SERVER, true) . "'");
_Log("\$_SESSION = '" . print_r($_SESSION, true) . "'");

//認証チェック----------------------------------------------------------------------start
$loginInfo = array();
$loginInfo['usr_user_id'] = NOLOGIN_USER_ID;
$loginInfo['usr_auth_id'] = AUTH_NON;
//認証チェック----------------------------------------------------------------------end

//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("{HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = $commonPath . 'temp_html/temp_base.txt';
_Log("{HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '" . $tempFile . "'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
    _Log("{HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
    //取得できなかった場合
    _Log("{HTMLテンプレートを読み込み} (基本) 【失敗】");
    $html .= "HTMLテンプレートファイルを取得できません。\n";
}

$tempSidebarUserMenuFile = $commonPath . 'temp_html/temp_sidebar_login.txt';
_Log("{HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '" . $tempSidebarUserMenuFile . "'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
    _Log("{HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
    //取得できなかった場合
    _Log("{HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("{HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end

//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_COMPANY_INFO;

//クライアント様メールアドレス
$clientMail = COMPANY_E_MAIL;
//マスター用メールアドレス
$masterMailList = $_COMPANY_MASTER_MAIL_LIST;

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

//パラメーターを取得する。
$xmlName = XML_NAME_CMP;//XMLファイル名を設定する。
$id = null;
$step = null;
$stepId = null;

switch ($_SERVER["REQUEST_METHOD"]) {
    case 'POST':
        //ターゲットID
        $id = (isset($_POST['condition']['_id_']) ? $_POST['condition']['_id_'] : null);
        //ステップID
        $step = (isset($_POST['condition']['_step_']) ? $_POST['condition']['_step_'] : null);

        _Log("{ログインユーザー権限処理} ユーザーID = '" . $loginInfo['usr_user_id'] . "'");
        _Log("{ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "'");

        //権限によって、表示するユーザー情報を制限する。
        switch ($loginInfo['usr_auth_id']) {
            case AUTH_NON://権限無し

                _Log("{ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "' = '権限無し'");
                _Log("{ログインユーザー権限処理} →自分の株式会社設立情報のみ表示する。");
                _Log("{ログインユーザー権限処理} →会社IDを検索する。");

                $id = null;

                //自分の株式会社設立情報のみ表示する。
                //会社IDを検索する。
                $id = _GetRelationCompanyId($loginInfo['usr_user_id']);

                _Log("{ログインユーザー権限処理} →会社ID = '" . $id . "'");
                break;
        }

        //入力値を取得する。
        $info = $_POST;
        _Log("POST = '" . print_r($info, true) . "'");
        //バックスラッシュを取り除く。
        $info = _StripslashesForArray($info);
        _Log("POST(バックスラッシュを取り除く。) = '" . print_r($info, true) . "'");

        //XMLファイル名、ターゲットIDを上書きする。
        $info['condition']['_xml_name_'] = $xmlName;
        $info['condition']['_id_'] = $id;

        break;

    case 'GET':
        //ターゲットID
        $id = (isset($_GET['id']) ? $_GET['id'] : null);
        //ステップID
        $step = (isset($_GET['step']) ? $_GET['step'] : null);

        //遷移元ページ
        $pId = (isset($_GET['p_id']) ? $_GET['p_id'] : null);

        //初期値を設定する。
        $undeleteOnly4def = false;

        _Log("{ログインユーザー権限処理} ユーザーID = '" . $loginInfo['usr_user_id'] . "'");
        _Log("{ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "'");

        //権限によって、表示するユーザー情報を制限する。
        switch ($loginInfo['usr_auth_id']) {
            case AUTH_NON://権限無し

                _Log("{ログインユーザー権限処理} 権限ID = '" . $loginInfo['usr_auth_id'] . "' = '権限無し'");
                _Log("{ログインユーザー権限処理} →自分の株式会社設立情報のみ表示する。");
                _Log("{ログインユーザー権限処理} →会社IDを検索する。");

                $id = null;
                $undeleteOnly4def = true;

                //自分の株式会社設立情報のみ表示する。
                //会社IDを検索する。
                $id = _GetRelationCompanyId($loginInfo['usr_user_id']);

                _Log("{ログインユーザー権限処理} →会社ID = '" . $id . "'");

                break;
        }

        $info['update'] = _GetDefaultInfo($xmlName, $id, $undeleteOnly4def);

        //XMLファイル名、ターゲットIDを初期値に追加する。
        $info['condition']['_xml_name_'] = $xmlName;
        $info['condition']['_id_'] = $id;

        break;
}

_Log("\$_SERVER[\"REQUEST_METHOD\"] = '" . $_SERVER["REQUEST_METHOD"] . "'");
_Log("XMLファイル名 = '" . $xmlName . "'");
_Log("ターゲットID = '" . $id . "'");

//会社タイプID="株式会社"を設定する。
$info['update']['tbl_company']['cmp_company_type_id'] = MST_COMPANY_TYPE_ID_CMP;
//ユーザー情報(ログイン情報)を設定する。→DB更新に使用する。
$info['update']['tbl_user'] = $loginInfo;

//初回だけ、未登録のときだけ設定する。
//取締役任期
if (!isset($info['update']['tbl_company']['cmp_term_year']) || _IsNull($info['update']['tbl_company']['cmp_term_year'])) {
    $info['update']['tbl_company']['cmp_term_year'] = 10;
}
//監査役任期
if (!isset($info['update']['tbl_company']['cmp_inspector_term_year']) || _IsNull($info['update']['tbl_company']['cmp_inspector_term_year'])) {
    $info['update']['tbl_company']['cmp_inspector_term_year'] = 4;
}
//※注意：上記の項目が表示される画面以外でも更新される。今後、他の項目を追加するときは要注意。(「発行可能株式の総数」を追加したとき、更新されてしまっていた。)

switch ($step) {
    case 2:
        //株式会社設立情報[資本金・事業年度]
        $xmlName = XML_NAME_CMP_CAPITAL;
        $stepId = "cmpn_capital";
        break;
    case 3:
        //株式会社設立情報[本店所在地]
        $xmlName = XML_NAME_CMP_ADDRESS;
        $stepId = "cmpn_address";
        break;
    case 4:
        //株式会社設立情報[事業の目的]
        $xmlName = XML_NAME_CMP_PURPOSE;
        $stepId = "cmpn_purpose";
        break;
    case 5:
        //株式会社設立情報[役員構成・任期]
        $xmlName = XML_NAME_CMP_BOARD_BASE;
        $stepId = "cmpn_board_base";
        break;
    case 6:
        //株式会社設立情報[取締役]
        $xmlName = XML_NAME_CMP_BOARD_NAME;
        $stepId = "cmpn_board_name";
        break;
    case 7:
        //株式会社設立情報[発起人]
        $xmlName = XML_NAME_CMP_PROMOTER;
        $stepId = "cmpn_promoter";
        break;
    case 8:
        //株式会社設立情報[出資金]
        //→出資金は、XML形式のフォームではなく。直接書き出す。
        $xmlName = XML_NAME_CMP_PROMOTER_INVESTMENT;
        //$xmlName = null;
        $stepId = "cmpn_promoter_investment";
        break;
    case 9:
        //株式会社設立情報[入力内容確認]
        $xmlName = XML_NAME_CMP_ALL;
        $stepId = "cmpn_confirm";
        break;
    default:
        //株式会社設立情報[商号(会社名)]
        $xmlName = XML_NAME_CMP_NAME;
        $stepId = "cmpn_name";
        $step = 1;
        break;
}
$info['condition']['_step_'] = $step;

_Log("ステップID = '" . $step . "'");
_Log("XMLファイル名(ステップID) = '" . $xmlName . "'");
$_SESSION['free_info_step'] = $step;

// 確認画面で戻るボタンが押された場合→すぐ遷移するので、XMLは読み込まない。
if (!empty($_POST['back']) && $step == 9) {
    $xmlName = null;
}

//初期値を設定する。
if ($xmlName == XML_NAME_CMP_PROMOTER) {
    //株式会社設立情報[発起人]
    //会社_発起人テーブル情報が未設定の場合、会社_役員テーブル情報を初期値として設定する。
    if (!isset($info['update']['tbl_company_promoter'])) {
        if (_IsNull($info['update']['tbl_company_promoter'])) {
            //会社_役員テーブル情報が設定済みの場合
            if (isset($info['update']['tbl_company_board'])) {
                if (!_IsNull($info['update']['tbl_company_board']) && is_array($info['update']['tbl_company_board'])) {
                    $bufList = array();
                    foreach ($info['update']['tbl_company_board'] as $tcbKey => $tblCompanyBoardInfo) {
                        $bufInfo = array();
                        $bufInfo['cmp_prm_family_name'] = $tblCompanyBoardInfo['cmp_bod_family_name'];                    //発起人名前(姓) ← 役員名前(姓)
                        $bufInfo['cmp_prm_first_name'] = $tblCompanyBoardInfo['cmp_bod_first_name'];                    //発起人名前(名) ← 役員名前(名)
                        $bufInfo['cmp_prm_family_name_kana'] = $tblCompanyBoardInfo['cmp_bod_family_name_kana'];        //発起人名前フリガナ(姓) ← 役員名前フリガナ(姓)
                        $bufInfo['cmp_prm_first_name_kana'] = $tblCompanyBoardInfo['cmp_bod_first_name_kana'];            //発起人名前フリガナ(名) ← 役員名前フリガナ(名)
                        $bufInfo['cmp_prm_zip1'] = $tblCompanyBoardInfo['cmp_bod_zip1'];                                //発起人住所(郵便番号1) ← 役員住所(郵便番号1)
                        $bufInfo['cmp_prm_zip2'] = $tblCompanyBoardInfo['cmp_bod_zip2'];                                //発起人住所(郵便番号2) ← 役員住所(郵便番号2)
                        $bufInfo['cmp_prm_pref_id'] = $tblCompanyBoardInfo['cmp_bod_pref_id'];                            //発起人住所(都道府県) ← 役員住所(都道府県)
                        $bufInfo['cmp_prm_address1'] = $tblCompanyBoardInfo['cmp_bod_address1'];                        //発起人住所(市区町村) ← 役員住所(市区町村)
                        $bufInfo['cmp_prm_address2'] = $tblCompanyBoardInfo['cmp_bod_address2'];                        //発起人住所(上記以降) ← 役員住所(上記以降)
                        $bufList[] = $bufInfo;
                    }
                    if (count($bufList) > 1) {
                        $info['update']['tbl_company_promoter'] = $bufList;
                        $message .= "※まだ発起人は登録されていません。\n取締役の情報を仮で表示してあります。\n以下の内容を確認・修正して保存してください。";
                    }
                }
            }
        }
    }
}

//フォーム用にマスタデータを設定する。
//発行可能株式の総数
$mstStockTotalNumList = _GetNumberArray(5000, 30000, 5000);
//登録中の「発行可能株式の総数」の値が上記配列にあるか？無い場合は、追加する。(※過去データ用)
if (isset($info['update']['tbl_company']['cmp_stock_total_num']) && !_IsNull($info['update']['tbl_company']['cmp_stock_total_num'])) {
    if (!isset($mstStockTotalNumList[$info['update']['tbl_company']['cmp_stock_total_num']])) {
        $addList = array(
            'id' => $info['update']['tbl_company']['cmp_stock_total_num']
        , 'name' => $info['update']['tbl_company']['cmp_stock_total_num'] . ' (【仕様変更】5千〜3万株固定 【注意】今の株数から変更すると元に戻せません。)'
        );
        $mstStockTotalNumList[$info['update']['tbl_company']['cmp_stock_total_num']] = $addList;
    }
}

$otherList = array(
    'mst_stock_total_num' => $mstStockTotalNumList
);

$xmlList = null;
if (!_IsNull($xmlName)) {
    //XMLを読み込む。
    $xmlFile = $commonPath . "form_xml/" . $xmlName . ".xml";
    _Log("XMLファイル = '" . $xmlFile . "'");
    $xmlList = _GetXml($xmlFile, $otherList);

    _Log("XMLファイル配列 = '" . print_r($xmlList, true) . "'");

    if ($xmlName == XML_NAME_CMP_ALL) {
        //株式会社設立情報[入力内容確認]

        //全てのXMLを読み込む。

        //株式会社設立情報[商号(会社名)]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_NAME . ".xml";
        _Log("XMLファイル = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_name'] = $bufXmlList['tbl_company'];

        //株式会社設立情報[資本金・事業年度]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_CAPITAL . ".xml";
        _Log("XMLファイル = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile, $otherList);
        $xmlList['tbl_company_capital'] = $bufXmlList['tbl_company'];

        //株式会社設立情報[本店所在地]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_ADDRESS . ".xml";
        _Log("XMLファイル = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_address'] = $bufXmlList['tbl_company'];

        //株式会社設立情報[事業の目的]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_PURPOSE . ".xml";
        _Log("XMLファイル = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_purpose'] = $bufXmlList['tbl_company_purpose'];

        //株式会社設立情報[役員構成・任期]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_BOARD_BASE . ".xml";
        _Log("XMLファイル = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_board_base'] = $bufXmlList['tbl_company'];

        //株式会社設立情報[取締役]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_BOARD_NAME . ".xml";
        _Log("XMLファイル = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_board'] = $bufXmlList['tbl_company_board'];

        //株式会社設立情報[発起人]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_PROMOTER . ".xml";
        _Log("XMLファイル = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_promoter'] = $bufXmlList['tbl_company_promoter'];

        //株式会社設立情報[出資金]
        $bufXmlFile = $commonPath . "form_xml/" . XML_NAME_CMP_PROMOTER_INVESTMENT . ".xml";
        _Log("XMLファイル = '" . $bufXmlFile . "'");
        $bufXmlList = _GetXml($bufXmlFile);
        $xmlList['tbl_company_promoter_investment'] = $bufXmlList['tbl_company_promoter_investment'];

        $info['update']['tbl_company_name'] = $info['update']['tbl_company'];
        $info['update']['tbl_company_capital'] = $info['update']['tbl_company'];
        $info['update']['tbl_company_address'] = $info['update']['tbl_company'];
        $info['update']['tbl_company_board_base'] = $info['update']['tbl_company'];

        _Log("XMLファイル配列(全XMLマージ後) = '" . print_r($xmlList, true) . "'");
        _Log("株式会社設立情報(全XMLマージ後) = '" . print_r($info, true) . "'");

        $mode = 2;
    }
}

//保存ボタン、次へボタンが押された場合
if (!empty($_POST['go']) || !empty($_POST['back']) || !empty($_POST['next'])) {
    //入力値チェック
    $message .= _CheackInputAll($xmlList, $info);

    switch ($xmlName) {
        case XML_NAME_CMP_PURPOSE:
            //株式会社設立情報[事業の目的]
            $message .= _CheackInput4CompanyPurpose($xmlList, $info);
            break;
        case XML_NAME_CMP_BOARD_NAME;
            //株式会社設立情報[取締役]
            $message .= _CheackInput4CompanyBoard($xmlList, $info);
            break;
        case XML_NAME_CMP_PROMOTER:
            //株式会社設立情報[発起人]
            $message .= _CheackInput4CompanyPromoter($xmlList, $info);
     