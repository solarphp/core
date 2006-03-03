<?php
/**
 * 
 * Helper for <script> tags from a public Solar resource.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
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
 * Helper for <script> tags from a public Solar resource.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_Script extends Solar_View_Helper {
    
    
    /**
     * 
     * Returns a <script></script> tag.
     * 
     * @param string $src The source href for the script.
     * 
     * @param string $type The script type (default 'text/javascript').
     * 
     * @param array $attribs Additional attributes for the <script> tag.
     * 
     * @return string The <script></script> tag.
     * 
     */
    public function script($src, $type ='text/javascript', $attribs = null)
    {
        settype($attribs, 'array');
        unset($attribs['src']);
        unset($attribs['type']);
        
        $src = $this->_view->publicHref($src);
        
        $type = $this->_view->escape($type);
        
        return '<script src="$src" type="$type"'
             . $this->_view->attribs($attribs) . '></script>';
    }
}
?>