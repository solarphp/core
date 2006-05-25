<?php
/**
 * 
 * A single unit test.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * A single unit test.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 */
class Solar_Test extends Solar_Base {
    
    /**
     * 
     * Setup before the entire unit test.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    /**
     * 
     * Teardown after the entire unit test.
     * 
     */
    public function __destruct()
    {
    }
    
    /**
     * 
     * Setup before each method test.
     * 
     */
    public function setup()
    {
    }
    
    /**
     * 
     * Teardown after each method test.
     * 
     */
    public function teardown()
    {
    }
    
    /**
     * 
     * Asserts that a variable is boolean true.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertTrue($actual)
    {
        if ($actual !== true) {
            $this->fail(
                'Expected true, actually not-true',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a variable is not boolean true.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotTrue($actual)
    {
        if ($actual === true) {
            $this->fail(
                'Expected not-true, actually true',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a variable is boolean false.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertFalse($actual)
    {
        if ($actual !== false) {
            $this->fail(
                'Expected false, actually not-false',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a variable is not boolean false.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotFalse($actual)
    {
        if ($actual === false) {
            $this->fail(
                'Expected not-false, actually false',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a variable is PHP null.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNull($actual)
    {
        if ($actual !== null) {
            $this->fail(
                'Expected null, actually not-null',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a variable is not PHP null.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotNull($actual)
    {
        if ($actual === null) {
            $this->fail(
                'Expected not-null, actually null',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a object is an instance of a class.
     * 
     * @param object $actual The object to test.
     * 
     * @param string $expect The expected class name.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertInstance($actual, $expect)
    {
        if (! is_object($actual)) {
            $this->fail(
                'Expected object, actually ' . gettype($actual),
                array(
                    'actual' => $this->_export($actual),
                )
            );
        }
        
        if (! class_exists($expect, false)) {
            $this->fail(
                "Expected class '$expect' not loaded for comparison"
            );
        }
        
        if (!($actual instanceof $expect)) {
            $this->fail(
                "Expected instance of class '$expect', actually '" . get_class($actual) . "'"
            );
        }
        
        return true;
    }
    
    /**
     * 
     * Asserts that a object is not an instance of a class.
     * 
     * @param object $actual The object to test.
     * 
     * @param string $expect The non-expected class name.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotInstance($actual, $expect)
    {
        if (! is_object($actual)) {
            $this->fail(
                "Expected object, actually "  . gettype($actual),
                array(
                    'actual' => $this->_export($actual),
                )
            );
        }
        
        if (! class_exists($expect, false)) {
            $this->fail(
                "Expected class '$expect' not loaded for comparison"
            );
        }
        
        if ($actual instanceof $expect) {
            $this->fail(
                "Expected instance not-of class '$expect', actually is"
            );
        }
        
        return true;
    }
    
    /**
     * 
     * Asserts that two variables have the same type and value.
     * 
     * When used on objects, asserts the two variables are 
     * references to the same object.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The expected value.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertSame($actual, $expect)
    {
        if (is_array($actual)) {
            $this->_ksort($actual);
        }
        
        if (is_array($expect)) {
            $this->_ksort($expect);
        }
        
        if ($actual !== $expect) {
            $this->fail(
                'Expected same, actually not-same',
                array(
                    'actual' => $this->_export($actual),
                    'expect' => $this->_export($expect),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that two variables are not the same type and value.
     * 
     * When used on objects, asserts the two variables are not
     * references to the same object.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The non-expected result.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotSame($actual, $expect)
    {
        if (is_array($actual)) {
            $this->_ksort($actual);
        }
        
        if (is_array($expect)) {
            $this->_ksort($expect);
        }
        
        if ($actual === $expect) {
            $this->fail(
                'Expected not-same, actually same',
                array(
                    'actual' => $this->_export($actual),
                    'expect' => $this->_export($expect),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that two variables are equal; type is not strict.
     *
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The expected value.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertEquals($actual, $expect)
    {
        if (is_array($actual)) {
            $this->_ksort($actual);
        }
        
        if (is_array($expect)) {
            $this->_ksort($expect);
        }
        
        if ($actual != $expect) {
            $this->fail(
                'Expected equals, actually not-equals',
                array(
                    'actual' => $this->_export($actual),
                    'expect' => $this->_export($expect),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that two variables are not equal; type is not strict.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The expected value.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotEquals($actual, $expect)
    {
        if (is_array($actual)) {
            $this->_ksort($actual);
        }
        
        if (is_array($expect)) {
            $this->_ksort($expect);
        }
        
        if ($actual == $expect) {
            $this->fail(
                'Expected not-equals, actually equals',
                array(
                    'actual' => $this->_export($actual),
                    'expect' => $this->_export($expect),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that an object property meets criteria.
     * 
     * The object property may be public, protected, or private.
     * 
     * @param object $object The object to test.
     * 
     * @param string $property The property to inspect.
     * 
     * @param string $test The Solar_Test_Assert method to call.
     * 
     * @param mixed $expect The expected result from the test method.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertProperty($object, $property, $test, $expect = null)
    {
        if (! is_object($object)) {
            $this->fail("Expected object, actually " . gettype($object));
        }
        
        // introspect the object and look for the property
        $class = get_class($object);
        $found = false;
        $reflect = new ReflectionObject($object);
        foreach ($reflect->getProperties() as $prop) {
        
            // $val is a ReflectionProperty object
            $name = $prop->getName();
            if ($name != $property) {
                // skip it, not the one we're looking for
                continue;
            }
            
            // found the requested property
            $found = true;
            $copy = (array) $object;
            
            // get the actual value.  the null-char
            // trick for accessing protected and private
            // properties comes from Mike Naberezny.
            if ($prop->isPublic()) {
                $actual = $copy[$name];
            } elseif ($prop->isProtected()) {
                $actual = $copy["\0*\0$name"];
            } else {
                $actual = $copy["\0$class\0$name"];
            }
            
            // done
            break;
        }
        
        // did we find $object->$property?
        if (! $found) {
            $this->fail(
                "Did not find expected property '$property' " .
                "in object of class '$class'"
            );
        }
        
        // test the property value
        $method = 'assert' . ucfirst($test);
        return $this->$method($actual, $expect);
    }
    
    /**
     * 
     * Throws an exception indicating a failed test.
     * 
     * @param string $text The failed-test message.
     * 
     * @param array $info Additional info for the exception.
     * 
     * @return void
     * 
     */
    public function fail($text = null, $info = null)
    {
        throw Solar::factory('Solar_Test_Exception_Fail', array(
            'class' => get_class($this),
            'code'  => 'ERR_FAIL',
            'text'  => ($text ? $text : $this->locale('ERR_FAIL')),
            'info'  => $info,
        ));
    }
    
    /**
     * 
     * Throws an exception indicating an incomplete test.
     * 
     * @param string $text The incomplete-test message.
     * 
     * @param array $info Additional info for the exception.
     * 
     * @return void
     * 
     */
    public function todo($text = null, $info = null)
    {
        throw Solar::factory('Solar_Test_Exception_Todo', array(
            'class' => get_class($this),
            'code'  => 'ERR_TODO',
            'text'  => ($text ? $text : $this->locale('ERR_TODO')),
            'info'  => $info,
        ));
    }
    
    /**
     * 
     * Throws an exception indicating a skipped test.
     * 
     * @param string $text The skipped-test message.
     * 
     * @param array $info Additional info for the exception.
     * 
     * @return void
     * 
     */
    public function skip($text = null, $info = null)
    {
        throw Solar::factory('Solar_Test_Exception_Skip', array(
            'class' => get_class($this),
            'code'  => 'ERR_SKIP',
            'text'  => ($text ? $text : $this->locale('ERR_SKIP')),
            'info'  => $info,
        ));
    }
    
    /**
     * 
     * Returns the output from var_export() for a variable.
     * 
     * @param mixed $var The variable to run through var_export().
     * 
     * @return string
     * 
     */
    protected function _export($var)
    {
        return stripslashes(var_export($var, true));
    }
    
    /**
     * 
     * Recrsively [[php ksort()]] an array.
     * 
     * Used so that order of array elements does not affect equality.
     *
     * @param array $array The array to sort.
     * 
     * @return void
     * 
     */
    protected function _ksort(&$array)
    {
        ksort($array);
        foreach($array as $key => $val) {
            if (is_array($val)) {
                $this->_ksort($array[$key]);
            }
        }
    }
}
?>