<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Extra_DefListTest extends Solar_Markdown_PluginTestCase 
{
    
    protected $_basic = "
Apple
:   Pomaceous fruit of plants of the genus Malus in 
    the family Rosaceae.

Orange
:   The fruit of an evergreen tree of the genus Citrus.
";
    
    protected $_lazy = "
Apple
:   Pomaceous fruit of plants of the genus Malus in 
the family Rosaceae.

Orange
:   The fruit of an evergreen tree of the genus Citrus.
";
    
    protected $_multi_def = "
Apple
:   Pomaceous fruit of plants of the genus Malus in 
    the family Rosaceae.
:   An american computer company.

Orange
:   The fruit of an evergreen tree of the genus Citrus.
";
    
    protected $_multi_term = "
Term 1
Term 2
:   Definition a

Term 3
:   Definition b
";
    
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
        $source = "foo bar\n$this->_basic\nbaz dib";
        $expect = "foo bar\n\n$this->_token\n\nbaz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testParse_lazy()
    {
        $source = "foo bar\n$this->_lazy\nbaz dib";
        $expect = "foo bar\n\n$this->_token\n\nbaz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testParse_multiDef()
    {
        $source = "foo bar\n$this->_multi_def\nbaz dib";
        $expect = "foo bar\n\n$this->_token\n\nbaz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testParse_multiTerm()
    {
        $source = "foo bar\n$this->_multi_term\nbaz dib";
        $expect = "foo bar\n\n$this->_token\n\nbaz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testRender()
    {
        $source = $this->_basic;
        $actual = $this->_markdown->transform($source);
        $expect = '
<dl>
<dt>Apple</dt>
<dd>Pomaceous fruit of plants of the genus Malus in 
the family Rosaceae.</dd>

<dt>Orange</dt>
<dd>The fruit of an evergreen tree of the genus Citrus.</dd>
</dl>
';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_lazy()
    {
        $source = $this->_lazy;
        $actual = $this->_markdown->transform($source);
        $expect = '
<dl>
<dt>Apple</dt>
<dd>Pomaceous fruit of plants of the genus Malus in 
the family Rosaceae.</dd>

<dt>Orange</dt>
<dd>The fruit of an evergreen tree of the genus Citrus.</dd>
</dl>
';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_multiDef()
    {
        $source = $this->_multi_def;
        $actual = $this->_markdown->transform($source);
        $expect = '
<dl>
<dt>Apple</dt>
<dd>Pomaceous fruit of plants of the genus Malus in 
the family Rosaceae.</dd>

<dd>An american computer company.</dd>

<dt>Orange</dt>
<dd>The fruit of an evergreen tree of the genus Citrus.</dd>
</dl>
';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_multiTerm()
    {
        $source = $this->_multi_term;
        $actual = $this->_markdown->transform($source);
        $expect = '
<dl>
<dt>Term 1</dt>
<dt>Term 2</dt>
<dd>Definition a</dd>

<dt>Term 3</dt>
<dd>Definition b</dd>
</dl>
';
        $this->assertSame(trim($actual), trim($expect));
    }
}
