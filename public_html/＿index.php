<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP" />
<meta name="keywords" content="ˡ����Ω,�����Ω,�������" />
<meta name="description" content="��ʬ�Ǥ�������뿷�����Ω��������ҡ���Ʊ��Ҥ�ˡ����Ω�Ͽ��������Ω����ˤ�Ǥ�������������Ż��괾�����е���������κ����ޤǡ����̤˽��ä����Ϥ��������OK�������ڤˤ����̤���������" />
<link rel="icon" type="image/png" href="img/etc/favicon.png">
<link rel="shortcut icon"  href="img/etc/favicon.ico" />
<link rel="stylesheet" href="css/import.css" type="text/css" />
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<script language="javascript" src="common/js/util.js" type="text/javascript"></script>
<script language="javascript" src="common/js/libs/mootools-core.js" type="text/javascript"></script>
<script language="javascript" src="common/js/libs/mootools-more.js" type="text/javascript"></script>
<script language="javascript" src="common/js/libs/smoothbox.js" type="text/javascript"></script>


<link href="css/style-top.css" rel="stylesheet" type="text/css">



<title>���������Ω�����ˡ����Ω</title>
</head>

<body id="home">

<?php
session_start();
$loginInfo = $_SESSION[SID_LOGIN_USER_INFO];
if (!empty($loginInfo)) {
    require_once("common/include.ini");
    $loginForm = _GetLoginUserNameHtml($loginInfo);
}
?>

<div id="wrapper">
	<div id="header">
	    <div id="headerContents">
	    <div class="header img"><img src="img/head/header_logo.jpg" alt="���������Ω����"></div>
		<img src="img/head/header_price.jpg" alt="���������Ω�ĥ����ƥ������2,800�ߡ��Ż��괾ǧ��4,000�ߡ���Ʊ���LLC��Ω�ĥ����ƥ������2,800�ߡ��Ż��괾����2,000��" id="price">
		<img src="img/head/header_phone.jpg" alt="�����äǤΤ��䤤��碌��03-3586-1523�ޤ�" id="phone">
		<ul id="subnav">
		<li style="margin:0 10px 0 0 ;padding:0 10px 0 0;border-right:solid 1px rgb(255,255,255);"><a href="about/">��ҳ���</a></li>
		<li><a href="law/">���꾦���</a></li>
		</ul>
		<div id="globalnav">
		<ul id="gn">
			<li id="gn_home"><a href="index.php">�ۡ���</a></li>
			<li id="gn_guide"><a href="guide/">�����ѥ�����</a></li>
			<li id="gn_registration"><a href="regist/">�����ѿ�����</a></li>
<!--		<li id="gn_experience"><a href="./experience/">�θ��Ԥ���</a></li> -->
			<li id="gn_faq"><a href="faq/">�褯�������</a></li>
			<li id="gn_inquiry"><a href="inquiry/">���䤤��碌</a></li>
		</ul>
	    </div><!-- End globalnav -->
		</div>
	</div><!-- End header -->

	

	<div id="wrapper_content">
		<div id="maincontent">
        <img src="http://www.sin-kaisha.jp/img/notice.gif">
<h1 class="top">���������Ω�����ˡ����Ω��</h1>
<div id="mc_home">
<section id="mainimage">
<hgroup style="display:block;">
<div class="def"><img src="img/maincontent/top_mainimage_h1.jpg" width="745" alt="��ʬ�Ǥ�������롢�����Ω�����ƥࡪ"></div>
<div class="def"><img src="img/maincontent/top_mainimage_h2.jpg" width="745" alt="ɬ�פʤΤϥ��󥿡��ͥåȤν����PC�������Ż��괾�����е���������κ����ޤǡ����̤˽��ä����Ϥ��������OK�������μ��Ϥ���ޤ���"></div>
</hgroup>
<img src="img/maincontent/top_mainimage_kabushiki.jpg" width="745" alt="���������Ω��ɬ�פ���ۤ�206,300��" id="mainimage_kabushiki" style="top:150px;">
<img src="img/maincontent/top_mainimage_llc.jpg" width="745" alt="��Ʊ���LLC��Ω��ɬ�פ���ۤ�64,300��" id="mainimage_llc">
<img src="img/maincontent/top_mainimage_genbutsu.jpg" width="745" alt="��������ҡ���Ʊ���LLC���ˡ���ʪ�л�ξ��ϼ������ۤ�2,000�ߤ��û�����ޤ���" id="mainimage_genbutsu">
<a href="regist/" id="mainimage_regist" title="�桼������Ͽ�򤹤��̵����"></a>
</section>

<section id="steps">
<div class="def"><img src="img/maincontent/top_steps_h1.jpg" width="735" alt="�ͥåȤǴ�ñ�������Ω"></div>
<strong style="display:block;"><img src="img/maincontent/top_steps_catch.jpg" width="735" alt="��Ω�ޤǤ��ä���4���ƥåס��ޥ˥奢�����ס���������ס�ɬ�פʤΤϥͥåȤ�PC������"></strong>
<div id="stepsText">
<p>������Ω����פǤϲ�Ҥ���Ω���뤿���ɬ�פ��괾���е������������٤Ƥ�WEB��Ǻ������������뤳�Ȥ��Ǥ��ޤ���</p>
<br>
<p>��������4���ߤ�����Ǥ����Ż��괾�䡢��ʪ�л��500���߰ʲ��ˤ��б���</p>
<br>
<p>�������Ω�˴ؤ����μ���̵�����Ǥ⡢���̤˽����ʤ������Ϥ��뤳�ȤǴ�ñ�˽���������Ǥ��ޤ���</p>
<br>
<p>�������Ω�򤪹ͤ��������ޤ��ϲ����Ͽ<<̵��>>����ɤ�����</p>
</div>
</section>

<section id="others" class="clearfix">
<div class="othersLine3 clearfix">
<div style="margin:0 15px 0 0;">
<a href="http://www.the-profit.biz/" target="_blank" title="��̳"><img src="img/maincontent/top_others_taxation.jpg" alt="��̳"></a>
<p>�������Ѥ路����̳������ˡ��и�˭�٤ʥץ�ե��å���ʥ뤬�б��������ޤ�������̵���Ǥ����Ѥ��������ޤ����ޤ��Ϥ����������</p>
<p class="readMore"><a href="http://www.the-profit.biz/" target="_blank">&gt;�ܤ����Ϥ�����</a></p>
</div>
<div>
<a href="http://www.josei-kin.jp/" target="_blank" title="������"><img src="img/maincontent/top_others_subsidy.jpg" alt="������"></a>
<p>��&quot;����������&quot;���ΤäƤ��뤱�ɡ��ɤ��������㤨��Τ��狼��ʤ��ġ��סֽ�����γۤ��ä����䤷�������פ�������ˤ��������ޤ���</p>
<p class="readMore"><a href="http://www.josei-kin.jp/" target="_blank">&gt;�ܤ����Ϥ�����</a></p>
</div>
<div style="margin:0 15px 0 0;">
<a href="http://www.uc-taisaku.jp/" target="blank" title="ͻ��"><img src="img/maincontent/top_others_financing.jpg" alt="ͻ��"></a>
<p>ͻ���ۤ������Τ˺��������ȸ��äƲ���ǤϤ���ޤ���ͻ��˶��������Τ˰���򤷤ơ���˾�ۤ����ۼ������ޤ��礦��</p>
<p class="readMore"><a href="http://www.uc-taisaku.jp/" target="_blank">&gt;�ܤ����Ϥ�����</a></p>
</div>
<div>
<a href="http://www.roudou-hoken.jp/" target="_blank" title="�Ҳ��ݸ���ϫƯ�ݸ�"><img src="img/maincontent/top_others_insurance.jpg" alt="�Ҳ��ݸ���ϫƯ�ݸ�"></a>
<p>�ݸ��������ȸ��ä�̤�����ΤޤޤǤϡ������Ȥ������˼��ˤ�����򤹤뤳�Ȥˡ����ε���˼Ҳ��ݸ�ϫ̳�Τ����̤��Ƥߤޤ��󤫡�</p>
<p class="readMore"><a href="http://www.roudou-hoken.jp/" target="_blank">&gt;�ܤ����Ϥ�����</a></p>
</div>

<div style="margin:10px 0; width:735px"><img src="img/maincontent/top_others_know.gif" alt="��Ω���ǲ����Ω���Ƥߤ�����ˤ��񤷤������ʤ��� ���¿������������椫��Ǥ������ץ�ˤ�Ǥ���Υ᡼��ץ�����̥ץ����ѹ��Ǥ��ޤ���"></div>

<div style="margin:0 15px 0 0; font-family:'�ҥ饮�γѥ� Pro W3', 'Hiragino Kaku Gothic Pro', '�ᥤ�ꥪ', Meiryo, Osaka, '�ͣ� �Х����å�', 'MS PGothic', sans-serif">
<a href="http://www.sin-kaisha.jp" target="blank" title="�����Ω��Ԥϡڿ������Ω.JP�� ���Ѥϸ�ʧ��206,800�ߡ�" style="float:none; clear:both;"><img src="img/maincontent/top_others_sin.jpg" alt="�������Ω.JP"></a>
<p style="width: 350px; text-align: center; font-weight:bold; border-bottom: 1px solid #EEE; margin-bottom: 10px; text-shadow: 2px 2px 1px rgba(0,0,0,0.1);"><span style="font-size: 28px;">�ȳ��ǰ� <span style="font-size: 34px; color:#ff9400;">206,300�ߡ�</span></span><br><span style="font-size: 22px;color:#8A8A8A;">�ץ�ˤ��٤Ƥ�Ǥ���ץ��</span></p>
<p style="width: 350px;">��ڤ˳��������Ω�������θ������פǡ����ʤ�206,300�ߡ����ץ����Ǥ���ˤ��¤��ʤ�ޤ���</p>
<p style="width: 350px;" class="readMore"><a href="http://www.sin-kaisha.jp" target="_blank">&gt;�ܤ����Ϥ�����</a></p>
</div>
<div style="font-family:'�ҥ饮�γѥ� Pro W3', 'Hiragino Kaku Gothic Pro', '�ᥤ�ꥪ', Meiryo, Osaka, '�ͣ� �Х����å�', 'MS PGothic', sans-serif">
<a href="http://www.llp-llc.jp/" target="_blank" title="LLP��LLC��Ω�ϡڹ�Ʊ���.JP�� ���Ѥϸ�ʧ��64,800�ߡ�" style="float:none; clear:both;"><img src="img/maincontent/top_others_llp.jpg" alt="��Ʊ���.JP"></a>
<p style="width: 350px; text-align: center; font-weight:bold; border-bottom: 1px solid #EEE; margin-bottom: 10px; text-shadow: 2px 2px 1px rgba(0,0,0,0.1);"><span style="font-size: 28px;">�ȳ��ǰ� <span style="font-size: 34px; color:#ff9400;">64,300�ߡ�</span></span><br><span style="font-size: 22px;color:#8A8A8A;">�ץ�ˤ��٤Ƥ�Ǥ���ץ��</span></p>
<p style="width: 350px;">��Ʊ�����Ω�ʤ餳���顪�����θ������פǡ����ʤ�64,300�ߡ����ץ����Ǥ���ˤ��¤��ʤ�ޤ���</p>
<p style="width: 350px;" class="readMore"><a href="http://www.llp-llc.jp/" target="_blank">&gt;�ܤ����Ϥ�����</a></p>
</div>

</div>

<br>
<br>
<div class="section" id="sec3">
<h2 class="title"><img src="img/top/140416/140416seturitu-31.png" width="735" height="132" alt="��ͤ�3���ǳ�����Ҥ��롪"/></h2>
<div class="section-body">
<div class="body">
<h3 class="section-title">�����ˡ�λܹԤ�ȼ����������Ҥ���Ω��³�������ǲ������ԡ��ɲ���<br>
�ޤ�졢������Ҥ������᤯��Ω���뤳�Ȥ���ǽ�ˤʤ�ޤ�����</h3><br>

<div class="entry">
<p><img src="img/top/140416/140416seturitu-17.png" width="166" height="132" alt="��ȯ����Ω�ס��罸��Ω��" class="pull-left"/>����ޤǤɤ����Ƥ���֤������äƤ���������浬����ű�Ѥ��졢ȯ����Ω�˴ؤ����ͻ���ؤλ��ܶ��ݴɾ���������������פˤʤä����Ȥǡ�������Ҥ���Ω��ɬ�פ��괾ǧ�ڤȳƼ������ФޤǤδ��֤������˲��������ޤ�����������Ҥ���Ω���֤ϡ�ȯ���ͤ���Ω���γ����򤹤٤ư���������ȯ����Ω��¾�νл�Ԥ�����罸��Ω��2�Ĥ�����ޤ���</p>
</div>
<div class="entry">
<p><img src="img/top/140416/140416seturitu-18.png" width="125" height="139" alt="" class="pull-right"/>���Τ���ȯ����Ω�ξ�硢ȯ���ͤ������Ω���˷��ꤷ�Ƥ���ɬ�פΤ��뾦�����Ź�ν���ϡ����ܶ�γۡ������߷ס�����ǯ�����δ��ܻ������ꤷ���л���ʧ�����ߤ���н����������С�<strong>���������Ω�μ�̳Ū�ʼ�³���������פ�����֤ϡ�3������д֤˹礤�ޤ���</strong></p>
<p>���������������Ω�ˤϡ����ˡ���μ������򡢤ޤ����괾�ȸ�����Ҥκ��ܵ�§����ɬ�פ�����ޤ�������Ū�ˤ�3���ǳ�����Ҥ���Ω���뤳�Ȥϲ�ǽ�Ǥ��������Ƴ�����Ҥ���Ω����ԤˤȤäơ�����Ϲ⤤�ϡ��ɥ�ȸ�����Ǥ��礦��</p>
</div>


<img src="img/top/140416/140416seturitu-19.png" width="163" height="155" alt="" class="pull-left"/><h3 class="section-title">���̤�ɬ�׻�������Ϥ������</h3>
<p>����������褷�������Ǥ��᤯1�ͤǳ�����Ҥ���Ω���������˾Ҳ𤷤����Τ����ֿ��������Ω����פǤ���������ȯ�����ܿͤ�����ΰ��վ����μ������Ҥΰ��դ��Ѱա��ޤ�����ͻ���ؤؤλ��ܶ�ʧ���������μ�̳��ɬ�פǤ��������Υ����ƥ����Ѥ���С����������Ω�˷и��Τʤ����⡢ɽ���������̤�ɬ�׻�������Ϥ�������ǽ��ര���ޤǥ��ࡼ���ˤ��Ȥ����Ӥޤ���</p>
<p>���γ��������Ω�����ƥ�ϡ����������Ω��ɬ�פ��괾�������е�����ο����������ƥͥåȾ�Ǻ������뤳�Ȥ��Ǥ��ޤ����ޤ����괾���Ż��괾����Ѥ��Ƥ���Τǡ���������פ�ޤ���<strong style="color:#f15a24;">�ޥ˥奢�������Ȥ⤳�Υ����ƥ�Ǥ�ɬ�פ���ޤ���</strong></p>
<p>���ˡ����ܶ�ۤ��礭���ƶ����븽ʪ�л��500���߰ʲ��ˤˤ��б����Ƥ��ޤ���<br>
��ñ�˥桼������Ͽ��̵���˽����Τǡ�������Ҥ򤽤Ĥʤ������ԡ��ǥ�������Ω���������ϡ�1�٤Τ����ƤߤƤϤ������Ǥ��礦����</p>
<br>
</div>
</div>
</div>
<div class="section" id="sec2">
<h2 class="title"><img src="img/top/140416/140416seturitu-30.png" width="735" height="133" alt="��ͤǣ����ǹ�Ʊ��Ҥ��롪"/></h2>
<div class="section-body">
<div class="body">
<div class="entry">
<p><strong>���������Ω��������Ѥ���С���Ʊ��Ҥ���Ω���������ƥ������2800�ߡ��Ż��괾������2000�ߡ���Ͽ�ȵ���6���ߤι��64800�ߡʸ�ʪ�л�ξ���2000�߲û��ˤǡ������μ����ʤ��Ƥ⡢���̤˽��ä�ɬ�׻�������Ϥ�������ǡ���ʬ1�ͤǴ�ñ�ˡ�1���ȸ���Ķû���֤���Ω���뤳�Ȥ���ǽ�Ǥ���</strong></p>
</div>
<br>
<div class="entry">
<h3 class="section-title">2006ǯ�ο����ˡ��ǧ���줿��Ʊ��Ҥϡ�<br>
�����˵��Ȥ�֤����ȲȤ��礭�ʴؿ��򽸤�Ƥ��ޤ���</h3>
<p><img src="img/top/140416/140416seturitu-09.png" width="200" height="133" alt="" class="pull-right"/>��Ω�������äΰ��Ӥ�é�äƤ��ꡢ2012ǯ�ˤϡ�������Ω����1���Ҥ�Ķ��������Ǥ⡢��2��3��Ҥ˵ڤ�Ǥ��ޤ������η����Ϻ����³���ȹͤ����ޤ���</p>
<p>������ͳ�ϡ���Ʊ��Ҥ���Ω���Ѥ��¤��ʳ�����Ҥ���Ͽ�ȵ��Ǥϡ����ܶ�γۤ�0��7��ǡ����γۤ�15���ߤˤߤ��ʤ����ϡ���Χ15���ߤ��Ф�����Ʊ��Ҥ�6���ߡˡ�����ʬ�ۤ�бĤˤĤ��Ƽ�ͳ�٤��⤯�������߷פ˶�ϫ���뤳�Ȥ�̵�������ˡ���Ұ����бļ��Ը�����ġ���ͭ�ȷбĤ����פ�����ҷ��֤ˤ�ؤ�餺���Ұ��ϼ�ʬ�νл񤷤�ʬ�����ˤ�����Ǥ�����ʤ�ͭ����Ǥ�β�ҤǤ��뤳�Ȥ��󤲤��ޤ���</p>
</div>
<br>

<img src="img/top/140416/140416seturitu-10.png" width="163" height="163" alt="" class="pull-left"/><h3 class="section-title">��û1������Ω��ǽ</h3>
<p>��Ʊ��Ҥϡ��������Ǥθ��ڿͤ��괾ǧ�ڤ��פ�ʤ��Τǡ�������Ҥ���᤯��Ҥ���Ω���뤳�Ȥ���ǽ�ǡ����������Ω��JP���󶡤���<strong style="color:#f15a24;">���������Ω����Υ����ƥ�����Ѥ���С�1�ͤǡ��������û1������Ω���뤳�Ȥ���ǽ�Ǥ���</strong>��Ω���˾��������ϡ����վ�������ɬ�׽�����Ѱդ��������Ǥ��ޤ���</p>
<p style="padding:40px 0; text-align:center;"><img src="img/top/140416/140416seturitu-11.png" width="670" height="38" alt="����Ԥϡ����ܶ�������Ԥ������Ǥ���"/></p>

</div>
</div>
</div>
<br>
<div class="section" id="sec4">
<h2 class="title"><img src="img/top/140416/140416seturitu-32.png" width="735" height="89" alt="��Ҥμ�������ħ"/></h2>
<div class="section-body">
<div class="body">
<div class="entry">
<p style="text-align:center; padding:20px 0;"><img src="img/top/140416/140416seturitu-20.png" width="561" height="190" alt="������ҡ�ʪŪ��ҡ˻�ʬ��ҡʿ�Ū��ҡ�"/></p>
<p>�ֲ�ҡפμ���ϡ�����������ҡ�������Ʊ��ҡ�������̾��ҡ���������Ҥΰʾ壴�Ĥ�����ޤ��������Σ��Ĥϡ�������ҡ�ʪŪ��ҡˤȻ�ʬ��ҡʿ�Ū��ҡˤǤ����Ʊ��ҡ���̾��ҡ�����ҤΣ��Ĥ�ʬ�व��ޤ���</p>
</div>
<div class="box">
<div class="box-left">
<p><img src="img/top/140416/140416seturitu-21.png" width="331" height="101" alt="�������"/></p>
<p>������ҤϽ�ͭ�ȷбĤ�ʬΥ���졢��Ҥμ¼�Ū�ʽ�ͭ�ԤǤ������ϷбĤ˴�Ϳ��������̳�μ��Ԥϡ� ��ҷбĤ�����ȤǤ���������������Ԥ��ޤ�������ˤ������������̤��ƷбĤ˴�Ϳ��ǽ�ʶ��׸�������ޤ���������Ū�˳���ϡ���ҷбĤ�ꡢ�������������ʼ��׸��ˤ����ž������������Ū�Ȥ���˽Ť����֤��ޤ���</p>
<p style="margin:15px 0 8px 0;"><img src="img/top/140416/140416seturitu-23.png" width="317" height="57" alt="��Ǥ"/></p>
<p>����ϡ��бĤ˴�Ϳ���ʤ��Τ���§�ǡ��л񤷤��ϰϤ���Ǥ���餤�ޤ���ͭ����Ǥ�ˡ��㤨�С���Ҥ���Ĥ��������ݻ�������硢�����Ϥ����λ��ڤ�ˤʤ�ޤ�������Һĸ��Ԥ��Ф�����Ǥ���餤�ޤ���</p>
<p style="text-align:center; "><img src="img/top/140416/140416seturitu-24.png" width="317" height="191" alt=""/></p>
</div>

<div class="box-right">
<p><img src="img/top/140416/140416seturitu-22.png" width="331" height="101" alt="��ʬ���"/></p>
<p>��ʬ��Ҥϡ���§�Ȥ��ƽл�ԡʼҰ��ˤ���դ˴�Ť�����ҷбĤ��ʤ��졢��̳���Ԥϡ��л�ԤǤ���Ұ������ǹԤ�����ͭ�ȷбĤ����β�������ҷ��֤Ǥ����ʤ�����Ʊ��ҤμҰ���������ͭ����Ǥ�Ұ��Ȥʤ뤿�ᡢ������Ҥ˽स�����꤬Ŭ�Ѥ���ޤ���</p>
<p style="margin:15px 0 8px 0;"><img src="img/top/140416/140416seturitu-23.png" width="317" height="57" alt="��Ǥ"/></p>
<p>��Ʊ��Ҥϡ��л��������ͭ����Ǥ�Ұ��Ǥ��ꡢ������ҤΤ褦�ʵ����߷פ����פʲ�ҷ��֤Ǥ�����ҤηбĤϡ���§�Ȥ��ƼҰ���������դ˴�Ť����Ԥ���ޤ�����Ҥ���Ω���Ѥ�������Ҥ���ٰ¤�����³�����ñ�������⡢�Ұ��ϷбĤ˴�Ϳ���Ƥ�л���ϰϤ���Ǥ���餦ͭ����Ǥ�ʤΤǡ��ʤ�٤���ñ���᤯��Ҥ���Ω���������ˤȤäƿ͵��Τ����ҷ��֤Ǥ�����̾��Ҥϡ���Һĸ��Ԥˤ�Ұ���Ϣ����Ǥ���餦̵����Ǥ�Ұ��Τߤǹ��������ҤǤ�����§�Ȥ��ơ��Ұ���������ɽ��������ޤ�����²���Τ����¤�줿���Ϳ��β�Ҥ�Ŭ������֤Ǥ���</p>
</div>
</div>
</div>
</div>
</div>

<div class="section" id="sec5">
<h2 class="title"><img src="img/top/140416/140416seturitu-33.png" width="735" height="99" alt="�����Ω���˴��ܻ������褦��"/></h2>

<div class="section-body">
<div class="body">
<div class="entry-header"><img src="img/top/140416/140416seturitu-27.png" width="289" height="318" alt="���ܻ���" class="pull-right"/>
<h3 class="section-title" style="padding-top:60px;">ȯ���ͤϲ�Ҥ���Ω���ˤϡ�<br>
��Ҥδ���Ū�ʻ������ꤹ��<br>
ɬ�פ�����ޤ���</h3>
<p>����Ū�ˤϡ���Ҥ�̾�ΤǤ��뾦�桢���Ȥ���Ū����Ź�ν���ϡ������߷ס����ܶ�γۡ�����ǯ�����Ǥ��������λ���ϡ���Ҿ������ꤹ����׻���Ǥ���</p>
</div><br>

<div class="step"> <img src="img/top/140416/140416seturitu-28.png" width="86" height="81" alt=""/></div>
<div class="entry">
<h4><img src="img/top/140416/140416seturitu-39.png" width="151" height="24" alt="���ä��긡Ƥ"/></h4>
<p>��Ҥ���Ω���ˤ����ˤĤ��ƽ�ʬ��Ƥ��ԤäƤ��ʤ�����Ω��λ���Ÿ�����礭�ʻپ㤭�������Ȥ�ͤ����ޤ��������Ω��˭�٤ʷи��������θ�����Ĳ����Ω������ȤȤ�Ϣ�Ȥ���������Ƥ��ä��긡Ƥ����ɬ�פ�����ޤ���</p>
</div>
<div class="step"> <img src="img/top/140416/140416seturitu-34.png" width="90" height="90" alt=""/></div>
<div class="entry">
<h4><img src="img/top/140416/140416seturitu-40.png" width="144" height="24" alt="�����Ĵ��"/></h4>
<p>����ˤĤ��Ƥϡ�������浬����ű�Ѥ���ޤ�������¾�Ԥ����Ѥ��뾦��䤳���������뾦��Ǥ����������ɻ�ˡ���񿨤���»��������ᤵ���ꥹ���⤢��Τǡ�<strong>�������ꤹ��ݤˤϡ�ɬ��ˡ̳�ɤ�ɽ����ˡ��ޤ᤿�����Ĵ����ԤäƤ���������</strong>�ޤ����ףţ¥����ȤΥ��ɥ쥹��������ǽ����Ĵ�٤Ƥ���������</p>
</div>
<div class="step"> <img src="img/top/140416/140416seturitu-35.png" width="75" height="90" alt=""/></div>
<div class="entry">
<h4><img src="img/top/140416/140416seturitu-41.png" width="162" height="24" alt="��Ū�����"/></h4>
<p>������Ū�ϡ�<strong>����Ԥ��������Ȥ�ޤ᤿���������礭�ʴ����������Ƥ���������</strong>�ɲä�Ǥ��ޤ��������κݤ���Ͽ�ȵ��Ǥ�������ȯ�����ޤ���������Ū�ϡ���ˡ�����ʤ�����������ǧ���졢������Ū���������ʶ��������׷�ϴ��¤���Ƥ��ޤ��ˤǤ���н�ʬ�Ǥ�����������ǧ�Ĥ�ɬ�פȤ�����Ȥ�¿���Τǡ�����Ȥ����̤��Ƥߤ�Ȥ褤�Ǥ��礦��</p>
</div>
<div class="step"> <img src="img/top/140416/140416seturitu-36.png" width="88" height="110" alt=""/></div>
<div class="entry">
<h4><img src="img/top/140416/140416seturitu-42.png" width="185" height="24" alt="����Ϥ����"/></h4>
<p>��Ź�ν���Ϥϲ�Ҥν���Τ��ȤǤ���<strong>��ʬ�ν��ཻ��ǤϤʤ��Τ���դ��Ƥ���������</strong>��Ź���֤�ʪ�郎���߼�ʪ��ξ��ϡ�ɬ�����Ȥ˻��Ѥ��뤳�Ȥ򥪡��ʡ��˹𤲡������ʡ��ξ����������Ƥ���������ʪ�����ˡ��ȿ�ϡ�����ط����˲��԰٤ȴ������졢���߼ڷ���β����ͳ�ˤʤ�ޤ���</p>
</div>
<div class="step"> <img src="img/top/140416/140416seturitu-37.png" width="70" height="83" alt=""/></div>
<div class="entry">
<h4><img src="img/top/140416/140416seturitu-43.png" width="187" height="24" alt="���ܶ�ۤη���"/></h4>
<p>���ܶ�γۤϡ������ˡ�λܹԤǣ��ߤǤ��ǽ�Ǥ�����<strong>��ҤΥ���å���ե������硼�Ȥ��ʤ��褦�ˤ�������;͵����ä��ۤ�ɬ�פȸ����ޤ���</strong>�ޤ���������Ҥ��罸��Ω�Ǥϡ�ï��������л񤹤�Τ��Ƿбĸ������ꤹ��Τǡ��������⽽ʬ�ͤ������ꤷ�Ƥ����������ޤ������ܶ��ʧ�������ͻ���ؤȤ�Ĵ����ɬ�פǤ���</p>
</div>
<div class="step"> <img src="img/top/140416/140416seturitu-38.png" width="79" height="57" alt=""/></div>
<div class="entry">
<h4><img src="img/top/140416/140416seturitu-44.png" width="123" height="24" alt="�����߷�"/></h4>
<p>�����߷פϡ������ˡ�λܹԤǡ��͡��ʵ����߷פ���ǽ�ˤʤ�ޤ���������Ω�����Ҥζ��������θ�����졢��Ŭ�ʵ����߷פ������ޤ���������	�ˤĤ��Ƥ⡢˭�٤ʷи���ͭ��������Ȥ��ηäϤȤƤ��Ϥˤʤ�ޤ�������ǯ�٤ˤĤ��Ƥϡ��̾�ǯ����Ȥ��ޤ�������ǯ�٤򤤤Ĥ��餤�ĤޤǤˤ��뤫�ϼ�ͳ�Ǥ�����<strong>��Ҥη軻������˻�����Ťʤ�ʤ��褦�ˤ��Ƥ���������</strong></p>
</div>
</div>
</div>
</div>


<div style="margin:20px 15px 15px 20px;">
<p>ˡ����Ω�Ͽ��������Ω����ˤ�Ǥ����
������Ҥ��Ʊ��Ҥʤɤ�ˡ����Ω��ɬ�פ��Ż��괾���е���������κ�����PC�β��̤˽��ä����Ϥ��������
�����μ����ʤ��Ȥ⡢ˡ����Ω��Ԥ����Ȥ��Ǥ��ޤ���
��������ˡ����Ω��Ԥ����ϡ������ͤΤ����˹�����Τ��������ػǤ��ޤ���
�����Ͽ�򤷤Ƥ��������ݤ����⤬ȯ�����뤳�Ȥϰ��ڤ���ޤ���Τǡ������ڤˤ����̤���������</p></div>
</section>

<img src="img/maincontent/top_phone.jpg" alt="�����äǤΤ����䡦���䤤��碌�� 03-3586-1523�ޤǡ����ջ��֤�ʿ��10:00��19:00" style="margin-bottom: 30px;">



</div><!-- End mc_home -->

		</div><!-- End maincontent -->
		
		<div id="sidebar">
            <div id="sd_login">
                <img src="img/login/aside_creditcard.jpg" title="" alt="���쥸�åȥ����ɤ�������ĺ���ޤ�" id="asideCreditcard">
                <?php if (!empty($loginInfo) && !empty($loginForm)): ?>
                    <div id="sd_menu"><?php echo $loginForm; ?></div>
                <?php else: ?>
                    <h2><img src="img/login/login.jpg" title="" alt="����ڡ����إ�����"></h2>

                    <form name="frmLoginSidebar" action="./login/" method="post" id="loginform">
                        <dl>
                            <dt>�᡼�륢�ɥ쥹</dt>
                            <dd><input type="text" name="e_mail" size="30" value=""/></dd>
                            <dt>�ѥ����</dt>
                            <dd><input type="password" name="pass" size="30" value=""/></dd>
                        </dl>
                        <div id="sd_lg_bt_login">
                            <input type="image" name="login" src="img/login/btn_login.jpg" alt="������"/>
                        </div>
                    </form>

                    <a id="sd_lg_bt_regist" href="regist/" title="" alt="�桼������Ͽ(̵��)">�桼������Ͽ(̵��)</a>

                    <a id="sd_lg_bt_confirm" href="login_remind/">�ѥ���ɤ�˺�줿���Ϥ�����</a>
                <?php endif; ?>

                <div id="article_wrap" class="nojs">
                    <?php
                    ob_start();
                    include '/article/wp-post/wp-post2.php';
                    $str = ob_get_flush();
                    echo mb_convert_encoding($str, 'EUCJP-win', 'UTF-8');
                    ?>
                </div>


<!--<div id="wp_seturitu">
<h3><a href="article/seturitu/">ˡ����Ω</a></h3>
</div>
-->
</div>

	<div id="asideOthers">
	<a href="faq/" id="asideFaq"><img src="img/login/aside_faq.jpg" title="" alt="�褯���뤴����Ϥ�����"></a>
	<img src="img/login/aside_gentei.jpg" title="" alt="������ꡡ�������ǲ�Ҥ���Ω�������ϡ��������عԤ�ɬ�פϤ���ޤ��󡪡����ʤ�������˹�����Τ��������ػǤ��ޤ���" id="asideGentei">
	<img src="img/login/todokede.jpg" title="" alt="��Ω����Ͻн��������Լ������������̵����">
	<img src="img/login/aside_partner.jpg" title="" alt="��Ω������Ź�罸��" style="margin-top:15px;">

	<div style="padding-top:15px;background-color:#fff;">
	<div class="fb-like-box" data-href="http://www.facebook.com/sinkaisha.jp" data-width="210" data-height="462" data-show-faces="true" data-stream="false" data-header="true"></div>
	</div>
	
	<strong><img src="img/login/aside_others.jpg" title="" alt="���Υ����󥸤�ޤȤ�ƥ��ݡ��ȡ������Ω��Ϣ�����ӥ�"></strong>
	<ul>
	<li><a href="http://www.kyo-ninka.jp/" target="_blank"><img src="img/login/aside_others_kyoninka.jpg" title="" alt="��ǧ��.JP"></a></li>
	<li><a href="http://www.kicho-daikou.jp/" target="_blank"><img src="img/login/aside_others_kichoudaikou.jpg" title="" alt="��Ģ���.JP"></a></li>
	<li><a href="http://www.josei-kin.jp/" target="_blank"><img src="img/login/aside_others_joseikin.jpg" title="" alt="������.JP"></a></li>
	<li><a href="http://www.npo-houjin.jp/" target="_blank"><img src="img/login/aside_others_npo.jpg" title="" alt="NPOˡ��.JP"></a></li>
	<li><a href="http://www.shugyo-kisoku.jp/" target="_blank"><img src="img/login/aside_others_shuugyoukisoku.jpg" title="" alt="���ȵ�§.JP"></a></li>
	<li><a href="http://www.roudou-hoken.jp/" target="_blank"><img src="img/login/aside_others_roudouhoken.jpg" title="" alt="ϫƯ�ݸ�.JP"></a></li>
	<li><a href="http://www.shakai-hoken.jp/" target="_blank"><img src="img/login/aside_others_shakaihoken.jpg" title="" alt="�Ҳ��ݸ�.JP"></a></li>
	<li><a href="http://www.uc-taisaku.jp/" target="_blank"><img src="img/login/aside_others_yuushitaisaku.jpg" title="" alt="ͻ���к�����.JP"></a></li>
	</ul>
    </div>
	<p style="padding: 0 0 20px 10px;">
	<a href="sitemap/">�����ȥޥå�</a><br />
	<a href="privacy/">�ץ饤�Х����ݥꥷ��</a>
	</p>
</div><!-- End sd_login -->

<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/ja_JP/all.js#xfbml=1&appId=348509651860808";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

		</div><!-- End sidebar -->

		<div class="end"></div>
	</div><!-- End wrapper_content -->

<!--<div id="wrm_link">test</div>-->
<style type="text/css">
<!--
#wrm_link {width:100%;height:10px;line-height:10px;margin:0;padding:0;font-size:10px;text-align:center;}
#wrm_link span {background-color:#ccc;color:#555;padding:1px 2px;}
#wrm_link a {color:#555;text-decoration:none;}
-->
</style>
<!-- Piwik --> 
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://woorom-com.ssl-xserver.jp/piwik/" : "http://piwik.woorom.com/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 9);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://piwik.woorom.com/piwik.php?idsite=9" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tracking Code -->

<!-- GoogleAnalytics -->
<script>
 (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
 (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
 m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
 })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

 ga('create', 'UA-39384215-46', 'seturitu-kun.com');
 ga('send', 'pageview');

</script>
<!-- End GoogleAnalytics -->


	<div id="footer">
		<span>Copyright (C) WOOROM All Rights Reserved.</span>
	</div><!-- End footer -->

</div><!-- End wrapper -->
</body>
</html>