<?php
/**
 * 
 * @todo test what happens when you add an element type that doesn't exist
 * 
 */
class Test_Solar_View_Helper_Form extends Solar_Test {
    
    protected $_view;
    
    protected $_server;
    
    protected $_get;
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // when running from the command line, these elements are empty.
        // add them so that web-like testing can occur.
        $this->_server = $_SERVER;
        $this->_get = $_GET;
        $_SERVER['HTTP_HOST']    = 'example.com';
        $_SERVER['SCRIPT_NAME']  = '/path/to/index.php';
        $_SERVER['PATH_INFO']    = '/appname/action';
        $_SERVER['QUERY_STRING'] = 'foo=bar&baz=dib';
        $_SERVER['REQUEST_URI']  = $_SERVER['SCRIPT_NAME']
                                 . $_SERVER['PATH_INFO']
                                 . '?' . $_SERVER['QUERY_STRING'];

        // emulate $_GET vars from the URI
        parse_str($_SERVER['QUERY_STRING'], $_GET);
    }
    
    public function __destruct()
    {
        $_GET = $this->_get;
        $_SERVER = $this->_server;
        parent::__destruct();
    }
    
    public function setup()
    {
        parent::setup();
        $this->_view = Solar::factory('Solar_View');
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $helper = $this->_view->getHelper('form');
        $this->assertInstance($helper, 'Solar_View_Helper_Form');
    }
    
    // functionally similar to addElement()
    public function test__call()
    {
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
                    'attribs' => array(
                        'id' => 'test',
                        'class' => 'input-button test',
                    ),
                    'options' => array(),
                    'disable' => false,
                    'require' => false,
                    'feedback' => array(),
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
        
        // check for fluency
        $this->assertInstance($helper, 'Solar_View_Helper_Form');
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    // get the form object
    public function testForm_default()
    {
        $helper = $this->_view->form();
        
        // check for fluency
        $this->assertInstance($helper, 'Solar_View_Helper_Form');
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    // set attribs
    public function testForm_array()
    {
        $attribs = array('foo' => 'bar');
        $helper = $this->_view->form($attribs);
        
        $expect = array(
            'action'  => $_SERVER['REQUEST_URI'],
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
            'foo'     => 'bar',
        );
        $this->assertProperty($helper, '_attribs', 'same', $expect);
        
        // check for fluency
        $this->assertInstance($helper, 'Solar_View_Helper_Form');
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
        $expect = array(
            'action'  => $_SERVER['REQUEST_URI'],
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
        $this->assertInstance($helper, 'Solar_View_Helper_Form');
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    public function testAddElement()
    {
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
                    'attribs' => array(
                        'id' => 'test',
                        'class' => 'input-button test',
                    ),
                    'options' => array(),
                    'disable' => false,
                    'require' => false,
                    'feedback' => array(),
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
        
        // check for fluency
        $this->assertInstance($helper, 'Solar_View_Helper_Form');
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    public function testSetStatus()
    {
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
                    'attribs' => array(
                        'id' => 'baz',
                        'class' => 'input-text baz',
                    ),
                    'options' => array(),
                    'disable' => false,
                    'require' => false,
                    'feedback' => array(),
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
    }
    
    public function testAuto_array()
    {
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
                    'attribs' => array(
                        'id' => 'baz',
                        'class' => 'input-text baz',
                    ),
                    'options' => array(),
                    'disable' => false,
                    'require' => false,
                    'feedback' => array(),
                ),
            ),
        );
        $this->assertProperty($helper, '_stack', 'same', $expect);
    }
    
    public function testBeginGroup()
    {
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
        $this->assertInstance($helper, 'Solar_View_Helper_Form');
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    public function testEndGroup()
    {
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
        $this->assertInstance($helper, 'Solar_View_Helper_Form');
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    public function testBeginFieldset()
    {
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
        $this->assertInstance($helper, 'Solar_View_Helper_Form');
        $this->assertSame($helper, $this->_view->getHelper('form'));
    }
    
    public function testEndFieldset()
    {
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
        $this->assertInstance($helper, 'Solar_View_Helper_Form');
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
        // first, add an element
        $this->testAddElement();
        
        // now reset everything
        $helper = $this->_view->form()->reset();
        
        // test everything :-(
        $expect = array(
            'action'  => $_SERVER['REQUEST_URI'],
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
?>