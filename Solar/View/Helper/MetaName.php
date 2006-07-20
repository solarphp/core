<?php
/**
 * 
 * Helper for meta name tags.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
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
 * Helper for meta name tags.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_MetaName extends Solar_View_Helper {
    
    /**
     * 
     * Returns a <meta name="" content="" /> tag.
     * 
     * @param string $key The name value.
     * 
     * @param string $val The content value.
     * 
     * @return string The <meta name="" content="" /> tag.
     * 
     */
    public function metaName($key, $val)
    {
        $spec = array(
            'name' => $key,
            'content' => $val,
        );
        return '<meta' . $this->_view->attribs($spec) . ' />';
    }

}
?>