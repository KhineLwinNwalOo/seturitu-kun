<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppToConvert', 'Lib/Convert');

class ReviewCreateToTblReview extends AppToConvert {
	
    public function getSaveData() {
        $convert	= $this;
        $ctlAlias	= $convert->ctlAlias;
        $ormAlias	= $convert->ormAlias;
        $ctlData	= $convert->ctlData;

        $saveData = array(
            $ormAlias => array(
                'user_name'             => $ctlData[$ctlAlias]['user_name'],
                'address'               => $ctlData[$ctlAlias]['address'],
                'car_maker'             => $ctlData[$ctlAlias]['car_maker'],
                'car_model'             => $ctlData[$ctlAlias]['car_model'],
                'mst_evaluation_id'     => $ctlData[$ctlAlias]['mst_evaluation_id'],
                'uses_count'            => $ctlData[$ctlAlias]['uses_count'],
                'content'               => $ctlData[$ctlAlias]['content'],
            ),
        );
        return $saveData;
    }
}