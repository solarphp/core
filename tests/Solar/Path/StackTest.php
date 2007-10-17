<?php
require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

class Solar_Path_StackTest extends PHPUnit_Framework_TestCase
{
    
    private $_support_path  = '';
    
    public function setup()
    {
        $this->_support_path = realpath(dirname(__FILE__) . '/../../support/Solar/Path/Stack');
    }
    
    public function teardown()
    {
        $this->_support_path = '';
    }
    
    public function testGet()
    {
        $expect = array(
            Solar_Dir::fix('/path/foo/'),
            Solar_Dir::fix('/path/bar/'),
            Solar_Dir::fix('/path/baz/'),
        );
        
        $stack = Solar::factory('Solar_Path_Stack');
        $stack->set('/path/foo:/path/bar:/path/baz');
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byArray()
    {
        $stack = Solar::factory('Solar_Path_Stack');
        $stack->add(array('/path/foo', '/path/bar', '/path/baz'));
        
        $expect = array(
            Solar_Dir::fix('/path/foo/'),
            Solar_Dir::fix('/path/bar/'),
            Solar_Dir::fix('/path/baz/'),
        );
        
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byString()
    {
        // add to the stack as a shell pathspec
        $stack = Solar::factory('Solar_Path_Stack');
        $stack->add('/path/foo:/path/bar:/path/baz');
        
        $expect = array(
            Solar_Dir::fix('/path/foo/'),
            Solar_Dir::fix('/path/bar/'),
            Solar_Dir::fix('/path/baz/'),
        );
        
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byLifo()
    {
        $stack = Solar::factory('Solar_Path_Stack');
        $stack->add('/path/foo');
        $stack->add('/path/bar');
        $stack->add('/path/baz');
        
        $expect = array(
            Solar_Dir::fix('/path/baz/'),
            Solar_Dir::fix('/path/bar/'),
            Solar_Dir::fix('/path/foo/'),
        );
        
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testSet_byString()
    {
        $expect = array(
            Solar_Dir::fix('/path/foo/'),
            Solar_Dir::fix('/path/bar/'),
            Solar_Dir::fix('/path/baz/'),
        );
        
        $stack = Solar::factory('Solar_Path_Stack');
        $stack->set('/path/foo:/path/bar:/path/baz');
        $this->assertSame($stack->get(), $expect);
    
    }
    
    public function testSet_byArray()
    {
        $expect = array(
            Solar_Dir::fix('/path/foo/'),
            Solar_Dir::fix('/path/bar/'),
            Solar_Dir::fix('/path/baz/'),
        );
        
        $stack = Solar::factory('Solar_Path_Stack');
        $stack->set($expect);
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testFind()
    {
        // get the stack object FIRST
        $stack = Solar::factory('Solar_Path_Stack');
        
        // now reset the include_path
        $old_path = set_include_path($this->_support_path);
        
        // use the testing directory to look for files
        $path = array(
            "a",
            "b",
            "c",
        );
        
        $stack->add($path[0]);
        $stack->add($path[1]);
        $stack->add($path[2]);
        
        // should find it at a
        $actual = $stack->find('target1');
        $expect = Solar_Dir::fix($path[0]) . 'target1';
        $this->assertSame($expect, $actual);
        
        // should find it at b
        $actual = $stack->find('target2');
        $expect = Solar_Dir::fix($path[1]) . 'target2';
        $this->assertSame($expect, $actual);
        
        // should find it at c
        $actual = $stack->find('target3');
        $expect = Solar_Dir::fix($path[2]) . 'target3';
        $this->assertSame($expect, $actual);
        
        // should not find it at all
        $actual = $stack->find('no_such_file');
        $this->assertFalse($actual);
        
        // put the include_path back
        set_include_path($old_path);
    }
    
    public function testFindReal()
    {
        // use the testing directory to look for __construct.phpt files
        $dir = $this->_support_path;
        $path = array(
            "$dir/a",
            "$dir/b",
            "$dir/c",
        );
        
        $stack = Solar::factory('Solar_Path_Stack');
        $stack->add($path[0]);
        $stack->add($path[1]);
        $stack->add($path[2]);
        
        // should find it at Solar_Base
        $actual = $stack->findReal('target1');
        $expect = Solar_Dir::fix($path[0]) . 'target1';
        $this->assertSame($expect, $actual);
        
        // should find it at Solar_Debug_Timer
        $actual = $stack->findReal('target2');
        $expect = Solar_Dir::fix($path[1]) . 'target2';
        $this->assertSame($expect, $actual);
        
        // should find it at Solar_Debug_Var
        $actual = $stack->findReal('target3');
        $expect = Solar_Dir::fix($path[2]) . 'target3';
        $this->assertSame($expect, $actual);
        
        // should not find it at all
        $actual = $stack->findReal('no_such_file');
        $this->assertFalse($actual);
    }
}