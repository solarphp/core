<?php

require_once dirname(__FILE__) . '/../AdapterTestCase.php';

class Solar_Cache_Adapter_XcacheTest extends Solar_Cache_AdapterTestCase {

    protected $_cache;

    protected $_config = array(
        'adapter' => 'Solar_Cache_Adapter_Xcache',
        'config'  => array(
            'life'   => 7, // 7 seconds
            'user' => 'foo',
            'pass' => 'bar',
        ),
    );


    public function setup()
    {
        if (! extension_loaded('xcache')) {
            $this->markTestSkipped('xcache extension not loaded');
        }
        
        parent::setup();
    }
}
?>