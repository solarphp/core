<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Inflect extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Inflect = array(
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
        $obj = Solar::factory('Solar_Inflect');
        $this->assertInstance($obj, 'Solar_Inflect');
    }
    
    /**
     * 
     * Test -- Returns "camelCapsWord" and "CamelCapsWord" as "camel-caps-word".
     * 
     */
    public function testCamelToDashes()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns "camelCapsWord" and "CamelCapsWord" as "Camel_Caps_Word".
     * 
     */
    public function testCamelToUnder()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns "Class_Name" as "Class/Name.php".
     * 
     */
    public function testClassToFile()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns "foo-bar-baz" as "fooBarBaz".
     * 
     */
    public function testDashesToCamel()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns "foo-bar-baz" as "FooBarBaz".
     * 
     */
    public function testDashesToStudly()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns any string, converted to using dashes with only lowercase  alphanumerics.
     * 
     */
    public function testToDashes()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns a singular word as a plural.
     * 
     */
    public function testToPlural()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns a plural word as a singular.
     * 
     */
    public function testToSingular()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns "foo_bar_baz" as "fooBarBaz".
     * 
     */
    public function testUnderToCamel()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns "foo_bar_baz" as "FooBarBaz".
     * 
     */
    public function testUnderToStudly()
    {
        $this->todo('stub');
    }
}
