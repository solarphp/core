<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Wiki_PageLink extends Test_Solar_Markdown_Plugin {
    
    protected $_encode = "\x1BSolar_Markdown_Wiki_PageLink:.*?\x1B";
    
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
        $expect = 'foo <a href="/wiki/read/Page_name#frag">page name</a> bar';
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
    
}
?>