<?php

class Flashy_Integration_Model_Resource_Carthash extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('flashy/carthash', 'id');
    }
}