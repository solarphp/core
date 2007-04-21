<?php
/**
 * 
 * Log adapter to save one event in multiple logs.
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
 * Log adapter to echo messages directly.
 * 
 * {{code: php
 *     // config for a multiple log
 *     $config = array(
 *         'adapter' => 'Solar_Log_Adapter_Multi', // could also be a dependency object?
 *         'adapters' => array(
 *             array(
 *                 'adapter' => 'Solar_Log_Adapter_File',
 *                 'events' => '*',
 *                 'format' => null,
 *                 'file' => '/path/to/file.log',
 *             ),
 *             array(
 *                 'adapter' => 'Solar_Log_Adapter_Echo',
 *                 'events' => 'debug',
 *                 'format' => null,
 *             ),
 *             array(
 *                 'adapter' => 'Solar_Log_Adapter_Sql',
 *                 'events' => 'warning, severe, critical',
 *                 'sql'    => 'sql',
 *                 'table'  => 'table_name',
 *                 '%t'     => 'ts',
 *                 '%e'     => 'evt',
 *                 '%m'     => 'msg',
 *             ),
 *         ),
 *     );
 *     $log = Solar::factory('Solar_Log', $config);
 * }}
 * 
 * @category Solar
 * 
 * @package Solar_Log
 * 
 */
class Solar_Log_Adapter_Multi extends Solar_Log_Adapter {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `adapters`
     * : (array) An array of arrays, where each sub-array
     *   is a separate adapter configuration.
     * 
     * @var array
     * 
     * @todo make the standard events config key the default for 
     * all sub-adapters.
     * 
     */
    protected $_Solar_Log_Adapter_Multi = array(
        'adapters' => array(
            array(
                'adapter' => 'Solar_Log_Adapter_None',
                'config'  => array(
                    'events'  => '*',
                ),
            ),
        ),
    );
    
    /**
     * 
     * An array of adapter instances.
     * 
     * @var array
     * 
     */
    protected $_adapters = array();
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $events = $this->_config['events'];
        
        // build each sub-adapter
        foreach ($this->_config['adapters'] as $val) {
            
            // basic config
            $class = $val['adapter'];
            $config = empty($val['config']) ? null : $val['config'];
            
            // use default events?
            if (empty($config['events'])) {
                $config['events'] = $events;
            }
            
            $this->_adapters[] = Solar::factory($class, $config);
        }
    }
     
    /**
     * 
     * Attempts to save the log message to each log in the collection.
     * 
     * @param string $class The class name reporting the event.
     * 
     * @param string $event The event type (for example 'info' or 'debug').
     * 
     * @param string $descr A description of the event. 
     * 
     * @return mixed Boolean false if the event was not saved at all
     * (usually because it was not recognized), or an integer count of
     * how many logs saved the message.
     * 
     */
    protected function _save($class, $event, $descr)
    {
        // was the message saved in at least one sub-log?
        $count = false;
        
        // loop through all sub-log adapters and save the event
        foreach ($this->_adapters as $log) {
            $result = $log->save($class, $event, $descr);
            if ($result !== false) {
                $count ++;
            }
        }
        
        // done
        return $count;
    }
}
