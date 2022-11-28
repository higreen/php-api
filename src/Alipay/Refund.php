<?php

namespace Higreen\Api\Alipay;

/**
 * 退款
 */
class Refund extends A
{
    /**
     * @param  array $params [
     *  out_trade_no   【string】【必填】【商户订单号】
     *  refund_amount  【float】【必填】【退款金额。单位为元，支持两位小数】
     *  refund_reason  【string|256】【可选】【退款原因说明】
     *  out_request_no 【string|64】【可选】【退款请求号。标识一次退款请求，需要保证在交易号下唯一，如需部分退款，则此参数必传】
     * ]
     * @return array
     */
    public function __invoke($params)
    {
        # 业务参数
        $data = [
            'out_trade_no' => $params['out_trade_no'],
            'refund_amount' => $params['refund_amount'],
        ];

        # 可选参数
        if (isset($params['refund_reason'])) {
            $data['refund_reason'] = $params['refund_reason'];
        }
        if (isset($params['out_request_no'])) {
            $data['out_request_no'] = $params['out_request_no'];
        }

        return $this->sendRequest('alipay.trade.refund', $data);
    }

    /**
     * 查询交易退款状态
     * 
     * @param array $params [
     *  out_trade_no   【string】【必填】【商户订单号】
     *  out_request_no 【string】【可选】【退款请求号】
     *  query_options  【string[]】【可选】【查询选项】
     * ]
     * @return array
     */
    public function query($params)
    {
        # 业务参数
        $data = [
            'out_trade_no' => $params['out_trade_no'],
            'out_request_no' => '',
        ];

        # 可选参数
        if (empty($data['out_request_no'])) {
            $data['out_request_no'] = $params['out_trade_no'];
        }
        if (isset($params['query_options'])) {
            $data['query_options'] = $params['query_options'];
        }

        return $this->sendRequest('alipay.trade.fastpay.refund.query', $data);
    }
}
