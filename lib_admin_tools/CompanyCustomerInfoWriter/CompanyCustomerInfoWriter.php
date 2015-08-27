<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once dirname(__FILE__) . '/../AdminTool.php';
require_once dirname(__FILE__) . '/../venders/GoogleFormAPI/GoogleFormAPI.php';

/**
 * Description of CompanyCustomerInfoWriter
 *
 * @author hanai
 */
class CompanyCustomerInfoWriter implements AdminTool {
	
	const OUTPUT		= true;
	
	// PMA URL https://sv306.xserver.jp/phpmyadmin5/
	const DB_DSN		= 'mysql:dbname=sinkaisha_seturitukun;host=mysql31.xserver.jp';
	const DB_USER		= 'sinkaisha_kun';
	const DB_PASSWORD	= '7FBDFe3470EE230';
	
	private $result = array();

	public function run() {
		// 顧客会社情報取得
		$data = self::getCompanyCustomerInfo($this);
		// GoogleFormに情報を送信
		self::writerCompanyCustomerInfo($this, $data);
	}
	
	/**
	 * 顧客会社情報取得
	 * @param self $tool
	 * @return type
	 */
	private static function getCompanyCustomerInfo(self $tool) {
		$tool->result[] = 'Get Company Customer Info Start';
		try {
			$pdo		= self::getPdoInstance();
			$statement	= self::getPdoStatement($pdo);
			$result		= self::getCompanyCustomerInfoData($statement);
		} catch (PDOException $e) {
			throw new ErrorException('PDO Error ' . $e->getMessage());
		}
		$tool->result[] = 'Get Company Customer Info End';
		return $result;
	}
	
	private static function getPdoInstance() {
		$options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		);		
		return new PDO(self::DB_DSN, self::DB_USER, self::DB_PASSWORD, $options);
	}
	
	private static function getPdoStatement(PDO $pdo) {
		$tmpSql = array(
			"SELECT ",
			"	t1.usr_create_date								timestamp,",
			"	t1.usr_user_id									user_id,",
			"	CASE t3.cmp_company_type_id ",
			"		WHEN 1 THEN '株式'",
			"		WHEN 2 THEN '合同'",
			"		ELSE '' END 								company_type,",
			"	t1.usr_family_name 								filer_family_name,",
			"	t1.usr_first_name								filer_first_name,",
			"	concat(usr_zip1, '-', usr_zip2)					filer_zip,",
			"	m1.name 										filer_address1,",
			"	usr_address1									filer_address2,",
			"	usr_address2									filer_address3,	",
			"	concat(usr_tel1, '-', usr_tel2, '-', usr_tel3)	filer_mobile_phone_number,",
			"	usr_e_mail										filer_mail,",
			"	t3.cmp_company_name								company_name,",
			"	concat(cmp_zip1, '-', cmp_zip2)					company_zip,",
			"	m2.name 										company_address1,",
			"	cmp_address1									company_address2,",
			"	cmp_address2									company_address3,",
			"	t4.cmp_bod_family_name							representative_family_name,",
			"	t4.cmp_bod_first_name							representative_first_name,",
			"	t4.cmp_bod_family_name_kana						representative_family_name_kana,",
			"	t4.cmp_bod_first_name_kana						representative_first_name_kana,",
			"	CASE t3.cmp_business_start_month ",
			"		WHEN null THEN null",
			"		WHEN 1 THEN 12",
			"		ELSE t3.cmp_business_start_month - 1 END	company_settlement_month,",
			"	concat(t3.cmp_found_year, '平成(', t3.cmp_found_year - 1988, ')年', t3.cmp_found_month, '月', t3.cmp_found_day, '日')		company_found_date,",
			"	t3.cmp_note										company_remark",
			"FROM",
			"	tbl_user t1 ",
			"		LEFT JOIN tbl_user_company_relation t2 ",
			"		ON t1.usr_user_id = t2.usr_cmp_rel_user_id	",
			"		AND t2.usr_cmp_rel_del_flag = 0",
			"			LEFT JOIN tbl_company t3",
			"			ON t2.usr_cmp_rel_company_id = t3.cmp_company_id",
			"			AND t3.cmp_del_flag = 0",
			"				LEFT JOIN mst_pref m1",
			"				ON t1.usr_pref_id = m1.id",
			"				AND m1.del_flag = 0",
			"					LEFT JOIN mst_pref m2",
			"					ON t3.cmp_pref_id = m2.id",
			"					AND m2.del_flag = 0",
			"						LEFT JOIN tbl_company_board t4",
			"						ON t3.cmp_company_id = t4.cmp_bod_company_id ",
			"						AND t4.cmp_bod_post_id = 1",
			"						AND t4.cmp_bod_del_flag = 0",
			"WHERE ",
			"	t1.usr_del_flag = 0",
			"ORDER BY t1.usr_user_id DESC",
		);
		$sql = mb_convert_encoding(join(' ', $tmpSql), 'utf-8', 'eucjp');
		return $pdo->query($sql);
	}
	
	private static function getCompanyCustomerInfoData(PDOStatement $statement) {
		$results = array();
		while($row = $statement->fetch(PDO::FETCH_ASSOC)){
			$results[] = $row;
		}
		return $results;
	}
	
	/**
	 * GoogleFormに情報を送信
	 * @param self $tool
	 * @param array $data
	 */
	private static function writerCompanyCustomerInfo(self $tool, array $data) {
		$tool->result[] = 'Writer Company Customer Info Start[' . date('Y-m-d H:i:s') . ']';
		$results = array();
		$googleFormAPI = new GoogleFormAPI();
		for ($i = 0, $cnt = count($data); $i < $cnt; ++$i) {
			$googleFormAPI->init();
			$googleFormAPI->setPostData($data[$i]);
			$results[]	= $tmp = $googleFormAPI->send();
			$tmpResult	= $tmp? 'OK': 'NG';
			$user_id	= $data[$i]['user_id'];
			$timestamp	= $data[$i]['timestamp'];
			self::output('Call API User Id[' . $user_id . '] Create Datetime[' . $timestamp . '][' . $tmpResult . ']');
		}
		$result = in_array(false, $results)? false: true;
		
		$tool->result[] = $result? 'Write All OK': 'Write NG';
		$tool->result[] = 'Writer Company Customer Info End[' . date('Y-m-d H:i:s') . ']';
	}
	
	private static function output($output) {
		if (self::OUTPUT) {
			echo date('Y-m-d H:i:s') . '---' . $output . "<br />\n";
		}
	}

	public function getResult() {
		return nl2br(htmlspecialchars(join("\n", $this->result)));
	}
}