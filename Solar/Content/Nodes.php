<?php
/**
 * 
 * Nodes within an area, equivalent to containers for related content parts.
 * 
 * @category Solar
 * 
 * @package Solar_Content
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Nodes within an area, equivalent to containers for related content parts.
 * 
 * @category Solar
 * 
 * @package Solar_Content
 * 
 */
class Solar_Content_Nodes extends Solar_Sql_Table {
    
    /**
     * 
     * Schema setup.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        // the table name
        $this->_name = 'nodes';
        
        // default order
        $this->order = array(
            'rank ASC',
            'LOWER(name) ASC'
        );
        
        
        // -------------------------------------------------------------
        // 
        // COLUMNS
        // 
        
        // the area in which this node belongs
        $this->_col['area_id'] = array(
            'type'    => 'int',
            'require' => true,
            'valid'   => 'word',
        );
        
        // the node name (equivalent to a wiki-word)
        $this->_col['name'] = array(
            'type'    => 'varchar',
            'size'    => 127,
            'valid'   => 'word',
        );
        
        // is this node part of another node?
        $this->_col['part_of'] = array(
            'type'    => 'int',
            'require' => true,
            'default' => 0,
        );
        
        // username of the node owner
        $this->_col['owner_handle'] = array(
            'type'    => 'varchar',
            'size'    => 255,
            'default' => Solar::shared('user')->auth->username,
        );
        
        // username of the most-recent editor
        $this->_col['editor_handle'] = array(
            'type'    => 'varchar',
            'size'    => 255,
            'default' => Solar::shared('user')->auth->username,
        );
        
        // ip address of the most-recent editor
        $this->_col['editor_ipaddr'] = array(
            'type'    => 'char',
            'size'    => 15,
            'default' => Solar::server('REMOTE_ADDR'),
            'valid'   => 'ipv4',
        );
        
        // the node type (bookmark, wiki, blog, comment, trackback, etc)
        $this->_col['type'] = array(
            'type'    => 'varchar',
            'size'    => 32,
        );
        
        // the locale for this part
        $this->_col['locale'] = array(
            'type'    => 'char',
            'size'    => 5,
            'default' => 'en_US',
            'valid'   => 'locale',
        );
        
        // tags on this node (space-separated words)
        $this->_col['tags'] = array(
            'type'    => 'varchar',
            'size'    => 255,
            'valid'   => 'sepWords'
        );
        
        // arbitrary list-order, sequence, or ranking
        $this->_col['rank'] = array(
            'type'    => 'int',
            'default' => 0,
        );
        
        // arbitrary user-assigned rating, score, level, or value
        $this->_col['rating'] = array(
            'type'    => 'int',
            'default' => 0,
        );
        
        // email related to this part
        $this->_col['email'] = array(
            'type'    => 'varchar',
            'size'    => 255,
            'valid'   => 'email',
        );
        
        // uri related to this part
        $this->_col['uri'] = array(
            'type'    => 'varchar',
            'size'    => 255,
            'valid'   => 'uri',
        );
        
        // the node "subject" or title
        $this->_col['subj'] = array(
            'type'    => 'varchar',
            'size'    => 255,
        );
        
        // summary description of the node
        $this->_col['summ'] = array(
            'type'    => 'varchar',
            'size'    => 255,
        );
        
        // mime type of the body
        $this->_col['mime'] = array(
            'type'    => 'varchar',
            'size'    => 64,
            'default' => 'text/plain',
            'valid'   => 'mimeType',
        );
        
        // the actual node content
        $this->_col['body'] = array(
            'type'    => 'clob',
        );
        
        // serialized array of preferences for this node
        $this->_col['prefs'] = array(
            'type'    => 'clob',
        );
        
        // -------------------------------------------------------------
        // 
        // KEYS AND INDEXES
        // 
        
        $this->_idx = array(
            // composite unique index to ensure unique node names within
            // an area_id
            'unique_in_area' => array(
                'type' => 'unique',
                'cols' => array('area_id', 'name'),
            ),
            // other indexes
            'area_id'      => 'normal',
            'name'         => 'normal',
            'part_of'      => 'normal',
            'owner_handle' => 'normal',
            'type'         => 'normal',
            'locale'       => 'normal',
            'tags'         => 'normal',
            'rank'         => 'normal',
            'rating'       => 'normal',
            'uri'          => 'normal',
            'email'        => 'normal',
        );
    }
    
    public function insert($data)
    {
        // although the Table object itself would increment the ID for
        // us automatically, we do so manually here, because we may need
        // it for a name (i.e., if the name is blank or not set).
        if (empty($data['id'])) {
            $data['id'] = $this->increment('id');
        }
        
        // make sure we have a unique name for the node (specifically,
        // a unique name in the area_id for this node).
        if (empty($data['name']) || trim($data['name']) == '') {
            $data['name'] = $data['id'];
        }
        
        return parent::insert($data);
    }
}
?>