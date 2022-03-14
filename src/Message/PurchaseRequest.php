<?php namespace Omnipay\Pagarme\Message;

class PurchaseRequest extends AbstractRequest
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

            case 'boleto':
                $data = $this->getDataBoleto();
                break;

            case 'pix':
                $data = $this->getDataPix();
                break;

            default:
                $data = $this->getDataCreditCard();
        }

        return $data;
    }
}
