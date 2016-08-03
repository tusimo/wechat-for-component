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
            //处理verifyTicket
            $server->setVerifyTicketHandler(function($message) use ($pimple){
                Log::info('接收到ticket事件'.$message);
                $componentVerifyTicket = new ComponentVerifyTicket($pimple['cache']);
                $componentVerifyTicket->setComponentVerifyTicket($message->ComponentVerifyTicket);
            });
            return $server;
        };
    }
}
