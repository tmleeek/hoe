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


class AW_Affiliate_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @test
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function testStrToTime($dataProvider)
    {
        Mage::getSingleton('core/locale')->setLocale($dataProvider['locale']);

        $subject = Mage::helper('awaffiliate')->strToTime($dataProvider['date']);
        $subjectDate = date(AW_Affiliate_Model_Resource_Campaign::MYSQL_DATE_FORMAT, $subject);

        $expected = $this->expected($dataProvider['test_id']);

        $this->assertEquals(
            $expected->getDate(),
            $subjectDate
        );
    }

}
