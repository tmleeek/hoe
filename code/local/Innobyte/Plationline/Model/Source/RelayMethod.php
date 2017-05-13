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
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Plationline Payment Action Dropdown source
 */
class Innobyte_Plationline_Model_Source_RelayMethod
{
    /**
     * Prepare payment action list as optional array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Innobyte_Plationline_Model_Api::RELAY_PTOR, 'label' => Mage::helper('plationline')->__(Innobyte_Plationline_Model_Api::RELAY_PTOR)),
            array('value' => Innobyte_Plationline_Model_Api::RELAY_POST_S2S_PO_PAGE, 'label' => Mage::helper('plationline')->__(Innobyte_Plationline_Model_Api::RELAY_POST_S2S_PO_PAGE)),
            array('value' => Innobyte_Plationline_Model_Api::RELAY_POST_S2S_MT_PAGE, 'label' => Mage::helper('plationline')->__(Innobyte_Plationline_Model_Api::RELAY_POST_S2S_MT_PAGE)),
            array('value' => Innobyte_Plationline_Model_Api::RELAY_SOAP_PO_PAGE, 'label' => Mage::helper('plationline')->__(Innobyte_Plationline_Model_Api::RELAY_SOAP_PO_PAGE)),
            array('value' => Innobyte_Plationline_Model_Api::RELAY_SOAP_MT_PAGE, 'label' => Mage::helper('plationline')->__(Innobyte_Plationline_Model_Api::RELAY_SOAP_MT_PAGE)),
        );
    }
}
