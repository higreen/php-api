<?php

namespace Higreen\Api\Weixin;

/**
 * 微信开放平台
 * 文档:https://developers.weixin.qq.com/doc/oplatform/Mobile_App/Resource_Center_Homepage.html
 */
class Oplatform extends Base
{
    /**
     * @param array $init
     *  app_id       [str] [必填] [AppID(应用ID)]
     *  app_secret   [str] [必填] [AppSecret(应用密钥)]
     */
    public function __construct($init)
    {
        if (empty($init['app_id'])) {
            throw new \Exception('I need the "app_id"');
        }
        if (empty($init['app_secret'])) {
            throw new \Exception('I need the "app_secret"');
        }

        $this->app_id     = $init['app_id'];
        $this->app_secret = $init['app_secret'];
    }

    /**
     * 通过code获取access_token
     *
     * @param  string $code [用户授权code]
     * @return array
     */
    public function code2access_token($code)
    {
        // 发送请求
        $response = Http::get([
            'url' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
            'data' => [
                'appid' => $this->app_id,
                'secret' => $this->app_secret,
                'code' => $code,
                'grant_type' => 'authorization_code',
            ],
        ]);

        // 检测响应
        if (empty($response['errcode'])) {
            return $response;
        } else {
            throw new \Exception($response['errmsg']);
        }
    }

    /**
     * 获取用户信息
     *
     * @param  string $access_token [接口调用凭据]
     * @param  string $openid       [用户openid]
     * @return array
     */
    public function getUserinfo($access_token, $openid)
    {
        // 发送HTTP请求
        $response = Http::get([
            'url' => 'https://api.weixin.qq.com/sns/userinfo',
            'data' => [
                'access_token' => $access_token,
                'openid' => $openid,
                'lang' => 'zh_CN',
            ],
        ]);

        if (!empty($response['errcode'])) {
            throw new \Exception($response['errmsg']);
        }

        return $response;
    }
}
