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
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * Application controller class.
 */
Solar::loadClass('Solar_Controller_Page');

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
class Solar_App_Bookmarks extends Solar_Controller_Page {
    
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
        
        // tags/:tags
        'tag' => array('tags'),
        
        // user/:owner_handle/:tags
        'user' => array('owner_handle', 'tags'),
        
        // edit/:id
        'edit' => array('id'),
        
        // userFeed/:owner_handle/:tags
        'userFeed' => array('owner_handle', 'tags'),
        
        // tagFeed/:tags
        'tagFeed' => array('tags'),
        
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
     * The requested bookmark order (title, tags, created, etc).
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
     * An array of error messages.
     * 
     * @var array
     * 
     */
    public $err;
    
    /**
     * 
     * The current form processing object.
     * 
     * @var object Solar_Form
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
            $order = 'created ASC';
            break;
        
        case 'created_desc':
        case 'ts_desc':
            $order = 'created DESC';
            break;
            
        // title
        case 'subj':
        case 'subj_asc':
        case 'title':
        case 'title_asc':
            $order = 'LOWER(subj) ASC';
            break;
        
        case 'subj_desc':
        case 'title_desc':
            $order = 'LOWER(subj) DESC';
            break;
        
        // tags
        case 'tag':
        case 'tag_asc':
        case 'tags':
        case 'tags_asc':
            $order = 'LOWER(tags) ASC';
            break;
            
        case 'tag_desc':
        case 'tags_desc':
            $order = 'LOWER(tags) DESC';
            break;
        
        // rank
        case 'rank':
        case 'rank_asc':
            $order = 'rank ASC';
            break;
        
        case 'rank_desc':
            $order = 'rank DESC';
            break;
        
        // owner handle (username)
        case 'owner':
        case 'owner_asc':
        case 'user':
        case 'user_asc':
            $order = 'LOWER(owner_handle) ASC';
            break;
        
        case 'owner_desc':
        case 'user_desc':
            $order = 'LOWER(owner_handle) DESC';
            break;
        
        // default
        default:
            $order = 'created DESC';
            break;
        
        }
        
        return $order;
    }
}
?>