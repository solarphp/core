<?php
/**
 * 
 * Locale file.  Returns the strings for a specific language.
 * 
 * @category Solar
 * 
 * @package Solar_Locale
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */
return array(
    
    // formatting codes and information
    'FORMAT_LANGUAGE'    => 'English',
    'FORMAT_COUNTRY'     => 'United States',
    'FORMAT_CURRENCY'    => '$%s', // printf()
    'FORMAT_DATE'        => '%b %d, %Y', // strftime(): Mar 19, 2005
    'FORMAT_TIME'        => '%r', // strftime: 12-hour am/pm
    
    // operation actions
    'OP_SAVE'            => 'Save',
    'OP_PREVIEW'         => 'Preview',
    'OP_CANCEL'          => 'Cancel',
    'OP_CREATE'          => 'Create',
    'OP_DELETE'          => 'Delete',
    'OP_RESET'           => 'Reset',
    'OP_NEXT'            => 'Next',
    'OP_PREVIOUS'        => 'Previous',
    'OP_SEARCH'          => 'Search',
    'OP_GO'              => 'Go!',
    
    // error messages
    'ERR_CONNECTION_FAILED'    => 'Connection failed.',
    'ERR_EXTENSION_NOT_LOADED' => 'Extension not loaded.',
    'ERR_FILE_NOT_FOUND'       => 'File not found.',
    'ERR_FILE_NOT_READABLE'    => 'File not readable or does not exist.',
    'ERR_METHOD_NOT_CALLABLE'  => 'Method not callable.',
    'ERR_METHOD_NOT_IMPLEMENTED' => 'Method not implemented.',
    'ERR_FORM'                 => 'Please correct the noted errors.',
    'ERR_INVALID'              => 'Invalid data.',
    
    // validation messages
    'VALID_ALPHA'        => 'Please use only the letters A-Z.',
    'VALID_ALNUM'        => 'Please use only letters (A-Z) and numbers (0-9).',
    'VALID_BLANK'        => 'This value must be blank.',
    'VALID_EMAIL'        => 'Please enter a valid email address.',
    'VALID_INKEYS'       => 'Please choose a different value.',
    'VALID_INLIST'       => 'Please choose a different value.',
    'VALID_INSCOPE'      => 'This value is not in the proper scope.',
    'VALID_INTEGER'      => 'Please use only whole numbers.',
    'VALID_ISODATE'      => 'Please enter a date in "yyyy-mm-dd" format.',
    'VALID_ISODATETIME'  => 'Please enter a date-time in "yyyy-mm-ddThh:ii:ss" format.',
    'VALID_ISOTIME'      => 'Please enter a time in "hh:ii:ss" format.',
    'VALID_MAX'          => 'Please enter a smaller value.',
    'VALID_MAXLENGTH'    => 'Please enter a shorter string.',
    'VALID_MIN'          => 'Please enter a larger value.',
    'VALID_MINLENGTH'    => 'Please enter a longer string.',
    'VALID_NONZERO'      => 'This value is not allowed to be zero.',
    'VALID_NOTBLANK'     => 'This value is not allowed to be blank.',
    'VALID_SEPWORDS'     => 'Please enter Please use only letters (A-Z), numbers (0-9), underscores(_), and separators.',
    'VALID_URI'          => 'Please enter a valid web address.',
    'VALID_WORD'         => 'Please use only letters (A-Z), numbers (0-9), and underscores(_).',
    
    // success messages
    'OK_SAVED'           => 'Saved.',
    
    // generic text
    'TEXT_LOGIN'         => 'Sign In',
    'TEXT_LOGOUT'        => 'Sign Out',
    'TEXT_AUTH_USERNAME' => 'Signed in as',
    
    // generic labels
    'LABEL_OP'           => 'Operation',
    'LABEL_USERNAME'     => 'Username',
    'LABEL_PASSWORD'     => 'Password',
);
?>