<?php
/**
 * Processes IPN requests.
 * 
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Model_Ipn
{
    /**
     * @param Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * @var Innobyte_PayULite_Model_Config
     */
    protected $_config = null;
    
    /**
     * @var Innobyte_PayULite_Helper_Data The innobyte_payu_lite data helper.
     */
    protected $_dataHelper = null;
    
    /**
     * @var array   IPN request data.
     */
    protected $_request = array();
    
    /**
     * @var const string    Ipn order statuses.
     */ 
    const PAYU_IPN_STATUS_REVERSED = 'REVERSED';
    const PAYU_IPN_STATUS_REFUND   = 'REFUND';
    const PAYU_IPN_STATUS_COMPLETE = 'COMPLETE';
    const PAYU_IPN_STATUS_PAYMENT_AUTHORIZED = 'PAYMENT_AUTHORIZED';
    const PAYU_IPN_STATUS_PAYMENT_RECEIVED   = 'PAYMENT_RECEIVED';
    const PAYU_IPN_STATUS_CASH     = 'CASH';
    const PAYU_IPN_STATUS_TEST     = 'TEST';    
    
    
    
    /**
     * Constructor; initializes stuffs.
     * 
     * @param   array $arrRequestData
     * @throws  Mage_Core_Exception
     */
    public function __construct(array $arrRequestData)
    {
        $this->_request = $arrRequestData;
        $this->_init();
    }
    
    
    
    /**
     * Performs initializations, checks.
     * 
     * @return  Innobyte_PayULite_Model_Ipn
     * @throws  Mage_Core_Exception
     */
    protected function _init()
    {
        /* checks request data is set */
        if (!count($this->getRequestData())) {
            Mage::throwException($this->getDataHelper()->__('Request does not contain any params.'));
        }
        /* check order ID */
        if (!strlen($this->getRequestData('REFNOEXT'))) {
            Mage::throwException($this->getDataHelper()->__('Missing or invalid order ID.'));
        }
        /* load order for further validation */
        if (!$this->_getOrder()->getId()) {
            Mage::throwException($this->getDataHelper()->__('Order "%s" not found.', $this->getRequestData('REFNOEXT')));
        }
        /* check PayU Lite payment method */
        if (!($this->_getOrder()->getPayment()->getMethodInstance() instanceof Innobyte_PayULite_Model_Method_Abstract)) {
            Mage::throwException($this->getDataHelper()->__('Invalid payment method.'));
        }
        /* init config */
        $this->_config = $this->_getOrder()->getPayment()->getMethodInstance()->getConfig();
        /* validate ips */
        if ($this->getConfig()->isIpnUrlAccessRestricted()) {
            if (!$this->_checkIp()) {
                Mage::throwException($this->getDataHelper()->__('Access denied for IP %s.', Mage::helper('core/http')->getRemoteAddr()));
            }
        }
        /* validate hash */
        $signature = $this->getRequestData('HASH');
        unset($this->_request['HASH']);
        $signatureCheck = $this->getDataHelper()
            ->hmacMd5($this->getConfig()->getTransactionKey(), $this->getRequestData());
        if ($signature != $signatureCheck) {
            Mage::throwException($this->getDataHelper()->__('Invalid signature.'));
        }
        return $this;
    }
    
    
    
    /**
     * IPN request data getter.
     * 
     * @param   string  $key    Optional key to get value for.
     * @return  mixed
     */
    public function getRequestData($key = null)
    {
        if (null === $key) {
            return $this->_request;
        }
        return array_key_exists($key, $this->_request) ? $this->_request[$key] : null;
    }

    
    
    /**
     * Getter method for payment config.
     * 
     * @return Innobyte_PayULite_Model_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }
    
    
    
    /**
     * Getter method for order; implements lazy instantiation.
     * 
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order')
                ->loadByIncrementId($this->getRequestData('REFNOEXT'));
        }
        return $this->_order;
    }
    
    
    
    /**
     * Getter method for data helper; implements lazy instantiation.
     * 
     * @return Innobyte_PayULite_Helper_Data
     */
    public function getDataHelper()
    {
        if (is_null($this->_dataHelper)) {
            $this->_dataHelper = Mage::helper('innobyte_payu_lite');
        }
        return $this->_dataHelper;
    }
    
    
    
    /**
     * Process IPN data.
     * 
     * @return  Innobyte_PayULite_Model_Ipn
     * @throws  Exception
     */
    public function processIpnRequest()
    {
        /* debug data to process */
        $this->_getOrder()->getPayment()->getMethodInstance()
            ->debugData(array('Received request in ' . __METHOD__ => $this->getRequestData()));
        
        /* perform different actions based on status received */
        switch ($this->getRequestData('ORDERSTATUS')) {
            case self::PAYU_IPN_STATUS_TEST: // for TEST orders the IDN and IRN calls are not allowed.
                $this->_registerPaymentAuthorization();
                break;
            case self::PAYU_IPN_STATUS_PAYMENT_RECEIVED:
            case self::PAYU_IPN_STATUS_COMPLETE:
                $this->_registerPaymentCapture(true);
                break;
            case self::PAYU_IPN_STATUS_PAYMENT_AUTHORIZED:
                $this->_registerPaymentAuthorization();
                if ($this->getConfig()->getPaymentAction() == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE
                    && $this->_getOrder()->getPayment()->canCapture()) {
                    $this->_registerPaymentCapture(false);
                    $this->_getOrder()->getPayment()->getMethodInstance()->capture(
                        $this->_getOrder()->getPayment(),
                        $this->getRequestData('IPN_TOTALGENERAL')
                    );
                }
                break;
            case self::PAYU_IPN_STATUS_REFUND:
                $this->_registerPaymentRefund();
                break;
            case self::PAYU_IPN_STATUS_REVERSED:
                $this->_registerPaymentReversal();
                break;
            default:
                $this->_getOrder()->getPayment()->getMethodInstance()
                    ->debugData('Unknown IPN status: ' . $this->getRequestData('ORDERSTATUS'));
        }
        return $this;
    }
    
    

    /**
     * Register authorized payment.
     * 
     * @return  Innobyte_PayULite_Model_Ipn
     */
    protected function _registerPaymentAuthorization()
    {
        /* register authorization */
        $this->_getOrder()->getPayment()
            ->setPreparedMessage($this->_createIpnComment(''))
            ->setTransactionId($this->getRequestData('REFNO'))
            ->setIsTransactionClosed(0)
            ->registerAuthorizationNotification($this->getRequestData('IPN_TOTALGENERAL'));
        /* notify customer */
        if (!$this->_getOrder()->getEmailSent()) {
            // 1.8.1 bugfix - order:sendNewOrderEmail loads again the order and resets properties,
            // but order status and state were modified in payment::registerAuthorizationNotification
            // and thus they get reset and the new state/status are lost...
            $orderState = $this->_getOrder()->getState();
            $orderStatus = $this->_getOrder()->getStatus();
            $this->_getOrder()
                ->sendNewOrderEmail()
                ->setData('state', $orderState)
                ->setData('status', $orderStatus);
        }
        $this->_getOrder()->save();
        return $this;
    }
    
    
    
    /**
     * Process completed|completing payment.
     * 
     * @param   boolean     $capturingFlag      Capture is or not completed.
     * @return  Innobyte_PayULite_Model_Ipn
     */
    protected function _registerPaymentCapture($captured = false)
    {
        /* register capture */
        $payment = $this->_getOrder()->getPayment();          
        $payment->setParentTransactionId($this->getRequestData('REFNO'))
                ->setPreparedMessage($this->_createIpnComment(''));
        if (!$captured) {
            $payment->setIsTransactionClosed(0)
                    ->setIsTransactionPending(1); // until payment is confirmed from gateway (PAYU_IPN_STATUS_COMPLETE)
        } else {
            $payment->setIsTransactionClosed(0)
                    ->setIsTransactionPending(0);
        }
        $payment->registerCaptureNotification($this->getRequestData('IPN_TOTALGENERAL'));
        $this->_getOrder()->save();
        /* notify customer */
        if ($invoice = $payment->getCreatedInvoice()) {         
            $invoice->sendEmail();
            $this->_getOrder()
                ->addStatusHistoryComment(
                    $this->getDataHelper()->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
                )
                ->setIsCustomerNotified(true)
                ->save();
        }
        return $this;
    }
    
    
    
    /**
     * Process a refund.
     * 
     * @return  Innobyte_PayULite_Model_Ipn
     */
    protected function _registerPaymentRefund()
    {
        /* register refund */
        $this->_getOrder()->getPayment()
            ->setPreparedMessage($this->_createIpnComment(''))
            ->setParentTransactionId($this->getRequestData('REFNO'))
            ->registerRefundNotification(-1 * $this->getRequestData('IPN_TOTALGENERAL'));
        $this->_getOrder()->save();
        /* notify customer */
        if ($creditmemo = $this->_getOrder()->getPayment()->getCreatedCreditmemo()) {      
            $creditmemo->sendEmail();
            $this->_getOrder()
                 ->addStatusHistoryComment(
                     $this->getDataHelper()->__('Notified customer about creditmemo #%s.', $creditmemo->getIncrementId())
                 )
                 ->setIsCustomerNotified(true)
                 ->save();
        }
        return $this;
    }
    
    
    
    /**
     * Process a chargeback.
     * 
     * @return  Innobyte_PayULite_Model_Ipn
     */
    protected function _registerPaymentReversal()
    {
        $this->_getOrder()->getPayment()
            ->setPreparedMessage($this->_createIpnComment(''))
            ->setParentTransactionId($this->getRequestData('REFNO'))
            ->registerVoidNotification($this->getRequestData('IPN_TOTALGENERAL'));
        $this->_getOrder()->save();
        return $this;
    }

    
    
    /**
     * Generate an "IPN" comment with additional explanation.
     * Returns the generated comment or order status history object
     *
     * @param string $comment
     * @param bool $addToHistory
     * @return string|Mage_Sales_Model_Order_Status_History
     */
    protected function _createIpnComment($comment = '', $addToHistory = false)
    {
        $message = $this->getDataHelper()->__('IPN "%s". ', $this->getRequestData('ORDERSTATUS'));
        if ($comment) {
            $message .= ' ' . $comment;
        }
        if ($addToHistory) {
            $message = $this->_order->addStatusHistoryComment($message);
            $message->setIsCustomerNotified(null);
        }
        return $message;
    }
    
    
    
    /**
     * Checks for allowed ips.
     * 
     * @return boolean  TRUE if found an allowed IP, FALSE otherwise.
     */
    protected function _checkIp()
    {
        $ips = array();
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ips[] = $_SERVER['REMOTE_ADDR'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = array_merge($ips, explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        }
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ips = array_merge($ips, explode(',', $_SERVER['HTTP_X_REAL_IP']));
        }
        $ips = array_unique(array_map('trim', $ips));
        foreach ($this->getConfig()->getAllowedIpsForIpn() as $allowedIp) {
            foreach ($ips as $ip) {
                if ($allowedIp === $ip) {
                    return true;
                }
            }
        }
        return false;
    }
}
