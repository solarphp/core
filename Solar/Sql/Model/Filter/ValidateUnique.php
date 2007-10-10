<?php
/**
 * 
 * Validates that a value for the current data key is unique among all
 * model records of its inheritance type.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
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
 * Validates that a value for the current data key is unique among all
 * model records of its inheritance type.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 */
class Solar_Sql_Model_Filter_ValidateUnique extends Solar_Filter_Abstract {
    
    /**
     * 
     * Validates that a value for the current data key is unique among all
     * model records of its inheritance type.
     * 
     * This will exclude any record having the same primary-key value as the
     * current record.
     * 
     * {{code: php
     *     $where = array(
     *         'id != :id', // or 'id IS NOT NULL' if the ID is null
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
        // make sure the $where is an array
        settype($where, 'array');
        
        // get the record (data) model
        $model = $this->_filter->getData()->getModel();
        
        // what is the primary-key column for the record model?
        $primary = $model->primary_col;
        
        // exclude the current record by its primary key value
        if ($this->_filter->getData($primary) === null) {
            $where[] = "$primary IS NOT NULL";
        } else {
            $where[] = "$primary != :$primary";
        }
        
        // base condition to check for uniqueness on the current column.
        // added conditions already exist in the "where"
        $key = $this->_filter->getDataKey();
        $where[] = "$key = :$key";
        
        // see if we can fetch a row, with only the primary-key column to
        // reduce resource usage.
        $result = $model->fetchValue(array(
            'where' => $where,
            'cols'  => array($primary),
            'bind'  => $this->_filter->getData()->toArray(),
        ));
        
        // if empty, no result was returned, so the value is unique.
        return empty($result);
    }
}