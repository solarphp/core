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
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
 * Helper for a formatted timestamp using [[php date()]] conventions.
 * 
 * Default format is "Y-m-d\TH:i:s".
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
     * Default format is "Y-m-d\TH:i:s".
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'format' => 'Y-m-d\TH:i:s',
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
     * formatting string.
     * 
     * @return string The formatted date string.
     * 
     */
    public function timestamp($spec, $format = null)
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