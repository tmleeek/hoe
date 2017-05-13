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


class AW_Affiliate_Test_Model_Transaction_Profit extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     *
     * @todo: it currently takes campaign_id, traffic_id and other data from data provider,
     * but they also exists in the fixture in awaffiliate/client_history table. Either make the test
     * respect the fixture data, or remove this table (and other related) from the fixture.
     * Not yet removed only because there is a possibility that the fixture data
     * may be required for other tests.
     */
    public function testCreateTransaction($dataProvider)
    {
        $subject = Mage::getModel('awaffiliate/transaction_profit');

        Mage::getConfig()
            ->saveConfig(
                AW_Affiliate_Helper_Config::PATH_CONFIG_CONSIDER_TAX,
                $dataProvider['consider_tax']
            )
            ->cleanCache();

        $invoice = Mage::getModel('sales/order_invoice')->load($dataProvider['invoice_id']);
        $order = $invoice->getOrder();
        $d = array(
            'campaign_id' => $dataProvider['campaign_id'],
            'affiliate_id' => $dataProvider['affiliate_id'],
            'traffic_id' => $dataProvider['traffic_id'],
            'client_id' => $dataProvider['client_id'],
            'linked_entity_type' => AW_Affiliate_Model_Source_Transaction_Profit_Linked::INVOICE_ITEM,
            // 'linked_entity_type' => $dataProvider['entity_type'],
            'linked_entity_id' => $order->getIncrementId(),
            'linked_entity_invoice' => $invoice,
            'linked_entity_order' => $order,
            'created_at' => Mage::getModel('core/date')->gmtDate(),
            'type' => AW_Affiliate_Model_Source_Transaction_Profit_Type::CUSTOMER_PURCHASE
            // 'type' => $dataProvider['type']
        );

        $subject->setData($d);
        $subject->createTransaction();

        $expected = $this->expected($dataProvider['test_id']);

        $this->assertEquals(
            $expected->getAmount(),
            $subject->getAmount()
        );
    }
}
