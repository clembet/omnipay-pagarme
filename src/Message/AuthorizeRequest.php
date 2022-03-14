<?php namespace Omnipay\Pagarme\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\ItemBag;

class AuthorizeRequest extends AbstractRequest
{
    protected $resource = 'orders';
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */

    public function getData()
    {
        $this->validate('customer', 'paymentType');

        $data = [];
        switch(strtolower($this->getPaymentType()))
        {
            case 'creditcard':
                $data = $this->getDataCreditCard();
                break;

            default:
                $data = $this->getDataCreditCard();
        }

        return $data;
    }

    public function getDataCreditCard()
    {
        $data = parent::getDataCreditCard();
        $data["payments"]["credit_card"]["operation_type"] = "pre_auth";//auth_only, qual a diferença entre esses dois?

        return $data;
    }

}
