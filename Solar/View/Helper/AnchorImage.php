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
 * @version $Id: AnchorImage.php 1560 2006-07-28 17:38:51Z pmjones $
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
     * @param array $a_attribs Additional attributes for the anchor.
     * 
     * @param array $img_attribs Additional attributes for the image.
     * 
     * @return string An <a href="..."><img ... /></a> tag set.
     * 
     * @see Solar_View_Helper_Image
     * 
     */
    public function anchorImage($spec, $src, $a_attribs = array(),
        $img_attribs = array())
    {
        if ($spec instanceof Solar_Uri) {
            // fetch the full href, not just the path/query/fragment
            $href = $spec->fetch(true);
        } else {
            $href = $spec;
        }
        
        // escape the anchor href itself
        $href = $this->_view->escape($href);
        
        // get the <img /> tag
        $img = $this->_view->image($src, $img_attribs);
        
        // get the anchor attribs
        settype($a_attribs, 'array');
        unset($a_attribs['href']);
        $attr = $this->_view->attribs($a_attribs);
        
        // build the full anchor/img tag set
        return "<a href=\"$href\"$attr>$img</a>";
    }
}
?>