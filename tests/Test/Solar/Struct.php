<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Struct extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Struct = array(
    );
    
    // -----------------------------------------------------------------
    // 
    // Support methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration parameters.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    /**
     * 
     * Destructor; runs after all methods are complete.
     * 
     * @param array $config User-defined configuration parameters.
     * 
     */
    public function __destruct()
    {
        parent::__destruct();
    }
    
    /**
     * 
     * Setup; runs before each test method.
     * 
     */
    public function setup()
    {
        parent::setup();
    }
    
    /**
     * 
     * Setup; runs after each test method.
     * 
     */
    public function teardown()
    {
        parent::teardown();
    }
    
    protected function _newStruct()
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
    
    // -----------------------------------------------------------------
    // 
    // Test methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Test -- Constructor.
     * 
     */
    public function test__construct()
    {
        $obj = $this->_newStruct();
        $this->assertInstance($obj, 'Solar_Struct');
    }
    
    public function test__construct_badData()
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
    }
    
    /**
     * 
     * Test -- Gets a data value.
     * 
     */
    public function test__get()
    {
        $struct = $this->_newStruct();
        $this->assertSame($struct->foo, 'bar');
        $this->assertSame($struct['foo'], 'bar');
        
        $struct = $this->_newStruct();
        try {
            $invalid = $struct->noSuchKey;
            $this->fail('Should have thrown a NO_SUCH_KEY exception.');
        } catch (Solar_Struct_Exception_NoSuchKey $e) {
            // pass
        }
        
        $struct = $this->_newStruct();
        try {
            $invalid = $struct['no_such_key'];
            $this->fail('Should have thrown a NO_SUCH_KEY exception.');
        } catch (Solar_Struct_Exception_NoSuchKey $e) {
            // pass
        }
    }
    
    /**
     * 
     * Test -- Does a certain key exist in the data?
     * 
     */
    public function test__isset()
    {
        $struct = $this->_newStruct();
        $this->assertTrue(isset($struct->foo));
        $this->assertTrue(isset($struct['foo']));
        $this->assertFalse(isset($struct->noSuchKey));
        $this->assertFalse(isset($struct['noSuchKey']));
    }
    
    /**
     * 
     * Test -- Sets a key value.
     * 
     */
    public function test__set()
    {
        $struct = $this->_newStruct();
        $struct->zim = 'irk';
        $this->assertSame($struct->zim, 'irk');
        $this->assertSame($struct->zim, $struct['zim']);
        
        $struct = $this->_newStruct();
        $struct->a = 'b';
        $this->assertSame($struct->a, 'b');
        $this->assertSame($struct->a, $struct['a']);
    }
    
    /**
     * 
     * Test -- Sets a key in the data to null.
     * 
     */
    public function test__unset()
    {
        $struct = $this->_newStruct();
        unset($struct->foo);
        $this->assertFalse(isset($struct->foo));
        try {
            $invalid = $struct->foo;
            $this->fail('Should have thrown a NO_SUCH_KEY exception.');
        } catch (Solar_Struct_Exception_NoSuchKey $e) {
            // pass
        }
        
        $struct = $this->_newStruct();
        unset($struct['foo']);
        $this->assertFalse(isset($struct['foo']));
        try {
            $invalid = $struct['foo'];
            $this->fail('Should have thrown a NO_SUCH_KEY exception.');
        } catch (Solar_Struct_Exception_NoSuchKey $e) {
            // pass
        }
    }
    
    /**
     * 
     * Test -- Countable: how many keys are there?
     * 
     */
    public function testCount()
    {
        $struct = $this->_newStruct();
        $actual = count($struct);
        $expect = 3;
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- Iterator: get the current value for the array pointer.
     * 
     */
    public function testCurrent()
    {
        $struct = $this->_newStruct();
        $actual = $struct->current();
        $expect = 'bar';
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- Iterator: get the current key for the array pointer.
     * 
     */
    public function testKey()
    {
        $struct = $this->_newStruct();
        $actual = $struct->key();
        $expect = 'foo';
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- Loads the struct with data from an array or another struct.
     * 
     */
    public function testLoad()
    {
        $struct = $this->_newStruct();
        $expect = array(
            'foo' => 'bar2',
            'baz' => 'dib2',
            'zim' => 'gir2',
        );
        $struct->load($expect);
        $actual = $struct->toArray();
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- Iterator: move to the next position.
     * 
     */
    public function testNext()
    {
        $struct = $this->_newStruct();
        $struct->next();
        
        $actual = $struct->key();
        $expect = 'baz';
        $this->assertSame($actual, $expect);
        
        $actual = $struct->current();
        $expect = 'dib';
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- ArrayAccess: does the requested key exist?
     * 
     */
    public function testOffsetExists()
    {
        $struct = $this->_newStruct();
        $this->assertTrue($struct->offsetExists('foo'));
        $this->assertFalse($struct->offsetExists('noSuchKey'));
    }
    
    /**
     * 
     * Test -- ArrayAccess: get a key value.
     * 
     */
    public function testOffsetGet()
    {
        $struct = $this->_newStruct();
        
        $actual = $struct->offsetGet('foo');
        $expect = 'bar';
        $this->assertSame($actual, $expect);
        
        try {
            $actual = $struct->offsetGet('noSuchKey');
            $this->fail('Should have thrown a NO_SUCH_KEY exception.');
        } catch (Solar_Struct_Exception_NoSuchKey $e) {
            // pass
        }
    }
    
    /**
     * 
     * Test -- ArrayAccess: set a key value.
     * 
     * Identical to __set().
     * 
     */
    public function testOffsetSet()
    {
        $struct = $this->_newStruct();
        $struct->offsetSet('zim', 'irk');
        $this->assertSame($struct->offsetGet('zim'), 'irk');
    }
    
    /**
     * 
     * Test -- ArrayAccess: unset a key.
     * 
     * Identical to __unset().
     * 
     */
    public function testOffsetUnset()
    {
        $struct = $this->_newStruct();
        $this->assertTrue($struct->offsetExists('foo'));
        $struct->offsetUnset('foo');
        $this->assertFalse($struct->offsetExists('foo'));
    }
    
    /**
     * 
     * Test -- Iterator: move to the first position.
     * 
     */
    public function testRewind()
    {
        $struct = $this->_newStruct();
        
        // next() to the end
        $struct->next();
        $struct->next();
        
        $actual = $struct->key();
        $expect = 'zim';
        $this->assertSame($actual, $expect);
        
        $actual = $struct->current();
        $expect = 'gir';
        $this->assertSame($actual, $expect);
        
        // rewind and check
        $struct->rewind();
        
        $actual = $struct->key();
        $expect = 'foo';
        $this->assertSame($actual, $expect);
        
        $actual = $struct->current();
        $expect = 'bar';
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- Returns a copy of the object data as an array.
     * 
     */
    public function testToArray()
    {
        $struct = $this->_newStruct();
        $actual = $struct->toArray();
        $expect = array(
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => 'gir',
        );
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- Iterator: is the current position valid?
     * 
     */
    public function testValid()
    {
        // foo
        $struct = $this->_newStruct();
        $this->assertTrue($struct->valid());
        
        // bar
        $struct->next();
        $this->assertTrue($struct->valid());
        
        // baz
        $struct->next();
        $this->assertTrue($struct->valid());
        
        // done!
        $struct->next();
        $this->assertFalse($struct->valid());
    }
    
    public function test_iterator()
    {
        $struct = $this->_newStruct();
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
