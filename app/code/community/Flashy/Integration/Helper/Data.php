<?php

class Flashy_Integration_Helper_Data extends Mage_Core_Helper_Abstract {

    public function __construct()
    {
        $this->_includeLibFiles();
    }

    protected function _includeLibFiles()
    {
        $this->_recursiveRequireOnce(Mage::getBaseDir('lib') . DS . 'Flashy');
    }

    protected function _recursiveRequireOnce($path)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                // if it's a directory, continue iterating
                continue;
            } elseif (substr($item->getFilename(), -4) === '.php') {
                // only include php files
                require_once $item->getRealPath();
            }
        }
    }

    public function getFlashyId()
    {
        if(Mage::getStoreConfig('flashy/flashy/active')){
            return Mage::getStoreConfig('flashy/flashy/flashy_id');
        }
        else {
            return false;
        }
    }

    public function getCart()
    {
        $cart = Mage::getModel('checkout/cart')->getQuote();

        $tracking = [];

        foreach($cart->getAllVisibleItems() as $item)
        {
            $tracking['content_ids'][] = $item->getProductId();
        }

        $tracking['value'] = intval($cart->getGrandTotal());

        if( isset($tracking['content_ids']) && count($tracking['content_ids']) < 1 )
        {
            return false;
        }

        $tracking['currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();

        $tracking = json_encode($tracking);

        Mage::getSingleton('core/cookie')->set('flashy_cart', base64_encode($tracking), 86400, '/');

        return $tracking;
    }

    public function extractDataFromCustomer($customer)
    {
        $data = [
            'email' => $customer->getEmail(),
            'first_name' => $customer->getFirstname(),
            'last_name' => $customer->getLastname(),
        ];

        if($customer->getGender() != '')
        {
            $data['gender'] = $this->fitGender($customer->getGender());
        }

        if($customer->getDob() != '')
        {
            $data['birthday'] = strtotime($customer->getDob());
        }

        if($customer->getTelephone() != '')
        {
            $data['phone'] = $customer->getTelephone();
        }

        if($customer->getCity() != '')
        {
            $data['city'] = $customer->getCity();
        }

        return $data;
    }

    public function extractDataFromOrder($order)
    {
        $data = [
            'email' => $order->getCustomerEmail(),
            'first_name' => $order->getCustomerFirstname(),
            'last_name' => $order->getCustomerLastname(),
        ];

        if($order->getCustomerDob() != '')
        {
            $data['birthday'] = $order->getCustomerDob();
        }

        if($order->getCustomerGender() != '')
        {
            $data['gender'] = $this->fitGender($order->getCustomerGender());
        }

        return $data;
    }

    public function extractDataFromBilling($billing)
    {
        $data = [];

        if($billing->getTelephone() != '')
        {
            $data['phone'] = $billing->getTelephone();
        }

        if($billing->getCity() != '')
        {
            $data['city'] = $billing->getCity();
        }

        if($billing->getCountry() != '')
        {
            $data['country'] = $billing->getCountry();
        }

        return $data;
    }

    public function fitGender($gender)
    {
        switch($gender) {
            case '1':
                return 'Male';
            case '2':
                return 'Female';

            default:
                return 'Unknown';
        }
    }

    public function addLog($m)
    {
        if (Mage::getStoreConfig('flashy/flashy/log')) {
            Mage::log($m, null, 'flashy.log', true);
        }
    }

    /**
     * @param Closure $func
     * @return mixed
     */
    public static function tryOrLog(Closure $func)
    {
        $flashy_helper = Mage::helper("flashy");

        if( phpversion() > 7 )
        {
            try {
                return $func();
            }
            catch ( \Throwable $e )
            {
                $flashy_helper->addLog("Was not able to do something safely: {$e->getMessage()} \n " . $e->getTraceAsString());
            }
        }
        else
        {
            try {
                return $func();
            }
            catch ( Exception $e )
            {
                $flashy_helper->addLog("Was not able to do something safely: {$e->getMessage()} \n " . $e->getTraceAsString());
            }
        }

        return null;
    }

    public static function getPixel($account_id)
    {
		$env = self::isDev() === true ? "dev" : "production";

        echo 'window.flashyMetadata = {"platform": "Magento 1.9","version": "1.0.0", "env": "' . $env . '"}; console.log("Flashy Init", flashyMetadata);';

        echo "'use strict'; (function (a, b, c) { if (!a.flashy) { a.flashy = function () { a.flashy.event && a.flashy.event(arguments), a.flashy.queue.push(arguments) }, a.flashy.queue = []; var d = document.getElementsByTagName('script')[0], e = document.createElement(b); e.src = c, e.async = !0, d.parentNode.insertBefore(e, d) } })(window, 'script', '" . self::getBasePixel() . "'), flashy('init', " . $account_id . ");";
    }

    public static function getBasePixel()
    {
        if ( self::isDev() )
            return "https://js.flashy.dev/thunder.js";

        return "https://js.flashyapp.com/thunder.js";
    }

    public static function isDev()
    {
        if (!empty($_SERVER['FLASHY_ENV']) && $_SERVER['FLASHY_ENV'] == "dev") {
            return true;
        }

        return false;
    }
}