<?php
/**
 * 
 * Greatly simplified one-dimensional array object.
 * 
 * @category Solar
 * 
 * @package Solar_Struct
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
 * Greatly simplified one-dimensional array object.
 * 
 * Using this class, you can access data using both array notation
 * ($foo['bar']) and object notation ($foo->bar).  This helps with 
 * moving data among form objects, view helpers, SQL objects, etc.
 * 
 * Once set at construction time, the data keys cannot be added to,
 * changed, or removed.
 * 
 * Examples:
 * 
 * <code type="php">
 * $data = array('foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir');
 * $struct = Solar::factory('Solar_Struct', array('data' => $data));
 * 
 * echo $struct['foo']; // 'bar'
 * echo $struct->foo;   // 'bar'
 * 
 * echo count($struct); // 3
 * 
 * foreach ($struct as $key => $val) {
 *     echo "$key=$val ";
 * } // foo=bar  baz=dib zim=gir
 * 
 * $struct->zim = 'irk';
 * echo $struct['zim']; // 'irk'
 * 
 * $struct->noSuchKey = 'nothing';
 * echo $struct->noSuchKey; // null
 * </code>
 * 
 * The one problem is that casting the object to an array will not
 * reveal the data; you'll get an empty array.  Instead, you should use
 * the toArray() method to get a copy of the object data.
 * 
 * <code type="php">
 * $data = array('foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir');
 * $object = Solar::factory('Solar_Struct', array('data' => $data));
 * 
 * $struct = (array) $object; // $struct = array();
 * 
 * $struct = $object->toArray(); // $struct = array('foo' => 'bar', ...)
 * </code>
 * 
 * @category Solar
 * 
 * @package Solar_Struct
 * 
 */
class Solar_Struct extends Solar_Base implements ArrayAccess, Countable, Iterator {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are:
     * 
     * : \\data\\ : (array) Key-value pairs.
     * 
     */
    protected $_config = array(
        'data' => array(),
    );
    
    /**
     * 
     * The row data in colname => value format.
     * 
     * @var array
     * 
     */
    protected $_data = array();
    
    /**
     * 
     * Countable: cached value for count().
     * 
     * @var int
     * 
     */
    protected $_count = null;
    
    /**
     * 
     * Iterator: is the current position valid?
     * 
     * @var array
     * 
     */
    protected $_valid = false;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_data = (array) $this->_config['data'];
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
        }
    }
    
    /**
     * 
     * Sets a key value; disallows setting of unknown keys.
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
        if (array_key_exists($key, $this->_data)) {
            $this->_data[$key] = $val;
        }
    }
    
    /**
     * 
     * Does a certain key exist in the data?
     * 
     * @param string $key The requested data key.
     * 
     * @param mixed $val The value to set the data to.
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
        $this->_data[$key] = null;
    }
    
    /**
     * 
     * Returns a copy of the object data as an array.
     * 
     * @return array
     * 
     */
    public function toArray()
    {
        return $this->_data;
    }
    
    /**
     * 
     * Loads the object data from an array.
     * 
     * @param array $data The data to load into the object.
     * 
     * @return void
     * 
     */
    public function load($data)
    {
        // get the *original* data keys
        $keys = array_keys($this->_config['data']);
        
        // if $data is a struct, convert to array
        if ($data instanceof Solar_Struct) {
            $data = $data->toArray();
        }
        
        // only set values for original keys
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $this->_data[$key] = $data[$key];
            }
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
        return array_key_exists($key, $this->_data);
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
     * ArrayAccess: unset a key (sets it to null).
     * 
     * @param string $key The requested key.
     * 
     * @return void
     * 
     */
    public function offsetUnset($key)
    {
        $this->__set($key, null);
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
        if (is_null($this->_count)) {
            $this->_count = count($this->_data);
        }
        return $this->_count;
    }
    
    /**
     * 
     * Iterator: get the current key value.
     * 
     * @return mixed
     * 
     */
    public function current()
    {
        return current($this->_data);
    }

    /**
     * 
     * Iterator: what is the key at the current position?
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
        $this->_valid = (next($this->_data) !== false);
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
        $this->_valid = (reset($this->_data) !== false);
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
        return $this->_valid;
    }
}
?>