<?php

use Flashy\Helper;

class Flashy_Integration_Model_System_Config_Backend_Key extends Mage_Core_Model_Config_Data
{
    public $flashy;

    /**
     * @return Mage_Core_Model_Abstract
     * @throws Flashy_Error
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave()
    {
        if($this->getValue() != '') {

            $flashy_helper = Mage::helper('flashy');

            $this->flashy = new \Flashy\Flashy(array(
                'api_key' => $this->getValue(),
                'log_path' => Mage::getBaseDir( 'var' ) . '\log\flashy.log'
            ));

            $info = Helper::tryOrLog( function () {
                return $this->flashy->account->get();
            });

            if($info == null || !$info->success()) {
                throw Mage::exception(
                    'Mage_Core', Mage::helper('flashy')->__('Flashy API Key is not valid.')
                );
            }
        }

        return parent::_beforeSave();
    }

    /**
     * @return Mage_Core_Model_Abstract
     * @throws Flashy_Error
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function _afterSave()
    {
        $api_key = $this->getValue();
        $scope = $this->getScope();
        $scope_id = $this->getScopeId();
        if($api_key == ''){
            $value = 0;
            $flashy_id = 0;
        }
        else {
            $store_email = Mage::getStoreConfig('trans_email/ident_general/email', $scope_id);
            $store_name = Mage::getStoreConfig('general/store_information/name', $scope_id);
            $base_url = Mage::app()->getStore($scope_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

            if(empty($store_name)){
                $store = Mage::getModel('core/store')->load($scope_id);
                $store_name = $store->getName();
            }

            $data = array(
                "profile" => array(
                    "from_name" => $store_name,
                    "from_email" => $store_email,
                    "reply_to" => $store_email,
                ),
                "store"	=> array(
                    "platform" => "magento",
                    "api_key" => $api_key,
                    "store_name" => $store_name,
                    "store" => $base_url,
                    "debug" => array(
                        "magento" => Mage::getVersion(),
                        "php" => phpversion(),
                        "memory_limit" => ini_get('memory_limit'),
                    ),
                )
            );

            $entities = array('products', 'contacts', 'orders');

            foreach ($entities as $entity)
            {
                $data[$entity] = array(
                    "url" => $base_url . "flashy?export=$entity&store_id=$scope_id&flashy_pagination=true&flashy_key=$api_key&limit=100&page=1",
                    "format" => "json_url",
                );
            }

            $this->flashy = new \Flashy\Flashy(array(
                'api_key' => $api_key,
                'log_path' => Mage::getBaseDir( 'var' ) . '\log\flashy.log'
            ));

            $connect = Helper::tryOrLog( function () use($data) {
                return $this->flashy->platforms->connect($data);
            });

            $info = Helper::tryOrLog( function () {
                return $this->flashy->account->get();
            });

            if( isset($info) && $info->success() == true ) {
                $flashy_id = $info->getData()['id'];
                $value = 1;
            }
        }

        Mage::getConfig()->saveConfig('flashy/flashy/flashy_connected', $value, $scope, $scope_id);
        Mage::getConfig()->saveConfig('flashy/flashy/flashy_id', $flashy_id, $scope, $scope_id);

        return parent::_afterSave();
    }

    protected function _afterDelete()
    {
        Mage::getConfig()->deleteConfig('flashy/flashy/flashy_connected', $this->getScope(), $this->getScopeId());
        Mage::getConfig()->deleteConfig('flashy/flashy/flashy_id', $this->getScope(), $this->getScopeId());

        return parent::_afterDelete();
    }
}