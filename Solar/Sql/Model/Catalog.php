<?php
/**
 * 
 * Provides a catalog of all properties for all loaded models.
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

/**
 * 
 * Provides a catalog of all properties for all loaded models.
 * 
 * Why keep a catalog of model properties?  So that you don't need to expend
 * resources every time you instantiate a new model object.  This also lets
 * each model know about the properties of other models, which is needed for
 * things such as paging, primary keys, inheritance, and so on.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @todo In _fixTableCols(), add a "sync" check to see if column data in the
 * class matches column data in the database, and throw an exception if they
 * don't match pretty closely.
 * 
 * @todo Add 'delete' key to $_related, to allow cascading (or refusal) of
 * deleting related records when a main record is deleted.
 * 
 * @todo Add 'custom' or 'sql' key to $_related, to allow completely
 * customized SELECT statement?
 * 
 */
class Solar_Sql_Model_Catalog extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `sql`
     * : (dependency) A Solar_Sql dependency.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Model_Catalog = array(
        'sql'   => 'sql',
        'cache' => null,
    );
    
    /**
     * 
     * Catalog of all model-specific data, so we don't need to instantiate
     * new models just to find out what their primary key is, etc.
     * 
     * @var array
     * 
     */
    static protected $_catalog = array();
    
    /**
     * 
     * Cache to store catalog data between page loads.
     * 
     * @var Solar_Cache
     * 
     */
    protected $_cache;
    
    /**
     * 
     * SQL object for working with the database.
     * 
     * @var Solar_Sql
     * 
     */
    protected $_sql;
    
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
        
        // connect to the database if needed
        $this->_sql = Solar::dependency(
            'Solar_Sql',
            $this->_config['sql']
        );
        
        // get the optional dependency object for caching column definitions
        if ($this->_config['cache']) {
            $this->_cache = Solar::dependency(
                'Solar_Cache',
                $this->_config['cache']
            );
        }
    }
    
    /**
     * 
     * Checks to see if catalog data exists for a model class.
     * 
     * Loads the catalog data from the cache as available.
     * 
     * @param string $class The model class to check.
     * 
     * @return bool True if the class is in the catalog, false if not.
     * 
     */
    public function exists($class)
    {
        // do we already have it in the catalog?
        if (empty(self::$_catalog[$class])) {
            // do we have a cache?
            if ($this->_cache) {
                // is it in the cache?
                $model = $this->_cache->fetch("Solar_Sql_Model_Catalog/$class");
                if ($model) {
                    // save it in the catalog
                    self::$_catalog[$class] = $model;
                }
            }
        }
        
        // the "real" check
        return ! empty(self::$_catalog[$class]);
    }
    
    /**
     * 
     * Sets the catalog data for a model class.
     * 
     * @param string $class The model class name.
     * 
     * @param array $vars All the class properties for the model as key-value
     * pairs.
     * 
     * @param array $methods All the class methods for the model.
     * 
     * @return void
     * 
     */
    public function set($class, $vars)
    {
        $ignore = array('_sql');
        
        // set the catalog entry (blank)
        self::$_catalog[$class] = new StdClass;
        
        // keep a convenient reference to the catalog entry
        $model = self::$_catalog[$class];
        
        // save each of the vars in the catalog entry, as defined by the user
        foreach ($vars as $key => $val) {
            $key = preg_replace('/\W/', '', $key);
            if (! in_array($key, $ignore)) {
                $model->$key = $val;
            }
        }
        
        // follow-on cleanup of critical user-defined values
        $this->_fixStack($model);
        $this->_fixTableName($model);
        $this->_fixIndexes($model);
        $this->_fixTableCols($model);
        $this->_fixModelName($model);
        $this->_fixPropertyCols($model);
        $this->_fixAccessMethods($model);
        $this->_fixFilters($model); // including datafilter class
        $this->_fixRelated($model);
        
        // do we have a cache?
        if ($this->_cache) {
            // save in the cache for next time
            $this->_cache->save(
                "Solar_Sql_Model_Catalog/$class",
                $model
            );
        }
    }
    
    /**
     * 
     * Gets the catalog data for a model class.
     * 
     * @param string|object $spec The model class name, or an instance of the
     * model class.
     * 
     * @return StdClass A clone of the catalog data for the model class.
     * 
     */
    public function get($spec)
    {
        // get the class name
        if (is_object($spec)) {
            $class = get_class($spec);
        } else {
            $class = $spec;
        }
        
        // see if it already exists; this also loads from cache if available
        if (! $this->exists($class)) {
            // cause it to self-register
            Solar::factory($class);
        
            // save it in the cache
            if ($this->_cache) {
                $this->_cache->save(
                    "Solar_Sql_Model_Catalog/$class",
                    self::$_catalog[$class]
                );
            }
        }
        
        // return a clone of the catalog object (don't want others messing
        // with the fixed data)
        return clone(self::$_catalog[$class]);
    }
    
    /**
     * 
     * Fixes the stack of parent classes for the model.
     * 
     * @param StdClass $model The model property catalog.
     * 
     * @return void
     * 
     */
    protected function _fixStack($model)
    {
        $parents = Solar::parents($model->_class, true);
        array_pop($parents); // Solar_Base
        array_pop($parents); // Solar_Sql_Model
        $model->_stack = Solar::factory('Solar_Class_Stack');
        $model->_stack->add($parents);
    }
    
    /**
     * 
     * Loads table name into $this->_table_name, and pre-sets the value of
     * $this->_inherit_model based on the class name.
     * 
     * @param StdClass $model The model property catalog.
     * 
     * @return void
     * 
     */
    protected function _fixTableName($model)
    {
        /**
         * Pre-set the value of $_inherit_model.  Will be modified one
         * more time in _fixTableCols().
         */
        
        // find the closest parent called *_Model.  we do this so that
        // we can honor the top-level table name with inherited models.
        // *do not* use the class stack, as Solar_Sql_Model has been
        // removed from it.
        $parents = Solar::parents($model->_class, true);
        foreach ($parents as $key => $val) {
            if (substr($val, -6) == '_Model') {
                break;
            }
        }
        
        // $key is now the value of the closest "_Model" class.
        // -1 to get the first class below that (e.g., *_Model_Nodes).
        // $parent is then the parent class name that represents the base of
        // the model-inheritance hierarchy (which may not be the immediate
        // parent in some cases).
        $parent = $parents[$key - 1];
        
        // compare parent class name to the current class name.
        // if it has an undersore after the parent class name, this class
        // is considered to be an inheritance model.
        $len = strlen($parent) + 1;
        $class = $model->_class;
        if (substr($class, 0, $len) == "{$parent}_") {
            $model->_inherit_model = substr($class, $len);
            $model->_inherit_base = $parent;
        }
        
        // get the part after the last underscore in the parent class name.
        // e.g., "Solar_Model_Node" => "Node".  If no underscores, use the
        // parent class name as-is.
        $pos = strrpos($parent, '_');
        if ($pos === false) {
            $table = $parent;
        } else {
            $table = substr($parent, $pos + 1);
        }
        
        /**
         * Auto-set the table name, if needed.
         */
        if (empty($model->_table_name)) {
            // auto-defined table name. change TableName to table_name.
            // this is our one concession on inflecting names, because if the
            // class was called Table_Name, it would set the inheritance
            // model improperly.
            $table = preg_replace('/([a-z])([A-Z])/', '$1_$2', $table);
            $model->_table_name = strtolower($table);
        } else {
            // user-defined table name.
            $model->_table_name = strtolower($model->_table_name);
        }
    }
    
    /**
     * 
     * Fixes table column definitions into $_table_cols, and post-sets
     * inheritance values.
     * 
     * @param StdClass $model The model property catalog.
     * 
     * @return void
     * 
     */
    protected function _fixTableCols($model)
    {
        // is a table with the same name already at the database?
        $list = $this->_sql->fetchTableList();
        
        // if not found, attempt to create it
        if (! in_array($model->_table_name, $list)) {
            $this->_createTableAndIndexes($model);
        }
        
        // reset the columns to be **as they are at the database**
        $model->_table_cols = $this->_sql->fetchTableCols($model->_table_name);
        
        // @todo add a "sync" check to see if column data in the class
        // matches column data in the database, and throw an exception
        // if they don't match pretty closely.
        
        // set the primary column
        foreach ($model->_table_cols as $key => $val) {
            if ($val['primary']) {
                $model->_primary_col = $key;
                break;
            }
        }
    }
    
    /**
     * 
     * Fixes up special column indicator properties, and post-sets the 
     * $_inherit_model value based on the existence of the inheritance column.
     * 
     * @param StdClass $model The model property catalog.
     * 
     * @return void
     * 
     * @todo How to make foreign_col recognize that it's inherited, and should
     * use the parent foreign_col value?  Can we just work up the chain?
     * 
     */
    protected function _fixPropertyCols($model)
    {
        // simply force these to arrays
        settype($model->_serialize_cols, 'array');
        
        // make sure these actually exist in the table, otherwise unset them
        $list = array(
            '_created_col',
            '_updated_col',
            '_primary_col',
            '_inherit_col',
        );
        
        foreach ($list as $col) {
            if (trim($model->$col) == '' ||
                ! array_key_exists($model->$col, $model->_table_cols)) {
                // doesn't exist in the table
                $model->$col = null;
            }
        }
        
        // post-set the inheritance model value
        if (! $model->_inherit_col) {
            $model->_inherit_model = null;
            $model->_inherit_base = null;
        }
        
        // set up the fetch-cols list
        settype($model->_fetch_cols, 'array');
        if (! $model->_fetch_cols) {
            $model->_fetch_cols = array_keys($model->_table_cols);
        }
        
        // the "sequence" columns.  make sure they point to a sequence name.
        // e.g., string 'col' becomes 'col' => 'col'.
        $tmp = array();
        foreach ((array) $model->_sequence_cols as $key => $val) {
            if (is_int($key)) {
                $tmp[$val] = $val;
            } else {
                $tmp[$key] = $val;
            }
        }
        $model->_sequence_cols = $tmp;
        
        // make sure we have a hint to foreign models as to what colname
        // to use when referring to this model
        if (empty($model->_foreign_col)) {
            
            if (! $model->_inherit_model) {
                // not inherited
                $model->_foreign_col = strtolower($model->_model_name)
                                     . '_' . $model->_primary_col;
            } else {
                // inherited, can't just use the model name as a column name.
                // need to find base model foreign_col value.
                $base = $this->get($model->_inherit_base);
                $model->_foreign_col = $base->_foreign_col;
            }
        }
    }
    
    /**
     * 
     * Loads accessor method lists for column and related properties.
     * 
     * These let users override how the column properties are accessed
     * through the magic __get, __set, etc. methods.
     * 
     * @param StdClass $model The model property catalog.
     * 
     * @param array $methods The list of all methods from the model.
     * 
     * @return void
     * 
     */
    protected function _fixAccessMethods($model)
    {
        // get a list of table-column and related-data properties names
        $vars = array_merge(
            array_keys($model->_table_cols),
            array_keys($model->_related)
        );
        
        // look for access methods on each one
        foreach ($vars as $var) {
            $name = str_replace('_', ' ', $var);
            $name = str_replace(' ', '', ucwords($name));
            $list = array(
                "get"   => "__get$name",
                "set"   => "__set$name",
                "isset" => "__isset$name",
                "unset" => "__unset$name",
            );
            foreach ($list as $type => $method) {
                if (method_exists($model->_class, $method)) {
                    $model->_access_methods[$type][$var] = $method;
                }
            }
        }
    }
    
    /**
     * 
     * Loads the array-name for user input to this model.
     * 
     * @param StdClass $model The model property catalog.
     * 
     * @return void
     * 
     */
    protected function _fixModelName($model)
    {
        if (! $model->_model_name) {
            if ($model->_inherit_model) {
                $model->_model_name = $model->_inherit_model;
            } else {
                // get the part after the last Model_ portion
                $pos = strpos($model->_class, 'Model_');
                if ($pos) {
                    $model->_model_name = substr($model->_class, $pos+6);
                } else {
                    $model->_model_name = $model->_class;
                }
            }
        }
    }
    
    /**
     * 
     * Loads the baseline data filters for each column.
     * 
     * @param StdClass $model The model property catalog.
     * 
     * @return void
     * 
     */
    protected function _fixFilters($model)
    {
        // make sure we have a datafilter class
        if (empty($model->_datafilter_class)) {
            $class = $model->_stack->load('DataFilter', false);
            if (! $class) {
                $class = 'Solar_Sql_Model_DataFilter';
            }
            $model->_datafilter_class = $class;
        }
        
        // make sure filters are an array
        settype($model->_filters, 'array');
        
        // make sure that strings are converted
        // to arrays so that _applyFilters() works properly.
        foreach ($model->_filters as $col => $list) {
            foreach ($list as $key => $val) {
                if (is_string($val)) {
                    $model->_filters[$col][$key] = array($val);
                }
            }
        }
        
        // low and high range values for integer filters
        $range = array(
            'smallint' => array(pow(-2, 15), pow(+2, 15) - 1),
            'int'      => array(pow(-2, 31), pow(+2, 31) - 1),
            'bigint'   => array(pow(-2, 63), pow(+2, 63) - 1)
        );
        
        // add final fallback filters based on data type
        foreach ($model->_table_cols as $col => $info) {
            
            $type = $info['type'];
            
            switch ($type) {
            case 'bool':
                $model->_filters[$col][] = array('validateBoolean');
                $model->_filters[$col][] = array('sanitizeBoolean');
                break;
                
            case 'char':
            case 'varchar':
                $model->_filters[$col][] = array('validateString');
                $model->_filters[$col][] = array('validateMaxLength',
                    $info['size']);
                $model->_filters[$col][] = array('sanitizeString');
                break;
                
            case 'smallint':
            case 'int':
            case 'bigint':
                $model->_filters[$col][] = array('validateInt');
                $model->_filters[$col][] = array('validateRange',
                    $range[$type][0], $range[$type][1]);
                $model->_filters[$col][] = array('sanitizeInt');
                break;
                
            case 'numeric':
                $model->_filters[$col][] = array('validateNumeric');
                $model->_filters[$col][] = array('validateSizeScope',
                    $info['size'], $info['scope']);
                $model->_filters[$col][] = array('sanitizeNumeric');
                break;
                
            case 'float':
                $model->_filters[$col][] = array('validateFloat');
                $model->_filters[$col][] = array('sanitizeFloat');
                break;
                
            case 'clob':
                $model->_filters[$col][] = array('validateString');
                $model->_filters[$col][] = array('sanitizeString');
                break;
                
            case 'date':
                $model->_filters[$col][] = array('validateIsoDate');
                $model->_filters[$col][] = array('sanitizeIsoDate');
                break;
                
            case 'time':
                $model->_filters[$col][] = array('validateIsoTime');
                $model->_filters[$col][] = array('sanitizeIsoTime');
                break;
                
            case 'timestamp':
                $model->_filters[$col][] = array('validateIsoTimestamp');
                $model->_filters[$col][] = array('sanitizeIsoTimestamp');
                break;
            }
        }
    }
    
    /**
     * 
     * Corrects the relationship definitions in $this->_related.
     * 
     * `name`
     * : (string) The name for this relationship; becomes a property key in
     *   the record, so it should not be allowed to conflict with native
     *   column names.
     * 
     * `type`
     * : (string) The association type: has_one/belongs_to, has_many,
     *   shares_many.
     * 
     * `foreign_model`
     * : (string) The class name of the foreign model. Default is the first
     *   matching class for the relationship name, as loaded from the parent
     *   class stack. Automatically honors single-table inheritance.
     * 
     * `foreign_table`
     * : (string) The name of the table for the foreign model. Default is the
     *   table specified by the foreign model.
     * 
     * `foreign_alias`
     * : (string) Aliases the foreign table to this name. Default is the 
     *   relationship name.
     * 
     * `foreign_col`
     * : (string) The name of the column to join with in the *foreign* table.
     *   This forms one-half of the relationship.  Default is per association
     *   type.
     * 
     * `foreign_inherit`
     * : (string) If the foreign model has an inheritance type, this is a
     *   condition suitable for WHERE and JOIN clauses to retrieve only 
     *   records of the proper model.
     *   
     * `native_col`
     * : (string) The name of the column to join with in the *native* table.
     *   This forms one-half of the relationship.  Default is per association
     *   type.
     * 
     * `distinct`
     * : (bool) Should the relationship be DISTINCT or not?  Default false.
     * 
     * `where`
     * : (string|array) Additional WHERE clause to determine what record is
     *   returned.  Default is no conditions.
     * 
     * `group`
     * : (string|array) A GROUP clause to determine how foreign records are
     *   grouped.  Default is none.
     * 
     * `having`
     * : (string|array) A HAVING clause to determine a post-grouping
     *   condition.  Default is none.
     * 
     * `order`
     * : (string|array) An ORDER clause to determine how foreign records are
     *   ordered. Default is the foreign model default order.
     * 
     * `paging`
     * : (int) Retrieve this many records per page (0 for all).  Default is
     *   the foreign model default paging value.
     * 
     * `fetch`
     * : (string) Fetch one, all, assoc, value, etc.
     * 
     * `cols`
     * : (array|string) Retrieve these columns from the foreign model.
     *   Default is all columns; primary-key and inheritance columns are
     *   added automatically.
     * 
     * <http://ar.rubyonrails.com/classes/ActiveRecord/Associations/ClassMethods.html>
     * 
     * There is a "virtual" key as well, `foreign_key`, that automatically
     * populates the `native_col` or `foreign_col` value for you, based on the
     * association type.  This will be used **only** when `native_col` **and**
     * `foreign_col` are not set.
     * 
     * @param StdClass $model The model property catalog.
     * 
     * @return void
     * 
     */
    protected function _fixRelated($model)
    {
        foreach ($model->_related as $name => $opts) {
            
            // is the relation name already a column name?
            if (array_key_exists($name, $model->_table_cols)) {
                throw $this->_exception(
                    'ERR_RELATION_NAME_CONFLICT',
                    array('name' => $name)
                );
            } else {
                $opts['name'] = $name;
            }
            
            // what type of association is this?
            $type = $opts['type'];
            
            // make sure we have at least a base model name
            if (empty($opts['foreign_model'])) {
                $opts['foreign_model'] = $name;
            }
            
            // can we load a related model class?
            // first, look through the hierarchy.
            $class = $model->_stack->load($opts['foreign_model'], false);
            if ($class) {
                $opts['foreign_model'] = $class;
            } else {
                // not in the hierarchy. look for the model class literally.
                // this will throw an exception if the class cannot be 
                // found anywhere.
                try {
                    Solar::loadClass($opts['foreign_model']);
                } catch (Solar_Exception $e) {
                    throw $this->_exception('ERR_LOAD_FOREIGN_MODEL', array(
                        'native_model' => $model->_class,
                        'related_name' => $name,
                        'related_opts' => $opts,
                    ));
                }
            }
            
            // retain the model class name
            $class = $opts['foreign_model'];
            
            // catalog data for the foreign model
            $foreign = $this->get($class);
            
            // the foreign table name
            if (empty($opts['foreign_table'])) {
                $opts['foreign_table'] = $foreign->_table_name;
            }
            
            // the foreign table alias
            if (empty($opts['foreign_alias'])) {
                $opts['foreign_alias'] = $name;
            }
            
            // custom WHERE clauses
            if (empty($opts['where'])) {
                $opts['where'] = array();
            }
            settype($opts['where'], 'array');
            
            // custom ORDER clause
            if (empty($opts['order'])) {
                $opts['order'] = $foreign->_order;
            }
            settype($opts['order'], 'array');
            
            // the list of foreign table cols to retrieve
            if (empty($opts['cols'])) {
                $opts['cols'] = $foreign->_fetch_cols;
            } elseif (is_string($opts['cols'])) {
                $opts['cols'] = explode(',', $opts['cols']);
            } else {
                settype($opts['cols'], 'array');
            }
            
            // make sure we always retrieve the foreign primary key value,
            // if there is one.
            $col = $foreign->_primary_col;
            if ($col && ! in_array($col, $opts['cols'])) {
                $opts['cols'][] = $col;
            }
            
            // if inheritance is turned on for the foreign mode,
            // make sure we always retrieve the foreign inheritance value.
            $col = $foreign->_inherit_col;
            if ($col && ! in_array($col, $opts['cols'])) {
                $opts['cols'][] = $col;
            }
            
            // if inheritance is turned on, force the foreign_inherit
            // column and value
            if ($foreign->_inherit_col && $foreign->_inherit_model) {
                $opts['foreign_inherit_col'] = $foreign->_inherit_col;
                $opts['foreign_inherit_val'] = $foreign->_inherit_model;
            } else {
                $opts['foreign_inherit_col'] = null;
                $opts['foreign_inherit_val'] = null;
            }
            
            // paging from the foreign model
            if (empty($opts['paging'])) {
                $opts['paging'] = $foreign->_paging;
            }
            
            // default page number. note that we don't use empty() here,
            // because we want to allow for "page = 0".
            if (! array_key_exists('page', $opts)) {
                $opts['page'] = 1;
            }
            
            // distinct?
            if (empty($opts['distinct'])) {
                $opts['distinct'] = null;
            }
            
            // group?
            if (empty($opts['group'])) {
                $opts['group'] = null;
            }
            
            // having?
            if (empty($opts['having'])) {
                $opts['having'] = null;
            }
            
            // and now, the tricky part ;-)
            switch ($opts['type']) {
            case 'belongs_to':
                $this->_fixRelatedBelongsTo($opts, $model, $foreign);
                break;
            case 'has_one':
                $this->_fixRelatedHasOne($opts, $model, $foreign);
                break;
            case 'has_many':
                // this will check for "has_many through" as well
                $this->_fixRelatedHasMany($opts, $model, $foreign);
                break;
            }
            
            // retain the corrected values in a standard order
            $model->_related[$name] = array(
                'name'                => $name,
                'type'                => $opts['type'],
                'foreign_model'       => $opts['foreign_model'],
                'foreign_table'       => $opts['foreign_table'],
                'foreign_alias'       => $opts['foreign_alias'],
                'foreign_col'         => $opts['foreign_col'],
                'foreign_inherit_col' => $opts['foreign_inherit_col'],
                'foreign_inherit_val' => $opts['foreign_inherit_val'],
                'foreign_primary_col' => $foreign->_primary_col,
                'native_col'          => $opts['native_col'],
                'through'             => $opts['through'],
                'through_table'       => $opts['through_table'],
                'through_alias'       => $opts['through_alias'],
                'through_native_col'  => $opts['through_native_col'],
                'through_foreign_col' => $opts['through_foreign_col'],
                'distinct'            => $opts['distinct'],
                'where'               => $opts['where'],
                'group'               => $opts['group'],
                'having'              => $opts['having'],
                'order'               => $opts['order'],
                'paging'              => (int) $opts['paging'],
                'page'                => (int) $opts['page'],
                'cols'                => $opts['cols'],
            );
        }
    }
    
    /**
     * 
     * A support method for _fixRelated() to handle belongs-to relationships.
     * 
     * @param array &$opts The relationship options; these are modified in-
     * place.
     * 
     * @param StdClass $model The catalog entry for the native model (i.e.,
     * this model).
     * 
     * @param StdClass $foreign The catalog entry for the foreign model.
     * 
     * @return void
     * 
     */
    protected function _fixRelatedBelongsTo(&$opts, $model, $foreign)
    {
        // a little magic
        if (empty($opts['foreign_col']) && empty($opts['native_col']) &&
            ! empty($opts['foreign_key'])) {
            // foreign key is stored in the native model
            $opts['native_col'] = $opts['foreign_key'];
        }
        
        // the foreign column
        if (empty($opts['foreign_col'])) {
            // named by foreign primary key (e.g., foreign.id)
            $opts['foreign_col'] = $foreign->_primary_col;
        }
        
        // the native column
        if (empty($opts['native_col'])) {
            // named by foreign table name and foreign primary key
            // (e.g., native.foreign_id)
            $opts['native_col'] = $foreign->_foreign_col;
        }
        
        // not "through" anything
        $this->_fixRelatedNotThrough($opts);
    }
    
    /**
     * 
     * A support method for _fixRelated() to handle has-one relationships.
     * 
     * @param array &$opts The relationship options; these are modified in-
     * place.
     * 
     * @param StdClass $model The catalog entry for the native model (i.e.,
     * this model).
     * 
     * @param StdClass $foreign The catalog entry for the foreign model.
     * 
     * @return void
     * 
     */
    protected function _fixRelatedHasOne(&$opts, $model, $foreign)
    {
        // a little magic
        if (empty($opts['foreign_col']) && empty($opts['native_col']) &&
            ! empty($opts['foreign_key'])) {
            // foreign key is stored in the foreign model
            $opts['foreign_col'] = $opts['foreign_key'];
        }
        
        // the foreign column
        if (empty($opts['foreign_col'])) {
            // named by native table and native primary (e.g.,
            // foreign.native_id)
            $opts['foreign_col'] = $model->_foreign_col;
        }
        
        // the native column
        if (empty($opts['native_col'])) {
            // named by native primary key (e.g., native.id)
            $opts['native_col'] = $model->_primary_col;
        }
        
        // not "through" anything
        $this->_fixRelatedNotThrough($opts);
    }
    
    /**
     * 
     * A support method for _fixRelated() to handle has-many relationships.
     * 
     * @param array &$opts The relationship options; these are modified in-
     * place.
     * 
     * @param StdClass $model The catalog entry for the native model (i.e.,
     * this model).
     * 
     * @param StdClass $foreign The catalog entry for the foreign model.
     * 
     * @return void
     * 
     */
    protected function _fixRelatedHasMany(&$opts, $model, $foreign)
    {
        // a little magic
        if (empty($opts['foreign_col']) && empty($opts['native_col']) &&
            ! empty($opts['foreign_key'])) {
            // foreign key is stored in the foreign model
            $opts['foreign_col'] = $opts['foreign_key'];
        }
        
        // the foreign column
        if (empty($opts['foreign_col'])) {
            // named by native table and native primary (e.g.,
            // foreign.native_id)
            $opts['foreign_col'] = $model->_foreign_col;
        }
        
        // the native column
        if (empty($opts['native_col'])) {
            // named by native primary key (e.g., native.id)
            $opts['native_col'] = $model->_primary_col;
        }
        
        // are we working through another relationship?
        if (empty($opts['through'])) {
            // not "through" anything
            $this->_fixRelatedNotThrough($opts);
        } else {
            // through another relationship
            $this->_fixRelatedHasManyThrough($opts, $model, $foreign);
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
     * @param StdClass $model The catalog entry for the native model (i.e.,
     * this model).
     * 
     * @param StdClass $foreign The catalog entry for the foreign model.
     * 
     * @return void
     * 
     */
    protected function _fixRelatedHasManyThrough(&$opts, $model, $foreign)
    {
        // make sure the "through" relationship exists
        if (empty($model->_related[$opts['through']])) {
            throw $this->_exception('ERR_THROUGH_NOT_EXIST', array(
                'model'   => $model->_class,
                'name'    => $opts['name'],
                'through' => $opts['through'],
            ));
        }
        
        // convenient reference to the "through" relationship
        $through = $model->_related[$opts['through']];
        
        // get the through-table
        if (empty($opts['through_table'])) {
            $opts['through_table'] = $through['foreign_table'];
        }
        
        // get the through-alias
        if (empty($opts['through_alias'])) {
            $opts['through_alias'] = $through['foreign_alias'];
        }
        
        // a little magic
        if (empty($opts['through_native_col']) &&
            empty($opts['through_foreign_col']) &&
            ! empty($opts['through_key'])) {
            // use this
            $opts['through_foreign_key'] = $opts['through_key'];
        }
        
        // what's the native model key in the through table?
        if (empty($opts['through_native_col'])) {
            $opts['through_native_col'] = $through['foreign_col'];
        }
        
        // what's the foreign model key in the through table?
        if (empty($opts['through_foreign_col'])) {
            $opts['through_foreign_col'] = $foreign->_foreign_col;
        }
    }
    
    /**
     * 
     * A support method for _fixRelatedHasMany() to set the "through" options
     * to blanks.
     * 
     * @param array &$opts The relationship options; these are modified in-
     * place.
     * 
     * @param StdClass $model The catalog entry for the native model (i.e.,
     * this model).
     * 
     * @param StdClass $foreign The catalog entry for the foreign model.
     * 
     * @return void
     * 
     */
    protected function _fixRelatedNotThrough(&$opts)
    {
        $opts['through']                = null;
        $opts['through_table']          = null;
        $opts['through_alias']          = null;
        $opts['through_native_col']     = null;
        $opts['through_foreign_col']    = null;
    }
    
    /**
     * 
     * Fixes $this->_indexes listings.
     * 
     * @param StdClass $model The model property catalog.
     * 
     * @return void
     * 
     */
    protected function _fixIndexes($model)
    {
        // baseline index definition
        $baseidx = array(
            'name'    => null,
            'type'    => 'normal',
            'cols'    => null,
        );
        
        // fix up each index to have a full set of info
        foreach ($model->_indexes as $key => $val) {
            
            if (is_int($key) && is_string($val)) {
                // array('col')
                $info = array(
                    'name' => $val,
                    'type' => 'normal',
                    'cols' => array($val),
                );
            } elseif (is_string($key) && is_string($val)) {
                // array('col' => 'unique')
                $info = array(
                    'name' => $key,
                    'type' => $val,
                    'cols' => array($key),
                );
            } else {
                // array('alt' => array('type' => 'normal', 'cols' => array(...)))
                $info = array_merge($baseidx, (array) $val);
                $info['name'] = (string) $key;
                settype($info['cols'], 'array');
            }
            
            $model->_indexes[$key] = $info;
        }
    }
    
    /**
     * 
     * Creates the table and indexes in the database using $model->_table_cols
     * and $model->_indexes.
     * 
     * @param StdClass $model The model property catalog.
     * 
     * @return void
     * 
     */
    protected function _createTableAndIndexes($model)
    {
        /**
         * Create the table.
         */
        $this->_sql->createTable(
            $model->_table_name,
            $model->_table_cols
        );
        
        /**
         * Create the indexes.
         */
        foreach ($model->_indexes as $name => $info) {
            try {
                // create this index
                $this->_sql->createIndex(
                    $model->_table_name,
                    $info['name'],
                    $info['type'] == 'unique',
                    $info['cols']
                );
            } catch (Exception $e) {
                // cancel the whole deal.
                $this->_sql->dropTable($model->_table_name);
                throw $e;
            }
        }
    }
}
