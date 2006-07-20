<?php
/**
 * 
 * Helper for meta tags.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Jeff Surgeson <solar@3hex.com>
 * 
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Meta.php 1186 2006-05-21 15:38:37Z pmjones $
 * 
 */

/**
 * Solar_View_Helper
 */
Solar::loadClass('Solar_View_Helper');
 
/**
 * 
 * Helper for meta tags.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_Meta extends Solar_View_Helper {
    
    /**
     * 
     * Returns a <meta ... /> tag.
     * 
     * @param string $spec The specification array, typically
     * with keys 'name' or 'http-equiv', and 'content'.
     * 
     * @return string The <meta ... /> tag.
     * 
     */
    public function meta($spec)
    {
        return '<meta' . $this->_view->attribs($spec) . ' />';
    }

}
?>