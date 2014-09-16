<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
if($_GET['view'] && file_exists(LANG::$templates."/views/".$_GET['view'])){
    ob_start();
    include "templates/views/".$_GET['view'];
    $raw_content = ob_get_clean();
    $content = $main->content_file_str_replace($raw_content,'out');
    echo $content;
}else{
    header("HTTP/1.0 404 Not Found");    
    include "404.htm";
}
?>