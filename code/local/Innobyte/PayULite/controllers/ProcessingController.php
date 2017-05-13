<?php
/**
 * Processing payment controller.
 *
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_ProcessingController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve singleton of Checkout Session Model.
     * 
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    
    
    /**
     * Place form action. Submits payment to PayU API.
     */
    public function placeformAction()
    {
        try {
            /* save order 's new state */
            $order = Mage::getModel('sales/order')
                ->loadByIncrementId($this->_getCheckout()->getLastRealOrderId());
            if (!$order->getId()) {
                Mage::throwException(Mage::helper('innobyte_payu_lite')->__('No order for processing found.'));
            }
            $order->setState(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage::helper('innobyte_payu_lite')->__('The customer was redirected to PayU.')
            );
            $order->save();
            
            $this->_getCheckout()->setData('payulite_order_increment_id', $order->getIncrementId());
            
            /* deactivate quote */
            $this->_getCheckout()->getQuote()->setIsActive(false)->save();
            $this->_getCheckout()->clear();
            
            /* debug data */
            $methodInstance = $order->getPayment()->getMethodInstance();
            $formFields = $methodInstance->getFormFields()->toArray();
            $methodInstance->debugData(array('Request in ' . __METHOD__ => $formFields));
            
            /* render layout */
            $this->loadLayout();
            $this->renderLayout();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_redirect('checkout/cart', array('_secure' => true));
        }
    }
    
    
    
    /**
     * Timeout exceeded on PayU website.
     */
    public function cancelAction()
    {
        $helper = Mage::helper('innobyte_payu_lite');
        try {
            $order = Mage::getModel('sales/order')
                ->loadByIncrementId($this->_getCheckout()->getData('payulite_order_increment_id'));
            if ($order->getIncrementId() !== $this->getRequest()->getParam('order', '')) {
                Mage::throwException(Mage::helper('innobyte_payu_lite')->__('Invalid order.'));
            }
            $this->_getCheckout()->unsetData('payulite_order_increment_id');
            $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
            
            /* cancel order */
            $order->cancel();
            $order->getStatusHistoryCollection()->getLastItem()->setComment($helper->__('Payment timeout.'));
            $order->save();
            
            /* reactivate quote */
            if ($quote->getId()) {
                $quote->setIsActive(true)->save();
                $this->_getCheckout()->setQuoteId($quote->getId());
            }
            /* debug error */
            $order->getPayment()->getMethodInstance()->debugData(
                'Payment timeout expired for order "' . $order->getIncrementId() . '" in ' . __METHOD__
            );
            Mage::throwException($helper->__('Payment failed. The order has been canceled.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckout()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getCheckout()->addError($helper->__('An error occurred. Please try again later.'));
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart', array('_secure' => true));
    }

    
    
    /**
     * When PayU returns an answer after placeform.
     */
    public function returnAction()
    {
        $helper = Mage::helper('innobyte_payu_lite');
        try {
            $order = Mage::getModel('sales/order')
                ->loadByIncrementId($this->_getCheckout()->getData('payulite_order_increment_id'));
            if ($order->getIncrementId() !== $this->getRequest()->getParam('order', '')) {
                Mage::throwException($helper->__('Invalid order.'));
            }
            $this->_getCheckout()->unsetData('payulite_order_increment_id');
            $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
            
            if (strlen($this->getRequest()->getParam('err', ''))) { // an error occurred
                /* cancel order */
                $order->cancel();
                $order->getStatusHistoryCollection()->getLastItem()->setComment($helper->__('Payment failed.'));
                $order->save();
                /* reactivate quote */
                if ($quote->getId()) {
                    $quote->setIsActive(true)->save();
                    $this->_getCheckout()->setQuoteId($quote->getId());
                }
                /* debug returned error */
                $order->getPayment()->getMethodInstance()->debugData(
                    'PayU returned "' . $this->getRequest()->getParam('err') . '" for order "' . $order->getIncrementId() . '" in ' . __METHOD__
                );
                Mage::throwException($helper->__('Payment failed. The order has been canceled.'));
            } elseif (strlen($this->getRequest()->getParam('ctrl'))) { // success
                $config = $order->getPayment()->getMethodInstance()->getConfig();
                if (is_object($config)) {
                    $hmacArr = array(
                        'SUCCESS_URL' => str_replace('{{ORDER_ID}}', $order->getIncrementId(), $config->getReturnUrl())
                    );
                    if ($this->getRequest()->getParam('ctrl')
                        === Mage::helper('innobyte_payu_lite')->hmacMd5($config->getTransactionKey(), $hmacArr)) {
                        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId())
                                             ->setLastQuoteId($order->getQuoteId())
                                             ->setLastOrderId($order->getId());
                    }
                    
                    /* payment waiting for authorization */
                    $order->getPayment()->setIsTransactionPending(1)->authorize(false, $order->getBaseGrandTotal());
                    $order->save();
                    
                    $this->_redirect('checkout/onepage/success', array('_secure' => true));
                    return;
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckout()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getCheckout()->addError($helper->__('An error occurred. Please try again later.'));
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart', array('_secure' => true));
    }
    
    
    
    /**
     * IPN requests from PayU will come here.
     */
    public function ipnAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }
        
        try {
            $data = $this->getRequest()->getPost();
            $ipnModel = Mage::getModel('innobyte_payu_lite/ipn', $data)->processIpnRequest();
            /* compose return message */
            $ipnPid   = $ipnModel->getRequestData('IPN_PID');
            $ipnPname = $ipnModel->getRequestData('IPN_PNAME');
            $ipnDate  = $ipnModel->getRequestData('IPN_DATE');
            $date     = Mage::getModel('core/date')->date('YmdHis');
            $hash     = $ipnModel->getDataHelper()->hmacMd5(
                $ipnModel->getConfig()->getTransactionKey(),
                array(
                    $ipnPid[0],
                    $ipnPname[0],
                    $ipnDate,
                    $date,
                )
            );
            $this->getResponse()->setBody('<EPAYMENT>' . $date . '|' . $hash . '</EPAYMENT>');
        } catch (Mage_Core_Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody($e->getMessage());
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody(Mage::helper('innobyte_payu_lite')->__('An error occurred.'));
            Mage::logException($e);
        }
    }
}
