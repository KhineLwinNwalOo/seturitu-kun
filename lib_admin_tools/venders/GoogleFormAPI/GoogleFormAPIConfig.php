<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GoogleFormAPIConfig
 *
 * @author hanai
 */
class GoogleFormAPIConfig {
	
	public $test = array(
		'postUrl'	=> 'https://docs.google.com/forms/d/1pBUIF0R2z3n_GToqhSrw8cBhp_CbcOh4TsUlOKiExc8/formResponse',
		'answerUrl'	=> 'https://docs.google.com/spreadsheets/d/1p1QMYauLB3cyJi0QhEevxiAH0G8IGXG6r_kzE6tAiq0',
		'dataMappings' => array(
			'entry.1837530974'	=> 'company_found_date',
			'entry.1045368190'	=> 'company_type',
			'entry.401500225'	=> 'filer_family_name',
			'entry.2037893346'	=> 'representative_family_name_kana',
		),
	);
	
	public $default = array(
		'postUrl'	=> 'https://docs.google.com/forms/d/1B38iXMUEcADFU4H0wf_Fiq2TVjsV0EakPzeSMnv4ORI/formResponse',
		'answerUrl'	=> '',
		'dataMappings' => array(
			// 登録日時（タイムスタンプ）
			'entry.1955088939'	=>	'timestamp',
			// 株式・合同
			'entry.1887743666'	=> 'company_type',
			// 申込者名  (姓)
			'entry.263402809'	=> 'filer_family_name',
			// 申込者名  (名)
			'entry.445889048'	=> 'filer_first_name',
			// 申込者住所  (郵便番号)
			'entry.1844637705'	=> 'filer_zip',
			// 申込者住所  (都道府県)
			'entry.1025467526'	=> 'filer_address1',
			// 申込者住所  (市区・町村・番地)
			'entry.1693024973'	=> 'filer_address2',
			// 申込者住所  (建物・階数・部屋)
			'entry.1345558419"'	=> 'filer_address3',
			// 携帯
			'entry.2061817994'	=> 'filer_mobile_phone_number',
			// E-Mail
			'entry.2115239144'	=> 'filer_mail',
			// 会社名・法人名(組合名)
			'entry.233440400'	=> 'company_name',
			// 本店・会社(組合)所在地  (郵便番号)
			'entry.1385968865'	=> 'company_zip',
			// 本店・会社(組合)所在地  (都道府県)
			'entry.1094481564'	=> 'company_address1',
			// 本店・会社(組合)所在地  (市区・町村・番地)
			'entry.2063078137'	=> 'company_address2',
			// 本店・会社(組合)所在地  (建物・階数・部屋)
			'entry.1519392436'	=> 'company_address3',
			// 代表取締役・代表者  (漢字)(姓)
			'entry.1265830762'	=> 'representative_family_name',
			// 代表取締役・代表者  (漢字)(名)
			'entry.663452288'	=> 'representative_first_name',
			// 代表取締役・代表者  (ふりがな)(姓)
			'entry.1775543065'	=> 'representative_family_name_kana',
			// 代表取締役・代表者  (ふりがな)(名)
			'entry.2142524355'	=> 'representative_first_name_kana',
			// 決済月
			'entry.685040440'	=> 'company_settlement_month',
			// 設立日
			'entry.1603366250'	=> 'company_found_date',
			// 備考
			'entry.1273654686'	=> 'company_remark',
		),
	);
	
}