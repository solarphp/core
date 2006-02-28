<?php
/**
 * 
 * Concrete Solar_View for XHTML.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

require_once 'Solar/View.php';

/**
 * 
 * Concrete Solar_View for XHTML.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Xhtml extends Solar_View {
    
    protected $_escape = array(
        'quotes' => ENT_COMPAT,
        'charset' => 'iso-8859-1',
    );
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        if (! empty($this->_config['escape']['quotes'])) {
            $this->_escape['quotes'] = $this->_config['escape']['quotes'];
        }
        if (! empty($this->_config['escape']['charset'])) {
            $this->_escape['charset'] = $this->_config['escape']['charset'];
        }
    }
    
    public function escape($value)
    {
        return htmlspecialchars(
            $value,
            $this->_escape['quotes'],
            $this->_escape['charset']
        );
    }
    
    public function setHelperPath($path = null)
    {
        $this->_helper_path->set('Solar/View/Xhtml/');
        $this->_helper_path->add($path);
    }
}
?>