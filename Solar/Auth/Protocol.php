<?php
/**
 * 
 * Abstract Authentication Protocol.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Adapter.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
abstract class Solar_Auth_Protocol extends Solar_Base {

    /**
     * 
     * Details on the current request.
     * 
     * @var Solar_Request
     * 
     */
    protected $_request;

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
        
        // get the current request environment
        $this->_request = Solar_Registry::get('request');
    }
    
}