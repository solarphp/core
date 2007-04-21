<?php
/**
 * 
 * Represents multiple Solar_Sql_Row objects from a SELECT.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
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
 * Represents multiple Solar_Sql_Row objects from a SELECT.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Rowset extends Solar_Sql_Row {
    
    /**
     * 
     * The class of individual row objects.
     * 
     * @var string
     * 
     */
    protected $_row_class = 'Solar_Sql_Row';
    
    /**
     * 
     * Individual row objects.
     * 
     * @var array
     * 
     */
    protected $_rows = array();
    
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
        // don't return rows that don't exist in the original data
        if (empty($this->_data[$key])) {
            return null;
        }
        
        // load the row if needed
        if (empty($this->_rows[$key])) {
            $this->_rows[$key] = Solar::factory(
                $this->_row_class,
                array(
                    'data' => $this->_data[$key],
                    'save' => $this->_save,
                )
            );
        }
        
        // return the row
        return $this->_rows[$key];
    }
    
    /**
     * 
     * ArrayAccess: set a key value (not allowed).
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
    }
    
    /**
     * 
     * ArrayAccess: unset a key (not allowed).
     * 
     * @param string $key The requested key.
     * 
     * @return void
     * 
     */
    public function offsetUnset($key)
    {
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
        return $this->offsetGet($this->key());
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
        $array = array();
        foreach ($this as $row) {
            $array[] = $row->toArray();
        }
        return $array;
    }
    
    /**
     * 
     * Load new data (not allowed).
     * 
     * @param array $data An array of new data.
     * 
     * @param bool $reset Blank out the data array first so that only keys
     * in the $spec will be in the struct.
     * 
     * @return void
     * 
     */
    public function load($data, $reset = false)
    {
    }
}
