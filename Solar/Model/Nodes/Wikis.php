<?php
class Solar_Model_Nodes_Wikis extends Solar_Model_Nodes {
    protected function _setup()
    {
        parent::_setup();
        
        /**
         * Relationships.
         */
        $this->_hasMany('revisions', array(
            'foreign_class' => 'revision',
            'foreign_key'   => 'parent_id',
            'order'         => 'id DESC',
        ));
        
        $this->_hasMany('comments', array(
            'foreign_class' => 'comment',
            'foreign_key'   => 'parent_id',
        ));
    }
}
