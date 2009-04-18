<?php
/**
 * 
 * Inherited model class.
 * 
 */
class Solar_Model_Bookmarks extends Solar_Model_Nodes {
    
    /**
     * 
     * Model-specific setup.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        parent::_setup();
        $this->_addFilter('uri', 'validateNotBlank');
        $this->_addFilter('tags_as_string', 'validateNotBlank');
    }
}
