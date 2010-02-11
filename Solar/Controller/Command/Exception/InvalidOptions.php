<?php
class Solar_Controller_Command_Exception_InvalidOptions extends Solar_Controller_Command_Exception
{
    public function getMessageInvalid()
    {
        $text = parent::getMessage();
        $info = $this->getInfo();
        $invalid = $info['invalid'];
        $options = $info['options'];
        
        foreach ($invalid as $name => $list) {
            
            $opt   = $options[$name];
            $long  = ($opt['long'])  ? "--{$opt['long']}" : '';
            $short = ($opt['short']) ? "-{$opt['short']}" : '';
            
            if ($long && $short) {
                $label = "$long | $short";
            } else {
                $label = $long . $short;
            }
                   
            foreach ($list as $value) {
                $text .= PHP_EOL . "$label: $value";
            }
        }
        return $text;
    }
}