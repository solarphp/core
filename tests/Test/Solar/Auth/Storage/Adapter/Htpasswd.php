<?php
/**
 * 
 * Concrete adapter class test.
 * 
 */
class Test_Solar_Auth_Storage_Adapter_Htpasswd extends Test_Solar_Auth_Storage_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Storage_Adapter_Htpasswd = array(
    );
    
    protected $_expect = array(
        'handle' => 'pmjones',
    );
    
    protected function _preConfig()
    {
        parent::_preConfig();
        $dir  = Solar_Class::dir('Mock_Solar_Auth_Adapter_Htpasswd');
        $file = $dir . 'users.htpasswd';
        $this->_Test_Solar_Auth_Storage_Adapter_Htpasswd['file'] = $file;
    }
}
