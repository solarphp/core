<?php
/**
 * 
 * Adapter class test.
 * 
 */
class Test_Solar_Auth_Adapter_Htpasswd extends Test_Solar_Auth_Adapter {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Adapter_Htpasswd = array(
    );
    
    public function setup()
    {
        $dir = Solar_Class::dir('Test_Solar_Auth_Adapter', '_support');
        $this->_config['file'] = $dir . 'users.htpasswd';
        parent::setup();
    }
}
