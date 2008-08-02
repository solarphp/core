<?php
/**
 * 
 * An SQL-centric Model class combining TableModule and TableDataGateway,
 * using a Collection of Record objects for returns, with integrated caching
 * of versioned result data.
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
abstract class Solar_Sql_Model extends Solar_Base
{
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `sql`
     * : (dependency) A Solar_Sql dependency.
     * 
     * `cache`
     * : (dependency) A Solar_Cache dependency for the Solar_Sql_Model_Cache
     *   object.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Model = array(
        'sql'   => 'sql',
        'cache' => array(
            'adapter' => 'Solar_Cache_Adapter_Var',
        ),
        'auto_cache' => false,
    );
    
    /**
     * 
     * A Solar_Sql dependency object.
     * 
     * @var Solar_Sql_Adapter
     * 
     */
    protected $_sql = null;
    
    /**
     * 
     * A Solar_Sql_Model_Cache object.
     * 
     * @var Solar_Sql_Model_Cache
     * 
     */
    protected $_cache = null;
    
    /**
     * 
     * When data values for this model are part of an array, use this name
     * as the array key for those values.
     * 
     * When inheritance is enabled, the default is the $_inherit_model value,
     * otherwise, the default is the $_table_name.
     * 
     * @var string
     * 
     */
    protected $_model_name;
    
    // -----------------------------------------------------------------
    //
    // Classes
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * A Solar_Class_Stack object for fallback hierarchy.
     * 
     * @var Solar_Class_Stack
     * 
     */
    protected $_stack;
    
    /**
     * 
     * The results of get_class($this) so we don't call get_class() all the time.
     * 
     * @var string
     * 
     */
    protected $_class;
    
    /**
     * 
     * The final fallback class for an individual record.
     * 
     * Default is Solar_Sql_Model_Record.
     * 
     * @var string
     * 
     */
    protected $_record_class = 'Solar_Sql_Model_Record';
    
    /**
     * 
     * The final fallback class for collections of records.
     * 
     * Default is Solar_Sql_Model_Collection.
     * 
     * @var string
     * 
     */
    protected $_collection_class = 'Solar_Sql_Model_Collection';
    
    /**
     * 
     * The class to use for building SELECT statements.
     * 
     * @var string
     * 
     */
    protected $_select_class = 'Solar_Sql_Select';
    
    /**
     * 
     * The class to use for filter chains.
     * 
     * @var string
     * 
     */
    protected $_filter_class = null;
    
    protected $_cache_class = 'Solar_Sql_Model_Cache';
    
    // -----------------------------------------------------------------
    //
    // Table and index definition
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * The table name.
     * 
     * @var string
     * 
     */
    protected $_table_name = null;
    
    /**
     * 
     * The column specification array for all columns in this table.
     * 
     * Used in auto-creation, and for sync-checks.
     * 
     * Will be overridden by _fixTableCols() when it reads the table info, so you
     * don't *have* to enter anything here ... but if it's empty, you won't
     * get auto-creation.
     * 
     * Each element in this array looks like this...
     * 
     * {{code: php
     *     $_table_cols = array(
     *         'col_name' => array(
     *             'name'    => (string) the col_name, same as the key
     *             'type'    => (string) char, varchar, date, etc
     *             'size'    => (int) column size
     *             'scope'   => (int) decimal places
     *             'default' => (string) default value
     *             'require' => (bool) is this a required (non-null) column?
     *             'primary' => (bool) is this part of the primary key?
     *             'autoinc' => (bool) auto-incremented?
     *          ),
     *     );
     * }}
     * 
     * @var array
     * 
     */
    protected $_table_cols = array();
    
    /**
     * 
     * The index specification array for all indexes on this table.
     * 
     * Used only in auto-creation.
     * 
     * The array should be in this format ...
     * 
     * {{code: php
     *     // the index type: 'normal' or 'unique'
     *     $type = 'normal';
     * 
     *     // index on a single column:
     *     // CREATE INDEX idx_name ON table_name (col_name)
     *     $this->_index['idx_name'] = array(
     *         'type' => $type,
     *         'cols' => 'col_name'
     *     );
     * 
     *     // index on multiple columns:
     *     // CREATE INDEX idx_name ON table_name (col_1, col_2, ... col_N)
     *     $this->_index['idx_name'] = array(
     *         'type' => $type,
     *         'cols' => array('col_1', 'col_2', ..., 'col_N')
     *     );
     * 
     *     // easy shorthand for an index on a single column,
     *     // giving the index the same name as the column:
     *     // CREATE INDEX col_name ON table_name (col_name)
     *     $this->_index['col_name'] = $type;
     * }}
     * 
     * The $type may be 'normal' or 'unique'.
     * 
     * @var array
     * 
     */
    protected $_index = array();
    
    // -----------------------------------------------------------------
    //
    // Special columns and column behaviors
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * A list of column names that don't exist in the table, but should be
     * calculated by the model as-needed.
     * 
     * @var array
     * 
     */
    protected $_calculate_cols = array();
    
    /**
     * 
     * A list of column names that use sequence values.
     * 
     * When the column is present in a data array, but its value is null,
     * a sequence value will automatically be added.
     * 
     * @var array
     * 
     */
    protected $_sequence_cols = array();
    
    /**
     * 
     * A list of column names to serialize/unserialize automatically.
     * 
     * Will be unserialized by the Record class as the values are loaded,
     * then re-serialized just before insert/update in the Model class.
     * 
     * @var array
     * 
     */
    protected $_serialize_cols = array();
    
    /**
     * 
     * The column name for the primary key; default is 'id'.
     * 
     * @var string
     * 
     */
    protected $_primary_col = 'id';
    
    /**
     * 
     * The column name for 'created' timestamps; default is 'created'.
     * 
     * @var string
     * 
     */
    protected $_created_col = 'created';
    
    /**
     * 
     * The column name for 'updated' timestamps; default is 'updated'.
     * 
     * @var string
     * 
     */
    protected $_updated_col = 'updated';
    
    /**
     * 
     * Other models that relate to this model should use this as the foreign-key
     * column name.
     * 
     * @var string
     * 
     */
    protected $_foreign_col = null;
    
    // -----------------------------------------------------------------
    //
    // Other/misc
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Relationships to other Model classes.
     * 
     * Keyed on a "virtual" column name, which will be used as a property
     * name in returned records.
     * 
     * @var array
     * 
     */
    protected $_related = array();
    
    /**
     * 
     * Filters to validate and sanitize column data.
     * 
     * Default is to use validate*() and sanitize*() methods in the filter
     * class, but if the method exists locally, it will be used instead.
     * 
     * The filters apply only to Record objects from the model; if you use
     * the model insert() and update() methods directly, the filters are not
     * applied.
     * 
     * Example usage follows; note that "_validate" and "_sanitize" refer
     * to internal (protected) filtering methods that have access to the
     * entire data set being filtered.
     * 
     * {{code: php
     *     // filter 'col_1' to have only alpha chars, with a max length of
     *     // 32 chars
     *     $this->_filters['col_1'][] = 'sanitizeStringAlpha';
     *     $this->_filters['col_1'][] = array('validateMaxLength', 32);
     * 
     *     // filter 'col_2' to have only numeric chars, validate as an
     *     // integer, in a range of -10 to +10.
     *     $this->_filters['col_2'][] = 'sanitizeNumeric';
     *     $this->_filters['col_2'][] = 'validateInteger';
     *     $this->_filters['col_2'][] = array('validateRange', -10, +10);
     * 
     *     // filter 'handle' to have only alpha-numeric chars, with a length
     *     // of 6-14 chars, and unique in the table.
     *     $this->_filters['handle'][] = 'sanitizeStringAlnum';
     *     $this->_filters['handle'][] = array('validateRangeLength', 6, 14);
     *     $this->_filters['handle'][] = 'validateUnique';
     * 
     *     // filter 'email' to have only emails-allowed chars, validate as an
     *     // email address, and be unique in the table.
     *     $this->_filters['email'][] = 'sanitizeStringEmail';
     *     $this->_filters['email'][] = 'validateEmail';
     *     $this->_filters['email'][] = 'validateUnique';
     * 
     *     // filter 'passwd' to be not-blank, and should match any existing
     *     // 'passwd_confirm' value.
     *     $this->_filters['passwd'][] = 'validateNotBlank';
     *     $this->_filters['passwd'][] = 'validateConfirm';
     * }}
     * 
     * @var array
     * 
     * @see $_filter_class
     * 
     * @see _addFilter()
     * 
     */
    protected $_filters;
    
    // -----------------------------------------------------------------
    //
    // Single-table inheritance
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * The base model this class is inherited from, in single-table inheritance.
     * 
     * @var string
     * 
     */
    protected $_inherit_base = null;
    
    /**
     * 
     * When inheritance is turned on, the class name value for this class
     * in $_inherit_col.
     * 
     * @var string
     * 
     */
    protected $_inherit_model = false;
    
    /**
     * 
     * The column name that tracks single-table inheritance; default is
     * 'inherit'.
     * 
     * @var string
     * 
     */
    protected $_inherit_col = 'inherit';
    
    // -----------------------------------------------------------------
    //
    // Select options
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Only fetch these columns from the table.
     * 
     * @var array
     * 
     */
    protected $_fetch_cols;
    
    /**
     * 
     * The default order when fetching rows.
     * 
     * @var array
     * 
     */
    protected $_order;
    
    /**
     * 
     * The number of rows per page when selecting.
     * 
     * @var int
     * 
     */
    protected $_paging = 10;
    
    protected $_inflect;
    
    // -----------------------------------------------------------------
    //
    // Constructor and magic methods
    //
    // -----------------------------------------------------------------
    
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
        
        // inflection reference
        $this->_inflect = Solar_Registry::get('inflect');
        
        // our class name so that we don't call get_class() all the time
        $this->_class = get_class($this);
        
        // connect to the database
        $this->_sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // user-defined setup
        $this->_setup();
    
        // follow-on cleanup of critical user-defined values
        $this->_fixStack();
        $this->_fixTableName();
        $this->_fixIndex();
        $this->_fixTableCols(); // also creates table if needed
        $this->_fixModelName();
        $this->_fixOrder();
        $this->_fixPropertyCols();
        $this->_fixFilters(); // including filter class
        $this->_fixCache(); // including cache class
        
        // create the cache object and set its model
        $this->_cache = Solar::factory($this->_cache_class, array(
            'cache'  => $this->_config['cache'],
        ));
        $this->_cache->setModel($this);
    }
    
    /**
     * 
     * Call this before you unset the instance so that you release the memory
     * from all the internal child objects.
     * 
     * @return void
     * 
     */
    public function free()
    {
        foreach ($this->_related as $key => $val) {
            unset($this->_related[$key]);
        }
        
        unset($this->_cache);
    }
    
    /**
     * 
     * User-defined setup.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
    
    // -----------------------------------------------------------------
    //
    // Getters and setters
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Read-only access to protected model properties.
     * 
     * @param string $key The requested property; e.g., `'foo'` will read from
     * `$_foo`.
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        $var = "_$key";
        if (property_exists($this, $var)) {
            return $this->$var;
        } else {
            throw $this->_exception('ERR_PROPERTY_NOT_DEFINED', array(
                'class' => get_class($this),
                'key' => $key,
                'var' => $var,
            ));
        }
    }
    
    /**
     * 
     * Gets the number of records per page.
     * 
     * @return int The number of records per page.
     * 
     */
    public function getPaging()
    {
        return $this->_paging;
    }
    
    /**
     * 
     * Sets the number of records per page.
     * 
     * @param int $paging The number of records per page.
     * 
     * @return void
     * 
     */
    public function setPaging($paging)
    {
        $this->_paging = (int) $paging;
    }
    
    // -----------------------------------------------------------------
    //
    // Fetch
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Magic call implements "fetchOneBy...()" and "fetchAllBy...()" for
     * columns listed in the method name.
     * 
     * You *have* to specify params for all of the named columns.
     * 
     * Optionally, you can pass a final array for the "extra" paramters to the
     * fetch ('order', 'group', 'having', etc.)
     * 
     * Example:
     * 
     * {{code: php
     *     // fetches one record by status
     *     $model->fetchOneByStatus('draft');
     * 
     *     // fetches all records by area_id and owner_handle
     *     $model->fetchAllByAreaIdAndOwnerHandle($area_id, $owner_handle);
     * 
     *     // fetches all records by area_id and owner_handle,
     *     // with ordering and page-limiting
     *     $extra = array('order' => 'area_id DESC', 'page' => 2);
     *     $model->fetchAllByAreaIdAndOwnerHandle($area_id, $owner_handle, $extra);
     * }}
     * 
     * @param string $method The virtual method name, composed of "fetchOneBy"
     * or "fetchAllBy", with a series of column names joined by "And".
     * 
     * @param array $params Parameters to pass to the method: one for each
     * column, plus an optional one for extra fetch parameters.
     * 
     * @return mixed
     * 
     * @todo Expand to cover assoc, col, pairs, and value.
     * 
     */
    public function __call($method, $params)
    {
        // fetch a record, or a collection?
        if (substr($method, 0, 7) == 'fetchBy') {
            // fetch a record
            $fetch = 'fetchOne';
            $method = substr($method, 7);
        } elseif (substr($method, 0, 10) == 'fetchOneBy') {
            // fetch a record
            $fetch = 'fetchOne';
            $method = substr($method, 10);
        } elseif (substr($method, 0, 10) == 'fetchAllBy') {
            // fetch a collection
            $fetch = 'fetchAll';
            $method = substr($method, 10);
        } else {
            throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
                'method' => $method,
            ));
        }
        
        // get the list of columns from the remainder of the method name
        // e.g., fetchAllByParentIdAndAreaId => ParentId, AreaId
        $list = explode('And', $method);
        
        // build the fetch params
        $where = array();
        foreach ($list as $key => $col) {
            // convert from ColName to col_name
            $col = strtolower(
                $this->_inflect->camelToUnder($col)
            );
            $where["{$this->_model_name}.$col = ?"] = $params[$key];
        }
        
        // add the last param after last column name as the "extra" settings
        // (order, group, having, page, paging, etc).
        $k = count($list);
        if (count($params) > $k) {
            $opts = (array) $params[$k];
        } else {
            $opts = array();
        }
        
        // merge the where with the base fetch params
        $opts = array_merge($opts, array(
            'where' => $where,
        ));
        
        // do the fetch
        return $this->$fetch($opts);
    }
    
    /**
     * 
     * Fetches a record or collection by primary key value(s).
     * 
     * @param int|array $spec The primary key value for a single record, or an
     * array of primary key values for a collection of records.
     * 
     * @return Solar_Sql_Model_Record|Solar_Sql_Model_Collection A record or
     * record-set object.
     * 
     */
    public function fetch($spec)
    {
        $col = "{$this->_model_name}.{$this->_primary_col}";
        if (is_array($spec)) {
            $where = array("$col IN (?)" => $spec);
            return $this->fetchAll(array('where' => $where, 'order' => $col));
        } else {
            $where = array("$col = ?" => $spec);
            return $this->fetchOne(array('where' => $where, 'order' => $col));
        }
    }
    
    /**
     * 
     * Fetches a collection of all records by arbitrary parameters.
     * 
     * Recognized parameters for the fetch are:
     * 
     * `eager`
     * : (string|array) Eager-fetch records from these related models.
     * 
     * `distinct`
     * : (bool) Use DISTINCT?
     * 
     * `cols`
     * : (string|array) Return only these columns.
     * 
     * `where`
     * : (string|array) A Solar_Sql_Select::multiWhere() value parameter to
     *   restrict which records are returned.
     * 
     * `group`
     * : (string|array) GROUP BY these columns.
     * 
     * `having`
     * : (string|array) HAVING these column values.
     * 
     * `order`
     * : (string|array) ORDER BY these columns.
     * 
     * `paging`
     * : (int) Return this many records per page.
     * 
     * `page`
     * : (int) Return only records from this page-number.
     * 
     * `limit`
     * : (int|array) Limit to a count of this many records (when an integer), 
     *   or limit by count and offset (when an array).  When `limit` is non-
     *   empty, the `page` and `paging` params are ignored.
     * 
     * `bind`
     * : (array) Key-value pairs to bind into the query.
     * 
     * `cache`
     * : (bool) Use the cache?
     * 
     * `cache_key`
     * : (bool) An explicit cache key to use; otherwise, defaults to the
     *   serialized SELECT params.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having', 'order', etc.
     * 
     * @return Solar_Sql_Model_Collection A collection object.
     * 
     */
    public function fetchAll($params = array())
    {
        // fetch the result array and select object
        list($result, $select) = $this->_fetchResultSelect('all', $params);
        if (! $result) {
            return array();
        }
        
        // create a collection from the result
        $coll = $this->newCollection($result);
        
        // add has-many eager data to the collection
        foreach ((array) $params['eager'] as $name) {
            $related = $this->getRelated($name);
            if ($related->type == 'has_many') {
                // should we send along $params instead for easier caching?
                $result = $related->fetchArray($select);
                $coll->loadRelated($name, $result);
            }
        }
        
        // add pager-info to the collection
        if ($params['count_pages']) {
            $this->_setCollectionPagerInfo($coll, $params);
        }
        
        // done
        return $coll;
    }
    
    /**
     * 
     * The same as fetchAll(), except the record collection is keyed on the
     * first column of the results (instead of being a strictly sequential
     * array.)
     * 
     * Recognized parameters for the fetch are:
     * 
     * `eager`
     * : (string|array) Eager-fetch records from these related models.
     * 
     * `distinct`
     * : (bool) Use DISTINCT?
     * 
     * `cols`
     * : (string|array) Return only these columns.
     * 
     * `where`
     * : (string|array) A Solar_Sql_Select::multiWhere() value parameter to
     *   restrict which records are returned.
     * 
     * `group`
     * : (string|array) GROUP BY these columns.
     * 
     * `having`
     * : (string|array) HAVING these column values.
     * 
     * `order`
     * : (string|array) ORDER BY these columns.
     * 
     * `paging`
     * : (int) Return this many records per page.
     * 
     * `page`
     * : (int) Return only records from this page-number.
     * 
     * `limit`
     * : (int|array) Limit to a count of this many records (when an integer), 
     *   or limit by count and offset (when an array).  When `limit` is non-
     *   empty, the `page` and `paging` params are ignored.
     * 
     * `bind`
     * : (array) Key-value pairs to bind into the query.
     * 
     * `cache`
     * : (bool) Use the cache?
     * 
     * `cache_key`
     * : (bool) An explicit cache key to use; otherwise, defaults to the
     *   serialized SELECT params.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having', 'order', etc.
     * 
     * @return Solar_Sql_Model_Collection A collection object.
     * 
     */
    public function fetchAssoc($params = array())
    {
        // fetch the result array and select object
        list($result, $select) = $this->_fetchResultSelect('assoc', $params);
        if (! $result) {
            return array();
        }
        
        // create a collection from the result
        $coll = $this->newCollection($result);
        
        // add has-many eager data to the collection
        foreach ((array) $params['eager'] as $name) {
            $related = $this->getRelated($name);
            if ($related->type == 'has_many') {
                $result = $related->fetchArray($select);
                $coll->loadRelated($name, $result);
            }
        }
        
        // add pager-info to the collection
        if ($params['count_pages']) {
            $this->_setCollectionPagerInfo($coll, $params);
        }
        
        // done
        return $coll;
    }
    
    /**
     * 
     * Fetches an array of rows by arbitrary parameters.
     * 
     * Recognized parameters for the fetch are:
     * 
     * `eager`
     * : (string|array) Eager-fetch records from these related models.
     * 
     * `distinct`
     * : (bool) Use DISTINCT?
     * 
     * `cols`
     * : (string|array) Return only these columns.
     * 
     * `where`
     * : (string|array) A Solar_Sql_Select::multiWhere() value parameter to
     *   restrict which records are returned.
     * 
     * `group`
     * : (string|array) GROUP BY these columns.
     * 
     * `having`
     * : (string|array) HAVING these column values.
     * 
     * `order`
     * : (string|array) ORDER BY these columns.
     * 
     * `paging`
     * : (int) Return this many records per page.
     * 
     * `page`
     * : (int) Return only records from this page-number.
     * 
     * `limit`
     * : (int|array) Limit to a count of this many records (when an integer), 
     *   or limit by count and offset (when an array).  When `limit` is non-
     *   empty, the `page` and `paging` params are ignored.
     * 
     * `bind`
     * : (array) Key-value pairs to bind into the query.
     * 
     * `cache`
     * : (bool) Use the cache?
     * 
     * `cache_key`
     * : (bool) An explicit cache key to use; otherwise, defaults to the
     *   serialized SELECT params.
     * 
     * @return array
     * 
     */
    public function fetchArray($params = array())
    {
        // fetch the result array and select object
        list($result, $select) = $this->_fetchResultSelect('all', $params);
        if (! $result) {
            return array();
        } else {
            return $result;
        }
    }
    
    /**
     * 
     * Sets the pager info in a collection, calling countPages() along the
     * way.
     * 
     * @param Solar_Sql_Model_Collection $coll The record collection to set
     * pager info on.
     * 
     * @param array $params The params for the original fetchAll() or
     * fetchAssoc().
     * 
     * @return void
     */
    protected function _setCollectionPagerInfo($coll, $params)
    {
        $total = $this->countPages($params);
        $start = ($params['page'] - 1) * $params['paging'];
        $coll->setPagerInfo(array(
            'count'  => $total['count'],
            'pages'  => $total['pages'],
            'paging' => $params['paging'],
            'page'   => $params['page'],
            'begin'  => $start + 1,
            'end'    => $start + $coll->count(),
        ));
    }
    
    /**
     * 
     * Fetches one record by arbitrary parameters.
     * 
     * Recognized parameters for the fetch are:
     * 
     * `eager`
     * : (string|array) Eager-fetch records from these related models.
     * 
     * `distinct`
     * : (bool) Use DISTINCT?
     * 
     * `distinct`
     * : (bool) Is this a SELECT DISTINCT?
     * 
     * `cols`
     * : (string|array) Return only these columns.
     * 
     * `where`
     * : (string|array) A Solar_Sql_Select::multiWhere() value parameter to
     *   restrict which records are returned.
     * 
     * `group`
     * : (string|array) GROUP BY these columns.
     * 
     * `having`
     * : (string|array) HAVING these column values.
     * 
     * `order`
     * : (string|array) ORDER BY these columns.
     * 
     * `bind`
     * : (array) Key-value pairs to bind into the query.
     * 
     * `cache`
     * : (bool) Use the cache?
     * 
     * `cache_key`
     * : (bool) An explicit cache key to use; otherwise, defaults to the
     *   serialized SELECT params.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having', 'order', etc.
     * 
     * @return Solar_Sql_Model_Record A record object.
     * 
     */
    public function fetchOne($params = array())
    {
        // fetch the result array and select object
        list($result, $select) = $this->_fetchResultSelect('one', $params);
        if (! $result) {
            return null;
        }
        
        // get the main record, which sets the belongs_to/has_one data
        $record = $this->newRecord($result);
        
        // get related data from each eager has_many relationship
        foreach ((array) $params['eager'] as $name) {
            $related = $this->getRelated($name);
            if ($related->type == 'has_many') {
                $record->$name = $related->fetchObject($record);
            }
        }
        
        // done
        return $record;
    }
    
    /**
     * 
     * Fetches a sequential array of values from the model, using only the
     * first column of the results.
     * 
     * Recognized parameters for the fetch are:
     * 
     * `eager`
     * : (string|array) Eager-fetch records from these related models.
     * 
     * `distinct`
     * : (bool) Use DISTINCT?
     * 
     * `cols`
     * : (string|array) Return only these columns; only the first one will
     * be honored.
     * 
     * `where`
     * : (string|array) A Solar_Sql_Select::multiWhere() value parameter to
     *   restrict which records are returned.
     * 
     * `group`
     * : (string|array) GROUP BY these columns.
     * 
     * `having`
     * : (string|array) HAVING these column values.
     * 
     * `order`
     * : (string|array) ORDER BY these columns.
     * 
     * `paging`
     * : (int) Return this many records per page.
     * 
     * `page`
     * : (int) Return only records from this page-number.
     * 
     * `limit`
     * : (int|array) Limit to a count of this many records (when an integer), 
     *   or limit by count and offset (when an array).  When `limit` is non-
     *   empty, the `page` and `paging` params are ignored.
     * 
     * `bind`
     * : (array) Key-value pairs to bind into the query.
     * 
     * `cache`
     * : (bool) Use the cache?
     * 
     * `cache_key`
     * : (bool) An explicit cache key to use; otherwise, defaults to the
     *   serialized SELECT params.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having', 'order', etc.
     * 
     * @return array
     * 
     */
    public function fetchCol($params = array())
    {
        // fetch the result array and select object
        list($result, $select) = $this->_fetchResultSelect('col', $params);
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }
    
    /**
     * 
     * Fetches an array of key-value pairs from the model, where the first
     * column is the key and the second column is the value.
     * 
     * Recognized parameters for the fetch are:
     * 
     * `eager`
     * : (string|array) Eager-fetch records from these related models.
     * 
     * `distinct`
     * : (bool) Use DISTINCT?
     * 
     * `cols`
     * : (string|array) Return only these columns; only the first two will
     *   be honored.
     * 
     * `where`
     * : (string|array) A Solar_Sql_Select::multiWhere() value parameter to
     *   restrict which records are returned.
     * 
     * `group`
     * : (string|array) GROUP BY these columns.
     * 
     * `having`
     * : (string|array) HAVING these column values.
     * 
     * `order`
     * : (string|array) ORDER BY these columns.
     * 
     * `paging`
     * : (int) Return this many records per page.
     * 
     * `page`
     * : (int) Return only elements from this page-number.
     * 
     * `bind`
     * : (array) Key-value pairs to bind into the query.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having', 'order', etc.
     * 
     * @return array
     * 
     */
    public function fetchPairs($params = array())
    {
        // fetch the result array and select object
        list($result, $select) = $this->_fetchResultSelect('pairs', $params);
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }
    
    /**
     * 
     * Fetches a single value from the model (i.e., the first column of the 
     * first record of the returned page set).
     * 
     * Recognized parameters for the fetch are:
     * 
     * `eager`
     * : (string|array) Eager-fetch records from these related models.
     * 
     * `distinct`
     * : (bool) Use DISTINCT?
     * 
     * `cols`
     * : (string|array) Return only these columns; only the first one will
     *   be honored.
     * 
     * `where`
     * : (string|array) A Solar_Sql_Select::multiWhere() value parameter to
     *   restrict which records are returned.
     * 
     * `group`
     * : (string|array) GROUP BY these columns.
     * 
     * `having`
     * : (string|array) HAVING these column values.
     * 
     * `order`
     * : (string|array) ORDER BY these columns.
     * 
     * `paging`
     * : (int) Return this many records per page.
     * 
     * `page`
     * : (int) Return only elements from this page-number.
     * 
     * `bind`
     * : (array) Key-value pairs to bind into the query.
     * 
     * `cache`
     * : (bool) Use the cache?
     * 
     * `cache_key`
     * : (bool) An explicit cache key to use; otherwise, defaults to the
     *   serialized SELECT params.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having', 'order', etc.
     * 
     * @return mixed The single value from the model query, or null.
     * 
     */
    public function fetchValue($params = array())
    {
        // fetch the result array and select object
        list($result, $select) = $this->_fetchResultSelect('value', $params);
        return $result;
    }
    
    /**
     * 
     * Returns a data result and the select used to fetch the data.
     * 
     * If caching is turned on, this will fetch from the cache (if available)
     * and save the result back to the cache (if needed).
     * 
     * @param string $type The type of fetch to perform: 'all', 'one', etc.
     * 
     * @param array &$params A reference to the params for the select; these
     * will be passed through fixSelectParams(), so the calling code doesn't
     * have to do it twice.
     * 
     * @return array An array of two elements; element 0 is the result data,
     * element 1 is the Solar_Sql_Select object used to fetch the data.  Note
     * that if the 
     */
    public function _fetchResultSelect($type, &$params)
    {
        $params = $this->fixSelectParams($params);
        $select = $this->newSelect($params);
        
        // fetch from cache?
        if ($params['cache']) {
            $key = $this->_cache->entry($params);
            $result = $this->_cache->fetch($key);
            if ($result !== false) {
                // found some data!
                return array($result, $select);
            }
        }
        
        // attempt to fetch from database, and add to the cache
        $result = $select->fetch($type);
        
        // add to cache?
        if ($params['cache']) {
            $this->_cache->add($key, $result);
        }
        
        // done
        return array($result, $select);
    }
    
    /**
     * 
     * Returns a new record with default values.
     * 
     * @param array $spec An array of user-specified data to place into the
     * new record, if any.
     * 
     * @return Solar_Sql_Model_Record A record object.
     * 
     */
    public function fetchNew($spec = null)
    {
        // the user-specifed data
        settype($spec, 'array');
        
        // the array of data for the record
        $data = array();
        
        // loop through each specified column and collect default data
        foreach ($this->_table_cols as $key => $val) {
            if (array_key_exists($key, $spec)) {
                // user-specified
                $data[$key] = $spec[$key];
            } else {
                // default value
                $data[$key] = $val['default'];
            }
        }
        
        // if we have inheritance, set that too
        if ($this->_inherit_model) {
            $key = $this->_inherit_col;
            $data[$key] = $this->_inherit_model;
        }
        
        // set placeholders for relateds.
        $names = array_keys($this->_related);
        foreach ($names as $name) {
            $data[$name] = null;
        }
        
        // done, return the proper record object
        $record = $this->newRecord($data);
        $record->setStatus('new');
        return $record;
    }
    
    /**
     * 
     * Fetches count and pages of available records.
     * 
     * @param array $params An array of clauses for the SELECT COUNT()
     * statement, including 'where', 'group, and 'having'.
     * 
     * @return array An array with keys 'count' and 'pages'; 'count' is the
     * number of records, 'pages' is the number of pages.
     * 
     */
    public function countPages($params = null)
    {
        // fix up the parameters
        $params = $this->fixSelectParams($params);
        
        // add a fake param called 'count' to make this different from the
        // orginating query (for cache deconfliction).
        $params['__count__'] = true;
        
        // check the cache
        if ($params['cache']) {
            $key = $this->_cache->entry($params);
            $result = $this->_cache->fetch($key);
            if ($result !== false) {
                // cache hit
                return $result;
            }
        }
        
        // remove the 'eager' param for now, so we don't get the column-
        // based eager joins.
        $eager = (array) $params['eager'];
        $params['eager'] = array();
        
        // get the base select
        $select = $this->newSelect($params);
        
        // add count-based eager joins
        foreach ($eager as $name) {
            $related = $this->getRelated($name);
            $related->modSelectCountPages($select);
        }
        
        // count on the primary column
        $col = "{$this->_model_name}.{$this->_primary_col}";
        $result = $select->countPages($col);
        
        // save in cache?
        if ($params['cache']) {
            $this->_cache->add($key, $result);
        }
        
        // done
        return $result;
    }
    
    // -----------------------------------------------------------------
    //
    // Select
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * "Cleans up" SELECT clause parameters.
     * 
     * `eager`
     * : (string|array) Eager-fetch records from these related models.
     * 
     * `distinct`
     * : (bool) Use DISTINCT?
     * 
     * `cols`
     * : (string|array) Return only these columns.
     * 
     * `where`
     * : (string|array) A Solar_Sql_Select::multiWhere() value parameter to
     *   restrict which records are returned.
     * 
     * `group`
     * : (string|array) GROUP BY these columns.
     * 
     * `having`
     * : (string|array) HAVING these column values.
     * 
     * `order`
     * : (string|array) ORDER BY these columns.
     * 
     * `paging`
     * : (int) Return this many records per page.
     * 
     * `page`
     * : (int) Return only records from this page-number.
     * 
     * `limit`
     * : (int|array) Limit to a count of this many records (when an integer), 
     *   or limit by count and offset (when an array).  When `limit` is non-
     *   empty, the `page` and `paging` params are ignored.
     * 
     * `bind`
     * : (array) Key-value pairs to bind into the query.
     * 
     * `count_pages`
     * : (bool) Perform a second query for count and pages.
     * 
     * `cache`
     * : (bool) Use the cache?
     * 
     * `cache_key`
     * : (bool) An explicit cache key to use; otherwise, defaults to the
     *   serialized SELECT params.
     * 
     * @param array $params The parameters for the SELECT clauses.
     * 
     * @return array A normalized set of clause params.
     * 
     */
    public function fixSelectParams($params)
    {
        settype($params, 'array');
        
        // if we have eager values, make sure they're unique
        if (! empty($params['eager'])) {
            $params['eager'] = array_unique((array) $params['eager']);
        }
        
        // even after uniqing, the eager values might still be empty
        if (empty($params['eager'])) {
            $params['eager'] = null;
        }
        
        if (empty($params['distinct'])) {
            $params['distinct'] = null;
        }
        
        // if we have columns, make sure they're unique
        if (! empty($params['cols'])) {
            $params['cols'] = array_unique((array) $params['cols']);
        }
        
        // even after uniqing, cols might still be empty
        if (empty($params['cols'])) {
            $params['cols'] = array_keys($this->_table_cols);
        }
        
        if (empty($params['where'])) {
            $params['where'] = null;
        }
        
        if (empty($params['group'])) {
            $params['group'] = null;
        }
        
        if (empty($params['having'])) {
            $params['having'] = null;
        }
        
        if (empty($params['order'])) {
            $params['order'] = $this->_order;
        }
        
        if (empty($params['limit'])) {
            $params['limit'] = null;
        } else {
            // force to array
            settype($params['limit'], 'array');
            // pad out to 2 elements (count, offset)
            $params['limit'] = array_pad($params['limit'], 2, 0);
        }
        
        if (empty($params['paging'])) {
            $params['paging'] = $this->_paging;
        }
        
        if (empty($params['page'])) {
            $params['page'] = null;
        }
        
        if (empty($params['bind'])) {
            $params['bind'] = null;
        }
        
        if (empty($params['count_pages'])) {
            $params['count_pages'] = false;
        }
        
        // go by array_key_exists() so that an explicit "false" does not
        // accidentally get overwritten
        if (! array_key_exists('cache', $params)) {
            // key not present, use the default
            $params['cache'] = $this->_config['auto_cache'];
        }
        
        // force to boolean
        $params['cache'] = (bool) $params['cache'];
        
        // explicit cache key?
        if (empty($params['cache_key'])) {
            $params['cache_key'] = false;
        }
        
        // done
        return $params;
    }
    
    /**
     * 
     * Returns a new Solar_Sql_Select tool, with the proper SQL object
     * injected automatically, and with eager "to-one" associations joined.
     * 
     * @param array $params An array of SELECT parameters.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function newSelect($params = null)
    {
        $params = $this->fixSelectParams($params);
        
        // get the select object
        $select = Solar::factory(
            $this->_select_class,
            array('sql' => $this->_sql)
        );
        
        // modify the select to add eager joins
        foreach ((array) $params['eager'] as $name) {
            $related = $this->getRelated($name);
            $related->modSelectEager($select);
        }
        
        // inheritance for native model
        if ($this->_inherit_model) {
            $select->where(
                "{$this->_model_name}.{$this->_inherit_col} = ?",
                $this->_inherit_model
            );
        }
        
        // all the other pieces
        $select->distinct($params['distinct'])
               ->from("{$this->_table_name} AS {$this->_model_name}", $params['cols'])
               ->multiWhere($params['where'])
               ->group($params['group'])
               ->multiHaving($params['having'])
               ->order($params['order'])
               ->setPaging($params['paging'])
               ->bind($params['bind']);
        
        // limit by count/offset, or by page?
        if ($params['limit']) {
            list($count, $offset) = $params['limit'];
            $select->limit($count, $offset);
        } else {
            $select->limitPage($params['page']);
        }
        
        // done!
        return $select;
    }
    
    // -----------------------------------------------------------------
    //
    // Record and Collection factories
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns the appropriate record object for an inheritance model.
     * 
     * @param array $data The data to load into the record.
     * 
     * @return Solar_Sql_Model_Record A record object.
     * 
     */
    public function newRecord($data)
    {
        Solar::dump($data);
        
        // the model object to use -- might get overridden by inheritance
        $model = $this;
        
        // the record class we'll use
        $record_class = null;
        
        // look for an inheritance in relation to $data
        $inherit = null;
        if ($this->_inherit_col && ! empty($data[$this->_inherit_col])) {
            // inheritance is available, and a value is set for the
            // inheritance column in the data
            $inherit = trim($data[$this->_inherit_col]);
        }
        
        // did we find an inheritance value?
        if ($inherit) {
            // try to find a model class based on inheritance, going up the
            // stack as needed. this checks for Current_Model_Type,
            // Parent_Model_Type, Grandparent_Model_Type, etc.  blow up if we
            // can't find it.
            $model_class = $this->_stack->load($inherit);
            
            // if different from the current class, reset the model object.
            if ($model_class != $this->_class) {
                // use the inherited model class, it's different from the
                // current model
                $model = Solar::factory($model_class, array($this->_config));
            }
            
            // now we need the inherited record class.  suppress exceptions.
            // note that $record_class could still end up false, as we might not find
            // a related class in the hierarchy.
            $record_class = $this->_stack->load($inherit . '_Record', false);
        }
        
        // even if inheritance failed, look for a model-specific record class
        if (! $record_class) {
            $record_class = $this->_stack->load('Record', false);
        }
        
        // final fallback: the default record class
        if (! $record_class) {
            $record_class = $this->_record_class;
        }
        
        // factory the appropriate record class, set the model for it, then
        // load and return it.
        $record = Solar::factory($record_class);
        $record->setModel($model);
        $record->load($data);
        $record->setStatus('clean');
        return $record;
    }
    
    /**
     * 
     * Returns the appropriate collection object for this model.
     * 
     * @param array $data The data to load into the collection, if any.
     * 
     * @return Solar_Sql_Model_Collection A collection object.
     * 
     */
    public function newCollection($data = null)
    {
        // the collection class we'll use
        $class = $this->_stack->load('Collection', false);
        
        // final fallback
        if (! $class) {
            $class = $this->_collection_class;
        }
        
        // factory the collection class, load it, and return it.
        $collection = Solar::factory($class);
        $collection->setModel($this);
        $collection->load($data);
        return $collection;
    }
    
    // -----------------------------------------------------------------
    //
    // Insert, update, or delete rows in the model.
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Inserts one row to the model table and deletes cache entries.
     * 
     * @param array|Solar_Sql_Model_Record $spec The row data to insert.
     * 
     * @return array The data as inserted, including auto-incremented values,
     * auto-sequence values, created/updated/inherit values, etc.
     * 
     * @see Solar_Sql_Model_Cache::deleteAll()
     * 
     */
    public function insert($spec)
    {
        if (! is_array($spec) && ! ($spec instanceof Solar_Sql_Model_Record)) {
            throw $this->_exception('ERR_NOT_ARRAY_OR_RECORD');
        }
        
        /**
         * Force or auto-set special columns
         */
        // force the 'created' value if there is a 'created' column
        $now = date('Y-m-d H:i:s');
        $key = $this->_created_col;
        if ($key) {
            $spec[$key] = $now;
        }
        
        // force the 'updated' value if there is an 'updated' column (same as
        // the 'created' timestamp)
        $key = $this->_updated_col;
        if ($key) {
            $spec[$key] = $now;
        }
        
        // if inheritance is turned on, auto-set the inheritance value,
        // if not already set.
        $key = $this->_inherit_col;
        if ($key && $this->_inherit_model && empty($spec[$key])) {
            $spec[$key] = $this->_inherit_model;
        }
        
        // auto-set sequence values if needed
        foreach ($this->_sequence_cols as $key => $val) {
            if (empty($spec[$key])) {
                // no value given for the key.
                // add a new sequence value.
                $spec[$key] = $this->_sql->nextSequence($val);
            }
        }
        
        /**
         * Record filtering
         */
        if ($spec instanceof Solar_Sql_Model_Record) {
            // apply record filters and convert to array
            $spec->filter();
            $data = $spec->toArray();
        } else {
            // already an array
            $data = $spec;
        }
        
        /**
         * Final prep, then insert
         */
        // remove non-existent table columns from the data
        foreach ($data as $key => $val) {
            if (empty($this->_table_cols[$key])) {
                unset($data[$key]);
                // not in the table, so no need to check for autoinc
                continue;
            }
            
            // remove empty autoinc columns to soothe postgres, which won't
            // take explicit NULLs in SERIAL cols.
            if ($this->_table_cols[$key]['autoinc'] && empty($val)) {
                unset($data[$key]);
            }
        }
        
        // do the insert
        $this->serializeCols($data);
        $this->_sql->insert($this->_table_name, $data);
        $this->unserializeCols($data);
        
        /**
         * Post-insert
         */
        // no exception thrown, so it must have worked.
        
        // if there was an autoincrement column, set its value in the data.
        foreach ($this->_table_cols as $key => $val) {
            if ($val['autoinc']) {
                // set the value and leave the loop (should be only one)
                $data[$key] = $this->_sql->lastInsertId($this->_table_name, $key);
                break;
            }
        }
        
        // clear the cache for this model and related models
        $this->_cache->deleteAll();
        
        // refresh the table data in the record
        if ($spec instanceof Solar_Sql_Model_Record) {
            // set the primary column so refresh will work
            $key = $this->_primary_col;
            $spec->$key = $data[$key];
            // now refresh it
            $spec->refresh();
            $spec->setStatus('inserted');
        }
        
        // return the data as inserted
        return $data;
    }
    
    /**
     * 
     * Updates rows in the model table and deletes cache entries.
     * 
     * @param array|Solar_Sql_Model_Record $spec The row data to insert.
     * 
     * @param string|array $where The WHERE clause to identify which rows to 
     * update.
     * 
     * @return array The data as updated.
     * 
     * @see Solar_Sql_Model_Cache::deleteAll()
     * 
     */
    public function update($spec, $where)
    {
        if (! is_array($spec) && ! ($spec instanceof Solar_Sql_Model_Record)) {
            throw $this->_exception('ERR_NOT_ARRAY_OR_RECORD');
        }
        
        /**
         * Force or auto-set special columns
         */
        // force the 'updated' value
        $key = $this->_updated_col;
        if ($key) {
            $spec[$key] = date('Y-m-d H:i:s');
        }
        
        // if inheritance is turned on, auto-set the inheritance value,
        // if not already set.
        $key = $this->_inherit_col;
        if ($key && $this->_inherit_model && empty($this->$key)) {
            $spec[$key] = $this->_inherit_model;
        }
        
        // auto-set sequences where keys exist and values are empty
        foreach ($this->_sequence_cols as $key => $val) {
            // hack to to account for arrays *and* Record/Struct objects
            $exists = array_key_exists($key, $spec) || isset($spec[$key]);
            if ($exists && empty($spec[$key])) {
                // key is present but no value is given.
                // add a new sequence value.
                $spec[$key] = $this->_sql->nextSequence($val);
            }
        }
        
        /**
         * Record filtering and WHERE clause
         */
        
        // what's the primary key?
        $primary = $this->_primary_col;
        if ($spec instanceof Solar_Sql_Model_Record) {
            
            // apply record filters
            $spec->filter();
            
            // convert to array
            $data = $spec->toArray();
            
            // force the WHERE clause
            $where = array("$primary = ?" => $data[$primary]);
            
            // retain only changed columns
            foreach ($data as $key => $val) {
                if (! $spec->isChanged($key)) {
                    unset($data[$key]);
                }
            }
            
            // it's possible there are no columns that changed.
            // if so, we're done -- make it match what's at the DB.
            if (! $data) {
                $spec->refresh();
                return $data;
            }
            
        } else {
            // already an array
            $data = $spec;
        }
        
        // don't update the primary key
        unset($data[$primary]);
        
        /**
         * Final prep, then update
         */
        // remove non-existent table columns from the data
        foreach ($data as $key => $val) {
            if (empty($this->_table_cols[$key])) {
                unset($data[$key]);
            }
        }
        
        // perform the update
        $this->serializeCols($data);
        $this->_sql->update($this->_table_name, $data, $where);
        $this->unserializeCols($data);
        
        // clear the cache for this model and related models
        $this->_cache->deleteAll();
        
        // refresh the table data in the record
        if ($spec instanceof Solar_Sql_Model_Record) {
            $spec->refresh();
            $spec->setStatus('updated');
        }
        
        // unserialize cols and return the data as updated. note that if this
        // was a record, only the changed columns will be returned here.
        return $data;
    }
    
    /**
     * 
     * Deletes rows from the model table and deletes cache entries.
     * 
     * @param string|array|Solar_Sql_Model_Record $spec The WHERE clause to
     * identify which rows to delete, or a record to delete.
     * 
     * @return void
     * 
     * @see Solar_Sql_Model_Cache::deleteAll()
     * 
     */
    public function delete($spec)
    {
        if ($spec instanceof Solar_Sql_Model_Record) {
            $primary = $this->_primary_col;
            $where = array("$primary = ?" => $spec->$primary);
        } else {
            $where = $spec;
        }
        
        // perform the deletion
        $result = $this->_sql->delete($this->_table_name, $where);
        
        // clear the cache for this model and related models
        $this->_cache->deleteAll();
        
        // done
        return $result;
    }
    
    /**
     * 
     * Serializes data values in-place based on $this->_serialize_cols.
     * 
     * Does not attempt to serialize null values.
     * 
     * If serializing fails, stores 'null' in the data.
     * 
     * @param array &$data Record data.
     * 
     * @return void
     * 
     */
    public function serializeCols(&$data)
    {
        foreach ($this->_serialize_cols as $key) {
            if (! empty($data[$key]) && $data[$key] !== null) {
                $data[$key] = serialize($data[$key]);
                if (! $data[$key]) {
                    // serializing failed
                    $data[$key] = null;
                }
            }
        }
    }
    
    /**
     * 
     * Un-serializes data values in-place based on $this->_serialize_cols.
     * 
     * Does not attempt to un-serialize null values.
     * 
     * If un-serializing fails, stores 'null' in the data.
     * 
     * @param array &$data Record data.
     * 
     * @return void
     * 
     */
    public function unserializeCols(&$data)
    {
        // unseralize columns as-needed
        foreach ($this->_serialize_cols as $key) {
            // only unserialize if a non-empty string
            if (! empty($data[$key]) && is_string($data[$key])) {
                $data[$key] = unserialize($data[$key]);
                if (! $data[$key]) {
                    // unserializing failed
                    $data[$key] = null;
                }
            }
        }
    }
    
    /**
     * 
     * Adds a column filter.
     * 
     * This can be a "real" (table) or "virtual" (calculate) column.
     * 
     * Remember, filters are applied only to Record object data.
     * 
     * @param string $col The column name to filter.
     * 
     * @param string $method The filter method name, e.g. 'validateUnique'.
     * 
     * @args Remaining arguments are passed to the filter method.
     * 
     * @return void
     * 
     */
    protected function _addFilter($col, $method)
    {
        $args = func_get_args();
        array_shift($args); // the first param is $col
        $this->_filters[$col][] = $args;
    }
    
    /**
     * 
     * Adds a named has-one relationship.
     * 
     * @param string $name The relationship name, which will double as a
     * property when records are fetched from the model.
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _hasOne($name, $opts = null)
    {
        $this->_addRelated($name, 'HasOne', $opts);
    }
    
    /**
     * 
     * Adds a named belongs-to relationship.
     * 
     * @param string $name The relationship name, which will double as a
     * property when records are fetched from the model.
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _belongsTo($name, $opts = null)
    {
        $this->_addRelated($name, 'BelongsTo', $opts);
    }
    
    /**
     * 
     * Adds a named has-many relationship.
     * 
     * Note that you can get "has-and-belongs-to-many" using "has-many"
     * with a "through" option ("has-many-through").
     * 
     * @param string $name The relationship name, which will double as a
     * property when records are fetched from the model.
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _hasMany($name, $opts = null)
    {
        $this->_addRelated($name, 'HasMany', $opts);
    }
    
    /**
     * 
     * Support method for adding relations.
     * 
     * @param string $name The relationship name, which will double as a
     * property when records are fetched from the model.
     * 
     * @param string $type The relationship type.
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _addRelated($name, $type, $opts)
    {
        // is the relation name already a column name?
        if (array_key_exists($name, $this->_table_cols)) {
            throw $this->_exception(
                'ERR_RELATED_NAME_CONFLICT',
                array(
                    'name'  => $name,
                    'class' => $this->_class,
                )
            );
        }
        
        // is the relation name already in use?
        if (array_key_exists($name, $this->_related)) {
            throw $this->_exception(
                'ERR_RELATED_NAME_EXISTS',
                array(
                    'name'  => $name,
                    'class' => $this->_class,
                )
            );
        }
        
        // keep it!
        $opts['name']  = $name;
        $opts['class'] = "Solar_Sql_Model_Related_$type";
        $this->_related[$name] = (array) $opts;
    }
    
    /**
     * 
     * Gets the control object for a named relationship.
     * 
     * @param string $name The related name.
     * 
     * @return Solar_Sql_Model_Related The relationship control object.
     * 
     */
    public function getRelated($name)
    {
        if (! array_key_exists($name, $this->_related)) {
            throw $this->_exception(
                'ERR_RELATED_NAME_NOT_EXISTS',
                array(
                    'name'  => $name,
                    'class' => $this->_class,
                )
            );
        }
        
        if (is_array($this->_related[$name])) {
            $opts = $this->_related[$name];
            $this->_related[$name] = Solar::factory($opts['class']);
            unset($opts['class']);
            $this->_related[$name]->setNativeModel($this);
            $this->_related[$name]->load($opts);
        }
        
        return $this->_related[$name];
    }
    
    /**
     * 
     * Fixes the stack of parent classes for the model.
     * 
     * @return void
     * 
     */
    protected function _fixStack()
    {
        $this->_stack = Solar::factory('Solar_Class_Stack');
        
        // get the class parents and work from this class upwards
        $parents = Solar::parents($this->_class, true);
        array_pop($parents); // Solar_Base
        array_pop($parents); // Solar_Sql_Model
        $parents = array_reverse($parents);
        
        // any time we change vendors, add NewVendor_Model in between.
        // this helps with single-table-inheritance between vendors,
        // provided they use the Vendor_Model convention.
        $old_vendor = false;
        foreach ($parents as $class) {
            $tmp = explode('_', $class);
            $new_vendor = $tmp[0];
            if ($old_vendor && $new_vendor != $old_vendor) {
                $this->_stack->add("{$new_vendor}_Model");
            }
            $this->_stack->add($class);
            $old_vendor = $new_vendor;
        }
    }
    
    /**
     * 
     * Loads table name into $this->_table_name, and pre-sets the value of
     * $this->_inherit_model based on the class name.
     * 
     * @return void
     * 
     */
    protected function _fixTableName()
    {
        /**
         * Pre-set the value of $_inherit_model.  Will be modified one
         * more time in _fixTableCols().
         */
        // find the closest base called *_Model.  we do this so that
        // we can honor the top-level table name with inherited models.
        // *do not* use the class stack, as Solar_Sql_Model has been
        // removed from it.
        $base_class = null;
        $base_name  = null;
        $parents = Solar::parents($this->_class, true);
        foreach ($parents as $key => $val) {
            if (substr($val, -6) == '_Model') {
                // $key is now the value of the closest "_Model" class. -1 to
                // get the first class below that (e.g., *_Model_Nodes).
                // $base_class is then the class name that represents the
                // base of the model-inheritance hierarchy (which may not be
                // the immediate base in some cases).
                $base_class = $parents[$key - 1];
                
                // the base model name (e.g., Nodes).
                $pos = strrpos($base_class, '_Model_');
                if ($pos !== false) {
                    // the part after "*_Model_"
                    $base_name = substr($base_class, $pos + 7);
                } else {
                    // the whole class name
                    $base_name = $base_class;
                }
                
                break;
            }
        }
        
        // find the current model name (the part after "*_Model_")
        $pos = strrpos($this->_class, '_Model_');
        if ($pos !== false) {
            $curr_name = substr($this->_class, $pos + 7);
        } else {
            $curr_name = $this->_class;
        }
        
        // compare base model name to the current model name.
        // if they are different, consider this class an inherited one.
        if ($curr_name != $base_name) {
            
            // Solar_Model_Bookmarks and Solar_Model_Nodes_Bookmarks
            // both result in "bookmarks".
            $len = strlen($base_name);
            if (substr($curr_name, 0, $len + 1) == "{$base_name}_") {
                $this->_inherit_model = substr($curr_name, $len + 1);
            } else {
                $this->_inherit_model = $curr_name;
            }
            
            // set the base-class for inheritance
            $this->_inherit_base = $base_class;
        }
        
        /**
         * Auto-set the table name, if needed; leave it alone if already
         * user-specified.
         */
        if (empty($this->_table_name)) {
            // auto-define the table name.
            // change TableName to table_name.
            $this->_table_name = strtolower(
                $this->_inflect->camelToUnder($base_name)
            );
        }
    }
    
    /**
     * 
     * Fixes $this->_index listings.
     * 
     * @return void
     * 
     */
    protected function _fixIndex()
    {
        // baseline index definition
        $baseidx = array(
            'name'    => null,
            'type'    => 'normal',
            'cols'    => null,
        );
        
        // fix up each index to have a full set of info
        foreach ($this->_index as $key => $val) {
            
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
            
            $this->_index[$key] = $info;
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
    protected function _fixTableCols()
    {
        // is a table with the same name already at the database?
        $list = $this->_sql->fetchTableList();
        
        // if not found, attempt to create it
        if (! in_array($this->_table_name, $list)) {
            $this->_createTableAndIndexes($this);
        }
        
        // reset the columns to be **as they are at the database**
        $this->_table_cols = $this->_sql->fetchTableCols($this->_table_name);
        
        // @todo add a "sync" check to see if column data in the class
        // matches column data in the database, and throw an exception
        // if they don't match pretty closely.
        
        // set the primary column based on the first primary key;
        // ignores later primary keys
        foreach ($this->_table_cols as $key => $val) {
            if ($val['primary']) {
                $this->_primary_col = $key;
                break;
            }
        }
    }
    
    /**
     * 
     * Fixes the array-name and table-alias for user input to this model.
     * 
     * @return void
     * 
     */
    protected function _fixModelName()
    {
        if (! $this->_model_name) {
            if ($this->_inherit_model) {
                $this->_model_name = $this->_inherit_model;
            } elseif ($this->_table_name) {
                $this->_model_name = $this->_table_name;
            } else {
                // get the part after the last Model_ portion
                $pos = strpos($this->_class, 'Model_');
                if ($pos) {
                    $this->_model_name = substr($this->_class, $pos+6);
                } else {
                    $this->_model_name = $this->_class;
                }
            }
            
            // convert FooBar to foo_bar
            $this->_model_name = strtolower(
                $this->_inflect->camelToUnder($this->_model_name)
            );
        }
    }
    
    /**
     * 
     * Fixes the default order when fetching records from this model.
     * 
     * @return void
     * 
     */
    protected function _fixOrder()
    {
        if (! $this->_order) {
            $this->_order = $this->_model_name . '.' . $this->_primary_col;
        }
    }
    
    /**
     * 
     * Fixes up special column indicator properties, and post-sets the
     * $_inherit_model value based on the existence of the inheritance column.
     * 
     * @return void
     * 
     * @todo How to make foreign_col recognize that it's inherited, and should
     * use the parent foreign_col value?  Can we just work up the chain?
     * 
     */
    protected function _fixPropertyCols()
    {
        // make sure these actually exist in the table, otherwise unset them
        $list = array(
            '_created_col',
            '_updated_col',
            '_primary_col',
            '_inherit_col',
        );
        
        foreach ($list as $col) {
            if (trim($this->$col) == '' ||
                ! array_key_exists($this->$col, $this->_table_cols)) {
                // doesn't exist in the table
                $this->$col = null;
            }
        }
        
        // post-set the inheritance model value
        if (! $this->_inherit_col) {
            $this->_inherit_model = null;
            $this->_inherit_base = null;
        }
        
        // set up the fetch-cols list
        settype($this->_fetch_cols, 'array');
        if (! $this->_fetch_cols) {
            $this->_fetch_cols = array_keys($this->_table_cols);
        }
        
        // simply force to array
        settype($this->_serialize_cols, 'array');
        
        // the "sequence" columns.  make sure they point to a sequence name.
        // e.g., string 'col' becomes 'col' => 'col'.
        $tmp = array();
        foreach ((array) $this->_sequence_cols as $key => $val) {
            if (is_int($key)) {
                $tmp[$val] = $val;
            } else {
                $tmp[$key] = $val;
            }
        }
        $this->_sequence_cols = $tmp;
        
        // make sure we have a hint to foreign models as to what colname
        // to use when referring to this model
        if (empty($this->_foreign_col)) {
            if (! $this->_inherit_model) {
                // not inherited
                $prefix = $this->_inflect->toSingular($this->_model_name);
                $this->_foreign_col = strtolower($prefix)
                                     . '_' . $this->_primary_col;
            } else {
                // inherited, can't just use the model name as a column name.
                // need to find base model foreign_col value.
                $base = Solar::factory($this->_inherit_base, array(
                    'sql' => $this->_sql
                ));
                $this->_foreign_col = $base->foreign_col;
                unset($base);
            }
        }
    }
    
    /**
     * 
     * Loads the baseline data filters for each column.
     * 
     * @return void
     * 
     */
    protected function _fixFilters()
    {
        // make sure we have a filter class
        if (empty($this->_filter_class)) {
            $class = $this->_stack->load('Filter', false);
            if (! $class) {
                $class = 'Solar_Sql_Model_Filter';
            }
            $this->_filter_class = $class;
        }
        
        // make sure filters are an array
        settype($this->_filters, 'array');
        
        // make sure that strings are converted
        // to arrays so that _applyFilters() works properly.
        foreach ($this->_filters as $col => $list) {
            foreach ($list as $key => $val) {
                if (is_string($val)) {
                    $this->_filters[$col][$key] = array($val);
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
        foreach ($this->_table_cols as $col => $info) {
            
            $type = $info['type'];
            switch ($type) {
            case 'bool':
                $this->_filters[$col][] = array('validateBool');
                $this->_filters[$col][] = array('sanitizeBool');
                break;
            
            case 'char':
            case 'varchar':
                // only add filters if not serializing
                if (! in_array($col, $this->_serialize_cols)) {
                    $this->_filters[$col][] = array('validateString');
                    $this->_filters[$col][] = array('validateMaxLength',
                        $info['size']);
                    $this->_filters[$col][] = array('sanitizeString');
                }
                break;
            
            case 'smallint':
            case 'int':
            case 'bigint':
                $this->_filters[$col][] = array('validateInt');
                $this->_filters[$col][] = array('validateRange',
                    $range[$type][0], $range[$type][1]);
                $this->_filters[$col][] = array('sanitizeInt');
                break;
            
            case 'numeric':
                $this->_filters[$col][] = array('validateNumeric');
                $this->_filters[$col][] = array('validateSizeScope',
                    $info['size'], $info['scope']);
                $this->_filters[$col][] = array('sanitizeNumeric');
                break;
            
            case 'float':
                $this->_filters[$col][] = array('validateFloat');
                $this->_filters[$col][] = array('sanitizeFloat');
                break;
            
            case 'clob':
                // no filters, clobs are pretty generic
                break;
            
            case 'date':
                $this->_filters[$col][] = array('validateIsoDate');
                $this->_filters[$col][] = array('sanitizeIsoDate');
                break;
            
            case 'time':
                $this->_filters[$col][] = array('validateIsoTime');
                $this->_filters[$col][] = array('sanitizeIsoTime');
                break;
            
            case 'timestamp':
                $this->_filters[$col][] = array('validateIsoTimestamp');
                $this->_filters[$col][] = array('sanitizeIsoTimestamp');
                break;
            }
        }
    }
    
    /**
     * 
     * Fixes the cache class name.
     * 
     * @return void
     * 
     */
    protected function _fixCache()
    {
        // make sure we have a cache class
        if (empty($this->_cache_class)) {
            $class = $this->_stack->load('Cache', false);
            if (! $class) {
                $class = 'Solar_Sql_Model_Cache';
            }
            $this->_cache_class = $class;
        }
    }
    
    /**
     * 
     * Creates the table and indexes in the database using $this->_table_cols
     * and $this->_index.
     * 
     * @return void
     * 
     */
    protected function _createTableAndIndexes()
    {
        /**
         * Create the table.
         */
        $this->_sql->createTable(
            $this->_table_name,
            $this->_table_cols
        );
        
        /**
         * Create the indexes.
         */
        foreach ($this->_index as $name => $info) {
            try {
                // create this index
                $this->_sql->createIndex(
                    $this->_table_name,
                    $info['name'],
                    $info['type'] == 'unique',
                    $info['cols']
                );
            } catch (Exception $e) {
                // cancel the whole deal.
                $this->_sql->dropTable($this->_table_name);
                throw $e;
            }
        }
    }
}
