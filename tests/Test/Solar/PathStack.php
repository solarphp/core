<?php

class Test_Solar_PathStack extends Solar_Test {
    
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function setup()
    {
    }
    
    public function teardown()
    {
    }
    
    public function testGet()
    {
        $expect = array(
          '/path/foo/',
          '/path/bar/',
          '/path/baz/',
        );

        $stack = Solar::factory('Solar_PathStack');
        $stack->set('/path/foo:/path/bar:/path/baz');
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byArray()
    {
        $stack = Solar::factory('Solar_PathStack');
        $stack->add(array('/path/foo', '/path/bar', '/path/baz'));

        $expect = array(
          "/path/foo/",
          "/path/bar/",
          "/path/baz/",
        );
        
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byString()
    {
        // add to the stack as a shell pathspec
        $stack = Solar::factory('Solar_PathStack');
        $stack->add('/path/foo:/path/bar:/path/baz');

        $expect = array(
          "/path/foo/",
          "/path/bar/",
          "/path/baz/",
        );
        
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byLifo()
    {
        $stack = Solar::factory('Solar_PathStack');
        $stack->add('/path/foo');
        $stack->add('/path/bar');
        $stack->add('/path/baz');

        $expect = array(
          "/path/baz/",
          "/path/bar/",
          "/path/foo/",
        );
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testSet_byString()
    {
        $expect = array(
          '/path/foo/',
          '/path/bar/',
          '/path/baz/',
        );

        $stack = Solar::factory('Solar_PathStack');
        $stack->set('/path/foo:/path/bar:/path/baz');
        $this->assertSame($stack->get(), $expect);

    }
    
    public function testSet_byArray()
    {
        $expect = array(
          '/path/foo/',
          '/path/bar/',
          '/path/baz/',
        );
        
        $stack = Solar::factory('Solar_PathStack');
        $stack->set($expect);
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testFind()
    {
        // get the stack object FIRST
        $stack = Solar::factory('Solar_PathStack');
        
        // now reset the include_path
        $old_path = set_include_path(dirname(__FILE__) . '/PathStack');
        
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
        $this->assertSame($actual, "{$path[0]}/target1");

        // should find it at b
        $actual = $stack->find('target2');
        $this->assertSame($actual, "{$path[1]}/target2");

        // should find it at c
        $actual = $stack->find('target3');
        $this->assertSame($actual, "{$path[2]}/target3");

        // should not find it at all
        $actual = $stack->find('no_such_file');
        $this->assertFalse($actual);

        // put the include_path back
        set_include_path($old_path);
    }
    
    public function testFindReal()
    {
        // use the testing directory to look for __construct.phpt files
        $dir = dirname(__FILE__) . "/PathStack";
        $path = array(
            "$dir/a",
            "$dir/b",
            "$dir/c",
        );
        
        $stack = Solar::factory('Solar_PathStack');
        $stack->add($path[0]);
        $stack->add($path[1]);
        $stack->add($path[2]);
        

        // should find it at Solar_Base
        $actual = $stack->findReal('target1');
        $this->assertSame($actual, "{$path[0]}/target1");

        // should find it at Solar_Debug_Timer
        $actual = $stack->findReal('target2');
        $this->assertSame($actual, "{$path[1]}/target2");

        // should find it at Solar_Debug_Var
        $actual = $stack->findReal('target3');
        $this->assertSame($actual, "{$path[2]}/target3");

        // should not find it at all
        $actual = $stack->findReal('no_such_file');
        $this->assertFalse($actual);
    }
}
?>