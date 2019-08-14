<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Block_Admin_Vbserver_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        
        $vbserver = Mage::registry('vbserver');
        $helper = Mage::helper('aitvbulletin');
        /* @var $helper Aitoc_Aitvbulletin_Helper_Data */

        $form->setDataObject($vbserver);
        
        $fieldset = $form->addFieldset('database', 
            array('legend' => $helper->__('Database Settings'))
        );
        
        $fieldset->addField('vbserver_type', 'select', array(
            'label' => $helper->__('Database Type'),
            'title' => $helper->__('Database Type'),
            'name'  => 'type',
            'value' => $vbserver->getType(),
            'values'=> Mage::getModel('aitvbulletin/config_sourceDbtype')->toOptionArray(),
        ));
        $fieldset->addField('vbserver_name', 'text', array(
            'label' => $helper->__('Database Name'),
            'title' => $helper->__('Database Name'),
            'note'  => $helper->__('e.g., %s', 'forum'),
            'name'  => 'name',
            'value' => $vbserver->getName(),
            'required' => true,
        ));
        $fieldset->addField('vbserver_prefix', 'text', array(
            'label' => $helper->__('Table Prefix'),
            'title' => $helper->__('Table Prefix'),
            'name'  => 'prefix',
            'value' => $vbserver->getPrefix(),
        ));
        $fieldset->addField('vbserver_user', 'text', array(
            'label' => $helper->__('Database User'),
            'title' => $helper->__('Database User'),
            'note'  => $helper->__('e.g., %s', 'root'),
            'name'  => 'user',
            'value' => $vbserver->getUser(),
            'required' => true,
        ));
        $fieldset->addField('vbserver_pass', 'text', array(
            'label' => $helper->__('User Password'),
            'title' => $helper->__('User Password'),
            'name'  => 'pass',
            'value' => $vbserver->getPass(),
        ));
        $fieldset->addField('vbserver_host', 'text', array(
            'label' => $helper->__('Database Host'),
            'title' => $helper->__('Database Host'),
            'note'  => $helper->__('e.g., %s', 'localhost'),
            'name'  => 'host',
            'value' => $vbserver->getHost(),
            'required' => true,
        ));
        $fieldset->addField('vbserver_port', 'text', array(
            'label' => $helper->__('Database Port'),
            'title' => $helper->__('Database Port'),
            'note'  => $helper->__('optional, 3306 by default'),
            'name'  => 'port',
            'value' => $vbserver->getPort(),
            'class' => 'validate-digits',
        ));
        
        $fieldset2 = $form->addFieldset('product', 
            array('legend' => $helper->__('Product Settings'))
        );
        
        $fieldset2->addField('vbserver_link', 'text', array(
            'label' => $helper->__('Forum Link'),
            'title' => $helper->__('Forum Link'),
            'note'  => $helper->__('starting with http://'),
            'name'  => 'link',
            'value' => $vbserver->getLink(),
            'class' => 'validate-url',
            'required' => true,
        ));
        $fieldset2->addField('vbserver_apikey', 'text', array(
            'label' => $helper->__('Module API key'),
            'title' => $helper->__('Module API key'),
            'note'  => $helper->__('must be the same as it is set in vBulletin\'s AdminCP'),
            'name'  => 'apikey',
            'value' => $vbserver->getApikey(),
            'required' => true,
        ));
        
        $form->setFieldNameSuffix('vbserver');
        $this->setForm($form);
    }

}
