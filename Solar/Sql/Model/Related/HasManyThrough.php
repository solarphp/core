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
 * @version $Id: HasMany.php 3617 2009-02-16 19:47:30Z pmjones $
 * 
 */
class Solar_Sql_Model_Related_HasManyThrough extends Solar_Sql_Model_Related_ToMany
{

    /**
     * 
     * The relationship name through which we find foreign records.
     * 
     * @var string
     * 
     */
    public $through;
    
    /**
     * 
     * The "through" table name.
     * 
     * @var string
     * 
     */
    public $through_table;
    
    /**
     * 
     * The "through" table alias.
     * 
     * @var string
     * 
     */
    public $through_alias;
    
    /**
     * 
     * In the "through" table, the column that has the matching native value.
     * 
     * @var string
     * 
     */
    public $through_native_col;
    
    /**
     * 
     * In the "through" table, the column that has the matching foreign value.
     * 
     * @var string
     * 
     */
    public $through_foreign_col;
    
    /**
     * 
     * The virtual element `through_key` automatically 
     * populates the 'through_foreign_col' value for you.
     * 
     * @var string.
     * 
     */
    public $through_key;
    
    /**
     * 
     * Modifies the base select statement for the relationship type.
     * 
     * @param Solar_Sql_Select $select The selection object to modify.
     * 
     * @param Solar_Sql_Model_Record|array $spec find based on the ID of the
     * record.
     * 
     * @return void
     * 
     */
    protected function _modSelectRelatedToRecord($select, $spec)
    {
        $this->_modSelectAddThrough($select);
        
        // restrict to the related native column value in the "through" table
        $select->where(
            "{$this->through_alias}.{$this->through_native_col} = ?",
            $spec[$this->native_col] // this is where we set the filtering clause
        );
    }
    
    /**
     * 
     * Modifies the base select statement for the relationship type.
     * 
     * @param Solar_Sql_Select $select The selection object to modify.
     * 
     * @param Solar_Sql_Model_Collection $spec A set of records to fetch
     * related records for
     * 
     * @return void
     * 
     */
    protected function _modSelectRelatedToCollection($select, $spec, $parent_col = NULL)
    {
        $this->_modSelectAddThrough($select, $parent_col);
        
        // Restrict to the set of IDs in the driving collection
        $keys = $spec->getPrimaryVals($this->native_col);
        
        // be nice and only use unique values
        $keys = array_unique($keys);
        
        // how many are there?
        $num_keys = count($keys);
        if ($num_keys == 0) {
            // We are too far down to stop the SELECT from being issued, but
            // we can give a big fat hint to the SQL optimizer
            $select->where('FALSE');
        } else if ($num_keys == 1) {
            $select->where(
                "{$this->through_alias}.{$this->through_native_col} = ?",
                $keys[0]
            );
        } else {
            $select->where(
                "{$this->through_alias}.{$this->through_native_col} IN (?)",
                $keys
            );
        }
    }
    
    /**
     * 
     * Modifies the base select statement for the relationship type.
     * 
     * @param Solar_Sql_Select $select The selection object to modify.
     * 
     * @param Solar_Sql_Select $spec used as an "inner" select to find the 
     * correct native IDs.
     * 
     * @return void
     * 
     */
    protected function _modSelectRelatedToSelect($select, $spec, $parent_alias, $parent_col = NULL)
    {
        $this->_modSelectAddThrough($select, $parent_col);
        
        // $spec is a Select object. restrict to a sub-select of IDs from
        // the native table.
        $clone = clone $spec;
        
        // We don't care about eager fetching in this result set
        $clone->clearOptionalEager();
        
        // sub-select **only** the native column, so that we're not
        // pulling back everything, just the part we need to join on.
        // SQLite needs the explicit "AS" here.
        // <http://osdir.com/ml/db.sqlite.general/2003-05/msg00228.html>
        $clone->clear('cols');
        $primary_col = "{$parent_alias}.{$this->native_col} AS {$this->native_col}";
        $clone->cols($primary_col);
        
        $inner = str_replace("\n", "\n\t\t", $clone->fetchSql());
        
        // add the native table ID at the top through a join
        $select->innerJoin(
            "($inner) AS {$parent_alias}",
            "{$this->through_alias}.{$this->through_native_col} = {$parent_alias}.{$this->native_col}"
        );
    }
    
    /**
     * 
     * When the native model is doing a select and an eager-join is requested
     * for this relation, this method modifies the select to add the eager
     * join.
     * 
     * **Does not** add the foreign columns to the select, because that would
     * result in really large result tables. Note that we fetch rows from the
     * has-many relation separately, so not adding columns here is OK.
     * 
     * @param Solar_Sql_Select $select The SELECT to be modified.
     * 
     * @param array $options options controlling eager selection
     * 
     * @return void The SELECT is modified in place.
     * 
     */
    public function modSelectEager($select, $parent_alias, $options = array())
    {
        $options = $this->_fixEagerOptions($options);
        if (!$options['require_related']) {
            // for client side joins, we do not modify the select
            return;
        }
        
        // join through the mapping table.
        $join_table = "{$this->through_table} AS {$this->through_alias}";
        $join_where = "{$parent_alias}.{$this->native_col} = "
                    . "{$this->through_alias}.{$this->through_native_col}";
                    
        $select->innerJoin($join_table, $join_where);
        
        $join_table = "{$this->foreign_table} AS {$this->foreign_alias}";
        $join_where = "{$this->through_alias}.{$this->through_foreign_col} = "
                    . "{$this->foreign_alias}.{$this->foreign_col}";
                    
        $select->innerJoin($join_table, $join_where);
        
        // added where conditions for the join
        $select->multiWhere($options['where']);
        $select->multiWhere($this->where);
        
        // make the rows distinct, so we only get one row regardless of
        // the number of related rows (since we're not selecting cols).
        $select->distinct(true);
        
        // don't chain because we're not fetching
    }
    
    /**
     * 
     * Add our through table to a select
     * 
     * @param Solar_Sql_Select $select The selection object to modify.
     * 
     * @return void
     * 
     */
    protected function _modSelectAddThrough($select, $parent_col = NULL)
    {
        // join through the mapping table.
        $join_table = "{$this->through_table} AS {$this->through_alias}";
        $join_where = "{$this->foreign_alias}.{$this->foreign_col} = "
                    . "{$this->through_alias}.{$this->through_foreign_col}";
                    
        // Add a column so that we know what parent we are joining to
        if ($parent_col) {
            $join_col = "{$this->through_native_col} AS {$parent_col}";
        } else {
            $join_col = NULL;
        }
        
        $select->leftJoin($join_table, $join_where, $join_col);
    }
    
    /**
     * 
     * Sets the relationship type.
     * 
     * @return void
     * 
     */
    protected function _setType()
    {
        $this->type = 'has_many_through';
    }
    
    /**
     * 
     * Corrects the foreign_key value in the options; uses the native-model
     * table name as singular when a regular has-many, and the foreign-
     * model primary column as-is when a 'has-many through'.
     * 
     * @param array &$opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _fixForeignKey(&$opts)
    {
        $opts['foreign_key'] = $this->_foreign_model->primary_col;
    }
    
    /**
     * 
     * A support method for _fixRelated() to handle has-many relationships.
     * 
     * @param array &$opts The relationship options; these are modified in-
     * place.
     * 
     * @return void
     * 
     */
    protected function _setRelated($opts)
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
            if ($this->through) {
                $this->through_table = $through->foreign_table;
            } else {
                // guess an appropriate table name.
                // if 'through' is not specified, this should generally be
                $this->through_table = 
                    $this->_native_model->table_name . '_' . 
                    $this->_foreign_model->table_name;
            }
        } else {
            $this->through_table = $opts['through_table'];
        }
        
        // get the through-alias
        if (empty($opts['through_alias'])) {
            if ($this->through) {
                $this->through_alias = $through->foreign_alias;
            } else {
                $this->through_alias = $this->name . '_through';
            }
        } else {
            $this->through_alias = $opts['through_alias'];
        }
        
        // a little magic
        if (empty($opts['through_native_col']) &&
            empty($opts['through_foreign_col']) &&
            ! empty($opts['through_key'])) {
            // pre-define through_foreign_col
            $this->through_key = $opts['through_key'];
            $opts['through_foreign_col'] = $opts['through_key'];
        }
        
        // what's the native model key in the through table?
        if (empty($opts['through_native_col'])) {
            if ($this->through) {
                $this->through_native_col = $through->foreign_col;
            } else {
                 $this->through_native_col = $this->_native_model->foreign_col;
            }
        } else {
            $this->through_native_col = $opts['through_native_col'];
        }
        
        // what's the foreign model key in the through table?
        if (empty($opts['through_foreign_col'])) {
            $this->through_foreign_col = $this->_foreign_model->foreign_col;
        } else {
            $this->through_foreign_col = $opts['through_foreign_col'];
        }
    }
    
    // the problem here is to make sure the "through" collection has an entry
    // for each foreign record, with the right ID on it.
    public function save($native)
    {
        // get the foreign collection to work with
        $foreign = $native->{$this->name};
        
        // get the through collection to work with
        $through = $native->{$this->through};
        
        // if no foreign, and no through, we're done
        if (! $foreign && ! $through) {
            return;
        }
        
        // if no foreign records, kill off all through records
        if (! $foreign) {
            $through->deleteAll();
            return;
        }
        
        // save the foreign records as they are, which creates the necessary
        // primary key values the through mapping will need
        $foreign->save();
        
        // we need a through mapping
        if (! $through) {
            // make a new collection
            $through = $native->newRelated($this->through);
            $native->{$this->through} = $through;
        }
        
        // the list of existing foreign values
        $foreign_list = $foreign->getColVals($this->foreign_col);
        
        // the list of existing through values
        $through_list = $through->getColVals($this->through_foreign_col);
        
        // find mappings that *do* exist but shouldn't, and delete them
        foreach ($through_list as $through_key => $through_val) {
            if (! in_array($through_val, $foreign_list)) {
                $through->deleteOne($through_key);
            }
        }
        
        // make sure all existing "through" have the right native IDs on the
        foreach ($through as $record) {
            $record->{$this->through_native_col} = $native->{$this->native_col};
        }
        
        // find mappings that *don't* exist, and add them
        foreach ($foreign_list as $foreign_val) {
            if (! in_array($foreign_val, $through_list)) {
                $through->appendNew(array(
                    $this->through_native_col  => $native->{$this->native_col},
                    $this->through_foreign_col => $foreign_val,
                ));
            }
        }
        
        // done with the mappings, save them
        $through->save();
    }
    
    /**
     * 
     * Are the related "foreign" and "through" collections valid?
     * 
     * @return bool
     * 
     */
    public function isInvalid($native)
    {
        $foreign = $native->{$this->name};
        $through = $native->{$this->through};
        
        // no foreign and no through means they can't be invalid
        if (! $foreign && ! $through) {
            return false;
        }
        
        // is foreign invalid?
        if ($foreign && $foreign->isInvalid()) {
            return true;
        }
        
        // is through invalid?
        if ($through && $through->isInvalid()) {
            return true;
        }
        
        // both foreign and through are valid
        return false;
    }
}
