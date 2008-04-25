<?php
/**
 * 
 * Helper for a 'timestamp' pseudo-element.
 * 
 * For an element named 'foo[bar]', builds a series of selects:
 * 
 * - foo[bar][Y] : +/- 4 years from selected value; if none, current year +/- 4
 * - foo[bar][m] : 01-12
 * - foo[bar][d] : 01-31
 * - foo[bar][H] : 00-23
 * - foo[bar][i] : 00-59
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormText.php 2933 2007-11-09 20:37:35Z moraes $
 * 
 */
class Solar_View_Helper_FormTimestamp extends Solar_View_Helper_FormElement {
    
    /**
     * 
     * Helper for a 'timestamp' pseudo-element.
     * 
     * For an element named 'foo[bar]', returns a series of selects:
     * 
     * - foo[bar][Y] : +/- 4 years from selected value; if none, current year +/- 4
     * - foo[bar][m] : 01-12
     * - foo[bar][d] : 01-31
     * - foo[bar][H] : 00-23
     * - foo[bar][i] : 00-59
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formTimestamp($info)
    {
        $this->_prepare($info);
        return $this->_selectYear()  . '-'
             . $this->_selectMonth() . '-'
             . $this->_selectDay()   . ' @ '
             . $this->_selectHour()  . ':'
             . $this->_selectMinute();
    }
    
    /**
     * 
     * Looks up a part of the element value based on a date() format character
     * key.
     * 
     * @param string $key The date() format character key: Y, d, h, H, i.
     * 
     * @return string
     * 
     */
    protected function _getValue($key)
    {
        if (! $this->_value) {
            return null;
        }
        
        if (is_array($this->_value)) {
            if (array_key_exists($key, $this->_value)) {
                return $this->_value[$key];
            } else {
                return null;
            }
        }
        
        switch ($key) {
            
        // work forward, to support date-only values
        // 0123456789
        // 1970-01-23
        case 'Y':
            return substr($this->_value, 0, 4);
            break;
        case 'm':
            return substr($this->_value, 5, 2);
            break;
        case 'd':
            return substr($this->_value, 8, 2);
            break;
            
        // work backward, to support time-only values
        // 87654321
        // 01:23:45
        case 'H':
            return substr($this->_value, -8, 2);
            break;
        case 'i':
            return substr($this->_value, -5, 2);
            break;
        case 's':
            return substr($this->_value, -2, 2);
            break;
        }
    }
    
    /**
     * 
     * Returns a <select>...</select> tag for the year.
     * 
     * @return string
     * 
     */
    protected function _selectYear()
    {
        $name    = $this->_name . '[Y]';
        $value   = $this->_getValue('Y');
        
        if ($value) {
            $tmp = $value;
        } else {
            $tmp = date('Y');
        }
        
        $options = array(
            ''=>'-',
            $tmp-4=>$tmp-4, $tmp-3=>$tmp-3, $tmp-2=>$tmp-2, $tmp-1=>$tmp-1,
            $tmp+0=>$tmp+0, $tmp+1=>$tmp+1, $tmp+2=>$tmp+2, $tmp+3=>$tmp+3,
            $tmp+4=>$tmp+4,
        );
        
        return $this->_view->formSelect(array(
            'name'    => $name,
            'value'   => $value,
            'options' => $options,
        )) . "\n";
    }
    
    /**
     * 
     * Returns a <select>...</select> tag for the month.
     * 
     * @return string
     * 
     */
    protected function _selectMonth()
    {
        $name    = $this->_name . '[m]';
        $value   = $this->_getValue('m');
        $options = array(
            ''   => '-',
            '01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04', '05'=>'05',
            '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09', '10'=>'10',
            '11'=>'11', '12'=>'12',
        );
        
        return $this->_view->formSelect(array(
            'name'    => $name,
            'value'   => $value,
            'options' => $options,
        )) . "\n";
    }
    
    /**
     * 
     * Returns a <select>...</select> tag for the day of the month.
     * 
     * @return string
     * 
     */
    protected function _selectDay()
    {
        $name    = $this->_name . '[d]';
        $value   = $this->_getValue('d');
        $options = array(
            ''=>'-',
            '01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04', '05'=>'05',
            '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09', '10'=>'10',
            '11'=>'11', '12'=>'12', '13'=>'13', '14'=>'14', '15'=>'15',
            '16'=>'16', '17'=>'17', '18'=>'18', '19'=>'19', '20'=>'20',
            '21'=>'21', '22'=>'22', '23'=>'23', '24'=>'24', '25'=>'25',
            '26'=>'26', '27'=>'27', '28'=>'28', '29'=>'29', '30'=>'30',
            '31'=>'31',
        );
        
        return $this->_view->formSelect(array(
            'name'    => $name,
            'value'   => $value,
            'options' => $options,
        )) . "\n";
    }
    
    /**
     * 
     * Returns a <select>...</select> tag for the hour.
     * 
     * @return string
     * 
     */
    protected function _selectHour()
    {
        $name    = $this->_name . '[H]';
        $value   = $this->_getValue('H');
        $options = array(
            ''=>'-',
            '00'=>'00', '01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04',
            '05'=>'05', '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09',
            '10'=>'10', '11'=>'11', '12'=>'12', '13'=>'13', '14'=>'14',
            '15'=>'15', '16'=>'16', '17'=>'17', '18'=>'18', '19'=>'19',
            '20'=>'20', '21'=>'21', '22'=>'22', '23'=>'23',
        );
        
        return $this->_view->formSelect(array(
            'name'    => $name,
            'value'   => $value,
            'options' => $options,
        )) . "\n";
    }
    
    /**
     * 
     * Returns a <select>...</select> tag for the minute.
     * 
     * @return string
     * 
     */
    protected function _selectMinute()
    {
        $name    = $this->_name . '[i]';
        $value   = $this->_getValue('i');
        $options = array(
            ''=>'-',
            '00'=>'00', '01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04',
            '05'=>'05', '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09',
            '10'=>'10', '11'=>'11', '12'=>'12', '13'=>'13', '14'=>'14',
            '15'=>'15', '16'=>'16', '17'=>'17', '18'=>'18', '19'=>'19',
            '20'=>'20', '21'=>'21', '22'=>'22', '23'=>'23', '24'=>'24',
            '25'=>'25', '26'=>'26', '27'=>'27', '28'=>'28', '29'=>'29',
            '30'=>'30', '31'=>'31', '32'=>'32', '33'=>'33', '34'=>'34',
            '35'=>'35', '36'=>'36', '37'=>'37', '38'=>'38', '39'=>'39',
            '40'=>'40', '41'=>'41', '42'=>'42', '43'=>'43', '44'=>'44',
            '45'=>'45', '46'=>'46', '47'=>'47', '48'=>'48', '49'=>'49',
            '50'=>'50', '51'=>'51', '52'=>'52', '53'=>'53', '54'=>'54',
            '55'=>'55', '56'=>'56', '57'=>'57', '58'=>'58', '59'=>'59',
        );
        
        return $this->_view->formSelect(array(
            'name'    => $name,
            'value'   => $value,
            'options' => $options,
        )) . "\n";
    }
}
