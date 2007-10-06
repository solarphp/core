<?php
class Solar_Model_Nodes_Bookmarks_Record extends Solar_Model_Nodes_Record {
    public function form($cols = null)
    {
        // force the columns to be shown in the form
        $cols = array(
            'uri'  => array(
                'attribs' => array(
                    'size' => 48,
                ),
            ),
            'subj' => array(
                'attribs' => array(
                    'size' => 48,
                ),
            ),
            'summ' => array(
                'type'    => 'textarea',
                'attribs' => array(
                    'rows' => 6,
                    'cols'  => 48,
                ),
            ),
            'tags_as_string' => array(
                'type' => 'text',
                'attribs' => array(
                    'size' => 48,
                ),
            ),
            'pos'  => array(
                'attribs' => array(
                    'size' => 3,
                ),
            ),
        );
        
        return parent::form($cols);
    }
}