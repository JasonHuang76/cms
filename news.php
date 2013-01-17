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
        if(!empty($_REQUEST["type"])){
            $_REQUEST["func"]="n_".$_REQUEST["type"];
            $this->func_str=$cms_cfg['base_root']."news/".$_REQUEST["f"];
        }else{
            $_REQUEST["f"]="news";
            $this->func_str=$cms_cfg['base_root'].$_REQUEST["f"];
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
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板     
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["NEWS"]);
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG["NEWS"]);
        $tpl->assignGlobal("TAG_PAGE_BACK", $TPLMSG['PAGE_BACK']);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["news"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["news"]);//左方menu title
        $tpl->assignGlobal( "TAG_NEWS_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["news"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-news"); //主要顯示區域的css設定
        $main->google_code(); //google analystics code , google sitemap code
    }

//最新消息--列表================================================================
    function news_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $nc_id=0;
        $ext=($this->ws_seo)?".htm":".php";
        $news_link="<a href=\"".$cms_cfg["base_root"]."news".$ext."\">".$TPLMSG["NEWS"]."</a>";
        //最新消息分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_news_cate where nc_status='1' order by nc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
//            if($this->ws_seo==1 ){
//                if(trim($row["nc_seo_filename"])==""){
//                    $cate_link=$cms_cfg["base_root"]."news/nlist-".$row["nc_id"].".htm";
//                }else{
//                    $cate_link=$cms_cfg["base_root"]."news/".$row["nc_seo_filename"].".htm";
//                }
//            }else{
//                $cate_link=$cms_cfg["base_root"]."news.php?func=n_list&nc_id=".$row["nc_id"];
//            }
            $cate_link = $this->get_link($row);
            //顯示左方分類
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME" => $row["nc_subject"],
                                 "VALUE_CATE_LINK"  => $cate_link,
            ));
            if($_REQUEST["nc_id"]==$row["nc_id"] || ($_REQUEST["f"]==$row["nc_seo_filename"])){
                $tpl->assign( "TAG_CURRENT_CLASS"  , "class='current'");
                $news_link .= $this->ps."<a href=\"".$cate_link."\">".$row["nc_subject"]."</a>";
                if($this->ws_seo){
                    $meta_array=array("meta_title"=>$row["nc_seo_title"],
                                      "meta_keyword"=>$row["nc_seo_keyword"],
                                      "meta_description"=>$row["nc_seo_description"],
                                      "seo_short_desc" => $row["nc_seo_short_desc"],
                                      "seo_h1"=>(trim($row["nc_seo_h1"])=="")?$row["nc_subject"]:$row["nc_seo_h1"],
                    );
                    $main->header_footer($meta_array);
                }else{
                    $main->header_footer("news",$TPLMSG["NEWS"]);
                }
                $nc_id=$row["nc_id"];
            }
        }
        //最新消息首頁適用的 header_footer
        if(!isset($_GET['nc_id']) && !isset($_GET['f']) ){
            $main->header_footer("news",$TPLMSG["NEWS"]);
        }        
        $tpl->assignGlobal("TAG_LAYER",$news_link);
        //最新消息列表
        if($nc_id!=0){
            $and_str="and nc_id='".$nc_id."'";
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_news where (n_status='1' or (n_status='2' and n_startdate <= '".date("Y-m-d")."' and n_enddate >= '".date("Y-m-d")."')) ".$and_str." order by n_sort ".$cms_cfg['sort_pos'].",n_modifydate desc";
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        if($this->ws_seo==1 && trim($_REQUEST["f"])!=""){
            //$func_str=$cms_cfg['base_root']."news/".$_REQUEST["f"];
            $func_str=$this->func_str;
            $page=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        }else{
            $func_str=$cms_cfg["base_root"]."news.php?func=n_list&nc_id=".$nc_id;
            $page=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        }
        //重新組合包含limit的sql語法
        $sql=$main->sqlstr_add_limit($this->op_limit,$_REQUEST["nowp"],$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            if($row["n_content_type"]==1) {
//                if($this->ws_seo==1 ){
//                    if(trim($row["n_seo_filename"])==""){
//                        $n_link=$cms_cfg["base_root"]."news/ndetail-".$row["nc_id"]."-".$row["n_id"].".html";
//                    }else{
//                        $n_link=$cms_cfg["base_root"]."news/".$row["n_seo_filename"].".html";
//                    }
//                }else{
//                    $n_link="news.php?func=n_show&nc_id=".$row["nc_id"]."&n_id=".$row["n_id"];
//                }
                $n_link = $this->get_link($row, true);
            }else{
                $n_link = $row["n_url"];
            }
            $tpl->newBlock( "NEWS_LIST" );
            $n_img=(trim($row["n_s_pic"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["n_s_pic"];
            $tpl->assign( array("VALUE_NC_ID"  => $row["nc_id"],
                                "VALUE_N_ID"  => $row["n_id"],
                                "VALUE_N_SUBJECT" => $row["n_subject"],
                                "VALUE_N_SHORT" => $row["n_short"],
                                "VALUE_N_LINK" => $n_link,
                                "VALUE_N_MODIFYDATE" => substr($row["n_modifydate"],0,10),
                                "VALUE_N_SHOWDATE" => $row["n_showdate"],
                                "VALUE_N_TARGET" => ($row["n_pop"])?"_blank":"_parent",
                                "VALUE_N_SERIAL" => $i,
                                "VALUE_N_S_PIC" => $n_img,
            ));
            if($row["n_content_type"]==2){
                $tpl->assign("VALUE_N_LINK" , $row["n_url"]);
            }
        }
        //分頁
        if($i==0){
            $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);
        }else{
            $tpl->newBlock( "PAGE_DATA_SHOW" );
            $tpl->assign( array("VALUE_TOTAL_RECORDS"  => $page["total_records"],
                                "VALUE_TOTAL_PAGES"  => $page["total_pages"],
                                "VALUE_PAGES_STR"  => $page["pages_str"],
                                "VALUE_PAGES_LIMIT"=>$this->op_limit
            ));
            if($page["bj_page"]){
                $tpl->newBlock( "PAGE_BACK_SHOW" );
                $tpl->assign( "VALUE_PAGES_BACK"  , $page["bj_page"]);
                $tpl->gotoBlock("PAGE_DATA_SHOW");
            }
            if($page["nj_page"]){
                $tpl->newBlock( "PAGE_NEXT_SHOW" );
                $tpl->assign( "VALUE_PAGES_NEXT"  , $page["nj_page"]);
                $tpl->gotoBlock("PAGE_DATA_SHOW");
            }
        }
    }
//最新消息--顯示================================================================
    function news_show(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //最新消息分類
        $ext=($this->ws_seo)?".htm":".php";
        $news_link="<a href=\"".$cms_cfg["base_root"]."news".$ext."\">".$TPLMSG["NEWS"]."</a>";
        $sql="select * from ".$cms_cfg['tb_prefix']."_news_cate where nc_status='1' order by nc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
//            if($this->ws_seo==1 ){
//                if(trim($row["nc_seo_filename"])==""){
//                    $cate_link=$cms_cfg["base_root"]."news/nlist-".$row["nc_id"].".htm";
//                }else{
//                    $cate_link=$cms_cfg["base_root"]."news/".$row["nc_seo_filename"].".htm";
//                }
//            }else{
//                $cate_link=$cms_cfg["base_root"]."news.php?func=n_list&nc_id=".$row["nc_id"];
//            }
            $cate_link = $this->get_link($row);
            $cate_link_array[$row["nc_id"]]=$cate_link;
            $cate_subject_array[$row["nc_id"]]=$row["nc_subject"];
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME" => $row["nc_subject"],
                                 "VALUE_CATE_LINK"  => $cate_link,
            ));
        }
        //最新消息內容
        if(!empty($_REQUEST["n_id"])){
            $and_str="n_id='".$_REQUEST["n_id"]."'";
        }
        if(!empty($_REQUEST["f"]) && $_REQUEST["type"]=="show"){
            $and_str="n_seo_filename='".$_REQUEST["f"]."'";
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_news where ".$and_str;
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        if($this->ws_seo){
            $meta_array=array("meta_title"=>$row["n_seo_title"],
                              "meta_keyword"=>$row["n_seo_keyword"],
                              "meta_description"=>$row["n_seo_description"],
                              "seo_h1"=>(trim($row["n_seo_h1"])=="")?$row["n_subject"]:$row["n_seo_h1"],
            );
            $main->header_footer($meta_array);
        }else{
            $main->header_footer("news",$TPLMSG["NEWS"]);
        }
        $news_link .= $this->ps."<a href=\"".$cate_link_array[$row["nc_id"]]."\">".$cate_subject_array[$row["nc_id"]]."</a>".$this->ps.$row["n_subject"];
        $tpl->newBlock( "NEWS_SHOW" );
        $row["n_content"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["n_content"]);
        $tpl->assign( array("VALUE_N_ID"  => $row["n_id"],
                            "VALUE_N_SUBJECT" => $row["n_subject"],
                            "VALUE_N_CONTENT" => $row["n_content"],
                            "VALUE_N_MODIFYDATE" => $row["n_modifydate"],
        ));
        $tpl->assignGlobal("TAG_LAYER",$news_link);
    }
    
    /*由$row取得該筆記錄的url
     */
    function get_link(&$row,$is_news=false){
        global $cms_cfg;
        $link = "";
        if($is_news){
            if($this->ws_seo==1 ){
                if(trim($row["n_seo_filename"])==""){
                    $link = $cms_cfg["base_root"]."news/ndetail-".$row["nc_id"]."-".$row["n_id"].".html";
                }else{
                    $link = $cms_cfg["base_root"]."news/".$row["n_seo_filename"].".html";
                }
            }else{
                $link = "news.php?func=n_show&nc_id=".$row["nc_id"]."&n_id=".$row["n_id"];
            }   
        }else{
            if($this->ws_seo==1 ){
                if(trim($row["nc_seo_filename"])==""){
                    $link = $cms_cfg["base_root"]."news/nlist-".$row["nc_id"].".htm";
                }else{
                    $link = $cms_cfg["base_root"]."news/".$row["nc_seo_filename"].".htm";
                }
            }else{
                $link = $cms_cfg["base_root"]."news.php?func=n_list&nc_id=".$row["nc_id"];
            }
        }
        return $link;                  
    }      
}
?>