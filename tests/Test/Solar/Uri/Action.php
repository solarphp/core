<?php

require_once realpath(dirname(__FILE__) . '/../Uri.php');

class Test_Solar_Uri_Action extends Test_Solar_Uri {
    
    protected $_class = 'Solar_Uri_Action';
    
    public function testFetch()
    {
        $this->_uri->set('controller/action/id/?page=1');

        // partial fetch
        $this->assertSame($this->_uri->fetch(), '/index.php/controller/action/id?page=1');

        // full fetch
        $this->assertSame($this->_uri->fetch(true), 'http://example.com/index.php/controller/action/id?page=1');
    }
    
    public function testQuick()
    {
        // partial
        $actual = $this->_uri->quick('/controller/action/id?foo=bar');
        $expect = '/index.php/controller/action/id?foo=bar';
        $this->assertSame($actual, $expect);
        
        // semi-partial
        $expect = '/index.php/controller/action/id?foo=bar';
        $actual = $this->_uri->quick($expect);
        $this->assertSame($actual, $expect);

        // full
        $expect = 'http://example.com/index.php/controller/action?foo=bar';
        $actual = $this->_uri->quick($expect, true);
        $this->assertSame($actual, $expect);
    }
}
?>