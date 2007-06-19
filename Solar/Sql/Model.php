<?php
/**
 * 
 * Acts as the "model" portion of Model-Controller-View.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Table.php 2093 2007-01-15 15:13:12Z pmjones $
 * 
 */

/**
 * 
 * Acts as the "model" portion of Model-Controller-View.
 * 
 * Implements a number of design patterns:
 * 
 * - TableModule
 * - MetadataMapping
 * - DataMapper
 * - SingleTableInheritance
 * - ActiveRecord
 * - RecordSet
 * - AssociationTableMapping
 * - DomainModel (?)
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @todo Honor "eager" loading of to-many by fetching all has_many records for
 * all records in a collection, so as to hit the database only once per relation.
 * 
 * @todo Make it so you can add new records to the end of a collection?
 * 
 * @todo In __set(), make sure that we check for class and focus per the 
 * foriegn_model value (and has_many vice belong_to/has_one.)
 * 
 * @todo Add explicit hooks for pre/post save, pre-post insert, pre/post update,
 * pre/post delete, pre validate, pre/post fetch ... ?
 * 
 * @todo Add master-focus insert, update, and delete
 * 
 * @todo When setting a related value, make sure it's of a model of the 
 * proper type, in the proper focus.  Esp. important for to-one relations.
 * 
 * @todo Add isValid() method?
 * 
 * @todo Add isRecord(), isCollection(), isMaster(), getStatus(), getFocus()?
 * 
 * @todo Make delete() cascade as needed.
 * 
 * @todo Add saving of related records?  Need to wrap in a transaction.  Also
 * need to look up related values, insert-id's, etc.
 * 
 * @todo When saving, save related Record and Collection properties.
 * 
 * @todo When saving related, populate the related primary-key back to the 
 * native record.
 * 
 * @todo When saving, should we save the related "belongs-to" record?
 * 
 * @todo Make it possible to append to a Collection, and then insert as needed
 * when saving.
 * 
 * @todo Add "soft delete" feature? $deleted_col = 'deleted', and then only
 * retrieve non-deleted cols.
 * 
 * @todo Add "increment" and "decrement" feature?  Good for instant-update
 * counting ... although when you save, it still overwrites the increments from
 * any other instance.  :-(
 * 
 */
abstract class Solar_Sql_Model extends Solar_Base
    implements ArrayAccess, Countable, IteratorAggregate {
    
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
    protected $_Solar_Sql_Model = array(
        'sql' => 'sql',
    );
    
    /**
     * 
     * The list of accessor methods for individual column properties.
     * 
     * For example, a method called __getFooBar() will be registered for
     * ['get']['foo_bar'] => '__getFooBar'.
     * 
     * @var array
     * 
     */
    protected $_access_methods = array();
    
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
     * The column name for 'created' timestamps; default is 'created'.
     * 
     * @var string
     * 
     */
    protected $_created_col = 'created';
    
    /**
     * 
     * Current data for the object.
     * 
     * When in "record" focus, the data for the current record.
     * 
     * When in "collection" focus, the data for all records in the collection.
     * 
     * @var array
     * 
     */
    protected $_data = array();
    
    /**
     * 
     * The class to use for external filtering methods.
     * 
     * @param string
     * 
     * @see _applyFilters()
     * 
     * @var string
     * 
     */
    protected $_datafilter_class;
    
    /**
     * 
     * Filters to validate and sanitize column data.
     * 
     * Default is to use validate*() and sanitize*() methods in the filter
     * class, but if the method exists locally, it will be used instead.
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
     *     $this->_filters['handle'][] = '_validateUnique';
     *     
     *     // filter 'email' to have only emails-allowed chars, validate as an
     *     // email address, and be unique in the table.
     *     $this->_filters['email'][] = 'sanitizeStringEmail';
     *     $this->_filters['email'][] = 'validateEmail';
     *     $this->_filters['email'][] = '_validateUnique';
     *     
     *     // filter 'passwd' to be not-blank, and should match any existing
     *     // 'passwd_confirm' value.
     *     $this->_filters['passwd'][] = 'validateRequire';
     *     $this->_filters['passwd'][] = '_validateConfirm';
     * }}
     * 
     * @var array
     * 
     * @see $_datafilter_class
     * 
     * @see _applyFilters()
     * 
     */
    protected $_filters;
    
    /**
     * 
     * Is this model instance focused as a 'master', a 'collection' of
     * records, or a single 'record'?
     * 
     * The focus determines which methods are available, and how iterators,
     * countable, array-access, and magic methods operate.  When in 'record'
     * mode, for example, you cannot fetch new records.
     * 
     * @var string
     * 
     */
    protected $_focus = 'master';
    
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
     *     $this->_indexes['idx_name'] = array($type, 'col_name');
     * 
     *     // index on multiple columns:
     *     // CREATE INDEX idx_name ON table_name (col_1, col_2, ... col_N)
     *     $this->_indexes['idx_name'] = array(
     *         $type,
     *         array('col_1', 'col_2', ..., 'col_N')
     *     );
     *     
     *     // easy shorthand for an index on a single column,
     *     // giving the index the same name as the column:
     *     // CREATE INDEX col_name ON table_name (col_name)
     *     $this->_indexes['col_name'] = $type; 
     * }}
     * 
     * The $type may be 'normal' or 'unique'.
     * 
     * @var array
     * 
     */
    protected $_indexes = array();
    
    /**
     * 
     * When inheritance is turned on, the inherit-model value for this class
     * in $_inherit_col.
     * 
     * @var string
     * 
     */
    protected $_inherit_model;
    
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
     * Other models that relate to this model should use this as the foreign-key
     * column name.
     * 
     * @var string
     * 
     */
    protected $_foreign_col = null;
    
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
     * The column name that tracks single-table inheritance; default is
     * 'model'.
     * 
     * @var string
     * 
     */
    protected $_inherit_col = 'model';
    
    /**
     * 
     * Keeps track of validation failure messages from processing data
     * filters.
     * 
     * @var array
     * 
     * @see _insert()
     * 
     * @see _update()
     * 
     */
    protected $_invalid;
    
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
     * When in "collection" focus, an array of model record objects as 
     * instantiated from the data.
     * 
     * @var array
     * 
     */
    protected $_records = array();
    
    /**
     * 
     * For a related record collection, what page are we on?
     * 
     * @var array
     * 
     */
    protected $_related_page = array();
    
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
     * A Solar_Sql dependency object.
     * 
     * @var Solar_Sql
     * 
     */
    protected $_sql = null;
    
    /**
     * 
     * A Solar_Class_Stack object for fallback hierarchy.
     * 
     * Used for finding and loading these classes:
     * 
     * - DataFilter
     * - The proper Model for single-table inheritance
     * 
     * @var Solar_Class_Stack
     * 
     */
    protected $_stack;
    
    /**
     * 
     * Tracks the of the status of this record.
     * 
     * Status values are:
     * 
     * `clean`
     * : The record is unmodified from the database.
     * 
     * `deleted`
     * : This record has been deleted; load(), etc. will not work.
     * 
     * `dirty`
     * : At least one record property has changed.
     * 
     * `inserted`
     * : The record was inserted successfully.
     * 
     * `invalid`
     * : Validation was attempted, with failure.
     * 
     * `new`
     * : This is a new record and has not been saved to the database.
     * 
     * `updated`
     * : The record was updated successfully.
     * 
     * @var bool
     * 
     */
    protected $_status = 'clean';
    
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
     * The table name.
     * 
     * @var string
     * 
     */
    protected $_table_name = null;
    
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
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // main construction
        parent::__construct($config);
        
        // connect to the database
        $this->_sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // attach to the model catalog. be sure to use the same SQL
        // connection for the catalog as in the model.
        $catalog = Solar::factory(
            'Solar_Sql_Model_Catalog',
            array('sql' => $this->_sql)
        );
        
        // our class name so that we don't call get_class() all the time
        $this->_class = get_class($this);
        
        // do we have a catalog entry for this model class yet?
        if (! $catalog->exists($this->_class)) {
            // call user-defined setup
            $this->_setup();
            // set and fix properties in the catalog.
            $catalog->set($this->_class, get_object_vars($this));
        }
        
        // get fixed properties back from the catalog
        $vars = $catalog->get($this->_class);
        foreach ($vars as $key => $val) {
            $this->$key = $val;
        }
    }
    
    /**
     * 
     * Magic getter for record properties; automatically calls __getColName()
     * methods when they exist.
     * 
     * @param string $key The property name.
     * 
     * @return mixed The property value.
     * 
     */
    public function __get($key = null)
    {
        // allow property-like access to record data
        $this->_checkFocus('record');
        if ($this->_focus == 'record') {
            
            // disallow if status is 'deleted'
            $this->_checkDeleted();
            
            // do we need to load relationship data?
            $load_related = empty($this->_data[$key]) &&
                array_key_exists($key, $this->_related);
            
            if ($load_related) {
                // the key was for a relation that has no data yet.
                // load the data.
                $this->_data[$key] = $this->_fetchRelated(
                    $key,
                    $this->_related_page[$key]
                );
            }
            
            // if an accessor method exists, use it.
            if (! empty($this->_access_methods['get'][$key])) {
                $method = $this->_access_methods['get'][$key];
                return $this->$method();
            }
            
            // look for the data key and return its value.
            if (array_key_exists($key, $this->_data)) {
                return $this->_data[$key];
            }
        }
    }
    
    /**
     * 
     * Magic setter for record properties; automatically calls __setColName()
     * methods when they exist.
     * 
     * @param string $key The property name.
     * 
     * @param mixed $val The value to set.
     * 
     * @return void
     * 
     */
    public function __set($key, $val)
    {
        // allow property-like access to record data
        $this->_checkFocus('record');
        if ($this->_focus == 'record') {
            
            // disallow if status is 'deleted'
            $this->_checkDeleted();
            
            // set to dirty only if not 'new'
            if ($this->_status != 'new') {
                $this->_status = 'dirty';
            }
            
            // how to set the value?
            if (! empty($this->_access_methods['set'][$key])) {
                // use accessor method
                $method = $this->_access_methods['set'][$key];
                $this->$method($val);
            } elseif ($key == $this->_primary_col) {
                // disallow setting of primary keys; do nothing.
            } else {
                // no accessor method, not a primary key; assign directly.
                $this->_data[$key] = $val;
            }
        }
    }
    
    /**
     * 
     * Does a certain key exist in the data?
     * 
     * @param string $key The requested data key.
     * 
     * @param mixed $val The value to set the data to.
     * 
     * @return void
     * 
     */
    public function __isset($key)
    {
        if ($this->_focus == 'record') {
            // standard method, or special accessor?
            if (! empty($this->_access_methods['isset'][$key])) {
                // use accessor method
                $method = $this->_access_methods['isset'][$key];
                return $this->$method();
            } else {
                // no accessor method
                return array_key_exists($key, $this->_data);
            }
        }
    }
    
    /**
     * 
     * Sets a key in the data to null.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __unset($key)
    {
        if ($this->_focus == 'record') {
            // standard method, or special accessor?
            if (! empty($this->_access_methods['unset'][$key])) {
                // use accessor method
                $method = $this->_access_methods['unset'][$key];
                $this->$method();
            } else {
                // no accessor method
                unset($this->_data[$key]);
            }
        }
    }
    
    // -----------------------------------------------------------------
    // 
    // Master methods
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
     */
    public function __call($method, $params)
    {
        // is it "fetchOneBy" or "fetchAllBy"?
        if (substr($method, 0, 10) == 'fetchOneBy') {
            $fetch = 'fetchOne';
            $method = substr($method, 10);
        } elseif (substr($method, 0, 10) == 'fetchAllBy') {
            $fetch = 'fetchAll';
            $method = substr($method, 10);
        } else {
            throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
                'method' => $method,
                'params' => $params,
            ));
        }
        
        // get the list of columns from the remainder of the method name
        // e.g., fetchAllByParentIdAndAreaId => ParentId, AreaId
        $list = explode('And', $method);
        
        // build the fetch params
        $where = array();
        $bind  = array();
        foreach ($list as $key => $col) {
            // convert from ColName to col_name
            $col = preg_replace('/([a-z])([A-Z])/', '$1_$2', $col);
            $col = strtolower($col);
            $where[] = "$col = :$col";
            $bind[$col] = $params[$key];
        }
        
        // add the last param after last column name as the "extra" settings
        // (order, group, having, page, paging, etc).
        $k = count($list);
        if (count($params) > $k) {
            $opts = (array) $params[$k];
        } else {
            $opts = array();
        }
        
        // merge the where/bind with the base fetch params
        $opts = array_merge($opts, array(
            'where' => $where,
            'bind'  => $bind,
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
     * @return Solar_Sql_Model A Model object with a 'record' or 'collection'
     * focus.
     * 
     */
    public function fetch($spec)
    {
        $this->_checkFocus('master');
        
        $col = "{$this->_table_name}.{$this->_primary_col}";
        if (is_array($spec)) {
            $where = array("$col IN (?)" => $spec);
            return $this->fetchAll(array('where' => $where));
        } else {
            $where = array("$col = ?" => $spec);
            return $this->fetchOne(array('where' => $where));
        }
    }
    
    /**
     * 
     * Fetches all records by arbitrary parameters.
     * 
     * Recognized parameters for the fetch are:
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
     * `bind`
     * : (array) Bind these placeholder keys to these values in the where,
     *   group, having, etc. clauses.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having, 'order', and 'page'.
     * 
     * @return Solar_Sql_Model A model object with a 'collection' focus.
     * 
     */
    public function fetchAll($params = array())
    {
        $this->_checkFocus('master');
        
        // setup
        $params = $this->_fixSelectParams($params);
        $select = $this->_newSelect($params['eager']);
        
        // build
        $select->distinct($params['distinct'])
               ->from($this->_table_name, $params['cols'])
               ->multiWhere($params['where'])
               ->group($params['group'])
               ->having($params['having'])
               ->order($params['order'])
               ->setPaging($params['paging'])
               ->limitPage($params['page'])
               ->bind($params['bind']);
               
        // fetch
        $data = $select->fetch('all');
        if ($data) {
            $result = Solar::factory($this->_class);
            $result->_focus = 'collection';
            $result->_data = $data;
            return $result;
        } else {
            return array();
        }
    }
    
    /**
     * 
     * The same as fetchAll(), except the record collection is keyed on the
     * first column of the results (instead of being a strictly sequential
     * array.)
     * 
     * Recognized parameters for the fetch are:
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
     * `bind`
     * : (array) Bind these placeholder keys to these values in the where,
     * group, having, etc. clauses.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having, 'order', and 'page'.
     * 
     * @return Solar_Sql_Model A model in 'collection' focus.
     */
    public function fetchAssoc($params = array())
    {
        $this->_checkFocus('master');
        
        // setup
        $params = $this->_fixSelectParams($params);
        $select = $this->_newSelect($params['eager']);
        
        // build
        $select->distinct($params['distinct'])
               ->from($this->_table_name, $params['cols'])
               ->multiWhere($params['where'])
               ->group($params['group'])
               ->having($params['having'])
               ->order($params['order'])
               ->setPaging($params['paging'])
               ->limitPage($params['page'])
               ->bind($params['bind']);
               
        // fetch
        $data = $select->fetch('assoc');
        if ($data) {
            $result = Solar::factory($this->_class);
            $result->_focus = 'collection';
            $result->_data = $data;
            return $result;
        } else {
            return array();
        }
    }
    
    /**
     * 
     * Fetches one record by arbitrary parameters.
     * 
     * Recognized parameters for the fetch are:
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
     * : (array) Bind these placeholder keys to these values in the where,
     * group, having, etc. clauses.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having, and 'order'.
     * 
     * @return Solar_Sql_Model A model object with a 'record' focus.
     * 
     */
    public function fetchOne($params = array())
    {
        $this->_checkFocus('master');
        
        // setup
        $params = $this->_fixSelectParams($params);
        $select = $this->_newSelect($params['eager']);
        
        // build
        $select->distinct($params['distinct'])
               ->from($this->_table_name, $params['cols'])
               ->multiWhere($params['where'])
               ->group($params['group'])
               ->having($params['having'])
               ->order($params['order'])
               ->bind($params['bind']);
        
        // fetch
        $data = $select->fetch('one');
        if ($data) {
            
            // get the main record
            $record = $this->_newRecord($data);
            
            // get related data from each eager has_many relationship
            $list = (array) $params['eager'];
            foreach ($this->_related as $name => $opts) {
                $eager = in_array($name, $list);
                if ($eager && $opts['type'] == 'has_many') {
                    $record->_data[$name] = $this->_fetchRelated($name, $opts['page']);
                }
            }
            
            // done
            return $record;
            
        } else {
            return null;
        }
    }
    
    /**
     * 
     * Fetches a sequential array of values from the model, using only the
     * first column of the results.
     * 
     * Recognized parameters for the fetch are:
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
     * `bind`
     * : (array) Bind these placeholder keys to these values in the where,
     * group, having, etc. clauses.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having, and 'order'.
     * 
     * @return array
     * 
     */
    public function fetchCol($params = array())
    {
        $this->_checkFocus('master');
        
        // setup
        $params = $this->_fixSelectParams($params);
        $select = $this->_newSelect($params['eager']);
        
        // build
        $select->distinct($params['distinct'])
               ->from($this->_table_name, $params['cols'])
               ->multiWhere($params['where'])
               ->group($params['group'])
               ->having($params['having'])
               ->order($params['order'])
               ->setPaging($params['paging'])
               ->limitPage($params['page'])
               ->bind($params['bind']);
               
        // fetch
        $data = $select->fetch('col');
        if ($data) {
            return $data;
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
     * : (array) Bind these placeholder keys to these values in the where,
     * group, having, etc. clauses.
     * 
     * @param array $params An array of parameters for the fetch, with keys
     * for 'cols', 'where', 'group', 'having, and 'order'.
     * 
     * @return array
     * 
     */
    public function fetchPairs($params = array())
    {
        $this->_checkFocus('master');
        
        // setup
        $params = $this->_fixSelectParams($params);
        $select = $this->_newSelect($params['eager']);
        
        // build
        $select->distinct($params['distinct'])
               ->from($this->_table_name, $params['cols'])
               ->multiWhere($params['where'])
               ->group($params['group'])
               ->having($params['having'])
               ->order($params['order'])
               ->setPaging($params['paging'])
               ->limitPage($params['page'])
               ->bind($params['bind']);
               
        // fetch
        $data = $select->fetch('pairs');
        if ($data) {
            return $data;
        } else {
            return array();
        }
    }
    
    /**
     * 
     * Returns a new record with default values.
     * 
     * @param array $spec An array of user-specified data to place into the
     * new record, if any.
     * 
     * @return Solar_Sql_Model
     * 
     */
    public function fetchNew($spec = null)
    {
        $this->_checkFocus('master');
        return $this->_fetchNew($spec);
    }
    
    /**
     * 
     * Internal-only support method to fetch new (blank) records.
     * 
     * @param array $spec An array of user-specified data to place into the
     * new record, if any.
     * 
     * @return Solar_Sql_Model
     * 
     * @todo Add placeholder keys for related models?  Set as null, or as
     * new record/collection objects?
     * 
     */
    protected function _fetchNew($spec = null)
    {
        // the user-specifed data
        settype($spec, 'array');
        
        // the array of data for the record
        $data = array();
        
        // loop through each specified column and collect default data
        foreach ($this->_table_cols as $key => $val) {
            if (! empty($spec[$key])) {
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
        foreach ($this->_related as $key => $val) {
            $data[$key] = null;
        }
        
        // done, return the proper record object
        $record = $this->_newRecord($data);
        $record->_status = 'new';
        return $record;
    }
    
    /**
     * 
     * Converts the properties of this model Record or Collection to an array,
     * including related models stored in properties.
     * 
     * @return array
     * 
     */
    public function toArray()
    {
        // only works with records and collections
        $this->_checkFocus(array('record', 'collection'));
        
        // get a copy of everything
        $data = array();
        foreach ($this->_data as $key => $val) {
            if ($val instanceof Solar_Sql_Model) {
                $data[$key] = $val->toArray();
            } else {
                $data[$key] = $val;
            }
        }
        
        // done!
        return $data;
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
        $this->_checkFocus('master');
        
        $params = $this->_fixSelectParams($params);
        
        $select = $this->_newSelect();
        $select->distinct($params['distinct'])
               ->from($this->_table_name)
               ->multiWhere($params['where'])
               ->group($params['group'])
               ->having($params['having'])
               ->setPaging($this->_paging);
               
        $col = "{$this->_table_name}.{$this->_primary_col}";
        
        return $select->countPages($col);
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
        $this->_checkFocus('master');
        $this->_paging = (int) $paging;
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
        $this->_checkFocus('master');
        return $this->_paging;
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Record-focused methods
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Inserts or updates the current record based on its primary key,
     * or if a collection, inserts or updates each record in the collection.
     * 
     * @param array $data If the model focus is 'record', an associative
     * array of data to merge with existing record data.  Ignored in 
     * 'collection' focus.
     * 
     * @return void
     * 
     * @todo Wrap these in transactions?  Need to track transaction status
     * in the Catalog, so we don't start multiple transactions.
     * 
     * @todo When saving related, don't save deleted ones; catch ERR_DELETED
     * and proceed ot the next related.
     * 
     */
    public function save($data = null)
    {
        // only save records and collections, that have not been deleted
        $this->_checkFocus(array('record', 'collection'));
        $this->_checkDeleted();
        
        // ok, what kind of save?
        if ($this->_focus == 'record') {
            $this->_saveRecord($data);
        } elseif ($this->_focus == 'collection') {
            $this->_saveCollection();
        }
    }
    
    /**
     * 
     * Saves this individual record, along with any related record instances.
     * 
     * @param array $data An associative array of data to merge with existing
     * record data.
     * 
     * @return void
     * 
     */
    protected function _saveRecord($data = null)
    {
        // load data at save-time?
        if ($data) {
            
            // force to an array
            settype($data, 'array');
            
            // the model name in array keys
            $name = $this->_model_name;
            
            // do we have an array-key for this model in the data?
            if (array_key_exists($name, $data) && is_array($data[$name])) {
                // get just the array-key values for this model name
                $this->_loadRecord($data[$name]);
            } else {
                // use the top-level array keys as data input
                $this->_loadRecord($data);
            }
            
            // set the status
            if ($this->_status != 'new') {
                $this->_status = 'dirty';
            }
        }
        
        // only save if we're not clean
        if ($this->_status != 'clean') {
            // if the primary key value is not present, insert;
            // otherwise, update.
            $primary = $this->_primary_col;
            if (empty($this->_data[$primary])) {
                $this->_insert();
            } else {
                $this->_update();
            }
            $this->_status = 'clean';
        }
        
        // now save each related, but only if instantiated
        foreach ($this->_related as $name => $info) {
            if (! empty($this->_data[$name]) &&
                $this->_data[$name] instanceof Solar_Sql_Model) {
                // not empty, and is a model instance
                $this->_data[$name]->save();
            }
        }
    }
    
    /**
     * 
     * Saves each record in the collection.
     * 
     * @param array $data An associative array of data to merge with existing
     * record data.
     * 
     * @return void
     * 
     * @todo Don't attempt to save deleted records in a collection; or, catch
     * ERR_DELETED and go on to the next record in the collection.
     * 
     */
    protected function _saveCollection()
    {
        foreach ($this->_data as $key => $val) {
            $val->save();
        }
    }
    
    /**
     * 
     * Reloads data for this record from the database.
     * 
     * Note that this does not reload any related values.
     * 
     * @return void
     * 
     * @todo rename to refresh(), so we can have a load() method?
     * 
     */
    public function reload()
    {
        $this->_checkFocus('record');
        if ($this->_status != 'new') {
            $master = Solar::factory($this->_class, array('sql' => $this->_sql));
            $result = $master->fetch($this->_data[$this->_primary_col]);
            $this->_data = $result->_data;
            $this->_status = 'clean';
        }
    }
    
    /**
     * 
     * Deletes this record from the database.
     * 
     * Note that it does not delete any related values.
     * 
     * @return void
     * 
     */
    public function delete()
    {
        $this->_checkFocus('record');
        if ($this->_status != 'new') {
            $where = $this->_sql->quoteInto(
                "{$this->_primary_col} = ?",
                $this->_data[$this->_primary_col]
            );
            $this->_sql->delete($this->_table_name, $where);
        }
        $this->_status = 'deleted';
        $this->_data = null;
    }
    
    /**
     * 
     * Gets the list of invalid columns and their localized invalidation
     * messages.
     * 
     * @return array
     * 
     */
    public function getInvalid()
    {
        $this->_checkFocus('record');
        return $this->_invalid;
    }
    
    /**
     * 
     * Sets one or more invalidation messages for a column.
     * 
     * @param string $key The column name to set as invalid.
     * 
     * @param string $msg The reason for invalidation.
     * 
     * @return void
     * 
     */
    public function setInvalid($key, $msg)
    {
        $this->_checkFocus('record');
        $this->_status = 'invalid';
        $this->_invalid[$key][] = $msg;
    }
    
    /**
     * 
     * Sets the page-number for related collections.
     * 
     * @param string $name The relationship name.
     * 
     * @param int $page The page-number to set.
     * 
     * @return void
     * 
     */
    public function setRelatedPage($name, $page)
    {
        $this->_checkFocus('record');
        $this->_related_page[$name] = (int) $page;
    }
    
    /**
     * 
     * Counts the number of records in a related model for a given record.
     * 
     * @param string $name The relationship name.
     * 
     * @param array $params Parameters for the related SELECT; honors keys for
     * 'where', 'having', 'group', and 'paging'.
     * 
     * @return array An array with keys 'count' (the count of records) and
     * 'pages' (the number of pages of records).
     * 
     */
    public function countRelatedPages($name, $params = null)
    {
        $this->_checkFocus('record');
        $params = $this->_fixSelectParams($params);
        
        $select = $this->_newRelatedSelect($name);
        $select->multiWhere($params['where'])
               ->group($params['group'])
               ->having($params['having'])
               ->setPaging($params['paging']);
        
        $opts = $this->_related[$name];
        $col  = "{$opts['foreign_alias']}.{$opts['foreign_primary_col']}";
        return $select->countPages($col);
    }
    
    // -----------------------------------------------------------------
    // 
    // Other methods
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Loads data for a single record.
     * 
     * @param array $data The data to load into the object.
     * 
     * @return void
     * 
     */
    protected function _loadRecord($data)
    {
        // collected data for related models
        $rel_data = array();
        
        // load each key into $this->_data, or save in $rel_data
        foreach ($data as $key => $val) {
            
            // don't set numeric or underscore-prefixed properties
            $key = (string) $key;
            if (is_numeric($key) || $key[0] == '_') {
                continue;
            }
            
            // if the key has double-underscores, it's an eager-load record.
            if (strpos($key, '__') !== false) {
                list($rel_name, $rel_key) = explode('__', $key);
                $rel_data[$rel_name][$rel_key] = $val;
                continue;
            }
            
            // set the value
            $this->_data[$key] = $data[$key];
        }
        
        // unserialize as needed
        $this->_unserialize();
        
        // load related data, if any was passed in
        $related = $this->_related;
        foreach ($related as $name => $opts) {
            
            // is this a "to-one" association with data already in place?
            $type = $opts['type'];
            if (($type == 'has_one' || $type == 'belongs_to') &&
                ! empty($rel_data[$name])) {
                // create a record object from the related model
                $model = Solar::factory($opts['foreign_model']);
                $this->_data[$name] = $model->_newRecord($rel_data[$name]);
            } else {
                // set a placeholder for lazy-loading in __get()
                $this->_data[$name] = null;
            }
            
            // either way, set the default related page
            $this->_related_page[$name] = $opts['page'];
        }
    }
    
    /**
     * 
     * Returns the appropriate record object for an inheritance model.
     * 
     * For example ...
     * {{
     *     class Solar_Model_Nodes extends Solar_Sql_Model {
     *         // ...
     *     }
     *     
     *     $nodes = Solar::factory('Solar_Model_Nodes');
     *     $class = $nodes->getRecordClass('comment');
     *     // $class == 'Solar_Model_Nodes_Comment';
     * }}
     * 
     * @param array $data The data to load into the record.
     * 
     * @return Solar_Sql_Model A model with 'record' focus.
     * 
     */
    protected function _newRecord($data)
    {
        // the model class we'll use for the record
        $class = null;
        
        // look for an inheritance model in relation to $data
        $inherit = null;
        if ($this->_inherit_col && ! empty($data[$this->_inherit_col])) {
            // inheritance is available, and a value is set for the
            // inheritance column in the data
            $inherit = trim($data[$this->_inherit_col]);
        }
        
        // did we find an inheritance?
        if ($inherit) {
            // try to find a class based on inheritance, going up the stack
            // as needed. this checks for Current_Model_Type,
            // Parent_Model_Type, Grandparent_Model_Type, etc.
            // suppress exceptions.
            // 
            // note that $class could still end up false, as we might not find
            // a related class in the hierarchy.
            $class = $this->_stack->load($inherit, false);
        }
        
        // were we able to load the inheritance class?
        if (! $class) {
            // no, fall back to default.
            $class = $this->_class;
        }
        
        // get the appropriate model class, load it, and return it.
        // use factory instead of registry, since STI may not have
        // caught this particular class yet.
        $obj = Solar::factory($class);
        $obj->_focus = 'record';
        $obj->_loadRecord($data);
        return $obj;
    }
    
    /**
     * 
     * Filters and inserts the current record data into the table.
     * 
     * @return void
     * 
     */
    protected function _insert()
    {
        // keep a copy of the data for manipulation (filters, etc)
        $this->_data = array_merge(
            $this->_fetchNew()->toArray(), // use the *protected* version
            $this->_data
        );
        
        // needed for created/updated timestamps
        $now = date('Y-m-d\\TH:i:s');
        
        // auto-add a 'created' value if there is a 'created' column
        // and its value is not already set.
        $key = $this->_created_col;
        if ($key && empty($this->_data[$key])) {
            $this->_data[$key] = $now;
        }
        
        // auto-add an 'updated' value if there is an 'updated' column
        // and its value is not already set.
        $key = $this->_updated_col;
        if ($key && empty($this->_data[$key])) {
            $this->_data[$key] = $now;
        }
        
        // auto-add sequence values
        foreach ($this->_sequence_cols as $key => $val) {
            if (empty($this->_data[$key])) {
                // no value given for the key.
                // add a new sequence value.
                $this->_data[$key] = $this->_sql->nextSequence($val);
            }
        }
        
        // filter the data (sanitize and validate)
        $filter = Solar::factory($this->_datafilter_class);
        $filter->setModel($this);
        $invalid = $filter->process($this->_data);
        
        // was there any invalid data?
        $this->_invalid = array();
        if ($invalid) {
            foreach ($invalid as $key => $str) {
                // set the generic invalidation message for this key
                $this->setInvalid($key, $this->locale("INVALID_" . strtoupper($key)));
                // set the invalidation reason
                $this->setInvalid($key, $str);
            }
            throw $this->_exception('ERR_FAILED_VALIDATION', $this->_invalid);
        }
        
        // remove non-existent ("virtual") columns from the data,
        foreach ($this->_data as $key => $val) {
            if (empty($this->_table_cols[$key])) {
                unset($this->_data[$key]);
            }
        }
        
        // serialize and attempt the insert.
        $this->_serialize();
        $result = $this->_sql->insert($this->_table_name, $this->_data);
        
        // if there was an autoincrement column, set its value in the data.
        foreach ($this->_table_cols as $key => $val) {
            if ($val['autoinc']) {
                // set the value and leave the loop (only one autoinc
                // should be here anyway)
                $this->_data[$key] = $this->_sql->lastInsertId();
                break;
            }
        }
        
        // unserialize the data and return.
        // @todo This does not reflect values from sql-based functions;
        // would need to re-select from the DB to get those.
        $this->_unserialize();
        $this->_status = 'inserted';
    }
    
    /**
     * 
     * Filters and updates an array of data in the table based on a WHERE
     * clause.
     * 
     * @return void
     * 
     * @todo Currently allows changing of primary-key values; should this be
     * disallowed as before?
     * 
     */
    protected function _update()
    {
        // auto-add an 'updated' value if there is an 'updated' column
        // and its value is not already set.
        $key = $this->_updated_col;
        if ($key && empty($this->_data[$key])) {
            $this->_data[$key] = date('Y-m-d\\TH:i:s');
        }
        
        // auto-add sequence values
        foreach ($this->_sequence_cols as $key => $val) {
            if (array_key_exists($key, $this->_data) &&
                empty($this->_data[$key])) {
                // key exists, but has no value.
                // update with new sequence value.
                $this->_data[$key] = $this->_sql->nextSequence($val);
            }
        }
        
        // filter the data (sanitize and validate)
        $filter = Solar::factory($this->_datafilter_class);
        $filter->setModel($this);
        $invalid = $filter->process($this->_data);
        
        // was there any invalid data?
        $this->_invalid = array();
        if ($invalid) {
            foreach ($invalid as $key => $str) {
                // set the generic invalidation message for this key
                $this->setInvalid($key, $this->locale("INVALID_" . strtoupper($key)));
                // set the invalidation reason
                $this->setInvalid($key, $str);
            }
            throw $this->_exception('ERR_FAILED_VALIDATION', $this->_invalid);
        }
        
        // remove non-existent ("virtual") columns from the data
        foreach ($this->_data as $key => $val) {
            if (empty($this->_table_cols[$key])) {
                unset($this->_data[$key]);
            }
        }
        
        // serialize data
        $this->_serialize();
        
        // keep the primary-key value and build a WHERE clause
        
        // attempt the update
        $primary = $this->_primary_col;
        $where = "$primary = :{$primary}";
        $this->_sql->update($this->_table_name, $this->_data, $where);
        
        // unserialize the data and return.
        // @todo This does not reflect values from sql-based functions;
        // would need to re-select from the DB to get those.
        $this->_unserialize();
        $this->_status = 'updated';
    }
    
    /**
     * 
     * Serializes values in $this->_data based on $this->_serialize_cols.
     * 
     * Does not attempt to serialize null values.
     * 
     * If serializing fails, stores 'null' in the data.
     * 
     * @return void
     * 
     */
    protected function _serialize()
    {
        foreach ($this->_serialize_cols as $key) {
            if (! empty($this->_data[$key]) && $this->_data[$key] !== null) {
                $this->_data[$key] = serialize($this->_data[$key]);
                if (! $this->_data[$key]) {
                    // serializing failed
                    $this->_data[$key] = null;
                }
            }
        }
    }
    
    /**
     * 
     * Unserializes values in $this->_data based on $this->_serialize_cols.
     * 
     * Does not attempt to unserialize null values.
     * 
     * If unserializing fails, stores 'null' in the data.
     * 
     * @return void
     * 
     */
    protected function _unserialize()
    {
        foreach ($this->_serialize_cols as $key) {
            if (! empty($this->_data[$key]) && $this->_data[$key] !== null) {
                $this->_data[$key] = unserialize($this->_data[$key]);
                if (! $this->_data[$key]) {
                    // unserializing failed
                    $this->_data[$key] = null;
                }
            }
        }
    }
    
    // -----------------------------------------------------------------
    // 
    // Record fetching and counting on *related* models.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Fetches the related record or collection for a named relationship and
     * primary key.
     * 
     * @param string $name The relationship name.
     * 
     * @param mixed $spec The primary key value; if an array, looks for the
     * primary-key column name and uses the corresponding value.
     * 
     * @param int $page For to-many associations, the page-number of records
     * to fetch (default null, which fetches all records).  Ignored by to-one
     * associations.  Paging is based on the related model's "$_paging"
     * property.
     * 
     * @return Solar_Sql_Model A model object with a 'record' or 'collection'
     * focus.
     * 
     */
    protected function _fetchRelated($name, $page = null)
    {
        $select = $this->_newRelatedSelect($name);
        $opts = $this->_related[$name];
        $type = $opts['type'];
        
        // fetch per the association type
        if ($type == 'has_one' || $type == 'belongs_to') {
            
            // this is a to-one association; fetch the data.
            $data = $select->fetch('one');
            
            // create and load into an appropriate Record object.
            $model = Solar::factory($opts['foreign_model']);
            $result = $model->_newRecord($data);
            
        } else {
            
            // this is a has_many association.  set the page ...
            $select->limitPage($page);
            
            // ... and fetch the data.
            $data = $select->fetch('all');
            
            // create and load a collection object
            $result = Solar::factory($opts['foreign_model']);
            $result->_focus = 'collection';
            $result->_data = $data;
        }
        
        // done!
        return $result;
    }
    
    /**
     * 
     * Returns a new Solar_Sql_Select tool, with the proper SQL object
     * injected automatically, and with eager "to-one" associations joined.
     * 
     * @param array $eager An array of to-one relationship names to eager-
     * load with LEFT JOIN clauses.
     * 
     * @return Solar_Sql_Select
     * 
     */
    protected function _newSelect($eager = null)
    {
        // get the select object
        $select = Solar::factory(
            'Solar_Sql_Select',
            array('sql' => $this->_sql)
        );
        
        // add eager has_one/belongs_to joins
        foreach ((array) $eager as $name) {
            
            if (empty($this->_related[$name])) {
                // skip unrecognized relationship names
                continue;
            }
            
            // get the relationship options
            $opts = $this->_related[$name];
            
            // only process eager to-one associations
            if ($opts['type'] == 'has_many') {
                continue;
            }
            
            // build column names as "name__col" so that we can extract the
            // the related data later.
            $cols = array();
            foreach ($opts['cols'] as $col) {
                $cols[] = "$col AS {$name}__$col";
            }
            
            // primary-key join condition on foreign table
            // local.id = foreign_alias.local_id
            $cond = "{$this->_table_name}.{$opts['native_col']} = "
                  . "{$opts['foreign_alias']}.{$opts['foreign_col']}";
            
            // add the join
            $select->leftJoin(
                "{$opts['foreign_table']} AS {$opts['foreign_alias']}",
                $cond,
                $cols
            );
            
            // inheritance for foreign model
            if ($opts['foreign_inherit_col']) {
                $select->where(
                    "{$opts['foreign_alias']}.{$opts['foreign_inherit_col']} = ?",
                    $opts['foreign_inherit_val']
                );
                
            }
            
            // added where conditions for the join
            $select->multiWhere($opts['where']);
        }
        
        // inheritance for native model
        if ($this->_inherit_model) {
            $select->where(
                "{$this->_table_name}.{$this->_inherit_col} = ?",
                $this->_inherit_model
            );
        }
        
        // done!
        return $select;
    }
    
    /**
     * 
     * Returns a new Solar_Sql_Select tool for selecting related records.
     * 
     * @param string $name The relationship name.
     * 
     * @return Solar_Sql_Select
     * 
     */
    protected function _newRelatedSelect($name)
    {
        if (! array_key_exists($name, $this->_related)) {
            throw $this->_exception('ERR_RELATED_NOT_EXIST', array(
                'name' => $name,
            ));
        }
        
        // get the options for this relationship
        $opts = $this->_related[$name];
        
        // get a select object
        $select = Solar::factory(
            'Solar_Sql_Select',
            array('sql' => $this->_sql)
        );
        
        // is this to-many through another relationship?
        if ($opts['type'] == 'has_many' && $opts['through']) {
            
            // more-complex 'has_many through' relationship.
            // select from the foreign table.
            $select->from(
                "{$opts['foreign_table']} AS {$opts['foreign_alias']}",
                $opts['cols']
            );
            
            // join through the mapping table.
            $join_table = "{$opts['through_table']} AS {$opts['through_alias']}";
            $join_where = "{$opts['foreign_alias']}.{$opts['foreign_col']} = "
                        . "{$opts['through_alias']}.{$opts['through_foreign_col']}";
            
            $select->leftJoin($join_table, $join_where);
            
            // restrict to the related native column value in the "through" table
            $select->where(
                "{$opts['through_alias']}.{$opts['through_native_col']} = ?",
                $this->_data[$opts['native_col']]
            );
            
            // honor foreign inheritance
            if ($opts['foreign_inherit_col']) {
                $select->where(
                    "{$opts['foreign_alias']}.{$opts['foreign_inherit_col']} = ?",
                    $opts['foreign_inherit_val']
                );
            }
            
        } else {
            
            // simple belongs_to, has_one, or has_many.
            // select columns from the foreign table.
            $select->from(
                "{$opts['foreign_table']} AS {$opts['foreign_alias']}",
                $opts['cols']
            );
            
            // restrict to the related native column value in the foreign table
            $select->where(
                "{$opts['foreign_alias']}.{$opts['foreign_col']} = ?",
                $this->_data[$opts['native_col']]
            );
            
            // honor foreign inheritance
            if ($opts['foreign_inherit_col']) {
                $select->where(
                    "{$opts['foreign_alias']}.{$opts['foreign_inherit_col']} = ?",
                    $opts['foreign_inherit_val']
                );
            }
        }
        
        // everything else
        $select->distinct($opts['distinct'])
               ->multiWhere($opts['where'])
               ->group($opts['group'])
               ->having($opts['having'])
               ->order($opts['order'])
               ->setPaging($opts['paging']);
        
        // done
        return $select;
    }
    
    
    /**
     * 
     * "Cleans up" SELECT clause parameters.
     * 
     * @param array $params The parameters for the SELECT clauses.
     * 
     * @return array A normalized set of clause params.
     * 
     */
    protected function _fixSelectParams($params)
    {
        settype($params, 'array');
        
        if (empty($params['distinct'])) {
            $params['distinct'] = false;
        }
        
        if (empty($params['cols'])) {
            $params['cols'] = array_keys($this->_table_cols);
        } else {
            // add primary and inherit cols?
        }
        
        if (empty($params['eager'])) {
            $params['eager'] = null;
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
        
        if (empty($params['paging'])) {
            $params['paging'] = $this->_paging;
        }
        
        if (empty($params['page'])) {
            $params['page'] = null;
        }
        
        if (empty($params['bind'])) {
            $params['bind'] = null;
        }
        
        return $params;
    }
    
    // -----------------------------------------------------------------
    // 
    // Relationship definitions.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Adds a named relationship.
     * 
     * @param string $type The relationship type: belongs_to, has_many, or
     * has_one.  Note that has-and-belongs-to-many (many-to-many) is 
     * accomplished using "has_many" through another relationship.
     * 
     * @param string $name The relationship name, which will double as a 
     * property when records are fetched from the model.
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _addRelated($type, $name, $opts = null)
    {
        if ($type == 'belongs_to' || $type == 'has_one' || $type == 'has_many') {
            settype($opts, 'array');
            $opts['type'] = $type;
            $this->_related[$name] = $opts;
        } else {
            throw $this->_exception('ERR_RELATED_TYPE', array(
                'type' => $type,
                'name' => $name,
                'opts' => $opts,
            ));
        }
    }
    
    
    // -----------------------------------------------------------------
    // 
    // User setup and post-setup corrections.
    // 
    // -----------------------------------------------------------------
    
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
    
    /**
     * 
     * Checks the current focus, and throws an exception if they don't match.
     * 
     * @param string|array $allow The focus we should have right now.
     * 
     * @return void
     * 
     * @throws Solar_Sql_Model_Exception_Focus_Is_* Indicates the focus
     * is not the requested one.
     * 
     */
    protected function _checkFocus($allow)
    {
        settype($allow, 'array');
        if (! in_array($this->_focus, $allow)) {
            throw $this->_exception(
                "ERR_FOCUS_IS_" . strtoupper($this->_focus),
                array(
                    'allow' => $allow,
                )
            );
        }
    }
    
    /**
     * 
     * Throws an exception if this record status is 'deleted'.
     * 
     * @return void
     * 
     * @throws Solar_Sql_Model_Exception_Deleted Indicates that the
     * record object has been deleted and cannot be used.
     * 
     */
    protected function _checkDeleted()
    {
        if ($this->_status == 'deleted') {
            throw $this->_exception('ERR_DELETED');
        }
    }
    
    /**
     * 
     * Custom dumper so that you don't get a gigantic readout of all
     * embedded objects.
     * 
     * @param string $var The property name to dump; if empty, dumps a
     * reduced set of properties.
     * 
     * @param string $label Add this label to the dump output.  If empty,
     * uses a default label.
     * 
     * @return void
     * 
     */
    public function dump($var = null, $label = null)
    {
        if ($this->_focus == 'record' || $this->_focus == 'collection') {
            $copy = $this->_dump($this);
            return parent::dump($copy, $label);
        }
        
        if ($var) {
            Solar::dump($this->$var, $label);
        } else {
            // clone $this and remove the parent config arrays
            $clone = clone($this);
            foreach (Solar::parents($this) as $class) {
                $key = "_$class";
                unset($clone->$key);
            }
            
            // unset some other big items
            unset($clone->_sql);
            unset($clone->_model);
            
            // done!
            parent::dump($clone, $label);
        }
    }
    
    /**
     * 
     * Helper method for dump(); copies object and unsets properties from the
     * copy.
     * 
     * @param mixed $orig The original variable.
     * 
     * @return mixed A copy of the variable with some properties unset.
     * 
     */
    protected function _dump($orig)
    {
        $copy = clone($orig);
        
        // unset filters, table info, etc. note related to the record
        // directly.
        $unset = array(
            '_class',
            '_config',
            '_created_col',
            '_datafilter_class',
            '_fetch_cols',
            '_filters',
            '_foreign_col',
            '_indexes',
            '_inherit_base',
            '_inherit_col',
            '_inherit_model',
            '_model_name',
            '_order',
            '_paging',
            '_primary_col',
            '_records',
            '_related',
            '_sequence_cols',
            '_serialize_cols',
            '_sql',
            '_stack',
            '_table_cols',
            '_table_name',
            '_updated_col',
        );
        
        // remove parent configs too
        foreach (Solar::parents($copy) as $class) {
            $unset[] = "_$class";
        }
        
        // actually unset
        foreach ($unset as $var) {
            unset($copy->$var);
        }
        
        // unset internal sub-records
        foreach ($copy->_data as $key => $val) {
            if ($val instanceof Solar_Sql_Model) {
                // get a copy for the dump
                $copy->_data[$key] = $this->_dumpRecord($val);
            }
        }
        
        // done!
        return $copy;
    }
    
    /**
     * 
     * ArrayAccess: does the requested key exist?
     * 
     * @param string $key The requested key.
     * 
     * @return bool
     * 
     */
    final public function offsetExists($key)
    {
        return array_key_exists($key, $this->_data);
    }
    
    /**
     * 
     * ArrayAccess: get a key value.
     * 
     * @param string $key The requested key.
     * 
     * @return mixed
     * 
     */
    final public function offsetGet($key)
    {
        $this->_checkFocus(array('record', 'collection'));
        
        if ($this->_focus == 'record') {
            return $this->__get($key);
        }
        
        if ($this->_focus == 'collection') {
            // don't return records that don't exist in the original data
            if (empty($this->_data[$key])) {
                return false;
            }
            
            // load the record if needed, honoring single table inheritance
            if (empty($this->_records[$key])) {
                $this->_records[$key] = $this->_newRecord($this->_data[$key]);
            }
        
            // return the record
            return $this->_records[$key];
        }
    }
    
    /**
     * 
     * ArrayAccess: set a key value.
     * 
     * @param string $key The requested key.
     * 
     * @param string $val The value to set it to.
     * 
     * @return void
     * 
     * @todo If $key is null, that is [] ("append") notation.  Only let it
     * work for collections?
     * 
     */
    final public function offsetSet($key, $val)
    {
        $this->_checkFocus(array('record', 'collection'));
        
        if ($this->_focus == 'record') {
            return $this->__set($key, $val);
        }
        
        if ($this->_focus == 'collection') {
            if ($key === null) {
                $key = $this->count();
                if (! $key) {
                    $key = 0;
                }
            }
            return $this->_data[$key] = $val;
        }
    }
    
    /**
     * 
     * ArrayAccess: unset a key (sets it to null).
     * 
     * @param string $key The requested key.
     * 
     * @return void
     * 
     */
    final public function offsetUnset($key)
    {
        $this->__set($key, null);
    }
    
    /**
     * 
     * Countable: how many keys are there?
     * 
     * @return int
     * 
     */
    final public function count()
    {
        if ($this->_focus == 'record') {
            return count($this->_data);
        }
        
        if ($this->_focus == 'collection') {
            return count($this->_data);
        }
        
        return false;
    }
    
    /**
     * 
     * IteratorAggregate: return an Iterator appropriate for the model focus.
     * 
     * @return Solar_Sql_Model_RecordIterator|Solar_Sql_Model_CollectionIterator
     * 
     */
    final public function getIterator()
    {
        if ($this->_focus == 'record') {
            $class = 'Solar_Sql_Model_RecordIterator';
        } elseif ($this->_focus == 'collection') {
            $class = 'Solar_Sql_Model_CollectionIterator';
        } else {
            // not in the right focus for iteration
            return null;
        }
        
        $iter = Solar::factory($class);
        $iter->setData($this->_data);
        $iter->setModel($this);
        return $iter;
    }
}
