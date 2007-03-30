<?php
/**
 * 
 * Iterator aggregate for record properties.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Table.php 2093 2007-01-15 15:13:12Z pmjones $
 * 
 */

/**
 * 
 * Iterator aggregate for record properties.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 */
class Solar_Sql_Model_RecordIterator extends Solar_Base implements Iterator {
    
    /**
     * 
     * A reference to the data for iteration.
     * 
     * @var array
     * 
     */
    protected $_data;
    
    /**
     * 
     * The model from which the data originates.
     * 
     * @var Solar_Sql_Model
     * 
     */
    protected $_model;
    
    /**
     * 
     * Injects a reference to the iteration data.
     * 
     * @param array &$data The data to iterate through.
     * 
     * @return void
     */
    public function setData(&$data)
    {
        $this->_data =& $data;
    }
    
    /**
     * 
     * Injects the model from which the data originates.
     * 
     * @param Solar_Sql_Model The origin model object.
     * 
     * @return void
     */
    public function setModel(Solar_Sql_Model $model)
    {
        $this->_model = $model;
    }
    
    /**
     * 
     * Gets the current key value.
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
     * What is the key at the current position?
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
     * Moves to the next position.
     * 
     * @return void
     * 
     */
    public function next()
    {
        return next($this->_data);
    }

    /**
     * 
     * Moves to the first position.
     * 
     * @return void
     * 
     */
    public function rewind()
    {
        return reset($this->_data);
    }

    /**
     * 
     * Is the current position valid?
     * 
     * @return void
     * 
     */
    public function valid()
    {
        return $this->current() !== false;
    }
}