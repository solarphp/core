<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Wiki_MethodSynopsisTest extends Solar_Markdown_PluginTestCase {
    
    protected $_source = "foo bar

{{method: methodName
   @access public
   @param  int
   @param  bool, \$var2
   @param  float, \$var3, \"value\"
   @return string
   @throws Class_1
   @throws Class_2
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

<div class="method-synopsis">
    <span class="access">public</span>
    <span class="return">string</span>
    <span class="method">methodName</span> (
        <span class="param"><span class="type">int</span> <span class="name"></span>, 
        <span class="param"><span class="type">bool</span> <span class="name">$var2</span>, 
        <span class="param-default"><span class="type">float</span> <span class="name">$var3</span> default <span class="default">&quot;value&quot;</span>
    )
    <span class="throws">throws <span class="type">Class_1</span></span>, 
    <span class="throws">throws <span class="type">Class_2</span></span>
</div>

baz dib';
        $this->assertSame(trim($actual), trim($expect));
    }
}
