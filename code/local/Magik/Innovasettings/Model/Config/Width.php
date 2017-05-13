<?php


class Magik_Innovasettings_Model_Config_Width
{

    public function toOptionArray()
    {
        return array(
            array(
	            'value' => 'flexible',
	            'label' => Mage::helper('innovasettings')->__('flexible')),
            array(
	            'value' => 'fixed',
	            'label' => Mage::helper('innovasettings')->__('fixed')),
        );
    }

}
