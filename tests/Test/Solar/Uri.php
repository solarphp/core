<?php

class Test_Solar_Uri extends Solar_Test {
    
    protected $_server;
    
    protected $_get;
    
    protected $_uri;
    
    protected $_class = 'Solar_Uri';
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_server = $_SERVER;
        $this->_get = $_GET;
        
        // when running from the command line, these elements are empty.
        // add them so that web-like testing can occur.
        $_SERVER['HTTP_HOST']    = 'example.com';
        $_SERVER['SCRIPT_NAME']  = '/path/to/index.php';
        $_SERVER['PATH_INFO']    = '/appname/action';
        $_SERVER['QUERY_STRING'] = 'foo=bar&baz=dib';
        $_SERVER['REQUEST_URI']  = $_SERVER['SCRIPT_NAME']
                                 . $_SERVER['PATH_INFO']
                                 . '?' . $_SERVER['QUERY_STRING'];

        // emulate $_GET vars from the URI
        parse_str($_SERVER['QUERY_STRING'], $_GET);
    }
    
    public function __destruct()
    {
        $_GET = $this->_get;
        $_SERVER = $this->_server;
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
        // $assert->setLabel('Initial import');
        $this->assertSame($this->_uri->scheme, $scheme);
        $this->assertSame($this->_uri->host, $host);
        $this->assertSame($this->_uri->port, $port);
        $this->assertSame($this->_uri->path, explode('/', $path));
        $this->assertSame($this->_uri->query, $query);

        // npw export in full, then re-import and check again.
        // do this to make sure there are no translation errors.
        $spec = $this->_uri->fetch(true);
        $this->_uri->set($spec);
        // $assert->setLabel('Retranslation');
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
        // $assert->setLabel('full');
        $this->assertSame($this->_uri->fetch(true), $expect_full);

        // partial fetch
        // $assert->setLabel('part');
        $this->assertSame($this->_uri->fetch(), $expect_part);
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