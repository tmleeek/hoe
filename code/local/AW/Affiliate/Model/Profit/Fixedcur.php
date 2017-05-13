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


class AW_Affiliate_Model_Profit_Fixedcur extends Varien_Object implements AW_Affiliate_Model_Profit_Interface
{
    protected $_rate = null;

    protected function _construct()
    {
        parent::_construct();
    }

    public function getRate()
    {
        return $this->_rate;
    }

    public function getProfitAmount($attractionAmount)
    {
        $amount = $this->_rate * $attractionAmount;
        return $amount;
    }

    public function setRateSettings($rateSettings)
    {
        if (array_key_exists('profit_rate_cur', $rateSettings)) {
            $this->_rate = $rateSettings['profit_rate_cur'];
            unset($rateSettings['profit_rate_cur']);
        }
        $this->setData('rate_settings', $rateSettings);
        return true;
    }

}
