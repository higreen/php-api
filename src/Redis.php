<?php

namespace Higreen\Api;

/**
 * Redis客户端
 */
class Redis
{
    private static $instance;

    // 构造函数私有，不允许在外部实例化
    private function __construct() {}

    // 防止对象实例被克隆
    private function __clone() {}

    // 防止被反序列化
    private function __wakeup() {}

    /**
     * 获取Redis实例
     *
     * @return object
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new \Redis();
            static::$instance->connect('127.0.0.1', 6379);
        }

        return static::$instance;
    }
}
