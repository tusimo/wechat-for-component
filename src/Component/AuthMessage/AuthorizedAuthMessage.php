<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/11
 * Time: 14:10
 */

namespace Tusimo\Wechat\Component\AuthMessage;


class AuthorizedAuthMessage extends AbstractAuthMessage
{
    protected $infoType = 'authorized';

    protected $authorizerAppId;

    protected $authorizationCode;

    protected $authorizationCodeExpiredTime;


}