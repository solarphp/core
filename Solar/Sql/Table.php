<?php
/**
 * 
 * Class for representing an SQL table.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Table.php 1879 2006-09-30 19:35:32Z pmjones $
 * 
 */

/**
 * Needed for instanceof comparisons.
 */
Solar::loadClass('Solar_Sql_Row');

/**
 * 
 * Class for representing an SQL table.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Table extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `sql`
     * : (dependency) A Solar_Sql dependency object.
     * 
     * `locale`
     * : (string) Path to locale files.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Table = array(
        'sql'    => 'sql',
        'paging' => 10,
    );
    
    /**
     * 
     * The table name.
     * 
     * @var string
     * 
     */
    protected $_name = null;
    
    /**
     * 
     * The default order when fetching rows.
     * 
     * @var array
     * 
     */
    protected $_order = array('id');
    
    /**
     * 
     * The numer of rows per page when selecting.
     * 
     * @var int
     * 
     */
    protected $_paging = 10;
    
    /**
     * 
     * The column specification array for all columns in this table.
     * 
     * Each element in this array looks like this...
     * 
     * {{code: php
     *     $col = array(
     *         'col_name' => array(
     *             'name'    => (string) the col_name, same as the key
     *             'type'    => (string) char, varchar, date, etc
     *             'size'    => (int) column size
     *             'scope'   => (int) decimal places
     *             'valid'   => (array) Solar_Valid methods and args
     *             'require' => (bool) is this a required (non-null) column?
     *             'autoinc' => (bool) auto-increment
     *             'default' => (string|array) default value
     *             'primary' => (bool) is this part of the primary key?
     *          ),
     *     );
     * }}
     * 
     * @var array
     * 
     */
    protected $_col = array();
    
    /**
     * 
     * The index specification array for all indexes on this table.
     * 
     * @var array
     * 
     * @see addIndex()
     * 
     */
    protected $_idx = array();
    
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
     * The object class returned by fetch(), fetchNew(), and fetchWhere().
     * 
     * @var string
     * 
     */
    protected $_row_class = 'Solar_Sql_Row';
    
    /**
     * 
     * The object class returned by fetchAll().
     * 
     * @var string
     * 
     */
    protected $_all_class = 'Solar_Sql_Rowset';
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     * @return void
     * 
     */
    public function __construct($config = null)
    {
        // main construction
        parent::__construct($config);
        $this->setPaging($this->_config['paging']);
        
        // perform column and index setup, then fix everything.
        $this->_setup();
        $this->_autoSetup();
        
        // connect to the database with dependency injection
        $this->_sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // auto-create the table if needed
        $this->_autoCreate();
    }
    
    /**
     * 
     * Allows reading of protected properties.
     * 
     * @param string $key The property name.
     * 
     * @return mixed The property value.
     * 
     */
    public function __get($key = null)
    {
        $prop = array('col', 'idx', 'name', 'paging');
        if (in_array($key, $prop)) {
            $key = "_$key";
            return $this->$key;
        } else {
            return null;
        }
    }
    
    /**
     * 
     * Sets the number of rows per page.
     * 
     * @param int $val The number of rows per page.
     * 
     * @return void
     * 
     */
    public function setPaging($val)
    {
        $this->_paging = (int) $val;
    }
    
    /**
     * 
     * Inserts or updates a single row based on its ID.
     * 
     * @param array $data An associative array of data to be saved, in
     * the format (field => value).
     * 
     * @return array The data as inserted or updated.
     * 
     */
    public function save($data)
    {
        if (empty($data['id'])) {
            return $this->insert($data);
        } else {
            $where = $this->_sql->quoteInto('id = ?', $data['id']);
            return $this->update($data, $where);
        }
    }
    
    /**
     * 
     * Validates and inserts data into the table.
     * 
     * @param array $data An associative array of data to be inserted, in
     * the format (field => value).
     * 
     * @return array The data as inserted.
     * 
     */
    public function insert($data)
    {
        // set defaults
        $data = array_merge($this->fetchNew()->toArray(), (array) $data);
        
        // auto-add sequential values
        foreach ($this->_col as $colname => $colinfo) {
            // does this column autoincrement, and is no data provided?
            if ($colinfo['autoinc'] && empty($data[$colname])) {
                $data[$colname] = $this->increment($colname);
            }
        }
        
        // add created/updated timestamps
        $now = date('Y-m-d\TH:i:s');
        
        if (empty($data['created'])) {
            $data['created'] = $now;
        }
        
        if (empty($data['updated'])) {
            $data['updated'] = $now;
        }
        
        // validate and recast the data.
        $result = $this->_autoValid($data);
        
        // attempt the insert.
        $result = $this->_sql->insert($this->_name, $data);
        
        // return the data as inserted
        return $data;
    }
    
    /**
     * 
     * Validates and updates data in the table based on a WHERE clause.
     * 
     * @param array $data An associative array of data to be updated, in
     * the format (field => value).
     * 
     * @param string $where An SQL WHERE clause limiting the updated
     * rows.
     * 
     * @return array The data as updated.
     * 
     */
    public function update($data, $where)
    {
        // retain primary key data in this array for return values
        $retain = array();
        
        // disallow the changing of primary key data
        foreach (array_keys($data) as $field) {
            if (! empty($this->_col[$field]['primary'])) {
                $retain[$field] = $data[$field];
                unset($data[$field]);
            }
        }
        
        // forcibly set the "updated" timestamp
        $data['updated'] = date('Y-m-d\TH:i:s');
        
        // validate and recast the data
        $result = $this->_autoValid($data);
        
        // attempt the update
        $result = $this->_sql->update($this->_name, $data, $where);
        
        // restore retained primary key data and return
        $data = array_merge($data, $retain);
        return $data;
    }
    
    /**
     * 
     * Deletes rows in the table based on a WHERE clause.
     * 
     * @param string $where An SQL WHERE clause limiting the deleted rows.
     * 
     * @return void
     * 
     */
    public function delete($where)
    {
        // attempt the deletion
        $result = $this->_sql->delete($this->_name, $where);
        return $result;
    }
    
    /**
     * 
     * Convenience method to select rows from this table.
     * 
     * @param string $type The type of select to execute: 'all', 'one',
     * 'row', etc. Default is 'result'.
     * 
     * @param string|array $where A Solar_Sql_Select::multiWhere() parameter.
     * 
     * @param string|array $order A Solar_Sql_Select::order() parameter.
     * 
     * @param int $page The page number of rows to fetch.
     * 
     * @return mixed
     * 
     */
    public function select($type = 'result', $where = null,
        $order = null, $page = null)
    {
        $select = Solar::factory('Solar_Sql_Select');
        
        if ($type == 'all') {
            $class = $this->_all_class;
        } elseif ($type == 'row') {
            $class = $this->_row_class;
        } else {
            $class = null;
        }
        
        $result = $select->from($this->_name, array_keys($this->_col))
                         ->multiWhere($where)
                         ->order($order)
                         ->setPaging($this->_paging)
                         ->limitPage($page)
                         ->fetch($type, $class);
        
        if ($result instanceof Solar_Sql_Row) {
            $result->setSave($this);
        }
        
        return $result;
    }
    
    /**
     * 
     * Increments and returns the sequence value for a column.
     * 
     * @param string $name The column name.
     * 
     * @return int The next sequence number for the column.
     * 
     */
    public function increment($name)
    {
        // only increment if auto-increment is set
        if (! empty($this->_col[$name]['autoinc'])) {
            // table__column
            $seqname = $this->_name . '__' . $name;
            $result = $this->_sql->nextSequence($seqname);
            return $result;
        } else {
            return null;
        }
    }
    
    /**
     * 
     * Fetches one row from the table by its primary key ID.
     * 
     * @param int $id The primary key ID value.
     * 
     * @return Solar_Sql_Row
     * 
     */
    public function fetch($id)
    {
        $where = array('id = ?' => $id);
        return $this->select('row', $where);
    }
    
    /**
     * 
     * Fetches all rows by arbitrary criteria.
     * 
     * @param string|array $where A Solar_Sql_Select::multiWhere() parameter.
     * 
     * @param string|array $order A Solar_Sql_Select::order() parameter.
     * 
     * @param int $page The page number of rows to fetch.
     * 
     * @return Solar_Sql_Rowset
     * 
     */
    public function fetchAll($where = null, $order = null, $page = null)
    {
        return $this->select('all', $where, $order, $page);
    }
    
    /**
     * 
     * Returns a new row of column keys and default values.
     * 
     * @return Solar_Sql_Row
     * 
     */
    public function fetchNew()
    {
        // the array of default data
        $data = array();
        
        // loop through each specified column and collect default data
        $spec = array_keys($this->_col);
        foreach ($spec as $name) {
            
            // skip columns that don't exist
            if (empty($this->_col[$name])) {
                continue;
            }
            
            // get the column info
            $info = $this->_col[$name];
            
            // is there a default set?
            if (empty($info['default'])) {
                // no default, so it's null.
                $data[$name] = null;
                continue;
            }
            
            // yes, so get it based on the kind of default.
            // we shift off the front of the array as we go.
            // element 0 is the type (literal or callback),
            // element 1 is the literal (or callback name),
            // elements 2+ are any arguments for a callback.
            $type = array_shift($info['default']);
            switch ($type) {
            
            case 'callback':
                $func = array_shift($info['default']);
                $data[$name] = call_user_func_array($func, $info['default']);
                break;
            
            case 'literal':
                $data[$name] = array_shift($info['default']);
                break;
            
            default:
                $data[$name] = null;
            }
        }
        
        // done!
        $row = Solar::factory($this->_row_class, array('data' => $data));
        $row->setSave($this);
        return $row;
    }
    
    /**
     * 
     * Fetches one row by arbitrary criteria.
     * 
     * @param string|array $where A Solar_Sql_Select::multiWhere() parameter.
     * 
     * @param string|array $order A Solar_Sql_Select::order() parameter.
     * 
     * @return Solar_Sql_Row
     * 
     */
    public function fetchWhere($where = null, $order = null)
    {
        return $this->select('row', $where, $order);
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Support and management methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Use this to set up extended table objects.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
    
    /**
     * 
     * Fixes the $col and $idx properties after user setup.
     * 
     * @return void
     * 
     */
    protected function _autoSetup()
    {
        // make sure there's a table name.  defaults to the
        // part after the last underscore, then converts camelCaps 
        // to underscore_words.
        if (empty($this->_name)) {
            // get the class name
            $tmp = get_class($this);
            // get the part after the last underscore
            $tmp = substr($tmp, strrpos($tmp, '_'));
            // camels to unders
            $this->_name = preg_replace('/([a-z])([A-Z])/', "$1_$2", $tmp);
        }
        
        // make sure table name is lower case regardless
        $this->_name = strtolower($this->_name);
        
        // a baseline column definition
        $basecol = array(
            'name'    => null,
            'type'    => null,
            'size'    => null,
            'scope'   => null,
            'primary' => false,
            'require' => false,
            'autoinc' => false,
            'default' => null,
            'valid'   => array(),
        );
        
        // baseline index definition
        $baseidx = array(
            'name'    => null,
            'type'    => 'normal',
            'cols'    => null,
        );
        
        // auto-added columns and indexes
        $autocol = array();
        $autoidx = array();
        
        // auto-add an ID column and index for unique identification
        if (! array_key_exists('id', $this->_col)) {
            $autocol['id'] = array(
                'type'    => 'int',
                'primary' => true,
                'require' => true,
                'autoinc' => true,
            );
            
            $autoidx['id'] = array(
                'type' => 'unique',
                'cols' => 'id',
            );
        }
        
        // auto-add a "created" column to track when created
        if (! array_key_exists('created', $this->_col)) {
            $autocol['created'] = array(
                'type'    => 'timestamp',
                'default' => array('callback', 'date', 'Y-m-d\TH:i:s'),
            );
            
            $autoidx['created'] = array(
                'type' => 'normal',
                'cols' => 'created',
            );
        }
        
        // auto-add an "updated" column and index
        // to track when last updated
        if (! array_key_exists('updated', $this->_col)) {
            $autocol['updated'] = array(
                'type'    => 'timestamp',
                'default' => array('callback', 'date', 'Y-m-d\TH:i:s'),
            );
            
            $autoidx['updated'] = array(
                'type' => 'normal',
                'cols' => 'updated',
            );
        }
        
        // merge the auto-added items on top of the rest
        $this->_col = array_merge($autocol, $this->_col);
        $this->_idx = array_merge($autoidx, $this->_idx);
        
        // fix up each column to have a full set of info
        foreach ($this->_col as $name => $info) {
        
            // fill in missing elements
            $info = array_merge($basecol, $info);
            
            // make sure there's a name
            $info['name'] = $name;
            
            // if 'valid' is a string, make the validation a simple
            // Solar_Valid method call.
            if (is_string($info['valid'])) {
                $info['valid'] = array(
                    $info['valid'],
                    'VALID_' . strtoupper($info['valid']),
                );
            }
            
            
            // if 'default' is not already an array, make it
            // one as a literal.  this lets you avoid the array
            // when setting up simple literals.
            if (! is_array($info['default'])) {
                $info['default'] = array('literal', $info['default']);
            }
            
            // save back into the column info
            $this->_col[$name] = $info;
        }
        
        // fix up each index to have a full set of info
        foreach ($this->_idx as $key => $val) {
            
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
            
            $this->_idx[$key] = $info;
        }
    }
    
    /**
     * 
     * Creates the table in the database if it does not already exist.
     * 
     * @return bool False if the table already existed and didn't
     * need to be created, or true if the table did not exist and was
     * successfully created.
     * 
     */
    protected function _autoCreate()
    {
        // is a table with the same name already there?
        $tmp = $this->_sql->listTables();
        $here = strtolower($this->_name);
        foreach ($tmp as $there) {
            if ($here == strtolower($there)) {
                // table already exists
                return false;
            }
        }
        
        // create the table itself
        $this->_sql->createTable(
            $this->_name,
            $this->_col
        );
        
        // create each of the indexes
        foreach ($this->_idx as $name => $info) {
            try {
                // create this index
                $this->_sql->createIndex(
                    $this->_name,
                    $info['name'],
                    $info['type'] == 'unique',
                    $info['cols']
                );
            } catch (Exception $e) {
                /** @todo Does this throw a TableNotCreated exception too? */
                // cancel the whole deal.
                $this->_sql->dropTable($this->_name);
                throw $e;
            }
        }
        
        // post-creation
        try {
            $this->_postCreate();
        } catch (Exception $e) {
            /** @todo Does this throw a TableNotCreated exception too? */
            // cancel the whole deal.
            $this->_sql->dropTable($this->_name);
            throw $e;
        }
        
        // creation of the table and its indexes is complete
        return true;
    }
    
    /**
     * 
     * Additional table-creation tasks, such as inserting rows.
     * 
     * @return void
     * 
     */
    protected function _postCreate()
    {
    }
    
    /**
     * 
     * Validates and recasts an array of input/update data in-place.
     * 
     * @param array &$data An associative array of data as (field => value).
     * Note that this is a reference; the array will be modified in-place.
     * 
     * @return void
     * 
     * @todo Better error codes and exceptions?
     * 
     */
    protected function _autoValid(&$data)
    {
        // object methods for validation
        $valid = Solar::factory('Solar_Valid');
        
        // low and high range values for integers
        $int_range = array(
            'smallint' => array(pow(-2, 15), pow(+2, 15) - 1),
            'int'      => array(pow(-2, 31), pow(+2, 31) - 1),
            'bigint'   => array(pow(-2, 63), pow(+2, 63) - 1)
        );
        
        // collect all errors captured for all fields
        $err = array();
        
        // the list of available fields; discard data that
        // does not correspond to one of the known fields.
        $known = array_keys($this->_col);
        
        // loop through each data field
        foreach ($data as $field => $value) {
            
            // is this field recognized?
            if (! in_array($field, $known)) {
                // drop it and loop to the next field.
                unset($data[$field]);
                continue;
            }
            
            // if 'require' not present, it's not required
            if (isset($this->_col[$field]['require'])) {
                $require = $this->_col[$field]['require'];
            } else {
                $require = false;
            }
            
            // if null and required, it's not valid.
            if ($require && is_null($value)) {
                $err[$field][] = array(
                    'code' => 'VALID_NOTBLANK',
                    'text' => $this->locale('VALID_NOTBLANK'),
                    'data' => $value,
                    'info' => array(),
                );
                continue;
            }
            
            // if null and not required, it's valid.
            if (! $require && is_null($value)) {
                continue;
            }
            
            
            // -------------------------------------------------------------
            // 
            // Recast first, then validate for column type
            // 
            
            $type = $this->_col[$field]['type'];
            switch ($type) {
            
            case 'bool':
                $value = ($value) ? 1 : 0;
                break;
            
            case 'char':
            case 'varchar':
                settype($value, 'string');
                $max = $this->_col[$field]['size'];
                if (! $valid->maxLength($value, $max, Solar_Valid::OR_BLANK)) {
                    $err[$field][] = array(
                        'code' => 'VALID_MAXLENGTH',
                        'text' => $this->locale('VALID_MAXLENGTH'),
                        'data' => $value,
                        'info' => array(
                            'max' => $max,
                        ),
                    );
                }
                break;
            
            case 'int':
            case 'bigint':
            case 'smallint':
                settype($value, 'int');
                $min = $int_range[$type][0];
                $max = $int_range[$type][1];
                if (! $valid->range($value, $min, $max)) {
                    $err[$field][] = array(
                        'code' => 'VALID_RANGE',
                        'text' => $this->locale('VALID_RANGE'),
                        'data' => $value,
                        'info' => array(
                            'min' => $min,
                            'max' => $max,
                        ),
                    );
                }
                break;
            
            case 'float':
                settype($value, 'float');
                break;
            
            case 'numeric':
                settype($value, 'float');
                $size = $this->_col[$field]['size'];
                $scope = $this->_col[$field]['scope'];
                if (! $valid->scope($value, $size, $scope)) {
                    $err[$field][] = array(
                        'code' => 'VALID_SCOPE',
                        'text' => $this->locale('VALID_SCOPE'),
                        'data' => $value,
                        'info' => array(
                            'size' => $size,
                            'scope' => $scope,
                        ),
                    );
                }
                break;
            
            case 'date':
                settype($value, 'string');
                if (! $valid->isoDate($value)) {
                    $err[$field][] = array(
                        'code' => 'VALID_DATE',
                        'text' => $this->locale('VALID_DATE'),
                        'data' => $value,
                        'info' =>  array(),
                    );
                }
                break;
            
            case 'time':
                settype($value, 'string');
                if (strlen($value) == 5) {
                    // add seconds if only hours and minutes
                    $value .= ":00";
                }
                if (! $valid->isoTime($value)) {
                    $err[$field][] = array(
                        'code' => 'VALID_TIME',
                        'text' => $this->locale('VALID_TIME'),
                        'data' => $value,
                        'info' =>  array(),
                    );
                }
                break;
            
            case 'timestamp':
                settype($value, 'string');
                // make sure it's in the format yyyy-mm-ddThh:ii:ss
                $value = substr($value, 0, 10) . 'T' . substr($value, 11, 8);
                if (! $valid->isoTimestamp($value)) {
                    $err[$field][] = array(
                        'code' => 'VALID_TIMESTAMP',
                        'text' => $this->locale('VALID_TIMESTAMP'),
                        'data' => $value,
                        'info' =>  array(),
                    );
                }
                break;
            }
            
            // -------------------------------------------------------------
            // 
            // Content validations, if any
            // 
            
            if ($this->_col[$field]['valid']) {
                
                // the error code if validation fails.
                // (0 is the method, 1 is the message, 2... are params)
                $code = $this->_col[$field]['valid'][1];
                
                // if there was a message returned, then validation failed.
                if ($valid->feedback($value, $this->_col[$field]['valid'])) {
                    $err[$field][] = array(
                        'code' => $code,
                        'text' => $this->locale($code),
                        'data' => $value,
                        'info' =>  array(),
                    );
                }
            }
            
            // ---------------------------------------------------------
            // 
            // Retain the recasted and validated value, since it was
            // passed by reference.
            // 
            
            $data[$field] = $value;
            
            
        } // endforeach()
        
        
        // -------------------------------------------------------------
        // 
        // Done.
        // 
        
        if ($err) {
            // there were errors, throw an exception
            throw $this->_exception('ERR_INVALID_DATA', $err);
        }
    }
}
?>