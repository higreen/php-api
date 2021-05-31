<?php

namespace Higreen\Api\Weixin;

use Higreen\Api\Http;

/**
 * 微信普通支付(V3版)
 * 文档: https://pay.weixin.qq.com/wiki/doc/apiv3/wxpay/pages/transactions.shtml
 */
class Pay
{
    public $mch_id;
    public $mch_key;
    public $sslcert;
    public $sslkey;

    /**
     * @param array $init
     *  mch_id      [str] [必填] [商户ID]
     *  mch_key     [str] [必填] [商户密钥]
     *  sslcert     [str] [必填] [证书路径]
     *  sslkey      [str] [必填] [证书密钥路径]
     */
    public function __construct($init)
    {
        if (empty($init['mch_id']))
            throw new \Exception('I need the mch_id');
        if (empty($init['mch_key']))
            throw new \Exception('I need the mch_key');
        if (empty($init['sslcert']) || !file_exists($init['sslcert']))
            throw new \Exception('I need the sslcert');
        if (empty($init['sslkey']) || !file_exists($init['sslkey']))
            throw new \Exception('I need the sslkey');

        $this->mch_id     = $init['mch_id'];
        $this->mch_key    = $init['mch_key'];
        $this->sslcert    = $init['sslcert'];
        $this->sslkey     = $init['sslkey'];
    }

    /**
     * 统一下单
     *
     * @param string $trade_type  [交易类型：APP,H5,JSAPI,Native]
     * @param array $params
     *  appid        [str] [必填] [微信生成的应用ID]
     *  description  [str] [必填] [商品描述]
     *  notify_url   [str] [必填] [直接可访问的URL，不允许携带查询串，要求必须为https地址]
     *  out_trade_no [str] [必填] [商户系统内部订单号，只能是数字、大小写字母_-*且在同一个商户号下唯一]
     *  total        [int] [必填] [订单总金额，单位为分]
     *  attach       [str] [可选] [附加数据，在查询API和支付通知中原样返回]
     *  openid       [str] [可选] [trade_type=JSAPI，此参数必传，用户在直连商户appid下的唯一标识]
     *  type         [str] [可选] [trade_type=H5，此参数必传，场景类型 示例值：iOS, Android, Wap]
     * @return array [支付参数]
     */
    public function order($trade_type, $params)
    {
        // 请求参数
        $data = [
            'amount'       => ['total' => $params['total']],
            'appid'        => $params['appid'],
            'attach'       => '',
            'description'  => $params['description'],
            'mchid'        => $this->mch_id,
            'notify_url'   => $params['notify_url'],
            'out_trade_no' => $params['out_trade_no'],
        ];

        // 判断交易类型
        $trade_type = strtolower($trade_type);
        switch ($trade_type) {
            case 'app':
                break;
            case 'h5':// 移动端网页
                $data['scene_info'] = [
                    'payer_client_ip' => $_SERVER['REMOTE_ADDR'],
                    'h5_info' => ['type' => $params['type']],
                ];
                break;
            case 'jsapi':// 小程序或公众号
                $data['payer'] = ['openid' => $params['openid']];
                break;
            case 'native':
                break;
            default:
                throw new \Exception('未定义的交易类型', 555);
                break;
        }

        // 可选参数
        if (isset($params['attach'])) {
            $data['attach'] = strval($params['attach']);
        }

        // 发送请求
        $url = 'https://api.mch.weixin.qq.com/v3/pay/transactions/' . $trade_type;
        $auth = $this->_getAuthorization('post', $url, $data);
        $response = Http::post([
            'url'    => $url,
            'data'   => $data,
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: {$auth}",
                'User-Agent: green',
            ],
        ]);

        // 判断响应
        if (!empty($response['prepay_id'])) {
            return $this->_getPayment($trade_type, $response, $params['appid']);
        } else {
            throw new \Exception($response['message'], 555);
        }
    }

    /**
     * 查询订单支付结果
     *
     * @param  string $out_trade_no [商户系统内部订单号]
     * @return array
     */
    public function orderResult($out_trade_no)
    {
        // 发送请求
        $url = "https://api.mch.weixin.qq.com/v3/pay/transactions/out-trade-no/{$out_trade_no}?mchid={$this->mch_id}";
        $auth = $this->_getAuthorization('get', $url, '');
        $response = Http::get([
            'url'    => $url,
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: {$auth}",
                'User-Agent: green',
            ],
        ]);

        // 判断响应
        if (!empty($response['message'])) {
            throw new \Exception($response['message'], 555);
        } else {
            return $response;
        }
    }

    /**
     * 申请退款
     *
     * @param  array $params
     *  transaction_id  [str] [选择] [原支付交易对应的微信订单号,2选1]
     *  out_trade_no    [str] [选择] [原支付交易对应的商户订单号,2选1]
     *  out_refund_no   [str] [必填] [商户系统内部的退款单号，商户系统内部唯一，只能是数字、大小写字母_-|*@ ，同一退款单号多次请求只退一笔]
     *  refund          [int] [必填] [退款金额，币种的最小单位]
     *  total           [int] [必填] [原支付交易的订单总金额，币种的最小单位]
     *  reason          [str] [可选] [退款原因]
     *  notify_url      [str] [可选] [异步接收微信支付退款结果通知的回调地址]
     * @return array
     */
    public function refund($params)
    {
        // 请求参数
        $data = [
            'out_refund_no' => $params['out_refund_no'],
            'amount'        => [
                'refund'    => $params['refund'],
                'total'     => $params['total'],
                'currency'  => 'CNY',
            ],
        ];
        if (isset($params['transaction_id'])) {
            $data['transaction_id'] = $params['transaction_id'];
        } elseif (isset($params['out_trade_no'])) {
            $data['out_trade_no'] = $params['out_trade_no'];
        } else {
            throw new \Exception("I need 'transaction_id' or 'out_trade_no'.");
        }

        // 可选参数
        if (isset($params['reason'])) {
            $data['reason'] = $params['reason'];
        }
        if (isset($params['notify_url'])) {
            $data['notify_url'] = $params['notify_url'];
        }

        // 发送请求
        $url = 'https://api.mch.weixin.qq.com/v3/refund/domestic/refunds';
        $auth = $this->_getAuthorization('post', $url, $data);
        $response = Http::post([
            'url'    => $url,
            'data'   => $data,
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: {$auth}",
                'User-Agent: green',
            ],
        ]);

        // 判断响应
        if (!empty($response['code'])) {
            throw new \Exception($response['message'], 555);
        }

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | 静态方法
    |--------------------------------------------------------------------------
    */

    /**
     * 回调报文解密
     *
     * @param  string $mch_key  [商户号APIV3密钥]
     * @param  string $resource [回调的加密报文]
     * @return array
     */
    public static function decryptResource($mch_key, $resource)
    {
        $ciphertext = base64_decode($resource['ciphertext']);
        $ctext = substr($ciphertext, 0, -16);
        $authTag = substr($ciphertext, -16);

        $data = \openssl_decrypt(
            $ctext,
            'aes-256-gcm',
            $mch_key,
            \OPENSSL_RAW_DATA,
            $resource['nonce'],
            $authTag,
            $resource['associated_data']
        );

        return json_decode($data, true);
    }

    /*
    |--------------------------------------------------------------------------
    | 私有方法
    |--------------------------------------------------------------------------
    */

    /**
     * 获取请求头签名
     *
     * @param  string $method [请求方法]
     * @param  string $url    [请求链接]
     * @param  array  $data   [请求报文主体]
     * @return string
     */
    private function _getAuthorization($method, $url, $data)
    {
        $method = strtoupper($method);
        $url = str_replace('https://api.mch.weixin.qq.com', '', $url);
        if ($data) $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $timestamp = time();
        $nonce_str = rand();
        $serial_no = openssl_x509_parse(file_get_contents($this->sslcert));
        $serial_no = $serial_no['serialNumberHex'];
        $message = [$method, $url, $timestamp, $nonce_str, $data];
        $signature = $this->_getSignature($message);

        return sprintf('WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%d",timestamp="%d",serial_no="%s",signature="%s"',
            $this->mch_id, $nonce_str, $timestamp, $serial_no, $signature);
    }

    /**
     * 获取签名
     * @param  array  $params    [签名的数据]
     * @param  string $sign_type [签名方式:MD5,RSA]
     * @return string
     */
    private function _getSignature($params)
    {
        // 拼接签名参数
        $data = '';
        foreach ($params as $key => $value) {
            $data .= "{$value}\n";
        }

        openssl_sign($data, $signature, file_get_contents($this->sslkey), OPENSSL_ALGO_SHA256);
        $signature = base64_encode($signature);

        return $signature;
    }

    /**
     * 获取支付参数
     *
     * @param  string $trade_type   [交易类型]
     * @param  string $response     [响应数据]
     * @param  string $appid        [应用ID]
     * @return mixed
     */
    private function _getPayment($trade_type, $response, $appid = '')
    {
        // 判断交易类型
        switch ($trade_type) {
            case 'app':
                $data = [
                    'appid'     => $appid,
                    'timestamp' => strval(time()),
                    'noncestr'  => strval(rand()),
                    'prepayid'  => $response['prepay_id'],
                ];
                $data['paySign'] = $this->_getSignature($data);
                $data['package'] = 'Sign=WXPay';
                $data['partnerid'] = $this->mch_id;
                break;
            case 'h5':
                $data = $response['h5_url'];
                break;
            case 'jsapi':
                $data = [
                    'appId'     => $appid,
                    'timeStamp' => strval(time()),
                    'nonceStr'  => strval(rand()),
                    'package'   => 'prepay_id=' . $response['prepay_id'],
                ];
                $data['paySign'] = $this->_getSignature($data);
                $data['signType'] = 'RSA';
                unset($data['appId']);
                break;
            default:
                $data = [];
                break;
        }

        return $data;
    }
}
