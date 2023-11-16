<?php
class Flashy_Integration_Block_Adminhtml_System_Config_Connected
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $scope_id = Mage::getSingleton('adminhtml/config_data')->getScopeId();
        $flashy_connected = Mage::getStoreConfig('flashy/flashy/flashy_connected', $scope_id);
        $html = '<table cellspacing="0" class="form-list"><tr><td class="label"></td><td class="value"><ul class="messages"><li class="' . ($flashy_connected?'success':'error') . '-msg"><span > ' . __(($flashy_connected?'C':'Not c').'onnected with Flashy.') . '</span></li></ul></td><td></td><td></td></tr></table>';
        return $html;
    }
}
