<?php

require_once dirname(dirname(__FILE__)) . '/Adapter.php';

class Test_Solar_Auth_Adapter_Htpasswd extends Test_Solar_Auth_Adapter {
    
    public function setup()
    {
        $this->_config['file'] = dirname(dirname(__FILE__)) . '/users.htpasswd';
        parent::setup();
    }
}
?>