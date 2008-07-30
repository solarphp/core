<?php
/**
 * 
 * Helper for action anchors and hrefs, with built-in text translation.
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
class Solar_View_Helper_Action extends Solar_View_Helper
{
    /**
     * 
     * Returns an action anchor, or just an action href.
     * 
     * If the $text link text is empty, will just return the
     * href value, not an <a href="">...</a> anchor tag.
     * 
     * @param string|Solar_Uri_Action $spec The action specification.
     * 
     * @param string $text A locale translation key.
     * 
     * @param string $attribs Additional attributes for the anchor.
     * 
     * @return string
     * 
     */
    public function action($spec, $text = null, $attribs = null)
    {
        // get an escaped href action value
        $href = $this->_view->actionHref($spec);
        
        // return the href, or an anchor?
        if (empty($text)) {
            return $href;
        } else {
            // build attribs, after dropping any 'href' attrib
            settype($attribs, 'array');
            unset($attribs['href']);
            $attribs = $this->_view->attribs($attribs);
            
            // escape text and return
            $text = $this->_view->getText($text);
            return "<a href=\"$href\"$attribs>$text</a>";
        }
    }
}
