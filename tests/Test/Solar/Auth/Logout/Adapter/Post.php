<?php
/**
 * 
 * Concrete adapter class test.
 * 
 */
class Test_Solar_Auth_Logout_Adapter_Post extends Test_Solar_Auth_Logout_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Logout_Adapter_Post = array(
    );
    
    protected function _setLogoutRequest()
    {
        $this->_request->post['process'] = $this->_adapter->locale('PROCESS_LOGOUT');
    }
}
