<?php
/**
 * 
 * Concrete adapter class test.
 * 
 */
class Test_Solar_Auth_Login_Adapter_HttpBasic extends Test_Solar_Auth_Login_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Login_Adapter_HttpBasic = array(
    );
    
    protected function _setLoginRequest()
    {
        $this->_request->server['PHP_AUTH_USER'] = 'pmjones';
        $this->_request->server['PHP_AUTH_PW'] = 'jones';
    }
    
    public function testGetLoginRedirect()
    {
        $this->skip('redirects not supported directly with http-basic auth');
    }
}
