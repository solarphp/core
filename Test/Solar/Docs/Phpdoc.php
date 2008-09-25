<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Docs_Phpdoc extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Docs_Phpdoc = array(
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
        $obj = Solar::factory('Solar_Docs_Phpdoc');
        $this->assertInstance($obj, 'Solar_Docs_Phpdoc');
    }
    
    /**
     * 
     * Test -- Returns docblock comment parsed into summary, narrative, and technical information portions.
     * 
     */
    public function testParse()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one or more @author lines into $this->_info.
     * 
     */
    public function testParseAuthor()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @category line into $this->_info.
     * 
     */
    public function testParseCategory()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @copyright line into $this->_info.
     * 
     */
    public function testParseCopyright()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @deprec line into $this->_info; alias for @deprecated.
     * 
     */
    public function testParseDeprec()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @deprecated line into $this->_info.
     * 
     */
    public function testParseDeprecated()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @example line into $this->_info.
     * 
     */
    public function testParseExample()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one or more @exception lines into $this->_info; alias for @throws.
     * 
     */
    public function testParseException()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @ignore line into $this->_info.
     * 
     */
    public function testParseIgnore()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @license line into $this->_info.
     * 
     */
    public function testParseLicense()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one or more @link lines into $this->_info.
     * 
     */
    public function testParseLink()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @package line into $this->_info.
     * 
     */
    public function testParsePackage()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one or more @param lines into $this->_info.
     * 
     */
    public function testParseParam()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @return line into $this->_info.
     * 
     */
    public function testParseReturn()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one or more @see lines into $this->_info.
     * 
     */
    public function testParseSee()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @since line into $this->_info.
     * 
     */
    public function testParseSince()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one or more @staticvar lines into $this->_info.
     * 
     */
    public function testParseStaticvar()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @subpackage line into $this->_info.
     * 
     */
    public function testParseSubpackage()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one or more @throws lines into $this->_info.
     * 
     */
    public function testParseThrows()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one ore more @todo lines into $this->_info.
     * 
     */
    public function testParseTodo()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @var line into $this->_info.
     * 
     */
    public function testParseVar()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Parses one @version line into $this->_info.
     * 
     */
    public function testParseVersion()
    {
        $this->todo('stub');
    }
}
