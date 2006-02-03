<?php
/**
 * 
 * Default template system for Solar; extended from Savant3.
 * 
 * @category Solar
 * 
 * @package Solar_Template
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * This class is extended from Savant3.
 * @link http://phpsavant.com/
 */
include_once dirname(__FILE__) . '/Template/Savant3.php';

/**
 * 
 * Default template system for Solar; extended from Savant3.
 * 
 * @category Solar
 * 
 * @package Solar_Template
 * 
 */
class Solar_Template extends Savant3 {
    
    /**
     *
     * Constructor.
     * 
     * @access public
     * 
     */
    public function __construct($config = null)
    {
        // get the user config from the Solar.config.php file, if any.
        $class = get_class($this);
        $default = Solar::config($class, null, array());
        
        // ... then merge the passed user config ...
        settype($config, 'array');
        $config = array_merge($default, $config);
        
        // use exceptions
        $config['exceptions'] = true;
        
        // find the Solar/Template/Plugin directory...
        $dir = dirname(__FILE__) . '/Template/Plugin/';
        
        // ... and add it at the top so it becomes the default fallback
        // (just before the Savant3 defaults).
        if (! isset($config['resource_path']) ||
            empty($config['resource_path'])) {
            
            // not set, or empty/null/blank
            $config['resource_path'] = array($dir);
            
        } elseif (is_string($config['resource_path'])) {
            
            // path string
            $config['resource_path'] = $dir . DIRECTORY_SEPARATOR .
                $config['resource_path'];
                
        } elseif (is_array($config['resource_path'])) {
            
            // array of paths
            array_unshift($config['resource_path'], $dir);
            
        }
        
        // ... and pass to the Savant3 constructor.
        parent::__construct($config);
    }
}
?>