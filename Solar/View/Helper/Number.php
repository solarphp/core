<?php
/**
 * 
 * Helper for a formatted mnumber
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Jeff Moore <jeff@procata.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_View_Helper_Number extends Solar_View_Helper
{
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are:
     * 
     * `dec_point`
     * : (string) Designates the character used for decimal points. Default is
     *   the locale string for FORMAT_DEC_POINT.
     * 
     * `thousands_sep`
     * : (string) Designates the character used to separate thousands. Default
     *   is the locale string for FORMAT_THOUSANDS_SEP.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_Number = array(
        'dec_point'     => null,
        'thousands_sep' => null,
    );
    
    /**
     * 
     * Constructor..
     * 
     * @param array $config User-defined configuration keys.
     * 
     */
    public function __construct($config = null)
    {
        $this->_Solar_View_Helper_Number['dec_point'] = $this->locale(
            'FORMAT_DEC_POINT'
        );
        
        $this->_Solar_View_Helper_Number['thousands_sep'] = $this->locale(
            'FORMAT_THOUSANDS_SEP'
        );
        
        parent::__construct($config);
    }
    
    /**
     * 
     * Returns a numeric value formatted with [[php::number_format() | ]]
     * 
     * @param string|int|float $number A numeric value.
     * 
     * @param int $decimals Round to this many decimal places; if null, use
     * all decimal places in $number.
     * 
     * @return string The formatted number string.
     * 
     */
    public function number($number, $decimals = null)
    {
        if ($decimals === null) {
            return number_format($number);
        } else {
            return number_format(
                $number, 
                $decimals, 
                $this->_config['dec_point'], 
                $this->_config['thousands_sep']
            );
        }
    }
}
