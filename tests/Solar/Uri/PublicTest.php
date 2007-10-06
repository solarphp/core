<?php

require_once dirname(__FILE__) . '/../UriTest.php';

class Solar_Uri_PublicTest extends Solar_UriTest {
    
    protected $_class = 'Solar_Uri_Public';
    
    public function testFetch()
    {
        $this->_uri->set('Solar/styles/default.css');
        
        // partial fetch
        $this->assertSame($this->_uri->get(), '/Solar/styles/default.css');
        
        // full fetch
        $this->assertSame($this->_uri->get(true), 'http://example.com/Solar/styles/default.css');
        
    }
    
    public function testQuick()
    {
        // partial
        $expect = 'Solar/styles/default.css';
        $actual = $this->_uri->quick($expect);
        $this->assertSame($actual, "/$expect");
        
        // full
        $expect = 'http://example.com/Solar/styles/default.css';
        $actual = $this->_uri->quick($expect, true);
        $this->assertSame($actual, $expect);
    }
}
