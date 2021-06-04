<?php

namespace Higreen\Api\Weixin;

use Higreen\Api\Http;

/**
 * 微信小程序-文字识别
 * 文档:https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/ocr/ocr.bankcard.html
 */
class MiniOcr
{
    /**
     * 银行卡
     *
     * @param  string $access_token [接口调用凭证]
     * @param  string $img_url      [图片链接]
     * @return array
     */
    public static function bankcard($access_token, $img_url)
    {
        $url = "https://api.weixin.qq.com/cv/ocr/bankcard?type=MODE&access_token={$access_token}&img_url={$img_url}";
        return self::_ocr($url);
    }

    /**
     * 营业执照
     *
     * @param  string $access_token [接口调用凭证]
     * @param  string $img_url      [图片链接]
     * @return array
     */
    public static function businessLicense($access_token, $img_url)
    {
        $url = "https://api.weixin.qq.com/cv/ocr/bizlicense?access_token={$access_token}&img_url={$img_url}";
        return self::_ocr($url);
    }

    /**
     * 驾驶证
     *
     * @param  string $access_token [接口调用凭证]
     * @param  string $img_url      [图片链接]
     * @return array
     */
    public static function driverLicense($access_token, $img_url)
    {
        $url = "https://api.weixin.qq.com/cv/ocr/drivinglicense?access_token={$access_token}&img_url={$img_url}";
        return self::_ocr($url);
    }

    /**
     * 身份证
     *
     * @param  string $access_token [接口调用凭证]
     * @param  string $img_url      [图片链接]
     * @return array
     */
    public static function idcard($access_token, $img_url)
    {
        $url = "https://api.weixin.qq.com/cv/ocr/idcard?access_token={$access_token}&img_url={$img_url}";
        return self::_ocr($url);
    }

    /**
     * 通用印刷体
     *
     * @param  string $access_token [接口调用凭证]
     * @param  string $img_url      [图片链接]
     * @return array
     */
    public static function printedText($access_token, $img_url)
    {
        $url = "https://api.weixin.qq.com/cv/ocr/comm?access_token={$access_token}&img_url={$img_url}";
        return self::_ocr($url);
    }

    /**
     * 行驶证
     *
     * @param  string $access_token [接口调用凭证]
     * @param  string $img_url      [图片链接]
     * @return array
     */
    public static function vehicleLicense($access_token, $img_url)
    {
        $url = "https://api.weixin.qq.com/cv/ocr/driving?access_token={$access_token}&img_url={$img_url}";
        return self::_ocr($url);
    }

    /*
    |--------------------------------------------------------------------------
    | 私有方法
    |--------------------------------------------------------------------------
    */

    /**
     * 文字识别
     *
     * @param  string $url     [接口]
     * @return array
     */
    private static function _ocr($url)
    {
        // 发送请求
        $response = Http::post([
            'url' => $url,
        ]);

        // 检测响应
        if (empty($response['errcode'])) {
            return $response;
        } else {
            throw new \Exception($response['errmsg'], 555);
        }
    }
}
