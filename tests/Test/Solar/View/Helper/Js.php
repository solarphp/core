<?php

class Test_Solar_View_Helper_Js extends Solar_Test {

    protected $_view;

    public function __construct($config = null)
    {
        parent::__construct($config);
    }

    public function __destruct()
    {
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
        $helper = $this->_view->getHelper('js');
        $this->assertInstance($helper, 'Solar_View_Helper_Js');
    }

    public function testAddFile()
    {
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

    public function testFetch()
    {
        $helper = $this->_view->js()->addFile('foo.js')
                                    ->addFile('bar.js');

        // one second highlight of #test
        $this->_view->jsScriptaculous()->highlight('#test', array('duration' => 1));

        $actual = $helper->fetch();

        $expect = '    <script src="/public/foo.js" type="text/javascript"></script>'."\n";
        $expect .= '    <script src="/public/bar.js" type="text/javascript"></script>'."\n";
        $expect .= '    <script src="/public/Solar/scripts/prototype/prototype.js" type="text/javascript"></script>'."\n";
        $expect .= '    <script src="/public/Solar/scripts/scriptaculous/effects.js" type="text/javascript"></script>'."\n";
        $expect .= '<script type="text/javascript">'."\n";
        $expect .= "//<![CDATA[\n";
        $expect .= "Event.observe(window, 'load', function() {\n";
        $expect .= "    \$\$('#test').each(function(li){new Effect.Highlight(li, {duration:1})});\n";
        $expect .= "});\n";
        $expect .= "//]]>\n";
        $expect .= "</script>\n";
        $this->assertSame(trim($actual), trim($expect));
    }

    public function testReset()
    {
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
}

?>