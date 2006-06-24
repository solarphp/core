<?php

/**
 * 
 * @todo change try/catch failures to throw a regular exception instead
 * of calling $this->fail(), since that gets caught by the success catch.
 * 
 */
class Test_Solar_Test extends Solar_Test {
    
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function __destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        parent::setup();
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $actual = Solar::factory('Solar_Test');
        $this->assertInstance($actual, 'Solar_Test');
    }
    
    public function testAssertTrue()
    {
        // positive test
        $this->assertTrue(true);
        
        // negative test
        try {
            $this->assertTrue(null);
            throw new Exception('asserted true when actually null');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertNotTrue()
    {
        // positive test
        $this->assertNotTrue(null);
        
        // negative test
        try {
            $this->assertNotTrue(true);
            throw new Exception('asserted not true when actually true');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertFalse()
    {
        // positive test
        $this->assertFalse(false);
        
        // negative test
        try {
            $this->assertFalse(null);
            throw new Exception('asserted false when actually true');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertNotFalse()
    {
        // positive test
        $this->assertNotFalse(null);
        
        // negative test
        try {
            $this->assertNotFalse(false);
            throw new Exception('asserted not false when actually false');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertNull()
    {
        // positive test
        $this->assertNull(null);
        
        // negative test
        try {
            $this->assertNull(false);
            throw new Exception('asserted null when actually false');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertNotNull()
    {
        // positive test
        $this->assertNotNull(false);
        
        // negative test
        try {
            $this->assertNotNull(null);
            throw new Exception('asserted not null when actually null');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertInstance()
    {
        // normal use
        $this->assertInstance($this, 'Solar_Test');
        
        // not an object
        $actual = 'not an object';
        try {
            $this->assertInstance($actual, 'Solar_Test');
            throw new Exception('asserted an instance when actually not an object');
        } catch (Solar_Test_Exception_Fail $e){
            // do nothing, it passed :-)
        }
        
        // class not loaded
        try {
            $this->assertInstance($this, 'No_Such_Class');
            throw new Exception('asserted an instance when actually class not loaded');
        } catch (Solar_Test_Exception_Fail $e){
            // do nothing, it passed :-)
        }
    }
    
    public function testAssertNotInstance()
    {
        // normal use
        $this->assertNotInstance($this, 'Solar');
        
        // not an object
        $actual = 'not an object';
        try {
            $this->assertNotInstance($actual, 'Solar');
            throw new Exception('asserted not an instance when actually not an object');
        } catch (Solar_Test_Exception_Fail $e){
            // do nothing, it passed :-)
        }
        
        // class not loaded
        try {
            $this->assertNotInstance($this, 'No_Such_Class');
            throw new Exception('asserted not an instance when actually class not loaded');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertSame_scalar()
    {
        $actual = '1';
        $expect = '1';
        $this->assertSame($actual, $expect);
        
        $actual = 1;
        $expect = '1';
        try {
            $this->assertSame($actual, $expect);
            throw new Exception('asserted integer 1 was same as string 1');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        
        $actual = array();
        $expect = '';
        try {
            $this->assertSame($actual, $expect);
            throw new Exception('asserted empty array was same as empty string');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertSame_array()
    {
        // same order
        $actual = array(1, 2, 3);
        $expect = array(1, 2, 3);
        $this->assertSame($actual, $expect);
        
        // order matters in sequentials
        $actual = array(1, 2, 3);
        $expect = array(1, 3, 2);
        try {
            $this->assertSame($actual, $expect);
            throw new Exception('asserted different seq-arrays were same');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        
        // same keys in hash
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('a' => 1, 'b' => 2, 'c' => 3);
        $this->assertSame($actual, $expect);
        
        // key order doesn't matter in hashes
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('a' => 1, 'c' => 3, 'b' => 2);
        $this->assertSame($actual, $expect);
        
        // different value types are not same
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('a' => '1', 'b' => '2', 'c' => '3');
        try {
            $this->assertSame($actual, $expect);
            throw new Exception('asserted different assoc-array values were same');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertSame_object()
    {
        $actual = Solar::factory('Solar_Test_Example');
        $expect = $actual;
        $this->assertSame($actual, $expect);
        
        $expect = clone($actual);
        try {
            $this->assertSame($actual, $expect);
            throw new Exception('asserted cloned object was same as original');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertNotSame_scalar()
    {
        $actual = 1;
        $expect = '1';
        $this->assertNotSame($actual, $expect);
        
        $actual = '1';
        $expect = '1';
        try {
            $this->assertNotSame($actual, $expect);
            throw new Exception('asserted same strings were different');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        
        $actual = array();
        $expect = '';
        $this->assertNotSame($actual, $expect);
    }
    
    public function testAssertNotSame_array()
    {
        // different order
        $actual = array(1, 2, 3);
        $expect = array(1, 3, 2);
        $this->assertNotSame($actual, $expect);
        
        // order matters in sequentials
        $actual = array(1, 2, 3);
        $expect = array(1, 2, 3);
        try {
            $this->assertNotSame($actual, $expect);
            throw new Exception('asserted same seq-arrays were different');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        
        // different keys in hash
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('d' => 1, 'e' => 2, 'f' => 3);
        $this->assertNotSame($actual, $expect);
        
        // key order doesn't matter in hashes
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('a' => 1, 'c' => 3, 'b' => 2);
        try {
            $this->assertNotSame($actual, $expect);
            throw new Exception('asserted same assoc-arrays were different');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        
        // different value types are not same
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('a' => '1', 'b' => '2', 'c' => '3');
        $this->assertNotSame($actual, $expect);
    }
    
    public function testAssertNotSame_object()
    {
        $actual = Solar::factory('Solar_Test_Example');
        $expect = clone($actual);
        $this->assertNotSame($actual, $expect);
        
        $expect = $actual;
        try {
            $this->assertNotSame($actual, $expect);
            throw new Exception('asserted object was not reference to original');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertEquals_scalar()
    {
        $actual = 1;
        $expect = '1';
        $this->assertEquals($actual, $expect);
        
        $actual = false;
        $expect = null;
        $this->assertEquals($actual, $expect);
        
        $actual = 0;
        $expect = '1';
        try {
            $this->assertEquals($actual, $expect);
            throw new Exception('asserted equals when not equals');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertNotEquals_scalar()
    {
        $actual = 0;
        $expect = '1';
        $this->assertNotEquals($actual, $expect);

        $actual = 1;
        $expect = '1';
        try {
            $this->assertNotEquals($actual, $expect);
            throw new Exception('asserted not equals when not equals');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertEquals_array()
    {
        // same order
        $actual = array(1, 2, 3);
        $expect = array(1, 2, 3);
        $this->assertEquals($actual, $expect);
        
        // order matters in sequentials
        $actual = array(1, 2, 3);
        $expect = array(1, 3, 2);
        try {
            $this->assertEquals($actual, $expect);
            throw new Exception('asserted inequal arrays were equals');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        
        // same keys in hash
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('a' => 1, 'b' => 2, 'c' => 3);
        $this->assertEquals($actual, $expect);
        
        // key order doesn't matter in hashes
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('a' => 1, 'c' => 3, 'b' => 2);
        $this->assertEquals($actual, $expect);
        
        // different value types are equals
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('a' => '1', 'b' => '2', 'c' => '3');
        $this->assertEquals($actual, $expect);
        
    }
    
    public function testAssertNotEquals_array()
    {
        // different order
        $actual = array(1, 2, 3);
        $expect = array(1, 3, 2);
        $this->assertNotEquals($actual, $expect);
        
        // order matters in sequentials
        $actual = array(1, 2, 3);
        $expect = array(1, 2, 3);
        try {
            $this->assertNotEquals($actual, $expect);
            throw new Exception('asserted equal seq-arrays were not equal');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        
        // different keys in hash
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('d' => 1, 'e' => 2, 'f' => 3);
        $this->assertNotEquals($actual, $expect);
        
        // key order doesn't matter in hashes
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('a' => 1, 'c' => 3, 'b' => 2);
        try {
            $this->assertNotEquals($actual, $expect);
            throw new Exception('asserted equal assoc-arrays were not equal');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
        
        // different value types are equals
        $actual = array('a' => 1, 'b' => 2, 'c' => 3);
        $expect = array('a' => '1', 'b' => '2', 'c' => '3');
        try {
            $this->assertNotEquals($actual, $expect);
            throw new Exception('asserted equal assoc-array values were not equal');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertEquals_object()
    {
        $actual = new StdClass();
        $actual->a = 1;
        $actual->b = 2;
        $actual->c = 3;
        
        $expect = clone($actual);
        $this->assertEquals($actual, $expect);
        
        $expect->c = '3';
        $this->assertEquals($actual, $expect);
        
        $expect->c = null;
        try {
            $this->assertEquals($actual, $expect);
            throw new Exception('asserted inequal object properties were equal');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertNotEquals_object()
    {
        $actual = new StdClass();
        $actual->a = 1;
        $actual->b = 2;
        $actual->c = 3;
        
        $expect = clone($actual);
        $expect->c = null;
        $this->assertNotEquals($actual, $expect);
        
        unset($expect->c);
        $this->assertNotEquals($actual, $expect);
        
        $expect = clone($actual);
        try {
            $this->assertNotEquals($actual, $expect);
            throw new Exception('asserted equal object properties were not equal');
        } catch (Solar_Test_Exception_Fail $e) {
            // do nothing, it passed :-)
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    public function testAssertProperty_protected()
    {
        $example = Solar::factory('Solar_Test_Example');
        $expect = array(
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => 'gaz',
        );
        $this->assertProperty($example, '_config', 'equals', $expect);
    }
    
    public function testAssertProperty_private()
    {
        $example = Solar::factory('Solar_Test_Example');
        $expect = 'invisible';
        $this->assertProperty($example, '_private_var', 'equals', $expect);
    }
    
    public function testFail()
    {
        try {
            $this->fail('failed');
            throw new Exception('did not throw Solar_Test_Exception_Fail');
        } catch (Exception $e) {
            $this->assertInstance($e, 'Solar_Test_Exception_Fail');
        }
    }
    
    public function testTodo()
    {
        try {
            $this->todo('incomplete');
            throw new Exception('did not throw Solar_Test_Exception_Todo');
        } catch (Solar_Test_Exception_Todo $e) {
            $this->assertInstance($e, 'Solar_Test_Exception_Todo');
        }
    }
    
    public function testSkip()
    {
        try {
            $this->skip('skipped');
            throw new Exception('did not throw Solar_Test_Exception_Skip');
        } catch (Solar_Test_Exception_Skip $e) {
            $this->assertInstance($e, 'Solar_Test_Exception_Skip');
        }
    }
}
?>