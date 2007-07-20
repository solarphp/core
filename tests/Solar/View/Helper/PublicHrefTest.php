<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_PublicHrefTest extends Solar_View_HelperTestCase {
    
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
        // should escape
        $actual = $this->_view->publicHref('/path/to/<file>');
        $expect = '/public/path/to/&lt;file&gt;';
        $this->assertSame($actual, $expect);
        
        // should not escape
        $actual = $this->_view->publicHref('/path/to/<file>', true);
        $expect = '/public/path/to/<file>';
        $this->assertSame($actual, $expect);
    }
}
