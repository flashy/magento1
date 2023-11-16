<?php   
class Flashy_Integration_Block_Order extends Mage_Core_Block_Template {

    public static $_store = '';

    public $flashy;

    protected function _getStore()
    {
        if(self::$_store){
            self::$_store = Mage::app()->getStore()->getStoreId();
        }

        return self::$_store;
    }

    public function getOrderDetails()
    {
        $flashy_helper = Mage::helper("flashy");
        $flashy_helper->addLog('getOrderDetails');

        $this->flashy = new \Flashy\Flashy(array(
            'api_key' => Mage::getStoreConfig('flashy/flashy/flashy_key'),
            'log_path' => Mage::getBaseDir( 'var' ) . '\log\flashy.log'
        ));

        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);

        if($order->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

            $loggedInCustomer = $flashy_helper->extractDataFromCustomer($customer);
        }

        $billingAddress = $order->getBillingAddress();
        $contactData = $flashy_helper->extractDataFromCustomer($billingAddress);

        if(isset($loggedInCustomer))
        {
            $contactData = array_merge($loggedInCustomer, $contactData);
        }

        $flashy_helper->addLog('Contact info: ');
        $flashy_helper->addLog($contactData);

        $createOrUpdate = $flashy_helper->tryOrLog( function () use($contactData) {
            return $this->flashy->contacts->create($contactData, 'email', true, true);
        });

        $flashy_helper->addLog('Response: ');
        $flashy_helper->addLog($createOrUpdate);


        $total = (float) $order->getSubtotal();
        $items = $order->getAllItems();

        $products = [];
        foreach($items as $i):
            $products[] = $i->getProductId();
        endforeach;

        $currency = Mage::app()->getStore($this->_getStore())->getCurrentCurrencyCode();

        $data = array(
            "order_id"  => $orderId,
            "value"   => $total,
            "content_ids"  => $products,
            "status" => $order->getStatus(),
            "email" => $contactData['email'],
            "currency"  => $currency
        );

        $flashy_helper->addLog('Order data=' .$data);

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