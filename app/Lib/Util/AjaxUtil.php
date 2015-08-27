<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AjaxUtil
 *
 * @author hanai
 */
class AjaxUtil {
	
    const TOKEN_FIELD		= 'self_ajax_token';
    const SESSION_KEY_BASE	= 'tmp.self_ajax_token.';
    const DEFAULT_KEY		= 'default';
    
    const TOKEN_KEY_PREFIX	= 'brmivj9E48gjLgrg';
    const TOKEN_KEY_SUFFIX	= 'o9884jNvbD9tghm,';

    /**
     * トークン取得
     * @param SessionHelper $session
     * @param string $dataAlias
     * @return string
     */
    public static function getToken(SessionHelper $session, $dataAlias = self::DEFAULT_KEY) {
        $sessionKey = self::SESSION_KEY_BASE . $dataAlias;
        return $session->read($sessionKey);
    }

    /**
     * トークン発効
     * @param SessionComponent $session
     * @param string $dataAlias
     */
    public static function setToken(SessionComponent $session, $dataAlias = self::DEFAULT_KEY) {
        $sessionKey = self::SESSION_KEY_BASE . $dataAlias;
        $prefix		= self::TOKEN_KEY_PREFIX;
        $suffix		= self::TOKEN_KEY_SUFFIX;

        $token = md5($prefix . uniqid() . $suffix);
        $session->write($sessionKey, $token);
    }

    /**
     * トークンチェック
     * @param CakeRequest $request
     * @param SessionComponent $session
     * @param string $dataAlias
     * @param string $tokenField
     * @return boolean
     */
    public static function checkToken($requestToken, SessionComponent $session, $dataAlias = self::DEFAULT_KEY) {
        $sessionKey		= self::SESSION_KEY_BASE . $dataAlias;
        $sessionToken	= $session->read($sessionKey);
        return ($requestToken === $sessionToken)? true: false;
    }
	
}