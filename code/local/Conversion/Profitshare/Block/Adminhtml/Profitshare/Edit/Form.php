<?php

class Conversion_Profitshare_Block_Adminhtml_Profitshare_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('General')));
        $fieldset->addField('advertiser_code', 'text', array(
                'name'  => 'advertiser_code',
                'label' => Mage::helper('adminhtml')->__('Cod advertiser'),
                'title' => Mage::helper('adminhtml')->__('Cod advertiser'),
                'required' => true,
            )
        );

        $fieldset->addField('encrypted_params', 'text', array(
                'name'  => 'encrypted_params',
                'label' => Mage::helper('adminhtml')->__('Cheia de criptare'),
                'title' => Mage::helper('adminhtml')->__('Cheia de criptare'),
                'required' => true,
            )
        );

        $fieldset->addField('api_user', 'text', array(
        		'name'  => 'api_user',
        		'label' => Mage::helper('adminhtml')->__('User API'),
        		'title' => Mage::helper('adminhtml')->__('User API'),
        		'required' => true,
        	)
        );
        
        $fieldset->addField('api_key', 'text', array(
        		'name'  => 'api_key',
        		'label' => Mage::helper('adminhtml')->__('Cheie API'),
        		'title' => Mage::helper('adminhtml')->__('Cheie API'),
        		'required' => true,
        	)
        );

        $fieldset->addField('import', 'select', array(
        		'label'     => Mage::helper('adminhtml')->__('Export automat de produse'),
        		'class'     => 'required-entry',
        		'required'  => false,
        		'name'      => 'import',
        		'onclick' => "",
        		'onchange' => "",
        		'value'  => '-1',
        		'values' => array('-1'=>'Selecteaza','1' => 'Da','2' => 'Nu'),
        		'disabled' => false,
        		'readonly' => false,
        		'after_element_html' => '<small>(Produsele vor fi incarcate automat in Profitshare, pentru aceasta optiune trebuie sa pornesti cron-ul din Magento)</small>',
        		'tabindex' => 1
        ));
        
        $shops = array();
        foreach (Mage::app()->getWebsites() as $website) {
        	foreach ($website->getGroups() as $group) {
        		$stores = $group->getStores();
        		foreach ($stores as $store) {
        			$shops[$store->getId()] = $store->getName();
        		}
        	}
        }
        
        $fieldset->addField('shop', 'select', array(
        		'label'     => Mage::helper('adminhtml')->__('Selecteaza shop-ul integrat cu Profitshare'),
        		'class'     => 'required-entry',
        		'required'  => true,
        		'name'      => 'shop',
        		'onclick' 	=> "",
        		'onchange' 	=> "",
        		'value'  	=> '1',
        		'values'	=> $shops,
        		'disabled' 	=> false,
        		'readonly' 	=> false,
        		'tabindex' 	=> 1
        ));
        
        $default = array(
        		"advertiser_code"	=> Mage::getStoreConfig('profitshare/advertiser_code'),
        		"encrypted_params"	=> Mage::getStoreConfig('profitshare/encrypted_params'),
        		"api_user"			=> Mage::getStoreConfig('profitshare/api_user'),
        		"api_key"			=> Mage::getStoreConfig('profitshare/api_key'),
        		"import"			=> Mage::getStoreConfig('profitshare/import'),
        		"shop"				=> Mage::getStoreConfig('profitshare/shop')
        );
        $form->setValues($default);
        $form->setAction($this->getUrl('*/*/save'));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
