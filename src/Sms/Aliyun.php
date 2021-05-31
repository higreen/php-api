<?php

namespace Higreen\Api\Sms;

use Higreen\Api\Http;

/**
 * 阿里云短信
 * 文档地址: https://help.aliyun.com/document_detail/101414.html?spm=a2c4g.11186623.6.625.2af456e0dOQErU
 */
class Aliyun
{
    // 应用ID
    private $id;
    // 应用秘钥
    private $secret;
    // 短信签名
    private $sign;

    /**
     * @param array $init
     *  id     [str] [必填] [应用Access Key ID]
     *  secret [str] [必填] [应用Access Key Secret]
     *  sign   [str] [必填] [签名名称]
     */
    public function __construct($init)
    {
        if (empty($init['id'])) {
            throw new \Exception('I need the "id"');
        }
        if (empty($init['secret'])) {
            throw new \Exception('I need the "secret"');
        }
        if (empty($init['sign'])) {
            throw new \Exception('I need the "sign"');
        }

        $this->id     = $init['id'];
        $this->secret = $init['secret'];
        $this->sign   = $init['sign'];
    }

    /**
     * 发送短信
     *
     * @param  string $phones   [电话号码。支持对多个手机号码发送短信，手机号码之间以英文逗号（,）分隔。上限为1000个手机号码。]
     * @param  string $template [短信模板ID]
     * @param  array  $param    [短信模板参数]
     * @return mixed            [返回true，或者失败信息]
     */
    public function send($phones, $template, $param)
    {
        // 公共请求参数
        $data = [
            'Signature' => '',
            'AccessKeyId' => $this->id,
            'Action' => 'SendSms',
            'Format' => 'JSON',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => rand(),
            'SignatureVersion' => '1.0',
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'Version' => '2017-05-25',
        ];

        // 接口请求参数
        $data = array_merge($data, [
            'PhoneNumbers' => $phones,
            'SignName' => $this->sign,
            'TemplateCode' => $template,
            'TemplateParam' => json_encode($param, 265),
        ]);

        // 发送请求
        $query = $this->_getQuery($data);
        $response = Http::get([
            'url' => 'https://dysmsapi.aliyuncs.com/?' . $query,
        ]);

        if ($response['Code'] !== 'OK') {
            throw new \Exception($response['Message']);
        } else {
            return $response;
        }
    }

    /**
     * --------------------------------------------------------------------
     * 私有方法
     * --------------------------------------------------------------------
     */

    /**
     * 获取请求字符串
     * 文档地址：https://help.aliyun.com/document_detail/101343.html
     *
     * @param  array $config [请求参数]
     * @return string
     */
    private function _getQuery(array $config)
    {
        // 拼接请求参数
        $config = array_filter($config);
        ksort($config);
        $data = '';
        foreach ($config as $key => $value) {
            $key = $this->_speciaUrlencode($key);
            $value = $this->_speciaUrlencode($value);
            $data .= "&{$key}={$value}";
        }
        $query = substr($data, 1);

        // 哈希加密
        $data = $this->_speciaUrlencode($query);
        $data = 'GET&%2F&' . $data;
        $key = $this->secret . '&';
        $signature = base64_encode(hash_hmac('sha1', $data, $key, true));
        $signature = $this->_speciaUrlencode($signature);

        return "Signature={$signature}&{$query}";
    }

    /**
     * 特殊URL编码
     *
     * @param  string $value [待编码的值]
     * @return string
     */
    private function _speciaUrlencode(string $value)
    {
        $value = urlencode($value);
        $value = preg_replace('/\+/', '%20', $value);
        $value = preg_replace('/\*/', '%2A', $value);
        $value = preg_replace('/%7E/', '~', $value);

        return $value;
    }
}
