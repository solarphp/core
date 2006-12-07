<?php
/**
 * 
 * Parses a single PHPDoc comment block into summary, narrative, and
 * technical portions.
 * 
 * @category Solar
 * 
 * @package Solar_Docs
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
 * Parses a single PHPDoc comment block into summary, narrative, and
 * technical portions.
 * 
 * http://java.sun.com/j2se/javadoc/writingdoccomments/index.html#format
 * 
 * Supported technical tags are ...
 * 
 * For classes ...
 * 
 *     @category name                # category for the package
 *     @package name                 # class package name
 *     @subpackage name              # class subpackage name
 * 
 * For properties ...
 * 
 *     @var type [summary]           # class property
 * 
 * For methods ...
 * 
 *     @param type [$name] [summary] # method parameter
 *     @return type [summary]        # method return
 *     @throws class [summary]       # exceptions thrown by method
 *     @exception class [summary]    # alias to @throws
 * 
 * General-purpose ...
 * 
 *     @see name                     # "see also" this element name
 *     @todo summary                 # todo item
 * 
 * @category Solar
 * 
 * @package Solar_Docs
 * 
 */
class Solar_Docs_Phpdoc extends Solar_Base {
    
    /**
     * 
     * Where the technical information from block tags is stored.
     * 
     * @var array
     * 
     */
    protected $_info = array();
    
    /**
     * 
     * Returns docblock comment parsed into summary, narrative, and
     * technical information portions.
     * 
     * @param string $block The docblock comment text.
     * 
     * @return array An array with keys 'summ', 'narr', and 'tech' 
     * corresponding to the summary, narrative, and technical portions
     * of the docblock.
     * 
     */
    public function parse($block)
    {
        // clear out prior info
        $this->_info = array();
        
        // fix line-endings from windows
        $block = str_replace("\r\n", "\n", $block);
        
        // fix line-endings from mac os 9 and previous
        $block = str_replace("\r", "\n", $block);
        
        // remove the leading comment indicator (slash-star-star)
        $block = preg_replace('/^\s*\/\*\*\s*$/m', '', $block);
        
        // remove the trailing comment indicator (star-slash)
        $block = preg_replace('/^\s*\*\/\s*$/m', '', $block);
        
        // remove the star (and optionally one space) leading each line
        $block = preg_replace('/^\s*\*( )?/m', '', $block);
        
        // wrap with exactly one beginning and ending newline
        $block = "\n" . trim($block) . "\n";
        
        // find narrative and technical portions
        $pos = strpos($block, "\n@");
        if ($pos === false) {
            // apparently no technical section
            $narr = $block;
            $tech = '';
        } else {
            // there appears to be a technical section
            $narr = trim(substr($block, 0, $pos));
            $tech = trim(substr($block, $pos));
        }
        
        // load the formal technical info array
        $this->_loadInfo($tech);
        
        // now take the summary line off the narrative
        $dot = strpos($narr, ".");
        $new = strpos($narr, "\n");
        if ($dot !== false) {
            // summary is first sentence
            $pos = $dot + 1;
        } elseif ($new !== false) {
            // summary is first line
            $pos = $new;
        } else {
            // appears there is no summary line, go only
            // with the narrative.
            $pos = 0;
        }
        
        // return the summary, narrative, and technical portions
        return array(
            'summ' => trim(substr($narr, 0, $pos)),
            'narr' => trim(substr($narr, $pos + 1)),
            'tech' => $this->_info,
        );
    }
    
    /**
     * 
     * Gets the technical information from a docblock comment.
     * 
     * @param string $tech The technical portion of a docblock.
     * 
     * @return array An array of technical information.
     * 
     */
    protected function _loadInfo($tech)
    {
        $tech = "\n" . trim($tech) . "\n";
        
        // split into elements at each "\n@"
        $split = preg_split(
            '/\n\@/m',
            $tech,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        
        // process each element
        foreach ($split as $line) {
            $line = trim($line);
            $found = preg_match('/(\w+)\s+(.*)/ms', $line, $matches);
            if (! $found) {
                continue;
            }
            
            $func = "parse" . ucfirst($matches[1]);
            $line = str_replace("\n", ' ', $matches[2]);
            if (is_callable(array($this, $func))) {
                $this->$func($line);
            }
        }
    }
    
    /**
     * 
     * Parses an @param line into $this->_info.
     * 
     * Multiple @param tags are allowed.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseParam($line)
    {
        // string|array $varname Summary or description.
        // string|array $varname
        // string|array Summary or description.
        preg_match('/(\S+)?((\s+\&?\$)(\S+))?((\s+)(.*))?/', $line, $matches);
        
        if (! $matches) {
            return;
        }
        
        // do we have a params array?
        if (empty($this->_info['param'])) {
            $this->_info['param'] = array();
        }
        
        // variable type
        $type = $matches[1];
        
        // if no variable name, name for the param count
        if (empty($matches[4])) {
            $name = count($this->_info['param']);
        } else {
            $name = $matches[4];
        }
        
        // always need a summary element
        if (empty($matches[7])) {
            $summ = '';
        } else {
            $summ = $matches[7];
        }
        
        // save the param
        $this->_info['param'][$name] = array(
            'type' => $type,
            'summ' => $summ,
        );
    }
    
    /**
     * 
     * Parses an @return line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseReturn($line)
    {
        // string|array summary
        $parts = $this->_2part($line);
        if ($parts) {
            $this->_info['return'] = $parts;
        }
    }
    
    /**
     * 
     * Parses an @todo line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseTodo($line)
    {
        // @todo (multi)
        if (! empty($this->_info['todo'])) {
            $this->_info['todo'] = array();
        }
        $this->_info['todo'][] = $line;
    }
    
    /**
     * 
     * Parses an @see line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseSee($line)
    {
        // @see (multi)
        if (! empty($this->_info['see'])) {
            $this->_info['see'] = array();
        }
        $this->_info['see'][] = $line;
    }
    
    /**
     * 
     * Parses an @var line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseVar($line)
    {
        // @var (single)
        // string|array summary
        $parts = $this->_2part($line);
        if ($parts) {
            $this->_info['var'] = $parts;
        }
    }
    
    /**
     * 
     * Parses an @throws line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseThrows($line)
    {
        // @throws (multiple)
        // Class_Name summary
        $parts = $this->_2part($line);
        if ($parts) {
            $this->_info['throws'][] = $parts;
        }
    }
    
    /**
     * 
     * Parses an @exception line into $this->_info; alias for @throws.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseException($line)
    {
        return $this->parseThrows($line);
    }
    
    /**
     * 
     * Parses an @category line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseCategory($line)
    {
        $this->_info['category'] = $this->_1part($line);
    }
    
    /**
     * 
     * Parses an @package line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parsePackage($line)
    {
        $this->_info['package'] = $this->_1part($line);
    }
    
    /**
     * 
     * Parses an @subpackage line into $this->_info.
     * 
     * @param string $line The block line.
     * 
     * @return void
     * 
     */
    public function parseSubpackage($line)
    {
        $this->_info['subpackage'] = $this->_1part($line);
    }
    
    /**
     * 
     * Parses a one-part block line.
     * 
     * Strips everything after the first space.
     * 
     * @param string $line The block line.
     * 
     * @return string
     * 
     */
    protected function _1part($line)
    {
        return preg_replace('/^(\S+)(\s.*)/', '$1', trim($line));
    }
    
    /**
     * 
     * Parses a two-part block line.
     * 
     * @param string $line The block line.
     * 
     * @return array An array with keys 'type' (the first part)
     * and 'summ' (the second part).
     * 
     */
    protected function _2part($line)
    {
        preg_match('/([\S]+)((\s+)(.*))?/', $line, $matches);
        if (empty($matches)) {
            return array();
        }
        if (empty($matches[4])) {
            $matches[4] = '';
        }
        return array(
            'type' => $matches[1],
            'summ' => $matches[4],
        );
    }
}

/**
 * 
 * WHAT WE PROBABLY WILL SUPPORT
 * 
 * @author name <author@email>
 * @copyright name date
 * @deprecated summary
 * @deprec summary
 * @example /path/to/example
 * @license href name text
 * @link href text
 * @since version|date
 * @staticvar name type summary
 * @version version
 * 
 * WHAT WE PROBABLY WILL NOT SUPPORT
 * 
 * @access       public or private
 * @global       type $globalvarname 
 *  or
 * @global       type description of global variable usage in a function
 * @name         procpagealias
 *  or
 * @name         $globalvaralias
 * @magic        phpdoc.de compatibility
 * @internal     private information for advanced developers only
 * @static       static method or property
 * @ignore
 * {@code}
 * {@docRoot}
 * {@inheritDoc}
 * {@link}
 * {@linkplain}
 * {@literal}
 * {@value}
 * 
 */
