<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 15:23
 */

namespace Tusimo\Wechat\Component;


use EasyWeChat\Core\Http;

class ComponentHttp extends Http
{

    public function __construct($token = null)
    {
        $this->component_access_token = $token instanceof ComponentAccessToken ? $token->getToken() : $token;
    }

    public function json($url, $options = [], $encodeOption = JSON_UNESCAPED_UNICODE)
    {
        $url .= (stripos($url, '?') ? '&' : '?') . 'component_access_token=' . $this->component_access_token;
        return parent::parseJSON(parent::json($url, $options, $encodeOption));
    }
}