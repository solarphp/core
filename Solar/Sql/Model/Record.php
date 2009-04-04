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
    const STATUS_DELETED    = 'deleted';
    const STATUS_INSERTED   = 'inserted';
    const STATUS_INVALID    = 'invalid';
    const STATUS_NEW        = 'new';
    const STATUS_ROLLBACK   = 'rollback';
    const STATUS_UPDATED    = 'updated';
    
    /**
     * 
     * A list of all accessor methods for all record classes.
     * 
     * @var array
     * 
     */
    static protected $_access_methods_list = array();
    
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
    protected $_status = self::STATUS_CLEAN;
    
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
        $found = array_key_exists($key, $this->_data);
        if (! $found && ! empty($this->_model->related[$key])) {
            // the key was for a relation that has no data yet.
            // lazy-load the data.
            $this->_data[$key] = $this->fetchRelated($key);
        }
        
        // if an accessor method exists, use it
        if (! empty($this->_access_methods[$key]['get'])) {
            // use accessor method
            $method = $this->_access_methods[$key]['get'];
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
        // keep track if this is a "new" record
        $is_new = $this->getStatus() == self::STATUS_NEW;
        
        // if an accessor method exists, use it
        if (! empty($this->_access_methods[$key]['set'])) {
            // use accessor method. will mark the record and its parents
            // as dirty.
            $method = $this->_access_methods[$key]['set'];
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
            $this->_status = self::STATUS_NEW;
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
        // if an accessor method exists, use it
        if (! empty($this->_access_methods[$key]['unset'])) {
            // use accessor method
            $method = $this->_access_methods[$key]['unset'];
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
        // if an accessor method exists, use it
        if (! empty($this->_access_methods[$key]['isset'])) {
            // use accessor method
            $method = $this->_access_methods[$key]['isset'];
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
        
        // Wholesale dump values into our data array bypassing __set
        $this->_data = array_merge($this->_data, $load);

        // reset values that require a set access method
        foreach ($this->_access_methods as $field => $methods) {
            if (isset($methods['set']) && array_key_exists($field, $load)) {
                $this->__set($field, $load[$field]);
            }
        }
        
        // fix relateds, and we're done
        $this->_fixRelatedData();
    }
    
    /**
     * 
     * Sets the access method lists for this instance.
     * 
     * @return void
     * 
     */
    protected function _setAccessMethods()
    {
        $class = get_class($this);
        if (! array_key_exists($class, self::$_access_methods_list)) {
            $this->_loadAccessMethodsList($class);
        }
        $this->_access_methods = self::$_access_methods_list[$class];
    }
    
    /**
     * 
     * Loads the access method list for a given class.
     * 
     * @param string $class The class to load methods for.
     * 
     * @return void
     * 
     * @see $_access_methods_list
     * 
     */
    protected function _loadAccessMethodsList($class)
    {
        $list = array();
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            
            // if not a "__" method, or if a native magic method, skip it
            $skip = strncmp($method, '__', 2) !== 0
                 || $method == '__set'
                 || $method == '__get'
                 || $method == '__isset'
                 || $method == '__unset';
                 
            if ($skip) {
                continue;
            }
            
            // get
            if (strncmp($method, '__get', 5) == 0) {
                $col = strtolower(preg_replace(
                    '/([a-z])([A-Z])/',
                    '$1_$2',
                    substr($method, 5)
                ));
                $list[$col]['get'] = $method;
                continue;
            }
            
            // set
            if (strncmp($method, '__set', 5) == 0) {
                $col = strtolower(preg_replace(
                    '/([a-z])([A-Z])/',
                    '$1_$2',
                    substr($method, 5)
                ));
                $list[$col]['set'] = $method;
                continue;
            }
            
            // isset
            if (strncmp($method, '__isset', 7) == 0) {
                $col = strtolower(preg_replace(
                    '/([a-z])([A-Z])/',
                    '$1_$2',
                    substr($method, 7)
                ));
                $list[$col]['isset'] = $method;
                continue;
            }
            
            // unset
            if (strncmp($method, '__unset', 7) == 0) {
                $col = strtolower(preg_replace(
                    '/([a-z])([A-Z])/',
                    '$1_$2',
                    substr($method, 7)
                ));
                $list[$col]['unset'] = $method;
                continue;
            }
        }
        
        // retain the list of methods
        self::$_access_methods_list[$class] = $list;
    }
    
    // -----------------------------------------------------------------
    //
    // Model
    //
    // -----------------------------------------------------------------
    
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
        
        // snag a full list of available values
        // unloaded related values are not included            
        $keys = array_merge(
            array_keys($this->_data), 
            array_keys($this->_model->calculate_cols)
        );

        foreach ($keys as $key) {
            
            // not an empty-related. get the existing value.
            $val = $this->$key;
            
            // get the sub-value if any
            if ($val instanceof Solar_Struct) {
                $val = $val->toArray();
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
            $this->setStatus(self::STATUS_DIRTY);
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
                $this->_status = self::STATUS_ROLLBACK;
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
            $this->_status = self::STATUS_ROLLBACK;
            
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
        if ($this->_status != self::STATUS_CLEAN) {
        
            // pre-save routine
            $this->_preSave();
            
            // insert or update based on current status
            if ($this->_status == self::STATUS_NEW) {
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
            $this->setStatus(self::STATUS_CLEAN);
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
            
            $this->setStatus(self::STATUS_INVALID);
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
        $this->setStatus(self::STATUS_INVALID);
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
        $this->setStatus(self::STATUS_INVALID);
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
     * Sets the status of this record.
     * 
     * @param string $status The new status.
     * 
     * @return void
     * 
     */
    public function setStatus($status)
    {
        $dirty_new = $status == self::STATUS_DIRTY
                  && $this->_status == self::STATUS_NEW;
                  
        if ($dirty_new) {
            // new records cannot be dirty
            return;
        }
        
        // set the new status
        $this->_status = $status;
        
        // should we reset initial data?
        $reset = $this->_status != self::STATUS_DIRTY
              && $this->_status != self::STATUS_INVALID;
              
        if ($reset) {
            
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
        if ($this->_status == self::STATUS_DELETED) {
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
        case self::STATUS_INVALID:
        case self::STATUS_ROLLBACK:
            $form->setStatus(false);
            break;
        case self::STATUS_INSERTED:
        case self::STATUS_UPDATED:
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

    /**
     * 
     * Fetch related objects
     * This differs from a simple traversal in that parameters
     * can further restrict or transform the results.
     * 
     * @param string $key The property name.
     * 
     * @param array $params An array of SELECT parameters.
     * 
     * @return mixed The property value.
     * 
     */
    public function fetchRelated($name, $params = array())
    {
        $related = $this->_model->getRelated($name);
        return $related->fetch($this, $params);
    }
    
    /**
     * 
     * Initialize the record object.  This is effectively a "first load"
     * method.
     * 
     * @param Solar_Sql_Model $model The origin model object.
     * 
     * @return void
     * 
     */
    public function init(Solar_Sql_Model $model, $spec, $status = null)
    {
        // default status
        if (! $status) {
            $status = self::STATUS_CLEAN;
        }
        
        // inject the model
        $this->_model = $model;
        
        // sets access methods
        $this->_setAccessMethods();
        
        // force spec to array
        if ($spec instanceof Solar_Struct) {
            // we can do this because $spec is of the same class
            $load = $spec->_data;
        } elseif (is_array($spec)) {
            $load = $spec;
        } else {
            $load = array();
        }
        
        // unserialize any serialize_cols in the load
        $this->_model->unserializeCols($load);
        
        // Wholesale dump values into our data array bypassing __set
        $this->_data = $load;
        
        // Record the inital values but only for columns that have physical backing
        $this->_initial = array_intersect_key($load, $model->table_cols);
        
        // reset values that require an access method
        foreach ($this->_access_methods as $col => $methods) {
            if (isset($methods['set']) && array_key_exists($col, $load)) {
                $this->$col = $load[$col];
            }
            if (isset($methods['get']) && array_key_exists($col, $this->_initial)) {
                $this->_initial[$col] = $this->$col;
            }
        }
        
        
        // fix up related data elements
        $this->_fixRelatedData();

        // can't be invalid
        $this->_invalid = array();

        // set status directly, bypassing setStatus() logic
        $this->_status = $status;
        
        // done!
    }
    
    /**
     * 
     * Make sure our related data values are the right value and type.
     * 
     * Make sure our related objects are the right type or will be loaded when
     * necessary
     * 
     * @return void
     * 
     */
    protected function _fixRelatedData()
    {
        foreach ($this->_model->related as $name => $obj) {
            
            // convert related values to correct object type
            $convert = array_key_exists($name, $this->_data)
                    && ! is_object($this->_data[$name]);
            
            if ($convert) {
                $related = $this->_model->getRelated($name);
                $this->_data[$name] = $related->newObject($this->_data[$name]);
            }
        }
    }
    
}