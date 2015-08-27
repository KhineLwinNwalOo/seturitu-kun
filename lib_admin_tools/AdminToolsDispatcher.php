<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once dirname(__FILE__) . '/AdminTool.php';

/**
 * Description of AdminToolsDispatcher
 *
 * @author hanai
 */

class AdminToolsDispatcher {
	
	
	public static function run($toolName) {
		try {
			$toolInstance	= self::getToolInstance($toolName);
			$result			= self::runToolInstance($toolInstance);
		} catch (ErrorException $e) {
			$result = $e->getMessage() . '(' . $e->getLine() . ')';
		}
		return $result;
	}
	
	private static function getToolInstance($toolName) {
		$classFilePath = dirname(__FILE__) . '/' . $toolName . '/' . $toolName . '.php';
		require_once $classFilePath;
		$instance = new $toolName();
		if ($instance instanceof AdminTool) {
			return $instance;
		}
		throw new ErrorException('Instance Of Not AdminTool');;
	}
	
	private static function runToolInstance(AdminTool $toolInstance) {
		$toolInstance->run();
		$result = $toolInstance->getResult();
		return $result;
	}
	
}