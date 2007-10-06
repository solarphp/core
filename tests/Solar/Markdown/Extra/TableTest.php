<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Extra_TableTest extends Solar_Markdown_PluginTestCase
{
    
    protected $_plain = "
First Header  | Second Header
------------- | -------------
Content A     | Content C    
Content B     | Content D    
";
    
    protected $_pipes = "
| First Header  | Second Header |
| ------------- | ------------- |
| Content A     | Content C     |
| Content B     | Content D     |
";
    
    protected $_align = "
| Left      | Right     |
| :-------- | --------: |
| Content A | Content C |
| Content B | Content D |
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
        $source = "foo bar\n$this->_plain\nbaz dib";
        $expect = "foo bar\n\n$this->_token\n\nbaz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
   
    public function testParse_pipes()
    {
        $source = "foo bar\n$this->_pipes\nbaz dib";
        $expect = "foo bar\n\n$this->_token\n\nbaz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
   
    public function testParse_align()
    {
        $source = "foo bar\n$this->_align\nbaz dib";
        $expect = "foo bar\n\n$this->_token\n\nbaz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testRender()
    {
        $source = $this->_plain;
        $actual = $this->_markdown->transform($source);
        
        $expect = '
<table>
    <thead>
        <tr>
            <th>First Header</th>
            <th>Second Header</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Content A</td>
            <td>Content C</td>
        </tr>
        <tr>
            <td>Content B</td>
            <td>Content D</td>
        </tr>
    </tbody>
</table>';
        
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_pipes()
    {
        $source = $this->_pipes;
        $actual = $this->_markdown->transform($source);
        
        $expect = '
<table>
    <thead>
        <tr>
            <th>First Header</th>
            <th>Second Header</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Content A</td>
            <td>Content C</td>
        </tr>
        <tr>
            <td>Content B</td>
            <td>Content D</td>
        </tr>
    </tbody>
</table>';
        
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_align()
    {
        $source = $this->_align;
        $actual = $this->_markdown->transform($source);
        
        $expect = '
<table>
    <thead>
        <tr>
            <th align="left">Left</th>
            <th align="right">Right</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td align="left">Content A</td>
            <td align="right">Content C</td>
        </tr>
        <tr>
            <td align="left">Content B</td>
            <td align="right">Content D</td>
        </tr>
    </tbody>
</table>';
        
        $this->assertSame(trim($actual), trim($expect));
    }
}
