<?php

namespace Higreen\Api\Alipay;

use Higreen\Api\Http;

/**
 * 支付宝小程序
 */
class Mini extends A
{
    /**
     * 获取支付宝用户的唯一userId
     *
     * @param  array $code [授权码]
     * @return string
     */
    public function getUserId(string $code)
    {
        // 请求参数
        $data['grant_type'] = 'authorization_code';
        $data['code'] = $code;

        // 获取签名
        $data['sign'] = $this->getSignature($data);

        // 发送请求
        $response = $this->sendRequest('alipay.system.oauth.token', $data);
        if (!empty($response['error_response']) && !empty($response['error_response']['sub_msg'])) {
            throw new \ErrorException($response['error_response']['sub_msg'], 555);
        }

        return $response['alipay_system_oauth_token_response']['user_id'];
    }
}
