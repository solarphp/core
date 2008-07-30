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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_View_Helper_Anchor extends Solar_View_Helper
{
    /**
     * 
     * Returns an anchor tag or anchor href.
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
        // get an escaped href value
        $href = $this->_view->href($spec);
        
        if (empty($text)) {
            return $href;
        } else {
            settype($attribs, 'array');
            unset($attribs['href']);
            $text = $this->_view->getText($text);
            $attr = $this->_view->attribs($attribs);
            return "<a href=\"$href\"$attr>$text</a>";
        }
    }
}
