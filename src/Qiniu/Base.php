<?php

namespace Higreen\Api\Qiniu;

/**
 * 基类
 */
class Base extends AnotherClass
{
    // 应用秘钥
    private $access_key = '';
    private $secret_key = '';

    /**
     * @param array $init
     *  access_key [str] [必填] [AccessKey]
     *  secret_key [str] [必填] [SecretKey]
     */
    public function __construct($init)
    {
        if (empty($init['access_key'])) {
            throw new \Exception('I need the "access_key"');
        }
        if (empty($init['secret_key'])) {
            throw new \Exception('I need the "secret_key"');
        }

        $this->access_key = $init['access_key'];
        $this->secret_key = $init['secret_key'];
    }
}
