<?php

require_once dirname(dirname(__FILE__)) . '/AdapterTestCase.php';

class Solar_Auth_Adapter_IniTest extends Solar_Auth_AdapterTestCase {
    
    public function setup()
    {
        $this->_config['file'] = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'users.ini';
        $this->_moniker = 'Paul M. Jones';
        $this->_email = 'pmjones@solarphp.com';
        $this->_uri = 'http://paul-m-jones.com';
        parent::setup();
    }
}
?>