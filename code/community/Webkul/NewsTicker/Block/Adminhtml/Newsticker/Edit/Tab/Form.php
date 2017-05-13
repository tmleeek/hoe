<?php

    class Webkul_NewsTicker_Block_Adminhtml_Newsticker_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

        protected function _prepareForm() {
            $form = new Varien_Data_Form();
            $this->setForm($form);
            $fieldset = $form->addFieldset("newsticker_form", array("legend" => Mage::helper("newsticker")->__("Item information")));
            $version = substr(Mage::getVersion(), 0, 3);
    		$wysiwygConfig = Mage::getSingleton("cms/wysiwyg_config")->getConfig(array("add_variables" => false, "add_widgets" => false,"files_browser_window_url"=>$this->getBaseUrl()."admin/cms_wysiwyg_images/index/"));    		
            $fieldset->addField("newsticker", "editor", array(
                "name"      => "newsticker",
                "label"     => Mage::helper("newsticker")->__("Content"),
                "title"     => Mage::helper("newsticker")->__("Content"),
                "style"     => "width:700px; height:200px;",
                "state"     => "html",
                "config"    => $wysiwygConfig,
                "wysiwyg"   => true,
                "required"  => true
            ));

    		$fieldset->addField("sort_order", "text", array(
                "label"     => Mage::helper("newsticker")->__("Sort Order"),
                "class"     => "required-entry",
                "required"  => true,
                "name"      => "sort_order"
            ));

            $fieldset->addField("status", "select", array(
                "label"     => Mage::helper("newsticker")->__("Status"),
                "class"     => "required-entry",
                "name"      => "status",
                "values"    => array(
                    array(
                        "value" => 1,
                        "label" => Mage::helper("newsticker")->__("Enabled"),
                    ),
                    array(
                        "value" => 2,
                        "label" => Mage::helper("newsticker")->__("Disabled"),
                    )
                )
            ));

            if (Mage::getSingleton("adminhtml/session")->getBannerData()) {
                $form->setValues(Mage::getSingleton("adminhtml/session")->getBannerData());
                Mage::getSingleton("adminhtml/session")->setBannerData(null);
            }
            elseif (Mage::registry("newsticker_data"))
                $form->setValues(Mage::registry("newsticker_data")->getData());
            return parent::_prepareForm();
        }

    }
