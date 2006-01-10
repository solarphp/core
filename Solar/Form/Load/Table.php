<?php

/**
 * 
 * Class for loading form definitions from Solar_Sql_Table columns.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * Needed for loading validations.
 */
Solar::loadClass('Solar_Valid');

/**
 * 
 * Class for loading form definitions from Solar_Sql_Table columns.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_Form
 * 
 */

class Solar_Form_Load_Table extends Solar_Base {
    
    
    /**
     * 
     * Loads Solar_Form elements based on Solar_Sql_Table columns.
     * 
     * @access public
     * 
     * @param object $filename Path to form definition file.
     * 
     * @return object|false Solar_Form object, boolean false on error.
     *
     */
    
    public function fetch($table, $list = '*', $array_name = null) 
    {
        if (! $table instanceof Solar_Sql_Table) {
            return $this->_error(
                'ERR_FORM_LOAD_TABLE',
                array(),
                E_USER_WARNING
            );
        }
        
        // if not specified, set the array_name to the table name
        if (empty($array_name)) {
            $array_name = $table->name;
        }
        
        // all columns known by the table
        $all_cols = array_keys($table->col);
        
        // special condition: if looking for '*' columns,
        // get the list of all the table columns.
        if ($list == '*') {
            $list = $all_cols;
        } else {
            settype($list, 'array');
        }
        
        // loop through the list of requested columns and collect elements
        $elements = array();
        foreach ($list as $name => $info) {
            
            // if $name is integer, $info is just a column name,
            // and there is no added element info.
            if (is_int($name)) {
                $name = $info;
                $info = array();
            } else {
                settype($info, 'array');
            }
            
            // is the column name in the table?
            if (! in_array($name, $all_cols)) {
                continue;
            }
            
            // initial set of element info based on the table column
            $base = array(
                'name'     => $array_name . '[' . $name . ']',
                'type'     => null,
                'label'    => $table->locale(strtoupper("LABEL_$name")),
                'descr'    => $table->locale(strtoupper("DESCR_$name")),
                'value'    => $table->getDefault($name),
                'require'  => $table->col[$name]['require'],
                'disable'  => $table->col[$name]['primary'],
                'options'  => array(),
                'attribs'  => array(),
                'feedback' => array(),
                'valid'    => $table->col[$name]['valid'],
            );
            $info = array_merge($base, $info);
            
            // make primary keys hidden and disabled
            if ($table->col[$name]['primary']) {
                $info['type'] = 'hidden';
                $info['disable'] = true;
            }
            
            // pick an element type based on the column type
            if (empty($info['type'])) {
                // base the element type on the column type.
                switch ($table->col[$name]['type']) {
                
                case 'bool':
                    $info['type'] = 'checkbox';
                    break;
                    
                case 'clob':
                    $info['type'] = 'textarea';
                    break;
                    
                case 'date':
                case 'time':
                case 'timestamp':
                    $info['type'] = $table->col[$name]['type'];
                    break;
                    
                default:
                    
                    // make 'id' and '*_id' columns hidden
                    if ($name == 'id' || substr($name, -3) == '_id') {
                        $info['type'] = 'hidden';
                    }
                    
                    // if there is a validation for 'inList' or 'inKeys',
                    // make this a select element.
                    foreach ($info['valid'] as $v) {
                        if ($v[0] == 'inKeys' || $v[0] == 'inList') {
                            $info['type'] = 'select';
                            break;
                        }
                    }
                    
                    // if type is still empty, make it text.
                    if (empty($info['type'])) {
                        $info['type'] = 'text';
                    }
                    break;
                }
            }
            
            // add validations based on column type, but only if
            // not hidden or disabled
            if ($info['type'] != 'hidden' && ! $info['disable'] &&
                empty($info['valid'])) {
            
                $method = null;
                
                switch ($table->col[$name]['type']) {
                case 'date':
                    $method = 'isoDate';
                    break;
                case 'time':
                    $method = 'isoTime';
                    break;
                case 'timestamp':
                    $method = 'isoDateTime';
                    break;
                case 'smallint':
                case 'int':
                case 'bigint':
                    $method = 'integer';
                    break;
                }
                
                if ($method) {
                    $code = 'VALID_' . strtoupper($method);
                    $info['valid'] = array(
                        array(
                            $method,
                            Solar::locale('Solar', $code),
                            Solar_Valid::OR_BLANK
                        )
                    );
                }
            }
            
            // set up options for checkboxes if none specified
            if ($info['type'] == 'checkbox' && empty($info['options'])) {
                // look for 'inKeys' or 'inList' validation.
                foreach ($info['valid'] as $v) {
                    if ($v[0] == 'inKeys' || $v[0] == 'inList') {
                        $info['options'] = $v[2];
                        break;
                    }
                }
                // if still empty, set to 1,0
                if (empty($info['options'])) {
                    $info['options'] = array(1,0);
                }
            }
            
            // set up options for select and radio if none specified
            if (($info['type'] == 'select' || $info['type'] == 'radio') &&
                empty($info['options'])) {
                // look for 'inKeys' or 'inList' validation.
                foreach ($info['valid'] as $v) {
                    if ($v[0] == 'inKeys' || $v[0] == 'inList') {
                        $info['options'] = $v[2];
                        break;
                    }
                }
            }
            
            // for text elements, set up maxlength if none specified
            if ($info['type'] == 'text' &&
                empty($info['attribs']['maxlength']) && 
                $table->col[$name]['size'] > 0) {
                /** @todo Add +1 or +2 to 'size' for numeric types? */
                $info['attribs']['maxlength'] = $table->col[$name]['size'];
            }
            
            // if no label specified, set up based on element name
            if (empty($info['label'])) {
                $info['label'] = $info['name'];
            }
            
            // keep the element
            $elements[$info['name']] = $info;
        }
        
        return array(
            'attribs'  => array(),
            'elements' => $elements
        );
    }
}
?>