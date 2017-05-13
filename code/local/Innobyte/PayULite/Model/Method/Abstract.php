<?php
/**
 * Abstract PayU payment method class.
 * 
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

abstract class Innobyte_PayULite_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var array       Allowed currencies.
     */
    protected $_allowedCurrencies = array('RON', 'EUR', 'USD', 'TRY', 'HUF', 'CZK', 'PLN', 'RUB', 'UAH', 'INR');
    
    /**
     * @var Innobyte_PayULite_Model_Config      Config model.
     */
    protected $_config = null;
    
    /**
     * @var Mage_Sales_Model_Order      Order model.
     */
    protected $_order = null;
      
    /**
     * @var array   Successfull IOS statuses.
     */
    protected $_successfullStatuses = array(
        'COMPLETE',
        'REFUND',
        'REVERSED',
        'PAYMENT_AUTHORIZED',
        'CASH',
    );
    
    /**
     * @var array   IOS failure statuses.
     */
    protected $_deniedStatuses = array(
        'NOT_FOUND',
        'CARD_NOTAUTHORIZED',
        'INVALID',
    );
    
    /**
     * @Override
     * @var bool    Payment method features.
     */
    protected $_isGateway                   = true;
    protected $_canOrder                    = false;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = false;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = true;
    protected $_isInitializeNeeded          = true;
    protected $_canFetchTransactionInfo     = true;
    protected $_canReviewPayment            = false;
    protected $_canCreateBillingAgreement   = false;
    protected $_canManageRecurringProfiles  = true;
    
    
    
    /**
     * Check method for processing with base currency.
     * 
     * @Override
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        return in_array($currencyCode, $this->_allowedCurrencies);
    }
    
    
    
    /**
     * Getter method for config object; implements lazy instantiation.
     * 
     * @return  Innobyte_PayULite_Model_Config
     */
    public function getConfig()
    {
        if (is_null($this->_config)) {
            $this->_config = Mage::getSingleton('innobyte_payu_lite/config')
                ->setMethodCode($this->getCode());
            if ($this->getInfoInstance() instanceof Mage_Sales_Model_Order_Payment) {
                $this->_config->setStoreId($this->getInfoInstance()->getOrder()->getStoreId());
            } elseif ($this->getInfoInstance() instanceof Mage_Sales_Model_Quote_Payment) {
                $this->_config->setStoreId($this->getInfoInstance()->getQuote()->getStoreId());
            }
        }
        return $this->_config;
    }
    
    
    
    /**
     * Getter method for order object; implements lazy instantiation.
     * 
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = $this->getInfoInstance()->getOrder();
        }
        return $this->_order;
    }
    
    
    
    /**
     * Redirect url to PayU submit form.
     * 
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('payulite/processing/placeform', array('_secure' => true));
    }
    
    
    
    /**
     * Retrieve form data to be sent to PayU in placeform step.
     * 
     * @return  Varien_Object
     */
    abstract public function getFormFields();
    
    
    
    /**
     * Retrieve a part of the data to send to PayU.
     * PAY_METHOD and ORDER_HASH are not set! You should override them in child classes.
     * 
     * @return  Varien_Object   An object with a part of data that should be sent to API.
     */
    protected function _getFormFields() 
    {
        $returnValue = new Varien_Object();
        $order = $this->getOrder();
        $config = $this->getConfig();
        
        /* general data */
        $apiData = array(
            'MERCHANT'   => $config->getMerchantCode(),
            'ORDER_REF'  => $order->getIncrementId(),
            'ORDER_DATE' => $order->getCreatedAt(),
        );
        
        /* order details */
        if ($order->getIsVirtual()) {
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $billingAddress;
        } else {
            $shippingAddress = $order->getShippingAddress();
            $billingAddress = $order->getBillingAddress();
        }
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $apiData['ORDER_PNAME'][] = $item->getName();
            $apiData['ORDER_PCODE'][] = $item->getSku();
            $apiData['ORDER_PINFO'][] = $item->getDescription();
            $apiData['ORDER_PRICE'][] = sprintf('%.2F', $item->getBasePriceInclTax());
            $apiData['ORDER_PRICE_TYPE'][] = 'GROSS';
            $apiData['ORDER_QTY'][]   = intval($item->getQtyOrdered());
            $apiData['ORDER_VAT'][]   = sprintf('%.2F', $item->getTaxPercent());
        }
        
        $apiData['ORDER_SHIPPING']      = sprintf('%.2F', $order->getBaseShippingInclTax());
        $apiData['PRICES_CURRENCY']     = $order->getBaseCurrencyCode();
        $apiData['DISCOUNT']            = sprintf('%.2F', abs($order->getBaseDiscountAmount()));
        $apiData['DESTINATION_CITY']    = $shippingAddress->getCity();
        $apiData['DESTINATION_COUNTRY'] = $shippingAddress->getCountry();
        $apiData['PAY_METHOD'] = ''; // override this in child class
        $apiData['ORDER_HASH'] = ''; // override this in child class
        
        /* additional info */
        $cancelUrl  = str_replace('{{ORDER_ID}}', $order->getIncrementId(), $config->getCancelUrl());
        $backRefUrl = str_replace('{{ORDER_ID}}', $order->getIncrementId(), $config->getReturnUrl());
        $apiData['AUTOMODE']      = $config->isAutomode() ? '1' : '0';
        $apiData['TESTORDER']     = $config->isTestmode() ? 'TRUE' : 'FALSE';
        $apiData['LANGUAGE']      = $config->getLanguage();
        $apiData['ORDER_TIMEOUT'] = $config->getPaymentTimeout();
        $apiData['TIMEOUT_URL']   = $cancelUrl;
        $apiData['BACK_REF']      = $backRefUrl;
        
        /* additional billing & shipping info */
        $apiData['BILL_FNAME'] = $billingAddress->getFirstname();
        $apiData['BILL_LNAME'] = $billingAddress->getLastname();
        if (mb_strlen($billingAddress->getCompany())) {
            $apiData['BILL_COMPANY'] = $billingAddress->getCompany();
        }
        $apiData['BILL_EMAIL'] = $order->getCustomerEmail();
        if (mb_strlen($billingAddress->getTelephone())) {
            $apiData['BILL_PHONE'] = $billingAddress->getTelephone();
        } else {
            $apiData['BILL_PHONE'] = '-'; // field needed for anti fraud check
        }
        if (mb_strlen($billingAddress->getFax())) {
            $apiData['BILL_FAX'] = $billingAddress->getFax();
        }
        $apiData['BILL_ADDRESS']     = $billingAddress->getStreet(1);
        $apiData['BILL_ADDRESS2']    = $billingAddress->getStreet(2);
        $apiData['BILL_ZIPCODE']     = $billingAddress->getPostcode();
        $apiData['BILL_CITY']        = $billingAddress->getCity();
        $apiData['BILL_COUNTRYCODE'] = $billingAddress->getCountry();

        $apiData['DELIVERY_FNAME']   = $shippingAddress->getFirstname();
        $apiData['DELIVERY_LNAME']   = $shippingAddress->getLastname();
        if (mb_strlen($shippingAddress->getCompany())) {
            $apiData['DELIVERY_COMPANY'] = $shippingAddress->getCompany();
        }
        if (mb_strlen($shippingAddress->getTelephone())) {
            $apiData['DELIVERY_PHONE'] = $shippingAddress->getTelephone();
        } else {
            $apiData['DELIVERY_PHONE'] = '-';
        }
        $apiData['DELIVERY_ADDRESS']  = $shippingAddress->getStreet(1);
        $apiData['DELIVERY_ADDRESS2'] = $shippingAddress->getStreet(2);
        $apiData['DELIVERY_ZIPCODE']  = $shippingAddress->getPostcode();
        // other fields DELIVERY_{CITY|REGION|COUTNRY} will be overriden by DESTINATION_{*} by PayU
        
        $apiData = Mage::helper('innobyte_payu_lite')->utf8izeArray($apiData);
        
        $arrHash = array(
            'MERCHANT'            => $apiData['MERCHANT'],
            'ORDER_REF'           => $apiData['ORDER_REF'],
            'ORDER_DATE'          => $apiData['ORDER_DATE'],
            'ORDER_PNAME'         => $apiData['ORDER_PNAME'],
            'ORDER_PCODE'         => $apiData['ORDER_PCODE'],
            'ORDER_PINFO'         => $apiData['ORDER_PINFO'],
            'ORDER_PRICE'         => $apiData['ORDER_PRICE'],
            'ORDER_QTY'           => $apiData['ORDER_QTY'],
            'ORDER_VAT'           => $apiData['ORDER_VAT'],
            'ORDER_SHIPPING'      => $apiData['ORDER_SHIPPING'],
            'PRICES_CURRENCY'     => $apiData['PRICES_CURRENCY'],
            'DISCOUNT'            => $apiData['DISCOUNT'],
            'DESTINATION_CITY'    => $apiData['DESTINATION_CITY'],
            'DESTINATION_COUNTRY' => $apiData['DESTINATION_COUNTRY'],
            'PAY_METHOD'          => $apiData['PAY_METHOD'], // override this in child class
            'ORDER_PRICE_TYPE'    => $apiData['ORDER_PRICE_TYPE'],
        );
        
        $returnValue->setData(
            array(
                'form_fields' => new Varien_Object($apiData),
                'hash_fields' => new Varien_Object($arrHash),
            )
        );
        return $returnValue;
    }
    
    
    
    /**
     * Placeform 's url.
     * 
     * @return string
     */
    public function getUrl()
    {
        return $this->getConfig()->getLuUrl();
    }
    
    
    
    /**
     * Check void availability. 
     * I treat VOID as a REVERSE. REVERSE is when order was not confirmed(captured).
     * 
     * @Override
     * @param   Varien_Object $payment
     * @return  bool
     */
    public function canVoid(Varien_Object $payment)
    {
        if (!$this->_canVoid) {
            return false;
        } elseif ($payment instanceof Mage_Sales_Model_Order_Invoice
                  || $payment instanceof Mage_Sales_Model_Order_Creditmemo) {
            return false;
        } else { // check if no IDN request was made, so IRN - REVERSE could be done.
            $collection = Mage::getResourceModel('sales/order_payment_transaction_collection')
                ->setOrderFilter($this->getOrder())
                ->addPaymentIdFilter($this->getOrder()->getPayment()->getId());
            if ($collection->getSize() != 1
                || $collection->getFirstItem()->getTxnType() != Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH) {
                return false;
            }
        }
        return true;
    }
    
    
    
    /**
     * Void payment. Performs an IRN request with REVERSE purpose.
     * 
     * @Override
     * @param   Varien_Object $payment
     * @return  Mage_Payment_Model_Abstract
     * @throws  Mage_Core_Exception
     */
    public function void(Varien_Object $payment)
    {
        $returnValue = parent::void($payment); // check if REVERSE is eligible
        $this->_irn($payment, $this->getOrder()->getBaseGrandTotal(), 'void');
        return $returnValue;
    }
    
    
    
    /**
     * Refund specified amount for payment. Performs an IRN request with REFUND purpose.
     * 
     * @Override
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $returnValue = parent::refund($payment, $amount);
        $this->_irn($payment, $amount, 'refund');
        return $returnValue;
    }
    
    
    
    /**
     * Cancel payment. Performs an IRN request with either REFUND | REVERSE purpose.
     * 
     * @param Varien_Object $payment
     * @return Mage_Payment_Model_Abstract
     */
    public function cancel(Varien_Object $payment)
    {
        $returnValue = parent::cancel($payment);
        if ($this->canVoid($payment) || $this->canRefund()) {
            $this->_irn($payment, $this->getOrder()->getBaseGrandTotal(), 'cancel');
        }
        return $returnValue;
    }

    
    
    /**
     * Capture payment. Performs an IDN request.
     * 
     * @Override
     * @param   Varien_Object   $payment
     * @param   float   $amount
     * @return  Mage_Payment_Model_Abstract
     * @throws  Mage_Core_Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $returnValue = parent::capture($payment, $amount);
        $helper = Mage::helper('innobyte_payu_lite');
        if ($amount <= 0.00) {
            Mage::throwException($helper->__('Invalid amount to capture.'));
        }
        
        $postData = array(
            'MERCHANT'       => $this->getConfig()->getMerchantCode(),
            'ORDER_REF'      => str_replace(array('-capture', '-refund', '-void'), '', $payment->getLastTransId()),
            'ORDER_AMOUNT'   => $amount,
            'ORDER_CURRENCY' => $this->getOrder()->getBaseCurrencyCode(),
            'IDN_DATE'       =>  Mage::getModel('core/date')->date('Y-m-d H:i:s'),
        );
        $postData['ORDER_HASH'] = $helper->hmacMd5($this->_getK(), $postData);
        
        $debugData = array(
            'method'  => __METHOD__,
            'request' => $postData,
        );
        $exception = '';
        
        $error = array();
        $response = $this->_makeApiCall($this->getConfig()->getIdnUrl(), http_build_query($postData), $error);
        if (is_object($response)) {
            $debugData['response'] = $response->getBody();
            $arrResponse = explode('|', strip_tags($response->getBody()));
            if (count($arrResponse) >= 5) {
                $hash = $arrResponse[4]; // ORDER_HASH
                unset($arrResponse[4]);
                $hashCheck = Mage::helper('innobyte_payu_lite')->hmacMd5($this->_getK(), $arrResponse);
                if ($hash != $hashCheck) {
                    $debugData['response [ERR]'] = $helper->__('Could not capture payment. Hash check failed.');
                    $exception = $debugData['response [ERR]'];
                } elseif ($arrResponse[1] != 1) { // RESPONSE_CODE
                    /*
                     * IDN response code messages
                     * 1 Confirmed
                     * 2 ORDER_REF missing or incorrect
                     * 3 ORDER_AMOUNT missing or incorrect
                     * 4 ORDER_CURRENCY is missing or incorrect
                     * 5 IDN_DATE is not in the correct format
                     * 6 Error confirming order
                     * 7 Order already confirmed
                     * 8 Unknown error
                     * 9 Invalid ORDER_REF
                     * 10 Invalid ORDER_AMOUNT
                     * 11 Invalid ORDER_CURRENCY
                     */
                    $debugData['response [ERR]'] = $helper->__('Could not capture payment. %s', $arrResponse[2]);
                    $exception = $debugData['response [ERR]'];
                } else { // successfull request
                    $payment->setIsTransactionPending(1) // wait for IPN to confirm complete order
                            ->setIsTransactionClosed(0);
                }
            }
        } else {
            $debugData['response [ERR]'] = $error;
            $exception = $helper->__('Could not capture payment.');
        }
        
        $this->_debug($debugData);
        
        if (strlen($exception)) {
            Mage::throwException($exception);
        }
        
        return $returnValue;
    }
    
    
    
    /**
     * Fetch transaction info. Performs an IOS request.
     * 
     * @Override
     * @param Mage_Payment_Model_Info $payment
     * @param string $transactionId
     * @return array
     * @throws Mage_Core_Exception
     */
    public function fetchTransactionInfo(Mage_Payment_Model_Info $payment, $transactionId)
    {
        $returnValue = parent::fetchTransactionInfo($payment, $transactionId);
        if (!$payment->getOrder()->getId()) {
            return $returnValue;
        }
        
        $postData = array(
            'MERCHANT' => $this->getConfig()->getMerchantCode(),
            'REFNOEXT' => $payment->getOrder()->getIncrementId(),
        );
        $postData['HASH'] = Mage::helper('innobyte_payu_lite')->hmacMd5($this->_getK(), $postData);
        
        $debugData = array(
            'method'  => __METHOD__,
            'request' => $postData,
        );
        
        $error = array();
        $response = $this->_makeApiCall($this->getConfig()->getIosUrl(), http_build_query($postData), $error);
        if (is_object($response)) {
            $debugData['response'] = $response->getBody();
            try {
                $xml = new SimpleXMLElement($response->getBody());
                $result = $xml->xpath('/Order/REFNO');
                if (count($result)) {
                    $returnValue['REFNO'] = strval($result[0]);
                }
                $result = $xml->xpath('/Order/ORDER_STATUS');
                if (count($result)) {
                    $returnValue['ORDER_STATUS'] = strval($result[0]);
                }
                /* set additional info based on status */
                if ($returnValue['ORDER_STATUS'] == 'FRAUD') {
                    $payment->setIsTransactionPending(1)
                            ->setIsFraudDetected(1);
                } elseif (in_array($returnValue['ORDER_STATUS'], $this->_successfullStatuses)) {
                    $payment->setIsTransactionApproved(1);
                } elseif (in_array($returnValue['ORDER_STATUS'], $this->_deniedStatuses)) {
                    $payment->setIsTransactionDenied(1);
                }
                $result = $xml->xpath('/Order/PAYMETHOD');
                if (count($result)) {
                    $returnValue['PAYMETHOD'] = strval($result[0]);
                }
            } catch (Exception $e) {
                $debugData['response [ERR]'] = $e->getMessage();
            }
        } else {
            $debugData['response [ERR]'] = $error;
        }
        
        $this->_debug($debugData);
        
        return $returnValue;
    }
    
    
    
    /**
     * Make an API call.
     * 
     * @param string     $url        Url to make request to.
     * @param string     $postData   Data to send. (request body)
     * @param array      $error      An array containing error info, if any occurred;
     *                               it has 2 keys 'code' and 'msg'; (optional)
     * @param string     $method     Method uses when sending request (POST|GET|PUT|DELETE...) (optional, default POST)
     * @return Zend_Http_Response|null NULL if an error occurred - check $error, Zend_Http_Response object otherwise.
     */
    protected function _makeApiCall($url, $postData, &$error = null, $method = Zend_Http_Client::POST)
    {
        $config = array(
            'adapter' => $this->getConfig()->getMakeApiCallAdapter(),
            'timeout' => $this->getConfig()->getMakeApiCallTimeout(),
        );
        try {
            $webClient = new Zend_Http_Client();
            $webClient->setUri($url)
                      ->setConfig($config)
                      ->setMethod($method)
                      ->setRawData($postData);
            
            $response = $webClient->request();
            if ($response->isError()) {
                $error['code'] = $response->getStatus();
                $error['msg'] = $response->getMessage();
                return null;
            }
        } catch (Exception $e) {
            $error['code'] = $e->getCode();
            $error['msg'] = $e->getMessage();
            return null;
        }
        return $response;
    }
    
    
    
    /**
     * Check whether payment method can be used.
     * 
     * @Override
     * @param Mage_Sales_Model_Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $returnValue = parent::isAvailable($quote);
        if ($quote != null) {
            if ($quote->getBaseGrandTotal() <= 0) {
                return false;
            }
        } elseif ($this->getOrder()->getId()) {
            if ($this->getOrder()->getBaseGrandTotal() <= 0) {
                return false;
            }
        } 
        if (!Mage::helper('innobyte_payu_lite')->isModuleOutputEnabled()) {
            return false;
        }
        
        return $returnValue;
    }
    
    
    
    /**
     * Performs an IRN request.
     * 
     * @param   Varien_Object   $payment
     * @param   double          $amount
     * @param   string          $context    The context from which this method is called (void|cancel|refund)
     * @throws  Mage_Core_Exception
     */
    protected function _irn(Varien_Object $payment, $amount, $context)
    {
        $helper = Mage::helper('innobyte_payu_lite');
        if ($amount <= 0.00) {
            Mage::throwException($helper->__('Invalid amount to %s.', $helper->__($context)));
        }
        $postData = array(
            'MERCHANT'       => $this->getConfig()->getMerchantCode(),
            'ORDER_REF'      => str_replace(array('-capture', '-refund', '-void'), '', $payment->getLastTransId()),
            'ORDER_AMOUNT'   => $amount,
            'ORDER_CURRENCY' => $this->getOrder()->getBaseCurrencyCode(),
            'IRN_DATE'       => Mage::getModel('core/date')->date('Y-m-d H:i:s'),
        );
        $postData['ORDER_HASH'] = $helper->hmacMd5($this->_getK(), $postData);
        
        $debugData = array(
            'method'  => __METHOD__,
            'request' => $postData,
            'context' => $context,
        );
        $exception = '';
        
        $error = array();
        $response = $this->_makeApiCall($this->getConfig()->getIrnUrl(), http_build_query($postData), $error);
        if (is_object($response)) {
            $debugData['response'] = $response->getBody();
            $arrResponse = explode('|', strip_tags($response->getBody()));
            if (count($arrResponse) >= 5) {
                $hash = $arrResponse[4]; // ORDER_HASH
                unset($arrResponse[4]);
                $hashCheck = Mage::helper('innobyte_payu_lite')->hmacMd5($this->_getK(), $arrResponse);
                if ($hash != $hashCheck) {
                    $debugData['response [ERR]'] = $helper->__('Could not %s the payment. Hash check failed.', $helper->__($context));
                    $exception = $debugData['response [ERR]'];
                } elseif ($arrResponse[1] != 1) { // RESPONSE_CODE
                    /*
                     * IRN response code messages
                     * 1 OK
                     * 2 ORDER_REF missing or incorrect
                     * 3 ORDER_AMOUNT missing or incorrect
                     * 4 ORDER_CURRENCY is missing or incorrect
                     * 5 IRN_DATE is not in the correct format
                     * 6 Error cancelling order
                     * 7 Order already cancelled
                     * 8 Unknown error
                     * 9 Invalid ORDER_REF
                     * 10 Invalid ORDER_AMOUNT
                     * 11 Invalid ORDER_CURRENCY
                     */
                    $debugData['response [ERR]'] = $helper->__('Could not %s the payment. %s', $helper->__($context), $arrResponse[2]);
                    $exception = $debugData['response [ERR]'];
                }
            }
        } else {
            $debugData['response [ERR]'] = $error;
            $exception = $helper->__('Could not %s the payment.', $helper->__($context));
        }
        
        $this->_debug($debugData);
        
        if (strlen($exception)) {
            Mage::throwException($exception);
        }
    }
    
    
    
    /**
     * Shortcut for config::getTransactionKey
     * 
     * @return  string  The transaction key.
     */
    protected function _getK()
    {
        return $this->getConfig()->getTransactionKey();
    }
}
