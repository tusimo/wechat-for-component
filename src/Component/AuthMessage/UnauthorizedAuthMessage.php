<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/11
 * Time: 14:08
 */

namespace Tusimo\Wechat\Component\AuthMessage;


class UnauthorizedAuthMessage extends AbstractAuthMessage
{
    protected $infoType = 'unauthorized';

    protected $authorizerAppId;

}