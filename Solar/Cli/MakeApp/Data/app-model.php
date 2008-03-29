class {:class} extends {:extends} {
    
    protected $_action_default = 'browse';
    
    public $list;
    
    public $item;
    
    public $form;
    
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
    
    public function actionDelete($id)
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
