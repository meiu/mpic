<?php
/**
 * $Id: page.class.php 359 2012-03-04 16:12:39Z lingter@gmail.com $
 * 
 * Page Class: Generate paging code
 *      
 * @author : Lingter
 * @support : http://www.meiu.cn
 * @copyright : (c)2010 - 2011 meiu.cn lingter@gmail.com
 */
class page_cla{
    
    var $tpl = 'default';
    var $pageset = 4;
    var $pagination = null;
    var $pagerep = '%5B%23page%23%5D';//rawurlencode('[#page#]');
    
    function setStyle($tpl){
        $this->tpl = $tpl;

        if(file_exists(LIBDIR.'page_styles/'.$tpl.'.php')){
            include(LIBDIR.'page_styles/'.$tpl.'.php');
        }else{
            exit(lang('pagination_tpl_not_exists!'));
        }
        $this->pagination = $pagination;
    }

    function fetch($total,$page,$url='',$count = 0,$pageset=0){
        if(!$this->pagination){
            $this->setStyle($this->tpl);
        }
        if(!$pageset){
            $pageset = $this->pageset;
        }
        $ppset = "";
    
        if($total>0){
            if($page<1 || $page=="")
            $page=1;
            if($page>$total)
            $page=$total;
            
            $ppset = str_replace(
                array('{total_count}','{total_page}','{nowpage}'),
                array($count,$total,$page),
                $this->pagination['start']
            );

            if($page>1){
                $ppset .= str_replace('{url}',str_replace($this->pagerep,'1',$url),$this->pagination['first']);
                $ppset .= str_replace('{url}',str_replace($this->pagerep,($page-1),$url),$this->pagination['pre']);
            }

            if(($page-$pageset)>1 && $this->pagination['shownum']){
                $ppset .= str_replace(
                    array('{url}','{num}'),
                    array(str_replace($this->pagerep,'1',$url),'1'),
                    $this->pagination['num']
                 );
                $ppset .= $this->pagination['ellipsis'];

                for($i=$page-$pageset;$i<$page;$i++){
                    $ppset .= str_replace(
                        array('{url}','{num}'),
                        array(str_replace($this->pagerep,$i,$url),$i),
                        $this->pagination['num']
                    );
                }
            }elseif($this->pagination['shownum']){
                for($i=1;$i<$page;$i++){
                     $ppset .= str_replace(
                        array('{url}','{num}'),
                        array(str_replace($this->pagerep,$i,$url),$i),
                        $this->pagination['num']
                    );
                }
            }
                
            if($this->pagination['shownum'])
                $ppset .= str_replace(
                        array('{url}','{num}'),
                        array(str_replace($this->pagerep,$page,$url),$page),
                        $this->pagination['current']
                    );
                
            if(($page+$pageset)<$total && $this->pagination['shownum']){

                for($i=$page+1;$i<=($page+$pageset);$i++){
                    $ppset .= str_replace(
                        array('{url}','{num}'),
                        array(str_replace($this->pagerep,$i,$url),$i),
                        $this->pagination['num']
                    );
                }

                $ppset .= $this->pagination['ellipsis'];
                $ppset .= str_replace(
                        array('{url}','{num}'),
                        array(str_replace($this->pagerep,$total,$url),$total),
                        $this->pagination['num']
                );

            }elseif($this->pagination['shownum']){

                for($i=$page+1;$i<=$total;$i++){
                    $ppset .= str_replace(
                        array('{url}','{num}'),
                        array(str_replace($this->pagerep,$i,$url),$i),
                        $this->pagination['num']
                    );
                }

            }


            if($page<$total){
                $ppset .= str_replace('{url}',str_replace($this->pagerep,($page+1),$url),$this->pagination['next']);
                $ppset .= str_replace('{url}',str_replace($this->pagerep,$total,$url),$this->pagination['last']);
            }
            
            $ppset .= str_replace(
                array('{total_count}','{total_page}','{nowpage}'),
                array($count,$total,$page),
                $this->pagination['end']
            );

            return $ppset;

        }else{
            return $this->pagination['none'];
        }
    }
}