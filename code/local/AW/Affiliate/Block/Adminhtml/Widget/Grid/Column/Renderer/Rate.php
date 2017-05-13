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


class AW_Affiliate_Block_Adminhtml_Widget_Grid_Column_Renderer_Rate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        switch ($row->getType()) {
            case AW_Affiliate_Model_Source_Transaction_Profit_Type::ADMIN :
                $html = $this->__('N/A');
                break;
            case AW_Affiliate_Model_Source_Transaction_Profit_Type::CUSTOMER_VISIT :
            case AW_Affiliate_Model_Source_Transaction_Profit_Type::CUSTOMER_PURCHASE :
                $profit = Mage::getModel('awaffiliate/profit')
                    ->loadByCampaignId($row->getData('campaign_id'));
                switch ($profit->getType()) {
                    case AW_Affiliate_Model_Source_Profit_Type::FIXED :
                    case AW_Affiliate_Model_Source_Profit_Type::TIER :
                        $html = $this->_getValue($row)."%";
                        break;
                    case AW_Affiliate_Model_Source_Profit_Type::FIXED_CUR :
                    case AW_Affiliate_Model_Source_Profit_Type::TIER_CUR :
                        $html = Mage::app()->getLocale()
                            ->currency(Mage::helper('awaffiliate')->getDefaultCurrencyCode())
                            ->toCurrency($this->_getValue($row));
                        break;
                    default:
                        $html = $this->_getValue($row);
                }
                break;
            default:
                $html = $this->_getValue($row);
        }
        return $html;
    }

}
