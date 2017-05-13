<?php
/**
 * Available languages on PayU website source model.
 *
 * @category    Innobyte
 * @package     Innobyte_PayULite
 * @author      Bogdan Constantinescu <bogdan.constantinescu@innobyte.com>
 */

class Innobyte_PayULite_Model_Source_Language
{
    /**
     * @var array   Array with options.
     */
    protected $_options = array();
    
    
    
    /**
     * Getter method for options; implements lazy instantiation.
     * 
     * @return array
     */
    public function toOptionArray()
    {
        if (empty($this->_options)) {
            $this->_options = array(
                array('value' => 'RO', 'label' => Zend_Locale::getTranslation('ro', 'language', Mage::app()->getLocale()->getLocaleCode())),
                array('value' => 'EN', 'label' => Zend_Locale::getTranslation('en', 'language', Mage::app()->getLocale()->getLocaleCode())),
                array('value' => 'DE', 'label' => Zend_Locale::getTranslation('de', 'language', Mage::app()->getLocale()->getLocaleCode())),
                array('value' => 'FR', 'label' => Zend_Locale::getTranslation('fr', 'language', Mage::app()->getLocale()->getLocaleCode())),
                array('value' => 'IT', 'label' => Zend_Locale::getTranslation('it', 'language', Mage::app()->getLocale()->getLocaleCode())),
                array('value' => 'ES', 'label' => Zend_Locale::getTranslation('es', 'language', Mage::app()->getLocale()->getLocaleCode())),
                array('value' => 'HU', 'label' => Zend_Locale::getTranslation('hu', 'language', Mage::app()->getLocale()->getLocaleCode())),
                array('value' => 'RU', 'label' => Zend_Locale::getTranslation('ru', 'language', Mage::app()->getLocale()->getLocaleCode())),
                array('value' => 'BG', 'label' => Zend_Locale::getTranslation('bg', 'language', Mage::app()->getLocale()->getLocaleCode())),
            );
            array_unshift($this->_options, array('value' => '', 'label' => '',));
        }
        return $this->_options;
    }
}
