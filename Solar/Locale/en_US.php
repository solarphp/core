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
    'FORMAT_DEC_POINT'           => '.', // number_format
    'FORMAT_THOUSANDS_SEP'       => ',', // number_format
    'FORMAT_DATE'                => '%b %d, %Y', // strftime(): Mar 19, 2005
    'FORMAT_TIME'                => '%r', // strftime: 12-hour am/pm
    
    // action processes
    'PROCESS_ADD'                => 'Add',
    'PROCESS_CANCEL'             => 'Cancel',
    'PROCESS_CREATE'             => 'Create',
    'PROCESS_DELETE'             => 'Delete',
    'PROCESS_DELETE_CONFIRM'     => 'Delete (Confirm)',
    'PROCESS_EDIT'               => 'Edit',
    'PROCESS_GO'                 => 'Go!',
    'PROCESS_LOGIN'              => 'Sign In',
    'PROCESS_LOGOUT'             => 'Sign Out',
    'PROCESS_NEXT'               => 'Next',
    'PROCESS_PREVIEW'            => 'Preview',
    'PROCESS_PREVIOUS'           => 'Previous',
    'PROCESS_RESET'              => 'Reset',
    'PROCESS_SAVE'               => 'Save',
    'PROCESS_SEARCH'             => 'Search',
    
    // controller actions
    'ACTION_BROWSE'              => 'Browse',
    'ACTION_READ'                => 'Read',
    'ACTION_EDIT'                => 'Edit',
    'ACTION_ADD'                 => 'Add',
    'ACTION_DELETE'              => 'Delete',
    'ACTION_NOT_FOUND'           => 'Not Found',
    
    // exception error messages  
    'ERR_CONNECTION_FAILED'      => 'Connection failed.',
    'ERR_EXTENSION_NOT_LOADED'   => 'Extension not loaded.',
    'ERR_FILE_NOT_FOUND'         => 'File not found.',
    'ERR_FILE_NOT_READABLE'      => 'File not readable or does not exist.',
    'ERR_METHOD_NOT_CALLABLE'    => 'Method not callable.',
    'ERR_METHOD_NOT_IMPLEMENTED' => 'Method not implemented.',
    
    // success feedback messages
    'SUCCESS_FORM'               => 'Saved.',
    'SUCCESS_ADDED'              => 'Added.',
    'SUCCESS_DELETED'            => 'Deleted.',
    
    // failure feedback messages  
    'FAILURE_FORM'               => 'Not saved; please correct the noted errors.',
    'FAILURE_INVALID'            => 'Invalid data.',
    
    // pagers
    'PAGER_PREV'                 => 'Prev',
    'PAGER_NEXT'                 => 'Next',
    
    // generic text      
    'TEXT_AUTH_USERNAME'         => 'Signed in as',
    
    // generic form element labels  
    'LABEL_SUBMIT'               => 'Process',
    'LABEL_HANDLE'               => 'Username',
    'LABEL_PASSWD'               => 'Password',
    'LABEL_EMAIL'                => 'Email',
    'LABEL_MONIKER'              => 'Display Name',
    'LABEL_URI'                  => 'Website',
    'LABEL_CREATED'              => 'Created',
    'LABEL_UPDATED'              => 'Updated',
);