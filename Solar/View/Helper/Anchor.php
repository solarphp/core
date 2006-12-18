<?php
/**
 * 
 * Helper for anchor href tags, with built-in text translation.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
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
 * @package Solar_View_Helper
 * 
 */
class Solar_View_Helper_Anchor extends Solar_View_Helper {
    
    /**
     * 
     * Returns a anchor href tag.
     * 
     * If the $text link text is empty, will return only the href
     * value (no attributes) instead of an <a href="">...</a> tag.
     * 
     * @param Solar_Uri|string $spec The anchor href specification.
     * 
     * @param string $text A locale translation key.
     * 
     * @param array $attribs Attributes for the anchor.
     * 
     * @return string
     * 
     */
    public function anchor($spec, $text = null, $attribs = array())
    {
        if ($spec instanceof Solar_Uri) {
            // fetch the full href, not just the path/query/fragment
            $href = $spec->fetch(true);
        } else {
            $href = $spec;
        }
        
        if (empty($text)) {
            return $this->_view->escape($href);
        } else {
            settype($attribs, 'array');
            unset($attribs['href']);
            $href = $this->_view->escape($href);
            $text = $this->_view->getText($text);
            $attr = $this->_view->attribs($attribs);
            return "<a href=\"$href\"$attr>$text</a>";
        }
    }
}
