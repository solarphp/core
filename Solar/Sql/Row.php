<?php
/**
 * 
 * Class to represent values of a single row result as both array and object.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id: Cache.php 899 2006-03-20 17:40:25Z pmjones $
 * 
 */

/**
 * 
 * Class to represent values of a single row result as both array and object.
 * 
 * This does not implement any way to connect back to the database,
 * it only makes the row values available to the user.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Row extends Solar_Base implements ArrayAccess {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are:
     * 
     * : \\data\\ : (array) The array of column names and values.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'data' => null,
    );
    
    /**
     * 
     * A sequential array of column names.
     * 
     * @var array
     * 
     */
    protected $_cols;
    
    /**
     * 
     * Constructor.
     * 
     * @param mixed $config User-defined configuration.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        settype($this->_config['data'], 'array');
        $this->_cols = array_keys($this->_config['data']);
        $this->import($this->_config['data']);
    }
    
    /**
     * 
     * Sets a column value.
     * 
     * Fails silently if you try to set a column that doesn't exist.
     * 
     * @param string $key The column to set.
     * 
     * @param mixed $val The value for the column.  Casts all
     * values to strings.
     * 
     * @return void
     * 
     */
    public function __set($key, $val)
    {
        // disallows setting of columns that don't exist
        if (in_array($key, $this->_cols)) {
            $this->$key = $val;
        }
    }
    
    /**
     * 
     * Imports a data array; the data must be in column => value format.
     * 
     * @param array $data The array of column => value elements.
     * 
     * @return void
     * 
     */
    public function import($data)
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }
    
    /**
     * 
     * Checks to see if a column name exists.
     * 
     * @return bool
     * 
     */
    public function offsetExists($key)
    {
        return in_array($key, $this->_cols);
    }
    
    /**
     * 
     * Returns the value of a column.
     * 
     * @return string
     * 
     */
    public function offsetGet($key)
    {
        return $this->$key;
    }
    
    /**
     * 
     * Sets the value of a column.
     * 
     * @param string $key The column to set.
     * 
     * @param mixed $val The value for the column.  Casts all
     * values to strings.
     * 
     * @return void
     * 
     */
    public function offsetSet($key, $val)
    {
        $this->$key = $val;
    }
    
    /**
     * 
     * Sets a column value to null.
     * 
     * @return void
     * 
     */
    public function offsetUnset($key)
    {
        $this->$key = null;
    }
}
?>