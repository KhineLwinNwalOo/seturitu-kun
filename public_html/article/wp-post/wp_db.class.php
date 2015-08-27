<?php
/**
 * This is One of the programs of the pair for WordPress. Another One is wp-article.php.
 *   use PHP5.3+, PDO, MySQL
 */
class wordpress_db extends PDO {

	function __construct($db_name, $db_user, $db_password, $db_host='localhost', $db_charset='utf8', $db_driver='mysql', $db_port=3306) {
		// MySQL接続設定
		$this->dsn = "$db_driver:host=$db_host;dbname=$db_name;port=$db_port";
		$this->user = $db_user;
		$this->password=$db_password;
		// MySQL接続
		try {
			$this->db = new PDO($this->dsn, $this->user, $this->password, 
				array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET $db_charset")
			);

		} catch (PDOException $e) {
//			echo 'Connection failed: ' . $e->getMessage();
		}
	}

	function db_table_check() {
		$query = 'SHOW TABLES';
		$stmt = $this->db->prepare($query);
		$result = $stmt->execute();
		$res = $stmt->fetchAll();
// PDO エラー表示
//print_r($stmt->errorInfo());
		return isset($res) ? $res : false;
	}

	/* データ抽出 */
	function db_select($main_table=false, $post_type = 'post', $sort = 1, $limit = 100) {
		$rows = array();
		switch ($sort) {
			case 1:
				$sort = 'ORDER BY wp_posts.post_date DESC';
				break;
			case 2:
				$sort = 'ORDER BY wp_posts.post_date ASC';
				break;
			case 3:
				$sort = 'ORDER BY wp_posts.post_modified DESC';
				break;
			case 4:
				$sort = 'ORDER BY wp_posts.post_modified ASC';
				break;
			default:
				$sort = '';
				break;
		}
		/* データベース読み込み wp_posts, wp_terms, wp_relationships */
		$query = "SELECT * FROM $main_table 
					RIGHT OUTER JOIN wp_term_relationships ON wp_posts.ID = wp_term_relationships.object_id 
					JOIN wp_term_taxonomy ON wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id
					JOIN wp_terms ON wp_terms.term_id = wp_term_taxonomy.term_id
					WHERE post_status = :post_status && post_type = :post_type && taxonomy = :taxonomy 
					$sort 
					LIMIT $limit";
		$stmt = $this->db->prepare($query);
		$post_status = 'publish';
//		$post_type = 'post';
		$taxonomy = 'category';
		$stmt->bindValue(':post_status', $post_status, PDO::PARAM_STR);
		$stmt->bindValue(':post_type', $post_type, PDO::PARAM_STR);
		$stmt->bindValue(':taxonomy', $taxonomy, PDO::PARAM_STR);
		$result = $stmt->execute();
		$i=0;
		while ($res = $stmt->fetch()) {
			$rows[$i] = $res;
			$i++;
		}
		return isset($rows) ? $rows : false;
	}

	function value_settings($db_array = false, $datetime_format = 0, $content_size = 20, $continue_text = '...', $category_size = 0, $html_charset = 'UTF-8') {
		if ($db_array) {
			$wp = array();
			$j = count($db_array);
			for ($i = 0; $i < $j; $i++) {
				$v = $db_array[$i];
				if ((isset($db_array[$i-1]['ID']) && $db_array[$i-1]['ID'] != $db_array[$i]['ID']) || $i === 0) {
					$post_id = $v['ID'];
					$post_title = $v['post_title'];
					$post_name = $v['post_name'];
					$raw_post_date = $v['post_date'];
					$post_date = wordpress_db::datetime_format($v['post_date'], $datetime_format);
					$post_modified = wordpress_db::datetime_format($v['post_modified'], $datetime_format);
					$comment_count = $v['comment_count'];
					$post_content = mb_strimwidth(strip_tags($v['post_content']), 0, $content_size, $continue_text, $html_charset);
					$guid = $v['guid'];
					$category = ($category_size) ? $v['name'] : '';
					$i_category = 1;
					$slug = $v['slug'];
				} else {
					if ($i_category < $category_size) {
						$category .= '&nbsp;' . $v['name'];
						++$i_category;
					}
				}
				if ((isset($db_array[$i+1]['ID']) && $db_array[$i+1]['ID'] != $db_array[$i]['ID']) || !isset($db_array[$i+1]['ID'])) {
					$wp[] = array('post_id' => $post_id, 
									'post_title' => $post_title, 
									'post_name' => $post_name, 
									'raw_post_date' => $raw_post_date, 
									'post_date' => $post_date, 
									'post_modified' => $post_modified, 
									'comment_count' => $comment_count, 
									'post_content' => $post_content, 
									'guid' => $guid, 
									'category' => $category,
									'slug' => $slug, 
									);
				}
			}
		}
		return isset($wp) ? $wp : false;
	}

	function datetime_format($datetime, $datetime_format = 0) {
		switch ($datetime_format) {
			case 1: 
				$datetime_array = explode(' ', $datetime);
				$date_array = explode('-', $datetime_array[0]);
				for ($i = 0; $i < count($date_array); $i++) {
					if ($i == 0 && $date_array[$i] != '99') {
						$datetime = $date_array[$i];
					} elseif ($date_array[$i] != '99') {
						$datetime .= '-' . $date_array[$i];
					}
				}
			break;
			case 2: 
				$datetime_array = explode(' ', $datetime);
				$date_array = explode('-', $datetime_array[0]);
				for ($i = 0; $i < count($date_array); $i++) {
					if ($i == 0 && $date_array[$i] != '99') {
						$datetime = $date_array[$i];
					} elseif ($date_array[$i] != '99') {
						$datetime .= '/' . $date_array[$i];
					}
				}
			break;
			case 3: 
				$datetime_array = explode(' ', $datetime);
				$date_array = explode('-', $datetime_array[0]);
				for ($i = 0; $i < count($date_array) - 1; $i++) {
					if ($i == 0 && $date_array[$i] != '99') {
						$datetime = $date_array[$i];
					} elseif ($date_array[$i] != '99') {
						$datetime .= '/' . $date_array[$i];
					}
				}
			break;
			case 4: 
				$datetime_array = explode(' ', $datetime);
				$date_array = explode('-', $datetime_array[0]);
				$date_array = array_merge($date_array, explode(':', $datetime_array[1]));
				for ($i = 1; $i < count($date_array); $i++) {
					if ($i == 1 && $date_array[$i] != '99') {
						$datetime = $date_array[$i];
					} elseif ($i < 3 && $date_array[$i] != '99') {
						$datetime .= '/' . $date_array[$i];
					} elseif ($i == 3 && $date_array[$i] != '99') {
						$datetime .= ' ' . $date_array[$i];
					} elseif ($date_array[$i] != '99') {
						$datetime .= ':' . $date_array[$i];
					}
				}
			break;
			case 5: 
				$datetime_array = explode(' ', $datetime);
				$date_array = explode('-', $datetime_array[0]);
//				$time_array = explode(':', $datetime_array[1]);
				for ($i = 0; $i < count($date_array); $i++) {
					if ($i == 0 && $date_array[$i] != '99') {
						$datetime = $date_array[$i] . '年';
					} elseif ($i == 1 && $date_array[$i] != '99') {
						$datetime .= $date_array[$i] . '月';
					} elseif ($i == 2 && $date_array[$i] != '99') {
						$datetime .= $date_array[$i] . '日';
					}
				}
			break;
			default:
			$datetime = $datetime;
			break;
		}
		return isset($datetime) ? $datetime : false;
	}

	function archive_link_format($datetime, $archive_format = 2, $wp_permalink = 1) {
		$datetime_array = explode(' ', $datetime);
		$date_array = explode('-', $datetime_array[0]);
		if ($archive_format < 3) unset($date_array[2]);
		if ($archive_format < 2) unset($date_array[1]);
		switch ($wp_permalink) {
			case 1: 
				$archive_link = '?m=' . implode($date_array, '');
				break;
			case 2: 
				$archive_link = implode($date_array, '/');
				break;
			default: 
				$archive_link = implode($date_array, '/');
		}
		return isset($archive_link) ? $archive_link : false;
	}

	function divide_category($wp = array()) {
		if (!empty($wp)) {
			foreach ($wp as $key => $value) {
				$category = $value['slug'];
				$wp_categorised[$category][] = $value;
			}
		}
		return isset($wp_categorised) ? $wp_categorised : false;
	}

	function archive($wp = array(), $archive_format = 2, $wp_permalink = 1, $datetime_format = 0, $archive_category = false) {
		if (!empty($wp)) {
			$wp_archive = array();
			foreach ($wp as $key => $value) {
				if ($archive_category && $value['category'] == $archive_category) {
					$datetime_array = explode(' ', $value['raw_post_date']);
					$date_array = explode('-', $datetime_array[0]);
					$year = $date_array[0];
					$month = $date_array[1];
					$day = $date_array[2];
					switch ($archive_format) {
						case 1: 
							$key_date = $date_array[0] . '-99-99 99:99:99';
							break;
						case 2: 
							$key_date = $date_array[0] . '-' . $date_array[1] . '-99 99:99:99';
							break;
						case 3: 
							$key_date = $date_array[0] . '-' . $date_array[1] . '-' . $date_array[2] . ' 99:99:99';
							break;
						default:
							$key_date = $date_array[0] . '-' . $date_array[1] . '-99 99:99:99';
					}
					if (empty($wp_archive[$key_date])) {
						$wp_archive[$key_date] = 1;
					} else {
						++$wp_archive[$key_date];
					}
				}
			}
			krsort($wp_archive);
			$wp_archives = array();
			foreach ($wp_archive as $key => $value) {
				$link = wordpress_db::archive_link_format($key, $archive_format, $wp_permalink);
				$date = wordpress_db::datetime_format($key, $datetime_format);
				$wp_archives[] = array(
										'date' => $date,
										'count' => $value,
										'link' => $link,
									);
			}
		}
		return isset($wp_archives) ? $wp_archives : false;
	}

	function close() {
		$this->db = null;
	}

}
?>
