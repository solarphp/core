<?php

require_once dirname(__FILE__) . '/../AdapterTestCase.php';

class Solar_Cache_Adapter_MemcacheTest extends Solar_Cache_AdapterTestCase {
    
    protected $_cache;

    protected $_config = array(
        'adapter' => 'Solar_Cache_Adapter_Memcache',
        'config'  => array(
            'host' => '127.0.0.1',
            'port' => 11211,
            'life' => 7, // 7 seconds
        ),
    );

    public function setup()
    {
        if (! extension_loaded('memcache')) {
            $this->markTestSkipped('memcache extension not loaded');
        }
        
        // make sure we can connect
        try {
            parent::setup();
        } catch (Exception $e) {
            if ($e->getCode() == 'ERR_CONNECTION_FAILED' ) {
                $this->markTestSkipped('memcache connection failed');
            } else {
                throw $e;
            }
        }
        
        // for some reason, we need to wait a second before trying
        // to add/remove entries from the cache.  otherwise all the
        // tests fail.
        sleep(1);
    }
}