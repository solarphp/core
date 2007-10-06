<?php
class Solar_Model_Nodes_Bookmarks extends Solar_Model_Nodes {
    protected function _setup()
    {
        parent::_setup();
        $this->_addFilter('uri', 'validateNotBlank');
        $this->_addFilter('tags_as_string', 'validateNotBlank');
    }
}
