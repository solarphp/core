<?php

class Test_Solar_Uri extends Solar_Test {
    
    protected $_server;
    
    protected $_get;
    
    protected $_uri;
    
    protected $_class = 'Solar_Uri';
    
    protected $_request;
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // when running from the command line, these elements are empty.
        // add them so that web-like testing can occur.
        $this->_request = Solar::factory('Solar_Request');
        $this->_request->server['HTTP_HOST']  = 'example.com';
        $this->_request->server['SCRIPT_NAME']  = '/path/to/index.php';
        $this->_request->server['PATH_INFO']    = '/appname/action';
        $this->_request->server['QUERY_STRING'] = 'foo=bar&baz=dib';
        $this->_request->server['REQUEST_URI']  = $this->_request->server['SCRIPT_NAME']
                                                . $this->_request->server['PATH_INFO']
                                                . '?'
                                                . $this->_request->server['QUERY_STRING'];

        // emulate GET vars from the URI
        parse_str($this->_request->server['QUERY_STRING'], $this->_request->get);
    }
    
    public function setup()
    {
        $this->_uri = Solar::factory($this->_class);
    }
    
    public function teardown()
    {
        unset($this->_uri);
    }
    
    public function test__construct()
    {
        $this->assertInstance($this->_uri, $this->_class);
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
?>