<?php

class Test_Solar_Session extends Solar_Test {
    
    protected $_session;
    
    protected $_class;
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_class = get_class($this);
    }
    
    public function __destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        parent::setup();
        $this->_session = Solar::factory('Solar_Session');
        $this->_session->setClass($this->_class);
    }
    
    public function teardown()
    {
        // make sure values don't pass into the next test
        $this->_session->resetAll();
    }
    
    public function test__construct()
    {
        $this->assertInstance($this->_session, 'Solar_Session');
    }
    
    public function testSet()
    {
        $this->_session->set('foo', 'bar');
        $actual = $_SESSION[$this->_class]['foo'];
        $expect = 'bar';
        $this->assertSame($actual, $expect);
    }
    
    public function testAdd()
    {
        $this->_session->add('foo', 'bar');
        $this->_session->add('foo', 'baz');
        $this->_session->add('foo', 'zim');
        
        $actual = $_SESSION[$this->_class]['foo'];
        $expect = array('bar', 'baz', 'zim');
        $this->assertSame($actual, $expect);
    }
    
    public function testGet()
    {
        // set the value
        $this->_session->set('foo', 'bar');
        $actual = $_SESSION[$this->_class]['foo'];
        $expect = 'bar';
        $this->assertSame($actual, $expect);
        
        // read the value
        $actual = $this->_session->get('foo');
        $this->assertSame($actual, $expect);
        
        // ask for nonexistent value and get default instead
        $actual = $this->_session->get('baz', 'dib');
        $expect = 'dib';
        $this->assertSame($actual, $expect);
    }
    
    public function testReset()
    {
        // set the value
        $this->_session->set('foo', 'bar');
        $actual = $_SESSION[$this->_class]['foo'];
        $expect = 'bar';
        $this->assertSame($actual, $expect);
        
        // now reset
        $this->_session->reset();
        $actual = $_SESSION[$this->_class];
        $expect = array();
        $this->assertSame($actual, $expect);
    }
    
    public function testSetFlash()
    {
        $this->_session->setFlash('foo', 'bar');
        $actual = $_SESSION['Solar_Session']['flash'][$this->_class]['foo'];
        $expect = 'bar';
        $this->assertSame($actual, $expect);
    }
    
    public function testAddFlash()
    {
        $this->_session->addFlash('foo', 'bar');
        $this->_session->addFlash('foo', 'baz');
        $this->_session->addFlash('foo', 'zim');
        
        $actual = $_SESSION['Solar_Session']['flash'][$this->_class]['foo'];
        $expect = array('bar', 'baz', 'zim');
        $this->assertSame($actual, $expect);
    }
    
    public function testGetFlash()
    {
        // set the value
        $this->_session->setFlash('foo', 'bar');
        $actual = $_SESSION['Solar_Session']['flash'][$this->_class]['foo'];
        $expect = 'bar';
        $this->assertSame($actual, $expect);
        
        // read the value
        $actual = $this->_session->getFlash('foo');
        $this->assertSame($actual, $expect);
        
        // should have removed it after reading
        $actual = empty($_SESSION['Solar_Session']['flash'][$this->_class]['foo']);
        $this->assertTrue($actual);
    }
    
    public function testResetFlash()
    {
        // set the value
        $this->_session->setFlash('foo', 'bar');
        $actual = $_SESSION['Solar_Session']['flash'][$this->_class]['foo'];
        $expect = 'bar';
        $this->assertSame($actual, $expect);
        
        // now reset
        $this->_session->resetFlash();
        $actual = $_SESSION['Solar_Session']['flash'][$this->_class];
        $expect = array();
        $this->assertSame($actual, $expect);
    }
    
    public function testResetAll()
    {
        // set the value
        $this->_session->set('foo', 'bar');
        $actual = $_SESSION[$this->_class]['foo'];
        $expect = 'bar';
        $this->assertSame($actual, $expect);
        
        // set the flash value
        $this->_session->setFlash('foo', 'bar');
        $actual = $_SESSION['Solar_Session']['flash'][$this->_class]['foo'];
        $expect = 'bar';
        $this->assertSame($actual, $expect);
        
        // reset all
        $this->_session->resetAll();
        $expect = array();
        
        // should be blank in store ...
        $actual = $_SESSION[$this->_class];
        $this->assertSame($actual, $expect);
        
        // ... and in flash.
        $actual = $_SESSION['Solar_Session']['flash'][$this->_class];
        $this->assertSame($actual, $expect);
    }
    
}
?>