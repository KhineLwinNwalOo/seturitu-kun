<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
App::uses('AppConvert', 'Lib/Convert');

/**
 * Description of AppToConvert
 *
 * @author hanai
 */
abstract class AppToConvert extends AppConvert {
	
    /**
     *
     * @var AppLockModel
     */
    protected $lockModel = null;

    /**
     *
     * @var array
     */
    protected $ctlData = array();

    public function getDataSource() {
            return $this->ctlModel->getDataSource();
    }

    public function getLockModel() {
            return $this->lockModel;
    }

    public function setLockModel(AppLockModel $lockModel) {
            $this->lockModel = $lockModel;
    }

    public function setCtlData(array $ctlData) {
            $this->ctlData = $ctlData;
    }

    abstract public function getSaveData();
	
}