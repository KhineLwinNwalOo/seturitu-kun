<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UrlUtil
 *
 * @author hanai
 */
class UrlUtil {
	
    /**
     * ログイン（共通）
     * Memo:
     *  login:
     *  login(success): redirec to User Menu
     * 
     * @accessUses public
     * @viewType create
     * @return array
     */
    public static function getMainsLogin() {
        return array(
            'controller'    => 'Mains',
            'action'        => 'login',
        );
    }

    /**
     * ログアウト（共通）
     * Memo
     *  logout: Logout, After redirect To Login
     * 
     * @accessUses customer,admin
     * @viewType none
     * @return array
     */
    public static function getMainsLogout() {
        return array(
            'controller'    => 'Mains',
            'action'        => 'logout',
        );
    }

    /**
     * メニュ
     * @return array
     */
    public static function getMenu() {
        return array(
            'controller'    => 'Mains',
            'action'        => 'index',
        );
    }

    

    /**
     * ユーザメニュ（ユーザ）
     * 
     * @accessUses customer
     * @viewType static
     * @return array
     */
    public static function getMainsIndex() {
        return array(
            'controller'	=> 'Mains',
            'action'		=> 'index',
        );
    }
    
    /**
     * 
     * Admin Reviews
     * @accessUses review
     * @viewType list
     * @return array
     */
    public static function getReviewListsIndex() {
        return array(
            'controller'	=> 'TblReviews',
            'action'		=> 'index',
        );
    }
    
    /**
     * 
     * Admin Customers view
     * @accessUses review
     * @viewType list
     * @return array
     */
    public static function getCustomerListsIndex() {
        return array(
            'controller'	=> 'TblCustomerlogins',
            'action'		=> 'index',
        );
    }
    
    /**
     * 登録
     * Memo:
     * 
     * @accessUses public
     * @viewType create
     * @return array
     */
    public function getReviewCreateIndex() {
        return array(
            'controller'	=> 'ReviewCreates',
            'action'		=> 'index',
        );
    }   
}