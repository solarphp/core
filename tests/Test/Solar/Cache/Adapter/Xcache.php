<?php
class Test_Solar_Cache_Adapter_Xcache extends Solar_Test {

    protected $_cache;

    protected $_Test_Solar_Cache_Adapter_Xcache = array(
        'adapter' => 'Solar_Cache_Adapter_Xcache',
        'config'  => array(
            'life'   => 7, // 7 seconds
            'user' => 'foo',
            'pass' => 'bar',
        ),
    );

    public function __construct($config = null)
    {
        parent::__construct($config);
        if (! extension_loaded('xcache')) {
            $this->skip('xcache extension not loaded');
        }
    }

    public function setup()
    {
        // create a Solar_Cache with the Solar_Cache_Xcache adapter
        $this->_cache = Solar::factory('Solar_Cache', $this->_config);

        // remove all previous entries
        $this->_cache->deleteAll();
    }

    public function tearDown()
    {
        // remove all previous entries
        $this->_cache->deleteAll();
    }

    public function testDelete()
    {
        $id = 'spiff';
        $data = 'Spaceman Spiff';

        // data has not been stored yet
        $this->assertNull($this->_cache->fetch($id));

        // store it
        $this->assertTrue($this->_cache->save($id, $data));

        // and we should be able to fetch now
        $this->assertSame($this->_cache->fetch($id), $data);

        // delete it, should not be able to fetch again
        $this->_cache->delete($id);
        $this->assertNull($this->_cache->fetch($id));
    }

    public function testDeleteAll()
    {
        $list = array(1, 2, 'five');
        $data = 'Spaceman Spiff';

        foreach ($list as $id) {
            // data has not been stored yet
            $this->assertNull($this->_cache->fetch($id));
            // so store some data
            $this->assertTrue($this->_cache->save($id, $data));
            // and we should be able to fetch now
            $this->assertSame($this->_cache->fetch($id), $data);
        }

        // delete everything
        $this->_cache->deleteAll();

        // should not be able to fetch again
        foreach ($list as $id) {
            $this->assertNull($this->_cache->fetch($id));
        }
    }

    public function testFetch()
    {
        $id = 'spiff';
        $data = 'Spaceman Spiff';

        // data has not been stored yet
        $this->assertNull($this->_cache->fetch($id));

        // store it
        $this->assertTrue($this->_cache->save($id, $data));

        // and we should be able to fetch now
        $this->assertSame($this->_cache->fetch($id), $data);

        // xcache has no ability to de-activate a cached value

    }

    public function testTTL()
    {
        $id = 'spiff';
        $data = 'Spaceman Spiff';

        // configured from setup
        // xcache has no getLife()-type method
        //$this->assertSame($this->_cache->getLife(), $this->_config['config']['life']);

        // store something
        $this->assertTrue($this->_cache->save($id, $data, 3));
        $this->assertSame($this->_cache->fetch($id), $data);

        // wait until just before the lifetime,
        // we should still get data
        sleep(1);
        $this->assertSame($this->_cache->fetch($id), $data);

        // wait until just after the lifetime,
        // we should get nothing
        sleep(5);
        $this->assertNull($this->_cache->fetch($id));
    }

    public function testSave_Array()
    {
        $id = 'spiff';
        $data = array(
            'name'   => 'Spiff',
            'type'   => 'Spaceman',
            'visits' => 'Zartron-9',
            'action' => 'Reboot saucer\'s computer and recalibrate weapons',
        );
        $this->assertTrue($this->_cache->save($id, $data));
        $this->assertSame($this->_cache->fetch($id), $data);
    }

    public function testSave_Object()
    {
        $id = 'spiff';
        $data = Solar::factory('Solar_Test_Example');
        $this->assertTrue($this->_cache->save($id, $data));
        $this->assertEquals($this->_cache->fetch($id), $data);
    }

    public function testSave_String()
    {
        $id = 'spiff';
        $data = 'Spaceman Spiff';
        $this->assertTrue($this->_cache->save($id, $data));
        $this->assertSame($this->_cache->fetch($id), $data);
    }
}
?>