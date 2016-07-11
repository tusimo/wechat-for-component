<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 17:24
 */

namespace Tusimo\Wechat\Component;


use Doctrine\Common\Cache\FilesystemCache;
use EasyWeChat\Core\AccessToken;
use EasyWeChat\Core\Exception;
use Symfony\Component\HttpFoundation\Request;
use Tusimo\Wechat\Providers\AuthGuardServiceProvider;

trait ApplicationTrait
{
    //重写支持从第三方平台获取的Token
    private function registerBase(){
        $this['request'] = function () {
            return Request::createFromGlobals();
        };

        $this['cache'] = function () {
            return new FilesystemCache(sys_get_temp_dir());
        };

        /**
         * if set component init access_token from component
         */
        if (isset($config['component_app_id']) && $config['component_app_secret'] && !empty($config['component_app_id']) && !empty($config['component_app_secret'])) {
            //注册auth_guard
            $this->register(new AuthGuardServiceProvider());
            
            $authorizerAppId = $this->setAuthorizerAppId();
            $authorizerRefreshToken = $this->getAuthorizerRefreshToken();
            $this['access_token'] = new AuthorizerAccessToken(
                $authorizerAppId,
                $authorizerRefreshToken,
                $this['config']['component_app_id'],
                $this['config']['component_app_secret'],
                $this['cache']
            );
        }

        $this['access_token'] = function () {
            return new AccessToken(
                $this['config']['app_id'],
                $this['config']['secret'],
                $this['cache']
            );
        };
    }

    public function setAuthorizerAppId(){
        return $this['config']['authorizer_app_id'];
    }

    private function getAuthorizerRefreshToken(){
        if (!$refreshToken =$this['cache']->fetch('wechat.authorizer_refresh_token'.$this->setAuthorizerAppId())){
            return new Exception('未获取到refreshtoken,请重新授权');
        }
        return $refreshToken;
    }
}