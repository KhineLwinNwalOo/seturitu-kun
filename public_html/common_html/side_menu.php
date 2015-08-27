<?php
/*
 * [管理画面]
 * サイドメニュー
 *
 * 更新履歴：2008/02/20	d.ishikawa	新規作成
 *
 */


include_once(dirname(dirname(__FILE__))."/common/include.ini");
_Log("[/admin/common_html/side_menu.php] start.");
$urlBase = URL_BASE;
?>
<h2>メニュー</h2>

<div id="sidebar_center">

<?if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] == AUTH_WOOROM || $_SESSION[SID_ADMIN_LOGIN_INFO]['mng_auth_id'] == AUTH_CLIENT) {?>
<ul id="sidebar_menu">
<?if (false) {?>
<!--<li><a href="<?=$urlBase?>/" title="home">[home]</a></li>-->
	<li><a href="<?=$urlBase?>/item/" title="商品一覧">[商品一覧]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_ITEM?>" title="商品登録">[商品登録]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_BOTTLE_IMAGE?>" title="ボトル登録">[ボトル登録]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_DESIGN_IMAGE?>" title="彫刻パターン登録">[彫刻ﾊﾟﾀｰﾝ登録]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_CHARACTER_J_IMAGE?>" title="彫刻和字登録">[彫刻和字登録]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_CHARACTER_E_IMAGE?>" title="彫刻英字登録">[彫刻英字登録]</a></li>
	<li><a href="<?=$urlBase?>/master/?mst_name=<?=MST_NAME_CATEGORY?>" title="カテゴリー">[カテゴリー]</a></li>
	<li><a href="<?=$urlBase?>/master/?mst_name=<?=MST_NAME_SUBCATEGORY?>" title="サブカテゴリー">[サブカテゴリー]</a></li>
	<li><a href="<?=$urlBase?>/inquiry/" title="問合せ一覧">[問合せ一覧]</a></li>
	<li><a href="<?=$urlBase?>/inquiry_price/" title="請求額一覧">[請求額一覧]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_INQ_FROM_MAIL?>" title="問合せMail登録">[問合せMail登録]</a></li>
	<li><a href="<?=$urlBase?>/master/?mst_name=<?=MST_NAME_STATUS?>" title="状況">[状況]</a></li>
	<li><a href="<?=$urlBase?>/master/?mst_name=<?=MST_NAME_STAFF?>" title="担当者">[担当者]</a></li>
<?}?>
	<li><a href="<?=$urlBase?>/inquiry/" title="問合せ一覧">[問合せ一覧]</a></li>
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_INQ?>" title="問合せ情報登録">[問合せ情報登録]</a></li>
<?//if ($_SESSION[SID_ADMIN_LOGIN_INFO]['mng_manager_id'] == '1') {?>
<?//}?>
<!--
	<li><a href="#" title="ｘｘｘｘ">[ｘｘｘｘ]</a></li>
-->
</ul>
<?} else {?>
<ul id="sidebar_menu">
	<li><a href="<?=$urlBase?>/info/?xml_name=<?=XML_NAME_INQ?>" title="問合せ情報">[問合せ情報]</a></li>
	<li><a href="<?=$urlBase?>/pdf/" title="PDF作成">[PDF作成]</a></li>
</ul>
<?}?>

<ul id="sidebar_logout" class="logout">
	<li><a href="<?=$urlBase?>/?logout" title="ログアウト">[ログアウト]</a></li>
</ul>

</div>
<?
_Log("[/admin/common_html/side_menu.php] end.");
?>