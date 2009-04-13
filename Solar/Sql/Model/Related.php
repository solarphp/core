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
     * class stack.
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
     * Additional ORDER clauses when fetching records.
     * 
     * @var string|array
     * 
     */
    public $order;

    /**
     * 
     * Indicates whether to prefer WHERE ... IN (...) style queries
     * or FROM (SELECT ...) style queries when performing client side joins.
     * 
     * @var int
     * 
     */
    protected $_fromselect_threshold;

    /**
     * 
     * Indicates the general strategy to use for joins 'client' or 'server'
     * 
     * @var int
     * 
     */
    protected $_join_strategy;
    
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
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // main construction
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
            
            // retain the foreign key
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
     * Is this related to one record?
     * 
     * @return bool
     * 
     */
    abstract public function isOne();
    
    /**
     * 
     * Is this related to many records?
     * 
     * @return bool
     * 
     */
    abstract public function isMany();

    /**
     * Merge dependent data into a target array based on an index
     * by primary column
     *
     * @param array $target The array to merge into
     *
     * @param array $index An associative list of records
     * 
     * @param string $primary Name of primary key
     * 
     * @return array Merge result sets
     */
    protected function _joinResults($target, $index, $primary)
    {
        $null = $this->fetchEmpty();
        $col = $this->name;
        foreach($target as $key => $row) {
            if (empty($index[$row[$primary]])) {
                // we must use a placeholder to prevent lazy loading later
                $target[$key][$col] = $null;
            } else {
                $target[$key][$col] = $index[$row[$primary]];
            }
        }
        return $target;
    }

    /**
     * 
     * Normalize a set of eager options
     * 
     * @param array $options Set of options controlling eager fetching
     * 
     * @return array A normalized set of clause params.
     * 
     */
    protected function _fixEagerOptions($options)
    {
        $params = array();
        if (!empty($options['eager'])) {
            $eager = array();
            foreach ((array) $options['eager'] as $key => $val) {
                if (is_int($key)) {
                    // No options specified
                    $eager[$val] = array();
                } else {
                    // associate options with eager as key
                    $eager[$key] = (array) $val;
                }
            }
        } else {
            $eager = array();
        }
        $options['eager'] = $this->_foreign_model->modEagerOptions($eager);

        if (isset($options['fromselect_threshold'])) {
            $options['fromselect_threshold'] = (int) $options['fromselect_threshold'];
        } else {
            $options['fromselect_threshold'] = $this->_fromselect_threshold;
        }

        if (empty($options['join_strategy'])) {
            $options['join_strategy'] = $this->_join_strategy;
        }
        
        if (empty($options['require_related'])) {
            $options['require_related'] = false;
        }

        if (!empty($options['where'])) {
            $options['where'] = (array) $options['where'];
            $options['require_related'] = true;
        } else {
            $options['where'] = array();
        }

        return $options;
    }

    /**
     * 
     * Normalize a set of eager options when we are going to modify a select statement
     * 
     * @param array $options Set of options controlling eager fetching
     * 
     * @return array A normalized set of clause params.
     * 
     */
    protected function _fixColumnPrefixOption($options)
    {
        if (empty($options['column_prefix'])) {
            $options['column_prefix'] = $this->name;
        }

        // make sure any of our dependents know which column prefix to use
        foreach ($options['eager'] as $name => $dependent_options) {
            $options['eager'][$name]['column_prefix'] = 
                $options['column_prefix'] . '__' . $name;
        }

        return $options;
    }

    /**
     * 
     * Merge select parameters with options from this association.
     * 
     * @param array $params The parameters for the SELECT clauses.
     * 
     * @return array A normalized set of clause params.
     * 
     */
    protected function _mergeSelectParams($params)
    {
        if (empty($params['table_alias'])) {
            $params['table_alias'] = $this->foreign_alias;
        }

        if (empty($params['order'])) {
            $params['order'] = $this->order;
        }

        // merge where conditions
        if (!empty($params['where'])) {
            $params['where'] = array_merge((array) $params['where'], (array) $this->where);
        }
        if (empty($params['where'])) {
            $params['where'] = null;
        }

        $params['cols'] = $this->cols;
            
        return $params;
    }

    /**
     * 
     * packages foreign data as a record or collection object.
     * 
     * @param array $data The foreign Data
     * 
     * @return Solar_Sql_Model_Record|Solar_Sql_Model_Collection A record or 
     * collection object.
     * 
     */
    abstract public function newObject($data);

    /**
     * 
     * Fetch a null object appropriate for this association
     * 
     * @return null|array
     * 
     */
    abstract public function fetchEmpty();

    /**
     * 
     * Fetches foreign data as a record or collection object.
     * 
     * @param Solar_Sql_Model_Record $spec The specification for the
     * native selection.  Uses the primary key from that record.
     * 
     * @param array $params An array of SELECT parameters.
     * 
     * @return Solar_Sql_Model_Record|Solar_Sql_Model_Collection A record or 
     * collection object.
     * 
     */
    abstract public function fetch($spec, $params = array());

    /**
     * 
     * Join related objects into a parent record or collection 
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
    abstract public function joinAll($target, $select, $options = array());
    
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
        // restrict to the related native column value in the foreign table
        $select->where(
            "{$this->foreign_alias}.{$this->foreign_col} = ?",
            $spec[$this->native_col]
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
        // Restrict to the set of IDs in the driving collection
        $keys = $spec->getPrimaryVals($this->native_col);
        $num_keys = count($keys);
        if ($num_keys == 0) {
            // We are too far down to stop the SELECT from being issued, but
            // we can give a big fat hint to the SQL optimizer
            $select->where('FALSE');
        } else if ($num_keys == 1) {
            $select->where(
                "{$this->foreign_alias}.{$this->foreign_col} = ?",
                $keys[0]
            );
        } else {
            $select->where(
                "{$this->foreign_alias}.{$this->foreign_col} IN (?)",
                array_unique($keys)
            );
        }

        // Add a column so that we know what parent we are joining to
        if ($parent_col) {
            $select->cols("{$this->foreign_alias}.{$this->foreign_col} AS {$parent_col}");
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
        
        // Condition to join on        
        $cond = "{$this->foreign_alias}.{$this->foreign_col} = {$parent_alias}.{$this->native_col}";

        // Add a column so that we know what parent we are joining to
        if ($parent_col) {
            $col = "{$this->native_col} AS {$parent_col}";
        } else {
            $col = NULL;
        }
        
        $select->innerJoin(
            "($inner) AS {$parent_alias}",
            $cond,
            $col
        );
    }

    /**
     * 
     * When the native model is doing a select and an eager-join is requested
     * for this relation, this method modifies the select to add the eager
     * join.
     * 
     * @param Solar_Sql_Select $select The SELECT to be modified.
     * 
     * @param array $options options controlling eager selection
     * 
     * @return void The SELECT is modified in place.
     * 
     */
    abstract public function modSelectEager($select, $parent_alias, $options = array());
    
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
    abstract protected function _setForeignClass($opts);
    
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
        // get the foreign model from the catalog by its class name
        $catalog = $this->_native_model->catalog;
        $this->_foreign_model = $catalog->getModelByClass($this->foreign_class);
        
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
        
    }
    
    /**
     * 
     * Sets additional selection clauses ('where') for
     * related records based on user-defined relationship options.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _setSelect($opts)
    {
        
        // where
        if (empty($opts['where'])) {
            $this->where = null;
        } else {
            $this->where = (array) $opts['where'];
        }
        
        // order
        if (empty($opts['order'])) {
            // default to the foreign primary key
            $this->order = array("{$this->foreign_alias}.{$this->_foreign_model->primary_col}");
        } else {
            $this->order = (array) $opts['order'];
        }
        
        if (empty($opts['fromselect_threshold'])) {
            $this->_fromselect_threshold = 10; // default maybe should be config option?
        } else {
            $this->_fromselect_threshold = (int) $opts['fromselect_threshold'];
        }

    }

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
    
    function preSave($native)
    {
        // at least for now, only belongs-to needs this
    }
    
    abstract public function save($native);
}
