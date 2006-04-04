<?php
/**
 * 
 * Helper for <style>...</style> tags.
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
 * Helper for <style>...</style> tags.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_Style extends Solar_View_Helper {
    
    /**
     * 
     * Returns a <style>...</style> tag.
     * 
     * @param string $href The source href for the stylesheet.
     * 
     * @param array $attribs Additional attributes for the <style> tag.
     * 
     * @return string The <style>...</style> tag.
     * 
     */
    public function style($href, $attribs = null)
    {
        settype($attribs, 'array');
        $attribs['type'] = 'text/css';
        $url = $this->_view->publicHref($href, true);
        return '<style' . $this->_view->attribs($attribs) . '>'
             . '@import url("' . $url . '");</style>';
    }
}
?>