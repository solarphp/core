<?php
class Solar_Vt100 extends Solar_Base
{
    /**
     * 
     * Array of format conversions for use on a variety of pre-set console
     * style combinations.
     * 
     * Based on ANSI VT100 Color/Style Codes, according to the [VT100 User Guide][1]
     * and the [ANSI/VT100 Terminal Control reference][2]. Inspired by
     * [PEAR Console_Color][3].
     * 
     * [1]: http://vt100.net/docs/vt100-ug
     * [2]: http://www.termsys.demon.co.uk/vtansi.htm
     * [3]: http://pear.php.net/Console_Color
     * 
     * @var array
     * 
     */
    static protected $_format = array(
        
        // literal percent sign
        '%%'    => '%',             // percent-sign
        
        // color, normal weight
        '%k'    => "\033[30m",      // black
        '%r'    => "\033[31m",      // red
        '%g'    => "\033[32m",      // green
        '%y'    => "\033[33m",      // yellow
        '%b'    => "\033[34m",      // blue
        '%m'    => "\033[35m",      // magenta/purple
        '%p'    => "\033[35m",      // magenta/purple
        '%c'    => "\033[36m",      // cyan/light blue
        '%w'    => "\033[37m",      // white
        '%n'    => "\033[0m",       // reset to terminal default
        
        // color, bold
        '%K'    => "\033[30;1m",    // black, bold
        '%R'    => "\033[31;1m",    // red, bold
        '%G'    => "\033[32;1m",    // green, bold
        '%Y'    => "\033[33;1m",    // yellow, bold
        '%B'    => "\033[34;1m",    // blue, bold
        '%M'    => "\033[35;1m",    // magenta/purple, bold
        '%P'    => "\033[35;1m",    // magenta/purple, bold
        '%C'    => "\033[36;1m",    // cyan/light blue, bold
        '%W'    => "\033[37;1m",    // white, bold
        '%N'    => "\033[0;1m",     // terminal default, bold
        
        // background color
        '%0'    => "\033[40m",      // black background
        '%1'    => "\033[41m",      // red background
        '%2'    => "\033[42m",      // green background
        '%3'    => "\033[43m",      // yellow background
        '%4'    => "\033[44m",      // blue background
        '%5'    => "\033[45m",      // magenta/purple background
        '%6'    => "\033[46m",      // cyan/light blue background
        '%7'    => "\033[47m",      // white background
        
        // assorted style shortcuts
        '%F'    => "\033[5m",       // blink/flash
        '%_'    => "\033[5m",       // blink/flash
        '%U'    => "\033[4m",       // underline
        '%I'    => "\033[7m",       // reverse/inverse
        '%*'    => "\033[1m",       // bold
        '%d'    => "\033[2m",       // dim        
    );
    
    static public function format($text)
    {
        return strtr($text, self::$_format);
    }
    
    /**
     * 
     * Escapes ASCII control codes (0-31, 127) and %-signs.
     * 
     * Note that this will catch newlines and carriage returns as well.
     * 
     * @param string $text The text to escape.
     * 
     * @return string The escaped text.
     * 
     */
    static public function escape($text)
    {
        static $list;
        
        if (! $list) {
            
            $list = array(
                '%' => '%%',
            );
            
            for ($i = 0; $i < 32; $i ++) {
                $list[chr($i)] = "\\$i";
            }
            
            $list[chr(127)] = "\\127";
            
        }
        
        return strtr($text, $list);
    }
}