<?php
/*
 * [��������]
 * �إå���
 *
 * ��������2008/02/20	d.ishikawa	��������
 *
 */


include_once(dirname(dirname(__FILE__))."/common/include.ini");
_Log("[/admin/common_html/header.php] start.");
?>
<div id="header_top"></div>
<div id="header_center">
	<div id="header_center_1"></div>
	<div id="header_center_2"><h1><a href="<?=URL_BASE?>/" title="<?=ADMIN_TITLE?>"><?=ADMIN_TITLE?></a></h1></div>
	<div id="header_center_3"></div>
</div>
<?
_Log("[/admin/common_html/header.php] end.");
?>