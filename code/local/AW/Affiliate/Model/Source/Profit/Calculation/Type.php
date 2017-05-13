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


class AW_Affiliate_Model_Source_Profit_Calculation_Type extends AW_Affiliate_Model_Source_Abstract
{
    const LAST_MONTH = 'last_month';
    const ALL_TIME = 'all_time';

    const LAST_MONTH_LABEL = 'Last month';
    const ALL_TIME_LABEL = 'All time';

    public function toOptionArray()
    {
        $helper = $this->_getHelper();
        return array(
            array('value' => self::LAST_MONTH, 'label' => $helper->__(self::LAST_MONTH_LABEL)),
            array('value' => self::ALL_TIME, 'label' => $helper->__(self::ALL_TIME_LABEL))
        );
    }
}
