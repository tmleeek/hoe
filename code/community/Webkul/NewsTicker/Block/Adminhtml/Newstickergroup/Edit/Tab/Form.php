<?php

    class Webkul_NewsTicker_Block_Adminhtml_Newstickergroup_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

        protected function _prepareForm() {
            $form = new Varien_Data_Form();
            $this->setForm($form);
            $fieldset = $form->addFieldset("newstickergroup_form", array("legend" => Mage::helper("newsticker")->__("Item information")));

            $fieldset->addField("group_name", "text", array(
                "label" => Mage::helper("newsticker")->__("Newsticker Group Name"),
                "class" => "required-entry",
                "required" => true,
                "name" => "group_name"
    		));

            $fieldset->addField("group_code", "text", array(
                "label" => Mage::helper("newsticker")->__("Newticker Group Code"),
                "class" => "required-entry",
                "name" => "group_code",
                "required" => true
            ));

    		$fieldset->addField("texttitle", "text", array(
    			"label" => Mage::helper("newsticker")->__("Text Title"),
    			"class" => "required-entry",
    			"name" => "texttitle",
    			"required" => true
            ));

    		$fieldset->addField("tickerwidth", "text", array(
    			"label" => Mage::helper("newsticker")->__("Width"),
    			"class" => "required-entry",
    			"name" => "tickerwidth",
    			"required" => true
            ));

    		$fieldset->addField("direction", "select", array(
                "label" => Mage::helper("newsticker")->__("Direction"),
                "class" => "required-entry",
                "name" => "direction",
                "values" => array(
                    array(
                        "value" => 1,
                        "label" => Mage::helper("newsticker")->__("Right"),
                    ),
                    array(
                        "value" => 0,
                        "label" => Mage::helper("newsticker")->__("Left"),
                    )
                )
            ));

    		$fieldset->addField("controls", "select", array(
                "label" => Mage::helper("newsticker")->__("Controls"),
                "class" => "required-entry",
                "name" => "controls",
                "values" => array(
                    array(
                        "value" => 1,
                        "label" => Mage::helper("newsticker")->__("yes"),
                    ),
                    array(
                        "value" => 0,
                        "label" => Mage::helper("newsticker")->__("no"),
                    )
                )
            ));

    		$fieldset->addField("displaytype", "select", array(
                "label" => Mage::helper("newsticker")->__("DisplayType"),
                "class" => "required-entry",
                "name" => "displaytype",
                "values" => array(
                    array(
                        "value" => 1,
                        "label" => Mage::helper("newsticker")->__("Fade"),
                    ),
                    array(
                        "value" => 0,
                        "label" => Mage::helper("newsticker")->__("Reveal"),
                    )
                )
            ));

  			$fieldset->addField("pauseonitems", "text", array(
                "label" => Mage::helper("newsticker")->__("PauseOnItems"),
                "class" => "required-entry",
                "name" => "pauseonitems",
                "required" => true
            ));

    		$fieldset->addField("fadeinspeed", "text", array(
                "label" => Mage::helper("newsticker")->__("FadeInSpeed"),
                "class" => "required-entry",
                "name" => "fadeinspeed",
                "required" => true
            ));

    		$fieldset->addField("fadeoutspeed", "text", array(
                "label" => Mage::helper("newsticker")->__("FadeOutSpeed"),
                "class" => "required-entry",
                "name" => "fadeoutspeed",
                "required" => true
            ));

    		$fieldset->addField("speed", "text", array(
                "label" => Mage::helper("newsticker")->__("Speed"),
                "class" => "required-entry",
                "name" => "speed",
                "required" => true,
				"after_element_html" => "<small style='color:red;'>eg. 100</small>"
            ));

    	    $fieldset->addField("status", "select", array(
                "label" => Mage::helper("newsticker")->__("Status"),
                "class" => "required-entry",
                "name" => "status",
                "values" => array(
                    array(
                        "value" => 1,
                        "label" => Mage::helper("newsticker")->__("Enabled"),
                    ),
                    array(
                        "value" => 0,
                        "label" => Mage::helper("newsticker")->__("Disabled"),
                    )
                )
            ));

			$fieldset->addField("cms_pages", "multiselect", array(
    			"label" => Mage::helper("newsticker")->__("Add to Pages"),
    			"name" => "cms_pages[]",
    			"values" => $this->fetchCMS()
    		));

            if (Mage::getSingleton("adminhtml/session")->getBannergroupData()) {
                $form->setValues(Mage::getSingleton("adminhtml/session")->getBannergroupData());
                Mage::getSingleton("adminhtml/session")->setBannergroupData(null);
            }
            elseif (Mage::registry("newstickergroup_data"))
                $form->setValues(Mage::registry("newstickergroup_data")->getData());
            return parent::_prepareForm();
        }

    	public function fetchCMS()   {
    		$cms = array();
    		$cms_pages = Mage::getModel("cms/page")->getCollection();
    		$cms_pages->load();
    		foreach($cms_pages as $one_row)
                array_push($cms,array("value" => $one_row->getPageId(), "label" => Mage::helper("adminhtml")->__($one_row->getTitle())));
    		return $cms;
    	}

    }
