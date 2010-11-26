<?php
/**
 * 
 * Helper for a formatted timestamp using [[php::date() | ]] format codes.
 * 
 * Default format is "Y-m-d H:i:s".
 * 
 * Note that this helper is timezone-aware.  For example, if all your input
 * timestamps are in the GMT timezone, but you want to show them as being in the
 * America/Chicago timezone, you can set these config keys:
 * 
 * {{code: php
 *     $config['Solar_View_Helper_Timestamp']['tz_origin'] = 'GMT';
 *     $config['Solar_View_Helper_Timestamp']['tz_output'] = 'America/Chicago';
 * }}
 * 
 * Then when you call call the timestamp helper, it will move the input time
 * back by 5 hours (or by 6, during daylight savings time) and output that 
 * instead of the GMT time.
 * 
 * This works for arbitrary timezones, so you can have your input times in any
 * timezone and convert them to any other timezone.
 * 
 * Note that Solar_View_Helper_Date and Solar_View_Helper_Time descend from 
 * this helper, so you only need to set the timezone configs in one place (i.e.,
 * for this helper).
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
class Solar_View_Helper_Timestamp extends Solar_View_Helper
{
    /**
     * 
     * Default configuration values.
     * 
     * @config bool strftime When true, uses strftime() instead of date() for formatting 
     *   dates. Default is false.
     * 
     * @config string format The default output formatting using [[php::date() | ]] codes.
     *   When `strftime` is true, uses [[php::strftime() | ]] codes instead.
     *   Default is 'Y-m-d H:i:s' (using date() format codes).
     * 
     * @config string tz_origin Consider all input timestamps as being from this timezone.
     *   Default is the value of [[php::date_default_timezone_get() | ]].
     * 
     * @config string tz_output Output all timestamps after converting to this timezone.
     *   Default is the value of [[php::date_default_timezone_get() | ]].
     * 
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_Timestamp = array(
        'strftime'  => false,
        'format'    => 'Y-m-d H:i:s',
        'tz_origin' => null,
        'tz_output' => null,
    );
    
    /**
     * 
     * The timezone that date-time strings originate from.
     * 
     * @var string
     * 
     */
    protected $_tz_origin = null;
    
    /**
     * 
     * The timezone that date-time strings should be converted to before output.
     * 
     * @var string
     * 
     */
    protected $_tz_output = null;
    
    /**
     * 
     * The offset in seconds between the origin and output timezones.
     * 
     * This value will be added to the time (in seconds) before formatting for
     * output.
     * 
     * @var int
     * 
     */
    protected $_tz_offset = 0;
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // set the origin timezone
        $this->_tz_origin = $this->_config['tz_origin'];
        if (! $this->_tz_origin) {
            $this->_tz_origin = date_default_timezone_get();
        }
        
        // set the output timezone
        $this->_tz_output = $this->_config['tz_output'];
        if (! $this->_tz_output) {
            $this->_tz_output = date_default_timezone_get();
        }

        $this->_calculateOffset(time());
    }

    /**
     *
     * Calculates the offset between the origin and output timezones
     * for the given timestamp.
     *
     * @param integer $timestamp a Unix timestamp
     *
     * @return void
     *
     **/
    protected function _calculateOffset($timestamp)
    {
        // if different zones, determine the offset between them
        if ($this->_tz_origin != $this->_tz_output) {
            
            // origin timestamp
            $origin_tz     = new DateTimeZone($this->_tz_origin);
            $origin_date   = new DateTime(date('Y-m-d H:i:s', $timestamp), $origin_tz);
            $origin_offset = $origin_date->getOffset();

            // output timestamp
            $output_tz     = new DateTimeZone($this->_tz_output);
            $output_date   = new DateTime(date('Y-m-d H:i:s', $timestamp), $output_tz);
            $output_offset = $output_tz->getOffset($output_date);

            // retain the differential offset
            $this->_tz_offset = $output_offset - $origin_offset;
        }
    }
    
    /**
     * 
     * Outputs a formatted timestamp using [[php::date() | ]] format codes.
     * 
     * @param string $spec Any date-time string suitable for
     * strtotime().
     * 
     * @param string $format An optional custom [[php::date() | ]]
     * formatting string.
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
    public function timestamp($spec, $format = null, $tz_origin = null, $tz_output = null)
    {
        return $this->_process($spec, $format, $tz_origin, $tz_output);
    }
    
    /**
     * 
     * Outputs a formatted timestamp using [[php::date() | ]] format codes.
     * 
     * @param string|int $spec Any date-time string suitable for strtotime();
     * if an integer, will be used as a Unix timestamp as-is.
     * 
     * @param string $format An optional custom [[php::date() | ]] formatting
     * string.
     * 
     * @return string The formatted date string.
     * 
     */
    protected function _process($spec, $format, $tz_origin = null, $tz_output = null)
    {
        // must have an explicit spec; empty *does not* mean "now"
        if (! $spec) {
            return;
        }
        
        if (! $format) {
            $format = $this->_config['format'];
        }
        
        if (is_int($spec)) {
            $time = $spec;
        } else {
            $time = strtotime($spec);
        }

        // origin and output timezones specified?
        // if so, use them, otherwise use the configured zones

        if ($tz_origin) {
            $tz_origin_save = $this->_tz_origin;
            $this->_tz_origin = $tz_origin;
        }

        if ($tz_output) {
            $tz_output_save = $this->_tz_output;
            $this->_tz_output = $tz_output;
        }

        // calculate the offset between origin and output timezones
        $this->_calculateOffset($time);

        // move by the offset
        $time += $this->_tz_offset;
        
        // use strftime() or date()?
        if ($this->_config['strftime']) {
            $val = strftime($format, $time);
        } else {
            $val = date($format, $time);
        }

        // restore configured time zones

        if (isset($tz_origin_save)) {
            $this->_tz_origin = $tz_origin_save;
        }

        if (isset($tz_output_save)) {
            $this->_tz_output = $tz_output_save;
        }

        return $this->_view->escape($val);
    }
}
