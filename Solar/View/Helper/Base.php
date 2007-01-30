<?php
/**
 * 
 * Helper for base tags.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Base.php 1581 2006-08-03 16:34:33Z pmjones $
 * 
 */

/**
 * Solar_View_Helper
 */
Solar::loadClass('Solar_View_Helper');

/**
 * Needed for instanceof comparisons.
 */
Solar::loadClass('Solar_Uri');

/**
 * 
 * Helper for base tags.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_Base extends Solar_View_Helper {
    
    /**
     * 
     * Returns a <base ... /> tag.
     * 
     * @param string|Solar_Uri $spec The base HREF.
     * 
     * @return string The <base ... /> tag.
     * 
     */
    public function base($spec)
    {
        if ($spec instanceof Solar_Uri) {
            
            // work with a copy of the spec
            $uri = clone($spec);
            
            // remove any current path and query
            $uri->setPath(null);
            $uri->setQuery(null);
            
            // use that as the base
            $href = $uri->fetch(true);
            
        } else {
            $href = $spec;
        }
        
        $href = $this->_view->escape($href);
        return "<base href=\"$href\" />";
    }

}
?>