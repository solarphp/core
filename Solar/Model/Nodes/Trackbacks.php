<?php
class Solar_Model_Nodes_Trackbacks extends Solar_Model_Nodes {
    protected function _setup()
    {
        parent::_setup();
        $this->_belongsTo('node', array(
            'foreign_class' => 'nodes',
            'foreign_key'   => 'parent_id', // normally node_id
        ));
        
        $this->_fetch_cols = array('id', 'created', 'updated', 'subj', 'body',
            'inherit', 'area_id', 'parent_id', 'moniker', 'email', 'uri', );
    }
}
