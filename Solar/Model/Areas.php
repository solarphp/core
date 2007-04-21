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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

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
        
        // area summary description or tagline
        $this->_col['summ'] = array(
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
    
    /**
     * 
     * Returns one area row by name.
     * 
     * @param string $name The area name to fetch by.
     * 
     * @return Solar_Sql_Row
     * 
     */
    public function fetchByName($name)
    {
        $where = array('name = ?' => $name);
        return $this->select('row', $where);
    }
    
    /**
     * 
     * Returns an array of area names and titles.
     * 
     * @param string $name The area name to fetch by.
     * 
     * @return array
     * 
     */
    public function fetchAllNames()
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->from($this, array('name', 'subj'));
        return $select->fetch('pairs');
    }
}
