<?php

namespace Higreen\Api\Alipay;

/**
 * 转账到支付宝账户
 */
class Transfer extends A
{
    /**
     * @param array $params [
     *  out_biz_no   [str] [必填] [商户端的唯一订单号]
     *  trans_amount [str] [必填] [订单总金额，单位为元，精确到小数点后两位]
     *  identity     [str] [必填] [支付宝的会员ID|支付宝登录号]
     *  name         [str] [可选] [参与方真实姓名,当identity_type=ALIPAY_LOGON_ID时，本字段必填]
     *  product_code [str] [可选] [业务产品码，默认支付宝账户：STD_RED_PACKET(收发现金红包),TRANS_ACCOUNT_NO_PWD(单笔无密转账到支付宝账户),TRANS_BANKCARD_NO_PWD(单笔无密转账到银行卡)]
     *  order_title  [str] [可选] [转账业务的标题，用于在支付宝用户的账单里显示]
     *  remark       [str] [可选] [业务备注]
     * ]
     * @return bool
     */
    public function __invoke($params)
    {
        // 业务参数
        $data = [
            'out_biz_no' => $params['out_biz_no'],
            'trans_amount' => $params['trans_amount'],
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
            'biz_scene' => 'PERSONAL_COLLECTION',
            'order_title' => '',
            'payee_info' => [
                'identity' => $params['identity'],
                'identity_type' => 'ALIPAY_LOGON_ID',
                'name' => '',
            ],
            'remark' => '',
        ];

        // 可选参数
        if (is_numeric($params['identity'])) {
            $data['payee_info']['identity_type'] = 'ALIPAY_USER_ID';
        }
        if (isset($params['name'])) {
            $data['payee_info']['name'] = $params['name'];
        }
        if (isset($params['product_code'])) {
            $data['product_code'] = $params['product_code'];
        }
        if (isset($params['remark'])) {
            $data['remark'] = $params['remark'];
        }

        // 发送请求
        $response = $this->sendRequest('alipay.fund.trans.toaccount.transfer', $data);

        // 判断响应
        $response = $response['alipay_fund_trans_toaccount_transfer_response'];
        if ($response['code'] == '10000') {
            return $response;
        } else {
            throw new \ErrorException($response['sub_msg'], 555);
        }
    }
}
