<?php

namespace Paytic\Omnipay\Euplatesc\Traits;

/**
 * Trait HasIntegrationParametersTrait
 * @package Paytic\Omnipay\Euplatesc\Traits
 */
trait HasIntegrationParametersTrait
{

    /**
     * @param $value
     * @return mixed
     */
    public function setMid($value)
    {
        return $this->setParameter('mid', $value);
    }
    /**
     * @param $value
     * @return mixed
     */
    public function setKey($value)
    {
        return $this->setParameter('key', $value);
    }
    /**
     * @return mixed
     */
    public function getMid()
    {
        return $this->getParameter('mid');
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->getParameter('key');
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setExtraData($value)
    {
        return $this->setParameter('ExtraData', $value);
    }

    /**
     * @return mixed
     */
    public function getExtraData()
    {
        return $this->getParameter('ExtraData');
    }
}
