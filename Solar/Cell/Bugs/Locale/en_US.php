<?php

/**
* 
* Locale file.  Returns the strings for a specific language.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Bugs
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

return array(
	
	// validation messages
	'VALID_SUMM'   => 'Please enter a short summary of the report.',
	'VALID_EMAIL'  => 'Please use a valid email address.',
	'VALID_TYPE'   => 'Please select a valid bug type.',
	'VALID_PACK'   => 'Please select a valid package for the bug.',
	'VALID_STATUS' => 'Please select a valid progress status.',
	'VALID_DESCR'  => 'Please enter a full description.',
	'VALID_OS'     => 'Please enter an operating system.',
	'VALID_VER'    => 'Please type in the PHP version.',
	
	// process errors,
	'ERR_ID'       => 'Requested ID does not exist.',
	
	// report types
	'TYPE_BUG'        => 'Bug report',
	'TYPE_CRITICAL'   => 'Critical issue',
	'TYPE_EXAMPLE'    => 'Request for example',
	'TYPE_FEATURE'    => 'Request for feature',
	
	// status codes
	'STATUS_NEW'      => 'New',
	'STATUS_ACCEPT'   => 'Accepted/verified',
	'STATUS_FEEDBACK' => 'Feedback request',
	'STATUS_FIXED'    => 'Fixed',
	'STATUS_DUPE'     => 'Duplicate',
	'STATUS_BOGUS'    => 'Bogus',
	'STATUS_WONTFIX'  => 'Will not fix',
	'STATUS_SUSPEND'  => 'Suspended',
	'STATUS_REOPEN'   => 'Re-opened',
	
	// form labels
	'LABEL_ID'        => 'Bug ID',
	'LABEL_SUMM'      => 'Short summary',
	'LABEL_TS_NEW'    => 'First reported',
	'LABEL_TS_MOD'    => 'Last modified',
	'LABEL_TYPE'      => 'Report type',
	'LABEL_STATUS'    => 'Progress status',
	'LABEL_PACK'      => 'Class/package',
	'LABEL_OS'        => 'Operating system',
	'LABEL_VER'       => 'PHP version',
	'LABEL_USER_ID'   => 'Assigned to',
	'LABEL_EMAIL'     => 'Your email',
	'LABEL_DESCR'     => 'Description',
);
?>