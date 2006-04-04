<?php
/**
 * 
 * Generic content management class.
 * 
 * @category Solar
 * 
 * @package Solar_Content
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Generic content management class.
 * 
 * @category Solar
 * 
 * @package Solar_Content
 * 
 * @todo Build in content permission system.
 * 
 */
class Solar_Content extends Solar_Base {
    
    /**
     * 
     * A table object representing the broad areas of content.
     * 
     * @var Solar_Model_Areas
     * 
     */
    public $areas;
    
    /**
     * 
     * A table object representing the container nodes in an area.
     * 
     * @var Solar_Model_Nodes
     * 
     */
    public $nodes;
    
    /**
     * 
     * A table object representing the searchable tags on each node.
     * 
     * @var Solar_Model_Tags
     * 
     */
    public $tags;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->areas = Solar::factory('Solar_Model_Areas');
        $this->nodes = Solar::factory('Solar_Model_Nodes');
        $this->tags  = Solar::factory('Solar_Model_Tags');
    }
}
?>