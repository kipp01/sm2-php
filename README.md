
<h1 align="center">Heepay Customer</h1>

<p align="center">汇付宝支付商户入驻资料国密SM2签名和验签PHP扩展</p>

## 安装

```sh
$ composer require vochina/heepay-customer -vvv
```

## 配置

在使用本扩展之前，请自己生成国密SM2私钥和公钥，可以使用[支付宝开放平台开发助手](https://opendocs.alipay.com/open/02kipl?pathHash=c5b1c31d)一键生成，生成的格式Tests文件夹有示例

## 使用

```php
use Vochina\HeepayCustomer\HeepayCustomer;

$sm2PrivateKeyPath = __DIR__ . '/test_sm2_private_key.pem';
$sm2publicKeyPath = __DIR__ . '/test_sm2_public_key.pem';
$handler = new HeepayCustomer($sm2PrivateKeyPath, $sm2publicKeyPath);
```

###  签名方法 (注意：汇付宝要求签名前把请求参数格式化，请调用parameterText方法格式化后再签名)

```php
$result = $handler->sign('This is the string that needs to be signed!');
```
示例：

```
签名结果： nUjieZmUOhuEc6+rjy5EqetXRSlM9Nk3nZgv1Dz6P6CLrEt72TsOSLrWT/HIvFwBmsxgc5tHydUyEXXqeSobjA==
```

### 验证签名

```php
$verify_result = $handler->verifySign($sign_content, $sign);
```
示例：

```
验证签名结果： bool(true)
```

### 请求参数格式化

```php
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
```

### 在 Laravel 中使用

在 Laravel 中使用也是同样的安装方式，配置写在 `config/services.php` 中：

```php
    .
    .
    .
     'heepay-customer' => [
        'sm2PrivateKeyPath' => storage_path('cert/sm2_private_key.pem'),
        'sm2publicKeyPath' => storage_path('cert/sm2_public_key.pem'),
    ],
```

然后把key文件复制到相应的文件夹下

可以用两种方式来获取 `Vochina\HeepayCustomer\HeepayCustomer` 实例：

#### 方法参数注入

```php
    .
    .
    .
    public function edit(HeepayCustomer $HeepayCustomer)
    {
        $response = $HeepayCustomer->sign('This is the string that needs to be signed!');
    }
    .
    .
    .
```

#### 服务名访问

```php
    .
    .
    .
    public function edit()
    {
        $response = app('heepay-customer')->sign('This is the string that needs to be signed!');
    }
    .
    .
    .

```
### 测试

```bash
./vendor/bin/phpunit --filter verifySign
```

## License

MIT