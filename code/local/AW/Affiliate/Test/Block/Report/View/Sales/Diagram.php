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


class AW_Affiliate_Test_Block_Report_View_Sales_Diagram extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function testGetChartRows($dataProvider)
    {
        # make sure we don't save report items in session, as it tends to lift the test scope to controllers
        $this->_registerSaveInSessionStub();

        $postData = $dataProvider['request'];

        # for the report to work, we need affiliate registered
        $affiliate = Mage::getModel('awaffiliate/affiliate');
        $affiliate->load($dataProvider['affiliate_id']);
        Mage::register('current_affiliate', $affiliate);

        $block = Mage::app()->getLayout()->createBlock('awaffiliate/report_view_sales_diagram');

        if (is_array($dataProvider['columns'])) foreach($dataProvider['columns'] as $column) {
            $block->addChartColumn($column['name'], $column['type'], $column['label']);
        }
        $block->setItems($dataProvider['report']);

        $columns = $block->getChartColumns();
        $rows = $block->getChartRows();
        $oneRow = array_pop($rows);

        $expected = $this->expected($dataProvider['test_id']);

        $this->assertEquals(
            $expected->getCount(),
            count($oneRow)
        );

        $this->assertEquals(
            count($oneRow),
            count($columns)
        );

        if (!empty($rows)) foreach ($rows as $row) {
            $this->assertEquals(
                count($row),
                count($oneRow)
            );
        }

        Mage::unregister('current_affiliate');
    }

    private function _registerSaveInSessionStub() {
        $saveItemsInSessionMock = $this->getBlockMock(
            'awaffiliate/report_view_sales',
            array(
                '_saveItemsInSession'
            )
        );

        $saveItemsInSessionMock
            ->expects($this->any())
            ->method('_saveItemsInSession')
            ->will($this->returnValue(null));

        $this->replaceByMock(
            'block',
            'awaffiliate/report_view_sales',
            $saveItemsInSessionMock
        );
    }

}
