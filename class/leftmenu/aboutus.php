<?php
class Leftmenu_Aboutus extends Leftmenu_Abstract {
    public $au_cate;
    public function __construct(TemplatePower $tpl,$au_cate='aboutus') {
        $this->au_cate = $au_cate;
        parent::__construct($tpl);
    }
    protected function _getItems(){
        $db = App::getHelper('db');
        //前台關於我們列表
        $sql="select * from ".$db->prefix("aboutus")."  where au_status='1' and au_cate = '".$this->au_cate."' order by au_sort ".App::configs()->sort_pos.",au_modifydate desc";
        $selectrs = $db->query($sql);
        if(empty($_REQUEST["au_id"]) && empty($_REQUEST["f"])){
           $sel_top_record=true;
        }
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $menuItem = array();
            $menuItem['name'] = $row['au_subject'];
            $menuItem['link'] = ($i==1)? App::configs()->base_root . $this->au_cate.".htm" : $this->get_link($row);
            if(($i==1 && $sel_top_record) || ($_REQUEST["au_id"]==$row["au_id"]) || (App::configs()->ws_module->ws_seo && ($_REQUEST["f"] && $_REQUEST["f"]==$row["au_seo_filename"]))){
                $menuItem['tag_cur'] = "class='current'";
                $this->currentRow = $row;
                if(App::configs()->ws_module->ws_seo){
                    $meta_array=array(
                        "meta_title"=>$row["au_seo_title"],
                        "meta_keyword"=>$row["au_seo_keyword"],
                        "meta_description"=>$row["au_seo_description"],
                        "seo_h1"=>(trim($row["au_seo_h1"])=="")?$row["au_subject"]:$row["au_seo_h1"],
                    );
                    App::getHelper('main')->header_footer($meta_array);
                }else{
                    App::getHelper('main')->header_footer($this->au_cate,$row["au_subject"]);
                }
            }            
            if(App::configs()->ws_module->ws_left_main_au==1){
                if(App::configs()->ws_module->ws_aboutus_au_subcate){
                    if(!empty($row['au_subcate'])){
                        if(!isset($left_menu[$row['au_subcate']])){
                            $left_menu[$row['au_subcate']]['name'] = $row['au_subcate'];
                            $left_menu[$row['au_subcate']]['link'] = '#';
                        }
                        if(isset($menuItem['tag_cur'])){
                            $left_menu[$row['au_subcate']]['tag_cur'] = "class='current'";
                        }
                        $left_menu[$row['au_subcate']]['sub'][]=$menuItem;
                    }else{
                        $left_menu[]=$menuItem;
                    }
                }else{
                    $left_menu[]=$menuItem;
                }
            }
        }
        return $left_menu;
    }
    function get_link($row){
        return App::getHelper('request')->get_link('aboutus',$row);
    }
}
