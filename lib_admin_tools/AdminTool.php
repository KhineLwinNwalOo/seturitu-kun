<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminTool
 *
 * @author hanai
 */
interface AdminTool {
	
	/**
	 * 処理を実行
	 * (@return Void)
	 */
	public function run();
	
	/**
	 * 実行結果
	 * @return String
	 */
	public function getResult();
}