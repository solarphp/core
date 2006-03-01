<?php
/**
 * 
 * Helper for a formatted timestamp using date() conventions.
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
require_once 'Solar/View/Helper.php';
 
/**
 * 
 * Helper for a formatted timestamp using [[php date()]] conventions.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 */
class Solar_View_Helper_Timestamp extends Solar_View_Helper {
    
    /**
     * 
     * The default date() format string.
     * 
     * @access public
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'format' => DATE_RFC822,
    );
    
    /**
     * 
     * Outputs a formatted timestamp using [[php date()]] conventions.
     * 
     * @access public
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
    function timestamp($spec, $format = null)
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
?>