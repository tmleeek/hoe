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
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Plationline payment method model
 */

//if (!class_exists('PO3', false)) {
//  Mage::log("loading clspo.php from ".__FILE__);
//  require_once(BP . DS . 'app/code/local' . DS . 'Innobyte/Plationline' . DS . 'clspo.php');
//}

require_once Mage::getBaseDir('base') . '/lib/PlatiOnline/po5.php';

class Innobyte_Plationline_Model_Api extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'plationline';
    protected $_formBlockType = 'plationline/form';
    protected $_infoBlockType = 'plationline/info';
    protected $_config = null;
    protected $_order = null;

    /**
     * Availability options
     */
    protected $_isGateway = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;

    /* Plationline payment process statuses */
    const PROCESSING_PO_STATUS = 'processing_plationline';
    const PROCESSED_PO_STATUS = 'processed_plationline';
    const PENDING_PO_STATUS = 'pending_plationline';
    const CANCEL_PO_STATUS = 'cancel_plationline';
    const DECLINE_PO_STATUS = 'decline_plationline';
    const ERROR_PO_STATUS = 'error_plationline';
    const ONHOLD_PO_STATUS = 'onhold_plationline';
    const SETTLED_PO_STATUS = 'settled_plationline';
    const CREDITED_PO_STATUS = 'credited_plationline';
    const PAYMENT_REFUSED_PO_STATUS = 'payment_refused_plationline';
    const EXPIRED30_PO_STATUS = 'expired30_plationline';
    const PENDING_SETTLED_PO_STATUS = 'pending_settled_plationline';
    const PENDING_CREDITED_PO_STATUS = 'pending_credited_plationline';
    const PENDING_CANCEL_PO_STATUS = 'pending_cancel_plationline';

    /* Plationline response statuses */
    const PO_AUTHORIZED = 2;
    const PO_AUTH_ERROR = 10;
    const PO_AUTH_REFUSED = 8;
    const PO_PAYMENT_ONHOLD = 13;

    /* Layout of the payment method */
    const PMLIST_HORISONTAL_LEFT = 0;
    const PMLIST_HORISONTAL = 1;
    const PMLIST_VERTICAL = 2;

    /* plationline payment action constant*/
    const PO_AUTHORIZE_ACTION = 2;
    const PO_INSTALLMENTS_ACTION = 20;

    const RELAY_PTOR = 'PTOR';
    const RELAY_POST_S2S_PO_PAGE = 'POST_S2S_PO_PAGE';
    const RELAY_POST_S2S_MT_PAGE = 'POST_S2S_MT_PAGE';
    const RELAY_SOAP_PO_PAGE = 'SOAP_PO_PAGE';
    const RELAY_SOAP_MT_PAGE = 'SOAP_MT_PAGE';

    /**
     * Init Plationline Api instance, detup default values
     *
     * @return Innobyte_Plationline_Model_Api
     */
    public function __construct()
    {
        $this->_config = Mage::getSingleton('plationline/config');
        return $this;
    }

    /**
     * Return plationline config instance
     *
     * @return Innobyte_Plationline_Model_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Return debug flag by storeConfig
     *
     * @param int storeId
     * @return bool
     */
    public function getDebug($storeId = null)
    {
        return $this->getConfig()->getConfigData('debug_flag', $storeId);
    }

    /**
     * Flag witch prevents automatic invoice creation
     *
     * @return bool
     */
    public function isInitializeNeeded()
    {
        return true;
    }

    /**
     * Redirect url to plationline submit form
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('plationline/api/placeform', array('_secure' => true));
    }

    /**
     * Return payment_action value from config area
     *
     * @return string
     */
    public function getPaymentAction()
    {
        return $this->getConfig()->getConfigData('payment_action');
    }

    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = $this->getInfoInstance()->getOrder();
        }
        return $this->_order;
    }

    /**
     * Rrepare params array to send it to gateway page via POST
     *
     * @param Mage_Sales_Model_Order
     * @return array
     */
    public function getFormFields($order)
    {

        if (empty($order)) {
            if (!($order = $this->getOrder())) {
                return array();
            }
        }

        $send_sms = Mage::getStoreConfig('payment/plationline/send_customer_sms');
        $send_sms = isset($send_sms) ? Mage::getStoreConfig('payment/plationline/send_customer_sms') : 0;

        $f_request = array();

        $f_request['f_order_number'] = $order->getIncrementId();
        $f_request['f_amount'] = number_format($order->getBaseGrandTotal(), 2, '.', '');
        $f_request['f_currency'] = Mage::app()->getStore()->getBaseCurrencyCode();
        //$f_request['f_auth_minutes'] = 20; // 0 - waiting forever, 20 - default (in minutes)
        $f_request['f_language'] = Mage::getStoreConfig('general/country/default');

        $customer_info = array();

        $billingAddress = $order->getBillingAddress();
        //contact
        $customer_info['contact']['f_email'] = $order->getCustomerEmail();
        if (strlen($billingAddress->getTelephone()) >= 10) {
            $customer_info['contact']['f_phone'] = $billingAddress->getTelephone();
            $customer_info['contact']['f_mobile_number'] = $billingAddress->getTelephone();
        }
        $customer_info['contact']['f_send_sms'] = $send_sms;
        $customer_info['contact']['f_first_name'] = $billingAddress->getFirstname();
        $customer_info['contact']['f_last_name'] = $billingAddress->getLastname();
        //$customer_info['contact']['f_middle_name']     = '';

        $regionModel = Mage::getModel("directory/region")->load($billingAddress->getRegion_id());
        $region = $regionModel->getName();
        //invoice
        if ($billingAddress->getCompany()) {
            $customer_info['invoice']['f_company'] = $billingAddress->getCompany();
        }

        //$customer_info['invoice']['f_cui']             = '111111';
        //$customer_info['invoice']['f_reg_com']         = 'J55/99/2000';
        //$customer_info['invoice']['f_cnp']             = '9999999999999';
        $customer_info['invoice']['f_zip'] = $billingAddress->getPostcode();
        $customer_info['invoice']['f_country'] = $billingAddress->getCountry();
        $customer_info['invoice']['f_state'] = $region;
        $customer_info['invoice']['f_city'] = $billingAddress->getCity();
        $customer_info['invoice']['f_address'] = str_replace("\n", ' ', $billingAddress->getStreet(-1));

        $f_request['customer_info'] = $customer_info;

        $shipping_info = array();

        $shipping_info['same_info_as'] = '1'; // 0 - different info, 1- same info as customer_info

        $shipping = $order->getShippingAddress();
        if (isset($shipping) && !empty($shipping)) {
            $shipping_info['same_info_as'] = '0';

            $regionModel = Mage::getModel("directory/region")->load($shipping->getRegion_id());
            $region = $regionModel->getName();

            //contact
            $shipping_info['contact']['f_email'] = $order->getCustomerEmail();
            if (strlen($shipping->getTelephone()) >= 10) {
                $shipping_info['contact']['f_phone'] = $shipping->getTelephone();
                $shipping_info['contact']['f_mobile_number'] = $shipping->getTelephone();
            }
            $shipping_info['contact']['f_send_sms'] = $send_sms;
            $shipping_info['contact']['f_first_name'] = $shipping->getFirstname();
            $shipping_info['contact']['f_last_name'] = $shipping->getLastname();
            //$shipping_info['contact']['f_middle_name']     = '';

            //address
            if ($shipping->getCompany()) {
                $shipping_info['address']['f_company'] = $shipping->getCompany();
            }

            $shipping_info['address']['f_zip'] = $shipping->getPostcode();
            $shipping_info['address']['f_country'] = $shipping->getCountry();
            $shipping_info['address']['f_state'] = $region;
            $shipping_info['address']['f_city'] = $shipping->getCity();
            $shipping_info['address']['f_address'] = str_replace("\n", ' ', $shipping->getStreet(-1));
        }

        $f_request['shipping_info'] = $shipping_info;
        $card_holder_info = array();
        $card_holder_info['same_info_as'] = 0; // 0 - different info, 1- same info as customer_info
        $f_request['card_holder_info'] = $card_holder_info;

        $transaction_relay_response = array();

        $transaction_relay_response['f_relay_response_url'] = Mage::app()->getStore()->getBaseUrl() . "/plationline/api/postBack";

        $transaction_relay_response['f_relay_method'] = Mage::getStoreConfig('payment/plationline/relay_method'); // PTOR, POST_S2S_PO_PAGE, POST_S2S_MT_PAGE, SOAP_PO_PAGE, SOAP_MT_PAGE
        $transaction_relay_response['f_post_declined'] = 1; // Valoarea = 1    (default value; sistemul PO trimite rezultatul la f_relay_response_url prin metoda f_relay_method)  Valoarea = 0    (systemul PO trimite rezultatul doar pentru tranzactiile "Autorizate" si "In curs de verificare" la <f_relay_response_url> prin metoda <f_relay_method>)
        $transaction_relay_response['f_relay_handshake'] = 1; // default 0
        $f_request['transaction_relay_response'] = $transaction_relay_response;

        $f_request['f_order_cart'] = array();

        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $myitem = array();
            $myitem['prodid'] = $item->getId();
            $myitem['name'] = $item->getName();
            $myitem['description'] = $item->getDescription();
            $myitem['qty'] = $item->getQtyOrdered();
            $myitem['itemprice'] = (float) $item->getPrice();
            $myitem['vat'] = (float) $item->getTaxAmount();
            $myitem['stamp'] = date('Y-m-d');
            $myitem['prodtype_id'] = 0;

            $f_request['f_order_cart'][] = $myitem;
        }

        $coupon1 = array();
        if (abs($order->getDiscountAmount()) > 0) {
            //coupon 1
            $coupon1['key'] = '0';
            $coupon1['value'] = abs($order->getDiscountAmount());
            $coupon1['percent'] = 0;
            $coupon1['workingname'] = Mage::helper('plationline')->__('Discount');
            $coupon1['type'] = 0;
            $coupon1['scop'] = 1;
            $coupon1['vat'] = 0;
            $f_request['f_order_cart']['coupon1'] = $coupon1;
        }

        $coupon2 = array();
        if (abs($order->getGiftCardsAmountUsed()) > 0) {
            //coupon 2
            $coupon2['key'] = '0';
            $coupon2['value'] = abs($order->getGiftCardsAmountUsed());
            $coupon2['percent'] = 0;
            $coupon2['workingname'] = Mage::helper('plationline')->__('Discount');
            $coupon2['type'] = 0;
            $coupon2['scop'] = 1;
            $coupon2['vat'] = 0;
            $f_request['f_order_cart']['coupon2'] = $coupon2;
        }

        // declare $f_request['f_order_cart']['coupon1'], $f_request['f_order_cart']['coupon2']; we index the field ['coupon'] to have different names in array and to avoid overwriting the values
        // the array to xml method takes care of this case by looking for "coupon" substring

        $shipping = array();
        if ($order->getShippingAmount() > 0) {
            //shipping
            $shipping['name'] = Mage::helper('sales')->__('Shipping & Handling');
            $shipping['price'] = number_format($order->getShippingAmount(), 2, '.', '');
            $shipping['pimg'] = 0;
            $shipping['vat'] = number_format($order->getShippingTaxAmount(), 2, '.', '');
        }

        $f_request['f_order_cart']['shipping'] = $shipping;
//        $f_request['f_order_string'] = 'test';
	$f_request['f_order_string'] = 'Comanda nr. '.$order->getIncrementId().' pe site-ul '.Mage::app()->getStore()->getBaseUrl();


        $po = new PO5();
        //f_login and RSA key will be saved in config
        $po->f_login = Mage::getStoreConfig('payment/plationline/login_id');


//        $f_request['f_website'] = $po->f_login;
	$f_request['f_website'] = str_replace('www.', '',$_SERVER['SERVER_NAME']);

        // RSA Public AUTH [Merchant side]:
        $po->setRSAKeyEncrypt(Mage::getStoreConfig('payment/plationline/rsa_public_auth'));

        // IV AUTH:
        $po->setIV(Mage::getStoreConfig('payment/plationline/iv_auth'));
        //end f_login and RSA key will be saved in config

        // test mode: 0 - disabled, 1 - enabled
        $po->test_mode = Mage::getStoreConfig('payment/plationline/test_flag');

        //plationline autorizare
        $po->auth($f_request, 2);
    }

    /**
     * to translate UTF 8 to ISO 8859-1
     * Plationline system is only compatible with iso-8859-1 and does not (yet) fully support the utf-8
     */
    protected function _translate($text)
    {
        return htmlentities(iconv("UTF-8", "ISO-8859-1", $text));
        //      return $text;
    }

    /**
     * Get Plationline Payment Action value
     *
     * @param string
     * @return string
     */
    protected function _getPlationlinePaymentOperation()
    {
        $value = $this->getPaymentAction();
        if ($value == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE) {
            $value = Innobyte_Plationline_Model_Api::PO_AUTHORIZE_ACTION;
        } elseif ($value == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE) {
            $value = Innobyte_Plationline_Model_Api::PO_AUTHORIZE_CAPTURE_ACTION;
        }
        return $value;
    }

    /**
     * get xml formated order description
     *
     * @param Mage_Sales_Model_Order
     * @return string
     */
    protected function _getOrderString($order)
    {
        $invoiceDesc = '<start_string>';

        $type = $order->getPayment()->getMethod();
        //print $order->getTaxAmount();
        //$taxInfo = $order->getFullTaxInfo();
        //print Mage::helper('checkout')->formatPrice($taxInfo['amount']);
        //print_r($taxInfo);

        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            //print $item->getTaxAmount().":".get_class($item)."<br/>";

            $invoiceDesc .= '<item>';
            $invoiceDesc .= '<ProdID>' . $item->getID() . '</ProdID>';
            $invoiceDesc .= '<qty>' . $item->getQtyOrdered() . '</qty>';
            $invoiceDesc .= '<itemprice>' . $item->getPrice() . '</itemprice>';
            $invoiceDesc .= '<name>' . htmlentities($item->getName()) . '</name>';
            $invoiceDesc .= '<period></period>';
            $invoiceDesc .= '<rec_id></rec_id>';
            $invoiceDesc .= '<description></description>';
            $invoiceDesc .= '<pimg></pimg>';
            $invoiceDesc .= '<rec_price>' . ($item->getPrice() * $item->getQtyOrdered()) . '</rec_price>';
            $invoiceDesc .= '<vat>' . $item->getTaxAmount() . '</vat>';
            $invoiceDesc .= '<lang_id>' . Mage::app()->getLocale()->getLocaleCode() . '</lang_id>';
            $invoiceDesc .= '<stamp>' . date("m") . "/" . date("d") . "/" . date("Y") . " " . date("h:i:s a") . '</stamp>';
            $invoiceDesc .= '<on_stoc>1</on_stoc>';
            $invoiceDesc .= '<prodtype_id>1</prodtype_id>';
            $invoiceDesc .= '<categ_id>1</categ_id>';
            $invoiceDesc .= '<merchLoginID>' . $this->getConfig()->getLoginId() . '</merchLoginID>';
            $invoiceDesc .= '</item>';
        }

        if ($order->getShippingAmount() > 0) {
            $invoiceDesc .= '<shipping>';
            $invoiceDesc .= '<type>' . Mage::helper('sales')->__('Shipping & Handling') . '</type>';
            $invoiceDesc .= '<price>' . number_format($order->getShippingAmount(), 2, '.', '') . '</price>';
            $invoiceDesc .= '<vat>' . number_format($order->getShippingTaxAmount(), 2, '.', '') . '</vat>';
            $invoiceDesc .= '<pimg></pimg>';
            $invoiceDesc .= '</shipping>';

        }

        if (abs($order->getDiscountAmount()) > 0) {
            $invoiceDesc .= '<coupon>';
            $invoiceDesc .= '<key>0</key>';
            $invoiceDesc .= '<value>' . abs($order->getDiscountAmount()) . '</value>';
            $invoiceDesc .= '<percent>0</percent>';
            $invoiceDesc .= '<workingname>' . Mage::helper('plationline')->__('Discount') . '</workingname>';
            $invoiceDesc .= '<type>0</type>';
            $invoiceDesc .= '<scop>1</scop>';
            $invoiceDesc .= '<vat>0</vat>';
            $invoiceDesc .= '</coupon>';
        }

        if (abs($order->getGiftCardsAmountUsed()) > 0) {
            $invoiceDesc .= '<coupon>';
            $invoiceDesc .= '<key>0</key>';
            $invoiceDesc .= '<value>' . abs($order->getGiftCardsAmountUsed()) . '</value>';
            $invoiceDesc .= '<percent>0</percent>';
            $invoiceDesc .= '<workingname>' . Mage::helper('plationline')->__('Gift card') . '</workingname>';
            $invoiceDesc .= '<type>0</type>';
            $invoiceDesc .= '<scop>1</scop>';
            $invoiceDesc .= '<vat>0</vat>';
            $invoiceDesc .= '</coupon>';
        }

        if (abs($order->getCustomerBalanceAmount()) > 0) {
            $invoiceDesc .= '<coupon>';
            $invoiceDesc .= '<key>0</key>';
            $invoiceDesc .= '<value>' . abs($order->getCustomerBalanceAmount()) . '</value>';
            $invoiceDesc .= '<percent>0</percent>';
            $invoiceDesc .= '<workingname>' . Mage::helper('plationline')->__('Credit magazin') . '</workingname>';
            $invoiceDesc .= '<type>0</type>';
            $invoiceDesc .= '<scop>1</scop>';
            $invoiceDesc .= '<vat>0</vat>';
            $invoiceDesc .= '</coupon>';
        }

        if (abs($order->getRewardCurrencyAmount()) > 0) {
            $invoiceDesc .= '<coupon>';
            $invoiceDesc .= '<key>0</key>';
            $invoiceDesc .= '<value>' . abs($order->getRewardCurrencyAmount()) . '</value>';
            $invoiceDesc .= '<percent>0</percent>';
            $invoiceDesc .= '<workingname>' . Mage::helper('plationline')->__('Puncte fidelitate') . '</workingname>';
            $invoiceDesc .= '<type>0</type>';
            $invoiceDesc .= '<scop>1</scop>';
            $invoiceDesc .= '<vat>0</vat>';
            $invoiceDesc .= '</coupon>';
        }

        $invoiceDesc .= '</start_string>';
        return $invoiceDesc;
    }

    public function settle($transid)
    {
        $po = new PO5();

        //set up settle config
        // RSA Public AUTH [Merchant side]:
        $po->setRSAKeyEncrypt($this->getConfig()->getRsaPub());
        // IV AUTH:
        $po->setIV($this->getConfig()->getIVAuth());

        $po->f_login = $this->getConfig()->getLoginId();

        $f_request['f_website'] = $po->f_login;
        $f_request['f_order_number'] = $this->getOrder()->getIncrementId();
        $f_request['x_trans_id'] = $transid;
        $f_request['f_shipping_company'] = '';
        $f_request['f_awb'] = '';

        $response_settle = $po->settle($f_request, 3);

        if ($response_settle['PO_SETTLE_RESPONSE']['PO_ERROR_CODE'] == 1) {
            return false; // die('<b>ERROR</b>: ' . $response_settle['PO_SETTLE_RESPONSE']['PO_ERROR_REASON']);
        } else {
            switch ($response_settle['PO_SETTLE_RESPONSE']['X_RESPONSE_CODE']) {
                case '3':
                    // it worked
                    Mage::log('Transaction settled: ' . $transid, null, 'plationline.log');
                    return true;
                    break;
                case '10':
                    Mage::log('Errors occured, transaction NOT SETTLED', null, 'plationline.log');
                    return false;
                    break;
            }
        }
    }

    public function refund(Varien_Object $payment, $amount)
    {
        $po = new PO5();
        $transid = $payment->getLastTransId();

        @$this->settle($transid);

        //set up refund config

        // RSA Public AUTH [Merchant side]:
        $po->setRSAKeyEncrypt($this->getConfig()->getRsaPub());
        // IV AUTH:
        $po->setIV($this->getConfig()->getIVAuth());

        $po->f_login = $this->getConfig()->getLoginId();

        // what do?

        $f_request['f_website'] = $po->f_login;
        $f_request['f_order_number'] = $this->getOrder()->getIncrementId();
        $f_request['f_amount'] = (float) $amount; // needed amount
        $f_request['x_trans_id'] = (int) $transid;

        $response_refund = $po->refund($f_request, 1);

        if ($response_refund['PO_REFUND_RESPONSE']['PO_ERROR_CODE'] == 1) {
            Mage::log($response_refund, null, "testlog.log");
            // die('<b>ERROR</b>: ' . $response_refund['PO_REFUND_RESPONSE']['PO_ERROR_REASON']);
        } else {
            switch ($response_refund['PO_REFUND_RESPONSE']['X_RESPONSE_CODE']) {
                case '1':
                    Mage::log(Mage::helper('sales')->__('The amount of ') . $response_refund['PO_REFUND_RESPONSE']['F_AMOUNT'] . Mage::helper('sales')->__(' successfully refunded'), null, 'plationline.log');
                    break;
                case '10':
                    Mage::log(Mage::helper('sales')->__('Errors occured, transaction ' . $transid . ' NOT REFUNDED'), null, 'plationline.log');
                    break;
            }
        }

        return $this;
    }

    public function processCreditmemo($creditmemo, $payment)
    {
        return parent::processCreditmemo($creditmemo, $payment);
    }
}
