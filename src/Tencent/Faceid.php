<?php

namespace Higreen\Api\Tencent;

use Higreen\Api\Http;

/*
 * 人脸核身
 * 文档: https://cloud.tencent.com/document/product/1007
 */
class Faceid extends Base
{
    public function __construct($init)
    {
        parent::__construct($init);
    }

    /**
     * 传入姓名和身份证号，校验两者的真实性和一致性。
     *
     * @param  string $name 姓名
     * @param  string $number 身份证号
     * @return bool
     */
    public function verifyNameNumber(string $name, string $number)
    {
        $url = 'https://faceid.tencentcloudapi.com';

        // 请求数据
        $data = [
            'IdCard' => $number,
            'Name' => $name,
        ];

        $authorization = $this->getAuthorization('POST', $url, $data);
        $time = time();

        // 发送请求
        $response = Http::post([
            'url' => $url,
            'data' => $data,
            'header' => [
                "Authorization: {$authorization}",
                'X-TC-Action: IdCardVerification',
                "X-TC-Timestamp: {$time}",
                'X-TC-Version: 2018-03-01',
            ],
        ]);

        // 判断响应
        $code = $response['Response']['Result'] ?? null;
        $message = $response['Response']['Description'] ?? '未知错误';
        switch ($code) {
            case '0':
                return true;
            case '1':
                return false;
            default:
                throw new \ErrorException($message, 555);
        }
    }
}
