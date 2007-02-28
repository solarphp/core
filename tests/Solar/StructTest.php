<?php

require_once dirname(__FILE__) . '/../SolarUnitTest.config.php';
require_once 'Solar/Struct.php';

class Solar_StructTest extends PHPUnit_Framework_TestCase
{
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

    public function setUp() 
    {
        Solar::start('config.inc.php');
    }
    
    public function tearDown() 
    {
        Solar::stop();
    }
    
    public function testCanInstantiateThroughFactory()
    {
        $object = Solar::factory('Solar_Struct');
        $this->assertTrue($object instanceof Solar_Struct);
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
    
    public function test__construct_dataNotArray()
    {
        $struct = Solar::factory(
            'Solar_Struct',
            array('data' => null)
        );
        $this->assertSame($struct->toArray(), array());
        
        $struct = Solar::factory(
            'Solar_Struct',
            array('data' => '')
        );
        $this->assertSame($struct->toArray(), array());
        
        $struct = Solar::factory(
            'Solar_Struct',
            array('data' => 0)
        );
        $this->assertSame($struct->toArray(), array());
    }}
