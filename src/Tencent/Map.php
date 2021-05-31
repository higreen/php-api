<?php

namespace Higreen\Api\Tencent;

use Higreen\Api\Http;

/**
 * 地图
 * 文档地址：https://lbs.qq.com/webservice_v1/index.html
 */
class Map
{
    // 应用秘钥
    private $key;

    /**
     * @param string $key [密钥]
     */
    public function __construct($key)
    {
        if (!$key) {
            throw new \Exception('I need the "key"');
        }

        $this->key = $key;
    }

    /**
     * 地址定位
     *
     * @param  string $address [地址描述]
     * @return array
     */
    public function locateByAddress(string $address)
    {
        // 发送请求
        $response = Http::get([
            'url' => 'https://apis.map.qq.com/ws/geocoder/v1/',
            'data' => [
                'address' => $address,
                'key' => $this->key,
            ],
        ]);

        return $response;
    }

    /**
     * IP定位
     *
     * @param  string $ip [IP地址]
     * @return array
     */
    public function locateByIp($ip = '')
    {
        $ip = $ip ?: $_SERVER['REMOTE_ADDR'];

        // 发送请求
        $response = Http::get([
            'url' => 'https://apis.map.qq.com/ws/location/v1/ip',
            'data' => [
                'ip' => $ip,
                'key' => $this->key,
            ],
        ]);
        if ($response['status'] !== 0) {
            throw new \Exception($response['message']);
        }

        return $response['result'];
    }

    /**
     * 查询行政区
     *
     * @param  integer $id [父级行政区划ID]
     * @return array
     */
    public function getDistrict($id = 0)
    {
        $url = 'https://apis.map.qq.com/ws/district/v1/list';
        $data = [
            'key' => $this->key,
        ];

        // 子级行政区划
        if ($id) {
            $url = 'https://apis.map.qq.com/ws/district/v1/getchildren';
            $data['id'] = $id;
        }

        // 发送请求
        $response = Http::get([
            'url' => $url,
            'data' => $data,
        ]);
        if ($response['status'] !== 0) {
            throw new \Exception($response['message']);
        }

        return $response['result'];
    }
}
