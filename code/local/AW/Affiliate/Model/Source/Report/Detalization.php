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


class AW_Affiliate_Model_Source_Report_Detalization extends AW_Affiliate_Model_Source_Abstract
{
    const DAY = 'day';
    const MONTH = 'month';
    const YEAR = 'year';

    const DAY_LABEL = 'Day';
    const MONTH_LABEL = 'Month';
    const YEAR_LABEL = 'Year';

    public function toOptionArray()
    {
        $helper = $this->_getHelper();
        return array(
            array('value' => self::DAY, 'label' => $helper->__(self::DAY_LABEL)),
            array('value' => self::MONTH, 'label' => $helper->__(self::MONTH_LABEL)),
            array('value' => self::YEAR, 'label' => $helper->__(self::YEAR_LABEL)),
        );
    }
}
