<?php
/**
 * 
 * Anti-social bookmarking application.
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
class Solar_App_Bookmarks extends Solar_App_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string area_name The content area for the bookmarks app, default "default".
     *   Will be created automatically if it does not exist.
     * 
     * @var array
     * 
     */
    protected $_Solar_App_Bookmarks = array(
        'area_name'    => 'default',
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
     * These actions support these non-default formats.
     * 
     * @var array
     * 
     */
    protected $_action_format = array(
        'user' => 'rss',
        'tag'  => 'rss',
    );
    
    /**
     * 
     * The area we're using.
     * 
     * @var Solar_Model_Areas_Record
     * 
     */
    public $area;
    
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
     * The total number of records in the query.
     * 
     * @var int
     * 
     */
    public $count;
    
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
     * Local reference to the 'user' object in [[Solar_Registry::get()]].
     * 
     * @var Solar_User
     * 
     */
    public $user;
    
    /**
     * 
     * Extended setup.
     * 
     * Checks to make sure a bookmarks area exists, gets a user object
     * to check priviliges, sets up a bookmarks model, and adds a 
     * bookmarks stylesheet.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        parent::_setup();
        
        // get an area object
        $name = $this->_config['area_name'];
        $this->area = $this->_model->areas->fetchOneByName($name);
        
        // could we find the area?
        if (! $this->area) {
            // area didn't exist, create it.
            $this->area = $this->_model->areas->fetchNew();
            $this->area->name = $name;
            $result = $this->area->save();
            if (! $result) {
                throw $this->_exception('ERR_AREA_NOT_CREATED');
            }
        }
        
        // get a user object for privileges
        $this->user = Solar_Registry::get('user');
    }
    
    /**
     * 
     * Returns a safe SQL ORDER value from the 'order' query parameter.
     * 
     * @return string
     * 
     */
    protected function _getSqlOrder()
    {
        $tmp = strtolower($this->_query('order'));
        
        switch ($tmp) {
        
        // created timestamp
        case 'created':
        case 'created_asc':
            $order = 'bookmarks.created ASC, bookmarks.id ASC';
            break;
        
        case 'created_desc':
            $order = 'bookmarks.created DESC, bookmarks.id DESC';
            break;
            
        // title
        case 'subj':
        case 'subj_asc':
            $order = 'LOWER(bookmarks.subj) ASC, bookmarks.id ASC';
            break;
        
        case 'subj_desc':
            $order = 'LOWER(bookmarks.subj) DESC, bookmarks.id DESC';
            break;
        
        // pos
        case 'pos':
        case 'pos_asc':
            $order = 'bookmarks.pos ASC, bookmarks.id ASC';
            break;
        
        case 'pos_desc':
            $order = 'bookmarks.pos DESC, bookmarks.id DESC';
            break;
        
        // default
        default:
            $order = 'bookmarks.created DESC, bookmarks.id DESC';
            break;
        
        }
        
        return $order;
    }
    
    /**
     * 
     * Adds a new bookmark for a signed-in user.
     * 
     * @return void
     * 
     */
    public function actionAdd()
    {
        // must be logged in to proceed
        if (! $this->user->auth->isValid()) {
            return $this->_error('ERR_NOT_LOGGED_IN');
        }
        
        // build a link for _redirect() calls and the backlink.
        $href = $this->_session->getFlash('backlink');
        if (! $href) {
            // probably browsed to this page directly.  link to the user's list.
            $uri = Solar::factory('Solar_Uri_Action');
            $href = $uri->quick("bookmarks/user/{$this->user->auth->handle}");
        }
        
        // keep the backlink for the next page load
        $this->_session->setFlash('backlink', $href);
        
        // which record cols do we want to work with?
        $cols = array('uri', 'subj', 'summ', 'tags_as_string', 'pos');
        
        // get a blank bookmark, then populate with submitted data
        $item = $this->_model->bookmarks->fetchNew();
        $data = $this->_request->post('bookmarks', array());
        $item->load($data, $cols);
        
        // force these values
        $item->area_id = $this->area->id;
        $item->owner_handle = $this->user->auth->handle;
        $item->editor_handle = $this->user->auth->handle;
        
        // save?
        if ($this->_isProcess('save') && $item->save()) {
            $this->_session->setFlash('success_added', true);
            $this->_redirect("bookmarks/edit/{$item->id}");
        }
        
        // cancel?
        if ($this->_isProcess('cancel')) {
            $this->_redirect($href);
        }
        
        // assign data for the view, and done
        $this->formdata = $item->form($cols);
        $this->backlink = $href;
    }
    
    /**
     * 
     * Allows a signed-in user to edit an existing bookmark.
     * 
     * @param int $id The bookmark node ID.
     * 
     * @return void
     * 
     */
    public function actionEdit($id = null)
    {
        // must be logged in to proceed
        if (! $this->user->auth->isValid()) {
            return $this->_error('ERR_NOT_LOGGED_IN');
        }
        
        // get the bookmark ID
        $id = (int) $id;
        if (! $id) {
            return $this->_error('ERR_NOT_SELECTED');
        }
        
        // fetch the bookmark
        $item = $this->_model->bookmarks->fetch($id);
        
        // does it exist?
        if (! $item) {
            return $this->_error('ERR_NO_SUCH_BOOKMARK');
        }
        
        // must be in this area
        if ($item->area_id != $this->area->id) {
            return $this->_error('ERR_NOT_IN_AREA');
        }
        
        // must be the item owner to edit it
        $item = $this->_model->bookmarks->fetchOneById($id, array(
            'eager' => array('tags'),
        ));
        
        if ($this->user->auth->handle != $item->owner_handle) {
            return $this->_error('ERR_NOT_OWNER');
        }
        
        // -------------------------------------------------------------------
        // 
        // build a link for _redirect() calls and the backlink.
        // 
        // if we came from a tag or user page, link to it.
        // otherwise, link to the list for the user.
        //
        
        $href = $this->_session->getFlash('backlink');
        if (! $href) {
            // probably browsed directly to this page; return to the user's list
            $uri = Solar::factory('Solar_Uri_Action');
            $href = $uri->quick("bookmarks/user/{$this->user->auth->handle}");
        }
        
        // keep the backlink for the next page load
        $this->_session->setFlash('backlink', $href);
        
        // -------------------------------------------------------------------
        // 
        // loading
        // 
        
        // which record cols do we want to work with?
        $cols = array('uri', 'subj', 'summ', 'tags_as_string', 'pos');
        
        // load from posted data
        $data = $this->_request->post('bookmarks', array());
        $item->load($data, $cols);
        
        // force these values
        $item->area_id = $this->area->id;
        $item->owner_handle = $this->user->auth->handle;
        $item->editor_handle = $this->user->auth->handle;
        
        // -------------------------------------------------------------------
        // 
        // processes
        // 
        
        if ($this->_isProcess('save')) {
            $item->save();
        }
        
        // cancel
        if ($this->_isProcess('cancel')) {
            $this->_redirect($href);
        }
        
        // delete
        if ($this->_isProcess('delete')) {
            $item->delete();
            $this->_redirect($href);
        }
        
        // ---------------------------------------------------------------------
        // 
        // completion
        // 
        
        // get the form
        $form = $item->form($cols);
        
        // was this from a quickmark or an "add" process request?
        if ($this->_session->getFlash('success_added')) {
            $form->setStatus(true);
            $form->feedback = $this->locale('SUCCESS_ADDED');
        }
        
        // assign data for the view
        $this->formdata = $form;
        $this->backlink = $href;
    }
    
    /**
     * 
     * Handles JavaScript bookmarking requests from offsite.
     * 
     * @param string $uri The URI to bookmark.
     * 
     * @param string $subj The title for the bookmark, typically the
     * page title from the bookmarked page.
     * 
     * @return void
     * 
     */
    public function actionQuick($uri = null, $subj = null)
    {
        // must be logged in to proceed
        if (! $this->user->auth->isValid()) {
            return $this->_error('ERR_NOT_LOGGED_IN');
        }
        
        // get the quickmark info from the query
        $uri = $this->_query('uri');
        $subj = $this->_query('subj');
        
        // we need to see if the user already has the same URI in
        // his bookmarks so that we don't add it twice.
        $existing = $this->_model->bookmarks->fetchOneByOwnerHandleAndUri(
            $this->user->auth->handle,
            $uri
        );
        
        // if the user *does* already have that URI bookmarked,
        // redirect to the existing bookmark.
        if ($existing) {
            $this->_session->setFlash('backlink', $uri);
            $this->_redirect("bookmarks/edit/{$existing->id}");
        }
        
        // which record cols do we want to work with?
        $cols = array('uri', 'subj', 'summ', 'tags_as_string', 'pos');
        
        // get a blank bookmark, then populate with submitted data
        $item = $this->_model->bookmarks->fetchNew();
        
        // populate first with query values
        $item->uri = $uri;
        $item->subj = $subj;
        
        // overwrite with POST values, if any
        $data = $this->_request->post('bookmarks', array());
        $item->load($data, $cols);
        
        // force these values
        $item->area_id = $this->area->id;
        $item->owner_handle = $this->user->auth->handle;
        $item->editor_handle = $this->user->auth->handle;
        
        // save?
        if ($this->_isProcess('save') && $item->save()) {
            $this->_session->setFlash('success_added', true);
            $this->_redirect("bookmarks/edit/{$item->id}");
        }
        
        // assign data for the view, and done
        $this->formdata = $item->form($cols);
        $this->backlink = $uri;
    }
    
    /**
     * 
     * Shows a list of bookmarks filtered by tag, regardless of owner.
     * 
     * @param string|array $tag_list The tags to show; either a space- (or
     * plus-) separated list of tags, or a sequential array of tags.
     * 
     * @return void
     * 
     */
    public function actionTag($tag_list = null)
    {
        // params for the query
        $params = array(
            'where'  => array(
                // only this area
                'bookmarks.area_id = ?' => $this->area->id,
            ),
            'order'  => $this->_getSqlOrder(),
            'paging' => $this->_query('paging', 10),
            'page'   => $this->_query('page', 1),
            'eager'  => array('tags'),
            'count_pages' => true,
        );
        
        // get the list of bookmarks
        $this->list = $this->_model->bookmarks->fetchAllByTags($tag_list, $params);
        
        // flash forward the backlink in case we go to edit, but only if this
        // is a regular-format request
        if (! $this->_format) {
            $this->_session->setFlash(
                'backlink',
                $this->_request->server('REQUEST_URI')
            );
        }
        
        // assign the list of tags in use
        $this->tags_in_use = $this->_model->tags->fetchAllWithCount(array(
            'order' => 'tags.name'
        ));
        
        // assign everything else for the view
        if ($this->list) {
            $total = $this->list->getPagerInfo();
            $this->count = $total['count'];
            $this->pages = $total['pages'];
        }
        
        $this->order         = $this->_query('order');
        $this->page          = $params['page'];
        $this->owner_handle  = null; // requested owner_handle
        $this->tags          = $tag_list; // the requested tags
        $this->feed['title'] = 'tag';
        $this->feed['descr'] = $tag_list;
        
        // set the view
        $this->_view = 'browse';
    }
    
    /**
     * 
     * Shows all bookmarks for an owner, optionally filtered by tag.
     * 
     * @param string $owner_handle The owner to show bookmarks for.
     * 
     * @param string|array $tag_list An optional set of tags to filter by;
     * either a space- (or plus-) separated list of tags, or a
     * sequential array of tags.
     * 
     * @return void
     * 
     */
    public function actionUser($owner_handle = null, $tag_list = null)
    {
        // must have passed an owner handle
        if (! $owner_handle) {
            return $this->_error('ERR_NO_OWNER_HANDLE');
        }
        
        // params for the query
        $params = array(
            'where'  => array(
                // only this area
                'bookmarks.area_id = ?'      => $this->area->id,
                // only the specified user
                'bookmarks.owner_handle = ?' => $owner_handle,
            ),
            'order'  => $this->_getSqlOrder(),
            'paging' => $this->_query('paging', 10),
            'page'   => $this->_query('page', 1),
            'eager'  => array('tags'),
            'count_pages' => true,
        );
        
        // tags or no-tags?
        if ($tag_list) {
            $this->list = $this->_model->bookmarks->fetchAllByTags($tag_list, $params);
        } else {
            $this->list = $this->_model->bookmarks->fetchAll($params);
        }
        
        // flash forward the backlink in case we go to edit, but only if this
        // is a regular-format request
        if (! $this->_format) {
            $this->_session->setFlash('backlink', $this->_request->server('REQUEST_URI'));
        }
        
        // assign the list of tags in use
        $this->tags_in_use = $this->_model->tags->fetchAllByOwnerHandle(
            $owner_handle,
            array(
                'order' => 'tags.name'
            )
        );
        
        // assign remaining view vars
        if ($this->list) {
            $total = $this->list->getPagerInfo();
            $this->count = $total['count'];
            $this->pages = $total['pages'];
        }
        
        $this->order         = $this->_query('order');
        $this->page          = $params['page'];
        $this->owner_handle  = $owner_handle; // requested owner_handle
        $this->tags          = $tag_list; // the requested tags
        $this->feed['title'] = 'user';
        $this->feed['descr'] = $this->owner_handle . '/' . $this->tags;
        
        // set the view
        $this->_view = 'browse';
    }
}
