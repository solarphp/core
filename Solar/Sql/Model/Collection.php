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
            
            // done
            $this->_data[$key] = $this->_model->newRecord($load);
        }
        
        // return the record
        return $this->_data[$key];
    }
    
    /**
     * 
     * Returns a list of the unique primary keys contained in this collection.
     * Will not cause records to be created for as of yet unaccessed rows.
     *
     * @param string $primary primary column to collect.
     *
     * @return array
     * 
     */
    public function getPrimaryVals($key = null)
    {
        $list = array();
        if (empty($key)) {
            $key = $this->_model->primary_col;
        }
        foreach ($this->_data as $row) {
            $list[] = $row[$key];
        }
        $list = array_unique($list);
        return $list;
    }
    
    public function getColVals($col)
    {
        $list = array();
        foreach ($this as $record) {
            $list[] = $record->$col;
        }
        return $list;
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
            if (! $record->isDeleted()) {
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
    public function deleteAll()
    {
        $this->_preDeleteAll();
        $list = $this->getPrimaryVals();
        foreach ($list as $key) {
            $this->deleteOne($key);
        }
        $this->_postDeleteAll();
    }
    
    /**
     * 
     * User-defined pre-delete logic.
     * 
     * @return void
     * 
     */
    protected function _preDeleteAll()
    {
    }
    
    /**
     * 
     * User-defined post-delete logic.
     * 
     * @return void
     * 
     */
    protected function _postDeleteAll()
    {
    }
    
    public function appendNew($spec = null)
    {
        // create a new record from the spec and append it
        $record = $this->_model->fetchNew($spec);
        $record->setParent($this);
        $this->_data[] = $record;
        return $record;
    }
    
    public function deleteOne($key)
    {
        if ($this->__isset($key)) {
            $record = $this->__get($key);
            if (! $record->isDeleted()) {
                $record->delete();
            }
            $record->free();
            unset($record);
            unset($this->_data[$key]);
        }
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