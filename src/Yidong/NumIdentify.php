<?php

namespace Higreen\Api\Yidong;

use Higreen\Api\Http;

/**
 * 号码认证
 * 文档:http://dev.10086.cn/docInside?contentId=10000067529678
 */
class NumIdentify
{
    /**
     * APPID
     * 
     * @var string
     */
    public $app_id;

    /**
     * APPKEY
     * 
     * @var string
     */
    public $app_key;

    /**
     * 平台公钥
     * 
     * @var string
     */
    public $public_key;

    /**
     * 应用私钥
     * 
     * @var string
     */
    public $private_key;

    /**
     * Create a new instance.
     * 
     * @param array $init [
     *  app_id      [str] [必填] [应用ID]
     *  public_key  [str] [必填] [平台公钥]
     *  private_key [str] [必填] [应用私钥.创建应用时,请使用同一对密钥]
     * ]
     * @return void
     */
    public function __construct($init)
    {
        if (empty($init['app_id'])) {
            throw new \Exception('I need the app_id');
        }
        if (empty($init['public_key'])) {
            throw new \Exception('I need the public_key');
        }
        if (empty($init['private_key'])) {
            throw new \Exception('I need the private_key');
        }

        $public_key = wordwrap($init['public_key'], 64, "\n", true);
        $public_key = "-----BEGIN RSA PUBLIC KEY-----\n{$public_key}\n-----END RSA PUBLIC KEY-----";

        $private_key = wordwrap($init['private_key'], 64, "\n", true);
        $private_key = "-----BEGIN RSA PRIVATE KEY-----\n{$private_key}\n-----END RSA PRIVATE KEY-----";

        $this->app_id = $init['app_id'];
        $this->public_key = $public_key;
        $this->private_key = $private_key;
    }

    /**
     * 获取手机号码
     * 
     * @param string $token 业务凭证
     * @return string 手机号
     */
    public function getPhoneNumber($token)
    {
        $data = [
            'version' => '2.0',
            'msgid' => strval(rand()),
            'systemtime' => date('YmdHis000'),
            'strictcheck' => '1',
            'appid' => $this->app_id,
            'token' => $token,
            'sign' => '',
            'encryptionaigorithm' => 'RSA',
        ];

        // 签名
        openssl_sign($this->app_id . $token, $signature, $this->private_key, OPENSSL_ALGO_SHA256);
        $data['sign'] = bin2hex($signature);

        // 发送请求
        $response = Http::post([
            'url' => 'https://www.cmpassport.com/unisdk/rsapi/loginTokenValidate',
            'data' => $data,
        ]);

        // 验证响应
        $code = $response['resultCode'] ?? '';
        if ($code === '10300') {
            // 解密手机号
            $data = hex2bin($response['msisdn']);
            openssl_private_decrypt($data, $decrypted_data, $this->private_key);
            return $decrypted_data;
        }

        $message = match ($code) {
            '103101' => '签名错误',
            '103113' => 'token格式错误',
            '103119' => 'appid不存在',
            '103133' => 'sourceid不合法（服务端需要使用调用SDK时使用的appid去换取号码）',
            '103211' => '其他错误',
            '103412' => '无效的请求',
            '103414' => '参数校验异常',
            '103511' => '请求ip不在社区配置的服务器白名单内',
            '103811' => 'token为空',
            '104201' => 'token失效或不存在',
            '105018' => '用户权限不足（使用了本机号码校验的token去调用本接口）',
            '105019' => '应用未授权（开发者社区未勾选能力）',
            '105312' => '套餐已用完',
            '105313' => '非法请求',
            default => '未知错误',
        };

        throw new \ErrorException($message, 555);
    }

    /**
     * 本机号码校验
     * 
     * @param string $token 业务凭证
     * @param string $open_type 运营商类型：1:移动;2:联通;3:电信;0:未知
     * @return bool
     */
    public function isDivecePhone($token, $open_type = '0')
    {
        return false;
    }
}
