<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Debug_Var extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Debug_Var = array(
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
        
        // var dumpers
        $this->_text = Solar::factory('Solar_Debug_Var', array(
            'output' => 'text',
        ));
        
        $this->_html = Solar::factory('Solar_Debug_Var', array(
            'output' => 'html',
        ));
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
        $obj = Solar::factory('Solar_Debug_Var');
        $this->assertInstance($obj, 'Solar_Debug_Var');
    }
    
    /**
     * 
     * Test -- Prints the output of Solar_Debug_Var::fetch() with a label.
     * 
     */
    public function testDisplay()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns formatted output from var_dump().
     * 
     */
    public function testFetch()
    {
        $var = 'foo < bar > baz " dib & zim ? gir';
        $expect = "string(33) \"foo < bar > baz \" dib & zim ? gir\"\n";
        $actual = $this->_text->fetch($var);
        $this->assertSame($actual, $expect);
    }
    
    public function testFetch_array()
    {
        $var = array(
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => array(
                'gir', 'irk'
            )
        );
        
        $expect = <<<EXPECT
array(3) {
  ["foo"] => string(3) "bar"
  ["baz"] => string(3) "dib"
  ["zim"] => array(2) {
    [0] => string(3) "gir"
    [1] => string(3) "irk"
  }
}

EXPECT;

        $actual = $this->_text->fetch($var);
        $this->assertSame($actual, $expect);
    }
}
