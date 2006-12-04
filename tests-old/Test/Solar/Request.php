<?php

class Test_Solar_Request extends Solar_Test {
    
    protected $_orig = array(
        '_GET'    => array(),
        '_POST'   => array(),
        '_COOKIE' => array(),
        '_SERVER' => array(),
        '_ENV'    => array(),
        '_FILES'  => array(),
    );
    
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function __destruct()
    {
        parent::__destruct();
    }
    
    protected function _getRequest()
    {
        $config = array('reload' => true);
        return Solar::factory('Solar_Request', $config);
    }
    
    public function setup()
    {
        parent::setup();
        
        // save original values
        $keys = array_keys($this->_orig);
        foreach ($keys as $var) {
            if (isset($GLOBALS[$var])) {
                $this->_orig[$var] = $GLOBALS[$var];
            }
        }
    }
    
    public function teardown()
    {
        parent::teardown();
        
        // return original values
        $keys = array_keys($this->_orig);
        foreach ($keys as $var) {
            $this->_orig[$var] = $GLOBALS[$var] = $this->_orig[$var];
        }
    }
    
    public function test__construct()
    {
        $request = $this->_getRequest();
        $this->assertInstance($request, 'Solar_Request');
    }
    
    public function testGet()
    {
        $_GET['foo'] = 'bar';
        $request = $this->_getRequest();
        
        // get a key
        $actual = $request->get('foo');
        $this->assertSame($actual, 'bar');
        
        // get a non-existent key
        $actual = $request->get('baz');
        $this->assertNull($actual);
        
        // get a non-existent key with default value
        $actual = $request->get('baz', 'dib');
        $this->assertSame($actual, 'dib');
    }
    
    public function testPost()
    {
        $_POST['foo'] = 'bar';
        $request = $this->_getRequest();
        
        // get a key
        $actual = $request->post('foo');
        $this->assertSame($actual, 'bar');
        
        // get a non-existent key
        $actual = $request->post('baz');
        $this->assertNull($actual);
        
        // get a non-existent key with default value
        $actual = $request->post('baz', 'dib');
        $this->assertSame($actual, 'dib');
    }
    
    public function testCookie()
    {
        $_COOKIE['foo'] = 'bar';
        $request = $this->_getRequest();
        
        // get a key
        $actual = $request->cookie('foo');
        $this->assertSame($actual, 'bar');
        
        // get a non-existent key
        $actual = $request->cookie('baz');
        $this->assertNull($actual);
        
        // get a non-existent key with default value
        $actual = $request->cookie('baz', 'dib');
        $this->assertSame($actual, 'dib');
    }
    
    public function testEnv()
    {
        $_ENV['foo'] = 'bar';
        $request = $this->_getRequest();
        
        // env a key
        $actual = $request->env('foo');
        $this->assertSame($actual, 'bar');
        
        // env a non-existent key
        $actual = $request->env('baz');
        $this->assertNull($actual);
        
        // env a non-existent key with default value
        $actual = $request->env('baz', 'dib');
        $this->assertSame($actual, 'dib');
    }
    
    public function testServer()
    {
        $_SERVER['foo'] = 'bar';
        $request = $this->_getRequest();
        
        // get a key
        $actual = $request->server('foo');
        $this->assertSame($actual, 'bar');
        
        // get a non-existent key
        $actual = $request->server('baz');
        $this->assertNull($actual);
        
        // get a non-existent key with default value
        $actual = $request->server('baz', 'dib');
        $this->assertSame($actual, 'dib');
    }
    
    public function testFiles()
    {
        $_FILES['foo'] = 'bar';
        $request = $this->_getRequest();
        
        // get a key
        $actual = $request->files('foo');
        $this->assertSame($actual, 'bar');
        
        // get a non-existent key
        $actual = $request->files('baz');
        $this->assertNull($actual);
        
        // get a non-existent key with default value
        $actual = $request->files('baz', 'dib');
        $this->assertSame($actual, 'dib');
    }
    
    public function testHttp()
    {
        $_SERVER['HTTP_FOO'] = 'bar';
        $request = $this->_getRequest();
        
        // get a key
        $actual = $request->http('Foo');
        $this->assertSame($actual, 'bar');
        
        // get a non-existent key
        $actual = $request->http('Baz');
        $this->assertNull($actual);
        
        // get a non-existent key with default value
        $actual = $request->http('Baz', 'dib');
        $this->assertSame($actual, 'dib');
    }
    
    public function testIsGet()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = $this->_getRequest();
        $this->assertTrue($request->isGet());
        
        $_SERVER['REQUEST_METHOD'] = 'XXX';
        $request = $this->_getRequest();
        $this->assertFalse($request->isGet());
    }
    
    public function testIsPost()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = $this->_getRequest();
        $this->assertTrue($request->isPost());
        
        $_SERVER['REQUEST_METHOD'] = 'XXX';
        $request = $this->_getRequest();
        $this->assertFalse($request->isPost());
    }
    
    public function testIsPut()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request = $this->_getRequest();
        $this->assertTrue($request->isPut());
        
        $_SERVER['REQUEST_METHOD'] = 'XXX';
        $request = $this->_getRequest();
        $this->assertFalse($request->isPut());
    }
    
    public function testIsDelete()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $request = $this->_getRequest();
        $this->assertTrue($request->isDelete());
        
        $_SERVER['REQUEST_METHOD'] = 'XXX';
        $request = $this->_getRequest();
        $this->assertFalse($request->isDelete());
    }
    
    public function testIsXml()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $request = $this->_getRequest();
        $this->assertTrue($request->isXml());
        
        
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XXX';
        $request = $this->_getRequest();
        $this->assertFalse($request->isXml());
    }
    
    public function testLoad()
    {
        // test loading the first time
        $request = Solar::factory('Solar_Request', array('reload' => true));
        
        // test changing the vars, should not be reloaded
        $_GET['foo'] = 'bar';
        $actual = $request->get('foo');
        $this->assertNull($actual);
        
        // now reload, should pick up the changed $_GET
        $request = Solar::factory('Solar_Request', array('reload' => true));
        $actual = $request->get('foo');
        $this->assertSame($actual, 'bar');
    }
}
?>