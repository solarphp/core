<?php

class Solar_Model_Nodes_Revisions extends Solar_Model_Nodes {
    protected function _setup()
    {
        parent::_setup();
        
        /**
         * Relationships.
         */
        $this->_belongsTo('node', array(
            'foreign_class' => 'node',
            'foreign_key'   => 'parent_id',
        ));
    }
}
