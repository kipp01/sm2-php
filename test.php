<?php

include "./vendor/autoload.php";

use Vochina\HeepayCustomer\HeepayCustomer;

$sm2PrivateKeyPath = __DIR__ . '/tests/test_sm2_private_key.pem';
$sm2publicKeyPath = __DIR__ . '/tests/test_sm2_public_key.pem';
$handler = new HeepayCustomer($sm2PrivateKeyPath, $sm2publicKeyPath);
$result = $handler->sign(
    mb_convert_encoding('appId=JST_18JBXZWBH5Q0W&bizContent={"bizUserId":"10039393","memberName":"测试3","mobileNumber":"13229784981","roleNo":"1080","userNickname":"测试3"}&charset=utf-8&format=json&method=jst.kernel.BizPayMerchantService.memberOpening&signType=SM2&timestamp=20230629104554251&version=11', 'UTF-8', 'auto'),
    'JST_18JBXZWBH5Q0W'
);
echo $result;
