<?php

namespace Vochina\HeepayCustomer\Tests;

use Mockery\Matcher\AnyArgs;
use PHPUnit\Framework\TestCase;
use Vochina\HeepayCustomer\Exceptions\HttpException;
use Vochina\HeepayCustomer\Exceptions\InvalidArgumentException;
use Vochina\HeepayCustomer\HeepayCustomer;

class HeepayCustomerTest extends TestCase
{
    public function testSign()
    {
        $sm2PrivateKeyPath = __DIR__ . '/test_sm2_private_key.pem';
        $sm2publicKeyPath = __DIR__ . '/test_sm2_public_key.pem';
        $handler = new HeepayCustomer($sm2PrivateKeyPath, $sm2publicKeyPath);
        $result = $handler->sign('This is the string that needs to be signed!');
        echo $result;
        $this->assertEquals(88, strlen($result));
    }

    public function testVerifySign()
    {
        $sm2PrivateKeyPath = __DIR__ . '/test_sm2_private_key.pem';
        $sm2publicKeyPath = __DIR__ . '/test_sm2_public_key.pem';
        $handler = new HeepayCustomer($sm2PrivateKeyPath, $sm2publicKeyPath);
        $sign_content = 'This is the string that needs to be signed!';
        $sign = $handler->sign($sign_content);
        $verify_result = $handler->verifySign($sign_content, $sign);
        $this->assertTrue($verify_result);
    }

    public function testParameterText()
    {
        $data = [
            "app_id" => "100000000000000",
            "method" => "customer.entry.apply",
            "version" => "1.0",
            "charset" => "utf-8",
            "format" => "json",
            "timestamp" => date('Y-m-d H:i:s'),
            "notify_url" => "https://www.google.com",
            "sign_type" => "SM2",
            "biz_content" => [
                "apply_no" => "1233231212212",
            ],
        ];
        $sm2PrivateKeyPath = __DIR__ . '/test_sm2_private_key.pem';
        $sm2publicKeyPath = __DIR__ . '/test_sm2_public_key.pem';
        $handler = new HeepayCustomer($sm2PrivateKeyPath, $sm2publicKeyPath);
        $sign_text = $handler->parameterText($data, '&', true, null);
        var_dump($sign_text);
        $this->assertIsString($sign_text);
    }
}
