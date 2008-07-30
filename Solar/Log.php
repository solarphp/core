<?php
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
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Log extends Solar_Factory
{
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
}
