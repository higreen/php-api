<?php

namespace Higreen\Api\Weixin;

use Higreen\Api\Http;

/**
 * 微信小程序-内容安全
 * 文档:https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/sec-check/security.imgSecCheck.html
 */
class MiniSecurity
{
    /**
     * 校验一张图片是否含有违法违规内容
     *
     * @param  string $access_token [接口调用凭证]
     * @param  string $contents     [图片二进制内容]
     * @return bool
     */
    public static function checkImg($access_token, $contents)
    {
        $url = 'https://api.weixin.qq.com/wxa/img_sec_check?access_token=' . $access_token;

        // 发送请求
        $response = Http::post([
            'url' => $url,
            'data' => [
                'media' => file_get_contents($src),
            ],
            'data_type' => 'form',
        ]);

        // 检测响应
        if (empty($response['errcode'])) {
            return true;
        } else {
            throw new \Exception($response['errMsg'], 555);
        }
    }

    /**
     * 检测文本内容
     *
     * @param  string $access_token [接口调用凭证]
     * @param  string $contents     [文本内容]
     * @return bool
     */
    public static function checkText($access_token, $contents)
    {
        $url = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token=' . $access_token;

        // 发送请求
        $response = Http::post([
            'url' => $url,
            'data' => [
                'content' => $contents,
            ],
        ]);

        return empty($response['errcode']);
    }

    /**
     * 异步校验图片/音频是否含有违法违规内容。
     *
     * @param  string $access_token [接口调用凭证]
     * @return [type] [description]
     */
    public static function cehckMedia($access_token, $contents) {}
}
