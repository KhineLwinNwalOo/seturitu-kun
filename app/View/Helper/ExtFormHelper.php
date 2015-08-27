<?php

App::uses('FormHelper', 'View/Helper');

class ExtFormHelper extends FormHelper {
	
	protected $_model = null;

	protected  $_extraFormat = array();


	/**
	 * モデルオブジェクトを取得する
	 * 
	 * @return Model
	 */
	public function getModel() {
		return $this->_model;
	}

	/**
	 * フォーム開始タグを作成する
	 * 
	 * @param type $model
	 * @param type $options
	 * @return string
	 */
	public function create($model = null, $options = array()) {
		
		if (!is_null($model) && !($this->_model instanceof $model)) {
			$this->_model = ClassRegistry::init($model);
		}
		return parent::create($model, $options);
	}

	/**
	 * インプットタグを作成する
	 * 
	 * @param type $fieldName
	 * @param type $options
	 * @return string
	 */
	public function input($fieldName, $options = array()) {
		$options['div']			= isset($options['div'])		? $options['div']			: false;
		$options['label']		= isset($options['label'])		? $options['label']			: false;
		$options['error']		= isset($options['error'])		? $options['error']			: false;
		$options['legend']		= isset($options['legend'])		? $options['legend']		: false;
		$options['refer']		= isset($options['refer'])		? $options['refer']			: true;
		
		$arrTmp = explode('.', $fieldName);
		if (isset($arrTmp[1]) && $options['refer'] === true) {
			$model = ClassRegistry::init(array_shift($arrTmp));
			$options = am($model->getFormParams(join('.', $arrTmp)), $options);
		} else if ($this->_model instanceof Model && $options['refer'] === true){
			$options = am($this->_model->getFormParams($fieldName), $options);
		}
		unset($options['refer']);
		if (in_array($options['type'], array('date', 'time', 'datetime'))) {
			$options['monthNames']	= isset($options['monthNames'])	? $options['monthNames']	: false;
			$options['timeFormat']	= isset($options['timeFormat'])	? $options['timeFormat']	: 24;
			$options['dateFormat']	= isset($options['dateFormat'])	? $options['dateFormat']	: 'YMD';
		}
		
		return parent::input($fieldName, $options);
	}
	
	/**
	 * Valueの表示項目拡張版
	 * 
	 * @param type $options
	 * @param type $field
	 * @param type $key
	 * @return type
	 */
	public function extValue($options = array(), $field = null, $key = 'value') {
		$result = parent::value($options, $field, $key);
		if (!is_string($options)) {
			return $result;
		}
		
		$arrTmp = explode('.', $options);
		if (isset($arrTmp[1])) {
			$model = ClassRegistry::init(array_shift($arrTmp));
			$formParams = $model->getFormParams(join('.', $arrTmp));
		} else if ($this->_model instanceof Model){
			$formParams = $this->_model->getFormParams($options);
		}
		// valueがIDの項目
		$checkTypes1 = array('select', 'radio', 'checkbox', );
		if (isset($formParams['type']) && isset($formParams['options']) && in_array($formParams['type'], $checkTypes1)) {
			$result = is_array($result)? $result: array($result);
			$result2 = array();
			foreach ($result as $val) {
				if (isset($formParams['options'][$val])) {
					$result2[] = $formParams['options'][$val];
				}
			}
			return join('、' , $result2);
		}
		// 日時入力の項目
		$checkTypes2 = array('date', 'datetime', 'time', );
		if (isset($formParams['type']) && in_array($formParams['type'], $checkTypes2)) {
			$year		= isset($result['year'])?		$result['year']:		'  ';
			$month		= isset($result['month'])?		$result['month']:		'  ';
			$day		= isset($result['day'])?		$result['day']:			'  ';
			$meridian	= isset($result['meridian'])?	$result['meridian']:	'';
			$hour		= isset($result['hour'])?		$result['hour']:		'  ';
			$min		= isset($result['min'])?		$result['min']:			'  ';
			
			$date = $year . '-' . $month . '-' . $day;
			$time = $meridian . $hour . ':' . $min;
			
			if ($formParams['type'] === 'date') {
				return $date;
			} else if ($formParams['type'] === 'time') {
				return $time;
			} else {
				return $date . ' ' . $time;
			}
		}
		return $result;
	}
	
	/**
	 * 「年月日時分」入力セレクトボックス作成機能を拡張する
	 */
	public function dateTime($fieldName, $dateFormat = 'DMY', $timeFormat = '12', $attributes = array()) {
		if (isset($attributes['extraFormat'])) {
			$this->_extraFormat[$fieldName] = $attributes['extraFormat'];
			unset($attributes['extraFormat']);
		}
		return parent::dateTime($fieldName, $dateFormat, $timeFormat, $attributes);
	}

	/**
	 * 「時」入力セレクトボックス作成機能を拡張する
	 */
	public function hour($fieldName, $format24Hours = false, $attributes = array()) {
		if (isset($this->_extraFormat[$fieldName]) && in_array($this->_extraFormat[$fieldName], array('hour36', 'hour48'))) {
			return $this->select(
				$fieldName . ".hour",
				$this->_generateOptions($this->_extraFormat[$fieldName]),
				$attributes
			);
		}
		return parent::hour($fieldName, $format24Hours, $attributes);
	}
	
	/**
	 * オプション設定の機能を拡張する
	 */
	protected function _generateOptions($name, $options = array()) {
		if ($name=='hour36') {
			$data = array();
			for ($i = 0; $i <= 35; $i++) {
				$data[sprintf('%02d', $i)] = $i;
			}
			$this->_options[$name] = $data;
			return $this->_options[$name];
		}
		if ($name=='hour48') {
			$data = array();
			for ($i = 0; $i <= 47; $i++) {
				$data[sprintf('%02d', $i)] = $i;
			}
			$this->_options[$name] = $data;
			return $this->_options[$name];
		}
		return parent::_generateOptions($name, $options);
	}
}