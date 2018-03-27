# [微信第三方开放平台SDK]
#注意：本项目已废弃，请直接使用。[http://easywechat.org/](http://easywechat.org/)

本SDK基于 Easy WeChat开发

- 实现了授权流程
- 实现了授权后代公众号调用流程
- 由于授权模式和直接开发模式只有获取的`access_token`不一样，其他都是一样的。所以直接替换掉Easy WeChat的token即可使用

>最重要的一点：由于直接使用的Easy WeChat开发 而Easy WeChat将AccessToken限制类型了。所以不能传入AuthorizerAccessToken类的实例，
>所以要么重写大部分的代码，要么取巧 删除这个类型限制。
>vendor\overtrue\wechat\src\Core\AbstractAPI 第60行114行的类型限制AccessToken就可以了
>原谅我使用这种方式。因为实在是没有找到更好更快的方法
>有什么问题邮件：ucc862@gmail.com


## 安装

```shell
composer require "tusimo/wechat-for-component"
```

## 使用

新增使用：
### 公众号绑定
-跳转授权页面
```php
<?php
use Tusimo\Wechat\Component\Application;
$app = new Application($options);
$component =  $app->component;
$redirectUrl = '授权回调地址';//这个时候可以传递一个参数（举例company_id=1） 用来绑定当前授权的authoizer_app_id
$authPageUrl = $component->loginPage($redirectUrl);//返回一个同步授权的回调url地址
header('Location:' .$authPageUrl);//跳转到授权页面，这个时候用户可以扫描二维码进行授权
```
-授权同步回调页面
```php
<?php
use Tusimo\Wechat\Component\Application;
$app = new Application($options);
$component =  $app->component;
$component->setAuthCallback(function($authInfo){
    //此处会返回该公众号的相关信息，请根据自己的业务逻辑保存起来,将company_id=1与这个公众号绑定保存起来
});
```
-异步授权回调
```php
<?php
use Tusimo\Wechat\Component\Application;
$app = new Application($options);
$authServer = $app->auth_server;
    $authServer->setMessageHandler(function($message){
        //处理默认的事件
        switch ($message->InfoType) {
            case 'unauthorized'://取消授权事件,由于是授权模式的 公众号可能会被取消授权，这个时候相关的异步会回调到这里，请自行解除绑定
                //TODO放自己的业务逻辑
                break;
            case 'authorized' : //授权事件，授权事件也会异步发送通知到这里
                //TODO放自己的业务逻辑
            case 'updateauthorized'://更新授权事件 可以和授权事件采用相同的处理方式
                //TODO 放自己的业务逻辑
                break;
        }
    });
    $response = $authServer->serve();
    $response->send();
    exit();
```
基本使用:

```php
<?php

use Tusimo\Wechat\Component\Application;

$options = [
    'debug'     => true,
    'app_id'    => '', // 当前操作的APPID,由于是代授权模式每次必须指定使用的APPID
    'component_app_id'      => '',//新增以下四个开放平台参数
    'component_app_secret'  => '',
    'component_token'       => '',
    'component_aes_key'     => '',

    'log' => [
        'level' => 'debug',
        'file'  => '/tmp/easywechat.log',
    ],
    // ...
];

$app = new Application($options);

$app->setAuthorizerAppId('app_id');//代码设定选择要使用的公众号

//以下使用没有区别，和Easy WeChat一样的

$server = $app->server;
$user = $app->user;



$server->setMessageHandler(function($message) use ($user) {
    $fromUser = $user->get($message->FromUserName);

    return "{$fromUser->nickname} 您好！欢迎关注 overtrue!";
});

$server->serve()->send();
```

更多请参考[http://easywechat.org/](http://easywechat.org/)。
