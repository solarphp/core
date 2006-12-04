<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_AnchorImage extends Test_Solar_View_Helper {
    
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
        $expect = '<a href="/path/to/script.php">'
                . '<img src="/public/images/example.gif" alt="example.gif" /></a>';
        $this->assertSame($actual, $expect);
        
        // with attribs
        $actual = $this->_view->anchorImage($uri, $src, $a_attribs, $img_attribs);
        $expect = '<a href="/path/to/script.php" class="foo">'
                . '<img src="/public/images/example.gif" class="bar" alt="example.gif" /></a>';
        $this->assertSame($actual, $expect);
    }
}
?>