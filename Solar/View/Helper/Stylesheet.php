<?php
/**
 * 
 * Helper for <link rel="stylesheet" ... /> tags.
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
 * Helper for <link rel="stylesheet"> tags.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_Stylesheet extends Solar_View_Helper {
    
    /**
     * 
     * Returns a <link rel="stylesheet" ... /> tag.
     * 
     * @param string $href The source href for the stylesheet.
     * 
     * @param array $attribs Additional attributes for the <link> tag.
     * 
     * @return string The <link ... /> tag.
     * 
     */
    public function stylesheet($href, $attribs = null)
    {
        settype($attribs, 'array');
        $attribs['rel'] = 'stylesheet';
        $attribs['type'] = 'text/css';
        $attribs['href'] = $this->_view->publicHref($href, true);
        return $this->_view->link($attribs);
    }
}
?>