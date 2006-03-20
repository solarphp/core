<?php
/**
 * 
 * Abstract Solar_View_Helper class.
 * 
 * @category Solar
 * 
 * @package Solar_View
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
 * Abstract Solar_View_Helper class.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

abstract class Solar_View_Helper extends Solar_Base {
    
    /**
     * 
     * Reference to the parent Solar_View object.
     * 
     * @access protected
     * 
     * @var Solar_View
     * 
     */
    protected $_view;
    
    /**
     * 
     * Constructor.
     * 
     * @access public
     * 
     * @param array $conf An array of configuration keys and values for
     * this plugin.
     * 
     * @return void
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        if (empty($this->_config['_view']) ||
            ! $this->_config['_view'] instanceof Solar_View) {
            // we need the parent view object
            throw Solar::exception(
                get_class($this),
                'ERR_VIEW_NOT_SET',
                "Config key '_view' not set, or not Solar_View object"
            );
        }
        $this->_view = $this->_config['_view'];
        unset($this->_config['_view']);
    }
}
?>