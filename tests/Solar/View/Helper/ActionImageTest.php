<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_ActionImageTest extends Solar_View_HelperTestCase {
    
    public function testActionImage_linkFromString()
    {
        $src = '/images/example.gif';
        
        $actual = $this->_view->actionImage('/controller/action/id', $src);
        $expect = '<a href="/index.php/controller/action/id">'
                . '<img src="/public/images/example.gif" alt="example.gif" /></a>';
                
        $this->assertSame($actual, $expect);
    }
    
    public function testActionImage_linkFromUri()
    {
        $uri = Solar::factory('Solar_Uri_Action');
        $uri->setPath('/controller/action/id');
        $uri->setQuery('page=1');
        
        $src = '/images/example.gif';
        
        $actual = $this->_view->actionImage($uri, $src);
        $expect = '<a href="/index.php/controller/action/id?page=1">'
                . '<img src="/public/images/example.gif" alt="example.gif" /></a>';
                
        $this->assertSame($actual, $expect);
    }
}
