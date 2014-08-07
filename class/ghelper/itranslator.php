<?php
/// <summary>
/// 语言翻译者接口
/// ZhangQingFeng    2012-7-27    add
/// </summary>
interface Ghelper_ITranslator
{
        /// <summary>
        /// 翻译文本
        /// ZhangQingFeng    2012-7-27    add
        /// </summary>
        /// <param name="sourceText">源文本</param>
        /// <param name="sourceLanguageCode">源语言类型代码，如：en、zh-CN、zh-TW、ru等</param>
        /// <param name="targetLanguageCode">目标语言类型代码，如：en、zh-CN、zh-TW、ru等</param>
        /// <returns>翻译结果</returns>
        function Translate($sourceText, $targetLanguageCode,$sourceLanguageCode="");

}