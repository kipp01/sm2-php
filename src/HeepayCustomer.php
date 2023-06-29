<?php

namespace Vochina\HeepayCustomer;

use Rtgm\sm\RtSm2;
use Rtgm\util\MyAsn1;

class HeepayCustomer
{
    //国密SM2私钥路径
    protected string $sm2PrivateKeyPath;
    //国密SM2公钥路径
    protected string $sm2publicKeyPath;

    public function __construct($sm2PrivateKeyPath, $sm2publicKeyPath)
    {
        $this->sm2PrivateKeyPath = $sm2PrivateKeyPath;
        $this->sm2publicKeyPath = $sm2publicKeyPath;
    }

    public function sign($content, $cerId = '1234567812345678'): string
    {
        $sm2 = new RtSm2('base64', true);
        $key_string = file_get_contents($this->sm2PrivateKeyPath);
        $key = MyAsn1::decode($key_string, 'base64');
        $key = MyAsn1::decode($key[2], 'hex');
        $sign = $sm2->doSign($content, $key[1], $cerId);
        return self::asn1_to_rs($sign);
    }

    public function verifySign($content, $sign, $cerId = '1234567812345678'): bool
    {
        $sm2 = new RtSm2('base64', true);
        $sign = self::rs_to_asn1($sign);
        $pem = MyAsn1::decode(file_get_contents($this->sm2publicKeyPath), 'base64');
        return $sm2->verifySign($content, $sign, $pem[1], $cerId);
    }

    public function parameterText($map, $separator, $ignoreNullValue, $ignoreKey)
    {
        if ($map == null) {
            return "";
        }

        $bizContent = $map['biz_content'] ?? '';
        if (!empty($bizContent)) {
            if (is_array($bizContent)) {
                $jsonStr = json_encode($bizContent);
                $jsonObject = json_decode($jsonStr);
                $map["biz_content"] = $jsonObject;
            } else {
                $bizContent = json_decode(json_encode($bizContent));
                $map["biz_content"] = $bizContent;
            }
        }

        $sb = "";
        $ignoreKeyArr = null;
        if (!empty($ignoreKey)) {
            $ignoreKeyArr = $ignoreKey;
            sort($ignoreKeyArr);
        }

        // 未排序须处理
        $keys = array_keys($map);
        sort($keys);
        foreach ($keys as $k) {
            $valueStr = "";
            $o = $map[$k];

            // 要忽略的key，不管是否空值 都不追加到待签名串
            if (!empty($ignoreKey) && in_array($k, $ignoreKeyArr)) {
                continue;
            }

            // 忽略空值
            if ($ignoreNullValue && (empty($o) || $o == "")) {
                continue;
            }

            // 没有要忽略的key，也不忽略空值
            // 如果值为null
            if (empty($o)) {
                $sb .= $k . "=" . $o . $separator;
            } else {
                if (is_object($o)) {
                    $o = json_decode(json_encode($o), true);
                }
                if (is_array($o)) {
                    $valueStr .= json_encode($o, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } else {
                    $valueStr = $o;
                }
                if (gettype($valueStr) == "string" && strlen($valueStr) > 0) {
                    $sb .= $k . "=" . $valueStr . $separator;
                }
            }
        }

        if (strlen($sb) > 0) {
            $sb = substr($sb, 0, strlen($sb) - 1);
        }

        return $sb;
    }

    public static function rs_to_asn1($str, $format = 'base64')
    {
        if ($format == 'base64') {
            $str = base64_decode($str);
        } else if ($format == 'hex') {
            $str = hex2bin($str);
        }

        $binR = self::_trim_int_pad(substr($str, 0, 32));
        $binS = self::_trim_int_pad(substr($str, 32));
        $lenR = strlen($binR);
        $lenS = strlen($binS);
        $result = chr(48) . chr(2 + $lenR + 2 + $lenS) . chr(2) . chr($lenR) . $binR . chr(2) . chr($lenS) . $binS;
        return base64_encode($result);
    }

    protected static function _trim_int_pad($binStr)
    {
        //trim 0
        while (ord($binStr[0]) == 0) {
            $binStr = substr($binStr, 1);
        }
        // add 0 if necessary
        if (ord($binStr[0]) > 127) {
            $binStr = chr(0) . $binStr;
        }
        return $binStr;
    }

    public static function asn1_to_rs($str, $format = 'base64')
    {
        if ($format == 'base64') {
            $str = base64_decode($str);
        } else if ($format == 'hex') {
            $str = hex2bin($str);
        }
        $arr = MyAsn1::decode($str);
        $r = self::_padding_zero($arr[0]);
        $s = self::_padding_zero($arr[1]);
        return base64_encode($r . $s);
    }

    protected static function _padding_zero(string $hex): string
    {
        $len = 64; // r,s都是32字节
        $left = $len - strlen($hex);
        if ($left > 0) {
            $hex = str_repeat('0', $left) . $hex;
        }
        return hex2bin($hex);
    }
}
