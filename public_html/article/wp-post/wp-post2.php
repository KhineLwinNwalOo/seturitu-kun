<?php
/**
 * This is One of the programs of the pair for WordPress. Another One is wp_db.class.php.
 *   use PHP5.3+, PDO, MySQL
 *
 * このファイルはワードプレス、データベースの設定項目を含むファイルです。
 * PHPが正常に動作しないサーバーでは直接設定項目が表示される可能性があります。
 * 設定後の動作確認を必ず行ってください。
 *
 */
require_once('wp_db.class.php');
// データベース設定
$db_driver = 'mysql'; // データベース種類
$db_name = 'sinkaisha_seturitukuncom'; // データベース名
$db_user = 'sinkaisha_user'; // データベースユーザー名
$db_password = 'C091e83c31dbB0f'; // データベースパスワード
$db_host = 'mysql31.xserver.jp'; // データベースホスト名
$db_charset = 'utf8'; // データベース文字コード utf8 || sjis || ujis
$db_port = 3306; // データベースポート番号
$main_table = 'wp_posts'; // テーブル名
//$post_type = 'post'; // 投稿タイプ
//$taxonomy = 'category'; // タクソノミー
$wp_url = 'http://www.seturitu-kun.com/article/'; // WordPress URL
$wp_permalink = 2; // パーマリンク設定 1:デフォルト/数字ベース    2:日付と投稿名/月と投稿名/投稿名    ※ 1 に固定でも可

// データ出力用設定
$html_charset = 'UTF-8'; //html文字コード UTF-8 || SJIS || EUC-JP
$content_size = 50; // 投稿内容表示文字数
$continue_text = '...'; // 投稿内容省略文字
$category_size = 1; // 投稿カテゴリー表示数
$datetime_format = 5; // 0:2012-12-24 12:00:00  1:2012-12-24  2:2012/12/24  3:12/24 4:12/24 12:00  5:2012年12月24日
$article_num = 5; // 投稿表示数
$archive_num = 10; // アーカイブ表示数
$sort = 1; // 投稿表示順 1:投稿日時（新→古）  2:投稿日時（古→新）  3:更新日時（新→古）  4:更新日時（古→新）  
$limit = 100; // データベース読み込み件数
$archive_format = 2; // アーカイブ形式 1:/年/    2:/年/月/    3:/年/月/日/
$archive_category = '新着情報'; // アーカイブカテゴリー名
$archive_display = false; // 新着情報アーカイブ表示 true/false

// データベース読み込み用オブジェクト
$db = new wordpress_db($db_name, $db_user, $db_password, $db_host, $db_charset, $db_driver, $db_port);
// データベース読み込み post
$db_array = $db->db_select($main_table, 'post', $sort, $limit);
// データベース読み込み custom post
// $info_array = $db->db_select($main_table, 'information', $sort, $limit);
$db->close();
$wp = $db->value_settings($db_array, $datetime_format, $content_size, $continue_text, $category_size, $html_charset);
$wp_categorised = $db->divide_category($wp);
$wp_archives = $db->archive($wp, $archive_format, $wp_permalink, $datetime_format, $archive_category);
?>


<?php $cat_slug = 'information' ?>
<?php if (!empty($wp_categorised[$cat_slug])) : $categorised_post = $wp_categorised[$cat_slug]; ?>
<div id="wp_<?php echo $cat_slug; ?>">
<h3><a href="<?php echo $wp_url . $categorised_post[0]['slug']; ?>/"><?php echo $categorised_post[0]['category']; ?></a></h3>
<ul>
<?php
for ($i = 0; $i < $article_num; $i++) {
if (empty($categorised_post[$i])) break;
extract($categorised_post[$i], EXTR_OVERWRITE);
echo <<< WP_EOF
<li><a href="{$wp_url}{$slug}/{$post_name}/"><span>&gt; </span>{$post_title}</a></li>
WP_EOF;
}
?>
</ul>
</div>
<?php endif; ?>

<?php if (!empty($wp_categorised)) : foreach ($wp_categorised as $category => $categorised_post) : ?>
<?php if ($category != 'information') : ?>
<div id="wp_<?php echo $category; ?>">
<h3><a href="<?php echo $wp_url . $categorised_post[0]['slug']; ?>/"><?php echo $categorised_post[0]['category']; ?></a></h3>
<?php $url = str_replace('/', '\/', $wp_url); if (preg_match("/^{$url}{$category}\/.*$/", 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])) : ?>
<ul>
<?php foreach ($categorised_post as $post) : ?>
<?php if (empty($post) || !$post['post_id']) break; ?>
<?php print <<< WP_EOF
<!-- output repeat start -->
<li><a href="{$wp_url}{$post['slug']}/{$post['post_name']}/"><span>&gt; </span>{$post['post_title']}</a></li>
<!-- output repeat end -->
WP_EOF;
?>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</div>
<?php endif; ?>
<?php endforeach; endif; ?>

<?php if ($archive_display) : // アーカイブ表示例 ?>
<div id="wp_archive">
<h3><?php echo $archive_category; ?> アーカイブ</h3>
<ul>
<?php
for ($i = 0; $i < $archive_num; $i++) {
if (empty($wp_archives[$i])) break;
extract($wp_archives[$i], EXTR_OVERWRITE);
echo <<< WP_EOF
<li><a href="{$wp_url}{$link}"><span>&gt; </span>{$date}({$count})</a></li>
WP_EOF;
}
?>
</ul>
</div>
<?php endif; ?>
