<?php
/**
 * 
 * Returns a URI object for the current action.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Action.php 3988 2009-09-04 13:51:51Z pmjones $
 * 
 */
class Solar_View_Helper_ActionUri extends Solar_View_Helper
{
    /**
     * 
     * Internal URI object for cloning.
     * 
     * @var Solar_Uri_Action
     * 
     */
    protected $_uri = null;
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        $this->_uri = Solar::factory('Solar_Uri_Action');
    }
    
    /**
     * 
     * Returns a URI object for the current action.
     * 
     * @return Solar_Uri_Action
     * 
     */
    public function actionUri()
    {
        return clone $this->_uri;
    }
}
