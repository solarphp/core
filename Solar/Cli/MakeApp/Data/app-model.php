/**
 * 
 * Generic BREAD application for {:model}.
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
     * A list of records.
     * 
     * @var Solar_Sql_Model_Collection
     * 
     */
    public $list;
    
    /**
     * 
     * A single record.
     * 
     * @var Solar_Sql_Model_Record
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
     * Browses records by page.
     * 
     * @return void
     * 
     */
    public function actionBrowse()
    {
        // get the model
        $model = Solar::factory('{:model}');
        
        // get the collection
        $this->list = $model->fetchAll(array(
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
                
        // get the model
        $model = Solar::factory('{:model}');
        
        // get the record
        $item = $model->fetch($id);
        
        // does the record exist?
        if (! $item) {
            return $this->_error('ERR_NO_SUCH_ITEM');
        }
        
        // done
        $this->item = $item;
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
        
        // need an id
        if (! $id) {
            return $this->_error('ERR_NO_ID_SPECIFIED');
        }
        
        // get the model
        $model = Solar::factory('{:model}');
        
        // get the record
        $item = $model->fetch($id);
        
        // does the record exist?
        if (! $item) {
            return $this->_error('ERR_NO_SUCH_ITEM');
        }
        
        // process: save
        if ($this->_isProcess('save')) {
            
            // what array name should we look for?
            $model_name = $model->model_name;
            
            // get the POST data using the array name
            $data = $this->_request->post($model_name, array());
            
            // load the data to the record
            $item->load($data);
            
            // attempt the save.  this will update the record and
            // set invalidation messages if it didn't work.
            $item->save();
        }
        
        // get the form-building hints
        $form = $item->form();
        
        // catch flash indicating a successful add
        if ($this->_session->getFlash('success_added')) {
            $form->setStatus(true);
            $form->feedback = $this->locale('SUCCESS_ADDED');
        }
        
        // done
        $this->item = $item;
        $this->form = $form;
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
        
        // get the model
        $model = Solar::factory('{:model}');
        
        // get a new record
        $item = $model->fetchNew();
        
        // process: save
        if ($this->_isProcess('save')) {
            
            // what array name should we look for?
            $model_name = $model->model_name;
            
            // get the POST data using the array name
            $data = $this->_request->post($model_name, array());
            
            // load the data to the record
            $item->load($data);
            
            // attempt the save.  this will update the record and
            // set invalidation messages if it didn't work.
            if ($item->save()) {
                // save a flash value for the next page
                $this->_session->setFlash('success_added', true);
                // redirect to editing.
                return $this->_redirectNoCache("/{$this->_controller}/edit/{$item->id}");
            }
        }
        
        // set the item
        $this->item = $item;
        
        // get the form-building hints
        $this->form = $item->form();
    }
    
    /**
     * 
     * Deletes a record by ID. Asks for confirmation before actually deleting.
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
        
        // get the model
        $model = Solar::factory('{:model}');
        
        // get the record
        $item = $model->fetch($id);
        
        // does the record exist?
        if (! $item) {
            return $this->_error('ERR_NO_SUCH_ITEM');
        }
        
        // process: delete confirm
        if ($this->_isProcess('delete_confirm')) {
            // delete it
            $item->delete();
            // redirect to browse
            $this->_redirectNoCache("/{$this->_controller}");
        }
        
        // show the item for deletion
        $this->item = $item;
    }
}
