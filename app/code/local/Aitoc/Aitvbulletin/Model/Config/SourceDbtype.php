<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitvbulletin_Model_Config_SourceDbtype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'mysql',  'label' => Mage::helper('aitvbulletin')->__('MySQL')),
            array('value' => 'mysqli', 'label' => Mage::helper('aitvbulletin')->__('MySQLi')),
        );
    }
}