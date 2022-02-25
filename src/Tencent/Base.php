<?php

namespace Higreen\Api\Tencent;

/*
 * 基类
 */
class Base
{
    // 密钥ID
    private $id;

    // 密钥KEY
    private $key;

    /**
     * Create a new instance.
     * 
     * @param array $init [
     *  id   [str] [必填] [密钥ID]
     *  key  [str] [必填] [密钥KEY]
     * ]
     * @return void
     */
    public function __construct($init)
    {
        if (empty($init['id'])) {
            throw new \Exception('I need the id');
        }
        if (empty($init['key'])) {
            throw new \Exception('I need the key');
        }

        $this->id = $init['id'];
        $this->key = $init['key'];
    }

    /** 
     * HTTP 标准身份认证头部字段
     * 
     * @param string $method 请求方法
     * @param string $url 请求链接
     * @param array $data 请求数据
     * @return string
     */
    protected function getAuthorization($method, $url, $data)
    {
        $time = time();
        $date = gmdate('Y-m-d', $time);
        $host = str_replace('https://', '', $url);
        $service = substr($host, 0, strpos($host, '.'));

        // step 1: build canonical request string
        $HTTPRequestMethod = $method;
        $CanonicalURI = '/';
        $CanonicalQueryString = '';
        $CanonicalHeaders = "content-type:application/json; charset=utf-8\nhost:{$host}\n";
        $SignedHeaders = "content-type;host";
        $HashedRequestPayload = hash('SHA256', json_encode($data, JSON_UNESCAPED_UNICODE));
        $CanonicalRequest = "{$HTTPRequestMethod}\n{$CanonicalURI}\n{$CanonicalQueryString}\n{$CanonicalHeaders}\n{$SignedHeaders}\n{$HashedRequestPayload}";

        // step 2: build string to sign
        $Algorithm = 'TC3-HMAC-SHA256';
        $CredentialScope = "{$date}/{$service}/tc3_request";
        $HashedCanonicalRequest = hash('SHA256', $CanonicalRequest);
        $StringToSign ="{$Algorithm}\n{$time}\n{$CredentialScope}\n{$HashedCanonicalRequest}";

        // step 3: sign string
        $secretDate = hash_hmac("SHA256", $date, "TC3{$this->key}", true);
        $secretService = hash_hmac("SHA256", $service, $secretDate, true);
        $secretSigning = hash_hmac("SHA256", "tc3_request", $secretService, true);
        $signature = hash_hmac("SHA256", $StringToSign, $secretSigning);

        // step 4: build authorization
        $authorization = "{$Algorithm} Credential={$this->id}/{$CredentialScope}, SignedHeaders=content-type;host, Signature={$signature}";

        return $authorization;
    }
}
