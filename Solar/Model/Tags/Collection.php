<?php
/**
 * 
 * A collection of records from the "tags" model.
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
class Solar_Model_Tags_Collection extends Solar_Model_Collection
{
    /**
     * 
     * Gets the names of all tags in this collection as an array.
     * 
     * @return array
     * 
     */
    public function getNames()
    {
        // use a clone so we don't screw up other iterators
        $clone = clone($this);
        
        // build the list
        $list = array();
        foreach ($clone as $tag) {
            $list[] = $tag->name;
        }
        
        // done!
        return $list;
    }
}