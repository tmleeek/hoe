<?php
/**
 * Custom fieldset renderer.
 * 
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Block_Adminhtml_System_Config_Fieldset_Payment
        extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{       
    /**
     * Return header comment part of html for fieldset.
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $html = '<div class="config-heading"><div class="heading"><strong>';
        
        $groupConfig = $this->getGroup($element)->asArray();
        $html .= '<img style="vertical-align: middle;" src="' . $this->getSkinUrl('images/innobyte/payu-lite/payu-logo.gif', array('_area'=>'adminhtml')) . '" alt="" width="60px" height="20px" /> Gecad ePayment Gateway';
        if (!empty($groupConfig['learn_more_link'])) {
            $html .= '<a class="link-more" href="' . $groupConfig['learn_more_link'] . '" target="_blank">'
                . $this->__('Learn More') . '</a>';
        }
        if (!empty($groupConfig['demo_link'])) {
            $html .= '<a class="link-demo" href="' . $groupConfig['demo_link'] . '" target="_blank">'
                . $this->__('View Demo') . '</a>';
        }
        $html .= '</strong>';
        if ($element->getComment()) {
            $html .= '<span class="heading-intro">' . $element->getComment() . '</span>';
        }
        $html .= '</div>';

        $html .= '<div style="float:right;">
            <img style="vertical-align: middle;margin-right: 10px;" src="' . $this->getSkinUrl('images/innobyte/payu-lite/secure-1-2.png', array('_area'=>'adminhtml')) . '" alt="" width="180px" height="45px" />
            </div><br style="clear: right;"/></div>';
        return $html;
    }
}
