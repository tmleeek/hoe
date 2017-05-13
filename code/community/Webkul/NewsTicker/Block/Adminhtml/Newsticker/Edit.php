<?php

    class Webkul_NewsTicker_Block_Adminhtml_Newsticker_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

        public function __construct() {
            parent::__construct();
            $this->_objectId = "id";
            $this->_blockGroup = "newsticker";
            $this->_controller = "adminhtml_newsticker";
            $this->_updateButton("save", "label", Mage::helper("newsticker")->__("Save Item"));
            $this->_updateButton("delete", "label", Mage::helper("newsticker")->__("Delete Item"));
            $this->_addButton("saveandcontinue", array(
                "label" => Mage::helper("adminhtml")->__("Save And Continue Edit"),
                "onclick" => "saveAndContinueEdit()",
                "class" => "save",
                    ), -100);
            $this->_formScripts[] = "function saveAndContinueEdit(){
                    editForm.submit($('edit_form').action+'back/edit/');
                }";
        }

        public function getHeaderText() {
            if (Mage::registry("newsticker_data") && Mage::registry("newsticker_data")->getId())
                return Mage::helper("newsticker")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("newsticker_data")->getTitle()));
            else
                return Mage::helper("newsticker")->__("Add Item");
        }

    }
