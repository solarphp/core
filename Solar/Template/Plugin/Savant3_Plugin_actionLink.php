<?php
/**
 * 
 * Solar-specific plugin to help with action links.
 * 
 * @category Solar
 * 
 * @package Solar_Template
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
 * Solar-specific plugin to help with action links.
 * 
 * @category Solar
 * 
 * @package Solar_Template
 * 
 */
class Savant3_Plugin_actionLink extends Savant3_Plugin {
    
    /**
     * 
     * The base href path, generally the front-controller path.
     * 
     * @var string
     * 
     */
    public $path = '/index.php/';
    
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
        parent::__construct($config);
        $this->_uri = Solar::factory('Solar_Uri');
        $this->_uri->clear();
        if ($this->path[0] != '/') {
            $this->path = '/' . $this->path;
        }
        if (substr($this->path, -1) != '/') {
            $this->path .= '/';
        }
    }
    
    /**
     * 
     * Returns an action link.
     * 
     * @param string $spec The action specification.
     * 
     * @param string $text The link text.
     * 
     * @return string
     * 
     */
    public function actionLink($spec, $text)
    {
        if ($spec instanceof Solar_Uri) {
            // get just the action portions of the URI object
            $this->_uri->importAction($spec->exportAction());
        } else {
            // import the string as an action spec
            $this->_uri->importAction($spec);
        }
        
        // add the base path to the action href
        $href = $this->path . $this->_uri->exportAction();
        
        // done!
        return '<a href="' . htmlspecialchars($href) . '">'
             . htmlspecialchars($text) . "</a>";
    }

}
?>