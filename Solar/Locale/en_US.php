<?php

/**
* 
* Locale file.  Returns the strings for a specific language.
* 
* @category Solar
* 
* @package Solar
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
	'FORMAT_LANGUAGE'   => 'English',
	'FORMAT_COUNTRY'    => 'United States',
	'FORMAT_CURRENCY'   => '$%s', // printf()
	'FORMAT_DATE'       => '%b %d, %Y', // strftime(): Mar 19, 2005
	'FORMAT_TIME'       => '%r', // strftime: 12-hour am/pm
	
	// operation actions
	'OP_SAVE'       => 'Save',
	'OP_PREVIEW'    => 'Preview',
	'OP_CANCEL'     => 'Cancel',
	'OP_RESET'      => 'Reset',
	'OP_NEXT'       => 'Next',
	'OP_PREVIOUS'   => 'Previous',
	'OP_SEARCH'     => 'Search',
	'OP_GO'         => 'Go!',
	
	// error messages
	'ERR_FORM'      => 'Please correct the noted errors.',
	'ERR_FILE_FIND' => 'Cannot find file.',
	'ERR_FILE_OPEN' => 'Cannot open file.',
	'ERR_FILE_READ' => 'Cannot read file.',
	'ERR_EXTENSION' => 'Extension not loaded.',
	'ERR_CONNECT'   => 'Connection failed.',
	'ERR_INVALID'   => 'Invalid data.',
	
	// success messages
	'OK_SAVED'      => 'Saved.',
);
?>