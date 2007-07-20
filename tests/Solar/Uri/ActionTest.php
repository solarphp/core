<?php

require_once dirname(__FILE__) . '/../UriTest.php';

class Solar_Uri_ActionTest extends Solar_UriTest {
    
    protected $_class = 'Solar_Uri_Action';
    
    public function testFetch()
    {
        $this->_uri->set('controller/action/id/?page=1');

        // partial fetch
        $this->assertSame($this->_uri->fetch(), '/controller/action/id?page=1');

        // full fetch
        $this->assertSame($this->_uri->fetch(true), 'http://example.com/controller/action/id?page=1');
    }
    
    public function testQuick()
    {
        // partial
        $actual = $this->_uri->quick('/controller/action/id?foo=bar');
        $expect = '/controller/action/id?foo=bar';
        $this->assertSame($actual, $expect);
        
        // semi-partial
        $expect = '/controller/action/id?foo=bar';
        $actual = $this->_uri->quick($expect);
        $this->assertSame($actual, $expect);

        // full
        $expect = 'http://example.com/controller/action?foo=bar';
        $actual = $this->_uri->quick($expect, true);
        $this->assertSame($actual, $expect);
    }
}
