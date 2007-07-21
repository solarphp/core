<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_AnchorTest extends Solar_View_HelperTestCase {
    
    protected $_request = null;
    
    public function setup()
    {
        Solar::start(false); // to get the $locale object
        parent::setup();
    }
    
    public function testAnchor_hrefFromString()
    {
        $actual = $this->_view->anchor('/path/to/script.php');
        $expect = '/path/to/script.php';
        $this->assertSame($actual, $expect);
        
        // attribs should not return
        $actual = $this->_view->anchor(
            '/path/to/script.php', null, array('foo' => 'bar')
        );
        $expect = '/path/to/script.php';
        $this->assertSame($actual, $expect);
    }
    
    public function testAnchor_linkFromString()
    {
        // no translation key
        $actual = $this->_view->anchor(
            '/path/to/script.php', 'example'
        );
        $expect = '<a href="/path/to/script.php">example</a>';
        $this->assertSame($actual, $expect);
        
        // translation key
        $actual = $this->_view->anchor(
            '/path/to/script.php', 'ACTION_BROWSE'
        );
        $expect = '<a href="/path/to/script.php">Browse</a>';
        $this->assertSame($actual, $expect);
        
        // with attribs
        $actual = $this->_view->anchor(
            '/path/to/script.php',
            'ACTION_BROWSE',
            array('foo' => 'bar')
        );
        $expect = '<a href="/path/to/script.php" foo="bar">Browse</a>';
        $this->assertSame($actual, $expect);
        
    }
    
    public function testAnchor_hrefFromUri()
    {
        $uri = Solar::factory('Solar_Uri');
        
        $uri->setPath('/path/to/script.php');
        $uri->setQuery('page=1');
        
        $actual = $this->_view->anchor($uri);
        $expect = '/path/to/script.php?page=1';
        $this->assertSame($actual, $expect);
    }
    
    public function testAnchor_linkFromUri()
    {
        $uri = Solar::factory('Solar_Uri');
        
        $uri->setPath('/path/to/script.php');
        $uri->setQuery('page=1');
        
        // no translation key
        $actual = $this->_view->anchor($uri, 'example');
        $expect = '<a href="/path/to/script.php?page=1">example</a>';
        $this->assertSame($actual, $expect);
        
        // translation key
        $actual = $this->_view->anchor($uri, 'ACTION_BROWSE');
        $expect = '<a href="/path/to/script.php?page=1">Browse</a>';
        $this->assertSame($actual, $expect);
        
        // with attribs
        $actual = $this->_view->anchor(
            $uri,
            'ACTION_BROWSE',
            array('foo' => 'bar')
        );
        $expect = '<a href="/path/to/script.php?page=1" foo="bar">Browse</a>';
        $this->assertSame($actual, $expect);
    }
}