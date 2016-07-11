<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/8
 * Time: 15:48
 */

namespace Tusimo\Wechat\Component;


use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

trait CacheTrait
{
    protected $cache;
    /**
     * Set cache instance.
     *
     * @param \Doctrine\Common\Cache\Cache $cache
     *
     * @return $this
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Return the cache manager.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        return $this->cache ?: $this->cache = new FilesystemCache(sys_get_temp_dir());
    }
}