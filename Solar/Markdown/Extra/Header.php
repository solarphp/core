<?php
Solar::loadClass('Solar_Markdown_Plugin_Header');
class Solar_Markdown_Extra_Header extends Solar_Markdown_Plugin_Header {
    
    protected $_chars = '-={}#';
    
    public function parse($text)
    {
        // setext top-level
        $text = preg_replace_callback(
            '{ (^.+?) (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? [ \t]*\n=+[ \t]*\n+ }mx',
            array($this, '_parseTop'),
            $text
        );
    
        // setext sub-level
        $text = preg_replace_callback(
            '{ (^.+?) (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? [ \t]*\n-+[ \t]*\n+ }mx',
            array($this, '_parseSub'),
            $text
        );
        
        // atx
    	$text = preg_replace_callback(
    	    '{
    			^(\#{1,6})	# $1 = string of #\'s
    			[ \t]*
    			(.+?)		# $2 = Header text
    			[ \t]*
    			\#*			# optional closing #\'s (not counted)
    			(?:[ ]+\{\#([-_:a-zA-Z0-9]+)\}[ ]*)? # id attribute
    			\n+
    		}mx',
    		array($this, '_parseAtx'),
    		$text
    	);
		
        return $text;
    }
    
    protected function _parseTop($matches)
    {
        if (! empty($matches[2])) {
            $id = ' id="' . $this->_escape($matches[2]) . '"';
        } else {
            $id = '';
        }
        
        return "<h1$id>"
             . $this->_processSpans($matches[1])
             . "</h1>"
             . "\n\n";
    }
    
    protected function _parseSub($matches)
    {
        if (! empty($matches[2])) {
            $id = ' id="' . $this->_escape($matches[2]) . '"';
        } else {
            $id = '';
        }
        
        return "<h2$id>"
             . $this->_processSpans($matches[1])
             . "</h2>"
             . "\n\n";
    }
    
    protected function _parseAtx($matches)
    {
        if (! empty($matches[3])) {
            $id = ' id="' . $this->_escape($matches[3]) . '"';
        } else {
            $id = '';
        }
        
        $tag = 'h' . strlen($matches[1]); // h1, h2, h5, etc
        
        return "<$tag$id>"
             . $this->_processSpans($matches[2])
             . "</$tag>"
             . "\n\n";
    }
}
?>