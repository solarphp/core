<?php
/**
 * 
 * Facade for a log adapter.
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
 * 
 * Facade for a log adapter.
 * 
 * Example:
 * 
 * <code type="php">
 * 
 * // example setup of a single adapter
 * $config = array(
 *     'adapter' => 'Solar_Log_Adapter_File',
 *     'events'  => '*',
 *     'file'    => '/path/to/file.log',
 * );
 * $log = Solar::factory('Solar_Log', $config);
 * 
 * // write/record/report/etc an event in the log.
 * // note that we don't do "priority levels" here, just
 * // event types.
 * $log->save('event_name', 'message text');
 * 
 * </code>
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 */
class Solar_Log extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are:
     * 
     * : \\adapter\\ : (string) The adapter class to use, e.g. 'Solar_Log_Adapter_File'.
     *   Default is 'Solar_Log_Adapter_None'.
     * 
     * All other keys are passed to the adapter class as its $config values.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'adapter' => 'Solar_Log_Adapter_None',
    );
    
    /**
     * 
     * The internal Solar_Log_Adapter instance.
     * 
     * @var Solar_Log_Adapter
     * 
     */
    protected $_adapter;
    
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
        $adapter_config = $this->_config;
        unset($adapter_config['adapter']);
        $this->_adapter = Solar::dependency(
            $this->_config['adapter'],
            $adapter_config
        );
    }
    
    /**
     * 
     * Magic shorthand for saving an event using a method name.
     * 
     * <code type="php">
     * // these are equivalent:
     * $log->save('info', 'informational message');
     * $log->info('informational message');
     * </code>
     * 
     * @param string $method The event type.
     * 
     * @param array $params Additional parameters; only the first
     * paramter is used (the message to log).
     * 
     */
    public function __call($method, $params)
    {
        if (! empty($params[0])) {
            return $this->save($method, $params[0]);
        } else {
            // will throw a PHP warning about missing second param
            return $this->save($method);
        }
    }
    
    /**
     * 
     * Saves (writes) an event and message to the log.
     * 
     * <code type="php">
     * // these are equivalent:
     * $log->save('info', 'informational message');
     * $log->save('critical', 'critical message');
     * $log->save('my special event type', 'describing the event');
     * </code>
     * 
     * @param string $event The event type (typically 'debug', 'info',
     * 'notice', 'severe', 'critical', etc).
     * 
     * @param string $message A text description of the event.
     * 
     * @return mixed Boolean false if the event was not saved, or a
     * non-empty value if the event was saved (typically boolean true).
     * 
     */
    public function save($event, $message)
    {
        return $this->_adapter->save($event, $message);
    }
}
?>