<?php   
class Flashy_Integration_Block_Product extends Mage_Core_Block_Template {

    public static $_store = '';

    public $flashy;

    protected function _getStore()
    {
        if(self::$_store){
            self::$_store = Mage::app()->getStore()->getStoreId();
        }

        return self::$_store;
    }

    public function getProductDetails()
    {
        $product = Mage::registry('current_product');
        $products = array($product->getId());

        $data = array(
            "content_ids"  => $products
        );

        return $data;
    }

    public function getFlashyId()
    {
        if(Mage::getStoreConfig('flashy/flashy/active', $this->_getStore())){
            return Mage::getStoreConfig('flashy/flashy/flashy_id', $this->_getStore());
        }
        else {
            return false;
        }
    }
}