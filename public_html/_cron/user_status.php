#!/usr/bin/php
<?php
/*
 * [新★会社設立.JP ツール]
 * ユーザー状況更新
 *
 * 更新履歴：2011/01/11	d.ishikawa	新規作成
 *
 */

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/_cron/user_status.php] start.");

//サイトタイトル
$siteTitle = SITE_TITLE;

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





_Log("[/_cron/user_status.php] システム利用期限(単位:日{null:期間制限無}) = '".SYSTEM_USE_DEADLINE."'");
if (_IsNull(SYSTEM_USE_DEADLINE)) {
	echo "システム利用期限(単位:日{null:期間制限無}) → 期限なし → 処理終了";
	_Log("[/_cron/user_status.php] → 期限なし → 処理終了");
	_Log("[/_cron/user_status.php] end.");
	exit;
}



//DBをオープンする。
$cid = _DB_Open();


_Log("[/_cron/user_status.php] ユーザー_状況テーブルを検索する。");
//ユーザー_状況テーブルを検索し、更新するデータをチェックする。
$condition = array();
//$condition['usr_sts_system_course_id'] = array(MST_SYSTEM_COURSE_ID_CMP, MST_SYSTEM_COURSE_ID_LLC);		//システムコースID="[株式会社] 株式会社設立 (システム利用料金)", "[合同会社] 合同会社設立LLC (システム利用料金)"
$condition['usr_sts_system_course_id'] = array(
MST_SYSTEM_COURSE_ID_CMP				//[株式会社] 株式会社設立 (システム利用料金)
,MST_SYSTEM_COURSE_ID_LLC				//[合同会社] 合同会社設立LLC (システム利用料金)
,MST_SYSTEM_COURSE_ID_STANDARD_CMP		//[スタンダードパートナープラン][株式会社] 株式会社設立 (システム利用料金)
,MST_SYSTEM_COURSE_ID_STANDARD_LLC		//[スタンダードパートナープラン][合同会社] 合同会社設立LLC (システム利用料金)
,MST_SYSTEM_COURSE_ID_PLATINUM_CMP		//[プラチナパートナープラン][株式会社] 株式会社設立 (システム利用料金)
,MST_SYSTEM_COURSE_ID_PLATINUM_LLC		//[プラチナパートナープラン][合同会社] 合同会社設立LLC (システム利用料金)
);										//システムコースID
$condition['usr_sts_pay_status_id'] = array(MST_PAY_STATUS_ID_OK);										//支払状況ID="入金済"
$undeleteOnly = true;																					//削除フラグ="未削除"のみ
$order = null;
$order .= "usr_sts_user_id";																				//ユーザーIDの昇順
$order .= ",usr_sts_no";																					//状況Noの昇順
$tblUserStatusList = _DB_GetList('tbl_user_status', $condition, $undeleteOnly, $order, 'usr_sts_del_flag');
if (_IsNull($tblUserStatusList)) {
	echo "ユーザー_状況テーブル → 更新対象データなし → 処理終了";
	_Log("[/_cron/user_status.php] → 更新対象データなし → 処理終了");
	_Log("[/_cron/user_status.php] end.");
	exit;
}

$undeleteOnly4Mst = true;
$condition = null;
$order = "lpad(show_order,10,'0'),id";

//システムコースマスタ
$mstSystemCourseList = _DB_GetList('mst_system_course', $condition, $undeleteOnly4Mst, $order, 'del_flag', 'id');
//支払状況マスタ
$mstPayStatusList = _DB_GetList('mst_pay_status', $condition, $undeleteOnly4Mst, $order, 'del_flag', 'id');



//IPアドレスを設定する。
$ip = $_SERVER["REMOTE_ADDR"];
$date = null;

//入金日エラーカウンター
$payDateErrorCount = 0;
//更新エラーカウンター
$updateErrorCount = 0;
//期限切れカウンター
$endCount = 0;
//期限OKカウンター
$okCount = 0;

foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	_Log("[/_cron/user_status.php] ----------------------------------------------");
	_Log("[/_cron/user_status.php] ユーザーID = '".$tblUserStatusInfo['usr_sts_user_id']."'");
	_Log("[/_cron/user_status.php] 状況No = '".$tblUserStatusInfo['usr_sts_no']."'");
	_Log("[/_cron/user_status.php] 会社ID = '".$tblUserStatusInfo['usr_sts_company_id']."'");
	_Log("[/_cron/user_status.php] 支払状況ID = '".$tblUserStatusInfo['usr_sts_pay_status_id']."'");
	_Log("[/_cron/user_status.php] システムコースID = '".$tblUserStatusInfo['usr_sts_system_course_id']."'");
	_Log("[/_cron/user_status.php] システムコース名 = '".$tblUserStatusInfo['usr_sts_system_course_name']."'");
	_Log("[/_cron/user_status.php] システムコース価格 = '".$tblUserStatusInfo['usr_sts_system_course_price']."'");
	_Log("[/_cron/user_status.php] 入金日(年) = '".$tblUserStatusInfo['usr_sts_pay_year']."'");
	_Log("[/_cron/user_status.php] 入金日(月) = '".$tblUserStatusInfo['usr_sts_pay_month']."'");
	_Log("[/_cron/user_status.php] 入金日(日) = '".$tblUserStatusInfo['usr_sts_pay_day']."'");

	//本日を取得する。
	$nowTime = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
	$nowYear = date('Y', $nowTime);
	$nowMonth = date('n', $nowTime);
	$nowDay = date('j', $nowTime);
	$nowYmd = sprintf('%04d/%02d/%02d', $nowYear, $nowMonth, $nowDay);

	//入金日の登録あるか？
	if (!_IsNull($tblUserStatusInfo['usr_sts_pay_year']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_month']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_day'])) {
		$payYear = $tblUserStatusInfo['usr_sts_pay_year'];
		$payMonth = $tblUserStatusInfo['usr_sts_pay_month'];
		$payDay = $tblUserStatusInfo['usr_sts_pay_day'];
		$payTime = mktime(0, 0, 0, $payMonth, $payDay, $payYear);
		$payYmd = sprintf('%04d/%02d/%02d', $payYear, $payMonth, $payDay);
		//入金日のNヶ月後を取得する。
		$deadlineTime = mktime(0, 0, 0, $payMonth, $payDay + SYSTEM_USE_DEADLINE, $payYear);
		$deadlineYear = date('Y', $deadlineTime);
		$deadlineMonth = date('n', $deadlineTime);
		$deadlineDay = date('j', $deadlineTime);
		$deadlineYmd = sprintf('%04d/%02d/%02d', $deadlineYear, $deadlineMonth, $deadlineDay);

		_Log("[/_cron/user_status.php] 入金日 = '".$payYmd."'");
		_Log("[/_cron/user_status.php] 期限日 = '".$deadlineYmd."'");
		_Log("[/_cron/user_status.php] 本　日 = '".$nowYmd."'");
		_Log("[/_cron/user_status.php] 入金日(time) = '".$payTime."'");
		_Log("[/_cron/user_status.php] 期限日(time) = '".$deadlineTime."'");
		_Log("[/_cron/user_status.php] 本　日(time) = '".$nowTime."'");

		$tblUserStatusInfo['_deadline_date_'] = $deadlineYmd;
		//上書きする。
		$tblUserStatusList[$tusKey] = $tblUserStatusInfo;

		_Log("[/_cron/user_status.php] 期限チェック");
		if ($nowTime <= $deadlineTime) {
			_Log("[/_cron/user_status.php] → OK");
			$okCount++;
			$tblUserStatusInfo['_ok_flag_'] = true;
			//上書きする。
			$tblUserStatusList[$tusKey] = $tblUserStatusInfo;
		} else {
			_Log("[/_cron/user_status.php] → 【NG】");
			_Log("[/_cron/user_status.php] ユーザー_状況テーブルに更新する。");
			//ユーザー_状況テーブルに更新する。
			$bufInfo = array();
			$bufInfo['usr_sts_user_id'] = $tblUserStatusInfo['usr_sts_user_id'];		//ユーザーID
			$bufInfo['usr_sts_no'] = $tblUserStatusInfo['usr_sts_no'];					//状況No
			$bufInfo['usr_sts_pay_status_id'] = MST_PAY_STATUS_ID_END;					//支払状況ID="期限切れ"
			$bufInfo['usr_sts_end_year'] = $deadlineYear;								//終了日(年)
			$bufInfo['usr_sts_end_month'] = $deadlineMonth;								//終了日(月)
			$bufInfo['usr_sts_end_day'] = $deadlineDay;									//終了日(日)
			$bufInfo['usr_sts_update_ip'] = $ip;										//更新IP
			$bufInfo['usr_sts_update_date'] = null;										//更新日
			$resDb = _DB_SaveInfo('tbl_user_status', $bufInfo);
			if ($resDb === false) {
				_Log("[/_cron/user_status.php] →【失敗】");
				_Log("[/_cron/user_status.php] {ERROR} ユーザー_状況テーブル更新に失敗しました。_DB_SaveInfo('tbl_user_status', xxx) ", 1);
				_Log("[/_cron/user_status.php] {ERROR} ユーザー_状況テーブル情報 = '".print_r($bufInfo,true)."'", 1);
				$updateErrorCount++;
				$tblUserStatusInfo['_update_error_flag_'] = true;
				//上書きする。
				$tblUserStatusList[$tusKey] = $tblUserStatusInfo;
			} else {
				_Log("[/_cron/user_status.php] →【成功】");
				$endCount++;
				//最新情報を取得する。
				$condition = array();
				$condition['usr_sts_user_id'] = $tblUserStatusInfo['usr_sts_user_id'];		//ユーザーID
				$condition['usr_sts_no'] = $tblUserStatusInfo['usr_sts_no'];				//状況No
				$undeleteOnly = false;														//削除フラグ=無視
				$newInfo = _DB_GetInfo('tbl_user_status', $condition, $undeleteOnly, 'usr_sts_del_flag');
				$newInfo['_end_flag_'] = true;
				$newInfo['_deadline_date_'] = $deadlineYmd;
				//上書きする。
				$tblUserStatusList[$tusKey] = $newInfo;
			}
		}
	} else {
		_Log("[/_cron/user_status.php] 入金日が未設定 → 【NG】");
		$payDateErrorCount++;
		$tblUserStatusInfo['_pay_date_error_flag_'] = true;
		//上書きする。
		$tblUserStatusList[$tusKey] = $tblUserStatusInfo;
	}



}

$bodyAll = null;

//更新した事をユーザーにメールする。
foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	if (!isset($tblUserStatusInfo['_end_flag_'])) continue;
	if (!$tblUserStatusInfo['_end_flag_']) continue;
	//ユーザー情報を取得する。
	$userInfo = _GetUserInfo($tblUserStatusInfo['usr_sts_user_id']);
	//株式会社設立情報を取得する。
	$companyInfo = _GetCompanyInfo($tblUserStatusInfo['usr_sts_company_id']);


	//メール本文の共通部分を設定する。
	$body = null;

	$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
	$body .= "ユーザー情報\n";
	$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
//	$body .= "ユーザーID：";
//	$body .= $userInfo['tbl_user']['usr_user_id'];
//	$body .= "\n";
	$body .= "お名前：";
	$body .= $userInfo['tbl_user']['usr_family_name'];
	$body .= " ";
	$body .= $userInfo['tbl_user']['usr_first_name'];
	$body .= "\n";
	$body .= "メールアドレス：";
	$body .= $userInfo['tbl_user']['usr_e_mail'];
	$body .= "\n";
	$body .= "\n";

	$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
	$body .= "会社情報\n";
	$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
	$body .= "商号(会社名)：";
	$body .= $companyInfo['tbl_company']['cmp_company_name'];
	$body .= "\n";
	$body .= "\n";

	$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
	$body .= "ご利用コース情報\n";
	$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
	$body .= "ご利用コース：";
	$body .= $mstSystemCourseList[$tblUserStatusInfo['usr_sts_system_course_id']]['name'];
	$body .= "\n";
	$body .= "ご利用ステータス：";
	$body .= $mstPayStatusList[$tblUserStatusInfo['usr_sts_pay_status_id']]['name'];
	$body .= "\n";
	$body .= "システムご利用開始日：";
	$body .= sprintf('%04d/%02d/%02d', $tblUserStatusInfo['usr_sts_pay_year'], $tblUserStatusInfo['usr_sts_pay_month'], $tblUserStatusInfo['usr_sts_pay_day']);
	$body .= "\n";
	$body .= "システムご利用終了日：";
	$body .= sprintf('%04d/%02d/%02d', $tblUserStatusInfo['usr_sts_end_year'], $tblUserStatusInfo['usr_sts_end_month'], $tblUserStatusInfo['usr_sts_end_day']);
	$body .= "\n";
	$body .= "システムご利用可能期間：";
	$body .= SYSTEM_USE_DEADLINE."日間 (".floor(SYSTEM_USE_DEADLINE / 30)."ヶ月間)";
	$body .= "\n";
	$body .= "\n";
	$body .= "\n";
	$body .= "\n";


	$bodyAll .= "-----------------------------------------";
	$bodyAll .= "\n";
	$bodyAll .= $body;


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

	$body .= "ご利用ステータス更新日時：".date("Y年n月j日 H時i分")."\n";
	$body .= $_SERVER["REMOTE_ADDR"]."\n";


	//管理者用メール本文を設定する。
	$adminBody = "";
	//$adminBody .= $siteTitle." \n";
	//$adminBody .= "\n";
	$adminBody .= "**************************************************************************************\n";
	$adminBody .= "『".$siteTitle."』ご利用ステータス自動更新報告\n";
	$adminBody .= "**************************************************************************************\n";
	$adminBody .= "\n";
	$adminBody .= $body;

	//お客様用メール本文を設定する。
	$customerBody = "";
	$customerBody .= $userInfo['tbl_user']['usr_family_name']." ".$userInfo['tbl_user']['usr_first_name']." 様\n";
	$customerBody .= "\n";
	$customerBody .= "**************************************************************************************\n";
	$customerBody .= "いつも、『".$siteTitle."』のご利用ありがとうございます。\n";
	$customerBody .= "ご利用コースの期限が切れましたので、お知らせいたします。\n";
	$customerBody .= "継続してご利用される場合は、「ご利用料金のお支払い」フォームから\n";
	$customerBody .= "再度お申込み、お支払いをお願いいたします。\n";
	$customerBody .= "**************************************************************************************\n";
	$customerBody .= "\n";
	$customerBody .= $body;


	//管理者用タイトルを設定する。
	$adminTitle = "[".$siteTitle."] ご利用コース期限切れ (".$userInfo['tbl_user']['usr_family_name']." ".$userInfo['tbl_user']['usr_first_name']." 様 / ".$companyInfo['tbl_company']['cmp_company_name'].")";
	//お客様用タイトルを設定する。
	$customerTitle = "[".$siteTitle."] ご利用コース期限切れのお知らせ (".$companyInfo['tbl_company']['cmp_company_name'].")";

	mb_language("Japanese");

	$parameter = "-f ".$clientMail;

	//メール送信
	//お客様に送信する。
	$rcd = mb_send_mail($userInfo['tbl_user']['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

	//クライアントに送信する。
	$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$clientMail);

	//マスターに送信する。
	foreach($masterMailList as $masterMail){
		$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$clientMail);
	}
}




//cronからメール送信する。
$buf = null;
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= __FILE__;
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "入金日エラーカウンター = '".$payDateErrorCount."'";
if ($payDateErrorCount > 0) {
	$buf .= " ";
	$buf .= "【ERROR】";
}
$buf .= "\n";
$buf .= "更新エラーカウンター = '".$updateErrorCount."'";
if ($updateErrorCount > 0) {
	$buf .= " ";
	$buf .= "【ERROR】";
}
$buf .= "\n";
$buf .= "期限切れカウンター = '".$endCount."'";
$buf .= "\n";
$buf .= "期限OKカウンター = '".$okCount."'";
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "\n";
$buf .= "入金日エラー(入金日が未設定)：";
$buf .= "\n";
foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	if (isset($tblUserStatusInfo['_pay_date_error_flag_']) && $tblUserStatusInfo['_pay_date_error_flag_']) {
		$buf .= "-----------------------------------------";
		$buf .= "\n";
		$buf .= "ユーザーID = '".$tblUserStatusInfo['usr_sts_user_id']."'";
		$buf .= "\n";
		$buf .= "状況No = '".$tblUserStatusInfo['usr_sts_no']."'";
		$buf .= "\n";
		$buf .= "会社ID = '".$tblUserStatusInfo['usr_sts_company_id']."'";
		$buf .= "\n";
		$buf .= "システムコースID = '".$tblUserStatusInfo['usr_sts_system_course_id']."'";
		$buf .= "\n";
		$buf .= "支払状況ID = '".$tblUserStatusInfo['usr_sts_pay_status_id']."'";
		$buf .= "\n";
	}
}
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "\n";
$buf .= "更新エラー：";
$buf .= "\n";
foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	if (isset($tblUserStatusInfo['_update_error_flag_']) && $tblUserStatusInfo['_update_error_flag_']) {
		$buf .= "-----------------------------------------";
		$buf .= "\n";
		$buf .= "ユーザーID = '".$tblUserStatusInfo['usr_sts_user_id']."'";
		$buf .= "\n";
		$buf .= "状況No = '".$tblUserStatusInfo['usr_sts_no']."'";
		$buf .= "\n";
		$buf .= "会社ID = '".$tblUserStatusInfo['usr_sts_company_id']."'";
		$buf .= "\n";
		$buf .= "システムコースID = '".$tblUserStatusInfo['usr_sts_system_course_id']."'";
		$buf .= "\n";
		$buf .= "支払状況ID = '".$tblUserStatusInfo['usr_sts_pay_status_id']."'";
		$buf .= "\n";
		$buf .= "期限日 = '".$tblUserStatusInfo['_deadline_date_']."'";
		$buf .= "\n";
	}
}
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "\n";
$buf .= "期限切れ：";
$buf .= "\n";
foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	if (isset($tblUserStatusInfo['_end_flag_']) && $tblUserStatusInfo['_end_flag_']) {
		$buf .= "-----------------------------------------";
		$buf .= "\n";
		$buf .= "ユーザーID = '".$tblUserStatusInfo['usr_sts_user_id']."'";
		$buf .= "\n";
		$buf .= "状況No = '".$tblUserStatusInfo['usr_sts_no']."'";
		$buf .= "\n";
		$buf .= "会社ID = '".$tblUserStatusInfo['usr_sts_company_id']."'";
		$buf .= "\n";
		$buf .= "システムコースID = '".$tblUserStatusInfo['usr_sts_system_course_id']."'";
		$buf .= "\n";
		$buf .= "支払状況ID = '".$tblUserStatusInfo['usr_sts_pay_status_id']."'";
		$buf .= "\n";
		$buf .= "期限日 = '".$tblUserStatusInfo['_deadline_date_']."'";
		$buf .= "\n";
	}
}
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "\n";
$buf .= "期限OK：";
$buf .= "\n";
foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	if (isset($tblUserStatusInfo['_ok_flag_']) && $tblUserStatusInfo['_ok_flag_']) {
		$buf .= "-----------------------------------------";
		$buf .= "\n";
		$buf .= "ユーザーID = '".$tblUserStatusInfo['usr_sts_user_id']."'";
		$buf .= "\n";
		$buf .= "状況No = '".$tblUserStatusInfo['usr_sts_no']."'";
		$buf .= "\n";
		$buf .= "会社ID = '".$tblUserStatusInfo['usr_sts_company_id']."'";
		$buf .= "\n";
		$buf .= "システムコースID = '".$tblUserStatusInfo['usr_sts_system_course_id']."'";
		$buf .= "\n";
		$buf .= "支払状況ID = '".$tblUserStatusInfo['usr_sts_pay_status_id']."'";
		$buf .= "\n";
		$buf .= "期限日 = '".$tblUserStatusInfo['_deadline_date_']."'";
		$buf .= "\n";
	}
}
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "\n";
$buf .= "送信メール本文：";
$buf .= "\n";
$buf .= $bodyAll;
$buf .= "\n";

echo $buf;
_Log("[/_cron/user_status.php] cronメール = \n'".$buf."\n'");


//DBを閉じる
_DB_Close($cid);

_Log("[/_cron/user_status.php] end.");
?>
