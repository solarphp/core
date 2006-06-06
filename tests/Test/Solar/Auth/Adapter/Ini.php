<?php

require_once dirname(dirname(__FILE__)) . '/Adapter.php';

class Test_Solar_Auth_Adapter_Ini extends Test_Solar_Auth_Adapter {
    
    public function setup()
    {
        $this->_config['file'] = dirname(dirname(__FILE__)) . '/users.ini';
        $this->_name = 'Paul M. Jones';
        $this->_email = 'pmjones@solarphp.com';
        $this->_uri = 'http://paul-m-jones.com';
        parent::setup();
    }
}
?>