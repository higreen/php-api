<?php

namespace Higreen\Api\Baidu;

use Higreen\Api\Http;

/**
 * 基础类
 */
class Base
{
    // 应用ID
    protected $app_id;
    // 应用公钥
    protected $api_key;
    // 应用密钥
    protected $secret_key;

    /**
     * 获取接口调用凭据
     *
     * @return string
     */
    public function getAccessToken()
    {
        // 发送请求
        $response = Http::post([
            'url'  => 'https://aip.baidubce.com/oauth/2.0/token',
            'data' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->api_key,
                'client_secret' => $this->secret_key,
            ],
            'data_type' => 'form',
            'response_type' => 'json',
        ]);

        // 检测响应
        if (empty($response['error'])) {
            return $response['access_token'];
        } else {
            throw new \Exception($response['error_description']);
        }
    }

    /**
     * 获取签名
     *
     * @param  array $config [签名的参数]
     * @return string
     */
    protected function getSignature(array $config)
    {
        ksort($config);

        $data = '';
        foreach ($config as $key => $val) {
            $data .= "{$key}={$val}&";
        }
        $data .= 'key='.$this->mch_key;

        return strtoupper(md5($data));
    }
}
