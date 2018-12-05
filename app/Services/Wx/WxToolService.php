<?php
namespace App\Services\Wx;

use App\Services\BaseService;

/**
 * 微信工具
 *
 * @author lilin
 *         wx(tel):13408099056
 *         qq:182436607
 *        
 */
abstract class WxToolService extends BaseService
{
    public $appid;
    public $encodingAesKey;
    public $token;
    
    public function __construct()
    {
        $this->appid            = config('console.appid');
        $this->encodingAesKey   = config('console.wx_decrypt.encoding_aes_key');
        $this->token            = config('console.wx_decrypt.token');
    }
    
    /**
     * xml转成数据
     *
     * @param string $xml
     *
     * @return array
     */
    public static function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
    
    /**
     * 数组转成XML
     *
     * @param array $array
     * @return string
     */
    public static function arrayToXml(array $config)
    {
        $xml = "<xml>";
        foreach ($config as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    
    /**
     * 用SHA1算法生成安全签名
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @param string $encrypt 密文消息
     */
    public static function getSHA1($timestamp, $nonce, $encrypt_msg)
    {
        $array = array($encrypt_msg, config('console.wx_decrypt.token'), $timestamp, $nonce);
        sort($array, SORT_STRING);
        $str = implode($array);
        return sha1($str);
    }
}

