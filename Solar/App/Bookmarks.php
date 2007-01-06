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
     * Keys are ...
     * 
     * `area_name`
     * : (string) The content area for the bookmarks
     *   app, default 'Solar_App_Bookmarks'.  Will be created automatically
     *   if it does not exist.
     * 
     * `content`
     * : (dependency) A Solar_Content domain model dependency object.
     * 
     * @var array
     * 
     */
    protected $_Solar_App_Bookmarks = array(
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
     * Local reference to the 'user' object in [[Solar::registry()]].
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
        
        // make sure a bookmarks area exists
        $content = Solar::dependency('Solar_Content', $this->_config['content']);
        $name = $this->_config['area_name'];
        $area = $content->areas->fetchByName($name);
        if (! $area->id) {
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
        if (! $this->_user->auth->isValid()) {
            $this->errors[] = 'ERR_NOT_LOGGED_IN';
            return $this->_forward('error');
        }

        // build a link for _redirect() calls and the backlink.
        $href = $this->_session->getFlash('backlink');
        if (! $href) {
            // probably browsed to this page directly.  link to the user's list.
            $uri = Solar::factory('Solar_Uri_Action');
            $href = $uri->quick("bookmarks/user/{$this->_user->auth->handle}");
        }

        // keep the backlink for the next page load
        $this->_session->setFlash('backlink', $href);

        // build the basic form, populated with the bookmark data
        // from the database
        $item = $this->_bookmarks->fetchNew();
        $form = $this->_bookmarks->form($item);

        // now populate the the submitted POST values to the form
        $form->populate();


        // ---------------------------------------------------------------------
        // 
        // processes
        // 

        // Process: save
        if ($this->_isProcess('save') && $form->validate()) {
    
            // load data from the form input
            $item->load($form->values('bookmark'));
            
            // force these values
            $item->owner_handle = $this->_user->auth->handle;
            $item->editor_handle = $this->_user->auth->handle;
    
            // save the data
            try {
                
                $item->save();
                $this->_session->setFlash('add_ok', true);
                $this->_redirect("bookmarks/edit/{$item->id}");
        
            } catch (Solar_Exception $e) {
        
                // exception on save()
                // we should not have gotten to this point,
                // but need to be aware of possible problems.
                $form->setStatus(false);
                $form->feedback[] = $e->getClass() . ' -- ' . $e->getMessage();
        
            }
        }
        
        // OP: Cancel
        if ($this->_isProcess('cancel')) {
            $this->_redirect($href);
        }
        
        // ---------------------------------------------------------------------
        // 
        // completion
        // 
        
        // assign data for the view
        $this->formdata = $form;
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
        if (! $this->_user->auth->isValid()) {
            $this->errors[] = 'ERR_NOT_LOGGED_IN';
            return $this->_forward('error');
        }

        // get the bookmark ID (0 means a new bookmark)
        $id = (int) $id;
        if (! $id) {
            $this->errors[] = 'ERR_NOT_SELECTED';
            return $this->_forward('error');
        }

        // must be the item owner to edit it
        $item = $this->_bookmarks->fetch($id);
        if ($this->_user->auth->handle != $item->owner_handle) {
            $this->errors[] = 'ERR_NOT_OWNER';
            return $this->_forward('error');
        }

        // ---------------------------------------------------------------------
        // 
        // build a link for _redirect() calls and the backlink.
        // 
        // if we came from a tag or user page, return there.
        // otherwise, return the list for the user.
        //

        $href = $this->_session->getFlash('backlink');
        if (! $href) {
            // probably browsed directly to this page; return to the user's list
            $uri = Solar::factory('Solar_Uri_Action');
            $href = $uri->quick("bookmarks/user/{$this->_user->auth->handle}");
        }
        
        // keep the backlink for the next page load
        $this->_session->setFlash('backlink', $href);

        // ---------------------------------------------------------------------
        // 
        // processing
        // 

        // build the basic form, populated with the bookmark data
        // from the database
        $form = $this->_bookmarks->form($item);

        // now populate the the submitted POST values to the form
        $form->populate();
        
        // was this from a quickmark or an "add" process request?
        if ($this->_session->getFlash('add_ok')) {
            $form->setStatus(true);
            $form->feedback = $this->locale('SUCCESS_ADDED');
        }
        
        // Save?
        if ($this->_isProcess('save') && $form->validate()) {
    
            // load the item with form input
            $item->load($form->values('bookmark'));
            
            // force these values
            $item->owner_handle = $this->_user->auth->handle;
            $item->editor_handle = $this->_user->auth->handle;
    
            // save the data
            try {
        
                // attempt the save, may throw an exception
                $item->save();
        
            } catch (Solar_Sql_Table_Exception $e) {
        
                // exception on save()
                // we should not have gotten to this point,
                // but need to be aware of possible problems.
                $form->setStatus(false);
                $form->feedback[] = $e->getClass() . ' -- ' . $e->getMessage();
        
                // add bookmark[*] element feedback
                $form->addFeedback($e->getInfo(), 'bookmark');
            }
        }

        // Cancel?
        if ($this->_isProcess('cancel')) {
            $this->_redirect($href);
        }

        // Delete?
        if ($this->_isProcess('delete')) {
            $values = $form->values();
            $id = $values['bookmark']['id'];
            $this->_bookmarks->delete($id);
            $this->_redirect($href);
        }

        // ---------------------------------------------------------------------
        // 
        // completion
        // 

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
        if (! $this->_user->auth->isValid()) {
            $this->errors[] = 'ERR_NOT_LOGGED_IN';
            return $this->_forward('error');
        }

        // get the quickmark info from the query
        $uri = $this->_query('uri');
        $subj = $this->_query('subj');

        // we need to see if the user already has the same URI in
        // his bookmarks so that we don't add it twice.
        $existing = $this->_bookmarks->fetchByOwnerUri(
            $this->_user->auth->handle,
            $uri
        );
        
        // if the user *does* already have that URI bookmarked,
        // redirect to the existing bookmark.
        if (! empty($existing->id)) {
            $this->_session->setFlash('backlink', $uri);
            $this->_redirect("bookmarks/edit/{$existing['id']}");
        }

        // get a blank bookmark item, build the basic form
        $item = $this->_bookmarks->fetchNew();
        $item->uri = $uri;
        $item->subj = $subj;
        $form = $this->_bookmarks->form($item);

        // overwrite form defaults with submissions
        $form->populate();

        // check for a 'Save' process request
        if ($this->_isProcess('save') && $form->validate()) {
    
            // save the data
            try {
    
                // get the form values
                $item->load($form->values('bookmark'));
                $item->owner_handle = $this->_user->auth->handle;
                $item->editor_handle = $this->_user->auth->handle;
        
                // save
                $item->save();
        
                // redirect to the source URI (external)
                $this->_redirect($item->uri);
        
            } catch (Solar_Exception $e) {
        
                // exception on save()
                // we should not have gotten to this point,
                // but need to be aware of possible problems.
                $form->setStatus(false);
                $form->feedback[] = $e->getClass() . ' -- ' . $e->getMessage();
                echo $e;
        
            }
        }

        // assign data for the view
        $this->formdata = $form;
        $this->backlink = $uri;
    }
    
    /**
     * 
     * Shows a list of bookmarks filtered by tag, regardless of owner.
     * 
     * @param string|array $tags The tags to show; either a space- (or
     * plus-) separated list of tags, or a sequential array of tags.
     * 
     * @return void
     * 
     */
    public function actionTag($tags = null)
    {
        // allow uri to set the "count" for each page (default 10)
        $this->_bookmarks->setPaging($this->_query('paging', 10));

        // the requested owner_handle (none)
        $owner_handle = null;

        // the requested ordering of list results
        $order = $this->_getOrder();

        // what page-number of the results are we looking for?
        // (regardless of RSS or HTML)
        $page = $this->_query('page', 1);

        // get the list of results
        $this->list = $this->_bookmarks->fetchAll($tags, $owner_handle, $order, $page);

        // get the total pages and row-count
        $total = $this->_bookmarks->countPages($tags, $owner_handle);

        // flash forward the backlink in case we go to edit
        $this->_session->setFlash('backlink', $this->_request->server('REQUEST_URI'));

        // assign everything else for the view
        $this->pages        = $total['pages'];
        $this->order        = $this->_request->get('order', 'created_desc');
        $this->page         = $page;
        $this->owner_handle = null; // requested owner_handle
        $this->tags         = $tags; // the requested tags
        $this->tags_in_use  = $this->_bookmarks->fetchTags($owner_handle); // all tags

        // use the 'browse' view
        $this->_view = 'browse';

        // RSS feed link for the page
        $uri = Solar::factory('Solar_Uri_Action');
        $uri->path[1] = 'tag-feed';
        $this->layout_link[] = array(
            'rel'   => 'alternate',
            'type'  => 'application/rss+xml',
            'title' => $this->_request->server('PATH_INFO'),
            'href'  => $uri->fetch(),
        );
    }
    
    /**
     * 
     * Shows an RSS feed of bookmarks filtered by tag, regardless of 
     * owner.
     * 
     * @param string|array $tags The tags to show; either a space- (or
     * plus-) separated list of tags, or a sequential array of tags.
     * 
     * @return void
     * 
     */
    public function actionTagFeed($tags = null)
    {
        // build the local variables
        $this->_forward('tag', array($tags));

        // explicitly pick a different view script
        $this->_view = 'feed';

        // assign to the view
        $this->feed['title'] = 'tag';
        $this->feed['descr'] = $this->tags;

        $uri = Solar::factory('Solar_Uri_Action');
        $uri->info[1] = 'tag';
        $this->feed['link'] = $uri->fetch(true);

        // explicitly use a one-step view (i.e., no layout)
        $this->_layout = false;
    }
    
    /**
     * 
     * Shows all bookmarks for an owner, optionally filtered by tag.
     * 
     * @param string $owner_handle The owner to show bookmarks for.
     * 
     * @param string|array $tags An optional set of tags to filter by;
     * either a space- (or plus-) separated list of tags, or a
     * sequential array of tags.
     * 
     * @return void
     * 
     */
    public function actionUser($owner_handle = null, $tags = null)
    {
        // allow uri to set the "count" for each page (default 10)
        $this->_bookmarks->setPaging($this->_query('paging', 10));

        // the requested ordering of list results
        $order = $this->_getOrder();

        // which page number?
        $page = $this->_query('page', 1);

        // get the list of results
        $this->list = $this->_bookmarks->fetchAll($tags, $owner_handle, $order, $page);

        // get the total pages and row-count
        $total = $this->_bookmarks->countPages($tags, $owner_handle);

        // flash forward the backlink in case we go to edit
        $this->_session->setFlash('backlink', $this->_request->server('REQUEST_URI'));

        // set the view
        $this->_view = 'browse';

        // assign view vars
        $this->pages        = $total['pages'];
        $this->order        = $this->_request->get('order', 'created_desc');
        $this->page         = $page;
        $this->owner_handle = $owner_handle; // requested owner_handle
        $this->tags         = $tags; // the requested tags
        $this->tags_in_use  = $this->_bookmarks->fetchTags($owner_handle); // all tags for this user

        // set the RSS feed link for the layout
        $uri = Solar::factory('Solar_Uri_Action');
        $uri->path[1] = 'user-feed';

        if ($tags) {
            // there are tags requested, so the RSS should show all pages
            // (i.e., page zero) and ignore the rows-per-page settings.
            $uri->query['page'] = 'all';
            unset($uri->query['rows_per_page']);
        }

        $this->layout_link[] = array(
            'rel'   => 'alternate',
            'type'  => 'application/rss+xml',
            'title' => $this->_request->server('PATH_INFO'),
            'href'  => $uri->fetch(),
        );
    }
    
    /**
     * 
     * Shows all bookmarks for an owner as an RSS feed, optionally 
     * filtered by tag.
     * 
     * @param string $owner_handle The owner to show bookmarks for.
     * 
     * @param string|array $tags An optional set of tags to filter by;
     * either a space- (or plus-) separated list of tags, or a
     * sequential array of tags.
     * 
     * @return void
     * 
     */
    public function actionUserFeed($owner_handle = null, $tags = null)
    {
        // build the local vars
        $this->_forward('user', array($owner_handle, $tags));

        // explicitly use a different view
        $this->_view = 'feed';

        // assign vars
        $this->feed['title'] = 'user';
        $this->feed['descr'] = $this->owner_handle . '/' . $this->tags;
        $this->feed['date'] = date(DATE_RSS);

        $uri = Solar::factory('Solar_Uri_Action');
        $uri->info[1] = 'user';
        $this->feed['link'] = $uri->fetch(true);

        // explicitly use a one-step view (no layout)
        $this->_layout = false;
    }
}
