<?php
/**
 * 
 * A collection of Solar_Model_Tags records.
 * 
 */
class Solar_Model_Tags_Collection extends Solar_Model_Collection {
    
    /**
     * 
     * Gets the names of all tags in this collection as an array.
     * 
     * @return array
     * 
     */
    public function getNames()
    {
        // use a clone so we don't screw up external iterations.
        $clone = clone($this);
        
        // build the list
        $list = array();
        foreach ($clone as $tag) {
            $list[] = $tag->name;
        }
        
        // done!
        return $list;
    }
    
    public function setNames($spec)
    {
        // we need the tags model for various reasons
        $model = $this->getModel();
        
        // accept a string or array for population
        if (is_string($spec)) {
            // convert string to array
            $list = $this->_stringToArray($spec);
        } elseif (is_array($spec)) {
            // take unique values only from the array
            $list = array_unique($spec);
        } elseif (! $spec) {
            $list = array();
        } else {
            // oops
            throw $this->_exception('ERR_UNRECOGNIZED_TYPE', array(
                'type' => gettype($spec),
            ));
        }
        
        // remove tags not in the new list. use a clone so as not to 
        // screw up external iterations.
        $clone = clone($this);
        foreach ($clone as $key => $tag) {
            if (! in_array($tag->name, $list)) {
                // we don't delete from the $clone,
                // we remove from $this collection
                $this->removeOne($key);
            }
        }
        
        // the tags remaining after removals
        $remaining = $this->getNames();
        
        // add remaining tags
        foreach ($list as $name) {
            if (! in_array($name, $remaining)) {
                // not in the current list.
                // does it exist at the db?
                $tag = $model->fetchOneByName($name);
                if ($tag) {
                    // tag already exists at the db
                    $this[] = $tag;
                } else {
                    // not in the db, append new tag
                    // for insert later
                    $this->appendNew(array(
                        'name' => $name,
                    ));
                }
            }
        }
    }
    
    public function getNamesAsString()
    {
        $list = $this->getNames();
        return implode(' ', $list);
    }
    
    protected function _stringToArray($text)
    {
        $list = preg_replace('/[ ]{2,}/', ' ', $text);
        
        if (! $list) {
            $list = array();
        } else {
            $list = explode(' ', $list);
        }
        
        return array_unique($list);
    }
}
