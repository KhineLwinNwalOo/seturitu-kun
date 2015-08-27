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
class ReviewListHelper extends AppCtlHelper {
    private $dataPaginate = array();
	
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
    * カウンタテキスト
    * @return string
    */
    public function getPaginatorCounter() {
           $paginator	= $this->Paginator;
           $options = array(
                   'format' => __('{:start}件～{:end}件 (全 {:count}件)')
           );
           return $paginator->counter($options);
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
    /**
    * TblReview.id
    * @param int $index
    * @return string
    */
   public function getTextId($index = 0) {
           $data	= $this->dataPaginate[$index];
           $alias	= 'TblReview';
           $field	= 'id';

           return h($data[$alias][$field]);
   }
   /**
    * TblReview.user_name
    * @param int $index
    * @return string
    */
   public function getTextUserName($index = 0) {
           $data	= $this->dataPaginate[$index];
           $alias	= 'TblReview';
           $field	= 'user_name';

           return h($data[$alias][$field]);
   }
   
   /**
    * ソートリンク（id）
    * @return string
    */
   public function getPaginatorSortId() {
           $paginator	= $this->Paginator;
           $key		= 'id';
           $title	= 'ID';
           $options	= array();

           return $paginator->sort($key, $title, $options);
   }
   /**
    * ソートリンク（user_name）
    * @return string
    */
   public function getPaginatorSortUserName() {
           $paginator	= $this->Paginator;
           $key		= 'user_name';
           $title	= 'ユーザ名';
           $options	= array();

           return $paginator->sort($key, $title, $options);
   }
   
   /////////////////////////////////////////////////////////////////////////////
   
   /**
    * TblReview.created
    * @param int $index
    * @return string
    */
   public function getTextCreated($index = 0) {
           $data	= $this->dataPaginate[$index];
           $alias	= 'TblReview';
           $field	= 'created';

           $str = $data[$alias][$field];
           $str = date("Y/m/dH:i", strtotime($str));
                   
           return $str;
   }
   /**
    * TblReview.car_model
    * @param int $index
    * @return string
    */
   public function getTextCarModel($index = 0) {
           $data	= $this->dataPaginate[$index];
           $alias	= 'TblReview';
           $field	= 'car_model';

           return h($data[$alias][$field]);
   }
   /**
    * TblReview.mst_evaluation_id
    * @param int $index
    * @return string
    */
   public function getTextMstEvaluationId($index = 0) {
           $data	= $this->dataPaginate[$index];
           $alias	= 'TblReview';
           $field	= 'mst_evaluation_id';

           return h($data[$alias][$field]);
   }
   
    /**
    * TblReview.address
    * @param int $index
    * @return string
    */
   public function getTextAddress($index = 0) {
           $data	= $this->dataPaginate[$index];
           $alias	= 'TblReview';
           $field	= 'address';

           return h($data[$alias][$field]);
   }
   /**
    * TblReview.uses_count
    * @param int $index
    * @return string
    */
   public function getTextUsesCount($index = 0) {
           $data	= $this->dataPaginate[$index];
           $alias	= 'TblReview';
           $field	= 'uses_count';

           return h($data[$alias][$field]);
   }
    /**
    * TblReview.content
    * @param int $index
    * @return string
    */
   public function getTextShowFlag($index = 0) {
           $data	= $this->dataPaginate[$index];
           $alias	= 'TblReview';
           $field	= 'show_flag';

           return h($data[$alias][$field]);
   }
    /**
    * TblReview.content
    * @param int $index
    * @return string
    */
   public function getTextContent($index = 0) {
           $data	= $this->dataPaginate[$index];
           $alias	= 'TblReview';
           $field	= 'content';

           return h($data[$alias][$field]);
   }
}
