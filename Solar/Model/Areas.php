<?php
/**
 * 
 * Broad content areas equivalent to logical namespaces.
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id: Areas.php 752 2006-02-04 18:59:34Z pmjones $
 * 
 */

/**
 * Solar_Sql_Table
 */
Solar::loadClass('Solar_Sql_Table');

/**
 * 
 * Broad content areas equivalent to logical namespaces.
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 */
class Solar_Model_Areas extends Solar_Sql_Table {
    
    /**
     * 
     * Schema setup.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        // the table name
        $this->_name = 'areas';
        
        // the area name
        $this->_col['name'] = array(
            'type'    => 'varchar',
            'size'    => 127,
            'require' => true,
            'valid'   => 'word',
        );
        
        // the user who owns this area
        $this->_col['owner_handle'] = array(
            'type'    => 'varchar',
            'size'    => 255,
        );
        
        // freeform area "subject" or title
        $this->_col['subj'] = array(
            'type'    => 'varchar',
            'size'    => 255,
        );
        
        // serialized preferences
        $this->_col['prefs'] = array(
            'type'    => 'clob',
        );
        
        
        // keys and indexes
        $this->_idx = array(
            'name'         => 'unique',
            'owner_handle' => 'normal',
        );
    }
}
?>