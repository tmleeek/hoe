<?php
/**
 *
 * @category   Nuber
 * @package    Nuber_Scopeviewer
 * @author     Martin Nuber
 * @copyright  Copyright (c) 2014 Martin Nuber
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Nuber_Scopeviewer_Block_Adminhtml_System_Config_Form_Field extends Mage_Adminhtml_Block_System_Config_Form_Field {
    
    private $_systemConfig = false;
    
    private function getSystemConfig() {
        if ($this->_systemConfig === false) {
            $this->_systemConfig = Mage::getConfig()->loadModulesConfiguration('system.xml')->applyExtends();
        }
        
        return $this->_systemConfig;
    }

    /**
     * Compare scope values to see differences
     *
     * @param string $scopeValue Value from a different scope
     * @param string|object $currentValue Value from current scope
     * @param object $node Modules xml config to get source model if any
     * @param boolean $isMultiple Dropdown or not
     * @return string|boolean
     */
    private function getScopeDifference($scopeValue, $currentValue, $node, $isMultiple = false) {
        $currentValue = (string) $currentValue;
        if ($scopeValue !== $currentValue) {
            if ($isMultiple === true) {
                $scopeValues = explode(',', $scopeValue);
                $scopeValue = '';
                $returnValues = array();
                
                foreach ($scopeValues as $value) {
                    if (($sourceValue = $this->getSourceValue($value, $node)) !== false) {
                        $returnValues[] = $sourceValue;
                    } else {
                        $returnValues[] = $scopeValue;
                    }
                }
                
                return !empty($returnValues) ? implode(', ', $returnValues) : '[blank]';
            } else {
                if (($sourceValue = $this->getSourceValue($scopeValue, $node)) !== false) {
                    return !empty($sourceValue) ? $sourceValue : '[blank]';
                } else {
                    return !empty($scopeValue) ? $scopeValue : '[blank]';
                }
            }
        } else {
            return false;
        }
    }
    
    /**
     * Get value label from source model (for dropdowns)
     * Example: Returns United States for US, returns Yes for 1, etc.
     *
     * @param string $value
     * @param object $node
     * @return string|boolean
     */
    private function getSourceValue($value, $node) {
        if ($node && $node->source_model) {
            $model = Mage::getSingleton((string) $node->source_model);

            $options = $model->toOptionArray();

            foreach ($options as $id => $option) {
                if ($option['value'] == $value) {
                    $sourceValue = $option['label'];
                }
            }
        }
        
        if ($sourceValue) {
            return $sourceValue;
        } else {
            return false;
        }
    }

    /**
     * Override render() to add scope tooltip
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        if (version_compare(Mage::getVersion(), '1.7.0.1', '<')) {
            $useContainerId = $element->getData('use_container_id');
            $html = '<tr id="row_' . $id . '">'
                  . '<td class="label"><label for="'.$id.'">'.$element->getLabel().'</label></td>';
        } else {
            $html = '<td class="label"><label for="'.$id.'">'.$element->getLabel().'</label></td>';            
        }

        //$isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $isMultiple = $element->getExtType()==='multiple';

        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $options = $element->getValues();

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
        }
        elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit()==1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        if (version_compare(Mage::getVersion(), '1.7.0.1', '<')) {
            $html.= '<td class="value">';
            $html.= $this->_getElementHtml($element);
        } else {
            if ($element->getTooltip()) {
                $html .= '<td class="value with-tooltip">';
                $html .= $this->_getElementHtml($element);
                $html .= '<div class="field-tooltip"><div>' . $element->getTooltip() . '</div></div>';
            } else {
                $html .= '<td class="value">';
                $html .= $this->_getElementHtml($element);
            };          
        }
        
        if ($element->getComment()) {
            $html.= '<p class="note"><span>'.$element->getComment().'</span></p>';
        }
        $html.= '</td>';

        if ($addInheritCheckbox) {

            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k=>$v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif ($v['value']==$defText) {
                        $defTextArr[] = $v['label'];
                        break;
                    }
                }
                $defText = join(', ', $defTextArr);
            }

            // default value
            $html.= '<td class="use-default">';
            $html.= '<input id="' . $id . '_inherit" name="'
                . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
                . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
            $html.= '<label for="' . $id . '_inherit" class="inherit" title="'
                . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
            $html.= '</td>';
        }

        $html.= '<td class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html.= '</td>';

        $scopeDifference = array();

        preg_match('/^groups\[(.*)\]\[fields\]\[(.*)\]\[value\](\[\])?$/', $element->getName(), $pathParts);
        $pathParts[0] = Mage::app()->getRequest()->getParam('section'); // strstr($element->getId(), '_' . $pathParts[1], true);
        
        $path = $pathParts[0] . '/' . $pathParts[1] . '/' . $pathParts[2];
        $sectionPath = 'sections/' . $pathParts[0] . '/groups/' . $pathParts[1] . '/fields/' . $pathParts[2];

        $node = $this->getSystemConfig()->getNode($sectionPath);
        $value = (string) Mage::getConfig()->getNode($path, 'default');
        
        if (false !== ($value = $this->getScopeDifference($value, $element->getValue(), $node, $isMultiple))) {
            $scopeDifference['default']['name'] = 'Default Config';
            $scopeDifference['default']['value'] = $value;
        }
        
        foreach (Mage::app()->getWebsites() as $website) {
            $value = (string) Mage::getConfig()->getNode($path, 'website', $website->getCode());

            if (false !== ($value = $this->getScopeDifference($value, $element->getValue(), $node, $isMultiple))) {
                $scopeDifference['website_' . $website->getCode()]['name'] = $website->getName();
                $scopeDifference['website_' . $website->getCode()]['value'] = $value;
            }

            foreach ($website->getGroups() as $group) {                
                foreach ($group->getStores() as $store) {
                    $value = (string) Mage::getConfig()->getNode($path, 'store', $store->getCode());

                    if (false !== ($value = $this->getScopeDifference($value, $element->getValue(), $node, $isMultiple))) {
                        $scopeDifference['store_' . $store->getCode()]['name'] = $website->getName() . '->' . $store->getName();
                        $scopeDifference['store_' . $store->getCode()]['value'] = $value;
                    }
                }
            }
        }

        $html.= '<td class="scope-label nuber-scopeviwer with-tooltip">';
        if (!empty($scopeDifference)) {
            $html.= '<div class="field-tooltip"><div>';
            $html.= '<strong>' . $this->__('Following scopes have different values') . '</strong><br />';
            
            foreach ($scopeDifference as $scopeId => $scope) {
                $html.= $scope['name'] . ': ' . $scope['value'] . '<br />';
            }

            $html.= '</div></div>';
        }
        $html.= '</td>';
        
        $html.= '<td class="">';
        if ($element->getHint()) {
            $html.= '<div class="hint" >';
            $html.= '<div style="display: none;">' . $element->getHint() . '</div>';
            $html.= '</div>';
        }
        $html.= '</td>';

        if (version_compare(Mage::getVersion(), '1.7.0.1', '<')) {
            $html.= '</tr>';
            return $html;
        } else {
            return $this->_decorateRowHtml($element, $html);         
        }
    }
}
