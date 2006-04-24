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
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: Assert.php 1041 2006-04-04 15:12:36Z pmjones $
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
abstract class Solar_Test extends Solar_Base {
    
    /**
     * 
     * Setup before the entire unit test.
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
     * Assert that a variable is boolean true.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertTrue($actual)
    {
        if ($actual !== true) {
            $this->_fail(
                'Expected true, actually not-true',
                array(
                    'actual' => var_export($actual, true),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Assert that a variable is not boolean true.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertNotTrue($actual)
    {
        if ($actual === true) {
            $this->_fail(
                'Expected not-true, actually true',
                array(
                    'actual' => var_export($actual, true),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Assert that a variable is boolean false.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertFalse($actual)
    {
        if ($actual !== false) {
            $this->_fail(
                'Expected false, actually not-false',
                array(
                    'actual' => var_export($actual, true),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Assert that a variable is not boolean false.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertNotFalse($actual)
    {
        if ($actual === false) {
            $this->_fail(
                'Expected not-false, actually false',
                array(
                    'actual' => var_export($actual, true),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Assert that a variable is PHP null.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertNull($actual)
    {
        if ($actual !== null) {
            $this->_fail(
                'Expected null, actually not-null',
                array(
                    'actual' => var_export($actual, true),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Assert that a variable is not PHP null.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertNotNull($actual)
    {
        if ($actual === null) {
            $this->_fail(
                'Expected not-null, actually null',
                array(
                    'actual' => var_export($actual, true),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Assert that a object is an instance of a class.
     * 
     * @param object $actual The object to test.
     * 
     * @param string $expect The expected class name.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertInstance($actual, $expect)
    {
        if (! is_object($actual)) {
            $this->_fail(
                'Expected object, actually ' . gettype($actual),
                array(
                    'actual' => var_export($actual, true),
                )
            );
        }
        
        if (! class_exists($expect, false)) {
            $this->_fail(
                "Expected class '$expect' not loaded for comparison"
            );
        }
        
        if (!($actual instanceof $expect)) {
            $this->_fail(
                "Expected instance of class '$expect', actually not",
                array(
                    'actual' => var_export($actual, true),
                )
            );
        }
        
        return true;
    }
    
    /**
     * 
     * Assert that a object is not an instance of a class.
     * 
     * @param object $actual The object to test.
     * 
     * @param string $expect The non-expected class name.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertNotInstance($actual, $expect)
    {
        if (! is_object($actual)) {
            $this->_fail(
                "Expected object, actually "  . gettype($actual),
                array(
                    'actual' => var_export($actual, true),
                )
            );
        }
        
        if (! class_exists($expect, false)) {
            $this->_fail(
                "Expected class '$expect' not loaded for comparison"
            );
        }
        
        if ($actual instanceof $expect) {
            $this->_fail(
                "Expected instance not-of class '$expect', actually is",
                array(
                    'actual' => var_export($actual, true),
                )
            );
        }
        
        return true;
    }
    
    /**
     * 
     * Assert that two variables are the same.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The expected result.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertSame($actual, $expect)
    {
        if ($actual !== $expect) {
            $this->_fail(
                'Expected same, actually not-same',
                array(
                    'expect' => var_export($expect, true),
                    'actual' => var_export($actual, true),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Assert that two variables are not the same.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The non-expected result.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertNotSame($actual, $expect)
    {
        if ($actual === $expect) {
            $this->_fail(
                'Expected not-same, actually same',
                array(
                    'expect' => var_export($expect, true),
                    'actual' => var_export($actual, true),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Assert that two variables are equal.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The expected result.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertEquals($actual, $expect)
    {
        $exp = serialize($expect);
        $act = serialize($actual);
        
        if ($exp != $act) {
            $this->_fail(
                'Expected equals, actually not-equals',
                array(
                    'expect' => var_export($expect, true),
                    'actual' => var_export($actual, true),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Assert that two variables are not equal.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The expected result.
     * 
     * @return bool The assertion result.
     * 
     */
    protected function _assertNotEquals($actual, $expect)
    {
        $exp = serialize($expect);
        $act = serialize($actual);
        
        if ($exp == $act) {
            $this->_fail(
                'Expected not-equals, actually equals',
                array(
                    'expect' => var_export($expect, true),
                    'actual' => var_export($actual, true),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Assert that an object property meets criteria.
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
    protected function _assertProperty($object, $property, $test, $expect = null)
    {
        if (! is_object($object)) {
            $this->_fail("Expected object, actually " . gettype($object));
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
            $this->_fail(
                "Did not find expected property '$property' " .
                "in object of class '$class'"
            );
        }
        
        // test the property value
        $method = '_assert' . ucfirst($test);
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
    protected function _fail($text = null, $info = null)
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
    protected function _todo($text = null, $info = null)
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
    protected function _skip($text = null, $info = null)
    {
        throw Solar::factory('Solar_Test_Exception_Skip', array(
            'class' => get_class($this),
            'code'  => 'ERR_SKIP',
            'text'  => ($text ? $text : $this->locale('ERR_SKIP')),
            'info'  => $info,
        ));
    }
}
?>