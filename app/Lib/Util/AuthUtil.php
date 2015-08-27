<?php 


class AuthUtil {

    /**
     * 認証情報の更新
     * @param AuthComponent $auth
     */
    public static function refresh(AuthComponent $auth) {
        $authrModelNmae	= 'TblUser';
        $authModel	= ClassRegistry::init($authrModelNmae);
        $primaryKey	= $authModel->primaryKey;
        $id		= $auth->user($primaryKey);
        $data		= $authModel->read(null, $id);

        $tmp1	= $data[$authrModelNmae];
        $tmp2	= $data;
        unset($tmp2[$authrModelNmae]);

        $authData = am($tmp1, $tmp2);
        $auth->login($authData);
    }

    /**
     * @param AuthComponent $auth
     */
    public static function getTblUserId(AuthComponent $auth) {
        return $auth->user('id');
    }

    /**
     * @param AuthComponent $auth
     */
    public static function getTblUserUserName(AuthComponent $auth) {
        return $auth->user('user_name');
    }

    /**
     * @param AuthComponent $auth
     */
    public static function getTblUserUserMail(AuthComponent $auth) {
        return $auth->user('user_mail');
    }

    /**
     * @param AuthComponent $auth
     */
    public static function getTblUserPassword(AuthComponent $auth) {
        return $auth->user('password');
    }

    /**
     * @param AuthComponent $auth
     */
    public static function getTblUserUserPassword(AuthComponent $auth) {
        return $auth->user('user_password');
    }

    /**
     * @param AuthComponent $auth
     */
    public static function getTblUserLoginFlag(AuthComponent $auth) {
        return $auth->user('login_flag');
    }

    /**
     * @param AuthComponent $auth
     */
    public static function getTblUserCreateIp(AuthComponent $auth) {
        return $auth->user('create_ip');
    }

    /**
     * @param AuthComponent $auth
     */
    public static function getTblUserUpdateIp(AuthComponent $auth) {
        return $auth->user('update_ip');
    }

    /**
     * @param AuthComponent $auth
     */
    public static function getTblUserCreated(AuthComponent $auth) {
        return $auth->user('created');
    }

    /**
     * @param AuthComponent $auth
     */
    public static function getTblUserUpdated(AuthComponent $auth) {
        return $auth->user('updated');
    }

}