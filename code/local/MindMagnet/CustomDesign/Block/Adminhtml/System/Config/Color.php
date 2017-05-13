<?php

class MindMagnet_CustomDesign_Block_Adminhtml_System_Config_Color extends Mage_Adminhtml_Block_System_Config_Form_Field {

	protected function _getElementHtml( Varien_Data_Form_Element_Abstract $element ) {
		$color = new Varien_Data_Form_Element_Text();
		$data = array(
			'name'      => $element->getName(),
			'html_id'   => $element->getId(),
		);
		$color->setData( $data );
                /** @todo setare valoare format */
                $format = '';
		$color->setValue( $element->getValue(), $format );
		$color->setForm( $element->getForm() );
		$color->addClass( 'color ' . $element->getClass() );

		return $color->getElementHtml();
	}

}
