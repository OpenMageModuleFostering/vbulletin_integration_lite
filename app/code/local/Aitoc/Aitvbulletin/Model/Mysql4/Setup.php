<?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitvbulletin_Model_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
    public function getDefaultEntities()
    {
        return array(
            'customer' => array(
                'entity_model'         => 'customer/customer' ,
                'attribute_model'      => '' ,
                'table'                => 'customer/entity' ,
                'increment_model'      => 'eav/entity_increment_numeric' ,
                'increment_per_store'  => 0,
                'attributes'        => array(
                    'aitvbulletin_user_id' => array(
                        'type'              => 'int',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'Forum User ID',
                        'input'             => '',
                        'default'           => '0',
                        'class'             => '',
                        'source'            => '',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                        'visible'           => false,
                        'required'          => false,
                        'user_defined'      => false,
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'visible_in_advanced_search' => false,
                        'unique'            => false,
                    ),
                ),
            ),
        );
    }
}
