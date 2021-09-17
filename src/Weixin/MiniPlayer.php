<?php

namespace Higreen\Api\Weixin;

use Higreen\Api\Http;

/**
 * 微信小程序-直播
 * 文档:https://developers.weixin.qq.com/miniprogram/dev/framework/liveplayer/studio-api.html
 */
class MiniPlayer
{
    /**
     * 获取直播间列表
     *
     * @param  string  $access_token [接口调用凭证]
     * @param  integer $start        [起始房间]
     * @param  integer $limit        [每次拉取的房间数量]
     * @return bool
     */
    public static function getLiveInfo($access_token, $start = 0, $limit = 10)
    {
        $url = 'https://api.weixin.qq.com/wxa/business/getliveinfo?access_token=' . $access_token;

        // 发送请求
        $response = Http::post([
            'url' => $url,
            'data' => [
                'start' => $start,
                'limit' => $limit,
            ],
        ]);

        // 检测响应
        if (empty($response['errcode'])) {
            return $response;
        } else {
            throw new \ErrorException($response['errmsg'], 555);
        }
    }
}
