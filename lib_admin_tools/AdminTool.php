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
	 * ������¹�
	 * (@return Void)
	 */
	public function run();
	
	/**
	 * �¹Է��
	 * @return String
	 */
	public function getResult();
}