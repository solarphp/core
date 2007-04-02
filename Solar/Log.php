<?php
/**
 * 
 * Factory for a log adapter.
 * 
 * @category Solar
 * 
 * @package Solar_Log
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
 * Factory for a log adapter.
 * 
 * {{code: php
 *     // example setup of a single adapter
 *     $config = array(
 *         'adapter' => 'Solar_Log_Adapter_File',
 *         'events'  => '*',
 *         'file'    => '/path/to/file.log',
 *     );
 *     $log = Solar::factory('Solar_Log', $config);
 *     
 *     // write/record/report/etc an event in the log.
 *     // note that we don't do "priority levels" here, just
 *     // class names and event types.
 *     $log->save('class_name', 'event_name', 'message text');
 * }}
 * 
 * @category Solar
 * 
 * @package Solar_Log
 * 
 */
class Solar_Log extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `adapter`
     * : (string) The adapter class to use, for example 'Solar_Log_Adapter_File'.
     *   Default is 'Solar_Log_Adapter_None'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Log = array(
        'adapter' => 'Solar_Log_Adapter_None',
    );
    
    /**
     * 
     * Factory method for returning adapters.
     * 
     * @return Solar_Log_Adapter
     * 
     */
    public function solarFactory()
    {
        // bring in the config and get the adapter class.
        $config = $this->_config;
        $class = $config['adapter'];
        unset($config['adapter']);
        
        // deprecated: support a 'config' key for the adapter configs.
        // this was needed for facades, but is not needed for factories.
        if (isset($config['config'])) {
            $tmp = $config['config'];
            unset($config['config']);
            $config = array_merge($config, (array) $tmp);
        }
        
        return Solar::factory($class, $config);
    }
}
