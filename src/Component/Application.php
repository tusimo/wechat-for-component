<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 17:08
 */

namespace Tusimo\Wechat\Component;

class Application extends \EasyWeChat\Foundation\Application
{
    use ApplicationTrait;

    public function setAuthCallBack($callable){
        return $this['component']->setAuthCallback($callable);
    }
}