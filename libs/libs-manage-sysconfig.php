<?php
ob_start("ob_gzhandler");
include_once("../libs/libs-mysql.php");
include_once("../libs/libs-main.php");
include_once("../TP/class.TemplatePower.inc.php");
include_once("../lang/cht-utf8.php");
define('APP_ROOT_PATH', realpath(dirname(__FILE__).'/../') . DIRECTORY_SEPARATOR);
$db = new DB($cms_cfg['db_host'],$cms_cfg['db_user'],$cms_cfg['db_password'],$cms_cfg['db_name'],$cms_cfg['tb_prefix']);
$mainfunc_class = class_exists("MAINFUNC_NEW")?"MAINFUNC_NEW":"MAINFUNC";
$main = new $mainfunc_class;

//取得網站的設定
$sql="select * from ".$cms_cfg['tb_prefix']."_system_config where sc_id='1'";
$selectrs = $db->query($sql);
$row = $db->fetch_array($selectrs,1);
$rsnum = $db->numRows($selectrs);
if($rsnum >0 ){
	foreach($row as $key => $value){
		$_SESSION[$cms_cfg['sess_cookie_name']][$key]=$value;
	}
}
//處理串接購物車模組
if($cms_cfg['ws_module']['ws_shopping_cart_module']){
    $tmp = explode('+',$cms_cfg['ws_module']['ws_shopping_cart_module']);
    if(is_dir($_SERVER['DOCUMENT_ROOT'] . $cms_cfg['base_root'] .  $tmp[0])){
        $cms_cfg['new_cart_path'] = $cms_cfg['base_root']. $tmp[0] . '/';
    }
    $tmp[1] = str_replace('.', '/', $tmp[1]);
    $new_cart_class = $_SERVER['DOCUMENT_ROOT'] . $cms_cfg['base_root'] . $tmp[1].".php";
    if(file_exists($new_cart_class)){
        //引用歐付寶類別
        require_once( $new_cart_class);
    }
}
include_once(APP_ROOT_PATH."conf/default-items.php");
//autoload class
require APP_ROOT_PATH."class/autoloader.php";
$autoloader = new autoloader();
spl_autoload_register(array($autoloader,"load"));
//儲存設定
App::configs($cms_cfg);
App::defaults($ws_array);
//session handler
$sessHandler = Model_Session::factory($cms_cfg['sess_cookie_name']);
//helper機制
App::addHelper('db', $db);
App::addHelper('main', $main);
App::addHelper('session', $sessHandler);
App::addHelper('request', new Model_Request());
App::addHelper('dbtable', new Model_Dbtable($db));
App::addHelper('ad', new Model_Ad($db,$_SERVER['DOCUMENT_ROOT'].$cms_cfg['base_root'],$cms_cfg['sort_pos']));
?>
