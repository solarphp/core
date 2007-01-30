<?php

require_once realpath(dirname(__FILE__) . '/../Uri.php');

class Test_Solar_Uri_Public extends Test_Solar_Uri {
    
    protected $_class = 'Solar_Uri_Public';
    
    public function testFetch()
    {
        $this->_uri->set('Solar/styles/default.css');

        // partial fetch
        $this->assertSame($this->_uri->fetch(), '/public/Solar/styles/default.css');

        // full fetch
        $this->assertSame($this->_uri->fetch(true), 'http://example.com/public/Solar/styles/default.css');
        
    }
    
    public function testQuick()
    {
        // partial
        $expect = 'Solar/styles/default.css';
        $actual = $this->_uri->quick($expect);
        $this->assertSame($actual, "/public/$expect");

        // full
        $expect = 'http://example.com/public/Solar/styles/default.css';
        $actual = $this->_uri->quick($expect, true);
        $this->assertSame($actual, $expect);
    }
}
?>