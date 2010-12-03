<?php
/**
 * 
 * Concrete adapter class test.
 * 
 */
class Test_Solar_Auth_Login_Adapter_Post extends Test_Solar_Auth_Login_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Login_Adapter_Post = array(
    );
    
    protected function _setLoginRequest()
    {
        $this->_request->post['handle'] = 'pmjones';
        $this->_request->post['passwd'] = 'jones';
        $this->_request->post['redirect'] = 'http://example.com';
        $this->_request->post['process'] = $this->_adapter->locale('PROCESS_LOGIN');
    }
}
