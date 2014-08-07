<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");

$translator = Ghelper_TranslatorFactory::CreateTranslator("Google");

$lst = array();
//$lst[] = "企鵝比人還要高大，<b>還能用可愛形容嗎</b>？近日挖掘出<font color=\"#f00\">已絕種的巨型企鵝化石</font>─卡式古冠企鵝(Palaeeudyptes klekowskii)，推估身長超過2米、重達115公斤，比一般成人還高大";
//$lst[] = "送你一份愛的禮物，我祝妳幸福，不論你在何時，或是在何處，莫忘了我的<div style=\"color:#f00;font-size:16px;\">祝福</div>";
//$lst[] = "this is my book, this is my book, this is my book, this is my book, this is my apple, this is my orange,this is my banana,this is my ";
$lst[] = "產品敘述";
$lst[] = "產品規格";
$lst[] = "產品說明";
//設定網頁編碼
header('content-type:text/html;charset=Shift-JIS');
$strSource=implode("|",$lst);
//參數說明
// @1: 欲翻譯文字
// @2: 目的語言欲翻譯文字
// @2: 來源語言，最好有設
$str = $translator->Translate($strSource, "en",'zh-tw');
echo "legth:".$translator->textLength;
echo "<hr/>";
echo $str;
echo "<hr/>";
echo "done";
?>