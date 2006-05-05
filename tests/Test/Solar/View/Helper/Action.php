<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_Action extends Test_Solar_View_Helper {
    
    public function testAction_hrefFromString()
    {
        $actual = $this->_view->action('/controller/action/id');
        $expect = '/index.php/controller/action/id';
        $this->assertSame($actual, $expect);
    }
    
    public function testAction_linkFromString()
    {
        // no translation key
        $actual = $this->_view->action('/controller/action/id', 'example');
        $expect = '<a href="/index.php/controller/action/id">example</a>';
        $this->assertSame($actual, $expect);
        
        // translation key
        $actual = $this->_view->action('/controller/action/id', 'ACTION_BROWSE');
        $expect = '<a href="/index.php/controller/action/id">Browse</a>';
        $this->assertSame($actual, $expect);
    }
    
    public function testAction_hrefFromUri()
    {
        $uri = Solar::factory('Solar_Uri_Action');
        $uri->setPath('/controller/action/id');
        $uri->setQuery('page=1');
        
        $actual = $this->_view->action($uri);
        $expect = '/index.php/controller/action/id?page=1';
        $this->assertSame($actual, $expect);
    }
    
    public function testAction_linkFromUri()
    {
        $uri = Solar::factory('Solar_Uri_Action');
        $uri->setPath('/controller/action/id');
        $uri->setQuery('page=1');
        
        // no translation key
        $actual = $this->_view->action($uri, 'example');
        $expect = '<a href="/index.php/controller/action/id?page=1">example</a>';
        $this->assertSame($actual, $expect);
        
        // translation key
        $actual = $this->_view->action($uri, 'ACTION_BROWSE');
        $expect = '<a href="/index.php/controller/action/id?page=1">Browse</a>';
        $this->assertSame($actual, $expect);
    }
}
?>