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
 * Extends the Solar_Sql_Row class, and contains Solar_Sql_Row objects.
 */
Solar::loadClass('Solar_Sql_Row');

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
     * Instantiated Solar_Sql_Row objects.
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
        if (empty($this->_rows[$key])) {
            $this->_rows[$key] = Solar::factory(
                'Solar_Sql_Row',
                array(
                    'data' => $this->_data[$key],
                    'save' => $this->_save,
                )
            );
        }
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
     * @return void
     * 
     */
    public function load($data)
    {
    }
}
?>