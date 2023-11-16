<?php

class Flashy_Integration_Block_Adminhtml_Form_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'flashy';
        $this->_controller = 'adminhtml_form';
        $this->_headerText = Mage::helper('flashy')->__('Export Products');
    }

}