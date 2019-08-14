<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Block_Admin_Vbserver_Edit extends Mage_Adminhtml_Block_Widget
{
    const AITOC_MODULE_LINK = 'http://www.aitoc.com/en/magentomods_vbulletin_integration_lite.html';
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitvbulletin/vbserver/edit.phtml');
        $this->setId('vbserver_edit');
    }

    /**
     * Retrieve currently edited vbserver object
     *
     * @return Aitoc_Aitvbulletin_Model_Vbulletin_Server
     */
    public function getVbserver()
    {
        return Mage::registry('vbserver');
    }

    public function getVbserverId()
    {
        return $this->getVbserver()->getId();
    }

    protected function _prepareLayout()
    {
        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Reset'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/*/*', array('_current'=>true)).'\')'
                ))
        );

        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Save'),
                    'onclick'   => 'vbserverForm.submit()',
                    'class'     => 'save',
                ))
        );
        
        return parent::_prepareLayout();
    }

    // buttons html
    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    // urls
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
    
    public function getVbDownloadLinkHtml()
    {
        return '<a href="'.self::AITOC_MODULE_LINK.'">'.Mage::helper('aitvbulletin')->__('vBulletin to Magento Bridge').'</a>';
    }
    
}
