<?php
/**
 * 
 * Concrete adapter class test.
 * 
 */
class Test_Solar_Auth_Logout_Adapter_Get extends Test_Solar_Auth_Logout_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Logout_Adapter_Get = array(
    );
    
    protected function _setLogoutRequest()
    {
        $this->_request->get['process'] = $this->_adapter->locale('PROCESS_LOGOUT');
    }
}
