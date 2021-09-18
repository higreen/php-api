<?php

namespace Higreen\Api\Alipay;

use Higreen\Api\Http;

/**
 * 支付
 */
class Pay extends Base
{
    /**
     * App支付
     *
     * @param array $params [
     *  subject          [str] [必填] [商品的标题/交易标题/订单标题/订单关键字等]
     *  out_trade_no     [str] [必填] [商户网站唯一订单号]
     *  total_amount     [str] [必填] [订单总金额，单位为元，精确到小数点后两位]
     *  time_expire      [str] [可选] [订单绝对超时时间。格式为yyyy-MM-dd HH:mm:ss。]
     *  product_code     [str] [可选] [销售产品码，商家和支付宝签约的产品码，为固定值 QUICK_MSECURITY_PAY]
     *  passback_params  [str] [可选] [异步通知时将该参数原样返回。本参数必须进行 UrlEncode]
     * ]
     * @return string 支付参数
     */
    public function app($params)
    {
        // 公共请求参数
        $data = $this->request_body;
        $data['method'] = 'alipay.trade.app.pay';

        // 接口请求参数
        $biz_content = [
            'subject' => $params['subject'],
            'out_trade_no' => $params['out_trade_no'],
            'total_amount' => $params['total_amount'],
        ];

        // 可选参数
        if (isset($params['passback_params'])) {
            $biz_content['passback_params'] = $params['passback_params'];
        }
        if (isset($params['product_code'])) {
            $biz_content['product_code'] = $params['product_code'];
        }
        if (isset($params['time_expire'])) {
            $biz_content['time_expire'] = $params['time_expire'];
        }

        // 获取签名
        $data['biz_content'] = json_encode($biz_content, JSON_UNESCAPED_UNICODE);
        $data['sign'] = $this->getSignature($data);

        return http_build_query($data);
    }

    /**
     * 小程序
     *
     * @param array $params [
     *  subject          [str] [必填] [商品的标题/交易标题/订单标题/订单关键字等]
     *  out_trade_no     [str] [必填] [商户网站唯一订单号]
     *  total_amount     [str] [必填] [订单总金额，单位为元，精确到小数点后两位]
     *  buyer_id         [str] [必填] [支付宝用户的唯一userId ]
     * ]
     * @return array [out_trade_no,trade_no]
     */
    public function mini($params)
    {
        // 公共请求参数
        $data = $this->request_body;
        $data['method'] = 'alipay.trade.create';

        // 接口请求参数
        $biz_content = [
            'subject' => $params['subject'],
            'out_trade_no' => $params['out_trade_no'],
            'total_amount' => $params['total_amount'],
            'buyer_id' => $params['buyer_id'],
        ];

        // 获取签名
        $data['biz_content'] = json_encode($biz_content);
        $data['sign'] = $this->getSignature($data);

        // 发送请求
        $response = Http::get([
            'url' => $this->url,
            'data' => $data,
            'response_type' => 'json',
        ]);
        $response = $response['alipay_trade_create_response'];
        if ($response['code'] !== '10000') {
            throw new \ErrorException($response['sub_msg'], 555);
        }

        return $response;
    }

    /**
     * 当面付，调用支付宝接口，生成二维码后，展示给用户，由用户扫描二维码完成订单支付。
     *
     * @param array $params [
     *  subject      [str] [必填] [商品标题/交易标题/订单标题/订单关键字等]
     *  out_trade_no [str] [必填] [商户订单号]
     *  total_amount [str] [必填] [订单总金额，单位为人民币（元）]
     * ]
     * @return string         [二维码内容]
     */
    public function qrcode($params)
    {
        // 公共请求参数
        $data = $this->request_body;
        $data['method'] = 'alipay.trade.precreate';

        // 接口请求参数
        $biz_content = [
            'subject' => $params['subject'],
            'out_trade_no' => $params['out_trade_no'],
            'total_amount' => $params['total_amount'],
        ];

        // 可选参数
        if (!empty($params['passback_params'])) {
            $biz_content['passback_params'] = $params['passback_params'];
        }

        // 获取签名
        $data['biz_content'] = json_encode($biz_content);
        $data['sign'] = $this->getSignature($data);

        // 发送请求
        $response = Http::post([
            'url' => $this->url,
            'data' => $data,
            'data_type' => 'form',
        ]);

        if (!empty($response['alipay_trade_precreate_response']['qr_code'])) {
            return $response['alipay_trade_precreate_response']['qr_code'];
        }

        // 抛出错误异常
		$response = mb_convert_encoding($response, 'UTF-8', 'GBK');
        $response = json_decode($response, true);
        throw new \ErrorException($response['alipay_trade_precreate_response']['sub_msg'], 555);
    }
}
