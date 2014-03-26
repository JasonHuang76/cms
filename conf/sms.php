<?php
//帳號
$cms_cfg['emome-sms']['account'] = ""; 
//密碼
$cms_cfg['emome-sms']['password'] = "";
// **發送者號碼的種類**
// 0 (代表from address是一個手機門號)，
// 1 (代表from_addr是一個emome代碼)，
// 2 (代表from_addr 是為帳號為開頭的字串)，長度最多為9。
$cms_cfg['emome-sms']['from_addr_type'] = 0;
//這通SMS訊息的發送者的號碼。由於此欄位關係到收費對象
$cms_cfg['emome-sms']['from_addr'] = '';