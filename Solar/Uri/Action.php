<?php
/**
 * 
 * Manipulates and generates action URI strings.
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

/**
 * Base URI class.
 */
Solar::loadClass('Solar_Uri');

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
 */
class Solar_Uri_Action extends Solar_Uri {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are ...
     * 
     * `path`
     * : (string) A path prefix specifically for actions, for example '/index.php/'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Uri_Action = array(
        'path' => '/index.php/',
    );
}
