<?php

namespace Paytic\Omnipay\Euplatesc\Message\Traits;

use Paytic\Omnipay\Common\Message\Traits\GatewayNotificationRequestTrait;
use Paytic\Omnipay\Euplatesc\Helper;

/**
 * Class CompletePurchaseRequestTrait
 * @package Paytic\Omnipay\Euplatesc\Message\Traits
 */
trait CompletePurchaseRequestTrait
{
    use GatewayNotificationRequestTrait;

    public $fields = [
        'amount',
        'curr',
        'invoice_id',
        'ep_id',
        'merch_id',
        'action',
        'message',
        'approval',
        'timestamp',
        'nonce',
    ];

    /**
     * @return mixed
     */
    public function isValidNotification()
    {
        return $this->hasPOST('amount', 'invoice_id', 'merch_id', 'ep_id', 'fp_hash');
    }

    /**
     * @return bool|mixed
     */
    protected function parseNotification()
    {
        $data = $this->httpRequest->request->all();
        if ($this->validateHash()) {
            return $data;
        }
        return [];
    }

    /**
     * @return string
     */
    public function generateHashString()
    {
        $return = "";
        $fields = $this->fields;
        foreach ($fields as $field) {
            $value = addslashes(trim($this->httpRequest->request->get($field)));
            $return .= Helper::generateHashFromString($value);
        }

        return $return;
    }

    /**
     * @return mixed
     */
    public function getModelIdFromRequest()
    {
        return $this->httpRequest->request->get('invoice_id');
    }

    /**
     * @return boolean
     */
    protected function validateHash()
    {
        $hash = $this->httpRequest->request->get('fp_hash');
        $hmac = strtoupper($this->generateHmac($this->generateHashString()));

        if ($hmac == $hash) {
            return true;
        }

        return false;
    }

    /**
     * @param $data
     * @return string
     */
    protected function generateHmac($data)
    {
        $key = $this->getKey();

        return Helper::generateHmac($data, $key);
    }
}
