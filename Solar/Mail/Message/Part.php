<?php
/**
 * 
 * Represents one MIME part of a Solar_Mail_Message.
 * 
 * Refactored and modified from Zend_Mail_Message and related classes.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
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
 * Represents one MIME part of a Solar_Mail_Message.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
 * 
 */
class Solar_Mail_Message_Part {
    
    /**
     * 
     * The character set for this part.
     * 
     * @var string
     * 
     */
    public $charset = 'utf-8';
    
    /**
     * 
     * The CRLF sequence for this part.
     * 
     * @var string
     * 
     */
    public $crlf = "\r\n";
    
    /**
     * 
     * The Content-Disposition for this part.
     * 
     * Typically 'inline' or 'attachment'.
     * 
     * @var string
     * 
     */
    public $disposition = 'attachment';
    
    /**
     * 
     * The Content-Transfer-Encoding for this part.
     * 
     * @var string
     * 
     */
    public $encoding = 'base64';
    
    /**
     * 
     * When the part represents a file, use this as the filename.
     * 
     * @var string
     * 
     */
    public $filename = null;
    
    /**
     * 
     * The Content-Type for this part.
     * 
     * @var string
     * 
     */
    public $type = 'application/octet-stream';
    
    /**
     * 
     * The body content for this part.
     * 
     * @var string
     * 
     */
    public $content = null;
    
    public $boundary = null;
    
    /**
     * 
     * Array of custom headers for this part.
     * 
     * @var array
     * 
     */
    protected $_headers = array();
    
    /**
     * 
     * Sets (or resets) one header in the part.
     * 
     * You can only set one label to one value; you can't have multiple
     * repetitions of the same label to get multiple values.
     * 
     * @param string $label The header label.
     * 
     * @param string $value The header value.
     * 
     */
    public function setHeader($label, $value)
    {
        // sanitize the header label
        $label = Solar_Mail_Encoding::headerLabel($label);
        
        // not allowed to add headers for these labels
        $list = array('content-type', 'content-transfer-encoding',
            'content-disposition');
        if (in_array(strtolower($label), $list)) {
            throw $this->_exception('ERR_ADD_STANDARD_HEADER');
        }
        
        // save the label and value
        $this->_headers[$label] = $value;
    }
    
    /**
     * 
     * Returns the headers, a newline, and the content, all as a single block.
     * 
     * @return string
     * 
     */
    public function fetch()
    {
        return $this->fetchHeaders()
             . $this->crlf
             . $this->fetchContent();
    }
    
    /**
     * 
     * Returns all the headers as a string.
     * 
     * @return string
     * 
     */
    public function fetchHeaders()
    {
        // start with all the "custom" headers.
        // we will apply header-value encoding at the end.
        $headers = $this->_headers;
        
        // Content-Type:
        $content_type = $this->type;
        
        if ($this->charset) {
            $content_type .= '; charset="' . $this->charset . '"';
        }
        
        if ($this->boundary) {
            $content_type .= ';' . $this->crlf
                           . ' boundary="' . $this->boundary . '"';
        }
        
        $headers['Content-Type'] = $content_type;
        
        // Content-Disposition:
        if ($this->disposition) {
            $disposition = $this->disposition;
            if ($this->filename) {
                $disposition .= '; filename="' . $this->filename . '"';
            }
            $headers['Content-Disposition'] = $disposition;
        }
        
        // Content-Transfer-Encoding:
        if ($this->encoding) {
            $headers['Content-Transfer-Encoding'] = $this->encoding;
        }

        // now loop through all the headers and build the header block,
        // using header-value encoding as we go.
        $output = '';
        foreach ($headers as $label => $value) {
            $value = Solar_Mail_Encoding::headerValue(
                $label, $value, $this->charset, $this->crlf
            );
            $output .= $label . ': ' . $value . $this->crlf;
        }
        
        return $output;
    }
    
    /**
     * 
     * Returns the body content of this part with the proper encoding.
     * 
     * @return string
     * 
     */
    public function fetchContent()
    {
        $content = Solar_Mail_Encoding::apply(
            $this->encoding,
            $this->content,
            $this->crlf
        );
        
        return $content;
    }
}
