<?php
App::uses('AppController', 'Controller');
App::uses('UrlUtil', 'Lib/Util');

/**
 * ReviewCreates Controller
 *
 * @property ReviewCreate $ReviewCreate
 * @property PaginatorComponent $Paginator
 */
class SearchBusinessPurposesController extends AppController {
    
    public function index() {
        // 処理なし	
    }
    
    public function big_category() {
        $ctl		= $this;
        $model		= $ctl->SearchBusinessPurpose;
	
	$bigData = $model->getBigCategory();
	$ctl->set(compact('bigData'));
    }
    
    public function mid_category($Bigid){
		
	$ctl	    = $this;
	$model	    = $ctl->SearchBusinessPurpose;
	$midData    = $model->getMidCategory($Bigid);
	
	$ctl->set(compact('midData'));
	$ctl->layout = false;
    }
    
    public function min_category($Midid){
		
	$ctl	    = $this;
	$model	    = $ctl->SearchBusinessPurpose;
	$minData   = $model->getMinCategory($Midid);
	
	$ctl->set(compact('minData'));
	$ctl->layout = false;
    }
    
    public function search_category(){
	$ctl	    = $this;
        $model	    = $ctl->SearchBusinessPurpose;
	
	$model->setPaginateParams($ctl);
        $dataPaginate = $model->getDataPaginate($ctl);
		
	$ctl->set(compact('dataPaginate'));
	$ctl->layout = false;
    }
}