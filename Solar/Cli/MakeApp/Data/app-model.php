/**
 * 
 * Generic BREAD application for {:model_class}.
 * 
 */
class {:class} extends {:extends} {
    
    /**
     * 
     * The default action when no action is specified.
     * 
     * @var string
     * 
     */
    protected $_action_default = 'browse';
    
    /**
     * 
     * A collection of records.
     * 
     * @var {:model_class}_Collection
     * 
     */
    public $list;
    
    /**
     * 
     * A single record.
     * 
     * @var {:model_class}_Record
     * 
     */
    public $item;
    
    /**
     * 
     * A form for editing a single record.
     * 
     * @var Solar_Form
     * 
     */
    public $form;
    
    /**
     * 
     * Use only these columns in the form, and when loading record data.
     * 
     * When empty, uses all columns.
     * 
     * @var array
     * 
     */
    protected $_cols = array();
    
    /**
     * 
     * An instance of the model catalog.
     * 
     * @var Solar_Sql_Model_Catalog
     * 
     */
    protected $_model;
    
    /**
     * 
     * Setup logic to register and retain a model catalog.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        // parent logic
        parent::_setup();
        
        // register the model catalog
        if (! Solar_Registry::exists('model_catalog')) {
            Solar_Registry::set('model_catalog', 'Solar_Sql_Model_Catalog');
        }
        
        // retain the model catalog
        $this->_model = Solar_Registry::get('model_catalog');
    }
    
    /**
     * 
     * Browse records by page.
     * 
     * @return void
     * 
     */
    public function actionBrowse()
    {
        // get the collection
        $this->list = $this->_model->{:model_name}->fetchAll(array(
            'page'        => $this->_query('page', 1),
            'paging'      => $this->_query('paging', 10),
            'count_pages' => true,
        ));
    }
    
    /**
     * 
     * View one record by ID.
     * 
     * @param int $id The record ID to view.
     * 
     * @return void
     * 
     */
    public function actionRead($id = null)
    {
        // need an id
        if (! $id) {
            return $this->_error('ERR_NO_ID_SPECIFIED');
        }
                
        // get the record
        $this->item = $this->_model->{:model_name}->fetch($id);
        
        // does the record exist?
        if (! $this->item) {
            return $this->_error('ERR_NO_SUCH_ITEM');
        }
    }
    
    /**
     * 
     * Edit a record by ID.
     * 
     * @param int $id The record id.
     * 
     * @return void
     * 
     */
    public function actionEdit($id = null)
    {
        // need an id
        if (! $id) {
            return $this->_error('ERR_NO_ID_SPECIFIED');
        }
        
        // process: cancel
        if ($this->_isProcess('cancel')) {
            // forward back to reading
            return $this->_redirect("/{$this->_controller}/read/$id");
        }
        
        // process: delete
        if ($this->_isProcess('delete')) {
            // forward to the delete method for confirmation
            return $this->_redirect("/{$this->_controller}/delete/$id");
        }
        
        // get the record
        $this->item = $this->_model->{:model_name}->fetch($id);
        
        // does the record exist?
        if (! $this->item) {
            return $this->_error('ERR_NO_SUCH_ITEM');
        }
        
        // process: save
        if ($this->_isProcess('save')) {
            
            // what array name should we look for in the POST data?
            $name = $this->_model->{:model_name}->model_name;
            
            // get the POST data using the array name
            $data = $this->_request->post($name, array());
            
            // load the data cols to the record
            $this->item->load($data, $this->_cols);
            
            // attempt the save.  this will update the record and
            // set invalidation messages if it didn't work.
            $this->item->save();
        }
        
        // get the form-building hints for the cols
        $this->form = $this->item->form($this->_cols);
        
        // catch flash indicating a successful add
        if ($this->_session->getFlash('success_added')) {
            $this->form->setStatus(true);
            $this->form->feedback = $this->locale('SUCCESS_ADDED');
        }
    }
    
    /**
     * 
     * Add a new record.
     * 
     * @return void
     * 
     */
    public function actionAdd()
    {
        // process: cancel
        if ($this->_isProcess('cancel')) {
            // forward back to browse
            return $this->_redirect("/{$this->_controller}/browse");
        }
        
        // get a new record
        $this->item = $this->_model->{:model_name}->fetchNew();
        
        // process: save
        if ($this->_isProcess('save')) {
            
            // what array name should we look for in the POST data?
            $name = $this->_model->{:model_name}->model_name;
            
            // get the POST data using the array name
            $data = $this->_request->post($name, array());
            
            // load the data cols to the record
            $this->item->load($data, $this->_cols);
            
            // attempt the save.  this will update the record and
            // set invalidation messages if it didn't work.
            if ($this->item->save()) {
                // save a flash value for the next page
                $this->_session->setFlash('success_added', true);
                // redirect to editing using the primary-key value
                $id = $this->item->getPrimaryVal();
                return $this->_redirectNoCache("/{$this->_controller}/edit/$id");
            }
        }
        
        // get the form-building hints for the cols
        $this->form = $this->item->form($this->_cols);
    }
    
    /**
     * 
     * Delete a record by ID; asks for confirmation before actually deleting.
     * 
     * @param int $id The record ID.
     * 
     * @return void
     * 
     */
    public function actionDelete($id = null)
    {
        // need an id
        if (! $id) {
            return $this->_error('ERR_NO_ID_SPECIFIED');
        }
        
        // get the record
        $this->item = $this->_model->{:model_name}->fetch($id);
        
        // does the record exist?
        if (! $this->item) {
            return $this->_error('ERR_NO_SUCH_ITEM');
        }
        
        // process: delete confirm
        if ($this->_isProcess('delete_confirm')) {
            // delete it
            $this->item->delete();
            // redirect to browse
            $this->_redirectNoCache("/{$this->_controller}");
        }
    }
}
