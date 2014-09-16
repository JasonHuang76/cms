<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$goodlink = new GOODLINK;
class GOODLINK{
    function GOODLINK(){
        global $db,$cms_cfg,$tpl,$main;
        $this->op_limit=$cms_cfg['newsop_limit'];
        $this->jp_limit=$cms_cfg['jp_limit'];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        switch($_REQUEST["func"]){
            case "l_list"://相關網站列表
                $this->ws_tpl_file = $cms_cfg['base_templates']."ws-goodlink-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->goodlink_list();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("lc",$_REQUEST["lc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
                $this->ws_tpl_type=1;
                break;
            default:    //相關網站列表
                $this->ws_tpl_file = $cms_cfg['base_templates']."ws-goodlink-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->goodlink_list();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("lc",$_REQUEST["lc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
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
        $tpl->assignGlobal( "TAG_GOODLINK_CURRENT" , "class='current'");
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]['goodlink']);
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]['goodlink']);
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["goodlink"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "about"); //主要顯示區域的css設定
        $main->header_footer("goodlink",$TPLMSG['GOODLINK']);
        if($_GET){
            $main->layer_link($TPLMSG['GOODLINK'],$cms_cfg['base_root']."goodlink.htm");
        }else{
            $main->layer_link($TPLMSG['GOODLINK']);
        }
    }

//相關網站--列表================================================================
    function goodlink_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $cateRow = $this->left_cat_list();
        //相關網站列表
        if($cateRow){
            $lc_id = $cateRow["lc_id"];
            $and_str = " and lc_id='".$lc_id."' ";
            $main->header_footer("goodlink",$cateRow["lc_subject"]);
            $main->layer_link($cateRow["lc_subject"]);            
        }else{
            $lc_id=0;
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_goodlink where l_status='1' ".$and_str." order by l_sort ".$cms_cfg['sort_pos'].",l_modifydate desc";
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結，重新組合包含limit的sql語法
        if($this->ws_seo==1 ){
            $fulc_str = $cms_cfg['base_root']."goodlink/llist-".$lc_id;
            $sql=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$fulc_str,$total_records,$sql);
        }else{
            $fulc_str = $cms_cfg['base_root']."goodlink.php?func=l_list&lc_id=".$lc_id;
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$fulc_str,$total_records,$sql);
        }
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "GOODLINK_LIST" );
            $img = $row["l_s_pic"]?$cms_cfg['file_root'].$row["l_s_pic"]:$cms_cfg['default_preview_pic'];
            $tpl->assign( array(
                "VALUE_LC_ID"  => $row["lc_id"],
                "VALUE_L_ID"  => $row["l_id"],
                "VALUE_L_SUBJECT" => $row["l_subject"],
                "VALUE_L_LINK" => $main->content_file_str_replace($row["l_url"],'out'),
                "VALUE_L_MODIFYDATE" => substr($row["l_modifydate"],0,10),
                "VALUE_L_TARGET" => ($row["l_pop"])?"_blank":"_parent",
                "VALUE_L_SERIAL" => $i,
                "VALUE_L_S_PIC" => $img,
                "VALUE_L_STRIP_CONTENT" => $main->content_file_str_replace(strip_tags($row["l_content"]),'out'),
            ));
        }
    }
    function left_cat_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //相關網站分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_goodlink_cate where lc_status='1' order by lc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( 
                "VALUE_CATE_NAME" => $row["lc_subject"],
                "VALUE_CATE_LINK"  => $this->get_link($row),
            ));
            if($_REQUEST["lc_id"] && $_REQUEST["lc_id"]==$row["lc_id"]){
                $current_row = $row;
                $tpl->assign( "TAG_CURRENT_CLASS"  , "class='current'");
            }
        }        
        return $current_row;
    }
    function get_link($row){
        global $cms_cfg;
        if($this->ws_seo==1 ){
            $link = $cms_cfg['base_root']."goodlink/llist-".$row["lc_id"].".htm";
        }else{
            $link = $cms_cfg['base_root']."goodlink.php?func=l_list&lc_id=".$row["lc_id"];
        }
        return $link;
    }
}
?>
