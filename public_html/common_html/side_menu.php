<?php
/*
 * [��������]
 * �����ɥ�˥塼
 *
 * ��������2008/02/20	d.ishikawa	��������
 *
 */


include_once(dirname(dirname(__FILE__))."/common/include.ini");
_Log("[/admin/common_html/side_menu.php] start.");
$urlBase = URL_BASE;
?>
<h2>��˥塼</h2>

<div id="sidebar_center">

<?if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] == AUTH_WOOROM || $_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] == AUTH_CLIENT) {?>
<ul id="sidebar_menu">
<?if (false) {?>
<!--<li><a href="<?=$urlBase?>/" title="home">[home]</a></li>-->
	<li><a href="<?=$urlBase?>/item/" title="���ʰ���">[���ʰ���]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_ITEM?>" title="������Ͽ">[������Ͽ]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_BOTTLE_IMAGE?>" title="�ܥȥ���Ͽ">[�ܥȥ���Ͽ]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_DESIGN_IMAGE?>" title="Ħ��ѥ�������Ͽ">[Ħ��ʎߎ�������Ͽ]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_CHARACTER_J_IMAGE?>" title="Ħ���»���Ͽ">[Ħ���»���Ͽ]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_CHARACTER_E_IMAGE?>" title="Ħ��ѻ���Ͽ">[Ħ��ѻ���Ͽ]</a></li>
	<li><a href="<?=$urlBase?>/master/?mst_name=<?=MST_NAME_CATEGORY?>" title="���ƥ��꡼">[���ƥ��꡼]</a></li>
	<li><a href="<?=$urlBase?>/master/?mst_name=<?=MST_NAME_SUBCATEGORY?>" title="���֥��ƥ��꡼">[���֥��ƥ��꡼]</a></li>
	<li><a href="<?=$urlBase?>/inquiry/" title="��礻����">[��礻����]</a></li>
	<li><a href="<?=$urlBase?>/inquiry_price/" title="����۰���">[����۰���]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_INQ_FROM_MAIL?>" title="��礻Mail��Ͽ">[��礻Mail��Ͽ]</a></li>
	<li><a href="<?=$urlBase?>/master/?mst_name=<?=MST_NAME_STATUS?>" title="����">[����]</a></li>
	<li><a href="<?=$urlBase?>/master/?mst_name=<?=MST_NAME_STAFF?>" title="ô����">[ô����]</a></li>
<?}?>
	<li><a href="<?=$urlBase?>/inquiry/" title="��礻����">[��礻����]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_INQ?>" title="��礻������Ͽ">[��礻������Ͽ]</a></li>
<?//if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_manager_id'] == '1') {?>
<?//}?>
<!--
	<li><a href="#" title="��������">[��������]</a></li>
-->
</ul>
<?} else {?>
<ul id="sidebar_menu">
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_INQ?>" title="��礻����">[��礻����]</a></li>
	<li><a href="<?=$urlBase?>/pdf/" title="PDF����">[PDF����]</a></li>
</ul>
<?}?>

<ul id="sidebar_logout" class="logout">
	<li><a href="<?=$urlBase?>/?logout" title="��������">[��������]</a></li>
</ul>

</div>
<?
_Log("[/admin/common_html/side_menu.php] end.");
?>