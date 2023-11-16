<?php
class Flashy_Integration_Model_Resource_Carthash_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('flashy/carthash');
    }
}