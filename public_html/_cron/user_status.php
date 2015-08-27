#!/usr/bin/php
<?php
/*
 * [���������Ω.JP �ġ���]
 * �桼������������
 *
 * ��������2011/01/11	d.ishikawa	��������
 *
 */

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/_cron/user_status.php] start.");

//�����ȥ����ȥ�
$siteTitle = SITE_TITLE;

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





_Log("[/_cron/user_status.php] �����ƥ����Ѵ���(ñ��:��{null:��������̵}) = '".SYSTEM_USE_DEADLINE."'");
if (_IsNull(SYSTEM_USE_DEADLINE)) {
	echo "�����ƥ����Ѵ���(ñ��:��{null:��������̵}) �� ���¤ʤ� �� ������λ";
	_Log("[/_cron/user_status.php] �� ���¤ʤ� �� ������λ");
	_Log("[/_cron/user_status.php] end.");
	exit;
}



//DB�򥪡��ץ󤹤롣
$cid = _DB_Open();


_Log("[/_cron/user_status.php] �桼����_�����ơ��֥�򸡺����롣");
//�桼����_�����ơ��֥�򸡺�������������ǡ���������å����롣
$condition = array();
//$condition['usr_sts_system_course_id'] = array(MST_SYSTEM_COURSE_ID_CMP, MST_SYSTEM_COURSE_ID_LLC);		//�����ƥॳ����ID="[�������] ���������Ω (�����ƥ���������)", "[��Ʊ���] ��Ʊ�����ΩLLC (�����ƥ���������)"
$condition['usr_sts_system_course_id'] = array(
MST_SYSTEM_COURSE_ID_CMP				//[�������] ���������Ω (�����ƥ���������)
,MST_SYSTEM_COURSE_ID_LLC				//[��Ʊ���] ��Ʊ�����ΩLLC (�����ƥ���������)
,MST_SYSTEM_COURSE_ID_STANDARD_CMP		//[����������ɥѡ��ȥʡ��ץ��][�������] ���������Ω (�����ƥ���������)
,MST_SYSTEM_COURSE_ID_STANDARD_LLC		//[����������ɥѡ��ȥʡ��ץ��][��Ʊ���] ��Ʊ�����ΩLLC (�����ƥ���������)
,MST_SYSTEM_COURSE_ID_PLATINUM_CMP		//[�ץ���ʥѡ��ȥʡ��ץ��][�������] ���������Ω (�����ƥ���������)
,MST_SYSTEM_COURSE_ID_PLATINUM_LLC		//[�ץ���ʥѡ��ȥʡ��ץ��][��Ʊ���] ��Ʊ�����ΩLLC (�����ƥ���������)
);										//�����ƥॳ����ID
$condition['usr_sts_pay_status_id'] = array(MST_PAY_STATUS_ID_OK);										//��ʧ����ID="�����"
$undeleteOnly = true;																					//����ե饰="̤���"�Τ�
$order = null;
$order .= "usr_sts_user_id";																				//�桼����ID�ξ���
$order .= ",usr_sts_no";																					//����No�ξ���
$tblUserStatusList = _DB_GetList('tbl_user_status', $condition, $undeleteOnly, $order, 'usr_sts_del_flag');
if (_IsNull($tblUserStatusList)) {
	echo "�桼����_�����ơ��֥� �� �����оݥǡ����ʤ� �� ������λ";
	_Log("[/_cron/user_status.php] �� �����оݥǡ����ʤ� �� ������λ");
	_Log("[/_cron/user_status.php] end.");
	exit;
}

$undeleteOnly4Mst = true;
$condition = null;
$order = "lpad(show_order,10,'0'),id";

//�����ƥॳ�����ޥ���
$mstSystemCourseList = _DB_GetList('mst_system_course', $condition, $undeleteOnly4Mst, $order, 'del_flag', 'id');
//��ʧ�����ޥ���
$mstPayStatusList = _DB_GetList('mst_pay_status', $condition, $undeleteOnly4Mst, $order, 'del_flag', 'id');



//IP���ɥ쥹�����ꤹ�롣
$ip = $_SERVER["REMOTE_ADDR"];
$date = null;

//���������顼�����󥿡�
$payDateErrorCount = 0;
//�������顼�����󥿡�
$updateErrorCount = 0;
//�����ڤ쥫���󥿡�
$endCount = 0;
//����OK�����󥿡�
$okCount = 0;

foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	_Log("[/_cron/user_status.php] ----------------------------------------------");
	_Log("[/_cron/user_status.php] �桼����ID = '".$tblUserStatusInfo['usr_sts_user_id']."'");
	_Log("[/_cron/user_status.php] ����No = '".$tblUserStatusInfo['usr_sts_no']."'");
	_Log("[/_cron/user_status.php] ���ID = '".$tblUserStatusInfo['usr_sts_company_id']."'");
	_Log("[/_cron/user_status.php] ��ʧ����ID = '".$tblUserStatusInfo['usr_sts_pay_status_id']."'");
	_Log("[/_cron/user_status.php] �����ƥॳ����ID = '".$tblUserStatusInfo['usr_sts_system_course_id']."'");
	_Log("[/_cron/user_status.php] �����ƥॳ����̾ = '".$tblUserStatusInfo['usr_sts_system_course_name']."'");
	_Log("[/_cron/user_status.php] �����ƥॳ�������� = '".$tblUserStatusInfo['usr_sts_system_course_price']."'");
	_Log("[/_cron/user_status.php] ������(ǯ) = '".$tblUserStatusInfo['usr_sts_pay_year']."'");
	_Log("[/_cron/user_status.php] ������(��) = '".$tblUserStatusInfo['usr_sts_pay_month']."'");
	_Log("[/_cron/user_status.php] ������(��) = '".$tblUserStatusInfo['usr_sts_pay_day']."'");

	//������������롣
	$nowTime = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
	$nowYear = date('Y', $nowTime);
	$nowMonth = date('n', $nowTime);
	$nowDay = date('j', $nowTime);
	$nowYmd = sprintf('%04d/%02d/%02d', $nowYear, $nowMonth, $nowDay);

	//����������Ͽ���뤫��
	if (!_IsNull($tblUserStatusInfo['usr_sts_pay_year']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_month']) && !_IsNull($tblUserStatusInfo['usr_sts_pay_day'])) {
		$payYear = $tblUserStatusInfo['usr_sts_pay_year'];
		$payMonth = $tblUserStatusInfo['usr_sts_pay_month'];
		$payDay = $tblUserStatusInfo['usr_sts_pay_day'];
		$payTime = mktime(0, 0, 0, $payMonth, $payDay, $payYear);
		$payYmd = sprintf('%04d/%02d/%02d', $payYear, $payMonth, $payDay);
		//��������N������������롣
		$deadlineTime = mktime(0, 0, 0, $payMonth, $payDay + SYSTEM_USE_DEADLINE, $payYear);
		$deadlineYear = date('Y', $deadlineTime);
		$deadlineMonth = date('n', $deadlineTime);
		$deadlineDay = date('j', $deadlineTime);
		$deadlineYmd = sprintf('%04d/%02d/%02d', $deadlineYear, $deadlineMonth, $deadlineDay);

		_Log("[/_cron/user_status.php] ������ = '".$payYmd."'");
		_Log("[/_cron/user_status.php] ������ = '".$deadlineYmd."'");
		_Log("[/_cron/user_status.php] �ܡ��� = '".$nowYmd."'");
		_Log("[/_cron/user_status.php] ������(time) = '".$payTime."'");
		_Log("[/_cron/user_status.php] ������(time) = '".$deadlineTime."'");
		_Log("[/_cron/user_status.php] �ܡ���(time) = '".$nowTime."'");

		$tblUserStatusInfo['_deadline_date_'] = $deadlineYmd;
		//��񤭤��롣
		$tblUserStatusList[$tusKey] = $tblUserStatusInfo;

		_Log("[/_cron/user_status.php] ���¥����å�");
		if ($nowTime <= $deadlineTime) {
			_Log("[/_cron/user_status.php] �� OK");
			$okCount++;
			$tblUserStatusInfo['_ok_flag_'] = true;
			//��񤭤��롣
			$tblUserStatusList[$tusKey] = $tblUserStatusInfo;
		} else {
			_Log("[/_cron/user_status.php] �� ��NG��");
			_Log("[/_cron/user_status.php] �桼����_�����ơ��֥�˹������롣");
			//�桼����_�����ơ��֥�˹������롣
			$bufInfo = array();
			$bufInfo['usr_sts_user_id'] = $tblUserStatusInfo['usr_sts_user_id'];		//�桼����ID
			$bufInfo['usr_sts_no'] = $tblUserStatusInfo['usr_sts_no'];					//����No
			$bufInfo['usr_sts_pay_status_id'] = MST_PAY_STATUS_ID_END;					//��ʧ����ID="�����ڤ�"
			$bufInfo['usr_sts_end_year'] = $deadlineYear;								//��λ��(ǯ)
			$bufInfo['usr_sts_end_month'] = $deadlineMonth;								//��λ��(��)
			$bufInfo['usr_sts_end_day'] = $deadlineDay;									//��λ��(��)
			$bufInfo['usr_sts_update_ip'] = $ip;										//����IP
			$bufInfo['usr_sts_update_date'] = null;										//������
			$resDb = _DB_SaveInfo('tbl_user_status', $bufInfo);
			if ($resDb === false) {
				_Log("[/_cron/user_status.php] ���ڼ��ԡ�");
				_Log("[/_cron/user_status.php] {ERROR} �桼����_�����ơ��֥빹���˼��Ԥ��ޤ�����_DB_SaveInfo('tbl_user_status', xxx) ", 1);
				_Log("[/_cron/user_status.php] {ERROR} �桼����_�����ơ��֥���� = '".print_r($bufInfo,true)."'", 1);
				$updateErrorCount++;
				$tblUserStatusInfo['_update_error_flag_'] = true;
				//��񤭤��롣
				$tblUserStatusList[$tusKey] = $tblUserStatusInfo;
			} else {
				_Log("[/_cron/user_status.php] ����������");
				$endCount++;
				//�ǿ������������롣
				$condition = array();
				$condition['usr_sts_user_id'] = $tblUserStatusInfo['usr_sts_user_id'];		//�桼����ID
				$condition['usr_sts_no'] = $tblUserStatusInfo['usr_sts_no'];				//����No
				$undeleteOnly = false;														//����ե饰=̵��
				$newInfo = _DB_GetInfo('tbl_user_status', $condition, $undeleteOnly, 'usr_sts_del_flag');
				$newInfo['_end_flag_'] = true;
				$newInfo['_deadline_date_'] = $deadlineYmd;
				//��񤭤��롣
				$tblUserStatusList[$tusKey] = $newInfo;
			}
		}
	} else {
		_Log("[/_cron/user_status.php] ��������̤���� �� ��NG��");
		$payDateErrorCount++;
		$tblUserStatusInfo['_pay_date_error_flag_'] = true;
		//��񤭤��롣
		$tblUserStatusList[$tusKey] = $tblUserStatusInfo;
	}



}

$bodyAll = null;

//������������桼�����˥᡼�뤹�롣
foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	if (!isset($tblUserStatusInfo['_end_flag_'])) continue;
	if (!$tblUserStatusInfo['_end_flag_']) continue;
	//�桼���������������롣
	$userInfo = _GetUserInfo($tblUserStatusInfo['usr_sts_user_id']);
	//���������Ω�����������롣
	$companyInfo = _GetCompanyInfo($tblUserStatusInfo['usr_sts_company_id']);


	//�᡼����ʸ�ζ�����ʬ�����ꤹ�롣
	$body = null;

	$body .= "��������������������������������������������������������\n";
	$body .= "�桼��������\n";
	$body .= "��������������������������������������������������������\n";
//	$body .= "�桼����ID��";
//	$body .= $userInfo['tbl_user']['usr_user_id'];
//	$body .= "\n";
	$body .= "��̾����";
	$body .= $userInfo['tbl_user']['usr_family_name'];
	$body .= " ";
	$body .= $userInfo['tbl_user']['usr_first_name'];
	$body .= "\n";
	$body .= "�᡼�륢�ɥ쥹��";
	$body .= $userInfo['tbl_user']['usr_e_mail'];
	$body .= "\n";
	$body .= "\n";

	$body .= "��������������������������������������������������������\n";
	$body .= "��Ҿ���\n";
	$body .= "��������������������������������������������������������\n";
	$body .= "����(���̾)��";
	$body .= $companyInfo['tbl_company']['cmp_company_name'];
	$body .= "\n";
	$body .= "\n";

	$body .= "��������������������������������������������������������\n";
	$body .= "�����ѥ���������\n";
	$body .= "��������������������������������������������������������\n";
	$body .= "�����ѥ�������";
	$body .= $mstSystemCourseList[$tblUserStatusInfo['usr_sts_system_course_id']]['name'];
	$body .= "\n";
	$body .= "�����ѥ��ơ�������";
	$body .= $mstPayStatusList[$tblUserStatusInfo['usr_sts_pay_status_id']]['name'];
	$body .= "\n";
	$body .= "�����ƥऴ���ѳ�������";
	$body .= sprintf('%04d/%02d/%02d', $tblUserStatusInfo['usr_sts_pay_year'], $tblUserStatusInfo['usr_sts_pay_month'], $tblUserStatusInfo['usr_sts_pay_day']);
	$body .= "\n";
	$body .= "�����ƥऴ���ѽ�λ����";
	$body .= sprintf('%04d/%02d/%02d', $tblUserStatusInfo['usr_sts_end_year'], $tblUserStatusInfo['usr_sts_end_month'], $tblUserStatusInfo['usr_sts_end_day']);
	$body .= "\n";
	$body .= "�����ƥऴ���Ѳ�ǽ���֡�";
	$body .= SYSTEM_USE_DEADLINE."���� (".floor(SYSTEM_USE_DEADLINE / 30)."�����)";
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
	$adminBody .= "��".$siteTitle."�٤����ѥ��ơ�������ư�������\n";
	$adminBody .= "**************************************************************************************\n";
	$adminBody .= "\n";
	$adminBody .= $body;

	//�������ѥ᡼����ʸ�����ꤹ�롣
	$customerBody = "";
	$customerBody .= $userInfo['tbl_user']['usr_family_name']." ".$userInfo['tbl_user']['usr_first_name']." ��\n";
	$customerBody .= "\n";
	$customerBody .= "**************************************************************************************\n";
	$customerBody .= "���Ĥ⡢��".$siteTitle."�٤Τ����Ѥ��꤬�Ȥ��������ޤ���\n";
	$customerBody .= "�����ѥ������δ��¤��ڤ�ޤ����Τǡ����Τ餻�������ޤ���\n";
	$customerBody .= "��³���Ƥ����Ѥ������ϡ��֤���������Τ���ʧ���ץե����फ��\n";
	$customerBody .= "���٤������ߡ�����ʧ���򤪴ꤤ�������ޤ���\n";
	$customerBody .= "**************************************************************************************\n";
	$customerBody .= "\n";
	$customerBody .= $body;


	//�������ѥ����ȥ�����ꤹ�롣
	$adminTitle = "[".$siteTitle."] �����ѥ����������ڤ� (".$userInfo['tbl_user']['usr_family_name']." ".$userInfo['tbl_user']['usr_first_name']." �� / ".$companyInfo['tbl_company']['cmp_company_name'].")";
	//�������ѥ����ȥ�����ꤹ�롣
	$customerTitle = "[".$siteTitle."] �����ѥ����������ڤ�Τ��Τ餻 (".$companyInfo['tbl_company']['cmp_company_name'].")";

	mb_language("Japanese");

	$parameter = "-f ".$clientMail;

	//�᡼������
	//�����ͤ��������롣
	$rcd = mb_send_mail($userInfo['tbl_user']['usr_e_mail'], $customerTitle, $customerBody, "from:".$clientMail, $parameter);

	//���饤����Ȥ��������롣
	$rcd = mb_send_mail($clientMail, $adminTitle, $adminBody, "from:".$clientMail);

	//�ޥ��������������롣
	foreach($masterMailList as $masterMail){
		$rcd = mb_send_mail($masterMail, $adminTitle, $adminBody, "from:".$clientMail);
	}
}




//cron����᡼���������롣
$buf = null;
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= __FILE__;
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "���������顼�����󥿡� = '".$payDateErrorCount."'";
if ($payDateErrorCount > 0) {
	$buf .= " ";
	$buf .= "��ERROR��";
}
$buf .= "\n";
$buf .= "�������顼�����󥿡� = '".$updateErrorCount."'";
if ($updateErrorCount > 0) {
	$buf .= " ";
	$buf .= "��ERROR��";
}
$buf .= "\n";
$buf .= "�����ڤ쥫���󥿡� = '".$endCount."'";
$buf .= "\n";
$buf .= "����OK�����󥿡� = '".$okCount."'";
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "\n";
$buf .= "���������顼(��������̤����)��";
$buf .= "\n";
foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	if (isset($tblUserStatusInfo['_pay_date_error_flag_']) && $tblUserStatusInfo['_pay_date_error_flag_']) {
		$buf .= "-----------------------------------------";
		$buf .= "\n";
		$buf .= "�桼����ID = '".$tblUserStatusInfo['usr_sts_user_id']."'";
		$buf .= "\n";
		$buf .= "����No = '".$tblUserStatusInfo['usr_sts_no']."'";
		$buf .= "\n";
		$buf .= "���ID = '".$tblUserStatusInfo['usr_sts_company_id']."'";
		$buf .= "\n";
		$buf .= "�����ƥॳ����ID = '".$tblUserStatusInfo['usr_sts_system_course_id']."'";
		$buf .= "\n";
		$buf .= "��ʧ����ID = '".$tblUserStatusInfo['usr_sts_pay_status_id']."'";
		$buf .= "\n";
	}
}
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "\n";
$buf .= "�������顼��";
$buf .= "\n";
foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	if (isset($tblUserStatusInfo['_update_error_flag_']) && $tblUserStatusInfo['_update_error_flag_']) {
		$buf .= "-----------------------------------------";
		$buf .= "\n";
		$buf .= "�桼����ID = '".$tblUserStatusInfo['usr_sts_user_id']."'";
		$buf .= "\n";
		$buf .= "����No = '".$tblUserStatusInfo['usr_sts_no']."'";
		$buf .= "\n";
		$buf .= "���ID = '".$tblUserStatusInfo['usr_sts_company_id']."'";
		$buf .= "\n";
		$buf .= "�����ƥॳ����ID = '".$tblUserStatusInfo['usr_sts_system_course_id']."'";
		$buf .= "\n";
		$buf .= "��ʧ����ID = '".$tblUserStatusInfo['usr_sts_pay_status_id']."'";
		$buf .= "\n";
		$buf .= "������ = '".$tblUserStatusInfo['_deadline_date_']."'";
		$buf .= "\n";
	}
}
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "\n";
$buf .= "�����ڤ졧";
$buf .= "\n";
foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	if (isset($tblUserStatusInfo['_end_flag_']) && $tblUserStatusInfo['_end_flag_']) {
		$buf .= "-----------------------------------------";
		$buf .= "\n";
		$buf .= "�桼����ID = '".$tblUserStatusInfo['usr_sts_user_id']."'";
		$buf .= "\n";
		$buf .= "����No = '".$tblUserStatusInfo['usr_sts_no']."'";
		$buf .= "\n";
		$buf .= "���ID = '".$tblUserStatusInfo['usr_sts_company_id']."'";
		$buf .= "\n";
		$buf .= "�����ƥॳ����ID = '".$tblUserStatusInfo['usr_sts_system_course_id']."'";
		$buf .= "\n";
		$buf .= "��ʧ����ID = '".$tblUserStatusInfo['usr_sts_pay_status_id']."'";
		$buf .= "\n";
		$buf .= "������ = '".$tblUserStatusInfo['_deadline_date_']."'";
		$buf .= "\n";
	}
}
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "\n";
$buf .= "����OK��";
$buf .= "\n";
foreach ($tblUserStatusList as $tusKey => $tblUserStatusInfo) {
	if (isset($tblUserStatusInfo['_ok_flag_']) && $tblUserStatusInfo['_ok_flag_']) {
		$buf .= "-----------------------------------------";
		$buf .= "\n";
		$buf .= "�桼����ID = '".$tblUserStatusInfo['usr_sts_user_id']."'";
		$buf .= "\n";
		$buf .= "����No = '".$tblUserStatusInfo['usr_sts_no']."'";
		$buf .= "\n";
		$buf .= "���ID = '".$tblUserStatusInfo['usr_sts_company_id']."'";
		$buf .= "\n";
		$buf .= "�����ƥॳ����ID = '".$tblUserStatusInfo['usr_sts_system_course_id']."'";
		$buf .= "\n";
		$buf .= "��ʧ����ID = '".$tblUserStatusInfo['usr_sts_pay_status_id']."'";
		$buf .= "\n";
		$buf .= "������ = '".$tblUserStatusInfo['_deadline_date_']."'";
		$buf .= "\n";
	}
}
$buf .= "\n";
$buf .= "--------------------------------------------------------------------------";
$buf .= "\n";
$buf .= "\n";
$buf .= "�����᡼����ʸ��";
$buf .= "\n";
$buf .= $bodyAll;
$buf .= "\n";

echo $buf;
_Log("[/_cron/user_status.php] cron�᡼�� = \n'".$buf."\n'");


//DB���Ĥ���
_DB_Close($cid);

_Log("[/_cron/user_status.php] end.");
?>
