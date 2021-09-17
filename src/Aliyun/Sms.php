<?php

namespace Higreen\Api\Aliyun;

use Higreen\Api\Http;

/**
 * 阿里云短信服务
 * 文档: https://help.aliyun.com/document_detail/101414.html
 */
class Sms
{
    // 密钥ID
    private $id;

    // 秘钥KEY
    private $secret;

    // 短信签名
    private $sign;

    /**
     * Create a new instance.
     * 
     * @param array $init [
     *  id     [str] [必填] [密钥AccessKey ID]
     *  secret [str] [必填] [密钥AccessKey Secret]
     *  sign   [str] [必填] [短信签名]
     * ]
     * @return void
     */
    public function __construct($init)
    {
        if (empty($init['id'])) {
            throw new \Exception('I need the id');
        }
        if (empty($init['secret'])) {
            throw new \Exception('I need the secret');
        }
        if (empty($init['sign'])) {
            throw new \Exception('I need the sign');
        }

        $this->id = $init['id'];
        $this->secret = $init['secret'];
        $this->sign = $init['sign'];
    }

    /**
     * 发送短信
     *
     * @param  string $phones   电话号码。支持对多个手机号码发送短信，手机号码之间以英文逗号（,）分隔。上限为1000个手机号码
     * @param  string $template 短信模板ID
     * @param  array  $params   短信模板参数
     * @return array
     */
    public function send($phones, $template, $params = [])
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
        ]);
        if ($params) {
            $data['TemplateParam'] = json_encode($params, 265);
        }

        // 发送请求
        $query = $this->_getQuery($data);
        $response = Http::get([
            'url' => 'https://dysmsapi.aliyuncs.com/?' . $query,
        ]);

        return $response;
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
     * @param  array $config 请求参数
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
     * @param  string $value 待编码的值
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
