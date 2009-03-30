<?php
/**
 * 
 * Represents a collection of Solar_Sql_Model_Record objects.
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
 * @todo Implement an internal unit-of-work status registry so that we can 
 * handle mass insert/delete without hitting the database unnecessarily.
 * 
 */
class Solar_Sql_Model_Collection extends Solar_Struct
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
     * Data for related objects.
     * 
     * @var array
     * 
     */
    protected $_data_related = array();
    
    /**
     * 
     * Which relteds have ahd data eager-loaded for them?
     * 
     * @var array
     * 
     */
    protected $_load_related = array();
    
    /**
     * 
     * The pager information for this collection.
     * 
     * Keys are ...
     * 
     * `count`
     * : (int) The total number of rows in the database.
     * 
     * `pages`
     * : (int) The total number of pages in the database (count / paging).
     * 
     * `paging`
     * : (int) The number of rows per page for the collection.
     * 
     * `page`
     * : (int) The page-number of the collection.
     * 
     * `begin`
     * : (int) The row-number at which the collection begins.
     * 
     * `end`
     * : (int) The row-number at which the collection ends.
     * 
     * @var array
     * 
     * @see setPagerInfo()
     * 
     * @see getPagerInfo()
     * 
     */
    protected $_pager_info = array(
        'count'  => null,
        'pages'  => null,
        'paging' => null,
        'page'   => null,
        'begin'  => null,
        'end'    => null,
    );
    
    /**
     * 
     * Returns a record from the collection based on its key value.  Converts
     * the stored data array to a record of the correct class on-the-fly.
     * 
     * @param int|string $key The sequential or associative key value for the
     * record.
     * 
     * @return Solar_Sql_Model_Record
     * 
     */
    public function __get($key)
    {
        if (! $this->__isset($key)) {
            // create a new blank record for the missing key
            $this->_data[$key] = $this->_model->fetchNew();
        }
        
        // convert array to record object.
        // honors single-table inheritance.
        if (is_array($this->_data[$key])) {
            
            // convert the data array to an object.
            // get the main data to load to the record.
            $load = $this->_data[$key];
            
            // set placeholders for all related data. we do this so the
            // record actually has a key for the related data, even if it's
            // empty, to do eager loading on empty (but fetched) records.
            foreach ($this->_load_related as $name) {
                $load[$name] = null;
            }
            
            // add related data to load data
            $primary = $load[$this->_model->primary_col];
            foreach ($this->_data_related as $name => $data) {
                // load any related data that actually exists
                if (! empty($data[$primary])) {
                    // add the data 
                    $load[$name] = $data[$primary];
                    // save some memory
                    unset($data[$primary]);
                }
            }
            
            // done
            $this->_data[$key] = $this->_model->newRecord($load);
        }
        
        // return the record
        return $this->_data[$key];
    }
    
    /**
     * 
     * Return a list of the unique primary keys contained in this collection.
     * Will not cause records to be created for as of yet unaccessed rows.
     *
     * @param string $primary primary column to collect.
     *
     * @return array
     * 
     */
    public function uniqueKeys($primary = null)
    {
        $keys = array();
        if (empty($primary)) {
            $primary = $this->_model->primary_col;
        }
        foreach ($this->_data as $row) {
            $keys[] = $row[$primary];
        }
        $keys = array_unique($keys);
        return $keys;
    }
    
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
     * Injects pager information for the collection.
     * 
     * Generally used only by the model fetchAll() and fetchAssoc() methods.
     * 
     * @param array $info An array of information with keys for `count`,
     * `pages`, `paging`, `page`, `begin`, and `end`.
     * 
     * @return void
     * 
     * @see $_pager_info
     * 
     */
    public function setPagerInfo($info)
    {
        $base = array(
            'count'  => null,
            'pages'  => null,
            'paging' => null,
            'page'   => null,
            'begin'  => null,
            'end'    => null,
        );
        
        $this->_pager_info = array_merge($base, $info);
    }
    
    /**
     * 
     * Gets the injected pager information for the collection.
     * 
     * @return array An array of information with keys for `count`,
     * `pages`, `paging`, `page`, `begin`, and `end`.
     * 
     * @see $_pager_info
     * 
     */
    public function getPagerInfo()
    {
        return $this->_pager_info;
    }
    
    /**
     * 
     * Loads the struct with data from an array or another struct.
     * 
     * This is a complete override from the parent load() method.
     * 
     * We need this so that fetchAssoc() loading works properly; otherwise, 
     * integer keys get renumbered, which disconnects the association.
     * 
     * @param array|Solar_Struct $spec The data to load into the object.
     * 
     * @return void
     * 
     */
    public function load($spec)
    {
        // force to array
        if ($spec instanceof Solar_Struct) {
            // we can do this because $spec is of the same class
            $this->_data = $spec->_data;
        } elseif (is_array($spec)) {
            $this->_data = $spec;
        } else {
            $this->_data = array();
        }
    }
    
    /**
     * 
     * Loads *related* data for the collection.
     * 
     * Applies particularly to has-many eager loading.
     * 
     * This keeps hold of the related data, which will be loaded into the
     * record by offsetGet().
     * 
     * @param string $name The relationship name.
     * 
     * @param array $data The related data.
     * 
     * @return void
     * 
     * @see offsetGet()
     * 
     */
    public function loadRelated($name, $data)
    {
        // track that related data was loaded, *even if it's empty for a
        // particular record in the collection*
        $this->_load_related[] = $name;
        
        // get the related object
        $related = $this->_model->getRelated($name);
        
        // if a to-one, no need for a loop
        if ($related->isOne()) {
            $id = array_shift($data);
            $this->_data_related[$name][$id] = $data;
            return;
        }
        
        // many records, needs a loop.  however, we only need to keep
        // related keys if the related fetch was an 'assoc'.
        if ($related->fetch == 'assoc') {
            // keep assoc keys
            foreach ($data as $key => $val) {
                $id = array_shift($val);
                $this->_data_related[$name][$id][$key] = $val;
            }
        } else {
            // renumber related keys from zero, so that it "looks right"
            // when you inspect the array
            foreach ($data as $val) {
                $id = array_shift($val);
                $this->_data_related[$name][$id][] = $val;
            }
        }
    }
    
    /**
     * 
     * Returns the data for each record in this collection as an array.
     * 
     * @return array
     * 
     */
    public function toArray()
    {
        $data = array();
        $clone = clone($this);
        foreach ($clone as $key => $record) {
            $data[$key] = $record->toArray();
        }
        return $data;
    }
    
    /**
     * 
     * Saves all the records from this collection to the database one-by-one,
     * inserting or updating as needed.
     * 
     * @return void
     * 
     */
    public function save()
    {
        // pre-logic
        $this->_preSave();
        
        // save, instantiating each record
        foreach ($this as $record) {
            $status = $record->getStatus();
            if ($status != 'deleted') {
                $record->save();
            }
        }
        
        // post-logic
        $this->_postSave();
    }
    
    /**
     * 
     * User-defined pre-save logic for the collection.
     * 
     * @return void
     * 
     */
    protected function _preSave()
    {
    }
    
    /**
     * 
     * User-defined post-save logic for the collection.
     * 
     * @return void
     * 
     */
    protected function _postSave()
    {
    }
    
    /**
     * 
     * Deletes each record in the collection one-by-one.
     * 
     * @return void
     * 
     */
    public function delete()
    {
        $this->_preDelete();
        foreach ($this as $record) {
            $status = $record->getStatus();
            if ($status != 'deleted') {
                $record->delete();
            }
        }
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
    
    // -----------------------------------------------------------------
    //
    // ArrayAccess
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * ArrayAccess: set a key value; appends to the array when using []
     * notation.
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
    public function offsetSet($key, $val)
    {
        if ($key === null) {
            $key = $this->count();
            if (! $key) {
                $key = 0;
            }
        }
        
        return $this->__set($key, $val);
    }
}