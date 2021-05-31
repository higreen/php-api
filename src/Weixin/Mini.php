<?php

namespace Higreen\Api\Weixin;

use Higreen\Api\Http;

/**
 * 微信小程序
 * 文档:https://developers.weixin.qq.com/miniprogram/dev/api-backend/
 */
class Mini
{
    public $app_id;
    public $app_secret;

    /**
     * @param array $init
     *  app_id       [str] [必填] [AppID(小程序ID)]
     *  app_secret   [str] [必填] [AppSecret(小程序密钥)]
     */
    public function __construct($init)
    {
        if (empty($init['app_id'])) {
            throw new \Exception('I need the app_id');
        }
        if (empty($init['app_secret'])) {
            throw new \Exception('I need the app_secret');
        }

        $this->app_id     = $init['app_id'];
        $this->app_secret = $init['app_secret'];
    }

    /**
     * 登录凭证校验
     *
     * @param  string  $code [wx.login()接口获得临时登录凭证code]
     * @return array
     */
    public function code2session($code)
    {
        // 发送请求
        $response = Http::get([
            'url' => 'https://api.weixin.qq.com/sns/jscode2session',
            'data' => [
                'appid' => $this->app_id,
                'secret' => $this->app_secret,
                'js_code' => $code,
                'grant_type' => 'authorization_code',
            ],
        ]);

        // 检测响应
        if (empty($response['errcode'])) {
            return $response;
        } else {
            throw new \Exception($response['errmsg'], 555);
        }
    }

    /**
     * 获取小程序全局唯一后台接口调用凭据
     *
     * @return string
     */
    public function getAccessToken()
    {
        // 发送请求
        $response = Http::get([
            'url' => 'https://api.weixin.qq.com/cgi-bin/token',
            'data' => [
                'grant_type' => 'client_credential',
                'appid' => $this->app_id,
                'secret' => $this->app_secret,
            ],
            'response_type' => 'json',
        ]);

        // 检测响应
        if (empty($response['errcode'])) {
            return $response['access_token'];
        } else {
            throw new \Exception($response['errmsg'], 555);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 静态方法
    |--------------------------------------------------------------------------
    */

    /**
     * 解密数据
     *
     * @param  string $data          [用户信息的加密数据]
     * @param  string $session_key   [会话密钥]
     * @param  string $iv            [加密算法的初始向量]
     * @return array
     */
    public static function decryptData($data, $session_key, $iv)
    {
        $userinfo = \openssl_decrypt(
            base64_decode($data),
            'AES-128-CBC',
            base64_decode($session_key),
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );

        return json_decode($userinfo, true);
    }

    /**
     * 获取小程序码
     *
     * @param string $access_token [接口调用凭证]
     * @param array  $params
     *  scene      [str] [必填] [最大32个可见字符]
     *  page       [str] [可选] [已经发布的小程序存在的页面]
     *  width      [int] [可选] [二维码的宽度，单位 px，最小 280px，最大 1280px]
     *  auto_color [bol] [可选] [自动配置线条颜色]
     *  line_color [arr] [可选] [auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示]
     *  is_hyaline [bol] [可选] [是否需要透明底色]
     * @return string [图片二进制内容]
     */
    public static function getWXACodeUnlimit($access_token, $params)
    {
        // 发送请求
        $response = Http::post([
            'url' => 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token,
            'data' => $params,
            'data_type' => 'json',
        ]);

        // 检测响应
        if (empty($response['errcode'])) {
            return $response;
        } else {
            throw new \Exception($response['errmsg'], 555);
        }
    }

    /**
     * 下发小程序和公众号统一的服务消息
     *
     * @param  string $access_token [接口调用凭证]
     * @param  array $params [
     * touser               [必填] [str] [用户openid，可以是小程序的openid，也可以是mp_template_msg.appid对应的公众号的openid]
     * appid                [必填] [str] [公众号appid，要求与小程序有绑定且同主体]
     * template_id          [必填] [str] [公众号模板id]
     * url                  [必填] [str] [公众号模板消息所要跳转的url]
     * data                 [必填] [arr] [模板内容，格式形如 { "key1": { "value": any }, "key2": { "value": any } }]
     * pagepath             [可选] [str] [点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转。]
     * ]
     * @return array
     */
    public static function sendUniformMessage($access_token, $params)
    {
        // 检测请求参数
        $data = [
            'touser' => $params['touser'],
            'mp_template_msg' => [
                'appid' => $params['appid'],
                'template_id' => $params['template_id'],
                'url' => $params['url'],
                // 'miniprogram' => [
                //     'appid' => $this->app_id,
                //     'pagepath' => $params['pagepath'],
                // ],
                'data' => $params['data'],
            ],
        ];

        // 发送请求
        $response = Http::post([
            'url' => 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token=' . $access_token,
            'data' => $data,
        ]);

        // 检测响应
        if (empty($response['errcode'])) {
            return $response;
        } else {
            throw new \Exception($response['errmsg'], 555);
        }
    }
}
