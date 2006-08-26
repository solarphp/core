<?php
Solar::loadClass('Solar_Test_Bench');

class Bench_Solar_Dequote extends Solar_Test_Bench {

    protected $_haystack;
    protected $_valid_callbacks = array(
        'uninitialized',
        'loading',
        'loaded',
        'interactive',
        'complete',
        'failure',
        'success'
    );
    protected $_deQuote;
    
    
    
    public function __construct()
    {
        $h = <<< HAYSTACK
{"parameters":Form.serialize('foo'),"asynchronous":true,"onSuccess":function(t) { new Effect.Highlight(el, {"duration":1});},"on404":function(t) { alert('Error 404: location not found'); },"onFailure":function(t) { alert('Ack!'); },"requestHeaders":["X-Solar-Version","@package_version@","X-Foo","Bar"]}
HAYSTACK;
        $this->_haystack = $h;

        $codes = range(100, 599);
        $this->_valid_callbacks = array_merge($this->_valid_callbacks, $codes);

        $deQuote = array_map(
            create_function(
                '$val',
                'return "on".ucfirst($val);'),
                $this->_valid_callbacks);
        $deQuote[] = 'parameters';
        
        $this->_deQuote = $deQuote;

    }

    public function benchWithoutSModifier()
    {
        $keys = $this->_deQuote;
        $encoded = $this->_haystack;
        
        foreach ($keys as $key) {
            $pattern = "/(\"".$key."\"\:)(\".*(?:[^\\\]\"))/U";
            $encoded = preg_replace_callback(
                $pattern,
                create_function(
                    '$matches',
                    'return $matches[1].stripslashes(substr($matches[2], 1, -1));'),
                $encoded
            );
        }            
    }
    
    public function benchWithoutCreateFunction()
    {
        $keys = $this->_deQuote;
        $encoded = $this->_haystack;
        
        foreach ($keys as $key) {
            $pattern = "/(\"".$key."\"\:)(\".*(?:[^\\\]\"))/U";
            $encoded = preg_replace_callback(
                $pattern,
                array($this, '_stripvalueslashes'),
                $encoded
            );
        }            
    }    

    public function benchWithSModifier()
    {
        $keys = $this->_deQuote;
        $encoded = $this->_haystack;
        
        foreach ($keys as $key) {
            $pattern = "/(\"".$key."\"\:)(\".*(?:[^\\\]\"))/US";
            $encoded = preg_replace_callback(
                $pattern,
                create_function(
                    '$matches',
                    'return $matches[1].stripslashes(substr($matches[2], 1, -1));'),
                $encoded
            );
        }            
    }

    public function benchWithoutCreateFunctionWithSModifier()
    {
        $keys = $this->_deQuote;
        $encoded = $this->_haystack;
        
        foreach ($keys as $key) {
            $pattern = "/(\"".$key."\"\:)(\".*(?:[^\\\]\"))/US";
            $encoded = preg_replace_callback(
                $pattern,
                array($this, '_stripvalueslashes'),
                $encoded
            );
        }            
    }    
    
    protected function _stripvalueslashes($matches)
    {
        return $matches[1].stripslashes(substr($matches[2], 1, -1));
    }

}?>