<?php namespace Omnipay\Pagarme;

use Omnipay\Common\AbstractGateway;

/**
 * https://PagarMev3.docs.apiary.io/#reference/pix
 * @method \Omnipay\Common\Message\RequestInterface completeAuthorize(array $options = array())
 * @method \Omnipay\Common\Message\RequestInterface completePurchase(array $options = array())
 * @method \Omnipay\Common\Message\RequestInterface refund(array $options = array())
 * @method \Omnipay\Common\Message\RequestInterface createCard(array $options = array())
 * @method \Omnipay\Common\Message\RequestInterface updateCard(array $options = array())
 * @method \Omnipay\Common\Message\RequestInterface deleteCard(array $options = array())
 */
class Gateway extends AbstractGateway
{
    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     * @return string
     */
    public function getName()
    {
        return 'Pagarme';
    }

    /**
     * Define gateway parameters, in the following format:
     *
     * [
     *     'apiKey' => '', // string The Merchant Key
     * ];
     * @return array
     */
    public function getDefaultParameters()
    {
        return [
            'publicKey' => '',
            'secretKey' => '',
            'testMode' => false,
        ];
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


    /**
     * Authorize Request
     *
     * An Authorize request is similar to a purchase request but the
     * charge issues an authorization (or pre-authorization), and no money
     * is transferred.  The transaction will need to be captured later
     * in order to effect payment. Uncaptured charges expire in 5 days.
     *
     * Either a card object or card_id is required by default. Otherwise,
     * you must provide a card_hash, like the ones returned by PagarMe
     *
     * PagarMe gateway supports only two types of "payment_method":
     *
     * * credit_card
     *
     * Optionally, you can provide the customer details to use the antifraude
     * feature. These details is passed using the following attributes available
     * on credit card object:
     *
     * * firstName
     * * lastName
     * * address1 (must be in the format "street, street_number and neighborhood")
     * * address2 (used to specify the optional parameter "street_complementary")
     * * postcode
     * * phone (must be in the format "DDD PhoneNumber" e.g. "19 98888 5555")
     *
     * @param array $parameters
     * @return \Omnipay\PagarMe\Message\AuthorizeRequest
     */
    /*public function authorize(array $parameters = [])//ok
    {
        return $this->createRequest('\Omnipay\PagarMe\Message\AuthorizeRequest', $parameters);
    }*/

    public function acceptNotification(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PagarMe\Message\NotificationRequest', $parameters);
    }

    /**
     * Capture Request
     *
     * Use this request to capture and process a previously created authorization.
     *
     * @param array $parameters
     * @return \Omnipay\PagarMe\Message\CaptureRequest
     */
    /*public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PagarMe\Message\CaptureRequest', $parameters);
    }*/

    /**
     * Purchase request.
     *
     * To charge a credit card  you create a new transaction
     * object. If your MerchantID is in test mode, the supplied card won't actually
     * be charged, though everything else will occur as if in live mode.
     *
     * Either a card object or card_id is required by default. Otherwise,
     * you must provide a card_hash, like the ones returned by PagarMe
     *
     * PagarMe gateway supports only one type of "payment_method":
     *
     * * credit_card
     *
     *
     * Optionally, you can provide the customer details to use the antifraude
     * feature. These details is passed using the following attributes available
     * on credit card object:
     *
     * * firstName
     * * lastName
     * * address1 (must be in the format "street, street_number and neighborhood")
     * * address2 (used to specify the optional parameter "street_complementary")
     * * postcode
     * * phone (must be in the format "DDD PhoneNumber" e.g. "19 98888 5555")
     *
     * @param array $parameters
     * @return \Omnipay\PagarMe\Message\PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PagarMe\Message\PurchaseRequest', $parameters);
    }

    public function authorize(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PagarMe\Message\AuthorizeRequest', $parameters);
    }
    public function capture(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PagarMe\Message\CaptureRequest', $parameters);
    }


    /**
     * Void Transaction Request
     *
     *
     *
     * @param array $parameters
     * @return \Omnipay\PagarMe\Message\VoidRequest
     */
    public function void(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PagarMe\Message\VoidRequest', $parameters);
    }

    public function fetchTransaction(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PagarMe\Message\FetchTransactionRequest', $parameters);
    }
}
