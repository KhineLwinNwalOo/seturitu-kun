<?php
/**
 * Application level View Helper
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Helper
 * @since         CakePHP(tm) v 0.2.9
 */

App::uses('AppHelper', 'View/Helper');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
class AppCtlHelper extends AppHelper {
	
	public $helpers = array('ExtForm', 'Html', 'Session', 'Paginator');
	
	protected $data = array();
	
	protected $alias = '';
	
	
	public function __construct(View $View, $settings = array()) {
		$this->alias = self::createAlias($this);
		parent::__construct($View, $settings);
	}

	public function setData($data) {
		$this->data = $data;
	}
	
	/**
	 * 
	 * @param self $helper
	 */
	private static function createAlias(self $helper) {
		return preg_replace('/Helper$/', '', get_class($helper));
	}

        /**
	 * ユーザ情報作成リンク
	 * @return string
	 */
	public function getLinkCustomerRegistration() {
		$html		= $this->Html;
		$title		= __('新規ユーザ登録');
		$url		= UrlUtil::getCustomerRegistrationsIndex();
		$options	= array();
		return $html->link($title, $url, $options);
	}
	
	public function getLinkUserSearchs() {
		$html		= $this->Html;
		$title		= __('ユーザ情報検索');
		$url		= UrlUtil::getUserSearchs();
		$options	= array();
		return $html->link($title, $url, $options);
	}
	
	public function getLinkUserListsIndex() {
		$html		= $this->Html;
		$title		= __('ユーザ情報');
		$url		= UrlUtil::getUserListsIndex();
		$options	= array();
		return $html->link($title, $url, $options);
	}
}