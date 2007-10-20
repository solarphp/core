<?php
/**
 * 
 * @todo test what happens when you add an element type that doesn't exist
 * 
 */
require_once dirname(__FILE__) . '/../../../SolarUnitTest.config.php';

class Solar_View_Helper_FormTest extends Solar_View_HelperTestCase
{
    
    protected $_view;
    
    protected $_server;
    
    protected $_get;
    
    protected $_request;
    
    public function setup()
    {
        Solar::start(false); // to get the $locale object
        parent::setup();
        
        // when running from the command line, these elements are empty.
        // add them so that web-like testing can occur.
        $this->_request->server['HTTP_HOST']    = 'example.com';
        $this->_request->server['SCRIPT_NAME']  = '/path/to/index.php';
        $this->_request->server['PATH_INFO']    = '/appname/action';
        $this->_request->server['QUERY_STRING'] = 'foo=bar&baz=dib';
        $this->_request->server['REQUEST_URI']  = $this->_request->server['SCRIPT_NAME']
                                                . $this->_request->server['PATH_INFO']
                                                . '?'
                                                . $this->_request->server['QUERY_STRING'];
        
        // emulate GET vars from the URI
        parse_str($this->_request->server['QUERY_STRING'], $this->_request->get);
        
        // set up a "master" view object
        $this->_view = Solar::factory('Solar_View');
    }
    
    public function teardown()
    {
        $this->_view = null;
    }
    
    public function test__construct()
    {
        $helper = $this->_view->getHelper('form');
        $this->assertType('Solar_View_Helper_Form', $helper);
    }
    
    // functionally similar to addElement()
    public function test__call()
    {
        $this->markTestSkipped('brittle test');
        // add a button
        $info = array(
            'name' => 'test',
            'value' => 'push me',
        );
        $helper = $this->_view->form()->button($info);
        
        // make sure the element was added properly to the stack
        $expect = array(
            0 => array(
                0 => 'element',
                1 => array(
                    'type' => 'button',
                    'name' => 'test',
                    'value' => 'push me',
                    'label' => '',
                    'descr' => '',
                    'status' => null,
                    'attribs' => array(
                        'id' => 'test',
                        'class' => 'input-button test',
                    ),
                    'options' => array(),
                    'disable' => false,
                    'require' => false,
                    'invalid' => array(),
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
        
        // check for fluency
        $this->assertType('Solar_View_Helper_Form', $helper);
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    // get the form object
    public function testForm_default()
    {
        $helper = $this->_view->form();
        
        // check for fluency
        $this->assertType('Solar_View_Helper_Form', $helper);
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    // set attribs
    public function testForm_array()
    {
        $this->markTestSkipped('brittle test');
        $attribs = array('foo' => 'bar');
        $helper = $this->_view->form($attribs);
        
        $expect = array(
            'action'  => $this->_request->server['REQUEST_URI'],
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
            'foo'     => 'bar',
        );
        $this->assertProperty($helper, '_attribs', 'same', $expect);
        
        // check for fluency
        $this->assertType('Solar_View_Helper_Form', $helper);
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    // build and return entire form
    public function testForm_solarFormObject()
    {
        $form = Solar::factory('Solar_Form');
        
        $form->attribs['foo'] = 'bar';
        
        $form->setElement(
            'baz',
            array(
                'type' => 'text',
                'value' => 'dib',
                'attribs' => array('size' => 10),
            )
        );
        
        $actual = $this->_view->form($form);
        $expect = <<<EXPECT
<form action="/path/to/index.php/appname/action?foo=bar&amp;baz=dib" method="post" enctype="multipart/form-data" foo="bar">
        
        <dl>
            <dt><label for="baz"></label></dt>
            <dd><input type="text" name="baz" value="dib" size="10" id="baz" class="input-text baz" /></dd>
        
        </dl>
</form>
EXPECT;
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testSetAttrib()
    {
        $this->markTestSkipped('brittle test');
        $expect = array(
            'action'  => $this->_request->server['REQUEST_URI'],
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
            'foo'     => 'bar',
        );
        
        $helper = $this->_view->getHelper('form');
        $helper->setAttrib('foo', 'bar');
        $this->assertProperty($helper, '_attribs', 'same', $expect);
    }
    
    public function testAddFeedback()
    {
        $this->markTestSkipped('brittle test');
        // add a button
        $info = array(
            'name' => 'test',
            'value' => 'push me',
        );
        $helper = $this->_view->form()->addFeedback('This is a feedback message.')
                                      ->addFeedback('And so is this.');
              
        // make sure the element was added properly to the stack
        $expect = array(
            0 => 'This is a feedback message.',
            1 => 'And so is this.',
        );
        $this->assertProperty($helper, '_feedback', 'same', $expect);
        
        // check for fluency
        $this->assertType('Solar_View_Helper_Form', $helper);
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    public function testAddElement()
    {
        $this->markTestSkipped('brittle test');
        $info = array(
            'type' => 'button',
            'name' => 'test',
            'value' => 'push me',
        );
        
        $helper = $this->_view->form()->addElement($info);
        
        // make sure the element was added properly to the stack
        $expect = array(
            0 => array(
                0 => 'element',
                1 => array(
                    'type' => 'button',
                    'name' => 'test',
                    'value' => 'push me',
                    'label' => '',
                    'descr' => '',
                    'status' => null,
                    'attribs' => array(
                        'id' => 'test',
                        'class' => 'input-button test',
                    ),
                    'options' => array(),
                    'disable' => false,
                    'require' => false,
                    'invalid' => array(),
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
        
        // check for fluency
        $this->assertType('Solar_View_Helper_Form', $helper);
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    public function testSetStatus()
    {
        $this->markTestSkipped('brittle test');
        $helper = $this->_view->getHelper('form');
        
        $helper->setStatus(true);
        $this->assertProperty($helper, '_status', 'true');
        
        $helper->setStatus(false);
        $this->assertProperty($helper, '_status', 'false');
        
        $helper->setStatus(null);
        $this->assertProperty($helper, '_status', 'null');
    }
    
    public function testGetStatus()
    {
        $helper = $this->_view->getHelper('form');
        
        $helper->setStatus(true);
        $this->assertTrue($helper->getStatus());
        
        $helper->setStatus(false);
        $this->assertFalse($helper->getStatus());
        
        $helper->setStatus(null);
        $this->assertNull($helper->getStatus());
    }
    
    public function testAuto_solarFormObject()
    {
        $this->markTestSkipped('brittle test');
        
        $form = Solar::factory('Solar_Form');
        
        $form->setElement(
            'baz',
            array(
                'type' => 'text',
                'value' => 'dib',
            )
        );
        
        $helper = $this->_view->form();
        $helper->auto($form);
        
        // make sure the element was added properly to the stack
        $expect = array(
            0 => array(
                0 => 'element',
                1 => array(
                    'type' => 'text',
                    'name' => 'baz',
                    'value' => 'dib',
                    'label' => '',
                    'descr' => '',
                    'status' => null,
                    'attribs' => array(
                        'id' => 'baz',
                        'class' => 'input-text baz',
                    ),
                    'options' => array(),
                    'disable' => false,
                    'require' => false,
                    'invalid' => array(),
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
    }
    
    public function testAuto_array()
    {
        $this->markTestSkipped('brittle test');
        $elements = array(
            array(
                'name'  => 'baz',
                'type'  => 'text',
                'value' => 'dib',
            ),
        );
        
        $helper = $this->_view->form();
        $helper->auto($elements);
        
        // make sure the element was added properly to the stack
        $expect = array(
            0 => array(
                0 => 'element',
                1 => array(
                    'type' => 'text',
                    'name' => 'baz',
                    'value' => 'dib',
                    'label' => '',
                    'descr' => '',
                    'status' => null,
                    'attribs' => array(
                        'id' => 'baz',
                        'class' => 'input-text baz',
                    ),
                    'options' => array(),
                    'disable' => false,
                    'require' => false,
                    'invalid' => array(),
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
    }
    
    public function testBeginGroup()
    {
        $this->markTestSkipped('brittle test');
        $helper = $this->_view->getHelper('form');
        $helper->beginGroup();
        
        // make sure the group-begin was added properly to the stack
        $expect = array(
            0 => array(
                0 => 'group',
                1 => array(
                    0 => true,
                    1 => null,
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
        
        // check for fluency
        $this->assertType('Solar_View_Helper_Form', $helper);
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    public function testEndGroup()
    {
        $this->markTestSkipped('brittle test');
        $helper = $this->_view->getHelper('form');
        $helper->beginGroup();
        $helper->endGroup();
        
        // make sure the group-begin was added properly to the stack
        $expect = array(
            0 => array(
                0 => 'group',
                1 => array(
                    0 => true,
                    1 => null,
                ),
            ),
            1 => array(
                0 => 'group',
                1 => array(
                    0 => false,
                    1 => null,
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
        
        // check for fluency
        $this->assertType('Solar_View_Helper_Form', $helper);
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    public function testBeginFieldset()
    {
        $this->markTestSkipped('brittle test');
        $helper = $this->_view->getHelper('form');
        $helper->beginFieldset('legend');
        
        // make sure the group-begin was added properly to the stack
        $expect = array(
            0 => array(
                0 => 'fieldset',
                1 => array(
                    0 => true,
                    1 => 'legend',
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
        
        // check for fluency
        $this->assertType('Solar_View_Helper_Form', $helper);
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    public function testEndFieldset()
    {
        $this->markTestSkipped('brittle test');
        $helper = $this->_view->getHelper('form');
        $helper->beginFieldset('legend');
        $helper->endFieldset();
        
        // make sure the group-begin was added properly to the stack
        $expect = array(
            0 => array(
                0 => 'fieldset',
                1 => array(
                    0 => true,
                    1 => 'legend',
                ),
            ),
            1 => array(
                0 => 'fieldset',
                1 => array(
                    0 => false,
                    1 => null,
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
        
        // check for fluency
        $this->assertType('Solar_View_Helper_Form', $helper);
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    
    public function testFetch()
    {
        $helper = $this->_view->form();
        
        $helper->setAttrib('foo', 'bar');
        
        $helper->addElement(
            array(
                'name' => 'baz',
                'type' => 'text',
                'size' => '10',
                'value' => 'dib',
            )
        );
        
        $actual = $helper->fetch();
        $expect = <<<EXPECT
<form action="/path/to/index.php/appname/action?foo=bar&amp;baz=dib" method="post" enctype="multipart/form-data" foo="bar">
        
        <dl>
            <dt><label for="baz"></label></dt>
            <dd><input type="text" name="baz" value="dib" id="baz" class="input-text baz" /></dd>
        
        </dl>
</form>
EXPECT;
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testFetch_noFormTag()
    {
        $helper = $this->_view->form();
        
        $helper->setAttrib('foo', 'bar');
        
        $helper->addElement(
            array(
                'name' => 'baz',
                'type' => 'text',
                'size' => '10',
                'value' => 'dib',
            )
        );
        
        $actual = $helper->fetch(false);
        $expect = <<<EXPECT
        <dl>
            <dt><label for="baz"></label></dt>
            <dd><input type="text" name="baz" value="dib" id="baz" class="input-text baz" /></dd>
        
        </dl>
EXPECT;
        $this->assertSame(trim($actual), trim($expect));
    }
    
    
    
    public function testListFeedback()
    {
        $helper = $this->_view->form();
        $messages = array('this is feedback', 'so is this');
        $actual = $helper->listFeedback($messages);
        $expect = <<<EXPECT
<ul>
    <li>this is feedback</li>
    <li>so is this</li>
</ul>
EXPECT;
        $this->assertSame(trim($actual), trim($expect));
        
        // now with a class
        $actual = $helper->listFeedback($messages, 'failure');
        $expect = <<<EXPECT
<ul class="failure">
    <li>this is feedback</li>
    <li>so is this</li>
</ul>
EXPECT;
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testReset()
    {
        $this->markTestSkipped('brittle test');
        // first, add an element
        $this->testAddElement();
        
        // now reset everything
        $helper = $this->_view->form()->reset();
        
        // test everything :-(
        $expect = array(
            'action'  => $this->_request->server['REQUEST_URI'],
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        );
        $this->assertProperty($helper, '_attribs', 'same', $expect);
        $this->assertProperty($helper, '_feedback', 'same', array());
        $this->assertProperty($helper, '_hidden', 'same', array());
        $this->assertProperty($helper, '_stack', 'same', array());
        $this->assertProperty($helper, '_status', 'null');
    }
}