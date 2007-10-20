<?php

require_once dirname(__FILE__) . '/../AdapterTestCase.php';

class Solar_Cache_Adapter_FileTest extends Solar_Cache_AdapterTestCase {
    
    protected $_cache;
    
    protected $_config = array(
        'adapter' => 'Solar_Cache_Adapter_File',
        'path'   => null, // set in constructor
        'life'   => 7, // 7 seconds
    );
    
    public function setup()
    {
        if (is_null($this->_config['path'])) {
            $this->_config['path'] = Solar_Dir::tmp('/Solar_Cache_Testing/');
        }
        
        parent::setup();
    }
}