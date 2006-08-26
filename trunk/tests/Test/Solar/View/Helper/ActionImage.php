<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_ActionImage extends Test_Solar_View_Helper {
    
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
?>