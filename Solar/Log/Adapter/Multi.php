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
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * Solar_Log_Adapter
 */
Solar::loadClass('Solar_Log_Adapter');

/**
 * 
 * Log adapter to echo messages directly.
 * 
 * <code type="php">
 * // config for a multiple log
 * $config = array(
 *     'adapter' => 'Solar_Log_Adapter_Multiple', // could also be a dependency object?
 *     'adapters' => array(
 *         array(
 *             'adapter' => 'Solar_Log_Adapter_File',
 *             'events' => '*',
 *             'format' => null,
 *             'file' => '/path/to/file.log',
 *         ),
 *         array(
 *             'adapter' => 'Solar_Log_Adapter_Echo',
 *             'events' => 'debug',
 *             'format' => null,
 *         ),
 *         array(
 *             'adapter' => 'Solar_Log_Adapter_Sql',
 *             'events' => 'warning, severe, critical',
 *             'sql'    => 'sql',
 *             'table'  => 'table_name',
 *             '%t'     => 'ts',
 *             '%e'     => 'evt',
 *             '%m'     => 'msg',
 *         ),
 *     ),
 * );
 * $log = Solar::factory('Solar_Log', $config);
 * </code>
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 */
class Solar_Log_Adapter_Multi extends Solar_Log_Adapter {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are:
     * 
     * : \\adapters\\ : (array) An array of arrays, where each sub-array
     * is a separate adapter configuration.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'adapters' => array(
            array(
                'adapter' => 'Solar_Log_Adapter_None',
                'events'  => '*',
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
        foreach ($this->_config['adapters'] as $adapter_config) {
            $class = $adapter_config['adapter'];
            unset($adapter_config['adapter']);
            $this->_adapters[] = Solar::dependency($class, $adapter_config);
        }
    }
     
    /**
     * 
     * Attempts to save the log message to each log in the collection.
     * 
     * @param string $event The event type (e.g. 'info' or 'debug').
     * 
     * @param string $descr A description of the event. 
     * 
     * @return mixed Boolean false if the event was not saved at all
     * (usually because it was not recognized), or an integer count of
     * how many logs saved the message.
     * 
     */
    protected function _save($event, $descr)
    {
        // was the message saved in at least one sub-log?
        $count = false;
        
        // loop through all sub-log adapters and save the event
        foreach ($this->_adapters as $log) {
            $result = $log->save($event, $descr);
            if ($result !== false) {
                $count ++;
            }
        }
        
        // done
        return $count;
    }
}
?>