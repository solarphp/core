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
 * @version $Id$
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
        'app_default' => 'bookmarks',
        'app_class'   => array(
            'bookmarks' => 'Solar_App_Bookmarks',
        ),
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
    protected $_app_default;

    /**
     * 
     * Map of front-controller app names to actual application classes.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_app_class;

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
        $this->_app_default = $this->_config['app_default'];
        $this->_app_class = $this->_config['app_class'];
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
        $uri = Solar::factory('Solar_Uri');
        
        // override current URI with user spec
        if (is_string($spec)) {
            // this won't work if there's no domain, path, etc.
            $uri->import($spec);
        }
        
        // pull the app name off the top of the path_info
        $app = array_shift($uri->info);
        
        // instantiate the app class and fetch content
        $class = $this->_app_class[$app];
        $app = Solar::factory($class);
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