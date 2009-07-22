<?php
/**
 * 
 * Greatly simplified one-dimensional array object.
 * 
 * Using this class, you can access data using both array notation
 * ($foo['bar']) and object notation ($foo->bar).  This helps with 
 * moving data among form objects, view helpers, SQL objects, etc.
 * 
 * Examples ...
 * 
 * {{code: php
 *     $data = array('foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir');
 *     $struct = Solar::factory('Solar_Struct', array('data' => $data));
 *     
 *     echo $struct['foo']; // 'bar'
 *     echo $struct->foo;   // 'bar'
 *     
 *     echo count($struct); // 3
 *     
 *     foreach ($struct as $key => $val) {
 *         echo "$key=$val ";
 *     } // foo=bar  baz=dib zim=gir
 *     
 *     $struct->zim = 'irk';
 *     echo $struct['zim']; // 'irk'
 *     
 *     $struct->noSuchKey = 'nothing';
 *     echo $struct->noSuchKey; // null
 * }}
 * 
 * One problem is that casting the object to an array will not
 * reveal the data; you'll get an empty array.  Instead, you should use
 * the toArray() method to get a copy of the object data.
 * 
 * {{code: php
 *     $data = array('foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir');
 *     $object = Solar::factory('Solar_Struct', array('data' => $data));
 *     
 *     $struct = (array) $object; // $struct = array();
 *     
 *     $struct = $object->toArray(); // $struct = array('foo' => 'bar', ...)
 * }}
 * 
 * Another problem is that you can't use object notation inside double-
 * quotes directly; you need to wrap in braces.
 * 
 * {{code: php
 *     echo "$struct->foo";   // won't work
 *     echo "{$struct->foo}"; // will work
 * }}
 * 
 * A third problem is that you can't address keys inside a foreach() 
 * loop directly using array notation; you have to use object notation.
 * Originally reported by Antti Holvikari.
 * 
 * {{code: php
 *     // will not work
 *     foreach ($struct['subarray'] as $key => $val) { ... }
 *     
 *     // will work
 *     foreach ($struct->subarray as $key => $val) { ... }
 * }}
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Struct extends Solar_Base implements ArrayAccess, Countable, Iterator {
    
    /**
     * 
     * The struct is clean (no values have changed).
     * 
     * @const
     * 
     */
    const STATUS_CLEAN = 'clean';
    
    /**
     * 
     * The struct is dirty (at least one value has changed).
     * 
     * @const
     * 
     */
    const STATUS_DIRTY = 'dirty';
    
    /**
     * 
     * Default configuration values.
     * 
     * @config array data Key-value pairs.
     * 
     * @var array
     * 
     */
    protected $_Solar_Struct = array(
        'data' => array(),
    );
    
    /**
     * 
     * The keys/properties in name => value format.
     * 
     * @var array
     * 
     */
    protected $_data = array();
    
    /**
     * 
     * Iterator: is the current position valid?
     * 
     * @var array
     * 
     */
    protected $_iterator_valid = false;
    
    /**
     * 
     * The status of the struct (clean/dirty).
     * 
     * @var string
     * 
     */
    protected $_status = self::STATUS_CLEAN;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config Configuration value overrides, if any.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        if (is_array($this->_config['data'])) {
            $this->_data = $this->_config['data'];
        } else {
            $this->_data = array();
        }
        
        // set iterator validity
        if ($this->_data) {
            $this->_iterator_valid = true;
        } else {
            $this->_iterator_valid = false;
        }
    }
    
    /**
     * 
     * Gets a data value.
     * 
     * @param string $key The requested data key.
     * 
     * @return mixed The data value.
     * 
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        } else {
            throw $this->_exception('ERR_NO_SUCH_KEY', array(
                'class' => get_class($this),
                'key'   => $key,
                'keys'  => array_keys($this->_data),
            ));
        }
    }
    
    /**
     * 
     * Sets a key value and marks the struct as "dirty"; also marks all parent
     * structs as "dirty" too.
     * 
     * @param string $key The requested data key.
     * 
     * @param mixed $val The value to set the data to.
     * 
     * @return void
     * 
     */
    public function __set($key, $val)
    {
        // set the value and mark self as dirty
        $this->_data[$key] = $val;
        $this->setStatus(self::STATUS_DIRTY);
    }
    
    /**
     * 
     * Does a certain key exist in the data?
     * 
     * Note that this is slightly different from normal PHP isset(); it will
     * say the key is set, even if the key value is null or otherwise empty.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->_data);
    }
    
    /**
     * 
     * Sets a key in the data to null.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __unset($key)
    {
        // nullify and unset the vavlue, then mark as dirty
        $this->_data[$key] = null;
        unset($this->_data[$key]);
        $this->setStatus(self::STATUS_DIRTY);
    }
    
    /**
     * 
     * Returns a string representation of the object.
     * 
     * @return string
     * 
     * @see toString()
     * 
     */
    public function __toString()
    {
        return serialize($this->_data);
    }
    
    /**
     * 
     * Returns a string representation of the struct.
     * 
     * @return string
     * 
     */
    public function toString()
    {
        return $this->__toString();
    }
    
    /**
     * 
     * Returns a copy of the struct as an array, recursively descending to
     * convert child structs into arrays as well.
     * 
     * @return array
     * 
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->_data as $key => $val) {
            $data[$key] = $this->_toArray($val);
        }
        return $data;
    }
    
    /**
     * 
     * Support method for toArray().
     * 
     * @param mixed $value The value to convert to an array.
     * 
     * @return mixed
     * 
     */
    protected function _toArray($value)
    {
        if (is_array($value)) {
            // recursively process array values
            $result = array();
            foreach ($value as $key => $val) {
                $result[$key] = $this->_toArray($val);
            }
        } elseif ($value instanceof Solar_Struct) {
            // get Solar_Struct array
            $result = $value->toArray();
        } else {
            // non-array non-struct
            $result = $value;
        }
        return $result;
    }
    
    /**
     * 
     * Sets the status (clean/dirty/etc) on the struct.
     * 
     * @param string $status The status value.
     * 
     * @return void
     * 
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }
    
    /**
     * 
     * Returns the status (clean/dirty/etc) of the struct.
     * 
     * @return string The status value.
     * 
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * 
     * Has this structure been changed?
     * 
     * @return bool
     * 
     */
    public function isDirty()
    {
        return $this->_status == self::STATUS_DIRTY;
    }
    
    /**
     * 
     * Loads the struct with data from an array or another struct.
     * 
     * @param array|Solar_Struct $spec The data to load into the object.
     * 
     * @return void
     * 
     */
    public function load($spec)
    {
        // force to array
        if ($spec instanceof Solar_Struct) {
            // we can do this because $spec is of the same class
            $data = $spec->_data;
        } elseif (is_array($spec)) {
            $data = $spec;
        } else {
            $data = array();
        }
        
        // load new data, merging new values with old
        $this->_data = array_merge($this->_data, $data);
        
        // set iterator validity
        if ($this->_data) {
            $this->_iterator_valid = true;
        } else {
            $this->_iterator_valid = false;
        }
    }
    
    /**
     * 
     * Frees memory used by this struct.
     * 
     * @return void
     * 
     */
    public function free()
    {
        $this->_free($this->_data);
    }
    
    /**
     * 
     * Recursively descends and calls free() on child structs.
     * 
     * @param mixed $value The value to free.
     * 
     * @return void
     * 
     */
    protected function _free($value)
    {
        if (is_array($value)) {
            // recursively process array values
            foreach ($value as $key => $val) {
                $this->_free($val);
            }
        } elseif ($value instanceof Solar_Struct) {
            // recursively free child Solar_Struct objects
            $value->free();
        }
    }
    
    /**
     * 
     * ArrayAccess: does the requested key exist?
     * 
     * @param string $key The requested key.
     * 
     * @return bool
     * 
     */
    public function offsetExists($key)
    {
        return $this->__isset($key);
    }
    
    /**
     * 
     * ArrayAccess: get a key value.
     * 
     * @param string $key The requested key.
     * 
     * @return mixed
     * 
     */
    public function offsetGet($key)
    {
        return $this->__get($key);
    }
    
    /**
     * 
     * ArrayAccess: set a key value.
     * 
     * @param string $key The requested key.
     * 
     * @param string $val The value to set it to.
     * 
     * @return void
     * 
     */
    public function offsetSet($key, $val)
    {
        $this->__set($key, $val);
    }
    
    /**
     * 
     * ArrayAccess: unset a key.
     * 
     * @param string $key The requested key.
     * 
     * @return void
     * 
     */
    public function offsetUnset($key)
    {
        $this->__unset($key);
    }
    
    /**
     * 
     * Countable: how many keys are there?
     * 
     * @return int
     * 
     */
    public function count()
    {
        return count($this->_data);
    }
    
    /**
     * 
     * Iterator: get the current value for the array pointer.
     * 
     * @return mixed
     * 
     */
    public function current()
    {
        return $this->__get($this->key());
    }
    
    /**
     * 
     * Iterator: get the current key for the array pointer.
     * 
     * @return mixed
     * 
     */
    public function key()
    {
        return key($this->_data);
    }
    
    /**
     * 
     * Iterator: move to the next position.
     * 
     * @return void
     * 
     */
    public function next()
    {
        $this->_iterator_valid = (next($this->_data) !== false);
    }
    
    /**
     * 
     * Iterator: move to the first position.
     * 
     * @return void
     * 
     */
    public function rewind()
    {
        $this->_iterator_valid = (reset($this->_data) !== false);
    }
    
    /**
     * 
     * Iterator: is the current position valid?
     * 
     * @return void
     * 
     */
    public function valid()
    {
        return $this->_iterator_valid;
    }
}
