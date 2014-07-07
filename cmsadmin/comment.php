<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) ){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$faq = new COMMENT;
class COMMENT{
    function COMMENT(){
        global $db,$cms_cfg,$tpl;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "reply":
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->comment_reply();
                $this->ws_tpl_type=1;
                break;
            case "del"://刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->comment_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;            
            case "detail"://問與答分類列表
                $this->current_class="CM";
                $this->ws_tpl_file = "templates/ws-manage-comment-detail-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->comment_detail();
                $this->ws_tpl_type=1;
                break;
            case "list":
            default:    //問與答列表
                $this->current_class="CM";
                $this->ws_tpl_file = "templates/ws-manage-comment-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->comment_list();
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
        $tpl->assignGlobal("CSS_BLOCK_COMMENT","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    function comment_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //問與答列表
        $sql="select * from ".App::getHelper('db')->prefix("comment")." where admin='0' and del = '0'";
        $sql .= " order by createdate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records = $db->numRows($selectrs);
        //取得分頁連結
        $func_str="comment.php?func=list&id=".$_REQUEST["id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array(
            "VALUE_TOTAL_BOX" => $rsnum,
            "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
            "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
        ));
        $i = $main->get_pagination_offset($cms_cfg["op_limit"]);
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "COMMENT_LIST" );
            foreach($row as $k => $v){
                $tpl->assign(strtoupper($k),$v);
            }
            $tpl->assign(array(
                "TAG_SERIAL" => $i,
            ));
        }
    }
//問與答--表單================================================================
    function comment_detail(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $cate=(trim($_REQUEST["id"])!="")?1:0;
        $tpl->assignGlobal( array(
            "MSG_MODE" => $TPLMSG['ADD'],
            "VALUE_F_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_faq","f","id",$_REQUEST["id"],$cate),
            "STR_F_STATUS_CK1" => "checked",
            "STR_F_STATUS_CK0" => "",
            "VALUE_ACTION_MODE" => $action_mode
        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array(
                "VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                "VALUE_JUMP_PAGE" => $_REQUEST['jp'],
            ));
        }
        $comment = App::getHelper('dbtable')->comment->getData($_GET['id'])->getDataRow();
        if($comment){
            foreach($comment as $k => $v){
                $tpl->assignGlobal(strtoupper($k) , $v);
            }
            $attach = App::getHelper('dbtable')->comment_attach->getdataList("comment_id='".$comment['id']."'","file","id");
            if($attach){
                $tpl->newBlock("ATTACH_ZONE");
                foreach($attach as $file){
                    $tpl->newBlock("ATTACH_LIST");
                    $tpl->assign("FILE",$cms_cfg['file_root'].$file['file']);
                }
            }
            //管理者回覆
            $commentReply = App::getHelper('dbtable')->comment->getDataList("parent='".$_GET['id']."' and admin='1'",'*','createdate desc');
            if($commentReply){
                foreach($commentReply as $reply){
                    $tpl->newBlock("REPLY_LIST");
                    $tpl->assign(array(
                        'REPLY_DATE' => $reply['createdate'],
                        'CONTENT'    => $reply['content'],
                    ));
                }
            }
        }else{
            header("location:".$_SERVER['PHP_SELF']);
        }
    }
    //刪除--資料刪除可多筆處理================================================================
    function comment_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if(is_array($_REQUEST["id"])){
            $id=$_REQUEST["id"];
        }else{
            $id=array(0=>$_REQUEST["id"]);
        }
        if(!empty($id)){
            $id_str = implode(",",$id);
            App::getHelper('dbtable')->comment->update(array('del'=>1),"id in(".$id_str.")");
            $db_msg = App::getHelper('dbtable')->comment->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$_SERVER['PHP_SELF']."?func=list&&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }    
    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                $this->comment_del();
                break;
        }
    }    
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=0){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }    
    function comment_reply(){
        global $tpl,$TPLMSG;
        if(App::getHelper('request')->isPost()){
            $originCcomment = App::getHelper('dbtable')->comment->getData($_POST['comment_id'])->getDataRow('url,subject');
            $commentReply = $originCcomment;
            $commentReply['parent'] = $_POST['comment_id'];
            $commentReply['subject'] = 'Re: '.$commentReply['subject'];
            $commentReply['content'] = $_POST['content'];
            $commentReply['createdate'] = date("Y-m-d H:i:s");
            $commentReply['admin'] = 1;
            App::getHelper('dbtable')->comment->writeData($commentReply);
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);            
            $this->goto_target_page($_SERVER['HTTP_REFERER'], 2);
        }
    }
}
//ob_end_flush();
?>
