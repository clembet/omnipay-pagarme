<?php namespace Omnipay\Pagarme\Message;


class CaptureRequest extends AbstractRequest
{
    protected $resource = 'charges';
    protected $requestMethod = 'POST';


    public function getData()
    {
        $this->validate('transactionId', 'amount');
        //$data = parent::getData();
        $data = [
                "amount"=>(int)($this->getAmount()*100.0),
                "code"=>$this->getOrderId()
        ];

        return $data;
    }

    public function sendData($data)
    {
        $this->validate('transactionId', 'amount');

        $url = $this->getEndpoint();

        $headers = [
            'Authorization'=> 'Basic '.$this->getAuthHash(),
            'Content-Type' => 'application/json',
        ];

        $url = sprintf(
            "%s/%s/capture",
            $this->getEndpoint(),
            $this->getTransactionID()
        );

        //print_r([$this->getMethod(), $url, $headers, $data]);exit();
        $httpResponse = $this->httpClient->request($this->getMethod(), $url, $headers, $this->toJSON($data));
        $json = $httpResponse->getBody()->getContents();
        return $this->createResponse(@json_decode($json, true));
    }
}
