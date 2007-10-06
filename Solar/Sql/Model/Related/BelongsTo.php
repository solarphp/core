<?php
class Solar_Sql_Model_Related_BelongsTo extends Solar_Sql_Model_Related {
    
    protected function _setType()
    {
        $this->type = 'belongs_to';
    }
    
    // foreign key is stored in the native model
    protected function _fixRelatedCol(&$opts)
    {
        $opts['native_col'] = $opts['foreign_key'];
    }
    
    /**
     * 
     * A support method for _fixRelated() to handle belongs-to relationships.
     * 
     * @param array &$opts The relationship options; these are modified in-
     * place.
     * 
     * @param StdClass $foreign The catalog entry for the foreign model.
     * 
     * @return void
     * 
     */
    protected function _setRelated($opts)
    {
        // the foreign column
        if (empty($opts['foreign_col'])) {
            // named by foreign primary key
            $this->foreign_col = $this->_foreign_model->primary_col;
        } else {
            $this->foreign_col = $opts['foreign_col'];
        }
        
        // the native column
        if (empty($opts['native_col'])) {
            // named by foreign table's suggested foreign_col name
            $this->native_col = $this->_foreign_model->foreign_col;
        } else {
            $this->native_col = $opts['native_col'];
        }
        
        // the fetch type
        if (empty($opts['fetch'])) {
            $this->fetch = 'one';
        } else {
            $this->fetch = $opts['fetch'];
        }
    }
}