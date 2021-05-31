<?php

namespace Higreen\Api;

/**
 * 数据校验
 */
class Check
{
	/**
     * 正整数

     * @param  mixed  $arr [数组|待检查的数据]
     * @param  string $key [键]
     * @return bool
     */
    public static function positiveInteger($arr, $key = '')
    {
        $value = $key ? ($arr[$key] ?? '') : $arr;

        return is_int($value) && $value > 0;
    }
	
    /**
     * 检测——字符串
     *
     * @param  [arr] $arr  [数组]
     * @param  [str] $key  [键名]
     * @param  [int] $min  [最小字符，默认0，可以为空]
     * @param  [int] $max  [最大字符，默认-1，无限制]
     * @return void
     */
    public static function str(array $arr, string $key, $min = 0, $max = null)
    {
        if (!isset($arr[$key])) {
            throw new \Exception("{$key} is required");
        }

        if (!is_string($arr[$key])) {
            throw new \Exception("{$key} is not a string");
        }

        if (mb_strlen($arr[$key]) < $min) {
            throw new \Exception("{$key} cannot be less than {$min} characters");
        }

        if ($max && mb_strlen($arr[$key]) > $max) {
            throw new \Exception("{$key} cannot be more than {$max} characters");
        }

    }

    /**
     * 检测——数字
     *
     * @param  [arr] $arr  [数组]
     * @param  [str] $key  [键名]
     * @param  [int] $min  [最小值，默认null，无限制]
     * @param  [int] $max  [最大值，默认null，无限制]
     * @return void
     */
    public static function num(array $arr, string $key, $min = null, $max = null)
    {
        if (!isset($arr[$key])) {
            throw new \Exception("{$key} is required");
        }

        if (!is_numeric($arr[$key])) {
            throw new \Exception("{$key} is not a number");
        }

        if (!is_null($min) && $arr[$key] < $min) {
            throw new \Exception("{$key} cannot be less than {$min}");
        }

        if (!is_null($max) && $arr[$key] > $max) {
            throw new \Exception("{$key} cannot be more than {$max}");
        }

        return true;
    }

    /**
     * 检测手机号
     *
     * @return bool
     */
    public static function phone(array $arr, string $key)
    {
        if (!isset($arr[$key])) {
            return false;
        }

        if (!preg_match('/^1\d{10}$/', $arr[$key])) {
            return false;
        }

        return true;
    }

    /**
     * 银行卡
     *
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public static function bankCard($number)
    {
        $arr_no = str_split($number);

        $last_n = $arr_no[count($arr_no) - 1];

        krsort($arr_no);

        $i = 1;

        $total = 0;

        foreach ($arr_no as $n) {
            if ($i % 2 == 0) {
                $ix = $n * 2;
                if ($ix >= 10) {
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                } else {
                    $total += $ix;
                }
            } else {
                $total += $n;
            }

            $i++;
        }

        $total -= $last_n;

        $total *= 9;

        if ($last_n == ($total % 10)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 身份证
     *
     * @param  [type] $number [description]
     */
    public static function IDcard($number)
    {
        if (strlen($number) == 15) {
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (array_search(substr($number, 12, 3), array('996', '997', '998', '999')) !== false) {
                $idcard = substr($number, 0, 6) . '18' . substr($number, 6, 9);
            } else {
                $idcard = substr($number, 0, 6) . '19' . substr($number, 6, 9);
            }

            $number = $idcard . idcardVerifyNumber($idcard);
        }

        if (!preg_match('/^\d{17}[0-9xX]$/', $number)) //基本格式校验
        {
            return false;
        }

        $parsed = date_parse(substr($number, 6, 8));

        if (!(isset($parsed['warning_count']) && $parsed['warning_count'] == 0)) //年月日位校验
        {
            return false;
        }

        $base = substr($number, 0, 17);

        $token = idcardVerifyNumber($base);

        $lastChar = strtoupper(substr($number, 17, 1));

        return ($lastChar === $token); //最后一位校验位校验
    }

    // 计算身份证校验码，根据国家标准GB 11643-1999
    public function idcardVerifyNumber($idcard_base)
    {
        if (strlen($idcard_base) != 17) {
            return false;
        }

        $base = substr($idcard_base, 0, 17);

        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        $tokens = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        $checkSum = 0;

        for ($i = 0; $i < 17; $i++) {
            $checkSum += intval(substr($base, $i, 1)) * $factor[$i];
        }

        $mod = $checkSum % 11;

        $token = $tokens[$mod];

        return $token;
    }
}
