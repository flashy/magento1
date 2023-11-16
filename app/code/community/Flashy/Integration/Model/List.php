<?php

use Flashy\Helper;

class Flashy_Integration_Model_List
{
    public $flashy;

    public function toOptionArray()
    {
        $options = array();

        if (Mage::getStoreConfig( 'flashy/flashy/flashy_key' ) !== '')
        {
            $this->flashy = new \Flashy\Flashy(array(
                'api_key' => Mage::getStoreConfig('flashy/flashy/flashy_key'),
                'log_path' => Mage::getBaseDir( 'var' ) . '\log\flashy.log'
            ));

            $lists = Helper::tryOrLog( function () {
                return $this->flashy->lists->get();
            });

            $options[] = array(
                'value' => strval(''),
                'label' => 'Choose a list'
            );

            if(isset($lists)) {
                foreach ($lists->getData() as $list) {
                    $options[] = array(
                        'value' => strval($list['id']),
                        'label' => $list['title']
                    );
                }
            }
        }

        return $options;
    }
}