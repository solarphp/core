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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Content.php 1862 2006-09-26 00:14:31Z pmjones $
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
     * User-provided configuration values.
     * 
     * Keys are ...
     * 
     * `sql`
     * : (dependency) A Solar_Sql dependency injection, passed
     *   into the table objects at creation time.
     * 
     * @var array
     * 
     */
    protected $_Solar_Content = array(
        'sql' => 'sql',
    );
    
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
        $this->areas = Solar::factory('Solar_Model_Areas', $this->_config);
        $this->nodes = Solar::factory('Solar_Model_Nodes', $this->_config);
        $this->tags  = Solar::factory('Solar_Model_Tags', $this->_config);
    }
}
?>