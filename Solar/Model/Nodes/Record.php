<?php
/**
 * 
 * A single Solar_Model_Nodes record.
 * 
 */
class Solar_Model_Nodes_Record extends Solar_Model_Record {
    
    /**
     * 
     * Magic method to get the 'tags_as_string' property.
     * 
     * @return string
     * 
     */
    public function __getTagsAsString()
    {
        // populate for the first time
        if (empty($this->_data['tags_as_string'])) {
            // $this->tags forces the __get() call to the related object,
            // then only proceeds if there are tags there.
            if ($this->tags) {
                $this->_data['tags_as_string'] = $this->tags->getNamesAsString();
            }
        }
        
        return $this->_data['tags_as_string'];
    }
    
    /**
     * 
     * Magic method to set the 'tags_as_string' property.
     * 
     * Maintains the tags collection on-the-fly.
     * 
     * @param string $val A space-separated list of tags.
     * 
     * @return void
     * 
     */
    public function __setTagsAsString($val)
    {
        if (! $this->tags) {
            $this->tags = $this->newRelated('tags');
        }
        $this->tags->setNames($val);
        $this->_data['tags_as_string'] = $this->tags->getNamesAsString();
    }
    
    /**
     * 
     * Deletes all tag mappings, leaving tags in place.
     * 
     * @return void
     * 
     */
    protected function _postDelete()
    {
        if ($this->taggings) {
            $this->taggings->delete();
        }
    }
}
