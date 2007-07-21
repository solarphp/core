<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_AnchorImageTest extends Solar_View_HelperTestCase {
    
    protected $_request = null;
    
    public function setup()
    {
        Solar::start(false); // to get the $locale object
        parent::setup();
        
        // when running from the command line, these elements are empty.
        // add them so that web-like testing can occur.
        $this->_request->server['HTTP_HOST']    = 'example.com';
        $this->_request->server['SCRIPT_NAME']  = '/path/to/index.php';
        $this->_request->server['PATH_INFO']    = '/appname/action';
        $this->_request->server['QUERY_STRING'] = '';
        $this->_request->server['REQUEST_URI']  = $this->_request->server['SCRIPT_NAME']
                                                . $this->_request->server['PATH_INFO']
                                                . '?'
                                                . $this->_request->server['QUERY_STRING'];
        
        // emulate GET vars from the URI and inject to $this->_request
        parse_str($this->_request->server['QUERY_STRING'], $this->_request->get);
    }
    
    public function testAnchorImage_linkFromString()
    {
        $uri = '/path/to/script.php';
        $src = '/images/example.gif';
        $a_attribs = array("class" => "foo");
        $img_attribs = array("class" => "bar");
        
        // no attribs
        $actual = $this->_view->anchorImage($uri, $src);
        $expect = '<a href="/path/to/script.php">'
                . '<img src="/public/images/example.gif" alt="example.gif" /></a>';
        $this->assertSame($actual, $expect);
        
        // with attribs
        $actual = $this->_view->anchorImage($uri, $src, $a_attribs, $img_attribs);
        $expect = '<a href="/path/to/script.php" class="foo">'
                . '<img src="/public/images/example.gif" class="bar" alt="example.gif" /></a>';
        $this->assertSame($actual, $expect);
    }
    
    public function testAnchorImage_linkFromUri()
    {
        $uri = Solar::factory('Solar_Uri');
        
        $uri->setPath('/path/to/script.php');
        $src = '/images/example.gif';
        $a_attribs = array("class" => "foo");
        $img_attribs = array("class" => "bar");
        
        // no attribs
        $actual = $this->_view->anchorImage($uri, $src);
        $expect = '<a href="http://example.com/path/to/script.php">'
                . '<img src="/public/images/example.gif" alt="example.gif" /></a>';
        $this->assertSame($actual, $expect);
        
        // with attribs
        $actual = $this->_view->anchorImage($uri, $src, $a_attribs, $img_attribs);
        $expect = '<a href="http://example.com/path/to/script.php" class="foo">'
                . '<img src="/public/images/example.gif" class="bar" alt="example.gif" /></a>';
        $this->assertSame($actual, $expect);
    }
}