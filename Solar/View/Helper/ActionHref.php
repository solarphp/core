<?php
/**
 * 
 * Helper to build an escaped href or src attribute value for an action URI.
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
class Solar_View_Helper_ActionHref extends Solar_View_Helper
{
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
     * @param array $config Configuration value overrides, if any.
     * 
     */
    public function __construct($config = null)
    {
        // do the real configuration
        parent::__construct($config);
        
        // get a URI processor
        $this->_uri = Solar::factory('Solar_Uri_Action');
    }
    
    /**
     * 
     * Returns an escaped href or src attribute value for an action URI.
     * 
     * @param Solar_Uri_Action|string $spec The href or src specification.
     * 
     * @return string
     * 
     */
    public function actionHref($spec)
    {
        if ($spec instanceof Solar_Uri_Action) {
            // already an action uri object
            $href = $spec->get();
        } else {
            // build-and-fetch the string as an action spec
            $href = $this->_uri->quick($spec);
        }
        
        return $this->_view->escape($href);
    }
}