<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'GoogleFormAPIConfig.php';

/**
 * Description of GoogleFormAPI
 *
 * @author hanai
 */
class GoogleFormAPI {
	
	const CONFIG_KEY_POST_URL		= 'postUrl';
	const CONFIG_KEY_ANSWER_URL		= 'answerUrl';
	const CONFIG_KEY_DATA_MAPPINGS	= 'dataMappings';
	
	private $postUrl		= '';
	private $answerUrl		= '';
	private $dataMappings	= array();
	private $postData		= array();
	
	/**
	 * ���󥹥ȥ饯��
	 * @param string $configName ��³̾
	 */
	public function __construct($configName = 'default') {
		$this->init($configName);
	}
	
	/**
	 * �����
	 * @param type $configName
	 */
	public function init($configName = 'default') {
		$apiConfig	= new GoogleFormAPIConfig();
		$config		= $apiConfig->{$configName};
		$this->postUrl		= $config[self::CONFIG_KEY_POST_URL];
		$this->answerUrl	= $config[self::CONFIG_KEY_ANSWER_URL];
		$this->dataMappings	= $config[self::CONFIG_KEY_DATA_MAPPINGS];
		$this->postData		= array();
	}
	
	
	public function getPostUrl() {
		return $this->postUrl;
	}

	public function setPostUrl($postUrl) {
		$this->postUrl = $postUrl;
	}

	public function getAnswerUrl() {
		return $this->answerUrl;
	}

	public function setAnswerUrl($answerUrl) {
		$this->answerUrl = $answerUrl;
	}

	public function getDataMappings() {
		return $this->dataMappings;
	}

	public function setDataMappings($dataMappings) {
		$this->dataMappings = $dataMappings;
	}
	
	/**
	 * �������������
	 * @param array $inputData
	 */
	public function setPostData(array $inputData) {
		$keys = array_keys($inputData);
		for ($i = 0, $cnt = count($keys); $i < $cnt; ++$i) {
			$key = $keys[$i];
			$val = $inputData[$key];
			$this->addPostData($key, $val, true);
		}
	}

	/**
	 * ����������ɲ�
	 * @param string $dataKey
	 * @param string $val
	 * @param boolean $overwrite
	 * @throws RuntimeException
	 */
	public function addPostData($dataKey, $val, $overwrite = false) {
		$postKeys = self::getPostKeys($dataKey, $this->dataMappings);
		for ($i = 0, $cnt = count($postKeys); $i < $cnt; ++$i) {
			$postKey = $postKeys[$i];
			if (isset($this->postData[$postKey]) && $overwrite !== false) {
				throw new RuntimeException();
			}
			$this->postData[$postKey] = $val;
		}
	}
	
	/**
	 * ����������ɲ�
	 * @param string $dataKey
	 * @param string $val
	 */
	public function deletePostData($dataKey) {
		$postKeys = self::getPostKeys($dataKey, $this->dataMappings);
		for ($i = 0, $cnt = count($postKeys); $i < $cnt; ++$i) {
			$postKey = $postKeys[$i];
			unset($this->postData[$postKey]);
		}
	}
	
	/**
	 * ��������
	 * @param string $dataKey
	 * @param string $dataMappings
	 * @return array
	 */
	private static function getPostKeys($dataKey, $dataMappings) {
		$postKeys	= array_keys($dataMappings, $dataKey);
		return empty($postKeys)? array($dataKey): $postKeys;
	}

	/**
	 * ����
	 * @return boolean
	 */
	public function send() {
		$postData	= $this->postData;
		$postUrl	= $this->postUrl;
		// ����
		$postResult	= self::sendFormSubmission($postUrl, $postData);
		// �������
		$sendResult	= self::getSendResult(mb_convert_encoding($postResult, 'euc-jp', 'utf8'));
		
		return $sendResult;
	}
	
	/**
	 * ����
	 * @param string $postUrl
	 * @param array $postData
	 * @return string or false
	 */
	private static function sendFormSubmission($postUrl, array $postData) {
		$curl = curl_init($postUrl);
		curl_setopt($curl,CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER , 1 );
		$result = curl_exec($curl);
		
		curl_close($curl);
		return $result;
	}
	
	/**
	 * �������
	 * @param string or false $postResult
	 * @return boolean
	 */
	private static function getSendResult($postResult) {
		if ($postResult === false) {
			return false;
		}
		if (preg_match('%<div class="ss-resp-message">������Ͽ���ޤ�����</div>%', $postResult)) {
			return true;
		} else {
			return false;
		}
	}
}