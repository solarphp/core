<?php
class Solar_Model_Tags_Collection extends Solar_Model_Collection {
    
    public function getNames()
    {
        $list = array();
        foreach ($this as $tag) {
            $list[] = $tag->name;
        }
        return $list;
    }
}