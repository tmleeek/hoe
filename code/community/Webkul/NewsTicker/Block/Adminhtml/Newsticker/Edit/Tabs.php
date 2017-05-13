<?php

    class Webkul_NewsTicker_Block_Adminhtml_Newsticker_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

        public function __construct() {
            parent::__construct();
            $this->setId("newsticker_tabs");
            $this->setDestElementId("edit_form");
            $this->setTitle(Mage::helper("newsticker")->__("News Information"));
        }

        protected function _beforeToHtml() {
            $this->addTab("form_section", array(
                "label" => Mage::helper("newsticker")->__("Add News"),
                "alt" => Mage::helper("newsticker")->__("Add News"),
                "content" => $this->getLayout()->createBlock("newsticker/adminhtml_newsticker_edit_tab_form")->toHtml(),
            ));
            return parent::_beforeToHtml();
        }

    }
