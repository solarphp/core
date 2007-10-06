<?php
/**
 * 
 * Log adapter to echo messages directly.
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
 * @category Solar
 * 
 * @package Solar_Log
 * 
 */
class Solar_Log_Adapter_Echo extends Solar_Log_Adapter {
    
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
     * `format`
     * : (string) The line format for each saved event.
     *   Use '%t' for the timestamp, '%e' for the class name, '%e' for
     *   the event type, '%m' for the event description, and '%%' for a
     *   literal percent.  Default is '%t %c %e %m'.
     * 
     * `output`
     * : (string) Output mode.  Set to 'html' for HTML; 
     *   or 'text' for plain text.  Default autodetects by SAPI version.
     * 
     * @var array
     * 
     */
    protected $_Solar_Log_Adapter_Echo = array(
        'events' => '*',
        'format' => '%t %c %e %m', // time, class, event, message
        'output' => null,
    );
    
    /**
     * 
     * Constructor.  Detect output mode by SAPI if none is specified.
     * 
     * @param array $config User-defined configuration.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        if (empty($this->_config['output'])) {
            $mode = (PHP_SAPI == 'cli') ? 'text' 
                                        : 'html';
            $this->_config['output'] = $mode;
        }
    }
    
    /**
     * 
     * Echos the log message.
     * 
     * @param string $class The class name reporting the event.
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
    protected function _save($class, $event, $descr)
    {
        $text = str_replace(
            array('%t', '%c', '%e', '%m', '%%'),
            array($this->_getTime(), $class, $event, $descr, '%'),
            $this->_config['format']
        );
        
        if (strtolower($this->_config['output']) == 'html') {
            $text = htmlspecialchars($text) . '<br />';
        } else {
            $text .= PHP_EOL;
        }
    
        echo $text;
        return true;
    }
}
