<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Mail_Message_Part extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Mail_Message_Part = array(
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
        $obj = Solar::factory('Solar_Mail_Message_Part');
        $this->assertInstance($obj, 'Solar_Mail_Message_Part');
    }
    
    /**
     * 
     * Test -- Returns the headers, a newline, and the content, all as a single block.
     * 
     */
    public function testFetch()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the body content of this part with the proper encoding.
     * 
     */
    public function testFetchContent()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns all the headers as a string.
     * 
     */
    public function testFetchHeaders()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the Content-Type boundary for this part.
     * 
     */
    public function testGetBoundary()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the Content-Type character set for this part.
     * 
     */
    public function testGetCharset()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the body content for this part.
     * 
     */
    public function testGetContent()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the CRLF sequence for this part.
     * 
     */
    public function testGetCrlf()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the Content-Disposition for this part.
     * 
     */
    public function testGetDisposition()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the Content-Transfer-Encoding for this part.
     * 
     */
    public function testGetEncoding()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the Content-Disposition filename for this part.
     * 
     */
    public function testGetFilename()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the Content-Type for this part.
     * 
     */
    public function testGetType()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the Content-Type boundary for this part.
     * 
     */
    public function testSetBoundary()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the Content-Type character set for this part.
     * 
     */
    public function testSetCharset()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the body content for this part.
     * 
     */
    public function testSetContent()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the CRLF sequence for this part.
     * 
     */
    public function testSetCrlf()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the Content-Disposition for this part.
     * 
     */
    public function testSetDisposition()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the Content-Transfer-Encoding for this part.
     * 
     */
    public function testSetEncoding()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the Content-Disposition filename for this part.
     * 
     */
    public function testSetFilename()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets (or resets) one header in the part.
     * 
     */
    public function testSetHeader()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the Content-Type for this part.
     * 
     */
    public function testSetType()
    {
        $this->todo('stub');
    }
}
