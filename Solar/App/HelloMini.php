<?php
/**
 * 
 * Absolute minimal "hello world" application for benchmarking.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_HelloWorld
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Absolute minimal "hello world" application for benchmarking.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_HelloWorld
 * 
 */
class Solar_App_HelloMini extends Solar_Controller_Page {
    
    /**
     * 
     * Default action.
     * 
     * @var string
     * 
     */
    protected $_action_default = 'index';
    
    /**
     * 
     * Action with no code at all; only passes to the view.
     * 
     * @return void
     * 
     */
    public function actionIndex()
    {
    }
}
