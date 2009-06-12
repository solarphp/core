<?php
/**
 * 
 * Represents the characteristics of a "to-one" related model.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Jeff Moore <jeff@procata.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Related.php 3761 2009-05-27 18:20:20Z pmjones $
 * 
 */
abstract class Solar_Sql_Model_Related_ToOne extends Solar_Sql_Model_Related
{
    /**
     * 
     * Is this related to one record?
     * 
     * @return bool
     * 
     */
    public function isOne()
    {
        return true;
    }
    
    /**
     * 
     * Is this related to many records?
     * 
     * @return bool
     * 
     */
    public function isMany()
    {
        return false;
    }
    
    /**
     * 
     * Returns a record object populated with the passed data; if data is 
     * empty, returns a brand-new record object.
     * 
     * @param array $data The foreign data.
     * 
     * @return Solar_Sql_Model_Record A foreign record object.
     * 
     */
    public function newObject($data)
    {
        if (! $data) {
            return $this->_foreign_model->fetchNew();
        } else {
            return $this->_foreign_model->newRecord($data);
        }
    }
    
    /**
     * 
     * Fetches an empty value for the related.
     * 
     * @return null
     * 
     */
    public function fetchEmpty()
    {
        return null;
    }
    
    /**
     * 
     * Fetches a new related record.
     * 
     * @param array $data Data for the new record.
     * 
     * @return Solar_Sql_Model_Record
     * 
     */
    public function fetchNew($data = array())
    {
        return $this->_foreign_model->fetchNew($data);
    }
    
    /**
     * 
     * Fetches foreign data as a record or collection object.
     * 
     * @param Solar_Sql_Model_Record $record The specification for the
     * native selection.  Uses the primary key from that record.
     * 
     * @param array $params An array of SELECT parameters.
     * 
     * @return Solar_Sql_Model_Record A record  
     * 
     */
    public function fetch($record, $params = array())
    {
        if (! ($record instanceof Solar_Sql_Model_Record)) {
            throw $this->_exception('ERR_RELATED_SPEC', array(
                'spec' => $record
            ));
        }
        
        // inject parameters from our options
        $params = $this->_mergeSelectParams($params);
        
        // get a select object for the related rows
        $select = $this->_foreign_model->newSelect($params);
        
        // modify the select per-relationship.
        $this->_modSelectRelatedToRecord($select, $record);
        
        // fetch data and return
        $data = $select->fetch('one');
        if (empty($data)) {
            return $this->fetchEmpty();
        } else {
            return $this->newObject($data);
        }
    }
    
    /**
     * Convert a result array into an indexed result based on a 
     * primary key
     *
     * @param array $result The result array.
     *
     * @param string $primary The primary key.
     * 
     * @return array An associative list of records.
     */
    protected function _indexResult($result, $primary)
    {
        // Create an index of our dependent data
        $index = array();
        foreach ($result as $val) {
            $index[$val[$primary]] = $val;
        }
        return $index;
    }
    
    /**
     * 
     * Extracts dependent to-one records from a single row.
     * 
     * Essentially, in to-one server joins, this pulls out the columns in
     * the row that were joined from another table.
     * 
     * @param array &$target The array row from which we are extracting the 
     * dependent row.
     * 
     * @return array The dependent record data.
     * 
     */
    protected function _extractDependentOne(&$target)
    {
        $prefix = $this->name . '__';
        $prefix_len = strlen($prefix);
        
        // Don't bother if our dependent record is not represented
        $test_key = $prefix . $this->foreign_primary_col;
        if (empty($target[$test_key])) {
            // Remove columns for non-existant ToOne Record
            foreach ($target as $key => $val) {
                if (strncmp($key, $prefix, $prefix_len) === 0) {
                    unset($target[$key]);
                }
            }
            return null;
        }
        
        // Extract a record
        $result = array();
        foreach ($target as $key => $val) {
            if (strncmp($key, $prefix, $prefix_len) === 0) {
                $result[substr($key, $prefix_len)] = $val;
                unset($target[$key]);
            }
        }
        
        return $result;
    }
    
    /**
     * 
     * Extracts dependent to-one records from a multiple rows.
     * 
     * Essentially, in to-one server joins, this pulls out the columns in
     * the row that were joined from another table.
     * 
     * @param array &$target The array rows from which we are extracting the 
     * dependent rows.
     * 
     * @return array The dependent record data.
     * 
     */
    protected function _extractDependentAll(&$target)
    {
        // Build a column map because its faster to build it once for the first
        // record and apply it to the rest of the array.
        $column_map = array();
        $prefix = $this->name . '__';
        $prefix_len = strlen($prefix);
        $first_row = reset($target);
        foreach ($first_row as $key => $val) {
            if (strncmp($key, $prefix, $prefix_len) === 0) {
                $column_map[$key] = substr($key, $prefix_len);
            }
        }
        
        $result = array();
        $test_col = $prefix . $this->foreign_primary_col;
        foreach ($target as $target_key => $target_row) {
            // Quick test to see if we have data to merge
            if (!empty($target_row[$test_col])) {
                $row = array();
                // restructure to-one dependent data to remove current prefix
                foreach ($column_map as $source_col => $row_col) {
                    // transfer the dependent data to the new row
                    $row[$row_col] = $target_row[$source_col];
                    
                    // remove it from the target
                    unset($target[$target_key][$source_col]);
                }
                $result[] = $row;
            } else {
                // Remove columns for non-existant ToOne Record
                foreach ($column_map as $source_col => $row_col) {
                    unset($target[$target_key][$source_col]);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 
     * Join related objects into a parent record or collection.
     *
     * @param Solar_Sql_Model_Collection $target colletion to join into
     * 
     * @param Solar_Sql_Select $select The SELECT that fetched the parent.
     * 
     * @param array $options options controlling eager selection
     * 
     * @return Solar_Sql_Model_Collection|array A replacement for the target
     * 
     */
    public function joinAll($target, $select, $options = array())
    {
        if (empty($target)) {
            return $target;
        }
        $options = $this->_fixEagerOptions($options);
        $count = count($target);
        
        if ($options['join_strategy'] == 'server' || $options['require_related']) {

            if ($count == 1) {
                $result = array();
                $onlyone = reset($target);
                $onlyone = $this->_extractDependentOne($onlyone);
                if ($onlyone) {
                    $result[] = $onlyone;
                }
            } else {
                $result = $this->_extractDependentAll($target);
            }
            
            // Chain eager
            foreach ($options['eager'] as $name => $dependent_options) {
                $related = $this->_foreign_model->getRelated($name);
                $result = $related->joinAll($result, $select, $dependent_options);
            }
        } else if ($options['join_strategy'] == 'client') {
        
            $params = array('eager' => $options['eager']);
            
            // inject parameters from our options
            $params = $this->_mergeSelectParams($params);
            
            // get a select object for the related rows
            $dependent_select = $this->_foreign_model->newSelect($params);
        
            if ($count > $options['fromselect_threshold']) {
                // join using FROM (SELECT ...)
                $this->_modSelectRelatedToSelect($dependent_select, $select, $this->native_alias);
            } else {
                // join using WHERE ... IN (...)
                $collection = $this->newObject($target);
                $this->_modSelectRelatedToCollection($dependent_select, $collection);
            }
    
            $result = $dependent_select->fetch('all');
        } else {
            throw $this->_exception('ERR_UNRECOGNIZED_STRATEGY');
        }
        
        $index = $this->_indexResult($result, $this->foreign_col);
        
        return $this->_joinResults($target, $index, $this->native_col);
    }
    
    /**
     * 
     * Join related objects into a parent record or collection.
     *
     * @param Solar_Sql_Model_Record $target Record to Join into
     * 
     * @param Solar_Sql_Select $select The SELECT that fetched the parent.
     * 
     * @param array $options options controlling eager selection
     * 
     * @return Solar_Sql_Model_Collection|array A replacement for the target
     * 
     */
    public function joinOne($target, $select, $options = array())
    {
    
        if (empty($target)) {
            return $target;
        }
        
        $options = $this->_fixEagerOptions($options);
        
        if ($options['join_strategy'] == 'server' || $options['require_related']) {
        
            $result = $this->_extractDependentOne($target);

            if ($result) {
                foreach ($options['eager'] as $name => $dependent_options) {
                    $related = $this->_foreign_model->getRelated($name);
                    $result = $related->joinOne($result, $select, $dependent_options);
                }
            }            
        } else if ($options['join_strategy'] == 'client') {
        
            $params = array('eager' => $options['eager']);
            
            // inject parameters from our related options
            $params = $this->_mergeSelectParams($params);
            
            // get a select object for the related rows
            $dependent_select = $this->_foreign_model->newSelect($params);
            
            $this->_modSelectRelatedToRecord($dependent_select, $target);
    
            $result = $dependent_select->fetch('one');
            
        } else {
            throw $this->_exception('ERR_UNRECOGNIZED_STRATEGY');
        }
        
        $target[$this->name] = $result;
        
        
        return $target;
    }
    
    /**
     * 
     * When the native model is doing a select and an eager-join is requested
     * for this relation, this method modifies the select to add the eager
     * join.
     * 
     * Automatically adds the foreign columns to the select.
     * 
     * @param Solar_Sql_Select $select The SELECT to be modified.
     * 
     * @param string $parent_alias The alias for the parent table.
     * 
     * @param array $options options controlling eager selection
     * 
     * @return void The SELECT is modified in place.
     * 
     */
    public function modSelectEager($select, $parent_alias, $options = array())
    {
        $options = $this->_fixEagerOptions($options);
        if ($options['join_strategy'] !== 'server'  && !$options['require_related']) {
            // for client side joins, we do not modify the select
            return;
        }
        
        $options = $this->_fixColumnPrefixOption($options);
        $column_prefix = $options['column_prefix'];
        
        // build column names as "name__col" so that we can extract the
        // the related data later.
        $cols = array();
        foreach ($this->cols as $col) {
            $cols[] = "$col AS {$column_prefix}__$col";
        }
        
        $join_cond = array_merge(
            (array) $this->where, 
            $this->_foreign_model->getWhereMods($this->foreign_alias));
        
        // primary-key join condition on foreign table
        $join_cond[] = "{$parent_alias}.{$this->native_col} = "
                     . "{$this->foreign_alias}.{$this->foreign_col}";
        
        if ($options['require_related']) {
            $select->innerJoin(
                "{$this->foreign_table} AS {$this->foreign_alias}",
                $join_cond,
                $cols
            );
            $select->multiWhere($options['where']);
        } else {
            $select->leftJoin(
                "{$this->foreign_table} AS {$this->foreign_alias}",
                $join_cond,
                $cols
            );
        }
        
        // Chain modSelectEager
        foreach ($options['eager'] as $name => $dependent_options) {
            $related = $this->_foreign_model->getRelated($name);
            $related->modSelectEager($select, $this->foreign_alias, $dependent_options);
        }
        
    }
        
    
    /**
     * 
     * Sets the base name for the foreign class; assumes the related name is
     * is singular and inflects it to plural.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _setForeignClass($opts)
    {
        if (empty($opts['foreign_class'])) {
            // no class given.  convert 'foo_bar' to 'foo_bars' ...
            $plural = $this->_inflect->toPlural($opts['name']);
            // ... then use the plural form of the name to get the class.
            $catalog = $this->_native_model->catalog;
            $this->foreign_class = $catalog->getClass($plural);
        } else {
            $this->foreign_class = $opts['foreign_class'];
        }
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
}