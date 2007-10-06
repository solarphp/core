<?php

require_once dirname(__FILE__) . '/../SolarUnitTest.config.php';
require_once 'Solar/View.php';

class Solar_ViewTest extends PHPUnit_Framework_TestCase
{
    private $_view = null;
    
    public function setUp() 
    {
        Solar::start('config.inc.php');
        $this->_view = Solar::factory('Solar_View');
    }
    
    public function tearDown() 
    {
        Solar::stop();
        $this->_uri = null;
    }
    
    public function testCanInstantiateThroughFactory()
    {
        $object = Solar::factory('Solar_View');
        $this->assertTrue($object instanceof Solar_View);
    }
    
    public function test__set()
    {
        $this->_view->foo = 'bar';
        $this->assertTrue($this->_view->foo === 'bar');
    }
    
    public function testAssign_byNameAndValue()
    {
        $this->_view->assign('foo', 'bar');
        $this->assertTrue($this->_view->foo === 'bar');
    }
    
    public function testAssign_byArray()
    {
        $array = array('foo' => 'bar');
        $this->_view->assign($array);
        $this->assertTrue($this->_view->foo === 'bar');
    }
    
    public function testAssign_byObject()
    {
        $obj = new StdClass();
        $obj->foo = 'bar';
        $this->_view->assign($obj);
        $this->assertTrue($this->_view->foo === 'bar');
    }
    
    // loads and calls a helper class on-the-fly
    public function test__call()
    {
        $expect = 'NO_SUCH_LOCALE_KEY';
        $actual = $this->_view->getTextRaw($expect);
        $this->assertEquals($actual, $expect);
    }
    
    public function testSetHelperClass()
    {
        $this->_view->setHelperClass('Other_Helper_Foo');
        $actual = $this->_view->getHelperClass();
        $expect = array(
            0 => 'Other_Helper_Foo_',
            1 => 'Solar_View_Helper_',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testAddHelperClass()
    {
        $this->_view->addHelperClass('Other_Helper_Foo');
        $this->_view->addHelperClass('Other_Helper_Bar');
        $actual = $this->_view->getHelperClass();
        $expect = array (
            0 => 'Other_Helper_Bar_',
            1 => 'Other_Helper_Foo_',
            2 => 'Solar_View_Helper_',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testGetHelperClass()
    {
        $actual = $this->_view->getHelperClass();
        $expect = array (
            0 => 'Solar_View_Helper_',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testGetHelper()
    {
        // should reference the same object
        $a = $this->_view->getHelper('getTextRaw');
        $b = $this->_view->getHelper('getTextRaw');
        $this->assertSame($a, $b);
    }
    
    public function testNewHelper()
    {
        // should *not* reference the same object
        $a = $this->_view->newHelper('getTextRaw');
        $b = $this->_view->newHelper('getTextRaw');
        $this->assertNotSame($a, $b);
    }
    
    public function testEscape()
    {
        $string = "hello <there> i'm a \"quote\"";
        $expect = htmlspecialchars($string);
        $actual = $this->_view->escape($string);
        $this->assertSame($actual, $expect);
    }
    
    public function testSetTemplatePath()
    {
        $this->_view->setTemplatePath('path/foo/');
        $actual = $this->_view->getTemplatePath();
        $expect = array(
            0 => 'path/foo/',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testAddTemplatePath()
    {
        $this->_view->addTemplatePath('path/foo/');
        $this->_view->addTemplatePath('path/bar/');
        $this->_view->addTemplatePath('path/baz/');
        $actual = $this->_view->getTemplatePath();
        $expect = array(
            0 => 'path/baz/',
            1 => 'path/bar/',
            2 => 'path/foo/',
        );
        $this->assertSame($actual, $expect);
    }
    
    public function testGetTemplatePath()
    {
        $this->_view->addTemplatePath(dirname(__FILE__) . '/View/templates/');
        $actual = $this->_view->getTemplatePath();
        $expect = array(0 => dirname(__FILE__) . '/View/templates/');
        $this->assertSame($expect, $actual);
    }
    
    public function testDisplay()
    {
        $this->_view->addTemplatePath(dirname(__FILE__) . '/../support/Solar/View/templates/');
        $this->_view->foo = 'bar';
        ob_start();
        $this->_view->display('test.view.php');
        $actual = ob_get_clean();
        $expect = 'bar';
        $this->assertSame($expect, $actual);
    }
    
    public function testFetch()
    {
        $this->_view->addTemplatePath(dirname(__FILE__) . '/../support/Solar/View/templates/');
        $this->_view->foo = 'bar';
        $actual = $this->_view->fetch('test.view.php');
        $expect = 'bar';
        $this->assertSame($expect, $actual);
    }
    
    public function testTemplate()
    {
        $this->_view->addTemplatePath(dirname(__FILE__) . '/../support/Solar/View/templates/');
        $actual = $this->_view->template('test.view.php');
        $expect = dirname(__FILE__) . '/../support/Solar/View/templates/test.view.php';
        $this->assertSame($expect, $actual);
    }
}
