<?php
/*
 * [��������]
 * ���ʰ�������
 *
 * ��������2008/02/22	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/item/index.php] start.");

//ǧ�ڥ����å�----------------------------------------------------------------------start
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/item/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/item/index.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ".URL_BASE);
	exit;
}
//����������������롣
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
_CheckAuth($loginInfo, AUTH_CLIENT, AUTH_WOOROM);
//ǧ�ڥ����å�----------------------------------------------------------------------end



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
$mstCategoryList = _GetMasterList('mst_category', $undeleteOnly);			//���ƥ��꡼�ޥ���
$mstSubcategoryList = _GetMasterList('mst_subcategory', $undeleteOnly);		//���֥��ƥ��꡼�ޥ���
$yearList = _GetYearArray(SYSTEM_START_YEAR, date('Y') + 2);				//ǯ
$monthList = _GetMonthArray();												//��
$dayList = _GetDayArray();													//��
//����ե饰
$delFlagList = array(
			 DELETE_FLAG_NO => array('id' => DELETE_FLAG_NO, 'name' => DELETE_FLAG_NO_NAME, 'name2' => '��')
			,DELETE_FLAG_YES => array('id' => DELETE_FLAG_YES, 'name' => DELETE_FLAG_YES_NAME, 'name2' => '��')
			);
//�Ǻܥե饰
$showFlagList = array(
			 SHOW_FLAG_YES => array('id' => SHOW_FLAG_YES, 'name' => SHOW_FLAG_YES_NAME, 'name2' => '��')
			,SHOW_FLAG_NO => array('id' => SHOW_FLAG_NO, 'name' => SHOW_FLAG_NO_NAME, 'name2' => '��')
			);


//ư��⡼��{1:����/2:��λ(����)/3:���顼}
$mode = 1;

//��å�����
$message = "";
//���顼�ե饰
$errorFlag = false;

//��å�����
$message4js = "";


//���submit�к�
if (!isset($_SESSION['token'])) $_SESSION['token'] = uniqid("itm_");


//���Ͼ�����Ǽ��������
$info = array();
//����ͤ����ꤹ�롣
$info['condition']['itm_del_flag'] = array(DELETE_FLAG_NO);				//���ʥơ��֥�.����ե饰="̤���"
//$info['condition']['itm_show_flag'] = array(SHOW_FLAG_YES);			//���ʥơ��֥�.�Ǻܥե饰="̤���"
//$y = date('Y');
//$m = date('n');
//$info['condition']['itm_create_date_year_from'] = $y;					//���ʥơ��֥�.������From(ǯ)
//$info['condition']['itm_create_date_month_from'] = $m;				//���ʥơ��֥�.������From(��)
//$info['condition']['itm_create_date_day_from'] = 1;					//���ʥơ��֥�.������From(��)
//$info['condition']['itm_create_date_year_to'] = $y;					//���ʥơ��֥�.������To(ǯ)
//$info['condition']['itm_create_date_month_to'] = $m;					//���ʥơ��֥�.������To(��)
//$info['condition']['itm_create_date_day_to'] = date('j' ,mktime(0, 0, 0, ($m==12?1:$m+1), 0, ($m==12?$y+1:$y)));	//���ʥơ��֥�.������To(��)���������������ꤹ�롣
//
//$info['condition']['itm_update_date_year_from'] = $y;					//���ʥơ��֥�.������From(ǯ)
//$info['condition']['itm_update_date_month_from'] = $m;				//���ʥơ��֥�.������From(��)
//$info['condition']['itm_update_date_day_from'] = 1;					//���ʥơ��֥�.������From(��)
//$info['condition']['itm_update_date_year_to'] = $y;					//���ʥơ��֥�.������To(ǯ)
//$info['condition']['itm_update_date_month_to'] = $m;					//���ʥơ��֥�.������To(��)
//$info['condition']['itm_update_date_day_to'] = date('j' ,mktime(0, 0, 0, ($m==12?1:$m+1), 0, ($m==12?$y+1:$y)));	//���ʥơ��֥�.������To(��)���������������ꤹ�롣

//�����Ⱦ��
$order = null;
$order .= "lpad(m_ctg.show_order,10,'0')";	//���ƥ��꡼�ޥ���.ɽ����(����)
$order .= ",lpad(m_sbc.show_order,10,'0')";	//���֥��ƥ��꡼�ޥ���.ɽ����(����)
$order .= ",t_itm.itm_code";				//���ʥơ��֥�.���ʥ�����(����)

//������̤��Ǽ��������
$itemList = null;
//����������Ǽ����
$maxCount = 0;
//�ǥե���Ȥ�����ڡ��������ꤹ�롣
$activePage = 1;

//����ܥ��󤬲����줿���
if ($_POST['select'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
	_Log("[/item/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/item/index.php] POST(�Хå�����å���������) = '".print_r($info,true)."'");
	
	//�������Ѵ�
//	//�����ѡ׿������Ⱦ�ѡפ��Ѵ����롣------------------------------------------------------------
//	//��Ͽ����From
//	$info['condition']['site_relation_total_days_from'] = mb_convert_kana($info['condition']['site_relation_total_days_from'], "n");
//	//��Ͽ����To
//	$info['condition']['site_relation_total_days_to'] = mb_convert_kana($info['condition']['site_relation_total_days_to'], "n");
//	
//	//�����ͥ����å�
//	//Ⱦ�ѿ��������å�------------------------------------------------------------------------------
//	//��Ͽ����From
//	if (!_IsHalfSizeNumeric($info['condition']['site_relation_total_days_from'])) $info['condition']['site_relation_total_days_from'] = null;
//	//��Ͽ����To
//	if (!_IsHalfSizeNumeric($info['condition']['site_relation_total_days_to'])) $info['condition']['site_relation_total_days_to'] = null;
	

	//���ʾ���򸡺����롣
	$itemList = _GetItem($info['condition'], $order, false, false, $activePage, ITM_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE);
	//�����������롣
	$maxCount = _GetItem($info['condition'], $order, false, true);
	
	if (_IsNull($itemList)) {
		$message = "�������˳����������¸�ߤ��ޤ���";
	}
	
	
//�����ܥ��󤬲����줿���
} elseif ($_POST['go'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
	_Log("[/item/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/item/index.php] POST(�Хå�����å���������) = '".print_r($info,true)."'");
//	//�����ѡ׿������Ⱦ�ѡפ��Ѵ����롣
//	$info = _Mb_Convert_KanaForArray($info, 'n');
//	_Log("[/item/index.php] POST(�����ѡ׿������Ⱦ�ѡפ��Ѵ�����) = '".print_r($info,true)."'");

	//�����ͥ����å�

	//���å���󤫤鸡������������롣
	$info['condition'] = $_SESSION[SID_SRCH_ITM_CONDITION];
	//���å���󤫤鸡����̤�������롣
	$itemList = $_SESSION[SID_SRCH_ITM_LIST];
	//���å���󤫤鸡�������������롣
	$maxCount = $_SESSION[SID_SRCH_ITM_COUNT];
	//���å���󤫤�����ڡ�����������롣
	$activePage = $_SESSION[SID_SRCH_ITM_ACTIVE_PAGE];

	_Log("[/item/index.php] ���submit�к� SESSION�� = '".$_SESSION['token']."'");
	_Log("[/item/index.php] ���submit�к�    POST�� = '".$info['token']."'");

	//���submit�к��򤹤롣
	if ($_SESSION['token'] == $info['token']) {
		if (isset($info['update'])) {
			_Log("[/item/index.php] {���ʾ��󹹿�} -------------------- ����");
			
			//�����ͥ����å�
			$message .= "���顼��å�����";
			foreach ($info['update'] as $key => $newInfo) {
				//�����å�...
				
				//������̤������ͤ��񤭤��롣�����顼���κ�ɽ���Τ��ᡣ��
				foreach ($newInfo as $name => $value) {
					$itemList[$key][$name] = $value;	
				}
			}
	
			if (_IsNull($message)) {
				//���å���󤫤鸡����̤�Ƽ������롣���嵭���ǡ���񤭤��Ƥ��뤿�ᡣ
				$itemList = $_SESSION[SID_SRCH_ITM_LIST];
	
				$count = 0;
				foreach ($info['update'] as $key => $newInfo) {
					$count++;
		
					//�ѹ�̵ͭ������å����롣
					$updateFlag = false;							//�����ܤι���̵ͭ�ե饰
	

					//������˺ǿ���DB����Ǹ�����̤��񤭤��롣{true:��񤭤���/false:���ʤ�}
					$overwriteFlag = false;
					
					if ($updateFlag) {

						//��������...
						$overwriteFlag = true;

					}
		
					//���������ä���硢�ǿ���DB����Ǹ�����̤��񤭤��롣
					if ($overwriteFlag) {
						_Log("[/item/index.php] {���ʾ��󹹿�} 3.���������ä���硢�ǿ���DB����Ǹ�����̤��񤭤��롣");
						
						//���ʾ���򸡺����롣
						$condition4new = array();
						$condition4new['itm_item_id'] = $newInfo['itm_item_id'];//����ID
						$newItemList = _GetItem($condition4new, $order, false);	
						
						if (_IsNull($newItemList)) {
						} else {
							$itemList[$key] = $newItemList[0];
						}
						
						//��񤭤���������̤򥻥å�������¸���롣
						$_SESSION[SID_SRCH_ITM_LIST] = $itemList;
					}
				}
		
				//���å���󤫤鸡����̤�Ƽ������롣
				$itemList = $_SESSION[SID_SRCH_ITM_LIST];
		
				//�����ޤǤǥ�å����������ξ�硢��Ͽ��������������ʤ��ä���
				if (_IsNull($message)) {
					$message = "�ѹ��ս꤬����ޤ���";
				} else {
					//���顼̵���ξ�硢���submit�к��Υ�ˡ��������򹹿����롣
					$_SESSION['token'] = uniqid("itm_");
				}
				
			} else {
				//���顼��ͭ����
				$message = "�����Ϥ˸�꤬����ޤ���\n".$message;
				$errorFlag = true;
			}
	
		}
		
	} else {
		$message = "����Ź����Ǥ��������򤹤���ϡ��ֹ����ץܥ���򲡤��Ƥ���������";
		$errorFlag = true;
	}
	


//������說�ꥢ�ܥ��󤬲����줿���
} elseif ($_POST['clear'] != "") {

	unset($info['condition']);
	unset($_SESSION[SID_SRCH_ITM_CONDITION]);
	unset($_SESSION[SID_SRCH_ITM_LIST]);
	unset($_SESSION[SID_SRCH_ITM_COUNT]);
	unset($_SESSION[SID_SRCH_ITM_ACTIVE_PAGE]);


	//WOOROM���°ʳ��ξ�硢̤����ξ���Τ�ɽ�����롣
	if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] != AUTH_WOOROM) {
		//����ͤ����ꤹ�롣
		$info['condition']['itm_del_flag'] = array(DELETE_FLAG_NO);		//���ʥơ��֥�.����ե饰="̤���"
	}


//¾�ڡ���������äƤ������
} elseif (isset($_GET['back'])) {

	//���å���󤫤鸡������������롣
	$info['condition'] = $_SESSION[SID_SRCH_ITM_CONDITION];
	//���å���󤫤鸡����̤�������롣
	$itemList = $_SESSION[SID_SRCH_ITM_LIST];
	//���å���󤫤鸡�������������롣
	$maxCount = $_SESSION[SID_SRCH_ITM_COUNT];
	//���å���󤫤�����ڡ�����������롣
	$activePage = $_SESSION[SID_SRCH_ITM_ACTIVE_PAGE];


	//���ʾ����Ƹ������롣�����Υڡ����ǹ�������Ƥ����ǽ�������뤿�ᡣ
	//�����������ꤹ�롣���Υڡ����Ǹ������Ȥʤ���ܤ���������Ƥ����ǽ�������뤿�ᡢ����ɽ�����Ƥ�������Υ����ΤߤǸ������롣

	$condition4new = array();
	foreach ($itemList as $key => $itemInfo) {
		$condition4new['itm_item_id'][] = $itemInfo['itm_item_id'];	//����ID
	}

	//���ʾ���򸡺����롣
	$itemList = _GetItem($condition4new, $order, false);
	
	if (_IsNull($itemList)) {
		$message = "�������˳����������¸�ߤ��ޤ���";
	}

//�ڡ�����󥯤������줿���
} elseif (isset($_GET['page']) && $_GET['page'] != "") {

	//���å���󤫤鸡������������롣
	$info['condition'] = $_SESSION[SID_SRCH_ITM_CONDITION];
	//����ڡ�����������롣
	$activePage = $_GET['page'];

	//���ʾ���򸡺����롣
	$itemList = _GetItem($info['condition'], $order, false, false, $activePage, ITM_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE);
	//�����������롣
	$maxCount = _GetItem($info['condition'], $order, false, true);
	
	if (_IsNull($itemList)) {
		$message = "�������˳����������¸�ߤ��ޤ���";
	}

//���ư��
} else {
	//���ʾ���򸡺����롣
	$itemList = _GetItem($info['condition'], $order, false, false, $activePage, ITM_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE);
	//�����������롣
	$maxCount = _GetItem($info['condition'], $order, false, true);
	
	if (_IsNull($itemList)) {
		$message = "�������˳����������¸�ߤ��ޤ���";
	}
}


//�������򥻥å�������¸���롣
$_SESSION[SID_SRCH_ITM_CONDITION] = $info['condition'];
//������̤򥻥å�������¸���롣
$_SESSION[SID_SRCH_ITM_LIST] = $itemList;
//��������򥻥å�������¸���롣
$_SESSION[SID_SRCH_ITM_COUNT] = $maxCount;
//����ڡ����򥻥å�������¸���롣
$_SESSION[SID_SRCH_ITM_ACTIVE_PAGE] = $activePage;



//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
////ʸ����HTML����ƥ��ƥ����Ѵ����롣
//$itemList = _HtmlSpecialCharsForArray($itemList);

//echo ("\$info='".print_r($info,true)."'");

$onlode = null;
	
//�ѥ󤯤��ꥹ�Ⱦ�������ꤹ�롣
_SetBreadcrumbs($_SERVER['PHP_SELF'], 'back', '���ʰ���', 2);

//�ڡ�����󥯤����ꤹ�롣
$link = _GetPageLink($maxCount, $activePage, ITM_PAGE_LINK_TOP_MESSAGE, ITM_PAGE_LINK_ACTIVE_PAGE_MESSAGE, ITM_PAGE_LINK_COUNT_MESSAGE, null, ITM_PAGE_LINK_LIMIT, ITM_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE, ITM_PAGE_LINK_FRONT_TEXT, ITM_PAGE_LINK_REAR_TEXT);

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
<script language="javascript" src="../common/js/status_update_date.js" type="text/javascript"></script>

<script type="text/javascript">
<!--
//-->
</script>

<title><?=$clientName?></title>
</head>

<body id="home" onload="openBox('explain_sub', 'explain', 'explain_close');<?=$onlode?>">
<div id="wrapper">
	<div id="header">
		<?include_once("../common_html/header.php");?>
	</div><!-- End header -->

	<div class="breadcrumbs">
		<?=$breadcrumbs = _GetBreadcrumbs();?>
	</div><!-- End breadcrumbs -->
	
	<div id="sidebar">
		<?include_once("../common_html/side_menu.php");?>
	</div><!-- End sidebar -->

	<div id="maincontent">
		<h2>���ʰ���</h2>
		<h3>�������</h3>
		
		<form id="frmSelect" name="frmSelect" action="<?=$_SERVER['PHP_SELF']?>" method="post">
<!--			<h4 id="listMode">[ɽ�����]</h4>-->
			<table class="searchConditionTable">
<!--
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
-->
				<colgroup class="colgroupHeadItem"></colgroup>
				<colgroup class="colgroupBodyItem1"></colgroup>
				<colgroup class="colgroupHeadItem"></colgroup>
				<colgroup class="colgroupBodyItem2"></colgroup>

				<tr>
					<td class="colHead">
						���ƥ��꡼
					</td>
					<td colspan="3">
						<?$tabindex = _WriteCheckbox($mstCategoryList, 'condition[itm_category_id]', $info['condition']['itm_category_id'], (++$tabindex), 4, 'id', 'name_del_2', 'id', false, null);?>
					</td>
				</tr>
				<tr>
					<td class="colHead">
						���֥��ƥ��꡼
					</td>
					<td colspan="3">
						<?$tabindex = _WriteCheckbox($mstSubcategoryList, 'condition[itm_subcategory_id]', $info['condition']['itm_subcategory_id'], (++$tabindex), 4, 'id', 'name_del_2', 'id', false, null);?>
					</td>
				</tr>
				<tr>
					<td class="colHead">
						���ʥ�����
					</td>
					<td>
						<input type="text" name="condition[itm_code]" size="70" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['itm_code']?>" />
					</td>
					<td class="colHead">
						�Ǻܥե饰
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($showFlagList, 'condition[itm_show_flag]', $info['condition']['itm_show_flag'], (++$tabindex));?>
					</td>
				</tr>
				<tr>
					<td class="colHead">
						����̾
					</td>
					<td>
						<input type="text" name="condition[itm_name]" size="70" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['itm_name']?>" />
					</td>
<?if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] == AUTH_WOOROM) {?>
					<td class="colHead">
						����ե饰
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($delFlagList, 'condition[itm_del_flag]', $info['condition']['itm_del_flag'], (++$tabindex));?>
					</td>
<?} else {?>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
<?}?>
				</tr>
				<tr>
					<td class="colHead">
						��Ͽ��
					</td>
					<td>
						<?_WriteSelect($yearList, 'condition[itm_create_date_year_from]', $info['condition']['itm_create_date_year_from'], (++$tabindex), true, '&nbsp;');?>ǯ
						<?_WriteSelect($monthList, 'condition[itm_create_date_month_from]', $info['condition']['itm_create_date_month_from'], (++$tabindex), true, '&nbsp;');?>��
						<?_WriteSelect($dayList, 'condition[itm_create_date_day_from]', $info['condition']['itm_create_date_day_from'], (++$tabindex), true, '&nbsp;');?>��
						��
						<?_WriteSelect($yearList, 'condition[itm_create_date_year_to]', $info['condition']['itm_create_date_year_to'], (++$tabindex), true, '&nbsp;');?>ǯ
						<?_WriteSelect($monthList, 'condition[itm_create_date_month_to]', $info['condition']['itm_create_date_month_to'], (++$tabindex), true, '&nbsp;');?>��
						<?_WriteSelect($dayList, 'condition[itm_create_date_day_to]', $info['condition']['itm_create_date_day_to'], (++$tabindex), true, '&nbsp;');?>��
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
						������
					</td>
					<td>
						<?_WriteSelect($yearList, 'condition[itm_update_date_year_from]', $info['condition']['itm_update_date_year_from'], (++$tabindex), true, '&nbsp;');?>ǯ
						<?_WriteSelect($monthList, 'condition[itm_update_date_month_from]', $info['condition']['itm_update_date_month_from'], (++$tabindex), true, '&nbsp;');?>��
						<?_WriteSelect($dayList, 'condition[itm_update_date_day_from]', $info['condition']['itm_update_date_day_from'], (++$tabindex), true, '&nbsp;');?>��
						��
						<?_WriteSelect($yearList, 'condition[itm_update_date_year_to]', $info['condition']['itm_update_date_year_to'], (++$tabindex), true, '&nbsp;');?>ǯ
						<?_WriteSelect($monthList, 'condition[itm_update_date_month_to]', $info['condition']['itm_update_date_month_to'], (++$tabindex), true, '&nbsp;');?>��
						<?_WriteSelect($dayList, 'condition[itm_update_date_day_to]', $info['condition']['itm_update_date_day_to'], (++$tabindex), true, '&nbsp;');?>��
					</td>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
			</table>

			<div class="button">
				<input class="submit" type="submit" name="select" value="����������" tabindex="<?=(++$tabindex)?>" />
				&nbsp;
				<input class="submit" type="submit" name="reset" value="�������︡����" tabindex="<?=(++$tabindex)?>" />
				&nbsp;
				<input class="submit" type="submit" name="clear" value="�����ꥢ��" tabindex="<?=(++$tabindex)?>" />
			</div>
<?
if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] != AUTH_WOOROM) {
	//��������hidden�����ꤹ�롣
	$condition4hidden = array();
	$condition4hidden['condition']['itm_del_flag'] = $info['condition']['itm_del_flag'];
	echo _CreateHidden($condition4hidden);
}
?>
		</form>

<?
//����������ϡ���å�������¸�ߤ����硢���Ф���ɽ�����롣
if (!_IsNull($itemList) || !_IsNull($message)) {
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
if (!_IsNull($itemList)) {
?>
		<form id="frmUpdate" name="frmUpdate" action="<?=$_SERVER['PHP_SELF']?>" method="post">
			<div class="formWrapper">
				<div class="formList">
					<div class="page"><?=$link?></div>
					<table id="searchResultListTable">
<!--
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
-->
						<thead>
							<tr>
								<td>����ID</td>
								<td>���ƥ��꡼</td>
								<td>���֥��ƥ��꡼</td>
								<td>���ʥ�����</td>
								<td>����̾</td>
								<td>�Ǻ�</td>
<?if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] == AUTH_WOOROM) {?>
								<td>���</td>
<?}?>
								<td>��Ͽ��</td>
								<td>������</td>
								<td>�Խ�</td>
							</tr>
						</thead>

						<tbody>
<?
			$count = 0;
			foreach ($itemList as $key => $itemInfo) {
				$count++;
				
				$rowColorClass = null;
				if ($count % 2 == 0) {
					$rowColorClass = 'rowColor02';
				} else {
					$rowColorClass = 'rowColor01';
				}
		
				//�ʲ��ι��ܤϡ�ʸ������û������HTML����ƥ��ƥ����Ѵ����롣
				//����̾	itm_name
				$itmName = _SubStr($itemInfo['itm_name'], 20);
				$itmName = htmlspecialchars($itmName);

				
				//�طʿ������ꤹ�롣
				//���ƥ��꡼
				$bgColor4Category = null;
				if (isset($mstCategoryList[$itemInfo['itm_category_id']]['color']) && !_IsNull($mstCategoryList[$itemInfo['itm_category_id']]['color'])) {
					$bgColor4Category = "style=\"background-color:".$mstCategoryList[$itemInfo['itm_category_id']]['color'].";\"";
				}
				//���֥��ƥ��꡼
				$bgColor4Subcategory = null;
				if (isset($mstSubcategoryList[$itemInfo['itm_subcategory_id']]['color']) && !_IsNull($mstSubcategoryList[$itemInfo['itm_subcategory_id']]['color'])) {
					$bgColor4Subcategory = "style=\"background-color:".$mstSubcategoryList[$itemInfo['itm_subcategory_id']]['color'].";\"";
				}




				//ʸ����HTML����ƥ��ƥ����Ѵ����롣
				$itemInfo = _HtmlSpecialCharsForArray($itemInfo);
?>
							<tr class="<?=$rowColorClass?>">
								<td class="colWidthItem01 colCenter"><?=_FormatNo($itemInfo['itm_item_id'])?></td>
								<td class="colWidthItem02" <?=$bgColor4Category?>><?=$itemInfo['itm_category_name']?></td>
								<td class="colWidthItem03" <?=$bgColor4Subcategory?>><?=$itemInfo['itm_subcategory_name']?></td>
								<td class="colWidthItem04"><?=$itemInfo['itm_code']?></td>
								<td class="colWidthItem05" title="<?=$itemInfo['itm_name']?>"><?=$itmName?></td>
								<td class="colWidthItem06 colCenter"><?=$showFlagList[$itemInfo['itm_show_flag']]['name2']?></td>
<?
				if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] == AUTH_WOOROM) {?>
								<td class="colWidthItem07 colCenter"><?=$delFlagList[$itemInfo['itm_del_flag']]['name2']?></td>
<?
				}
?>
								<td class="colWidthItem08 colCenter" title="<?=$itemInfo['itm_create_date_yyyymmddhhmmss']?>"><?=$itemInfo['itm_create_date_yymmdd']?></td>
								<td class="colWidthItem09 colCenter" title="<?=$itemInfo['itm_update_date_yyyymmddhhmmss']?>"><?=$itemInfo['itm_update_date_yymmdd']?></td>
								<td class="colWidthItem10 colCenter"><a class="edit" href="../info/?xml_name=<?=XML_NAME_ITEM?>&amp;id=<?=$itemInfo['itm_item_id']?>" title="�Խ�">[�Խ�]</a></td>
							</tr>
<?
			}
?>
						</tbody>
					</table>
					<div class="page"><?=$link?></div>
				</div>
			</div>
<!--
			<div class="button">
				<input class="submit" type="submit" name="go" value="����������" tabindex="<?=(++$tabindex)?>" />
			</div>
-->
<?
//���å������ݻ����롣	
//	//��������hidden�����ꤹ�롣
//	$condition4hidden = array();
//	$condition4hidden['condition'] = $info['condition'];
//	echo _CreateHidden($condition4hidden);
?>

		<input type="hidden" name="token" value="<?=$_SESSION['token']?>" />
		
		</form>
<?	
}
?>
	</div><!-- End maincontent -->

	<div class="breadcrumbs">
		<?=$breadcrumbs?>
	</div><!-- End breadcrumbs -->

	<div id="footer">
		<?include_once("../common_html/footer.php");?>
	</div><!-- End footer -->
	
	
	<div id="debug"></div>

</div><!-- End wrapper -->
</body>
</html>

<?
//DB�򥯥������롣
_DB_Close($cid);

_Log("[/item/index.php] end.");

?>