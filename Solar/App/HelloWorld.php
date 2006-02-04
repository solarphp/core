<?php
/**
 * 
 * Social bookmarking application.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_Bookmarks
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id: Bookmarks.php 659 2006-01-15 19:46:43Z pmjones $
 * 
 */

/**
 * Application controller class.
 */
Solar::loadClass('Solar_Controller_Page');

/**
 * 
 * Social bookmarking application.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_Bookmarks
 * 
 */
class Solar_App_HelloWorld extends Solar_Controller_Page {
    
    /**
     * 
     * The default controller action.
     * 
     * @var string
     * 
     */
    protected $_action_default = 'main';
    
    /**
     * 
     * The path-info values for each action.
     * 
     * @var string
     * 
     */
    protected $_action_info = array(
        // main/:code
        'main' => array('code')
    );
    
    /**
     * 
     * The list of available codes.
     * 
     * @var array
     * 
     */
    public $list = array('en_US', 'es_ES', 'fr_FR');
    
    /**
     * 
     * The translation code we're using.
     * 
     * @var string
     * 
     */
    public $code;
    
    
    /**
     * 
     * The translated text.
     * 
     * @var string
     * 
     */
    public $text;
}
?>