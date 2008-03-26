<?php
/**
 * 
 * Class for loading form definitions from Solar_Sql_Model columns.
 * 
 * @category Solar
 * 
 * @package Solar_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Form_Load_Model extends Solar_Base {
    
    /**
     * 
     * Loads Solar_Form elements based on Solar_Sql_Model columns.
     * 
     * @param Solar_Sql_Model $model Load form elements from this model object.
     * 
     * @param array $list Which model columns to load as form elements, default '*'.
     * 
     * @param string $array_name Load the model columns as elements of this
     * array-name within the form.
     * 
     * @return array An array of form attributes and elements.
     * 
     */
    public function fetch($model, $list = '*', $array_name = null)
    {
        // make sure it's a Model
        if (! $model instanceof Solar_Sql_Model) {
            throw $this->_exception('ERR_NOT_MODEL_OBJECT');
        }
        
        // if not specified, set the array_name to the model name
        if (empty($array_name)) {
            $array_name = $model->model_name;
        }
        
        // table columns in the model
        $cols = $model->table_cols;
        
        // special condition: if looking for '*' columns,
        // set the list to all the model columns.
        if ($list == '*') {
            if ($model->fetch_cols) {
                // use the fetch columns
                $list = $model->fetch_cols;
            } else {
                // use all columns
                $list = array_keys($model->table_cols);
            }
            
            // flip around so we can unset easier
            $list = array_flip($list);
            
            // remove special columns
            unset($list[$model->primary_col]);
            unset($list[$model->created_col]);
            unset($list[$model->updated_col]);
            unset($list[$model->inherit_col]);
            
            // remove sequence columns
            foreach ($model->sequence_cols as $key => $val) {
                unset($list[$key]);
            }
            
            // done!
            $list = array_keys($list);
        } else {
            settype($list, 'array');
        }
        
        // default values
        $default = $model->fetchNew();
        
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
            
            // is the column name in the model table?
            if (empty($cols[$name])) {
                // not in the table, fake some elements
                $cols[$name] = array(
                    'primary' => false,
                    'require' => false,
                    'type'    => 'text',
                    'size'    => false,
                );
            }
            
            // initial set of element info based on the model column
            $base = array(
                'name'    => $array_name . '[' . $name . ']',
                'type'    => null,
                'label'   => $model->locale(strtoupper("LABEL_$name")),
                'descr'   => $model->locale(strtoupper("DESCR_$name")),
                'value'   => $default[$name],
                'require' => $cols[$name]['require'],
                'disable' => $cols[$name]['primary'],
                'options' => array(),
                'attribs' => array(),
                'filters' => array(),
                'invalid' => array(),
            );
            
            $info = array_merge($base, $info);
            
            // use the filters here
            if (! empty($model->filters[$name])) {
                $filters = $model->filters[$name];
            } else {
                $filters = array();
            }
            
            // make primary keys hidden and disabled
            if ($cols[$name]['primary']) {
                $info['type'] = 'hidden';
                $info['disable'] = true;
            }
            
            // pick an element type based on the column type
            if (empty($info['type'])) {
                // base the element type on the column type.
                switch ($cols[$name]['type']) {
                
                case 'bool':
                    $info['type'] = 'checkbox';
                    break;
                    
                case 'clob':
                    $info['type'] = 'textarea';
                    break;
                    
                case 'date':
                case 'time':
                case 'timestamp':
                    $info['type'] = $cols[$name]['type'];
                    break;
                    
                default:
                    
                    // make 'id' and '*_id' columns hidden
                    if ($name == 'id' || substr($name, -3) == '_id') {
                        $info['type'] = 'hidden';
                    }
                    
                    // if there is a filter to 'validateInList' or 'validateInKeys',
                    // make this a select element.
                    foreach ($filters as $v) {
                        if ($v[0] == 'validateInKeys' || $v[0] == 'validateInList') {
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
            
            // set up options for checkboxes if none specified
            if ($info['type'] == 'checkbox' && empty($info['options'])) {
                // look for 'validateInKeys' or 'validateInList' validation.
                foreach ($filters as $v) {
                    if ($v[0] == 'validateInKeys' || $v[0] == 'validateInList') {
                        $info['options'] = $this->_autoOptions($v[0], $v[1]);
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
                // look for 'validateInKeys' or 'validateInList'
                foreach ($filters as $v) {
                    if ($v[0] == 'validateInKeys' || $v[0] == 'validateInList') {
                        $info['options'] = $this->_autoOptions($v[0], $v[1]);
                        break;
                    }
                }
            }
            
            // for text elements, set up maxlength if none specified
            if ($info['type'] == 'text' &&
                empty($info['attribs']['maxlength']) && 
                $cols[$name]['size'] > 0) {
                /** @todo Add +1 or +2 to 'size' for numeric types? */
                $info['attribs']['maxlength'] = $cols[$name]['size'];
            }
            
            // if no label specified, set up based on element name
            if (empty($info['label'])) {
                $info['label'] = $info['name'];
            }
            
            // if there is a validateNotBlank filter, mark to require
            foreach ($filters as $v) {
                if ($v[0] == 'validateNotBlank') {
                    $info['require'] = true;
                    break;
                }
            }
            
            // keep the element
            $elements[$info['name']] = $info;
        }
        
        // done!
        $result = array(
            'attribs'  => array(),
            'elements' => $elements
        );
        
        return $result;
    }
    
    /**
     * 
     * Builds an option list from validateInKeys and validateInList values.
     * 
     * The 'validateInKeys' options are not changed.
     * 
     * The 'validateInList' options are generally sequential, so the label
     * and the value are made to be identical (based on the label).
     * 
     * @param string $type The validation type, 'validateInKeys' or 'validateInList'.
     * 
     * @param array $opts The options provided by the validation.
     * 
     * @return array
     * 
     */
    protected function _autoOptions($type, $opts)
    {
        // leave the labels and values alone
        if ($type == 'validateInKeys') {
            return $opts;
        }
        
        // make the form display the labels as both labels and values
        if ($type == 'validateInList') {
            $vals = array_values($opts);
            return array_combine($vals, $vals);
        }
    }
}
