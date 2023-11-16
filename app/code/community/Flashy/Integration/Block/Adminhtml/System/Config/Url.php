<?php
class Flashy_Integration_Block_Adminhtml_System_Config_Url
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $scope_id = Mage::getSingleton('adminhtml/config_data')->getScopeId();
        $flashy_key = Mage::getStoreConfig('flashy/flashy/flashy_key', $scope_id);
        $base_url = Mage::app()->getStore($scope_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $html = '<table>';
        $entities = array('products', 'contacts', 'orders');
        foreach ($entities as $entity) {
            $flashy_url = $base_url . "flashy?export=$entity&store_id=$scope_id&flashy_pagination=true&flashy_key=$flashy_key&limit=100&page=1";
            $html .= '<tr><td class="label">' . __("Flashy " . ucfirst($entity) . " Url") . '</td>';
            $html .= '<td class="value"><a href="' . $flashy_url . '" target="_blank">' . $flashy_url . '</a></td><td></td><td></td></tr>';
        }
        $html .= '</table>';
        return $html;
    }
}
