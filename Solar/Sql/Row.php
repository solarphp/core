<?php
/**
 * 
 * Represents a single row of SQL SELECT results, optionally with a 
 * source object to support saving the data.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
 
/**
 * Extends the Solar_Struct class.
 */
Solar::loadClass('Solar_Struct');

/**
 * 
 * Represents a single row of SQL SELECT results, optionally with a 
 * source object to support saving the data.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Row extends Solar_Struct {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `data`
     * : (array) Key-value pairs of colname => value.
     * 
     * `save`
     * : (object) Source object for save() calls.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Row = array(
        'data' => array(),
        'save' => null,
    );
    
    /**
     * 
     * Source object for save() calls.
     * 
     * @var object
     * 
     */
    protected $_save;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        if ($this->_config['save']) {
            $this->setSave($this->_config['save']);
        }
    }
    
    /**
     * 
     * Saves the current values via the $this->_save object.
     * 
     * @return void
     * 
     * @see Solar_Sql_Row::$_save
     * 
     * @see Solar_Sql_Row::setSave()
     * 
     * @see Solar_Sql_Row::getSave()
     * 
     */
    public function save()
    {
        if ($this->_save) {
            $result = $this->_save->save($this->_data);
            foreach ($result as $key => $val) {
                $this->$key = $val;
            }
        }
    }
        
    /**
     * 
     * Sets the source object for save() calls.
     * 
     * @param object $obj A source object with a save() method.  Use
     * null to explicitly turn off saving.
     * 
     * @return void
     * 
     */
    public function setSave($obj)
    {
        if (is_null($obj)) {
            $this->_save = null;
        } elseif (is_callable(array($obj, 'save'))) {
            $this->_save = $obj;
        } else {
            $this->_save = null;
            throw $this->_exception('ERR_SAVE_NOT_CALLABLE');
        }
    }
    
    /**
     * 
     * Gets the current source object for save() calls.
     * 
     * @return void
     * 
     */
    public function getSave()
    {
        return $this->_save;
    }
}
