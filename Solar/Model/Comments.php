<?php
/**
 * 
 * Comments management class.
 * 
 * A comment is generally "part_of" another node.
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 * @subpackage Solar_Model_Comments
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
 * Comments management class.
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 * @subpackage Solar_Model_Comments
 * 
 */
class Solar_Model_Comments extends Solar_Base {
    
    /**
     * 
     * User-defined configuration options.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'paging'       => 10,
    );
    
    /**
     * 
     * An all-purpose content object.
     * 
     * @var object Solar_Model
     * 
     */
    protected $_content;
    
    /**
     * 
     * The node type for all comments.
     * 
     * @var object Solar_Sql
     * 
     */
    protected $_node_type = 'comment';
    
    /**
     * 
     * A convenient baseline WHERE array when searching for comments.
     * 
     * @var array
     * 
     */
    protected $_where = array(
        'nodes.type = ?'    => 'comment',
    );
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // main construction
        parent::__construct($config);
        
        // create a content object and set its paging
        $this->_content = Solar::factory('Solar_Model');
        $this->paging($this->_config['paging']);
        
        // set up the baseline WHERE clause
        $this->_where = array(
            'nodes.type = ?'    => $this->_node_type,
        );
    }
    
    /**
     * 
     * Sets paging in the content object.
     * 
     * @param int $val The number of rows per page.
     * 
     * @return void
     * 
     */
    public function paging($val)
    {
        $this->_content->paging((int) $val);
    }
    
    /**
     * 
     * Inserts or updates a comment.
     * 
     * @param array $data The bookmark data.
     * 
     * @return array The data as inserted or updated.
     * 
     */
    public function save($data)
    {
        if (empty($data['id'])) {
            return $this->insert($data);
        } else {
            return $this->update($data['id'], $data);
        }
    }
    
    /**
     * 
     * Insert a new comment node.
     * 
     * @param array $data The comment data.
     * 
     * @return array The inserted data.
     * 
     */
    public function insert($data)
    {
        return $this->_content->insertNode($data);
    }
    
    /**
     * 
     * Update a comment node.
     * 
     * @param array $data The comment data.
     * 
     * @return array The updated data.
     * 
     */
    public function update($node_id, $data)
    {
        return $this->_content->updateNode(
            $node_id,
            $data
        );
    }
    
    /**
     * 
     * Fetch the default comment node data.
     * 
     * @return array Default data for a new comment node.
     * 
     */
    public function fetchDefault()
    {
        // a default generic node
        $data = $this->_content->defaultNode();
        $data['type'] = $this->_node_type;
        return $data;
    }
    
    /**
     * 
     * Fetch one comment (by node ID) from the content store.
     * 
     * @param int $id The comment node ID.
     * 
     * @return array The comment data.
     * 
     */
    public function fetchItem($id)
    {
        $where = $this->_where;
        $where['nodes.id = ?'] = (int) $id;
        $data = $this->_content->fetchNode($where);
        return $data;
    }
    
    /**
     * 
     * Fetch a list of comments from the content store.
     * 
     * You can specify that comments are "part_of" another node, and
     * any tags on comments.
     * 
     * @param int $part_of Fetch comments that are "part of" this node
     * ID; if blank, fetches all comments on all nodes.
     * 
     * @param string|array $tags Fetch comments with all these
     * tags; if empty, fetches for all tags.
     * 
     * @param string|array $order Order in this fashion; if empty,
     * orders by creation-timestamp ascending (oldest first).
     * 
     * @param int $page Which page-number of results to fetch.
     * 
     * @return array The list of comments.
     * 
     */
    public function fetchList($part_of = null, $tags = null, $order = null,
        $page = null)
    {
        $where = $this->_where;
        
        if ($part_of) {
            $where['nodes.part_of = ?'] = (int) $part_of;
        }
        
        if (empty($order)) {
            $order = 'nodes.created ASC';
        }
        
        return $this->_content->fetchNodeList($tags, $where, $order, $page);
    }
    
    /**
     * 
     * Fetch a total count and pages of comments in the content store.
     * 
     * You can specify the "part_of" node ID and/or a list of
     * tags to limit the count.
     * 
     * @param int $part_of Count comments that are "part of" this node
     * ID; if blank, counts all comments on all nodes.
     * 
     * @param string|array $tags Count comments with all these
     * tags; if empty, counts for all tags.
     * 
     * @return array A array with keys 'count' (total number of 
     * comments) and 'pages' (number of pages).
     * 
     */
    public function countPages($part_of = null, $tags = null)
    {
        $where = $this->_where;
        if ($part_of) {
            $where['nodes.part_of = ?'] = $part_of;
        }
        
        return $this->_content->fetchNodeCount($tags, $where);
    }
    
    /**
     * 
     * Deletes one comment by node ID.
     * 
     * @param int $id The node ID to delete.
     * 
     * @return mixed
     * 
     */
    public function delete($id)
    {
        $where = $this->_where;
        $where['id = ?'] = (int) $id;
        return $this->_content->deleteNodes($where);
    }
    
    /**
     * 
     * Generates a short form for a single comment (email, body).
     * 
     * @param int|array $data An existing node ID, or an array of data to
     * pre-populate into the form.  The array should have a key
     * 'comments' with a sub-array using keys for 'uri', 'subj', 'summ',
     * 'tags', and 'rank'.  If empty, default values are pre-populated
     * into the form.
     * 
     * @return object A Solar_Form object.
     * 
     */
    public function shortForm($data = null)
    {
        // which node columns do we want?
        $cols = array('id', 'email', 'body');
        
        // extra info for the form elements
        $info['email']['attribs']['size'] = 48;
        $info['body']['attribs']['rows']  = 16;
        $info['body']['attribs']['cols']  = 48;
        
        // get the form with a set of 'comments' elements
        $form = $this->_content->form(
            get_class($this),
            $cols,
            $info,
            'comments'
        );
        
        // what data should we populate into the form?
        if (empty($data)) {
            // defaults
            $data = array(
                'comments' => $this->fetchDefault()
            );
        } elseif (is_numeric($data)) {
            // $data is a node ID, look it up for values
            $data = array(
                'comments' => $this->fetchItem((int) $data)
            );
        }
        
        // populate basic data into the form and return
        $form->populate($data);
        return $form;
    }
    
    /**
     * 
     * Generates a long form for a single comment (name, email, uri, body).
     * 
     * @param int|array $data An existing node ID, or an array of data to
     * pre-populate into the form.  The array should have a key
     * 'comments' with a sub-array using keys for 'uri', 'subj', 'summ',
     * 'tags', and 'rank'.  If empty, default values are pre-populated
     * into the form.
     * 
     * @return object A Solar_Form object.
     * 
     */
    public function longForm($data = null)
    {
        // name, mail, website, comment.
        // question is, which should be the human name: subj or summ?
        // ...
        // summ.  there's very likely to be a subject line, but a 
        // comment doesn't need a summary.
        // which node columns do we want?
        $cols = array('id', 'summ', 'email', 'uri', 'body');
        
        // extra info for the form elements
        $info['summ']['attribs']['size']  = 48;
        $info['email']['attribs']['size'] = 48;
        $info['uri']['attribs']['size']   = 48;
        $info['body']['attribs']['rows']  = 16;
        $info['body']['attribs']['cols']  = 48;
        
        // get the form with a set of 'comments' elements
        $form = $this->_content->form(
            get_class($this),
            $cols,
            $info,
            'comments'
        );
        
        // what data should we populate into the form?
        if (empty($data)) {
            // defaults
            $data = array(
                'comments' => $this->fetchDefault()
            );
        } elseif (is_numeric($data)) {
            // $data is a node ID, look it up for values
            $data = array(
                'comments' => $this->fetchItem((int) $data)
            );
        }
        
        // populate basic data into the form and return
        $form->populate($data);
        return $form;
    }
}
?>