<?php

namespace Higreen\Api;

/**
 * 快递100
 * 文档:https://www.kuaidi100.com/openapi/
 */
class Kuaidi100
{
    /**
     * 公司编号
     *
     * @var string
     */ 
    private $customer;

    /**
     * 密钥
     *
     * @var string
     */
    private $key;

    /**
     * Create a new instance.
     * 
     * @param  string customer 公司编号
     * @param  string key 密钥
     * @return void
     */
    public function __construct(string $customer, string $key)
    {
        $this->customer = $customer;
        $this->key = $key;
    }

    /**
     * 实时查询
     * 
     * @param  array $params
     * @return array
     */
    public function query(array $params): array
    {
        // 构建请求数据
        $data = [
            'customer' => $this->customer,
            'sign' => '',
            'param' => '',
        ];
        $data['param'] = json_encode($params, JSON_UNESCAPED_UNICODE);
        $data['sign'] = strtoupper(md5($data['param'] . $this->key . $data['customer']));

        // 发送请求
        $response = Http::post([
            'url' => 'https://poll.kuaidi100.com/poll/query.do',
            'data' => $data,
            'data_type' => 'form',
        ]);

        return $response;
    }

    /**
     * 订阅推送
     * 
     * @param  array $config [
     *  company     [str] [必填] [订阅的快递公司的编码]
     *  number      [str] [必填] [订阅的快递单号]
     *  callbackurl [str] [必填] [回调接口的地址]
     * ]
     * @return array
     */
    public function subscribe(array $config): array
    {
        // 检测请求参数
        if (empty($config['company']) || !is_string($config['company'])) {
            throw new \Exception('Illegal "company"');
        }
        if (empty($config['number']) || !is_string($config['number'])) {
            throw new \Exception('Illegal "number"');
        }
        if (empty($config['callbackurl']) || !is_string($config['callbackurl'])) {
            throw new \Exception('Illegal "callbackurl"');
        }

        // 构建请求数据
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

        // 发送请求
        $response = Http::post([
            'url' => 'https://poll.kuaidi100.com/poll',
            'data' => $data,
            'data_type' => 'form',
        ]);

        return $response;
    }

}
