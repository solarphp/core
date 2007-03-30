<?php
/**
 * 
 * Iterator aggregate for record collections.
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
 * Iterator aggregate for record collections.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 */
class Solar_Sql_Model_CollectionIterator extends Solar_Sql_Model_RecordIterator {
    
    /**
     * 
     * Returns the current record from the collection.
     * 
     * @return Solar_Sql_Model A model with a focus on one record.
     * 
     */
    public function current()
    {
        return $this->_model->offsetGet($this->key());
    }
}