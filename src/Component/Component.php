<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 15:22
 */

namespace Tusimo\Wechat\Component;

use Doctrine\Common\Cache\Cache;
use EasyWeChat\Support\Log;
use Symfony\Component\HttpFoundation\Request;

class Component
{
    use CacheTrait;

    const COMPONENT_LOGIN_PAGE = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=%s&pre_auth_code=%s&redirect_uri=%s';
    const API_CREATE_PREAUTHCODE = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode';
    const API_QUERY_AUTH = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth';
    const API_GET_AUTHORIZER_INFO = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info';
    const API_GET_AUTHORIZER_OPTION = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option';
    const API_SET_AUTHORIZER_OPTION = 'https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option';

    /**
     * 第三方平台ComponentAppId
     *
     * @var string
     */
    protected $componentAppId;

    /**
     * 第三方平台的ComponentAppSecret
     * @var string
     */
    protected $componentAppSecret;

    /**
     * 获取到的授权AppId
     * @var string
     */
    protected $authorizerAppId;
    /**
     * Http对象
     *
     * @var ComponentHttp
     */
    protected $http;

    protected $preAuthCodeCacheKey = 'wechat.pre_auth_code.%s';

    public function __construct($componentAppId, $componentAppSecret, Cache $cache = NULL)
    {
        $this->http = new ComponentHttp(new ComponentAccessToken($componentAppId, $componentAppSecret, $cache));
        $this->componentAppId = $componentAppId;
        $this->componentAppSecret = $componentAppSecret;
        $this->cache = $cache;
    }
    /**
     * 第三方平台授权页
     *
     * @param $redirect
     * @param null $identification
     * @return string
     */
    public function loginPage($redirect, $identification = null)
    {
        $preAuthCode = $this->createPreAuthCode($identification);
        // 拼接出微信公众号登录授权页面url
        return sprintf(self::COMPONENT_LOGIN_PAGE, $this->componentAppId, $preAuthCode, urlencode($redirect));
    }
    /**
     * 该API用于获取预授权码。
     * 预授权码用于公众号授权时的第三方平台方安全验证。
     *
     * @param $identification
     * @return mixed
     */
    public function createPreAuthCode($identification)
    {
        $cacheKey = sprintf($this->preAuthCodeCacheKey, $identification);
        $preAuthCode = $this->getCache()->fetch($cacheKey);
        if (!$preAuthCode) {
            $response = $this->http->json(self::API_CREATE_PREAUTHCODE, [
                'component_appid' => $this->componentAppId,
            ]);
            $preAuthCode = $response['pre_auth_code'];
            // 把pre_auth_code缓存起来
            $this->getCache()->save($cacheKey, $preAuthCode,$response['expires_in'] -100);
        }
        return $preAuthCode;
    }
    /**
     * 删除已经使用的预授权码.
     *
     * @param $identification
     * @return mixed
     */
    public function forgetPreAuthCode($identification)
    {
        $cacheKey = sprintf($this->preAuthCodeCacheKey, $identification);
        return $this->getCache()->delete($cacheKey);
    }

    /**
     * 使用授权码换取公众号的授权信息
     *
     * @param $authorizationCode
     * @return mixed
     */
    public function queryAuth($authorizationCode)
    {
        $params = array(
            'component_appid'    => $this->componentAppId,
            'authorization_code' => $authorizationCode,
        );
        return $this->http->json(self::API_QUERY_AUTH, $params);
    }

    /**
     * 获取授权方的账户信息
     *
     * @param $authorizerAppid
     * @return mixed
     */
    public function getAuthorizerInfo($authorizerAppid)
    {
        $params = array(
            'component_appid'  => $this->componentAppId,
            'authorizer_appid' => $authorizerAppid,
        );
        return $this->http->json(self::API_GET_AUTHORIZER_INFO, $params);
    }

    /**
     * 获取授权方的选项设置信息
     *
     * @param $authorizerAppId
     * @param $optionName
     * @return mixed
     */
    public function getAuthorizerOption($authorizerAppId, $optionName)
    {
        $params = array(
            'component_appid'  => $this->componentAppId,
            'authorizer_appid' => $authorizerAppId,
            'option_name'      => $optionName,
        );
        return $this->http->json(self::API_GET_AUTHORIZER_OPTION, $params);
    }

    /**
     * 设置授权方的选项信息
     *
     * @param $authorizerAppId
     * @param $optionName
     * @param $optionValue
     * @return mixed
     */
    public function setAuthorizerOption($authorizerAppId, $optionName, $optionValue)
    {
        $params = array(
            'component_appid'  => $this->componentAppId,
            'authorizer_appid' => $authorizerAppId,
            'option_name'      => $optionName,
            'option_value'     => $optionValue,
        );
        return $this->http->json(self::API_SET_AUTHORIZER_OPTION, $params);
    }

    /**
     * 处理授权
     * @param callable $callable
     */
    public function setAuthCallback(callable $callable){
        $request = Request::createFromGlobals();
        $authInfo = $this->queryAuth($request->get('auth_code'));
        Log::debug('处理授权同步回调'.$request->getContent());
        //保存信息
        $authirizerAccessToken = new AuthorizerAccessToken(
            $authInfo['authorization_info']['authorizer_appid'],
            $authInfo['authorization_info']['authorizer_refresh_token'],
            $this->componentAppId,
            $this->componentAppSecret,
            $this->cache
        );
        $authInfoDetails = $this->getAuthorizerInfo($authInfo['authorization_info']['authorizer_appid']);
        $authirizerAccessToken->setToken($authInfo['authorization_info']['authorizer_access_token'],$authInfo['authorization_info']['authorizer_refresh_token']);
        $callable(array_merge($authInfo,$authInfoDetails));
        return;
    }
}