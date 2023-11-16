<?php

class Flashy_Integration_Block_Adminhtml_Form_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('edit_home_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('flashy')->__('flashy'));
    }

    /**
     * add tabs before output
     *
     * @return Flashy_Block_Adminhtml_Form_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('flashy')->__('Export'),
            'title'     => Mage::helper('flashy')->__('Export'),
            'content'   => $this->getLayout()->createBlock('flashy/adminhtml_form_edit_tab_general')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}