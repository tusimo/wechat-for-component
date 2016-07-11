<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/11
 * Time: 14:10
 */

namespace Tusimo\Wechat\Component\AuthMessage;


class UpdateAuthorizedAuthMessage extends AbstractAuthMessage
{
    protected $infoType = 'updateauthorized';

    protected $authorizerAppId;

    protected $authorizationCode;

    protected $authorizationCodeExpiredTime;


}