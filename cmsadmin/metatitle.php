<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$metatile = new METATITLE;
class METATITLE{
    function METATITLE(){
        global $db,$cms_cfg,$tpl;
        $this->current_class="MT";
        switch($_REQUEST["func"]){
            case "addmeta"://新増meta項目
                $this->addmeta();
                break;
            case "mt_setup":  //各項seo設定
                $this->ws_tpl_file = "templates/ws-manage-meta-title-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_TABTITLE");
                $tpl->newBlock("JS_TINYMCE_SEO");
                $this->meta_title_form();
                $this->ws_tpl_type=1;
                break;
            case "mt_replace"://更新資料(update)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->meta_title_replace();
                $this->ws_tpl_type=1;
                break;
            default:    //各項服務說明設定
                $this->ws_tpl_file = "templates/ws-manage-meta-title-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_TABTITLE");
                $tpl->newBlock("JS_TINYMCE_SEO");
                $this->meta_title_form();
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$main;
        $tpl = new TemplatePower( $cms_cfg['manage_all_tpl'] );
        $tpl->assignInclude( "LEFT", $cms_cfg['manage_left_tpl']);
        $tpl->assignInclude( "TOP_MENU", $cms_cfg['manage_top_menu_tpl']);
        $tpl->assignInclude( "MAIN", $ws_tpl_file);
        $tpl->prepare();
        $tpl->assignGlobal("TAG_".$this->current_class."_CURRENT","class=\"current\"");
        $tpl->assignGlobal("CSS_BLOCK_METATITLE","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    ////各項服務說明設定--表單
    function meta_title_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $sql="select * from ".$db->prefix("metatitle");
        $selectrs = $db->query($sql);
        while($row = $db->fetch_array($selectrs,1)){
            if(!isset($cms_cfg['ws_module']['ws_'.$row['mt_name']]) || (isset($cms_cfg['ws_module']['ws_'.$row['mt_name']]) && $cms_cfg['ws_module']['ws_'.$row['mt_name']])){
                $tpl->newBlock("SEO_ZONE_TITLE");
                $tpl->assign("VALUE_MT_NAME",$row['mt_name']);
                $tpl->assign("VALUE_MT_NAME_TITLE",$ws_array["main"][$row['mt_name']]);
                $tpl->newBlock("SEO_ZONE_FIELDS");
                $tpl->assign(array(
                    "VALUE_MT_NAME_TITLE"       => $ws_array["main"][$row['mt_name']],
                    "VALUE_MT_NAME"             => $row['mt_name'],
                    "VALUE_SEO_TITLE"           => $row["mt_seo_title"],
                    "VALUE_SEO_KEYWORD"         => $row["mt_seo_keyword"], 
                    "VALUE_SEO_DESCRIPTION"     => $row["mt_seo_description"], 
                    "VALUE_SEO_SHORT_DESC"      => $main->content_file_str_replace($row["mt_seo_short_desc"],'out'), 
                    "VALUE_SEO_CUSTOM"          => $main->content_file_str_replace($row["mt_seo_custom"],'out'), 
                    "SEO_CUSTOM_STATUS_CK0"     => $row["mt_seo_custom"]?"":"checked", 
                    "SEO_CUSTOM_STATUS_CK1"     => $row["mt_seo_custom"]?"checked":"", 
                    "SEO_CUSTOM_STATUS_DISPLAY" => $row["mt_seo_custom"]?"":"none", 
                    "VALUE_SEO_H1"              => $row["mt_seo_h1"]
                ));
            }
        }
        $this->mainfunc_meta_list();
    }
    //資料更新
    function meta_title_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->magic_gpc($_POST);
        //關於我們seo欄位更新
        foreach($_POST['meta'] as $mt_name => $metafields){
            $sql="
                update ".$db->prefix("metatitle")." set
                    mt_seo_title='".htmlspecialchars($metafields["mt_seo_title"])."',
                    mt_seo_keyword='".htmlspecialchars($metafields["mt_seo_keyword"])."',
                    mt_seo_description='".htmlspecialchars($metafields["mt_seo_description"])."',
                    mt_seo_short_desc='".$db->quote($main->content_file_str_replace($metafields["mt_seo_short_desc"],'in'))."',
                    mt_seo_custom='".$db->quote($main->content_file_str_replace($metafields["mt_seo_custom"],'in'))."',
                    mt_seo_h1='".htmlspecialchars($metafields["mt_seo_h1"])."'
                where mt_name='".$mt_name."'";
            $rs = $db->query($sql);
            $db_msg .= $db->report();
        }
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."metatitle.php?func=mt_setup";
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=0){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
    function mainfunc_meta_list(){
        global $tpl;
        foreach(App::defaults()->main as $k => $v){
            if(!$this->is_exists($k)){
                $new_mt_name_list[$k] = $v;
            }
        }
        if($new_mt_name_list){
            $tpl->newBlock("META_NAME");
            foreach($new_mt_name_list as $mt_name => $mainfunc){
                $tpl->newBlock("META_NAME_LIST");
                $tpl->assign("MT_NAME_KEY",$mt_name);
                $tpl->assign("MT_NAME_VALUE",$mainfunc);
            }
        }
    }
    function addmeta(){
        if(isset(App::defaults()->main[$_GET['mt_name']]) && !$this->is_exists($_GET['mt_name'])){
            $data['mt_name'] = $_GET['mt_name'];
            App::getHelper('dbtable')->metatitle->insert($data);
        }
        header("location:".$_SERVER['HTTP_REFERER']);
    }
    
    function is_exists($mt_name){
        $row = App::getHelper('dbtable')->metatitle->getData($mt_name)->getDataRow('mt_name');
        return empty($row)?false:true;
    }
    
}
//ob_end_flush();
?>
