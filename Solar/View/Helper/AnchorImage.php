<?php
/**
 * 
 * Helper for anchored images.
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
 * Helper for anchor href tags, with built-in text translation.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_AnchorImage extends Solar_View_Helper {
    
    /**
     * 
     * Returns an image wrapped by an anchor href tag.
     * 
     * @param Solar_Uri|string $spec The anchor href specification.
     * 
     * @param string $src The href to the image source.
     * 
     * @param array $attribs Additional attributes for the image.
     * 
     * @return string An <a href="..."><img ... /></a> tag set.
     * 
     * @see Solar_View_Helper_Image
     * 
     */
    public function anchorImage($spec, $src, $attribs = array())
    {
        if ($spec instanceof Solar_Uri) {
            // fetch the full href, not just the path/query/fragment
            $href = $spec->fetch(true);
        } else {
            $href = $spec;
        }
        
        // escape the href itself
        $href = $this->_view->escape($href);
        
        // get the <img /> tag
        $img = $this->_view->image($src, $attribs);
        
        // done!
        return "<a href=\"$href\">$img</a>";
    }
}
?>