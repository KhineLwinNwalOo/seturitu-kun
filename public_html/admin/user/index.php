<?php
/*
 * [���������Ω.JP �ġ���]
 * [��������]
 * �桼��������
 *
 * ��������2011/10/17	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
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


//ǧ�ڥ����å�----------------------------------------------------------------------start
$loginInfo = null;

//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_LOGIN_USER_INFO])) {
	_Log("[/user/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/user/index.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ".URL_LOGIN);
	exit;
} else {
	//����������������롣
	$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];

	//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
	_CheckAuth($loginInfo, AUTH_CLIENT, AUTH_WOOROM);
}
//ǧ�ڥ����å�----------------------------------------------------------------------end



//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- start
_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ start");
$tempFile = '../../common/temp_html/temp_base.txt';
_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) HTML�ƥ�ץ졼�ȥե����� = '".$tempFile."'");

$html = @file_get_contents($tempFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($html !== false && !_IsNull($html)) {
	_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (����) �ڼ��ԡ�");
	$html .= "HTML�ƥ�ץ졼�ȥե����������Ǥ��ޤ���\n";
}


//$tempSidebarLoginFile = '../../common/temp_html/temp_sidebar_login.txt';
//_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarLoginFile."'");
//
//$htmlSidebarLogin = @file_get_contents($tempSidebarLoginFile);
////"HTML"��¸�ߤ����硢ɽ�����롣
//if ($htmlSidebarLogin !== false && !_IsNull($htmlSidebarLogin)) {
//	_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) ��������");
//} else {
//	//�����Ǥ��ʤ��ä����
//	_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼������) �ڼ��ԡ�");
//}

$tempSidebarUserMenuFile = '../../common/temp_html/temp_sidebar_user_menu.txt';
_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) HTML�ƥ�ץ졼�ȥե����� = '".$tempSidebarUserMenuFile."'");

$htmlSidebarUserMenu = @file_get_contents($tempSidebarUserMenuFile);
//"HTML"��¸�ߤ����硢ɽ�����롣
if ($htmlSidebarUserMenu !== false && !_IsNull($htmlSidebarUserMenu)) {
	_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) ��������");
} else {
	//�����Ǥ��ʤ��ä����
	_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} (�����ɥ�˥塼�����˥塼) �ڼ��ԡ�");
}

_Log("[/admin/user/index.php] {HTML�ƥ�ץ졼�Ȥ��ɤ߹���} ������������������������������ end");
//HTML�ƥ�ץ졼�Ȥ��ɤ߹��ࡣ------------------------------------------------------- end


//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

//�ڡ��������ȥ�
$pageTitle = PAGE_TITLE_ADMIN_USER;

//���饤������ͥ᡼�륢�ɥ쥹
$clientMail = COMPANY_E_MAIL;
//�ޥ������ѥ᡼�륢�ɥ쥹
$masterMailList = $_COMPANY_MASTER_MAIL_LIST;

//�ƥ�����
if (false) {
//if (true) {
	//���饤������ͥ᡼�륢�ɥ쥹
	$clientMail = "ishikawa@woorom.com";
	//�ޥ������ѥ᡼�륢�ɥ쥹
	//��,�פǤ����ä���������ɲä��Ʋ�������
	$masterMailList = array("ishikawa@woorom.com", "ishikawa@woorom.com");
}







//DB�򥪡��ץ󤹤롣
$cid = _DB_Open();





//�����ƥॳ�����ޥ���
$condition4Mst = null;
$undeleteOnly4Mst = false;//�����Ѥ���������ɽ�����롣
$order4Mst = "lpad(show_order,10,'0'),id";
$mstSystemCourseList = _DB_GetList('mst_system_course', $condition4Mst, $undeleteOnly4Mst, $order4Mst, 'del_flag', 'id');
if (!_IsNull($mstSystemCourseList)) {
	foreach ($mstSystemCourseList as $mKey => $mInfo) {
		$name = null;
		$name .= $mInfo['name'];
		if (!_IsNull($mInfo['price'])) {
			$name .= " ";
			$name .= "��";
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

//��ʧ�����ޥ���
$mstPayStatusList = _GetMasterList('mst_pay_status', $undeleteOnly4Mst);
//�ץ��ޥ���
$mstPlanList = _GetMasterList('mst_plan', $undeleteOnly4Mst);
//����ե饰
$delFlagList = array(
			 DELETE_FLAG_NO => array('id' => DELETE_FLAG_NO, 'name' => '������('.DELETE_FLAG_NO_NAME.')', 'name2' => '��')
			,DELETE_FLAG_YES => array('id' => DELETE_FLAG_YES, 'name' => '��Ͽ���('.DELETE_FLAG_YES_NAME.')', 'name2' => '��')
			);


$bufPost = $_POST;
//�Хå�����å�����������
$bufPost = _StripslashesForArray($bufPost);
_Log("[/admin_user_list/index.php] \$_POST(�Хå�����å�������) = '".print_r($bufPost,true)."'");

//���ѿ�
$info = null;		//������
$resList = null;	//������̰���
$resCount = 0;		//�������
$page = 1;			//����ڡ���
$orderId = 1;		//�����Ⱦ��
$message = null;
$errorFlag = false;	//���顼�ե饰


$order = null;
$undeleteOnly = false;


//���submit�к�
if (!isset($_SESSION['token'])) $_SESSION['token'] = uniqid("usr_");



//�����ܥ��󤬲����줿���
if (isset($_POST['select'])) {
	$info = $bufPost;												//�������
}
//�ڡ�����󥯤������줿���
else if (isset($_GET['page'])) {
	$info['condition'] = $_SESSION[SID_SRCH_USER_CONDITION];		//�������
	$page = $_GET['page'];											//����ڡ���
	$orderId = $_SESSION[SID_SRCH_USER_SORT];						//�����Ⱦ��
}
//�����ȥ�󥯤������줿���
else if (isset($_GET['order'])) {
	$info['condition'] = $_SESSION[SID_SRCH_USER_CONDITION];		//�������
	$orderId = $_GET['order'];										//�����Ⱦ��
}
//�����ܥ��󤬲����줿���
elseif (isset($_POST['go'])) {
	//�����ͤ�������롣
	$info = $bufPost;

	$info['condition'] = $_SESSION[SID_SRCH_USER_CONDITION];		//�������
	$resList = $_SESSION[SID_SRCH_USER_LIST];						//�������
	$resCount = $_SESSION[SID_SRCH_USER_COUNT];						//�������
	$page = $_SESSION[SID_SRCH_USER_PAGE];							//����ڡ���
	$orderId = $_SESSION[SID_SRCH_USER_SORT];						//�����Ⱦ��

	_Log("[/admin/user/index.php] ���submit�к� SESSION�� = '".$_SESSION['token']."'");
	_Log("[/admin/user/index.php] ���submit�к�    POST�� = '".$info['token']."'");

	//���submit�к��򤹤롣
	if ($_SESSION['token'] == $info['token']) {
		if (isset($info['update'])) {
			_Log("[/admin/user/index.php] {����} -------------------- ����");
			
			//�����ͥ����å�
//			$message .= "���顼��å�����";
			foreach ($info['update'] as $key => $newInfo) {
				//�����å�...
				
				//2011/10/18���ߡ������å�̵����
				
				//������̤������ͤ��񤭤��롣�����顼���κ�ɽ���Τ��ᡣ��
				foreach ($newInfo as $name => $value) {
					$resList[$key][$name] = $value;
				}
			}
	
			if (_IsNull($message)) {
				//���å���󤫤鸡����̤�Ƽ������롣���嵭���ǡ���񤭤��Ƥ��뤿�ᡣ
				$resList = $_SESSION[SID_SRCH_USER_LIST];
	
				$count = 0;
				foreach ($info['update'] as $key => $newInfo) {
					$count++;
		
					//�ѹ�̵ͭ������å����롣
					$updateFlag = false;							//�����ܤι���̵ͭ�ե饰
					$updateFlagTblUser = false;						//�桼�����ơ��֥�
					$updateFlagTblUserStatus = false;				//�桼����_�����ơ��֥�
					$messageTblUser = null;						//�桼�����ơ��֥�
					
					//����ե饰
					if (!isset($newInfo['usr_del_flag']) || _IsNull($newInfo['usr_del_flag'])) $newInfo['usr_del_flag'] = DELETE_FLAG_NO;
					if ($newInfo['usr_del_flag'] != $resList[$key]['usr_del_flag']) {
						$updateFlag = true;
						$updateFlagTblUser = true;
						$messageTblUser .= "����Ͽ�����";
					}
					//�ץ��ID
					if ($newInfo['usr_plan_id'] != $resList[$key]['usr_plan_id']) {
						$updateFlag = true;
						$updateFlagTblUser = true;
						$messageTblUser .= "�֥ץ���";
					}
					//��ʧ����ID
					if (isset($newInfo['usr_sts_pay_status_id'])) {
						foreach ($newInfo['usr_sts_pay_status_id'] as $usNo => $newPayStatusId) {
							if ($newPayStatusId != $resList[$key]['usr_sts_pay_status_id'][$usNo]) {
								$updateFlag = true;
								$updateFlagTblUserStatus = true;
								break;
							}
						}
					}

					//������˺ǿ���DB����Ǹ�����̤��񤭤��롣{true:��񤭤���/false:���ʤ�}
					$overwriteFlag = false;
					
					if ($updateFlag) {

						//��������...
						$overwriteFlag = true;

						$usrUserIdShow = "ID.".sprintf('%03d', $newInfo['usr_user_id']);
						$ip = $_SERVER["REMOTE_ADDR"];
						
						//�桼�����ơ��֥�
						if ($updateFlagTblUser) {
							$bufInfo = array();
							$bufInfo['tbl_user']['usr_user_id'] = $newInfo['usr_user_id'];				//�桼����ID
							$bufInfo['tbl_user']['usr_auth_id'] = $resList[$key]['usr_auth_id'];		//����ID���ѹ����ʤ���������ꤹ�롣
							$bufInfo['tbl_user']['usr_del_flag'] = $newInfo['usr_del_flag'];			//����ե饰
							$bufInfo['tbl_user']['usr_plan_id'] = $newInfo['usr_plan_id'];				//�ץ��ID
							$dbRes = _SaveUserInfo($bufInfo);
							if ($dbRes === false) {
								$message .= $usrUserIdShow." ".$messageTblUser."���ѹ��˼��Ԥ��ޤ�����\n";
								$errorFlag = true;
							} else {
								$message .= $usrUserIdShow." ".$messageTblUser."���ѹ����ޤ�����\n";
							}
						}
						//�桼����_�����ơ��֥�
						if ($updateFlagTblUserStatus) {
							if (isset($newInfo['usr_sts_pay_status_id'])) {
								
								$bodyUserCompany = null;
								$bodyUserStatus = null;
								$sameCompanyList = null;
								$payStatusOKFlag = false;//����ѥե饰
								
								foreach ($newInfo['usr_sts_pay_status_id'] as $usNo => $newPayStatusId) {
									if ($newPayStatusId != $resList[$key]['usr_sts_pay_status_id'][$usNo]) {
										$bufName = null;
										$bufName .= $resList[$key]['tbl_user_status'][$usNo]['usr_sts_create_date_yymmdd_2'];
										$bufName .= " - ";
										$bufName .= $resList[$key]['tbl_user_status'][$usNo]['usr_sts_system_course_name'];
										$bufName .= " - ";
										$bufName .= $mstPayStatusList[$newPayStatusId]['name'];
										
										$bufInfo = array();

										//��ʧ����ID�ˤ�äơ����դ򹹿����롣
										switch ($newPayStatusId) {
										case MST_PAY_STATUS_ID_NON:
											//̤����
											$bufInfo['usr_sts_pay_year'] = null;									//������(ǯ)
											$bufInfo['usr_sts_pay_month'] = null;									//������(��)
											$bufInfo['usr_sts_pay_day'] = null;										//������(��)
											$bufInfo['usr_sts_end_year'] = null;									//��λ��(ǯ)
											$bufInfo['usr_sts_end_month'] = null;									//��λ��(��)
											$bufInfo['usr_sts_end_day'] = null;										//��λ��(��)
											break;
										case MST_PAY_STATUS_ID_OK:
											//�����
											$bufInfo['usr_sts_pay_year'] = date('Y');								//������(ǯ)
											$bufInfo['usr_sts_pay_month'] = date('n');								//������(��)
											$bufInfo['usr_sts_pay_day'] = date('j');								//������(��)
											$bufInfo['usr_sts_end_year'] = null;									//��λ��(ǯ)
											$bufInfo['usr_sts_end_month'] = null;									//��λ��(��)
											$bufInfo['usr_sts_end_day'] = null;										//��λ��(��)
											$payStatusOKFlag = true;
											break;
										case MST_PAY_STATUS_ID_CANCEL:
											//����󥻥�
											$bufInfo['usr_sts_pay_year'] = null;									//������(ǯ)
											$bufInfo['usr_sts_pay_month'] = null;									//������(��)
											$bufInfo['usr_sts_pay_day'] = null;										//������(��)
											$bufInfo['usr_sts_end_year'] = null;									//��λ��(ǯ)
											$bufInfo['usr_sts_end_month'] = null;									//��λ��(��)
											$bufInfo['usr_sts_end_day'] = null;										//��λ��(��)
											break;
										case MST_PAY_STATUS_ID_END:
											//�����ڤ�
											$bufInfo['usr_sts_end_year'] = date('Y');								//��λ��(ǯ)
											$bufInfo['usr_sts_end_month'] = date('n');								//��λ��(��)
											$bufInfo['usr_sts_end_day'] = date('j');								//��λ��(��)
											break;
										}

										$bufInfo['usr_sts_user_id'] = $newInfo['usr_user_id'];						//�桼����ID
										$bufInfo['usr_sts_no'] = $usNo;												//����No
										$bufInfo['usr_sts_pay_status_id'] = $newPayStatusId;						//��ʧ����ID
										$bufInfo['usr_sts_update_ip'] = $ip;										//����IP
										$bufInfo['usr_sts_update_date'] = null;										//������
										$resDb = _DB_SaveInfo('tbl_user_status', $bufInfo);
										if ($resDb === false) {
											$message .= $usrUserIdShow." ��".$bufName."�פ��ѹ��˼��Ԥ��ޤ�����\n";
											$errorFlag = true;
										} else {
											$message .= $usrUserIdShow." ��".$bufName."�פ��ѹ����ޤ�����";
											if (isset($newInfo['send'])) {
												$message .= "(�᡼��������)";
												
//												if (!isset($sameCompanyList[$resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']])) {
//													//���������Ω�����������롣
//													$companyInfo = _GetCompanyInfo($resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']);
//													
//													if (!_IsNull($bodyUserCompany)) $bodyUserCompany .= "-----------------------------------------\n";
//													$bodyUserCompany .= "����(���̾)��";
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
													//���������Ω�����������롣
													$companyInfo = _GetCompanyInfo($resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']);
													$sameCompanyList[$resList[$key]['tbl_user_status'][$usNo]['usr_sts_company_id']] = $companyInfo;
												}
												$companyName = "<̤����>";
												if (!_IsNull($companyInfo)) {
													if (!_IsNull($companyInfo['tbl_company']['cmp_company_name'])) {
														$companyName = $companyInfo['tbl_company']['cmp_company_name'];
													}
												}
												
												if (!_IsNull($bodyUserStatus)) $bodyUserStatus .= "-----------------------------------------\n";
												$bodyUserStatus .= "����(���̾)��";
												$bodyUserStatus .= $companyName;
												$bodyUserStatus .= "\n";
												$bodyUserStatus .= "�����ѥ�������";
												$bodyUserStatus .= $resList[$key]['tbl_user_status'][$usNo]['usr_sts_system_course_name'];
												$bodyUserStatus .= "\n";
												$bodyUserStatus .= "�����ѥ��ơ�������";
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
										//�᡼����ʸ�ζ�����ʬ�����ꤹ�롣
										$body = null;
									
										$body .= "��������������������������������������������������������\n";
										$body .= "�桼��������\n";
										$body .= "��������������������������������������������������������\n";
										$body .= "��̾����";
										$body .= $resList[$key]['usr_family_name'];
										$body .= " ";
										$body .= $resList[$key]['usr_first_name'];
										$body .= " ��";
										$body .= "\n";
										$body .= "�᡼�륢�ɥ쥹��";
										$body .= $resList[$key]['usr_e_mail'];
										$body .= "\n";
										$body .= "\n";
									
//										$body .= "��������������������������������������������������������\n";
//										$body .= "��Ҿ���\n";
//										$body .= "��������������������������������������������������������\n";
//										$body .= $bodyUserCompany;
									
										$body .= "��������������������������������������������������������\n";
										$body .= "�����ѥ���������\n";
										$body .= "��������������������������������������������������������\n";
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
										if (!_IsNull(COMPANY_TEL)) $body .= "TEL��".COMPANY_TEL."\n";
										if (!_IsNull(COMPANY_FAX)) $body .= "FAX��".COMPANY_FAX."\n";
										$body .= "E-mail��".$clientMail." \n";
										if (!_IsNull(COMPANY_BUSINESS_HOURS)) $body .= "�ĶȻ��֡�".COMPANY_BUSINESS_HOURS."\n";
										$body .= "--------------------------------------------------------\n\n";
									
										$body .= "�����ѥ��ơ���������������".date("Yǯn��j�� H��iʬ")."\n";
										$body .= $_SERVER["REMOTE_ADDR"]."\n";
									
									
										//�������ѥ᡼����ʸ�����ꤹ�롣
										$adminBody = "";
										//$adminBody .= $siteTitle." \n";
										//$adminBody .= "\n";
										$adminBody .= "**************************************************************************************\n";
										$adminBody .= "��".$siteTitle."�٤����ѥ��ơ������������\n";
										$adminBody .= "**************************************************************************************\n";
										$adminBody .= "\n";
										$adminBody .= $body;
									
										//�������ѥ᡼����ʸ�����ꤹ�롣
										$customerBody = "";
										$customerBody .= $resList[$key]['usr_family_name']." ".$resList[$key]['usr_first_name']." ��\n";
										$customerBody .= "\n";
										$customerBody .= "**************************************************************************************\n";
										$customerBody .= "���Ĥ⡢��".$siteTitle."�٤Τ����Ѥ��꤬�Ȥ��������ޤ���\n";
										if ($payStatusOKFlag) {
											$customerBody .= "���ѤΤ����⤢�꤬�Ȥ��������ޤ�����\n";
										}
										$customerBody .= "�����ѥ��ơ���������������ޤ����Τǡ����Τ餻�������ޤ���\n";
										$customerBody .= "����ǧ���ꤤ�������ޤ���\n";
										$customerBody .= "**************************************************************************************\n";
										$customerBody .= "\n";
										$customerBody .= $body;
									
									
										//�������ѥ����ȥ�����ꤹ�롣
										$adminTitle = "[".$siteTitle."] �����ѥ��ơ��������� (".$resList[$key]['usr_family_name']." ".$resList[$key]['usr_first_name']." ��)";
										//�������ѥ����ȥ�����ꤹ�롣
										$customerTitle = "[".$siteTitle."] �����ѥ��ơ����������Τ��Τ餻";
									
										mb_language("Japanese");
										
										$parameter = "-f ".$clientMail;
									
										//�᡼������
										//�����ͤ��������롣
										$rcd = mb_send_mail($resList[$key]['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);
										if ($rcd === false) {
											$message .= $usrUserIdShow." �֥᡼�������פ˼��Ԥ��ޤ��������١��������Ƥ���������(����ʧ�������ѹ������ξ��ϡ���ö�����ᤷ�Ƥ�������ѹ����Ƥ���������)\n";
											$errorFlag = true;
										}
									
										//���饤����Ȥ��������롣
										$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$clientMail, $parameter);
									
										//�ޥ��������������롣
										foreach($masterMailList as $masterMail){
											$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$clientMail, $parameter);
										}
									}
								}
							}
						}
					}
		
					//���������ä���硢�ǿ���DB����Ǹ�����̤��񤭤��롣
					if ($overwriteFlag) {
						_Log("[/admin/user/index.php] {����} 3.���������ä���硢�ǿ���DB����Ǹ�����̤��񤭤��롣");
						
						//�桼��������򸡺����롣
						$condition4new = array();
						$condition4new['usr_user_id'] = $newInfo['usr_user_id'];//�桼����ID
						$newUserList = _GetUser($condition4new, $order, false);	
						
						if (_IsNull($newUserList)) {
						} else {
							$resList[$key] = $newUserList[0];
						}
						
						//��񤭤���������̤򥻥å�������¸���롣
						$_SESSION[SID_SRCH_USER_LIST] = $resList;
					}
				}
		
				//���å���󤫤鸡����̤�Ƽ������롣
				$resList = $_SESSION[SID_SRCH_USER_LIST];
		
				//�����ޤǤǥ�å����������ξ�硢��Ͽ��������������ʤ��ä���
				if (_IsNull($message)) {
					$message = "�ѹ��ս꤬����ޤ���";
				} else {
					//���顼̵���ξ�硢���submit�к��Υ�ˡ��������򹹿����롣
					$_SESSION['token'] = uniqid("usr_");
				}
			} else {
				//���顼��ͭ����
				$message = "�����Ϥ˸�꤬����ޤ���\n".$message;
				$errorFlag = true;
			}

			_Log("[/admin/user/index.php] {����} -------------------- ��λ");
		}
	} else {
		$message = "����Ź����Ǥ��������򤹤���ϡ��ֹ����ץܥ���򲡤��Ƥ���������";
		$errorFlag = true;
	}
}



if (_IsNull($message)) {
	//���������ɲä��롣
	$info['condition']['order_id'] = $orderId;							//�����Ⱦ��
	
	//�������롣
	$resList = _GetUser($info['condition'], $order, $undeleteOnly, false, $page, USER_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE, 2);
	$resCount = _GetUser($info['condition'], $order, $undeleteOnly, true);
	if (_IsNull($resList)) {
		$message .= "�������˳�������桼���������¸�ߤ��ޤ���\n";
	}

	//���å�������¸���롣
	$_SESSION[SID_SRCH_USER_CONDITION] = $info['condition'];	//�������
	$_SESSION[SID_SRCH_USER_LIST] = $resList;					//������̰���
	$_SESSION[SID_SRCH_USER_COUNT] = $resCount;					//�������
	$_SESSION[SID_SRCH_USER_PAGE] = $page;						//����ڡ���
	$_SESSION[SID_SRCH_USER_SORT] = $orderId;					//�����Ⱦ��
}



$htmlPage = _GetPageLink($resCount, $page, USER_PAGE_LINK_TOP_MESSAGE, USER_PAGE_LINK_ACTIVE_PAGE_MESSAGE, USER_PAGE_LINK_COUNT_MESSAGE, null, USER_PAGE_LINK_LIMIT, USER_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE, USER_PAGE_LINK_FRONT_TEXT, USER_PAGE_LINK_REAR_TEXT);
$htmlPage = "<div class=\"page\">".$htmlPage."</div>";
$htmlPage .= "\n";



















//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
$htmlResList = _HtmlSpecialCharsForArray($resList);
_Log("[/admin/user/index.php] POST(ʸ����HTML����ƥ��ƥ����Ѵ����롣) = '".print_r($info,true)."'");


//�����ȥ�����ꤹ�롣
$title = $pageTitle;

//����URL�����ꤹ�롣
$basePath = "../..";

//����ƥ�Ĥ����ꤹ�롣
$maincontent = null;
$maincontent .= "<h2>";
$maincontent .= "<img src=\"../../img/maincontent/pt_buy.jpg\" title=\"\" alt=\"����������Τ���ʧ��\">";
$maincontent .= "</h2>";
$maincontent .= "\n";

////���֥�˥塼�����ꤹ�롣
//$maincontent .= "<ul id=\"sealn\">";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_set\">";
//$maincontent .= "<a href=\"?step=1".$addHref."\">��������</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_imprint\">";
//$maincontent .= "<a href=\"?step=2".$addHref."\">��������</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_name\">";
//$maincontent .= "<a href=\"?step=3".$addHref."\">���̾�����Ϥ���</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "<li id=\"sealn_confirm\">";
//$maincontent .= "<a href=\"?step=4".$addHref."\">�������Ƴ�ǧ</a>";
//$maincontent .= "</li>";
//$maincontent .= "\n";
//$maincontent .= "</ul>";
//$maincontent .= "\n";


//$maincontent .= _GetFormTable($mode, $xmlList, $info, $tabindex, $loginInfo, $message, $errorFlag, $allShowFlag);

//�������
$mcSelect = null;
$mcSelect .= "<form id=\"frmSelect\" name=\"frmSelect\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" enctype=\"multipart/form-data\">";
$mcSelect .= "\n";
//$mcSelect .= "<div class=\"formWrapper\">";
//$mcSelect .= "\n";
//$mcSelect .= "<div class=\"formList\">";
//$mcSelect .= "\n";
$mcSelect .= "<div>";
$mcSelect .= "\n";
$mcSelect .= "<h3>�������</h3>";
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
$mcSelect .= "<td class=\"colHead\">̾��</td>";
$mcSelect .= "\n";
$mcSelect .= "<td>";
$mcSelect .= "��<input type=\"text\" name=\"condition[usr_family_name]\" size=\"10\" maxlength=\"100\" value=\"".$info['condition']['usr_family_name']."\" />";
$mcSelect .= "̾<input type=\"text\" name=\"condition[usr_first_name]\" size=\"10\" maxlength=\"100\" value=\"".$info['condition']['usr_first_name']."\" />";
$mcSelect .= "</td>";
$mcSelect .= "\n";
$mcSelect .= "<td class=\"colHead\">�᡼�륢�ɥ쥹</td>";
$mcSelect .= "\n";
$mcSelect .= "<td>";
$mcSelect .= "<input type=\"text\" name=\"condition[usr_e_mail]\" size=\"20\" maxlength=\"200\" value=\"".$info['condition']['usr_e_mail']."\" />";
$mcSelect .= "</td>";
$mcSelect .= "\n";
$mcSelect .= "</tr>";
$mcSelect .= "\n";
$mcSelect .= "<tr>";
$mcSelect .= "\n";
$mcSelect .= "<td class=\"colHead\">��ʧ����</td>";
$mcSelect .= "\n";
$mcSelect .= "<td>";
$mcSelect .= "\n";
$bufTabindex = null;
$mcSelect .= _GetCheckbox($mstPayStatusList, 'condition[usr_sts_pay_status_id]', $info['condition']['usr_sts_pay_status_id'], $bufTabindex, '', 'id', 'name_del_2');
$mcSelect .= "\n";
$mcSelect .= "</td>";
$mcSelect .= "\n";
$mcSelect .= "<td class=\"colHead\">���Ѿ���</td>";
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
$mcSelect .= "<td class=\"colHead\">�ץ��</td>";
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
$mcSelect .= "<input class=\"submit\" type=\"submit\" name=\"select\" value=\" ������ \" />";
$mcSelect .= "</div>";
$mcSelect .= "\n";
//$mcSelect .= "</div>";
//$mcSelect .= "\n";
//$mcSelect .= "</div>";
//$mcSelect .= "\n";
$mcSelect .= "</form>";
$mcSelect .= "\n";

//��������
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
	$mcList .= "<h3>�������</h3>";
	$mcList .= "\n";
	$mcList .= $htmlPage;
	$mcList .= "\n";
	$mcList .= "<table class=\"searchResultListTable\">";
	$mcList .= "\n";

	$mcList .= "<thead>";
	$mcList .= "\n";
	$mcList .= "<tr>";
	$mcList .= "\n";
	$mcList .= "<td class=\"colHead\">ID/<br />����<br />����</td>";
	$mcList .= "\n";
	$mcList .= "<td class=\"colHead\">�ץ��/̾��/<br />�᡼��/����</td>";
	$mcList .= "\n";
	$mcList .= "<td class=\"colHead\">��ʧ����</td>";
	$mcList .= "\n";
	$mcList .= "</tr>";
	$mcList .= "\n";
	$mcList .= "</thead>";
	$mcList .= "\n";

	$mcList .= "<tbody>";
	$mcList .= "\n";

	foreach ($resList as $resKey => $resInfo) {
		
		$htmlResInfo = $htmlResList[$resKey];

		//�桼����ID
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

		//E-Mail(ɽ����)
		$usrEMailShow = '-';
		if (!_IsNull($resInfo['usr_e_mail'])) {
			$usrEMailShow = $resInfo['usr_e_mail'];
			$usrEMailShow = htmlspecialchars($usrEMailShow, ENT_QUOTES);
		}
		//�ѥ����(ɽ����)
		$usrPassShow = '-';
		if (!_IsNull($resInfo['usr_pass'])) {
			$usrPassShow = $resInfo['usr_pass'];
			$usrPassShow = htmlspecialchars($usrPassShow, ENT_QUOTES);
		}

		//̾��(��)
		//̾��(̾)
		$usrFFName = '-';
		if (!_IsNull($resInfo['usr_family_name']) || !_IsNull($resInfo['usr_first_name'])) {
			$usrFFName = $resInfo['usr_family_name']." ".$resInfo['usr_first_name'];
			$usrFFName = _SubStr($usrFFName, 8, '...', 'UTF-8');
			$usrFFName = htmlspecialchars($usrFFName, ENT_QUOTES);
		}
		//�����ֹ�
		$usrTel = '-';
		if (!_IsNull($resInfo['usr_tel1']) || !_IsNull($resInfo['usr_tel2']) || !_IsNull($resInfo['usr_tel3'])) {
			$usrTel = $resInfo['usr_tel1']."-".$resInfo['usr_tel2']."-".$resInfo['usr_tel3'];
			$usrTel = htmlspecialchars($usrTel, ENT_QUOTES);
		}
		//����ե饰
		$checked = null;
		if ($resInfo['usr_del_flag'] == DELETE_FLAG_YES) {
			$checked = "checked=\"checked\"";
		}
		$delId = "del_".$usrUserId;
		$usrDelFlag = null;
		$usrDelFlag .= "<input type=\"checkbox\" name=\"update[".$usrUserId."][usr_del_flag]\" id=\"".$delId."\" value=\"".DELETE_FLAG_YES."\" ".$checked." />";
		$usrDelFlag .= "<br />";
		$usrDelFlag .= "<label for=\"".$delId."\">��Ͽ<br />���</label>";

		//�ץ��ID
		$usrPlanId = _GetSelect($mstPlanList, 'update['.$usrUserId.'][usr_plan_id]', $resInfo['usr_plan_id'], '', false, '&nbsp;', 1, false, 'id', 'name_mini');

		//�桼����_���_��Ϣ�եơ��֥�򸡺����롣
		$undeleteOnly4All = false;
		$condition4All = array();
		$condition4All['usr_cmp_rel_user_id'] = $usrUserId;			//�桼����ID
		$order4All = "usr_cmp_rel_company_id";						//�����Ƚ�=���ID�ξ���(�ʤ�Ǥ⤤�����ɡ�)
		$tblUserCompanyRelationList = _DB_GetListByAssociative('tbl_user_company_relation', 'usr_cmp_rel_company_id', null, $condition4All, $undeleteOnly4All, $order4All, 'usr_cmp_rel_del_flag');
		$tblCompanyList = null;
		if (!_IsNull($tblUserCompanyRelationList)) {
			//��ҥơ��֥�򸡺����롣
			$order4All = "cmp_company_type_id";									//�����Ƚ�=��ҥ�����ID�ξ���
			$order4All .= ",cmp_company_id desc";								//�����Ƚ�=���ID�ι߽�
			$condition4All = array();
			$condition4All['cmp_company_id'] = $tblUserCompanyRelationList;		//���ID
			$tblCompanyList = _DB_GetList('tbl_company', $condition4All, $undeleteOnly4All, $order4All, 'cmp_del_flag', 'cmp_company_id');
		}
		//ʸ����HTML����ƥ��ƥ����Ѵ����롣
		$htmlTblCompanyList = _HtmlSpecialCharsForArray($tblCompanyList);

		
		//�桼����_�����ơ��֥�
		$condition4Sts = array();
		$condition4Sts['usr_sts_user_id'] = $usrUserId;						//�桼����ID
		$undeleteOnly4Sts = true;
		$order4Sts = null;
		$order4Sts .= "usr_sts_create_date";
		$order4Sts .= ",usr_sts_no";
		$tblUserStatusList = _DB_GetList('tbl_user_status', $condition4Sts, $undeleteOnly4Sts, $order4Sts, 'usr_sts_del_flag');
		
		//���å������ɲä��롣����ö���ꥢ���롣
		unset($_SESSION[SID_SRCH_USER_LIST][$resKey]['usr_sts_pay_status_id']);
		
		$userStatus = null;
		if (!_IsNull($tblUserStatusList)) {
			
			//ʸ����HTML����ƥ��ƥ����Ѵ����롣
			$htmlTblUserStatusList = _HtmlSpecialCharsForArray($tblUserStatusList);
			
			$userStatus .= "<table class=\"searchResultListTableSub\">";
			$userStatus .= "\n";
			$userStatus .= "<thead>";
			$userStatus .= "\n";
			$userStatus .= "<tr>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">������</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">������</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">��λ(ͽ)</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">���̾</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">�����ƥॳ����</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">����</td>";
			$userStatus .= "\n";
			$userStatus .= "<td class=\"colHead\">��ʧ����</td>";
			$userStatus .= "\n";
			$userStatus .= "</tr>";
			$userStatus .= "\n";
			$userStatus .= "</thead>";
			$userStatus .= "\n";
			$userStatus .= "<tbody>";
			$userStatus .= "\n";
			foreach ($htmlTblUserStatusList as $tusKey => $tblUserStatusInfo) {

				//���å������ɲä��롣���ѹ�̵ͭ�����å��˻��Ѥ��롣
				$_SESSION[SID_SRCH_USER_LIST][$resKey]['usr_sts_pay_status_id'][$tblUserStatusInfo['usr_sts_no']] = $tblUserStatusInfo['usr_sts_pay_status_id'];
				//���å������ɲä��롣�����顼��å������ʤɤ˻��Ѥ��롣
				$_SESSION[SID_SRCH_USER_LIST][$resKey]['tbl_user_status'][$tblUserStatusInfo['usr_sts_no']] = $tblUserStatusList[$tusKey];

				//�����ƥॳ����̾
				$usrStsSystemCourseName = $tblUserStatusList[$tusKey]['usr_sts_system_course_name'];
				$usrStsSystemCourseName = _SubStr($usrStsSystemCourseName, 20);
				$usrStsSystemCourseName = htmlspecialchars($usrStsSystemCourseName);

				$payYmd = null;
				$deadlineYmd = null;

				//����������Ͽ���뤫��
				if (!_IsNull($tblUserStatusInfo['usr_sts_pay_year']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_month']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_day'])) {
					$payYear = $tblUserStatusInfo['usr_sts_pay_year'];
					$payMonth = $tblUserStatusInfo['usr_sts_pay_month'];
					$payDay = $tblUserStatusInfo['usr_sts_pay_day'];
					$payTime = mktime(0, 0, 0, $payMonth, $payDay, $payYear);
					$payYmd = sprintf('%04d/%02d/%02d', $payYear, $payMonth, $payDay);
					$payYmd = mb_substr($payYmd, 2, 8);
				}

				switch ($tblUserStatusInfo['usr_sts_system_course_id']) {
					case MST_SYSTEM_COURSE_ID_CMP://[�������] ���������Ω (�����ƥ���������)
					case MST_SYSTEM_COURSE_ID_LLC://[��Ʊ���] ��Ʊ�����ΩLLC (�����ƥ���������)
		
					case MST_SYSTEM_COURSE_ID_STANDARD_CMP://[����������ɥѡ��ȥʡ��ץ��][�������] ���������Ω (�����ƥ���������)
					case MST_SYSTEM_COURSE_ID_STANDARD_LLC://[����������ɥѡ��ȥʡ��ץ��][��Ʊ���] ��Ʊ�����ΩLLC (�����ƥ���������)
		
					case MST_SYSTEM_COURSE_ID_PLATINUM_CMP://[�ץ���ʥѡ��ȥʡ��ץ��][�������] ���������Ω (�����ƥ���������)
					case MST_SYSTEM_COURSE_ID_PLATINUM_LLC://[�ץ���ʥѡ��ȥʡ��ץ��][��Ʊ���] ��Ʊ�����ΩLLC (�����ƥ���������)

						//����������Ͽ���뤫��
						if (!_IsNull($tblUserStatusInfo['usr_sts_pay_year']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_month']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_day'])) {
							if (!_IsNull(SYSTEM_USE_DEADLINE)) {
								//��������N������������롣
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

				//��λ������Ͽ���뤫��������ϡ��������ͥ�褷��ɽ�����롣
				if (!_IsNull($tblUserStatusInfo['usr_sts_end_year']) && !_IsNull($tblUserStatusInfo['usr_sts_end_month']) && !_IsNull($tblUserStatusInfo['usr_sts_end_day'])) {
					$deadlineYmd = sprintf('%04d/%02d/%02d', $tblUserStatusInfo['usr_sts_end_year'], $tblUserStatusInfo['usr_sts_end_month'], $tblUserStatusInfo['usr_sts_end_day']);
					$deadlineYmd = mb_substr($deadlineYmd, 2, 8);
				}

				_Log("[_GetUserStatusHtml] ������ = '".$payYmd."'");
				_Log("[_GetUserStatusHtml] ������ = '".$deadlineYmd."'");
				_Log("[_GetUserStatusHtml] ������(time) = '".$payTime."'");
				_Log("[_GetUserStatusHtml] ������(time) = '".$deadlineTime."'");
				
				//��ʧ����ID
				$usrStsPayStatusId = _GetSelect($mstPayStatusList, 'update['.$usrUserId.'][usr_sts_pay_status_id]['.$tblUserStatusInfo['usr_sts_no'].']', $tblUserStatusInfo['usr_sts_pay_status_id'], '', false, '&nbsp;', 1, false, 'id', 'name');

				//����(���̾)
				$cmpCompanyName = "(̤��Ͽ)";
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
				//������(������)
				$userStatus .= "<td>";
				$userStatus .= $tblUserStatusInfo['usr_sts_create_date_yymmdd_2'];
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//������
				$userStatus .= "<td>";
				$userStatus .= $payYmd;
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//��λ��
				$userStatus .= "<td>";
				$userStatus .= $deadlineYmd;
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//����(���̾)
				$userStatus .= "<td>";
				$userStatus .= $cmpCompanyName;
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//�����ƥॳ����̾
				$userStatus .= "<td>";
				$userStatus .= $usrStsSystemCourseName;
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//�����ƥॳ��������
				$userStatus .= "<td>";
				$userStatus .= $tblUserStatusInfo['usr_sts_system_course_price'];
				$userStatus .= "</td>";
				$userStatus .= "\n";
				//��ʧ����ID
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
			$userStatus .= "<label for=\"".$sendId."\">�嵭��ʧ�������ѹ���桼�����ˤ��Τ餻���롣(�ѹ������ä���硢��������ޤ���)</label>";
			$userStatus .= "\n";
		}

//		//�桼�����˴�Ϣ���������Ҥβ��ID��������롣
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
//			//ʸ����HTML����ƥ��ƥ����Ѵ����롣
//			$relCompanyInfo = _HtmlSpecialCharsForArray($relCompanyInfo);
//			$bufRelCompany .= "������ҡ�";
//			$bufRelCompany .= "<a href=\"/user/company/info/?id=".$relCompanyInfo['cmp_company_id']."\" alt=\"�Խ�\">[�Խ�]</a>";
//			$bufRelCompany .= "<a href=\"/user/company/article/?id=".$usrUserId."\" alt=\"�괾����\">[�괾����]</a>";
//			$bufRelCompany .= "<a href=\"/user/company/pdf/?id=".$usrUserId."\" alt=\"�������\">[�������]</a>";
//			$bufRelCompany .= " ";
//			if (_IsNull($relCompanyInfo['cmp_company_name'])) {
//				$bufRelCompany .= "(���̤̾��Ͽ)";
//			} else {
//				$bufRelCompany .= $relCompanyInfo['cmp_company_name'];
//			}
//			$bufRelCompany .= "\n";
//		}
//		//�桼�����˴�Ϣ�����Ʊ��Ҥβ��ID��������롣
//		$relLlcId = _GetRelationLlcId($usrUserId, $undeleteOnly4Rel);
//		$relLlcInfo = null;
//		if (!_IsNull($relLlcId)) {
//			$condition4Rel = array();
//			$condition4Rel['cmp_company_id'] = $relLlcId;
//			$relLlcInfo = _DB_GetInfo('tbl_company', $condition4Rel, $undeleteOnly4Rel, 'cmp_del_flag');
//		}
//		if (!_IsNull($relLlcInfo)) {
//			//ʸ����HTML����ƥ��ƥ����Ѵ����롣
//			$relLlcInfo = _HtmlSpecialCharsForArray($relLlcInfo);
//			if (!_IsNull($bufRelCompany)) $bufRelCompany .= "<br />";
//			$bufRelCompany .= "��Ʊ��ҡ�";
//			$bufRelCompany .= "<a href=\"/user/llc/info/?id=".$relLlcInfo['cmp_company_id']."\" alt=\"�Խ�\">[�Խ�]</a>";
//			$bufRelCompany .= "<a href=\"/user/llc/article/?id=".$usrUserId."\" alt=\"�괾����\">[�괾����]</a>";
//			$bufRelCompany .= "<a href=\"/user/llc/pdf/?id=".$usrUserId."\" alt=\"�������\">[�������]</a>";
//			$bufRelCompany .= " ";
//			if (_IsNull($relLlcInfo['cmp_company_name'])) {
//				$bufRelCompany .= "(���̤̾��Ͽ)";
//			} else {
//				$bufRelCompany .= $relLlcInfo['cmp_company_name'];
//			}
//			$bufRelCompany .= "\n";
//		}

		$bufRelCompany = null;
		if (!_IsNull($htmlTblCompanyList)) {
			$bufRelCompany .= "����ա۰ʲ��Υ�󥯤�ʣ���֥饦�����ϡ����֤ǳ����ʤ��Ǥ���������<br />����ա��Խ��оݤβ�ҤȤ��ƾ�˳���1���Ʊ1��򥻥å������ݻ����Ƥ��ޤ���";
			$bufRelCompany .= "<br />";
			$bufRelCompany .= "\n";
			foreach ($htmlTblCompanyList as $tcKey => $tblCompanyInfo) {
				switch ($tblCompanyInfo['cmp_company_type_id']) {
					case MST_COMPANY_TYPE_ID_CMP:
						//�������
						$prm1 = "url=".rawurlencode("/user/company/info/?id=".$tblCompanyInfo['cmp_company_id'])."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$prm2 = "url=".rawurlencode("/user/company/article/?id=".$usrUserId)."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$prm3 = "url=".rawurlencode("/user/company/pdf/?id=".$usrUserId)."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$bufRelCompany .= "������ҡ�";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm1."\" alt=\"�Խ�\">[�Խ�]</a>";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm2."\" alt=\"�괾����\">[�괾����]</a>";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm3."\" alt=\"�������\">[�������]</a>";
						//��target="_blank"���դ��ʤ����ȡ���ͳ�ϡ��Խ��оݤβ��ID�Ͼ��1�Ĥˤ��뤿�ᡣ
						break;
					case MST_COMPANY_TYPE_ID_LLC:
						//��Ʊ���
						$prm1 = "url=".rawurlencode("/user/llc/info/?id=".$tblCompanyInfo['cmp_company_id'])."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$prm2 = "url=".rawurlencode("/user/llc/article/?id=".$usrUserId)."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$prm3 = "url=".rawurlencode("/user/llc/pdf/?id=".$usrUserId)."&amp;id=".$tblCompanyInfo['cmp_company_id'];
						$bufRelCompany .= "��Ʊ��ҡ�";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm1."\" alt=\"�Խ�\">[�Խ�]</a>";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm2."\" alt=\"�괾����\">[�괾����]</a>";
						$bufRelCompany .= "<a href=\"/user/set/admin.php?".$prm3."\" alt=\"�������\">[�������]</a>";
						//��target="_blank"���դ��ʤ����ȡ���ͳ�ϡ��Խ��оݤβ��ID�Ͼ��1�Ĥˤ��뤿�ᡣ
						break;
				}
				$bufRelCompany .= " ";
				if (_IsNull($tblCompanyInfo['cmp_company_name'])) {
					$bufRelCompany .= "(���̤̾��Ͽ)";
				} else {
					$bufRelCompany .= $tblCompanyInfo['cmp_company_name'];
				}
				$bufRelCompany .= "<br />";
				$bufRelCompany .= "\n";
			}
		}

//		if (!_IsNull($bufRelCompany)) $bufRelCompany .= "<br />";
		$bufRelCompany .= "�᡼�롧";
		$bufRelCompany .= $usrEMailShow;
		$bufRelCompany .= " / �ѥ���ɡ�";
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
	$mcList .= "<input class=\"submit\" type=\"submit\" name=\"go\" value=\" ������ \" />";
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

//��å�����
$mcMessage = null;
if (!_IsNull($message)) {
	$addClass = null;
	//���顼��ͭ���硢ʸ�������ѹ����롣
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


//������ץȤ����ꤹ�롣
$script = null;

$addStyle = null;

//switch ($xmlName) {
//	case XML_NAME_SEAL_SET:
//		//ˡ�Ͱ���ʸ����[����]
//		$buf = _CreateTableInput4SealSet($mode, $xmlList, $info, $tabindex);
//		$maincontent = str_replace('{form_info_seal_set}', $buf, $maincontent);
//		break;
//	case XML_NAME_SEAL_ALL:
//		//ˡ�Ͱ���ʸ����[�������Ƴ�ǧ]
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






//�����ɥ�˥塼�����ꤹ�롣
$sidebar = null;

////����URL
//$htmlSidebarLogin = str_replace('{base_url}', $basePath, $htmlSidebarLogin);
//
//$sidebar .= $htmlSidebarLogin;

//����URL
$htmlSidebarUserMenu = str_replace('{base_url}', $basePath, $htmlSidebarUserMenu);
//������桼����̾
$htmlSidebarUserMenu = str_replace('{user_name}', _GetLoginUserNameHtml($loginInfo), $htmlSidebarUserMenu);
//���ߤ����Ͼ���
$htmlSidebarUserMenu = str_replace('{company_info}', null, $htmlSidebarUserMenu);




$sidebar .= $htmlSidebarUserMenu;


//�ѥ󤯤��ꥹ�Ȥ����ꤹ�롣
_SetBreadcrumbs(PAGE_DIR_HOME, '', PAGE_TITLE_HOME, 1);
_SetBreadcrumbs(PAGE_DIR_USER, '', PAGE_TITLE_USER, 2);
_SetBreadcrumbs(PAGE_DIR_ADMIN_USER, '', PAGE_TITLE_ADMIN_USER, 3);
//�ѥ󤯤��ꥹ�Ȥ�������롣
$breadcrumbs = _GetBreadcrumbs();

//WOOROM�եå�������
$wooromFooter = @file_get_contents("http://www.woorom.com/admin/common/footer/get.php?id=17&server_name=".$_SERVER['SERVER_NAME']."&php_self=".$_SERVER['PHP_SELF']);
if ($wooromFooter === false) {
	$wooromFooter = null;
}



//�ƥ�ץ졼�Ȥ��Խ����롣(ɬ�ײս���ִ����롣)
//�����ȥ�
if (!_IsNull($title)) $title = "[".$title."] ";
$title = $siteTitle." ".$title;
$html = str_replace('{title}', $title, $html);
//�᥿����
$html = str_replace ('{keywords}', PAGE_KEYWORDS_HOME, $html);
$html = str_replace ('{description}', PAGE_DESCRIPTION_HOME, $html);
//����ƥ��
$html = str_replace('{maincontent}', $maincontent, $html);
//�����ɥ�˥塼
$html = str_replace('{sidebar}', $sidebar, $html);
//������ץ�
$html = str_replace('{script}', $script, $html);
//����URL
$html = str_replace('{base_url}', $basePath, $html);
//�ѥ󤯤��ꥹ��
$html = str_replace('{breadcrumbs}', $breadcrumbs, $html);
//WOOROM�եå�������
$html = str_replace('{woorom_footer}', $wooromFooter, $html);


_Log("[/admin/user/index.php] end.");
echo $html;

?>
