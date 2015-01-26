## Simple curl wrapper for php >= 5.4

## Install composer

Add composer.json require `"orel/simplecurl": "dev-master"`

# Usage

```php

use simplecurl\SCurl;

$curl = new SCurl();
$response = $curl->get('http://example.com', ['q1' => 1, q2 => 2], [CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT']])->exec();

var_dump($response->headers);
var_dump($response->info);
var_dump($response);

echo $response;

$response SCurl::instance()
    ->post('http://example.com', ['field1' => 'val1'])
    ->setHeader('X-Requested-With', 'XMLHttpRequest')
    ->setHeader(['X-Requested-With:XMLHttpRequest', 'Referer:http://example.com'])
    ->setAjax()
    ->setUserAgent('Simple curl')
    ->setReferer('http://example.com')
    ->setCookieFile(__DIR__. 'cookie.txt')
    ->setCookie('key', 'val')
    ->setCookie(['key' => 'val'])
    ->setCookieString('key=val;key=val')
    ->setLocation(true)
    ->exec();

var_dump($response->headers);
var_dump($response->info);
echo $response;


if (isset($response->errno) AND isset($response->error)) {
    echo '['. $response->errno .']'. $response->error;
}

// Response in callback functions

$response = SCurl::instance()
    ->post('http://example.com', ['field1' => 'val1'])
    ->setHeader('X-Requested-With', 'XMLHttpRequest')
    ->setHeader(['X-Requested-With:XMLHttpRequest', 'Referer:http://example.com'])
    ->success(function ($response)) {
        return $response;
    })
    ->error(function ($errorobj) {
        echo '['. $errorobj->errno .']'. $errorobj->error;
    })
    ->exec();
```