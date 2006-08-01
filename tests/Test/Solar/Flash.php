<?php

class Test_Solar_Flash extends Solar_Test {
    
    protected $_flash;
    
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function __destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        parent::setup();
        $this->_flash = Solar::factory('Solar_Flash');
    }
    
    public function teardown()
    {
        // make sure $_SESSION values don't pass into the next test
        $this->_flash->reset();
    }
    
    public function test__construct()
    {
        $this->assertInstance($this->_flash, 'Solar_Flash');
    }
    
    public function testSet()
    {
        $this->_flash->set('foo', 'bar');
        $actual = $_SESSION['Solar_Flash']['Solar']['foo'];
        $expect = 'bar';
        $this->assertSame($actual, $expect);
    }
    
    public function testAdd()
    {
        $this->_flash->add('foo', 'bar');
        $this->_flash->add('foo', 'baz');
        $this->_flash->add('foo', 'zim');
        
        $actual = $_SESSION['Solar_Flash']['Solar']['foo'];
        $expect = array('bar', 'baz', 'zim');
        $this->assertSame($actual, $expect);
    }
    
    public function testGet()
    {
        // set the value
        $this->_flash->set('foo', 'bar');
        $actual = $_SESSION['Solar_Flash']['Solar']['foo'];
        $expect = 'bar';
        $this->assertSame($actual, $expect);
        
        // read the value
        $actual = $this->_flash->get('foo');
        $this->assertSame($actual, $expect);
        
        // should have removed it after reading
        $actual = empty($_SESSION['Solar_Flash']['Solar']['foo']);
        $this->assertTrue($actual);
    }
}
?>