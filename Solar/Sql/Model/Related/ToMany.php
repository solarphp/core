<?php
/**
 * 
 * Represents the characteristics of a "to-many" related model.
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
abstract class Solar_Sql_Model_Related_ToMany extends Solar_Sql_Model_Related
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
        return false;
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
        return true;
    }
    
    /**
     * 
     * Returns foreign data as a collection object.
     * 
     * @param array $data The foreign data.
     * 
     * @return Solar_Sql_Model_Collection A foreign collection object.
     * 
     */
    public function newObject($data)
    {
        return $this->_foreign_model->newCollection($data);
    }
    
    /**
     * 
     * Fetches an empty value for the related.
     * 
     * @return array
     * 
     */
    public function fetchEmpty()
    {
        return array();
    }
    
    /**
     * 
     * Fetches a new related collection.
     * 
     * @param array $data Data for the new collection.
     * 
     * @return Solar_Sql_Model_Collection
     * 
     */
    public function fetchNew($data = array())
    {
        return $this->_foreign_model->newCollection($data);
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
     * @return Solar_Sql_Model_Collection A collection object.
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
        
        // we should respect the count_pages parameter here
        
        // fetch data and return
        $data = $select->fetch('all');
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
            $id = $val[$primary];
            unset($val[$primary]);
            $index[$id][] = $val;
        }
        return $index;
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
        $count = count($target);
        if ($count == 0) {
            // if our driving table has no records, don't
            // issue a query for dependent data; there is none
            return $target;
        }
        
        $options = $this->_fixEagerOptions($options);
        $params = array('eager' => $options['eager']);
        
        // inject parameters from our options
        $params = $this->_mergeSelectParams($params);
        
        // get a select object for the related rows
        $dependent_select = $this->_foreign_model->newSelect($params);
        
        $parent_col = "{$this->native_alias}__{$this->native_col}";
        if ($count > $options['fromselect_threshold']) {
            // join using FROM (SELECT ...)
            $this->_modSelectRelatedToSelect($dependent_select, $select, $this->native_alias, $parent_col);
        } else {
            // join using WHERE ... IN (...)
            $collection = $this->newObject($target);
            $this->_modSelectRelatedToCollection($dependent_select, $collection, $parent_col);
        }
        
        $result = $dependent_select->fetch('all');
        
        $index = $this->_indexResult($result, $parent_col);
        
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
        
        $params = array('eager' => $options['eager']);
        
        // inject parameters from our related options
        $params = $this->_mergeSelectParams($params);
        
        // get a select object for the related rows
        $dependent_select = $this->_foreign_model->newSelect($params);
        $this->_modSelectRelatedToRecord($dependent_select, $target);
        
        $result = $dependent_select->fetch('all');
        
        $target[$this->name] = $result;
    
        return $target;
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
        if (!$options['require_related']) {
            // for client side joins, we do not modify the select
            return;
        }
        
        $join_cond = array_merge(
            (array) $this->where, 
            $this->_foreign_model->getWhereMods($this->foreign_alias));
        
        // primary-key join condition on foreign table
        $join_cond[] = "{$parent_alias}.{$this->native_col} = "
                     . "{$this->foreign_alias}.{$this->foreign_col}";
                     
        $select->innerJoin(
            "{$this->foreign_table} AS {$this->foreign_alias}",
            $join_cond
            );
            
        // added where conditions for the join
        $select->multiWhere($options['where']);
        
        // make the rows distinct, so we only get one row regardless of
        // the number of related rows (since we're not selecting cols).
        $select->distinct(true);
        
        // don't chain because we're not fetching
    }
    
    /**
     * 
     * Sets the base name for the foreign class; assumes the related name is
     * is already plural.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _setForeignClass($opts)
    {
        if (empty($opts['foreign_class'])) {
            $catalog = $this->_native_model->catalog;
            $this->foreign_class = $catalog->getClass($opts['name']);
        } else {
            $this->foreign_class = $opts['foreign_class'];
        }
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
        $opts['foreign_key'] = $this->_native_model->foreign_col;
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
     * @return void
     * 
     */
    protected function _setRelated($opts)
    {
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
    }
}