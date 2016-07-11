<?php
/**
 * Created by PhpStorm.
 * User: YsYou
 * Date: 2016/7/11
 * Time: 14:03
 */

namespace Tusimo\Wechat\Component\AuthMessage;


use EasyWeChat\Support\Attribute;

class AbstractAuthMessage extends Attribute
{

    protected $infoType;

    protected $appId;

    protected $createTime;

    /**
     * Return type name message.
     *
     * @return string
     */
    public function getInfoType()
    {
        return $this->infoType;
    }

    /**
     * Magic getter.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return parent::__get($property);
    }

    /**
     * Magic setter.
     *
     * @param string $property
     * @param mixed $value
     * @return $this
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            parent::__set($property, $value);
        }

        return $this;
    }
}