<?php

namespace Higreen\Api;

/**
 * 实用工具
 */
class Utils
{
    /**
     * 计算两个坐标的距离
     *
     * @param  float $lon1 [坐标1-经度]
     * @param  float $lat1 [坐标1-纬度]
     * @param  float $lon2 [坐标2-经度]
     * @param  float $lat2 [坐标2-纬度]
     * @return integer
     */
    public static function getInstance($lon1, $lat1, $lon2, $lat2)
    {
        $dlat = deg2rad($lat2 - $lat1);
        $dlon = deg2rad($lon2 - $lon1);
        $a = pow(sin($dlat/2), 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * pow(sin($dlon/2), 2);
        $d = round(6378137 * 2 * atan2(sqrt($a), sqrt(1-$a)));

        if ($d < 1000) {
            return "{$d}m";
        }

        return round($d / 1000, 1) . 'km';
    }
}
