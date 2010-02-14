<?php
/**
 * 
 * Absolute minimal "hello world" application for benchmarking.
 * 
 * @category Solar
 * 
 * @package Solar_App Example applications bundled with Solar.
 * 
 * @subpackage Solar_App_Hello
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_App_Hello extends Solar_Controller_Page
{
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
