<?php

    class Webkul_NewsTicker_Block_Adminhtml_Newstickergroup_Grid extends Mage_Adminhtml_Block_Widget_Grid {

        public function __construct() {
            parent::__construct();
            $this->setId("newstickergroupGrid");
            $this->setDefaultSort("group_id");
            $this->setDefaultDir("ASC");
            $this->setSaveParametersInSession(true);
        }

        protected function _prepareCollection() {
            $collection = Mage::getModel("newsticker/newstickergroup")->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        protected function _prepareColumns() {
    	
        	$this->addColumn("group_id", array(
        		"header"    => Mage::helper("newsticker")->__("ID"),
        		"align"     => "right",
        		"width"     => "50px",
        		"index"     => "group_id"
        	));

        	$this->addColumn("group_name", array(
                "header"    => Mage::helper("newsticker")->__("Group name"),
                "index"     => "group_name",
        		"align"     => "center"
            ));
        		
        	$this->addColumn("group_code", array(
                "header"    => Mage::helper("newsticker")->__("Group code"),
                "index"     => "group_code",
        		"align"     => "center"
            ));

        	$this->addColumn("news_ids", array(
                "header"    => Mage::helper("newsticker")->__("News"),
                "width"     => "150px",
    			"align"     =>"center",
                "index"     => "news_ids"
            ));

            $this->addColumn("status", array(
                "header"    => Mage::helper("newsticker")->__("Status"),
                "align"     => "left",
                "width"     => "100px",
                "index"     => "status",
                "type"      => "options",
                "options"   => array(1 => "Enabled",2 => "Disabled")
            ));

            $this->addColumn("action", array(
                "header"    => Mage::helper("newsticker")->__("Action"),
                "width"     => "50",
                "type"      => "action",
                "getter"    => "getId",
                "actions"   => array(
                    array(
                        "caption"   => Mage::helper("newsticker")->__("Edit"),
                        "url"       => array("base" => "*/*/edit"),
                        "field"     => "id"
                    )
                ),
                "filter"    => false,
                "sortable"  => false,
                "index"     => "stores",
                "is_system" => true,
            ));
            $this->addExportType("*/*/exportCsv", Mage::helper("newsticker")->__("CSV"));
            $this->addExportType("*/*/exportXml", Mage::helper("newsticker")->__("XML"));
            return parent::_prepareColumns();
        }

        protected function _prepareMassaction() {
            $this->setMassactionIdField("group_id");
            $this->getMassactionBlock()->setFormFieldName("newsticker");
            $this->getMassactionBlock()->addItem("delete", array(
                "label"     => Mage::helper("newsticker")->__("Delete"),
                "url"       => $this->getUrl("*/*/massDelete"),
                "confirm"   => Mage::helper("newsticker")->__("Are you sure?")
            ));
            $statuses = Mage::getSingleton("newsticker/status")->getOptionArray();
            array_unshift($statuses, array("label" => "", "value" => ""));
            $this->getMassactionBlock()->addItem("status", array(
                "label"     => Mage::helper("newsticker")->__("Change status"),
                "url"       => $this->getUrl("*/*/massStatus", array("_current" => true)),
                "additional" => array(
                    "visibility" => array(
                    "name" => "status",
                    "type" => "select",
                    "class" => "required-entry",
                    "label" => Mage::helper("newsticker")->__("Status"),
                    "values" => $statuses
                    )
                )
            ));
            return $this;
        }

        public function getRowUrl($row) {
            return $this->getUrl("*/*/edit", array("id" => $row->getId()));
        }

    }
