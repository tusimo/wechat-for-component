<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 17:08
 */

namespace Tusimo\Wechat\Component;

use EasyWeChat\Core\Exception;
use Tusimo\Wechat\Providers\AuthGuardServiceProvider;

class Application extends \EasyWeChat\Foundation\Application
{

    public function __construct(array $config)
    {
        parent::__construct($config);
        /**
         * if set component init access_token from component
         */
        if (isset($config['component_app_id']) && $config['component_app_secret'] && !empty($config['component_app_id']) && !empty($config['component_app_secret'])) {
            //注册auth_guard
            $this->register(new AuthGuardServiceProvider());
        }
    }

    public function setAuthorizerAppId($authorizerAppId){
        $authorizerRefreshToken = $this->getAuthorizerRefreshToken($authorizerAppId);
        $this['access_token'] = new AuthorizerAccessToken(
            $authorizerAppId,
            $authorizerRefreshToken,
            $this['config']['component_app_id'],
            $this['config']['component_app_secret'],
            $this['cache']
        );
    }

    public function setAuthCallBack($callable){
        return $this['component']->setAuthCallback($callable);
    }

    private function getAuthorizerRefreshToken($authorizerAppId){
        if (!$refreshToken =$this['cache']->fetch('wechat.authorizer_refresh_token.'.$authorizerAppId)){
            return new Exception('未获取到refreshtoken,请重新授权');
        }
        return $refreshToken;
    }
}