<?php

namespace Higreen\Api\Baidu;

use Higreen\Api\Http;

/**
 * 百度语音
 * 文档：https://cloud.baidu.com/doc/SPEECH/index.html
 */
class Speech extends Base
{

    /**
     * @param array $init
     *  api_key    [str] [必填] [应用公钥]
     *  secret_key [str] [必填] [应用密钥]
     */
    public function __construct($init)
    {
        if (empty($init['api_key'])) {
            throw new \Exception('I need the "api_key"');
        }
        if (empty($init['secret_key'])) {
            throw new \Exception('I need the "secret_key"');
        }

        $this->api_key    = $init['api_key'];
        $this->secret_key = $init['secret_key'];
    }

    /**
     * 语音合成
     *
     * @param string $access_token [接口调用凭证]
     * @param array  $params [
     *  text [str] [必填] [文字]
     * ]
     * @return string [二进制文件]
     */
    public function text2audio($access_token, $params)
    {
        // 请求参数
        $data = [
            'tok' => $access_token,
            'tex' => $params['text'],
            'cuid' => strval(time()),
            'ctp' => '1',
            'lan' => 'zh',
            'spd' => '',
            'pit' => '',
            'vol' => '',
            'per' => '',
            'aue' => '',
        ];

        // 可选参数
        if (isset($params['spd']))
            $data['spd'] = $params['spd'];
        if (isset($params['pit']))
            $data['pit'] = $params['pit'];
        if (isset($params['vol']))
            $data['vol'] = $params['vol'];
        if (isset($params['per']))
            $data['per'] = $params['per'];
        if (isset($params['aue']))
            $data['aue'] = $params['aue'];

        // 格式化数据
        $data = array_filter($data);
        $data['tex'] = strtolower(urlencode($data['tex']));

        // 发送请求
        $response = Http::post([
            'url' => 'https://tsn.baidu.com/text2audio',
            'data' => $data,
            'data_type' => 'form',
        ]);

        // 判断响应
        if (empty($response['err_no'])) {
            return $response;
        } else {
            throw new \Exception($response['err_msg'], 555);
        }
    }
}
