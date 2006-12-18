<?php
/**
 * 
 * Abstract log adapter class.
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
 * Abstract log adapter class.
 * 
 * @category Solar
 * 
 * @package Solar_Log
 * 
 */
abstract class Solar_Log_Adapter extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `events`
     * : (string|array) The event types this instance
     *   should recognize; a comma-separated string of events, or
     *   a sequential array.  Default is all events ('*').
     * 
     * @var array
     * 
     */
    protected $_Solar_Log_Adapter = array(
        'events' => '*',
    );
    
    /**
     * 
     * The event types this instance will recognize, default '*'.
     * 
     * If you try to save an event type not in this list, the adapter
     * should not record it.
     * 
     * @var array
     * 
     * @see Solar_Log_Adapter::save()
     * 
     */
    protected $_events = array('*');
    
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
        if (! empty($this->_config['events'])) {
            $this->setEvents($this->_config['events']);
        }
    }
    
    /**
     * 
     * Saves (writes) an event and message to the log.
     * 
     * {{code: php
     *     $log->save('class_name', 'info', 'informational message');
     *     $log->save('class_name', 'critical', 'critical message');
     *     $log->save('class_name', 'my special event type', 'describing the event');
     * }}
     * 
     * @param string $class The class name logging the event.
     * 
     * @param string $event The event type (typically 'debug', 'info',
     * 'notice', 'severe', 'critical', etc).
     * 
     * @param string $descr A text description of the event.
     * 
     * @return mixed Boolean false if the event was not saved, or a
     * non-empty value if the event was saved (typically boolean true).
     * 
     */
    public function save($class, $event, $descr)
    {
        $save = in_array($event, $this->_events) ||
                in_array('*', $this->_events);
        
        if ($save) {
            return $this->_save($class, $event, $descr);
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Gets the list of events this adapter recognizes.
     * 
     * @return array The list of recognized events.
     * 
     */
    public function getEvents()
    {
        return $this->_events;
    }
    
    /**
     * 
     * Sets the list of events this adapter recognizes.
     * 
     * @param array|string $list The event types this instance
     * should recognize; a comma-separated string of events, or
     * a sequential array.
     * 
     * @return void
     * 
     */
    public function setEvents($list)
    {
        if (is_string($list)) {
            $list = explode(',', $list);
            foreach ($list as $key => $val) {
                $list[$key] = trim($val);
            }
        }
        
        $this->_events = $list;
    }
    
    /**
     * 
     * Gets the current timestamp in ISO-8601 format (yyyy-mm-ddThh:ii:ss).
     * 
     * @return string The current timestamp.
     * 
     */
    protected function _getTime()
    {
        return date('Y-m-d\TH:i:s');
    }
    
    /**
     * 
     * Support method to save (write) an event and message to the log.
     * 
     * @param string $class The class name saving the message.
     * 
     * @param string $event The event type (for example 'info' or 'debug').
     * 
     * @param string $descr A description of the event. 
     * 
     * @return mixed Boolean false if the event was not saved (usually
     * because it was not recognized), or a non-empty value if it was
     * saved.
     * 
     */
    abstract protected function _save($class, $event, $descr);
}
