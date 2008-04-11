<?php
/**
 * 
 * Validates that a file was uploaded.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateWord.php 2926 2007-11-09 16:25:44Z pmjones $
 * 
 */

/**
 * 
 * Validates that the value is an array of file-upload information, and
 * if a file is referred to, that is actually an uploaded file.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 */
class Solar_Filter_ValidateUpload extends Solar_Filter_Abstract {
    
    /**
     * 
     * Upload error codes matched with locale string keys.
     * 
     * @var array
     * 
     */
    protected $_error_invalid =  array(
        UPLOAD_ERR_INI_SIZE   => 'INVALID_UPLOAD_INI_SIZE',
        UPLOAD_ERR_FORM_SIZE  => 'INVALID_UPLOAD_FORM_SIZE',
        UPLOAD_ERR_PARTIAL    => 'INVALID_UPLOAD_PARTIAL',
        UPLOAD_ERR_NO_FILE    => 'INVALID_UPLOAD_NO_FILE',
        UPLOAD_ERR_NO_TMP_DIR => 'INVALID_UPLOAD_NO_TMP_DIR',
        UPLOAD_ERR_CANT_WRITE => 'INVALID_UPLOAD_CANT_WRITE',
        UPLOAD_ERR_EXTENSION  => 'INVALID_UPLOAD_EXTENSION',
    );

    /**
     * 
     * Validates that the value is an array of file-upload information, and
     * if a file is referred to, that is actually an uploaded file.
     * 
     * The required keys are 'error', 'name', 'size', 'tmp_name', 'type'. More
     * or fewer or different keys than this will return a "malformed" error.
     * 
     * If the upload is not required, and no file is uploaded, then it's still
     * valid as far as this method goes.
     * 
     * @param array $value An array of file-upload information.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateUpload($value)
    {
        // reset to the default invalid message after previous attempts
        $this->_resetInvalid();
        
        // check if it's required or not
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        // has to be an array
        if (! is_array($value)) {
            $this->_invalid = 'INVALID_UPLOAD_NOT_ARRAY';
            return false;
        }
        
        // presorted list of expected keys
        $expect = array('error', 'name', 'size', 'tmp_name', 'type');
        
        // sort the list of actual keys
        $actual = array_keys($value);
        sort($actual);
        
        // make sure the required and actual keys match up
        if ($expect != $actual) {
            $this->_invalid = 'INVALID_UPLOAD_ARRAY_MALFORMED';
            return false;
        }
        
        // was the upload explicitly ok?
        if ($value['error'] != UPLOAD_ERR_OK) {
            foreach ($this->_error_invalid as $error => $invalid) {
                if ($value['error'] == $error) {
                    $this->_invalid = $invalid;
                    return false;
                }
            }
            $this->_invalid = 'INVALID_UPLOAD_UNKNOWN_ERROR';
            return false;
        }
        
        // is it actually an uploaded file?
        if (! is_uploaded_file($value['tmp_name'])) {
            // nefarious happenings are afoot.
            $this->_invalid = 'INVALID_UPLOAD_NOT_UPLOADED_FILE';
            return false;
        }
        
        // looks like we're ok!
        return true;
    }
}