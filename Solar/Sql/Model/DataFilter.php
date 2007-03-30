<?php
/**
 * 
 * Collection of external data filter methods for Solar_Sql_Model classes.
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
 * Collection of external data filter methods for Solar_Sql_Model classes.
 * 
 * This filter class is an unmodified extension of Solar_DataFilter; you may
 * extend it for your own models.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 */
class Solar_Sql_Model_DataFilter extends Solar_DataFilter {
    
    protected $_data;
    
    protected $_data_key;
    
    protected $_model;
    
    protected $_catalog;
    
    /**
     * 
     * Injects the model from which the data originates.
     * 
     * @param Solar_Sql_Model $model The origin model object.
     * 
     * @return void
     * 
     */
    public function setModel(Solar_Sql_Model $model)
    {
        $this->_model = $model;
        $catalog = Solar::factory('Solar_Sql_Model_Catalog');
        $this->_catalog = $catalog->get($model);
    }
    
    /**
     * 
     * Validates and sanitizes $this->_data from the filter parameters 
     * stored in $this->_catalog->_filters.
     * 
     * @param array &$data The data array to filter.  Note that sanitizing
     * methods will modify the data in-place.
     * 
     * @return array An array of which data keys failed validation; in this
     * case, no news is good news.
     * 
     */
    public function process(&$data)
    {
        // keep a data reference
        $this->_data =& $data;
        
        // validation failure messages
        $invalid = array();
        
        // loop through the data array as column-value pairs.
        // note that we do so by reference, so that sanitizations
        // automatically go back to the data array.
        foreach ($this->_data as $key => &$val) {
            
            if (empty($this->_catalog->_filters[$key])) {
                // no filters on this column
                continue;
            } elseif (empty($this->_catalog->_table_cols[$key])) {
                // column not in the table; what the ... ?
                continue;
            } else {
                // easy reference to the column information
                $col = $this->_catalog->_table_cols[$key];
            }
            
            // keep track of which column we're working with
            $this->_data_key = $key;
            
            // is the column required?
            if ($col['autoinc']) {
                // auto-increments are never required as far as filtering is
                // concerned; the database will add it automatically.  they
                // only have to be validated and sanitized if they already
                // exist.
                $this->setRequire(false);
            } else {
                // honor the 'require' value for the column.
                $this->setRequire($col['require']);
            }
            
            // apply filters
            foreach ($this->_catalog->_filters[$key] as $params) {
                
                // take the method name off the top of the params ...
                $method = array_shift($params);
                
                // ... and put the value in its place.
                array_unshift($params, $val);
                
                // call the filtering method
                $result = call_user_func_array(
                    array($this, $method),
                    $params
                );
                
                // did the filter sanitize, or did it validate?
                $type = strtolower(substr($method, 0, 8));
                
                // what to do with the result?
                if ($type == 'sanitize') {
                    // retain the sanitized value
                    $val = $result;
                } elseif ($type == 'validate' && ! $result) {
                    // a validation method failed; use the method name as
                    // the locale translation key, converting from camelCase
                    // to camel_Case, then to CAMEL_CASE.
                    $tmp = preg_replace('/([a-z])([A-Z])/', '$1_$2', $method);
                    $tmp = strtoupper($tmp);
                    $invalid[$key] = $this->locale($tmp);
                    // no more validations on this key
                    break;
                }
            }
        }
        
        // all done
        return $invalid;
    }
    
    /**
     * 
     * Validates that a value for the current data key is unique among all
     * model records of its inheritance type.
     * 
     * The default $where clause will exclude any record having the same 
     * primary-key value as the current record.
     * 
     * {{code: php
     *     $where = array(
     *         'id != :id',
     *     );
     * }}
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $where Additional "WHERE" conditions to exclude records
     * from the uniqueness check.
     * 
     * @return bool True if unique, false if not.
     * 
     */
    public function validateUnique($value, $where = null)
    {
        // what is the primary-key column for the current model?
        $primary = $this->_catalog->_primary_col;
        
        // make sure the $where is an array for later
        settype($where, 'array');
        
        // do we have an exclusionary condition yet?
        if (! $where) {
            // set a base condition to exclude the current record
            $where[] = "$primary != :$primary";
        }
        
        // add a condition to check for uniqueness on the current column.
        // we don't use $value because, technically, $value is the same thing
        // as $this->_data[$this->_data_key], which will get bound into the
        // fetchOne() parameters below.
        $where[] = "{$this->_data_key} = :{$this->_data_key}";
        
        // get a new model class in "master" focus.
        $model = Solar::factory(get_class($this->_model));
        
        // see if we can fetch a row, with only the primary-key column to
        // reduce resource usage.
        $row = $model->fetchOne(array(
            'where' => $where,
            'cols'  => array($primary),
            'bind'  => $this->_data,
        ));
        
        // if empty, no row was returned, so the value is unique.
        return empty($row);
    }
    
    /**
     * 
     * Validates that the "confirmation" value is the same as the "real"
     * value being confirmed.
     * 
     * Useful for checking that the user entered the same password twice, or
     * the same email twice, etc.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $confirm_key Check against the value of this element in
     * $this->_data.  If $this->_data[$confirm_key] does not exist, the
     * validation will *pass*.  When empty, defaults to the current data
     * col being processed, with suffix '_confirm'.
     * 
     * @return bool True if the values are the same or if the $confirm_key
     * is not in the data being processed. False if the values are not the
     * same.
     * 
     */
    public function validateConfirm($value, $confirm_key = null)
    {
        if (! $confirm_key) {
            $confirm_key = $this->_data_key . '_confirm';
        }
        
        if (array_key_exists($confirm_key, $this->_data)) {
            return (bool) $value == $this->_data[$confirm_key];
        } else {
            return true;
        }
    }
}