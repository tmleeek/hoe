<?php

class Conversion_Profitshare_Adminhtml_ProfitshareController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			 ->_setActiveMenu('profitshare/items');
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			 ->renderLayout();
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {			
			try {
				// Save settings in DB
				Mage::getModel('core/config')->saveConfig('profitshare/advertiser_code', $data["advertiser_code"]);
				Mage::getModel('core/config')->saveConfig('profitshare/encrypted_params', $data["encrypted_params"]);
				Mage::getModel('core/config')->saveConfig('profitshare/api_user', $data["api_user"]);
				Mage::getModel('core/config')->saveConfig('profitshare/api_key', $data["api_key"]);
				Mage::getModel('core/config')->saveConfig('profitshare/import', $data["import"]);
				Mage::getModel('core/config')->saveConfig('profitshare/shop', $data["shop"]);
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('profitshare')->__('Setari salvate cu succes'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				Mage::app()->cleanCache();
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('profitshare')->__('Eroare la salvarea setarilor!'));
        $this->_redirect('*/*/');
	}
}
