<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppConvert
 *
 * @author hanai
 */
abstract class AppConvert {
	
    /**
     *
     * @var AppCtlModel 
     */
    protected $ctlModel = null;

    /**
     *
     * @var AppOrmModel 
     */
    protected $ormModel = null;

    /**
     *
     * @var AuthComponent 
     */
    protected $auth = null;

    /**
     *
     * @var string
     */
    protected $ctlAlias = '';

    /**
     *
     * @var string
     */
    protected $ormAlias = '';


    public function __construct(AppCtlModel $ctlModel, AppOrmModel $ormModel) {
        $convert = $this;

        $convert->ctlModel = $ctlModel;
        $convert->ormModel = $ormModel;
        $convert->ctlAlias = $ctlModel->alias;
        $convert->ormAlias = $ormModel->alias;
    }

    public function setAuth(AuthComponent $auth) {
        $this->auth = $auth;
    }

    public function getAuth() {
        return $this->auth;
    }

    public function getCtlModel() {
        return $this->ctlModel;
    }

    public function getOrmModel() {
        return $this->ormModel;
    }
}