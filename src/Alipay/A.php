<?php

namespace Higreen\Api\Alipay;

use Higreen\Api\Http;

/**
 * 鸡肋
 */
class A
{
    /**
     * @var string
     */
    protected $app_id;

    /**
     * @var string
     */
    protected $app_key;

    /**
     * @var string
     */
    protected $notify_url = '';

    /**
     * Create a new instance.
     * 
     * @param array $init [
     *  app_id     【string】【必填】【 支付宝分配给开发者的应用ID】
     *  app_key    【string】【必填】【应用密钥】
     *  notify_url 【string】【可选】【支付宝服务器主动通知商户服务器里指定的页面 http/https 路径】
     * ]
     * @return void
     */
    public function __construct($init)
    {
        $this->app_id = $init['app_id'];
        $this->app_key = $init['app_key'];
        if (isset($init['notify_url'])) {
            $this->notify_url = $init['notify_url'];
        }
    }

    /**
     * 获取公共请求参数
     */
    protected function getCommonParams(): array
    {
        return [
            'app_id'      => $this->app_id,
            'method'      => '',
            'format'      => 'JSON',
            'biz_content' => '',
            'charset'     => 'UTF-8',
            'sign_type'   => 'RSA2',
            'sign'        => '',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => '1.0',
            'notify_url'  => $this->notify_url,
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
     * 发送请求
     * 
     * @param  string 接口名称
     * @param  array  业务请求参数的集合
     * @return array
     */
    protected function sendRequest($method, $biz_content)
    {
        $data = $this->getCommonParams();
        $data['method'] = $method;
        $data['biz_content'] = json_encode($biz_content, JSON_UNESCAPED_UNICODE);
        $data['sign'] = $this->getSignature($data);

        return Http::post([
            'url' => 'https://openapi.alipay.com/gateway.do',
            'data' => $data,
            'data_type' => 'form',
        ]);
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
