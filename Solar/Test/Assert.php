<?php
/**
 * 
 * Class for simple unit-testing assertions.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Class for simple unit-testing assertions.
 * 
 * If the assertion is successful, it returns boolean true with no
 * output.  If the assertion fails, it throws and prints an exception, 
 * then returns false.
 * 
 * This is effectively the same behavior as in Greg Beaver's 
 * PEAR_PHPTest class, but uses exceptions instead of PEAR_Error 
 * and debug_backtrace().
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 */
class Solar_Test_Assert {
    
    /**
     * 
     * The label for messages.
     * 
     * @var string
     * 
     */
    protected $_label;
    
    /**
     * 
     * Sets the label for the next message.
     * 
     * @param string $label The message label.
     * 
     * @return void
     * 
     */
    public function setLabel($label)
    {
        $this->_label = $label;
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
    public function isTrue($actual)
    {
        if ($actual !== true) {
            return $this->fail(
                "Expected true, actually " . var_export($actual, true)
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
    public function notTrue($actual)
    {
        if ($actual === true) {
            return $this->fail(
                "Expected non-true, actually " . var_export($actual, true)
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
    public function isFalse($actual)
    {
        if ($actual !== false) {
            return $this->fail(
                "Expected false, actually " . var_export($actual, true)
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
    public function notFalse($actual)
    {
        if ($actual === false) {
            return $this->fail(
                "Expected non-false, actually " . var_export($actual, true)
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
    public function isNull($actual)
    {
        if ($actual !== null) {
            return $this->fail(
                "Expected null, actually " . var_export($actual, true)
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
    public function notNull($actual)
    {
        if ($actual === null) {
            return $this->fail(
                "Expected non-null, actually " . var_export($actual, true)
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
    public function isInstance($actual, $expect)
    {
        if (! is_object($actual)) {
            return $this->fail(
                "Expected object, actually " . var_export($actual, true)
            );
        }
        
        if (! class_exists($expect, false)) {
            return $this->fail(
                "Expected class '$expect' not loaded for comparison"
            );
        }
        
        if (!($actual instanceof $expect)) {
            return $this->fail(
                "Expected object of class '$expect', actually '" . get_class($actual) . "'"
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
    public function notInstance($actual, $expect)
    {
        if (! is_object($actual)) {
            return $this->fail(
                "Expected object, actually ",
                $actual
            );
        }
        
        if (! class_exists($expect, false)) {
            return $this->fail(
                "Expected class '$expect' not loaded for comparison"
            );
        }
        
        if ($actual instanceof $expect) {
            return $this->fail(
                "Expected object not of class '$expect', actually '" . get_class($actual) . "'"
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
    public function same($actual, $expect)
    {
        if ($actual !== $expect) {
            return $this->fail(
                "Expected same: " . var_export($expect, true) .
                "\nActually not same: " . var_export($actual, true)
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
    public function notSame($actual, $expect)
    {
        if ($actual === $expect) {
            return $this->fail(
                "Expected not same: " . var_export($expect, true) .
                "\nActually same: " . var_export($actual, true)
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
    public function equals($actual, $expect)
    {
        $exp = serialize($expect);
        $act = serialize($actual);
        
        if ($exp != $act) {
            return $this->fail(
                "Expected equals: " . var_export($expect, true) .
                "\nActually not equals: " . var_export($actual, true)
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
    public function notEquals($actual, $expect)
    {
        $exp = serialize($expect);
        $act = serialize($actual);
        
        if ($exp == $act) {
            return $this->fail(
                "Expected not equals: " . var_export($expect, true) .
                "\nActually equals: " . var_export($actual, true)
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
     * @param string $method The Solar_Test_Assert method to call.
     * 
     * @param mixed $expect The expected result from the test method.
     * 
     * @return bool The assertion result.
     * 
     */
    public function property($object, $property, $method, $expect = null)
    {
        if (! is_object($object)) {
            return $this->fail("Expected object, actually " . var_export($object));
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
            return $this->fail(
                "Did not find expected property '$property' " .
                "in object of class '$class'"
            );
        }
        
        // test the property value
        return $this->$method($actual, $expect);
    }
    
    /**
     * 
     * Throws and prints an exception indicating assertion failure.
     * 
     * @param string $message The failure message.
     * 
     * @return bool Always returns boolean false.
     * 
     */
    public function fail($message = 'Failure')
    {
        try {
            throw new Exception($message);
        } catch (Exception $e) {
            if ($this->_label) {
                echo $this->_label . "\n";
            }
            $this->_label = null;
            echo $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n\n";
            return false;
        }
    }
}
?>