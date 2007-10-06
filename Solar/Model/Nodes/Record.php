<?php
class Solar_Model_Nodes_Record extends Solar_Model_Record {
    
    public function __getTagsAsString()
    {
        // if exactly null, populate for the first time.
        if ($this->_data['tags_as_string'] === null) {
            $text = '';
            foreach ($this->tags as $tag) {
                $text .= "{$tag->name} ";
            }
            $this->_data['tags_as_string'] = rtrim($text);
        }
        
        return $this->_data['tags_as_string'];
    }
    
    public function __setTagsAsString($val)
    {
        $val = preg_replace('/[ ]{2,}/', ' ', $val);
        $this->_data['tags_as_string'] = trim($val);
        $new = explode(' ', $this->_data['tags_as_string']);
        $new = array_unique($new);
        
        // build a new tag collection
        $model = $this->tags->getModel();
        $coll = $model->newCollection();
        foreach ($new as $name) {
            $record = $model->fetchNew();
            $record->name = $name;
            $coll[] = $record;
        }
        
        // reset the tags collection
        $this->tags = $coll;
    }
    
    // work with related values before they get saved.
    // relateds:  areas, nodes, taggings, tags
    public function _postSave()
    {
        // -------------------------------------------------------------
        // 
        // make sure that each tag actually exists; insert a new tag
        // record for each new one.
        // 
        
        // hold on to the tags model
        $tags_model = $this->tags->getModel();
        
        // get the list of tags on the record
        $tag_names = $this->tags->getNames();
        if ($tag_names) {
            // make sure each tag actually exists as a record.  tag names
            // that do not exist will not be in this array.
            $params = array(
                'where' => array(
                    'tags.name IN (?)' => $tag_names,
                ),
            );
            $existing_tags = $tags_model->fetchAll($params);
        } else {
            $existing_tags = $tags_model->newCollection(null);
        }
        
        // loop through the tags
        foreach ($this->tags as $key => $tag) {
            
            // does this tag already exist?
            foreach ($existing_tags as $existing) {
                if ($tag->name == $existing->name) {
                    // replace with the existing tag record.
                    // this lets us "connect" user-entered tag names
                    // with existing records. capture both the tag from
                    // the loop and reset the collection value.
                    $tag = $existing;
                    $this->tags[$key] = $existing;
                    break;
                }
            }
            
            // if status is still 'new' then save it
            if ($tag->getStatus() == 'new') {
                $tag->save();
            }
            
            // look for a tagging on this tag
            $found = false;
            foreach ($this->taggings as $tagging) {
                // must match node ID and tag ID
                $match = $tagging->node_id == $this->id &&
                         $tagging->tag_id  == $tag->id;
                if ($match) {
                    $found = true;
                    break;
                }
            }
            
            // did we find a tagging for the tag?
            if (! $found) {
                // no, create one ...
                $tagging = $this->taggings->getModel()->fetchNew();
                $tagging->node_id = $this->id;
                $tagging->tag_id  = $tag->id;
                
                // ... and add to the collection
                $this->taggings[] = $tagging;
            }
        }
        
        // remove taggings that don't exist any more
        foreach ($this->taggings as $key => $tagging) {
            $found = false;
            foreach ($this->tags as $tag) {
                $match = $tagging->node_id == $this->id &&
                         $tagging->tag_id  == $tag->id;
                if ($match) {
                    $found = true;
                    break;
                }
            }
            
            // does the tagging match a tag?
            if (! $found) {
                // no, remove from the taggings
                $tagging->delete();
            }
        }
    }
}