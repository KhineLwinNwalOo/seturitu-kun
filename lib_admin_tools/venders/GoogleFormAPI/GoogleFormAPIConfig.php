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
			// ��Ͽ�����ʥ����ॹ����ס�
			'entry.1955088939'	=>	'timestamp',
			// ��������Ʊ
			'entry.1887743666'	=> 'company_type',
			// ������̾  (��)
			'entry.263402809'	=> 'filer_family_name',
			// ������̾  (̾)
			'entry.445889048'	=> 'filer_first_name',
			// �����Խ���  (͹���ֹ�)
			'entry.1844637705'	=> 'filer_zip',
			// �����Խ���  (��ƻ�ܸ�)
			'entry.1025467526'	=> 'filer_address1',
			// �����Խ���  (�Զ衦Į¼������)
			'entry.1693024973'	=> 'filer_address2',
			// �����Խ���  (��ʪ������������)
			'entry.1345558419"'	=> 'filer_address3',
			// ����
			'entry.2061817994'	=> 'filer_mobile_phone_number',
			// E-Mail
			'entry.2115239144'	=> 'filer_mail',
			// ���̾��ˡ��̾(�ȹ�̾)
			'entry.233440400'	=> 'company_name',
			// ��Ź�����(�ȹ�)�����  (͹���ֹ�)
			'entry.1385968865'	=> 'company_zip',
			// ��Ź�����(�ȹ�)�����  (��ƻ�ܸ�)
			'entry.1094481564'	=> 'company_address1',
			// ��Ź�����(�ȹ�)�����  (�Զ衦Į¼������)
			'entry.2063078137'	=> 'company_address2',
			// ��Ź�����(�ȹ�)�����  (��ʪ������������)
			'entry.1519392436'	=> 'company_address3',
			// ��ɽ��������ɽ��  (����)(��)
			'entry.1265830762'	=> 'representative_family_name',
			// ��ɽ��������ɽ��  (����)(̾)
			'entry.663452288'	=> 'representative_first_name',
			// ��ɽ��������ɽ��  (�դ꤬��)(��)
			'entry.1775543065'	=> 'representative_family_name_kana',
			// ��ɽ��������ɽ��  (�դ꤬��)(̾)
			'entry.2142524355'	=> 'representative_first_name_kana',
			// ��ѷ�
			'entry.685040440'	=> 'company_settlement_month',
			// ��Ω��
			'entry.1603366250'	=> 'company_found_date',
			// ����
			'entry.1273654686'	=> 'company_remark',
		),
	);
	
}