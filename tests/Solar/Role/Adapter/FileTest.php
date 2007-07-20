<?php

require_once dirname(dirname(__FILE__)) . '/AdapterTestCase.php';

class Solar_Role_Adapter_FileTest extends Solar_Role_AdapterTestCase {
    
    public function setup()
    {
        $this->_config['file'] = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'roles.txt';
        parent::setup();
    }
}
