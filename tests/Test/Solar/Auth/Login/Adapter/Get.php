<?php
/**
 * 
 * Concrete adapter class test.
 * 
 */
class Test_Solar_Auth_Login_Adapter_Get extends Test_Solar_Auth_Login_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Login_Adapter_Get = array(
    );
    
    protected function _setLoginRequest()
    {
        $this->_request->get['handle'] = 'pmjones';
        $this->_request->get['passwd'] = 'jones';
        $this->_request->get['redirect'] = 'http://example.com';
        $this->_request->get['process'] = $this->_adapter->locale('PROCESS_LOGIN');
    }
}
