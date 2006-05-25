<?php
/**
 * 
 * Log adapter for appending to a file.
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
 * Solar_Log_Adapter
 */
Solar::loadClass('Solar_Log_Adapter');

/**
 * 
 * Log adapter for appending to a file.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 */
class Solar_Log_Adapter_File extends Solar_Log_Adapter {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are:
     * 
     * : \\events\\ : (string|array) The event types this instance
     * should recognize; a comma-separated string of events, or
     * a sequential array.  Default is all events ('*').
     * 
     * : \\file\\ : (string) The file where events should be logged;
     *   e.g. '/www/username/logs/solar.log'.
     * 
     * : \\format\\ : (string) The line format for each saved event.
     *   Use '%t' for the timestamp, '%e' for the event type, '%m' for
     *   the event description, and '%%' for a literal percent.  Default
     *   is '%t %e %m'.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'events' => '*',
        'file'   => '',
        'format' => '%t %e %m', // time, event, descr
    );
    
    /**
     * 
     * The path to the log file.
     * 
     * @var string
     * 
     */
    protected $_file = '';
    
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
        if (! empty($this->_config['file'])) {
            $this->_file = Solar::fixdir($this->_config['file']);
        }
    }
    
    /**
     * 
     * Support method to save (write) an event and message to the log.
     * 
     * Appends to the file, and uses an exclusive lock (LOCK_EX).
     * 
     * @param string $event The event type (e.g. 'info' or 'debug').
     * 
     * @param string $descr A description of the event. 
     * 
     * @return mixed Boolean false if the event was not saved (usually
     * because it was not recognized), or a non-empty value if it was
     * saved.
     * 
     */
    protected function _save($event, $descr)
    {
        $text = str_replace(
            array('%t', '%e', '%m', '%%'),
            array($this->_getTime(), $event, $descr, '%'),
            $this->_config['format']
        ) . "\n";
    
        return file_put_contents($this->_file, $text, FILE_APPEND | LOCK_EX);
    }
}
?>