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


class AW_Affiliate_Block_Adminhtml_Campaign_Grid_Column_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{

    /**
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        /** @var AW_Affiliate_Model_Campaign $row */
        $statusId = (int)$this->_getValue($row);
        $activeFrom = new Zend_Date($row->getActiveFrom(), Varien_Date::DATETIME_INTERNAL_FORMAT);
        $activeTo = new Zend_Date($row->getActiveTo(), Varien_Date::DATETIME_INTERNAL_FORMAT);
        $now = new Zend_Date();
        if (
            $row->getActiveTo() !== null
            && $activeTo->compare($now, Zend_Date::DATES) < 0
        ) {
            $row->setStatus(AW_Affiliate_Model_Source_Campaign_Status_Expanded::EXPIRED);
        }
        elseif (
            $row->getActiveFrom() !== null
            && $activeFrom->compare($now, Zend_Date::DATES) > 0
            && $statusId == AW_Affiliate_Model_Source_Campaign_Status::ACTIVE
        ) {
            $row->setStatus(AW_Affiliate_Model_Source_Campaign_Status_Expanded::PENDING);
        }
        return parent::render($row);
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {
        $result = parent::renderExport($row);
        return strip_tags($result);
    }
}
