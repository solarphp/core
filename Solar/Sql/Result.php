<?php
/**
 * 
 * Class for iterating through selected row results.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Class for fetching selected row results.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Result extends Solar_Base implements Iterator {
    
    /**
     * 
     * User-defined configuration keys.
     * 
     * Keys are:
     * 
     * : \\PDOStatement\\ : (object) A PDOStatement object to be used as the
     * result source.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'PDOStatement' => null,
    );
    
    /**
     * 
     * The PDOStatement being used as a result source.
     * 
     * @var PDOStatement
     * 
     */
    protected $_stmt = null;
    
    /**
     * 
     * Collection of rows fetched from the result source.
     * 
     * Each element in the array is a Solar_Sql_Row object.
     * 
     * @var array
     * 
     */
    protected $_rows = array();
    
    protected $_assoc = array();
    
    /**
     * 
     * Pointer to the current iteration.
     * 
     * @var int
     * 
     */
    protected $_curr = null;
    
    /**
     * 
     * Have we filled Solar_Sql_Result::$_rows with all results?
     * 
     * @var bool
     * 
     */
    protected $_full = false;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        if (! ($this->_config['PDOStatement'] instanceof PDOStatement)) {
            throw $this->_exception('ERR_NOT_PDOSTATEMENT');
        }
        $this->_stmt = $this->_config['PDOStatement'];
    }
    
    /**
     * 
     * Rewinds the iterator back to the beginning.
     * 
     * @return void
     * 
     */
    public function rewind()
    {
        $this->_curr = 0;
    }
    
    /**
     * 
     * Returns the current iterator key.
     * 
     * @return int The current iterator key.
     * 
     */
    public function key()
    {
        return $this->_curr;
    }
    
    /**
     * 
     * Increments the iterator key.
     * 
     * @return int The incremented iterator key.
     * 
     * @todo Should this fetch a row too?
     * 
     */
    public function next()
    {
        // increment the counter
        $this->_curr += 1;
        
        // populate the row
        $this->_fetch();
        
        // return the incremented value
        return $this->_curr;
    }
    
    /**
     * 
     * Determines if the current iterator key is valid.
     * 
     * Also populates the current row from the result source.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function valid()
    {
        // do we already have a row in the current position?
        if (! empty($this->_rows[$this->_curr])) {
            return true;
        } else {
            // populate the current position
            return $this->_fetch();
        }
    }
    
    /**
     * 
     * Returns the current row for the iterator.
     * 
     * @return Solar_Sql_Row
     * 
     */
    public function current()
    {
        return $this->_rows[$this->_curr];
    }
    
    /**
     * 
     * Returns the next row from the result source.
     * 
     * @return Solar_Sql_Row|bool Boolean false if there is no
     * next row, or the next Solar_Sql_Row result.
     * 
     */
    public function fetch()
    {
        if ($this->valid()) {
            $row = $this->_rows[$this->_curr];
            $this->next();
            return $row;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Returns all rows from the result source.
     * 
     * @return array An array of Solar_Sql_Row objects.
     * 
     */
    public function fetchAll($assoc = false)
    {
        if (! $this->_full) {
            // populate all of $_rows
            foreach ($this as $val) {}
        }
        
        // return as associative on the first column?
        if ($assoc) {
            $rows = array();
            foreach ($this->_assoc as $key => $val) {
                $rows[$val] = $this->_rows[$key];
            }
            return $rows;
        }
        
        // return as sequential
        return $this->_rows;
    }
    
    /**
     * 
     * Support method to populate Solar::$_rows from the result source.
     * 
     * @return bool True if a row was populated, false if not.
     * 
     */
    protected function _fetch()
    {
        // is there a next row?
        $data = $this->_stmt->fetch(PDO::FETCH_ASSOC);
        if (! $data) {
            // no new rows, which means the $_rows array
            // must be fully populated.
            $this->_full = true;
            return false;
        }
        
        // found a row, retain it internally
        $this->_rows[$this->_curr] = Solar::factory(
            'Solar_Sql_Row',
            array('data' => $data)
        );
        
        // set the associative key for it
        $this->_assoc[$this->_curr] = array_shift($data);
        
        // done
        return true;
    }
    
}
?>