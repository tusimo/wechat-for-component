<?php

/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 14:27
 */
use Tusimo\Wechat\Component\Application;

require_once '../vendor/autoload.php';


$options = [
    /**
     * Debug 模式，bool 值：true/false
     *
     * 当值为 false 时，所有的日志都不会记录
     */
    'debug'  => true,
    /**
     * 账号基本信息，请从微信公众平台/开放平台获取
     */
    'app_id'  => '',         // AppID
    'secret'  => '',     // AppSecret
    'token'   => '',          // Token
    'aes_key' => '',                    // EncodingAESKey，安全模式下请一定要填写！！！

    /**
     * component 配置
     */

    'component_app_id'          => 'wx0f83d62624dccf9d',
    'component_app_secret'      => 'e36338e3902d558d7a9b5a2db1c4a578',
    'component_token'           => '527fbc96d9343d42',
    'component_aes_key'         => 'c2dd349vbde4b5dr95d8c6ehbe344c22414ede1df6e',

];

$app = new Application($options);
// 从项目实例中得到服务端应用实例。
$server = $app->auth_server;
$response = $server->serve();
$response->send(); // Laravel 里请使用：return $response;
