<?php

class Flashy_Integration_Model_Observer
{
    public $flashy;

    public function customerRegistered(Varien_Event_Observer $observer)
    {
        $flashy_helper = Mage::helper("flashy");
        $flashy_helper->addLog('Event: customerRegistered');

        $event = $observer->getEvent();
        $customer = $event->getCustomer();

        $contactData = $flashy_helper->extractDataFromCustomer($customer);

        $flashy_helper->addLog('Contact info: ');
        $flashy_helper->addLog($contactData);

        $this->flashy = new \Flashy\Flashy(array(
            'api_key' => Mage::getStoreConfig('flashy/flashy/flashy_key'),
            'log_path' => Mage::getBaseDir( 'var' ) . '\log\flashy.log'
        ));

        $create = $flashy_helper->tryOrLog( function () use($contactData){
            return $this->flashy->contacts->create($contactData);
        });

        $flashy_helper->addLog('Contact created: ' . json_encode($contactData));
    }

    public function newsletterSubscriberChange(Varien_Event_Observer $observer)
    {
        $flashy_helper = Mage::helper("flashy");
        $flashy_helper->addLog('Event: newsletterSubscriberChange');

        if(Mage::getStoreConfig('flashy/flashy/active')) {
            $subscriber = $observer->getEvent()->getSubscriber();

            if ($subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
                $list_id = Mage::getStoreConfig('flashy/flashy_lists/flashy_list', $subscriber->getStoreId());
                $flashy_key = Mage::getStoreConfig('flashy/flashy/flashy_key');
                if (!empty($list_id) && !empty($flashy_key))
                {
                    $this->flashy = new \Flashy\Flashy(array(
                        'api_key' => $flashy_key,
                        'log_path' => Mage::getBaseDir( 'var' ) . '\log\flashy.log'
                    ));

                    $contactData = ["email" => $subscriber->getSubscriberEmail(),];

                    $flashy_helper->addLog('Subscriber info: ');
                    $flashy_helper->addLog($contactData);

                    $subscribe = $flashy_helper->tryOrLog( function () use ($list_id, $contactData){
                        return $this->flashy->contacts->subscribe($contactData, $list_id);
                    });

                    $flashy_helper->addLog('Contact info: ');
                    $flashy_helper->addLog($subscribe);
                }
                else
                {
                    $flashy_helper->addLog('Failed: Flashy API Key="' . $flashy_key . '" list id="' . $list_id.'"');
                }
            }
        }
    }

    public function salesOrderChange(Varien_Event_Observer $observer)
    {
        $flashy_helper = Mage::helper("flashy");
        $flashy_helper->addLog('Event: salesOrderChange');
        $account_id = Mage::getStoreConfig('flashy/flashy/flashy_id');
        $flashy_key = Mage::getStoreConfig('flashy/flashy/flashy_key');
        if(Mage::getStoreConfig('flashy/flashy/active') && !empty($account_id) && !empty($flashy_key)) {

            $this->flashy = new \Flashy\Flashy(array(
                'api_key' => $flashy_key,
                'log_path' => Mage::getBaseDir( 'var' ) . '\log\flashy.log'
            ));

            $order = $observer->getEvent()->getOrder();

            if ($order->getStatus() != $order->getOrigData('status')) {
                if ($order->getCustomerId()) {
                    $email = $order->getCustomerEmail();
                } else {
                    $email = $order->getBillingAddress()->getEmail();
                }

                $data = array(
                    "order_id" => $order->getId(),
                    "status" => $order->getStatus()
                );

                foreach ($order->getTracksCollection() as $_track) {
                    $data['tracking_id'] = $_track->getNumber();
                }

                $data = array_merge(array("account_id" => $account_id, "email" => $email), $data);

                $flashy_helper->addLog('Order info: ');
                $flashy_helper->addLog($data);

                $track = $flashy_helper->tryOrLog( function () use ($data){
                    return $this->flashy->events->track("PurchaseUpdated", $data);
                });

                $flashy_helper->addLog('Response: ');
                $flashy_helper->addLog($track);
            }
        }
    }

    public function salesOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        $flashy_helper = Mage::helper("flashy");
        $flashy_helper->addLog('Event: salesOrderPlaceAfter');
        $flashy_key = Mage::getStoreConfig('flashy/flashy/flashy_key');
        $account_id = Mage::getStoreConfig('flashy/flashy/flashy_id');
        if(Mage::getStoreConfig('flashy/flashy/active') && !empty($account_id) && !empty($flashy_key) && Mage::getStoreConfig('flashy/flashy/purchase')) {

            $this->flashy = new \Flashy\Flashy(array(
                'api_key' => $flashy_key,
                'log_path' => Mage::getBaseDir( 'var' ) . '\log\flashy.log'
            ));

            $order = $observer->getEvent()->getOrder();

            if($order->getCustomerId())
            {
                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

                $loggedInCustomer = $flashy_helper->extractDataFromCustomer($customer);
            }
            else
            {
                $loggedInCustomer = [];
            }

            $orderCustomerData = $flashy_helper->extractDataFromOrder($order);

            $billingData = $flashy_helper->extractDataFromBilling($order->getBillingAddress());

            $contactData = array_merge($loggedInCustomer, $orderCustomerData, $billingData);


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

            $currency = Mage::app()->getStore(Mage::app()->getStore()->getStoreId())->getCurrentCurrencyCode();

            $data = array(
                "account_id" => $account_id,
                "email" => $contactData['email'],
                "order_id"  => $order->getId(),
                "value"   => $total,
                "content_ids"  => $products,
                "status" => $order->getStatus(),
                "currency"  => $currency
            );

            $data = array_merge(array("account_id" => $account_id, "email" => $contactData['email']), $data);

            $flashy_helper->addLog('Order info: ');
            $flashy_helper->addLog($data);

            $track = $flashy_helper->tryOrLog( function () use($account_id, $contactData, $data) {
                return $this->flashy->events->track("Purchase", $data);
            });

            $flashy_helper->addLog('Response: ');
            $flashy_helper->addLog($track);
        }
    }

    /**
     * Save cart data in flashy cart hash table
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkoutCartChange(Varien_Event_Observer $observer)
    {
        if(Mage::getStoreConfig('flashy/flashy/active')){
            //get cart from event observer
            $cart = $observer->getEvent()->getCart();

            //cart hash will not be updated
            $updateCart = false;

            //get key from cookie
            $key = Mage::getSingleton('core/cookie')->get('flashy_id');

            //if key exists
            if ($key) {
                //get model flashy cart hash
                $cartHash = Mage::getModel('flashy/carthash');

                //load cart hash by key
                $cartHash->load($key, 'key');

                //get quote from cart
                $quote = $cart->getQuote();

                //get all visible items of the cart
                $items = $quote->getAllVisibleItems();

                //cart items data
                $cartItems = array();

                //loop through cart visible items
                foreach ($items as $item) {
                    //get product options
                    $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

                    //update qty
                    $options['info_buyRequest']['qty'] = $item->getQty();

                    // unset uenc from cart item data
                    unset($options['info_buyRequest']['uenc']);

                    //add info to cart items
                    $cartItems[] = $options['info_buyRequest'];

                    //cart hash will be updated
                    $updateCart = true;
                }

                //check if cart will be updated
                if ($updateCart) {
                    try {
                        //save cart hash data
                        $cartHash->setKey($key);
                        $cartHash->setCart(json_encode($cartItems));
                        $cartHash->save();
                    } catch (\Exception $e) {
                        $this->_logger->info("Could not save flashy cart hash key=" . $cartHash->getKey() . " cart=" . $cartHash->getCart());
                    }
                }
            }
        }
    }
}