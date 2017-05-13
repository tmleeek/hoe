<?php


class Magik_Innovasettings_Model_Config_Footer
{

    public function toOptionArray()
    {
        return array(
            array(
	            'value'=>'simple',
	            'label' => Mage::helper('innovasettings')->__('simple')),
            array(
	            'value'=>'informative',
	            'label' => Mage::helper('innovasettings')->__('informative')),
        );
    }

}
