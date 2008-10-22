<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Class extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Class = array(
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
        $obj = Solar::factory('Solar_Class');
        $this->assertInstance($obj, 'Solar_Class');
    }
    
    /**
     * 
     * Test -- Loads a class or interface file from the include_path.
     * 
     */
    public function testAutoload()
    {
        $this->assertFalse(class_exists('Solar_Example', false));
        Solar_Class::autoload('Solar_Example');
        $this->assertTrue(class_exists('Solar_Example', false));
    }
    
    public function testAutoload_emptyClass()
    {
        $this->assertFalse(class_exists('Solar_Example', false));
        try {
            Solar_Class::autoload('');
            $this->fail('Should throw exception on empty class name.');
        } catch (Exception $e) {
            $this->assertInstance($e, 'Solar_Class_Exception_AutoloadEmpty');
        }
    }
    
    /**
     * @todo this actually tests the file-not-found logic, not the file-found-
     * but-class-not-there logic.  fix that.  :-/
     */
    public function testAutoload_noSuchClass()
    {
        try {
            Solar_Class::autoload('No_Such_Class');
            $this->fail('Should throw exception when class does not exist.');
        } catch (Exception $e) {
            $this->assertInstance($e, 'Solar_Exception');
        }
    }
    
    /**
     * 
     * Test -- Returns the directory for a specific class, plus an optional subdirectory path.
     * 
     */
    public function testDir()
    {
        $actual = Solar_Class::dir(get_class($this));
        $expect = dirname(__FILE__) . '/Class/';
        
        // to avoid include-vs-source difference, compare only the tails
        $len = strlen(get_class($this)) * -1 - 1;
        $actual = substr($actual, $len);
        $expect = substr($expect, $len);
        
        $this->assertSame($actual, $expect);
    }
    
    public function testDir_sub()
    {
        $actual = Solar_Class::dir(get_class($this), 'sub');
        $expect = dirname(__FILE__) . '/Class/sub/';
        
        // to avoid include-vs-source difference, compare only the tails
        $len = strlen(get_class($this)) * -1 - 1;
        $actual = substr($actual, $len);
        $expect = substr($expect, $len);
        
        $this->assertSame($actual, $expect);
    }
    
    public function testDir_object()
    {
        $actual = Solar_Class::dir($this);
        $expect = dirname(__FILE__) . '/Class/';
        
        // to avoid include-vs-source difference, compare only the tails
        $len = strlen(get_class($this)) * -1 - 1;
        $actual = substr($actual, $len);
        $expect = substr($expect, $len);
        
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- Returns an array of the parent classes for a given class.
     * 
     */
    public function testParents()
    {
        $actual = Solar_Class::parents('Solar_Example_Exception_CustomCondition');
        $expect = array(
            'Exception',
            'Solar_Exception',
            'Solar_Example_Exception',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testParents_withObject()
    {
        $object = Solar::factory('Solar_Example_Exception_CustomCondition');
        $actual = Solar_Class::parents($object);
        $expect = array(
            'Exception',
            'Solar_Exception',
            'Solar_Example_Exception',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testParents_includeSelf()
    {
        $actual = Solar_Class::parents('Solar_Example_Exception_CustomCondition', true);
        $expect = array(
            'Exception',
            'Solar_Exception',
            'Solar_Example_Exception',
            'Solar_Example_Exception_CustomCondition',
        );
        $this->assertSame($actual, $expect);
    }
    
}
