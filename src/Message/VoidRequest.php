<?php namespace Omnipay\Pagarme\Message;

/**
 *  O Cancelamento é aplicavel a transações do mesmo dia sendo autorizadas ou aprovadas
 *  O Estono é aplicável para transações onde virou o dia, seguindo o processo do adquirente
 * <code>
 *   // Do a refund transaction on the gateway
 *   $transaction = $gateway->void(array(
 *       'transactionId'     => $transactionCode,
 *   ));
 *
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *   }
 * </code>
 * 
 * curl --request DELETE \
     --url https://api.pagar.me/core/v5/charges/charge_id/ \
     --header 'Accept: application/json' \
     --header 'Content-Type: application/json'
 */

class VoidRequest extends AbstractRequest   // está dando  erro para vendas com cartao parcelado, não permitindo estornar individualmente o pagamento
{
    protected $resource = 'charges';
    protected $requestMethod = 'DELETE';


    public function getData()
    {
        $this->validate('transactionId', 'amount');
        //$data = parent::getData();
        $data = [
                "amount"=>(int)($this->getAmount()*100.0),
                //"code"=>"ABCDE123"
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
            "%s/%s",
            $this->getEndpoint(),
            $this->getTransactionID()
        );

        //print_r([$this->getMethod(), $url, $headers, $data]);exit();
        $httpResponse = $this->httpClient->request($this->getMethod(), $url, $headers, $this->toJSON($data));
        $json = $httpResponse->getBody()->getContents();
        return $this->createResponse(@json_decode($json, true));
    }

}
