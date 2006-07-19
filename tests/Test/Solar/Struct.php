<?php

class Test_Solar_Struct extends Solar_Test {
    
    protected function _getStruct()
    {
        $struct = Solar::factory(
            'Solar_Struct',
            array(
                'data' => array(
                    'foo' => 'bar',
                    'baz' => 'dib',
                    'zim' => 'gir',
                ),
            )
        );
        return $struct;
    }
    
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function __destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        parent::setup();
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $struct = $this->_getStruct();
        $this->assertInstance($struct, 'Solar_Struct');
        Solar::dump($struct);
        
    }
    
    public function test__get_existing()
    {
        $struct = $this->_getStruct();
        $this->assertSame($struct->foo, 'bar');
        $this->assertSame($struct['foo'], 'bar');
    }
    
    public function test__get_nonexisting()
    {
        $struct = $this->_getStruct();
        $this->assertNull($struct->noSuchKey);
        $this->assertNull($struct['noSuchKey']);
    }
    
    public function test__set_existing()
    {
        $struct = $this->_getStruct();
        $struct->zim = 'irk';
        $this->assertSame($struct->zim, 'irk');
        $this->assertSame($struct->zim, $struct['zim']);
    }
    
    public function test__set_new()
    {
        $struct = $this->_getStruct();
        $struct->a = 'b';
        $this->assertSame($struct->a, 'b');
        $this->assertSame($struct->a, $struct['a']);
    }
    
    public function test__isset()
    {
        $struct = $this->_getStruct();
        $this->assertTrue(isset($struct->foo));
        $this->assertTrue(isset($struct['foo']));
        $this->assertFalse(isset($struct->noSuchKey));
        $this->assertFalse(isset($struct['noSuchKey']));
    }
    
    public function test__unset()
    {
        $struct = $this->_getStruct();
        unset($struct->foo);
        $this->assertFalse(isset($struct->foo));
        $this->assertNull($struct->foo);
    }
    
    public function testToArray()
    {
        $struct = $this->_getStruct();
        $actual = $struct->toArray();
        $expect = array(
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => 'gir',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testLoad()
    {
        $struct = $this->_getStruct();
        $expect = array(
            'foo' => 'bar2',
            'baz' => 'dib2',
            'zim' => 'gir2',
        );
        $struct->load($expect);
        $actual = $struct->toArray();
        $this->assertSame($actual, $expect);
    }
    
    public function testLoad_reset()
    {
        $struct = $this->_getStruct();
        $expect = array(
            'foo2' => 'bar',
            'baz2' => 'dib',
            'zim2' => 'gir',
        );
        $struct->load($expect, true);
        $actual = $struct->toArray();
        $this->assertSame($actual, $expect);
    }
    
    public function testCount()
    {
        $struct = $this->_getStruct();
        $actual = count($struct);
        $expect = 3;
        $this->assertSame($actual, $expect);
    }
    
    public function test_iterator()
    {
        $struct = $this->_getStruct();
        $expect = array(
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => 'gir',
        );
        foreach ($struct as $key => $val) {
            $this->assertTrue(array_key_exists($key, $expect));
            $this->assertSame($val, $expect[$key]);
        }
    }
}
?>