<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 15:23
 */

namespace Tusimo\Wechat\Component;


class ComponentHttp extends HttpClient
{

    public function __construct($token = null)
    {
        $this->component_access_token = $token instanceof ComponentAccessToken ? $token->getToken() : $token;
        parent::__construct();
    }
}