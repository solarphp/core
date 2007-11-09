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
    protected $_related = array();
    
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
        // convert array to record object
        // honors single-table inheritance
        if (is_array($this->_data[$key])) {
            
            // convert the data array to an object.
            // get the main data to load to the record.
            $load = $this->_data[$key];
            
            // add related data to load data
            $primary = $load[$this->_model->primary_col];
            foreach ($this->_related as $name => $data) {
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
        $related = $this->_model->getRelated($name);
        if ($related->type == 'has_many') {
            foreach ($data as $item) {
                $id = array_shift($item);
                $this->_related[$name][$id][] = $item;
            }
        } else {
            $id = array_shift($data);
            $this->_related[$name][$id] = $data;
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
    public function _preSave()
    {
    }
    
    /**
     * 
     * User-defined post-save logic for the collection.
     * 
     * @return void
     * 
     */
    public function _postSave()
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
    public function _preDelete()
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