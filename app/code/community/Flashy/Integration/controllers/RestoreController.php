<?php

class Flashy_Integration_RestoreController extends Mage_Core_Controller_Front_Action
{

    public function cartAction()
    {
        $key = $this->getRequest()->getParam('id', 0);

        //get flashy cart hash
        $cartHash = Mage::getModel('flashy/carthash')->load($key, 'key');

        //get cart model
        $cartModel = Mage::getSingleton('checkout/cart');

        $messages = array();
        if ($cartHash->getKey()) {
            try {
                //get cart data from hash
                $cart = json_decode($cartHash->getCart(), true);

                //empty the cart
                $cartModel->truncate();

                //loop through cart items from hash
                foreach ($cart as $cart_item) {
                    //load product
                    $product = Mage::getModel('catalog/product')->load($cart_item['product']);
                    try {

                        //add product to cart
                        $cartModel->addProduct($product, $cart_item);
                        $messages[] = array(
                            'message' => __('Success! %1 is restored successfully.', $product->getName()),
                            'success' => true
                        );

                    } catch (\Exception $e) {
                        $messages[] = array(
                            'message' => __('Error! %1 is not restored. %2', $product->getName(), $e->getMessage()),
                            'success' => false
                        );
                        Mage::log($e->getMessage(), null, 'flashy.log');
                    }
                }

                //save the cart
                $cartModel->save();
            } catch (\Exception $e) {
                $messages[] = array(
                    'message' => __('Error! Cart is not restored.'),
                    'success' => false
                );
                Mage::log("Could not restore flashy cart hash for id=$key cart=" . $cartHash->getCart(), null, 'flashy.log');
            }
        }

        $this->getResponse()->setRedirect('/checkout/cart/index');
    }
}