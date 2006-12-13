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
        'sql'   => 'sql',
        'areas' => 'Solar_Model_Areas',
        'nodes' => 'Solar_Model_Nodes',
        'tags'  => 'Solar_Model_Tags',
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
        $model_config = array('sql' => $this->_config['sql']);
        $this->areas = Solar::dependency($this->_config['areas'], $model_config);
        $this->nodes = Solar::dependency($this->_config['nodes'], $model_config);
        $this->tags  = Solar::dependency($this->_config['tags'],  $model_config);
    }
}
