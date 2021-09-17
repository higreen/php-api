<?php

namespace Higreen\Api\Weixin;

use Higreen\Api\Http;

/**
 * 微信公众号
 */
class Offi
{
    public $app_id;
    public $app_secret;

    /**
     * Create a new instance.
     * 
     * @param array $init [
     *  app_id     [str] [必填] [AppID(公众号ID)]
     *  app_secret [str] [必填] [AppSecret(公众号密钥)]
     * ]
     * @return void
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
     * @param  string  $code [网页授权]
     * @return array
     */
	public function code2session($code)
	{
		$response = Http::get([
			'url' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
			'data' => [
				'appid' => $this->app_id,
				'secret' => $this->app_secret,
				'code' => $code,
				'grant_type' => 'authorization_code',
			],
		]);
		
		if (!empty($response['errcode'])) {
            throw new \ErrorException($response['errmsg'], 555);
        }

        return $response;
	}

    /**
     * 获取接口调用凭据
     *
     * @return string
     */
    public function getAccessToken()
    {
		// 发送HTTP请求
		$response = Http::get([
			'url' => 'https://api.weixin.qq.com/cgi-bin/token',
			'data' => [
				'appid' => $this->app_id,
				'secret' => $this->app_secret,
				'grant_type' => 'client_credential',
			],
		]);

        if (!empty($response['errcode'])) {
            throw new \ErrorException($response['errmsg'], 555);
        }

        return $response['access_token'];
    }

    /*
    |--------------------------------------------------------------------------
    | 静态方法
    |--------------------------------------------------------------------------
    */

    /**
     * JS-SDK 的权限验证配置
     *
     * @param  string $ticket [临时票据]
     * @param  string $url    [页面链接]
     * @return array
     */
    public static function getJsapiConfig($ticket, $url = '')
    {
        if (!$url || empty($_SERVER['HTTP_REFERER'])) {
            throw new \ErrorException('缺少当前网页的URL', 555);
        }

        $params = [
            'jsapi_ticket' => $ticket,
            'noncestr' => rand(),
            'timestamp' => time(),
            'url' => $url ?: $_SERVER['HTTP_REFERER'],
        ];

        // 签名算法
        $signature = '';
        foreach ($params as $key => $value) {
            $signature .= "&{$key}={$value}";
        }
        $signature = sha1(substr($signature, 1));
        $params['signature'] = $signature;

        return $params;
    }

    /**
     * 获取微信JS接口的临时票据
     *
     * @param  string $access_token [调用凭据]
     * @return string
     */
    public static function getJsapiTicket($access_token)
    {
        // 发送HTTP请求
        $response = Http::get([
            'url' => 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=' . $access_token,
        ]);

        if (!empty($response['errcode'])) {
            throw new \ErrorException($response['errmsg'], 555);
        }

        return $response['ticket'];
    }

    /**
     * 获取用户信息
     *
     * @param  string $access_token [接口调用凭据]
     * @param  string $openid       [用户openid]
     * @return array
     */
    public static function getUserinfo($access_token, $openid)
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
            throw new \ErrorException($response['errmsg'], 555);
        }

        return $response;
    }

    /**
     * 是否关注公众号
     *
     * @param  string  $access_token [调用接口凭证]
     * @param  string  $openid       [用户的标识]
     * @return boolean
     */
    public static function isFollow($access_token, $openid)
    {
        // 发送HTTP请求
        $response = Http::get([
            'url' => "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN",
        ]);

        if (!empty($response['errcode'])) {
            throw new \ErrorException($response['errmsg'], 555);
        }

        return empty($response['subscribe']);
    }

    /**
     * 发送模板消息
     *
     * @param string $access_token [调用凭据]
     * @param array $params
     *  touser       [str] [必填] [接收者openid]
     *  template_id  [str] [必填] [模板ID]
     *  data         [arr] [必填] [模板数据]
     * @return array
     */
    public static function sendMessage($access_token, $params)
    {
        $data = [
            'touser' => $params['touser'],
            'template_id' => $params['template_id'],
            'url' => '',
            'miniprogram' => [
                'appid' => '',
                'pagepath' => '',
            ],
            'data' => $params['data'],
            'color' => '',
        ];

        // 发送HTTP请求
        $response = Http::post([
            'url' => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token,
            'data' => $data,
        ]);

        if (!empty($response['errcode'])) {
            throw new \ErrorException($response['errmsg'], 555);
        }

        return $response;
    }
}
