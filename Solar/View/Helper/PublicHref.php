<?php
/**
 * 
 * Helper for public hrefs.
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
 * Helper for public hrefs.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_PublicHref extends Solar_View_Helper {
    
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
     * Returns an href to a public resource.
     * 
     * @param string $spec The action specification.
     * 
     * @return string
     * 
     */
    public function publicHref($spec, $raw = false)
    {
        if ($spec instanceof Solar_Uri) {
            // get just the action portions of the URI object
            $this->_uri->importAction($spec->exportAction());
        } else {
            // import the string as an action spec
            $this->_uri->importAction($spec);
        }
        
        // get the public href
        $href = $this->_uri->exportPublic();
        
        // return the href, or an anchor?
        if ($raw) {
            return $href;
        } else {
            $this->_view->escape($href);
        }
    }
}
?>