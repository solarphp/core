<?php
class Solar_Model_Nodes_Blogs extends Solar_Model_Nodes {
    protected function _setup()
    {
        parent::_setup();
        
        /**
         * Relationships.
         */
        
        $this->_belongsTo('area', array(
            'foreign_class' => 'areas',
            'foreign_key'   => 'area_id',
        ));
        
        $this->_hasMany('comments', array(
            'foreign_class' => 'comments',
            'foreign_key'   => 'parent_id',
        ));
    }
}
