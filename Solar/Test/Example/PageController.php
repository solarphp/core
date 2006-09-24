<?php
/**
 * 
 * Example page-controller to support unit tests.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Example.php 1438 2006-07-06 13:36:30Z pmjones $
 * 
 */

/**
 * Parent class.
 */
Solar::loadClass('Solar_Controller_Page');

/**
 * 
 * Example page-controller to support unit tests.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 */
class Solar_Test_Example_PageController extends Solar_Controller_Page {
    
    public $foo = 'bar';
    
    protected $_action_default = 'foo';
    
    public $hooks = array(
        '_setup'      => 0,
        '_preRun'     => 0,
        '_preAction'  => 0,
        '_postAction' => 0,
        '_postRun'    => 0,
        '_preRender'  => 0,
        '_postRender' => 0,
    );
    
    public function actionFoo()
    {
        // do nothing
    }
    
    public function actionBumpyCase()
    {
        // do nothing
    }
    
    public function actionNoRelatedView()
    {
        // do nothing
    }
    
    public function actionTestForward()
    {
        return $this->_forward('foo');
    }
    
    public function setActionDefault($val)
    {
        $this->_action_default = $val;
    }
    
    protected function _setup()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    protected function _preRun()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    protected function _preAction()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    protected function _postAction()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    protected function _postRun()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    protected function _preRender($view)
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    protected function _postRender($output)
    {
        $this->hooks[__FUNCTION__] ++;
        return $output;
    }
}
?>