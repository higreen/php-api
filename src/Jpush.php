<?php

namespace Higreen\Api;

/**
 * 极光推送
 * 文档:https://docs.jiguang.cn/jpush/server/push/rest_api_v3_push
 */
class Jpush
{
    /**
     * 极光平台应用的唯一标识
     *
     * @var string
     */
    private $key;

    /**
     * 用于服务器端 API 调用时与 AppKey 配合使用达到鉴权的目的
     *
     * @var string
     */
    private $secret;

    /**
     * Create a new instance.
     * 
     * @param  string $key AppKey
     * @param  string $secret Master Secret
     * @return void
     */
    public function __construct(string $key, string $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * 向某单个设备或者某设备列表推送一条通知、或者消息
     *
     * @param  array $params [
     *  platform [str|arr] [可选,默认所有平台] [推送平台设置:android,ios,quickapp,winphone]
     *  audience [str|arr] [可选,默认全部设备] [推送目标]
     *  message [arr] [可选] [消息内容体]
     *  notification [arr] [可选] [通知内容体]
     *  notification_3rd [arr] [可选] [自定义消息转厂商通知内容体]
     *  sms_message [arr] [可选] [短信渠道补充送达内容体]
     *  options [arr] [可选] [推送参数]
     *  callback [arr] [可选] [回调参数]
     *  cid [str] [可选] [用于防止 api 调用端重试造成服务端的重复推送而定义的一个标识符]
     * ]
     * @return array
     */
    public function push($params)
    {
        // 构建请求数据
        $data = [
            'platform' => 'all',
            'audience' => 'all',
        ];
        if (isset($params['platform'])) {
            $data['platform'] = $params['platform'];
        }
        if (isset($params['audience'])) {
            $data['audience'] = $params['audience'];
        }
        if (isset($params['message'])) {
            $data['message'] = $params['message'];
        }
        if (isset($params['notification'])) {
            $data['notification'] = $params['notification'];
        }
        if (isset($params['notification_3rd'])) {
            $data['notification_3rd'] = $params['notification_3rd'];
        }
        if (isset($params['sms_message'])) {
            $data['sms_message'] = $params['sms_message'];
        }
        if (isset($params['options'])) {
            $data['options'] = $params['options'];
        }
        if (isset($params['callback'])) {
            $data['callback'] = $params['callback'];
        }
        if (isset($params['cid'])) {
            $data['cid'] = $params['cid'];
        }
        if (empty($data['message']) && empty($data['notification'])) {
            throw new \Exception('缺少推送内容', 400);
        }

        // 发送请求
        $authorization = $this->_getAuthorization();
        $response = Http::post([
            'url' => 'https://api.jpush.cn/v3/push',
            'data' => $data,
            'header' => [
                "Authorization: {$authorization}",
            ],
        ]);

        return $response;
    }

    /**
     * 推送-消息
     *
     * @param  string $audience 推送目标
     * @param  array $message 消息内容体
     * @return array
     */
    public function pushMessage($audience, $message)
    {
        return $this->push([
            'audience' => $audience,
            'message' => $message,
        ]);
    }

    /**
     * 推送-通知
     *
     * @param  string $audience 推送目标
     * @param  array $notification 通知内容体
     * @return void
     */
    public function pushNotification($audience, $notification)
    {
        return $this->push([
            'audience' => $audience,
            'notification' => $notification,
        ]);
    }

    /**
     * 获取授权
     *
     * @return void
     */
    private function _getAuthorization(): string
    {
        $str = "{$this->key}:{$this->secret}";
        $str = base64_encode($str);

        return "Basic {$str}";
    }
}
