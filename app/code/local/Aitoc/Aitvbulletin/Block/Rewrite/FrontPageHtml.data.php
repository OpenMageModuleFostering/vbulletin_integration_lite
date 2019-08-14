<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Block_Rewrite_FrontPageHtml extends Mage_Page_Block_Html
{
    public function getAbsoluteFooter()
    {
        $html = '';
        
        if (Mage::helper('aitvbulletin')->isModuleEnabled() AND 
            $this->_getCustomerSession()->isLoggedIn() AND 
            $this->_getCustomerSession()->getCustomer()->getAitvbulletinUserId() )
        {
            $aVars = array(
                'do' => 'login',
                'userid'  => $this->_getCustomerSession()->getCustomer()->getAitvbulletinUserId(),
            );
            $model = Mage::getModel('aitvbulletin_vbulletin/user');
            /* @var $model Aitoc_Aitvbulletin_Model_Vbulletin_User */
            $resource = $model->getResource();
            /* @var $resource Aitoc_Aitvbulletin_Model_Vbulletin_Mysql4_User */
            $forumUrl = $resource->getProxyUrl($aVars, $aVars['do'].$aVars['userid']);
            
            $html .= '<div class="aitvbulletin-auth-image">';
            $html .= '<img src="' . $forumUrl . '" alt="" />';
            $html .= '</div>';
        }
        
        return parent::getAbsoluteFooter() . $html;
    }
    
    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
}