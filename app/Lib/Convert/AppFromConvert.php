<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
App::uses('AppConvert', 'Lib/Convert');

/**
 * Description of AppFromConvert
 *
 * @author hanai
 */
abstract class AppFromConvert extends AppConvert {
    //put your code here

    /**
     *
     * @var array
     */
    protected $ormData = array();

    public function setOrmData(array $ormData) {
        $this->ormData = $ormData;
    }

    abstract public function getCtlData();
}