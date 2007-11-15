<?php
/**
 * 
 * Represents the characteristics of a relationship where a native model
 * "has many" of a foreign model.  This includes "has many through" (i.e.,
 * a many-to-many relationship through an interceding mapping model).
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
class Solar_Sql_Model_Related_HasMany extends Solar_Sql_Model_Related {
    
    /**
     * 
     * Sets the relationship type.
     * 
     * @return void
     * 
     */
    protected function _setType()
    {
        $this->type = 'has_many';
    }
    
    /**
     * 
     * Fixes the related column names in the user-defined options **in place**.
     * 
     * The foreign key is stored in the **foreign** model.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _fixRelatedCol(&$opts)
    {
        $opts['foreign_col'] = $opts['foreign_key'];
    }
    
    /**
     * 
     * A support method for _fixRelated() to handle has-many relationships.
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
        // are we working through another relationship?
        if (! empty($opts['through'])) {
            // through another relationship, hand off to another method
            return $this->_setRelatedThrough($opts);
        }
        
        // the foreign column
        if (empty($opts['foreign_col'])) {
            // named by native table's suggested foreign_col name
            $this->foreign_col = $this->_native_model->foreign_col;
        } else {
            $this->foreign_col = $opts['foreign_col'];
        }
        
        // the native column
        if (empty($opts['native_col'])) {
            // named by native primary key
            $this->native_col = $this->_native_model->primary_col;
        } else {
            $this->native_col = $opts['native_col'];
        }
        
        // the fetch type
        if (empty($opts['fetch'])) {
            $this->fetch = 'all';
        } else {
            $this->fetch = $opts['fetch'];
        }
    }
    
    /**
     * 
     * A support method for _fixRelatedHasMany() to handle "through"
     * relationships.
     * 
     * @param array &$opts The relationship options; these are modified in-
     * place.
     * 
     * @param StdClass $foreign The catalog entry for the foreign model.
     * 
     * @return void
     * 
     */
    protected function _setRelatedThrough($opts)
    {
        // get the "through" relationship control
        $through = $this->_native_model->getRelated($opts['through']);
        $this->through = $opts['through'];
        
        // the foreign column
        if (empty($opts['foreign_col'])) {
            // named by foreign primary key (e.g., foreign.id)
            $this->foreign_col = $this->_foreign_model->primary_col;
        } else {
            $this->foreign_col = $opts['foreign_col'];
        }
        
        // the native column
        if (empty($opts['native_col'])) {
            // named by native primary key (e.g., native.id)
            $this->native_col = $this->_native_model->primary_col;
        } else {
            $this->native_col = $opts['native_col'];
        }
        
        // get the through-table
        if (empty($opts['through_table'])) {
            $this->through_table = $through->foreign_table;
        } else {
            $this->through_table = $opts['through_table'];
        }
        
        // get the through-alias
        if (empty($opts['through_alias'])) {
            $this->through_alias = $through->foreign_alias;
        } else {
            $this->through_alias = $opts['through_alias'];
        }
        
        // a little magic
        if (empty($opts['through_native_col']) &&
            empty($opts['through_foreign_col']) &&
            ! empty($opts['through_key'])) {
            // pre-define through_foreign_col
            $opts['through_foreign_col'] = $opts['through_key'];
        }
        
        // what's the native model key in the through table?
        if (empty($opts['through_native_col'])) {
            $this->through_native_col = $through->foreign_col;
        } else {
            $this->through_native_col = $opts['through_native_col'];
        }
        
        // what's the foreign model key in the through table?
        if (empty($opts['through_foreign_col'])) {
            $this->through_foreign_col = $this->_foreign_model->foreign_col;
        } else {
            $this->through_foreign_col = $opts['through_foreign_col'];
        }
        
        // the fetch type
        if (empty($opts['fetch'])) {
            $this->fetch = 'all';
        } else {
            $this->fetch = $opts['fetch'];
        }
    }
    
    /**
     * 
     * Special-case modification for selections of has-many-through records.
     * 
     * @param Solar_Sql_Select $select The selection object to modify.
     * 
     * @param Solar_Sql_Select|Solar_Sql_Model_Record $spec If a
     * Solar_Sql_Select, used as an "inner" select to find the correct native
     * IDs.  If a Solar_Sql_Model_Record, will find based on the ID of the
     * record.
     * 
     * @return void
     * 
     */
    protected function _modSelect($select, $spec)
    {
        // for non-through relationship, go with the parent method
        if (! $this->through) {
            return parent::_modSelect($select, $spec);
        }
        
        // more-complex 'has_many through' relationship.
        // join through the mapping table.
        $join_table = "{$this->through_table} AS {$this->through_alias}";
        $join_where = "{$this->foreign_alias}.{$this->foreign_col} = "
                    . "{$this->through_alias}.{$this->through_foreign_col}";
        
        $select->leftJoin($join_table, $join_where);
        
        // how to filter rows?
        if ($spec instanceof Solar_Sql_Model_Record) {
            // restrict to the related native column value in the "through" table
            $select->where(
                "{$this->through_alias}.{$this->through_native_col} = ?",
                $spec->{$this->native_col} // this is where we set the filtering clause
            );
        } else {
            // $spec is a Select object. restrict to a sub-select of IDs from
            // the native table.
            $clone = clone $spec;
            
            // sub-select **only** the native column, so that we're not
            // pulling back everything, just the part we need to join on.
            // this also helps SQLite, which is picky about fully-qualified
            // names in sub-selects.
            $clone->clear('cols');
            $disambig_col = $clone->disambiguate($this->native_alias, $this->native_col);
            $clone->cols($disambig_col);
            
            $inner = str_replace("\n", "\n\t\t", $clone->fetchSql());
            
            // add the native table ID at the top through a join
            $select->innerJoin(
                "($inner) AS {$this->native_alias}",
                "{$this->through_alias}.{$this->through_native_col} = {$this->native_alias}.{$this->native_col}",
                "{$this->native_col} AS {$this->native_alias}__{$this->native_col}"
            );
        }
        
        // select from the foreign table.
        $select->from(
            "{$this->foreign_table} AS {$this->foreign_alias}",
            $this->cols
        );
        
        // honor foreign inheritance
        if ($this->foreign_inherit_col) {
            $select->where(
                "{$this->foreign_alias}.{$this->foreign_inherit_col} = ?",
                $this->foreign_inherit_val
            );
        }
    }
}
