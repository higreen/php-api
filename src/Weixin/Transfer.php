<?php

namespace Higreen\Api\Weixin;

use Higreen\Api\Http;

/**
 * 微信转账
 */
class Transfer
{
    public $mch_id;// 商户号
    public $mch_key;// 商户秘钥
    public $sslcert;// 证书路径
    public $sslkey;

    public function __construct($init)
    {
        $this->mch_id = $init['mch_id'];
        $this->mch_key = $init['mch_key'];
        $this->sslcert = $init['sslcert'];
        $this->sslkey = $init['sslkey'];
    }

    /**
     * 付款到零钱
     * 文档：https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
     *
     * @param  array  $params [
     * partner_trade_no [str] [必填] [商户订单号]
     * mch_appid        [str] [必填] [商户账号appid]
     * openid           [str] [必填] [用户openid]
     * amount           [int] [必填] [企业付款金额，单位为分]
     * desc             [str] [必填] [企业付款备注]
     * re_user_name     [str] [可选] [收款用户姓名]
     * ]
     * @return array
     */
    public function balance($params)
    {
        // 请求参数
        $data = [
            'mch_appid'         => $params['mch_appid'],
            'mchid'             => $this->mch_id,
            'device_info'       => '',
            'nonce_str'         => rand(),
            'sign'              => '',
            'partner_trade_no'  => $params['partner_trade_no'],
            'openid'            => $params['openid'],
            'check_name'        => 'NO_CHECK',
            're_user_name'      => '',
            'amount'            => $params['amount'],
            'desc'              => $params['desc'],
            'spbill_create_ip'  => $_SERVER['REMOTE_ADDR'],
        ];

        // 可选参数
        if (isset($params['re_user_name'])) {
            $data['check_name'] = 'FORCE_CHECK';
            $data['re_user_name'] = $params['re_user_name'];
        }

        // 获取签名
        $data['sign'] = $this->getSignature($data);

        // 发送请求
        $response = Http::post([
            'url' => 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers',
            'data' => $data,
            'data_type' => 'xml',
            'response_type' => 'xml',
            'sslcert' => $this->sslcert,
            'sslkey' => $this->sslkey,
        ]);

        // 判断响应
        if ($response['return_code'] === 'SUCCESS') {
            if ($response['result_code'] === 'SUCCESS') {
                return $response;
            } else {
                throw new \Exception($response['err_code_des']);
            }
        } else {
            throw new \Exception($response['return_msg']);
        }
    }

    // 获取签名
    public function getSignature($params)
    {
        $params = array_filter($params);
        ksort($params);

        // 拼接签名参数
        $signature = '';
        foreach ($params as $key => $val) {
            $signature .= "{$key}={$val}&";
        }
        $signature .= "key={$this->mch_key}";

        $signature = strtoupper(md5($signature));

        return $signature;
    }
}
