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
          '/path/1/',
          '/path/2/',
          '/path/3/',
        );

        $stack = Solar::factory('Solar_PathStack');
        $stack->set('/path/1:/path/2:/path/3');
        $this->_assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byArray()
    {
        $stack = Solar::factory('Solar_PathStack');
        $stack->add(array('/path/1', '/path/2', '/path/3'));

        $expect = array(
          "/path/1/",
          "/path/2/",
          "/path/3/",
        );
        $this->_assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byString()
    {
        // add to the stack as a shell pathspec
        $stack = Solar::factory('Solar_PathStack');
        $stack->add('/path/1:/path/2:/path/3');

        $expect = array(
          "/path/1/",
          "/path/2/",
          "/path/3/",
        );
        $this->_assertSame($stack->get(), $expect);

    }
    
    public function testAdd_byLifo()
    {
        $stack = Solar::factory('Solar_PathStack');
        $stack->add('/path/1');
        $stack->add('/path/2');
        $stack->add('/path/3');

        $expect = array(
          "/path/3/",
          "/path/2/",
          "/path/1/",
        );
        $this->_assertSame($stack->get(), $expect);
    }
    
    public function testSet_byString()
    {
        $expect = array(
          '/path/1/',
          '/path/2/',
          '/path/3/',
        );

        $stack = Solar::factory('Solar_PathStack');
        $stack->set('/path/1:/path/2:/path/3');
        $this->_assertSame($stack->get(), $expect);

    }
    
    public function testSet_byArray()
    {
        $expect = array(
          '/path/1/',
          '/path/2/',
          '/path/3/',
        );
        
        $stack = Solar::factory('Solar_PathStack');
        $stack->set($expect);
        $this->_assertSame($stack->get(), $expect);
    }
    
    public function testFind()
    {
        $this->_todo('rebuild using new file locations');
        
        /*
        // get the stack object FIRST
        $stack = Solar::factory('Solar_PathStack');
        
        // NOW reset the include_path
        $old_path = set_include_path(dirname(dirname(__FILE__)));
        
        // use the testing directory to look for __construct.phpt files
        $dir = dirname(dirname(__FILE__));
        $path = array(
            "Solar_Base",
            "Solar_Debug_Timer",
            "Solar_Debug_Var",
        );

        $stack->set($path);

        // should find it at Solar_Base
        $actual = $stack->find('__construct.phpt');
        $this->_assertSame($actual, "{$path[0]}/__construct.phpt");

        // should find it at Solar_Debug_Timer
        $actual = $stack->find('start.phpt');
        $this->_assertSame($actual, "{$path[1]}/start.phpt");

        // should find it at Solar_Debug_Var
        $actual = $stack->find('dump.phpt');
        $this->_assertSame($actual, "{$path[2]}/dump.phpt");

        // should not find it at all
        $actual = $stack->find('no_such_file');
        $this->_assertFalse($actual);

        // put the include_path back
        set_include_path($old_path);
        */
    }
    
    public function testFindReal()
    {
        $this->_todo('rebuild using new file locations');
        
        /*
        // use the testing directory to look for __construct.phpt files
        $dir = dirname(dirname(__FILE__));
        $path = array(
            "$dir/Solar_Base",
            "$dir/Solar_Debug_Timer",
            "$dir/Solar_Debug_Var",
        );

        $stack = Solar::factory('Solar_PathStack');
        $stack->set($path);

        // should find it at Solar_Base
        $actual = $stack->findReal('__construct.phpt');
        $this->_assertSame($actual, "{$path[0]}/__construct.phpt");

        // should find it at Solar_Debug_Timer
        $actual = $stack->findReal('start.phpt');
        $this->_assertSame($actual, "{$path[1]}/start.phpt");

        // should find it at Solar_Debug_Var
        $actual = $stack->findReal('dump.phpt');
        $this->_assertSame($actual, "{$path[2]}/dump.phpt");

        // should not find it at all
        $actual = $stack->findReal('no_such_file');
        $this->_assertFalse($actual);
        */
    }
}
?>