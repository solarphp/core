<?php

require_once dirname(__FILE__) . '/../../../SolarUnitTest.config.php';
class Solar_View_Helper_JsTest extends PHPUnit_Framework_TestCase
{
    
    protected $_view;
    
    public function setup()
    {
        parent::setup();
        $this->_view = Solar::factory('Solar_View');
    }
    
    public function teardown()
    {
        $this->_view = null;
    }
    
    public function test__construct()
    {
        $helper = $this->_view->getHelper('js');
        $this->assertType('Solar_View_Helper_Js', $helper);
    }
    
    public function testAddFile()
    {
        $this->markTestSkipped('brittle test');
        $helper = $this->_view->js()->addFile('foo.js')
                                    ->addFile('bar.js');
        
        $expect = array(
            0 => 'foo.js',
            1 => 'bar.js'
        );
        $this->assertProperty($helper, 'files', 'same', $expect);
        
        // check for fluency
        $this->assertInstance($helper, 'Solar_View_Helper_Js');
        $this->assertSame($helper, $this->_view->getHelper('js'));
    }
    
    public function testFetch_Files_Inline()
    {
        
        // one second highlight of #test
        $this->_view->jsScriptaculous()->effect->highlight('#test', array('duration' => 1));
        
        $helper = $this->_view->js()->addFile('foo.js')
                                    ->addFile('bar.js');
        
        $actual = $helper->fetchFiles();
        $actual .= $helper->fetchInline();
        
        $expect = '    <script src="/public/Solar/scripts/prototype/prototype.js" type="text/javascript"></script>'."\n";
        $expect .= '    <script src="/public/Solar/scripts/scriptaculous/effects.js" type="text/javascript"></script>'."\n";
        $expect .= '    <script src="/public/foo.js" type="text/javascript"></script>'."\n";
        $expect .= '    <script src="/public/bar.js" type="text/javascript"></script>'."\n";
        $expect .= '<script type="text/javascript">'."\n";
        $expect .= "//<![CDATA[\n";
        $expect .= "Event.observe(window,'load',function() {\n";
        $expect .= "    \$\$('#test').each(function(el){\n";
        $expect .= "        new Effect.Highlight(el, {\"duration\":1});\n";
        $expect .= "    });\n";
        $expect .= "});\n";
        $expect .= "//]]>\n";
        $expect .= "</script>\n";
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testReset()
    {
        $this->markTestSkipped('brittle test');
        $helper = $this->_view->js()->addFile('foo.js')
                                    ->addFile('bar.js');
        
        $expect = array(
            0 => 'foo.js',
            1 => 'bar.js'
        );
        $this->assertProperty($helper, 'files', 'same', $expect);
        
        $helper = $this->_view->js()->reset();
        
        $expect = array();
        $this->assertProperty($helper, 'files', 'same', $expect);
        $this->assertProperty($helper, 'scripts', 'same', $expect);
        $this->assertProperty($helper, 'selectors', 'same', $expect);
        
        // check for fluency
        $this->assertInstance($helper, 'Solar_View_Helper_Js');
        $this->assertSame($helper, $this->_view->getHelper('js'));
    }
    
    public function testAddFile_Array()
    {
        $this->markTestSkipped('brittle test');
        
        $files = array('foo.js', 'bar.js');
        $helper = $this->_view->js()->addFile($files);
        $expect = array(
            0 => 'foo.js',
            1 => 'bar.js'
        );
        $this->assertProperty($helper, 'files', 'same', $expect);
        
        // check for fluency
        $this->assertInstance($helper, 'Solar_View_Helper_Js');
        $this->assertSame($helper, $this->_view->getHelper('js'));
    }

}

