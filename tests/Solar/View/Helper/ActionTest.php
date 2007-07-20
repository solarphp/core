<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_ActionTest extends Solar_View_HelperTestCase
{
    public function setup()
    {
        Solar::start(false); // to get the $locale object
        parent::setup();
    }
    
    public function testAction_hrefFromString()
    {
        $actual = $this->_view->action('/controller/action/id');
        $expect = '/index.php/controller/action/id';
        $this->assertSame($expect, $actual);
    }
    
    public function testAction_linkFromString()
    {
        // no translation key
        $actual = $this->_view->action('/controller/action/id', 'example');
        $expect = '<a href="/index.php/controller/action/id">example</a>';
        $this->assertSame($expect, $actual);
        
        // translation key
        $actual = $this->_view->action('/controller/action/id', 'ACTION_BROWSE');
        $expect = '<a href="/index.php/controller/action/id">Browse</a>';
        $this->assertSame($expect, $actual);
    }
    
    public function testAction_linkFromStringWithAttribs()
    {
        $attribs = array('foo' => 'bar');
        
        // no translation key
        $actual = $this->_view->action('/controller/action/id', 'example', $attribs);
        $expect = '<a href="/index.php/controller/action/id" foo="bar">example</a>';
        $this->assertSame($expect, $actual);
        
        // translation key
        $actual = $this->_view->action('/controller/action/id', 'ACTION_BROWSE', $attribs);
        $expect = '<a href="/index.php/controller/action/id" foo="bar">Browse</a>';
        $this->assertSame($expect, $actual);
    }
    
    public function testAction_hrefFromUri()
    {
        $uri = Solar::factory('Solar_Uri_Action');
        $uri->setPath('/controller/action/id');
        $uri->setQuery('page=1');
        
        $actual = $this->_view->action($uri);
        $expect = '/index.php/controller/action/id?page=1';
        $this->assertSame($expect, $actual);
    }
    
    public function testAction_linkFromUri()
    {
        $uri = Solar::factory('Solar_Uri_Action');
        $uri->setPath('/controller/action/id');
        $uri->setQuery('page=1');
        
        // no translation key
        $actual = $this->_view->action($uri, 'example');
        $expect = '<a href="/index.php/controller/action/id?page=1">example</a>';
        $this->assertSame($expect, $actual);
        
        // translation key
        $actual = $this->_view->action($uri, 'ACTION_BROWSE');
        $expect = '<a href="/index.php/controller/action/id?page=1">Browse</a>';
        $this->assertSame($expect, $actual);
    }
}
