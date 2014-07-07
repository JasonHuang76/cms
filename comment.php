<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
if($_POST && $_SERVER['HTTP_REFERER']){
    if(Model_Comment::isValid()){
        $data = array_merge($_POST,array(
            'createdate' => date("Y-m-d H:i:s"),
            'content'    => strip_tags($_POST['content'],'<b><p><br><a>'),
        ));
        App::getHelper('dbtable')->comment->writeData($data);
        $comment_id = App::getHelper('dbtable')->comment->get_insert_id();
        $comment_nodify_msg = sprintf($TPLMSG['COMMENT_NOTIFICATION'],"http://".$cms_cfg['server_name'].$data['url']);
        App::getHelper('main')->ws_mail_send_simple(App::getHelper('session')->sc_email,App::getHelper('session')->sc_email,$comment_nodify_msg,$TPLMSG['COMMENT_NOTIFY_SUBJECT']);
        $upHandler = new UploadHandler(array(
            'upload_dir'    => dirname(__FILE__).'/upload_files/comment/', //上傳圖片路徑
            'param_name'    => 'attach', //上傳檔案欄位名稱
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',  //接受上傳的檔案
            'image_library' => 0, //使用gd
        ),false);
        $attach = $upHandler->post(false);
        if(!empty($attach)){
            foreach($attach['attach'] as $obj){
                if($obj->error==''){
                    $commentAttach = array(
                        'comment_id' => $comment_id,
                        'file'       => 'upload_files/comment/'.$obj->name,
                    );
                    App::getHelper('dbtable')->comment_attach->writeData($commentAttach);
                }else{
                    if($obj->error_id==4)continue;
                    die($obj->error);
                }
            }
        }
    }else{
        die('not valid security code');
    }
    header("location:".$_SERVER['HTTP_REFERER']);
}
?>
