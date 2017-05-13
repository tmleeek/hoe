<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Affiliate
 * @version    1.1.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function testInvoicePay($dataProvider)
    {
        $this->_registerCanCaptureStub();
        $invoice = Mage::getModel('sales/order_invoice');

        if ($dataProvider['invoice_id'] > 0) {
            $invoice->load($dataProvider['invoice_id']);
            $order = $invoice->getOrder();
        }
        elseif ($dataProvider['order_id'] > 0) {
            $order = Mage::getModel('sales/order')->load($dataProvider['order_id']);
            $payment = Mage::getModel('sales/order_payment');
            $payment->setMethod("checkmo");
            $order->addPayment($payment);

            if(!$order->canInvoice()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
            }
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            if (!$invoice->getTotalQty()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without prods.'));
            }
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
            $invoice->register();
        }
        $invoice->save();

        $subject = Mage::getModel('awaffiliate/transaction_profit')
            ->getCollection()
            ->addFieldToFilter('linked_entity_type', array('eq' => 'invoice_item'))
            ->addFieldToFilter('linked_entity_id', array('eq' => $order->getIncrementId()));
        $profit = $subject->getFirstItem();

        $expected = $this->expected($dataProvider['test_id']);

        $this->assertEquals(
            $expected->getSize(),
            $subject->getSize()
        );

        $this->assertEquals(
            $expected->getAmount(),
            $profit->getAmount()
        );
    }

    /**
     * $invoice->canCapture() will always return true
     * to avoid unnecessary check in invoice and payment
     */
    private function _registerCanCaptureStub() {
        $mock = $this->getModelMock(
            'sales/order_invoice',
            array(
                'canCapture'
            )
        );

        $mock
            ->expects($this->any())
            ->method('canCapture')
            ->will($this->returnValue(true));

        $this->replaceByMock(
            'model',
            'sales/order_invoice',
            $mock
        );
    }

    /**
     * $payment->hasForcedState() will always return false
     * not really needed, because the manual payment we want to use
     * in testInvoicePay() does not have forced_state.
     * Both this and _registerPayStub() methods may become handy
     * for online payments.
     */
    private function _registerHasForcedStateStub() {
        $mock = $this->getModelMock(
            'sales/order_payment',
            array(
                'hasForcedState'
            )
        );

        $mock
            ->expects($this->any())
            ->method('hasForcedState')
            ->will($this->returnValue(false));

        $this->replaceByMock(
            'model',
            'sales/order_payment',
            $mock
        );
    }

    /**
     * $payment->pay() will not do anything
     * also not needed, because the offline payment we want to use
     * in testInvoicePay() does not capture any payment online
     */
    private function _registerPayStub() {
        $mock = $this->getModelMock(
            'sales/order_payment',
            array(
                'pay'
            )
        );

        $mock
            ->expects($this->any())
            ->method('pay')
            ->will($this->returnValue(true));

        $this->replaceByMock(
            'model',
            'sales/order_payment',
            $mock
        );
    }

}
