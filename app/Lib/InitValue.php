<?php
/**
 * DB初期値設定用モジュール
 * 
 * 　Schemaクラスのデストラクタに処理を実装する
 * 
 * ・実装例
	public function __destruct() {
		InitValue::insertData($this->_dbInitData);
	}
 
	$_dbInitData = array(
		'tableName1' => array(
			array('fieldName1' => 'value0-1', 'fieldName2' => 'value0-2', ・・・),
			array('fieldName1' => 'value1-1', 'fieldName2' => 'value1-2', ・・・),
			array('fieldName1' => 'value2-1', 'fieldName2' => 'value2-2', ・・・),
			・・・
		),
		'tableName2' => 'csvFileName.csv',
		・・・
	);
 * 
 */
App::uses('Model', 'Model');

/**
 * DB初期値設定用クラス
 */
class InitValue {
	
	/**
	 * DBの初期値を設定する
	 * 
	 * @param array $dbData
	 * @param string $csvDataPath
	 */
	public static function insertData(Array $dbData, $csvDataPath = null) {
		if (is_null($csvDataPath)) {
			$csvDataPath = APP.'Config'.DS.'Schema'.DS.'Csv'.DS;
		}
		foreach ($dbData as $table => $values) {
			$model = new Model();
			$model->useTable = $table;
			if (is_array($values)) {
				// 配列定義のデータを保存する
				$result = self::arrayDataSave($model, $values);
				$msg = $result? "[Success]": "[Error]";
				$msg .= "Array Save {$table} table";
				self::output($msg);
			} else {
				// CSV定義のデータを保存する
				$csvFile = $csvDataPath.$values;
				if (!file_exists($csvFile)) {
					$msg = "[There is no CSV]{$csvFile}";
					self::output($msg);
					unset($model);
					continue;
				}
				$result = self::csvDataSave($model, $csvFile);
				$msg = $result? "[Success]": "[Error]";
				$msg .= "Csv Save {$table} table";
				self::output($msg);
			}
			unset($model);
		}
	}

	/**
	 * 配列定義のデータを保存する
	 *
	 * @param Model $model
	 * @param Array $values
	 * @return boolean
	 */
	private static function arrayDataSave(Model $model, Array $values) {
		$alias	= $model->alias;
		$data = array();
		foreach ($values as $value) {
			$data[] = array($alias => $value);
		}
		$result = $model->saveAll($data);
		return $result;
	}
	
	/**
	 * CSV定義のデータを保存する
	 * @param Model $model
	 * @param String $csvFile
	 * @return boolean
	 */
	private static function csvDataSave(Model $model, $csvFile) {
		$saveCnt	= 50;
		$alias		= $model->alias;
		$results	= array();
		
		// CSVファイルを開く
		$csv = fopen($csvFile, 'r');
		// CSVヘッダの配列を取得する
		$header = fgetcsv($csv);
		
		$saveAllData = array();
		while ($line = fgetcsv($csv)) {
			// １件文のデータフォーマットを取得する
			$data = self::getCsvRowData($line, $header);
			$saveAllData[] = array($alias => $data);
			if (count($saveAllData)%$saveCnt == 0) {
				$results[] = $model->saveAll($saveAllData);
				$saveAllData = array();
			}
		}
		if (!empty($saveAllData)) {
			$results[] = $model->saveAll($saveAllData);
		}
		// CSVファイルを閉じる
		fclose($csv);
		return !in_array(false, $results);
	}
	
	/**
	 * CSV情報の配列を取得する
	 * 
	 * @param Array $line
	 * @param Array $header
	 * @return Array
	 */
	private static function getCsvRowData(Array $line, Array $header) {
		$data = array();
		for ($i=0, $cnt=count($line); $i<$cnt; $i++) {
			$field = $header[$i];
			$value = $line[$i];
			$data[$field] = $value;
		}
		return $data;
	}
	
	/**
	 * メッセージ出力
	 * @param String $msg
	 */
	private static function output($msg) {
		echo "{$msg}\n";
	}
}