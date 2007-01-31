<?php

require_once dirname(__FILE__) . '/../SolarUnitTest.config.php';
require_once 'Solar/Uri.php';

class Solar_UriTest extends PHPUnit_Framework_TestCase
{
    protected $_uri = null;

    public function setUp() 
    {
        Solar::start('config.inc.php');
        
        // when running from the command line, these elements are empty.
        // add them so that web-like testing can occur.
        $request = Solar::factory('Solar_Request');
        $request->server['HTTP_HOST']  = 'example.com';
        $request->server['SCRIPT_NAME']  = '/path/to/index.php';
        $request->server['PATH_INFO']    = '/appname/action';
        $request->server['QUERY_STRING'] = 'foo=bar&baz=dib';
        $request->server['REQUEST_URI']  = $request->server['SCRIPT_NAME']
                                                . $request->server['PATH_INFO']
                                                . '?'
                                                . $request->server['QUERY_STRING'];
        
        $this->_uri = Solar::factory('Solar_Uri');
    }
    
    public function tearDown() 
    {
        Solar::stop();
        $this->_uri = null;
    }
    
    public function testCanInstantiateThroughFactory()
    {
        $object = Solar::factory('Solar_Uri');
        $this->assertTrue($object instanceof Solar_Uri);
    }
    
    public function test_config()
    {
        $this->assertSame($this->_uri->scheme, 'http');
        $this->assertSame($this->_uri->host, 'example.com');
        $this->assertSame($this->_uri->port, null);
        $this->assertSame($this->_uri->user, null);
        $this->assertSame($this->_uri->pass, null);
        $this->assertSame($this->_uri->path, array('path', 'to', 'index.php', 'appname', 'action'));
        $this->assertSame($this->_uri->query, array('foo'=>'bar', 'baz'=>'dib'));
    }
    
    public function testSet()
    {
        // the URI object itself

        // set up the expected values
        $scheme = 'http';
        $host = 'www.example.net';
        $port = 8080;
        $path = 'some/path/index.php/more/path/info';
        $query = array(
            'a"key' => 'a&value',
            'b?key' => 'this that other',
            'c\'key' => 'tag+tag+tag',
        );

        $spec = "$scheme://$host:$port/$path/";

        $tmp = array();
        foreach ($query as $k => $v) {
            $tmp[] .= urlencode($k) . '=' . urlencode($v);
        }
        $spec .= '?' . implode('&', $tmp);

        // import the URI spec and test that it imported properly
        $this->_uri->set($spec);
        $this->assertSame($this->_uri->scheme, $scheme);
        $this->assertSame($this->_uri->host, $host);
        $this->assertSame($this->_uri->port, $port);
        $this->assertSame($this->_uri->path, explode('/', $path));
        $this->assertSame($this->_uri->query, $query);

        // npw export in full, then re-import and check again.
        // do this to make sure there are no translation errors.
        $spec = $this->_uri->fetch(true);
        $this->_uri->set($spec);
        $this->assertSame($this->_uri->scheme, $scheme);
        $this->assertSame($this->_uri->host, $host);
        $this->assertSame($this->_uri->port, $port);
        $this->assertSame($this->_uri->path, explode('/', $path));
        $this->assertSame($this->_uri->query, $query);
    }
    
    public function testFetch()
    {
        // preliminaries
        $scheme = 'http';
        $host = 'www.example.net';
        $port = 8080;
        $path = '/some/path/index.php';

        $info = array(
            'more', 'path', 'info'
        );

        $istr = implode('/', $info);

        $query = array(
            'a"key' => 'a&value',
            'b?key' => 'this that other',
            'c\'key' => 'tag+tag+tag',
        );

        $tmp = array();
        foreach ($query as $k => $v) {
            $tmp[] .= urlencode($k) . '=' . urlencode($v);
        }

        $qstr = implode('&', $tmp);

        // set up expectations
        $expect_full = "$scheme://$host:$port$path/$istr?$qstr";
        $expect_part = "$path/$istr?$qstr";

        // set the URI
        $this->_uri->set($expect_full);

        // full fetch
        $this->assertSame($this->_uri->fetch(true), $expect_full);

        // partial fetch
        $this->assertSame($this->_uri->fetch(false), $expect_part);
    }
    
    public function testQuick()
    {
        // partial
        $expect = '/path/to/index.php?foo=bar';
        $actual = $this->_uri->quick("http://example.com$expect");
        $this->assertSame($actual, $expect);

        // full
        $expect = 'http://example.com/path/to/index.php?foo=bar';
        $actual = $this->_uri->quick($expect, true);
        $this->assertSame($actual, $expect);
    }
    
    public function testSetQuery()
    {
        $this->_uri->setQuery('a=b&c=d');
        $this->assertSame($this->_uri->query, array('a' => 'b', 'c' => 'd'));
    }
    
    public function testSetPath()
    {
        $this->_uri->setPath('/very/special/example/');
        $this->assertSame($this->_uri->path, array('very', 'special', 'example'));
    }
}
