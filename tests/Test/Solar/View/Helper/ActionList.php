<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_ActionList extends Test_Solar_View_Helper {
    
    protected $_menu = array(
        'blog/browse' => 'Blog',
        'forum/read'  => 'Forum',
        'wiki/edit'   => 'Wiki',
    );
    
    protected $_display = array(
        'list_type'      => 'ul',
        'list_class'     => 'site-menu',
        'item_class'     => 'unselected',
        'selected_class' => 'selected',
    );
    
    public function testActionList()
    {
        $actual = $this->_view->actionList(
            $this->_menu,
            '/forum/read/',
            $this->_display
        );
        
        $tmp = array(
            '<ul class="site-menu">',
            '    <li class="unselected"><a href="/index.php/blog/browse">Blog</a></li>',
            '    <li class="selected">Forum</li>',
            '    <li class="unselected"><a href="/index.php/wiki/edit">Wiki</a></li>',
            '</ul>',
        );
        
        $expect = implode("\n", $tmp) . "\n";
        $this->assertSame($actual, $expect);
    }
}
?>