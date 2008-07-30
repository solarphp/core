<?php
abstract class Solar_Factory extends Solar_Base
{
    protected $_Solar_Factory = array(
        'adapter' => null,
    );
    
    /**
     * 
     * Disallow all calls to methods besides factory() and the existing
     * support methods.
     * 
     * @return void
     * 
     */
    final public function __call($method, $params)
    {
        throw $this->_exception('ERR_NOT_ADAPTER_INSTANCE', array(
            'method' => $method,
            'params' => $params,
        ));
    }
    
    /**
     * 
     * Factory method for returning adapter objects.
     * 
     * @return object
     * 
     */
    public function factory()
    {
        // bring in the config and get the adapter class.
        $config = $this->_config;
        $class = $config['adapter'];
        unset($config['adapter']);
        
        // return the factoried adapter object
        return new $class($config);
    }
}