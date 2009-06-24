<?php
/**
 * 
 * Factory class for cache adapters.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Cache extends Solar_Factory
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The adapter class for the factory, default 
     * 'Solar_Cache_Adapter_File'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache = array(
        'adapter' => 'Solar_Cache_Adapter_File',
    );
}
