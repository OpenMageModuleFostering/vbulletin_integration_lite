<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Block_Config_FormFieldset 
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    
    public function setGroup(Mage_Core_Model_Config_Element $group)
    {
        $_vbulletin_setup = true;
        try 
        {
            $_vbulletin_enabled = Mage::helper('aitvbulletin')->isVbulletinActive();
        }
        catch (Aitoc_Aitvbulletin_Model_Exception $ex)
        {
            $_vbulletin_setup = false;
        }
        
        foreach ($group->fields as $elements) 
        {
            foreach ($elements as $e) 
            {
                /* @var $e Mage_Core_Model_Config_Element */
                
                $_err = '';
                switch ($e->getName())
                {
                    case 'enabled':
                        if (!$_vbulletin_setup)
                        {
                            $_err .= Mage::helper('aitvbulletin')->__('Please setup vBulletin Connection first');
                        }
                        elseif (!$_vbulletin_enabled)
                        {
                            $_err .= Mage::helper('aitvbulletin')->__('Integration will not be really enabled until vBulletin is turned off.');
                        }
                    break;
                }
                
                if ($_err)
                {
                    $e->comment = '<strong class="required">'.$_err.'</strong>';
                }
            }
        }
        
        $newsBlock = $this->getLayout()->createBlock('aitvbulletin/admin_news', 'aitoc_news', array());
        /* @var $newsBlock Aitoc_Aitvbulletin_Block_Admin_News */
        $group->comment = $newsBlock->toHtml();
        
        return parent::setData('group', $group);
    }
    
}