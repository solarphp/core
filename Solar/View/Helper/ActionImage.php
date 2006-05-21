<?php
/**
 * 
 * Helper for action image anchors.
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
 * Helper for action image anchors.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_ActionImage extends Solar_View_Helper {
    
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
     * Returns an action image anchor.
     * 
     * @param string|Solar_Uri_Action $spec The action specification.
     * 
	 * @param string $src The href to the image source.
	 * 
	 * @param array $attribs Additional attributes for the tag.
	 * 
     * @return string An <a href="..."><img ... /></a> tag set.
     * 
     * @see Solar_View_Helper_Image
     * 
     */
    public function actionImage($spec, $src, $attribs = array())
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
        
        // get the <img /> tag
        $img = $this->_view->image($src, $attribs);
        
        // done!
        return "<a href=\"$href\">$img</a>";
    }
}
?>