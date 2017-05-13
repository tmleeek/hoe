<?php

    class Webkul_NewsTicker_Block_Adminhtml_Newstickergroup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

        public function __construct() {
            parent::__construct();
            $this->setId("newstickergroup_tabs");
            $this->setDestElementId("edit_form");
            $this->setTitle(Mage::helper("newsticker")->__("Newsticker Group Information"));
        }

        protected function _beforeToHtml() {
            $this->addTab("form_section", array(
                "label"     => Mage::helper("newsticker")->__("Newsticker Group"),
                "alt"       => Mage::helper("newsticker")->__("Newsticker Group"),
                "content"   => $this->getLayout()->createBlock("newsticker/adminhtml_newstickergroup_edit_tab_form")->toHtml(),
            ));
            $this->addTab("grid_section", array(
                "label"     => Mage::helper("newsticker")->__("News"),
                "alt"       => Mage::helper("newsticker")->__("News"),
                "content"   => $this->getLayout()->createBlock("newsticker/adminhtml_newstickergroup_edit_tab_gridnewsticker")->toHtml(),
            ));
            return parent::_beforeToHtml();
        }

    }
