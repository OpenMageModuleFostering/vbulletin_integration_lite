<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Block_Account_Logout extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        // url is set in Aitoc_Aitvbulletin_Model_Observer->customerLogout()
        $logoutUrl = $this->_getSession()->getLogoutUrl(true);
        
        $html = '';
        if ($logoutUrl)
        {
            $html .= '<div class="aitvbulletin-auth-image">';
            $html .= '<img src="' . $logoutUrl . '" alt="" />';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Retrieve customer session model object
     *
     * @return Aitoc_Aitvbulletin_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('aitvbulletin/session');
    }

}