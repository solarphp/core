<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Wiki_ColorCodeBlockTest extends Solar_Markdown_PluginTestCase {
    
    protected $_source = "foo bar

{{code: php
    phpinfo();
}}

baz dib";
    
    public function testIsBlock()
    {
        $this->assertTrue($this->_plugin->isBlock());
    }
    
    public function testIsSpan()
    {
        $this->assertFalse($this->_plugin->isSpan());
    }
    
    // should show no changes
    public function testPrepare()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->prepare($source);
        $this->assertSame($expect, $actual);
    }
    
    // should show no changes
    public function testCleanup()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->cleanup($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testParse()
    {
        $actual = $this->_plugin->parse($this->_source);
        $expect = "foo bar\n\n$this->_token\n\nbaz dib";
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testRender()
    {
        $actual = $this->_markdown->transform($this->_source);
        $expect = 'foo bar

<pre><code><span style="color: #0000BB">&lt;?php
phpinfo</span><span style="color: #007700">();
</span><span style="color: #0000BB">?&gt;</span></code></pre>

baz dib';
        $this->assertSame(trim($actual), trim($expect));
    }
}
