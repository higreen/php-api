<?php

namespace Higreen\Api\Tencent;

use Higreen\Api\Http;

/*
 * 腾讯云短信
 * 文档: https://cloud.tencent.com/document/product/382/55981
 */
class Sms extends Base
{
    /** 
     * 短信应用 SDKAppID
     * 
     * @var string
     * */ 
    private $app_id;

    /**
     * 短信签名
     * 
     * @var string
     */
    private $sign;

    /**
     * Create a new instance.
     * 
     * @param array $init [
     *  id      [str] [必填] [密钥 ID]
     *  key     [str] [必填] [密钥 KEY]
     *  app_id  [str] [必填] [短信应用 SDKAppID]
     *  sign    [str] [必填] [短信签名]
     * ]
     * @return void
     */
    public function __construct($init)
    {
        if (empty($init['app_id'])) {
            throw new \Exception('I need the app_id');
        }
        if (empty($init['sign'])) {
            throw new \Exception('I need the sign');
        }

        $this->app_id = $init['app_id'];
        $this->sign = $init['sign'];

        parent::__construct($init);
    }

    /**
     * 指定单个手机号
     *
     * @param  string  $template  短信模板ID
     * @param  string  $phones    手机号
     * @param  array   $params    短信模板参数
     * @return array
     */
    public function sendSingle($template, $phone, $params = [])
    {
        return $this->sendMulti($template, [$phone], $params);
    }

    /**
     * 发送多个手机号
     * 
     * @param  string  $template  短信模板ID
     * @param  array   $phones    手机号
     * @param  array   $params    短信模板参数
     * @return array
     */
    public function sendMulti($template, $phones, $params = [])
    {
        # 单次请求最多支持200个手机号
        if (count($phones) > 200) {
            $rest = array_splice($phones, 200);
            $this->sendMulti($template, $rest, $params);
        }

        $url = 'https://sms.tencentcloudapi.com';

        // 请求数据
        $data = [
            'PhoneNumberSet' => $phones,
            'SmsSdkAppId' => $this->app_id,
            'SignName' => $this->sign,
            'TemplateId' => $template,
        ];
        if ($params) {
            $data['TemplateParamSet'] = $params;
        }

        $authorization = $this->getAuthorization('POST', $url, $data);
        $time = time();

        // 发送请求
        $response = Http::post([
            'url' => $url,
            'data' => $data,
            'header' => [
                "Authorization: {$authorization}",
                'X-TC-Action: SendSms',
                'X-TC-Region: ap-guangzhou',
                "X-TC-Timestamp: {$time}",
                'X-TC-Version: 2021-01-11',
            ],
        ]);

        // 判断响应
        if (!empty($response['Error']['Message'])) {
            throw new \ErrorException($response['Error']['Message'], 555);
        }

        return $response;
    }
}
