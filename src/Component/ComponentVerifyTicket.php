<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 14:35
 */

namespace Tusimo\Wechat\Component;


use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

class ComponentVerifyTicket
{
    use CacheTrait;

    protected $prefix = 'wechat.component_verify_ticket';

    /**
     * ComponentVerifyTicket constructor.
     * @param Cache|null $cache
     */
    public function __construct(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * set the component_verify_ticket
     * @param $componentVerifyTicket
     * @return $this
     */
    public function setComponentVerifyTicket($componentVerifyTicket)
    {
        $this->getCache()->save($this->prefix, $componentVerifyTicket);

        return $this;
    }

    /**
     * get the component_verify_ticket
     * @return mixed
     */
    public function getComponentVerifyTicket()
    {
        return $this->getCache()->fetch($this->prefix);
    }

}