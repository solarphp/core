<?php
require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

/**
 * 
 * @todo Test helper stack for view
 * 
 * @todo Test template stack for view
 * 
 * @todo Test helper stack for layout
 * 
 * @todo Test template stack for layout
 * 
 * @todo Test hooks: _setup, _preRun, _preAction, _postAction, _postRun,
 * _preRender, _postRender ... do so by setting vars to say we hit them?
 * 
 * @todo Test _forward ... do so by setting var to say we hit it?
 * 
 * @todo Test _redirect (just inspect headers?)
 * 
 */
class Solar_Controller_PageTest extends PHPUnit_Framework_TestCase
{
    
    protected $_page;
    
    protected $_request;
    
    public function setup()
    {
        $this->markTestSkipped('rewrite this test');
        parent::setup();
        
        Solar::start('config.inc.php');
        
        // forcibly reload the request environment
        $this->_request = Solar::factory('Solar_Request');
        $this->_request->load(true);
        
        // set up the example page controller object
        $this->_page = Solar::factory('Solar_Controller_PageController');
    }
    
    public function teardown()
    {
        Solar::stop();
    }
    
    public function test__construct()
    {
        $this->assertType('Solar_Controller_Page', $this->_page);
    }
    
    public function test__set()
    {
        try {
            $this->_page->foo = 'baz';
        } catch (Exception $e) {
            // should *not* have thrown an exception
            $this->fail('shoud not have thrown exception: ' . $e->__toString());
        }
        
        try {
            $this->_page->zim = 'dib';
            $this->fail('should have thrown exception on non-existing var');
        } catch (Solar_Controller_Page_Exception_PropertyNotDefined $e) {
            // we expect this, do nothing
        }
        
        // done, we need at least one assertion to pass
        $this->assertSame($this->_page->foo, 'baz');
    }
    
    public function test__get()
    {
        $actual = $this->_page->foo;
        $this->assertSame($this->_page->foo, 'bar');
        
        try {
            $actual = $this->_page->noSuchVar;
            $this->fail('should have thrown exception on no-existing var');
        } catch (Solar_Controller_Page_Exception_PropertyNotDefined $e) {
            // we expect this, do nothing
        }
        
    }
    
    public function testFetch()
    {
        $actual = $this->_page->fetch('foo');
        $expect = 'foo = bar';
        $this->assertSame($actual, $expect);
    }
    
    public function testDisplay()
    {
        ob_start();
        $this->_page->display('foo');
        $actual = ob_get_clean();
        $expect = 'foo = bar';
        $this->assertSame($actual, $expect);
    }
    
    public function test_hooks()
    {
        $this->_page->fetch();
        $expect = array(
            '_setup'      => 1,
            '_preRun'     => 1,
            '_preAction'  => 1,
            '_postAction' => 1,
            '_postRun'    => 1,
            '_preRender'  => 1,
            '_postRender' => 1,
        );
        $this->assertSame($this->_page->hooks, $expect);
        
        // fetch again; setup should not trigger this time.
        $this->_page->fetch();
        $expect = array(
            '_setup'      => 1,
            '_preRun'     => 2,
            '_preAction'  => 2,
            '_postAction' => 2,
            '_postRun'    => 2,
            '_preRender'  => 2,
            '_postRender' => 2,
        );
        $this->assertSame($this->_page->hooks, $expect);
        
        // fetch **again** with an action that forwards internally;
        // the run hooks should hit once, but the action hooks should 
        // hit twice (once for the orginal method, once for the 
        // forwarded method).
        $this->_page->fetch('test-forward');
        $expect = array(
            '_setup'      => 1,
            '_preRun'     => 3,
            '_preAction'  => 4,
            '_postAction' => 4,
            '_postRun'    => 3,
            '_preRender'  => 3,
            '_postRender' => 3,
        );
        $this->assertSame($this->_page->hooks, $expect);
    }
    
    /**
     * @todo refactor this test so it doesn't concern itself with the internal
     * state of the object so much as the way the internal state adjusts how
     * the object reacts.
     */
    public function testFetch_stringSpecWithAction()
    {
        $this->markTestSkipped('brittle test');
        $spec = "foo/bar/baz";
        $this->_page->fetch($spec);
        
        // check the action
        $expect = 'foo';
        $this->assertProperty($this->_page, '_action', 'same', $expect);
        
        // check the pathinfo
        $expect = array('bar', 'baz');
        $this->assertProperty($this->_page, '_info', 'same', $expect);
    }
    
    /**
     * @todo refactor this test so it doesn't concern itself with the internal
     * state of the object so much as the way the internal state adjusts how
     * the object reacts.
     */
    public function testFetch_stringSpecWithoutAction()
    {
        $this->markTestSkipped('brittle test');
        $spec = "bar/baz";
        $this->_page->fetch($spec);
        
        // check the action
        $expect = 'foo';
        $this->assertProperty($this->_page, '_action', 'same', $expect);
        
        // check the pathinfo
        $expect = array('bar', 'baz');
        $this->assertProperty($this->_page, '_info', 'same', $expect);
    }
    
    /**
     * @todo refactor this test so it doesn't concern itself with the internal
     * state of the object so much as the way the internal state adjusts how
     * the object reacts.
     */
    public function testFetch_uriSpecWithAction()
    {
        $this->markTestSkipped('brittle test');
        $spec = Solar::factory('Solar_Uri_Action');
        $spec->setPath('/foo/bar/baz');
        $this->_page->fetch($spec);
        
        // check the action
        $expect = 'foo';
        $this->assertProperty($this->_page, '_action', 'same', $expect);
        
        // check the pathinfo
        $expect = array('bar', 'baz');
        $this->assertProperty($this->_page, '_info', 'same', $expect);
    }
    
    /**
     * @todo refactor this test so it doesn't concern itself with the internal
     * state of the object so much as the way the internal state adjusts how
     * the object reacts.
     */
    public function testFetch_uriSpecWithoutAction()
    {
        $this->markTestSkipped('brittle test');
        $spec = Solar::factory('Solar_Uri_Action');
        $spec->setPath('bar/baz');
        $this->_page->fetch($spec);
        
        // check the action
        $expect = 'foo';
        $this->assertProperty($this->_page, '_action', 'same', $expect);
        
        // check the pathinfo
        $expect = array('bar', 'baz');
        $this->assertProperty($this->_page, '_info', 'same', $expect);
    }
    
    /**
     * @todo unless there's a specific use case, this is scope creep.  Only the
     * last two cases seem legitimate.
     */
    public function testFetch_niceActionNames()
    { 
        $expect = "found actionBumpyCase";
        
        $actual = $this->_page->fetch("bumpy-case")->body;
        $this->assertSame($actual, $expect);
        
        $actual = $this->_page->fetch("bumpy_case")->body;
        $this->assertSame($actual, $expect);
        
        $actual = $this->_page->fetch("bumpyCase")->body;
        $this->assertSame($actual, $expect);
        
        $actual = $this->_page->fetch("BumpyCase")->body;
        $this->assertSame($actual, $expect);
    }
    
    public function testFetch_noRelatedView()
    {
        try {
            $this->_page->fetch("no-related-view");
            $this->fail('should have thrown TemplateNotFound exception');
        } catch (Solar_View_Exception_TemplateNotFound $e) {
            // this is expected
        }
        
        // need an assertion to pass
        $this->assertTrue(true);
    }
    
    /**
     * @todo should setActionDefault() be required here?  It seems if you 
     * make a request to fetch a specific action, it should not be routed to
     * the default if it does not exist.
     */
    public function testFetch_actionNotFound()
    {
        $this->_page->setActionDefault('notFound');
        try {
            $result = $this->_page->fetch('notFound');
            $this->fail('should have thrown an ActionNotFound exception');
        } catch (Solar_Controller_Page_Exception_ActionNotFound $e) {
            // do nothing, we expected this
        }
        
        // need at least one assertion to pass
        $this->assertTrue(true);
    }
}
?>