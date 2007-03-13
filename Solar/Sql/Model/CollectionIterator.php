<?php
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