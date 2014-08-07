<?php
/// <summary>
/// google translate翻译者类[非API，URL访问Google的方式]
/// ZhangQingFeng    2012-7-27    add
/// </summary>
class Ghelper_GoogleTranslator implements Ghelper_ITranslator
{

    //private string UrlTemplate = "http://translate.google.com.hk/?langpair={0}&text={1}";    //google翻译URL模板:GET方式请求
    private $UrlTemplate = "https://translate.google.com.hk/";     //google翻译URL模板:POST方式请求

    #region 常用语言编码
    private $AutoDetectLanguage = "auto"; //google自动判断来源语系
    
    //文字長度
    public $textLength = 0;
    #endregion

    /// <summary>
    /// 翻译文本
    /// ZhangQingFeng    2012-7-27    add
    /// </summary>
    /// <param name="sourceText">源文本</param>
    /// <param name="sourceLanguageCode">源语言类型代码，如：en、zh-CN、zh-TW、ru等</param>
    /// <param name="targetLanguageCode">目标语言类型代码，如：en、zh-CN、zh-TW、ru等</param>
    /// <returns>翻译结果</returns>
    public function Translate($sourceText, $targetLanguageCode, $sourceLanguageCode="")
    {
        if (empty($sourceText) || preg_match("/^\s*$/i",$sourceText))
        {
            return $sourceText;
        }

        $strReturn = "";

        #region POST方式实现，无长度限制
        $url = $this->UrlTemplate;

        //组织请求的数据
        $postData = sprintf("langpair=%s&text=%s", urlencode($sourceLanguageCode . "|" . $targetLanguageCode), urlencode($sourceText));
        $this->textLength = strlen(urlencode($sourceText));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //SSL
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  //連線逾期
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($postData)));         
        $strResult = curl_exec($ch);
//        WebClient client = new WebClient();
//        client.Headers.Add("Content-Type", "application/x-www-form-urlencoded");
//        client.Headers.Add("ContentLength", postData.Length.ToString());
//        byte[] responseData = client.UploadData(url, "POST", bytes);
//        string strResult = Encoding.UTF8.GetString(responseData);    //响应结果 
        #endregion

        #region GET方式实现，有长度限制
        //string url = string.Format(UrlTemplate, HttpUtility.UrlEncode(sourceLanguageCode + "|" + targetLanguageCode), HttpUtility.UrlEncode(sourceText));
        //WebClient wc = new WebClient();
        //wc.Encoding = Encoding.UTF8;
        //string strResult = wc.DownloadString(url);                //响应结果            
        #endregion

        //使用的正则表达式：    \s+id="?result_box"?\s+[^>]*>(.+)</span>\s*</div>\s*</div>\s*<div id=spell-place-holder\s+
        $strReg = '#<span id=result_box class="(long_text|short_text)">(.+)</span></div></div><div id="gt-edit"#i';
        preg_match($strReg,$strResult,$match );
        if ($match)
        {
            $strReturn = $match[2];
            //<br/>替换为换行，如为HTML翻译选项则可去除下行代码
            //$strReturn = preg_replace( "/<br\s*\/>/i", "\n", $strReturn);
            //$strReturn = preg_replace( "/<[^>]*>/i", "", $strReturn);
            //$strReturn = preg_replace( '#<span(\s+(title|onmouseover|onmouseout|style)="[^\"]+")*>([^<]+)(<br>)*</span>#i', "$3", $strReturn);
            $strReturn = strip_tags($strReturn);
            //$strReturn = html_entity_decode($strReturn);
            $strReturn = htmlspecialchars_decode($strReturn);
            $strReturn = preg_replace("/(\/|#)\s+/i","$1",$strReturn);
            //$strReturn = urldecode($strReturn);

        }
        return $strReturn;
    }
} 