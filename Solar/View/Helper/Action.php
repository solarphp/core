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
 * Helper for action anchors and hrefs, with built-in text translation.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_Action extends Solar_View_Helper {
    
    /**
     * 
     * Internal URI object for creating links.
     * 
     * @var Solar_Uri_Action
     * 
     */
    protected $_uri = null;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-specified configuration.
     * 
     */
    public function __construct($config)
    {
        // do the real configuration
        parent::__construct($config);
        
        // get a URI processor
        $this->_uri = Solar::factory('Solar_Uri_Action');
    }
    
    /**
     * 
     * Returns an action anchor, or just an action href.
     * 
     * If the $text link text is empty, will just return the
     * href value, not an <a href="">...</a> anchor tag.
     * 
     * @param string|Solar_Uri_Action The action specification.
     * 
     * @param string $text A locale translation key.
     * 
     * @return string
     * 
     */
    public function action($spec, $text = null)
    {
        if ($spec instanceof Solar_Uri_Action) {
            // already an action uri object
            $href = $spec->fetch();
        } else {
            // build-and-fetch the string as an action spec
            $href = $this->_uri->quick($spec);
        }
        
        // escape the href itself
        $href = $this->_view->escape($href);
        
        // return the href, or an anchor?
        if (empty($text)) {
            return $href;
        } else {
            $text = $this->_view->getText($text);
            return "<a href=\"$href\">$text</a>";
        }
    }
}
?>