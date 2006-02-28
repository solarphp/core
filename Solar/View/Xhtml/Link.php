<?php
/**
 * 
 * Helper for links.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id: Solar_View_Helper_actionLink.php 772 2006-02-09 16:12:55Z pmjones $
 *
 */

/**
 * 
 * Helper for links.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_Link extends Solar_View_Helper {
    
    /**
     * 
     * Returns a <link ... /> tag.
     * 
     * @param string $spec The specification array, typically
     * with keys 'rel' and 'href'.
     * 
     * @return string The <link ... /> tag.
     * 
     */
    public function link($spec)
    {
        return '<link' . $this->_view->attribs($spec) . ' />';
    }

}
?>