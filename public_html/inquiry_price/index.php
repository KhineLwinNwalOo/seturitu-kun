<?php
/*
 * [��������]
 * ����۰�������
 *
 * ��������2007/12/03	d.ishikawa	��������
 *
 */

//����å����ͭ���ˤ��롣
session_cache_limiter('private, private_no_expire');
session_start();

include_once("../common/include.ini");


_LogDelete();
//_LogBackup();
_Log("[/inquiry_price/index.php] start.");

//ǧ�ڥ����å�----------------------------------------------------------------------start
//�����󤷤Ƥ��뤫��
if (!isset($_SESSION[SID_ADMIN_LOGIN_INFO])) {
	_Log("[/inquiry_price/index.php] �����󤷤Ƥ��ʤ��ʤΤǥ�������̤�ɽ�����롣");
	_Log("[/inquiry_price/index.php] end.");
	//��������̤�ɽ�����롣
	header("Location: ".URL_LOGIN);
	exit;
}
//����������������롣
$loginInfo = $_SESSION[SID_ADMIN_LOGIN_INFO];

//�ܲ��̤���Ѳ�ǽ�ʸ��¤������å����롣�����ԲĤξ�硢��������̤����ܤ��롣
_CheckAuth($loginInfo, AUTH_WOOROM);
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
$mstStatusList = _GetMasterList('mst_status', $undeleteOnly);										//�����ޥ���
$mstAftereffectsGrade01List = _GetMasterList('mst_aftereffects_grade_01', $undeleteOnly);		//���㳲����(��)�ޥ���
$yearList = _GetYearArray(SYSTEM_START_YEAR, date('Y') + 3);										//ǯ
$monthList = _GetMonthArray();																		//��
$dayList = _GetDayArray();																			//��
//����ե饰
$delFlagList = array(
			 DELETE_FLAG_NO => array('id' => DELETE_FLAG_NO, 'name' => DELETE_FLAG_NO_NAME, 'no_name' => '')
			,DELETE_FLAG_YES => array('id' => DELETE_FLAG_YES, 'name' => DELETE_FLAG_YES_NAME, 'no_name' => '')
			);


//ư��⡼��{1:����/2:��λ(����)/3:���顼}
$mode = 1;

//��å�����
$message = "";
//���顼�ե饰
$errorFlag = false;

//��å�����
$message4js = "";


//���Ͼ�����Ǽ��������
$info = array();
//����ͤ����ꤹ�롣
$info['condition']['iuq_del_flag'] = array(DELETE_FLAG_NO);						//��礻�ơ��֥�.����ե饰="̤���"
//$y = date('Y');
//$m = date('n');
//$info['condition']['iuq_agd_create_date_year_from'] = $y;						//����������From(ǯ)
//$info['condition']['iuq_agd_create_date_month_from'] = $m;						//����������From(��)
//$info['condition']['iuq_agd_create_date_day_from'] = 1;						//����������From(��)
//$info['condition']['iuq_agd_create_date_year_to'] = $y;						//����������To(ǯ)
//$info['condition']['iuq_agd_create_date_month_to'] = $m;						//����������To(��)
//$info['condition']['iuq_agd_create_date_day_to'] = date('j' ,mktime(0, 0, 0, ($m==12?1:$m+1), 0, ($m==12?$y+1:$y)));	//����������To(��)���������������ꤹ�롣


$y = date('Y');
$info['condition']['iuq_agd_create_date_year_from'] = $y;					//����������From(ǯ)
$info['condition']['iuq_agd_create_date_month_from'] = 1;					//����������From(��)
$info['condition']['iuq_agd_create_date_day_from'] = 1;					//����������From(��)
$info['condition']['iuq_agd_create_date_year_to'] = $y;					//����������To(ǯ)
$info['condition']['iuq_agd_create_date_month_to'] = 12;					//����������To(��)
$info['condition']['iuq_agd_create_date_day_to'] = 31;						//����������To(��)

//����="�������"��"���"��"�����󽷼���"��"��λ"
$info['condition']['iuq_agd_status_id'] = array(MST_STATUS_ID_START_MONEY, MST_STATUS_ID_NEGOTIATION, MST_STATUS_ID_SUCCESS_MONEY, MST_STATUS_ID_END);

//��礻_���㳲����(��)_����ơ��֥�򸡺��оݤ��ɲä��롣
$info['condition']['add_tbl_inquiry_aftereffects_grade_decision'] = true;


//������̤��Ǽ��������
$inquiryList = null;
//����������Ǽ����
$maxCount = 0;
//�ǥե���Ȥ�����ڡ��������ꤹ�롣
$activePage = 1;

//�����Υ����Ⱦ��
$order .= " t_inq.iuq_inquiry_id";		//��礻�ơ��֥�.��礻ID�ξ���
$order .= ",t_iuq_agd.iuq_agd_no";		//��礻_���㳲����(��)_����ơ��֥�.No�ξ���


//����ܥ��󤬲����줿���
if ($_POST['select'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
	_Log("[/inquiry_price/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/inquiry_price/index.php] POST(�Хå�����å���������) = '".print_r($info,true)."'");

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

	//��礻����򸡺����롣
	$inquiryList = _GetInquiry($info['condition'], $order, false, false, $activePage, null, 2);
//	//�����������롣
//	$maxCount = _GetInquiry($info['condition'], $order, false, true);

	if (_IsNull($inquiryList)) {
		$message = "�������˳����������¸�ߤ��ޤ���";
	}


//�����ܥ��󤬲����줿���
} elseif ($_POST['go'] != "") {
	//�����ͤ�������롣
	$info = $_POST;
	_Log("[/inquiry_price/index.php] POST = '".print_r($info,true)."'");
	//�Хå�����å�����������
	$info = _StripslashesForArray($info);
	_Log("[/inquiry_price/index.php] POST(�Хå�����å���������) = '".print_r($info,true)."'");
//	//�����ѡ׿������Ⱦ�ѡפ��Ѵ����롣
//	$info = _Mb_Convert_KanaForArray($info, 'n');
//	_Log("[/inquiry_price/index.php] POST(�����ѡ׿������Ⱦ�ѡפ��Ѵ�����) = '".print_r($info,true)."'");


//������說�ꥢ�ܥ��󤬲����줿���
} elseif ($_POST['clear'] != "") {
	unset($info['condition']);
	unset($_SESSION[SID_PRICE_INQ_CONDITION]);
//	unset($_SESSION[SID_SRCH_INQ_LIST]);
//	unset($_SESSION[SID_SRCH_INQ_COUNT]);
//	unset($_SESSION[SID_SRCH_INQ_ACTIVE_PAGE]);


	$y = date('Y');
	$info['condition']['iuq_agd_create_date_year_from'] = $y;					//����������From(ǯ)
	$info['condition']['iuq_agd_create_date_month_from'] = 1;					//����������From(��)
	$info['condition']['iuq_agd_create_date_day_from'] = 1;					//����������From(��)
	$info['condition']['iuq_agd_create_date_year_to'] = $y;					//����������To(ǯ)
	$info['condition']['iuq_agd_create_date_month_to'] = 12;					//����������To(��)
	$info['condition']['iuq_agd_create_date_day_to'] = 31;						//����������To(��)

	//WOOROM���°ʳ��ξ�硢̤����ξ���Τ�ɽ�����롣
	if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] != AUTH_WOOROM) {
		//����ͤ����ꤹ�롣
		$info['condition']['iuq_del_flag'] = array(DELETE_FLAG_NO);					//��礻�ơ��֥�.����ե饰="̤���"
	}

//¾�ڡ���������äƤ������
} elseif (isset($_GET['back'])) {

	//���å���󤫤鸡������������롣
	$info['condition'] = $_SESSION[SID_PRICE_INQ_CONDITION];

	//��礻����򸡺����롣
	$inquiryList = _GetInquiry($info['condition'], $order, false, false, $activePage, null, 2);
//	//�����������롣
//	$maxCount = _GetInquiry($info['condition'], $order, false, true);

	if (_IsNull($inquiryList)) {
		$message = "�������˳����������¸�ߤ��ޤ���";
	}

//�ڡ�����󥯤������줿���
} elseif (isset($_GET['page']) && $_GET['page'] != "") {

//	//���å���󤫤鸡������������롣
//	$info['condition'] = $_SESSION[SID_PRICE_INQ_CONDITION];
//	//����ڡ�����������롣
//	$activePage = $_GET['page'];
//
//	//��礻����򸡺����롣
//	$order = null;		//�����Ⱦ��
//	$inquiryList = _GetInquiry($info['condition'], $order, false, false, $activePage, null);
//	//�����������롣
//	$maxCount = _GetInquiry($info['condition'], $order, false, true);
//
//	if (_IsNull($inquiryList)) {
//		$message = "�������˳����������¸�ߤ��ޤ���";
//	}

//���ư��
} else {
	//��礻����򸡺����롣
	$inquiryList = _GetInquiry($info['condition'], $order, false, false, $activePage, null, 2);
//	//�����������롣
//	$maxCount = _GetInquiry($info['condition'], $order, false, true);

	if (_IsNull($inquiryList)) {
		$message = "�������˳����������¸�ߤ��ޤ���";
	}
}

//�������򥻥å�������¸���롣
$_SESSION[SID_PRICE_INQ_CONDITION] = $info['condition'];
////������̤򥻥å�������¸���롣
//$_SESSION[SID_SRCH_INQ_LIST] = $inquiryList;
////��������򥻥å�������¸���롣
//$_SESSION[SID_SRCH_INQ_COUNT] = $maxCount;
////����ڡ����򥻥å�������¸���롣
//$_SESSION[SID_SRCH_INQ_ACTIVE_PAGE] = $activePage;

//���ܤȤʤ�ǯ���������롣
$ymdList = _GetYmdList($info['condition'], INQUIRY_PRICE_START_MONTH, INQUIRY_PRICE_TOTAL_MONTH);

//////////////////////////////////////////////////////////////////////////////////////////////////////// start
//���ܤȤʤ�ǯ������ꤹ�롣
function _GetYmdList($list, $monthStart, $limit) {
	_Log("[_GetYmdList] start.");

	_Log("[_GetYmdList] (����)��� ='".print_r($list,true)."'");
	_Log("[_GetYmdList] (����)���Ϸ� ='".$monthStart."'");
	_Log("[_GetYmdList] (����)��ߥå� ='".$limit."'");


	$yearFrom = $list['iuq_agd_create_date_year_from'];
	$monthFrom = $list['iuq_agd_create_date_month_from'];
	$dayFrom = $list['iuq_agd_create_date_day_from'];
	$yearTo = $list['iuq_agd_create_date_year_to'];
	$monthTo = $list['iuq_agd_create_date_month_to'];
	$dayTo = $list['iuq_agd_create_date_day_to'];

	$resList = array();

	$buf1List = array();
	for ($y = $yearFrom; $y <= $yearTo; $y++) {

		if ($y == $yearFrom) {
//			$mFrom = $monthFrom;

			//���Ϸ�����ꤹ�롣
			$flg = true;
			$bufMonthStart = $monthStart;

			while ($flg) {
				$bufList = array();
				for ($i = 0; $i < $limit; $i++) {
					_Log("[_GetYmdList] {���Ϸ�����} ���� = '".$bufMonthStart."'");

					$bufList[] = $bufMonthStart;
					$bufMonthStart++;
					if ($bufMonthStart > 12) $bufMonthStart = 1;
				}

				_Log("[_GetYmdList] {���Ϸ�����} �������� = '".print_r($bufList,true)."'");

				if (in_array($monthFrom, $bufList)) {
					$flg = false;
					$mFrom = $bufList[0];

					_Log("[_GetYmdList] {���Ϸ�����} ������� = '".$mFrom."'");
				}
			}

		} else {
			$mFrom = 1;
		}
		if ($y == $yearTo) {
			$mTo = $monthTo;
		} else {
			$mTo = 12;
		}

		for ($m = $mFrom; $m <= $mTo; $m++) {

			$buf2List = array();
			$buf2List['year'] = $y;
			$buf2List['month'] = $m;
			$buf1List[] = $buf2List;

			if (count($buf1List) == $limit) {
				$resList[] = $buf1List;
				$buf1List = array();
			}
		}
	}

	//����Ⱦ��ξ�硢­��ʤ�ʬ��­����
	while (count($buf1List) > 0) {
		//�Ǹ�����ꤵ�줿ǯ���������롣
		$buf = $buf1List[count($buf1List)-1];
		//�Ǹ�����ꤵ�줿ǯ��μ���ǯ������ꤹ�롣
		if ($buf['month'] == 12) {
			$y = $buf['year'] + 1;
			$m = 1;
		} else {
			$y = $buf['year'];
			$m = $buf['month'] + 1;
		}

		$buf2List = array();
		$buf2List['year'] = $y;
		$buf2List['month'] = $m;
		$buf1List[] = $buf2List;

		if (count($buf1List) == $limit) {
			$resList[] = $buf1List;
			$buf1List = array();
		}
	}

	_Log("[_GetYmdList] ��� ='".print_r($resList,true)."'");
	_Log("[_GetYmdList] end.");

	return $resList;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////// end


//ʸ����HTML����ƥ��ƥ����Ѵ����롣
$info = _HtmlSpecialCharsForArray($info);
////ʸ����HTML����ƥ��ƥ����Ѵ����롣
//$inquiryList = _HtmlSpecialCharsForArray($inquiryList);

//echo ("\$info='".print_r($info,true)."'");

$onlode = null;

//�ѥ󤯤��ꥹ�Ⱦ�������ꤹ�롣
_SetBreadcrumbs($_SERVER['PHP_SELF'], 'back', '����۰���', 2);

//�ڡ�����󥯤����ꤹ�롣
//$link = _GetPageLink($maxCount, $activePage, INQ_PAGE_LINK_TOP_MESSAGE, INQ_PAGE_LINK_ACTIVE_PAGE_MESSAGE, INQ_PAGE_LINK_COUNT_MESSAGE, null, INQ_PAGE_LINK_LIMIT, INQ_PAGE_LINK_SHOW_NUM_OF_ONE_PAGE, INQ_PAGE_LINK_FRONT_TEXT, INQ_PAGE_LINK_REAR_TEXT);

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
		<h2>����۰���</h2>
		<h3>�������</h3>

		<form id="frmSelect" name="frmSelect" action="<?=$_SERVER['PHP_SELF']?>" method="post">
<!--			<h4 id="listMode">[ɽ�����]</h4>-->
			<table class="priceConditionTable">
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>
				<colgroup class="colgroupHead"></colgroup>
				<colgroup class="colgroupBody"></colgroup>

				<tr>
					<td class="colHead">
						���������&nbsp;��
					</td>
					<td colspan="5">
						<?_WriteSelect($yearList, 'condition[iuq_agd_create_date_year_from]', $info['condition']['iuq_agd_create_date_year_from'], (++$tabindex), false, '&nbsp;');?>ǯ
						<?_WriteSelect($monthList, 'condition[iuq_agd_create_date_month_from]', $info['condition']['iuq_agd_create_date_month_from'], (++$tabindex), false, '&nbsp;');?>��
						<?_WriteSelect($dayList, 'condition[iuq_agd_create_date_day_from]', $info['condition']['iuq_agd_create_date_day_from'], (++$tabindex), false, '&nbsp;');?>��
						��
						<?_WriteSelect($yearList, 'condition[iuq_agd_create_date_year_to]', $info['condition']['iuq_agd_create_date_year_to'], (++$tabindex), false, '&nbsp;');?>ǯ
						<?_WriteSelect($monthList, 'condition[iuq_agd_create_date_month_to]', $info['condition']['iuq_agd_create_date_month_to'], (++$tabindex), false, '&nbsp;');?>��
						<?_WriteSelect($dayList, 'condition[iuq_agd_create_date_day_to]', $info['condition']['iuq_agd_create_date_day_to'], (++$tabindex), false, '&nbsp;');?>��
						<br />
						��<?=INQUIRY_PRICE_START_MONTH?>���<?=INQUIRY_PRICE_TOTAL_MONTH?>����ñ�̤˹�ס�1����ʿ�Ѥ�׻����ޤ���
					</td>
				</tr>
				<tr>
					<td class="colHead">
						̾��
					</td>
					<td>
						<input type="text" name="condition[usr_name]" size="28" maxlength="100" tabindex="<?=(++$tabindex)?>" value="<?=$info['condition']['usr_name']?>" />
					</td>
					<td class="colHead" rowspan="4">
						����
					</td>
					<td rowspan="4">
						<?_WriteSelect($mstStatusList, 'condition[iuq_agd_status_id]', $info['condition']['iuq_agd_status_id'], (++$tabindex), false, "&nbsp;", 6, true, 'id', 'name_del_2', 'id', 'class="multiple"', true);?>
					</td>
					<td class="colHead" rowspan="4">
						����
					</td>
					<td rowspan="4">
						<?_WriteSelect($mstAftereffectsGrade01List, 'condition[iuq_agd_aftereffects_grade_01_id]', $info['condition']['iuq_agd_aftereffects_grade_01_id'], (++$tabindex), false, "&nbsp;", 6, true, 'id', 'name_del_2', 'id', 'class="multiple"', true);?>
					</td>
				</tr>
				<tr>
<?if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id']==AUTH_WOOROM) {?>
					<td class="colHead">
						����ե饰
					</td>
					<td>
						<?$tabindex = _WriteCheckbox($delFlagList, 'condition[iuq_del_flag]', $info['condition']['iuq_del_flag'], (++$tabindex));?>
					</td>
<?} else {?>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
<?
	//��������hidden�����ꤹ�롣
	$condition4hidden = array();
	$condition4hidden['condition']['iuq_del_flag'] = $info['condition']['iuq_del_flag'];
	echo _CreateHidden($condition4hidden);
?>
						&nbsp;
					</td>
<?}?>
				</tr>
				<tr>
					<td class="colHead">
						&nbsp;
					</td>
					<td>
						&nbsp;
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

			<div class="button">
				<input class="submit" type="submit" name="select" value="����������" tabindex="<?=(++$tabindex)?>" />
				&nbsp;
				<input class="submit" type="submit" name="reset" value="�������︡����" tabindex="<?=(++$tabindex)?>" />
				&nbsp;
				<input class="submit" type="submit" name="clear" value="�����ꥢ��" tabindex="<?=(++$tabindex)?>" />
			</div>

<?
	//��������hidden�����ꤹ�롣
	$condition4hidden = array();
	$condition4hidden['condition']['add_tbl_inquiry_aftereffects_grade_decision'] = $info['condition']['add_tbl_inquiry_aftereffects_grade_decision'];
	echo _CreateHidden($condition4hidden);
?>


		</form>

<?
//����������ϡ���å�������¸�ߤ����硢���Ф���ɽ�����롣
if (!_IsNull($inquiryList) || !_IsNull($message)) {
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
if (!_IsNull($inquiryList)) {
?>
		<form id="frmUpdate" name="frmUpdate" action="<?=$_SERVER['PHP_SELF']?>" method="post">
			<div class="formWrapper">
				<div class="formList">
<!--
					<div class="page"><?=$link?></div>
-->
					<table id="priceResultListTable">
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
								<td colspan="2" rowspan="2"></td>
								<td rowspan="2">ID</td>
<!--								<td rowspan="2">����<br />���</td>-->
								<td rowspan="2">̾��</td>
								<td colspan="3">���������</td>
								<td rowspan="2">����</td>
								<td rowspan="2">�㳲����</td>
								<td colspan="2">������</td>
								<td rowspan="2">1����ʿ��</td>
								<td rowspan="2">����</td>
								<td rowspan="2">�ܺ�</td>
<!--								<td class="tips" title="����::��礻���������Ͽ���ޤ���">����<br />/&nbsp;������</td>-->
							</tr>
							<tr>
								<td class="bdTop">ǯ</td>
								<td class="bdTop">��</td>
								<td class="bdTop">��</td>
								<td class="bdTop">�쥮��顼</td>
								<td class="bdTop">�ץ�ߥ�</td>
							</tr>
						</thead>

						<tbody>
<?
			$LF = "\n";
			$data = null;
			foreach ($ymdList as $ymdKey1 =>  $oneSetYmdList) {
				$oneSetTotalAftereffectsGrade01Price = 0;	//�����ñ�̤ι��������
				$oneSetTotalStartPrice = 0;					//�����ñ�̤ι�������
				$oneSetTotalYm = "";
				$data2 = null;
				foreach ($oneSetYmdList as $ymdKey2 =>  $ymdInfo) {
					if ($ymdKey2 == 0) {
						//�ǽ��ǯ��
						$oneSetTotalYm .= sprintf('%04d/%02d', $ymdInfo['year'], $ymdInfo['month']);
						$oneSetTotalYm .= "��";
					} elseif ($ymdKey2 == count($oneSetYmdList) - 1) {
						//�Ǹ��ǯ��
						$oneSetTotalYm .= sprintf('%04d/%02d', $ymdInfo['year'], $ymdInfo['month']);
					}

					$inquiryYmList = array();
					if (isset($inquiryList[$ymdInfo['year']][$ymdInfo['month']])) {
						$inquiryYmList = $inquiryList[$ymdInfo['year']][$ymdInfo['month']];
					} else {
						$dummy = array();
						$dummy[] = array();
						$inquiryYmList[] = $dummy; //���ߡ�
					}

					$ymTotalYm = sprintf('%04d/%02d', $ymdInfo['year'], $ymdInfo['month']);
					$ymTotalAftereffectsGrade01Price = 0;	//��ñ�̤ι��������
					$ymTotalStartPrice = 0;					//��ñ�̤ι�������
					$data3 = null;
					$count = 0;
					$count4TrColor = 0;
					foreach ($inquiryYmList as $day => $inquiryYmdList) {
						foreach ($inquiryYmdList as $key => $inquiryInfo) {
							$count++;

							$rowColorClass = null;
							if ($count % 2 == 0) {
								$rowColorClass = 'rowColor02';
							} else {
								$rowColorClass = 'rowColor01';
							}

							//ǯ
							$year = $ymdInfo['year'];
							//��
							$month = sprintf('%02d', $ymdInfo['month']);
							//��
							$day = null;
							if (isset($inquiryInfo['iuq_agd_create_date_d']) && !_IsNull($inquiryInfo['iuq_agd_create_date_d'])) {
								$day = sprintf('%02d', $inquiryInfo['iuq_agd_create_date_d']);
							} else {
								$year = "&nbsp;";
								$month = "&nbsp;";
							}
							//��礻ID
							//URL
							$inquiryId = null;
							$inquiryIdShow = null;
							$url = null;
							$trId = null;
							$trIdPrefix = "inq";
							if (isset($inquiryInfo['iuq_inquiry_id']) && !_IsNull($inquiryInfo['iuq_inquiry_id'])) {
								$inquiryId = $inquiryInfo['iuq_inquiry_id'];
								$inquiryIdShow = _FormatNo($inquiryId);
								$url = "<a class=\"edit\" href=\"../info/?xml_name=".XML_NAME_INQ."&amp;p_id=".PAGE_ID_INQ_PRICE."&amp;id=".$inquiryId."\" title=\"�ܺ�\">[�ܺ�]</a>";


								$trId = $trIdPrefix.$inquiryId."_".$inquiryInfo['iuq_agd_no'];
							}

							//̾��
							$usrName = _SubStr($inquiryInfo['usr_name'], 10);
							//̾���ڡ�������
							$usrNameLink = "%s";

							//����
							$statusName = null;
							if (isset($inquiryInfo['iuq_agd_status_id']) && !_IsNull($inquiryInfo['iuq_agd_status_id'])) {
								$statusName = $mstStatusList[$inquiryInfo['iuq_agd_status_id']]['name'];
							}
							//���㳲����(��)
							$aftereffectsGrade01Name = null;
							if (isset($inquiryInfo['iuq_agd_aftereffects_grade_01_id']) && !_IsNull($inquiryInfo['iuq_agd_aftereffects_grade_01_id'])) {
								$aftereffectsGrade01Name = $mstAftereffectsGrade01List[$inquiryInfo['iuq_agd_aftereffects_grade_01_id']]['name'];
							}


							//���㳲����(��)���
							$aftereffectsGrade01PriceFlag = false;
							$aftereffectsGrade01Price = 0;
							$aftereffectsGrade01PriceShow = null;
							if (isset($inquiryInfo['iuq_agd_aftereffects_grade_01_price']) && !_IsNull($inquiryInfo['iuq_agd_aftereffects_grade_01_price'])) {
								$aftereffectsGrade01PriceShow = number_format($inquiryInfo['iuq_agd_aftereffects_grade_01_price']);
								$aftereffectsGrade01Price = $inquiryInfo['iuq_agd_aftereffects_grade_01_price'];
								$aftereffectsGrade01PriceFlag = true;
							}
							//�����
							$startPriceFlag = false;
							$startPrice = 0;
							$startPriceShow = null;
							if (isset($inquiryInfo['iuq_tac_start_price']) && !_IsNull($inquiryInfo['iuq_tac_start_price'])) {
								$startPriceShow = number_format($inquiryInfo['iuq_tac_start_price']);
								$startPrice = $inquiryInfo['iuq_tac_start_price'];
								$startPriceFlag = true;
							}


							$iuqAgdNo = null;

							$preAftereffectsGrade01PriceFlag = false;
							$preStartPriceFlag = false;
							$preAftereffectsGrade01Price = 0;
							$preStartPrice = 0;
							$note = null;
							$rowColorClassDifferencePrice = null;
							if (isset($inquiryInfo['iuq_agd_no']) && !_IsNull($inquiryInfo['iuq_agd_no'])) {

								$iuqAgdNo = $inquiryInfo['iuq_agd_no'];

								//2���ܰʹߤ��������ξ��
								if ($inquiryInfo['iuq_agd_no'] > 1) {
									//���������������No�����ꤹ�롣
									$preNo = $inquiryInfo['iuq_agd_no'] - 1;
									//���������礻�����������롣
									$condition4pre = array();
									$condition4pre['iuq_inquiry_id'] = $inquiryId;									//��礻ID
									$condition4pre['iuq_agd_no'] = $preNo;												//���������������No
									$condition4pre['add_tbl_inquiry_aftereffects_grade_decision'] = true;		//��礻_���㳲����(��)_����ơ��֥�򸡺��оݤ��ɲä��롣
									$preInquiryList = _GetInquiry($condition4pre, null, false, false, $activePage, null);

									$preInquiryInfo = null;
									if (!_IsNull($preInquiryList)) $preInquiryInfo = $preInquiryList[0];//1��Τ߸�������롣

									if (!_IsNull($preInquiryInfo)) {
										//������Ρ�
										//���㳲����(��)���
										if (isset($preInquiryInfo['iuq_agd_aftereffects_grade_01_price']) && !_IsNull($preInquiryInfo['iuq_agd_aftereffects_grade_01_price'])) {
											$preAftereffectsGrade01Price = $preInquiryInfo['iuq_agd_aftereffects_grade_01_price'];
											$preAftereffectsGrade01PriceFlag = true;
										}
										//�����
										if (isset($preInquiryInfo['iuq_tac_start_price']) && !_IsNull($preInquiryInfo['iuq_tac_start_price'])) {
											$preStartPrice = $preInquiryInfo['iuq_tac_start_price'];
											$preStartPriceFlag = true;
										}


										//���ͤ����ꤹ�롣
										$note .= "����";
										//$note .= sprintf('%04d/%02d/%02d', $preInquiryInfo['iuq_agd_create_date_y'], $preInquiryInfo['iuq_agd_create_date_m'], $preInquiryInfo['iuq_agd_create_date_d']);
										$note .= $preInquiryInfo['iuq_agd_create_date_yymmdd'];
										$note .= "��";
										$note .= $mstAftereffectsGrade01List[$preInquiryInfo['iuq_agd_aftereffects_grade_01_id']]['name'];
										$note .= "��";
										$note .= "�ץ�ߥ�=";
										if (isset($preInquiryInfo['iuq_agd_aftereffects_grade_01_price']) && !_IsNull($preInquiryInfo['iuq_agd_aftereffects_grade_01_price'])) {
											$note .= number_format($preInquiryInfo['iuq_agd_aftereffects_grade_01_price']);
										}
										$note .= " /";
										$note .= "����";
										$note .= $aftereffectsGrade01PriceShow;

										//��������������إڡ������󥯤�ĥ�롣
										$usrNameLink = "<a href=\"#".$trIdPrefix.$preInquiryInfo['iuq_inquiry_id']."_".$preInquiryInfo['iuq_agd_no']."\">%s</a>";
									}

									//2���ܰʹߤ��������ιԤ�ʸ�������ѹ����롣
									$rowColorClassDifferencePrice = "clrDifferencePrice";

								//�����������ξ��
								} else {
									//2���ܤ���������No�����ꤹ�롣
									$nextNo = $inquiryInfo['iuq_agd_no'] + 1;
									//2���ܤ���礻�����������롣
									$condition4pre = array();
									$condition4pre['iuq_inquiry_id'] = $inquiryId;									//��礻ID
									$condition4pre['iuq_agd_no'] = $nextNo;											//2���ܤ���������No
									$condition4pre['add_tbl_inquiry_aftereffects_grade_decision'] = true;		//��礻_���㳲����(��)_����ơ��֥�򸡺��оݤ��ɲä��롣
									$nextInquiryList = _GetInquiry($condition4pre, null, false, false, $activePage, null);

									$nextInquiryInfo = null;
									if (!_IsNull($nextInquiryList)) $nextInquiryInfo = $nextInquiryList[0];//1��Τ߸�������롣
									if (!_IsNull($nextInquiryInfo)) {
										//2���ܤ��������إڡ������󥯤�ĥ�롣
										$usrNameLink = "<a href=\"#".$trIdPrefix.$nextInquiryInfo['iuq_inquiry_id']."_".$nextInquiryInfo['iuq_agd_no']."\">%s</a>";
									}

								}
							}

							//���㳲����(��)��� - (������θ��㳲����(��)���)  (��2���ܰʹߤ��������ξ��Ϻ��ۤ�û����롣)
							$subTotalAftereffectsGrade01Price = $aftereffectsGrade01Price - $preAftereffectsGrade01Price;
							$subTotalAftereffectsGrade01PriceShow = null;
							if ($aftereffectsGrade01PriceFlag || $preAftereffectsGrade01PriceFlag) {
								$subTotalAftereffectsGrade01PriceShow = number_format($subTotalAftereffectsGrade01Price);
							}

							//����� - (������������)  (��2���ܰʹߤ��������ξ��Ϻ��ۤ�û����롣)
							$subTotalStartPrice = $startPrice - $preStartPrice;
							$subTotalStartPriceShow = null;
							if ($startPriceFlag || $preStartPriceFlag) {
								$subTotalStartPriceShow = number_format($subTotalStartPrice);
							}



							//��ñ�̤ι�פ�׻����롣
							//������
							$ymTotalAftereffectsGrade01Price += $subTotalAftereffectsGrade01Price;
							//�����
							$ymTotalStartPrice += $subTotalStartPrice;


							//����ۡ������ۤ�0�ξ�硢��ɽ���ˤ��롣
							if (((!_IsNull($subTotalStartPriceShow) && $subTotalStartPriceShow != "0"))
							     ||
							     ((!_IsNull($subTotalAftereffectsGrade01PriceShow) && $subTotalAftereffectsGrade01PriceShow != "0"))
							    ) {
//							if (true) {
								$count4TrColor++;
								$rowColorClass = null;
								if ($count4TrColor % 2 == 0) {
									$rowColorClass = 'rowColor02';
								} else {
									$rowColorClass = 'rowColor01';
								}

								$data3 .= "<tr class=\"".$rowColorClass." ".$rowColorClassDifferencePrice."\" ".(_IsNull($trId)?"":"id=\"".$trId."\"").">".$LF;
								$data3 .= "<td class=\"colWidth01 bgOneSetTotal colCenter\"></td>".$LF;
								$data3 .= "<td class=\"colWidth02 bgYmTotal colCenter\"></td>".$LF;
								$data3 .= "<td class=\"colWidth03 bdTop colCenter\">".$inquiryIdShow."</td>".$LF;
	//							$data3 .= "<td class=\"colWidth04 bdTop colCenter\">".$iuqAgdNo."</td>".$LF;
								$data3 .= "<td class=\"colWidth05 bdTop\" title=\"".htmlspecialchars($inquiryInfo['usr_name'])."\">".sprintf($usrNameLink, htmlspecialchars($usrName))."</td>".$LF;
								$data3 .= "<td class=\"colWidth06 bdTop colCenter\">".$year."</td>".$LF;
								$data3 .= "<td class=\"colWidth07 bdTop colCenter\">".$month."</td>".$LF;
								$data3 .= "<td class=\"colWidth08 bdTop colCenter\">".$day."</td>".$LF;
								$data3 .= "<td class=\"colWidth09 bdTop colCenter\">".htmlspecialchars($statusName)."</td>".$LF;
								$data3 .= "<td class=\"colWidth10 bdTop colCenter\">".htmlspecialchars($aftereffectsGrade01Name)."</td>".$LF;
								$data3 .= "<td class=\"colWidth11 bdTop colNum\">".$subTotalStartPriceShow."</td>".$LF;
								$data3 .= "<td class=\"colWidth12 bdTop colNum\">".$subTotalAftereffectsGrade01PriceShow."</td>".$LF;
								$data3 .= "<td class=\"colWidth13 bdTop colNum\"></td>".$LF;
								$data3 .= "<td class=\"colWidth14 bdTop\">".htmlspecialchars($note)."</td>".$LF;
								$data3 .= "<td class=\"colWidth15 bdTop colCenter\">".$url."</td>".$LF;
								$data3 .= "</tr>".$LF;

						    }
						}

					}

					//�����ñ�̤ι�פ�׻����롣(2007/12/18���ߡ�3����ñ��)
					//������
					$oneSetTotalAftereffectsGrade01Price += $ymTotalAftereffectsGrade01Price;
					//�����
					$oneSetTotalStartPrice += $ymTotalStartPrice;

//					//��ñ�̤�ʿ�Ѷ�ۤ�׻����롣
//					$ymAveragePrice = ($ymTotalStartPrice + $ymTotalAftereffectsGrade01Price) / $count;


					$data2 .= "<tr>".$LF;
					$data2 .= "<td class=\"bgOneSetTotal\"></td>".$LF;
//					$data2 .= "<td class=\"bgYmTotal bdTop\" colspan=\"9\">".$ymTotalYm."�ι��</td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop\" colspan=\"8\">".$ymTotalYm."�ι��</td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop colNum\">".number_format($ymTotalStartPrice)."</td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop colNum\">".number_format($ymTotalAftereffectsGrade01Price)."</td>".$LF;
//					$data2 .= "<td class=\"bgYmTotal bdTop colNum\">".number_format($ymAveragePrice, 2)."</td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop\"></td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop\"></td>".$LF;
					$data2 .= "<td class=\"bgYmTotal bdTop\"></td>".$LF;
					$data2 .= "</tr>".$LF;

					$data2 .= $data3;
				}

				//�����ñ�̤ι�׶�� = ������ + ����� ��׻����롣
				$oneSetTotalPrice = $oneSetTotalStartPrice + $oneSetTotalAftereffectsGrade01Price;

				//�����ñ�̤�ʿ�Ѷ�ۤ�׻����롣
				$oneSetAveragePrice = $oneSetTotalPrice / INQUIRY_PRICE_TOTAL_MONTH;

				//100��ñ�̤��ڤ�ΤƤ롣
				$floorOneSetAveragePrice = $oneSetAveragePrice / 1000;
				$floorOneSetAveragePrice = floor($floorOneSetAveragePrice);
				$floorOneSetAveragePrice *= 1000;

				$data1 = null;
				$data1 .= "<tr>".$LF;
//				$data1 .= "<td class=\"bgOneSetTotal bdTop\" colspan=\"10\" rowspan=\"2\">".$oneSetTotalYm."�ι�ס�1����ʿ��</td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop\" colspan=\"9\" rowspan=\"2\">".$oneSetTotalYm."�ι�ס�1����ʿ��</td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop clrOneSetTotalPrice colNum\" colspan=\"2\">".number_format($oneSetTotalPrice)."</td>".$LF;
//				$data1 .= "<td class=\"bgOneSetTotal bdTop clrOneSetAveragePrice colNum\" rowspan=\"2\">".number_format($oneSetAveragePrice, 2)."</td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop clrOneSetAveragePrice colNum\" rowspan=\"2\">".number_format($floorOneSetAveragePrice)."</td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop\" rowspan=\"2\"></td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop\" rowspan=\"2\"></td>".$LF;
				$data1 .= "</tr>".$LF;

				$data1 .= "<tr>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop clrOneSetTotalPrice colNum\">".number_format($oneSetTotalStartPrice)."</td>".$LF;
				$data1 .= "<td class=\"bgOneSetTotal bdTop clrOneSetTotalPrice colNum\">".number_format($oneSetTotalAftereffectsGrade01Price)."</td>".$LF;
				$data1 .= "</tr>".$LF;

				$data .= $data1;
				$data .= $data2;
			}
			echo $data;
?>
						</tbody>
					</table>
<!--
					<div class="page"><?=$link?></div>
-->
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

_Log("[/inquiry_price/index.php] end.");

?>