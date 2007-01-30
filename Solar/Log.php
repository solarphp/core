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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Log.php 1862 2006-09-26 00:14:31Z pmjones $
 * 
 */

/**
 * 
 * Facade for a log adapter.
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
     * : (string) The adapter class to use, e.g. 'Solar_Log_Adapter_File'.
     *   Default is 'Solar_Log_Adapter_None'.
     * 
     * `config`
     * : (array) Configuration to pass to the adapter.
     * 
     * @var array
     * 
     */
    protected $_Solar_Log = array(
        'adapter' => 'Solar_Log_Adapter_None',
        'config'  => null,
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
        $class = $this->_config['adapter'];
        $config = empty($this->_config['config']) ? null : $this->_config['config'];
        $this->_adapter = Solar::factory($class, $config);
    }
    
    /**
     * 
     * Magic shorthand for saving an event using a method name.
     * 
     * {{code: php
     *     // these are equivalent ...
     *     $log->save('info', 'informational message');
     *     $log->info('informational message');
     * }}
     * 
     * @param string $method The event type.
     * 
     * @param array $params Additional parameters; only the first
     * paramter is used (the message to log).
     * 
     * @return mixed Boolean false if the event was not saved, or a
     * non-empty value if the event was saved (typically boolean true).
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
     * {{code: php
     *     $log->save('info', 'informational message');
     *     $log->save('critical', 'critical message');
     *     $log->save('my special event type', 'describing the event');
     * }}
     * 
     * @param string $class The class name logging the event.
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
    public function save($class, $event, $message)
    {
        return $this->_adapter->save($class, $event, $message);
    }
}
?>