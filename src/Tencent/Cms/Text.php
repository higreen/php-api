<?php

namespace Higreen\Api\Tencent\Cms;

use Higreen\Api\Http;

/**
 * 文本内容安全
 * 文档: https://cloud.tencent.com/document/api/1124/51869
 */
class Text
{
    private $secret_id;
    private $secret_key;

    /**
     * Create a new instance.
     * 
     * @param array $init [
     *  secret_id   [str] [必填] [密钥ID]
     *  secret_key  [str] [必填] [密钥KEY]
     * ]
     * @return void
     */
    public function __construct($init)
    {
        $this->secret_id = $init['secret_id'];
        $this->secret_key = $init['secret_key'];
    }

    public function check($content)
    {
        $url = 'https://tms.tencentcloudapi.com';

        $data = [
            'Content' => base64_encode($content),
        ];

        $authorization = $this->_getAuthorization('POST', $url, $data);

        // 发送请求
        $time = time();
        $response = Http::post([
            'url' => $url,
            'data' => $data,
            'header' => [
                "Authorization: {$authorization}",
                'X-TC-Action: TextModeration',
                'X-TC-Region: ap-guangzhou',
                "X-TC-Timestamp: {$time}",
                'X-TC-Version: 2020-12-29',
            ],
        ]);

        if (!empty($response['Response']['Error']['Message'])) {
            throw new \ErrorException($response['Response']['Error']['Message'], 555);
        }

        return $response['Response']['Suggestion'] === 'Pass';
    }

    private function _getAuthorization($method, $url, $data)
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
        $HashedRequestPayload = hash('SHA256', json_encode($data));
        $CanonicalRequest = "{$HTTPRequestMethod}\n{$CanonicalURI}\n{$CanonicalQueryString}\n{$CanonicalHeaders}\n{$SignedHeaders}\n{$HashedRequestPayload}";

        // step 2: build string to sign
        $Algorithm = 'TC3-HMAC-SHA256';
        $CredentialScope = "{$date}/{$service}/tc3_request";
        $HashedCanonicalRequest = hash('SHA256', $CanonicalRequest);
        $StringToSign ="{$Algorithm}\n{$time}\n{$CredentialScope}\n{$HashedCanonicalRequest}";

        // step 3: sign string
        $secretDate = hash_hmac("SHA256", $date, "TC3{$this->secret_key}", true);
        $secretService = hash_hmac("SHA256", $service, $secretDate, true);
        $secretSigning = hash_hmac("SHA256", "tc3_request", $secretService, true);
        $signature = hash_hmac("SHA256", $StringToSign, $secretSigning);

        // step 4: build authorization
        $authorization = "{$Algorithm} Credential={$this->secret_id}/{$CredentialScope}, SignedHeaders=content-type;host, Signature={$signature}";

        return $authorization;
    }
}