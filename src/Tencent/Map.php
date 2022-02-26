<?php

namespace Higreen\Api\Tencent;

use Higreen\Api\Http;

/**
 * 腾讯地图
 * 文档地址：https://lbs.qq.com/webservice_v1/index.html
 */
class Map
{
    /**
     * 应用秘钥
     * 
     * @var string
     */
    private $key;

    /**
     * Create a new instance.
     * 
     * @param  string $key 密钥
     * @return void
     */
    public function __construct($key)
    {
        if (!$key) {
            throw new \Exception('I need the key');
        }

        $this->key = $key;
    }

    /**
     * 地址定位
     *
     * @param  string $address 地址描述
     * @param  bool $location 是否为经纬度
     * @return array
     */
    public function locateByAddress($address, $is_location = false)
    {
        // 请求参数
        $data = [
            'key' => $this->key,
        ];
        if ($is_location) {
            $data['location'] = $address;
        } else {
            $data['address'] = $address;
        }

        // 发送请求
        $response = Http::get([
            'url' => 'https://apis.map.qq.com/ws/geocoder/v1/',
            'data' => $data,
        ]);
        if ($response['status'] !== 0) {
            throw new \ErrorException($response['message'], 555);
        }

        return $response['result'];
    }

    /**
     * 坐标定位
     *
     * @param float $longitude 经度
     * @param float $latitude 纬度
     * @return array
     */
    public function locateByCoordinate($longitude, $latitude)
    {
        # 检测请求参数
        if (
            !is_numeric($latitude)
            || !is_numeric($latitude)
            || abs($longitude) > 180
            || abs($latitude) > 90
        ) {
            throw new \ErrorException('经纬度不合法', 406);
        }

        return $this->locateByAddress("{$latitude},{$longitude}", true);
    }

    /**
     * IP定位
     *
     * @param  string $ip IP地址,默认当前请求IP
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
            throw new \ErrorException($response['message'], 555);
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
            throw new \ErrorException($response['message'], 555);
        }

        return $response['result'];
    }
}
