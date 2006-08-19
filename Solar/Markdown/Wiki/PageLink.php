<?php

Solar::loadClass('Solar_Markdown_Plugin');

class Solar_Markdown_Wiki_PageLink extends Solar_Markdown_Plugin {
    
    protected $_is_span = true;
    
    // list of which pages exist (true) and which don't (false)
    protected $_pages;
    
    // saved data for each link in the text
    protected $_links;
    
    // count of all links
    protected $_count = 0;
    
    // this class name
    protected $_class;
    
    // attribs for anchor links. note that 'href' is special, in that
    // it is an sprintf() format string.
    protected $_attribs = array(
        
        // pages that exist
        'read' => array(
            'href' => 'index.php/wiki/read/Main/%s'
        ),
        
        // pages that do not exist
        'add' => array(
            'href' => 'index.php/wiki/add/Main/%s'
        ),
    );
    
    protected $_check_pages = false;
    
    public function setCheckPagesCallback($callback)
    {
        protected $_check_pages = $callback;
    }
    
    public function setAttrib($type, $key, $val)
    {
        $this->_attribs[$type][$key] = $val;
    }
    
    public function setAttribs($type, $list)
    {
        $this->_attribs[$type] = $list;
    }
    
    // gets the list of pages linked-to
    public function getPages()
    {
        return array_keys($this->_pages);
    }
    
    public function __construct($config)
    {
        parent::__construct($config);
        $this->_class = get_class($this);
    }
    
    public function reset()
    {
        parent::reset();
        $this->_links = array();
        $this->_pages = array();
        $this->_count = 0;
    }
    
    public function parse($text)
    {
        // [[wiki page]]
        // [[wiki page #anchor]]
        // [[wiki page]]s
        // [[wiki page | display this instead]]
        $regex = '/\[\[(.*?)(\#.*?)?(\|.*?)?\]\](\S*?)/';
        return preg_replace_callback(
            $regex,
            array($this, '_parse'),
            $text
        );
    }
    
    protected function _parse($matches)
    {
        $page = $matches[1];
        $frag = empty($matches[2]) ? null  : trim($matches[2]);
        $text = empty($matches[3]) ? $page : trim($matches[3]);
        $atch = empty($matches[4]) ? null  : trim($matches[4]);
        
        // normalize the page name
        $norm = $this->_normalize($page);
        
        // assume the page exists
        $this->_pages[$norm] = true;
        
        // save the link
        $this->_links[$this->_count] = array(
            'norm' => $norm,
            'page' => $page,
            'frag' => $frag,
            'text' => $text,
            'atch' => $atch,
        );
        
        // generate an escaped WikiLink token to be replaced at
        // cleanup() time with real HTML.
        $key = $this->_class . ':' . $this->_count ++;
        return "\x1B$key\x1B";
    }
    
    protected function _normalize($page)
    {
        // trim, force only the first letter to upper-case (leaving all
        // other characters alone), and then replace all whitespace
        // runs with a single underscore.
        return preg_replace('/\s+/', '_', ucfirst(trim($page)));
    }
    
    public function cleanup($text)
    {
        // first, update $this->_pages against the data store to see
        // which pages exist and which do not.
        if ($this->_check_pages) {
            $this->_pages = call_user_func($_check_pages, $this->_pages);
        }
        
        // now go through and replace tokens
        $regex = "/\x1B{$this->_class}:(.*?)\x1B/";
        $text = preg_replace_callback(
            $regex,
            array($this, '_cleanup'),
            $text
        );
    }
    
    protected function _cleanup($matches)
    {
        $key = $matches[1];
        $tmp = $this->_links[$key];
        
        // normalized page name
        $norm = $tmp['norm'];
        
        // page name as entered
        $page = $tmp['page'];
        
        // anchor "#fragment"
        $frag = $tmp['frag'];
        
        // optional display text
        $text = $tmp['text'];
        
        // optional attached text outside the link
        $atch = $tmp['atch'];
        
        // make sure the page is listed; the check-pages callback
        // may not have populated it back.
        if (empty($this->_pages[$norm])) {
            $this->_pages[$norm] = false;
        }
        
        // use "read" or "add" attribs?
        if ($this->_pages[$norm]) {
            // page exists
            $attribs = $this->_attribs['read'];
        } else {
            // page does not exist
            $attribs = $this->_attribs['add'];
        }
        
        // make sure we have an href attrib
        if (empty($attribs['href'])) {
            $attribs['href'] = '%s';
        }
        
        // build the opening <a href="" portion of the tag.
        $html = '<a href="'
              . $this->_escape(sprintf($attribs['href'], $norm . $frag))
              . '"';
              
        // add attributes and close the opening tag
        unset($attribs['href']);
        foreach ($attribs as $key => $val) {
            $key = $this->_escape($key);
            $val = $this->_escape($val);
            $html .= " $key=\"$val\"";
        }
        $html .= ">";
        
        // add the escaped the display text and close the tag
        $html .= $this->_escape($text . $atch) . "</a>";
        
        // done!
        return $html;
    }
}
?>