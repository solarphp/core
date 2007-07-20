<?php

require_once dirname(__FILE__) . '/../AdapterTestCase.php';

class Solar_Cache_Adapter_ApcTest extends Solar_Cache_AdapterTestCase {

    protected $_cache;

    protected $_config = array(
        'adapter' => 'Solar_Cache_Adapter_Apc',
        'config'  => array(
            'life'   => 7, // 7 seconds
        ),
    );
    
    public function setup()
    {
        if (! extension_loaded('apc')) {
            $this->markTestSkipped('apc extension not loaded');
        }
        
        parent::setup();
    }
}