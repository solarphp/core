<?php
/**
 * 
 * Adapter class test.
 * 
 */
class Test_Solar_Auth_Adapter_Ini extends Test_Solar_Auth_Adapter {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Adapter_Ini = array(
    );
    
    public function setup()
    {
        $dir = Solar_Class::dir('Test_Solar_Auth_Adapter', '_support');
        $this->_config['file'] = $dir . 'users.ini';
        $this->_moniker = 'Paul M. Jones';
        $this->_email = 'pmjones@solarphp.com';
        $this->_uri = 'http://paul-m-jones.com';
        parent::setup();
    }
}
