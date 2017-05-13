<?php


class Magik_Innovasettings_Model_Config_Menu
{

    public function toOptionArray()
    {
        return array(
            array(
	            'value'=>'classic-menu',
	            'label' => Mage::helper('innovasettings')->__('Classic Menu')),
            array(
	            'value'=>'mega-menu',
	            'label' => Mage::helper('innovasettings')->__('Mega Menu')),                       

        );
    }

}
