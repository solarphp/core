<?php
/**
 * 
 * Helper for a formatted time using [[php::date() | ]] format codes.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_View_Helper_Time extends Solar_View_Helper_Timestamp
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string format The default output formatting using [[php::date() | ]] codes.
     *   Default is 'H:i:s'.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_Time = array(
        'format' => 'H:i:s',
    );
    
    /**
     * 
     * Outputs a formatted time using [[php::date() | ]] format codes.
     * 
     * @param string $spec Any date-time string suitable for
     * strtotime().
     * 
     * @param string $format An optional custom [[php::date() | ]]
     * formatting string; null by default.
     *
     * @param string $tz_origin an optional time zone name for the origin
     * timestamp. If $tz_origin or $tz_output are not set, the default values
     * (from config) will be used. The system time zone is used, if there are
     * no time zones configured.
     *
     * @param string $tz_output an optional time zone name, the output timestamp
     * will be converted to this time zone
     *
     * @return string The formatted date string.
     * 
     */
    public function time($spec, $format = null, $tz_origin = null, $tz_output = null)
    {
        return $this->_process($spec, $format, $tz_origin, $tz_output);
    }
}
