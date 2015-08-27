<?php

App::uses('AppHelper', 'Helper');

class MessageListHelper extends AppHelper {
	
	const ERROR_MSG_CSS_CLASS	= 'sysErrorMessages';
	const WARNING_MSG_CSS_CLASS = 'sysWarningMessages';
	const SUCCESS_MSG_CSS_CLASS = 'sysSuccessMessages';
	
	/**
	 *
	 * @var AppCtlModel 
	 */
	protected $_ctlModel = null;

	/**
	 * CtlModelを取得する
	 * @param AppCtlModel $ctlModelName
	 * @throws RuntimeException
	 */
	public function loadModel($ctlModelName) {
		if ($ctlModelName instanceof AppCtlModel) {
			$this->_ctlModel = $ctlModelName;
		} else if(is_string($ctlModelName)) {
			$this->_ctlModel = ClassRegistry::init($ctlModelName);
		} else {
			throw new RuntimeException;
		}
	}
	
	/**
	 * エラーメッセージ表示のUL要素を取得する
	 * @return string
	 */
	public function getErrorMessages() {
		$helper = $this;
		if (!isset($helper->_ctlModel->validationErrors)) {
			return '';
		}
		$messages	= $helper->_ctlModel->validationErrors;
		$cssClass	= self::ERROR_MSG_CSS_CLASS;
		return self::_createMessages($messages, $cssClass);
	}
	
	/**
	 * 警告メッセージ表示のUL要素を取得する
	 * @return string
	 */
	public function getWarningMessages() {
		$helper = $this;
		if (!isset($helper->_ctlModel->validationWarnings)) {
			return '';
		}
		$messages	= $helper->_ctlModel->validationWarnings;
		$cssClass	= self::WARNING_MSG_CSS_CLASS;
		return self::_createMessages($messages, $cssClass);
	}
	
	/**
	 * 警告メッセージ表示のUL要素を取得する
	 * @return string
	 */
	public function getSuccessMessages() {
		$helper = $this;
		if (!isset($helper->_ctlModel->validationSuccesses)) {
			return '';
		}
		$messages	= $helper->_ctlModel->validationSuccesses;
		$cssClass	= self::SUCCESS_MSG_CSS_CLASS;
		return self::_createMessages($messages, $cssClass);
	}
	
	/**
	 * メッセージ表示のUL要素を作成する
	 * @param array $messages
	 * @param type $cssClass
	 * @return type
	 */
	private static function _createMessages(array $messages, $cssClass = '') {
		$ulTpl	= self::_getUlTpl();
		$liTpl	= self::_getLiTpl();
		
		$liTags = array();
		foreach ($messages as $message) {
			$message = is_array($message)? array_shift($message): $message;
			$liTags[] = sprintf($liTpl, h($message));
		}
		return sprintf($ulTpl, h($cssClass), join("\n", $liTags));
	}

	/**
	 * Tagテンプレート（ul）
	 * @return string
	 */
	private static function _getUlTpl() {
		return '<ul class="%s">%s</ul>';
	}
	
	/**
	 * Tagテンプレート（li）
	 * @return string
	 */
	private static function _getLiTpl() {
		return '<li>%s</li>';
	}
}