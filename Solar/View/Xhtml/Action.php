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
 * @license LGPL
 * 
 * @version $Id: Solar_View_Helper_actionLink.php 772 2006-02-09 16:12:55Z pmjones $
 *
 */

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
     * @var Solar_Uri
     * 
     */
    protected $_uri = null;
    
    /**
     * 
     * Constructor.
     * 
     */
    public function __construct($config)
    {
        // do the real configuration
        parent::__construct($config);
        
        // build the base URI for links
        $this->_uri = Solar::factory('Solar_Uri');
        $this->_uri->clear();
    }
    
    /**
     * 
     * Returns an action anchor, or just an action href.
     * 
     * If the $text link text is empty, will just return the
     * href value, not an <a href="">...</a> anchor tag.
     * 
     * @param string $spec The action specification.
     * 
     * @param string $text A locale translation key.
     * 
     * @return string
     * 
     */
    public function action($spec, $text = null)
    {
        if ($spec instanceof Solar_Uri) {
            // get just the action portions of the URI object
            $href = $spec->exportAction();
        } else {
            // import the string as an action spec
            $this->_uri->importAction($spec);
            $href = $this->_uri->exportAction();
        }
        
        // get the action href
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