<?php

namespace Higreen\Api\Sms;

use Higreen\Api\Http;

/*
 * 腾讯云
 * 文档地址: https://cloud.tencent.com/document/product/382/38778
 */
class Tencent
{
    // 应用ID
    private $id;
    // 应用秘钥
    private $key;
    // 短信签名
    private $sign;

    /**
     * @param array $init
     *  id   [str] [必填] [应用id，SDK AppID]
     *  key  [str] [必填] [应用密钥，App Key]
     *  sign [str] [必填] [签名名称]
     */
    public function __construct($init)
    {
        if (empty($init['id'])) {
            throw new \Exception('I need the "id"');
        }
        if (empty($init['key'])) {
            throw new \Exception('I need the "key"');
        }
        if (empty($init['sign'])) {
            throw new \Exception('I need the "sign"');
        }

        $this->id   = $init['id'];
        $this->key  = $init['key'];
        $this->sign = $init['sign'];
    }

    /**
     * 指定模板单发短信
     *
     * @param  integer $phone       [电话号码]
     * @param  integer $tpl_id      [短信模板ID]
     * @param  array   $tpl_params  [短信模板参数]
     * @return mixed                [true||失败信息]
     */
    public function send($phone, $tpl_id, $tpl_params)
    {
        $random = rand();
        $time = time();
        $url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid='.$this->id.'&random='.$random;
        $data = [
            'tel' => [
                'nationcode' => '86',
                'mobile' => $phone,
            ],
            'tpl_id' => $tpl_id,
            'params' => $tpl_params,
            'time' => $time,
            'sig' => hash('sha256', 'appkey='.$this->key.'&random='.$random.'&time='.$time.'&mobile='.$phone),
            'sign' => $this->sign,
        ];

        // 发送请求
        $response = Http::post([
            'url' => $url,
            'data' => $data,
            'data_type' => 'json',
        ]);

        if ($response['result'] === 0) {
            return true;
        } else {
            return $response['errmsg'];
        }
    }
}
