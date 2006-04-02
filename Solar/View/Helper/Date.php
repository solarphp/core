<?php
/**
 * 
 * Helper for a formatted date using date() conventions.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * Solar_View_Helper
 */
Solar::loadClass('Solar_View_Helper');
 
/**
 * 
 * Helper for a formatted date using [[php date()]] conventions.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 */
class Solar_View_Helper_Date extends Solar_View_Helper {
    
    /**
     * 
     * The default date() format string.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'format' => 'Y-m-d',
    );
    
    /**
     * 
     * Outputs a formatted date using [[php date()]] conventions.
     * 
     * @param string $spec Any date-time string suitable for
     * strtotime().
     * 
     * @param string $format An optional custom [[php date()]]
     * formatting string; null by default.
     * 
     * @return string The formatted date string.
     * 
     */
    public function date($spec, $format = null)
    {
        if (! $spec) {
            return;
        }
        if (! $format) {
            $format = $this->_config['format'];
        }
        if (is_int($spec)) {
            return $this->_view->escape(date($format, $spec));
        } else {
            return $this->_view->escape(date($format, strtotime($spec)));
        }
    }
}
?>