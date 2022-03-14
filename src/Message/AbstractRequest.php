<?php namespace Omnipay\Pagarme\Message;


abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $liveEndpoint = 'https://api.pagar.me/core';//o ambiente de teste e produção é o mesmo, o que muda é a chaveSecreta
    //protected $testEndpoint = 'https://sandbox.pagar.me/core';
    protected $version = 5;
    protected $requestMethod = 'POST';
    protected $resource = 'orders';

    public function sendData($data)
    {
        $method = $this->requestMethod;
        $url = $this->getEndpoint();

        $headers = [
            'Authorization'=> 'Basic '.$this->getAuthHash(),
            'Content-Type' => 'application/json',
        ];

        //print_r([$method, $url, $headers, json_encode($data)]);exit();
        $response = $this->httpClient->request(
            $method,
            $url,
            $headers,
            $this->toJSON($data)
            //http_build_query($data, '', '&')
        );
        //print_r($response);
        //print_r($data);

        if ($response->getStatusCode() != 200 && $response->getStatusCode() != 201 && $response->getStatusCode() != 400) {
            $array = [
                'error' => [
                    'code' => $response->getStatusCode(),
                    'message' => $response->getReasonPhrase()
                ]
            ];

            return $this->response = $this->createResponse($array);
        }

        $json = $response->getBody()->getContents();
        $array = @json_decode($json, true);
        //print_r($array);

        return $this->response = $this->createResponse(@$array);
    }

    protected function setBaseEndpoint($value)
    {
        $this->baseEndpoint = $value;
    }

    public function __get($name)
    {
        return $this->getParameter($name);
    }

    protected function setRequestMethod($value)
    {
        return $this->requestMethod = $value;
    }

    protected function decode($data)
    {
        return json_decode($data, true);
    }

    public function getEmail()
    {
        return $this->getParameter('email');
    }

    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    public function getPublicKey()
    {
        return $this->getParameter('publicKey');
    }

    public function setPublicKey($value)
    {
        return $this->setParameter('publicKey', $value);
    }

    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey($value)
    {
        return $this->setParameter('secretKey', $value);
    }

    public function setOrderId($value)
    {
        return $this->setParameter('order_id', $value);
    }
    public function getOrderId()
    {
        return $this->getParameter('order_id');
    }

    public function setInstallments($value)
    {
        return $this->setParameter('installments', $value);
    }
    public function getInstallments()
    {
        return $this->getParameter('installments');
    }

    public function setSoftDescriptor($value)
    {
        return $this->setParameter('soft_descriptor', $value);
    }
    public function getSoftDescriptor()
    {
        return $this->getParameter('soft_descriptor');
    }

    public function getPaymentType()
    {
        return $this->getParameter('paymentType');
    }

    public function setPaymentType($value)
    {
        $this->setParameter('paymentType', $value);
    }

    public function getAmount()
    {
        return (int)round((parent::getAmount()*100.0), 0);
    }

    public function getDueDate()
    {
        $dueDate = $this->getParameter('dueDate');
        if($dueDate)
            return $dueDate;

        $time = localtime(time());
        $ano = $time[5]+1900;
        $mes = $time[4]+1+1;
        $dia = 1;// $time[3];
        if($mes>12)
        {
            $mes=1;
            ++$ano;
        }

        $dueDate = sprintf("%04d-%02d-%02d", $ano, $mes, $dia);
        $this->setDueDate($dueDate);

        return $dueDate;
    }

    public function setDueDate($value)
    {
        return $this->setParameter('dueDate', $value);
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getMethod()
    {
        return $this->requestMethod;
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }

    protected function getEndpoint()
    {
        $version = $this->getVersion();
        //$endPoint = ($this->getTestMode()?$this->testEndpoint:$this->liveEndpoint);
        $endPoint = $this->liveEndpoint;
        return  "{$endPoint}/v{$version}/{$this->getResource()}";
    }

    public function getData()
    {
        $this->validate('publicKey', 'secretKey');

        return [
        ];
    }

    public function toJSON($data, $options = 0)
    {
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($data, $options | 64);
        }
        return str_replace('\\/', '/', json_encode($data, $options));
    }

    protected function getAuthHash()
    {
        return base64_encode($this->getSecretKey().":");
    }

    public function getTransactionID()
    {
        return $this->getParameter('transactionId');
    }

    public function setTransactionID($value)
    {
        return $this->setParameter('transactionId', $value);
    }

    public function getDataCreditCard()
    {
        $this->validate('card');
        $card = $this->getCard();
        $customer = $this->getCustomerData();

        $data = [
            "code"=>$this->getOrderId(),
            "items"=> $this->getItemData(),
            "customer"=>$this->getCustomerData(),
            "shipping"=> $this->getShippingData(),
            //"ip"=> "52.168.67.32",
            /*"session_id"=> "322b821a",
            "device"=> [
                "platform"=> "ANDROID OS"
            ],
            "location"=> [
                "latitude"=> "-22.970722",
                "longitude"=> "43.182365"
            ],*/
            "payments"=>[
                [
                    "payment_method"=> "credit_card",
                    "credit_card"=>[
                        "recurrence"=> false,
                        "installments"=> $this->getInstallments(),
                        "operation_type"=>"auth_and_capture", // valores: auth_and_capture, auth_only, pre_auth
                        "statement_descriptor"=> $this->getSoftDescriptor(),
                        "card"=> [
                            "number"=> $card->getNumber(),
                            "holder_name"=> $card->getName(),
                            "exp_month"=> $card->getExpiryMonth(),
                            "exp_year"=> $card->getExpiryYear(),//TODO: são 2 ou 4 algarismos?
                            "cvv"=> $card->getCvv(),
                            "billing_address"=> [
                                "line_1"=> $customer->getBillingNumber().', '.$customer->getBillingAddress1().', '.$customer->getBillingDistrict(),
                                //"line_2"=> $customer->getBillingAddress2(),
                                "zip_code"=> $customer->getBillingPostcode(),
                                "city"=> $customer->getBillingCity(),
                                "state"=> $customer->getBillingState(),
                                "country"=> "BR"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $data;
    }

    public function getDataBoleto()
    {
        $customer = $this->getCustomerData();

        $data = [
            "code"=>$this->getOrderId(),
            "items"=> $this->getItemData(),
            "customer"=>$this->getCustomerData(),
            "shipping"=> $this->getShippingData(),
            "payments"=>[
                [
                    "payment_method"= "boleto",
                    "boleto"=>[
                        "instructions"=> "Pagar até o vencimento",
                        "due_at"=> $this->getDueDate()."T23:59:59Z",
                        //"document_number"=> "123",
                        "type"=> "DM"  
                    ]
                ]
            ]
        ];

        // onde coloca o valor total da transação com o valor do frete ? "Amount"=>$this->getAmount(),

        return $data;
    }

    public function getDataPix()
    {
        $customer = $this->getCustomerData();

        $data = [
            "code"=>$this->getOrderId(),
            "items"=> $this->getItemData(),
            "customer"=>$this->getCustomerData(),
            "shipping"=> $this->getShippingData(),
            "payments"=>[
                [
                    "payment_method"= "pix",
                    "pix"=>[
                        "expires_at"=> $this->getDueDate()."T23:59:59Z"
                    ]
                ]
            ]
        ];

        // onde coloca o valor total da transação com o valor do frete ? "Amount"=>$this->getAmount(),

        return $data;
    }

    public function getCustomer()
    {
        return $this->getParameter('customer');
    }

    public function setCustomer($value)
    {
        return $this->setParameter('customer', $value);
    }

    public function getCustomerData()
    {
        $customer = $this->getCustomer();

        $data = [
            "name"=>$customer->getName(),
            "email"=>$customer->getEmail(),
            "document_type"=>"CPF",
            "document"=>$customer->getDocumentNumber(),
            "type"=> "individual",
            "birthdate"=>$customer->getBirthday('dd/mm/YYYY'),
            //"ip"=>$this->getClientIp(),
            "address"=>[
                "line_1"=> $customer->getBillingNumber().', '.$customer->getBillingAddress1().', '.$customer->getBillingDistrict(),
                "line_2"=> $customer->getBillingAddress2(),
                "zip_code"=> $customer->getBillingPostcode(),
                "city"=> $customer->getBillingCity(),
                "state"=> $customer->getBillingState(),
                "country"=> "BR"
            ],
            "phones"=>[
                "mobile_phone"=>[
                    "country_code"=> "55",
                    "area_code"=> $customer->getCodeArea(),//TODO: confiramar nome de funcao
                    "number"=> $customer->getPhone()//TODO: confiramar nome de funcao
                ]
            ]
        ];

        return $data;
    }

    public function getShippingData()
    {
        $customer = $this->getCustomer();

        $data = [
                "amount"=> (int)($this->getShippingPrice()*100.0),
                "description"=> "Compra em ".$this->getSoftDescriptor(),
                "recipient_name"=> $customer->getName(),
                "recipient_phone"=> $customer->getPhone(),
                "address"=>[
                    "line_1"=> $customer->getBillingNumber().', '.$customer->getBillingAddress1().', '.$customer->getBillingDistrict(),
                    "line_2"=> $customer->getBillingAddress2(),
                    "zip_code"=> $customer->getBillingPostcode(),
                    "city"=> $customer->getBillingCity(),
                    "state"=> $customer->getBillingState(),
                    "country"=> "BR"
                ]
        ];

        return $data;
    }

    public function getItemData()
    {
        $data = [];
        $items = $this->getItems();

        if ($items) {
            foreach ($items as $n => $item) {
                $item_array = [];
                //$item_array['id'] = $n+1;
                //$item_array['title'] = $item->getName();
                $item_array['description'] = $item->getName();
                //$item_array['category_id'] = $item->getCategoryId();
                $item_array['quantity'] = (int)$item->getQuantity();
                //$item_array['currency_id'] = $this->getCurrency();
                $item_array['amount'] = (int)($item->getPrice()*100.0);

                array_push($data, $item_array);
            }
        }

        return $data;
    }
}
