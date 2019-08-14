<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitvbulletin_Model_Exception extends Exception 
{
    protected $_field;

    public function setField($field)
    {
        $this->_field = $field;
        return $this;
    }

    public function getField()
    {
        return $this->_field;
    }

}