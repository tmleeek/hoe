<?php

class Conversion_Profitshare_Block_Adminhtml_Profitshare_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'profitshare';
        $this->_controller = 'adminhtml_profitshare';
        $this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Salveaza Setarile'));
        $this->_removeButton('delete');
        $this->_removeButton('back');
        $this->_removeButton('reset');
    }

    public function getHeaderText()
    {
        return Mage::helper('adminhtml')->__('Setari Profitshare');
    }
}