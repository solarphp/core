<?php
/**
 * 
 * A single record from the "bookmarks" model.
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
class Solar_Model_Bookmarks_Record extends Solar_Model_Nodes_Record
{
    /**
     * 
     * Returns a pre-populated Solar_Form object.
     * 
     * Uses only these columns:  uri, subj, summ, tags_as_string, and pos.
     * 
     * @param array $cols Ignored.
     * 
     * @return Solar_Form
     * 
     */
    public function form($cols = null)
    {
        // force the columns to be shown in the form
        $cols = array(
            'uri'  => array(
                'attribs' => array(
                    'size' => 48,
                ),
            ),
            'subj' => array(
                'attribs' => array(
                    'size' => 48,
                ),
            ),
            'summ' => array(
                'type'    => 'textarea',
                'attribs' => array(
                    'rows' => 6,
                    'cols'  => 48,
                ),
            ),
            'tags_as_string' => array(
                'type' => 'text',
                'attribs' => array(
                    'size' => 48,
                ),
            ),
            'pos'  => array(
                'attribs' => array(
                    'size' => 3,
                ),
            ),
        );
        
        return parent::form($cols);
    }
}