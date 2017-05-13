<?php


class Magik_Innovasettings_Model_Config_Position
{

    public function toOptionArray()
    {
        return array(
            array(
	            'value'=>'top-left',
	            'label' => Mage::helper('innovasettings')->__('Top Left')),
            array(
	            'value'=>'top-right',
	            'label' => Mage::helper('innovasettings')->__('Top Right')),                       

        );
    }

}
