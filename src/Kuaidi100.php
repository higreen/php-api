<?php

namespace Higreen\Api;

use Higreen\Api\Http;

/**
 * 快递100
 * 文档地址：https://www.kuaidi100.com/openapi/
 */
class Kuaidi100
{

    // 公司编号
    private $customer = '';
    // 密钥
    private $key = '';

    /**
     * 构造函数
     * @param array $init [
     * customer [str] [必填] []
     * key      [str] [必填] []
     * ]
     * @return void
     */
    public function __construct(array $init)
    {
        if (empty($init['customer']) || !is_string($init['customer'])) {
            throw new \Exception('Illegal "customer"');
        }
        if (empty($init['key']) || !is_string($init['key'])) {
            throw new \Exception('Illegal "key"');
        }

        $this->customer = $init['customer'];
        $this->key = $init['key'];
    }

    /**
     * 实时查询API
     * @author Green
     * @return [type] [description]
     */
    public function query(array $config)
    {
        $data = [
            'customer' => $this->customer,
            'sign' => '',
            'param' => '',
        ];
        $data['param'] = json_encode($config);
        $data['sign'] = strtoupper(md5($data['param'] . $this->key . $data['customer']));

        // 请求第三方服务
        $response = Http::post([
            'url' => 'https://poll.kuaidi100.com/poll/query.do',
            'data' => $data,
            'data_type' => 'form',
        ]);

        return $response;
    }

    /**
     * 订阅推送API
     * @author Green
     * @param  array $config [
     *  company     [str] [必填] [订阅的快递公司的编码]
     *  number      [str] [必填] [订阅的快递单号]
     *  callbackurl [str] [必填] [回调接口的地址]
     * ]
     * @return [type] [description]
     */
    public function subscribe(array $config)
    {
        // 检测请求参数
        if (empty($config['company']) || !is_string($config['company'])) {
            throw new Exception('Illegal "company"');
        }
        if (empty($config['number']) || !is_string($config['number'])) {
            throw new Exception('Illegal "number"');
        }
        if (empty($config['callbackurl']) || !is_string($config['callbackurl'])) {
            throw new Exception('Illegal "callbackurl"');
        }

        // 构建请求参数
        $data = [
            'schema' => 'json',
            'param' => [
                'company' => $config['company'],
                'number' => $config['number'],
                'key' => $this->key,
                'parameters' => [
                    'callbackurl' => $config['callbackurl'],
                ],
            ],
        ];
        $data['param'] = json_encode($data['param']);

        // 请求第三方服务
        $response = Http::post([
            'url' => 'https://poll.kuaidi100.com/poll',
            'data' => $data,
            'data_type' => 'form',
        ]);

        return $response;
    }

}
