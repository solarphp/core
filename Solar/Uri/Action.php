<?php
/**
 * 
 * Manipulates and generates action URI strings.
 * 
 * This class is functionally identical to Solar_Uri, except that it
 * automatically adds a prefix to the "path" portion of all URIs.  This
 * makes it easy to work with front-controller and page-controller URIs.
 * 
 * Use the Solar_Uri_Action::$_config key for 'path' to specify
 * the path prefix leading to the front controller, if any.
 * 
 * @category Solar
 * 
 * @package Solar_Uri
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Uri_Action extends Solar_Uri
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string path A path prefix specifically for actions.  If Apache has used
     *   `SetEnv SOLAR_URI_ACTION_PATH /`, then that is the default value for
     *   this item; otherwise, the default value is "/index.php".
     * 
     * @var array
     * 
     */
    protected $_Solar_Uri_Action = array(
        'path' => '/index.php',
    );
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config Configuration value overrides, if any.
     * 
     */
    public function __construct($config = null)
    {
        // get the request environment
        $this->_request = Solar_Registry::get('request');
        
        // in a standard solar system, when mod_rewrite is turned on, it
        // may "SetEnv SOLAR_URI_ACTION_PATH /" as a hint for the default
        // action path. this lets you go from no-rewriting to rewriting in
        // one easy step, rather than having to remember to change the action
        // path in the solar config file as well.
        $this->_Solar_Uri_Action['path'] = $this->_request->server(
            'SOLAR_URI_ACTION_PATH',
            '/index.php'
        );
        
        // now do the real construction
        parent::__construct($config);
    }
}
