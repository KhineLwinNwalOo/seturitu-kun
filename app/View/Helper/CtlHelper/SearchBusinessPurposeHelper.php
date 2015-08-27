<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppCtlHelper', 'View/Helper');
App::uses('UrlUtil', 'Lib/Util');

/**
 * Description of ReviewListHelper
 *
 * @author ASUS
 */
class SearchBusinessPurposeHelper extends AppCtlHelper {
    private $dataPaginate = array();
    /**
     * 
     * Form start
     * @param type $options
     * @return type
     */
    public function getFormStart($options = array()) {
	$form	= $this->ExtForm;
	$alias	= $this->alias;
	return $form->create($alias, $options);
    }
	
    /**
     * 
     * Form end
     * @param type $options
     * @return type
     */
    public function getFormEnd() {
	$form	= $this->ExtForm;
	return $form->end;
    }
    
    /**
    * 
    * フリーワード
    * @param type $options
    * @return type
    */
    public function getInputFreeWord() {
	$form	= $this->ExtForm;
	$field	= 'free_word';

	$options	= array(
            'class'	=> 'txtBox_wh230',
        );

	return $form->error($field) . $form->input($field, $options);
    }
    
    /**
    * 
    * 大カテゴリ
    * @param type $options
    * @return type
    */
    public function getInputBigCategory() {
	$form		= $this->ExtForm;
	$field		= 'big_id';

	$options = array();

	return $form->error($field) . $form->input($field, $options);
    }
   
    /**
    * 
    * 中カテゴリ
    * @param type $options
    * @return type
    */
    public function getInputMidCategory() {
	$form		= $this->ExtForm;
	$field		= 'mid_id';

	$options = array();

	return $form->error($field) . $form->input($field, $options);
    }
    
    /**
    * 
    * 小カテゴリ
    * @param type $options
    * @return type
    */
    public function getInputMinCategory() {
	$form		= $this->ExtForm;
	$field		= 'min_id';

	$options = array();

	return $form->error($field) . $form->input($field, $options);
    }
    
    public function getInputRelevantData() {
	$form		= $this->ExtForm;
	$options	= array();
	$field		= 'check_item';
	return $form->error($field) . $form->input($field, $options);
    }
    
    /**
     * ページェントデータ
     * @param array $dataPaginate
     */
    public function setDataPaginate(array $dataPaginate) {
            $this->dataPaginate = $dataPaginate;
    }

    /**
     * データ数
     * @return int
     */
    public function getDataPaginateCount() {
            return count($this->dataPaginate);
    }
    
    /**
    * ページ遷移リンク
    * @return string
    */
    public function getPaginatorLinks() {
           $paginator	= $this->Paginator;
           
           $getPageLinkNumbers = self::getPaginatorLinkNumbers($paginator);
           if($getPageLinkNumbers == '') $getPageLinkNumbers = '<span class=current>1</span>';
           
           $result = array(
                   self::getPaginatorLinkPrev		($paginator), " ",
                   $getPageLinkNumbers , " ",
                   self::getPaginatorLinkNext		($paginator),
           );
           return join("", $result);
    }
    
    /**
    * ページNoリンク
    * @param PaginatorHelper $paginator
    * @return string
    */
    private static function getPaginatorLinkNumbers(PaginatorHelper $paginator) {
           $options = array(
              'separator' => ' ' 
           );
           return $paginator->numbers($options);
    }
    
    public function getPaginatorCounter() {
           $paginator	= $this->Paginator;
           $options = array(
                   'format' => __('{:start}件～{:end}件 (全 {:count}件)')
           );
           return $paginator->counter($options);
    }
    
    /**
    * 戻るリンク
    * @param PaginatorHelper $paginator
    * @return string
    */
    private static function getPaginatorLinkPrev(PaginatorHelper $paginator) {
         
        $pretext            = 'ページ：' ;
        $title              = __('<'); //戻る
        $options            = array(
              'separator' => ' ' 
           );
        $disabledTitle      = null;
        $disabledOptions    = array(
                'class' => 'prev disabled'
        );
        return $pretext . $paginator->prev($title , $options, $disabledTitle, $disabledOptions);
    }
    
    /**
    * 次へリンク
    * @param PaginatorHelper $paginator
    * @return string
    */
    private static function getPaginatorLinkNext(PaginatorHelper $paginator) {
           $title               = __('>'); //次へ
           $options             = array(
              'separator' => ' ' 
           );
           $disabledTitle       = null;
           $disabledOptions	= array(
                'class' => 'next disabled'
           );
           return $paginator->next($title , $options, $disabledTitle, $disabledOptions) ;
    }
    
    public function getTextContent($index = 0) {
           $data	= $this->dataPaginate[$index];
           $alias	= 'TblContent';
           $field	= 'content';

           return h($data[$alias][$field]);
   }
   
   public function getTextParentNavi($index = 0) {
            $data = $this->dataPaginate[$index];
            $result = array(
                Hash::get($data, 'TblBigCategory.name'),
                Hash::get($data, 'TblMidCategory.name'),
                Hash::get($data, 'TblMinCategory.name'),
           );
           
           return h(join('>', $result));
   }
}
