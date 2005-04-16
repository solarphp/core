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
	'VALID_SUMM'      => 'Please enter a short summary of the report.',
	'VALID_TYPE'      => 'Please select a valid bug type.',
	'VALID_QUEUE'     => 'Please select a valid queue for the bug.',
	'VALID_STATUS'    => 'Please select a valid progress status.',
	
	// processing errors
	'ERR_ID'          => 'Requested ID does not exist.',
	
	// report types
	'TYPE_BUG'        => 'Bug report',
	'TYPE_CRITICAL'   => 'Critical issue',
	'TYPE_EXAMPLE'    => 'Request for example',
	'TYPE_FEATURE'    => 'Request for feature',
	
	// status codes
	'STATUS_NEW'       => 'New',
	'STATUS_CONFIRMED' => 'Confirmed',
	'STATUS_ASSIGNED'  => 'Assigned',
	'STATUS_FEEDBACK'  => 'Feedback requested',
	'STATUS_RESOLVED'  => 'Resolved',
	'STATUS_DUPLICATE' => 'Duplicate',
	'STATUS_BOGUS'     => 'Bogus',
	'STATUS_WONTFIX'   => 'Will not fix',
	'STATUS_SUSPENDED' => 'Suspended',
	'STATUS_REOPENED'  => 'Re-opened',
	
	// form labels
	'LABEL_ID'        => 'Report ID',
	'LABEL_TS_NEW'    => 'First reported',
	'LABEL_TS_MOD'    => 'Last modified',
	'LABEL_SUMM'      => 'Short summary',
	'LABEL_TYPE'      => 'Report type',
	'LABEL_QUEUE'     => 'Queue',
	'LABEL_PRIORITY'  => 'Priority',
	'LABEL_USER_ID'   => 'Assigned to',
	'LABEL_STATUS'    => 'Progress status',
	
);
?>