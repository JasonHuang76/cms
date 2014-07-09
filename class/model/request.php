<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of request
 *
 * @author Administrator
 */
class Model_Request extends Model_Modules{
    //put your code here
    protected $_get;
    protected $_post;
    
    function __construct() {
        $this->_get = &$_GET;
        $this->_post = &$_POST;
    }
    
    function isPost(){
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    function isGet(){
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }
    function isAjax(){
        return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }
}
