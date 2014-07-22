<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$news = new NEWS;
class NEWS{
    function NEWS(){
        global $db,$cms_cfg,$tpl,$main;
        $this->op_limit=$cms_cfg['newsop_limit'];
        $this->jp_limit=$cms_cfg['jp_limit'];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        if($_GET['filename']){
            $tmp = App::getHelper('dbtable')->news_cate_class->getDataList("filename='".$_GET['filename']."'");;
            if($tmp[0]){
                $this->news_class = $tmp[0];
                $go = true;
            }
        }
        if(!$go){
            header("location:".$cms_cfg['base_root']);
            die();
        }
        switch($_REQUEST["func"]){
            case "n_list"://最新消息列表
                $this->ws_tpl_file = "templates/ws-news-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->news_list();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("nc",$_REQUEST["nc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
                $this->ws_tpl_type=1;
                break;
            case "n_show"://最新消息顯示
                $this->ws_tpl_file = "templates/ws-news-show-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->news_show();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("n",$_REQUEST["n_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
                $this->ws_tpl_type=1;
                break;
            default:    //最新消息列表
                $this->ws_tpl_file = "templates/ws-news-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->news_list();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("nc",$_REQUEST["nc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
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
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $ws_array['main'][$this->news_class['filename']]);
        $tpl->assignGlobal("TAG_PAGE_BACK", $TPLMSG['PAGE_BACK']);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array['left'][$this->news_class['filename']]);//左方menu title
        //$tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["news"]);//左方menu title
        $tpl->assignGlobal( "TAG_".strtoupper($this->news_class['filename'])."_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"][$this->news_class['filename']]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-news"); //主要顯示區域的css設定
        $main->google_code(); //google analystics code , google sitemap code
    }
    
    function news_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //左側選單
        $cate_row = $this->left_cate_list();
        //最新消息首頁適用的 header_footer
        if(!isset($_GET['nc_id']) && !isset($_GET['f']) ){
            $main->header_footer($this->news_class['filename'],$ws_array['main'][$this->news_class['filename']]);
        }else{
            if($this->ws_seo){
                $meta_array=array(
                    "meta_title"=>$cate_row["nc_seo_title"],
                    "meta_keyword"=>$cate_row["nc_seo_keyword"],
                    "meta_description"=>$cate_row["nc_seo_description"],
                    "seo_short_desc" => $cate_row["nc_seo_short_desc"],
                    "seo_h1"=>(trim($cate_row["nc_seo_h1"])=="")?$cate_row["nc_subject"]:$cate_row["nc_seo_h1"],
                );
                $main->header_footer($meta_array);
            }else{
                $main->header_footer($this->news_class['filename'],$ws_array['main'][$this->news_class['filename']]);
            }            
        }        

        //最新消息列表
        if($cate_row){
            $and_str="and n.nc_id='".$cate_row['nc_id']."'";
            $ext=($this->ws_seo)?".htm":".php";        
            $main->layer_link($ws_array['main'][$this->news_class['filename']],$cms_cfg["base_root"].$this->news_class['filename'].$ext);
            $main->layer_link($cate_row['nc_subject']);
        }else{
            $main->layer_link($ws_array['main'][$this->news_class['filename']]);
        }
        $sql="select n.* from ".$cms_cfg['tb_prefix']."_news as n inner join ".$db->prefix("news_cate")." as nc on n.nc_id=nc.nc_id and nc.nc_indep='1' and nc.nc_indep_id = (select id from ".$db->prefix("news_cate_class")." where filename='".$this->news_class['filename']."') where (n_status='1' or (n_status='2' and n_startdate <= '".date("Y-m-d")."' and n_enddate >= '".date("Y-m-d")."')) ".$and_str." order by n_showdate desc,n_sort ".$cms_cfg['sort_pos'].",n_modifydate desc";
        $selectrs = $db->query($sql);
        $total_records = $db->numRows($selectrs);
        //取得分頁連結，重新組合包含limit的sql語法
        if($this->ws_seo==1 && trim($_REQUEST["f"])!=""){
            //$func_str=$cms_cfg['base_root']."news/".$_REQUEST["f"];
            if($cate_row){
                $func_str=$cms_cfg['base_root'].$this->news_class['filename']."/".$cate_row['nc_seo_filename'];
            }else{
                $func_str=$cms_cfg['base_root'].$this->news_class['filename'];
            }
            $sql=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }else{
            $func_str=$cms_cfg["base_root"].$this->news_class['filename'].".php?func=n_list&nc_id=".$cate_row['nc_id'];
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            if($row["n_content_type"]==1) {
                $n_link = $this->get_link($row, true);
            }else{
                $n_link = $row["n_url"];
            }
            $tpl->newBlock( "NEWS_LIST" );
            $n_img=(trim($row["n_s_pic"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["n_s_pic"];
            $dimensions = $main->resizeto($n_img,$cms_cfg['news_img_width'],$cms_cfg['news_img_height']);
            $tpl->assign( array("VALUE_NC_ID"  => $row["nc_id"],
                                "VALUE_N_ID"  => $row["n_id"],
                                "VALUE_N_SUBJECT" => $row["n_subject"],
                                "VALUE_N_SHORT" => $main->get_short_str($row["n_short"],150),
                                "VALUE_N_LINK" => $n_link,
                                "VALUE_N_MODIFYDATE" => substr($row["n_modifydate"],0,10),
                                "VALUE_N_SHOWDATE" => $row["n_showdate"],
                                "VALUE_N_TARGET" => ($row["n_pop"])?"_blank":"_parent",
                                "VALUE_N_SERIAL" => $i,
                                "VALUE_N_S_PIC" => $n_img,
                                "VALUE_N_S_PIC_W" => $dimensions['width'],
                                "VALUE_N_S_PIC_H" => $dimensions['height'],
            ));
            if($row["n_content_type"]==2){
                $tpl->assign("VALUE_N_LINK" , $row["n_url"]);
            }
        }
    }
//最新消息--顯示================================================================
    function news_show(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //最新消息內容
        if(!empty($_REQUEST["n_id"])){
            $and_str="n_id='".$_REQUEST["n_id"]."'";
        }
        if(!empty($_REQUEST["f"])){
            $and_str="n_seo_filename='".$_REQUEST["f"]."'";
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_news where ".$and_str;
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        //左側選單
        $cate_row = $this->left_cate_list($row['nc_id']);        
        //header footer
        if($this->ws_seo){
            $meta_array=array("meta_title"=>$row["n_seo_title"],
                              "meta_keyword"=>$row["n_seo_keyword"],
                              "meta_description"=>$row["n_seo_description"],
                              "seo_h1"=>(trim($row["n_seo_h1"])=="")?$row["n_subject"]:$row["n_seo_h1"],
            );
            $main->header_footer($meta_array);
        }else{
            $main->header_footer($this->news_class['filename'],$row["n_subject"]);
        }
        //顯示內容
        $tpl->newBlock( "NEWS_SHOW" );
        $tpl->assign( array("VALUE_N_ID"  => $row["n_id"],
                            "VALUE_N_SUBJECT" => $row["n_subject"],
                            "VALUE_N_CONTENT" => $main->content_file_str_replace($row["n_content"],'out'),
                            "VALUE_N_MODIFYDATE" => $row["n_modifydate"],
        ));
        //指定TAG_LAYER
        $main->layer_link($ws_array['main'][$this->news_class['filename']],$cms_cfg['base_root'].$this->news_class['filename'].".htm");
        $main->layer_link($cate_row['nc_subject'],$this->get_link($cate_row))->layer_link($row["n_subject"]);
        //附檔
        if($cms_cfg['ws_module']['ws_news_upfiles']){
            $sql = "select * from ".$cms_cfg['tb_prefix']."_news_files where n_id='".$row['n_id']."'";
            $res = $db->query($sql,true);
            if($db->numRows($res)){
                $tpl->newBlock("ATTACH_BLOCK");
                while($nf = $db->fetch_array($res,1)){
                    $tpl->newBlock("NEWS_FILE_LIST");
                    $tpl->assign("TAG_LINK",$cms_cfg['file_root'].$nf['n_file']);
                    $tpl->assign("TAG_NAME",($nf['nf_desc'])?$nf['nf_desc']:basename($nf['n_file']));
                }
            }      
        }
        //上下筆連結
        $this->news_next_previous($row['n_id'], $row['nc_id'], $row['n_showdate']);
    }
    
    /*由$row取得該筆記錄的url
     */
    function get_link(&$row,$is_news=false){
        global $cms_cfg;
        $link = "";
        if($is_news){
            if($this->ws_seo==1 ){
                if(trim($row["n_seo_filename"])==""){
                    $link = $cms_cfg["base_root"].$this->news_class['filename']."/ndetail-".$row["nc_id"]."-".$row["n_id"].".html";
                }else{
                    $link = $cms_cfg["base_root"].$this->news_class['filename']."/".$row["n_seo_filename"].".html";
                }
            }else{
                $link = $this->news_class['filename']."-".$row["nc_id"]."-".$row["n_id"].".html";
            }   
        }else{
            if($this->ws_seo==1 ){
                if(trim($row["nc_seo_filename"])==""){
                    $link = $cms_cfg["base_root"].$this->news_class['filename']."/nlist-".$row["nc_id"].".htm";
                }else{
                    $link = $cms_cfg["base_root"].$this->news_class['filename']."/".$row["nc_seo_filename"].".htm";
                }
            }else{
                $link = $cms_cfg["base_root"].$this->news_class['filename']."-".$row["nc_id"].".htm";
            }
        }
        return $link;                  
    }      
    //左側選單
    function left_cate_list($cur_nc_id=null){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $sql="select * from ".$cms_cfg['tb_prefix']."_news_cate where nc_status='1' and nc_indep='1' and nc_indep_id = (select id from ".$db->prefix("news_cate_class")." where filename='".$this->news_class['filename']."') order by nc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            $cate_link = $this->get_link($row);
            //顯示左方分類
            $tpl->newBlock( "LEFT_CATE_LIST" );
            if($cur_nc_id == $row["nc_id"] ||  $_REQUEST["nc_id"]==$row["nc_id"] || ($this->ws_seo && $_REQUEST["f"]==$row["nc_seo_filename"])){
                $currentRow=$row;
                $current_class = "class='current'";
            }else{
                $current_class = "";
            }
            $tpl->assign( array( 
                "VALUE_CATE_NAME"    => $row["nc_subject"],
                "VALUE_CATE_LINK"    => $cate_link,
                "TAG_CURRENT_CLASS"  => $current_class, 
            ));
        }
        return $currentRow;
    }
    //上下筆區域
    function news_next_previous($n_id,$nc_id,$n_showdate){
        global $db,$tpl,$cms_cfg,$TPLMSG;    
        $add_con = "and nc_indep_id = (select id from ".$db->prefix("news_cate_class")." where filename='".$this->news_class['filename']."')";
        if(strtolower($cms_cfg['sort_pos'])=="asc"){
            $pre_sql="select n.n_sort,n.nc_id,n.n_id,n.n_subject,n.n_seo_filename,nc.nc_seo_filename from ".$cms_cfg['tb_prefix']."_news as n left join ".$cms_cfg['tb_prefix']."_news_cate as nc on n.nc_id=nc.nc_id
                where nc_status='1' and nc_indep='1' {$add_con} and n.nc_id='".$nc_id."' and n.n_id <> '".$n_id."' and n.n_status=1 and n.n_showdate>='".$n_showdate."' order by n.n_showdate, n.n_sort desc limit 0,1 ";
            $next_sql="select n.n_sort,n.nc_id,n.n_id,n.n_subject,n.n_seo_filename,nc.nc_seo_filename from ".$cms_cfg['tb_prefix']."_news as n left join ".$cms_cfg['tb_prefix']."_news_cate as nc on n.nc_id=nc.nc_id
                where nc_status='1' and nc_indep='1' {$add_con} and n.nc_id='".$nc_id."' and n.n_id <> '".$n_id."' and n.n_status=1 and n.n_showdate<='".$n_showdate."' order by n.n_showdate desc, n.n_sort limit 0,1 ";
        }else{
            $next_sql="select n.n_sort,n.nc_id,n.n_id,n.n_subject,n.n_seo_filename,nc.nc_seo_filename from ".$cms_cfg['tb_prefix']."_news as n left join ".$cms_cfg['tb_prefix']."_news_cate as nc on n.nc_id=nc.nc_id
                where nc_status='1' and nc_indep='1' {$add_con} and n.nc_id='".$nc_id."' and n.n_id <> '".$n_id."' and n.n_status=1 and n.n_showdate<='".$n_showdate."' order by n.n_showdate desc, n.n_sort desc limit 0,1 ";
            $pre_sql="select n.n_sort,n.nc_id,n.n_id,n.n_subject,n.n_seo_filename,nc.nc_seo_filename from ".$cms_cfg['tb_prefix']."_news as n left join ".$cms_cfg['tb_prefix']."_news_cate as nc on n.nc_id=nc.nc_id
                where nc_status='1' and nc_indep='1' {$add_con} and n.nc_id='".$nc_id."' and n.n_id <> '".$n_id."' and n.n_status=1 and n.n_showdate>='".$n_showdate."' order by n.n_showdate, n.n_sort limit 0,1 ";
        }
        $selectrs = $db->query($pre_sql,true);
        $row = $db->fetch_array($selectrs,1);
        $rsnum  = $db->numRows($selectrs);
        if ($rsnum > 0) {
            $n_link = $this->get_link($row,true);
            $tpl->assignGlobal("TAG_PREVIOUS_NEWS","<a href='".$n_link."'>".$TPLMSG['PREV']."</a>");
        }
        $selectrs = $db->query($next_sql,true);
        $row = $db->fetch_array($selectrs,1);
        $rsnum  = $db->numRows($selectrs);
        if ($rsnum > 0) {
            $n_link = $this->get_link($row,true);
            $tpl->assignGlobal("TAG_NEXT_NEWS","<a href='".$n_link."'>".$TPLMSG['NEXT']."</a>");
        }
    }
}
?>