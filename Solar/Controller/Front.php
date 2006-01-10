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

    protected $config = array(
        'default_app' => 'bookmarks',
    );
    
    protected $_default_app;
    
    protected $_map = array(
        'bookmarks' => 'Solar_App4_Bookmarks',
    );
    
    public function __construct($config)
    {
        parent::__construct($config);
        $this->_default_app = $config['default_app'];
    }
    
    // $spec is "app/action/info?qstr#frag"
    public function fetch($spec = null)
    {
        // default to current URI
        $uri = Solar::object('Solar_Uri');
        
        // override current URI with user spec
        if (is_string($spec)) {
            $uri->import($spec);
        }
        
        // pull the app name off the top of the path_info
        $name = array_shift($uri->info);
        
        // instantiate the app class
        $class = $this->_map[$name];
        $app = Solar::object($class);
        $content = $app->fetch($uri);
        return $content;
    }
    
    public function display($spec = null)
    {
        echo $this->fetch($spec);
    }
}
?>