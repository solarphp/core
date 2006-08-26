<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_PublicHref extends Test_Solar_View_Helper {
    
    public function testPublicHref_fromString()
    {
        $actual = $this->_view->publicHref('/path/to/file');
        $expect = '/public/path/to/file';
        $this->assertSame($actual, $expect);
    }
    
    public function testPublicHref_fromUri()
    {
        $uri = Solar::factory('Solar_Uri_Public');
        $uri->setPath('/path/to/file');
        $uri->setQuery('page=1');
        
        $actual = $this->_view->publicHref($uri);
        $expect = '/public/path/to/file?page=1';
        $this->assertSame($actual, $expect);
    }
    
    public function testPublicHref_raw()
    {
        $actual = $this->_view->publicHref('/path/to/<file>');
        $expect = '/public/path/to/&lt;file&gt;';
        $this->assertSame($actual, $expect);
        
        $actual = $this->_view->publicHref('/path/to/<file>', true);
        $expect = '/public/path/to/<file>';
        $this->assertSame($actual, $expect);
    }
}
?>