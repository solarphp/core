<?php
/**
 * 
 * A model of nodes used as bookmarks.
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

/**
 * 
 * A model of nodes used as bookmarks.
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 */
class Solar_Model_Nodes_Bookmarks extends Solar_Model_Nodes {
    
    /**
     * 
     * Model setup.
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
