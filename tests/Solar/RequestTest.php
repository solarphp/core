<?php

require_once dirname(__FILE__) . '/../SolarUnitTest.config.php';
require_once 'Solar/Request.php';

class Solar_RequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        Solar::start('config.inc.php');
    }
    
    public function tearDown() 
    {
        Solar::stop();
    }
    
    protected function _getRequest()
    {
        return Solar::factory('Solar_Request');
    }

    public function testCanInstantiateThroughFactory()
    {
        $request = $this->_getRequest();
        $this->assertTrue($request instanceof Solar_Request);
    }
    
    public function testCanReadValuesFromGetSuperGlobalByGet()
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
    
    public function testCanReadValuesFromPostSuperGlobalByPost()
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
    
    public function testCanReadValuesFromCookieSuperGlobalByCookie()
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
    
    public function testCanReadValuesFromEnvSuperGlobalByEnv()
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
    
    public function testCanReadValuesFromServerSuperGlobalByServer()
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
    
    public function testCanReadValuesFromFilesByFiles()
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
    
    public function testCanReadHttpValuesFromWithinServerSuperGlobalByHttp()
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
    
    public function testIsgetReturnsBooleanBasedOnRequestMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = $this->_getRequest();
        $this->assertTrue($request->isGet());
        
        $_SERVER['REQUEST_METHOD'] = 'XXX';
        $request = $this->_getRequest();
        $this->assertFalse($request->isGet());
    }
    
    public function testIspostReturnsBooleanBasedOnRequestMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = $this->_getRequest();
        $this->assertTrue($request->isPost());
        
        $_SERVER['REQUEST_METHOD'] = 'XXX';
        $request = $this->_getRequest();
        $this->assertFalse($request->isPost());
    }
    
    public function testIsputReturnsBooleanBasedOnRequestMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request = $this->_getRequest();
        $this->assertTrue($request->isPut());
        
        $_SERVER['REQUEST_METHOD'] = 'XXX';
        $request = $this->_getRequest();
        $this->assertFalse($request->isPut());
    }
    
    public function testIsdeleteReturnsBooleanBasedOnRequestMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $request = $this->_getRequest();
        $this->assertTrue($request->isDelete());
        
        $_SERVER['REQUEST_METHOD'] = 'XXX';
        $request = $this->_getRequest();
        $this->assertFalse($request->isDelete());
    }
    
    public function testIsXhrReturnsBooleanBasedOnRequestMethod()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $request = $this->_getRequest();
        $this->assertTrue($request->isXhr());
        
        
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XXX';
        $request = $this->_getRequest();
        $this->assertFalse($request->isXhr());
    }
    
    // public function testVarsChangedAfterInitialLoadDoNotEffectRequestObject()
    // {
    //     // make sure that $_GET['foo'] is cleared
    //     if (isset($_GET['foo'])) {
    //         unset($_GET['foo']);
    //     }
    //     
    //     // test loading the first time
    //     $request = Solar::factory('Solar_Request', array('reload' => true));
    //     
    //     // test changing the vars, should not be reloaded
    //     $_GET['foo'] = 'bar';
    //     $actual = $request->get('foo');
    //     $this->assertNull($actual);
    // }
    
    // public function testVarsChangedBeforeInitialLoadAreReflectedInRequestObject()
    // {
    //     $_GET['foo'] = 'bar';
    //     
    //     // now reload, should pick up the changed $_GET
    //     $request = Solar::factory('Solar_Request', array('reload' => true));
    //     $actual = $request->get('foo');
    //     $this->assertSame($actual, 'bar');
    // }
}
