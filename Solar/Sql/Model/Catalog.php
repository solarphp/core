<?php
class Solar_Sql_Model_Catalog extends Solar_Base
{
    /**
     * 
     * User-defined configuration options.
     * 
     */
    protected $_Solar_Sql_Model_Catalog = array(
        'classes' => null,
    );
    
    /**
     * 
     * Inflection dependency.
     * 
     * @var Solar_Inflect
     * 
     */
    protected $_inflect;
    
    /**
     * 
     * Class stack for finding models.
     * 
     * @var Solar_Class_Stack
     * 
     */
    protected $_stack;
    
    /**
     * 
     * An array of instantiated model objects keyed by class name.
     * 
     * @var array
     * 
     */
    protected $_store = array();
    
    /**
     * 
     * A mapping of model names to model classes.
     * 
     * @var array
     * 
     */
    protected $_name_class = array();
    
    /**
     * 
     * Constructor.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // inflection dependency
        $this->_inflect = Solar::dependency(
            'Solar_Inflect',
            'inflect'
        );
        
        // model stack
        $this->_setStack($this->_config['classes']);
    }
    
    /**
     * 
     * Magic get to make it look like model names are object properties.
     * 
     * @param string $key The model name to retrieve.
     * 
     * @return Solar_Sql_Model The model object.
     * 
     */
    public function __get($key)
    {
        return $this->getModel($key);
    }
    
    /**
     * 
     * Frees memory for all models in the catalog.
     * 
     * @return void
     * 
     */
    public function free()
    {
        foreach ($this->_store as $class => $model) {
            $model->free();
        }
    }
    
    /**
     * 
     * Gets the model class for a particular model name.
     * 
     * @param string $name The model name.
     * 
     * @return string The model class.
     * 
     */
    public function getClass($name)
    {
        $name = $this->_inflect->underToStudly($name);
        
        if (empty($this->_name_class[$name])) {
            $class = $this->_stack->load($name);
            $this->_name_class[$name] = $class;
        }
        
        return $this->_name_class[$name];
    }
    
    /**
     * 
     * Returns a stored model instance by name, creating it if needed.
     * 
     * @param string $name The model name.
     * 
     * @return Solar_Sql_Model A model instance.
     * 
     */
    public function getModel($name)
    {
        $class = $this->getClass($name);
        return $this->getModelByClass($class);
    }
    
    /**
     * 
     * Returns a stored model instance by class, creating it if needed.
     * 
     * @param string $class The model class.
     * 
     * @return Solar_Sql_Model A model instance.
     * 
     */
    public function getModelByClass($class)
    {
        if (empty($this->_store[$class])) {
            $this->_store[$class] = $this->_newModel($class);
        }
        
        return $this->_store[$class];
    }
    
    /**
     * 
     * Sets a model name to be a specific instance or class.
     * 
     * Generally, you only need this when you want to bring in a single model
     * from outside the expected stack.
     * 
     * @param string $name The model name to use.
     * 
     * @param string|Solar_Sql_Model $spec If a model object, use directly;
     * otherwise, assume it's a string class name and create a new model using
     * that.
     * 
     * @return void
     * 
     */
    public function setModel($name, $spec)
    {
        if (! empty($this->_name_class[$name])) {
            throw $this->_exception('ERR_MODEL_NAME_EXISTS', array(
                'name' => $name,
            ));
        }
        
        // instance, or new model?
        if ($spec instanceof Solar_Sql_Model) {
            $model = $spec;
            $class = get_class($model);
        } else {
            $class = $spec;
            $model = $this->_newModel($class);
        }
        
        // retain the name-to-class mapping and the model itself
        $this->_name_class[$name] = $class;
        $this->_store[$class] = $model;
    }
    
    /**
     * 
     * Loads a model from the stack into the catalog by name, returning a 
     * true/false success indicator (instead of throwing an exception when
     * the class cannot be found).
     * 
     * @param string $name The model name to load from the stack into the
     * catalog.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function loadModel($name)
    {
        try {
            $class = $this->getClass($name);
        } catch (Solar_Class_Stack_Exception_ClassNotFound $e) {
            return false;
        }
        
        // retain the model internally
        $this->getModelByClass($class);
        
        // success
        return true;
    }
    
    /**
     * 
     * Returns a new model instance (not stored).
     * 
     * @param string $name The model name.
     * 
     * @return Solar_Sql_Model A model instance.
     * 
     */
    public function newModel($name)
    {
        $class = $this->getClass($name);
        return $this->_newModel($class);
    }
    
    /**
     * 
     * Returns information about the catalog as an array with keys for 'names'
     * (the model name-to-class mappings), 'store' (the classes actually
     * loaded up and retained), and 'stack' (the search stack for models).
     * 
     * @return array
     * 
     */
    public function getInfo()
    {
        return array(
            'names' => $this->_name_class,
            'store' => array_keys($this->_store),
            'stack' => $this->_stack->get(),
        );
    }
    
    /**
     * 
     * Sets the model stack.
     * 
     * @param array $classes An array of class prefixes to use for the model
     * stack.
     * 
     */
    protected function _setStack($classes)
    {
        if (! $classes) {
            // add per the vendor on this catalog and its inheritance
            $parents = Solar_Class::parents(get_class($this), true);
            array_shift($parents); // Solar_Base
            $old_vendor = false;
            foreach ($parents as $class) {
                $new_vendor = Solar_Class::vendor($class);
                if ($new_vendor != $old_vendor) {
                    $classes[] = "{$new_vendor}_Model";
                }
                $old_vendor = $new_vendor;
            }
        }
        
        // build the class stack
        $this->_stack = Solar::factory('Solar_Class_Stack');
        $this->_stack->add($classes);
    }
    
    /**
     * 
     * Returns a new model instance (not stored).
     * 
     * @param string $class The model class.
     * 
     * @return Solar_Sql_Model A model instance.
     * 
     */
    protected function _newModel($class)
    {
        // instantiate
        $model = Solar::factory($class, array(
            'catalog' => $this,
        ));
        
        // done!
        return $model;
    }
}