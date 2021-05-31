<?php

namespace Higreen\Api;

/**
 * 发送HTTP请求
 */
class Http
{
    /**
     * GET请求
     *
     * @param  array  $params [请求参数]
     * @return mixed
     */
    public static function get($params)
    {
        $params['method'] = 'GET';

        if (isset($params['data'])) {
            $params['url'] .= '?' . http_build_query($params['data']);
            unset($params['data']);
        }

        return self::send($params);
    }

    /**
     * POST请求
     *
     * @param  array  $params [请求参数]
     * @return mixed
     */
    public static function post($params)
    {
        $params['method'] = 'POST';
        if (!isset($params['header'])) {
            $params['header'] = [];
        }
        if (!isset($params['data_type'])) {
            $params['data_type'] = 'json';
        }

        // 判断内容类型
        switch ($params['data_type']) {
            case 'form':
                $params['header'][] = 'Content-Type: application/x-www-form-urlencoded';
                if (isset($params['data'])) {
                    $params['data'] = http_build_query($params['data']);
                }
                break;
            case 'xml':
                $params['header'][] = 'Content-Type: text/xml; charset=utf-8';
                $xml = '<xml>';
                foreach ($params['data'] as $key => $val) {
                    $xml .= "<$key>$val</$key>";
                }
                $xml .= '</xml>';
                $params['data'] = $xml;
                break;
            case 'json':
                $params['header'][] = 'Content-Type: application/json; charset=utf-8';
                $params['data'] = json_encode($params['data'], JSON_UNESCAPED_UNICODE);
                break;
            case 'raw':
            default:
                break;
        }

        return self::send($params);
    }

    /**
     * 发送请求
     *
     * @param  array $params [
     * method           [str] [必填] [请求的类型]
     * url              [str] [必填] [地址]
     * data             [str] [可选] [请求数据]
     * data_type        [str] [可选] [请求数据的类型]
     * response_type    [str] [可选] [响应的数据类型]
     * ]
     * @return mixed
     */
    public static function send($params)
    {
        // 初始化
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $params['method']);
        curl_setopt($ch, CURLOPT_URL, $params['url']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 可选参数
        if (isset($params['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header']);
        }
        if (isset($params['data'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['data']);
        }
        if (isset($params['sslcert'])) {
            curl_setopt($ch, CURLOPT_SSLCERT, $params['sslcert']);
            curl_setopt($ch, CURLOPT_SSLKEY, $params['sslkey']);
        }

        // 执行
        $response = curl_exec($ch);

        // 关闭
        curl_close($ch);

        // 是否设置响应的数据类型
        if (isset($params['response_type'])) {
            if ($params['response_type'] === 'json') {
                return json_decode($response, true);
            }

            if ($params['response_type'] === 'xml') {
                $response = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
                return json_decode(json_encode($response), true);
            }
        }

        return json_decode($response, true) ?: $response;
    }
}
