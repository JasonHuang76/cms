<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$maindefault = new MAINDEFAULT;
class MAINDEFAULT{
    function MAINDEFAULT(){
        global $main,$cms_cfg,$tpl;
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        //show page
        $this->ws_tpl_file = $cms_cfg['base_templates']."ws-index-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
        //請依首頁不同的版型取消註解，顯示以下的項目
//        $this->products_rand(); //隨機產品
//        $this->show_category_list(); //分類列表
//        $this->new_products_list();   //最新產品
//        $this->hot_products_list();   //熱門產品
//        $this->promotion_products_list(); //促銷產品
//        $main->ad_list(0); //廣告列表
//        $this->news_list(); //最新消息
//        $this->aboutus_list(); //關於我們
//        $main->counter(); //網站計數器
        //$this->gallery_list();
        //if($cms_cfg["ws_module"]['ws_index_banner'])$this->index_banner(); //自訂首頁banner
        $tpl->printToScreen();
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        //取得新樣版，依index-template.xml設定
        $new_template = $this->get_template();
        if($new_template){
            $tpl = new TemplatePower( $new_template );
        }else{
            $tpl = new TemplatePower( $this->ws_tpl_file );
        }
        //$tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        //$tpl->assignInclude( "TOP_MENU", $cms_cfg['base_top_menu_tpl']);// 功能列表
        //$tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方首頁列
        //$tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        //$tpl->assignInclude( "FOOTER", $cms_cfg['base_footer_tpl']); //尾檔功能列表
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["PRODUCTS"]);
        $tpl->assignGlobal( "TAG_INDEX_CURRENT" , "class='current'");
        $main->header_footer("index");
        $main->google_code(); //google analystics code , google sitemap code
//        $main->left_fix_cate_list();
        $this->nivo_slider();
        /*
        //取得目前的 cart type
        $sql="select sc_cart_type from ws_sysconfig where ws_id='".$this->ws_id."'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]=($row["sc_cart_type"]=="")?0:$row["sc_cart_type"];
        */
    }
    //分類列表
    function show_category_list(){
        global $db,$tpl,$cms_cfg,$main;
        //$sql="select a.pc_id,a.pc_parent,a.pc_name from ws_products_cate as a,ws_products_cate as b where a.pc_parent='0' or b.pc_parent=a.pc_id ";
        $sql="select pc_id,pc_parent,pc_name,pc_cate_img,pc_seo_filename from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent='0' and pc_status='1'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum >0){
            while($row = $db->fetch_array($selectrs,1)){
                $tpl->newBlock("PRODUCT_CATE_LIST");
                $img = $row['pc_cate_img']?$cms_cfg['file_root'].$row['pc_cate_img']:$cms_cfg['default_preview_pic'];
                $dimension = $main->resizeto($img,$cms_cfg['idx_prod_cate_img_width'],$cms_cfg['idx_prod_cate_img_height']);
                $tpl->assign(array(
                    "VALUE_PC_NAME" => $row['pc_name'],
                    "VALUE_PC_LINK" => $cms_cfg['base_root'].$row['pc_seo_filename'].".htm",
                    "VALUE_PC_IMG"  => $img,
                    "VALUE_PC_IMG_W"  => $dimension['width'],
                    "VALUE_PC_IMG_H"  => $dimension['height'],
                ));
            }
        }
    }
    //隨機產品
    function products_rand(){
        global $db,$tpl,$cms_cfg;
        $sql="select p.p_id,p.pc_id,p.p_name,p.p_name_alias,p.p_small_img,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id  where p_status='1' order by rand() limit 0,3";
		$selectrs = $db->query($sql);
        while($row = $db->fetch_array($selectrs,1)){
            if($this->ws_seo){
				$dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
                if(trim($row["p_seo_filename"]) !=""){
                    $p_link=$cms_cfg["base_root"].$dirname."/".$row["p_seo_filename"].".html";
                }else{
                    $p_link=$cms_cfg["base_root"].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
                }
            }else{
                $p_link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
            }
            $tpl->newBlock( "PRODUCT_RAND_LIST" );
            $tpl->assign( array("VALUE_P_NAME" =>$row["p_name"],
                                "VALUE_P_NAME_ALIAS" =>$row["p_name_alias"],
                                "VALUE_P_LINK"  => $p_link,
                                "VALUE_P_SMALL_IMG" => (trim($row["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["p_small_img"],
            ));
        }
    }
    //最新產品
    function new_products_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select p.p_id,p.pc_id,p.p_name,p.p_name_alias,p.p_small_img,p.p_seo_filename,pc.pc_seo_filename,p.p_special_price from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_type in ('1','3','5','7') and p.p_status='1' order by rand() limit 0,4";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum > 0){
            $tpl->newBlock( "NEW_PRODUCTS_ZONE" );
        }
       //當後台系統設定為詢價車,則強制把所有的價格隱藏
        if($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]!=1){
            $show_price=0;
        }else{
            $show_price=1;
        }
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            if($this->ws_seo){
				$dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
                if(trim($row["p_seo_filename"]) !=""){
                    $p_link=$cms_cfg["base_root"].$dirname."/".$row["p_seo_filename"].".html";
                }else{
                    $p_link=$cms_cfg["base_root"].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
                }
            }else{
                $p_link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
            }
            $tpl->newBlock( "NEW_PRODUCTS_LIST" );
            $img = (trim($row["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["p_small_img"];
            $dimension = $main->resizeto($img,$cms_cfg['idx_new_prod_img_width'],$cms_cfg['idx_new_prod_img_height']);
            $tpl->assign( array("VALUE_P_NAME" =>$row["p_name"],
                                "VALUE_P_NAME_ALIAS" =>$row["p_name_alias"],
                                "VALUE_P_LINK"  => $p_link,
                                "VALUE_P_SMALL_IMG" => $img,
                                "VALUE_P_SMALL_IMG_W" => $dimension['width'],
                                "VALUE_P_SMALL_IMG_H" => $dimension['height'],
            ));
            //詢價商品或是購物商品
            if($show_price==0){
                $tpl->assign("MSG_SPECIAL_PRICE","");
            }else{
                //會員有登入顯示折扣價
                if(!empty($this->discount) && $this->discount!=100){
                    $discount_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$row["p_special_price"]);
                    $tpl->assign("MSG_SPECIAL_PRICE",$TPLMSG["PRODUCT_DISCOUNT_PRICE"]);
                    $tpl->assign("VALUE_P_SPECIAL_PRICE",$discount_price);
                }else{
                    $tpl->assign("MSG_SPECIAL_PRICE",$TPLMSG["PRODUCT_SPECIAL_PRICE"]);
                    $tpl->assign("VALUE_P_SPECIAL_PRICE",$row["p_special_price"]);
                }
            }
        }
        $tpl->gotoBlock( "NEW_PRODUCTS_ZONE" );
    }
    //熱門產品
    function hot_products_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select p.p_id,p.pc_id,p.p_name,p.p_name_alias,p.p_small_img,p.p_seo_filename,pc.pc_seo_filename,p.p_special_price from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_type in ('2','3','6','7') and p.p_status='1' order by p_hot_sort ".$cms_cfg['sort_pos']." limit 0,4";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum > 0){
            //$tpl->newBlock( "HOT_PRODUCTS_ZONE" );
        }
        //當後台系統設定為詢價車,則強制把所有的價格隱藏
        if($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]!=1){
            $show_price=0;
        }else{
            $show_price=1;
        }
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            if($this->ws_seo){
				$dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
                if(trim($row["p_seo_filename"]) !=""){
                    $p_link=$cms_cfg["base_root"].$dirname."/".$row["p_seo_filename"].".html";
                }else{
                    $p_link=$cms_cfg["base_root"].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
                }
            }else{
                $p_link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
            }
            $dimension = App::getHelper('main')->resizeto($img,$cms_cfg['idx_hot_prod_img_width'],$cms_cfg['idx_hot_prod_img_height']);
            $tpl->newBlock( "HOT_PRODUCTS_LIST" );
            $tpl->assign( array("VALUE_P_NAME" =>$row["p_name"],
                                "VALUE_P_NAME_ALIAS" =>$row["p_name_alias"],
                                "VALUE_P_LINK"  => $p_link,
                                "VALUE_P_SMALL_IMG" => (trim($row["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["p_small_img"],
                                "VALUE_P_SMALL_IMG_W" => $dimension['width'],
                                "VALUE_P_SMALL_IMG_H" => $dimension['height'],                    
            ));
            //詢價商品或是購物商品
            if($show_price==0){
                $tpl->assign("MSG_SPECIAL_PRICE","");
            }else{
                //會員有登入顯示折扣價
                if(!empty($this->discount) && $this->discount!=100){
                    $discount_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$row["p_special_price"]);
                    $tpl->assign("MSG_SPECIAL_PRICE",$TPLMSG["PRODUCT_DISCOUNT_PRICE"]);
                    $tpl->assign("VALUE_P_SPECIAL_PRICE",$discount_price);
                }else{
                    $tpl->assign("MSG_SPECIAL_PRICE",$TPLMSG["PRODUCT_SPECIAL_PRICE"]);
                    $tpl->assign("VALUE_P_SPECIAL_PRICE",$row["p_special_price"]);
                }
            }
        }
        $tpl->gotoBlock( "HOT_PRODUCTS_ZONE" );
    }
    //促銷產品
    function promotion_products_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $this->discount=$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"];
        $sql="select p.p_id,p.pc_id,p.p_name,p.p_name_alias,p.p_small_img,p.p_seo_filename,pc.pc_seo_filename,p.p_special_price from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p_type in ('4','5','6','7') and p_status='1' order by rand() limit 0,4";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum > 0){
            $tpl->newBlock( "PROMOTION_PRODUCTS_ZONE" );
        }
        //當後台系統設定為詢價車,則強制把所有的價格隱藏
        if($_SESSION[$cms_cfg['sess_cookie_name']]["sc_cart_type"]!=1){
            $show_price=0;
        }else{
            $show_price=1;
        }
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            if($this->ws_seo){
				$dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
                if(trim($row["p_seo_filename"]) !=""){
                    $p_link=$cms_cfg["base_root"].$dirname."/".$row["p_seo_filename"].".html";
                }else{
                    $p_link=$cms_cfg["base_root"].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
                }
            }else{
                $p_link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
            }
            $dimension = App::getHelper('main')->resizeto($img,$cms_cfg['idx_pro_prod_img_width'],$cms_cfg['idx_pro_prod_img_height']);
            $tpl->newBlock( "PROMOTION_PRODUCTS_LIST" );
            $tpl->assign( array("VALUE_P_NAME" =>$row["p_name"],
                                "VALUE_P_NAME_ALIAS" =>$row["p_name_alias"],
                                "VALUE_P_LINK"  => $p_link,
                                "VALUE_P_SMALL_IMG" => (trim($row["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["p_small_img"],
                                "VALUE_P_SMALL_IMG_W" => $dimension['width'],
                                "VALUE_P_SMALL_IMG_H" => $dimension['height'],                
            ));
            //詢價商品或是購物商品
            if($show_price==0){
                $tpl->assign("MSG_SPECIAL_PRICE","");
            }else{
                //會員有登入顯示折扣價
                if(!empty($this->discount) && $this->discount!=100){
                    $discount_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$row["p_special_price"]);
                    $tpl->assign("MSG_SPECIAL_PRICE",$TPLMSG["PRODUCT_DISCOUNT_PRICE"]);
                    $tpl->assign("VALUE_P_SPECIAL_PRICE",$discount_price);
                }else{
                    $tpl->assign("MSG_SPECIAL_PRICE",$TPLMSG["PRODUCT_SPECIAL_PRICE"]);
                    $tpl->assign("VALUE_P_SPECIAL_PRICE",$row["p_special_price"]);
                }
            }
        }
        $tpl->gotoBlock( "PROMOTION_PRODUCTS_ZONE" );
    }
    //最新消息列表
    function news_list(){
        global $db,$tpl,$cms_cfg;
        //最新消息列表
        $sql="select n.*,nc.nc_subject from ".$cms_cfg['tb_prefix']."_news as n left join ".$cms_cfg['tb_prefix']."_news_cate as nc on n.nc_id=nc.nc_id where  nc.nc_status='1' and (n.n_status='1' or (n.n_status='2' and n.n_startdate <= '".date("Y-m-d")."' and n.n_enddate >= '".date("Y-m-d")."')) order by n.n_showdate desc,n.n_sort ".$cms_cfg['sort_pos'].",n.n_modifydate desc limit 0,4";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            if($row["n_content_type"]==1) {
                if($this->ws_seo==1 ){
                    if(trim($row["n_seo_filename"])==""){
                        $n_link=$cms_cfg["base_root"]."news/ndetail-".$row["nc_id"]."-".$row["n_id"].".html";
                    }else{
                        $n_link=$cms_cfg["base_root"]."news/".$row["n_seo_filename"].".html";
                    }
                }else{
                    $n_link="news.php?func=n_show&nc_id=".$row["nc_id"]."&n_id=".$row["n_id"];
                }
            }else{
                $n_link = $row["n_url"];
            }
            $tpl->newBlock( "NEWS_LIST" );
            $n_img=(trim($row["n_s_pic"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["n_s_pic"];
            $dimensions = App::getHelper('main')->resizeto($n_img,$cms_cfg['idx_news_img_width'],$cms_cfg['idx_news_img_height']);
            $tpl->assign( array( "VALUE_NC_ID" => $row["nc_id"],
                                 "VALUE_NC_SUBJECT"  => $row["nc_subject"],
                                 "VALUE_N_ID"  => $row["n_id"],
                                 "VALUE_N_SUBJECT"  => $row["n_subject"],
                                 "VALUE_N_SHORT" => App::getHelper('main')->get_short_str($row["n_short"],90),
                                 "VALUE_N_LINK" => $n_link,
                                 "VALUE_N_MODIFYDATE" => substr($row["n_modifydate"],0,10),
                                 "VALUE_N_SHOWDATE" => $row["n_showdate"],
                                 "VALUE_N_TARGET" => ($row["n_pop"])?"_blank":"_parent",
                                 "VALUE_N_S_PIC" => $n_img,
                                 "VALUE_N_S_PIC_W" => $dimensions['width'],
                                 "VALUE_N_S_PIC_H" => $dimensions['height'],
            ));
        }

    }
    //關於我們
    function aboutus_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //前台關於我們列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_aboutus  where  au_status='1' order by au_sort ".$cms_cfg['sort_pos'].",au_modifydate desc";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);

        if(empty($_REQUEST["au_id"])){
           $sel_top_record=true;
        }
        //mb_substr($row["c_profile"], 0, 120, 'utf-8')
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            if(($i==1 && $sel_top_record) || ($_REQUEST["au_id"]==$row["au_id"])){
                $tpl->assignGlobal( "VALUE_AU_CONTENT" , mb_substr($row["au_content"], 0, 520, 'utf-8')."...");
            }
        }
    }
    //自訂首頁banner
    function index_banner(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="select * from ".$cms_cfg['tb_prefix']."_index_banner where ib_img<>'' order by ib_id ";
        $res = $db->query($sql);
        if($db->numRows($res)){
            while($row = $db->fetch_array($res,1)){
                $tpl->newBlock("INDEX_BANNER_ITEM");
                //依據鏈結資料有無進入不同的區塊
                if(trim($row['ib_link'])){
                    $tpl->newBlock("INDEX_BANNER_ITEM_IN_LINK");
                    $tpl->assign(array(
                        "VALUE_BANNER_IMG"  => $row['ib_img'],
                        "VALUE_BANNER_LINK" => $row['ib_link']
                    ));
                }else{
                    $tpl->newBlock("INDEX_BANNER_ITEM_NO_LINK");
                    $tpl->assign(array(
                        "VALUE_BANNER_IMG"  => $row['ib_img']
                    ));
                }
            }
        }
    }
    //nivo slider scripts
    function nivo_slider(){
        global $tpl;
        $tpl->newBlock("NIVO_SLIDER_SCRIPT");
    }
    //依index-template.xml的設定取得期間內的樣版
    function get_template(){
        $xml = new SimpleXMLElement("index-template.xml",0,true);
        if(count($xml->items)){
            foreach($xml->items as $item){
                $ts1 = strtotime($item->start);
                $ts2 = strtotime($item->end);
                $cur = time();
                if($cur>=$ts1 ){
                    if($cur<=$ts2){
                        return $item->template;
                    }
                }
            }
        }
    }    
    //活動剪影
    function gallery_list(){
        global $cms_cfg,$ws_array,$tpl;
        $db = App::getHelper('db');
        $sql = "select * from ".$db->prefix("gallery")." where g_status='1' order by g_modifydate desc limit 1";
        $res = $db->query($sql);
        while($row = $db->fetch_array($res,1)){
            $tpl->newBlock("GALLERY_LIST");
            if($this->ws_seo){
                $link = $cms_cfg["base_root"]."gallery/gdetail-".$row["gc_id"]."-".$row["g_id"].".html";
            }else{
                $link = $cms_cfg['base_root']."gallery.php?func=g_show&gc_id=".$row["gc_id"]."&g_id=".$row["g_id"];
            }
            $gpic = $row['g_b_pic1']?$cms_cfg['file_root'].$row['g_b_pic1']:$cms_cfg['default_preview_pic'];
            $dimension = App::getHelper('main')->resizeto($gpic,286,166);
            $tpl->assign(array(
                "VALUE_G_SUBJECT" => $row['g_subject'],
                "VALUE_G_CONTENT" => App::getHelper('main')->get_short_str($row['g_content'],90),
                "VALUE_G_LINK" => $link,
                "VALUE_G_PIC" => $gpic,
                "VALUE_G_PIC_W" => $dimension['width'],
                "VALUE_G_PIC_H" => $dimension['height'],
            ));
        }
    }
}
?>