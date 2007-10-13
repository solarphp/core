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
 * @version $Id$
 * 
 */

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
     * @param string $name The name value.
     * 
     * @param string $content The content value.
     * 
     * @return string The <meta name="" content="" /> tag.
     * 
     */
    public function metaName($name, $content)
    {
        $spec = array(
            'name' => $name,
            'content' => $content,
        );
        return '<meta' . $this->_view->attribs($spec) . ' />';
    }

}
