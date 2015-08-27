<?php
/*
 * [��ߥ�󥯴�������]
 * ��ߥ�󥯥����Ȱ�������
 *
 * ��������2007/10/01	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/site/index.php] start.");

//�ܥե������̾�Τ�������롣
$phpName = basename($_SERVER['PHP_SELF']);
//�ե�����Υ������������ꤹ�롣
//$formAction = SSL_URL_THE_LIFEBOAT_COM_INQ.'/'.$phpName;
$formAction = $_SERVER['PHP_SELF'];

//�̾��URL(SSL�ǤϤʤ�URL)
$urlBase = URL_BASE;

//������̾
$clientName = ADMIN_TITLE;

//���֥���ǥå���
$tabindex = 0;

//DB�򥪡��ץ󤹤롣
$cid = _DB_Open();



//�ޥ��������������롣
$undeleteOnly = false;
$mstOwnSiteList = _GetMasterList('mst_own_site', $undeleteOnly);			//���ҥ����ȥޥ���
$mstGenreList = _GetMasterList('mst_genre', $undeleteOnly);				//������ޥ���
$mstLinkStatusList = _GetMasterList('mst_link_status', $undeleteOnly);	//��󥯾����ޥ���

$yearList = _GetYearArray(SYSTEM_START_YEAR, date('Y') + 3);				//ǯ
$monthList = _GetMonthArray();												//��
$dayList = _GetDayArray();													//��
//����ե饰
$delFlagList = array(
			 DELETE_FLAG_NO => array('id' => DELETE_FLAG_NO, 'name' => DELETE_FLAG_NO_NAME, 'no_name' => '')
			,DELETE_FLAG_YES => array('id' => DELETE_FLAG_YES, 'name' => DELETE_FLAG_YES_NAME, 'no_name' => '')
			);
//��Ϣ�դ��ե饰
$siteRelationFlagList = array(
			 SITE_RELATION_FLAG_YES => array('id' => SITE_RELATION_FLAG_YES, 'name' => SITE_RELATION_FLAG_YES_NAME, 'no_name' => '')
			,SITE_RELATION_FLAG_NO => array('id' => SITE_RELATION_FLAG_NO, 'name' => SITE_RELATION_FLAG_NO_NAME, 'no_name' => '')
			);
//��ߥ�󥯥����Ȱ���ɽ���⡼��
$siteListModeList = array(
			 SITE_LIST_MODE_NORMAL => array('id' => SITE_LIST_MODE_NORMAL, 'name' => SITE_LIST_MODE_NORMAL_NAME, 'no_name' => '')
			,SITE_LIST_MODE_MATRIX => array('id' => SITE_LIST_MODE_MATRIX, 'name' => SITE_LIST_MODE_MATRIX_NAME, 'no_name' => '')
			);


//ư��⡼��{1:����/2:��λ(����)/3:���顼}
$mode = 1;

//ǧ�ڥ����å�
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/site/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/site/index.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ../");
	exit;
}
//����������������롣
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//��å�����
$message = "";
//��å�����
$message4js = "";


//�䤤��碌������Ǽ��������
$info = array();
//����ͤ����ꤹ�롣
//[���ҥ����ȸ������]
$info['condition']['own_site_del_flag'] = array(DELETE_FLAG_NO);					//���ҥ�����.����ե饰="̤���"
//[��ߥ�󥯥����ȸ������]
$info['condition']['site_del_flag'] = array(DELETE_FLAG_NO);						//��ߥ�󥯥�����.����ե饰="̤���"
//[��󥯾����������]
$info['condition']['site_relation_flag'] = array(SITE_RELATION_FLAG_YES);			//��Ϣ�դ�����="��"
$info['condition']['site_relation_link_status_id'] = MST_LINK_STATUS_ID_SENT;		//��󥯾���="�ǿǥ᡼�������Ѥ�"
$info['condition']['site_relation_total_days_from'] = 14;							//�����Ͽ��="14���ʾ�(2���ַв�)"


//����ܥ��󤬲����줿���
if ($_POST['select'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
	_Log("[/site/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/site/index.php] POST(�Хå�����å���������) = '".print_r($info,true)."'");
	
	//�������Ѵ�
	//�����ѡ׿������Ⱦ�ѡפ��Ѵ����롣------------------------------------------------------------
	//��Ͽ����From
	$info['condition']['site_relation_total_days_from'] = mb_convert_kana($info['condition']['site_relation_total_days_from'], "n");
	//��Ͽ����To
	$info['condition']['site_relation_total_days_to'] = mb_convert_kana($info['condition']['site_relation_total_days_to'], "n");
	
	//�����ͥ����å�
	//Ⱦ�ѿ��������å�------------------------------------------------------------------------------
	//��Ͽ����From
	if (!_IsHalfSizeNumeric($info['condition']['site_relation_total_days_from'])) $info['condition']['site_relation_total_days_from'] = null;
	//��Ͽ����To
	if (!_IsHalfSizeNumeric($info['condition']['site_relation_total_days_to'])) $info['condition']['site_relation_total_days_to'] = null;
	
	
	//��ߥ�󥯤򸡺����롣
	$order = null;
	$siteList = _GetSite($info['condition'], $order, false);
	
	if (_IsNull($siteList)) {
		$message = "�������˳����������¸�ߤ��ޤ���";
	}
	
	//������̤򥻥å�������¸���롣
	$_SESSION[SID_SRCH_SITE_LIST] = $siteList;
	
//�����ܥ��󤬲����줿���
} elseif ($_POST['go'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
	_Log("[/site/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/site/index.php] POST(�Хå�����å���������) = '".print_r($info,true)."'");
//	//�����ѡ׿������Ⱦ�ѡפ��Ѵ����롣
//	$info = _Mb_Convert_KanaForArray($info, 'n');
//	_Log("[/site/index.php] POST(�����ѡ׿������Ⱦ�ѡפ��Ѵ�����) = '".print_r($info,true)."'");

	//�����ͥ����å�

	//���å���󤫤鸡����̤�������롣
	$siteList = $_SESSION[SID_SRCH_SITE_LIST];

	if (isset($info['update'])) {
		_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} -------------------- ����");
		$count = 0;
		foreach ($info['update'] as $key => $updateInfo) {
			$count++;
			
			//��Ͽ�������������˺ǿ���DB����Ǹ�����̤��񤭤��롣{true:��񤭤���/false:���ʤ�}
			$overwriteFlag = false;

			_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} key = '".$key."' ================================");
			_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} (��������)��Ϣ�դ��ե饰 = '".$updateInfo['site_relation_flag']."'");
			_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} (��������)��󥯾���ID = '".$updateInfo['link_status_id']."'");

			//�������ξ����������롣
			$oldInfo = $siteList[$key];
			_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} (DB����)��Ϣ�դ��ե饰 = '".$oldInfo['site_relation_flag']."'");
			_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} (DB����)��󥯾���ID = '".$oldInfo['link_status_id']."'");

			
			//��Ϣ�դ��ե饰������å����롣
			if (isset($updateInfo['site_relation_flag'])) {
				//��Ϣ�դ��ե饰=ON�ξ��

				_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1.��Ϣ�դ��ե饰=ON�ξ��");


				if ($oldInfo['site_relation_flag'] == SITE_RELATION_FLAG_YES) {
					//��������Ϣ�դ��Ѥξ��
					_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1-1.��������Ϣ�դ��Ѥξ��");

					//�ѹ�̵ͭ������å����롣
					$updateFlag = false;
					//��󥯾���
					if ($oldInfo['link_status_id'] != $updateInfo['link_status_id']) $updateFlag = true;
					
					if ($updateFlag) {
						_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1-1-1.�ѹ�ͭ��---�������롣");
						
						//����������ɲ����ꤹ�롣
						$updateInfo['del_flag'] = DELETE_FLAG_NO;				//����ե饰
						$updateInfo['update_ip'] = $_SERVER["REMOTE_ADDR"];	//����IP
						$updateInfo['update_date'] = null;					//������
						
						//�������롣
						$res = _DB_SaveInfo('tbl_site_relation', $updateInfo);
						if ($res === false) {
							$message .= "No."._FormatNo($count)."�ι����˼��Ԥ��ޤ��������ټ¹Ԥ򤪴ꤤ���ޤ���\n";
							_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1-1-1-2.�ڹ������ԡ�");
						} else {
							$message .= "No."._FormatNo($count)."�򹹿����ޤ�����\n";
							_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1-1-1-1.�ڹ���������");
							
							$overwriteFlag = true;
						}
					} else {
						_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1-1-2.�ѹ�̵��---����̵�������ء�");
					}

				} else {
					//������̤��Ϣ�դ��ξ��
					_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1-2.������̤��Ϣ�դ��ξ��---������Ͽ���롣");
					
					//��󥯾�����̤����ξ�硢����ͤȤ���"���ҥ����ȷǺܺѤ�"(1)�����ꤹ�롣
					if (_IsNull($updateInfo['link_status_id'])) $updateInfo['link_status_id'] = MST_LINK_STATUS_ID_CARRIED;

					//����������ɲ����ꤹ�롣
					$updateInfo['del_flag'] = DELETE_FLAG_NO;				//����ե饰
					$updateInfo['create_ip'] = $_SERVER["REMOTE_ADDR"];	//����IP
					$updateInfo['create_date'] = null;					//������					
					$updateInfo['update_ip'] = $_SERVER["REMOTE_ADDR"];	//����IP
					$updateInfo['update_date'] = null;					//������					
					
					//��Ͽ���롣
					$res = _DB_CreateInfo('tbl_site_relation', $updateInfo);
					if ($res === false) {
						$message .= "No."._FormatNo($count)."����Ͽ�˼��Ԥ��ޤ��������ټ¹Ԥ򤪴ꤤ���ޤ���\n";
						_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1-2-2.����Ͽ���ԡ�");
					} else {
						$message .= "No."._FormatNo($count)."����Ͽ���ޤ�����\n";
						_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 1-2-1.����Ͽ������");
						
						$overwriteFlag = true;
					}
				}

			} else {
				//��Ϣ�դ��ե饰=OFF�ξ��

				_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 2.��Ϣ�դ��ե饰=OFF�ξ��");

				if ($oldInfo['site_relation_flag'] == SITE_RELATION_FLAG_YES) {
					//��������Ϣ�դ��Ѥξ��
					_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 2-1.��������Ϣ�դ��Ѥξ��---������롣");

					//���������ɲ����ꤹ�롣(�祭���Τ�)
					$deleteInfo = array();
					$deleteInfo['site_id'] = $updateInfo['site_id'];				//��ߥ�󥯥�����ID
					$deleteInfo['own_site_id'] = $updateInfo['own_site_id'];		//���ҥ�����ID
					
					//������롣
					$res = _DB_DeleteInfo('tbl_site_relation', $deleteInfo);
					if ($res === false) {
						$message .= "No."._FormatNo($count)."�κ���˼��Ԥ��ޤ��������ټ¹Ԥ򤪴ꤤ���ޤ���\n";
						_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 2-1-2.�ں�����ԡ�");
					} else {
						$message .= "No."._FormatNo($count)."�������ޤ�����\n";
						_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 2-1-1.�ں��������");
						
						$overwriteFlag = true;
					}
				} else {
					//������̤��Ϣ�դ��ξ��
					_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 2-2.������̤��Ϣ�դ��ξ��---���̵�������ء�");
				}
			}
			
			//��Ͽ����������������ä���硢�ǿ���DB����Ǹ�����̤��񤭤��롣
			if ($overwriteFlag) {
				_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 3.��Ͽ����������������ä���硢�ǿ���DB����Ǹ�����̤��񤭤��롣");
				
				$condition4new = array();
				$condition4new['site_id'] = $updateInfo['site_id'];//��ߥ�󥯥�����ID
				$condition4new['own_site_id'] = $updateInfo['own_site_id'];//���ҥ�����ID
				$newSiteList = _GetSite($condition4new, null, false);
				
				if (_IsNull($newSiteList)) {
					_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 3-2.�ھ�񤭼��ԡ�");
				} else {
					$siteList[$key] = $newSiteList[0];
					_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} 3-1.�ھ��������");
				}
				

				//��񤭤���������̤򥻥å�������¸���롣
				$_SESSION[SID_SRCH_SITE_LIST] = $siteList;
			}
			
		}
		
		_Log("[/site/index.php] {���ҥ�����-��ߥ�󥯥����ȴ�Ϣ��} -------------------- ��λ");

		//���å���󤫤鸡����̤�Ƽ������롣
		$siteList = $_SESSION[SID_SRCH_SITE_LIST];

		//�����ޤǤǥ�å����������ξ�硢��Ͽ��������������ʤ��ä���
		if (_IsNull($message)) {
			$message = "�ѹ��ս꤬����ޤ���";
		}

	}

//������說�ꥢ�ܥ��󤬲����줿���
} elseif ($_POST['clear'] != "") {
	unset($info['condition']);
	unset($_SESSION[SID_SRCH_SITE_LIST]);
//���ư��
} else {
	//��ߥ�󥯤򸡺����롣
	$order = null;
	$siteList = _GetSite($info['condition'], $order, false);
	
	if (_IsNull($siteList)) {
		$message = "�������˳����������¸�ߤ��ޤ���";
	}
	
	//������̤򥻥å�������¸���롣
	$_SESSION[SID_SRCH_SITE_LIST] = $siteList;
}



//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
////ʸ����HTML����ƥ��ƥ����Ѵ����롣
//$siteList = _HtmlSpecialCharsForArray($siteList);

//echo ("\$info='".print_r($info,true)."'");


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

	<div id="maincontent">
		<h2>��ߥ�󥯥����Ȱ���</h2>
		<h3>�������</h3>
		
		<form id="frmSelect" name="frmSelect" action="<?=$_SERVER['PHP_SELF']?>" method="post">
			<h4 id="ownSite">[���ҥ����ȸ������]</h4>
			<table class="siteConditionTable">
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				
				<tr>
					<td class="colHead">
						�����ȥ����ȥ�
					</td>
					<td>
						<input type="text" name="condition[own_site_title]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['own_site_title']?>" />
					</td>
					<td class="colHead" rowspan="3">
						���ҥ�����
					</td>
					<td rowspan="3">
						<?_WriteSelect($mstOwnSiteList, 'condition[own_site_id]', $info['condition']['own_site_id'], (++$tabindex), false, "&nbsp;", 4, true, 'id', 'name_del_2', 'id', 'class="multiple"');?>
					</td>
					<td class="colHead" rowspan="3">
						������
					</td>
					<td rowspan="3">
						<?_WriteSelect($mstGenreList, 'condition[own_site_genre_id]', $info['condition']['own_site_genre_id'], (++$tabindex), false, "&nbsp;", 4, true, 'id', 'name_del_2', 'id', 'class="multiple"');?>
					</td>
				</tr>
				<tr>
					<td class="colHead">
						������URL
					</td>
					<td>
						<input type="text" name="condition[own_site_url]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['own_site_url']?>" />
					</td>
				</tr>
				<tr>
					<td class="colHead">
						����ե饰
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($delFlagList, 'condition[own_site_del_flag]', $info['condition']['own_site_del_flag'], (++$tabindex));?>
					</td>
				</tr>
			</table>

			<h4 id="linkSite">[��ߥ�󥯥����ȸ������]</h4>
			<table class="siteConditionTable">
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
								
				<tr>
					<td class="colHead">
						�����ȥ����ȥ�
					</td>
					<td>
						<input type="text" name="condition[site_title]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['site_title']?>" />
					</td>
					<td class="colHead">
						ô����̾
					</td>
					<td>
						<input type="text" name="condition[site_staff_name]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['site_staff_name']?>" />
					</td>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td class="colHead">
						������URL
					</td>
					<td>
						<input type="text" name="condition[site_url]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['site_url']?>" />
					</td>
					<td class="colHead">
						����ե饰
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($delFlagList, 'condition[site_del_flag]', $info['condition']['site_del_flag'], (++$tabindex));?>
					</td>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
			</table>
			
			<h4 id="linkStatus">[��󥯾����������]</h4>
			<table class="siteConditionTable">
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
								
				<tr>
					<td class="colHead">
						��Ϣ�դ�����
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($siteRelationFlagList, 'condition[site_relation_flag]', $info['condition']['site_relation_flag'], (++$tabindex));?>
					</td>
					<td class="colHead" rowspan="3">
						��Ͽ��
					</td>
					<td rowspan="3">
						<?_WriteSelect($yearList, 'condition[site_relation_create_date_year_from]', $info['condition']['site_relation_create_date_year_from'], (++$tabindex), true, 'ǯ');?>
						<?_WriteSelect($monthList, 'condition[site_relation_create_date_month_from]', $info['condition']['site_relation_create_date_month_from'], (++$tabindex), true, '��');?>
						<?_WriteSelect($dayList, 'condition[site_relation_create_date_day_from]', $info['condition']['site_relation_create_date_day_from'], (++$tabindex), true, '��');?>
						<br />
						��
						<br />
						<?_WriteSelect($yearList, 'condition[site_relation_create_date_year_to]', $info['condition']['site_relation_create_date_year_to'], (++$tabindex), true, 'ǯ');?>
						<?_WriteSelect($monthList, 'condition[site_relation_create_date_month_to]', $info['condition']['site_relation_create_date_month_to'], (++$tabindex), true, '��');?>
						<?_WriteSelect($dayList, 'condition[site_relation_create_date_day_to]', $info['condition']['site_relation_create_date_day_to'], (++$tabindex), true, '��');?>
					</td>
					<td class="colHead" rowspan="3">
						��󥯾���
					</td>
					<td rowspan="3">
						<?_WriteSelect($mstLinkStatusList, 'condition[site_relation_link_status_id]', $info['condition']['site_relation_link_status_id'], (++$tabindex), false, "&nbsp;", 4, true, 'id', 'name_del_2', 'id', 'class="multiple"');?>
					</td>
				</tr>
				<tr>
					<td class="colHead">
						��Ͽ����
					</td>
					<td>
						<input type="text" name="condition[site_relation_total_days_from]" size="4" maxlength="4" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['site_relation_total_days_from']?>" />
						��
						��
						<input type="text" name="condition[site_relation_total_days_to]" size="4" maxlength="4" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['site_relation_total_days_to']?>" />
						��
					</td>
				</tr>
				<tr>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
			</table>

			<h4 id="listMode">[ɽ�����]</h4>
			<table class="siteConditionTable">
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>

				<tr>
					<td class="colHead">
						ɽ���⡼��
					</td>
					<td colspan="5">
						<?$tabindex = _WriteRadio($siteListModeList, 'condition[site_list_mode]', $info['condition']['site_list_mode'], (++$tabindex));?>
					</td>
				</tr>

			</table>

			<div class="button">
				<input class="submit" type="submit" name="select" value="����������" tabindex="<?=(++$tabindex)?>" />
				&nbsp;
				<input class="submit" type="submit" name="clear" value="�����ꥢ��" tabindex="<?=(++$tabindex)?>" />
			</div>
		</form>

<?
//����������ϡ���å�������¸�ߤ����硢���Ф���ɽ�����롣
if (!_IsNull($siteList) || !_IsNull($message)) {
?>		
		<h3>�������</h3>
<?
}
?>

<?
if (!_IsNull($message)) {
	$addClass = null;
	//���顼��ͭ���硢ʸ�������ѹ����롣
	if ($errorFlag) $addClass = "errorMessage";
?>
		<div class="message <?=$addClass?>">
			<?=nl2br($message)?>
		</div>
<?
}
?>

<?
if (!_IsNull($siteList)) {
?>	
		<form id="frmUpdate" name="frmUpdate" action="<?=$_SERVER['PHP_SELF']?>" method="post">
			<div class="formWrapper">
				<div class="formList">
					<table id="siteListTable">
						<colgroup class="colWidth01"></colgroup>
						<colgroup class="colWidth02"></colgroup>
						<colgroup class="colWidth03"></colgroup>
						<colgroup class="colWidth04"></colgroup>
						<colgroup class="colWidth05"></colgroup>
						<colgroup class="colWidth06"></colgroup>
						<colgroup class="colWidth07"></colgroup>
						<colgroup class="colWidth08"></colgroup>
						<colgroup class="colWidth09"></colgroup>
						<colgroup class="colWidth10"></colgroup>

						<thead>
							<tr>
								<td rowspan="2">No</td>
								<td class="colOwnSite" rowspan="2">���ҥ�����</td>
								<td class="colLinkSite" colspan="3">��ߥ�󥯥�����</td>
								<td class="colLinkStatus" colspan="5">��󥯾���</td>
							</tr>
							<tr>
								<td class="colLinkSite">�����ȥ����ȥ�</td>
								<td class="colLinkSite">������URL</td>
								<td class="colLinkSite">ô����̾</td>
								<td class="colLinkStatus" colspan="2">��Ϣ��(��Ͽ��)</td>
								<td class="colLinkStatus">����</td>
								<td class="colLinkStatus">��󥯾���</td>
								<td class="colLinkStatus">&nbsp;</td>
							</tr>
						</thead>

						<tbody>
<?
	$count = 0;
	foreach ($siteList as $key => $siteInfo) {
		$count++;
		
		$rowColorClass = null;
		if ($count % 2 == 0) {
			$rowColorClass = 'rowColor02';
		} else {
			$rowColorClass = 'rowColor01';
		}

		//�ʲ��ι��ܤϡ�ʸ������û������HTML����ƥ��ƥ����Ѵ����롣
		//���ҥ����ȥ����ȥ�
		$ownSiteTitle = _SubStr($siteInfo['own_site_title'], 10);
		$ownSiteTitle = htmlspecialchars($ownSiteTitle);
		//��ߥ�󥯥����ȥ����ȥ�
		$siteTitle = _SubStr($siteInfo['title'], 10);
		$siteTitle = htmlspecialchars($siteTitle);
		//��ߥ�󥯥�����URl
		$siteUrl = _SubStr($siteInfo['url'],20);
		$siteUrl = htmlspecialchars($siteUrl);
		//��ߥ�󥯥�����ô����
		$siteStaffName = _SubStr($siteInfo['family_name']." ".$siteInfo['first_name'], 6);
		$siteStaffName = htmlspecialchars($siteStaffName);
		
		//ʸ����HTML����ƥ��ƥ����Ѵ����롣
		$siteInfo = _HtmlSpecialCharsForArray($siteInfo);
		
?>
							<tr class="<?=$rowColorClass?>">
								<td class="colWidth01 colCenter"><?=_FormatNo($count)?></td>
								<td class="colWidth02" title="<?=$siteInfo['own_site_title']?>"><?=$ownSiteTitle?></td>
								<td class="colWidth03" title="<?=$siteInfo['title']?>"><a href="../master/?mst_name=<?=TBL_NAME_SITE?>&amp;site_id=<?=$siteInfo['site_id']?>" title="��ߥ�󥯥����ȹ���"><?=$siteTitle?></a></td>
								<td class="colWidth04"><a href="<?=$siteInfo['url']?>" title="<?=$siteInfo['url']?>" target="_blank"><?=$siteUrl?></a></td>
								<td class="colWidth05" title="<?=$siteInfo['family_name']." ".$siteInfo['first_name']?>"><?=$siteStaffName?></td>
								<td class="colWidth06 colBorderDotted">
									<input type="checkbox" id="site_relation_flag_<?=$key?>" name="update[<?=$key?>][site_relation_flag]" value="<?=SITE_RELATION_FLAG_YES?>" tabindex="<?=(++$tabindex)?>" <?=($siteInfo['site_relation_flag']==SITE_RELATION_FLAG_YES?"checked=\"checked\"":"")?> />
									<input type="hidden" name="update[<?=$key?>][site_id]" value="<?=$siteInfo['site_id']?>" />
									<input type="hidden" name="update[<?=$key?>][own_site_id]" value="<?=$siteInfo['own_site_id']?>" />
								</td>
								<td class="colWidth07"><label for="site_relation_flag_<?=$key?>" style="display:block;width:100%;height:100%;"><?=(_IsNull($siteInfo['site_relation_create_date_yymmdd'])?"&nbsp;":$siteInfo['site_relation_create_date_yymmdd'])?></label></td>
								<td class="colWidth08 colNum"><?=$siteInfo['total_days']?></td>
								<td class="colWidth09"><?_WriteSelect($mstLinkStatusList, 'update['.$key.'][link_status_id]', $siteInfo['link_status_id'], (++$tabindex), ($siteInfo['site_relation_flag']==SITE_RELATION_FLAG_YES?false:true), "&nbsp;", 1, false, 'id', 'name_del_2');?></td>
								<td class="colWidth10"><a class="mail" href="../mail/?site_id=<?=$siteInfo['site_id']?>&amp;own_site_id=<?=$siteInfo['own_site_id']?>" title="�ǿǥ᡼��">[�ǿ�]</a></td>
							</tr>
<?
	}
?>
						
						</tbody>
					</table>


					<table>
						<thead>
						</thead>
						<tbody>
						</tbody>
					</table>

				</div>
			</div>

			<div class="button">
				<input class="submit" type="submit" name="go" value="����������" tabindex="<?=(++$tabindex)?>" />
			</div>

<?
//��������hidden�����ꤹ�롣
$condition4hidden = array();
$condition4hidden['condition'] = $info['condition'];
echo _CreateHidden($condition4hidden);
?>

		</form>
<?	
}
?>
	</div><!-- End maincontent -->

	<div id="footer">
		<?include_once("../common_html/footer.php");?>
	</div><!-- End footer -->

</div><!-- End wrapper -->
</body>
</html>

<?
//DB�򥯥������롣
_DB_Close($cid);

_Log("[/site/index.php] end.");

?>