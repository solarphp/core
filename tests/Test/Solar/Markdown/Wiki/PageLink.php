<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Wiki_PageLink extends Test_Solar_Markdown_Plugin {
    
    protected $_encode = "\x1BSolar_Markdown_Wiki_PageLink:.*?\x1B";
    
    protected function _transform($text)
    {
        // we need a special transform to process **spans** instead of
        // blocks, seeing as WikiLink is a span and only gets processed
        // inside a block.  so we need to fake it.
        $text = $this->_markdown->prepare($text);
        $text = $this->_markdown->processSpans($text);
        $text = $this->_markdown->cleanup($text);
        $text = $this->_markdown->render($text);
        return $text;
    }
    
    public function testIsBlock()
    {
        $this->assertFalse($this->_plugin->isBlock());
    }
    
    public function testIsSpan()
    {
        $this->assertTrue($this->_plugin->isSpan());
    }
    
    // should show no changes
    public function testPrepare()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->prepare($source);
        $this->assertSame($actual, $expect);
    }
    
    // should show no changes
    public function testCleanup()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->prepare($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse()
    {
        $source = 'foo [[page name]] bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegex($actual, "@$expect@");
    }
    
    public function testParse_frag()
    {
        $source = 'foo [[page name#frag]] bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegex($actual, "@$expect@");
    }
    
    public function testParse_text()
    {
        $source = 'foo [[page name | text]] bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegex($actual, "@$expect@");
    }
    
    public function testParse_atch()
    {
        $source = 'foo [[page name atch]]es bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegex($actual, "@$expect@");
    }
    
    public function testParse_combo()
    {
        $source = 'foo [[page name#frag | display]]s bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegex($actual, "@$expect@");
    }
    
    public function testRender()
    {
        $source = 'foo [[page name]] bar';
        $actual = $this->_transform($source);
        $expect = 'foo <a href="/wiki/read/Page_name">page name</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_frag()
    {
        $source = 'foo [[page name#frag]] bar';
        $actual = $this->_transform($source);
        $expect = 'foo <a href="/wiki/read/Page_name#frag">page name#frag</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_text()
    {
        $source = 'foo [[page name | text]] bar';
        $actual = $this->_transform($source);
        $expect = 'foo <a href="/wiki/read/Page_name">text</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_atch()
    {
        $source = 'foo [[page name atch]]es bar';
        $actual = $this->_transform($source);
        $expect = 'foo <a href="/wiki/read/Page_name_atch">page name atches</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_combo()
    {
        $source = 'foo [[page name#frag | display]]s bar';
        $actual = $this->_transform($source);
        $expect = 'foo <a href="/wiki/read/Page_name#frag">displays</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_comboCollapse()
    {
        $source = 'foo [[page name#frag | ]]s bar';
        $actual = $this->_transform($source);
        $expect = 'foo <a href="/wiki/read/Page_name#frag">page names</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testParse_interwiki()
    {
        $source = 'foo [[php::print()]] bar';
        $actual = $this->_plugin->parse($source);
        $expect = 'foo <a href="http://php.net/print()">php::print()</a> bar';
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_interwikiFrag()
    {
        $source = 'foo [[php::print() #anchor]] bar';
        $actual = $this->_plugin->parse($source);
        $expect = 'foo <a href="http://php.net/print()#anchor">php::print()#anchor</a> bar';
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_interwikiText()
    {
        $source = 'foo [[php::print() | other]] bar';
        $actual = $this->_plugin->parse($source);
        $expect = 'foo <a href="http://php.net/print()">other</a> bar';
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_interwikiAtch()
    {
        $source = 'foo [[php::print]]ers bar';
        $actual = $this->_plugin->parse($source);
        $expect = 'foo <a href="http://php.net/print">php::printers</a> bar';
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_interwikiCombo()
    {
        $source = 'foo [[php::print()#anchor | print]]ers bar';
        $actual = $this->_plugin->parse($source);
        $expect = 'foo <a href="http://php.net/print()#anchor">printers</a> bar';
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_interwikiComboCollapse()
    {
        $source = 'foo [[php::print#anchor | ]]ers bar';
        $actual = $this->_plugin->parse($source);
        $expect = 'foo <a href="http://php.net/print#anchor">printers</a> bar';
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_manyPerLine()
    {
        $source = 'foo [[page one]] '
                . 'bar [[page two]] '
                . 'baz [[page three]] '
                . 'dib';
        
        $expect = 'foo <a href="/wiki/read/Page_one">page one</a> '
                . 'bar <a href="/wiki/read/Page_two">page two</a> '
                . 'baz <a href="/wiki/read/Page_three">page three</a> '
                . 'dib';
        
        $actual = $this->_transform($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_interwikiManyPerLine()
    {
        $source = 'foo [[php::print()]] '
                . 'bar [[php::echo | ]] '
                . 'baz [[php::phpinfo()]] '
                . 'dib';
        
        $expect = 'foo <a href="http://php.net/print()">php::print()</a> '
                . 'bar <a href="http://php.net/echo">echo</a> '
                . 'baz <a href="http://php.net/phpinfo()">php::phpinfo()</a> '
                . 'dib';
        
        $actual = $this->_transform($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_mixed()
    {
        $source = 'foo [[page one]] '
                . 'bar [[php::print()]] '
                . 'baz [[page two]] '
                . 'dib [[php::echo | ]] '
                . 'zim [[page three]] '
                . 'gir [[php::phpinfo()]] '
                . 'irk';
        
        $expect = 'foo <a href="/wiki/read/Page_one">page one</a> '
                . 'bar <a href="http://php.net/print()">php::print()</a> '
                . 'baz <a href="/wiki/read/Page_two">page two</a> '
                . 'dib <a href="http://php.net/echo">echo</a> '
                . 'zim <a href="/wiki/read/Page_three">page three</a> '
                . 'gir <a href="http://php.net/phpinfo()">php::phpinfo()</a> '
                . 'irk';
                
        $actual = $this->_transform($source);
        $this->assertSame($actual, $expect);
    }
    
}
?>