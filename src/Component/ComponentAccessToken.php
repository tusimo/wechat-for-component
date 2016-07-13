<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 14:59
 */

namespace Tusimo\Wechat\Component;


use EasyWeChat\Core\AccessToken;
use EasyWeChat\Core\Exceptions\HttpException;
use EasyWeChat\Support\Log;

class ComponentAccessToken extends AccessToken
{

    protected $queryName = 'component_access_token';

    protected $prefix = 'wechat.component_access_token.';

    const API_TOKEN_GET = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';

    public function getToken($forceRefresh = false)
    {
        $cacheKey = $this->prefix.$this->appId;

        $cached = FALSE;//$this->getCache()->fetch($cacheKey);

        if ($forceRefresh || empty($cached)) {
            $token = $this->getTokenFromServer();

            // XXX: T_T... 7200 - 1500
            $this->getCache()->save($cacheKey, $token[$this->getQueryName()], $token['expires_in'] - 1500);
			Log::info('服务器获取到component_access_token'.$token[$this->getQueryName()]);
            return $token[$this->getQueryName()];
        }
		
		Log::info('缓存获取到component_access_token'.$cached);

        return $cached;
    }

    public function getTokenFromServer()
    {
        $componentVerifyTicketInstance = new ComponentVerifyTicket($this->getCache());
        $componentVerifyTicket = $componentVerifyTicketInstance->getComponentVerifyTicket();
        $params = [
            'component_appid' => $this->appId,
            'component_appsecret' => $this->secret,
            'component_verify_ticket' => $componentVerifyTicket,
        ];

        $http = $this->getHttp();

        $token = $http->parseJSON($http->json(self::API_TOKEN_GET, $params));

        if (empty($token[$this->getQueryName()])) {
            throw new HttpException('Request AccessToken fail. response: '.json_encode($token, JSON_UNESCAPED_UNICODE));
        }

        return $token;
    }

}