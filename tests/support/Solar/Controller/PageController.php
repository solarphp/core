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
 * @version $Id: PageController.php 1902 2006-10-26 20:19:12Z pmjones $
 * 
 */

/**
 * 
 * Example page-controller to support unit tests.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 */
class Solar_Controller_PageController extends Solar_Controller_Page 
{
    
    /**
     * 
     * Default action.
     * 
     * @var string
     * 
     */
    protected $_action_default = 'foo';
    
    /**
     * 
     * Silly variable for testing.
     * 
     * @var string
     * 
     */
    public $foo = 'bar';
    
    /**
     * 
     * Count of how many time each hook method has been called.
     * 
     * @var array
     * 
     */
    public $hooks = array(
        '_setup'      => 0,
        '_preRun'     => 0,
        '_preAction'  => 0,
        '_postAction' => 0,
        '_postRun'    => 0,
        '_preRender'  => 0,
        '_postRender' => 0,
    );
    
    /**
     * 
     * Default action.
     * 
     * @return void
     * 
     */
    public function actionFoo()
    {
        // do nothing
    }
    
    /**
     * 
     * Action named in BumpyCase.
     * 
     * @return void
     * 
     */
    public function actionBumpyCase()
    {
        // do nothing
    }
    
    /**
     * 
     * An action method that has no related view script.
     * 
     * @return void
     * 
     */
    public function actionNoRelatedView()
    {
        // do nothing
    }
    
    /**
     * 
     * Tests the _forward() method.
     * 
     * @return void
     * 
     */
    public function actionTestForward()
    {
        return $this->_forward('foo');
    }
    
    /**
     * 
     * Sets the default action for testing.
     * 
     * @param string $val The default action name.
     * 
     * @return void
     * 
     */
    public function setActionDefault($val)
    {
        $this->_action_default = $val;
    }
    
    /**
     * 
     * Hook for extended setups.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    /**
     * 
     * Hook for pre-run behavior.
     * 
     * @return void
     * 
     */
    protected function _preRun()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    /**
     * 
     * Hook for pre-action behavior.
     * 
     * @return void
     * 
     */
    protected function _preAction()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    /**
     * 
     * Hook for post-action behavior.
     * 
     * @return void
     * 
     */
    protected function _postAction()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    /**
     * 
     * Hook for post-run behavior.
     * 
     * @return void
     * 
     */
    protected function _postRun()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    /**
     * 
     * Hook for pre-render behavior.
     * 
     * @param Solar_View $view The Solar_View object.
     * 
     * @return void
     * 
     */
    protected function _preRender()
    {
        $this->hooks[__FUNCTION__] ++;
    }
    
    /**
     * 
     * Hook for post-render filtering.
     * 
     * @param string $output The output before filtering.
     * 
     * @return string $output The output after filtering.
     * 
     */
    protected function _postRender()
    {
        $this->hooks[__FUNCTION__] ++;
    }
}