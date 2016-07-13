<?php

namespace Tusimo\Wechat\Providers;

use EasyWeChat\Encryption\Encryptor;
use EasyWeChat\Support\Log;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tusimo\Wechat\Component\AuthGuard;
use Tusimo\Wechat\Component\Component;
use Tusimo\Wechat\Component\ComponentVerifyTicket;
use Tusimo\Wechat\Component\AuthorizerAccessToken;

/**
 * Class ServerServiceProvider.
 */
class AuthGuardServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['encryptor'] = function ($pimple) {
            return new Encryptor(
                $pimple['config']['component_app_id'],
                $pimple['config']['component_token'],
                $pimple['config']['component_aes_key']
            );
        };
        $pimple['component'] = function($pimple) {
            return new Component(
                $pimple['config']['component_app_id'],
                $pimple['config']['component_app_secret'],
                $pimple['cache']
            );
        };

        $pimple['auth_server'] = function ($pimple) {
            $server = new AuthGuard($pimple['config']['component_token']);

            $server->debug($pimple['config']['debug']);

            $server->setEncryptor($pimple['encryptor']);
            
            $server->setMessageHandler(function($message) use ($pimple){
                switch ($message->InfoType) {
                    case 'component_verify_ticket' : //发送ticket
                        Log::info('接收到ticket事件'.$message);
                        $componentVerifyTicket = new ComponentVerifyTicket($pimple['cache']);
                        $componentVerifyTicket->setComponentVerifyTicket($message->ComponentVerifyTicket);
                        break;
					case 'unauthorized'://取消授权事件
                        Log::info('接收到取消授权事件'.$message);
                        break;
                    case 'authorized' : //授权事件
                        Log::info('接收到授权事件'.$message);
                    case 'updateauthorized'://更新授权事件
                        Log::info('接收到更新授权事件');
						//get auth_info 
						$authInfo = $pimple['component']->queryAuth($message->AuthorizationCode);
                        Log::info('获取到auth_info'.json_encode($authInfo));
						$authirizerAccessToken = new AuthorizerAccessToken(
							$authInfo['authorization_info']['authorizer_appid'],
							$authInfo['authorization_info']['authorizer_refresh_token'],
							$pimple['config']['component_app_id'],
							$pimple['config']['component_app_secret'],
							$pimple['cache']
						);
						$authirizerAccessToken->setToken($authInfo['authorization_info']['authorizer_access_token'],$authInfo['authorization_info']['authorizer_refresh_token']);
                        break;
                }
            });
            return $server;
        };
    }
}
