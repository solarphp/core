<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_ItemList extends Test_Solar_View_Helper {
    
    protected $_items = array(
        'foo' => 'bar',
        'baz' => 'dib',
        'zim' => 'gir',
    );
    
    public function testItemList_ul()
    {
        $actual = $this->_view->itemList('ul', $this->_items);
        $tmp = array(
            '<ul>',
            '    <li>bar</li>',
            '    <li>dib</li>',
            '    <li>gir</li>',
            '</ul>',
        );
        $expect = implode("\n", $tmp) . "\n";
        $this->assertSame($actual, $expect);
    }
    
    public function testItemList_ol()
    {
        $actual = $this->_view->itemList('ol', $this->_items);
        $tmp = array(
            '<ol>',
            '    <li>bar</li>',
            '    <li>dib</li>',
            '    <li>gir</li>',
            '</ol>',
        );
        $expect = implode("\n", $tmp) . "\n";
        $this->assertSame($actual, $expect);
    }
    
    public function testItemList_dl()
    {
        $actual = $this->_view->itemList('dl', $this->_items);
        $tmp = array(
            '<dl>',
            '    <dt>foo</dt>',
            '    <dd>bar</dd>',
            '    <dt>baz</dt>',
            '    <dd>dib</dd>',
            '    <dt>zim</dt>',
            '    <dd>gir</dd>',
            '</dl>',
        );
        $expect = implode("\n", $tmp) . "\n";
        $this->assertSame($actual, $expect);
    }
}
?>