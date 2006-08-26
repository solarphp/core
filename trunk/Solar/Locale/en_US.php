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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
return array(
    
    // formatting codes and information
    'FORMAT_LANGUAGE'            => 'English',
    'FORMAT_COUNTRY'             => 'United States',
    'FORMAT_CURRENCY'            => '$%s', // printf()
    'FORMAT_DATE'                => '%b %d, %Y', // strftime(): Mar 19, 2005
    'FORMAT_TIME'                => '%r', // strftime: 12-hour am/pm
    
    // page submissions
    'SUBMIT_ADD'                 => 'Add',
    'SUBMIT_CANCEL'              => 'Cancel',
    'SUBMIT_CREATE'              => 'Create',
    'SUBMIT_DELETE'              => 'Delete',
    'SUBMIT_EDIT'                => 'Edit',
    'SUBMIT_GO'                  => 'Go!',
    'SUBMIT_LOGIN'               => 'Sign In',
    'SUBMIT_LOGOUT'              => 'Sign Out',
    'SUBMIT_NEXT'                => 'Next',
    'SUBMIT_PREVIEW'             => 'Preview',
    'SUBMIT_PREVIOUS'            => 'Previous',
    'SUBMIT_RESET'               => 'Reset',
    'SUBMIT_SAVE'                => 'Save',
    'SUBMIT_SEARCH'              => 'Search',
    
    // controller actions
    'ACTION_BROWSE'              => 'Browse',
    'ACTION_READ'                => 'Read',
    'ACTION_EDIT'                => 'Edit',
    'ACTION_ADD'                 => 'Add',
    'ACTION_DELETE'              => 'Delete',
    
    // exception error messages  
    'ERR_CONNECTION_FAILED'      => 'Connection failed.',
    'ERR_EXTENSION_NOT_LOADED'   => 'Extension not loaded.',
    'ERR_FILE_NOT_FOUND'         => 'File not found.',
    'ERR_FILE_NOT_READABLE'      => 'File not readable or does not exist.',
    'ERR_METHOD_NOT_CALLABLE'    => 'Method not callable.',
    'ERR_METHOD_NOT_IMPLEMENTED' => 'Method not implemented.',
    
    // validation messages (used when validation fails)
    'VALID_ALPHA'                => 'Please use only the letters A-Z.',
    'VALID_ALNUM'                => 'Please use only letters (A-Z) and numbers (0-9).',
    'VALID_BLANK'                => 'This value must be blank.',
    'VALID_EMAIL'                => 'Please enter a valid email address.',
    'VALID_INKEYS'               => 'Please choose a different value.',
    'VALID_INLIST'               => 'Please choose a different value.',
    'VALID_INTEGER'              => 'Please use only whole numbers.',
    'VALID_ISODATE'              => 'Please enter a date in "yyyy-mm-dd" format.',
    'VALID_ISOTIMESTAMP'         => 'Please enter a timestamp in "yyyy-mm-ddThh:ii:ss" format.',
    'VALID_ISOTIME'              => 'Please enter a time in "hh:ii:ss" format.',
    'VALID_MAX'                  => 'Please enter a smaller value.',
    'VALID_MAXLENGTH'            => 'Please enter a shorter string.',
    'VALID_MIN'                  => 'Please enter a larger value.',
    'VALID_MINLENGTH'            => 'Please enter a longer string.',
    'VALID_NOTZERO'              => 'This value is not allowed to be zero.',
    'VALID_NOTBLANK'             => 'This value is not allowed to be blank.',
    'VALID_RANGE'                => 'This value is outside the allowed range.',
    'VALID_RANGELENGTH'          => 'This value is too short, or too long.',
    'VALID_SCOPE'                => 'This value is not in the proper scope.',
    'VALID_SEPWORDS'             => 'Please use only letters (A-Z), numbers (0-9), underscores(_), and separators.',
    'VALID_URI'                  => 'Please enter a valid web address.',
    'VALID_WORD'                 => 'Please use only letters (A-Z), numbers (0-9), and underscores(_).',
    
    // success feedback messages
    'SUCCESS_FORM'               => 'Saved.',
    
    // failure feedback messages  
    'FAILURE_FORM'               => 'Not saved; please correct the noted errors.',
    'FAILURE_INVALID'            => 'Invalid data.',
    
    // generic text      
    'TEXT_AUTH_USERNAME'         => 'Signed in as',
    
    // generic form element labels  
    'LABEL_SUBMIT'               => 'Action',
    'LABEL_HANDLE'               => 'Username',
    'LABEL_PASSWD'               => 'Password',
    'LABEL_EMAIL'                => 'Email',
    'LABEL_MONIKER'              => 'Display Name',
    'LABEL_URI'                  => 'Website',
);
?>