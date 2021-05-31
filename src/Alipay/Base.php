<?php

namespace Higreen\Api\Alipay;

/**
 * 基础类
 */
class Base
{
    // 支付宝网关地址
    protected $url = 'https://openapi.alipay.com/gateway.do';
    // 应用ID
    protected $app_id;
    // 应用秘钥
    protected $app_key;
    // 公共请求参数
    protected $request_body = [];

    /**
     * @param array $init
     *  app_id       [str] [必填] [支付宝分配给开发者的应用ID]
     *  app_key      [str] [必填] [应用密钥]
     *  notify_url   [str] [必填] [支付宝服务器主动通知商户服务器里指定的页面 http/https 路径]
     */
    public function __construct($init)
    {
        if (empty($init['app_id'])) {
            throw new \Exception('I need the "app_id"');
        }
        if (empty($init['app_key'])) {
            throw new \Exception('I need the "app_key"');
        }
        if (empty($init['notify_url'])) {
            throw new \Exception('I need the "notify_url"');
        }

        $this->app_key = $init['app_key'];
        $this->request_body = [
            'app_id'      => $init['app_id'],
            'method'      => '',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'sign'        => '',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => '1.0',
            'notify_url'  => $init['notify_url'],
            'biz_content' => '',
        ];
    }

    /**
     * 获取签名
     *
     * @param  array $params [签名的数据]
     * @return string
     */
    protected function getSignature($params)
    {
        $params = array_filter($params);
        ksort($params);

        $data = '';
        foreach ($params as $key => $val) {
            $data .= "{$key}={$val}&";
        }
        $data = rtrim($data, '&');

        $priv_key_id = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->app_key, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";

        openssl_sign($data, $signature, $priv_key_id, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    /**
     * 验证签名
     *
     * @param  array  $params     [回调请求参数]
     * @param  string $public_key [支付宝公钥]
     * @return bool
     */
    public static function checkSignature($params, $public_key)
    {
        $sign = str_replace(' ', '+', $params['sign']);
        $signature = base64_decode($sign);
        unset($params['sign'], $params['sign_type']);
        ksort($params);

        $data = '';
        foreach ($params as $key => $val) {
            $data .= "{$key}={$val}&";
        }
        $data = rtrim($data, '&');

        $pub_key_id = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($public_key, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";

        $res = openssl_verify($data, $signature, $pub_key_id, OPENSSL_ALGO_SHA256);

        return $res === 1;
    }
}
