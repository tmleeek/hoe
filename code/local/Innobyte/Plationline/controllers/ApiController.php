<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Innobyte
 * @package     Innobyte_Plationline
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Plationline Api Controller
 */
require_once 'lib/PlatiOnline/po5.php';
class Innobyte_Plationline_ApiController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;
    private $itsn = false;
    protected $paymentAction = false;
    private $log = "plationline.log";
    private $auth_response = "";
    public $transid;
    public $orderid;

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get singleton with Checkout by Plationline Api
     *
     * @return Innobyte_Plationline_Model_Api
     */
    protected function _getApi()
    {
        return Mage::getSingleton('plationline/api');
    }

    /**
     * Return order instance loaded by increment id'
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        if (empty($this->_order)) {
            $orderId = (isset($this->auth_response['F_ORDER_NUMBER'])) ? $this->auth_response['F_ORDER_NUMBER'] : $this->orderid;
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($orderId);
        }
        return $this->_order;
    }

    /**
     * Validation of incoming data
     *
     * @return bool
     */
    protected function _validatePlationlineData()
    {
        if ($this->_getApi()->getDebug()) {
            $debug = Mage::getModel('plationline/api_debug')
                ->setDir('in')
                ->setUrl($this->getRequest()->getPathInfo())
                ->setData('data', http_build_query($this->getRequest()->getParams()))
                ->save();
        }

        $relay_method = Mage::getStoreConfig('payment/plationline/relay_method');

        $params = $this->getRequest()->getParams();

        $po = new PO5();
        // RSA Private ITSN [Merchant side]:
        $po->setRSAKeyDecrypt(Mage::getStoreConfig('payment/plationline/rsa_private_itsn'));
        //IV ITSN:
        $po->setIVITSN(Mage::getStoreConfig('payment/plationline/iv_itsn'));

        if ($relay_method == 'SOAP_PO_PAGE' || $relay_method == 'SOAP_MT_PAGE') {
            $soap_xml = file_get_contents("php://input");
            $soap_parsed = $po->parse_soap_response($soap_xml);
            $authorization_response = $po->auth_response($soap_parsed['PO_RELAY_REPONSE']['F_RELAY_MESSAGE'], $soap_parsed['PO_RELAY_REPONSE']['F_CRYPT_MESSAGE']);
            $X_RESPONSE_CODE = $authorization_response['PO_AUTH_RESPONSE']['X_RESPONSE_CODE'];
        } else {
            $authorization_response = $po->auth_response($_POST['F_Relay_Message'], $_POST['F_Crypt_Message']);
            $X_RESPONSE_CODE = $authorization_response['PO_AUTH_RESPONSE']['X_RESPONSE_CODE'];
        }

        $this->auth_response = $authorization_response['PO_AUTH_RESPONSE'];

        $debug = Mage::getModel('plationline/api_debug')
            ->setDir('in')
            ->setUrl($this->getRequest()->getPathInfo())
            ->setData('data', serialize($authorization_response))
            ->save();

        $orderId = $authorization_response['PO_AUTH_RESPONSE']['F_ORDER_NUMBER'];
        $response_code = $authorization_response['PO_AUTH_RESPONSE']['X_RESPONSE_CODE'];
        $response_text = $authorization_response['PO_AUTH_RESPONSE']['X_RESPONSE_REASON_TEXT'];
        $currency = $authorization_response['PO_AUTH_RESPONSE']['F_CURRENCY'];
        $amount = $authorization_response['PO_AUTH_RESPONSE']['F_AMOUNT'];

        $order = $this->_getOrder();
        if (!$order->getId()) {
            $error = Mage::helper('plationline')->__('Order is not valid');
            Mage::log($orderId . ' not valid', null, 'plationline.log');
        }

        if (isset($error)) {
            $this->_getCheckout()->addError($error);
            return false;
        }

        return true;
    }

    /**
     * Load place from layout to make POST
     */
    public function placeformAction()
    {
        $lastIncrementId = $this->_getCheckout()->getLastRealOrderId();
        if ($lastIncrementId) {
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($lastIncrementId);
            if ($order->getId()) {
                $order->setState(Innobyte_Plationline_Model_Api::PENDING_PO_STATUS, Innobyte_Plationline_Model_Api::PENDING_PO_STATUS, Mage::helper('plationline')->__('Start processing'));
                Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , placeformAction State: ' . Innobyte_Plationline_Model_Api::PENDING_PO_STATUS, null, 'statelog.log');
                $order->save();

                if ($this->_getApi()->getDebug()) {
                    $debug = Mage::getModel('plationline/api_debug')
                        ->setDir('out')
                        ->setUrl($this->getRequest()->getPathInfo())
                        ->setData('data', http_build_query($this->_getApi()->getFormFields($order)))
                        ->save();
                }
            }
        }

        $this->_getCheckout()->getQuote()->setIsActive(false)->save();
        $this->_getCheckout()->setPlationlineQuoteId($this->_getCheckout()->getQuoteId());
        $this->_getCheckout()->setPlationlineLastSuccessQuoteId($this->_getCheckout()->getLastSuccessQuoteId());
        $this->_getCheckout()->clear();

        $this->_getApi()->getFormFields($order);

        // $this->loadLayout();
        // $this->renderLayout();
    }

    /**
     * Display our pay page, need to plationline payment with external pay page mode
     */
    public function paypageAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Action to control postback data
     *
     */
    public function postBackAction()
    {
        if (!$this->_validatePlationlineData()) {
            $this->getResponse()->setHeader("Status", "404 Not Found");
            return false;
        }

        $this->_plationlineProcess();
    }

    /**
     * Made offline plationline data processing, depending of incoming statuses
     */
    protected function _plationlineProcess()
    {
        $status = $this->auth_response['X_RESPONSE_CODE'];
        $response = true;

        switch ($status) {
            case Innobyte_Plationline_Model_Api::PO_AUTHORIZED:
                $this->_acceptProcess();
                break;
            case Innobyte_Plationline_Model_Api::PO_AUTH_REFUSED:
                $this->_declineProcess();
                break;
            case Innobyte_Plationline_Model_Api::PO_PAYMENT_ONHOLD:
                $this->_exceptionProcess();
                break;
            case Innobyte_Plationline_Model_Api::PO_AUTH_ERROR:
                $this->_exceptionProcess(true);
                break;
            default:
                //all unknown transaction will accept as exceptional
                Mage::log('Unknown X_RESPONSE_CODE for order #' . $this->_getOrder()->getIncrementId(), null, 'plationline.log');
                $this->_exceptionProcess();
        }
    }

    /**
     * when payment gateway accept the payment, it will land to here
     * need to change order status as processed plationline
     * update transaction id
     *
     */
    public function acceptAction()
    {
        if (!$this->_validatePlationlineData()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_plationlineProcess();
    }

    /**
     * Process success action by accept url
     */
    protected function _acceptProcess()
    {
        $params = $this->auth_response;
        $order = $this->_getOrder();
        $transid = $this->getTransId();

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());
//         $this->_prepareCCInfo($order, $params);
        $order->getPayment()->setTransactionId($transid);

        try {
            if ($this->_getApi()->getPaymentAction() == Innobyte_Plationline_Model_Api::PO_AUTHORIZE_ACTION ||
                $this->paymentAction == Innobyte_Plationline_Model_Api::PO_AUTHORIZE_ACTION) {
                $this->_processAuthorize();
            } else {
                //$this->_processDirectSale();
                die("Payment action problem");
            }
        } catch (Exception $e) {
            if ($this->itsn) {
                // Mage::log('Order ' . $order->getIncrementId() . ' can\'t save (accept process) ' . $e->getMessage(), null, 'plationline.log');
                $this->_getCheckout()->addError(Mage::helper('plationline')->__("Order can't save"));
                $this->_redirect('checkout/cart');
            }
            return;
        }
    }

    /**
     * Process Configured Payment Actions: Authorized, Default operation
     * just place order
     */
    protected function _processAuthorize()
    {
        $relay_method = Mage::getStoreConfig('payment/plationline/relay_method');
        $params = $this->auth_response;
        $transid = $this->getTransId();

        $order = $this->_getOrder();
        $payment = $order->getPayment();
        try {
            $payment->setLastTransId($transid);
            $payment->setCcTransId($transid);
            $payment->setTransactionId($transid);
            $payment->setIsTransactionClosed(0);

            $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, "");
            $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $params);
            $transaction->save();

            // already processed, ignore itsn so it doesn't add another invoice
            if ($this->itsn && $order->getStatus() == Innobyte_Plationline_Model_Api::PROCESSED_PO_STATUS) {
                return false;
            }

            $payment->capture(null);

            $order->addStatusToHistory(Innobyte_Plationline_Model_Api::PROCESSED_PO_STATUS, 'PlatiOnline confirmation via IPN code: ' . $transid . PHP_EOL);
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, Innobyte_Plationline_Model_Api::PROCESSED_PO_STATUS, Mage::helper('plationline')->__('Authorized by Plati Online'));
            Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , processAuthorize State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');
            $order->sendNewOrderEmail();
            $order->setAuthorized(true);
            $order->save();

        } catch (Exception $e) {
            Mage::log('Order ' . $order->getIncrementId() . ' can\'t save (processAuthorize) ' . $e->getMessage(), null, 'plationline.log');
            if ($relay_method == 'PTOR') {
                $this->_getCheckout()->addError(Mage::helper('plationline')->__("Order can't be saved"));
                $this->_redirect('checkout/cart');
            } else {
                header('User-Agent:Mozilla/5.0 (Plati Online Relay Response Service)');
                header('PO_Transaction_Response_Processing: retry');
            }

            return;
        }

        if (!$this->itsn) {
            // prepare response data for POST_ or SOAP_ MT
            $response_data = array(
                'order' => $order,
                'transid' => $transid,
                'po_data' => $params,
            );

            switch ($relay_method) {
                case 'POST_S2S_PO_PAGE':
                    {
                        header('User-Agent:Mozilla/5.0 (Plati Online Relay Response Service)');
                        header('PO_Transaction_Response_Processing: true');
                        return;
                    }
                case 'POST_S2S_MT_PAGE':
                    {
                        header('User-Agent:Mozilla/5.0 (Plati Online Relay Response Service)');
                        header('PO_Transaction_Response_Processing: true');
                        echo $this->createResponseBlock($response_data);
                        return;
                    }
                case 'SOAP_PO_PAGE':
                    {
                        header('User-Agent:Mozilla/5.0 (Plati Online Relay Response Service)');
                        header('PO_Transaction_Response_Processing: true');
                        return;
                    }
                case 'SOAP_MT_PAGE':
                    {
                        header('User-Agent:Mozilla/5.0 (Plati Online Relay Response Service)');
                        header('PO_Transaction_Response_Processing: true');
                        echo $this->createResponseBlock($response_data);
                        return;
                    }
                case 'PTOR':
                    {
                        $this->_redirect('checkout/onepage/success');
                    }
            }
        }

        return; // ???
    }

    protected function createResponseBlock($data = array(), $success = true)
    {
        $this->loadLayout();

        $template = ($success == true) ? 'plationline/mt_response.phtml' : 'plationline/mt_response_error.phtml';

        $block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'mt_response',
            array('template' => $template)
        )->setData($data);

        //Release layout stream... lol... sounds fancy
        return $block->toHtml();
    }

    /**
     * We get some CC info from plati online, so we must save it
     *
     * @param Mage_Sales_Model_Order $order
     * @param array $ccInfo
     *
     * @return Innobyte_Plationline_ApiController
     */
    protected function _prepareCCInfo($order, $ccInfo)
    {
        $order->getPayment()->setCcOwner($ccInfo['CN']);
        $order->getPayment()->setCcNumberEnc($ccInfo['CARDNO']);
        $order->getPayment()->setCcLast4(substr($ccInfo['CARDNO'], -4));
        $order->getPayment()->setCcExpMonth(substr($ccInfo['ED'], 0, 2));
        $order->getPayment()->setCcExpYear(substr($ccInfo['ED'], 2, 2));
        return $this;
    }

    /**
     * the payment result is uncertain
     * exception status can be 52 or 92
     * need to change order status as processing
     * update transaction id
     *
     */
    public function exceptionAction()
    {
        if (!$this->_validatePlationlineData()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_exceptionProcess();
    }

    /**
     * Process exception action by exception url
     * @param bool $error true only if Plati Online has returned an error code
     */
    public function _exceptionProcess($error = false)
    {
        $params = $this->auth_response;
        $order = $this->_getOrder();
        $transid = $this->getTransId();

        $exception = '';
        $exception_temp = explode('^', $params['X_RESPONSE_REASON_TEXT']);
        $exception = array_shift($exception_temp);

        if (!empty($exception)) {
            try {
                $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());
                $this->_getCheckout()->addError($exception);
                $order->getPayment()->setLastTransId($transid);
                $status = $error ? Innobyte_Plationline_Model_Api::ERROR_PO_STATUS : Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $exception);
                Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , exceptionProcess State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');
                $order->addStatusToHistory($status, $exception);
                $order->save();
            } catch (Exception $e) {
                Mage::log('Order ' . $order->getIncrementId() . ' can not be save for system reason ' . $e->getMessage(), null, 'plationline.log');
                $this->_getCheckout()->addError($exception);
            }
        } else {
            $this->_getCheckout()->addError(Mage::helper('plationline')->__('Exception not defined'));
        }

        if (!$this->itsn) {
            $relay_method = Mage::getStoreConfig('payment/plationline/relay_method');
            if ($relay_method != 'PTOR') {
                header('User-Agent:Mozilla/5.0 (Plati Online Relay Response Service)');
                header('PO_Transaction_Response_Processing: true');
                if ($relay_method == 'POST_S2S_MT_PAGE' || $relay_method == 'SOAP_MT_PAGE') {

                    if (empty($exception)) {
                        $exception = $this->__('Exception not defined');
                    }

                    $response_data = array(
                        'order' => $order,
                        'transid' => $transid,
                        'exception' => $exception,
                        'po_data' => $params,
                    );

                    echo $this->createResponseBlock($response_data, false);
                }

                return;
            }

            $this->_redirect('checkout/onepage/success');
        }

    }

    public function getTransId()
    {
        $params = $this->auth_response;
        return (!$this->itsn) ? $params['X_TRANS_ID'] : $this->transid;
    }

    /**
     * when payment got decline
     * need to change order status to cancelled
     * take the user back to shopping cart
     *
     */
    public function declineAction()
    {
        if (!$this->_validatePlationlineData()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_getCheckout()->setQuoteId($this->_getCheckout()->getPlationlineQuoteId());
        $this->_declineProcess();
        return $this;
    }

    /**
     * Process decline action by plati online decline url
     */
    protected function _declineProcess()
    {
        $relay_method = Mage::getStoreConfig('payment/plationline/relay_method');
        $order = $this->_getOrder();
        $params = $this->auth_response;
        // $status = $params['X_RESPONSE_CODE'];
        $exception = '';
        $exception = array_shift(explode('^', $params['X_RESPONSE_REASON_TEXT']));

        $status = Innobyte_Plationline_Model_Api::DECLINE_PO_STATUS;
        if (isset($params['X_RESPONSE_REASON_TEXT']) && !empty($params['X_RESPONSE_REASON_TEXT'])) {
            $reason = explode('^', $params['X_RESPONSE_REASON_TEXT']);
            $reason = array_shift($reason);
        } else {
            $reason = 'ITSN';
        }

        $comment = Mage::helper('plationline')->__('Order declined by Plati Online ("' . $reason . '")');
        $reason = file_get_contents(BP . DS . 'app' . DS . 'design' . DS . 'frontend' . DS . 'default' . DS . 'default' . DS . 'template' . DS . 'plationline' . DS . 'fail.phtml');
        $this->_getCheckout()->addError(sprintf(Mage::helper('plationline')->__('Payment transaction has been declined: %s'), $reason));

        $this->_cancelOrder($status, $comment);

        if (!$this->itsn) {
            if ($relay_method != 'PTOR') {
                header('User-Agent:Mozilla/5.0 (Plati Online Relay Response Service)');
                header('PO_Transaction_Response_Processing: true');

                if (empty($exception)) {
                    $exception = 'Exception not defined';
                }

                if ($relay_method == 'POST_S2S_MT_PAGE' || $relay_method == 'SOAP_MT_PAGE') {
                    $response_data = array(
                        'order' => $order,
                        'transid' => $transid,
                        'exception' => $exception,
                        'po_data' => $params,
                    );

                    echo $this->createResponseBlock($response_data, false);
                }

                return;
            }
        }

    }

    //ITSN onhold
    protected function _onholdProcess()
    {
        $params = $this->auth_response;
        $transid = $this->getTransId();
        $order = $this->_getOrder();

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $order->getPayment()->setTransactionId($transid);

        $status = Innobyte_Plationline_Model_Api::ONHOLD_PO_STATUS;
        $comment = Mage::helper('plationline')->__('Order needs additional verification (on-hold) by Plati Online ( TransID: ' . $transid . ' )');

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $comment);
        Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , onholdProcess State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');

        $payment = $order->getPayment();

        $payment->setLastTransId($transid);
        $payment->setCcTransId($transid);
        $payment->setTransactionId($transid);
        $payment->setIsTransactionClosed(1);

        $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, "");
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $params);
        $transaction->save();

        $order->save();
    }

    //ITSN in curs de incasare
    protected function _pendingsettleProcess()
    {
        $params = $this->auth_response;
        $order = $this->_getOrder();
        $transid = $this->getTransId();

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $order->getPayment()->setTransactionId($transid);

        $status = Innobyte_Plationline_Model_Api::PENDING_SETTLED_PO_STATUS;
        $comment = Mage::helper('plationline')->__('Order pending settlement by Plati Online ( TransID: ' . $transid . ' )');

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $comment);
        Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , pendingsettleProcess State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');

        $payment = $order->getPayment();

        $payment->setLastTransId($transid);
        $payment->setCcTransId($transid);
        $payment->setTransactionId($transid);
        $payment->setIsTransactionClosed(1);

        $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, "");
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $params);
        $transaction->save();

        $order->save();
    }

    //ITSN in curs de creditare
    protected function _pendingcreditProcess()
    {
        $params = $this->auth_response;
        $order = $this->_getOrder();
        $transid = $this->getTransId();

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $order->getPayment()->setTransactionId($transid);

        $status = Innobyte_Plationline_Model_Api::PENDING_CREDITED_PO_STATUS;
        $comment = Mage::helper('plationline')->__('Pending credit by Plati Online ( TransID: ' . $transid . ' )');

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $comment);
        Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , pendingcreditProcess State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');

        $payment = $order->getPayment();

        $payment->setLastTransId($transid);
        $payment->setCcTransId($transid);
        $payment->setTransactionId($transid);
        $payment->setIsTransactionClosed(1);

        $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, "");
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $params);
        $transaction->save();

        $order->save();
    }

    //ITSN creditata
    protected function _creditProcess()
    {
        $params = $this->auth_response;
        $order = $this->_getOrder();
        $transid = $this->getTransId();

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $order->getPayment()->setTransactionId($transid);

        $status = Innobyte_Plationline_Model_Api::CREDITED_PO_STATUS;
        $comment = Mage::helper('plationline')->__('Credited by Plati Online ( TransID: ' . $transid . ' )');

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $comment);
        Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , creditProcess State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');

        $payment = $order->getPayment();

        $payment->setLastTransId($transid);
        $payment->setCcTransId($transid);
        $payment->setTransactionId($transid);
        $payment->setIsTransactionClosed(1);

        $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, "");
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $params);
        $transaction->save();

        $order->save();
    }

    //ITSN refuz la plata
    protected function _paymentRefusedProcess()
    {
        $params = $this->auth_response;
        $order = $this->_getOrder();
        $transid = $this->getTransId();

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $order->getPayment()->setTransactionId($transid);

        $status = Innobyte_Plationline_Model_Api::PAYMENT_REFUSED_PO_STATUS;
        $comment = Mage::helper('plationline')->__('Payment refused - Plati Online ( TransID: ' . $transid . ' )');

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $comment);
        Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , paymentRefusedProcess State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');

        $payment = $order->getPayment();

        $payment->setLastTransId($transid);
        $payment->setCcTransId($transid);
        $payment->setTransactionId($transid);
        $payment->setIsTransactionClosed(1);

        $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, "");
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $params);
        $transaction->save();

        $order->save();
    }

    //ITSN incasata
    protected function _settleProcess()
    {
        $params = $this->auth_response;
        $order = $this->_getOrder();
        $transid = $this->getTransId();

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $order->getPayment()->setTransactionId($transid);

        $status = Innobyte_Plationline_Model_Api::SETTLED_PO_STATUS;
        $comment = Mage::helper('plationline')->__('Order settled by Plati Online ( TransID: ' . $transid . ' )');

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $comment);
        Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , settleProcess State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');

        $payment = $order->getPayment();

        $payment->setLastTransId($transid);
        $payment->setCcTransId($transid);
        $payment->setTransactionId($transid);
        $payment->setIsTransactionClosed(1);

        $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, "");
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $params);
        $transaction->save();

        $order->save();
    }

    //ITSN in curs de anulare
    protected function _pendingcancelProcess()
    {
        $params = $this->auth_response;
        $order = $this->_getOrder();
        $transid = $this->getTransId();

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $order->getPayment()->setTransactionId($transid);

        $status = Innobyte_Plationline_Model_Api::PENDING_CANCEL_PO_STATUS;
        $comment = Mage::helper('plationline')->__('Pending cancel by Plati Online ( TransID: ' . $transid . ' )');

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $comment);
        Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , pendingcancelProcess State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');

        $payment = $order->getPayment();

        $payment->setLastTransId($transid);
        $payment->setCcTransId($transid);
        $payment->setTransactionId($transid);
        $payment->setIsTransactionClosed(1);

        $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, "");
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $params);
        $transaction->save();

        $order->save();
    }

    //ITSN expirata 30 zile
    protected function _expired30Process()
    {
        $params = $this->auth_response;
        $order = $this->_getOrder();
        $transid = $this->getTransId();

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $order->getPayment()->setTransactionId($transid);

        $status = Innobyte_Plationline_Model_Api::EXPIRED30_PO_STATUS;
        $comment = Mage::helper('plationline')->__('Transaction Expired 30 days Plati Online ( TransID: ' . $transid . ' )');

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $comment);
        Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , expired30Process State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');

        $payment = $order->getPayment();

        $payment->setLastTransId($transid);
        $payment->setCcTransId($transid);
        $payment->setTransactionId($transid);
        $payment->setIsTransactionClosed(0);

        $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, "");
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $params);
        $transaction->save();

        $order->save();
    }

    //ITSN eroare
    protected function _errorProcess()
    {
        $params = $this->auth_response;
        $order = $this->_getOrder();
        $transid = $this->getTransId();

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $order->getPayment()->setTransactionId($transid);

        $status = Innobyte_Plationline_Model_Api::ERROR_PO_STATUS;
        $comment = Mage::helper('plationline')->__('Error processing transaction Plati Online ( TransID: ' . $transid . ' )');

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $comment);
        Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , errorProcess State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');

        $payment = $order->getPayment();

        $payment->setLastTransId($transid);
        $payment->setCcTransId($transid);
        $payment->setTransactionId($transid);
        $payment->setIsTransactionClosed(0);

        $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, "");
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $params);
        $transaction->save();

        $order->save();
    }

    /**
     * when user cancel the payment
     * change order status to cancelled
     * need to rediect user to shopping cart
     *
     * @return Innobyte_Plationline_ApiController
     */
    public function cancelAction()
    {
        if (!$this->_validatePlationlineData()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_getCheckout()->setQuoteId($this->_getCheckout()->getPlationlineQuoteId());
        $this->_cancelProcess();
        return $this;
    }

    /**
     * Process cancel action by cancel url
     *
     * @return Innobyte_Plationline_ApiController
     */
    public function _cancelProcess()
    {
        $status = Innobyte_Plationline_Model_Api::CANCEL_PO_STATUS;
        $comment = Mage::helper('plationline')->__('Order canceled on plati online side');
        $this->_cancelOrder($status, $comment);
        return $this;
    }

    /**
     * Cancel action, used for decline and cancel processes
     *
     * @return Innobyte_Plationline_ApiController
     */
    protected function _cancelOrder($status, $comment = '')
    {
        $order = $this->_getOrder();
        $params = $this->auth_response;
        $transid = $this->getTransId();
        try {
            $order->cancel();
            $order->getPayment()->setLastTransId($transid);
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status, $comment);
            Mage::log('ITSN: ' . ($this->itsn ? '1' : '0') . ' , cancelOrder State: ' . Mage_Sales_Model_Order::STATE_PROCESSING, null, 'statelog.log');
            $order->save();
        } catch (Exception $e) {
            $this->_getCheckout()->addError(Mage::helper('plationline')->__('Order can not be canceled for system reason'));
        }
        if (!$this->itsn) {
            $relay_method = Mage::getStoreConfig('payment/plationline/relay_method');
            if ($relay_method != 'PTOR') {
                header('User-Agent:Mozilla/5.0 (Plati Online Relay Response Service)');
                header('PO_Transaction_Response_Processing: true');
            } else {
                $this->_redirect('checkout/cart');
            }
        }

        return $this;
    }

    /**
     * Set redirect into responce
     *
     * @param   string $path
     * @param   array $arguments
     */
    protected function _redirect($path, $arguments = array())
    {
        $this->getResponse()->setRedirect(Mage::getBaseUrl() . $path);
        return $this;
    }

    public function itsnAction()
    {
        $this->itsn = true;
        //decript ITSN call sent by PlatiOnline
        $po = new PO5();
        // RSA Private ITSN [Merchant side]:
        $po->setRSAKeyDecrypt($this->_getApi()->getConfig()->getRsaPvt());
        //IV ITSN:
        $po->setIVITSN($this->_getApi()->getConfig()->getIVITSN());

        $call_itsn = $po->itsn($_POST['f_itsn_message'], $_POST['f_crypt_message']);

        //set query config
        // RSA Public AUTH [Merchant side]:
        $po->setRSAKeyEncrypt($this->_getApi()->getConfig()->getRsaPub());
        // IV AUTH:
        $po->setIV($this->_getApi()->getConfig()->getIVAuth());

        $po->f_login = $this->_getApi()->getConfig()->getLoginId();

        $f_request['f_website'] = $po->f_login;
        $f_request['f_order_number'] = $call_itsn['PO_ITSN']['F_ORDER_NUMBER'];
        $f_request['x_trans_id'] = $call_itsn['PO_ITSN']['X_TRANS_ID'];

        $raspuns_itsn = $po->query($f_request, 0);

        //receive query response from PlatiOnline
        $query_response = $raspuns_itsn['PO_QUERY_RESPONSE'];

        // in case of error:

        //  <po_query_response>
        //      <po_error_code>1</po_error_code>
        //      <po_error_reason><![CDATA[Invalid request]]></po_error_reason>
        //  </po_query_response>

        if ($query_response['PO_ERROR_CODE'] == 1) {
            die($query_response['PO_ERROR_REASON']);
        } else {
            $tranzaction = $query_response['ORDER']['TRANZACTION'];
            $starefin1 = $tranzaction['STATUS_FIN1']['CODE'];
            $starefin2 = $tranzaction['STATUS_FIN2']['CODE'];

            $vX_TRAN_ID = $tranzaction['X_TRANS_ID'];

            $this->transid = $tranzaction['X_TRANS_ID'];
            $this->orderid = $call_itsn['PO_ITSN']['F_ORDER_NUMBER'];

            $stare1 = '<f_response_code>1</f_response_code>';
            switch ($starefin1) {
                case '13':
                    $starefin = 'In proces de verificare';
                    $this->_onholdProcess();
                    break;
                case '2':
                    $starefin = 'Autorizata';
                    $this->paymentAction = Innobyte_Plationline_Model_Api::PO_AUTHORIZE_ACTION;
                    $this->_acceptProcess();
                    break;
                case '8':
                    $starefin = 'Refuzata';
                    $this->_declineProcess();
                    break;
                case '3':
                    $starefin = 'In curs de incasare';
                    $this->_pendingsettleProcess();
                    break;
                case '5':
                    $starefin = 'Incasata';
                /* Verify X_STARE_FIN2 status*/
                    switch ($rez["X_STARE_FIN2"]) {
                    case '1':
                            $starefin = 'In curs de creditare';
                            $this->_pendingcreditProcess();
                            break;
                    case '2':
                            $starefin = 'Creditata';
                            $this->_creditProcess();
                            break;
                    case '3':
                            $starefin = 'Refuz la plata';
                            $this->_paymentRefusedProcess();
                            break;
                    case '4':
                            $starefin = 'Incasata';
                            $this->_settleProcess();
                            break;
                    }
                    break;
                case '6':
                    $starefin = 'In curs de anulare';
                    $this->_pendingcancelProcess();
                    break;
                case '7':
                    $starefin = 'Anulata';
                    $this->_cancelProcess();
                    break;
                case '9':
                    $starefin = 'Expirata 30 zile';
                    $this->_expired30Process();
                    break;
                case '10':
                    $starefin = 'Eroare';
                    $this->_errorProcess();
                    break;
                case '1':
                    $starefin = 'In curs de autorizare';
                    break;
                default:
                    $stare1 = '<f_response_code>0</f_response_code>';
            }

            /* send ITSN response */
            $raspuns_xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $raspuns_xml .= '<itsn>';
            $raspuns_xml .= '<x_trans_id>' . $vX_TRAN_ID . '</x_trans_id>';
            $raspuns_xml .= '<merchServerStamp>' . date("Y-m-d H:m:s") . '</merchServerStamp>';
            $raspuns_xml .= $stare1;
            $raspuns_xml .= '</itsn>';

            echo $raspuns_xml;
        }
    }

}
