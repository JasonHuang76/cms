<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$stores = new STORES;
class STORES{
    function STORES(){
        global $db,$cms_cfg,$tpl,$main;
        $this->op_limit=$cms_cfg['storesop_limit'];
        $this->jp_limit=$cms_cfg['jp_limit'];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        if(!empty($_REQUEST["type"])){
            $_REQUEST["func"]="sd_".$_REQUEST["type"];
            $this->func_str=$cms_cfg['base_root']."stores/".$_REQUEST["f"];
        }else{
            $_REQUEST["f"]="stores";
            $this->func_str=$cms_cfg['base_root'].$_REQUEST["f"];
        }
        switch($_REQUEST["func"]){
            case "sd_get_map":
                $this->get_map();
                break;
            case "sd_list"://門市管理列表
                $this->ws_tpl_file = $cms_cfg['base_templates']."ws-stores-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->stores_list();
                $tpl->newBlock("JQUERY_UI_SCRIPT");
                //page view record --ph_type,ph_type_id,m_id
                $this->ws_tpl_type=1;
                break;
            default:    //門市管理列表
                $this->ws_tpl_file = $cms_cfg['base_templates']."ws-stores-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->stores_list();
                $tpl->newBlock("JQUERY_UI_SCRIPT");
                //page view record --ph_type,ph_type_id,m_id
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $main->layer_link();
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$ws_array,$TPLMSG,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", $cms_cfg['base_templates']."ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", $cms_cfg['base_templates']."ws-fn-ad-v-tpl.html"); //直式廣告模板     
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["STORES"]);
        $tpl->assignGlobal("TAG_PAGE_BACK", $TPLMSG['PAGE_BACK']);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["stores"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["stores"]);//左方menu title
        $tpl->assignGlobal( "TAG_STORES_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["stores"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_IMG" , $ws_array["main_img"]["stores"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-stores"); //主要顯示區域的css設定
        $main->google_code(); //google analystics code , google sitemap code
    }

//門市管理--列表================================================================
    function stores_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $ext=($this->ws_seo)?".htm":".php";
        //門市管理分類
        $this->left_cate_list();      
        //門市管理列表
        ////網路門市
        if($_GET['f']=='webstores' || $_GET['sd_type']=='2'){
            $and_str = " and sd_type='2' ";
            $blocklist="WEB_STORES_LIST";
        }else{
            ////實體門市
            $and_str = " and sd_type='1'";
            if($this->currentRow){
                $and_str .= " and sdc_id in(".$this->fetch_sdc_id($this->currentRow['sdc_id']).")";
            }
            $blocklist="REAL_STORES_LIST";
        }
        if($this->currentRow){
            $this->layer_link($this->currentRow);
        }else{
            $main->layer_link($TPLMSG['STORES']);
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_stores where sd_status='1' ".$and_str." order by sd_sort ".$cms_cfg['sort_pos'];
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結，且重新組合包含limit的sql語法
        if($this->ws_seo==1 && trim($_REQUEST["f"])!=""){
            $func_str=$cms_cfg['base_root']."stores/".$_REQUEST["f"];
            $sql=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }else{
            $func_str=$cms_cfg["base_root"]."stores.php?func=sd_list&sd_type=".$_GET['sd_type']."&sdc_id=".$sdc_id;
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( $blocklist );
            $sd_img=(trim($row["sd_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["sd_img"];
            $tpl->assign( array("VALUE_SDC_ID"  => $row["sdc_id"],
                                "VALUE_SD_ID"  => $row["sd_id"],
                                "VALUE_SD_NAME" => $row["sd_name"],
                                "VALUE_SD_DESC" => $main->content_file_str_replace($row["sd_desc"],'out'),
                                "VALUE_SD_SERIAL" => $i,
                                "VALUE_SD_IMG" => $sd_img,
                                "VALUE_SD_URL" => $row['sd_url'],
            ));
            if($row['sd_gmurl']){
                $tpl->newBlock("GMURL_BLOCK");
                $tpl->assign("VALUE_SD_ID",$row["sd_id"]);
            }
        }
    }
//門市管理--顯示================================================================
    function stores_show(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //門市管理分類
        $ext=($this->ws_seo)?".htm":".php";
        $stores_link="<a href=\"".$cms_cfg["base_root"]."stores".$ext."\">".$TPLMSG["STORES"]."</a>";
        $sql="select * from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_status='1' order by sdc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            $cate_link = $this->get_link($row);
            $cate_link_array[$row["sdc_id"]]=$cate_link;
            $cate_subject_array[$row["sdc_id"]]=$row["sdc_subject"];
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME" => $row["sdc_subject"],
                                 "VALUE_CATE_LINK"  => $cate_link,
            ));
        }
        //門市管理內容
        if(!empty($_REQUEST["sd_id"])){
            $and_str="sd_id='".$_REQUEST["sd_id"]."'";
        }
        if(!empty($_REQUEST["f"]) && $_REQUEST["type"]=="show"){
            $and_str="sd_seo_filename='".$_REQUEST["f"]."'";
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_stores where ".$and_str;
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        if($this->ws_seo){
            $meta_array=array("meta_title"=>$row["sd_seo_title"],
                              "meta_keyword"=>$row["sd_seo_keyword"],
                              "meta_description"=>$row["sd_seo_description"],
                              "seo_h1"=>(trim($row["sd_seo_h1"])=="")?$row["sd_subject"]:$row["sd_seo_h1"],
            );
            $main->header_footer($meta_array);
        }else{
            $main->header_footer("stores",$TPLMSG["STORES"]);
        }
        $stores_link .= $this->ps."<a href=\"".$cate_link_array[$row["sdc_id"]]."\">".$cate_subject_array[$row["sdc_id"]]."</a>".$this->ps.$row["sd_subject"];
        $tpl->newBlock( "STORES_SHOW" );
        $row["sd_content"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["sd_content"]);
        $tpl->assign( array("VALUE_SD_ID"  => $row["sd_id"],
                            "VALUE_SD_SUBJECT" => $row["sd_subject"],
                            "VALUE_SD_CONTENT" => $row["sd_content"],
                            "VALUE_SD_MODIFYDATE" => $row["sd_modifydate"],
        ));
        $tpl->assignGlobal("TAG_LAYER",$stores_link);
    }
    
    /*由$row取得該筆記錄的url
     */
    function get_link($row){
        global $cms_cfg;
        if($this->ws_seo){
            $sub_cate_link = $cms_cfg['base_root']."stores/".$row['sdc_seo_filename'].".htm";  

        }else{
            $sub_cate_link = $cms_cfg['base_root']."stores.php?func=sd_list&sdc_id=".$row['sdc_id'];
        }
        return $sub_cate_link;
    }     
    
    //左側選單
    function left_cate_list(){
        global $db,$cms_cfg,$tpl,$main,$TPLMSG;
        $sd_type_arr = array(1=>$TPLMSG['STORE_CATE_1'],2=>$TPLMSG['STORE_CATE_2']);
        $menuItems = array();
        foreach($sd_type_arr  as $sd_type=>$info){
            if($sd_type==1){
                $cate_link = "#";
            }elseif($sd_type==2){
                if($this->ws_seo){
                    $cate_link = $cms_cfg['base_root']."stores/webstores.htm";
                }else{
                    $cate_link = $cms_cfg['base_root']."stores.php?func=sd_list&sd_type=2";
                }
            }
            $item = array(
                'name' => $info,
                'link' => $cate_link,
                'tag_cur' => ($sd_type==2 && ($_GET['f']=='webstores' || $_GET['sd_type']==2))?"class='current'":"",
            );
            if($sd_type==1){
                $storeCate = array();
                $this->_get_cate_link($storeCate);
                if($storeCate){
                    $item['sub'] = $storeCate;
                    if($this->currentRow) $meta_row = $this->currentRow;
                }
            }
            $menuItems[] = $item;
        }
        if($meta_row){
            if($this->ws_seo){
                $meta_array=array("meta_title"=>$meta_row["sdc_seo_title"],
                                  "meta_keyword"=>$meta_row["sdc_seo_keyword"],
                                  "meta_description"=>$meta_row["sdc_seo_description"],
                                  "seo_short_desc" => $meta_row["sdc_seo_short_desc"],
                                  "seo_h1"=>(trim($meta_row["sdc_seo_h1"])=="")?$meta_row["sdc_subject"]:$meta_row["sdc_seo_h1"],
                );
                $main->header_footer($meta_array);
            }else{
                $main->header_footer("stores",$TPLMSG["STORES"]);
            }                              
        }else{
            $main->header_footer("stores",$TPLMSG["STORES"]);
        }
        $main->new_left_menu($menuItems);
    }
    //取得google map
    function get_map(){
        global $cms_cfg,$db;
        if($_POST['sd_id']){
            $sql = "select sd_gmurl from ".$cms_cfg['tb_prefix']."_stores where sd_id='".$_POST['sd_id']."'";
            $res = $db->query($sql);
            list($gmurl) = $db->fetch_array($res);
            if($gmurl){
                echo $gmurl;
            }
        }        
    }
    function _get_cate_link(&$catelink,$parent=0){
        global $db,$cms_cfg;
        $sql = "select * from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_parent='{$parent}' order by sdc_sort ".$cms_cfg['sort_pos'];
        $res = $db->query($sql);
        while($row = $db->fetch_array($res,1)){
            $tmp = array(
                'link' => $this->get_link($row),
                'name' => $row['sdc_subject'],
            );
            if($_GET['sdc_id']==$row['sdc_id'] || ($_GET[f] && $_GET[f]==$row['sdc_seo_filename'])){
                $tmp['tag_cur'] = "class='current'";
                $this->currentRow = $row;
            }
            $sub = array();
            $this->_get_cate_link($sub, $row['sdc_id']);
            if($sub)$tmp['sub'] = $sub;
            $catelink[] = $tmp;
        }
    }    
    function fetch_sdc_id($sdc_id,&$box=array(),$deep=1){
        global $db,$cms_cfg;
        $box[] = $sdc_id;
        $sql = "select sdc_id from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_parent='{$sdc_id}' order by sdc_sort ".$cms_cfg['sort_pos'];
        $res = $db->query($sql,1);
        while(list($sub_sdc_id) = $db->fetch_array($res,0)){
            $this->fetch_sdc_id($sub_sdc_id, $box,$deep+1);
        }
        if($deep==1){
            return implode(',',$box);
        }
    }
    //產品自訂layer_link
    function layer_link($row){
        global $main,$cms_cfg,$db,$TPLMSG;
        $item_name = "sdc_subject";
        $parent_name = "sdc_parent";
        if(!isset($row[$parent_name])){
             trigger_error($parent_name." field missing!"); 
        }
        $parent_id = $row[$parent_name];
        $layer[]['name'] = $row[$item_name];
        //取得上層分類
        while($parent_id>0){
            $sql = "select * from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_id='".$parent_id."'";
            $row = $db->query_firstrow($sql);
            $parent_id = $row[$parent_name];
            $tmp = array(
                'name' => $row[$item_name],
                'link' => $this->get_link($row),
            );
            $layer[] = $tmp;
        }
        //寫入階層
        $main->layer_link($TPLMSG['STORES'],$cms_cfg['base_root']."stores.htm");
        if(($_GET['f']=='webstores' || $_GET['sd_type']==2)){
            $main->layer_link($TPLMSG['STORE_CATE_2']);
        }else{
            $main->layer_link($TPLMSG['STORE_CATE_1']);
        }
        while($layer){
            $item = array_pop($layer);
            if($item['link']){
                $main->layer_link($item['name'],$item['link']);
            }else{
                $main->layer_link($item['name']);  
            }
        }
    }        
}
?>