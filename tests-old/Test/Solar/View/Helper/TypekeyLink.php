<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_TypekeyLink extends Test_Solar_View_Helper {
    
    protected $_request;
    
    public function setup()
    {
        parent::setup();
        
        // forcibly reset the request environment
        $this->_request = Solar::factory('Solar_Request');
        $this->_request->load(true);
        
        // when running from the command line, these elements are empty.
        // add them so that web-like testing can occur.
        $this->_request->server['HTTP_HOST']  = 'example.com';
        $this->_request->server['SCRIPT_NAME']  = '/index.php';
        $this->_request->server['PATH_INFO']    = '/control/action';
        $this->_request->server['QUERY_STRING'] = 'foo=bar&baz=dib&submit=zim';
        $this->_request->server['REQUEST_URI']  = $this->_request->server['SCRIPT_NAME']
                                                . $this->_request->server['PATH_INFO']
                                                . '?'
                                                . $this->_request->server['QUERY_STRING'];

        // emulate GET vars from the URI
        parse_str($this->_request->server['QUERY_STRING'], $this->_request->get);
    }
    
    public function testTypekeyLink()
    {
        // note that the 'submit' key should have been removed
        $expect = '<a href="https://www.typekey.com:443/t/typekey/login?need_email=0&amp;_return=http%3A%2F%2Fexample.com%2Findex.php%2Fcontrol%2Faction%3Ffoo%3Dbar%26baz%3Ddib">Sign In</a>';
        
        $actual = $this->_view->typekeyLink('Sign In');
        $this->assertSame($actual, $expect);
    }
}
?>