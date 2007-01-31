<?php

require_once dirname(dirname(__FILE__)) . '/AdapterTestCase.php';

if (!class_exists('Solar_Auth_Adapter_HtpasswdTest')) {
class Solar_Auth_Adapter_HtpasswdTest extends Solar_Auth_AdapterTestCase {
    
    public function setup()
    {
        $this->_config['file'] = dirname(dirname(__FILE__)) . '/users.htpasswd';
        parent::setup();
    }
}
}
?>