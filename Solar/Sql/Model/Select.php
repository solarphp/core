<?php
/**
 * 
 * A model aware select statement.
 *
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Jeff Moore <jeff@procata.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Select.php 3617 2009-02-16 19:47:30Z pmjones $
 * 
 */
class Solar_Sql_Model_Select extends Solar_Sql_Select
{

    /**
     * 
     * The "parent" model for this record.
     * 
     * @var Solar_Sql_Model
     * 
     */
    protected $_model;
    
    /**
     * 
     * Which relationships should we eagerly fetch?
     * 
     * @var Solar_Sql_Model
     * 
     */
    protected $_eager = array();
    
    /**
     * 
     * Have we merged our required related clauses yet?
     * 
     * @var boolean
     * 
     */
    protected $_modified_for_required_related = FALSE;
    
    /**
     * 
     * The alias of the table driving this select.
     * 
     * @var boolean
     * 
     */
    protected $_table_alias;
    
    /**
     * 
     * Injects the model which drives this select statement.
     * 
     * @param Solar_Sql_Model $model The origin model object.
     * 
     * @return void
     * 
     */
    public function setModel(Solar_Sql_Model $model)
    {
        $this->_model = $model;
    }
    
    /**
     * 
     * Sets the table_alias driving this select.  You can probably figure this
     * out by examining sources.   But I have no idea how to do that.
     * 
     * @param string $table_alias The driving alias of this select
     * 
     * @return void
     * 
     */
    public function setTableAlias($table_alias)
    {
        $this->_table_alias = $table_alias;
    }
    
    /**
     * 
     * Injects the model which drives this select statement.
     * 
     * @param string $related The name of the relation to eager fetch
     * 
     * @param array $options options controlling fetching.
     * 
     * @return void
     * 
     */
    public function eager($related, $options = array())
    {
        if (!empty($options['where'])) {
        
            // force require_related to be true if there is a where clause
            $options['require_related'] = true;
            
            // Merge where clauses if necessary
            if (!empty($this->_eager[$related]['where'])) {
                $options['where'] = array_merge(
                    $this->_eager[$related]['where'],
                    $options['where']);
            }
        }
        
        // If we had already require_related, don't lose that information
        if (!empty($this->_eager[$related]['require_related'])) {
            $options['require_related'] = true;
        }
        $this->_eager[$related] = $options;
    }
    
    /**
     * 
     * Removes any optional eager clauses to simplify the query.
     * 
     * @return void
     * 
     */
    public function clearOptionalEager()
    {
        foreach ($this->_eager as $name => $options) {
            if (empty($options['require_related'])) {
                unset($this->_eager[$name]);
            }
        }
    }
    
    /**
     * 
     * Fetch the results based on the current query properties.
     * 
     * @param string $type The type of fetch to perform (all, one, col,
     * etc).  Default is 'pdo'.
     * 
     * @return mixed The query results.
     * 
     */
    public function fetchWithoutRelated($type = 'pdo')
    {
        return parent::fetch($type);
    }
    
    /**
     * 
     * Force the query to be modified by any specified related requirements.
     * 
     * @return void
     * 
     */
    protected function _modifyForRequiredRelated()
    {
        if ($this->_modified_for_required_related) {
            return;
        }
        $this->_modified_for_required_related = TRUE;
        
        foreach ($this->_eager as $name => $dependent_options) {
            if (!empty($dependent_options['require_related'])) {
                $related = $this->_model->getRelated($name);
                $related->modSelectEager($this, $this->_table_alias, $dependent_options);
            }
        }
    }
    
    /**
     * 
     * Fetch the results based on the current query properties.
     * 
     * @param string $type The type of fetch to perform (all, one, col,
     * etc).  Default is 'pdo'.
     * 
     * @return mixed The query results.
     * 
     */
    public function fetch($type = 'pdo')
    {
        // Required relations must be added to return proper results
        $this->_modifyForRequiredRelated();
        
        $eager = array();
        foreach ($this->_eager as $name => $dependent_options) {
            if (empty($dependent_options['require_related'])) {
                $eager[$name] = $dependent_options;
            }
        }
        
        if (empty($eager)) {
            $result = parent::fetch($type);
        } else {
            // Make sure that non required eager modifications don't change this select object
            $select = clone $this;
            
            // allow our eager buddies to modify this query
            foreach ($eager as $name => $dependent_options) {
                $related = $this->_model->getRelated($name);
                $related->modSelectEager($select, $this->_table_alias, $dependent_options);
            }
            $result = $select->fetchWithoutRelated($type);
        }
        
        // no post processing for some fetch types
        if ($type == 'sql' || $type == 'value' || $type == 'col' || $type == 'pairs') {
            return $result;
        }
        
        // Now we post process the result set based on our eager choices
        foreach ($this->_eager as $name => $dependent_options) {
            $related = $this->_model->getRelated($name);
            if ($type == 'one') {
                $result = $related->joinOne($result, $this, $dependent_options);
            } else {
                $result = $related->joinAll($result, $this, $dependent_options);
            }
        }
        return $result;
    }
    
    /**
     * 
     * Get the count of rows and number of pages for the current query.
     * 
     * @param string $col The column to COUNT() on.  Default is 'id'.
     * 
     * @return array An associative array with keys 'count' (the total number
     * of rows) and 'pages' (the number of pages based on $this->_paging).
     * 
     */
    public function countPages($col = 'id')
    {
        $this->_modifyForRequiredRelated();
        $this->clearOptionalEager();
        
        return parent::countPages($col);
    }
    
    /**
     * 
     * Support method for adding JOIN clauses.
     * 
     * @param string $type The type of join; empty for a plain JOIN, or
     * "LEFT", "INNER", etc.
     * 
     * @param string|Solar_Sql_Model $spec If a Solar_Sql_Model
     * object, the table to join to; if a string, the table name to
     * join to.
     * 
     * @param string|array $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the
     * joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    protected function _join($type, $spec, $cond, $cols)
    {
        // Add support for an array based $cond parameter
        if (is_array($cond)) {
            $on = array();
            foreach ((array) $cond as $key => $val) {
                if (is_int($key)) {
                    // integer key means a literal condition
                    // and no value to be quoted into it
                    $on[] = $val;
                } else {
                    // string $key means the key is a condition,
                    // and the $val should be quoted into it.
                    $on[] = $this->quoteInto($key, $val);
                }
            }
            $cond = implode($on, ' AND ');
        }
        return parent::_join($type, $spec, $cond, $cols);
    }
    
}
