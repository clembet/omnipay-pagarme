<?php namespace Omnipay\Pagarme\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Pagarme Response
 *
 * This is the response class for all Pagarme requests.
 *
 * @see \Omnipay\Pagarme\Gateway
 */
class Response extends AbstractResponse
{
    /**
     * Is the transaction successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        //$result = $this->data;
        if(isset($this->data['error']) || isset($this->data['error_messages']))
            return false;

        if (isset($this->data['Payment']['Status']) && isset($this->data['Payment']['ReasonCode']))
            if($this->data['Payment']['ReasonCode']==0)
                return true;

        return false;
    }

    /**
     * Get the transaction reference.
     *
     * @return string|null
     */
    public function getTransactionID()
    {
        if(isset($this->data['charges']['id']))
            return @$this->data['charges']['id'];

        return NULL;
    }

    public function getTransactionAuthorizationCode()
    {
        if(isset($this->data['charges']['id']))
            return @$this->data['charges']['id'];

        return NULL;
    }

    public function getStatus() 
    {
        $status = null;
        if(isset($this->data['charges']['status']))
            $status = @$this->data['charges']['status'];
        else
        {
            if(isset($this->data['status']))
                $status = @$this->data['status'];
        }

        /*
        Status final do pedido. Valores possíveis: paid, canceled ou failed. Caso não enviado, valor default será paid.
        */
        return $status;
    }

    public function isPaid()
    {
        $status = strtolower($this->getStatus());
        return (strcmp($status, "paid")==0)||(strcmp($status, "captured")==0);
    }

    public function isAuthorized()
    {
        $status = strtolower($this->getStatus());
        return (strcmp($status, "authorized_pending_capture")==0)||(strcmp($status, "waiting_capture")==0);
    }

    public function isPending()
    {
        $status = strtolower($this->getStatus());
        return (strcmp($status, "pending")==0)||(strcmp($status, "generated")==0)||(strcmp($status, "viewed")==0)||(strcmp($status, "processing")==0)||(strcmp($status, "waiting_payment")==0);
    }

    public function isVoided()
    {
        $status = strtolower($this->getStatus());
        return ((strcmp($status, "canceled")==0)||(strcmp($status, "voided")==0)||(strcmp($status, "refunded")==0)||(strcmp($status, "failed")==0));
    }

    /**
     * Get the error message from the response.
     *
     * Returns null if the request was successful.
     *
     * @return string|null
     */
    public function getMessage()
    {
        //print_r($this->data);
        if(isset($this->data['message']))
            return "{$this->data['message']}: ".@json_encode(@$this->data['erros']);

        return null;
    }

    public function getBoleto()
    {
        $data = $this->getData();
        $boleto = array();
        $boleto['boleto_url'] = @$data['charges']['last_transaction']['url'];
        $boleto['boleto_url_pdf'] = @$data['charges']['last_transaction']['pdf'];
        $boleto['boleto_barcode'] = @$data['charges']['last_transaction']['line'];
        $boleto['boleto_expiration_date'] = @$data['charges']['last_transaction']['due_at'];
        $boleto['boleto_valor'] = (@$data['charges']['amount']*1.0)/100.0;
        $boleto['boleto_transaction_id'] = @$data['charges']['id'];
        //@$this->setTransactionReference(@$data['transaction_id']);

        return $boleto;
    }

    public function getPix()
    {
        $data = $this->getData();
        $boleto = array();
        $boleto['pix_qrcodebase64image'] = $this->getBase64ImageFromUrl(@$data['charges']['last_transaction']['qr_code_url']);
        $boleto['pix_qrcodestring'] = @$data['charges']['last_transaction']['qr_code'];
        $boleto['pix_valor'] = (@$data['charges']['amount']*1.0)/100.0;
        $boleto['pix_transaction_id'] = @$data['charges']['id'];
        //@$this->setTransactionReference(@$data['transaction_id']);

        return $boleto;
    }

    public function getBase64ImageFromUrl($url)
    {
        $type = pathinfo($url, PATHINFO_EXTENSION);
        $data = file_get_contents($url);
        if (!$data)
            return NULL;

        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}