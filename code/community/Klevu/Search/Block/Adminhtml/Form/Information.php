<?php

class Klevu_Search_Block_Adminhtml_Form_Information extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    protected function _construct() {
        parent::_construct();

        if (!$this->getTemplate()) {
            // Set the default template
            $this->setTemplate("klevu/search/form/information.phtml");
        }
    }

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $html = $this->_getHeaderHtml($element);

        $html .= $this->_toHtml();

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

}
