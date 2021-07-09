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
     * Constructor
     * 
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
            'biz_content' => '',
            'charset'     => 'UTF-8',
            'method'      => '',
            'notify_url'  => $init['notify_url'],
            'sign'        => '',
            'sign_type'   => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => '1.0',
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

        // The string of data you wish to sign
        $data = '';
        foreach ($params as $key => $val) {
            $data .= "{$key}={$val}&";
        }
        $data = rtrim($data, '&');

        // Generate signature
        $private_key = wordwrap($this->app_key, 64, "\n", true);
        $private_key = "-----BEGIN RSA PRIVATE KEY-----\n{$private_key}\n-----END RSA PRIVATE KEY-----";
        openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    /**
     * 验证签名
     *
     * @param  string $public_key 支付宝公钥
     * @return array 验证成功返回请求参数
     */
    public static function checkSignature($public_key)
    {
        // 获取请求参数
        $input = file_get_contents('php://input');
        if (!$input) return [];

        // 格式化请求参数
        $data = explode('&', $input);
        $input = [];
        foreach ($data as $i) {
            $i = explode('=', $i);
            if (count($i) === 2) {
                $input[$i[0]] = urldecode($i[1]);
            }
        }
        if (!$input) return [];
        if ($input['trade_status'] !== 'TRADE_SUCCESS') return [];

        // 提取签名
        $signature = base64_decode($input['sign']);
        unset($input['sign'], $input['sign_type']);
        ksort($input);

        // The string of data used to generate the signature previously
        $data = '';
        foreach ($input as $key => $val) {
            $data .= "{$key}={$val}&";
        }
        $data = rtrim($data, '&');

        // Verify signature
        $public_key = wordwrap($public_key, 64, "\n", true);
        $public_key = "-----BEGIN PUBLIC KEY-----\n{$public_key}\n-----END PUBLIC KEY-----";
        $res = openssl_verify($data, $signature, $public_key, OPENSSL_ALGO_SHA256);

        return $res === 1 ? $input : [];
    }
}
