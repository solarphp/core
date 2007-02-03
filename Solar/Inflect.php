<?php
/**
 * 
 * Applies inflections to words: singular, plural, camel, underscore, etc.
 * 
 * @category Solar
 * 
 * @package Solar_Inflect
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
 * Applies inflections to words: singular, plural, camel, underscore, etc.
 * 
 * @category Solar
 * 
 * @package Solar_Inflect
 * 
 */
class Solar_Inflect extends Solar_Base {
    
    /**
     * 
     * User-defined configuration keys.
     * 
     * Keys are ...
     * 
     * `identical`
     * : (array) Words that do not change from singular to plural.
     * 
     * `irregular`
     * : (array) Irregular singular-to-plural inflections.
     * 
     * `one_to_many`
     * : (array) Rules for preg_replace() to convert singulars to plurals.
     * 
     * `many_to_one`
     * : (array) Rules for preg_replace() to convert plurals to singulars.
     * 
     * @var array
     * 
     */
    protected $_Solar_Inflect = array(
        'identical'   => array(),
        'irregular'   => array(),
        'many_to_one' => array(),
        'one_to_many' => array(),
    );
    
    /**
     * 
     * A list of words that are the same in singular and plural.
     * 
     * This list is adapted from Ruby on Rails ActiveSupport inflections.
     * 
     * @var array
     * 
     */
    protected $_identical = array(
        'equipment',
        'fish',
        'information',
        'money',
        'rice',
        'series',
        'sheep',
        'species',
    );
    
    /**
     * 
     * Irregular singular-to-plural conversions.
     * 
     * Array format is "singular" => "plural" and are literal text, not
     * regular expressions.
     * 
     * This list is adapted from Ruby on Rails ActiveSupport inflections.
     * 
     * @var array
     * 
     */
    protected $_irregular = array(
        'child'  => 'children',
        'man'    => 'men',
        'move'   => 'moves',
        'person' => 'people',
        'sex'    => 'sexes',
    );
    
    /**
     * 
     * Regex rules for converting plural to singular.
     * 
     * Array format is "pattern" => "replacement" for [[php::preg_replace() | ]].
     * 
     * All patterns are treated as '/pattern$/i'.
     * 
     * This list is adapted from Ruby on Rails ActiveSupport inflections.
     * 
     * @var array
     * 
     */
    protected $_many_to_one = array(
        's'                    => '',
        '(n)ews'               => '$1ews',
        '([ti])a'              => '$1um',
        '((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses' => '$1$2sis',
        '(^analy)ses'          => '$1sis',
        '([^f])ves'            => '$1fe',
        '(hive)s'              => '$1',
        '(tive)s'              => '$1',
        '([lr])ves'            => '$1f',
        '([^aeiouy]|qu)ies'    => '$1y',
        '(s)eries'             => '$1eries',
        '(m)ovies'             => '$1ovie',
        '(x|ch|ss|sh)es'       => '$1',
        '([m|l])ice'           => '$1ouse',
        '(bus)es'              => '$1',
        '(o)es'                => '$1',
        '(shoe)s'              => '$1',
        '(cris|ax|test)es'     => '$1is',
        '(octop|vir)i'         => '$1us',
        '(alias|status)es'     => '$1',
        '^(ox)en'              => '$1',
        '(vert|ind)ices'       => '$1ex',
        '(matr)ices'           => '$1ix',
        '(quiz)zes'            => '$1',
    );
    
    /**
     * 
     * Regex rules for converting singular to plural.
     * 
     * Array format is "pattern" => "replacement" for [[php::preg_replace() | ]].
     * 
     * All patterns are treated as '/pattern$/i'.
     * 
     * This list is taken from Ruby on Rails ActiveSupport inflections.
     * 
     * @var array
     * 
     */
    protected $_one_to_many = array(
        ''                     => 's',
        's'                    => 's',
        '(ax|test)is'          => '$1es',
        '(octop|vir)us'        => '$1i',
        '(alias|status)'       => '$1es',
        '(bu)s'                => '$1ses',
        '(buffal|tomat)o'      => '$1oes',
        '([ti])um'             => '$1a',
        'sis'                  => 'ses',
        '(?:([^f])fe|([lr])f)' => '$1$2ves',
        '(hive)'               => '$1s',
        '([^aeiouy]|qu)y'      => '$1ies',
        '(x|ch|ss|sh)'         => '$1es',
        '(matr|vert|ind)ix|ex' => '$1ices',
        '([m|l])ouse'          => '$1ice',
        '^(ox)'                => '$1en',
        '(quiz)'               => '$1zes',
    );
    
    /**
     * 
     * Constructor.
     * 
     * @param string $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // append to the default arrays from configs
        $list = array('identical', 'irregular', 'many_to_one', 'one_to_many');
        foreach ($list as $key) {
            if ($this->_config[$key]) {
                $var = "_$key";
                $this->$var = array_merge(
                    $this->$var,
                    (array) $this->_config[$key]
                );
            }
        }

        // reverse rules so they are processed in LIFO order
        $this->_one_to_many = array_reverse($this->_one_to_many);
        $this->_many_to_one = array_reverse($this->_many_to_one);
    }
    
    /**
     * 
     * Returns a singular word as a plural.
     *
     * @param string $str A singular word.
     * 
     * @return string The plural form of the word.
     * 
     */
    public function oneToMany($str)
    {
        $key = strtolower($str);
        
        // look for words that are the same either way
        if (in_array($key, $this->_identical)) {
            return $str;
        }
        
        // look for irregular words
        foreach ($this->_irregular as $key => $val) {
            $find = "/(.*)$key\$/i";
            $repl = "\$1$val";
            if (preg_match($find, $str)) {
                return preg_replace($find, $repl, $str);
            }
        }
        
        // apply normal rules
        foreach($this->_one_to_many as $find => $repl) {
            $find = '/' . $find . '$/i';
            if (preg_match($find, $str)) {
                return preg_replace($find, $repl, $str);
            }
        }
        
        // couldn't find a plural form
        return $str;
    }
    
    /**
     * 
     * Returns a plural word as a singular.
     *
     * @param string $str A plural word.
     * 
     * @return string The singular form of the word.
     * 
     */
    public function manyToOne($str)
    {
        $key = strtolower($str);
        
        // look for words that are the same either way
        if (in_array($key, $this->_identical)) {
            return $str;
        }
        
        // look for irregular words
        // note that we flip singulars and plurals
        $list = array_flip($this->_irregular);
        foreach ($list as $key => $val) {
            $find = "/(.*)$key\$/i";
            $repl = "\$1$val";
            if (preg_match($find, $str)) {
                return preg_replace($find, $repl, $str);
            }
        }
        
        // apply normal rules
        foreach($this->_many_to_one as $find => $repl) {
            $find = '/' . $find . '$/i';
            if (preg_match($find, $str)) {
                return preg_replace($find, $repl, $str);
            }
        }
        
        // couldn't find a singular form
        return $str;
    }
    
    /**
     * 
     * Returns "under_score_word" as "underScoreWord" or "UnderScoreWord".
     * 
     * @param string $str The underscore word.
     * 
     * @param bool $studly If true, force the first letter to uppercase; if
     * false, force to lower case.  Default false.
     * 
     * @return string The word in camel-caps.
     * 
     */
    public function underToCamel($str, $studly = false)
    {
        $str = ucwords(str_replace('_', ' ', $str));
        $str = str_replace(' ', '', $str);
        if ($studly) {
            $str[0] = strtoupper($str[0]);
        } else {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }
    
    /**
     * 
     * Returns "camelCapsWord" and "CamelCapsWord" as "Camel_Caps_Word".
     * 
     * @param string $str The camel-caps word.
     * 
     * @return string The word with underscores in place of camel caps.
     * 
     */
    public function camelToUnder($str)
    {
        $str = preg_replace('/([a-z])([A-Z])/', '$1 $2', $str);
        $str = str_replace(' ', '_', ucwords($str));
        return $str;
    }
    
    /**
     * 
     * Returns "Class_Name" as "Class/Name.php".
     * 
     * @param string $str The class name.
     * 
     * @return string The class as a file name.
     * 
     */
    public function classToFile($str)
    {
        return str_replace('_', '/', $str) . '.php';
    }
}