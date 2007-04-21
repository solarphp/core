<?php
/**
 * 
 * Helper for a formatted time using date() format codes.
 * 
 * @category Solar
 * 
 * @package Solar_View
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
 * Helper for a formatted time using [[php::date() | ]] format codes.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 */
class Solar_View_Helper_Time extends Solar_View_Helper {
    
    /**
     * 
     * The default date() format string.
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
     * @return string The formatted date string.
     * 
     */
    public function time($spec, $format = null)
    {
        if (! $spec) {
            return;
        }
        if (! $format) {
            $format = $this->_config['format'];
        }
        return $this->_view->date($spec, $format);
    }
}
