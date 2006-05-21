<?php
/**
 * 
 * Log adapter to ignore all messages.
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
 * Log adapter to ignore all messages.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 */
class Solar_Log_Adapter_None extends Solar_Log_Adapter {
    
    /**
     * 
     * Support method to save (write) an event and message to the log.
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
        return true;
    }
}
?>