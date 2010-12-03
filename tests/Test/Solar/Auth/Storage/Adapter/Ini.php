<?php
/**
 * 
 * Concrete adapter class test.
 * 
 */
class Test_Solar_Auth_Storage_Adapter_Ini extends Test_Solar_Auth_Storage_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Storage_Adapter_Ini = array(
    );
    
    protected $_expect = array(
        'handle'  => 'pmjones',
        'moniker' => 'Paul M. Jones',
        'email'   => 'pmjones@solarphp.com',
        'uri'     => 'http://paul-m-jones.com',
    );
    
    protected function _preConfig()
    {
        parent::_preConfig();
        $dir  = Solar_Class::dir('Mock_Solar_Auth_Adapter_Ini');
        $file = $dir . 'users.ini';
        $this->_Test_Solar_Auth_Storage_Adapter_Ini['file'] = $file;
    }
}
