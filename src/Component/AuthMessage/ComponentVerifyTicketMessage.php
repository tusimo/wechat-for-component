<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/11
 * Time: 14:06
 */

namespace Tusimo\Wechat\Component\AuthMessage;


class ComponentVerifyTicketMessage extends AbstractAuthMessage
{
    protected $infoType = 'component_verify_ticket';

    protected $componentVerifyTicket;

}