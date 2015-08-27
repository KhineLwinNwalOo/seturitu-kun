<?php
/*
 * [新★会社設立.JP ツール]
 * [管理者用]
 * ユーザー一覧
 *
 * 更新履歴：2011/10/17	d.ishikawa	新規作成
 *
 */

//キャッシュを有効にする。
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/admin/user/index.php] start.");


_Log("[/admin/user/index.php] \$_POST = '".print_r($_POST,true)."'");
_Log("[/admin/user/index.php] \$_GET = '".print_r($_GET,true)."'");
_Log("[/admin/user/index.php] \$_SERVER = '".print_r($_SERVER,true)."'");
_Log("[/admin/user/index.php] \$_SESSION = '".print_r($_SESSION,true)."'");


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
	_CheckAuth($loginInfo, AUTH_CLIENT, AUTH_WOOROM);
}
//認証チェック----------------------------------------------------------------------end



//HTMLテンプレートを読み込む。------------------------------------------------------- start
_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ start");
$tempFile = '../../common/temp_html/temp_base.txt';
_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} (基本) HTMLテンプレートファイル = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"が存在する場合、表示する。
if ($html !== false && !_IsNull($html)) {
	_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} (基本) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} (基本) 【失敗】");
	$html .= "HTMLテンプレートファイルを取得できません。\n";
}


//$tempSidebarLoginFile = '../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) HTMLテンプレートファイル = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"が存在する場合、表示する。
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【成功】");
//} else {
//	//取得できなかった場合
//	_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} (サイドメニューログイン) 【失敗】");
//}

$tempSidebarUserMenuFile = '../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) HTMLテンプレートファイル = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"が存在する場合、表示する。
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【成功】");
} else {
	//取得できなかった場合
	_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} (サイドメニュー会員メニュー) 【失敗】");
}

_Log("[/admin/user/index.php] {HTMLテンプレートを読み込み} ━━━━━━━━━━━━━━━ end");
//HTMLテンプレートを読み込む。------------------------------------------------------- end


//サイトタイトル
$siteTitle = SITE_TITLE;

//ページタイトル
$pageTitle = PAGE_TITLE_ADMIN_USER;

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







//DBをオープンする。
$cid = _DB_Open();





//システムコースマスタ
$condition4Mst = null;
$undeleteOnly4Mst = false;//管理用だから全部表示する。
$order4Mst = "lpad(show_order,10,'0'),id";
$mstSystemCourseList = _DB_GetList('mst_system_course', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
if (!_IsNull($mstSystemCourseList)) {
	foreach ($mstSystemCourseList as $mKey => $mInfo) {
		$name = null;
		$name .= $mInfo['name'];
		if (!_IsNull($mInfo['price'])) {
			$name .= " ";
			$name .= "￥";
			$name .= number_format($mInfo['price']);
		}
		$mInfo['name_price'] = $name;

		$nameTag = null;
		$nameTag = $name;
		if (!_IsNull($mInfo['comment1'])) {
			$name .= " ";
			$name .= $mInfo['comment1'];

			$nameTag .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$nameTag .= "<span class=\"input_comment\">";
			$nameTag .= $mInfo['comment1'];
			$nameTag .= "</span>";
		}
		$mInfo['name_price_comment'] = $name;
		$mInfo['name_price_comment_tag'] = $nameTag;
		$mstSystemCourseList[$mKey] = $mInfo;
	}
}

//支払状況マスタ
$mstPayStatusList = _GetMasterList('mst_pay_status', $undeleteOnly4Mst);
//プランマスタ
$mstPlanList = _GetMasterList('mst_plan', $undeleteOnly4Mst);
//削除フラグ
$delFlagList = array(
			 DELETE_FLAG_NO => array('id' => DELETE_FLAG_NO, 'name' => '利用中('.DELETE_FLAG_NO_NAME.')', 'name2' => '○')
			,DELETE_FLAG_YES => array('id' => DELETE_FLAG_YES, 'name' => '登録解除('.DELETE_FLAG_YES_NAME.')', 'name2' => '×')
			);


$bufPost = $_POST;
//バックスラッシュを取り除く。
$bufPost = _StripslashesForArray($bufPost);
_Log("[/admin_user_list/index.php] \$_POST(バックスラッシュ除去後) = '".print_r($bufPost,true)."'");

//各変数
$info = null;		//入力値
$resList = null;	//検索結果一覧
$resCount = 0;		//検索件数
$page = 1;			//選択ページ
$orderId = 1;		//ソート条件
$message = null;
$errorFlag = false;	//エラーフラグ


$order = null;
$undeleteOnly = false;


//二重submit対策
if (!isset($_SESSION['token'])) $_SESSION['token'] = uniqid("usr_");



//検索ボタンが押された場合
if (isset($_POST['select'])) {
	$info = $bufPost;												//検索条件
}
//ページリンクが押された場合
else if (isset($_GET['page'])) {
	$info['condition'] = $_SESSION[SID_SRCH_USER_CONDITION];		//検索条件
	$page = $_GET['page'];											//選択ページ
	$orderId = $_SESSION[SID_SRCH_USER_SORT];						//ソート条件
}
//ソートリンクが押された場合
else if (isset($_GET['order'])) {
	$info['condition'] = $_SESSION[SID_SRCH_USER_CONDITION];		//検索条件
	$orderId = $_GET['order'];										//ソート条件
}
//更新ボタンが押された場合
elseif (isset($_POST['go'])) {
	//入力値を取得する。
	$info = $bufPost;

	$info['condition'] = $_SESSION[SID_SRCH_USER_CONDITION];		//検索条件
	$resList = $_SESSION[SID_SRCH_USER_LIST];						//検索結果
	$resCount = $_SESSION[SID_SRCH_USER_COUNT];						//検索件数
	$page = $_SESSION[SID_SRCH_USER_PAGE];							//選択ページ
	$orderId = $_SESSION[SID_SRCH_USER_SORT];						//ソート条件

	_Log("[/admin/user/index.php] 二重submit対策 SESSION値 = '".$_SESSION['token']."'");
	_Log("[/admin/user/index.php] 二重submit対策    POST値 = '".$info['token']."'");

	//二重submit対策をする。
	if ($_SESSION['token'] == $info['token']) {
		if (isset($info['update'])) {
			_Log("[/admin/user/index.php] {更新} -------------------- 開始");
			
			//入力値チェック
//			$message .= "エラーメッセージ";
			foreach ($info['update'] as $key => $newInfo) {
				//チェック...
				
				//2011/10/18現在、チェック無し。
				
				//検索結果に入力値を上書きする。→エラー時の再表示のため。※
				foreach ($newInfo as $name => $value) {
					$resList[$key][$name] = $value;
				}
			}
	
			if (_IsNull($message)) {
				//セッションから検索結果を再取得する。→上記※で、上書きしているため。
				$resList = $_SESSION[SID_SRCH_USER_LIST];
	
				$count = 0;
				foreach ($info['update'] as $key => $newInfo) {
					$count++;
		
					//変更有無をチェックする。
					$updateFlag = false;							//全項目の更新有無フラグ
					$updateFlagTblUser = false;						//ユーザーテーブル
					$updateFlagTblUserStatus = false;				//ユーザー_状況テーブル
					$messageTblUser = null;						//ユーザーテーブル
					
					//削除フラグ
					if (!isset($newInfo['usr_del_flag']) || _IsNull($newInfo['usr_del_flag'])) $newInfo['usr_del_flag'] = DELETE_FLAG_NO;
					if ($newInfo['usr_del_flag'] != $resList[$key]['usr_del_flag']) {
						$updateFlag = true;
						$updateFlagTblUser = true;
						$messageTblUser .= "「登録解除」";
					}
					//プランID
					if ($newInfo['usr_plan_id'] != $resList[$key]['usr_plan_id']) {
						$updateFlag = true;
						$updateFlagTblUser = true;
						$messageTblUser .= "「プラン」";
					}
					//支払状況ID
					if (isset($newInfo['usr_sts_pay_status_id'])) {
						foreach ($newInfo['usr_sts_pay_status_id'] as $usNo => $newPayStatusId) {
							if ($newPayStatusId != $resList[$key]['usr_sts_pay_status_id'][$usNo]) {
								$updateFlag = true;
								$updateFlagTblUserStatus = true;
								break;
							}
						}
					}

					//更新後に最新のDB情報で検索結果を上書きする。{true:上書きする/false:しない}
					$overwriteFlag = false;
					
					if ($updateFlag) {

						//更新処理...
						$overwriteFlag = true;

						$usrUserIdShow = "ID.".sprintf('%03d', $newInfo['usr_user_id']);
						$ip = $_SERVER["REMOTE_ADDR"];
						
						//ユーザーテーブル
						if ($updateFlagTblUser) {
							$bufInfo = array();
							$bufInfo['tbl_user']['usr_user_id'] = $newInfo['usr_user_id'];				//ユーザーID
							$bufInfo['tbl_user']['usr_auth_id'] = $resList[$key]['usr_auth_id'];		//権限ID→変更しないために設定する。
							$bufInfo['tbl_user']['usr_del_flag'] = $newInfo['usr_del_flag'];			//削除フラグ
							$bufInfo['tbl_user']['usr_plan_id'] = $newInfo['usr_plan_id'];				//プランID
							$dbRes = _SaveUserInfo($bufInfo);
							if ($dbRes === false) {
								$message .= $usrUserIdShow." ".$messageTblUser."の変更に失敗しました。\n";
								$errorFlag = true;
							} else {
								$message .= $usrUserIdShow." ".$messageTblUser."を変更しました。\n";
							}
						}
						//ユーザー_状況テーブル
						if ($updateFlagTblUserStatus) {
							if (isset($newInfo['usr_sts_pay_status_id'])) {
								
								$bodyUserCompany = null;
								$bodyUserStatus = null;
								$sameCompanyList = null;
								$payStatusOKFlag = false;//入金済フラグ
								
								foreach ($newInfo['usr_sts_pay_status_id'] as $usNo => $newPayStatusId) {
									if ($newPayStatusId != $resList[$key]['usr_sts_pay_status_id'][$usNo]) {
										$bufName = null;
										$bufName .= $resList[$key]['tbl_user_status'][$usNo]['usr_sts_create_date_yymmdd_2'];
										$bufName .= " - ";
										$bufName .= $resList[$key]['tbl_user_status'][$usNo]['usr_sts_system_course_name'];
										$bufName .= " - ";
										$bufName .= $mstPayStatusList[$newPayStatusId]['name'];
										
										$bufInfo = array();

										//支払状況IDによって、日付を更新する。
										switch ($newPayStatusId) {
										case MST_PAY_STATUS_ID_NON:
											//未入金
											$bufInfo['usr_sts_pay_year'] = null;									//入金日(年)
											$bufInfo['usr_sts_pay_month'] = null;									//入金日(月)
											$bufInfo['usr_sts_pay_day'] = null;										//入金日(日)
											$bufInfo['usr_sts_end_year'] = null;									//終了日(年)
											$bufInfo['usr_sts_end_month'] = null;									//終了日(月)
											$bufInfo['usr_sts_end_day'] = null;										//終了日(日)
											break;
										case MST_PAY_STATUS_ID_OK:
											//入金済
											$bufInfo['usr_sts_pay_year'] = date('Y');								//入金日(年)
											$bufInfo['usr_sts_pay_month'] = date('n');								//入金日(月)
											$bufInfo['usr_sts_pay_day'] = date('j');								//入金日(日)
											$bufInfo['usr_sts_end_year'] = null;									//終了日(年)
											$bufInfo['usr_sts_end_month'] = null;									//終了日(月)
											$bufInfo['usr_sts_end_day'] = null;										//終了日(日)
											$payStatusOKFlag = true;
											break;
										case MST_PAY_STATUS_ID_CANCEL:
											//キャンセル
											$bufInfo['usr_sts_pay_year'] = null;									//入金日(年)
											$bufInfo['usr_sts_pay_month'] = null;									//入金日(月)
											$bufInfo['usr_sts_pay_day'] = null;										//入金日(日)
											$bufInfo['usr_sts_end_year'] = null;									//終了日(年)
											$bufInfo['usr_sts_end_month'] = null;									//終了日(月)
											$bufInfo['usr_sts_end_day'] = null;										//終了日(日)
											break;
										case MST_PAY_STATUS_ID_END:
											//期限切れ
											$bufInfo['usr_sts_end_year'] = date('Y');								//終了日(年)
											$bufInfo['usr_sts_end_month'] = date('n');								//終了日(月)
											$bufInfo['usr_sts_end_day'] = date('j');								//終了日(日)
											break;
										}

										$bufInfo['usr_sts_user_id'] = $newInfo['usr_user_id'];						//ユーザーID
										$bufInfo['usr_sts_no'] = $usNo;												//状況No
										$bufInfo['usr_sts_pay_status_id'] = $newPayStatusId;						//支払状況ID
										$bufInfo['usr_sts_update_ip'] = $ip;										//更新IP
										$bufInfo['usr_sts_update_date'] = null;										//更新日
										$resDb = _DB_SaveInfo('tbl_user_status', $bufInfo);
										if ($resDb === false) {
											$message .= $usrUserIdShow." 「".$bufName."」の変更に失敗しました。\n";
											$errorFlag = true;
										} else {
											$message .= $usrUserIdShow." 「".$bufName."」を変更しました。";
											if (isset($newInfo['send'])) {
												$message .= "(メール送信済)";
												
//												if (!isset($sameCompanyList[$resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']])) {
//													//株式会社設立情報を取得する。
//													$companyInfo = _GetCompanyInfo($resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']);
//													
//													if (!_IsNull($bodyUserCompany)) $bodyUserCompany .= "-----------------------------------------\n";
//													$bodyUserCompany .= "商号(会社名)：";
//													$bodyUserCompany .= $companyInfo['tbl_company']['cmp_company_name'];
//													$bodyUserCompany .= "\n";
//													$bodyUserCompany .= "\n";
//													
//													$sameCompanyList[$resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']] = true;
//												}

												$companyInfo = null;
												if (isset($sameCompanyList[$resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']])) {
													$companyInfo = $sameCompanyList[$resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']];
												} else {
													//株式会社設立情報を取得する。
													$companyInfo = _GetCompanyInfo($resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']);
													$sameCompanyList[$resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']] = $companyInfo;
												}
												$companyName = "<未設定>";
												if (!_IsNull($companyInfo)) {
													if (!_IsNull($companyInfo['tbl_company']['cmp_company_name'])) {
														$companyName = $companyInfo['tbl_company']['cmp_company_name'];
													}
												}
												
												if (!_IsNull($bodyUserStatus)) $bodyUserStatus .= "-----------------------------------------\n";
												$bodyUserStatus .= "商号(会社名)：";
												$bodyUserStatus .= $companyName;
												$bodyUserStatus .= "\n";
												$bodyUserStatus .= "ご利用コース：";
												$bodyUserStatus .= $resList[$key]['tbl_user_status'][$usNo]['usr_sts_system_course_name'];
												$bodyUserStatus .= "\n";
												$bodyUserStatus .= "ご利用ステータス：";
												$bodyUserStatus .= $mstPayStatusList[$newPayStatusId]['name'];
												$bodyUserStatus .= "\n";
												$bodyUserStatus .= "\n";
											}
											$message .= "\n";
										}
									}
								}
								
								
								if (isset($newInfo['send'])) {
									if (!_IsNull($bodyUserStatus)) {
										//メール本文の共通部分を設定する。
										$body = null;
									
										$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
										$body .= "ユーザー情報\n";
										$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
										$body .= "お名前：";
										$body .= $resList[$key]['usr_family_name'];
										$body .= " ";
										$body .= $resList[$key]['usr_first_name'];
										$body .= " 様";
										$body .= "\n";
										$body .= "メールアドレス：";
										$body .= $resList[$key]['usr_e_mail'];
										$body .= "\n";
										$body .= "\n";
									
//										$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
//										$body .= "会社情報\n";
//										$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
//										$body .= $bodyUserCompany;
									
										$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
										$body .= "ご利用コース情報\n";
										$body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
										$body .= $bodyUserStatus;
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
										$adminBody .= "『".$siteTitle."』ご利用ステータス更新報告\n";
										$adminBody .= "**************************************************************************************\n";
										$adminBody .= "\n";
										$adminBody .= $body;
									
										//お客様用メール本文を設定する。
										$customerBody = "";
										$customerBody .= $resList[$key]['usr_family_name']." ".$resList[$key]['usr_first_name']." 様\n";
										$customerBody .= "\n";
										$customerBody .= "**************************************************************************************\n";
										$customerBody .= "いつも、『".$siteTitle."』のご利用ありがとうございます。\n";
										if ($payStatusOKFlag) {
											$customerBody .= "費用のご入金ありがとうございました。\n";
										}
										$customerBody .= "ご利用ステータスが更新されましたので、お知らせいたします。\n";
										$customerBody .= "ご確認お願いいたします。\n";
										$customerBody .= "**************************************************************************************\n";
										$customerBody .= "\n";
										$customerBody .= $body;
									
									
										//管理者用タイトルを設定する。
										$adminTitle = "[".$siteTitle."] ご利用ステータス更新 (".$resList[$key]['usr_family_name']." ".$resList[$key]['usr_first_name']." 様)";
										//お客様用タイトルを設定する。
										$customerTitle = "[".$siteTitle."] ご利用ステータス更新のお知らせ";
									
										mb_language("Japanese");
										
										$parameter = "-f ".$clientMail;
									
										//メール送信
										//お客様に送信する。
										$rcd = mb_send_mail($resList[$key]['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);
										if ($rcd === false) {
											$message .= $usrUserIdShow." 「メール送信」に失敗しました。再度、送信してください。(※支払状況が変更成功の場合は、一旦元に戻してから再度変更してください。)\n";
											$errorFlag = true;
										}
									
										//クライアントに送信する。
										$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$clientMail, $parameter);
									
										//マスターに送信する。
										foreach($masterMailList as $masterMail){
											$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$clientMail, $parameter);
										}
									}
								}
							}
						}
					}
		
					//更新があった場合、最新のDB情報で検索結果を上書きする。
					if ($overwriteFlag) {
						_Log("[/admin/user/index.php] {更新} 3.更新があった場合、最新のDB情報で検索結果を上書きする。");
						
						//ユーザー情報を検索する。
						$condition4new = array();
						$condition4new['usr_user_id'] = $newInfo['usr_user_id'];//ユーザーID
						$newUserList = _GetUser($condition4new, $order, false);	
						
						if (_IsNull($newUserList)) {
						} else {
							$resList[$key] = $newUserList[0];
						}
						
						//上書きした検索結果をセッションに保存する。
						$_SESSION[SID_SRCH_USER_LIST] = $resList;
					}
				}
		
				//セッションから検索結果を再取得する。
				$resList = $_SESSION[SID_SRCH_USER_LIST];
		
				//ここまででメッセージが空の場合、登録、更新、削除がなかった。
				if (_IsNull($message)) {
					$message = "変更箇所がありません。";
				} else {
					//エラー無しの場合、二重submit対策のユニークキーを更新する。
					$_SESSION['token'] = uniqid("usr_");
				}
			} else {
				//エラーが有り場合
				$message = "※入力に誤りがあります。\n".$message;
				$errorFlag = true;
			}

			_Log("[/admin/user/index.php] {更新} -------------------- 終了");
		}
	} else {
		$message = "※二重更新です。更新をする場合は、「更新」ボタンを押してください。";
		$errorFlag = true;
	}
}



if (_IsNull($message)) {
	//検索条件を追加する。
	$info['condition']['order_id'] = $orderId;							//ソート条件
	
	//検索する。
	$resList = _GetUser($info['condition'], $order, $undeleteOnly, false, $page, USER_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE, 2);
	$resCount = _GetUser($info['condition'], $order, $undeleteOnly, true);
	if (_IsNull($resList)) {
		$message .= "検索条件に該当するユーザー情報は存在しません。\n";
	}

	//セッションに保存する。
	$_SESSION[SID_SRCH_USER_CONDITION] = $info['condition'];	//検索条件
	$_SESSION[SID_SRCH_USER_LIST] = $resList;					//検索結果一覧
	$_SESSION[SID_SRCH_USER_COUNT] = $resCount;					//検索件数
	$_SESSION[SID_SRCH_USER_PAGE] = $page;						//選択ページ
	$_SESSION[SID_SRCH_USER_SORT] = $orderId;					//ソート条件
}



$htmlPage = _GetPageLink($resCount, $page, USER_PAGE_LINK_TOP_MESSAGE, USER_PAGE_LINK_ACTIVE_PAGE_MESSAGE, USER_PAGE_LINK_COUNT_MESSAGE, null, USER_PAGE_LINK_LIMIT, USER_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE, USER_PAGE_LINK_FRONT_TEXT, USER_PAGE_LINK_REAR_TEXT);
$htmlPage = "<div class=\"page\">".$htmlPage."</div>";
$htmlPage .= "\n";



















//文字をHTMLエンティティに変換する。
$info = _HtmlSpecialCharsForArray($info);
$htmlResList = _HtmlSpecialCharsForArray($resList);
_Log("[/admin/user/index.php] POST(文字をHTMLエンティティに変換する。) = '".print_r($info,true)."'");


//タイトルを設定する。
$title = $pageTitle;

//基本URLを設定する。
$basePath = "../..";

//コンテンツを設定する。
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../img/maincontent/pt_buy.jpg\" title=\"\" alt=\"ご利用料金のお支払い\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

////サブメニューを設定する。
//$maincontent .= "<ul id=\"sealn\">";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_set\">";
//$maincontent .= "<a href=\"?step=1".$addHref."\">印鑑選択</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_imprint\">";
//$maincontent .= "<a href=\"?step=2".$addHref."\">印影選択</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_name\">";
//$maincontent .= "<a href=\"?step=3".$addHref."\">会社名・お届け先</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_confirm\">";
//$maincontent .= "<a href=\"?step=4".$addHref."\">入力内容確認</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "</ul>";
//$maincontent .= "\n";


//$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//検索条件
$mcSelect = null;
$mcSelect .= "<form id=\"frmSelect\" name=\"frmSelect\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" enctype=\"multipart/form-data\">";
$mcSelect .= "\n";
//$mcSelect .= "<div class=\"formWrapper\">";
//$mcSelect .= "\n";
//$mcSelect .= "<div class=\"formList\">";
//$mcSelect .= "\n";
$mcSelect .= "<div>";
$mcSelect .= "\n";
$mcSelect .= "<h3>検索条件</h3>";
$mcSelect .= "\n";
$mcSelect .= "<table class=\"searchConditionTable\">";
$mcSelect .= "\n";
$mcSelect .= "<colgroup class=\"colgroupHead_\"></colgroup>";
$mcSelect .= "\n";
$mcSelect .= "<colgroup class=\"colgroupBody_\"></colgroup>";
$mcSelect .= "\n";
$mcSelect .= "<colgroup class=\"colgroupHead_\"></colgroup>";
$mcSelect .= "\n";
$mcSelect .= "<colgroup class=\"colgroupBody_\"></colgroup>";
$mcSelect .= "\n";
$mcSelect .= "<tbody>";
$mcSelect .= "\n";
$mcSelect .= "<tr>";
$mcSelect .= "\n";
$mcSelect .= "<td class=\"colHead\">名前</td>";
$mcSelect .= "\n";
$mcSelect .= "<td>";
$mcSelect .= "姓<input type=\"text\" name=\"condition[usr_family_name]\" size=\"10\" maxlength=\"100\" value=\"".$info['condition']['usr_family_name']."\" />";
$mcSelect .= "名<input type=\"text\" name=\"condition[usr_first_name]\" size=\"10\" maxlength=\"100\" value=\"".$info['condition']['usr_first_name']."\" />";
$mcSelect .= "</td>";
$mcSelect .= "\n";
$mcSelect .= "<td class=\"colHead\">メールアドレス</td>";
$mcSelect .= "\n";
$mcSelect .= "<td>";
$mcSelect .= "<input type=\"text\" name=\"condition[usr_e_mail]\" size=\"20\" maxlength=\"200\" value=\"".$info['condition']['usr_e_mail']."\" />";
$mcSelect .= "</td>";
$mcSelect .= "\n";
$mcSelect .= "</tr>";
$mcSelect .= "\n";
$mcSelect .= "<tr>";
$mcSelect .= "\n";
$mcSelect .= "<td class=\"colHead\">支払状況</td>";
$mcSelect .= "\n";
$mcSelect .= "<td>";
$mcSelect .= "\n";
$bufTabindex = null;
$mcSelect .= _GetCheckbox($mstPayStatusList, 'condition[usr_sts_pay_status_id]', $info['condition']['usr_sts_pay_status_id'], $bufTabindex, '', 'id', 'name_del_2');
$mcSelect .= "\n";
$mcSelect .= "</td>";
$mcSelect .= "\n";
$mcSelect .= "<td class=\"colHead\">利用状況</td>";
$mcSelect .= "\n";
$mcSelect .= "<td>";
$mcSelect .= "\n";
$bufTabindex = null;
$mcSelect .= _GetCheckbox($delFlagList, 'condition[usr_del_flag]', $info['condition']['usr_del_flag'], $bufTabindex, '', 'id', 'name');
$mcSelect .= "\n";
$mcSelect .= "</td>";
$mcSelect .= "\n";
$mcSelect .= "</tr>";
$mcSelect .= "\n";
$mcSelect .= "<tr>";
$mcSelect .= "\n";
$mcSelect .= "<td class=\"colHead\">プラン</td>";
$mcSelect .= "\n";
$mcSelect .= "<td colspan=\"3\">";
$mcSelect .= "\n";
$bufTabindex = null;
$mcSelect .= _GetCheckbox($mstPlanList, 'condition[usr_plan_id]', $info['condition']['usr_plan_id'], $bufTabindex, '', 'id', 'name');
$mcSelect .= "\n";
$mcSelect .= "</td>";
$mcSelect .= "\n";
$mcSelect .= "</tr>";
$mcSelect .= "\n";
$mcSelect .= "</tbody>";
$mcSelect .= "\n";
$mcSelect .= "</table>";
$mcSelect .= "\n";
$mcSelect .= "</div>";
$mcSelect .= "\n";
$mcSelect .= "<div class=\"button\">";
$mcSelect .= "<input class=\"submit\" type=\"submit\" name=\"select\" value=\" 検　索 \" />";
$mcSelect .= "</div>";
$mcSelect .= "\n";
//$mcSelect .= "</div>";
//$mcSelect .= "\n";
//$mcSelect .= "</div>";
//$mcSelect .= "\n";
$mcSelect .= "</form>";
$mcSelect .= "\n";

//検索一覧
$mcList = null;
if (!_IsNull($resList)) {

	$mcList .= "<form id=\"frmUpdate\" name=\"frmUpdate\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" enctype=\"multipart/form-data\">";
	$mcList .= "\n";
//	$mcList .= "<div class=\"formWrapper\">";
//	$mcList .= "\n";
//	$mcList .= "<div class=\"formList\">";
//	$mcList .= "\n";
	$mcList .= "<div>";
	$mcList .= "\n";
	$mcList .= "<h3>検索結果</h3>";
	$mcList .= "\n";
	$mcList .= $htmlPage;
	$mcList .= "\n";
	$mcList .= "<table class=\"searchResultListTable\">";
	$mcList .= "\n";

	$mcList .= "<thead>";
	$mcList .= "\n";
	$mcList .= "<tr>";
	$mcList .= "\n";
	$mcList .= "<td class=\"colHead\">ID/<br />利用<br />状況</td>";
	$mcList .= "\n";
	$mcList .= "<td class=\"colHead\">プラン/名前/<br />メール/電話</td>";
	$mcList .= "\n";
	$mcList .= "<td class=\"colHead\">支払状況</td>";
	$mcList .= "\n";
	$mcList .= "</tr>";
	$mcList .= "\n";
	$mcList .= "</thead>";
	$mcList .= "\n";

	$mcList .= "<tbody>";
	$mcList .= "\n";

	foreach ($resList as $resKey => $resInfo) {
		
		$htmlResInfo = $htmlResList[$resKey];

		//ユーザーID
		$usrUserId = $resInfo['usr_user_id'];
		$usrUserIdShow = sprintf('%03d', $usrUserId);

		$usrUserIdHidden = null;
		$usrUserIdHidden .= "<input type=\"hidden\" name=\"update[".$usrUserId."][usr_user_id]\" value=\"".$usrUserId."\" />";


		//E-Mail
		$usrEMail = '-';
		if (!_IsNull($resInfo['usr_e_mail'])) {
			$usrEMail = $resInfo['usr_e_mail'];
			$usrEMail = _SubStr($usrEMail, 10, '...', 'UTF-8');
			$usrEMail = htmlspecialchars($usrEMail, ENT_QUOTES);
		}

		//E-Mail(表示用)
		$usrEMailShow = '-';
		if (!_IsNull($resInfo['usr_e_mail'])) {
			$usrEMailShow = $resInfo['usr_e_mail'];
			$usrEMailShow = htmlspecialchars($usrEMailShow, ENT_QUOTES);
		}
		//パスワード(表示用)
		$usrPassShow = '-';
		if (!_IsNull($resInfo['usr_pass'])) {
			$usrPassShow = $resInfo['usr_pass'];
			$usrPassShow = htmlspecialchars($usrPassShow, ENT_QUOTES);
		}

		//名前(姓)
		//名前(名)
		$usrFFName = '-';
		if (!_IsNull($resInfo['usr_family_name']) || !_IsNull($resInfo['usr_first_name'])) {
			$usrFFName = $resInfo['usr_family_name']." ".$resInfo['usr_first_name'];
			$usrFFName = _SubStr($usrFFName, 8, '...', 'UTF-8');
			$usrFFName = htmlspecialchars($usrFFName, ENT_QUOTES);
		}
		//電話番号
		$usrTel = '-';
		if (!_IsNull($resInfo['usr_tel1']) || !_IsNull($resInfo['usr_tel2']) || !_IsNull($resInfo['usr_tel3'])) {
			$usrTel = $resInfo['usr_tel1']."-".$resInfo['usr_tel2']."-".$resInfo['usr_tel3'];
			$usrTel = htmlspecialchars($usrTel, ENT_QUOTES);
		}
		//削除フラグ
		$checked = null;
		if ($resInfo['usr_del_flag'] == DELETE_FLAG_YES) {
			$checked = "checked=\"checked\"";
		}
		$delId = "del_".$usrUserId;
		$usrDelFlag = null;
		$usrDelFlag .= "<input type=\"checkbox\" name=\"update[".$usrUserId."][usr_del_flag]\" id=\"".$delId."\" value=\"".DELETE_FLAG_YES."\" ".$checked." />";
		$usrDelFlag .= "<br />";
		$usrDelFlag .= "<label for=\"".$delId."\">登録<br />解除</label>";

		//プランID
		$usrPlanId = _GetSelect($mstPlanList, 'update['.$usrUserId.'][usr_plan_id]', $resInfo['usr_plan_id'], '', false, '&nbsp;', 1, false, 'id', 'name_mini');

		//ユーザー_会社_関連付テーブルを検索する。
		$undeleteOnly4All = false;
		$condition4All = array();
		$condition4All['usr_cmp_rel_user_id'] = $usrUserId;			//ユーザーID
		$order4All = "usr_cmp_rel_company_id";						//ソート順=会社IDの昇順(なんでもいいけど…)
		$tblUserCompanyRelationList = _DB_GetListByAssociative('tbl_user_company_relation', 'usr_cmp_rel_company_id', null, $condition4All, $undeleteOnly4All, $order4All, 'usr_cmp_rel_del_flag');
		$tblCompanyList = null;
		if (!_IsNull($tblUserCompanyRelationList)) {
			//会社テーブルを検索する。
			$order4All = "cmp_company_type_id";									//ソート順=会社タイプIDの昇順
			$order4All .= ",cmp_company_id desc";								//ソート順=会社IDの降順
			$condition4All = array();
			$condition4All['cmp_company_id'] = $tblUserCompanyRelationList;		//会社ID
			$tblCompanyList = _DB_GetList('tbl_company', $condition4All, $undeleteOnly4All, $order4All, 'cmp_del_flag', 'cmp_company_id');
		}
		//文字をHTMLエンティティに変換する。
		$htmlTblCompanyList = _HtmlSpecialCharsForArray($tblCompanyList);

		
		//ユーザー_状況テーブル
		$condition4Sts = array();
		$condition4Sts['usr_sts_user_id'] = $usrUserId;						//ユーザーID
		$undeleteOnly4Sts = true;
		$order4Sts = null;
		$order4Sts .= "usr_sts_create_date";
		$order4Sts .= ",usr_sts_no";
		$tblUserStatusList = _DB_GetList('tbl_user_status', $condition4Sts, $undeleteOnly4Sts, $order4Sts, 'usr_sts_del_flag');
		
		//セッションに追加する。→一旦クリアする。
		unset($_SESSION[SID_SRCH_USER_LIST][$resKey]['usr_sts_pay_status_id']);
		
		$userStatus = null;
		if (!_IsNull($tblUserStatusList)) {
			
			//文字をHTMLエンティティに変換する。
			$htmlTblUserStatusList = _HtmlSpecialCharsForArray($tblUserStatusList);
			
			$userStatus .= "<table class=\"searchResultListTableSub\">";
			$userStatus .= "\n";
			$userStatus .= "<thead>";
			$userStatus .= "\n";
			$userStatus .= "<tr>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">申込日</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">入金日</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">終了(予)</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">会社名</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">システムコース</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">価格</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">支払状況</td>";
			$userStatus .= "\n";
			$userStatus .= "</tr>";
			$userStatus .= "\n";
			$userStatus .= "</thead>";
			$userStatus .= "\n";
			$userStatus .= "<tbody>";
			$userStatus .= "\n";
			foreach ($htmlTblUserStatusList as $tusKey => $tblUserStatusInfo) {

				//セッションに追加する。→変更有無チェックに使用する。
				$_SESSION[SID_SRCH_USER_LIST][$resKey]['usr_sts_pay_status_id'][$tblUserStatusInfo['usr_sts_no']] = $tblUserStatusInfo['usr_sts_pay_status_id'];
				//セッションに追加する。→エラーメッセージなどに使用する。
				$_SESSION[SID_SRCH_USER_LIST][$resKey]['tbl_user_status'][$tblUserStatusInfo['usr_sts_no']] = $tblUserStatusList[$tusKey];

				//システムコース名
				$usrStsSystemCourseName = $tblUserStatusList[$tusKey]['usr_sts_system_course_name'];
				$usrStsSystemCourseName = _SubStr($usrStsSystemCourseName, 20);
				$usrStsSystemCourseName = htmlspecialchars($usrStsSystemCourseName);

				$payYmd = null;
				$deadlineYmd = null;

				//入金日の登録あるか？
				if (!_IsNull($tblUserStatusInfo['usr_sts_pay_year']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_month']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_day'])) {
					$payYear = $tblUserStatusInfo['usr_sts_pay_year'];
					$payMonth = $tblUserStatusInfo['usr_sts_pay_month'];
					$payDay = $tblUserStatusInfo['usr_sts_pay_day'];
					$payTime = mktime(0, 0, 0, $payMonth, $payDay, $payYear);
					$payYmd = sprintf('%04d/%02d/%02d', $payYear, $payMonth, $payDay);
					$payYmd = mb_substr($payYmd, 2, 8);
				}

				switch ($tblUserStatusInfo['usr_sts_system_course_id']) {
					case MST_SYSTEM_COURSE_ID_CMP://[株式会社] 株式会社設立 (システム利用料金)
					case MST_SYSTEM_COURSE_ID_LLC://[合同会社] 合同会社設立LLC (システム利用料金)
		
					case MST_SYSTEM_COURSE_ID_STANDARD_CMP://[スタンダードパートナープラン][株式会社] 株式会社設立 (システム利用料金)
					case MST_SYSTEM_COURSE_ID_STANDARD_LLC://[スタンダードパートナープラン][合同会社] 合同会社設立LLC (システム利用料金)
		
					case MST_SYSTEM_COURSE_ID_PLATINUM_CMP://[プラチナパートナープラン][株式会社] 株式会社設立 (システム利用料金)
					case MST_SYSTEM_COURSE_ID_PLATINUM_LLC://[プラチナパートナープラン][合同会社] 合同会社設立LLC (システム利用料金)

						//入金日の登録あるか？
						if (!_IsNull($tblUserStatusInfo['usr_sts_pay_year']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_month']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_day'])) {
							if (!_IsNull(SYSTEM_USE_DEADLINE)) {
								//入金日のNヶ月後を取得する。
								$deadlineTime = mktime(0, 0, 0, $payMonth, $payDay + SYSTEM_USE_DEADLINE, $payYear);
								$deadlineYear = date('Y', $deadlineTime);
								$deadlineMonth = date('n', $deadlineTime);
								$deadlineDay = date('j', $deadlineTime);
								$deadlineYmd = sprintf('%04d/%02d/%02d', $deadlineYear, $deadlineMonth, $deadlineDay);
								$deadlineYmd = mb_substr($deadlineYmd, 2, 8);
								$deadlineYmd = "(".$deadlineYmd.")";
							}
						}
						break;
				}

				//終了日の登録あるか？ある場合は、こちらを優先して表示する。
				if (!_IsNull($tblUserStatusInfo['usr_sts_end_year']) && !_IsNull($tblUserStatusInfo['usr_sts_end_month']) && !_IsNull($tblUserStatusInfo['usr_sts_end_day'])) {
					$deadlineYmd = sprintf('%04d/%02d/%02d', $tblUserStatusInfo['usr_sts_end_year'], $tblUserStatusInfo['usr_sts_end_month'], $tblUserStatusInfo['usr_sts_end_day']);
					$deadlineYmd = mb_substr($deadlineYmd, 2, 8);
				}

				_Log("[_GetUserStatusHtml] 入金日 = '".$payYmd."'");
				_Log("[_GetUserStatusHtml] 期限日 = '".$deadlineYmd."'");
				_Log("[_GetUserStatusHtml] 入金日(time) = '".$payTime."'");
				_Log("[_GetUserStatusHtml] 期限日(time) = '".$deadlineTime."'");
				
				//支払状況ID
				$usrStsPayStatusId = _GetSelect($mstPayStatusList, 'update['.$usrUserId.'][usr_sts_pay_status_id]['.$tblUserStatusInfo['usr_sts_no'].']', $tblUserStatusInfo['usr_sts_pay_status_id'], '', false, '&nbsp;', 1, false, 'id', 'name');

				//商号(会社名)
				$cmpCompanyName = "(未登録)";
				if (!_IsNull($tblCompanyList)) {
					if (isset($tblCompanyList[$tblUserStatusInfo['usr_sts_company_id']])) {
						if (!_IsNull($tblCompanyList[$tblUserStatusInfo['usr_sts_company_id']]['cmp_company_name'])) {
							$cmpCompanyName = $tblCompanyList[$tblUserStatusInfo['usr_sts_company_id']]['cmp_company_name'];
							$cmpCompanyName = _SubStr($cmpCompanyName, 8);
						}
					}
				}
				$cmpCompanyName = htmlspecialchars($cmpCompanyName);

				$userStatus .= "<tr>";
				$userStatus .= "\n";
				//作成日(申込日)
				$userStatus .= "<td>";
				$userStatus .= $tblUserStatusInfo['usr_sts_create_date_yymmdd_2'];
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//入金日
				$userStatus .= "<td>";
				$userStatus .= $payYmd;
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//終了日
				$userStatus .= "<td>";
				$userStatus .= $deadlineYmd;
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//商号(会社名)
				$userStatus .= "<td>";
				$userStatus .= $cmpCompanyName;
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//システムコース名
				$userStatus .= "<td>";
				$userStatus .= $usrStsSystemCourseName;
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//システムコース価格
				$userStatus .= "<td>";
				$userStatus .= $tblUserStatusInfo['usr_sts_system_course_price'];
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//支払状況ID
				$userStatus .= "<td>";
				$userStatus .= "\n";
				$userStatus .= $usrStsPayStatusId;
				$userStatus .= "\n";
				$userStatus .= "</td>";
				$userStatus .= "\n";
				$userStatus .= "</tr>";
				$userStatus .= "\n";
			}
			$userStatus .= "</tbody>";
			$userStatus .= "\n";
			$userStatus .= "</table>";
			$userStatus .= "\n";
			
			$sendId = "send_".$usrUserId;
			$userStatus .= "<input type=\"checkbox\" name=\"update[".$usrUserId."][send]\" id=\"".$sendId."\" value=\"1\" />";
			$userStatus .= "<label for=\"".$sendId."\">上記支払状況の変更をユーザーにお知らせする。(変更があった場合、送信されます。)</label>";
			$userStatus .= "\n";
		}

//		//ユーザーに関連する株式会社の会社IDを取得する。
//		$undeleteOnly4Rel = false;
//		$relCompanyId = _GetRelationCompanyId($usrUserId, $undeleteOnly4Rel);
//		$relCompanyInfo = null;
//		if (!_IsNull($relCompanyId)) {
//			$condition4Rel = array();
//			$condition4Rel['cmp_company_id'] = $relCompanyId;
//			$relCompanyInfo = _DB_GetInfo('tbl_company', $condition4Rel, $undeleteOnly4Rel, 'cmp_del_flag');
//		}
//		$bufRelCompany = null;
//		if (!_IsNull($relCompanyInfo)) {
//			//文字をHTMLエンティティに変換する。
//			$relCompanyInfo = _HtmlSpecialCharsForArray($relCompanyInfo);
//			$bufRelCompany .= "株式会社：";
//			$bufRelCompany .= "<a href=\"/user/company/info/?id=".$relCompanyInfo['cmp_company_id']."\" alt=\"編集\">[編集]</a>";
//			$bufRelCompany .= "<a href=\"/user/company/article/?id=".$usrUserId."\" alt=\"定款印刷\">[定款印刷]</a>";
//			$bufRelCompany .= "<a href=\"/user/company/pdf/?id=".$usrUserId."\" alt=\"書類印刷\">[書類印刷]</a>";
//			$bufRelCompany .= " ";
//			if (_IsNull($relCompanyInfo['cmp_company_name'])) {
//				$bufRelCompany .= "(会社名未登録)";
//			} else {
//				$bufRelCompany .= $relCompanyInfo['cmp_company_name'];
//			}
//			$bufRelCompany .= "\n";
//		}
//		//ユーザーに関連する合同会社の会社IDを取得する。
//		$relLlcId = _GetRelationLlcId($usrUserId, $undeleteOnly4Rel);
//		$relLlcInfo = null;
//		if (!_IsNull($relLlcId)) {
//			$condition4Rel = array();
//			$condition4Rel['cmp_company_id'] = $relLlcId;
//			$relLlcInfo = _DB_GetInfo('tbl_company', $condition4Rel, $undeleteOnly4Rel, 'cmp_del_flag');
//		}
//		if (!_IsNull($relLlcInfo)) {
//			//文字をHTMLエンティティに変換する。
//			$relLlcInfo = _HtmlSpecialCharsForArray($relLlcInfo);
//			if (!_IsNull($bufRelCompany)) $bufRelCompany .= "<br />";
//			$bufRelCompany .= "合同会社：";
//			$bufRelCompany .= "<a href=\"/user/llc/info/?id=".$relLlcInfo['cmp_company_id']."\" alt=\"編集\">[編集]</a>";
//			$bufRelCompany .= "<a href=\"/user/llc/article/?id=".$usrUserId."\" alt=\"定款印刷\">[定款印刷]</a>";
//			$bufRelCompany .= "<a href=\"/user/llc/pdf/?id=".$usrUserId."\" alt=\"書類印刷\">[書類印刷]</a>";
//			$bufRelCompany .= " ";
//			if (_IsNull($relLlcInfo['cmp_company_name'])) {
//				$bufRelCompany .= "(会社名未登録)";
//			} else {
//				$bufRelCompany .= $relLlcInfo['cmp_company_name'];
//			}
//			$bufRelCompany .= "\n";
//		}

		$bufRelCompany = null;
		if (!_IsNull($htmlTblCompanyList)) {
			$bufRelCompany .= "【注意】以下のリンクを複数ブラウザ又は、タブで開かないでください。<br />【注意】編集対象の会社として常に株式1件、合同1件をセッションで保持しています。";
			$bufRelCompany .= "<br />";
			$bufRelCompany .= "\n";
			foreach ($htmlTblCompanyList as $tcKey => $tblCompanyInfo) {
				switch ($tblCompanyInfo['cmp_company_type_id']) {
					case MST_COMPANY_TYPE_ID_CMP:
						//株式会社
						$prm1 = "url=".rawurlencode("/user/company/info/?id=".$tblCompanyInfo['cmp_company_id'])."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$prm2 = "url=".rawurlencode("/user/company/article/?id=".$usrUserId)."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$prm3 = "url=".rawurlencode("/user/company/pdf/?id=".$usrUserId)."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$bufRelCompany .= "株式会社：";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm1."\" alt=\"編集\">[編集]</a>";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm2."\" alt=\"定款印刷\">[定款印刷]</a>";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm3."\" alt=\"書類印刷\">[書類印刷]</a>";
						//※target="_blank"を付けないこと。理由は、編集対象の会社IDは常に1つにするため。
						break;
					case MST_COMPANY_TYPE_ID_LLC:
						//合同会社
						$prm1 = "url=".rawurlencode("/user/llc/info/?id=".$tblCompanyInfo['cmp_company_id'])."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$prm2 = "url=".rawurlencode("/user/llc/article/?id=".$usrUserId)."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$prm3 = "url=".rawurlencode("/user/llc/pdf/?id=".$usrUserId)."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$bufRelCompany .= "合同会社：";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm1."\" alt=\"編集\">[編集]</a>";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm2."\" alt=\"定款印刷\">[定款印刷]</a>";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm3."\" alt=\"書類印刷\">[書類印刷]</a>";
						//※target="_blank"を付けないこと。理由は、編集対象の会社IDは常に1つにするため。
						break;
				}
				$bufRelCompany .= " ";
				if (_IsNull($tblCompanyInfo['cmp_company_name'])) {
					$bufRelCompany .= "(会社名未登録)";
				} else {
					$bufRelCompany .= $tblCompanyInfo['cmp_company_name'];
				}
				$bufRelCompany .= "<br />";
				$bufRelCompany .= "\n";
			}
		}

//		if (!_IsNull($bufRelCompany)) $bufRelCompany .= "<br />";
		$bufRelCompany .= "メール：";
		$bufRelCompany .= $usrEMailShow;
		$bufRelCompany .= " / パスワード：";
		$bufRelCompany .= $usrPassShow;

		$relCompany = null;
		if (!_IsNull($bufRelCompany)) {
			$relCompany .= "<div style=\"border:1px solid #999;background-color:#fff4d3;\">";
			$relCompany .= "\n";
			$relCompany .= $bufRelCompany;
			$relCompany .= "<div>";
			$relCompany .= "\n";
		}
		
		$mcList .= "<tr>";
		$mcList .= "\n";

		$mcList .= "<td>";
		$mcList .= "\n";
		$mcList .= $usrUserIdShow;
		$mcList .= "\n";
		$mcList .= $usrUserIdHidden;
		$mcList .= "\n";
		$mcList .= "<br />";
		$mcList .= "\n";
		$mcList .= $usrDelFlag;
		$mcList .= "\n";
		$mcList .= "</td>";
		$mcList .= "\n";
		$mcList .= "<td>";
		$mcList .= "\n";
		$mcList .= $usrPlanId;
		$mcList .= "\n";
		$mcList .= "<br />";
		$mcList .= "\n";
		$mcList .= "<span title=\"".$htmlResInfo['usr_family_name']." ".$htmlResInfo['usr_first_name']."\">".$usrFFName."</span>";
		$mcList .= "\n";
		$mcList .= "<br />";
		$mcList .= "\n";
		$mcList .= "<span title=\"".$htmlResInfo['usr_e_mail']."\">".$usrEMail."</span>";
		$mcList .= "\n";
		$mcList .= "<br />";
		$mcList .= "\n";
		$mcList .= "<span>".$usrTel."</span>";
		$mcList .= "\n";
		$mcList .= "</td>";
		$mcList .= "\n";
		$mcList .= "<td>";
		$mcList .= "\n";
		$mcList .= $userStatus;
		$mcList .= $relCompany;
		$mcList .= "</td>";
		$mcList .= "\n";
		$mcList .= "</tr>";
		$mcList .= "\n";
	}

	$mcList .= "</tbody>";
	$mcList .= "\n";
	$mcList .= "</table>";
	$mcList .= "\n";
	$mcList .= $htmlPage;
	$mcList .= "\n";
	$mcList .= "</div>";
	$mcList .= "\n";
	$mcList .= "<div class=\"button\">";
	$mcList .= "<input class=\"submit\" type=\"submit\" name=\"go\" value=\" 更　新 \" />";
	$mcList .= "</div>";
	$mcList .= "\n";
//	$mcList .= "</div>";
//	$mcList .= "\n";
//	$mcList .= "</div>";
//	$mcList .= "\n";

	$mcList .= "<input type=\"hidden\" name=\"token\" value=\"".$_SESSION['token']."\" />";
	$mcList .= "\n";

	$mcList .= "</form>";
	$mcList .= "\n";
}

//メッセージ
$mcMessage = null;
if (!_IsNull($message)) {
	$addClass = null;
	//エラーが有る場合、文字色を変更する。
	if ($errorFlag) $addClass = "errorMessage";
	$mcMessage .= "<div class=\"message ".$addClass."\">";
	$mcMessage .= "\n";
	$mcMessage .= nl2br($message);
	$mcMessage .= "\n";
	$mcMessage .= "</div>";
	$mcMessage .= "\n";
}


$maincontent .= "<div class=\"formWrapper\">";
$maincontent .= "\n";
$maincontent .= "<div class=\"formList\">";
$maincontent .= "\n";

$maincontent .= $mcSelect;
$maincontent .= $mcMessage;
$maincontent .= $mcList;

$maincontent .= "</div>";
$maincontent .= "\n";
$maincontent .= "</div>";
$maincontent .= "\n";


//スクリプトを設定する。
$script = null;

$addStyle = null;

//switch ($xmlName) {
//	case XML_NAME_SEAL_SET:
//		//法人印注文情報[印鑑]
//		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
//		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
//		break;
//	case XML_NAME_SEAL_ALL:
//		//法人印注文情報[入力内容確認]
////		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
////		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
//		break;
//	default:
//		break;
//}
//
//$script .= "<style type=\"text/css\">";
//$script .= "\n";
//$script .= "<!--";
//$script .= "\n";
//$script .= "ul#sealn li#".$stepId." a:link";
//$script .= ",ul#sealn li#".$stepId." a:visited";
//$script .= "\n";
//$script .= "{height: 32px;color: #3176af;border-bottom: 3px solid #76b0df;}";
//$script .= "\n";
//$script .= $addStyle;
//$script .= "\n";
//$script .= "-->";
//$script .= "\n";
//$script .= "</style>";
//$script .= "\n";






//サイドメニューを設定する。
$sidebar = null;

////基本URL
//$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);
//
//$sidebar .= $htmlSidebarLogin;

//基本URL
$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
//ログインユーザー名
$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
//現在の入力状況
$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);




$sidebar .= $htmlSidebarUserMenu;


//パンくずリストを設定する。
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_ADMIN_USER, '', PAGE_TITLE_ADMIN_USER, 3);
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


_Log("[/admin/user/index.php] end.");
echo $html;

?>
