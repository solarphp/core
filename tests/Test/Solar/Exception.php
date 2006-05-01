<?php

class Test_Solar_Exception extends Solar_Test {
    
    protected $_config = array(
        'class' => 'Solar_Test_Example',
        'code'  => 'ERR_CODE',
        'text'  => 'Error message',
        'info'  => array(
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => 'gir',
        ),
    );

    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function setup()
    {
    }
    
    public function teardown()
    {
    }
    
    public function test__construct()
    {
        $e = Solar::factory('Solar_Exception', $this->_config);
        $this->assertInstance($e, 'Solar_Exception');
        $this->assertProperty($e, '_class', 'same', $this->_config['class']);
        $this->assertProperty($e, 'code', 'same', $this->_config['code']);
        $this->assertProperty($e, 'message', 'same', $this->_config['text']);
        $this->assertProperty($e, '_info', 'same', $this->_config['info']);
    }
    
    public function test__toString()
    {
        $this->skip('filesystem-specific');
        
        /*
        $e = Solar::factory('Solar_Exception', $this->_config);
        
        $file = __FILE__;
        $expect = "exception 'Solar_Exception'
class::code 'Solar_Test_Example::ERR_CODE' 
with message 'Error message' 
information array (
  'foo' => 'bar',
  'baz' => 'dib',
  'zim' => 'gir',
) 
Stack trace:
  #0 $file(41): Solar::factory('Solar_Exception', Array)
  #1 /Users/pmjones/Sites/dev/solar/src/Solar/Test/Suite.php(197): Test_Solar_Exception->test__toString()
  #2 /Users/pmjones/Sites/dev/solar/src/tests2/run.php(12): Solar_Test_Suite->run()
  #3 {main}";
        
        Solar::dump($e->__toString());
        
        $this->assertSame($e->__toString(), $expect);
        */
    }
    
    public function testGetInfo()
    {
        $e = Solar::factory('Solar_Exception', $this->_config);
        $this->assertSame($e->getInfo(), $this->_config['info']);
    }
    
    public function testGetClass()
    {
        $e = Solar::factory('Solar_Exception', $this->_config);
        $this->assertSame($e->getClass(), $this->_config['class']);
    }
    
    public function testGetClassCode()
    {
        $e = Solar::factory('Solar_Exception', $this->_config);
        $this->assertSame($e->getClassCode(), $this->_config['class'] . '::' . $this->_config['code']);
    }
    
    public function testGetMessage()
    {
        $e = Solar::factory('Solar_Exception', $this->_config);
        $this->assertSame($e->getMessage(), $this->_config['text']);
    }
    
    public function testGetCode()
    {
        $e = Solar::factory('Solar_Exception', $this->_config);
        $this->assertSame($e->getCode(), $this->_config['code']);
    }
    
    public function testGetFile()
    {
        $this->skip('filesystem-specific, always reports "Solar.php"');
    }
    
    public function testGetLine()
    {
        $e = Solar::factory('Solar_Exception', $this->_config);
        $this->assertSame($e->getLine(), 416); // the line in Solar::factory() method
    }
    
    public function testGetTrace()
    {
        $this->skip('filesystem-specific');
        
        /*
        $e = Solar::factory('Solar_Exception', $this->_config);

        $expect = array(
          0 => array(
            'file' => __FILE__,
            'line' => 14,
            'function' => 'factory',
            'class' => 'Solar',
            'type' => '::',
            'args' => array(
              0 => 'Solar_Exception',
              1 => array(
                'class' => 'Solar_Test_Example',
                'code' => 'ERR_CODE',
                'text' => 'Error message',
                'info' => array(
                  'foo' => 'bar',
                  'baz' => 'dib',
                  'zim' => 'gir',
                ),
              ),
            ),
          ),
        );

        $this->assertSame($e->getTrace(), $expect);
        */
    }
    
    public function testGetTraceAsString()
    {
        $this->skip('filesystem-specific');
        
        /*
        $e = Solar::factory('Solar_Exception', $this->_config);

        $expect = "#0 " . __FILE__ . "(14): Solar::factory('Solar_Exception', Array)
        #1 {main}";

        $this->assertSame($e->getTraceAsString(), $expect);
        */
    }
    
    public function test_specificErrorCodes()
    {
        // list of error codes
        $list = array(
            'ERR_CONNECTION_FAILED'      => 'ConnectionFailed',
            'ERR_EXTENSION_NOT_LOADED'   => 'ExtensionNotLoaded',
            'ERR_FILE_NOT_FOUND'         => 'FileNotFound',
            'ERR_FILE_NOT_READABLE'      => 'FileNotReadable',
            'ERR_METHOD_NOT_CALLABLE'    => 'MethodNotCallable',
            'ERR_METHOD_NOT_IMPLEMENTED' => 'MethodNotImplemented',
        );

        $example = Solar::factory('Solar_Test_Example');

        foreach ($list as $code => $name) {
            try {
                // throw a Solar-wide specific exception based on an error code string
                $example->exceptionFromCode($code);
            } catch (Exception $e) {
                $this->assertInstance($e, "Solar_Exception_$name");
                // make sure the class and code works
                $this->assertSame($e->getClass(), 'Solar_Test_Example');
                $this->assertSame($e->getCode(), $code);
                // make sure the automatic translation works
                $this->assertSame($e->getMessage(), $example->locale($code));
            }
        }
    }
}
?>