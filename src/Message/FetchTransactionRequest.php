<?php namespace Omnipay\Pagarme\Message;

class FetchTransactionRequest extends AbstractRequest
{
    protected $resource = 'orders';
    //protected $resource = 'charges';// https://docs.pagar.me/reference#obter-cobran%C3%A7a
    protected $requestMethod = 'GET';

    public function sendData($data)
    {
        $this->validate('transactionId');

        $url = $this->getEndpoint();

        $headers = [
            'Authorization'=> 'Basic '.$this->getAuthHash(),
            'Content-Type' => 'application/json',
        ];

        $url = sprintf(
            '%s/%s',
            $this->getEndpoint(),
            $this->getTransactionID()//TODO: order_id
        );

        $httpResponse = $this->httpClient->request($this->getMethod(), $url, $headers);
        $json = $httpResponse->getBody()->getContents();
        return $this->createResponse(@json_decode($json, true));
    }
}
