<?php
/**
 * 
 * Front-controller class for Solar.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id: Front.php 320 2005-06-22 20:39:27Z pmjones $
 * 
 */

/**
 * 
 * Front-controller class for Solar.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @todo How to get data back from the app to use in the layout (e.g., html title?)
 * 
 */

class Solar_Controller_Front extends Solar_Base {

    /**
     * 
     * User-defined configuration array.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'default_app' => 'bookmarks',
    );

    /**
     * 
     * The default application to run when none is specified.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_default_app;

    /**
     * 
     * Map of front-controller app names to actual application classes.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_map = array(
        'bookmarks' => 'Solar_App_Bookmarks',
    );

    /**
     * 
     * Constructor.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->_default_app = $this->_config['default_app'];
        $this->_map = $this->_config['map'];
    }

    /**
     * 
     * Fetches the output of a front-controller specification URI.
     * 
     * @access public
     * 
     * @param string $spec An app spec for the front controller.
     * E.g., 'bookmarks/user/pmjones/php+blog?page=2'.
     * 
     * @return string The output of the application.
     * 
     */
    public function fetch($spec = null)
    {
        // default to current URI
        $uri = Solar::object('Solar_Uri');
        
        // override current URI with user spec
        if (is_string($spec)) {
            // this won't work if there's no domain, path, etc.
            $uri->import($spec);
        }
        
        // pull the app name off the top of the path_info
        $name = array_shift($uri->info);
        
        // instantiate the app class and fetch content
        $class = $this->_map[$name];
        $app = Solar::object($class);
        $content = $app->fetch($uri);
        return $content;
    }

    /**
     * 
     * Displays the output of a front-controller specification URI.
     * 
     * @access public
     * 
     * @param string $spec An app spec for the front controller.
     * E.g., 'bookmarks/user/pmjones/php+blog?page=2'.
     * 
     * @return string The output of the application.
     * 
     */
    public function display($spec = null)
    {
        echo $this->fetch($spec);
    }
}
?>