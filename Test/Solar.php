<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar = array(
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
        // why does this work?  because although __construct() is private,
        // Solar::factory() is operating inside the class.
        $obj = Solar::factory('Solar');
        $this->assertInstance($obj, 'Solar');
    }
    
    /**
     * 
     * Test -- Loads a class or interface file from the include_path.
     * 
     */
    public function testAutoload()
    {
        $this->assertFalse(class_exists('Solar_Example', false));
        Solar::autoload('Solar_Example');
        $this->assertTrue(class_exists('Solar_Example', false));
    }
    
    public function testAutoload_emptyClass()
    {
        $this->assertFalse(class_exists('Solar_Example', false));
        try {
            Solar::autoload('');
            $this->fail('Should throw exception on empty class name.');
        } catch (Exception $e) {
            $this->assertInstance($e, 'Solar_Exception');
        }
    }
    
    public function testAutoload_noSuchClass()
    {
        try {
            Solar::autoload('No_Such_Class');
            $this->fail('Should throw exception when class does not exist.');
        } catch (Exception $e) {
            $this->assertInstance($e, 'Solar_Exception');
        }
    }
    /**
     * 
     * Test -- Runs a series of callbacks using call_user_func_array().
     * 
     */
    public function testCallbacks()
    {
        $file = Solar_Class::dir($this) . '_support/callbacks.php';
        Solar::callbacks($file);
        $this->assertTrue($GLOBALS['SOLAR_CALLBACKS']);
    }
    
    public function testCallbacks_function()
    {
        $file = Solar_Class::dir($this) . '_support/callbacks-function.php';
        Solar_File::load($file);
        Solar::callbacks(array(
            array(null, 'solar_callbacks_function')
        ));
        $this->assertTrue($GLOBALS['SOLAR_CALLBACKS_FUNCTION']);
    }
    
    public function testCallbacks_staticMethod()
    {
        $file = Solar_Class::dir($this) . '_support/callbacks-static-method.php';
        Solar_File::load($file);
        Solar::callbacks(array(
            array('Solar_Callbacks_Static_Method', 'callback')
        ));
        $this->assertTrue($GLOBALS['SOLAR_CALLBACKS_STATIC_METHOD']);
    }
    
    public function testCallbacks_instanceMethod()
    {
        $file = Solar_Class::dir($this) . '_support/callbacks-instance-method.php';
        Solar_File::load($file);
        $instance = Solar::factory('Solar_Callbacks_Instance_Method');
        Solar::callbacks(array(
            array($instance, 'callback')
        ));
        $this->assertTrue($GLOBALS['SOLAR_CALLBACKS_INSTANCE_METHOD']);
    }
    
    /**
     * 
     * Test -- Cleans the global scope of all variables that are found in other super-globals.
     * 
     */
    public function testCleanGlobals()
    {
        $GLOBALS['foo'] = 'bar';
        $GLOBALS['baz'] = 'dib';
        $_POST['foo'] = 'bar';
        Solar::cleanGlobals();
        $this->assertTrue(empty($GLOBALS['foo']));
        $this->assertFalse(empty($GLOBALS['baz']));
    }
    
    /**
     * 
     * Test -- Safely gets a configuration group array or element value.
     * 
     */
    public function testConfig()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Combination dependency-injection and service-locator method; 
     * returns a dependency object as passed, or an object from the registry, 
     * or a new factory instance.
     * 
     */
    public function testDependency()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Generates a simple exception, but does not throw it.
     * 
     * @todo test exception hierarchy fallbacks
     * 
     */
    public function testException()
    {
        $actual = Solar::exception(
            'Solar',
            'ERR_FOO_BAR',
            'Foo bar error.',
            array(
                'foo' => 'bar'
            )
        );
        
        $this->assertInstance($actual, 'Solar_Exception');
        $this->assertEquals($actual->getClass(), 'Solar');
        $this->assertEquals($actual->getCode(), 'ERR_FOO_BAR');
        $this->assertEquals($actual->getClassCode(), 'Solar::ERR_FOO_BAR');
        $this->assertEquals($actual->getMessage(), 'Foo bar error.');
        $this->assertSame($actual->getInfo(), array('foo' => 'bar'));
    }
    
    /**
     * 
     * Test -- Convenience method to instantiate and configure an object.
     * 
     */
    public function testFactory()
    {
        $class = 'Solar_Example';
        $this->assertFalse(class_exists($class, false));
        $actual = Solar::factory('Solar_Example');
        $this->assertInstance($actual, $class);
    }
    
    /**
     * 
     * Test -- Fetches config file values.
     * 
     */
    public function testFetchConfig()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns an array of the parent classes for a given class.
     * 
     */
    public function testParents()
    {
        $actual = Solar::parents('Solar_Example_Exception_CustomCondition');
        $expect = array(
            'Solar_Example_Exception',
            'Solar_Exception',
            'Exception',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testParents_withObject()
    {
        $object = Solar::factory('Solar_Example_Exception_CustomCondition');
        $actual = Solar::parents($object);
        $expect = array(
            'Solar_Example_Exception',
            'Solar_Exception',
            'Exception',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testParents_includeSelf()
    {
        $actual = Solar::parents('Solar_Example_Exception_CustomCondition', true);
        $expect = array(
            'Solar_Example_Exception_CustomCondition',
            'Solar_Example_Exception',
            'Solar_Exception',
            'Exception',
        );
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- Starts Solar: loads configuration values and and sets up the environment.
     * 
     */
    public function testStart()
    {
        // @todo Maybe do this with Solar_Php?
        $this->skip("Can't test Solar::start() within a Solar environment.");
    }
    
    /**
     * 
     * Test -- Stops Solar: runs stop scripts and cleans up the Solar environment.
     * 
     */
    public function testStop()
    {
        // @todo Maybe do this with Solar_Php?
        $this->skip("Can't test Solar::stop() within a Solar environment.");
    }
}
