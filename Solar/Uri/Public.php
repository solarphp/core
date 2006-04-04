<?php
/**
 * 
 * Manipulates and generates public URI strings.
 * 
 * @category Solar
 * 
 * @package Solar_Uri
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id: Uri.php 1035 2006-04-04 13:24:44Z pmjones $
 * 
 */

/**
 * Base URI class.
 */
Solar::loadClass('Solar_Uri');

/**
 * 
 * Manipulates generates public URI strings.
 * 
 * @category Solar
 * 
 * @package Solar_Uri
 * 
 */
class Solar_Uri_Public extends Solar_Uri {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are:
     * 
     * : \\path\\ : (string) A path prefix specifically for public 
     * resources, e.g. '/public/'.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'path' => '/public/',
    );
}
?>