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
 * @version $Id: Savant3_Plugin_locale.php 676 2006-01-21 20:21:52Z pmjones $
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
     * The base path, generally the front-controller path.
     * 
     * @access public
     * 
     * @var string
     * 
     */
    public $path = '/index.php';
    
    /**
     * 
     * Internal URI object for creating links.
     * 
     * @access protected
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
        $this->_uri->path = $this->path;
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
        $href = $this->_uri->exportAction();
        return '<a href="' . htmlspecialchars($href) . '">'
             . htmlspecialchars($text) . "</a>";
    }

}
?>