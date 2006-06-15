<?php
/**
 * 
 * Social bookmarking application.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_Bookmarks
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Application-specific page controller class.
 */
Solar::loadClass('Solar_App');

/**
 * 
 * Social bookmarking application.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_Bookmarks
 * 
 */
class Solar_App_Bookmarks extends Solar_App {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are:
     * 
     * : \\area_name\\ : (string) The content area for the bookmarks
     * app, default 'Solar_App_Bookmarks'.  Will be created automatically
     * if it does not exist.
     * 
     * : \\content\\ : (dependency) A Solar_Content domain model dependency object.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'area_name'    => 'Solar_App_Bookmarks',
        'content'      => 'content',
    );
    
    /**
     * 
     * The default controller action.
     * 
     * @var string
     * 
     */
    protected $_action_default = 'tag';
    
    /**
     * 
     * Pathinfo mappings for each controller action.
     * 
     * @var array
     * 
     */
    protected $_action_info = array(
        'tag'       => 'tags',
        'user'      => 'owner_handle/tags',
        'edit'      => 'id',
        'quick'     => 'uri/subj',
        'user-feed' => 'owner_handle/tags',
        'tag-feed'  => 'tags',
    );
    
    /**
     * 
     * The total number of pages in the query.
     * 
     * @var int
     * 
     */
    public $pages;
    
    /**
     * 
     * The requested bookmark order (subj, tags, created, etc).
     * 
     * @var string
     * 
     */
    public $order;
    
    /**
     * 
     * The current page number being displayed.
     * 
     * @var int
     * 
     */
    public $page;
    
    /**
     * 
     * Feed information.
     * 
     * @var array
     * 
     */
    public $feed;
    
    /**
     * 
     * The current owner_handle being displayed.
     * 
     * @var string
     * 
     */
    public $owner_handle;
    
    /**
     * 
     * The tags requested for filtering results.
     * 
     * @var string
     * 
     */
    public $tags;
    
    /**
     * 
     * The list of all tags in use by the current owner_handle.
     * 
     * If no owner_handle, the list of all tags for all owners.
     * 
     * @var array
     * 
     */
    public $tags_in_use;
    
    /**
     * 
     * The list of all bookmarks for the current page.
     * 
     * @var array
     * 
     */
    public $list;
    
    /**
     * 
     * The current form processing object.
     * 
     * @var Solar_Form
     * 
     */
    public $formdata;
    
    /**
     * 
     * A link back to the previous page: list results, bookmarked page, etc.
     * 
     * @var string
     * 
     */
    public $backlink;
    
    /**
     * 
     * Local reference to the 'user' object in Solar::registry().
     * 
     * @var Solar_User
     * 
     */
    protected $_user;
    
    /**
     * 
     * A bookmarks node interaction manager.
     * 
     * @var Solar_Content_Bookmarks
     * 
     */
    protected $_bookmarks;
    
    /**
     * 
     * Extended setup.
     * 
     * @return void
     * 
     */
    public function _setup()
    {
        parent::_setup();
        
        // make sure a bookmarks area exists
        $content = Solar::dependency('Solar_Content', $this->_config['content']);
        $name = $this->_config['area_name'];
        $area = $content->areas->fetchByName($name);
        if (empty($area)) {
            // area didn't exist, create it.
            $data = array('name'  => $name);
            $area = $content->areas->insert($data);
        }
        
        // get a user object for privileges
        $this->_user = Solar::registry('user');
        
        // get the bookmarks model using the same content
        // dependency
        $this->_bookmarks = Solar::factory(
            'Solar_Content_Bookmarks',
            array(
                'content' => $this->_config['content'],
                'area_id' => $area['id']
            )
        );
        
        // add the bookmarks stylesheet
        $this->layout_style[] = 'Solar/styles/bookmarks.css';
    }
    
    /**
     * 
     * Returns a safe SQL ORDER value from the 'order' query parameter.
     * 
     * @return string
     * 
     */
    protected function _getOrder()
    {
        $tmp = strtolower($this->_query('order'));
        
        switch ($tmp) {
        
        // created timestamp
        case 'created':
        case 'created_asc':
        case 'ts':
        case 'ts_asc':
            $order = 'nodes.created ASC';
            break;
        
        case 'created_desc':
        case 'ts_desc':
            $order = 'nodes.created DESC';
            break;
            
        // title
        case 'subj':
        case 'subj_asc':
        case 'title':
        case 'title_asc':
            $order = 'LOWER(nodes.subj) ASC';
            break;
        
        case 'subj_desc':
        case 'title_desc':
            $order = 'LOWER(nodes.subj) DESC';
            break;
        
        // tags
        case 'tag':
        case 'tag_asc':
        case 'tags':
        case 'tags_asc':
            $order = 'LOWER(nodes.tags) ASC';
            break;
            
        case 'tag_desc':
        case 'tags_desc':
            $order = 'LOWER(nodes.tags) DESC';
            break;
        
        // pos
        case 'pos':
        case 'pos_asc':
            $order = 'nodes.pos ASC';
            break;
        
        case 'pos_desc':
            $order = 'nodes.pos DESC';
            break;
        
        // owner handle (username)
        case 'owner':
        case 'owner_asc':
        case 'user':
        case 'user_asc':
            $order = 'LOWER(nodes.owner_handle) ASC';
            break;
        
        case 'owner_desc':
        case 'user_desc':
            $order = 'LOWER(nodes.owner_handle) DESC';
            break;
        
        // default
        default:
            $order = 'nodes.created DESC';
            break;
        
        }
        
        return $order;
    }
}
?>