<?php
class Model_Ad {
    protected $tpl;
    protected $db;
    protected $sortType;
    protected $templatePath;
    protected $adItems;
    function __construct($db,$rootPath,$sortType='asc') {
        $this->db = $db;
        $this->sortType = $sortType;
        $this->templatePath = LANG::$real_root . LANG::$templates . DIRECTORY_SEPARATOR. "ad". DIRECTORY_SEPARATOR;
    }
    function getAd($adCate,$template="common",$belongTo=0){
        $this->_initTemplate($template);
        $this->_getAdItems($adCate,$belongTo);
        return $this->_render();
    }
    //取得樣版物件
    protected function _initTemplate($template){
        $tFile = $this->templatePath . $template . ".html";
        if(file_exists($tFile)){
            $tpl = new TemplatePower($tFile);
            $tpl->prepare();
            $this->tpl = $tpl;
        }else{
            throw new Exception("template file for ad doens't exists!");
        }
    }
    //取得ad記錄
    protected function _getAdItems($adCate,$belongTo){
        //篩選條件
        $ex_where_clause = "  and (ad_status='1' or (ad_status='2' and ad_startdate <= '".date("Y-m-d")."' and ad_enddate >= '".date("Y-m-d")."') ) ";
        $ex_where_clause .= "  and (ad_show_type='0' or (ad_show_type='1' and find_in_set('".$belongTo."',ad_show_zone)>0 )) ";  
        //排序方式
        switch(App::getHelper('session')->sc_ad_sort_type){
            case 2 :
                $orderby=" order by ad_sort ".$this->sortType." ";
                break;
            case 1 :
                $orderby=" order by ad_modifydate desc ";
                break;
            case 0 :
            default :
                $orderby=" order by rand() ";
        }        
        $sql="select * from ".$this->db->prefix("ad")." where ad_cate='{$adCate}' ". $ex_where_clause . $orderby;
        $this->adItems = array();
        $res = $this->db->query($sql,true);
        while($row = $this->db->fetch_array($res,1)){
            $this->adItems[] = $row;
        }
    }
    //取得廣告輸出
    protected function _render(){
        global $cms_cfg;
        foreach($this->adItems as $adItem){
            $adBlock = "AD_TYPE_".strtoupper($adItem['ad_file_type']);
            if($adItem['ad_file_type']!='flash' && !empty($adItem['ad_link'])){
                $adBlock .= "_LINK";
            }
            $this->tpl->newBlock($adBlock);
            $this->tpl->assign(array(
                "TAG_FILE_ROOT" => $cms_cfg['file_root'],
                "AD_LINK"    => App::getHelper('main')->content_file_str_replace($adItem['ad_link'],'out'),
                "AD_CONTENT" => $adItem['ad_file'],
                "AD_SUBJECT" => $adItem['ad_subject'],
            ));
        }
        return $this->tpl->getOutputContent();
    }
}
