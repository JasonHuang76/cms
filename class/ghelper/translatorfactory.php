<?php
class Ghelper_TranslatorFactory
{
    /// <summary>
    /// 翻译者
    /// </summary>
    /// <param name="type">翻译者类型，目前只有提供Google翻译</param>
    /// <returns>翻译者对象</returns>
    static public function CreateTranslator($type)
    {
        switch ($type)
        {
            case "Microsoft":
                break;
            case "Youdao":
                break;
            default:
                $translator = new Ghelper_GoogleTranslator();
                break;
        }

        return $translator;
    }
}