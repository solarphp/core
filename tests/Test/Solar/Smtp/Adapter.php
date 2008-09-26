<?php
/**
 * 
 * Abstract class test.
 * 
 */
class Test_Solar_Smtp_Adapter extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Smtp_Adapter = array(
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
        $this->skip('abstract class');
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
        $obj = Solar::factory('Solar_Smtp_Adapter');
        $this->assertInstance($obj, 'Solar_Smtp_Adapter');
    }
    
    /**
     * 
     * Test -- Issues SMTP AUTH (if not already issued) and returns success indicator.
     * 
     */
    public function testAuth()
    {
        $this->skip('abstract method');
    }
    
    /**
     * 
     * Test -- Connects to the SMTP server and sets the timeout.
     * 
     */
    public function testConnect()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Issues SMTP DATA to send the email message itself.
     * 
     */
    public function testData()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Issues SMTP QUIT and disconnects from the SMTP server.
     * 
     */
    public function testDisconnect()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the line-ending string.
     * 
     */
    public function testGetCrlf()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the connection log.
     * 
     */
    public function testGetLog()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Issues HELO/EHLO sequence to starts the session.
     * 
     */
    public function testHelo()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Are we currently connected to the server?
     * 
     */
    public function testIsConnected()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Issues SMTP MAIL FROM to indicate who the message is from.
     * 
     */
    public function testMail()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Issues SMTP NOOP to keep the connection alive (or check the connection).
     * 
     */
    public function testNoop()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Issues SMTP QUIT to end the current session.
     * 
     */
    public function testQuit()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Issues SMTP RCPT TO to indicate who the message is to.
     * 
     */
    public function testRcpt()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Clears the connection log.
     * 
     */
    public function testResetLog()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Issues SMTP RSET to reset the connection and clear transaction flags.
     * 
     */
    public function testRset()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the line-ending string.
     * 
     */
    public function testSetCrlf()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Issues SMTP VRFY to verify a username or email address at the server.
     * 
     */
    public function testVrfy()
    {
        $this->todo('stub');
    }
}
