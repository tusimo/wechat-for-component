<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 16:07
 */

namespace Tusimo\Wechat\Component;


use Doctrine\Common\Cache\Cache;
use EasyWeChat\Support\Log;

class AuthorizerAccessToken
{
    use CacheTrait;
    const API_AUTHORIZER_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token';
    /**
     * 授权方令牌
     *
     * @var string
     */
    protected $token;
    /**
     * 授权方appid
     *
     * @var string
     */
    protected $authorizerAppId;
    /**
     * 授权方的刷新令牌
     *
     * @var string
     */
    protected $authorizerRefreshToken;

    protected $componentAppId;

    protected $componentAppSecret;
    /**
     * 缓存前缀
     *
     * @var string
     */
    protected $cacheKey = 'wechat.authorizer_access_token.%s';

    protected $refreshKey = 'wechat.authorizer_refresh_token.%s';

    public function __construct($authorizerAppId, $authorizerRefreshToken, $componentAppId, $componentAppSecret, Cache $cache = NULL)
    {
        $this->authorizerAppId = $authorizerAppId;
        $this->authorizerRefreshToken = $authorizerRefreshToken;
        $this->componentAppId = $componentAppId;
        $this->componentAppSecret = $componentAppSecret;
        $this->cache = $cache;
    }
    /**
     * 获取授权公众号的令牌
     *
     * @return string
     */
    public function getToken()
    {
        $cacheKey = sprintf($this->cacheKey, $this->authorizerAppId);
        $this->token = FALSE;//$this->getCache()->fetch($cacheKey);
        if (!$this->token) {
            $params = array(
                'component_appid'          => $this->componentAppId,
                'authorizer_appid'         => $this->authorizerAppId,
                'authorizer_refresh_token' => $this->authorizerRefreshToken,
            );
            $http = new ComponentHttp(new ComponentAccessToken($this->componentAppId, $this->componentAppSecret,$this->cache));
            $response = $http->json(self::API_AUTHORIZER_TOKEN, $params);
			Log::record(is_array($response) ? json_encode($response) : $response);
            // 设置token
            $token = $response['authorizer_access_token'];
            // 把token缓存起来
            $this->getCache()->save($cacheKey,$token,$response['expires_in']);
            return $token;
        }
        return $this->token;
    }
	
	public function getQueryName(){
		return 'access_token';
	}

    /**
     * 将token信息保存到缓存里
     * @param $authorizerAccessToken
     * @param $authorizerRefreshToken
     * @return $this
     */
    public function setToken($authorizerAccessToken, $authorizerRefreshToken){
        $cacheKey = sprintf($this->cacheKey, $this->authorizerAppId);
        $this->getCache()->save($cacheKey, $authorizerAccessToken);
        $refreshKey = sprintf($this->refreshKey, $this->authorizerAppId);
        $this->getCache()->save($refreshKey, $authorizerRefreshToken);
        return $this;
    }
}