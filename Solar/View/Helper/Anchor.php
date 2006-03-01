<?php
/**
 * 
 * Helper for anchor href tags, with built-in text translation.
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
 * 
 * Helper for anchor href tags, with built-in text translation.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_Anchor extends Solar_View_Helper {
    
    /**
     * 
     * Returns an anchor href tag.
     * 
     * If the $text link text is empty, will just return the
     * href value, not an <a href="">...</a> tag.
     * 
     * @param string $href The anchor href.
     * 
     * @param string $text A locale translation key.
     * 
     * @param array $attribs Attributes for the anchor.
     * 
     * @return string
     * 
     */
    public function anchor($spec, $text, $attribs = array())
    {
        if ($spec instanceof Solar_Uri) {
            $href = $spec->export();
        } else {
            $href = $spec;
        }
        settype($attribs, 'array');
        unset($attribs['href']);
        $href = $this->_view->escape($href);
        $text = $this->_view->getText($text);
        $attr = $this->_view->attribs($attribs);
        return "<a href=\"$href\"$attr>$text</a>";
    }
}
?>