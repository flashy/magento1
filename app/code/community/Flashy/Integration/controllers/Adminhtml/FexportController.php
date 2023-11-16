<?php
//
//class Flashy_Integration_Adminhtml_FexportController extends Mage_Adminhtml_Controller_Action
//{
//
//	public $flashy;
//
//	/**
//     * View form action
//     */
//    public function indexAction()
//    {
//        if ( Mage::getStoreConfig( 'flashy/flashy/flashy_key' ) === '' )
//        {
//            Mage::getModel('core/session')->addError(Mage::helper('flashy')->__('Error! Flashyapp API Key missing.'));
//
//            $this->_redirect('*/system_config/edit/section/flashy');
//        }
//        else {
//            $this->loadLayout();
//            $this->_setActiveMenu('flashy/fexport');
//            $this->renderLayout();
//        }
//    }
//
//	/**
//     * Grid Action
//     * Display list of products related to current category
//     *
//     * @return void
//     */
//    public function saveAction()
//    {
//    	if ( Mage::getStoreConfig( 'flashy/flashy/flashy_key' ) === '' )
//    	{
//    		Mage::getModel('core/session')->addError(Mage::helper('flashy')->__('Error! Flashyapp API Key or Catalog missing.'));
//
//    		$this->_redirect('*/*/');
//    	}
//
//    	$general = $this->getRequest()->getParam('general');
//
//    	$catalog_id = intval($general['catalog']);
//
//    	Mage::getConfig()->saveConfig('flashy/flashy/flashy_catalog', $catalog_id, 'default', 0);
//
//    	$this->flashy = new Flashy_Flashy( Mage::getStoreConfig('flashy/flashy/flashy_key') );
//
//		$this->exportProducts(intval($general['store']), $catalog_id);
//
//    	$this->_redirect('*/*/');
//    }
//
//    public function exportProducts($store_id, $catalog_id)
//    {
//        $flashy_helper = Mage::helper('flashy');
//
//    	if( $store_id == 0 )
//    		$products = Mage::getModel('catalog/product')->getCollection();
//    	else
//    		$products = Mage::getModel('catalog/product')->setStoreId($store_id)->getCollection();
//
//		$products->addAttributeToSelect('*');
//
//		if( $store_id != 0 )
//		{
//			$products->addStoreFilter($store_id);
//		}
//
//		$export_products = array();
//
//		$i = 0;
//
//		$base_url = Mage::app()->getStore($store_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
//
//		$currency = Mage::app()->getStore($store_id)->getCurrentCurrencyCode();
//
//		foreach($products as $prod)
//		{
//			try {
//				$sync_id = $prod->getId();
//
//				$_product = Mage::getModel('catalog/product')->load($sync_id);
//
//				if( $store_id != 0 )
//				{
//					$_product->setStoreId($store_id);
//				}
//
//				$export_products[$i] = array(
//					'id'			=> $sync_id,
//					'link'			=> $_product->getProductUrl($_product),
//					'title'			=> $_product->getName(),
//					'description'	=> $_product->getShortDescription(),
//					'price'			=> $_product->getPrice(),
//					'currency'		=> $currency,
//					'tags'			=> $_product->getMetaKeyword()
//				);
//
//				if( $_product->getImage() && $_product->getImage() != 'no_selection' )
//					$export_products[$i]['image_link'] = $_product->getImageUrl();
//
//				$categoryCollection = $_product->getCategoryCollection()->addAttributeToSelect('name');
//
//				$export_products[$i]['product_type'] = "";
//
//				foreach( $categoryCollection as $_cat ) {
//					$export_products[$i]['product_type'] .= $_cat->getName() . '>';
//				}
//
//				$export_products[$i]['product_type'] = substr($export_products[$i]['product_type'], 0, -1);
//
//				$i++;
//			} catch (Exception $e) {
//
//			}
//		}
//
//        $export = $flashy_helper->tryOrLog( function () use($export_products, $catalog_id) {
//            return $this->flashy->import->products($export_products, $catalog_id);
//        });
//
//		if( isset($export['error']) )
//		{
//			Mage::getModel('core/session')->addError(Mage::helper('flashy')->__('Error! Flashy API Key incorrect.'));
//		}
//		else
//		{
//			Mage::getModel('core/session')->addSuccess(Mage::helper('flashy')->__('Success! %s products exported.', count($export_products)));
//		}
//    }
//
//	protected function _isAllowed(){
//		return true;
//	}
//}