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
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the directory for a specific class, plus an optional subdirectory path.
     * 
     */
    public function testDir()
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
        $actual = Solar_Class::parents('Solar_Example_Exception_CustomCondition');
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
        $actual = Solar_Class::parents($object);
        $expect = array(
            'Solar_Example_Exception',
            'Solar_Exception',
            'Exception',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testParents_includeSelf()
    {
        $actual = Solar_Class::parents('Solar_Example_Exception_CustomCondition', true);
        $expect = array(
            'Solar_Example_Exception_CustomCondition',
            'Solar_Example_Exception',
            'Solar_Exception',
            'Exception',
        );
        $this->assertSame($actual, $expect);
    }
    
}
