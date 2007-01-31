<?php

require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

class Solar_Class_StackTest extends PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $expect = array(
          'Base_Foo_',
          'Base_Bar_',
          'Base_Baz_',
        );

        $stack = Solar::factory('Solar_Class_Stack');
        $stack->set('Base_Foo, Base_Bar, Base_Baz');
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byArray()
    {
        $stack = Solar::factory('Solar_Class_Stack');
        $stack->add(array('Base_Foo', 'Base_Bar', 'Base_Baz'));

        $expect = array(
          "Base_Foo_",
          "Base_Bar_",
          "Base_Baz_",
        );
        
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byString()
    {
        // add to the stack as a csv list
        $stack = Solar::factory('Solar_Class_Stack');
        $stack->add('Base_Foo, Base_Bar, Base_Baz');

        $expect = array(
          "Base_Foo_",
          "Base_Bar_",
          "Base_Baz_",
        );
        
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testAdd_byLifo()
    {
        $stack = Solar::factory('Solar_Class_Stack');
        $stack->add('Base_Foo');
        $stack->add('Base_Bar');
        $stack->add('Base_Baz');

        $expect = array(
          "Base_Baz_",
          "Base_Bar_",
          "Base_Foo_",
        );
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testSet_byString()
    {
        $expect = array(
          'Base_Foo_',
          'Base_Bar_',
          'Base_Baz_',
        );

        $stack = Solar::factory('Solar_Class_Stack');
        $stack->set('Base_Foo, Base_Bar, Base_Baz');
        $this->assertSame($stack->get(), $expect);

    }
    
    public function testSet_byArray()
    {
        $expect = array(
          'Base_Foo_',
          'Base_Bar_',
          'Base_Baz_',
        );
        
        $stack = Solar::factory('Solar_Class_Stack');
        $stack->set($expect);
        $this->assertSame($stack->get(), $expect);
    }
    
    public function testLoad()
    {
        // try loading 'Stack' from Solar_Path and Solar_Class:
        // should find it first at Solar_Path_Stack.
        $stack = Solar::factory('Solar_Class_Stack');
        $stack->set('Solar_Path, Solar_Class');
        $actual = $stack->load('Stack');
        $expect = 'Solar_Path_Stack';
        $this->assertSame($actual, $expect);
        
        // now try loading 'Map': should not find it at Solar_Path, so
        // should fall back to Solar_Class.
        $actual = $stack->load('Map');
        $expect = 'Solar_Class_Map';
        $this->assertSame($actual, $expect);
        
        // should fail to find this anywhere on the stack
        try {
            $stack->load('NoSuchClass');
            $this->fail('Should have thrown an exception here');
        } catch (Solar_Exception $e) {
            // do nothing
        }
    }
}
?>