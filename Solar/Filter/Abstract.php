<?php
/**
 * 
 * Abstract class for filters.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Abstract class for filters.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 */
abstract class Solar_Filter_Abstract extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `filter`
     * : (Solar_Filter) The "parent" Solar_Filter object.
     * 
     * @var array
     * 
     */
    protected $_Solar_Filter_Abstract = array(
        'filter' => null,
    );
    
    /**
     * 
     * The "parent" filter object.
     * 
     * @var Solar_Filter
     * 
     */
    protected $_filter;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_filter = $this->_config['filter'];
    }
}