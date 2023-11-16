<?php

class Flashy_Integration_Block_Adminhtml_Form_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    public $flashy;

    /**
     * prepare form in tab
     */
    protected function _prepareForm()
    {
        $flashy_helper = Mage::helper('flashy');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('general_');
        $form->setFieldNameSuffix('general');

        if ( Mage::getStoreConfig( 'flashy/flashy/flashy_key' ) === '' )
        {
            Mage::getModel('core/session')->addError(Mage::helper('flashy')->__('Error! Flashyapp API Key missing.'));

            $this->_redirect('*/*/');
        }
        else
        {
            $this->flashy = new \Flashy\Flashy(array(
                'api_key' => Mage::getStoreConfig('flashy/flashy/flashy_key'),
                'log_path' => Mage::getBaseDir( 'var' ) . '\log\flashy.log'
            ));

            $info = $flashy_helper->tryOrLog( function () {
                return $this->flashy->account->get();
            });

            if( $info->success() == true )
            {
                Mage::getConfig()->saveConfig('flashy/flashy/flashy_id', $info['account']['id'], 'default', 0);

                $catalog_id = Mage::getStoreConfig('flashy/flashy/flashy_catalog');

                $catalogs = $flashy_helper->tryOrLog( function () {
                    return $this->flashy->catalogs->get();
                });

                foreach ($catalogs['catalogs'] as $catalog)
                {
                    $options[strval($catalog['id'])] = $catalog['title'];
                }

                $fieldset = $form->addFieldSet(
                    'general',
                    array('legend' => 'Export Products')
                );

                if (!Mage::app()->isSingleStoreMode())
                {
                    $fieldset->addField('stores', 'select', array(
                        'name'      => 'store',
                        'label'     => $flashy_helper->__('Select Store'),
                        'title'     => $flashy_helper->__('Select Store'),
                        'required'  => true,
                        'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(true, true),
                    ));
                }
                else {
                    $fieldset->addField('stores', 'hidden', array(
                        'name'      => 'store',
                        'value'     => Mage::app()->getStore(true)->getId()
                    ));
                }

                $select = array(
                    'label'     => $flashy_helper->__('Catalog'),
                    'class'     => 'required-entry',
                    'required'  => true,
                    'name'      => 'catalog',
                    'onclick'   => "",
                    'onchange'  => "",
                    'values'    => $options,
                    'disabled'  => false,
                    'readonly'  => false,
                    'note'     => 'You can create new catalog on Flashyapp.com',
                    'tabindex'  => 1
                );

                if( $catalog_id !== '' ) $select['value'] = $catalog_id;

                $fieldset->addField('catalog', 'select', $select);

                $fieldset->addField('submit', 'submit', array(
                    'name'  => 'export',
                    'value'  => 'Export Products',
                    'after_element_html' => '<small></small>',
                    'class' => 'form-button',
                    'tabindex' => 2
                ));
            }
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

}