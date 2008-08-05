<?php
/**
 * 
 * Abstract class to represent the characteristics of a related model.
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
abstract class Solar_Sql_Model_Related extends Solar_Base {
    
    /**
     * 
     * The name of the relationship as defined by the original (native) model.
     * 
     * @var string
     * 
     */
    public $name;
    
    /**
     * 
     * The type of the relationship as defined by the original (native) model;
     * e.g., 'has_one', 'belongs_to', 'has_many'.
     * 
     * @var string
     * 
     */
    public $type;
    
    /**
     * 
     * The class of the native model.
     * 
     * @var string
     * 
     */
    public $native_class;
    
    /**
     * 
     * The name of the native table.
     * 
     * @var string
     * 
     */
    public $native_table;
    
    /**
     * 
     * The alias for the native table.
     * 
     * @var string
     * 
     */
    public $native_alias;
    
    /**
     * 
     * The native column to match against the foreign primary column.
     * 
     * @var string
     * 
     */
    public $native_col;
    
    /**
     * 
     * The class name of the foreign model. Default is the first
     * matching class for the relationship name, as loaded from the parent
     * class stack. Automatically honors single-table inheritance.
     * 
     * 
     * @var string
     * 
     */
    public $foreign_class;
    
    /**
     * 
     * The name of the table for the foreign model. Default is the
     * table specified by the foreign model.
     * 
     * @var string
     * 
     */
    public $foreign_table;
    
    /**
     * 
     * Aliases the foreign table to this name. Default is the
     * relationship name.
     * 
     * @var string
     * 
     */
    public $foreign_alias;
    
    /**
     * 
     * The name of the column to join with in the *foreign* table.
     * This forms one-half of the relationship.  Default is per association
     * type.
     * 
     * @var string
     * 
     */
    public $foreign_col;
    
    /**
     * 
     * The name of the foreign primary column.
     * 
     * @var string
     * 
     */
    public $foreign_primary_col;
    
    /**
     * 
     * If the foreign model uses single-table inheritance, this is
     * the column where the inheritance value is stored.
     * 
     * 
     * @var string
     * 
     */
    public $foreign_inherit_col;
    
    /**
     * 
     * If the foreign model has an inheritance type, the value of
     * that inheritance type (as stored in foreign_inherit_col).
     * 
     * @var string
     * 
     */
    public $foreign_inherit_val;
    
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
     * When fetching records, use DISTINCT ?
     * 
     * @var bool
     * 
     */
    public $distinct;
    
    /**
     * 
     * Fetch these columns for the related records.
     * 
     * @var string|array
     * 
     */
    public $cols;
    
    /**
     * 
     * Additional WHERE clauses when fetching records.
     * 
     * @var string|array
     * 
     */
    public $where;
    
    /**
     * 
     * Additional GROUP clauses when fetching records.
     * 
     * @var string|array
     * 
     */
    public $group;
    
    /**
     * 
     * Additional HAVING clauses when fetching records.
     * 
     * @var string|array
     * 
     */
    public $having;
    
    /**
     * 
     * Additional ORDER clauses when fetching records.
     * 
     * @var string|array
     * 
     */
    public $order;
    
    /**
     * 
     * When fetching records, use this many records per page of results.
     * 
     * @var int
     * 
     */
    public $paging;
    
    /**
     * 
     * The fetch type to use: 'one', 'all', 'assoc', etc.
     * 
     * @var string
     * 
     */
    public $fetch;
    
    /**
     * 
     * The virtual element called `foreign_key` automatically
     * populates the `native_col` or `foreign_col` value for you, based on the
     * association type.  This will be used **only** when `native_col` **and**
     * `foreign_col` are not set.
     * 
     * @var string
     * 
     */
    public $foreign_key;
    
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
     * An instance of the native (origin) model that defined this relationship.
     * 
     * @var Solar_Sql_Model
     * 
     */
    protected $_native_model;
    
    /**
     * 
     * An instance of the foreign (related) model.
     * 
     * @var Solar_Sql_Model
     * 
     */
    protected $_foreign_model;
    
    /**
     * 
     * When calling fetchObject(), return this kind of result object.
     * 
     * Typically 'record' or 'collection'.
     * 
     * @var string
     * 
     * @see fetchObject()
     * 
     */
    protected $_fetch_object = 'record';
    
    /**
     * 
     * The registered Solar_Inflect object.
     * 
     * @var Solar_Inflect
     * 
     */
    protected $_inflect;
    
    /**
     * 
     * Constructor.
     * 
     * @param mixed $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_inflect = Solar_Registry::get('inflect');
    }
    
    /**
     * 
     * Sets the native (origin) model instance.
     * 
     * @param Solar_Sql_Model $model The native model instance.
     * 
     * @return void
     * 
     */
    public function setNativeModel($model)
    {
        $this->_native_model = $model;
        $this->native_class = $this->_native_model->class;
        $this->native_table = $this->_native_model->table_name;
        $this->native_alias = $this->_native_model->model_name;
    }
    
    /**
     * 
     * Returns the related (foreign) model instance.
     * 
     * @return Solar_Sql_Model
     * 
     */
    public function getModel()
    {
        return $this->_foreign_model;
    }
    
    /**
     * 
     * Returns the relation characteristics as an array.
     * 
     * @return array
     * 
     */
    public function toArray()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $val) {
            if ($key[0] == '_') {
                unset($vars[$key]);
            }
        }
        return $vars;
    }
    
    /**
     * 
     * Loads this relationship object with user-defined characteristics
     * (options), and corrects them as needed.
     * 
     * @param array $opts The user-defined options for the relationship.
     * 
     * @return void
     * 
     */
    public function load($opts)
    {
        $this->name = $opts['name'];
        $this->_setType();
        $this->_setForeignClass($opts);
        $this->_setForeignModel($opts);
        $this->_setCols($opts);
        $this->_setSelect($opts);
        
        // if the user has specified *neither* a foreign_col *nor* a native_col,
        // but *has* specified a foreign_key, use the foreign_key to define 
        // the foreign_col or native col (depending on relation type). 
        if (empty($opts['native_col']) && empty($opts['foreign_col'])) {
            
            // if a "virtual" foreign_key value is not set, define one
            if (empty($opts['foreign_key'])) {
                $this->_fixForeignKey($opts);
            }
            
            // retaing the foreign key
            $this->foreign_key = $opts['foreign_key'];
            
            // now set the related column based on the foreign_key value
            $this->_fixRelatedCol($opts);
        }
        
        $this->_setRelated($opts);
    }
    
    /**
     * 
     * Convenience method for getting a dump the whole object, or one of its
     * properties, or an external variable.
     * 
     * @param mixed $var If null, dump $this; if a string, dump $this->$var;
     * otherwise, dump $var.
     * 
     * @param string $label Label the dump output with this string.
     * 
     * @return void
     * 
     */
    public function dump($var = null, $label = null)
    {
        if ($var) {
            return parent::dump($var, $label);
        }
        
        $clone = clone($this);
        unset($clone->_config);
        unset($clone->_native_model);
        unset($clone->_foreign_model);
        unset($clone->_inflect);
        return parent::dump($clone, $label);
    }
    
    /**
     * 
     * Creates a new selection object for fetching records from this relation.
     * 
     * @param mixed $spec If an array, treated as params for a select 
     * statement (where, group, having, etc) for finding the native-model IDs.
     * If a Record object, the record's primary-key is used for the native-
     * model ID.  If a Solar_Sql_Select, used as-is for finding the native-
     * model IDs.
     * 
     * @return Solar_Sql_Select
     * 
     * @todo Can we get away without using a params array at all, and use only
     * Select or Record for the $spec?
     * 
     */
    public function newSelect($spec)
    {
        // specification must be a record, or params for a select, or a select
        if (! ($spec instanceof Solar_Sql_Model_Record)
            && ! is_array($spec)
            && ! ($spec instanceof Solar_Sql_Select)) {
            // problem
            throw $this->_exception('ERR_RELATED_SPEC', array(
                'spec' => $spec
            ));
        }
        
        // convert $spec array to a Select object for the native column ID list
        if (is_array($spec)) {
            // rebuild the spec as a select object
            $params = $spec;
            $spec = $this->_native_model->newSelect();
            $spec->distinct($params['distinct'])
                 ->from("{$this->native_table} AS {$this->native_alias}", $this->native_col)
                 ->multiWhere($params['where'])
                 ->group($params['group'])
                 ->having($params['having'])
                 ->order($params['order'])
                 ->setPaging($params['paging'])
                 ->limitPage($params['page']);
        }
        
        // get a select object for the related rows
        $select = Solar::factory(
            $this->_native_model->select_class,
            array('sql' => $this->_native_model->sql)
        );
        
        // modify the select per-relationship. only has-many-through uses
        // non-standard modification.
        $this->_modSelect($select, $spec);
        
        // add remaining clauses
        $select->distinct($this->distinct)
               ->multiWhere($this->where)
               ->group($this->group)
               ->having($this->having)
               ->order($this->order)
               ->setPaging($this->paging);
        
        // done
        return $select;
    }
    
    /**
     * 
     * Modifies the SELECT from a native model countPages() call to join
     * with the foreign model (especially on eager fetches).
     * 
     * @param Solar_Sql_Select $select The SELECT from the native model
     * countPages() method.
     * 
     * @return void The SELECT is modified in place.
     * 
     */
    public function modSelectCountPages($select)
    {
        // primary-key join condition on foreign table
        $cond = "{$this->native_alias}.{$this->native_col} = "
              . "{$this->foreign_alias}.{$this->foreign_col}";
        
        // add the join, no columns.
        $select->leftJoin(
            "{$this->foreign_table} AS {$this->foreign_alias}",
            $cond
        );
        
        // inheritance for foreign model
        if ($this->foreign_inherit_col) {
            $select->where(
                "{$this->foreign_alias}.{$this->foreign_inherit_col} = ?",
                $this->foreign_inherit_val
            );
        }
        
        // added where conditions for the join
        $select->multiWhere($this->where);
    }
    
    /**
     * 
     * Fetches foreign data as a record or collection object.
     * 
     * @param array|Solar_Sql_Model_Record $spec The specification for the
     * native selection.  If an array, treated as selection criteria; if a
     * record object, uses the primary key from that record.
     * 
     * @param int $page For to-many associations, the page-number of records
     * to fetch (default null, which fetches all records).  Ignored by to-one
     * associations.  Paging is based on the related model's `$_paging`
     * property.
     * 
     * @param array $bind Key-value pairs to bind to the select.
     * 
     * @return Solar_Sql_Model_Record|Solar_Sql_Model_Collection A record or 
     * collection object.
     * 
     */
    public function fetchObject($spec, $page = null, $bind = null)
    {
        // fetch the related data as an array
        $data = $this->fetchArray($spec, $page);
        
        // record or collection?
        if ($this->_fetch_object == 'record') {
            $result = $this->_foreign_model->newRecord($data);
        } elseif ($this->_fetch_object == 'collection') {
            $result = $this->_foreign_model->newCollection($data);
        } else {
            throw $this->_exception('ERR_FETCH_OBJECT_TYPE', array(
                'type' => $this->_fetch_object,
            ));
        }
        
        // done!
        return $result;
    }
    
    /**
     * 
     * Fetches foreign data as an array.
     * 
     * @param array|Solar_Sql_Model_Record $spec The specification for the
     * native selection.  If an array, treated as selection criteria; if a
     * record object, uses the primary key from that record.
     * 
     * @param int $page For to-many associations, the page-number of records
     * to fetch (default null, which fetches all records).  Ignored by to-one
     * associations.  Paging is based on the related model's "$_paging"
     * property.
     * 
     * @param array $bind Key-value pairs to bind to the select.
     * 
     * @return array An array of data from the fetch.
     * 
     * @todo CACHE THIS!!!  In fact, need to move it entirely out to the
     * "related" class.
     * 
     */
    public function fetchArray($spec, $page = null, $bind = null)
    {
        if (is_array($spec)) {
            $spec = $this->_native_model->fixSelectParams($spec);
        }
        
        $select = $this->newSelect($spec);
        $select->bind($bind);
        $select->limitPage($page);
        return $select->fetch($this->fetch);
    }
    
    /**
     * 
     * Modifies the base select statement for the relationship type.
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
        // simple belongs_to, has_one, or has_many.
        if ($spec instanceof Solar_Sql_Model_Record) {
            // restrict to the related native column value in the foreign table
            $select->where(
                "{$this->foreign_alias}.{$this->foreign_col} = ?",
                $spec->{$this->native_col}
            );
        } else {
            // $spec is a Select object. restrict to a sub-select of IDs from
            // the native table.
            $clone = clone $spec;
            
            // sub-select **only** the native column, so that we're not
            // pulling back everything, just the part we need to join on.
            // SQLite needs the explicit "AS" here.
            // <http://osdir.com/ml/db.sqlite.general/2003-05/msg00228.html>
            $clone->clear('cols');
            $primary_col = "{$this->native_alias}.{$this->native_col} AS {$this->native_col}";
            $clone->cols($primary_col);
            
            $inner = str_replace("\n", "\n\t\t", $clone->fetchSql());
            
            // add the native table ID at the top through a join
            $select->innerJoin(
                "($inner) AS {$this->native_alias}",
                "{$this->foreign_alias}.{$this->foreign_col} = {$this->native_alias}.{$this->native_col}",
                "{$this->native_col} AS {$this->native_alias}__{$this->native_col}"
            );
        }
        
        // select columns from the foreign table.
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
    
    /**
     * 
     * Support method for modSelectEager().  This implementation works for
     * belongs_to and has_one (with columns) and has_many (without columns).
     * The "has_many through" relation needs its own implementation.
     * 
     * @param Solar_Sql_Select $select The SELECT to be modified.
     * 
     * @param array $cols Any columns to add to the SELECT.
     * 
     * @return void The SELECT is modified in place.
     * 
     */
    protected function _modSelectEager($select, $cols = null)
    {
        // primary-key join condition on foreign table
        $cond = "{$this->native_alias}.{$this->native_col} = "
              . "{$this->foreign_alias}.{$this->foreign_col}";
        
        // add the join
        $select->leftJoin(
            "{$this->foreign_table} AS {$this->foreign_alias}",
            $cond,
            $cols
        );
        
        // inheritance for foreign model
        if ($this->foreign_inherit_col) {
            $select->where(
                "{$this->foreign_alias}.{$this->foreign_inherit_col} = ?",
                $this->foreign_inherit_val
            );
        }
        
        // added where conditions for the join
        $select->multiWhere($this->where);
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
            // no class given.  change 'foo_bar' to 'FooBar' ...
            $class = $this->_inflect->underToStudly($opts['name']);
            // ... then use the plural form of the name.
            $this->foreign_class = $this->_inflect->toPlural($class);
        } else {
            $this->foreign_class = $opts['foreign_class'];
        }
    }
    
    /**
     * 
     * Corrects the foreign_key value in the options.
     * 
     * @param array &$opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    abstract protected function _fixForeignKey(&$opts);
    
    /**
     * 
     * Sets the foreign model instance based on user-defined relationship
     * options.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _setForeignModel($opts)
    {
        // can we load a related model class from the hierarchy stack?
        $class = $this->_native_model->stack->load($this->foreign_class, false);
        
        // did we find it?
        if (! $class) {
            // look for a "parallel" class name, based on where the word
            // "Model" is in the current class name. this lets you pull
            // model classes from the same level, not from the inheritance
            // stack.
            $pos = strrpos($this->native_class, 'Model_');
            if ($pos !== false) {
                $pos += 6; // "Model_"
                $tmp = substr($this->native_class, 0, $pos) . ucfirst($this->foreign_class);
                try {
                    Solar::autoload($tmp);
                    // if no exception, $class gets set
                    $class = $tmp;
                } catch (Exception $e) {
                    // do nothing
                }
            }
        }
        
        // last chance: do we *still* need a class name?
        if (! $class) {
            // not in the hierarchy, and no parallel class name. look for the
            // model class literally. this will throw an exception if the
            // class cannot be found anywhere.
            try {
                Solar::autoload($this->foreign_class);
                // if no exception, $class gets set
                $class = $this->foreign_class;
            } catch (Solar_Exception $e) {
                throw $this->_exception('ERR_LOAD_FOREIGN_MODEL', array(
                    'native_model' => $this->_native_model->class,
                    'related_name' => $opts['name'],
                    'foreign_class' => $this->foreign_class,
                ));
            }
        }
        
        // finally we have a class name, keep it as the foreign model class
        $this->foreign_class = $class;
        
        // create a foreign model instance
        $this->_foreign_model = Solar::factory( $this->foreign_class, array(
            'sql' => $this->_native_model->sql
        ));
        
        // get its table name
        $this->foreign_table = $this->_foreign_model->table_name;
        
        // and its primary column
        $this->foreign_primary_col = $this->_foreign_model->primary_col;
        
        // set the foreign alias based on the relationship name
        $this->foreign_alias = $opts['name'];
    }
    
    /**
     * 
     * Sets the foreign columns to be selected based on user-defined 
     * relationship options.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _setCols($opts)
    {
        // the list of foreign table cols to retrieve
        if (empty($opts['cols'])) {
            $this->cols = $this->_foreign_model->fetch_cols;
        } elseif (is_string($opts['cols'])) {
            $this->cols = explode(',', $opts['cols']);
        } else {
            $this->cols = (array) $opts['cols'];
        }
        
        // make sure we always retrieve the foreign primary key value,
        // if there is one.
        $primary = $this->_foreign_model->primary_col;
        if ($primary && ! in_array($primary, $this->cols)) {
            $this->cols[] = $primary;
        }
        
        // if inheritance is turned on for the foreign model,
        // make sure we always retrieve the foreign inheritance value.
        $inherit = $this->_foreign_model->inherit_col;
        if ($inherit && ! in_array($inherit, $this->cols)) {
            $this->cols[] = $inherit;
        }
        
        // if inheritance is turned on, force the foreign_inherit
        // column and value
        if ($this->_foreign_model->inherit_col && $this->_foreign_model->inherit_model) {
            $this->foreign_inherit_col = $this->_foreign_model->inherit_col;
            $this->foreign_inherit_val = $this->_foreign_model->inherit_model;
        } else {
            $this->foreign_inherit_col = null;
            $this->foreign_inherit_val = null;
        }
    }
    
    /**
     * 
     * Sets additional selection clauses ('where', 'having', 'group') for
     * related records based on user-defined relationship options.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _setSelect($opts)
    {
        // distinct
        if (empty($opts['distinct'])) {
            $this->distinct = false;
        } else {
            $this->distinct = (bool) $opts['distinct'];
        }
        
        // where
        if (empty($opts['where'])) {
            $this->where = null;
        } else {
            $this->where = (array) $opts['where'];
        }
        
        // group
        if (empty($opts['group'])) {
            $this->group = null;
        } else {
            $this->group = (array) $opts['group'];
        }
        
        // having
        if (empty($opts['having'])) {
            $this->having = null;
        } else {
            $this->having = (array) $opts['having'];
        }
        
        // order
        if (empty($opts['order'])) {
            // default to the foreign primary key
            $this->order = array("{$this->foreign_alias}.{$this->_foreign_model->primary_col}");
        } else {
            $this->order = (array) $opts['order'];
        }
        
        // paging from the foreign model
        if (empty($opts['paging'])) {
            $this->paging = $this->_foreign_model->paging;
        } else {
            $this->paging = (int) $opts['paging'];
        }
    }
    
    /**
     * 
     * When the native model is doing a select and an eager-join is requested
     * for this relation, this method modifies the select to add the eager
     * join.
     * 
     * @param Solar_Sql_Select $select The SELECT to be modified.
     * 
     * @return void The SELECT is modified in place.
     * 
     */
    abstract public function modSelectEager($select);
    
    /**
     * 
     * Sets the relationship type.
     * 
     * @return void
     * 
     */
    abstract protected function _setType();
    
    /**
     * 
     * Fixes the related column names in the user-defined options **in place**.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    abstract protected function _fixRelatedCol(&$opts);
    
    /**
     * 
     * Sets the characteristics for the related model, table, etc. based on
     * the user-defined relationship options.
     * 
     * @param array $opts The user-defined options for the relationship.
     * 
     * @return void
     * 
     */
    abstract protected function _setRelated($opts);
}
