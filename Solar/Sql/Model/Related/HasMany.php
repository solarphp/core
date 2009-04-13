<?php
/**
 * 
 * Represents the characteristics of a relationship where a native model
 * "has many" of a foreign model.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Sql_Model_Related_HasMany extends Solar_Sql_Model_Related_ToMany
{
    /**
     * 
     * Sets the relationship type.
     * 
     * @return void
     * 
     */
    protected function _setType()
    {
        $this->type = 'has_many';
    }
    
    public function save($native)
    {
        $foreign = $native->{$this->name};
        if (! $foreign) {
            return;
        }
        
        // set the foreign_col on each foreign record to the native value
        foreach ($foreign as $record) {
            $record->{$this->foreign_col} = $native->{$this->native_col};
        }
        
        $foreign->save();
    }
}
