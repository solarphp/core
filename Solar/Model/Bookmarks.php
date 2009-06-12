<?php
/**
 * 
 * A model for bookmark nodes.
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
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
