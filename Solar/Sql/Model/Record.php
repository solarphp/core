<?php
/**
 * 
 * Represents a single record returned from a Solar_Sql_Model.
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
class Solar_Sql_Model_Record extends Solar_Struct
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
     * Notes which values are not valid.
     * 
     * Keyed on property name => failure message.
     * 
     * @var array
     * 
     */
    protected $_invalid = array();
    
    /**
     * 
     * Tracks which relationship pages are loaded.
     * 
     * Keys on the relationship name.
     * 
     * @var array
     * 
     */
    protected $_related_page = array();
    
    /**
     * 
     * If you call save() and an exception gets thrown, this stores that
     * exception.
     * 
     * @var Solar_Exception
     * 
     */
    protected $_save_exception;
    
    /**
     * 
     * Filters added for this one record object.
     * 
     * @var array
     * 
     */
    protected $_filters = array();
    
    /**
     * 
     * An array of the initial (clean) data for the record.
     * 
     * This tracks only table-column data, not calculate-cols or related-cols.
     * 
     * @var array
     * 
     * @see setStatus()
     * 
     */
    protected $_initial = array();
    
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
    public function __get($key)
    {
        // disallow if status is 'deleted'
        $this->_checkDeleted();
        
        // do we need to load relationship data?
        $load_related = empty($this->_data[$key]) &&
                        ! empty($this->_model->related[$key]);
        
        if ($load_related) {
            // the key was for a relation that has no data yet.
            // load the data.  don't return at this point, look
            // for accessor methods later.
            $related = $this->_model->getRelated($key);
            $this->_data[$key] = $related->fetchObject(
                $this,
                $this->_related_page[$key]
            );
        }
        
        // if an accessor method exists, use it
        if (! empty($this->_access_methods['get'][$key])) {
            // use accessor method
            $method = $this->_access_methods['get'][$key];
            return $this->$method();
        } else {
            // no accessor method; use parent method.
            return parent::__get($key);
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
        // disallow if status is 'deleted'
        $this->_checkDeleted();
        
        // keep track if this is a "new" record
        $is_new = $this->getStatus() == 'new';
        
        // if an accessor method exists, use it
        if (! empty($this->_access_methods['set'][$key])) {
            // use accessor method. will mark the record and its parents
            // as dirty.
            $method = $this->_access_methods['set'][$key];
            $this->$method($val);
        } else {
            // no accessor method; use parent method. will mark the record
            // and its parents as dirty.
            parent::__set($key, $val);
        }
        
        // setting values will mark the record as 'dirty' -- but 'new' is a
        // special case that we have to allow for.
        if ($is_new) {
            // reset self back to new.
            $this->_status = 'new';
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
        // disallow if status is 'deleted'
        $this->_checkDeleted();
        
        // if an accessor method exists, use it
        if (! empty($this->_access_methods['unset'][$key])) {
            // use accessor method
            $method = $this->_access_methods['unset'][$key];
            $this->$method();
        } else {
            // no accessor method; use parent method
            parent::__unset($key);
        }
    }
    
    /**
     * 
     * Checks if a data key is set.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __isset($key)
    {
        // disallow if status is 'deleted'
        $this->_checkDeleted();
        
        // if an accessor method exists, use it
        if (! empty($this->_access_methods['isset'][$key])) {
            // use accessor method
            $method = $this->_access_methods['isset'][$key];
            $result = $this->$method();
        } else {
            // no accessor method; use parent method
            $result = parent::__isset($key);
        }
        
        // done
        return $result;
    }
    
    /**
     * 
     * Overrides normal locale() to use the **model** locale strings.
     * 
     * @param string $key The key to get a locale string for.
     * 
     * @param string $num If 1, returns a singular string; otherwise, returns
     * a plural string (if one exists).
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return string The locale string, or the original $key if no
     * string found.
     * 
     */
    public function locale($key, $num = 1, $replace = null)
    {
        return $this->_model->locale($key, $num, $replace);
    }
    
    /**
     * 
     * Loads the struct with data from an array or another struct.
     * 
     * Also unserializes columns per the "serialize_cols" model property.
     * 
     * This is a complete override from the parent load() method.
     * 
     * @param array|Solar_Struct $spec The data to load into the object.
     * 
     * @param array $cols Load only these columns.
     * 
     * @return void
     * 
     */
    public function load($spec, $cols = null)
    {
        // force to array
        if ($spec instanceof Solar_Struct) {
            // we can do this because $spec is of the same class
            $load = $spec->_data;
        } elseif (is_array($spec)) {
            $load = $spec;
        } else {
            $load = array();
        }
        
        // remove any load columns not in the whitelist
        if (! empty($cols)) {
            $cols = (array) $cols;
            foreach ($load as $key => $val) {
                if (! in_array($key, $cols)) {
                    unset($load[$key]);
                }
            }
        }
        
        // unserialize any serialize_cols in the load
        $this->_model->unserializeCols($load);
        
        // set actual table columns, removing from the load as we go
        foreach ($this->_model->table_cols as $col => $info) {
            if (array_key_exists($col, $load)) {
                $this->__set($col, $load[$col]);
                unset($load[$col]);
            }
        }
        
        // restructure to-one eager-loaded data
        foreach ($load as $key => $val) {
            // if the key has double-underscores anywhere besides the very
            // first characters, it's from a single eager-load record.
            if (strpos($key, '__')) {
                list($rel_name, $rel_key) = explode('__', $key);
                $load[$rel_name][$rel_key] = $val;
                unset($load[$key]);
            }
        }
        
        // set related data as records and collections, removing from the load
        // as we go.
        $list = array_keys($this->_model->related);
        foreach ($list as $name) {
            
            // first, set a placeholder for lazy-loading in __get()
            $this->_data[$name] = null;
            
            // by default get all related records
            $this->_related_page[$name] = 0;
            
            // is there a key in the load for this related data?
            if (! array_key_exists($name, $load)) {
                // no key, which means no eager loading of related data.
                continue;
            }
            
            // populate eager-loaded data, even if it's empty.
            // get the relationship object
            $related = $this->_model->getRelated($name);
            
            // get the related model and build the related record/collection
            $model = $related->getModel();
            if ($related->isOne()) {
                $this->_data[$name] = $model->newRecord($load[$name]);
            } elseif ($related->isMany()) {
                $this->_data[$name] = $model->newCollection($load[$name]);
            }
            
            // remove from the load data
            unset($load[$name]);
        }
        
        // set placeholders for calculate cols
        $list = array_keys($this->_model->calculate_cols);
        foreach ($list as $name) {
            if (! array_key_exists($name, $this->_data)) {
                $this->_data[$name] = null;
            }
        }
        
        // set all remaining values in the load
        foreach ($load as $key => $val) {
            $this->__set($key, $val);
        }
    }
    
    // -----------------------------------------------------------------
    //
    // Model
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Injects the model from which the data originates.
     * 
     * Also loads accessor method lists for column and related properties.
     * 
     * These let users override how the column properties are accessed
     * through the magic __get, __set, etc. methods.
     * 
     * @param Solar_Sql_Model $model The origin model object.
     * 
     * @return void
     * 
     */
    public function setModel(Solar_Sql_Model $model)
    {
        $this->_model = $model;
        
        // get a list of table-column and related-data properties names
        $vars = array_merge(
            array_keys($this->_model->table_cols),
            array_keys($this->_model->related),
            array_keys($this->_model->calculate_cols)
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
                if (method_exists($this, $method)) {
                    $this->_access_methods[$type][$var] = $method;
                }
            }
            
            // put placeholders for each variable; these will be reset by
            // the load() and/or __set() methods.  need to have this here
            // because load() uses __set(), and primary keys will be ignored
            // in that case, leaving the data key unset.  at the same time,
            // we don't want to override values that are already present.
            if (! isset($this->_data[$var])) {
                $this->_data[$var] = null;
            }
        }
    }
    
    /**
     * 
     * Returns the model from which the data originates.
     * 
     * @return Solar_Sql_Model $model The origin model object.
     * 
     */
    public function getModel()
    {
        return $this->_model;
    }
    
    /**
     * 
     * Gets the name of the primary-key column.
     * 
     * @return string
     * 
     */
    public function getPrimaryCol()
    {
        return $this->_model->primary_col;
    }
    
    /**
     * 
     * Gets the value of the primary-key column.
     * 
     * @return mixed
     * 
     */
    public function getPrimaryVal()
    {
        $col = $this->_model->primary_col;
        return $this->$col;
    }
    
    
    // -----------------------------------------------------------------
    //
    // Record data
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Converts the properties of this model Record or Collection to an array,
     * including related models stored in properties and calculated columns.
     * 
     * @return array
     * 
     */
    public function toArray()
    {
        $data = array();
        
        $keys = array_keys($this->_data);
        foreach ($keys as $key) {
            
            // is the key a related record/collection, but not fetched yet?
            $empty_related = ! empty($this->_model->related[$key])
                          && empty($this->_data[$key]);
            
            if ($empty_related) {
                
                $related = $this->_model->getRelated($key);
                
                // do not fetch related just for array conversion, this leads
                // to some deep (perhaps infinite?) recursion
                if ($related->isMany()) {
                    $val = array();
                } else {
                    $val = null;
                }
                
            } else {
                
                // not an empty-related. get the existing value.
                $val = $this->$key;
                
                // get the sub-value if any
                if ($val instanceof Solar_Struct) {
                    $val = $val->toArray();
                }
            }
            
            // keep the sub-value
            $data[$key] = $val;
        }
        
        // done!
        return $data;
    }
    
    // -----------------------------------------------------------------
    //
    // Persistence: save, insert, update, delete, refresh.
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Saves this record and all related records to the database, inserting or
     * updating as needed.
     * 
     * Hook methods:
     * 
     * 1. `_preSave()` runs before all save operations.
     * 
     * 2. `_preInsert()` and `_preUpdate()` run before the insert or update.
     * 
     * 3. As part of the model insert()/update() logic, `filter()` gets called,
     *    which itself has `_preFilter()` and `_postFilter()` hooks.
     *    
     * 4. `_postInsert()` and `_postUpdate()` run after the insert or update.
     * 
     * 5. `_postSave()` runs after all save operations, but before related
     *    records are saved.
     * 
     * 6. `_preSaveRelated()` runs before saving related records.
     * 
     * 7. Each related record is saved, invoking the save() routine with all
     *    its hooks on each related record.
     * 
     * 8. `_postSaveRelated()` runs after all related records are saved.
     * 
     * @param array $data An associative array of data to merge with existing
     * record data.
     * 
     * @return bool True on success, false on failure.
     * 
     * @todo Automatic connection of related IDs to each other?
     * 
     */
    public function save($data = null)
    {
        $this->_checkDeleted();
        $this->_save_exception = null;
        
        // load data at save-time?
        if ($data) {
            $this->load($data);
            $this->setStatus('dirty');
        }
        
        try {
            $this->_save();
            $this->_saveRelated();
            return true;
        } catch (Solar_Sql_Model_Record_Exception_Invalid $e) {
            $this->_save_exception = $e;
            return false;
        }
    }
    
    /**
     * 
     * Perform a save() within a transaction, with automatic commit and
     * rollback.
     * 
     * @param array $data An associative array of data to merge with existing
     * record data.
     * 
     * @return bool True on success, false on failure.
     * 
     * @todo Make this the default save() behavior? That means renamaing and
     * refactoring the record/collection save() methods.
     * 
     */
    public function saveInTransaction($data = null)
    {
        // convenient reference to the SQL connection
        $sql = $this->_model->sql;
        
        // start the transaction
        $sql->begin();
        
        try {
            
            // attempt the save
            if ($this->save($data)) {
                // entire save was valid, keep it
                $sql->commit();
                return true;
            } else {
                // at least one part of the save was *not* valid.
                // throw it all away.
                $sql->rollback();
                
                // note that we're not invalid, exactly, but that we
                // rolled back.
                $this->_status = 'rollback';
                return false;
            }
            
        } catch (Exception $e) {
            
            // some sort of exception came up **besides** invalid data (which
            // is handled inside save() already).  get its message.
            if ($e->getCode() == 'ERR_QUERY_FAILED') {
                // special treatment for failed queries.
                $info = $e->getInfo();
                $text = $info['pdo_text'] . ". "
                      . "Please call getSaveException() for more information.";
            } else {
                // normal treatment.
                $text = $e->getCode() . ': ' . $e->getMessage();
            }
            
            // roll back and retain the exception
            $sql->rollback();
            $this->_save_exception = $e;
            
            // set as invalid and force the record status afterwards
            $this->setInvalid('*', $text);
            $this->_status = 'rollback';
            
            // done
            return false;
        }
    }
    
    /**
     * 
     * Saves the current record, but only if the record is "dirty".
     * 
     * On saving, invokes the pre-save, pre- and post- insert/update,
     * and post-save hooks.
     * 
     * @return void
     * 
     */
    protected function _save()
    {
        // only save if we're not clean
        if ($this->_status != 'clean') {
        
            // pre-save routine
            $this->_preSave();
            
            // insert or update based on primary key value
            $primary = $this->_model->primary_col;
            if (empty($this->$primary)) {
                $this->_insert();
            } else {
                $this->_update();
            }
            
            // post-save routine
            $this->_postSave();
        }
    }
    
    /**
     * 
     * User-defined pre-save logic.
     * 
     * @return void
     * 
     */
    protected function _preSave()
    {
    }
    
    /**
     * 
     * User-defined post-save logic.
     * 
     * @return void
     * 
     */
    protected function _postSave()
    {
    }
    
    /**
     * 
     * Inserts the current record into the database, making calls to pre- and
     * post-insert logic.
     * 
     * @return void
     * 
     */
    protected function _insert()
    {
        try {
            $this->_preInsert();
            $this->_model->insert($this);
            $this->_postInsert();
        } catch (Solar_Sql_Adapter_Exception_QueryFailed $e) {
            // failed at at the database for some reason
            $this->setInvalid('*', $e->getInfo('pdo_text'));
            throw $e;
        }
    }
    
    /**
     * 
     * User-defined pre-insert logic.
     * 
     * @return void
     * 
     */
    protected function _preInsert()
    {
    }
    
    /**
     * 
     * User-defined post-insert logic.
     * 
     * @return void
     * 
     */
    protected function _postInsert()
    {
    }
    
    /**
     * 
     * Updates the current record at the database, making calls to pre- and
     * post-update logic.
     * 
     * @return void
     * 
     */
    protected function _update()
    {
        try {
            $this->_preUpdate();
            $where = null;
            $this->_model->update($this, $where);
            $this->_postUpdate();
        } catch (Solar_Sql_Adapter_Exception_QueryFailed $e) {
            // failed at at the database for some reason
            $this->setInvalid('*', $e->getInfo('pdo_text'));
            throw $e;
        }
    }
    
    /**
     * 
     * User-defined pre-update logic.
     * 
     * @return void
     * 
     */
    protected function _preUpdate()
    {
    }
    
    /**
     * 
     * User-defined post-update logic.
     * 
     * @return void
     * 
     */
    protected function _postUpdate()
    {
    }
    
    /**
     * 
     * Saves each related record.
     * 
     * Invokes the pre- and post- saveRelated methods.
     * 
     * @return void
     * 
     * @todo Keep track of invalid saves on related records and collections?
     * 
     */
    protected function _saveRelated()
    {
        $this->_preSaveRelated();
        
        // now save each related, but only if instantiated
        foreach ($this->_model->related as $name => $info) {
        
            // use $this->_data[$name] **instead of** $this->$name,
            // to avoid lazy-loading the related record (which in turn
            // causes infinite recursion)
            if (empty($this->_data[$name])) {
                continue;
            }
        
            if ($this->_data[$name] instanceof Solar_Sql_Model_Record ||
                $this->_data[$name] instanceof Solar_Sql_Model_Collection) {
                // is a record or collection, save them
                $this->_data[$name]->save();
            }
        }
        
        $this->_postSaveRelated();
    }
    
    /**
     * 
     * User-defined logic to execute before saving related records.
     * 
     * @return void
     * 
     */
    protected function _preSaveRelated()
    {
    }
    
    /**
     * 
     * User-defined logic to execute after saving related records.
     * 
     * @return void
     * 
     */
    protected function _postSaveRelated()
    {
    }
    
    /**
     * 
     * Deletes this record from the database.
     * 
     * @return void
     * 
     */
    public function delete()
    {
        $this->_checkDeleted();
        $this->_preDelete();
        $this->_model->delete($this);
        $this->_postDelete();
    }
    
    /**
     * 
     * User-defined pre-delete logic.
     * 
     * @return void
     * 
     */
    protected function _preDelete()
    {
    }
    
    /**
     * 
     * User-defined post-delete logic.
     * 
     * @return void
     * 
     */
    protected function _postDelete()
    {
    }
    
    /**
     * 
     * Refreshes data for this record from the database.
     * 
     * Note that this does not refresh any related or calculated values.
     * 
     * @param string $status Set the record status to this value when done
     * with the refresh.
     * 
     * @return void
     * 
     */
    public function refresh($status = null)
    {
        $id = $this->getPrimaryVal();
        if (! $id) {
            throw $this->_exception("ERR_CANNOT_REFRESH_BLANK_ID");
        }
        
        $result = $this->_model->fetch($id);
        foreach ($this->_model->table_cols as $col => $info) {
            $this->$col = $result->$col;
        }
        
        if ($status) {
            $this->setStatus($status);
        } else {
            $this->setStatus('clean');
        }
    }
    
    /**
     * 
     * Increments the value of a column **immediately at the database** and
     * retains the incremented value in the record.
     * 
     * Incrementing by a negative value effectively decrements the value.
     * 
     * N.b.: This results in 2 SQL calls: one to update the value at the
     * database, then one to select the new value from the database.
     * 
     * N.b.: You may have trouble incrementing from a NULL starting point.
     * You should define columns to be incremented with a  "DEFAULT '0'" so
     * they never are null (although strictly speaking you do *not* need to 
     * define them as NOT NULL).
     * 
     * N.b.: This **will not** clear the cache for the model, since it uses
     * direct SQL to effefct the increment.  Thus, you will need to clear the
     * cache manually if you want to the incremented values to show up from
     * the cache.
     * 
     * @param string $col The column to increment.
     * 
     * @param int|float $amt The amount to increment by (default 1).
     * 
     * @return int|float The value after incrementing.  Note that other 
     * processes may have incremented the column as well, so this may not
     * correspond directly with adding the amount to the current value in the
     * record.
     * 
     */
    public function increment($col, $amt = 1)
    {
        // make sure the column exists
        if (! array_key_exists($col, $this->_model->table_cols)) {
            throw $this->_exception('ERR_NO_SUCH_COLUMN', array(
                'name' => $col,
            ));
        }
        
        // the table and primary-key col name
        $table = $this->_model->table_name;
        $key = $this->getPrimaryCol();
        $sql = $this->_model->sql;
        
        // we need to have a primary value
        $val = $this->getPrimaryVal();
        if (! $val) {
            throw $this->_exception('ERR_NO_PRIMARY_VAL', array(
                'primary_col' => $col
            ));
        };
        
        // change column by $amt
        $cmd = "UPDATE $table SET $col = $col + :amt WHERE $key = :$key";
        $sql->query($cmd, array(
            $key  => $val,
            'amt' => $amt,
        ));
        
        // get the most-current value
        $cmd = "SELECT $col FROM $table WHERE $key = :$key";
        $new = $sql->fetchValue($cmd, array($key => $val));
        
        // set the data directly, **without** passing through
        // __set(), so as not to dirty the record.
        $this->_data[$col] = $new;
        
        // fake the initial value so that isChanged() won't trigger
        $this->_initial[$col] = $new;
        
        // done!
        return $new;
    }
    
    // -----------------------------------------------------------------
    // 
    // Filtering and data invalidation.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Filter the data.
     * 
     * @return void
     * 
     */
    public function filter()
    {
        // pre-filter hook
        $this->_preFilter();
        
        // create a filter object based on the model's filter class
        $filter = Solar::factory($this->_model->filter_class);
        
        // set filters as specified by the model
        foreach ($this->_model->filters as $key => $list) {
            $filter->addChainFilters($key, $list);
        }
        
        // set filters added to this record
        foreach ($this->_filters as $key => $list) {
            $filter->addChainFilters($key, $list);
        }
        
        // set which elements are required by the table itself
        // 
        // @todo Turn off created/updated, as they will get populated?  Also
        // turn off "inherit" values?  Or auto-set them in _preInsert() and
        // _preUpdate() ?
        foreach ($this->_model->table_cols as $key => $info) {
            if ($info['autoinc']) {
                // autoinc are not required
                $flag = false;
            } elseif (in_array($key, $this->_model->sequence_cols)) {
                // auto-sequence are not required
                $flag = false;
            } else {
                // go with the col info
                $flag = $info['require'];
            }
            
            // set the requirement flag
            $filter->setChainRequire($key, $flag);
        }
        
        // tell the filter to use the model for locale strings
        $filter->setChainLocaleObject($this->_model);
        
        // apply filters
        $valid = $filter->applyChain($this);
        
        // retain invalids
        $invalid = $filter->getChainInvalid();
        
        // reclaim memory
        $filter->free();
        unset($filter);
        
        // was it valid?
        if (! $valid) {
            
            // use custom validation messages per column when available
            foreach ($invalid as $key => $old) {
                $locale_key = "INVALID_" . strtoupper($key);
                $new = $this->_model->locale($locale_key);
                if ($new != $locale_key) {
                    $invalid[$key] = $new;
                }
            }
            
            $this->setStatus('invalid');
            $this->_invalid = $invalid;
            throw $this->_exception('ERR_INVALID', array($this->_invalid));
        }
        
        // post-logic, and done
        $this->_postFilter();
    }
    
    /**
     * 
     * User-defined logic executed before filters are applied to the record
     * data.
     * 
     * @return void
     * 
     */
    protected function _preFilter()
    {
    }
    
    /**
     * 
     * User-defined logic executed after filters are applied to the record
     * data.
     * 
     * @return void
     * 
     */
    protected function _postFilter()
    {
    }
    
    /**
     * 
     * Forces one property to be "invalid" and sets a validation failure message
     * for it.
     * 
     * @param string $key The property name.
     * 
     * @param string $message The validation failure message.
     * 
     * @return void
     * 
     */
    public function setInvalid($key, $message)
    {
        $this->setStatus('invalid');
        $this->_invalid[$key][] = $message;
    }
    
    /**
     * 
     * Forces multiple properties to be "invalid" and sets validation failure
     * message for them.
     * 
     * @param array $list An associative array where the key is the property
     * name, and the value is a string (or array of strings) of invalidation
     * messages.
     * 
     * @return void
     * 
     */
    public function setInvalids($list)
    {
        $this->setStatus('invalid');
        foreach ($list as $key => $messages) {
            foreach ((array) $messages as $message) {
                $this->_invalid[$key][] = $message;
            }
        }
    }
    
    /**
     * 
     * Returns the validation failure message for one or more properties.
     * 
     * @param string $key Return the message for this property; if empty,
     * returns messages for all invalid properties.
     * 
     * @return string|array
     * 
     */
    public function getInvalid($key = null)
    {
        if ($key) {
            return $this->_invalid[$key];
        } else {
            return $this->_invalid;
        }
    }
    
    // -----------------------------------------------------------------
    //
    // Record status
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Forces the status of this record.
     * 
     * @param string $status The new status: 'clean', 'deleted', 'dirty',
     * 'inserted', 'invalid', 'new', or 'updated'.
     * 
     * @return void
     * 
     */
    public function setStatus($status)
    {
        $this->_status = $status;
        if ($this->_status != 'dirty' && $this->_status != 'invalid') {
            
            // reset the initial data for table columns
            foreach (array_keys($this->_model->table_cols) as $col) {
                $this->_initial[$col] = $this->$col;
            }
            
            // can't be invalid, either
            $this->_invalid = array();
        }
    }
    
    /**
     * 
     * Returns the status of this record.
     * 
     * @return string $status Current status: 'clean', 'deleted', 'dirty',
     * 'inserted', 'invalid', 'new' or 'updated'.
     * 
     */
    public function getStatus()
    {
        return $this->_status;
    }
    
    /**
     * 
     * Tells if a particular table-column has changed.
     * 
     * This is slightly complicated.  Changes to or from a null are reported
     * as "changed".  If both the initial value and new value are numeric
     * (that is, whether they are string/float/int), they are compared using
     * normal inequality (!=).  Otherwise, the initial value and new value
     * are compared using strict inequality (!==).
     * 
     * This complexity results from converting string and numeric values in
     * and out of the database.  Coming from the database, a string numeric
     * '1' might be filtered to an integer 1 at some point, making it look
     * like the value was changed when in practice it has not.
     * 
     * Similarly, we need to make allowances for nulls, because a non-numeric
     * null is loosely equal to zero or an empty string.
     * 
     * @param string $col The table-column name.
     * 
     * @return void|bool Returns null if the table-column name does not exist,
     * boolean true if the data is changed, boolean false if not changed.
     * 
     * @todo How to handle changes to array values?
     * 
     */
    public function isChanged($col)
    {
        // col needs to exist in the initial array
        if (! array_key_exists($col, $this->_initial)) {
            return null;
        }
        
        // track changes on structs
        $dirty = $this->_data[$col] instanceof Solar_Struct
              && $this->_data[$col]->getStatus() == self::STATUS_DIRTY;
        if ($dirty) {
            return true;
        }
        
        // track changes to or from null
        $from_null = $this->_initial[$col] === null &&
                     $this->$col !== null;
        
        $to_null   = $this->_initial[$col] !== null &&
                     $this->$col === null;
        
        if ($from_null || $to_null) {
            return true;
        }
        
        // track numeric changes
        $both_numeric = is_numeric($this->_initial[$col]) &&
                        is_numeric($this->$col);
        if ($both_numeric) {
            // use normal inequality
            return $this->_initial[$col] != (string) $this->$col;
        }
        
        // use strict inequality
        return $this->_initial[$col] !== $this->$col;
    }
    
    /**
     * 
     * Gets a list of all changed table columns.
     * 
     * @return array
     * 
     */
    public function getChanged()
    {
        $list = array();
        foreach ($this->_initial as $col => $val) {
            if ($this->isChanged($col)) {
                $list[] = $col;
            }
        }
        return $list;
    }
    
    /**
     * 
     * Returns the exception (if any) generated by the most-recent call to the
     * save() method.
     * 
     * @return Exception
     * 
     * @see save()
     * 
     */
    public function getSaveException()
    {
        return $this->_save_exception;
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
    
    // -----------------------------------------------------------------
    // 
    // Automated forms.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns a Solar_Form object pre-populated with column properties,
     * values, and filters ready for processing (all based on the model for
     * this record).
     * 
     * @param array $cols An array of column property names to include in
     * the form.  If empty, uses all fetch columns and all calculate columns.
     * 
     * @return Solar_Form
     * 
     */
    public function form($cols = null)
    {
        // use all columns?
        if (empty($cols)) {
            $cols = array_merge(
                $this->_model->fetch_cols,
                array_keys($this->_model->calculate_cols)
            );
        }
        
        // put into this array in the form
        $array_name = $this->_model->model_name;
        
        // build the form
        $form = Solar::factory('Solar_Form');
        $form->load('Solar_Form_Load_Model', $this->_model, $cols, $array_name);
        $form->setValues($this->toArray(), $array_name);
        $form->addInvalids($this->_invalid, $array_name);
        
        // set the form status
        switch ($this->_status) {
        case 'invalid':
        case 'rollback':
            $form->setStatus(false);
            break;
        case 'inserted':
        case 'updated':
            $form->setStatus(true);
            break;
        }
        
        // if a column is invalid, and an element for it does not exist in the
        // form, add the invalidation message as feedback on the form as a 
        // whole.  this helps you track down errors on columns that prevented
        // a save but were not part of the form, like IDs.
        foreach ($this->_invalid as $key => $val) {
            // the element name in the form
            $elem_name = $array_name . "[$key]";
            // is the column invalid, but not in the form?
            if ($this->_invalid[$key] && empty($form->elements[$elem_name])) {
                // add the invalidation messages as feedback
                foreach ((array) $this->_invalid[$key] as $text) {
                    $form->feedback[] = "$elem_name: $text";
                }
            }
        }
        
        return $form;
    }
    
    /**
     * 
     * Adds a column filter to this record instance.
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
    public function addFilter($col, $method)
    {
        $args = func_get_args();
        array_shift($args); // the first param is $col
        $this->_filters[$col][] = $args;
    }
}